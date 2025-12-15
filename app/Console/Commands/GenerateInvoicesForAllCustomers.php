<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\CustomerProduct;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateInvoicesForAllCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-all {--month= : Specific month to generate invoices for (YYYY-MM format)} {--force : Force generation even if invoices already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate invoices with unique invoice numbers for all customers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $month = $this->option('month') ?? date('Y-m');
        $force = $this->option('force');
        
        $this->info("Generating invoices for all customers for month: {$month}");
        
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $displayMonth = $monthDate->format('F Y');
        } catch (\Exception $e) {
            $this->error("Invalid month format. Please use YYYY-MM format.");
            return 1;
        }
        
        // Get all active customers with active products
        $customers = $this->getAllActiveCustomersWithProducts($monthDate);
        
        if ($customers->isEmpty()) {
            $this->info("No active customers with products found for {$displayMonth}.");
            return 0;
        }
        
        $this->info("Found {$customers->count()} active customers with products.");
        
        $generatedCount = 0;
        $skippedCount = 0;
        
        foreach ($customers as $customer) {
            try {
                // If force flag is set, delete ALL existing invoices for this customer in this month
                if ($force) {
                    $existingInvoices = Invoice::whereHas('customerProduct', function($query) use ($customer) {
                        $query->where('c_id', $customer->c_id);
                    })
                    ->whereYear('issue_date', $monthDate->year)
                    ->whereMonth('issue_date', $monthDate->month)
                    ->get();
                    
                    if ($existingInvoices->count() > 0) {
                        $this->line("Deleting {$existingInvoices->count()} existing invoice(s) for customer {$customer->name}");
                        foreach ($existingInvoices as $inv) {
                            $inv->delete();
                        }
                    }
                }
                
                // Create invoices (one per product) - returns count of invoices created
                $invoicesCreated = $this->createCustomerInvoice($customer, $monthDate);
                
                if ($invoicesCreated > 0) {
                    $this->line("Generated {$invoicesCreated} invoice(s) for customer {$customer->name} ({$invoicesCreated} products)");
                    $generatedCount += $invoicesCreated;
                } else {
                    $this->line("Skipped customer {$customer->name} - all product invoices already exist");
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to generate invoices for customer {$customer->c_id}: " . $e->getMessage());
                Log::error("Invoice generation failed for customer {$customer->c_id}: " . $e->getMessage());
            }
        }
        
        $this->info("Invoice generation complete!");
        $this->info("Generated: {$generatedCount} invoices");
        $this->info("Skipped: {$skippedCount} customers (already had invoices)");
        
        return 0;
    }
    
    /**
     * Get all active customers with active products
     */
    private function getAllActiveCustomersWithProducts(Carbon $monthDate)
    {
        return Customer::with(['customerproducts.product'])
            ->where('is_active', 1)
            ->whereHas('customerproducts', function ($query) use ($monthDate) {
                $query->where('status', 'active')
                    ->where('is_active', 1)
                    ->where('assign_date', '<=', $monthDate->endOfMonth());
            })
            ->get();
    }
    
    /**
     * Create separate invoices for each product of a customer
     * Returns the count of invoices created
     */
    private function createCustomerInvoice($customer, Carbon $monthDate)
    {
        try {
            $invoicesCreated = 0;
            
            // Create separate invoice for each active product
            foreach ($customer->customerproducts as $customerProduct) {
                if ($customerProduct->isActive() && 
                    Carbon::parse($customerProduct->assign_date)->lessThanOrEqualTo($monthDate->endOfMonth())) {
                    
                    // Check if invoice already exists for this product and month
                    $existingInvoice = Invoice::where('cp_id', $customerProduct->cp_id)
                        ->whereYear('issue_date', $monthDate->year)
                        ->whereMonth('issue_date', $monthDate->month)
                        ->first();
                    
                    if ($existingInvoice) {
                        $this->line("  → Skipping {$customerProduct->product->name} - invoice already exists ({$existingInvoice->invoice_number})");
                        continue; // Skip if invoice already exists for this product
                    }
                    
                    // Use custom price if set, otherwise use product's monthly price
                    // ONLY use custom_price - no calculated price or fallback logic
                    if ($customerProduct->custom_price !== null && $customerProduct->custom_price > 0) {
                        $productAmount = (float) $customerProduct->custom_price;
                    }
                    // If no custom price is set, productAmount remains 0 (no fallback to calculated price)
                    
                    // Get previous due amount from unpaid invoices for THIS SPECIFIC PRODUCT
                    $previousDue = Invoice::where('cp_id', $customerProduct->cp_id)
                        ->where('status', '!=', 'paid')
                        ->where('next_due', '>', 0)
                        ->sum('next_due');
                    
                    $totalAmount = $productAmount + $previousDue;
                    
                    // Generate notes
                    $notes = 'Auto-generated invoice for ' . $monthDate->format('F Y');
                    $notes .= ' - Product: ' . $customerProduct->product->name;
                    $notes .= ' (' . $this->getBillingCycleText($customerProduct->billing_cycle_months) . ')';
                    if ($previousDue > 0) {
                        $notes .= " (Includes ৳" . number_format($previousDue, 2) . " previous due)";
                    }
                    
                    // Create the invoice for this specific product
                    $invoice = Invoice::create([
                        'cp_id' => $customerProduct->cp_id,
                        'issue_date' => $monthDate->format('Y-m-d'),
                        'previous_due' => $previousDue,
                        'subtotal' => $productAmount,
                        'total_amount' => $totalAmount,
                        'received_amount' => 0,
                        'next_due' => $totalAmount,
                        'status' => 'unpaid',
                        'notes' => $notes,
                        'created_by' => 1 // System generated
                    ]);
                    
                    $invoicesCreated++;
                    
                    $this->line("  → Created invoice {$invoice->invoice_number} for {$customerProduct->product->name} (৳{$totalAmount})");
                    Log::info("Auto-generated invoice {$invoice->invoice_number} for customer {$customer->name} - Product: {$customerProduct->product->name} with amount ৳{$totalAmount}");
                }
            }
            
            return $invoicesCreated; // Return count of invoices created
        } catch (\Exception $e) {
            Log::error('Failed to create invoices for customer ' . $customer->c_id . ': ' . $e->getMessage());
            return 0;
        }
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
}