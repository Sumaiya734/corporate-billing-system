<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Http\Controllers\Admin\MonthlyBillController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== DEBUGGING NEXT DUE DISPLAY ISSUE ===\n\n";

try {
    // Get the specific invoice from the screenshot (INV-25-03-0001)
    $invoice = Invoice::where('invoice_number', 'INV-25-03-0001')->first();
    
    if (!$invoice) {
        echo "❌ Invoice INV-25-03-0001 not found\n";
        // Get any invoice for testing
        $invoice = Invoice::first();
        if (!$invoice) {
            echo "❌ No invoices found in database\n";
            exit;
        }
    }
    
    echo "=== DIRECT DATABASE QUERY ===\n";
    echo "Invoice: {$invoice->invoice_number}\n";
    echo "   Database next_due: ৳{$invoice->next_due}\n";
    echo "   Database total_amount: ৳{$invoice->total_amount}\n";
    echo "   Database received_amount: ৳{$invoice->received_amount}\n";
    echo "   Database status: {$invoice->status}\n\n";
    
    // Check raw database value
    $rawInvoice = DB::table('invoices')
        ->where('invoice_number', $invoice->invoice_number)
        ->first();
    
    echo "=== RAW DATABASE QUERY ===\n";
    echo "   Raw next_due: ৳{$rawInvoice->next_due}\n";
    echo "   Raw total_amount: ৳{$rawInvoice->total_amount}\n";
    echo "   Raw received_amount: ৳{$rawInvoice->received_amount}\n";
    echo "   Raw status: {$rawInvoice->status}\n\n";
    
    // Test what the controller returns
    $currentMonth = '2025-03'; // March 2025 from screenshot
    
    echo "=== CONTROLLER TEST (March 2025) ===\n";
    
    // Get invoices the same way the controller does
    $controllerInvoices = Invoice::with([
        'payments', 
        'customerProduct.product', 
        'customerProduct.customer'
    ])
    ->whereHas('customerProduct', function($q) {
        $monthDate = Carbon::createFromFormat('Y-m', '2025-03');
        $q->where('status', 'active')
          ->where('is_active', 1)
          ->where('assign_date', '<=', $monthDate->endOfMonth());
    })
    ->where('is_active_rolling', 1)
    ->where('issue_date', '<=', Carbon::createFromFormat('Y-m', '2025-03')->endOfMonth())
    ->get();
    
    echo "Found " . $controllerInvoices->count() . " invoices from controller query\n";
    
    $testInvoice = $controllerInvoices->where('invoice_number', $invoice->invoice_number)->first();
    
    if ($testInvoice) {
        echo "Controller invoice data:\n";
        echo "   next_due: ৳{$testInvoice->next_due}\n";
        echo "   total_amount: ৳{$testInvoice->total_amount}\n";
        echo "   received_amount: ৳{$testInvoice->received_amount}\n";
        echo "   status: {$testInvoice->status}\n\n";
    }
    
    // Test the transformation
    $controller = new MonthlyBillController();
    $reflection = new ReflectionClass($controller);
    $transformMethod = $reflection->getMethod('transformInvoicesForMonth');
    $transformMethod->setAccessible(true);
    
    // Create a collection with just our test invoice
    $testCollection = collect([$testInvoice]);
    $transformedCollection = $transformMethod->invoke($controller, $testCollection, $currentMonth);
    $transformedInvoice = $transformedCollection->first();
    
    echo "=== AFTER TRANSFORMATION ===\n";
    echo "   Transformed next_due: ৳{$transformedInvoice->next_due}\n";
    echo "   Transformed total_amount: ৳{$transformedInvoice->total_amount}\n";
    echo "   Transformed received_amount: ৳{$transformedInvoice->received_amount}\n";
    echo "   Transformed status: {$transformedInvoice->status}\n\n";
    
    // Check if March 2025 is considered current month
    $currentSystemMonth = Carbon::now()->format('Y-m');
    echo "=== MONTH COMPARISON ===\n";
    echo "   System current month: {$currentSystemMonth}\n";
    echo "   Viewing month: {$currentMonth}\n";
    echo "   Is current month? " . ($currentMonth === $currentSystemMonth ? 'YES' : 'NO') . "\n\n";
    
    if ($currentMonth !== $currentSystemMonth) {
        echo "⚠️  ISSUE FOUND: March 2025 is NOT the current month!\n";
        echo "   The transformation is being applied because it's a past month.\n";
        echo "   This explains why database values are being overridden.\n\n";
    }
    
    // Show what the view would display
    echo "=== WHAT THE VIEW DISPLAYS ===\n";
    $nextDue = $transformedInvoice->next_due ?? 0;
    $totalAmount = $transformedInvoice->total_amount ?? 0;
    $receivedAmount = $transformedInvoice->received_amount ?? 0;
    
    $isFullyPaid = ($receivedAmount >= $totalAmount && $totalAmount > 0) ||
                   ($nextDue <= 0.00 && $transformedInvoice->status === 'paid') ||
                   ($nextDue <= 0.00 && $receivedAmount > 0);
    
    $isAdvancePayment = $receivedAmount > $totalAmount && $totalAmount > 0;
    
    echo "   Display logic:\n";
    echo "   - nextDue: ৳{$nextDue}\n";
    echo "   - isFullyPaid: " . ($isFullyPaid ? 'true' : 'false') . "\n";
    echo "   - isAdvancePayment: " . ($isAdvancePayment ? 'true' : 'false') . "\n";
    
    if ($isAdvancePayment) {
        echo "   → Would show: Advance Paid\n";
    } elseif ($isFullyPaid) {
        echo "   → Would show: Paid\n";
    } elseif ($nextDue > 0) {
        echo "   → Would show: ৳" . number_format($nextDue, 0) . "\n";
    } else {
        echo "   → Would show: Paid\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";