<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

echo "=== FIXING NEXT_DUE CALCULATION ISSUE ===\n\n";

try {
    // Get all invoices with incorrect next_due calculations
    $invoices = Invoice::whereRaw('next_due != (total_amount - COALESCE(received_amount, 0))')
        ->get();
    
    echo "Found " . $invoices->count() . " invoices with incorrect next_due calculations\n\n";
    
    foreach ($invoices as $invoice) {
        echo "Invoice: {$invoice->invoice_number}\n";
        echo "   Current - Total: ৳{$invoice->total_amount}, Received: ৳{$invoice->received_amount}, Next Due: ৳{$invoice->next_due}\n";
        
        // Calculate correct next_due
        $correctNextDue = max(0, $invoice->total_amount - $invoice->received_amount);
        
        // Determine correct status
        $correctStatus = 'unpaid';
        if ($invoice->received_amount >= $invoice->total_amount) {
            $correctStatus = 'paid';
            $correctNextDue = 0;
        } elseif ($invoice->received_amount > 0) {
            $correctStatus = 'partial';
        }
        
        echo "   Correct - Next Due: ৳{$correctNextDue}, Status: {$correctStatus}\n";
        
        // Update the invoice
        $invoice->update([
            'next_due' => $correctNextDue,
            'status' => $correctStatus
        ]);
        
        echo "   ✅ Updated invoice {$invoice->invoice_number}\n\n";
    }
    
    echo "=== VERIFICATION ===\n";
    
    // Verify the fix worked
    $stillIncorrect = Invoice::whereRaw('next_due != (total_amount - COALESCE(received_amount, 0))')
        ->count();
    
    if ($stillIncorrect == 0) {
        echo "✅ All invoices now have correct next_due calculations\n";
    } else {
        echo "❌ Still have {$stillIncorrect} invoices with incorrect calculations\n";
    }
    
    // Test the specific invoice from our previous test
    $testInvoice = Invoice::find(152);
    if ($testInvoice) {
        echo "\n=== TEST INVOICE VERIFICATION ===\n";
        echo "Invoice {$testInvoice->invoice_number}:\n";
        echo "   Total: ৳{$testInvoice->total_amount}\n";
        echo "   Received: ৳{$testInvoice->received_amount}\n";
        echo "   Next Due: ৳{$testInvoice->next_due}\n";
        echo "   Status: {$testInvoice->status}\n";
        
        $expectedDue = max(0, $testInvoice->total_amount - $testInvoice->received_amount);
        if ($testInvoice->next_due == $expectedDue) {
            echo "✅ Test invoice calculation is now correct\n";
        } else {
            echo "❌ Test invoice calculation is still incorrect\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIX COMPLETE ===\n";