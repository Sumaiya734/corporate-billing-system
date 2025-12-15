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
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            //'transaction_id' => 'nullable|string|max:100',
            'note' => 'nullable|string'
        ]);

        try {
            $invoice = Invoice::findOrFail($invoiceId);

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
                $status = $newReceivedAmount > 0 ? 'partial' : 'unpaid';
            }

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
                
            // Total pending amount across all unpaid/partial invoices
            $totalPendingAmount = Invoice::whereIn('status', ['unpaid', 'partial'])
                ->sum(DB::raw('GREATEST(total_amount - COALESCE(received_amount, 0), 0)'));
            
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
                ->where('next_due', '>', 0)
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
            
            // Include months that have:
            // 1. Due customers (should be billed)
            // 2. Invoices (already billed)
            // 3. Are current or future months
            $hasInvoices = $monthData['total_amount'] > 0;
            $hasDueCustomers = $monthData['total_customers'] > 0;
            $isCurrentOrFuture = $monthDate >= $currentDate;
            
            if ($hasDueCustomers || $hasInvoices || $isCurrentOrFuture) {
                // Check if month is closed
                $isClosed = BillingPeriod::isMonthClosed($month);
                $status = $isClosed ? 'Closed' : $monthData['status'];
                
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
                    'is_closed' => $isClosed,
                    'is_dynamic' => true,
                    'status' => $status,
                    'notes' => $isClosed ? 'Month closed - dues carried forward' : 'Automatically calculated'
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
            ->select(
                'c.c_id',
                'c.name',
                'c.customer_id',
                'p.monthly_price',
                'cp.billing_cycle_months',
                'cp.assign_date',
                'cp.due_date'
            )
            ->get();
        
        // Filter products that are due in this specific month based on billing cycle
        $dueProducts = $customerProducts->filter(function($cp) use ($monthDate) {
            $assignDate = Carbon::parse($cp->assign_date);
            $billingCycle = $cp->billing_cycle_months ?? 1;
            // Extract day from due_date, fallback to assign_date day if due_date is null
            $dueDay = $cp->due_date ? Carbon::parse($cp->due_date)->day : $assignDate->day;
            
            // Product must be assigned before the billing month
            if ($assignDate->greaterThan($monthDate->endOfMonth())) {
                return false;
            }
            
            // Calculate the first due date (next occurrence of due_day after assign_date)
            $firstDueDate = Carbon::parse($assignDate);
            
            // If assign date's day is before or equal to due day in same month, first due is this month
            if ($assignDate->day <= $dueDay) {
                $firstDueDate->day($dueDay);
            } else {
                // Otherwise, first due is next month
                $firstDueDate->addMonth()->day($dueDay);
            }
            
            // Product must have had its first due date before or during the billing month
            if ($firstDueDate->greaterThan($monthDate->endOfMonth())) {
                return false;
            }
            
            // Calculate months difference from first due date to billing month
            $monthsDiff = $firstDueDate->diffInMonths($monthDate);
            
            // Check if this month is a billing month for this product
            // Product is due if: months difference is divisible by billing cycle
            return ($monthsDiff % $billingCycle) === 0;
        });
        
        return $dueProducts;
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
        // Calculate product amount only (no service charge or VAT)
        $productAmount = $customer->monthly_price;
        $totalAmount = $productAmount;

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'c_id' => $customer->c_id,
            'issue_date' => $monthDate->format('Y-m-d'),
            'previous_due' => 0.00,
            'service_charge' => 0.00,
            'vat_percentage' => 0.00,
            'vat_amount' => 0.00,
            'subtotal' => $productAmount,
            'total_amount' => $totalAmount,
            'received_amount' => 0,
            'next_due' => $totalAmount,
            'status' => 'unpaid',
            'notes' => 'Auto-generated based on product assignment',
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
            $invoices = Invoice::with(['customer.customer.customerproducts.product', 'payments'])
                ->whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
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
                    $existingInvoice = Invoice::where('c_id', $customer->c_id)
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

            return response()->json([
                'success' => true,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $customer ? $customer->name : 'Unknown',
                'total_amount' => $invoice->total_amount,
                'received_amount' => $invoice->received_amount ?? 0,
                'next_due' => $invoice->next_due ?? 0,
                'payments' => $invoice->payments->map(function($payment) {
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
            'amount' => 'required|numeric|min:0',
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
}