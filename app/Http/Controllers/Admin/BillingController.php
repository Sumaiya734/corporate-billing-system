<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Payment;
use App\Models\CustomerPackage;
use App\Models\MonthlyBillingSummary;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * Display all invoices page
     */
    public function allInvoices()
    {
        try {
            $invoices = Invoice::with(['customer', 'invoicePackages'])
                ->orderBy('issue_date', 'desc')
                ->paginate(20);

            $stats = [
                'total_invoices' => Invoice::count(),
                'pending_invoices' => Invoice::whereIn('status', ['unpaid', 'partial'])->count(),
                'paid_invoices' => Invoice::where('status', 'paid')->count(),
                'total_revenue' => Invoice::sum('total_amount'),
                'total_received' => Invoice::sum('received_amount'),
                'total_due' => DB::table('invoices')
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->sum(DB::raw('total_amount - COALESCE(received_amount, 0)'))
            ];

            return view('admin.billing.all-invoices', compact('stats', 'invoices'));

        } catch (\Exception $e) {
            Log::error('All invoices error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading invoices: ' . $e->getMessage());
        }
    }

    /**
     * Generate bill for a customer
     */
    public function generateBill($id)
    {
        try {
            $customer = Customer::with(['activePackages'])->findOrFail($id);
            
            $regularPackages = Package::where('package_type', 'regular')->get();
            $specialPackages = Package::where('package_type', 'special')->get();

            return view('admin.billing.generate-bill', compact(
                'customer', 
                'regularPackages', 
                'specialPackages'
            ));

        } catch (\Exception $e) {
            Log::error('Generate bill error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading generate bill page: ' . $e->getMessage());
        }
    }

    /**
     * Process bill generation
     */
    public function processBillGeneration(Request $request, $customerId)
    {
        $request->validate([
            'billing_month' => 'required|date',
            'regular_packages' => 'required|array',
            'special_packages' => 'array',
            'discount' => 'numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        try {
            $customer = Customer::findOrFail($customerId);

            $regularPackageAmount = $this->calculatePackageAmount($request->regular_packages);
            $specialPackageAmount = $this->calculatePackageAmount($request->special_packages ?? []);
            
            $serviceCharge = 50.00;
            $vatPercentage = 5.00;
            $subtotal = $regularPackageAmount + $specialPackageAmount + $serviceCharge;
            $vatAmount = $subtotal * ($vatPercentage / 100);
            $discountAmount = $subtotal * ($request->discount / 100);
            $totalAmount = $subtotal + $vatAmount - $discountAmount;

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'c_id' => $customerId,
                'issue_date' => Carbon::parse($request->billing_month),
                'previous_due' => 0.00,
                'service_charge' => $serviceCharge,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => $vatAmount,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'received_amount' => 0,
                'next_due' => $totalAmount,
                'status' => 'unpaid',
                'notes' => $request->notes,
                'created_by' => Auth::id()
            ]);

            // Attach packages to invoice
            $this->attachPackagesToInvoice($invoice, $request->regular_packages, $request->special_packages);

            return redirect()->route('admin.billing.view-bill', $invoice->invoice_id)
                ->with('success', 'Bill generated successfully for ' . $customer->name);

        } catch (\Exception $e) {
            Log::error('Process bill generation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating bill: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to calculate package amount
     */
    private function calculatePackageAmount($packageIds)
    {
        return Package::whereIn('p_id', $packageIds)->sum('monthly_price');
    }

    /**
     * Attach packages to invoice
     */
    private function attachPackagesToInvoice($invoice, $regularPackages, $specialPackages)
    {
        $allPackages = array_merge($regularPackages, $specialPackages);
        
        foreach ($allPackages as $packageId) {
            $package = Package::find($packageId);
            if ($package) {
                DB::table('invoice_packages')->insert([
                    'invoice_id' => $invoice->invoice_id,
                    'cp_id' => $this->getCustomerPackageId($invoice->c_id, $packageId),
                    'package_price' => $package->monthly_price,
                    'billing_cycle_months' => 1,
                    'total_package_amount' => $package->monthly_price,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Get customer package ID
     */
    private function getCustomerPackageId($customerId, $packageId)
    {
        $customerPackage = CustomerPackage::where('c_id', $customerId)
            ->where('p_id', $packageId)
            ->where('status', 'active')
            ->where('is_active', true)
            ->first();

        return $customerPackage ? $customerPackage->cp_id : null;
    }

    /**
     * View bill details
     */
    public function viewBill($id)
    {
        try {
            $invoice = Invoice::with(['customer', 'invoicePackages.package', 'payments'])
                            ->findOrFail($id);

            return view('admin.billing.view-bill', compact('invoice'));

        } catch (\Exception $e) {
            Log::error('View bill error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading bill: ' . $e->getMessage());
        }
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, $invoiceId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string|max:100',
            'note' => 'nullable|string'
        ]);

        try {
            $invoice = Invoice::findOrFail($invoiceId);

            $payment = Payment::create([
                'invoice_id' => $invoiceId,
                'c_id' => $invoice->c_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'transaction_id' => $request->transaction_id,
                'note' => $request->note
            ]);

            // Update invoice status and amounts
            $newReceivedAmount = $invoice->received_amount + $request->amount;
            $newDue = max(0, $invoice->total_amount - $newReceivedAmount);

            $status = $newDue <= 0 ? 'paid' : ($newReceivedAmount > 0 ? 'partial' : 'unpaid');

            $invoice->update([
                'received_amount' => $newReceivedAmount,
                'next_due' => $newDue,
                'status' => $status
            ]);

            // If the request is AJAX, return JSON to the frontend
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully!'
                ]);
            }
            return redirect()->back()->with('success', 'Payment recorded successfully!');

        } catch (\Exception $e) {
            Log::error('Record payment error: ' . $e->getMessage());
            // If AJAX request, return JSON so front-end can handle it
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to record payment: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Customer profile page
     */
    public function profile($id)
    {
        try {
            $customer = Customer::with([
                'invoices' => function($query) {
                    $query->orderBy('issue_date', 'desc')->limit(12);
                }, 
                'activePackages.package'
            ])->findOrFail($id);

            // Get customer's active packages
            $packageNames = $customer->activePackages->pluck('package.name')->toArray();

            // Calculate monthly bill from active packages
            $monthlyBill = $customer->activePackages->sum(function($customerPackage) {
                return $customerPackage->package->monthly_price ?? 0;
            });

            // Format billing history
            $billingHistory = $customer->invoices->map(function($invoice) {
                return [
                    'month' => $invoice->issue_date->format('F Y'),
                    'amount' => '৳' . number_format($invoice->total_amount, 2),
                    'status' => ucfirst($invoice->status),
                    'due_date' => $invoice->issue_date->format('Y-m-d') // Using issue_date since due_date doesn't exist
                ];
            });

            return view('admin.customers.profile', compact('customer', 'packageNames', 'monthlyBill', 'billingHistory'));

        } catch (\Exception $e) {
            Log::error('Customer profile error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading customer profile: ' . $e->getMessage());
        }
    }

    /**
     * Show individual customer billing details
     */
    public function customerBillingDetails($c_id)
    {
        try {
            $customer = Customer::findOrFail($c_id);

            $packages = $customer->customerPackages()
                ->with('package')
                ->get();

            $invoices = $customer->invoices()
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.billing.customer-billing-details', compact(
                'customer',
                'packages',
                'invoices'
            ));
        } catch (\Exception $e) {
            Log::error("BillingController@customerBillingDetails: " . $e->getMessage());
            return back()->with('error', 'Failed to load customer billing details.');
        }
    }

    /**
     * Display dynamic billing summary page
     */
    public function billingInvoices(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            
            // Get statistics using Eloquent
            $totalActiveCustomers = Customer::active()->count();
            
            $currentMonthRevenue = Payment::whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->sum('amount');
                
            $totalPendingAmount = Invoice::whereIn('status', ['unpaid', 'partial'])
                ->sum(DB::raw('total_amount - COALESCE(received_amount, 0)'));
            
            // Calculate this month bills count
            $thisMonthBillsCount = $this->calculateThisMonthBillsCount();
            
            // Get dynamic monthly summary
            $monthlySummary = $this->getDynamicMonthlySummary();
            
            // Get current month stats
            $currentMonthStats = $this->calculateCurrentMonthStats();
            
            // Get available months for invoice generation
            $availableMonths = $this->getAvailableBillingMonths();
            
            // Get recent payments with relationships
            $recentPayments = Payment::with(['invoice.customer'])
                ->orderBy('payment_date', 'desc')
                ->limit(5)
                ->get();

            // Get overdue invoices (invoices with due amounts)
            $overdueInvoices = Invoice::with('customer')
                ->whereIn('status', ['unpaid', 'partial'])
                ->where('next_due', '>', 0)
                ->orderBy('issue_date', 'asc')
                ->limit(5)
                ->get();

            // Check if we have invoices
            $hasInvoices = Invoice::exists();

            return view('admin.billing.billing-invoices', [
                'monthlySummary' => $monthlySummary,
                'currentMonthStats' => $currentMonthStats,
                'availableMonths' => $availableMonths,
                'totalActiveCustomers' => $totalActiveCustomers,
                'currentMonthRevenue' => $currentMonthRevenue,
                'totalPendingAmount' => $totalPendingAmount,
                'previousMonthBillsCount' => $thisMonthBillsCount, // Fixed variable name
                'recentPayments' => $recentPayments,
                'overdueInvoices' => $overdueInvoices,
                'hasInvoices' => $hasInvoices,
                'year' => $year
            ]);

        } catch (\Exception $e) {
            Log::error('Billing invoices error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading billing data: ' . $e->getMessage());
        }
    }

    /**
     * Calculate this month bills count
     */
    private function calculateThisMonthBillsCount()
    {
        $currentMonth = date('Y-m');
        $monthDate = Carbon::createFromFormat('Y-m', $currentMonth);
        
        $dueCustomers = $this->getDueCustomersForMonth($monthDate);
        
        return $dueCustomers->count();
    }

    /**
     * Get dynamic monthly summary
     */
    private function getDynamicMonthlySummary()
    {
        $months = collect();
        $currentDate = Carbon::now()->startOfMonth();
        $currentMonth = $currentDate->format('Y-m');
        
        // Generate last 12 months + current month
        for ($i = 12; $i >= 0; $i--) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $month = $monthDate->format('Y-m');
            $displayMonth = $monthDate->format('F Y');
            
            $monthData = $this->calculateMonthData($month);
            
            // Only include months that have due customers or are current/future
            if ($monthData['total_customers'] > 0 || $monthDate >= $currentDate) {
                $months->push((object)[
                    'id' => $month,
                    'display_month' => $displayMonth,
                    'billing_month' => $month,
                    'total_customers' => $monthData['total_customers'],
                    'total_amount' => $monthData['total_amount'],
                    'received_amount' => $monthData['received_amount'],
                    'due_amount' => $monthData['due_amount'],
                    'is_current_month' => $month === $currentMonth,
                    'is_future_month' => $month > $currentMonth,
                    'is_locked' => $monthDate->lt(Carbon::now()->subMonths(3)),
                    'is_dynamic' => true,
                    'status' => $monthData['status'],
                    'notes' => 'Automatically calculated'
                ]);
            }
        }

        // Add next 3 months for future billing
        for ($i = 1; $i <= 3; $i++) {
            $monthDate = $currentDate->copy()->addMonths($i);
            $month = $monthDate->format('Y-m');
            $displayMonth = $monthDate->format('F Y');
            
            $monthData = $this->calculateMonthData($month);
            
            $months->push((object)[
                'id' => $month,
                'display_month' => $displayMonth,
                'billing_month' => $month,
                'total_customers' => $monthData['total_customers'],
                'total_amount' => $monthData['total_amount'],
                'received_amount' => 0,
                'due_amount' => $monthData['total_amount'],
                'is_current_month' => false,
                'is_future_month' => true,
                'is_locked' => false,
                'is_dynamic' => true,
                'status' => 'Pending',
                'notes' => 'Future billing projection'
            ]);
        }

        return $months->sortByDesc('billing_month')->values();
    }

    /**
     * Calculate data for a specific month
     */
    private function calculateMonthData($month)
    {
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        
        // Get due customers for this month
        $dueCustomers = $this->getDueCustomersForMonth($monthDate);
        
        // Calculate expected revenue from due customers
        $totalPackageAmount = $dueCustomers->sum('monthly_price');
        $serviceCharge = 50 * $dueCustomers->count();
        $subtotal = $totalPackageAmount + $serviceCharge;
        $vatAmount = $subtotal * 0.05;
        $totalAmount = $subtotal + $vatAmount;

        // Get actual payments from invoices for this month
        $payments = Invoice::whereYear('issue_date', $monthDate->year)
            ->whereMonth('issue_date', $monthDate->month)
            ->selectRaw('SUM(total_amount) as total, SUM(received_amount) as received')
            ->first();

        $receivedAmount = $payments->received ?? 0;
        $dueAmount = max(0, $totalAmount - $receivedAmount);
        
        // Calculate status
        $status = $this->calculateStatus($totalAmount, $receivedAmount, $dueAmount);
        
        return [
            'total_customers' => $dueCustomers->count(),
            'total_amount' => $totalAmount,
            'received_amount' => $receivedAmount,
            'due_amount' => $dueAmount,
            'status' => $status
        ];
    }

    /**
     * Calculate status based on amounts
     */
    private function calculateStatus($totalAmount, $receivedAmount, $dueAmount)
    {
        if ($totalAmount == 0) {
            return 'All Paid';
        }
        
        if ($dueAmount <= 0) {
            return 'All Paid';
        }
        
        $collectionRate = ($receivedAmount / $totalAmount) * 100;
        
        if ($collectionRate >= 80) {
            return 'Pending';
        }
        
        return 'Overdue';
    }

    /**
     * Get customers due in specific month
     */
    private function getDueCustomersForMonth(Carbon $monthDate)
    {
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();
        
        return Customer::select(
                'customers.c_id',
                'customers.name',
                'customers.customer_id',
                'packages.monthly_price'
            )
            ->join('customer_to_packages as cp', 'customers.c_id', '=', 'cp.c_id')
            ->join('packages', 'cp.p_id', '=', 'packages.p_id')
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where('customers.is_active', 1)
            ->where(function($query) use ($monthStart, $monthEnd) {
                // Customers whose billing cycle falls in this month
                $query->where(function($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('cp.due_date', [$monthStart, $monthEnd]);
                })
                // Monthly billing customers
                ->orWhere(function($q) use ($monthEnd) {
                    $q->where('cp.billing_cycle_months', 1)
                      ->where('cp.assign_date', '<=', $monthEnd);
                });
            })
            ->groupBy('customers.c_id', 'customers.name', 'customers.customer_id', 'packages.monthly_price')
            ->orderBy('customers.name')
            ->get();
    }

    /**
     * Calculate current month statistics
     */
    private function calculateCurrentMonthStats()
    {
        $currentMonth = date('Y-m');
        $monthData = $this->calculateMonthData($currentMonth);
        
        return (object)[
            'total_customers' => $monthData['total_customers'],
            'total_amount' => $monthData['total_amount'],
            'received_amount' => $monthData['received_amount'],
            'due_amount' => $monthData['due_amount']
        ];
    }

    /**
     * Get available billing months
     */
    private function getAvailableBillingMonths()
    {
        $months = collect();
        
        // Add current and future months (up to 6 months ahead)
        $currentDate = Carbon::now()->startOfMonth();
        for ($i = 0; $i <= 6; $i++) {
            $futureMonth = $currentDate->copy()->addMonths($i)->format('Y-m');
            $months->push($futureMonth);
        }

        // Add past 3 months for catch-up billing
        for ($i = 1; $i <= 3; $i++) {
            $pastMonth = $currentDate->copy()->subMonths($i)->format('Y-m');
            $months->push($pastMonth);
        }

        return $months->unique()->sortDesc()->values();
    }

    /**
     * Generate invoices for a specific month
     */
    public function generateMonthInvoices(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m'
        ]);

        try {
            $month = $request->month;
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $displayMonth = $monthDate->format('F Y');

            // Get due customers for the month
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);

            if ($dueCustomers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No customers due for billing in ' . $displayMonth
                ]);
            }

            $generatedCount = 0;
            $errors = [];

            foreach ($dueCustomers as $customer) {
                try {
                    // Check if invoice already exists
                    $existingInvoice = Invoice::where('c_id', $customer->c_id)
                        ->whereYear('issue_date', $monthDate->year)
                        ->whereMonth('issue_date', $monthDate->month)
                        ->first();

                    if (!$existingInvoice) {
                        // Create new invoice
                        $this->createCustomerInvoice($customer, $monthDate);
                        $generatedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Customer {$customer->name}: " . $e->getMessage();
                    Log::error("Invoice generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }

            $response = [
                'success' => true,
                'message' => "Generated $generatedCount invoices for $displayMonth",
                'generated_count' => $generatedCount
            ];

            if (!empty($errors)) {
                $response['warnings'] = $errors;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Generate month invoices error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create invoice for a customer
     */
    private function createCustomerInvoice($customer, Carbon $monthDate)
    {
        $serviceCharge = 50.00;
        $vatPercentage = 5.00;

        // Calculate package amount
        $packageAmount = $customer->monthly_price;
        $subtotal = $packageAmount + $serviceCharge;
        $vatAmount = $subtotal * ($vatPercentage / 100);
        $totalAmount = $subtotal + $vatAmount;

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'c_id' => $customer->c_id,
            'issue_date' => $monthDate->format('Y-m-d'),
            'previous_due' => 0.00,
            'service_charge' => $serviceCharge,
            'vat_percentage' => $vatPercentage,
            'vat_amount' => $vatAmount,
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'received_amount' => 0,
            'next_due' => $totalAmount,
            'status' => 'unpaid',
            'notes' => 'Auto-generated based on package assignment',
            'created_by' => Auth::id()
        ]);

        return $invoice;
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = Invoice::whereYear('created_at', $year)->latest()->first();

        if ($lastInvoice && preg_match('/-(\d+)$/', $lastInvoice->invoice_number, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "INV-{$year}-{$newNumber}";
    }

    /**
     * Store manual monthly billing summary
     */
    public function storeMonthly(Request $request)
    {
        $request->validate([
            'billing_month' => 'required|date_format:Y-m',
            'total_customers' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'received_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric|min:0',
            'status' => 'required|in:All Paid,Pending,Overdue',
            'notes' => 'nullable|string'
        ]);

        try {
            // Check if already exists
            $existing = MonthlyBillingSummary::where('billing_month', $request->billing_month)->first();
            if ($existing) {
                return redirect()->back()->with('error', 'Billing summary for this month already exists.');
            }

            MonthlyBillingSummary::create([
                'billing_month' => $request->billing_month,
                'display_month' => Carbon::createFromFormat('Y-m', $request->billing_month)->format('F Y'),
                'total_customers' => $request->total_customers,
                'total_amount' => $request->total_amount,
                'received_amount' => $request->received_amount,
                'due_amount' => $request->due_amount,
                'status' => $request->status,
                'notes' => $request->notes,
                'is_locked' => false,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('admin.billing.billing-invoices')
                ->with('success', 'Monthly billing summary created successfully.');

        } catch (\Exception $e) {
            Log::error('Store monthly billing error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating billing summary: ' . $e->getMessage());
        }
    }

    /**
     * Generate from invoices and packages
     */
    public function generateFromInvoices(Request $request)
    {
        $request->validate([
            'billing_month' => 'required|date_format:Y-m'
        ]);

        try {
            $month = $request->billing_month;
            $monthData = $this->calculateMonthData($month);

            // Check if already exists
            $existing = MonthlyBillingSummary::where('billing_month', $month)->first();
            if ($existing) {
                return redirect()->back()->with('error', 'Billing summary for this month already exists.');
            }

            MonthlyBillingSummary::create([
                'billing_month' => $month,
                'display_month' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                'total_customers' => $monthData['total_customers'],
                'total_amount' => $monthData['total_amount'],
                'received_amount' => $monthData['received_amount'],
                'due_amount' => $monthData['due_amount'],
                'status' => $monthData['status'],
                'notes' => 'Generated from customer packages and invoices',
                'is_locked' => false,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('admin.billing.billing-invoices')
                ->with('success', 'Monthly billing summary generated successfully from packages and invoices.');

        } catch (\Exception $e) {
            Log::error('Generate from invoices error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating billing summary: ' . $e->getMessage());
        }
    }
    
    /**
     * Display monthly bills for a specific month
     */
   
}