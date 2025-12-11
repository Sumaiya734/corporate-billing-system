<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIXING INVOICE PAYMENT CALCULATIONS ===\n\n";

echo "1. BEFORE FIX - Current incorrect values:\n";
$invoices = DB::table('invoices as i')
    ->leftJoin('payments as p', 'i.invoice_id', '=', 'p.invoice_id')
    ->join('customer_to_products as cp', 'i.cp_id', '=', 'cp.cp_id')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->select(
        'i.invoice_id',
        'i.invoice_number',
        'c.name as customer_name',
        'i.total_amount',
        'i.received_amount',
        'i.next_due',
        'i.status',
        DB::raw('COALESCE(SUM(p.amount), 0) as actual_payments_sum')
    )
    ->groupBy('i.invoice_id', 'i.invoice_number', 'c.name', 'i.total_amount', 'i.received_amount', 'i.next_due', 'i.status')
    ->get();

foreach($invoices as $invoice) {
    echo "  {$invoice->invoice_number} ({$invoice->customer_name}):\n";
    echo "    Total: ₹{$invoice->total_amount}, Received: ₹{$invoice->received_amount}, Next Due: ₹{$invoice->next_due}\n";
    echo "    Actual Payments: ₹{$invoice->actual_payments_sum}\n";
    echo "\n";
}

echo "2. APPLYING FIXES:\n";

// Fix each invoice
foreach($invoices as $invoice) {
    $correctReceivedAmount = $invoice->actual_payments_sum;
    $correctNextDue = $invoice->total_amount - $correctReceivedAmount;
    
    // Determine correct status
    $correctStatus = 'unpaid';
    if ($correctReceivedAmount >= $invoice->total_amount) {
        $correctStatus = 'paid';
    } elseif ($correctReceivedAmount > 0) {
        $correctStatus = 'partial';
    }
    
    // Update the invoice
    DB::table('invoices')
        ->where('invoice_id', $invoice->invoice_id)
        ->update([
            'received_amount' => $correctReceivedAmount,
            'next_due' => $correctNextDue,
            'status' => $correctStatus,
            'updated_at' => now()
        ]);
    
    echo "  Fixed {$invoice->invoice_number}:\n";
    echo "    Received Amount: ₹{$invoice->received_amount} → ₹{$correctReceivedAmount}\n";
    echo "    Next Due: ₹{$invoice->next_due} → ₹{$correctNextDue}\n";
    echo "    Status: {$invoice->status} → {$correctStatus}\n";
    echo "\n";
}

echo "3. AFTER FIX - Corrected values:\n";
$fixedInvoices = DB::table('invoices as i')
    ->join('customer_to_products as cp', 'i.cp_id', '=', 'cp.cp_id')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->select(
        'i.invoice_number',
        'c.name as customer_name',
        'i.total_amount',
        'i.received_amount',
        'i.next_due',
        'i.status'
    )
    ->get();

foreach($fixedInvoices as $invoice) {
    echo "  {$invoice->invoice_number} ({$invoice->customer_name}):\n";
    echo "    Total: ₹{$invoice->total_amount}\n";
    echo "    Received: ₹{$invoice->received_amount}\n";
    echo "    Next Due: ₹{$invoice->next_due}\n";
    echo "    Status: {$invoice->status}\n";
    
    // Verify calculation
    $calculatedNextDue = $invoice->total_amount - $invoice->received_amount;
    if (abs($invoice->next_due - $calculatedNextDue) < 0.01) {
        echo "    ✅ CORRECT: next_due calculation is accurate\n";
    } else {
        echo "    ❌ ERROR: next_due calculation is still wrong\n";
    }
    echo "\n";
}

echo "=== INVOICE PAYMENT CALCULATIONS FIXED ===\n";
echo "All invoices now have correct received_amount, next_due, and status values.\n";
echo "The monthly bills page will now show accurate payment calculations.\n";