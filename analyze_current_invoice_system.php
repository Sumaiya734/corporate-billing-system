<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Models\CustomerProduct;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== ANALYZING CURRENT INVOICE SYSTEM ===\n\n";

try {
    // Check how many invoices exist per customer
    $customerProducts = CustomerProduct::where('status', 'active')
        ->where('is_active', 1)
        ->with(['customer', 'product'])
        ->limit(3)
        ->get();
    
    foreach ($customerProducts as $cp) {
        echo "=== CUSTOMER: {$cp->customer->name} ===\n";
        echo "Product: {$cp->product->name}\n";
        echo "Assign Date: {$cp->assign_date}\n";
        echo "Billing Cycle: {$cp->billing_cycle_months} months\n\n";
        
        // Get all invoices for this customer product
        $invoices = Invoice::where('cp_id', $cp->cp_id)
            ->orderBy('issue_date')
            ->get();
        
        echo "Total Invoices: " . $invoices->count() . "\n";
        
        if ($invoices->count() > 0) {
            echo "Invoice Details:\n";
            foreach ($invoices as $invoice) {
                echo "   {$invoice->invoice_number} - {$invoice->issue_date->format('Y-m')} - ৳{$invoice->total_amount} - ৳{$invoice->received_amount} - ৳{$invoice->next_due} - {$invoice->status}\n";
            }
        }
        
        // Check if using rolling invoice system
        $rollingInvoices = $invoices->where('is_active_rolling', 1);
        echo "Rolling Invoices: " . $rollingInvoices->count() . "\n";
        
        if ($rollingInvoices->count() > 0) {
            echo "⚠️  ISSUE: Using rolling invoice system (single invoice for all months)\n";
        }
        
        echo "\n" . str_repeat("-", 50) . "\n\n";
    }
    
    echo "=== CURRENT SYSTEM ANALYSIS ===\n";
    
    // Check if the system is using rolling invoices
    $totalInvoices = Invoice::count();
    $rollingInvoices = Invoice::where('is_active_rolling', 1)->count();
    $monthlyInvoices = Invoice::where('is_active_rolling', 0)->count();
    
    echo "Total Invoices: {$totalInvoices}\n";
    echo "Rolling Invoices: {$rollingInvoices}\n";
    echo "Monthly Invoices: {$monthlyInvoices}\n\n";
    
    if ($rollingInvoices > 0) {
        echo "❌ PROBLEM IDENTIFIED: System is using rolling invoices\n";
        echo "   - One invoice per customer that gets updated each month\n";
        echo "   - Payments in one month affect all months\n";
        echo "   - No separate monthly payment tracking\n\n";
        
        echo "✅ SOLUTION NEEDED: Switch to monthly invoice system\n";
        echo "   - Separate invoice for each month\n";
        echo "   - Carry forward unpaid amounts to next month\n";
        echo "   - Independent payments per month\n";
    } else {
        echo "✅ System is using monthly invoices (correct)\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";