<?php

namespace App\Services;

use App\Models\CustomerProduct;
use App\Models\Invoice;
use Carbon\Carbon;

class BillingCalculationService
{
    public function getMonthlyBillingSummary($month = null)
    {
        if (!$month) {
            $month = date('Y-m');
        }
        
        $targetDate = Carbon::createFromFormat('Y-m', $month);
        $displayMonth = $targetDate->format('F Y');
        
        $dueCustomerProducts = $this->getCustomerProductsDueInMonth($month);
        
        $totalAmount = 0;
        $receivedAmount = 0;
        $customerIds = [];
        
        foreach ($dueCustomerProducts as $customerProduct) {
            $customerId = $customerProduct->c_id;
            $productAmount = $customerProduct->product_price;
            
            // Add to total amount
            $totalAmount += $productAmount;
            
            // Check if invoice exists and is paid for this customer in this month
            $invoice = Invoice::where('customer_id', $customerId)
                ->where('billing_month', 'LIKE', "%{$displayMonth}%")
                ->first();
                
            if ($invoice) {
                if ($invoice->status === 'paid') {
                    $receivedAmount += $invoice->received_amount;
                }
            }
            
            // Track unique customers
            if (!in_array($customerId, $customerIds)) {
                $customerIds[] = $customerId;
            }
        }
        
        $dueAmount = $totalAmount - $receivedAmount;
        $totalCustomers = count($customerIds);
        
        return [
            'display_month' => $displayMonth,
            'billing_month' => $month,
            'total_customers' => $totalCustomers,
            'total_amount' => $totalAmount,
            'received_amount' => $receivedAmount,
            'due_amount' => $dueAmount,
            'status' => $this->calculateStatus($dueAmount, $receivedAmount, $totalAmount),
            'is_locked' => $this->isMonthLocked($month),
            'is_current_month' => $month === date('Y-m'),
            'due_customers_count' => $totalCustomers
        ];
    }
    
    private function getCustomerProductsDueInMonth($month)
    {
        $targetDate = Carbon::createFromFormat('Y-m', $month);
        
        return CustomerProduct::with(['customer', 'product'])
            ->where('status', 'active')
            ->where('is_active', 1)
            ->get()
            ->filter(function ($customerProduct) use ($targetDate) {
                return $this->isCustomerProductDueInMonth($customerProduct, $targetDate);
            });
    }
    
    private function isCustomerProductDueInMonth($customerProduct, $targetDate)
    {
        $billingCycle = $customerProduct->billing_cycle_months;
        $startDate = Carbon::parse($customerProduct->assign_date);
        
        // If billing cycle is 1, customer pays every month
        if ($billingCycle == 1) {
            return true;
        }
        
        // For customers with billing cycles > 1, calculate if this is their billing month
        $monthsDiff = $targetDate->diffInMonths($startDate);
        
        // Customer is due if the months difference is divisible by billing cycle
        // Also ensure we're not in a month before their start date
        return $monthsDiff >= 0 && $monthsDiff % $billingCycle === 0;
    }
    
    private function calculateStatus($dueAmount, $receivedAmount, $totalAmount)
    {
        if ($totalAmount == 0) {
            return 'All Paid';
        }
        
        if ($dueAmount <= 0) {
            return 'All Paid';
        } elseif ($receivedAmount / $totalAmount >= 0.8) {
            return 'Pending';
        } else {
            return 'Overdue';
        }
    }
    
    private function isMonthLocked($month)
    {
        // Lock months that are more than 3 months in the past
        return Carbon::createFromFormat('Y-m', $month)->lt(Carbon::now()->subMonths(3));
    }
    
    // Method to get customers due in specific month for monthly bills page
    public function getCustomersDueInMonth($month)
    {
        $targetDate = Carbon::createFromFormat('Y-m', $month);
        $displayMonth = $targetDate->format('F Y');
        
        $dueCustomerProducts = $this->getCustomerProductsDueInMonth($month);
        
        $customersData = [];
        $customerMap = [];
        
        foreach ($dueCustomerProducts as $customerProduct) {
            $customerId = $customerProduct->c_id;
            
            if (!isset($customerMap[$customerId])) {
                $customerMap[$customerId] = [
                    'customer' => $customerProduct->customer,
                    'products' => [],
                    'total_amount' => 0,
                    'invoice' => null
                ];
            }
            
            // Add product to customer
            $customerMap[$customerId]['products'][] = [
                'product_name' => $customerProduct->product->name ?? 'Unknown Product',
                'product_price' => $customerProduct->product_price,
                'billing_cycle' => $customerProduct->billing_cycle_months
            ];
            
            $customerMap[$customerId]['total_amount'] += $customerProduct->product_price;
            
            // Get invoice for this customer in this month
            if (!$customerMap[$customerId]['invoice']) {
                $invoice = Invoice::where('customer_id', $customerId)
                    ->where('billing_month', 'LIKE', "%{$displayMonth}%")
                    ->first();
                    
                $customerMap[$customerId]['invoice'] = $invoice;
            }
        }
        
        return collect($customerMap)->values();
    }
    
    // Method to generate invoices for a specific month
    public function generateMonthInvoices($month)
    {
        $targetDate = Carbon::createFromFormat('Y-m', $month);
        $displayMonth = $targetDate->format('F Y');
        
        $dueCustomers = $this->getCustomersDueInMonth($month);
        $generatedInvoices = [];
        
        foreach ($dueCustomers as $customerData) {
            $customer = $customerData['customer'];
            $totalAmount = $customerData['total_amount'];
            
            // Check if invoice already exists
            $existingInvoice = Invoice::where('customer_id', $customer->c_id)
                ->where('billing_month', 'LIKE', "%{$displayMonth}%")
                ->first();
                
            if (!$existingInvoice) {
                // Create new invoice
                $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
                
                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $customer->c_id,
                    'issue_date' => $targetDate->copy()->startOfMonth(),
                    'due_date' => $targetDate->copy()->startOfMonth()->addDays(10),
                    'billing_month' => $displayMonth,
                    'previous_due' => 0,
                    'service_charge' => 50, // From your system settings
                    'vat_amount' => $totalAmount * 0.07, // 7% VAT
                    'subtotal' => $totalAmount,
                    'total_amount' => $totalAmount + 50 + ($totalAmount * 0.07),
                    'received_amount' => 0,
                    'next_due' => 0,
                    'status' => 'unpaid',
                    'created_by' => 1
                ]);
                
                $generatedInvoices[] = $invoice;
            }
        }
        
        return $generatedInvoices;
    }
    
    // Method to get available months that have invoices
    public function getAvailableMonthsFromInvoices()
    {
        return Invoice::selectRaw('DISTINCT billing_month')
            ->whereNotNull('billing_month')
            ->orderByRaw("STR_TO_DATE(CONCAT('01 ', billing_month), '%d %M %Y') DESC")
            ->pluck('billing_month')
            ->map(function ($month) {
                try {
                    $date = Carbon::createFromFormat('F Y', $month);
                    return [
                        'value' => $date->format('Y-m'),
                        'display' => $month
                    ];
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter()
            ->values();
    }
}