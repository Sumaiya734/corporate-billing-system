<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Http\Controllers\Admin\MonthlyBillController;
use Carbon\Carbon;

echo "=== TESTING MONTHLY BILLS DISPLAY ===\n\n";

try {
    $currentMonth = Carbon::now()->format('Y-m');
    echo "Testing for current month: {$currentMonth}\n\n";
    
    // Get a sample invoice
    $invoice = Invoice::with(['customerProduct'])->first();
    
    if (!$invoice) {
        echo "❌ No invoices found for testing\n";
        exit;
    }
    
    echo "=== ORIGINAL INVOICE DATA (from database) ===\n";
    echo "Invoice: {$invoice->invoice_number}\n";
    echo "   Total Amount: ৳{$invoice->total_amount}\n";
    echo "   Received Amount: ৳{$invoice->received_amount}\n";
    echo "   Next Due: ৳{$invoice->next_due}\n";
    echo "   Status: {$invoice->status}\n\n";
    
    // Test the transformation method
    $controller = new MonthlyBillController();
    $reflection = new ReflectionClass($controller);
    $transformMethod = $reflection->getMethod('transformSingleInvoice');
    $transformMethod->setAccessible(true);
    
    // Test transformation for current month
    $transformedInvoice = $transformMethod->invoke($controller, $invoice, $currentMonth);
    
    echo "=== TRANSFORMED INVOICE DATA (for current month) ===\n";
    echo "Invoice: {$transformedInvoice->invoice_number}\n";
    echo "   Total Amount: ৳{$transformedInvoice->total_amount}\n";
    echo "   Received Amount: ৳{$transformedInvoice->received_amount}\n";
    echo "   Next Due: ৳{$transformedInvoice->next_due}\n";
    echo "   Status: {$transformedInvoice->status}\n\n";
    
    // Check if values are the same (they should be for current month)
    $valuesMatch = (
        $invoice->total_amount == $transformedInvoice->total_amount &&
        $invoice->received_amount == $transformedInvoice->received_amount &&
        $invoice->next_due == $transformedInvoice->next_due &&
        $invoice->status == $transformedInvoice->status
    );
    
    if ($valuesMatch) {
        echo "✅ SUCCESS: Current month shows actual database values (no transformation)\n";
    } else {
        echo "❌ FAILED: Current month values were transformed (should use database values)\n";
        echo "   Expected Next Due: ৳{$invoice->next_due}\n";
        echo "   Got Next Due: ৳{$transformedInvoice->next_due}\n";
    }
    
    // Test transformation for a past month
    $pastMonth = Carbon::now()->subMonth()->format('Y-m');
    echo "\n=== TESTING PAST MONTH TRANSFORMATION ({$pastMonth}) ===\n";
    
    $pastTransformedInvoice = $transformMethod->invoke($controller, $invoice, $pastMonth);
    
    echo "Past month transformed Next Due: ৳{$pastTransformedInvoice->next_due}\n";
    echo "Current database Next Due: ৳{$invoice->next_due}\n";
    
    if ($pastTransformedInvoice->next_due != $invoice->next_due) {
        echo "✅ SUCCESS: Past month values are transformed (historical view)\n";
    } else {
        echo "⚠️  INFO: Past month values same as current (may be correct)\n";
    }
    
    echo "\n=== TESTING COMPLETE ===\n";
    echo "The monthly-bills page should now show:\n";
    echo "- Current month: Actual database values (including manual next_due from payments)\n";
    echo "- Past months: Transformed historical values\n";
    
} catch (\Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";