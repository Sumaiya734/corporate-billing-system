<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\CustomerProduct;
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
            
            $regularproducts = Product::whereHas('type', function($query) {
                $query->where('name', 'regular');
            })->get();
            
            $specialproducts = Product::whereHas('type', function($query) {
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
            $this->attachproductsToInvoice($invoice, $request->regular_products, $request->specialproducts);

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
                    
                    // Calculate actual revenue based on billing cycle and assignment date
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
        return Product::whereIn('p_id', $productIds)->sum('monthly_price');
    }

    /**
     * Attach products to invoice
     */
    private function attachproductsToInvoice($invoice, $regularproducts, $specialproducts)
    {
        $allproducts = array_merge($regularproducts, $specialproducts);
        
        foreach ($allproducts as $productId) {
            $product = Product::find($productId);
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
        $customerproduct = CustomerProduct::where('c_id', $customerId)
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
            // Load invoice with customerProduct relationship
            $invoice = Invoice::with(['customerProduct.customer', 'customerProduct.product', 'payments'])
                            ->find($invoiceId);

            // Check if invoice exists
            if (!$invoice) {
                Log::error("Invoice not found: {$invoiceId}");
                return response('<div class="alert alert-danger">Invoice not found.</div>', 404);
            }
            
            // Calculate billing cycle info
            $billingCycle = $invoice->customerProduct->billing_cycle_months ?? 1;
            $assignDate = Carbon::parse($invoice->customerProduct->assign_date ?? now());
            $issueDate = Carbon::parse($invoice->issue_date ?? now());
            $monthsDiff = $assignDate->diffInMonths($issueDate);
            $isBillingCycleMonth = ($monthsDiff % $billingCycle == 0);

            return view('admin.billing.invoice-html', compact('invoice', 'isBillingCycleMonth'));

        } catch (\Exception $e) {
            Log::error('Get invoice HTML error: ' . $e->getMessage());
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
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            $invoice = Invoice::with('customerProduct')->findOrFail($invoiceId);
            
            // Check if payment date is in future
            $paymentDate = Carbon::parse($request->payment_date);
            if ($paymentDate->greaterThan(Carbon::now()->addDay())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment date cannot be in the future.'
                ], 400);
            }

            $payment = Payment::create([
                'invoice_id' => $invoiceId,
                'c_id' => $invoice->customerProduct->c_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'note' => $request->note
            ]);

            // Update invoice
            $newReceivedAmount = $invoice->received_amount + $request->amount;
            $newDue = max(0, $invoice->total_amount - $newReceivedAmount);

            // Handle floating point precision
            if ($newDue < 0.01) {
                $newDue = 0;
                $status = 'paid';
            } elseif ($newReceivedAmount > 0) {
                $status = 'partial';
            } else {
                $status = 'unpaid';
            }

            // Update invoice
            $invoice->update([
                'received_amount' => $newReceivedAmount,
                'next_due' => $newDue,
                'status' => $status
            ]);
            
            // Refresh invoice
            $invoice->refresh();

            DB::commit();
            
            // Log successful completion
            Log::info('Payment Recorded Successfully', [
                'invoice_id' => $invoice->invoice_id,
                'payment_id' => $payment->payment_id ?? null
            ]);
            
            // If the request is AJAX, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully!',
                    'invoice_id' => $invoice->invoice_id,
                    'next_due' => $invoice->next_due,
                    'received_amount' => $invoice->received_amount,
                    'status' => $invoice->status,
                    'month' => $invoice->issue_date
                ]);
            }
            
            return redirect()->back()->with('success', 'Payment recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Error Details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // If AJAX request, return JSON
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
                    'due_date' => $invoice->issue_date->format('Y-m-d')
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
                
            // Total pending amount across all invoices
            $totalPendingAmount = Invoice::selectRaw('SUM(GREATEST(total_amount - COALESCE(received_amount, 0), 0)) as pending')
                ->value('pending') ?? 0;
            
            // Calculate this month bills count
            $thisMonthBillsCount = $this->calculateThisMonthBillsCount();
            
            // Additional statistics
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

            // Get overdue invoices - paginated
            $overdueInvoices = Invoice::with('customer')
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereRaw('total_amount > COALESCE(received_amount, 0)')
                ->orderBy('issue_date', 'asc')
                ->paginate(20);

            // Check if we have invoices
            $hasInvoices = Invoice::exists();

            // Calculate collection rate
            $collectionRate = $totalInvoiceAmount > 0 ? ($totalReceivedAmount / $totalInvoiceAmount) * 100 : 0;

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
                'totalReceivedAmount' => $totalReceivedAmount,
                'collectionRate' => $collectionRate
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
        
        // Get all unique months from customer product assignments
        $assignmentMonths = CustomerProduct::where('status', 'active')
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
        
        // Combine all months
        $allMonths = $assignmentMonths->merge($dueMonths)
            ->merge($allMonthsList)
            ->push($currentMonth)
            ->unique()
            ->sort()
            ->filter(function($month) use ($currentMonth) {
                return $month <= $currentMonth;
            });
        
        foreach ($allMonths as $month) {
            $monthData = $this->calculateMonthData($month);
            // Check if the month is closed
            $isClosed = class_exists('\App\Models\BillingPeriod') && method_exists('\App\Models\BillingPeriod', 'isMonthClosed') ? \App\Models\BillingPeriod::isMonthClosed($month) : false;
            
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
                'is_closed' => $isClosed,
                'is_dynamic' => true,
                'status' => $isClosed ? 'Closed' : $monthData['status'],
                'notes' => $monthData['notes'],
                'has_activity' => $monthData['has_activity'],
                'is_billing_cycle_month' => $monthData['is_billing_cycle_month'] ?? false,
                'billing_cycle' => $monthData['billing_cycle'] ?? 1
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
            $customerProducts = CustomerProduct::where('status', 'active')
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
                    if ($dueMonth !== $assignMonth) {
                        $dueMonths->push($dueMonth);
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
    private function calculateMonthData($month)
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
        $status = $this->calculateStatus($amounts['total_amount'], $amounts['received_amount'], $amounts['due_amount']);
        
        // Get notes
        $notes = $this->getMonthNotes($month, $customers, $amounts);
        
        // Check if this is a billing cycle month for most customers
        $isBillingCycleMonth = $this->isBillingCycleMonth($month);
        
        return [
            'total_customers' => count($customers),
            'total_amount' => $amounts['total_amount'],
            'received_amount' => $amounts['received_amount'],
            'due_amount' => $amounts['due_amount'],
            'status' => $status,
            'notes' => $notes,
            'has_activity' => $hasActivity,
            'is_billing_cycle_month' => $isBillingCycleMonth,
            'billing_cycle' => $this->getAverageBillingCycle($month)
        ];
    }

    /**
     * Get customers who should appear in a specific month
     */
    private function getCustomersForMonth($month)
    {
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        
        return DB::table('customers as c')
            ->join('customer_to_products as cp', 'c.c_id', '=', 'cp.c_id')
            ->where('c.is_active', 1)
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where('cp.assign_date', '<=', $monthDate->endOfMonth())
            ->distinct('c.c_id')
            ->select('c.c_id', 'c.name', 'c.customer_id')
            ->get()
            ->toArray();
    }

    /**
     * Calculate amounts for customers in a specific month
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
        
        // For each customer, get actual invoice data for this month
        foreach ($customerIds as $customerId) {
            // Get customer product details
            $customerProducts = DB::table('customer_to_products as cp')
                ->join('products as p', 'cp.p_id', '=', 'p.p_id')
                ->where('cp.c_id', $customerId)
                ->where('cp.status', 'active')
                ->where('cp.is_active', 1)
                ->select('cp.cp_id')
                ->get();
            
            foreach ($customerProducts as $customerProduct) {
                // Get invoice for this specific month
                $invoice = Invoice::where('cp_id', $customerProduct->cp_id)
                    ->whereYear('issue_date', $monthDate->year)
                    ->whereMonth('issue_date', $monthDate->month)
                    ->first();
                
                if ($invoice) {
                    // Use actual invoice values
                    $totalAmount += $invoice->total_amount ?? 0;
                    $receivedAmount += $invoice->received_amount ?? 0;
                    $dueAmount += $invoice->next_due ?? max(0, $invoice->total_amount - ($invoice->received_amount ?? 0));
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
    private function calculateStatus($totalAmount, $receivedAmount, $dueAmount)
    {
        if ($totalAmount == 0) {
            return 'No Activity';
        }
        
        if ($dueAmount <= 0) {
            return 'Paid';
        }
        
        if ($receivedAmount > 0 && $dueAmount > 0) {
            return 'Partial';
        }
        
        return 'Unpaid';
    }

    /**
     * Check if this is a billing cycle month
     */
    private function isBillingCycleMonth($month)
    {
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        
        // Get all active customer products
        $customerProducts = CustomerProduct::where('status', 'active')
            ->where('is_active', 1)
            ->where('assign_date', '<=', $monthDate->endOfMonth())
            ->get();
        
        $billingCycleCount = 0;
        $totalCustomers = $customerProducts->count();
        
        if ($totalCustomers == 0) {
            return false;
        }
        
        foreach ($customerProducts as $cp) {
            $assignDate = Carbon::parse($cp->assign_date);
            $monthsDiff = $assignDate->diffInMonths($monthDate);
            $billingCycle = $cp->billing_cycle_months ?? 1;
            
            if ($monthsDiff % $billingCycle == 0) {
                $billingCycleCount++;
            }
        }
        
        // If more than 50% customers are in billing cycle, mark as billing cycle month
        return ($billingCycleCount / $totalCustomers) > 0.5;
    }

    /**
     * Get average billing cycle for the month
     */
    private function getAverageBillingCycle($month)
    {
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        
        $avgBillingCycle = CustomerProduct::where('status', 'active')
            ->where('is_active', 1)
            ->where('assign_date', '<=', $monthDate->endOfMonth())
            ->avg('billing_cycle_months');
        
        return round($avgBillingCycle ?? 1);
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
        
        // Add billing cycle info
        if ($this->isBillingCycleMonth($month)) {
            $avgCycle = $this->getAverageBillingCycle($month);
            if ($avgCycle > 1) {
                $notes[] = $avgCycle . '-month billing cycle';
            }
        }
        
        return implode(' | ', $notes);
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
            $skippedCount = 0;
            $errors = [];

            foreach ($dueCustomers as $customer) {
                try {
                    // Generate monthly invoice
                    $invoice = $this->generateMonthlyInvoice($customer, $monthDate);
                    
                    if ($invoice) {
                        if ($invoice->wasRecentlyCreated) {
                            $generatedCount++;
                        } else {
                            $skippedCount++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Customer {$customer->name}: " . $e->getMessage();
                    Log::error("Invoice generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }

            $message = "Generated $generatedCount bills for " . $displayMonth;
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
     * Generate MONTHLY invoice (not rolling) with previous due
     */
    private function generateMonthlyInvoice($customer, Carbon $monthDate)
    {
        try {
            // Get customer product details
            $customerProduct = DB::table('customer_to_products as cp')
                ->join('products as p', 'cp.p_id', '=', 'p.p_id')
                ->where('cp.cp_id', $customer->cp_id)
                ->where('cp.status', 'active')
                ->where('cp.is_active', 1)
                ->select('cp.*', 'p.monthly_price', 'p.name as product_name')
                ->first();
            
            if (!$customerProduct) {
                Log::warning("No active customer product found for cp_id: {$customer->cp_id}");
                return null;
            }
            
            // Check if invoice already exists for this month
            $existingInvoice = Invoice::where('cp_id', $customer->cp_id)
                ->whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->first();
                
            if ($existingInvoice) {
                Log::info("Invoice already exists for cp_id: {$customer->cp_id}, month: {$monthDate->format('Y-m')}");
                return $existingInvoice;
            }
            
            // Calculate this month's charge based on billing cycle
            $subtotal = $this->calculateMonthlyCharge($customerProduct, $monthDate);
            
            // Get ONLY LAST MONTH'S remaining due, not all previous dues
            $previousDue = $this->getLastMonthDue($customer->cp_id, $monthDate);
            
            // Calculate total amount
            $totalAmount = $subtotal + $previousDue;
            
            // Generate invoice number
            $invoiceNumber = $this->generateMonthlyInvoiceNumber($monthDate);
            
            // Create new invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
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
                'notes' => $this->generateInvoiceNotes($subtotal, $previousDue, $customerProduct, $monthDate),
                'created_by' => Auth::id()
            ]);
            
            Log::info("Monthly invoice created", [
                'invoice_id' => $invoice->invoice_id,
                'invoice_number' => $invoiceNumber,
                'cp_id' => $customer->cp_id,
                'month' => $monthDate->format('Y-m'),
                'subtotal' => $subtotal,
                'previous_due' => $previousDue,
                'total_amount' => $totalAmount
            ]);
            
            return $invoice;
            
        } catch (\Exception $e) {
            Log::error("Error generating monthly invoice", [
                'cp_id' => $customer->cp_id ?? 'N/A',
                'month' => $monthDate->format('Y-m'),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Calculate monthly charge based on billing cycle
     * Returns 0 for non-billing cycle months
     */
    private function calculateMonthlyCharge($customerProduct, Carbon $monthDate)
    {
        $assignDate = Carbon::parse($customerProduct->assign_date);
        $currentMonth = $monthDate->format('Y-m');
        
        // Calculate months difference from assignment
        $monthsDiff = $assignDate->diffInMonths($monthDate);
        $billingCycle = $customerProduct->billing_cycle_months ?? 1;
        
        // Check if this is a billing cycle month
        $isBillingCycleMonth = ($monthsDiff % $billingCycle == 0);
        
        if (!$isBillingCycleMonth) {
            // Non-billing cycle month: no new charges
            return 0;
        }
        
        // Billing cycle month: calculate charge
        if ($customerProduct->is_custom_price && $customerProduct->custom_price > 0) {
            // Custom price set
            return $customerProduct->custom_price;
        } else {
            // Standard monthly price
            return $customerProduct->monthly_price * $billingCycle;
        }
    }
    
    /**
     * Get LAST MONTH'S due only (FIXED VERSION)
     */
    private function getLastMonthDue($cpId, Carbon $currentMonth)
    {
        $previousMonth = $currentMonth->copy()->subMonth();
        
        $lastMonthInvoice = Invoice::where('cp_id', $cpId)
            ->whereYear('issue_date', $previousMonth->year)
            ->whereMonth('issue_date', $previousMonth->month)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('next_due', '>', 0)
            ->first();
        
        if ($lastMonthInvoice) {
            return $lastMonthInvoice->next_due;
        }
        
        return 0;
    }
    
    /**
     * Generate monthly invoice number with year-month
     */
    private function generateMonthlyInvoiceNumber(Carbon $monthDate)
    {
        $yearMonth = $monthDate->format('Ym');
        
        // Count how many invoices already exist for this month
        $count = Invoice::whereYear('issue_date', $monthDate->year)
            ->whereMonth('issue_date', $monthDate->month)
            ->count();
            
        $sequentialNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return "INV-{$yearMonth}-{$sequentialNumber}";
    }
    
    /**
     * Generate invoice notes
     */
    private function generateInvoiceNotes($subtotal, $previousDue, $customerProduct, Carbon $monthDate)
    {
        $notes = [];
        
        // Product info
        $notes[] = "Product: {$customerProduct->product_name}";
        
        // Billing cycle info
        $billingCycle = $customerProduct->billing_cycle_months ?? 1;
        if ($billingCycle > 1) {
            $notes[] = "Billing Cycle: {$billingCycle} months";
            
            // Check if this is billing cycle month
            $assignDate = Carbon::parse($customerProduct->assign_date);
            $monthsDiff = $assignDate->diffInMonths($monthDate);
            $isBillingCycleMonth = ($monthsDiff % $billingCycle == 0);
            
            if ($isBillingCycleMonth) {
                $notes[] = "Billing Cycle Month - Full Charge Applied";
            } else {
                $notes[] = "Non-Billing Cycle Month - No New Charge";
            }
        }
        
        // Amount details
        if ($subtotal > 0) {
            $notes[] = "New Charges: ৳" . number_format($subtotal, 2);
        } else {
            $notes[] = "No New Charges This Month";
        }
        
        if ($previousDue > 0) {
            $previousMonth = $monthDate->copy()->subMonth()->format('F Y');
            $notes[] = "Previous Due from {$previousMonth}: ৳" . number_format($previousDue, 2);
        }
        
        return implode(' | ', $notes);
    }

    /**
     * Create invoice for a customer
     */
    private function createCustomerInvoice($customer, Carbon $monthDate)
    {
        return $this->generateMonthlyInvoice($customer, $monthDate);
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
            
            // Get invoices for THIS MONTH ONLY
            $invoices = Invoice::with(['customerProduct.customer', 'customerProduct.product', 'payments'])
                ->whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->orderBy('issue_date', 'desc')
                ->orderBy('invoice_number', 'desc')
                ->get();
            
            // Calculate statistics for THIS MONTH ONLY
            $totalCustomers = $invoices->unique('cp_id')->count();
            $totalBillingAmount = $invoices->sum('total_amount');
            $pendingAmount = $invoices->whereIn('status', ['unpaid', 'partial'])
                ->sum('next_due');
            $paidAmount = $invoices->sum('received_amount');
            
            // Get due customers who don't have invoice for this month
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);
            $customerIdsWithInvoices = $invoices->pluck('cp_id')->toArray();
            $customersWithoutInvoice = $dueCustomers->whereNotIn('cp_id', $customerIdsWithInvoices);

            return view('admin.billing.monthly-bills', compact(
                'month',
                'invoices',
                'totalCustomers',
                'totalBillingAmount',
                'pendingAmount',
                'paidAmount',
                'customersWithoutInvoice'
            ));

        } catch (\Exception $e) {
            Log::error('Monthly bills error: ' . $e->getMessage());
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
            $displayMonth = $monthDate->format('F Y');

            // Get due customers for the month
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);

            if ($dueCustomers->isEmpty()) {
                return redirect()->back()->with('error', 'No customers due for billing in ' . $displayMonth);
            }

            $generatedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($dueCustomers as $customer) {
                try {
                    // Generate monthly invoice (will check if exists)
                    $invoice = $this->generateMonthlyInvoice($customer, $monthDate);
                    
                    if ($invoice) {
                        if ($invoice->wasRecentlyCreated) {
                            $generatedCount++;
                        } else {
                            $skippedCount++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Customer {$customer->name}: " . $e->getMessage();
                    Log::error("Invoice generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }

            $message = "Generated $generatedCount bills for " . $displayMonth;
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
     * Get due customers for specific month
     */
    private function getDueCustomersForMonth(Carbon $monthDate)
    {
        // Get all active customer products
        $customerProducts = DB::table('customer_to_products as cp')
            ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
            ->join('products as p', 'cp.p_id', '=', 'p.p_id')
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where('c.is_active', 1)
            ->whereNotNull('cp.assign_date')
            ->where('cp.assign_date', '<=', $monthDate->endOfMonth())
            ->select(
                'c.c_id',
                'c.name',
                'c.customer_id',
                'p.monthly_price',
                'cp.billing_cycle_months',
                'cp.assign_date',
                'cp.due_date',
                'cp.cp_id',
                'cp.is_custom_price',
                'cp.custom_price'
            )
            ->get();
        
        // Filter customers who should have invoice this month
        // ALL active customers get invoices every month (for due display)
        $dueCustomers = $customerProducts->filter(function($cp) use ($monthDate) {
            $assignDate = Carbon::parse($cp->assign_date);
            
            // Product must be assigned before or during the billing month
            if ($assignDate->greaterThan($monthDate->endOfMonth())) {
                return false;
            }
            
            // All active customers should have monthly invoice for due tracking
            return true;
        });
        
        return $dueCustomers;
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
            
            // Get all payments for this invoice (without month filtering)
            $allPayments = $invoice->payments;

            return response()->json([
                'success' => true,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $customer ? $customer->name : 'Unknown',
                'total_amount' => $invoice->total_amount,
                'received_amount' => $invoice->received_amount ?? 0,
                'next_due' => $invoice->next_due ?? 0,
                'payments' => $allPayments->map(function($payment) {
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
            
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment updated successfully. Invoice amounts have been recalculated.',
                    'payment' => $payment,
                    'invoice' => [
                        'invoice_id' => $invoice->invoice_id,
                        'total_amount' => $invoice->total_amount,
                        'received_amount' => $invoice->received_amount,
                        'next_due' => $invoice->next_due,
                        'status' => $invoice->status
                    ]
                ]);
            }
            
            // Fallback to redirect for non-AJAX requests
            return redirect()
                ->route('admin.billing.monthly-bills', ['month' => \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m')])
                ->with('success', 'Payment updated successfully. Invoice amounts have been recalculated.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update payment error: ' . $e->getMessage());
            
            // Return JSON error response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment: ' . $e->getMessage()
                ], 500);
            }
            
            // Fallback to redirect for non-AJAX requests
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
     * Confirm user payment and carry forward remaining due
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
            $customerProduct = CustomerProduct::findOrFail($request->cp_id);

            // Mark invoice as confirmed
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
     * NEW METHOD: Close Month and Carry Forward Due
     */
    public function closeMonth(Request $request)
    {
        try {
            $request->validate([
                'month' => 'required|date_format:Y-m'
            ]);
            
            $month = $request->month;
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            
            // Get all invoices for this month
            $invoices = Invoice::with('customerProduct')
                ->whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->get();
            
            $carriedForwardCount = 0;
            
            foreach ($invoices as $invoice) {
                // Check if invoice has remaining due
                if ($invoice->next_due > 0 && in_array($invoice->status, ['unpaid', 'partial'])) {
                    // Carry forward to next month
                    $this->carryForwardDueToNextMonth($invoice, $invoice->next_due);
                    $carriedForwardCount++;
                }
            }
            
            // Mark month as closed (you might want to create a separate table for this)
            // For now, we'll just update the invoices
            Invoice::whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->update(['is_month_closed' => 1]);
            
            return redirect()->route('admin.billing.monthly-bills', ['month' => $month])
                ->with('success', "Month closed successfully. $carriedForwardCount due(s) carried forward to next month.");
                
        } catch (\Exception $e) {
            Log::error('Close month error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to close month: ' . $e->getMessage());
        }
    }
    
    /**
     * Carry forward remaining due amount to the next billing month (IMPROVED VERSION)
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
            // FIX: Check if due is already included in previous_due to avoid double counting
            if ($nextMonthInvoice->previous_due >= $remainingDue) {
                // Already included, no need to add again
                Log::info('Due already carried forward', [
                    'invoice_id' => $invoice->invoice_id,
                    'next_invoice_id' => $nextMonthInvoice->invoice_id,
                    'existing_previous_due' => $nextMonthInvoice->previous_due,
                    'remaining_due' => $remainingDue
                ]);
                return $nextMonthInvoice;
            }
            
            // Calculate the difference that needs to be added
            $dueToAdd = $remainingDue - $nextMonthInvoice->previous_due;
            
            if ($dueToAdd > 0) {
                $newPreviousDue = $nextMonthInvoice->previous_due + $dueToAdd;
                $newTotalAmount = $nextMonthInvoice->subtotal + $newPreviousDue;
                $newNextDue = max(0, $newTotalAmount - $nextMonthInvoice->received_amount);
                
                // Update status based on new amounts
                $newStatus = $nextMonthInvoice->status;
                if ($nextMonthInvoice->received_amount >= $newTotalAmount) {
                    $newStatus = 'paid';
                } elseif ($nextMonthInvoice->received_amount > 0) {
                    $newStatus = 'partial';
                } elseif ($newNextDue > 0) {
                    $newStatus = 'unpaid';
                }
                
                $nextMonthInvoice->update([
                    'previous_due' => $newPreviousDue,
                    'total_amount' => $newTotalAmount,
                    'next_due' => $newNextDue,
                    'status' => $newStatus,
                    'notes' => ($nextMonthInvoice->notes ?? '') . "\n[" . now()->format('Y-m-d H:i:s') . "] Added ৳" . number_format($dueToAdd, 2) . " carried forward from invoice #" . $invoice->invoice_number
                ]);
                
                Log::info('Updated existing next month invoice with additional carried forward amount', [
                    'original_invoice_id' => $invoice->invoice_id,
                    'next_invoice_id' => $nextMonthInvoice->invoice_id,
                    'amount_added' => $dueToAdd,
                    'total_previous_due_now' => $newPreviousDue
                ]);
            }
            
            return $nextMonthInvoice;
        } else {
            // Create a new invoice for the next month with ONLY the carried forward amount
            // No new charges will be added here - they will be added when generating invoices for next month
            $nextMonthInvoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'cp_id' => $customerProduct->cp_id,
                'issue_date' => $nextMonth->format('Y-m-d'),
                'previous_due' => $remainingDue,
                'service_charge' => 0.00,
                'vat_percentage' => 0.00,
                'vat_amount' => 0.00,
                'subtotal' => 0.00,
                'total_amount' => $remainingDue,
                'received_amount' => 0.00,
                'next_due' => $remainingDue,
                'status' => 'unpaid',
                'notes' => 'Carry-forward only: ৳' . number_format($remainingDue, 2) . " from invoice #" . $invoice->invoice_number . "\n[" . now()->format('Y-m-d H:i:s') . "] Carried forward from previous month",
                'created_by' => Auth::id() ?? 1
            ]);
            
            Log::info('Created new carry-forward invoice for next month', [
                'original_invoice_id' => $invoice->invoice_id,
                'new_invoice_id' => $nextMonthInvoice->invoice_id,
                'amount_carried_forward' => $remainingDue
            ]);
            
            return $nextMonthInvoice;
        }
    }
}