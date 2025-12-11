<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\product;
use App\Models\Payment;
use App\Models\Customerproduct;
use App\Models\MonthlyBillingSummary;
use App\Models\BillingPeriod;
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
            $invoices = Invoice::with(['customer', 'invoiceproducts'])
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
            $customer = Customer::with(['activeproducts'])->findOrFail($id);
            
            $regularproducts = product::whereHas('type', function($query) {
                $query->where('name', 'regular');
            })->get();
            
            $specialproducts = product::whereHas('type', function($query) {
                $query->where('name', 'special');
            })->get();

            return view('admin.billing.generate-bill', compact(
                'customer', 
                'regularproducts', 
                'specialproducts'
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
            'regular_products' => 'required|array',
            'special_products' => 'array',
            'discount' => 'numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        try {
            $customer = Customer::findOrFail($customerId);

            $regularproductAmount = $this->calculateproductAmount($request->regular_products);
            $specialproductAmount = $this->calculateproductAmount($request->special_products ?? []);
            
            // Calculate total without service charge or VAT
            $subtotal = $regularproductAmount + $specialproductAmount;
            $discountAmount = $subtotal * ($request->discount / 100);
            $totalAmount = $subtotal - $discountAmount;

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'c_id' => $customerId,
                'issue_date' => Carbon::parse($request->billing_month),
                'previous_due' => 0.00,
                'service_charge' => 0.00,
                'vat_percentage' => 0.00,
                'vat_amount' => 0.00,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'received_amount' => 0,
                'next_due' => $totalAmount,
                'status' => 'unpaid',
                'notes' => $request->notes,
                'created_by' => Auth::id()
            ]);

            // Attach products to invoice
            $this->attachproductsToInvoice($invoice, $request->regular_products, $request->special_products);

            return redirect()->route('admin.billing.view-bill', $invoice->invoice_id)
                ->with('success', 'Bill generated successfully for ' . $customer->name);

        } catch (\Exception $e) {
            Log::error('Process bill generation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating bill: ' . $e->getMessage());
        }
    }

    /**
     * Get monthly billing details
     */

        public function monthlyDetails($month)
{
    try {
        // Parse the month and get date range
        $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month)->endOfMonth();
        
        // Get all customers active during this month
        $customers = DB::table('customers as c')
            ->leftJoin('customer_to_products as cp', 'c.c_id', '=', 'cp.c_id')
            ->leftJoin('products as p', 'cp.p_id', '=', 'p.p_id')
            ->leftJoin('product_type as pt', 'p.product_type_id', '=', 'pt.id')
            ->where('c.is_active', 1)
            ->where(function($query) use ($startDate, $endDate) {
                // Customers who were active during this month
                $query->where('cp.assign_date', '<=', $endDate)
                      ->where(function($q) use ($startDate) {
                          $q->where('cp.due_date', '>=', $startDate)
                            ->orWhereNull('cp.due_date');
                      });
            })
            ->select(
                'c.c_id',
                'c.customer_id',
                'c.name as customer_name',
                'c.email',
                'c.phone',
                'c.address',
                'c.created_at as customer_created_at',
                'cp.cp_id',
                'cp.assign_date',
                'cp.billing_cycle_months',
                'cp.due_date',
                'cp.status as subscription_status',
                'p.p_id',
                'p.name as product_name',
                'p.monthly_price',
                'pt.name as product_type'
            )
            ->orderBy('c.created_at')
            ->orderBy('c.name')
            ->get();

        // Group customers and their products
        $customerData = [];
        $totalCustomers = 0;
        $totalProducts = 0;
        $totalMonthlyRevenue = 0;

        foreach ($customers as $row) {
            $customerId = $row->c_id;
            
            if (!isset($customerData[$customerId])) {
                $customerData[$customerId] = [
                    'customer_info' => [
                        'customer_id' => $row->customer_id,
                        'name' => $row->customer_name,
                        'email' => $row->email,
                        'phone' => $row->phone,
                        'address' => $row->address,
                        'created_at' => $row->customer_created_at,
                        'is_new' => \Carbon\Carbon::parse($row->customer_created_at)->between($startDate, $endDate)
                    ],
                    'products' => []
                ];
                $totalCustomers++;
            }

            // Add product if exists
            if ($row->p_id) {
                $customerData[$customerId]['products'][] = [
                    'product_name' => $row->product_name,
                    'product_type' => $row->product_type,
                    'monthly_price' => $row->monthly_price,
                    'assign_date' => $row->assign_date,
                    'billing_cycle' => $row->billing_cycle_months,
                    'due_date' => $row->due_date,
                    'status' => $row->subscription_status
                ];
                $totalProducts++;
                
                // FIXED: Calculate actual revenue based on billing cycle and assignment date
                $monthlyPrice = $row->monthly_price;
                $billingCycle = $row->billing_cycle_months;
                
                // Calculate actual monthly revenue contribution
                if ($billingCycle == 1) {
                    // Monthly billing - full amount
                    $monthlyContribution = $monthlyPrice;
                } else {
                    // For longer billing cycles, calculate monthly equivalent
                    $monthlyContribution = $monthlyPrice / $billingCycle;
                }
                
                $totalMonthlyRevenue += $monthlyContribution;
            }
        }

        // Get invoices for this month to compare with actual billed amounts
        $invoices = DB::table('invoices')
            ->whereYear('issue_date', $startDate->year)
            ->whereMonth('issue_date', $startDate->month)
            ->select('invoice_id', 'invoice_number', 'cp_id', 'total_amount', 'received_amount', 'status', 'subtotal')
            ->get();

        // Calculate actual billed amount from invoices
        $actualBilledAmount = $invoices->sum('subtotal');
        $actualReceivedAmount = $invoices->sum('received_amount');

        // Get payments for this month
        $payments = DB::table('payments as p')
            ->join('invoices as i', 'p.invoice_id', '=', 'i.invoice_id')
            ->whereYear('p.payment_date', $startDate->year)
            ->whereMonth('p.payment_date', $startDate->month)
            ->select('p.payment_id', 'p.amount', 'p.payment_method', 'p.payment_date', 'i.invoice_number')
            ->get();

        // Calculate statistics
        $newCustomers = collect($customerData)->filter(function($customer) {
            return $customer['customer_info']['is_new'];
        })->count();

        $existingCustomers = $totalCustomers - $newCustomers;

        return view('admin.billing.monthly-details', compact(
            'month',
            'customerData',
            'totalCustomers',
            'totalProducts',
            'totalMonthlyRevenue',
            'newCustomers',
            'existingCustomers',
            'invoices',
            'payments',
            'startDate',
            'endDate',
            'actualBilledAmount',
            'actualReceivedAmount'
        ));

    } catch (\Exception $e) {
        return redirect()->route('admin.billing.index')
            ->with('error', 'Error loading monthly details: ' . $e->getMessage());
    }
}



    /**
     * Helper method to calculate product amount
     */
    private function calculateproductAmount($productIds)
    {
        return product::whereIn('p_id', $productIds)->sum('monthly_price');
    }

    /**
     * Attach products to invoice
     */
    private function attachproductsToInvoice($invoice, $regularproducts, $specialproducts)
    {
        $allproducts = array_merge($regularproducts, $specialproducts);
        
        foreach ($allproducts as $productId) {
            $product = product::find($productId);
            if ($product) {
                DB::table('invoice_products')->insert([
                    'invoice_id' => $invoice->invoice_id,
                    'cp_id' => $this->getCustomerproductId($invoice->c_id, $productId),
                    'product_price' => $product->monthly_price,
                    'billing_cycle_months' => 1,
                    'total_product_amount' => $product->monthly_price,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Get customer product ID
     */
    private function getCustomerproductId($customerId, $productId)
    {
        $customerproduct = Customerproduct::where('c_id', $customerId)
            ->where('p_id', $productId)
            ->where('status', 'active')
            ->where('is_active', true)
            ->first();

        return $customerproduct ? $customerproduct->cp_id : null;
    }

    /**
     * View bill details
     */
    public function viewBill($id)
    {
        try {
            $invoice = Invoice::with(['customer', 'invoiceproducts.product', 'payments'])
                            ->findOrFail($id);

            return view('admin.billing.view-bill', compact('invoice'));

        } catch (\Exception $e) {
            Log::error('View bill error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading bill: ' . $e->getMessage());
        }
    }

    /**
     * Edit bill details
     */
    public function editBill($id)
    {
        try {
            $invoice = Invoice::with(['customerProduct.customer', 'customerProduct.product', 'payments'])
                            ->findOrFail($id);

            return view('admin.billing.edit-invoice', compact('invoice'));

        } catch (\Exception $e) {
            Log::error('Edit bill error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading bill for editing: ' . $e->getMessage());
        }
    }

    /**
     * Get invoice HTML for modal display
     */
    public function getInvoiceHtml($invoiceId)
    {
        try {
            // Load invoice with customerProduct relationship (which includes customer and product)
            $invoice = Invoice::with(['customerProduct.customer', 'customerProduct.product', 'payments'])
                            ->find($invoiceId);

            // Check if invoice exists
            if (!$invoice) {
                Log::error("Invoice not found: {$invoiceId}");
                return response('<div class="alert alert-danger">Invoice not found.</div>', 404);
            }

            return view('admin.billing.invoice-html', compact('invoice'));

        } catch (\Exception $e) {
            Log::error('Get invoice HTML error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Invoice ID: ' . $invoiceId);
            return response('<div class="alert alert-danger">Error loading invoice: ' . $e->getMessage() . '</div>', 500);
        }
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, $invoiceId)
    {
        // Log request data
        Log::info('Payment Request Data:', $request->all());
        
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            //'transaction_id' => 'nullable|string|max:100',
            'note' => 'nullable|string'
        ]);

        DB::beginTransaction(); // Start database transaction
        
        try {
            $invoice = Invoice::findOrFail($invoiceId);
            
            // Log current invoice data
            Log::info('Current Invoice Data', [
                'id' => $invoice->invoice_id,
                'total' => $invoice->total_amount,
                'received' => $invoice->received_amount,
                'next_due' => $invoice->next_due
            ]);

            $payment = Payment::create([
                'invoice_id' => $invoiceId,
                'c_id' => $invoice->customerProduct->c_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                //'transaction_id' => $request->transaction_id,
                'note' => $request->note
            ]);

            // Update invoice status and amounts
            $newReceivedAmount = $invoice->received_amount + $request->amount;
            $newDue = max(0, $invoice->total_amount - $newReceivedAmount);

            // Handle floating point precision - consider amounts less than 0.01 as zero
            if ($newDue < 0.01) {
                $newDue = 0;
                $status = 'paid';
            } else {
                // If there's a partial payment, set status to 'confirmed'
                $status = 'confirmed';
            }
            
            // Log calculation results
            Log::info('Payment Calculation Results', [
                'newReceivedAmount' => $newReceivedAmount,
                'newDue' => $newDue,
                'status' => $status
            ]);

            $updateResult = $invoice->update([
                'received_amount' => $newReceivedAmount,
                'next_due' => $newDue,
                'status' => $status
            ]);
            
            // Quick fix: Force refresh and manual save
            $invoice->refresh(); // Database থেকে fresh data আনো

            // যদি next_due still incorrect হয়
            if ($invoice->next_due != $newDue) {
                Log::warning('Next due not updated properly, forcing update');
                
                // Direct DB query
                DB::table('invoices')
                    ->where('invoice_id', $invoiceId)
                    ->update([
                        'next_due' => $newDue,
                        'received_amount' => $newReceivedAmount,
                        'status' => $status
                    ]);
                
                // আবার refresh
                $invoice->refresh();
            }

            // Verify final values
            Log::info('Final Invoice Values:', [
                'next_due' => $invoice->next_due,
                'received_amount' => $invoice->received_amount,
                'status' => $invoice->status
            ]);
            
            // Log after update
            Log::info('Invoice Update Result', [
                'success' => $updateResult,
                'invoice_id' => $invoice->invoice_id
            ]);

            // Handle carry-forward of remaining due to next month
            $this->carryForwardDueToNextMonth($invoice, $newDue);
            
            DB::commit(); // Commit the transaction if everything is successful
            
            // Log successful completion
            Log::info('Payment Recorded Successfully', [
                'invoice_id' => $invoice->invoice_id,
                'payment_id' => $payment->id ?? null
            ]);
            
            // If the request is AJAX, return JSON to the frontend with updated invoice data
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully!',
                    'invoice_id' => $invoice->invoice_id,
                    'next_due' => $invoice->next_due,
                    'received_amount' => $invoice->received_amount,
                    'status' => $invoice->status,
                    'carried_forward' => $newDue > 0 ? true : false
                ]);
            }
            return redirect()->back()->with('success', 'Payment recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on error
            Log::error('Payment Error Details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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
                'activeproducts.product'
            ])->findOrFail($id);

            // Get customer's active products
            $productNames = $customer->activeproducts->pluck('product.name')->toArray();

            // Calculate monthly bill from active products
            $monthlyBill = $customer->activeproducts->sum(function($customerproduct) {
                return $customerproduct->product->monthly_price ?? 0;
            });

            // Format billing history
            $billingHistory = $customer->invoices->map(function($invoice) {
                return [
                    'month' => $invoice->issue_date->format('F Y'),
                    'amount' => '৳' . number_format($invoice->total_amount, 0),
                    'status' => ucfirst($invoice->status),
                    'due_date' => $invoice->issue_date->format('Y-m-d') // Using issue_date since due_date doesn't exist
                ];
            });

            return view('admin.customers.profile', compact('customer', 'productNames', 'monthlyBill', 'billingHistory'));

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

            $products = $customer->customerproducts()
                ->with('product')
                ->get();

            $invoices = $customer->invoices()
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.billing.customer-billing-details', compact(
                'customer',
                'products',
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
            
            // Current month revenue (payments received this month)
            $currentMonthRevenue = Payment::whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->sum('amount');
                
            // Total pending amount across all invoices (calculated properly)
            $totalPendingAmount = Invoice::selectRaw('SUM(GREATEST(total_amount - COALESCE(received_amount, 0), 0)) as pending')
                ->value('pending') ?? 0;
            
            // Calculate this month bills count
            $thisMonthBillsCount = $this->calculateThisMonthBillsCount();
            
            // Additional statistics for better insights
            $totalInvoicesCount = Invoice::count();
            $totalPaymentsCount = Payment::count();
            $totalRevenue = Payment::sum('amount');
            $totalInvoiceAmount = Invoice::sum('total_amount');
            $totalReceivedAmount = Invoice::sum('received_amount');
            
            // Get dynamic monthly summary
            $monthlySummary = $this->getDynamicMonthlySummary();
            
            // Get current month stats
            $currentMonthStats = $this->calculateCurrentMonthStats();
            
            // Get available months for invoice generation
            $availableMonths = $this->getAvailableBillingMonths();
            
            // Get recent payments with relationships - paginated
            $recentPayments = Payment::with(['invoice.customer'])
                ->orderBy('payment_date', 'desc')
                ->paginate(20);

            // Get overdue invoices (invoices with due amounts) - paginated
            $overdueInvoices = Invoice::with('customer')
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereRaw('total_amount > COALESCE(received_amount, 0)')
                ->orderBy('issue_date', 'asc')
                ->paginate(20);

            // Check if we have invoices
            $hasInvoices = Invoice::exists();

            return view('admin.billing.billing-invoices', [
                'monthlySummary' => $monthlySummary,
                'currentMonthStats' => $currentMonthStats,
                'availableMonths' => $availableMonths,
                'totalActiveCustomers' => $totalActiveCustomers,
                'currentMonthRevenue' => $currentMonthRevenue,
                'totalPendingAmount' => $totalPendingAmount,
                'previousMonthBillsCount' => $thisMonthBillsCount,
                'recentPayments' => $recentPayments,
                'overdueInvoices' => $overdueInvoices,
                'hasInvoices' => $hasInvoices,
                'year' => $year,
                // Additional statistics
                'totalInvoicesCount' => $totalInvoicesCount,
                'totalPaymentsCount' => $totalPaymentsCount,
                'totalRevenue' => $totalRevenue,
                'totalInvoiceAmount' => $totalInvoiceAmount,
                'totalReceivedAmount' => $totalReceivedAmount
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
   /**
 * Get dynamic monthly summary based on product assignment dates and billing cycles
 */
private function getDynamicMonthlySummary()
{
    $months = collect();
    $currentDate = Carbon::now()->startOfMonth();
    $currentMonth = $currentDate->format('Y-m');
    
    // Get all unique months from customer product assignments
    $assignmentMonths = Customerproduct::where('status', 'active')
        ->where('is_active', 1)
        ->whereNotNull('assign_date')
        ->selectRaw('DATE_FORMAT(assign_date, "%Y-%m") as month')
        ->distinct()
        ->pluck('month')
        ->sort();
    
    // Get all due months based on billing cycles
    $dueMonths = $this->getAllDueMonthsFromAssignments($assignmentMonths);
    
    // Generate all months from earliest assignment to current month
    $allMonthsList = collect();
    if ($assignmentMonths->isNotEmpty()) {
        $earliestMonth = $assignmentMonths->min();
        $earliestDate = Carbon::createFromFormat('Y-m', $earliestMonth)->startOfMonth();
        $currentDateObj = Carbon::createFromFormat('Y-m', $currentMonth)->startOfMonth();
        
        // Generate all months between earliest assignment and current month
        $tempDate = $earliestDate->copy();
        while ($tempDate <= $currentDateObj) {
            $allMonthsList->push($tempDate->format('Y-m'));
            $tempDate->addMonth();
        }
    }
    
    // Combine all months: assignment months + due months + all months in range + current month
    $allMonths = $assignmentMonths->merge($dueMonths)
        ->merge($allMonthsList)
        ->push($currentMonth)
        ->unique()
        ->sort()
        ->filter(function($month) use ($currentMonth) {
            // Only show months up to and including current month
            return $month <= $currentMonth;
        });
    
    foreach ($allMonths as $month) {
        $monthData = $this->calculateNewMonthData($month);
        
        // Show all months from earliest assignment to current month
        // This ensures visibility into carry forward data in ALL months including middle months of billing cycles
        $months->push((object)[
            'id' => $month,
            'display_month' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
            'billing_month' => $month,
            'total_customers' => $monthData['total_customers'],
            'total_amount' => $monthData['total_amount'],
            'received_amount' => $monthData['received_amount'],
            'due_amount' => $monthData['due_amount'],
            'is_current_month' => $month === $currentMonth,
            'is_future_month' => $month > $currentMonth,
            'is_locked' => false,
            'is_closed' => false,
            'is_dynamic' => true,
            'status' => $monthData['status'],
            'notes' => $monthData['notes'],
            'has_activity' => $monthData['has_activity']
        ]);
    }
    
    return $months->sortByDesc('billing_month')->values();
}

/**
 * Get all due months from customer product assignments
 */
private function getAllDueMonthsFromAssignments($assignmentMonths)
{
    $dueMonths = collect();
    
    foreach ($assignmentMonths as $assignMonth) {
        // Get all customer products assigned in this month
        $customerProducts = Customerproduct::where('status', 'active')
            ->where('is_active', 1)
            ->whereRaw('DATE_FORMAT(assign_date, "%Y-%m") = ?', [$assignMonth])
            ->get();
        
        foreach ($customerProducts as $cp) {
            $assignDate = Carbon::parse($cp->assign_date);
            $billingCycle = $cp->billing_cycle_months ?? 1;
            $currentDate = Carbon::now()->startOfMonth();
            
            // Calculate due months: assign_date + n*billing_cycle
            $n = 0;
            while (true) {
                $dueMonthDate = $assignDate->copy()->addMonths($n * $billingCycle);
                $dueMonth = $dueMonthDate->format('Y-m');
                
                // Stop if due month is in the future
                if ($dueMonthDate > $currentDate) {
                    break;
                }
                
                // Add due month
                if ($dueMonth !== $assignMonth) { // Don't add assigned month as due month
                    $dueMonths->push($dueMonth);
                }
                
                $n++;
            }
            
            // Also add months AFTER each due month (for carry forward)
            $n = 0;
            while (true) {
                $dueMonthDate = $assignDate->copy()->addMonths($n * $billingCycle);
                $dueMonth = $dueMonthDate->format('Y-m');
                
                // Stop if due month is in the future
                if ($dueMonthDate > $currentDate) {
                    break;
                }
                
                // Add months after due month (for carry forward payments)
                if ($dueMonth !== $assignMonth) {
                    $m = 1;
                    while (true) {
                        $carryMonthDate = $dueMonthDate->copy()->addMonths($m);
                        $carryMonth = $carryMonthDate->format('Y-m');
                        
                        // Stop if we reach next due month or future
                        if ($carryMonthDate->month == $dueMonthDate->copy()->addMonths($billingCycle)->month || 
                            $carryMonthDate > $currentDate) {
                            break;
                        }
                        
                        $dueMonths->push($carryMonth);
                        $m++;
                    }
                }
                
                $n++;
            }
        }
    }
    
    return $dueMonths->unique();
}

/**
 * Calculate month data based on new requirements
 */
private function calculateNewMonthData($month)
{
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    $currentMonth = Carbon::now()->format('Y-m');
    
    // Get customers who should appear in this month
    $customers = $this->getCustomersForMonth($month);
    
    // Calculate amounts for these customers
    $amounts = $this->calculateAmountsForCustomers($customers, $month);
    
    // Determine if this month has any activity
    $hasActivity = $this->monthHasActivity($month, $customers, $amounts);
    
    // Get status based on actual payment data
    $status = $this->calculateNewStatus($amounts['total_amount'], $amounts['received_amount'], $amounts['due_amount']);
    
    // Get notes
    $notes = $this->getMonthNotes($month, $customers, $amounts);
    
    return [
        'total_customers' => count($customers),
        'total_amount' => $amounts['total_amount'],
        'received_amount' => $amounts['received_amount'],
        'due_amount' => $amounts['due_amount'],
        'status' => $status,
        'notes' => $notes,
        'has_activity' => $hasActivity
    ];
}

/**
 * Get customers who should appear in a specific month
 */
private function getCustomersForMonth($month)
{
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    
    // For rolling invoice system, show customers in ALL months after their assignment
    // This is because the same invoice carries forward month-to-month
    return DB::table('customers as c')
        ->join('customer_to_products as cp', 'c.c_id', '=', 'cp.c_id')
        ->where('c.is_active', 1)
        ->where('cp.status', 'active')
        ->where('cp.is_active', 1)
        ->where('cp.assign_date', '<=', $monthDate->endOfMonth()) // Show if assigned before or in this month
        ->distinct('c.c_id')
        ->select('c.c_id', 'c.name', 'c.customer_id')
        ->get()
        ->toArray();
}

/**
 * Calculate amounts for customers in a specific month
 */
/**
 * Calculate amounts for customers in a specific month - ONLY CARRY FORWARD
 */
/**
 * Calculate amounts for customers in a specific month - CALCULATE HISTORICAL AMOUNTS
 * Shows what the amounts SHOULD BE for each specific month based on billing logic
 */
private function calculateAmountsForCustomers($customers, $month)
{
    $monthDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
    
    // Get all customer IDs
    $customerIds = array_column($customers, 'c_id');
    
    if (empty($customerIds)) {
        return [
            'total_amount' => 0,
            'received_amount' => 0,
            'due_amount' => 0
        ];
    }
    
    $totalAmount = 0;
    $receivedAmount = 0;
    $dueAmount = 0;
    
    // For each customer, get actual invoice data from database
    foreach ($customerIds as $customerId) {
        // Get customer product details
        $customerProduct = DB::table('customer_to_products as cp')
            ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
            ->where('cp.c_id', $customerId)
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->select('cp.assign_date', 'cp.billing_cycle_months', 'cp.cp_id')
            ->first();
        
        if ($customerProduct) {
            // Get the actual invoice data from the database (instead of calculating)
            $invoice = Invoice::where('cp_id', $customerProduct->cp_id)
                ->where('issue_date', '<=', $monthDate)
                ->orderBy('issue_date', 'desc')
                ->first();
            if ($invoice) {
                // Use actual database values instead of calculated ones
                $totalAmount += $invoice->total_amount ?? 0;
                $receivedAmount += $invoice->received_amount ?? 0;
                $dueAmount += $invoice->next_due ?? ($invoice->total_amount - ($invoice->received_amount ?? 0));
            } else {
                // Fallback to calculated amounts if no invoice exists
                // Calculate the subtotal from customer product details
                if ($customerProduct->is_custom_price && $customerProduct->custom_price > 0) {
                    $subtotalAmount = $customerProduct->custom_price;
                } else {
                    $subtotalAmount = $customerProduct->product->monthly_price * $customerProduct->billing_cycle_months;
                }
                
                // Calculate what the amounts should be for this specific month using the new method
                $monthlyAmounts = $this->calculateMonthlyAmountsWithUserLogic(
                    $customerProduct->assign_date,
                    $customerProduct->billing_cycle_months,
                    $subtotalAmount,
                    $month
                );
                
                // Get actual payments for this customer in this month
                $payments = $this->getPaymentsForCustomerMonth($customerId, $month);
                
                if ($monthlyAmounts['total_amount'] > 0) {
                    $totalAmount += $monthlyAmounts['total_amount'];
                    $receivedAmount += $payments;
                    $dueAmount += max(0, $monthlyAmounts['total_amount'] - $payments);
                }
            }
        }
    }
    
    return [
        'total_amount' => $totalAmount,
        'received_amount' => $receivedAmount,
        'due_amount' => max(0, $dueAmount)
    ];
}

/**
 * Calculate what the rolling invoice amounts should be for a specific month
 * Based on the proper rolling invoice logic with carry-forward
 */
private function calculateMonthlyAmounts($assignDate, $billingCycle, $subtotalAmount, $targetMonth) {
    $assignMonth = Carbon::parse($assignDate)->startOfMonth();
    $targetMonthDate = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        
    // Calculate months since assignment
    $monthsSinceAssign = $assignMonth->diffInMonths($targetMonthDate);
        
    if ($monthsSinceAssign < 0) {
        // Target month is before assignment
        return [
            'subtotal' => 0,
            'previous_due' => 0,
            'total_amount' => 0,
            'cycle_number' => 0,
            'cycle_position' => 0
        ];
    }
        
    // Determine if this is a billing cycle month
    $isBillingCycleMonth = ($monthsSinceAssign % $billingCycle) == 0;
    $cycleNumber = floor($monthsSinceAssign / $billingCycle) + 1;
    $cyclePosition = $monthsSinceAssign % $billingCycle;
        
    if ($monthsSinceAssign == 0) {
        // Initial month
        return [
            'subtotal' => $subtotalAmount,
            'previous_due' => 0,
            'total_amount' => $subtotalAmount,
            'cycle_number' => 1,
            'cycle_position' => 0
        ];
    } else if ($isBillingCycleMonth) {
        // New billing cycle month - ALWAYS add new subtotal + carry forward previous total
        // This ensures continuous billing for ongoing products
        $completedCycles = floor($monthsSinceAssign / $billingCycle);
        $previousDue = $completedCycles * $subtotalAmount;
        $totalAmount = $subtotalAmount + $previousDue;
            
        return [
            'subtotal' => $subtotalAmount,
            'previous_due' => $previousDue,
            'total_amount' => $totalAmount,
            'cycle_number' => $cycleNumber,
            'cycle_position' => 0
        ];
    } else {
        // Carry forward month - subtotal = 0, previous_due = total_amount
        $completedCycles = floor($monthsSinceAssign / $billingCycle);
        $totalAmount = ($completedCycles + 1) * $subtotalAmount;
            
        return [
            'subtotal' => 0,
            'previous_due' => $totalAmount,
            'total_amount' => $totalAmount,
            'cycle_number' => $cycleNumber,
            'cycle_position' => $cyclePosition
        ];
    }
}
/**
 * Calculate installment amount for a specific period
 */
private function calculateInstallmentAmount($assignDate, $billingCycle, $monthlyPrice, $monthDate)
{
    // Check if this is the assigned month
    $isAssignedMonth = ($assignDate->year == $monthDate->year && $assignDate->month == $monthDate->month);
    
    if ($isAssignedMonth) {
        // For monthly billing
        if ($billingCycle == 1) {
            return $monthlyPrice;
        }
        
        // For longer cycles, charge the full cycle amount in assigned month
        return $monthlyPrice * $billingCycle;
    }
    
    // For due months (billing cycle months), charge the full cycle amount
    // Calculate if this is a due month based on billing cycle
    $monthsDiff = $assignDate->diffInMonths($monthDate);
    if ($monthsDiff >= 0 && $monthsDiff % $billingCycle == 0) {
        // This is a due month, charge the full cycle amount
        return $monthlyPrice * $billingCycle;
    }
    
    // For all other months (carry forward months) - NO NEW INSTALLMENT
    return 0;
}

/**
 * Get carried forward amount from previous months
 */
private function getCarriedForwardAmount($cpId, $monthDate)
{
    // Get all unpaid/partial/confirmed invoices for this customer product
    // This includes ALL unpaid invoices, not just those from immediately previous months
    return Invoice::where('cp_id', $cpId)
        ->whereIn('status', ['unpaid', 'partial', 'confirmed'])
        ->where('next_due', '>', 0)
        ->sum('next_due');
}

/**
 * Check if customer should pay in specific month
 */
private function shouldPayInMonth($assignDate, $billingCycle, $monthDate)
{
    // Check if assigned in this month (advance payment month)
    if ($assignDate->year == $monthDate->year && $assignDate->month == $monthDate->month) {
        return true;
    }
    
    // Check if this is a due month based on billing cycle
    // Due months are: assign_date + n * billing_cycle_months
    $monthsDiff = $assignDate->diffInMonths($monthDate);
    if ($monthsDiff >= 0 && $monthsDiff % $billingCycle == 0) {
        return true;
    }
    
    // For carry forward display: Only return true if there are actual unpaid invoices
    // This is handled separately in getCustomersForMonth, so we don't need to return true here
    // for all months after assignment
    
    return false;
}

/**
 * Get payments for customer in specific month
 */
private function getPaymentsForCustomerMonth($customerId, $month)
{
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    
    return Payment::whereHas('invoice', function($query) use ($customerId, $monthDate) {
            $query->whereHas('customerProduct', function($subQuery) use ($customerId) {
                $subQuery->where('c_id', $customerId);
            })
                  ->whereYear('issue_date', $monthDate->year)
                  ->whereMonth('issue_date', $monthDate->month);
        })
        ->sum('amount');
}

/**
 * Get payments for customer product in specific month
 */
private function getPaymentsForProductMonth($cpId, $month)
{
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    
    return Payment::whereHas('invoice', function($query) use ($cpId, $monthDate) {
            $query->where('cp_id', $cpId)
                  ->whereYear('issue_date', $monthDate->year)
                  ->whereMonth('issue_date', $monthDate->month);
        })
        ->sum('amount');
}

/**
 * Check if month has any activity
 */
private function monthHasActivity($month, $customers, $amounts)
{
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    $currentMonth = Carbon::now()->format('Y-m');
    
    // Always show current month
    if ($month === $currentMonth) {
        return true;
    }
    
    // Show if there are customers
    if (count($customers) > 0) {
        return true;
    }
    
    // Show if there are amounts
    if ($amounts['total_amount'] > 0 || $amounts['received_amount'] > 0 || $amounts['due_amount'] > 0) {
        return true;
    }
    
    return false;
}

/**
 * Calculate status based on new logic
 */
private function calculateNewStatus($totalAmount, $receivedAmount, $dueAmount)
{
    if ($totalAmount == 0) {
        return 'No Activity';
    }
    
    if ($dueAmount <= 0) {
        return 'All Paid';
    }
    
    if ($receivedAmount > 0 && $dueAmount > 0) {
        return 'Partial';
    }
    
    return 'Unpaid';
}

/**
 * Get notes for the month
 */
private function getMonthNotes($month, $customers, $amounts)
{
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    $currentMonth = Carbon::now()->format('Y-m');
    
    $notes = [];
    
    if ($month === $currentMonth) {
        $notes[] = 'Current Month';
    }
    
    if (count($customers) > 0) {
        $notes[] = count($customers) . ' customer(s)';
    }
    
    // if ($amounts['total_amount'] > 0) {
    //     $notes[] = 'Billing: ৳' . number_format($amounts['total_amount'], 0);
    // }
    
    return implode(' | ', $notes);
}

    /**
     * Calculate data for a specific month
     * Shows: 1) Assign months, 2) Due months (billing cycle), 3) Unpaid carry-forward months
     */
    private function calculateMonthData($month)
    {
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        
        // Count customers who should show in this month:
        // 1. Customers assigned in this month (for advance payment)
        // 2. Customers due in this month (based on billing cycle)
        // 3. Customers with unpaid invoices from previous months (carry forward)
        $customersInThisMonth = DB::table('customers as c')
            ->join('customer_to_products as cp', 'c.c_id', '=', 'cp.c_id')
            ->leftJoin('invoices as i', function($join) {
                $join->on('cp.cp_id', '=', 'i.cp_id');
            })
            ->where('c.is_active', 1)
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where(function($query) use ($monthDate) {
                // Condition 1: Assigned in this month AND product assignment date is not after this month
                $query->where(function($q) use ($monthDate) {
                    $q->whereYear('cp.assign_date', $monthDate->year)
                      ->whereMonth('cp.assign_date', $monthDate->month)
                      ->where('cp.assign_date', '<=', $monthDate->endOfMonth());
                })
                // Condition 2: Due in this month (billing cycle) AND product assignment date is not after this month
                ->orWhere(function($q) use ($monthDate) {
                    $q->where('cp.assign_date', '<=', $monthDate->endOfMonth())
                      ->whereRaw('
                          PERIOD_DIFF(
                              DATE_FORMAT(?, "%Y%m"),
                              DATE_FORMAT(cp.assign_date, "%Y%m")
                          ) % cp.billing_cycle_months = 0
                      ', [$monthDate->format('Y-m-01')])
                      ->whereRaw('
                          PERIOD_DIFF(
                              DATE_FORMAT(?, "%Y%m"),
                              DATE_FORMAT(cp.assign_date, "%Y%m")
                          ) >= 0
                      ', [$monthDate->format('Y-m-01')]);
                })
                // Condition 3: Unpaid invoices from previous months (carry forward)
                ->orWhere(function($q) use ($monthDate) {
                    $q->where('i.issue_date', '<', $monthDate->startOfMonth())
                      ->whereIn('i.status', ['unpaid', 'partial'])
                      ->where('i.next_due', '>', 0)
                      ->where('cp.assign_date', '<=', $monthDate->endOfMonth())
                      // Only carry forward if this month is a billing month for the customer
                      ->whereRaw('
                          PERIOD_DIFF(
                              DATE_FORMAT(?, "%Y%m"),
                              DATE_FORMAT(cp.assign_date, "%Y%m")
                          ) % cp.billing_cycle_months = 0
                      ', [$monthDate->format('Y-m-01')])
                      ->whereRaw('
                          PERIOD_DIFF(
                              DATE_FORMAT(?, "%Y%m"),
                              DATE_FORMAT(cp.assign_date, "%Y%m")
                          ) >= 0
                      ', [$monthDate->format('Y-m-01')]);
                });
            })
            ->distinct('c.c_id')
            ->count('c.c_id');
        
        // Get invoices for this month:
        // 1. Invoices issued in this month (for customers assigned this month)
        // 2. Invoices for customers due this month (billing cycle)
        // 3. Unpaid invoices from previous months (carry forward)
        $invoiceSummary = Invoice::where(function($query) use ($monthDate) {
                // Condition 1: Invoices issued in this month
                $query->where(function($q) use ($monthDate) {
                    $q->whereYear('issue_date', $monthDate->year)
                      ->whereMonth('issue_date', $monthDate->month);
                })
                // Condition 2: Customers due in this month (billing cycle)
                ->orWhere(function($q) use ($monthDate) {
                    $q->whereHas('customerProduct', function($query) use ($monthDate) {
                        $query->where('status', 'active')
                              ->where('is_active', 1)
                              ->where('assign_date', '<=', $monthDate->endOfMonth())
                              ->whereRaw('
                                  PERIOD_DIFF(
                                      DATE_FORMAT(?, "%Y%m"),
                                      DATE_FORMAT(assign_date, "%Y%m")
                                  ) % billing_cycle_months = 0
                              ', [$monthDate->format('Y-m-01')])
                              ->whereRaw('
                                  PERIOD_DIFF(
                                      DATE_FORMAT(?, "%Y%m"),
                                      DATE_FORMAT(assign_date, "%Y%m")
                                  ) >= 0
                              ', [$monthDate->format('Y-m-01')]);
                    })
                    ->where('issue_date', '<=', $monthDate->endOfMonth());
                })
                // Condition 3: Unpaid invoices from previous months (carry forward)
                ->orWhere(function($q) use ($monthDate) {
                    $q->where('issue_date', '<', $monthDate->startOfMonth())
                      ->whereIn('status', ['unpaid', 'partial'])
                      ->where('next_due', '>', 0);
                });
            })
            ->selectRaw('
                SUM(total_amount) as total, 
                SUM(COALESCE(received_amount, 0)) as received,
                SUM(COALESCE(next_due, 0)) as due
            ')
            ->first();

        // Use actual invoice totals
        $totalAmount = floatval($invoiceSummary->total ?? 0);
        $receivedAmount = floatval($invoiceSummary->received ?? 0);
        $dueAmount = floatval($invoiceSummary->due ?? 0);
        
        // Calculate status
        $status = $this->calculateStatus($totalAmount, $receivedAmount, $dueAmount);
        
        return [
            'total_customers' => $customersInThisMonth,
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
     * Includes customers who are due for billing AND customers with carried forward dues
     */
    private function getDueCustomersForMonth(Carbon $monthDate)
    {
        // Get all active customer products assigned before or during the billing month
        $customerProducts = DB::table('customer_to_products as cp')
            ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
            ->join('products as p', 'cp.p_id', '=', 'p.p_id')
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where('c.is_active', 1)
            ->whereNotNull('cp.assign_date')
            // Ensure product was assigned before or during the billing month
            ->where('cp.assign_date', '<=', $monthDate->endOfMonth())
            ->select(
                'c.c_id',
                'c.name',
                'c.customer_id',
                'p.monthly_price',
                'cp.billing_cycle_months',
                'cp.assign_date',
                'cp.due_date',
                'cp.cp_id'
            )
            ->get();
        
        // Filter products that are due in this specific month based on billing cycle
        $dueProducts = $customerProducts->filter(function($cp) use ($monthDate) {
            $assignDate = Carbon::parse($cp->assign_date);
            $billingCycle = $cp->billing_cycle_months ?? 1;
            
            // Product must be assigned before or during the billing month
            if ($assignDate->greaterThan($monthDate->endOfMonth())) {
                return false;
            }
            
            // Calculate months difference from assign_date to billing month
            $monthsDiff = $assignDate->diffInMonths($monthDate);
            
            // Check if this month is a billing month for this product
            // Product is due if: months difference is divisible by billing cycle
            return ($monthsDiff % $billingCycle) === 0;
        });
        
        // Get customer IDs with carried forward dues (unpaid/partial invoices from previous months)
        $customersWithCarriedForwardDues = Invoice::whereIn('status', ['unpaid', 'partial', 'confirmed'])
            ->where('issue_date', '<', $monthDate->startOfMonth())
            ->where('next_due', '>', 0)
            ->pluck('cp_id')
            ->unique();
            
        // Get customer products for customers with carried forward dues
        $carriedForwardCustomerProducts = DB::table('customer_to_products as cp')
            ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
            ->join('products as p', 'cp.p_id', '=', 'p.p_id')
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where('c.is_active', 1)
            ->whereIn('cp.cp_id', $customersWithCarriedForwardDues)
            ->select(
                'c.c_id',
                'c.name',
                'c.customer_id',
                'p.monthly_price',
                'cp.billing_cycle_months',
                'cp.assign_date',
                'cp.due_date',
                'cp.cp_id'
            )
            ->get();
        
        // Merge both collections and remove duplicates
        $allDueCustomers = $dueProducts->merge($carriedForwardCustomerProducts)->unique('cp_id');
        
        return $allDueCustomers;
    }

    /**
     * Calculate current month statistics
     */
   /**
 * Calculate current month statistics
 */
private function calculateCurrentMonthStats()
{
    $currentMonth = date('Y-m');
    $monthData = $this->calculateNewMonthData($currentMonth);
    
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
            $skippedCount = 0;
            $errors = [];

            foreach ($dueCustomers as $customer) {
                try {
                    // Check if invoice already exists
                    $existingInvoice = Invoice::whereHas('customerProduct', function($query) use ($customer) {
                            $query->where('c_id', $customer->c_id);
                        })
                        ->whereYear('issue_date', $monthDate->year)
                        ->whereMonth('issue_date', $monthDate->month)
                        ->first();

                    if ($existingInvoice) {
                        $skippedCount++;
                        continue;
                    }

                    // Create new invoice
                    $this->createCustomerInvoice($customer, $monthDate);
                    $generatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Customer {$customer->name}: " . $e->getMessage();
                    Log::error("Invoice generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }

            $message = "Generated $generatedCount bills for " . $monthDate->format('F Y');
            if ($skippedCount > 0) {
                $message .= " ($skippedCount already existed)";
            }

            return redirect()->route('admin.billing.monthly-bills', ['month' => $month])
                ->with('success', $message);

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
        // Get customer product details
        $customerProduct = DB::table('customer_to_products')
            ->where('cp_id', $customer->cp_id)
            ->first();
            
        // Calculate months difference from assign_date
        $assignDate = Carbon::parse($customerProduct->assign_date);
        $monthsDiff = $assignDate->diffInMonths($monthDate);
        
        // Check if this is a billing month (months difference % billing_cycle == 0)
        $isBillingMonth = ($monthsDiff % $customerProduct->billing_cycle_months) == 0;
        
        // Calculate subtotal based on whether it's a billing month or carry-forward month
        if ($isBillingMonth) {
            // Billing month: subtotal = monthly_price * billing_cycle
            $subtotal = $customer->monthly_price * $customerProduct->billing_cycle_months;
        } else {
            // Carry-forward month: subtotal = 0
            $subtotal = 0;
        }
        
        // Get previous due amount from all unpaid/partial/confirmed invoices
        $previousDue = Invoice::where('cp_id', $customer->cp_id)
            ->whereIn('status', ['unpaid', 'partial', 'confirmed'])
            ->where('next_due', '>', 0)
            ->sum('next_due');
        
        // Calculate total amount
        $totalAmount = $subtotal + $previousDue;

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'cp_id' => $customer->cp_id,
            'issue_date' => $monthDate->format('Y-m-d'),
            'previous_due' => $previousDue,
            'service_charge' => 0.00,
            'vat_percentage' => 0.00,
            'vat_amount' => 0.00,
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'received_amount' => 0,
            'next_due' => $totalAmount,
            'status' => 'unpaid',
            'notes' => 'Auto-generated based on product assignment (includes carry-forward dues)',
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
                'created_by' => Auth::id()
            ]);

            return redirect()->route('admin.billing.billing-invoices')
                ->with('success', 'Monthly billing summary created successfully.');

        } catch (\Exception $e) {
            Log::error('Store monthly billing error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating billing summary: ' . $e->getMessage());
        }
    }

    /**
     * Generate from invoices and products
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
                'notes' => 'Generated from customer products and invoices',
                'is_locked' => false,
                'created_by' => Auth::id()
            ]);

            return redirect()->route('admin.billing.billing-invoices')
                ->with('success', 'Monthly billing summary generated successfully from products and invoices.');

        } catch (\Exception $e) {
            Log::error('Generate from invoices error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating billing summary: ' . $e->getMessage());
        }
    }
    
    /**
     * Display monthly bills for a specific month
     */
    public function monthlyBills(Request $request, $month)
    {
        try {
            // Validate month format
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                return redirect()->route('admin.billing.billing-invoices')
                    ->with('error', 'Invalid month format.');
            }

            $monthDate = Carbon::createFromFormat('Y-m', $month);
            
            // Get all invoices for this month with relationships
            // Include:
            // 1. Invoices issued in this month
            // 2. Unpaid invoices from previous months (carry forward)
            $invoices = Invoice::with(['customer.customer.customerproducts.product', 'payments'])
                ->where(function($query) use ($monthDate) {
                    // Condition 1: Invoices issued in this month
                    $query->where(function($q) use ($monthDate) {
                        $q->whereYear('issue_date', $monthDate->year)
                          ->whereMonth('issue_date', $monthDate->month);
                    })
                    // Condition 2: Unpaid invoices from previous months (carry forward)
                    ->orWhere(function($q) use ($monthDate) {
                        $q->where('issue_date', '<', $monthDate->startOfMonth())
                          ->whereIn('status', ['unpaid', 'partial'])
                          ->where('next_due', '>', 0);
                    });
                })
                ->orderBy('issue_date', 'desc')
                ->get();

            // Calculate statistics
            $totalCustomers = $invoices->unique('c_id')->count();
            $totalBillingAmount = $invoices->sum('total_amount');
            $pendingAmount = $invoices->whereIn('status', ['unpaid', 'partial'])->sum('next_due');
            $paidAmount = $invoices->sum('received_amount');

            // System settings no longer needed (no service charge or VAT)
            $systemSettings = [];

            return view('admin.billing.monthly-bills', compact(
                'month',
                'invoices',
                'totalCustomers',
                'totalBillingAmount',
                'pendingAmount',
                'paidAmount',
                'systemSettings'
            ));

        } catch (\Exception $e) {
            Log::error('Monthly bills error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('admin.billing.billing-invoices')
                ->with('error', 'Error loading monthly bills: ' . $e->getMessage());
        }
    }

    /**
     * Generate monthly bills for all customers
     */
    public function generateMonthlyBills(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m'
        ]);

        try {
            $month = $request->month;
            $monthDate = Carbon::createFromFormat('Y-m', $month);

            // Get due customers for the month
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);

            if ($dueCustomers->isEmpty()) {
                return redirect()->back()->with('error', 'No customers due for billing in ' . $monthDate->format('F Y'));
            }

            $generatedCount = 0;
            $skippedCount = 0;

            foreach ($dueCustomers as $customer) {
                try {
                    // Check if invoice already exists
                    $existingInvoice = Invoice::whereHas('customerProduct', function($query) use ($customer) {
                            $query->where('c_id', $customer->c_id);
                        })
                        ->whereYear('issue_date', $monthDate->year)
                        ->whereMonth('issue_date', $monthDate->month)
                        ->first();

                    if ($existingInvoice) {
                        $skippedCount++;
                        continue;
                    }

                    // Create invoice
                    $this->createCustomerInvoice($customer, $monthDate);
                    $generatedCount++;

                } catch (\Exception $e) {
                    Log::error("Failed to generate invoice for customer {$customer->c_id}: " . $e->getMessage());
                }
            }

            $message = "Generated $generatedCount bills for " . $monthDate->format('F Y');
            if ($skippedCount > 0) {
                $message .= " ($skippedCount already existed)";
            }

            return redirect()->route('admin.billing.monthly-bills', ['month' => $month])
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Generate monthly bills error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating bills: ' . $e->getMessage());
        }
    }

    /**
     * Get invoice data for AJAX request
     */
    public function getInvoiceData($invoiceId)
    {
        try {
            $invoice = Invoice::with(['customer', 'payments'])
                ->findOrFail($invoiceId);

            return response()->json([
                'success' => true,
                'invoice' => [
                    'invoice_id' => $invoice->invoice_id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'received_amount' => $invoice->received_amount ?? 0,
                    'next_due' => $invoice->next_due ?? ($invoice->total_amount - ($invoice->received_amount ?? 0)),
                    'status' => $invoice->status,
                    'customer' => [
                        'name' => $invoice->customer->name ?? 'Unknown',
                        'email' => $invoice->customer->email ?? 'N/A',
                        'phone' => $invoice->customer->phone ?? 'N/A'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get invoice data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Get payments for an invoice
     */
    public function getInvoicePayments($invoiceId)
    {
        try {
            $invoice = Invoice::with(['customerProduct.customer', 'payments'])
                ->findOrFail($invoiceId);

            $customer = $invoice->customerProduct ? $invoice->customerProduct->customer : null;
            
            // Get current month from invoice issue date
            $invoiceMonth = \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m');
            
            // Filter payments by invoice month only - compare payment_date with invoice issue_date month
            $filteredPayments = $invoice->payments->filter(function($payment) use ($invoice, $invoiceMonth) {
                $paymentMonth = \Carbon\Carbon::parse($payment->payment_date)->format('Y-m');
                return $paymentMonth === $invoiceMonth;
            });

            return response()->json([
                'success' => true,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $customer ? $customer->name : 'Unknown',
                'total_amount' => $invoice->total_amount,
                'received_amount' => $invoice->received_amount ?? 0,
                'next_due' => $invoice->next_due ?? 0,
                'payments' => $filteredPayments->map(function($payment) {
                    return [
                        'payment_id' => $payment->payment_id,
                        'amount' => $payment->amount,
                        'payment_method' => $payment->payment_method,
                        'payment_date' => $payment->payment_date,
                        'note' => $payment->note
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Get invoice payments error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a payment
     */
    public function deletePayment($paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            $invoice = Invoice::findOrFail($payment->invoice_id);

            // Store payment amount before deleting
            $paymentAmount = $payment->amount;

            // Delete the payment
            $payment->delete();

            // Recalculate invoice amounts
            $newReceivedAmount = $invoice->received_amount - $paymentAmount;
            $newDue = $invoice->total_amount - $newReceivedAmount;

            // Update status
            if ($newReceivedAmount <= 0) {
                $status = 'unpaid';
            } elseif ($newDue <= 0) {
                $status = 'paid';
            } else {
                $status = 'partial';
            }

            $invoice->update([
                'received_amount' => max(0, $newReceivedAmount),
                'next_due' => max(0, $newDue),
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully. Invoice amounts have been recalculated.'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Show the form for editing a payment
     */
    public function editPayment($paymentId)
    {
        try {
            $payment = Payment::with(['invoice.customerProduct.customer', 'invoice.customerProduct.product'])
                ->findOrFail($paymentId);
            
            return view('admin.billing.edit-bill', compact('payment'));
            
        } catch (\Exception $e) {
            Log::error('Edit payment error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load payment for editing: ' . $e->getMessage());
        }
    }
    
    /**
     * Update a payment
     */
    public function updatePayment(Request $request, $paymentId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking,card,online',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        try {
            DB::beginTransaction();
            
            $payment = Payment::findOrFail($paymentId);
            $invoice = Invoice::findOrFail($payment->invoice_id);
            
            // Store original amount for invoice recalculation
            $originalAmount = $payment->amount;
            
            // Update payment
            $payment->update([
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);
            
            // Recalculate invoice amounts
            $amountDifference = $request->amount - $originalAmount;
            $newReceivedAmount = $invoice->received_amount + $amountDifference;
            $newDue = max(0, $invoice->total_amount - $newReceivedAmount);
            
            // Update status
            if ($newReceivedAmount <= 0) {
                $status = 'unpaid';
            } elseif ($newDue <= 0) {
                $status = 'paid';
            } else {
                $status = 'partial';
            }
            
            $invoice->update([
                'received_amount' => max(0, $newReceivedAmount),
                'next_due' => max(0, $newDue),
                'status' => $status
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('admin.billing.monthly-bills', ['month' => \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m')])
                ->with('success', 'Payment updated successfully. Invoice amounts have been recalculated.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update payment error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update payment: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Update an invoice
     */
    public function updateInvoice(Request $request, $invoiceId)
    {
        $request->validate([
            'subtotal' => 'required|numeric|min:0',
            'previous_due' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'received_amount' => 'required|numeric|min:0',
            'next_due' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        try {
            $invoice = Invoice::findOrFail($invoiceId);
            
            // Update invoice
            $invoice->update([
                'subtotal' => $request->subtotal,
                'previous_due' => $request->previous_due,
                'total_amount' => $request->total_amount,
                'received_amount' => $request->received_amount,
                'next_due' => $request->next_due,
                'notes' => $request->notes,
            ]);
            
            return redirect()
                ->route('admin.billing.monthly-bills', ['month' => \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m')])
                ->with('success', 'Invoice updated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Update invoice error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update invoice: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Calculate the correct billing amounts for a specific month
     * Handles billing cycles longer than one month (e.g., 2, 3, 6, or 12 months)
     *
     * @param string $assignDate The date when the product was assigned
     * @param int $billingCycle The billing cycle in months
     * @param float $subtotal The subtotal amount for the product
     * @param string $targetMonth The target month in Y-m format
     * @return array The calculated amounts with subtotal, previous_due, and total_amount
     */
    private function calculateMonthlyAmountsWithUserLogic($assignDate, $billingCycle, $subtotal, $targetMonth)
    {
        $assignMonth = Carbon::parse($assignDate)->startOfMonth();
        $targetMonthDate = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        
        // Calculate months since assignment
        $monthsSinceAssign = $assignMonth->diffInMonths($targetMonthDate);
        
        // If target month is before assignment, no charges apply
        if ($monthsSinceAssign < 0) {
            return [
                'subtotal' => 0,
                'previous_due' => 0,
                'total_amount' => 0,
                'cycle_number' => 0,
                'cycle_position' => 0
            ];
        }
        
        // Determine cycle information
        $cycleNumber = floor($monthsSinceAssign / $billingCycle) + 1;
        $cyclePosition = $monthsSinceAssign % $billingCycle;
        
        // If it's the first month of the billing cycle (cyclePosition == 0)
        if ($cyclePosition == 0) {
            // This is a new billing cycle, add the new subtotal
            // Calculate previous due as the sum of all previous completed cycles
            $completedCycles = $cycleNumber - 1;
            $previousDue = $completedCycles * $subtotal;
            
            return [
                'subtotal' => $subtotal,
                'previous_due' => $previousDue,
                'total_amount' => $subtotal + $previousDue,
                'cycle_number' => $cycleNumber,
                'cycle_position' => $cyclePosition
            ];
        } else {
            // This is a carry-forward month within the billing cycle
            // Subtotal should be 0, and previous_due should be the total_amount from the previous month
            // Calculate total amount as the sum of all previous completed cycles plus current cycle
            $totalAmount = $cycleNumber * $subtotal;
            
            return [
                'subtotal' => 0,
                'previous_due' => $totalAmount,
                'total_amount' => $totalAmount,
                'cycle_number' => $cycleNumber,
                'cycle_position' => $cyclePosition
            ];
        }
    }

    /**
     * Confirm user payment and carry forward remaining due
     * This marks the customer's billing for this month as confirmed
     * and carries forward any remaining due to the next billing cycle
     */
    public function confirmUserPayment(Request $request)
    {
        try {
            $request->validate([
                'invoice_id' => 'required|exists:invoices,invoice_id',
                'cp_id' => 'required|exists:customer_to_products,cp_id',
                'next_due' => 'required|numeric|min:0'
            ]);

            $invoice = Invoice::findOrFail($request->invoice_id);
            $customerProduct = Customerproduct::findOrFail($request->cp_id);

            // Mark invoice as confirmed (status = 'confirmed')
            // The next_due will remain and be carried forward to next month automatically
            $invoice->update([
                'status' => 'confirmed',
                'notes' => ($invoice->notes ?? '') . "\n[" . now()->format('Y-m-d H:i:s') . "] Month confirmed by " . Auth::user()->name . ". Remaining due: ৳" . number_format($request->next_due, 2) . " will be carried forward."
            ]);

            Log::info("User payment confirmed for invoice {$invoice->invoice_id}, CP {$customerProduct->cp_id}. Due carried forward: {$request->next_due}");

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully. Remaining due will be carried forward to next billing cycle.'
            ]);

        } catch (\Exception $e) {
            Log::error('Confirm user payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Carry forward remaining due amount to the next billing month
     * 
     * @param Invoice $invoice The current invoice
     * @param float $remainingDue The remaining due amount to carry forward
     * @return Invoice|null The newly created or updated next month invoice
     */
    private function carryForwardDueToNextMonth($invoice, $remainingDue)
    {
        // Only proceed if there's a remaining due amount
        if ($remainingDue <= 0) {
            return null;
        }
        
        // Get customer product details
        $customerProduct = $invoice->customerProduct;
        if (!$customerProduct) {
            Log::warning('Cannot carry forward due: No customer product found', [
                'invoice_id' => $invoice->invoice_id
            ]);
            return null;
        }
        
        // Get current month and calculate next billing month
        $currentMonth = Carbon::parse($invoice->issue_date);
        $nextMonth = $currentMonth->copy()->addMonth();
        
        // Check if there's already an invoice for the next month
        $nextMonthInvoice = Invoice::where('cp_id', $customerProduct->cp_id)
            ->whereYear('issue_date', $nextMonth->year)
            ->whereMonth('issue_date', $nextMonth->month)
            ->first();
            
        if ($nextMonthInvoice) {
            // Update existing next month invoice with carried forward amount
            // For carry-forward months, we set previous_due to the carried amount, not add to existing
            $newPreviousDue = $remainingDue;
            $newTotalAmount = $nextMonthInvoice->subtotal + $newPreviousDue;
            $newNextDue = max(0, $newTotalAmount - $nextMonthInvoice->received_amount);
            
            // Update status based on new amounts
            $newStatus = 'unpaid';
            if ($nextMonthInvoice->received_amount >= $newTotalAmount) {
                $newStatus = 'paid';
            } elseif ($nextMonthInvoice->received_amount > 0) {
                $newStatus = 'partial';
            }
            
            $nextMonthInvoice->update([
                'previous_due' => $newPreviousDue,
                'total_amount' => $newTotalAmount,
                'next_due' => $newNextDue,
                'status' => $newStatus,
                'notes' => ($nextMonthInvoice->notes ?? '') . "\n[" . now()->format('Y-m-d H:i:s') . "] Carried forward ৳" . number_format($remainingDue, 2) . " from previous invoice #" . $invoice->invoice_number
            ]);
            
            Log::info('Updated existing next month invoice with carried forward amount', [
                'original_invoice_id' => $invoice->invoice_id,
                'next_invoice_id' => $nextMonthInvoice->invoice_id,
                'amount_carried_forward' => $remainingDue
            ]);
            
            return $nextMonthInvoice;
        } else {
            // Create a new invoice for the next month with the carried forward amount
            $nextMonthInvoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'cp_id' => $customerProduct->cp_id,
                'issue_date' => $nextMonth->format('Y-m-d'),
                'previous_due' => $remainingDue,
                'service_charge' => 0.00,
                'vat_percentage' => 0.00,
                'vat_amount' => 0.00,
                'subtotal' => 0.00, // No new charges for carry-forward month
                'total_amount' => $remainingDue,
                'received_amount' => 0.00,
                'next_due' => $remainingDue,
                'status' => 'unpaid',
                'notes' => 'Auto-generated for carry-forward of ৳' . number_format($remainingDue, 2) . " from previous invoice #" . $invoice->invoice_number . "\n[" . now()->format('Y-m-d H:i:s') . "] Carried forward from previous invoice",
                'created_by' => Auth::id() ?? 1
            ]);
            
            Log::info('Created new invoice for next month with carried forward amount', [
                'original_invoice_id' => $invoice->invoice_id,
                'new_invoice_id' => $nextMonthInvoice->invoice_id,
                'amount_carried_forward' => $remainingDue
            ]);
            
            return $nextMonthInvoice;
        }
    }
}