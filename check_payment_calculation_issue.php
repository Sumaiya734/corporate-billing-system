<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING PAYMENT CALCULATION ISSUE ===\n\n";

echo "1. CURRENT INVOICE DATA:\n";
$invoices = DB::table('invoices as i')
    ->leftJoin('payments as p', 'i.invoice_id', '=', 'p.invoice_id')
    ->join('customer_to_products as cp', 'i.cp_id', '=', 'cp.cp_id')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->select(
        'i.invoice_number',
        'c.name as customer_name',
        'i.subtotal',
        'i.previous_due',
        'i.total_amount',
        'i.received_amount',
        'i.next_due',
        'i.status',
        DB::raw('SUM(COALESCE(p.amount, 0)) as actual_payments_sum')
    )
    ->groupBy('i.invoice_id', 'i.invoice_number', 'c.name', 'i.subtotal', 'i.previous_due', 'i.total_amount', 'i.received_amount', 'i.next_due', 'i.status')
    ->get();

foreach($invoices as $invoice) {
    echo "  Invoice: {$invoice->invoice_number} ({$invoice->customer_name})\n";
    echo "    Subtotal: ₹{$invoice->subtotal}\n";
    echo "    Previous Due: ₹{$invoice->previous_due}\n";
    echo "    Total Amount: ₹{$invoice->total_amount}\n";
    echo "    Received Amount (stored): ₹{$invoice->received_amount}\n";
    echo "    Actual Payments Sum: ₹{$invoice->actual_payments_sum}\n";
    echo "    Next Due (stored): ₹{$invoice->next_due}\n";
    
    // Calculate what next_due SHOULD be
    $correctNextDue = $invoice->total_amount - $invoice->actual_payments_sum;
    echo "    Next Due (should be): ₹{$correctNextDue}\n";
    echo "    Status: {$invoice->status}\n";
    
    if (abs($invoice->next_due - $correctNextDue) > 0.01) {
        echo "    ❌ MISMATCH: next_due is incorrect!\n";
    } else {
        echo "    ✅ CORRECT: next_due matches calculation\n";
    }
    echo "\n";
}

echo "2. PAYMENTS TABLE DATA:\n";
$payments = DB::table('payments as p')
    ->join('invoices as i', 'p.invoice_id', '=', 'i.invoice_id')
    ->join('customer_to_products as cp', 'i.cp_id', '=', 'cp.cp_id')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->select('p.*', 'i.invoice_number', 'c.name as customer_name')
    ->get();

if ($payments->count() > 0) {
    foreach($payments as $payment) {
        echo "  Payment ID: {$payment->payment_id}\n";
        echo "  Invoice: {$payment->invoice_number} ({$payment->customer_name})\n";
        echo "  Amount: ₹{$payment->amount}\n";
        echo "  Date: {$payment->payment_date}\n";
        echo "  Method: {$payment->payment_method}\n";
        echo "\n";
    }
} else {
    echo "  No payments found in database\n\n";
}

echo "3. ISSUE ANALYSIS:\n";
echo "The problem is that when payments are made:\n";
echo "- The payment is recorded in the payments table\n";
echo "- But the invoice's received_amount and next_due fields are not updated\n";
echo "- The calculation should be: next_due = total_amount - SUM(payments)\n";
echo "\n";

echo "4. SOLUTION NEEDED:\n";
echo "- Fix the payment recording process to update invoice fields\n";
echo "- Ensure next_due = total_amount - received_amount\n";
echo "- Update all existing invoices with correct calculations\n";
echo "- Use real database values, no hardcoding\n";