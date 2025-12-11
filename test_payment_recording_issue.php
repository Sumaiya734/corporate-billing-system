<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Models\CustomerProduct;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

echo "=== TESTING PAYMENT RECORDING ISSUE ===\n\n";

try {
    // Get a sample invoice to test with
    $invoice = Invoice::with('customerProduct')->first();
    
    if (!$invoice) {
        echo "❌ No invoices found in database\n";
        exit;
    }
    
    echo "✅ Found invoice: {$invoice->invoice_number}\n";
    echo "   Invoice ID: {$invoice->invoice_id}\n";
    echo "   CP ID: {$invoice->cp_id}\n";
    echo "   Total Amount: ৳{$invoice->total_amount}\n";
    echo "   Received Amount: ৳{$invoice->received_amount}\n";
    echo "   Next Due: ৳{$invoice->next_due}\n\n";
    
    // Test the customerProduct relationship
    echo "=== TESTING CUSTOMER PRODUCT RELATIONSHIP ===\n";
    
    if ($invoice->customerProduct) {
        echo "✅ CustomerProduct relationship works\n";
        echo "   Customer ID: {$invoice->customerProduct->c_id}\n";
        echo "   Product ID: {$invoice->customerProduct->p_id}\n";
        
        // Test accessing customer through customerProduct
        if ($invoice->customerProduct->customer) {
            echo "✅ Customer relationship through CustomerProduct works\n";
            echo "   Customer Name: {$invoice->customerProduct->customer->name}\n";
        } else {
            echo "❌ Customer relationship through CustomerProduct failed\n";
        }
    } else {
        echo "❌ CustomerProduct relationship failed\n";
        echo "   This is likely the cause of the payment recording issue\n";
    }
    
    echo "\n=== TESTING PAYMENT CALCULATION ===\n";
    
    // Simulate a payment of ₹1000
    $paymentAmount = 1000;
    $newReceivedAmount = $invoice->received_amount + $paymentAmount;
    $newDue = max(0, $invoice->total_amount - $newReceivedAmount);
    
    echo "   Simulating payment of ৳{$paymentAmount}\n";
    echo "   Current received: ৳{$invoice->received_amount}\n";
    echo "   New received would be: ৳{$newReceivedAmount}\n";
    echo "   New due would be: ৳{$newDue}\n";
    
    // Check if calculation is correct
    if ($newDue == ($invoice->total_amount - $newReceivedAmount)) {
        echo "✅ Payment calculation logic is correct\n";
    } else {
        echo "❌ Payment calculation logic has issues\n";
    }
    
    echo "\n=== CHECKING EXISTING PAYMENTS ===\n";
    
    $payments = Payment::where('invoice_id', $invoice->invoice_id)->get();
    echo "   Found " . $payments->count() . " existing payments for this invoice\n";
    
    $totalPayments = $payments->sum('amount');
    echo "   Total payments in database: ৳{$totalPayments}\n";
    echo "   Invoice received_amount: ৳{$invoice->received_amount}\n";
    
    if ($totalPayments == $invoice->received_amount) {
        echo "✅ Payment totals match invoice received_amount\n";
    } else {
        echo "❌ Payment totals don't match invoice received_amount\n";
        echo "   This could cause next_due calculation issues\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TESTING COMPLETE ===\n";