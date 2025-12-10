<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\CustomerProduct;
use App\Models\Payment;
use App\Models\BillingPeriod;

class MonthlyBillController extends Controller
{
    /**
     * Display monthly bills for a specific month
     */
    public function monthlyBills($month)
    {
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $displayMonth = $monthDate->format('F Y');
            
            // Check if it's a future month
            $currentMonth = Carbon::now()->format('Y-m');
            $isFutureMonth = $month > $currentMonth;
            
            // Check if it's the current month
            $isCurrentMonth = $month === $currentMonth;

            // Check if this month can be accessed (previous month must be closed)
            $canAccessMonth = BillingPeriod::canAccessMonth($month);
            $isMonthClosed = BillingPeriod::isMonthClosed($month);
            
            // If month cannot be accessed, redirect with error
            if (!$canAccessMonth && !$isCurrentMonth) {
                $previousMonth = $monthDate->copy()->subMonth()->format('F Y');
                return redirect()->route('admin.billing.billing-invoices')
                    ->with('error', "Cannot access {$displayMonth}. Please close {$previousMonth} first.");
            }

            // Get invoices for customers whose billing cycle falls in this month
            // OR customers with unpaid invoices from previous months (carry forward)
            $invoices = Invoice::with([
                'payments', 
                'customerProduct.product', 
                'customerProduct.customer'
            ])
            ->whereHas('customerProduct', function($q) use ($monthDate) {
                // For rolling invoice system, show customers in ALL months after their assignment
                $q->where('status', 'active')
                  ->where('is_active', 1)
                  ->where('assign_date', '<=', $monthDate->endOfMonth()); // Show if assigned before or in this month
            })
            ->where('is_active_rolling', 1) // Only show rolling invoices
            ->where('issue_date', '<=', $monthDate->endOfMonth())
            ->orderBy('issue_date', 'desc')
            ->orderBy('invoice_id', 'desc')
            ->paginate(20);

            // Get customers who are due for billing in this month (even if no invoice exists yet)
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);
            
            // Get ALL active customers with products for current month auto-generation
            $allActiveCustomers = $this->getAllActiveCustomersWithProducts($monthDate);
            
            // Automatically generate invoices for ALL active customers if it's the current month and some invoices are missing
            if ($isCurrentMonth && !$isFutureMonth && $allActiveCustomers->count() > $invoices->total()) {
                // Only generate invoices if there are more active customers than existing invoices
                $this->autoGenerateMissingInvoicesForAll($monthDate, $allActiveCustomers, $invoices);
                
                // Refresh invoices after auto-generation - with pagination
                // IMPORTANT: Maintain the same query structure to include unpaid invoices from previous months
                $invoices = Invoice::with([
                    'payments', 
                    'customerProduct.product', 
                    'customerProduct.customer'
                ])
                ->whereHas('customerProduct', function($q) use ($monthDate) {
                    // For rolling invoice system, show customers in ALL months after their assignment
                    $q->where('status', 'active')
                      ->where('is_active', 1)
                      ->where('assign_date', '<=', $monthDate->endOfMonth()); // Show if assigned before or in this month
                })
                ->where('is_active_rolling', 1) // Only show rolling invoices
                ->where('issue_date', '<=', $monthDate->endOfMonth())
                ->orderBy('issue_date', 'desc')
                ->orderBy('invoice_id', 'desc')
                ->paginate(20);
                
                // Transform invoices to show correct amounts for this specific month
                $invoices = $this->transformInvoicesForMonth($invoices, $month);
                
                // Refresh due customers after auto-generation
                $dueCustomers = $this->getDueCustomersForMonth($monthDate);
            }
            
            // Calculate statistics based on actual invoices
            $totalCustomersWithInvoices = $invoices->total();
            
            // Calculate customers with outstanding payments (unpaid + partial)
            $customersWithDue = $invoices->filter(function($invoice) {
                return in_array($invoice->status, ['unpaid', 'partial']) && $invoice->next_due > 0.00;
            })->count();
            
            // Calculate fully paid customers
            $fullyPaidCustomers = $invoices->filter(function($invoice) {
                return $invoice->status === 'paid' || $invoice->next_due <= 0.00;
            })->count();
            
            // Update total customers to include due customers without invoices
            $totalDueCustomers = $dueCustomers->count();
            
            // Total customers is customers with outstanding dues
            $totalCustomers = $customersWithDue;
            
            // Calculate historical amounts for this specific month
            $monthlyAmounts = $this->calculateHistoricalAmountsForMonth($month, $invoices);
            $totalBillingAmount = $monthlyAmounts['total_amount'];
            $paidAmount = $monthlyAmounts['received_amount'];
            $pendingAmount = $monthlyAmounts['due_amount'];

            // Get available months for the dropdown
            $availableMonths = $this->getAvailableBillingMonths();

            // Get system settings for service charge and VAT
            $systemSettings = $this->getSystemSettings();

            // Transform invoices to show correct amounts for this specific month
            $transformedInvoices = $this->transformInvoicesForMonth($invoices, $month);

            return view('admin.billing.monthly-bills', [
                'month' => $month,
                'displayMonth' => $displayMonth,
                'invoices' => $transformedInvoices,
                'dueCustomers' => $dueCustomers, // Add due customers to the view
                'totalCustomers' => $totalCustomers, // Customers with outstanding payments
                'totalCustomersWithInvoices' => $totalCustomersWithInvoices,
                'customersWithDue' => $customersWithDue, // Customers with outstanding balance
                'fullyPaidCustomers' => $fullyPaidCustomers, // Customers who paid fully
                'totalDueCustomers' => $totalDueCustomers,
                'totalBillingAmount' => $totalBillingAmount,
                'paidAmount' => $paidAmount,
                'pendingAmount' => $pendingAmount,
                'isFutureMonth' => $isFutureMonth,
                'isCurrentMonth' => $isCurrentMonth, // Add this to the view
                'isMonthClosed' => $isMonthClosed, // Add closed status
                'canAccessMonth' => $canAccessMonth, // Add access permission
                'availableMonths' => $availableMonths,
                'systemSettings' => $systemSettings
            ]);

        } catch (\Exception $e) {
            Log::error('Monthly bills error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading monthly bills: ' . $e->getMessage());
        }
    }

    /**
     * Automatically generate missing invoices for due customers
     */
    private function autoGenerateMissingInvoices(Carbon $monthDate, $dueCustomers, $existingInvoices)
    {
        try {
            // Get system settings
            $systemSettings = $this->getSystemSettings();
            $serviceCharge = $systemSettings['fixed_monthly_charge'] ?? 0.00;
            $vatPercentage = $systemSettings['vat_percentage'] ?? 0.00;
            
            // Get existing invoice customer IDs
            $existingCustomerIds = $existingInvoices->pluck('cp_id')->toArray();
            
            // Generate invoices for due customers who don't have invoices yet
            $generatedCount = 0;
            foreach ($dueCustomers as $customer) {
                // Skip if invoice already exists
                if (in_array($customer->c_id, $existingCustomerIds)) {
                    continue;
                }
                
                try {
                    // Create new invoice
                    $this->createCustomerMonthlyInvoice($customer, $monthDate, $serviceCharge, $vatPercentage);
                    $generatedCount++;
                } catch (\Exception $e) {
                    Log::error("Auto-generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }
            
            if ($generatedCount > 0) {
                Log::info("Auto-generated {$generatedCount} invoices for {$monthDate->format('F Y')}");
            }
            
        } catch (\Exception $e) {
            Log::error('Auto-generate missing invoices error: ' . $e->getMessage());
        }
    }

    /**
     * Automatically generate missing invoices for ALL active customers
     */
    private function autoGenerateMissingInvoicesForAll(Carbon $monthDate, $allActiveCustomers, $existingInvoices)
    {
        try {
            // Get system settings
            $systemSettings = $this->getSystemSettings();
            $serviceCharge = $systemSettings['fixed_monthly_charge'] ?? 0.00;
            $vatPercentage = $systemSettings['vat_percentage'] ?? 0.00;
            
            // Get existing invoice customer product IDs for this specific month
            $existingInvoiceCpIds = $existingInvoices
                ->filter(function($invoice) use ($monthDate) {
                    return $invoice->issue_date 
                        && \Carbon\Carbon::parse($invoice->issue_date)->year == $monthDate->year
                        && \Carbon\Carbon::parse($invoice->issue_date)->month == $monthDate->month;
                })
                ->pluck('cp_id')
                ->toArray();
            
            // Generate invoices for ALL active customers who don't have invoices for this month yet
            $generatedCount = 0;
            foreach ($allActiveCustomers as $customer) {
                // Parse product details to get cp_ids
                $cpIds = [];
                if ($customer->product_details) {
                    $products = explode(',', $customer->product_details);
                    foreach ($products as $product) {
                        list($p_id, $price, $cycle, $cp_id) = explode(':', $product);
                        $cpIds[] = $cp_id;
                    }
                }
                
                // Check if any of this customer's products already have invoices for this month
                $hasInvoiceForThisMonth = false;
                foreach ($cpIds as $cpId) {
                    if (in_array($cpId, $existingInvoiceCpIds)) {
                        $hasInvoiceForThisMonth = true;
                        break;
                    }
                }
                
                // Skip if invoice already exists for this month
                if ($hasInvoiceForThisMonth) {
                    continue;
                }
                
                try {
                    // Create new invoice
                    $this->createCustomerMonthlyInvoice($customer, $monthDate, $serviceCharge, $vatPercentage);
                    $generatedCount++;
                } catch (\Exception $e) {
                    Log::error("Auto-generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }
            
            if ($generatedCount > 0) {
                Log::info("Auto-generated {$generatedCount} invoices for ALL customers in {$monthDate->format('F Y')}");
            }
            
        } catch (\Exception $e) {
            Log::error('Auto-generate missing invoices for ALL customers error: ' . $e->getMessage());
        }
    }

    /**
     * Get all active customers with active products (regardless of billing cycle)
     */
    private function getAllActiveCustomersWithProducts(Carbon $monthDate)
    {
        return DB::table('customers as c')
            ->select(
                'c.c_id',
                'c.name',
                'c.customer_id',
                'c.email',
                'c.phone',
                DB::raw('SUM(p.monthly_price * cp.billing_cycle_months) as total_product_amount'),
                DB::raw('GROUP_CONCAT(CONCAT(p.p_id, ":", p.monthly_price, ":", cp.billing_cycle_months, ":", cp.cp_id)) as product_details')
            )
            ->join('customer_to_products as cp', 'c.c_id', '=', 'cp.c_id')
            ->join('products as p', 'cp.p_id', '=', 'p.p_id')
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where('c.is_active', 1)
            ->where('cp.assign_date', '<=', $monthDate->endOfMonth()) // Only include customers assigned before or during this month
            ->groupBy('c.c_id', 'c.name', 'c.customer_id', 'c.email', 'c.phone')
            ->orderBy('c.name')
            ->get()
            ->map(function($customer) {
                // Parse product details
                $productDetails = [];
                if ($customer->product_details) {
                    $products = explode(',', $customer->product_details);
                    foreach ($products as $product) {
                        list($p_id, $price, $cycle, $cp_id) = explode(':', $product);
                        $productDetails[] = [
                            'p_id' => $p_id,
                            'cp_id' => $cp_id,
                            'monthly_price' => $price,
                            'billing_cycle_months' => $cycle
                        ];
                    }
                }
                $customer->product_details = $productDetails;
                return $customer;
            });
    }

    /**
     * Generate monthly bills for a specific month (for ALL active customers with products)
     */
    public function generateMonthlyBillsForAll(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'include_service_charge' => 'sometimes|boolean'
        ]);

        try {
            $month = $request->month;
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $displayMonth = $monthDate->format('F Y');
            $includeServiceCharge = $request->boolean('include_service_charge', true);

            // Get system settings
            $systemSettings = $this->getSystemSettings();
            $serviceCharge = $includeServiceCharge ? ($systemSettings['fixed_monthly_charge'] ?? 0.00) : 0;
            $vatPercentage = $systemSettings['vat_percentage'] ?? 0.00;

            // Get ALL active customers with products (not just those due based on billing cycle)
            $allCustomers = $this->getAllActiveCustomersWithProducts($monthDate);

            if ($allCustomers->isEmpty()) {
                return redirect()->back()->with('error', 'No active customers with products found for ' . $displayMonth . '.');
            }

            $generatedCount = 0;
            $errors = [];

            foreach ($allCustomers as $customer) {
                try {
                    // Create invoices (one per product) - returns count of invoices created
                    $invoicesCreated = $this->createCustomerMonthlyInvoice($customer, $monthDate, $serviceCharge, $vatPercentage);
                    
                    if ($invoicesCreated > 0) {
                        $generatedCount += $invoicesCreated;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Customer {$customer->name}: " . $e->getMessage();
                    Log::error("Monthly bill generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }

            $message = "Generated $generatedCount monthly bills for all active customers in $displayMonth";
            
            if (!empty($errors)) {
                $message .= " (with " . count($errors) . " errors)";
            }

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'generated_count' => $generatedCount,
                    'warnings' => $errors
                ]);
            }
            
            return redirect()->route('admin.billing.monthly-bills', $month)
                ->with('success', $message)
                ->with('warnings', $errors);

        } catch (\Exception $e) {
            Log::error('Generate monthly bills for all error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate monthly bills: ' . $e->getMessage());
        }
    }



   

    /**
     * Create separate monthly invoices for each product of a customer (respecting billing cycles)
     * Returns the count of invoices created
     */
    private function createCustomerMonthlyInvoice($customer, Carbon $monthDate, $serviceCharge = 0.00, $vatPercentage = 0.00)
    {
        $invoicesCreated = 0;
        
        // Create separate invoice for each product
        foreach (($customer->product_details ?? []) as $product) {
            // Check if invoice already exists for this product and month
            $existingInvoice = Invoice::where('cp_id', $product['cp_id'])
                ->whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->first();
            
            if ($existingInvoice) {
                continue; // Skip if invoice already exists for this product
            }
            
            // Check if this customer should be billed for this product in this month based on billing cycle
            $shouldBillThisMonth = $this->shouldCustomerBeBilledForProductThisMonth(
                $product['cp_id'], 
                $product['billing_cycle_months'], 
                $monthDate
            );
            
            // Only calculate product amount if customer should be billed this month
            $productAmount = 0;
            if ($shouldBillThisMonth) {
                $productAmount = $product['monthly_price'] * $product['billing_cycle_months'];
            }
            
            // Get previous due amount from unpaid invoices for THIS SPECIFIC PRODUCT
            // This includes ALL unpaid amounts, not just from immediately previous months
            $previousDue = Invoice::where('cp_id', $product['cp_id'])
                ->where('status', '!=', 'paid')
                ->where('next_due', '>', 0)
                ->sum('next_due');
            
            $totalAmount = $productAmount + $previousDue;
            
            // Only create invoice if there's a total amount to bill (either current billing or carried forward)
            if ($totalAmount > 0) {
                $invoice = Invoice::create([
                    'cp_id' => $product['cp_id'],
                    'issue_date' => $monthDate->format('Y-m-d'),
                    'previous_due' => $previousDue,
                    'subtotal' => $productAmount,
                    'total_amount' => $totalAmount,
                    'received_amount' => 0,
                    'next_due' => $totalAmount,
                    'status' => 'unpaid',
                    'notes' => $this->generateBillingNotesForProduct($customer, $product, $monthDate, $previousDue),
                    'created_by' => \Illuminate\Support\Facades\Auth::id()
                ]);
                // Invoice number is auto-generated by the model based on issue_date
                
                $invoicesCreated++;
                
                Log::info("Created invoice {$invoice->invoice_number} for customer {$customer->name} - Product ID: {$product['p_id']} with amount ৳{$totalAmount}");
            }
        }
        
        return $invoicesCreated; // Return count of invoices created
    }
    
    /**
     * Check if a customer should be billed for a specific product in the given month based on billing cycle
     */
    private function shouldCustomerBeBilledForProductThisMonth($cpId, $billingCycleMonths, Carbon $monthDate)
    {
        // Get the customer product to get assign date
        $customerProduct = DB::table('customer_to_products')
            ->where('cp_id', $cpId)
            ->first();
            
        if (!$customerProduct) {
            return false;
        }
        
        $assignDate = Carbon::parse($customerProduct->assign_date);
        
        // Check if assigned in this month (advance payment)
        if ($assignDate->year == $monthDate->year && $assignDate->month == $monthDate->month) {
            return true;
        }
        
        // For customers with billing cycles > 1, calculate if this is their billing month
        if ($billingCycleMonths > 1) {
            // Calculate months difference from assign date to billing month
            $monthsDiff = $assignDate->diffInMonths($monthDate);
            
            // Customer is due if the months difference is divisible by billing cycle
            // Also ensure we're not in a month before their start date
            return $monthsDiff >= 0 && $monthsDiff % $billingCycleMonths === 0;
        } else {
            // For monthly billing (billing_cycle_months = 1)
            // Customer should be billed every month after assignment
            return $assignDate->lessThanOrEqualTo($monthDate->endOfMonth());
        }
    }
    
    /**
     * Generate billing notes for a specific product
     */
    private function generateBillingNotesForProduct($customer, $product, Carbon $monthDate, $previousDue)
    {
        $cycleText = $this->getBillingCycleText($product['billing_cycle_months']);
        $baseNote = "Auto-generated: {$cycleText} billing for {$product['billing_cycle_months']} month(s) - Due for " . $monthDate->format('F Y');
        
        if ($previousDue > 0) {
            $baseNote .= " (Includes ৳" . number_format($previousDue, 2) . " previous due)";
        }
        
        return $baseNote;
    }

    /**
     * Generate billing notes based on billing cycles
     */
    private function generateBillingNotes($customer, Carbon $monthDate)
    {
        $notes = [];
        
        foreach (($customer->product_details ?? []) as $product) {
            $cycleText = $this->getBillingCycleText($product['billing_cycle_months']);
            $notes[] = "{$cycleText} billing for {$product['billing_cycle_months']} month(s)";
        }
        
        $baseNote = 'Auto-generated: ' . implode(', ', $notes) . ' - Due for ' . $monthDate->format('F Y');
        
        // Add previous due info if any
        // FIXED: Use cp_id instead of c_id since Invoice model doesn't have c_id column
        $previousDue = 0;
        foreach (($customer->product_details ?? []) as $product) {
            $productPreviousDue = Invoice::where('cp_id', $product['cp_id'])
                ->where('status', '!=', 'paid')
                ->where('next_due', '>', 0)
                ->sum('next_due');
            $previousDue += $productPreviousDue;
        }
            
        if ($previousDue > 0) {
            $baseNote .= " (Includes ৳" . number_format($previousDue, 2) . " previous due)";
        }
        
        return $baseNote;
    }

    /**
     * Get human-readable billing cycle text
     */
    private function getBillingCycleText($months)
    {
        return match($months) {
            1 => 'Monthly',
            3 => 'Quarterly',
            6 => 'Semi-Annual',
            12 => 'Annual',
            default => "{$months}-Month"
        };
    }

    /**
     * Generate unique invoice number with format INV-YY-MM-XXXX
     */
    private function generateInvoiceNumber($issueDate = null)
    {
        // Use Invoice model's static method which handles the new format
        return Invoice::generateInvoiceNumber($issueDate);
    }

    /**
     * Get available billing months based on customer assignment dates and billing cycles
     */
    private function getAvailableBillingMonths()
    {
        $months = collect();
        
        // Get the earliest customer product assignment date using DB query
        $earliestAssignment = DB::table('customer_to_products')
            ->whereNotNull('assign_date')
            ->orderBy('assign_date')
            ->first();
            
        if (!$earliestAssignment) {
            // If no assignments, use current month
            $months->push(Carbon::now()->format('Y-m'));
            return $months;
        }

        $startDate = Carbon::parse($earliestAssignment->assign_date)->startOfMonth();
        $currentDate = Carbon::now()->startOfMonth();
        
        // Add all months from earliest assignment to current month
        while ($startDate <= $currentDate) {
            $months->push($startDate->format('Y-m'));
            $startDate->addMonth();
        }

        return $months->unique()->sortDesc()->values();
    }

    /**
     * Calculate historical amounts for a specific month
     * Shows what the amounts SHOULD BE for that specific month based on billing logic
     */
    private function calculateHistoricalAmountsForMonth($month, $invoices)
    {
        $totalAmount = 0;
        $receivedAmount = 0;
        $dueAmount = 0;
        
        // For each invoice, calculate what the amount should be for this specific month
        foreach ($invoices as $invoice) {
            if ($invoice->customerProduct) {
                // Get the actual subtotal amount from the invoice
                $subtotalAmount = $invoice->subtotal ?? 0;
                
                // Calculate what the amounts should be for this specific month
                $monthlyAmounts = $this->calculateMonthlyAmounts(
                    $invoice->customerProduct->assign_date,
                    $invoice->customerProduct->billing_cycle_months,
                    $subtotalAmount,
                    $month
                );
                
                if ($monthlyAmounts['total_amount'] > 0) {
                    $totalAmount += $monthlyAmounts['total_amount'];
                    $receivedAmount += 0; // Assuming no payments for now
                    $dueAmount += $monthlyAmounts['total_amount'];
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
            // New billing cycle month - add new subtotal + carry forward previous total
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
     * Transform invoices to show correct amounts for a specific month
     * This is needed because we have rolling invoices that update monthly,
     * but we need to show what they looked like in each specific month
     */
    private function transformInvoicesForMonth($invoices, $month)
    {
        // Handle both paginated and regular collections
        if (method_exists($invoices, 'getCollection')) {
            // This is a paginated collection
            $transformedItems = $invoices->getCollection()->map(function($invoice) use ($month) {
                return $this->transformSingleInvoice($invoice, $month);
            });
            $invoices->setCollection($transformedItems);
            return $invoices;
        } else {
            // This is a regular collection
            return $invoices->map(function($invoice) use ($month) {
                return $this->transformSingleInvoice($invoice, $month);
            });
        }
    }

    /**
     * Transform a single invoice for a specific month
     */
    private function transformSingleInvoice($invoice, $month)
    {
        if ($invoice->customerProduct) {
            // Use the actual subtotal from the invoice, not a hardcoded value
            $actualSubtotal = $invoice->subtotal ?? 0;
            
            // Calculate what this invoice should look like for this specific month
            $monthlyAmounts = $this->calculateMonthlyAmounts(
                $invoice->customerProduct->assign_date,
                $invoice->customerProduct->billing_cycle_months,
                $actualSubtotal, // Use actual subtotal from database
                $month
            );
            
            // Create a copy of the invoice with transformed amounts
            $transformedInvoice = clone $invoice;
            $transformedInvoice->subtotal = $monthlyAmounts['subtotal'];
            $transformedInvoice->previous_due = $monthlyAmounts['previous_due'];
            $transformedInvoice->total_amount = $monthlyAmounts['total_amount'];
            $transformedInvoice->next_due = $monthlyAmounts['total_amount']; // Assuming no payments
            
            // Add cycle information for debugging
            $transformedInvoice->cycle_number = $monthlyAmounts['cycle_number'] ?? 1;
            $transformedInvoice->cycle_position = $monthlyAmounts['cycle_position'] ?? 0;
            
            return $transformedInvoice;
        }
        
        return $invoice;
    }

    /**
     * Get system settings for billing
     */
    private function getSystemSettings()
    {
        try {
            $settings = DB::table('system_settings')
                ->whereIn('key', ['fixed_monthly_charge', 'vat_percentage'])
                ->pluck('value', 'key')
                ->toArray();

            return [
                'fixed_monthly_charge' => isset($settings['fixed_monthly_charge']) ? floatval($settings['fixed_monthly_charge']) : 0.00,
                'vat_percentage' => isset($settings['vat_percentage']) ? floatval($settings['vat_percentage']) : 0.00
            ];
        } catch (\Exception $e) {
            Log::warning('Could not fetch system settings: ' . $e->getMessage());
            return [
                'fixed_monthly_charge' => 0.00,
                'vat_percentage' => 0.00
            ];
        }
    }



    /**
     * Get invoice details for modal view
     */
    public function getInvoiceDetails($invoiceId)
    {
        try {
            $invoice = Invoice::with([
                'customer',
                'payments',
                'customer.customerproducts.product'
            ])->findOrFail($invoiceId);

            $html = view('admin.billing.partials.invoice-details-modal', compact('invoice'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Get invoice details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading invoice details'
            ], 500);
        }
    }

    /**
     * Close billing month and carry forward all outstanding dues
     */
    public function closeMonth(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m'
        ]);

        try {
            DB::beginTransaction();

            $month = $request->month;
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $displayMonth = $monthDate->format('F Y');
            $currentMonth = Carbon::now()->format('Y-m');

            // Prevent closing future months
            if ($month > $currentMonth) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot close a future month'
                ]);
            }

            // Check if month is already closed
            if (BillingPeriod::isMonthClosed($month)) {
                return response()->json([
                    'success' => false,
                    'message' => $displayMonth . ' is already closed'
                ]);
            }

            // Auto-generate invoices for all customers who are due but don't have invoices yet
            $this->autoGenerateMissingInvoicesBeforeClosing($monthDate);

            // Get all invoices for this month
            $allInvoices = Invoice::whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->get();

            $totalInvoices = $allInvoices->count();
            $totalAmount = $allInvoices->sum('total_amount');
            $receivedAmount = $allInvoices->sum('received_amount');

            // Get invoices with outstanding dues
            $invoicesWithDues = $allInvoices->filter(function($invoice) {
                return $invoice->next_due > 0;
            });

            $totalCarriedForward = 0;
            $affectedInvoices = 0;

            // Mark all invoices as closed and note the carried forward amount
            foreach ($allInvoices as $invoice) {
                $dueAmount = $invoice->next_due;
                
                // Update invoice with closure information
                $closedNote = "\n[Month Closed: " . now()->format('Y-m-d H:i:s') . " by " . (\Illuminate\Support\Facades\Auth::user()->name ?? 'System') . "]";
                
                if ($dueAmount > 0) {
                    $closedNote .= " Due amount of ৳" . number_format($dueAmount, 0) . " carried forward to next billing cycle.";
                    $totalCarriedForward += $dueAmount;
                    $affectedInvoices++;
                } else {
                    $closedNote .= " Invoice fully paid.";
                }
                
                $invoice->update([
                    'notes' => ($invoice->notes ?? '') . $closedNote,
                    'is_closed' => true,
                    'closed_at' => now(),
                    'closed_by' => \Illuminate\Support\Facades\Auth::id()
                ]);
            }

            // Create or update billing period record
            BillingPeriod::updateOrCreate(
                ['billing_month' => $month],
                [
                    'is_closed' => true,
                    'total_amount' => $totalAmount,
                    'received_amount' => $receivedAmount,
                    'carried_forward' => $totalCarriedForward,
                    'total_invoices' => $totalInvoices,
                    'affected_invoices' => $affectedInvoices,
                    'closed_at' => now(),
                    'closed_by' => \Illuminate\Support\Facades\Auth::id(),
                    'notes' => "Month closed with {$affectedInvoices} invoices having outstanding dues totaling ৳" . number_format($totalCarriedForward, 0)
                ]
            );

            // Log the month closure
            Log::info("Billing month {$displayMonth} closed by " . (\Illuminate\Support\Facades\Auth::user()->name ?? 'System') . ". Carried forward ৳{$totalCarriedForward} from {$affectedInvoices} invoices.");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully closed {$displayMonth}. ৳" . number_format($totalCarriedForward, 0) . " carried forward from {$affectedInvoices} invoices.",
                'carried_forward_amount' => $totalCarriedForward,
                'affected_invoices' => $affectedInvoices,
                'total_invoices' => $totalInvoices,
                'month' => $displayMonth
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Close month error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error closing month: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record payment for monthly bill - FLEXIBLE VERSION
     */
    public function recordPayment(Request $request, $invoiceId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0', // Minimum 0 taka,cz month close hole disable hoye jabe r next due ty aita show korbe
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking,card,online',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get invoice with customer
            $invoice = Invoice::with('customer')->findOrFail($invoiceId);
            $amount = round(floatval($request->amount)); // Round to whole number
            $dueAmount = round(floatval($invoice->next_due ?? $invoice->total_amount)); // Round to whole number

            // Validate amount - must be between 1 and due amount
            if ($amount < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount must be at least ৳1'
                ], 422);
            }

            if ($amount > $dueAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount (৳' . number_format($amount) . ') cannot exceed due amount (৳' . number_format($dueAmount) . ')'
                ], 422);
            }

            // Create payment record
            $paymentData = [
                'invoice_id' => $invoice->invoice_id,
                'c_id' => $invoice->customerProduct->c_id, // Link payment to customer
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
                'collected_by' => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : 1,
                'status' => 'completed',
            ];

            // Add transaction_id if payment method is not cash
            if ($request->payment_method !== 'cash' && $request->has('transaction_id')) {
                $paymentData['transaction_id'] = $request->transaction_id;
            }

            $payment = Payment::create($paymentData);

            // Calculate new amounts
            $newReceivedAmount = round($invoice->received_amount + $amount);
            $newDueAmount = round(max(0, $invoice->total_amount - $newReceivedAmount));

            // Determine new status based on remaining due
            // Use <= 0.01 to handle floating point precision issues
            if ($newDueAmount <= 0.01 || $newReceivedAmount >= $invoice->total_amount) {
                $status = 'paid';
                $newDueAmount = 0; // Ensure it's exactly 0
            } elseif ($newReceivedAmount > 0) {
                $status = 'partial';
            } else {
                $status = 'unpaid';
            }

            $invoice->update([
                'received_amount' => $newReceivedAmount,
                'next_due' => $newDueAmount,
                'status' => $status
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment of ৳' . number_format($amount) . ' recorded successfully!',
                'invoice_id' => $invoice->invoice_id,
                'new_status' => $status,
                'new_due' => $newDueAmount,
                'new_received' => $newReceivedAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Record payment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm user payment and close their month individually - FIXED VERSION
     */
    public function confirmUserPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'cp_id' => 'required|exists:customer_to_products,cp_id',
            'next_due' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::with(['customer', 'customerProduct'])->findOrFail($request->invoice_id);
            $dueAmount = round(floatval($request->next_due));

            // Mark current invoice as confirmed (NOT paid) and carry forward the due
            $closedNote = "\n[User Confirmed: " . now()->format('Y-m-d H:i:s') . " by " . (\Illuminate\Support\Facades\Auth::user()->name ?? 'System') . "]";
            
            if ($dueAmount > 0) {
                $closedNote .= " Due amount of ৳" . number_format($dueAmount, 0) . " carried forward to next billing cycle.";
                
                // Update current invoice - mark as confirmed but keep due amount visible
                $invoice->update([
                    'status' => 'confirmed', // ✅ New status for confirmed but not fully paid
                    'next_due' => $dueAmount, // ✅ Keep due amount visible
                    'notes' => ($invoice->notes ?? '') . $closedNote,
                    'is_closed' => true,
                    'closed_at' => now(),
                    'closed_by' => \Illuminate\Support\Facades\Auth::id()
                ]);

                // Create or update next month's invoice with carried forward amount
                $nextMonth = Carbon::parse($invoice->issue_date)->addMonth();
                
                $existingNextInvoice = Invoice::where('cp_id', $request->cp_id)
                    ->whereYear('issue_date', $nextMonth->year)
                    ->whereMonth('issue_date', $nextMonth->month)
                    ->first();

                if ($existingNextInvoice) {
                    // Update existing next month invoice - add carried forward amount to previous_due
                    $existingNextInvoice->update([
                        'previous_due' => $existingNextInvoice->previous_due + $dueAmount,
                        'total_amount' => $existingNextInvoice->subtotal + $existingNextInvoice->previous_due + $dueAmount,
                        'next_due' => ($existingNextInvoice->subtotal + $existingNextInvoice->previous_due + $dueAmount) - $existingNextInvoice->received_amount,
                        'notes' => ($existingNextInvoice->notes ?? '') . "\nAdded ৳" . number_format($dueAmount, 0) . " carried forward from invoice {$invoice->invoice_number}"
                    ]);
                } else {
                    // Create new invoice for next month with carried forward amount as previous_due
                    $newInvoice = Invoice::create([
                        'cp_id' => $request->cp_id,
                        'issue_date' => $nextMonth->format('Y-m-d'),
                        'previous_due' => $dueAmount, // ✅ Carry forward as previous due
                        'subtotal' => 0, // Will be calculated when products are added
                        'total_amount' => $dueAmount, // Total is the carried forward amount
                        'received_amount' => 0,
                        'next_due' => $dueAmount,
                        'status' => 'unpaid',
                        'notes' => "Carried forward amount from invoice {$invoice->invoice_number}",
                        'created_by' => \Illuminate\Support\Facades\Auth::id()
                    ]);
                }
            } else {
                // Fully paid, just mark as closed
                $invoice->update([
                    'status' => 'paid',
                    'is_closed' => true,
                    'closed_at' => now(),
                    'closed_by' => \Illuminate\Support\Facades\Auth::id()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User payment confirmed successfully!',
                'carried_forward_amount' => $dueAmount,
                'invoice_status' => $dueAmount > 0 ? 'confirmed' : 'paid'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Confirm user payment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm user payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice data for payment modal
     */
    public function getInvoiceData($invoiceId)
    {
        try {
            $invoice = Invoice::with([
                'customer.customer.customerproducts.product'
            ])
                ->where('invoice_id', $invoiceId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'invoice' => [
                    'invoice_id' => $invoice->invoice_id,
                    'invoice_number' => $invoice->invoice_number,
                    'subtotal' => $invoice->subtotal,
                    'previous_due' => $invoice->previous_due,
                    'total_amount' => $invoice->total_amount,
                    'next_due' => $invoice->next_due,
                    'received_amount' => $invoice->received_amount,
                    'status' => $invoice->status,
                    'customer' => [
                        'name' => $invoice->customer->name ?? 'N/A',
                        'email' => $invoice->customer->email ?? 'N/A',
                        'phone' => $invoice->customer->phone ?? 'N/A',
                        'customer' => [
                            'customerproducts' => $invoice->customer->customer->customerproducts ?? []
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Quick test method to check if invoices exist
     */
    public function testInvoices($month)
    {
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            
            $invoices = Invoice::with('customer')
                ->whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->get();

            return response()->json([
                'month' => $month,
                'invoices_count' => $invoices->count(),
                'invoices' => $invoices->map(function($invoice) {
                    return [
                        'invoice_id' => $invoice->invoice_id,
                        'invoice_number' => $invoice->invoice_number,
                        'customer_name' => $invoice->customer->name ?? 'No Customer',
                        'total_amount' => $invoice->total_amount,
                        'received_amount' => $invoice->received_amount,
                        'next_due' => $invoice->next_due,
                        'status' => $invoice->status
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer products for a specific month
     */
    public function getCustomerproducts($customerId, $month)
    {
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            
            $customer = Customer::with([
                'customerproducts' => function($query) use ($monthDate) {
                    $query->where('status', 'active')
                          ->where('is_active', true)
                          ->with('product');
                }
            ])->findOrFail($customerId);

            return response()->json([
                'success' => true,
                'customer' => $customer->name,
                'products' => $customer->customerproducts->map(function($cp) {
                    return [
                        'product_name' => $cp->product->name,
                        'monthly_price' => $cp->product->monthly_price,
                        'billing_cycle' => $cp->billing_cycle_months,
                        'total_amount' => $cp->product->monthly_price * $cp->billing_cycle_months
                    ];
                }),
                'total_monthly' => $customer->customerproducts->sum(function($cp) {
                    return $cp->product->monthly_price;
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Get customer products error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading customer products'
            ], 500);
        }
    }
       
    public function handleMonthlyBills(Request $request, $month)
    {
        $request->validate([
            'action' => 'required|string',
            'invoice_ids' => 'sometimes|array',
            'invoice_ids.*' => 'exists:invoices,invoice_id'
        ]);

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $action = $request->action;
            $invoiceIds = $request->invoice_ids ?? [];

            switch ($action) {
                case 'bulk_update_status':
                    return $this->bulkUpdateStatus($invoiceIds, $request->status, $monthDate);
                    
                case 'regenerate_bills':
                    return $this->regenerateBills($monthDate);
                    
                case 'export_data':
                    return $this->exportMonthlyBills($monthDate);
                    
                case 'recalculate_totals':
                    return $this->recalculateTotals($monthDate);
                    
                default:
                    return redirect()->back()->with('error', 'Invalid action specified.');
            }

        } catch (\Exception $e) {
            Log::error('Handle monthly bills error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process request: ' . $e->getMessage());
        }
    }



    /**
     * Bulk update invoice status
     */
    private function bulkUpdateStatus($invoiceIds, $status, Carbon $monthDate)
    {
        try {
            $validStatuses = ['paid', 'unpaid', 'partial', 'cancelled'];
            
            if (!in_array($status, $validStatuses)) {
                return redirect()->back()->with('error', 'Invalid status specified.');
            }

            $invoices = Invoice::whereIn('invoice_id', $invoiceIds)->get();
            $updatedCount = 0;

            foreach ($invoices as $invoice) {
                // For paid status, mark as fully paid
                if ($status === 'paid') {
                    $invoice->update([
                        'received_amount' => $invoice->total_amount,
                        'next_due' => 0,
                        'status' => 'paid'
                    ]);
                } 
                // For unpaid status, reset payments
                elseif ($status === 'unpaid') {
                    $invoice->update([
                        'received_amount' => 0,
                        'next_due' => $invoice->total_amount,
                        'status' => 'unpaid'
                    ]);
                }
                // For other statuses, just update the status
                else {
                    $invoice->update(['status' => $status]);
                }
                
                $updatedCount++;
            }

            return redirect()->back()->with('success', "Updated status for {$updatedCount} invoices to {$status}");

        } catch (\Exception $e) {
            throw new \Exception("Bulk status update failed: " . $e->getMessage());
        }
    }

    /**
     * Regenerate bills for the month (useful for corrections)
     */
    private function regenerateBills(Carbon $monthDate)
    {
        try {
            DB::beginTransaction();

            // Delete existing invoices for the month
            $deletedCount = Invoice::whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->delete();

            // Regenerate bills using your existing logic
            // You might want to call your generateMonthlyBills logic here
            $systemSettings = $this->getSystemSettings();
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);
            $regeneratedCount = 0;

            foreach ($dueCustomers as $customer) {
                $this->createCustomerMonthlyInvoice($customer, $monthDate, 
                    $systemSettings['fixed_monthly_charge'], 
                    $systemSettings['vat_percentage']);
                $regeneratedCount++;
            }

            DB::commit();

            return redirect()->back()->with('success', "Regenerated {$regeneratedCount} bills for " . $monthDate->format('F Y'));

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Regenerate bills failed: " . $e->getMessage());
        }
    }

    /**
     * Export monthly bills data
     */
    private function exportMonthlyBills(Carbon $monthDate)
    {
        try {
            $invoices = Invoice::with(['customer', 'payments'])
                ->whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->get();

            // In a real implementation, you would generate CSV/Excel file
            // For now, we'll just log and return success
            Log::info('Monthly bills export prepared', [
                'month' => $monthDate->format('F Y'),
                'invoice_count' => $invoices->count(),
                'total_amount' => $invoices->sum('total_amount')
            ]);

            return redirect()->back()->with('success', 'Export data prepared for ' . $monthDate->format('F Y') . ' (' . $invoices->count() . ' invoices)');

        } catch (\Exception $e) {
            throw new \Exception("Export failed: " . $e->getMessage());
        }
    }

    /**
     * Recalculate totals for the month (useful if there were data issues)
     */
    private function recalculateTotals(Carbon $monthDate)
    {
        try {
            DB::beginTransaction();

            $invoices = Invoice::with('payments')
                ->whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->get();

            $recalculatedCount = 0;

            foreach ($invoices as $invoice) {
                $totalReceived = $invoice->payments->sum('amount');
                $nextDue = max(0, $invoice->total_amount - $totalReceived);
                
                // Determine status based on payments
                if ($nextDue <= 0) {
                    $status = 'paid';
                } elseif ($totalReceived > 0) {
                    $status = 'partial';
                } else {
                    $status = 'unpaid';
                }

                $invoice->update([
                    'received_amount' => $totalReceived,
                    'next_due' => $nextDue,
                    'status' => $status
                ]);

                $recalculatedCount++;
            }

            DB::commit();

            return redirect()->back()->with('success', "Recalculated totals for {$recalculatedCount} invoices");

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Recalculate totals failed: " . $e->getMessage());
        }
    }

    /**
     * Generate invoices for all customers
     */
    public function generateAllInvoices(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'force' => 'nullable|boolean'
        ]);
        
        $month = $request->month;
        $force = $request->force ?? false;
        
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $displayMonth = $monthDate->format('F Y');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid month format. Please use YYYY-MM format.'
            ], 400);
        }
        
        // Get all active customers with active products
        $customers = $this->getAllActiveCustomersWithProducts($monthDate);
        
        if ($customers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "No active customers with products found for {$displayMonth}."
            ]);
        }
        
        $generatedCount = 0;
        $skippedCount = 0;
        $errors = [];
        
        foreach ($customers as $customer) {
            try {
                // Check if invoice already exists for this customer and month
                $existingInvoice = Invoice::where('cp_id', $customer->c_id)
                    ->whereYear('issue_date', $monthDate->year)
                    ->whereMonth('issue_date', $monthDate->month)
                    ->first();
                
                if ($existingInvoice && !$force) {
                    $skippedCount++;
                    continue;
                }
                
                if ($existingInvoice && $force) {
                    $existingInvoice->delete();
                }
                
                // Create new invoice
                $invoice = $this->createCustomerMonthlyInvoice($customer, $monthDate);
                
                if ($invoice) {
                    $generatedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Customer {$customer->name}: " . $e->getMessage();
                Log::error("Invoice generation failed for customer {$customer->c_id}: " . $e->getMessage());
            }
        }
        
        $message = "Generated {$generatedCount} invoices for all customers in {$displayMonth}";
        
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} customers already had invoices)";
        }
        
        if (!empty($errors)) {
            $message .= " (" . count($errors) . " errors occurred)";
        }
        
        $response = [
            'success' => true,
            'message' => $message,
            'generated_count' => $generatedCount,
            'skipped_count' => $skippedCount
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response);
    }

    /**
     * Auto-generate invoices for all customers who are due but don't have invoices yet
     */
    private function autoGenerateMissingInvoicesBeforeClosing(Carbon $monthDate)
    {
        try {
            // Get system settings
            $systemSettings = $this->getSystemSettings();
           
            // Get all active customers with products who are due for billing in this month
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);

            if ($dueCustomers->isEmpty()) {
                return;
            }

            // Get existing invoices for this month
            $existingInvoices = Invoice::whereYear('issue_date', $monthDate->year)
                ->whereMonth('issue_date', $monthDate->month)
                ->get();

            // Get existing invoice customer product IDs
            $existingCpIds = $existingInvoices->pluck('cp_id')->toArray();

            // Generate invoices for due customers who don't have invoices yet
            $generatedCount = 0;
            foreach ($dueCustomers as $customer) {
                try {
                    // Create invoices for each product (one per product)
                    foreach (($customer->product_details ?? []) as $product) {
                        // Skip if invoice already exists for this customer product
                        if (in_array($product['cp_id'], $existingCpIds)) {
                            continue;
                        }

                        // Create new invoice for this product
                        $this->createSingleProductInvoice($customer, $product, $monthDate, $serviceCharge, $vatPercentage);
                        $generatedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Auto-generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }

            if ($generatedCount > 0) {
                Log::info("Auto-generated {$generatedCount} invoices for {$monthDate->format('F Y')} before closing month");
            }

        } catch (\Exception $e) {
            Log::error('Auto-generate missing invoices before closing error: ' . $e->getMessage());
        }
    }

    /**
     * Create a single product invoice for a customer
     */
    private function createSingleProductInvoice($customer, $product, Carbon $monthDate, $serviceCharge = 0.00, $vatPercentage = 0.00)
    {
        try {
            $productAmount = $product['monthly_price'] * $product['billing_cycle_months'];

            // Get previous due amount from unpaid invoices for THIS SPECIFIC PRODUCT
            $previousDue = Invoice::where('cp_id', $product['cp_id'])
                ->where('status', '!=', 'paid')
                ->where('next_due', '>', 0)
                ->sum('next_due');

            $totalAmount = $productAmount + $previousDue;

            $invoice = Invoice::create([
                'cp_id' => $product['cp_id'],
                'issue_date' => $monthDate->format('Y-m-d'),
                'previous_due' => $previousDue,
                'subtotal' => $productAmount,
                'total_amount' => $totalAmount,
                'received_amount' => 0,
                'next_due' => $totalAmount,
                'status' => 'unpaid',
                'notes' => $this->generateBillingNotesForProduct($customer, $product, $monthDate, $previousDue),
                'created_by' => \Illuminate\Support\Facades\Auth::id()
            ]);

            Log::info("Created invoice {$invoice->invoice_number} for customer {$customer->name} - Product ID: {$product['p_id']} with amount ৳{$totalAmount}");

            return $invoice;
        } catch (\Exception $e) {
            Log::error("Failed to create invoice for customer {$customer->c_id}, product {$product['p_id']}: " . $e->getMessage());
            throw $e;
        }
    }
     private function createRollingInvoiceForProduct($customer, $product, Carbon $monthDate)
    {
        // Check if this is a billing month
        $shouldBill = RollingBillingHelper::shouldBillThisMonth($product['cp_id'], $monthDate);
        
        if (!$shouldBill) {
            // If not a billing month, but there's an active rolling invoice, update it
            $activeInvoice = Invoice::where('cp_id', $product['cp_id'])
                ->where('is_active_rolling', true)
                ->first();
            
            if ($activeInvoice) {
                // Update existing invoice date
                $activeInvoice->update([
                    'issue_date' => $monthDate->format('Y-m-d')
                ]);
                return 1;
            }
            return 0;
        }
        
        // Get or create rolling invoice
        $invoice = Invoice::getOrCreateRollingInvoice($product['cp_id'], $monthDate);
        
        if ($invoice) {
            Log::info("Rolling invoice updated: {$invoice->invoice_number} for customer {$customer->name}");
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Get customers due for billing this month
     */
    private function getDueCustomersForMonth(Carbon $monthDate)
    {
        return DB::table('customers as c')
            ->select(
                'c.c_id',
                'c.name',
                'c.customer_id',
                'c.email',
                'c.phone',
                DB::raw('GROUP_CONCAT(CONCAT(p.p_id, ":", p.monthly_price, ":", cp.billing_cycle_months, ":", cp.cp_id)) as product_details')
            )
            ->join('customer_to_products as cp', 'c.c_id', '=', 'cp.c_id')
            ->join('products as p', 'cp.p_id', '=', 'p.p_id')
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where('c.is_active', 1)
            ->where('cp.assign_date', '<=', $monthDate->endOfMonth())
            ->where(function($query) use ($monthDate) {
                // Include customers who should be billed this month
                $query->where(function($q) use ($monthDate) {
                    $q->whereRaw('PERIOD_DIFF(
                        DATE_FORMAT(?, "%Y%m"),
                        DATE_FORMAT(cp.assign_date, "%Y%m")
                    ) % cp.billing_cycle_months = 0', [$monthDate->format('Y-m-d')]);
                })
                // OR customers with active rolling invoices
                ->orWhereExists(function($existsQuery) {
                    $existsQuery->select(DB::raw(1))
                        ->from('invoices as i')
                        ->whereColumn('i.cp_id', 'cp.cp_id')
                        ->where('i.is_active_rolling', true)
                        ->where('i.status', '!=', 'paid');
                });
            })
            ->groupBy('c.c_id', 'c.name', 'c.customer_id', 'c.email', 'c.phone')
            ->get()
            ->map(function($customer) {
                // Parse product details
                $productDetails = [];
                if ($customer->product_details) {
                    $products = explode(',', $customer->product_details);
                    foreach ($products as $product) {
                        list($p_id, $price, $cycle, $cp_id) = explode(':', $product);
                        $productDetails[] = [
                            'p_id' => $p_id,
                            'cp_id' => $cp_id,
                            'monthly_price' => $price,
                            'billing_cycle_months' => $cycle
                        ];
                    }
                }
                $customer->product_details = $productDetails;
                return $customer;
            });
    }
    
    /**
     * Generate monthly bills using rolling invoice system
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

            // Get due customers
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);

            if ($dueCustomers->isEmpty()) {
                return redirect()->back()->with('error', 'No customers due for billing in ' . $displayMonth);
            }

            $generatedCount = 0;

            foreach ($dueCustomers as $customer) {
                foreach (($customer->product_details ?? []) as $product) {
                    try {
                        $created = $this->createRollingInvoiceForProduct($customer, $product, $monthDate);
                        $generatedCount += $created;
                    } catch (\Exception $e) {
                        Log::error("Rolling invoice failed for customer {$customer->c_id}: " . $e->getMessage());
                    }
                }
            }

            $message = "Generated/updated {$generatedCount} rolling invoices for {$displayMonth}";

            return redirect()->route('admin.billing.monthly-bills', $month)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Generate rolling bills error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating bills: ' . $e->getMessage());
        }
    }
}
