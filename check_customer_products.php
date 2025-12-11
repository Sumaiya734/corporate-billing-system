<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING CUSTOMER PRODUCTS ===\n\n";

$cps = DB::table('customer_to_products as cp')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->where('cp.status', 'active')
    ->select('cp.cp_id', 'cp.c_id', 'c.name', 'cp.assign_date', 'cp.billing_cycle_months')
    ->get();

foreach($cps as $cp) {
    echo "CP ID: {$cp->cp_id}\n";
    echo "Customer: {$cp->name} (ID: {$cp->c_id})\n";
    echo "Assign Date: {$cp->assign_date}\n";
    echo "Billing Cycle: {$cp->billing_cycle_months}\n";
    echo "\n";
}

echo "=== CHECKING INVOICES ===\n\n";

$invoices = DB::table('invoices')->get();
foreach($invoices as $invoice) {
    echo "Invoice: {$invoice->invoice_number}\n";
    echo "CP ID: {$invoice->cp_id}\n";
    echo "Issue Date: {$invoice->issue_date}\n";
    echo "Subtotal: {$invoice->subtotal}\n";
    echo "Previous Due: {$invoice->previous_due}\n";
    echo "Total Amount: {$invoice->total_amount}\n";
    echo "Is Active Rolling: " . ($invoice->is_active_rolling ? 'Yes' : 'No') . "\n";
    echo "\n";
}