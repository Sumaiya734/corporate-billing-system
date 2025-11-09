<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Package;
use App\Models\CustomerPackage;
use Carbon\Carbon;

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

            // Get invoices for the selected month with relationships
            $invoices = Invoice::with([
                'customer', 
                'payments',
                'customer.customerPackages.package'
            ])
            ->whereYear('issue_date', $monthDate->year)
            ->whereMonth('issue_date', $monthDate->month)
            ->orderBy('issue_date', 'desc')
            ->orderBy('invoice_id', 'desc')
            ->get();

            // Calculate statistics based on actual invoices
            $totalCustomers = $invoices->count();
            $totalBillingAmount = $invoices->sum('total_amount');
            $paidAmount = $invoices->sum('received_amount');
            $pendingAmount = $invoices->sum('next_due');

            // Get available months for the dropdown
            $availableMonths = $this->getAvailableBillingMonths();

            // Get system settings for service charge and VAT
            $systemSettings = $this->getSystemSettings();

            return view('admin.billing.monthly-bills', [
                'month' => $month,
                'displayMonth' => $displayMonth,
                'invoices' => $invoices,
                'totalCustomers' => $totalCustomers,
                'totalBillingAmount' => $totalBillingAmount,
                'paidAmount' => $paidAmount,
                'pendingAmount' => $pendingAmount,
                'isFutureMonth' => $isFutureMonth,
                'availableMonths' => $availableMonths,
                'systemSettings' => $systemSettings
            ]);

        } catch (\Exception $e) {
            Log::error('Monthly bills error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading monthly bills: ' . $e->getMessage());
        }
    }

    /**
     * Generate monthly bills for a specific month (respecting billing cycles)
     */
    public function generateMonthlyBills(Request $request)
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
            $serviceCharge = $includeServiceCharge ? ($systemSettings['fixed_monthly_charge'] ?? 50.00) : 0;
            $vatPercentage = $systemSettings['vat_percentage'] ?? 5.00;

            // Get customers who are due for billing in this month based on their billing cycles
            $dueCustomers = $this->getDueCustomersForMonth($monthDate);

            if ($dueCustomers->isEmpty()) {
                return redirect()->back()->with('error', 'No customers due for billing in ' . $displayMonth . ' based on their billing cycles.');
            }

            $generatedCount = 0;
            $errors = [];

            foreach ($dueCustomers as $customer) {
                try {
                    // Check if invoice already exists for this customer and month
                    $existingInvoice = Invoice::where('c_id', $customer->c_id)
                        ->whereYear('issue_date', $monthDate->year)
                        ->whereMonth('issue_date', $monthDate->month)
                        ->first();

                    if (!$existingInvoice) {
                        // Create new invoice
                        $this->createCustomerMonthlyInvoice($customer, $monthDate, $serviceCharge, $vatPercentage);
                        $generatedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Customer {$customer->name}: " . $e->getMessage();
                    Log::error("Monthly bill generation failed for customer {$customer->c_id}: " . $e->getMessage());
                }
            }

            $message = "Generated $generatedCount monthly bills for $displayMonth";
            
            if (!empty($errors)) {
                $message .= " (with " . count($errors) . " errors)";
            }

            return redirect()->route('admin.billing.monthly-bills', $month)
                ->with('success', $message)
                ->with('warnings', $errors);

        } catch (\Exception $e) {
            Log::error('Generate monthly bills error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate monthly bills: ' . $e->getMessage());
        }
    }

    /**
     * Get customers who are due for billing in specific month based on billing cycles
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
                DB::raw('SUM(p.monthly_price * cp.billing_cycle_months) as total_package_amount'),
                DB::raw('GROUP_CONCAT(CONCAT(p.p_id, ":", p.monthly_price, ":", cp.billing_cycle_months, ":", cp.cp_id)) as package_details')
            )
            ->join('customer_to_packages as cp', 'c.c_id', '=', 'cp.c_id')
            ->join('packages as p', 'cp.p_id', '=', 'p.p_id')
            ->where('cp.status', 'active')
            ->where('cp.is_active', 1)
            ->where('c.is_active', 1)
            ->where(function($query) use ($monthDate) {
                // Customers whose billing cycle falls in this month
                $query->where(function($q) use ($monthDate) {
                    // Monthly billing (billing_cycle_months = 1)
                    $q->where('cp.billing_cycle_months', 1)
                      ->where('cp.assign_date', '<=', $monthDate->endOfMonth());
                })
                // Quarterly, Semi-annual, Annual billing
                ->orWhere(function($q) use ($monthDate) {
                    $q->where('cp.billing_cycle_months', '>', 1)
                      ->whereRaw('DATE_ADD(cp.assign_date, INTERVAL cp.billing_cycle_months MONTH) > ?', 
                                [$monthDate->startOfMonth()])
                      ->where('cp.assign_date', '<=', $monthDate->startOfMonth());
                });
            })
            ->groupBy('c.c_id', 'c.name', 'c.customer_id', 'c.email', 'c.phone')
            ->orderBy('c.name')
            ->get()
            ->map(function($customer) {
                // Parse package details
                $packageDetails = [];
                if ($customer->package_details) {
                    $packages = explode(',', $customer->package_details);
                    foreach ($packages as $package) {
                        list($p_id, $price, $cycle, $cp_id) = explode(':', $package);
                        $packageDetails[] = [
                            'p_id' => $p_id,
                            'cp_id' => $cp_id,
                            'monthly_price' => $price,
                            'billing_cycle_months' => $cycle
                        ];
                    }
                }
                $customer->package_details = $packageDetails;
                return $customer;
            });
    }

    /**
     * Create monthly invoice for a customer (respecting billing cycles)
     */
    private function createCustomerMonthlyInvoice($customer, Carbon $monthDate, $serviceCharge = 50.00, $vatPercentage = 5.00)
    {
        // Calculate total package amount from active packages that are due this month
        $packageAmount = $customer->total_package_amount ?? 0;

        $subtotal = $packageAmount + $serviceCharge;
        $vatAmount = $subtotal * ($vatPercentage / 100);
        $totalAmount = $subtotal + $vatAmount;

        // Get previous due amount from unpaid invoices
        $previousDue = Invoice::where('c_id', $customer->c_id)
            ->where('status', '!=', 'paid')
            ->where('next_due', '>', 0)
            ->sum('next_due');

        $totalAmount += $previousDue;

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'c_id' => $customer->c_id,
            'issue_date' => $monthDate->format('Y-m-d'),
            'previous_due' => $previousDue,
            'service_charge' => $serviceCharge,
            'vat_percentage' => $vatPercentage,
            'vat_amount' => $vatAmount,
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'received_amount' => 0,
            'next_due' => $totalAmount,
            'status' => 'unpaid',
            'notes' => $this->generateBillingNotes($customer, $monthDate),
            'created_by' => auth()->id()
        ]);

        Log::info("Created invoice {$invoice->invoice_number} for customer {$customer->name} with amount ৳{$totalAmount}");

        return $invoice;
    }

    /**
     * Generate billing notes based on billing cycles
     */
    private function generateBillingNotes($customer, Carbon $monthDate)
    {
        $notes = [];
        
        foreach (($customer->package_details ?? []) as $package) {
            $cycleText = $this->getBillingCycleText($package['billing_cycle_months']);
            $notes[] = "{$cycleText} billing for {$package['billing_cycle_months']} month(s)";
        }
        
        $baseNote = 'Auto-generated: ' . implode(', ', $notes) . ' - Due for ' . $monthDate->format('F Y');
        
        // Add previous due info if any
        $previousDue = Invoice::where('c_id', $customer->c_id)
            ->where('status', '!=', 'paid')
            ->where('next_due', '>', 0)
            ->sum('next_due');
            
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
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $lastInvoice = Invoice::whereYear('created_at', $year)->latest('invoice_id')->first();

        if ($lastInvoice && preg_match('/INV-\d{4}-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $year . '-' . $newNumber;
    }

    /**
     * Get available billing months based on customer assignment dates and billing cycles
     */
    private function getAvailableBillingMonths()
    {
        $months = collect();
        
        // Get the earliest customer package assignment date using DB query
        $earliestAssignment = DB::table('customer_to_packages')
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
                'fixed_monthly_charge' => isset($settings['fixed_monthly_charge']) ? floatval($settings['fixed_monthly_charge']) : 50.00,
                'vat_percentage' => isset($settings['vat_percentage']) ? floatval($settings['vat_percentage']) : 5.00
            ];
        } catch (\Exception $e) {
            Log::warning('Could not fetch system settings: ' . $e->getMessage());
            return [
                'fixed_monthly_charge' => 50.00,
                'vat_percentage' => 5.00
            ];
        }
    }

    /**
     * Send payment reminder
     */
    public function sendReminder(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,invoice_id',
            'reminder_type' => 'required|in:payment_due,overdue,friendly',
            'message' => 'required|string'
        ]);

        try {
            $invoice = Invoice::with('customer')->findOrFail($request->invoice_id);

            // In a real application, you would send email/SMS here
            // For now, we'll just log it and return success
            Log::info('Payment reminder sent', [
                'invoice_id' => $invoice->invoice_id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->c_id,
                'customer_name' => $invoice->customer->name,
                'customer_email' => $invoice->customer->email,
                'reminder_type' => $request->reminder_type,
                'due_amount' => $invoice->next_due,
                'message' => $request->message
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder sent successfully to ' . $invoice->customer->name
            ]);

        } catch (\Exception $e) {
            Log::error('Send reminder error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder: ' . $e->getMessage()
            ], 500);
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
                'customer.customerPackages.package'
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
     * Record payment for monthly bill - WORKING VERSION
     */
    public function recordPayment(Request $request, $invoiceId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bkash,bank,card,nagad,rocket',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get invoice with customer
            $invoice = Invoice::with('customer')->findOrFail($invoiceId);
            $amount = $request->amount;
            $dueAmount = $invoice->next_due ?? $invoice->total_amount;

            // Validate amount
            if ($amount > $dueAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount (৳' . number_format($amount, 2) . ') cannot exceed due amount (৳' . number_format($dueAmount, 2) . ')'
                ], 422);
            }

            // Create payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->invoice_id,
                'c_id' => $invoice->c_id,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'notes' => $request->note,
                'collected_by' => auth()->id(),
                'status' => 'completed',
            ]);

            // Update invoice
            $newReceivedAmount = $invoice->received_amount + $amount;
            $newDueAmount = max(0, $invoice->total_amount - $newReceivedAmount);

            // Determine new status
            if ($newDueAmount <= 0) {
                $status = 'paid';
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
                'message' => 'Payment of ৳' . number_format($amount, 2) . ' recorded successfully!',
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
     * Get invoice data for payment modal
     */
    public function getInvoiceData($invoiceId)
    {
        try {
            $invoice = Invoice::with('customer')
                ->where('invoice_id', $invoiceId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'invoice' => [
                    'invoice_id' => $invoice->invoice_id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'next_due' => $invoice->next_due,
                    'received_amount' => $invoice->received_amount,
                    'status' => $invoice->status,
                    'customer' => [
                        'name' => $invoice->customer->name ?? 'N/A',
                        'email' => $invoice->customer->email ?? 'N/A',
                        'phone' => $invoice->customer->phone ?? 'N/A',
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
     * Get customer packages for a specific month
     */
    public function getCustomerPackages($customerId, $month)
    {
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            
            $customer = Customer::with([
                'customerPackages' => function($query) use ($monthDate) {
                    $query->where('status', 'active')
                          ->where('is_active', true)
                          ->with('package');
                }
            ])->findOrFail($customerId);

            return response()->json([
                'success' => true,
                'customer' => $customer->name,
                'packages' => $customer->customerPackages->map(function($cp) {
                    return [
                        'package_name' => $cp->package->name,
                        'monthly_price' => $cp->package->monthly_price,
                        'billing_cycle' => $cp->billing_cycle_months,
                        'total_amount' => $cp->package->monthly_price * $cp->billing_cycle_months
                    ];
                }),
                'total_monthly' => $customer->customerPackages->sum(function($cp) {
                    return $cp->package->monthly_price;
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Get customer packages error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading customer packages'
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
                case 'bulk_send_reminders':
                    return $this->bulkSendReminders($invoiceIds, $monthDate);
                    
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
     * Bulk send payment reminders
     */
    private function bulkSendReminders($invoiceIds, Carbon $monthDate)
    {
        try {
            $invoices = Invoice::with('customer')
                ->whereIn('invoice_id', $invoiceIds)
                ->where('status', '!=', 'paid')
                ->get();

            $sentCount = 0;
            $errors = [];

            foreach ($invoices as $invoice) {
                try {
                    // Simulate sending reminder (implement your email/SMS logic here)
                    Log::info('Bulk reminder sent', [
                        'invoice_id' => $invoice->invoice_id,
                        'customer' => $invoice->customer->name,
                        'email' => $invoice->customer->email,
                        'due_amount' => $invoice->next_due
                    ]);
                    
                    $sentCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to send reminder for {$invoice->invoice_number}: " . $e->getMessage();
                }
            }

            $message = "Sent {$sentCount} payment reminders";
            if (!empty($errors)) {
                $message .= " (" . count($errors) . " failed)";
                return redirect()->back()->with('warning', $message)->with('warnings', $errors);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            throw new \Exception("Bulk send reminders failed: " . $e->getMessage());
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

}