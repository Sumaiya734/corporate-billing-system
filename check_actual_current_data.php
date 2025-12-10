<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ACTUAL CURRENT DATABASE DATA ===\n\n";

echo "1. INVOICES TABLE:\n";
$invoices = DB::table('invoices')->get();
foreach($invoices as $invoice) {
    echo "  Invoice: {$invoice->invoice_number}\n";
    echo "  Subtotal: ₹{$invoice->subtotal}\n";
    echo "  Previous Due: ₹{$invoice->previous_due}\n";
    echo "  Total Amount: ₹{$invoice->total_amount}\n";
    echo "  Status: {$invoice->status}\n";
    echo "  Issue Date: {$invoice->issue_date}\n";
    echo "  CP ID: {$invoice->cp_id}\n";
    echo "\n";
}

echo "2. CUSTOMER-PRODUCT ASSIGNMENTS:\n";
$assignments = DB::table('customer_to_products as cp')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->join('products as p', 'cp.p_id', '=', 'p.p_id')
    ->select('c.name as customer_name', 'p.name as product_name', 'cp.custom_price', 'cp.billing_cycle_months', 'p.monthly_price', 'cp.cp_id')
    ->get();

foreach($assignments as $assignment) {
    echo "  Customer: {$assignment->customer_name}\n";
    echo "  Product: {$assignment->product_name}\n";
    echo "  Custom Price: ₹{$assignment->custom_price}\n";
    echo "  Monthly Price: ₹{$assignment->monthly_price}\n";
    echo "  Billing Cycle: {$assignment->billing_cycle_months} months\n";
    echo "  CP ID: {$assignment->cp_id}\n";
    echo "\n";
}

echo "3. WHAT THE MONTHLY BILLS PAGE SHOULD SHOW:\n";
// Simulate what the monthly bills page shows for January 2025
$monthlyBillsData = DB::table('invoices as i')
    ->join('customer_to_products as cp', 'i.cp_id', '=', 'cp.cp_id')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->join('products as p', 'cp.p_id', '=', 'p.p_id')
    ->where('i.is_active_rolling', 1)
    ->select(
        'i.invoice_number',
        'i.subtotal',
        'i.previous_due', 
        'i.total_amount',
        'i.status',
        'c.name as customer_name',
        'p.name as product_name',
        'p.monthly_price'
    )
    ->get();

foreach($monthlyBillsData as $data) {
    echo "  Invoice: {$data->invoice_number}\n";
    echo "  Customer: {$data->customer_name}\n";
    echo "  Product: {$data->product_name}\n";
    echo "  Product Monthly Price: ₹{$data->monthly_price}\n";
    echo "  Invoice Subtotal: ₹{$data->subtotal}\n";
    echo "  Previous Due: ₹{$data->previous_due}\n";
    echo "  Total Amount: ₹{$data->total_amount}\n";
    echo "  Status: {$data->status}\n";
    echo "\n";
}