<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

echo "=== TESTING PAYMENT FORM VALUE STORAGE ===\n\n";

try {
    // Get a sample invoice to test with
    $invoice = Invoice::where('status', '!=', 'paid')->first();
    
    if (!$invoice) {
        echo "❌ No unpaid invoices found for testing\n";
        exit;
    }
    
    echo "✅ Testing with invoice: {$invoice->invoice_number}\n";
    echo "   Current Total: ৳{$invoice->total_amount}\n";
    echo "   Current Received: ৳{$invoice->received_amount}\n";
    echo "   Current Next Due: ৳{$invoice->next_due}\n\n";
    
    // Simulate form data where user pays ₹500 and manually sets remaining to ₹1500
    $paymentAmount = 500;
    $manualRemainingAmount = 1500; // User manually entered this in the form
    
    echo "=== SIMULATING PAYMENT FORM SUBMISSION ===\n";
    echo "   Payment Amount: ৳{$paymentAmount}\n";
    echo "   Manual Remaining Amount (from form): ৳{$manualRemainingAmount}\n\n";
    
    // Calculate what the system would normally calculate
    $systemCalculatedRemaining = max(0, $invoice->total_amount - ($invoice->received_amount + $paymentAmount));
    echo "   System would calculate remaining as: ৳{$systemCalculatedRemaining}\n";
    echo "   But we want to use the manual value: ৳{$manualRemainingAmount}\n\n";
    
    // Create a test payment record
    $payment = Payment::create([
        'invoice_id' => $invoice->invoice_id,
        'c_id' => $invoice->customerProduct->c_id,
        'amount' => $paymentAmount,
        'payment_method' => 'cash',
        'payment_date' => now()->format('Y-m-d'),
        'notes' => 'Test payment - using manual remaining amount',
        'collected_by' => 1,
        'status' => 'completed',
    ]);
    
    echo "✅ Created test payment: ID {$payment->payment_id}\n";
    
    // Update invoice using the manual remaining amount (simulating the new logic)
    $newReceivedAmount = $invoice->received_amount + $paymentAmount;
    $newDueAmount = $manualRemainingAmount; // Use manual value instead of calculation
    
    $status = 'partial';
    if ($newDueAmount <= 0) {
        $status = 'paid';
        $newDueAmount = 0;
    } elseif ($newReceivedAmount > 0) {
        $status = 'partial';
    } else {
        $status = 'unpaid';
    }
    
    $invoice->update([
        'received_amount' => $newReceivedAmount,
        'next_due' => $newDueAmount, // This should be the manual value
        'status' => $status
    ]);
    
    echo "✅ Updated invoice with manual remaining amount\n\n";
    
    // Verify the update
    $invoice->refresh();
    echo "=== VERIFICATION ===\n";
    echo "   New Total: ৳{$invoice->total_amount}\n";
    echo "   New Received: ৳{$invoice->received_amount}\n";
    echo "   New Next Due: ৳{$invoice->next_due}\n";
    echo "   New Status: {$invoice->status}\n\n";
    
    if ($invoice->next_due == $manualRemainingAmount) {
        echo "✅ SUCCESS: Manual remaining amount (৳{$manualRemainingAmount}) was stored correctly\n";
    } else {
        echo "❌ FAILED: Expected ৳{$manualRemainingAmount}, got ৳{$invoice->next_due}\n";
    }
    
    // Clean up - delete the test payment
    $payment->delete();
    
    // Restore original invoice state
    $invoice->update([
        'received_amount' => $invoice->received_amount - $paymentAmount,
        'next_due' => $invoice->total_amount - ($invoice->received_amount - $paymentAmount),
        'status' => ($invoice->received_amount - $paymentAmount) > 0 ? 'partial' : 'unpaid'
    ]);
    
    echo "✅ Cleaned up test data and restored original invoice state\n";
    
} catch (\Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";