<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Invoice;

echo "=== DEBUGGING CURRENT DISPLAY ISSUE ===\n\n";

echo "1. ACTUAL DATABASE VALUES RIGHT NOW:\n";
$invoices = DB::table('invoices as i')
    ->join('customer_to_products as cp', 'i.cp_id', '=', 'cp.cp_id')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->leftJoin('payments as p', 'i.invoice_id', '=', 'p.invoice_id')
    ->select(
        'i.invoice_number',
        'c.name as customer_name',
        'i.subtotal',
        'i.previous_due',
        'i.total_amount',
        'i.received_amount',
        'i.next_due',
        'i.status',
        DB::raw('COALESCE(SUM(p.amount), 0) as actual_payments_sum')
    )
    ->groupBy('i.invoice_id', 'i.invoice_number', 'c.name', 'i.subtotal', 'i.previous_due', 'i.total_amount', 'i.received_amount', 'i.next_due', 'i.status')
    ->get();

foreach($invoices as $invoice) {
    echo "  {$invoice->invoice_number} ({$invoice->customer_name}):\n";
    echo "    Database Subtotal: ₹{$invoice->subtotal}\n";
    echo "    Database Previous Due: ₹{$invoice->previous_due}\n";
    echo "    Database Total Amount: ₹{$invoice->total_amount}\n";
    echo "    Database Received Amount: ₹{$invoice->received_amount}\n";
    echo "    Database Next Due: ₹{$invoice->next_due}\n";
    echo "    Actual Payments Sum: ₹{$invoice->actual_payments_sum}\n";
    echo "    Status: {$invoice->status}\n";
    
    // Check if calculations are correct
    $expectedNextDue = $invoice->total_amount - $invoice->actual_payments_sum;
    echo "    Expected Next Due: ₹{$expectedNextDue}\n";
    
    if (abs($invoice->next_due - $expectedNextDue) > 0.01) {
        echo "    ❌ DATABASE ISSUE: next_due is wrong in database\n";
    } else {
        echo "    ✅ DATABASE CORRECT: next_due matches calculation\n";
    }
    echo "\n";
}

echo "2. WHAT THE MONTHLY BILLS PAGE CONTROLLER RETURNS:\n";

// Simulate what MonthlyBillController returns for February 2025
$month = '2025-02';
$monthDate = \Carbon\Carbon::createFromFormat('Y-m', $month);

$controllerInvoices = Invoice::with([
    'payments', 
    'customerProduct.product', 
    'customerProduct.customer'
])
->whereHas('customerProduct', function($q) use ($monthDate) {
    $q->where('status', 'active')
      ->where('is_active', 1)
      ->where('assign_date', '<=', $monthDate->endOfMonth());
})
->where('is_active_rolling', 1)
->where('issue_date', '<=', $monthDate->endOfMonth())
->get();

echo "  Controller returns " . $controllerInvoices->count() . " invoices for {$month}:\n";

foreach($controllerInvoices as $invoice) {
    $customer = $invoice->customerProduct->customer ?? null;
    $customerName = $customer ? $customer->name : 'Unknown';
    
    echo "    {$invoice->invoice_number} ({$customerName}):\n";
    echo "      Raw Subtotal: ₹{$invoice->subtotal}\n";
    echo "      Raw Previous Due: ₹{$invoice->previous_due}\n";
    echo "      Raw Total Amount: ₹{$invoice->total_amount}\n";
    echo "      Raw Received Amount: ₹{$invoice->received_amount}\n";
    echo "      Raw Next Due: ₹{$invoice->next_due}\n";
    echo "      Payments Count: " . $invoice->payments->count() . "\n";
    echo "      Payments Sum: ₹" . $invoice->payments->sum('amount') . "\n";
    echo "\n";
}

echo "3. TESTING TRANSFORMATION LOGIC:\n";

// Test the transformation that might be affecting display
try {
    $controller = new \App\Http\Controllers\Admin\MonthlyBillController();
    $reflection = new ReflectionClass($controller);
    $transformMethod = $reflection->getMethod('transformInvoicesForMonth');
    $transformMethod->setAccessible(true);
    
    $transformedInvoices = $transformMethod->invoke($controller, $controllerInvoices, $month);
    
    echo "  After transformation:\n";
    foreach($transformedInvoices as $invoice) {
        $customer = $invoice->customerProduct->customer ?? null;
        $customerName = $customer ? $customer->name : 'Unknown';
        
        echo "    {$invoice->invoice_number} ({$customerName}):\n";
        echo "      Transformed Subtotal: ₹{$invoice->subtotal}\n";
        echo "      Transformed Previous Due: ₹{$invoice->previous_due}\n";
        echo "      Transformed Total Amount: ₹{$invoice->total_amount}\n";
        echo "      Transformed Next Due: ₹{$invoice->next_due}\n";
        echo "\n";
    }
} catch (Exception $e) {
    echo "  ❌ Transformation test failed: " . $e->getMessage() . "\n";
}

echo "4. CHECKING WHAT USER SEES ON PAGE:\n";
echo "  Based on your screenshot, you're seeing:\n";
echo "  - Imteaz INV-25-01-0001: Next Due ₹3,000 (but should be ₹2,000)\n";
echo "  - This suggests the display is NOT using the corrected database values\n";
echo "\n";

echo "5. POSSIBLE ISSUES:\n";
echo "  A) Browser cache - old data still showing\n";
echo "  B) Transformation logic overriding correct database values\n";
echo "  C) Different invoice being displayed than expected\n";
echo "  D) Database changes not committed properly\n";
echo "\n";

echo "6. IMMEDIATE FIXES TO TRY:\n";
echo "  1. Clear browser cache and refresh page\n";
echo "  2. Check if transformation logic is still overriding values\n";
echo "  3. Verify database changes were committed\n";
echo "  4. Check if correct invoice is being displayed\n";

echo "\n=== DEBUGGING COMPLETE ===\n";
echo "The issue is likely in the display/transformation logic, not the database.\n";