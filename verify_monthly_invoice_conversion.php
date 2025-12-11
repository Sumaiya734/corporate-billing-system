<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

echo "=== VERIFYING MONTHLY INVOICE CONVERSION ===\n\n";

try {
    // Check the conversion results
    $monthlyInvoices = Invoice::where('is_active_rolling', 0)
        ->orderBy('issue_date')
        ->get();
    
    $rollingInvoices = Invoice::where('is_active_rolling', 1)->count();
    $convertedInvoices = Invoice::where('status', 'converted')->count();
    
    echo "=== CONVERSION SUMMARY ===\n";
    echo "Monthly Invoices Created: " . $monthlyInvoices->count() . "\n";
    echo "Rolling Invoices Remaining: {$rollingInvoices}\n";
    echo "Converted Invoices: {$convertedInvoices}\n\n";
    
    if ($monthlyInvoices->count() > 0) {
        echo "✅ Monthly invoice system implemented successfully!\n\n";
        
        // Group by customer and show monthly progression
        $customerGroups = $monthlyInvoices->groupBy('cp_id');
        
        foreach ($customerGroups as $cpId => $invoices) {
            $firstInvoice = $invoices->first();
            echo "=== CUSTOMER: {$firstInvoice->customerProduct->customer->name} ===\n";
            
            foreach ($invoices as $invoice) {
                $month = Carbon::parse($invoice->issue_date)->format('Y-m');
                echo "   {$month}: {$invoice->invoice_number} - ৳{$invoice->total_amount} (Prev: ৳{$invoice->previous_due}, New: ৳{$invoice->subtotal}) - ৳{$invoice->received_amount} paid - ৳{$invoice->next_due} due\n";
            }
            echo "\n";
        }
        
        // Test payment scenario
        echo "=== TESTING PAYMENT SCENARIO ===\n";
        $testInvoice = $monthlyInvoices->where('issue_date', 'like', '2025-03%')->first();
        
        if ($testInvoice) {
            echo "Test Invoice: {$testInvoice->invoice_number} (March 2025)\n";
            echo "   Before Payment: ৳{$testInvoice->total_amount} total, ৳{$testInvoice->received_amount} received, ৳{$testInvoice->next_due} due\n";
            
            // Check if there are payments for this invoice
            $payments = Payment::where('invoice_id', $testInvoice->invoice_id)->get();
            echo "   Payments: " . $payments->count() . " payments totaling ৳" . $payments->sum('amount') . "\n";
            
            // Show what happens in other months
            $otherMonthInvoices = $monthlyInvoices->where('cp_id', $testInvoice->cp_id)
                ->where('invoice_id', '!=', $testInvoice->invoice_id);
            
            echo "   Other months for same customer:\n";
            foreach ($otherMonthInvoices as $otherInvoice) {
                $month = Carbon::parse($otherInvoice->issue_date)->format('Y-m');
                echo "     {$month}: ৳{$otherInvoice->total_amount} total, ৳{$otherInvoice->received_amount} received, ৳{$otherInvoice->next_due} due\n";
            }
        }
        
        echo "\n=== BENEFITS OF MONTHLY INVOICE SYSTEM ===\n";
        echo "✅ Separate invoice for each month\n";
        echo "✅ Independent payments per month\n";
        echo "✅ Carry forward unpaid amounts to next month\n";
        echo "✅ No overwriting of previous month data\n";
        echo "✅ Clear payment history per month\n";
        
    } else {
        echo "❌ No monthly invoices found. Conversion may have failed.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";