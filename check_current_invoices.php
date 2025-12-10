<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING CURRENT INVOICES ===\n\n";

$count = DB::table('invoices')->count();
echo "Total invoices: {$count}\n\n";

if ($count > 0) {
    $invoices = DB::table('invoices')->get();
    foreach($invoices as $invoice) {
        echo "Invoice: {$invoice->invoice_number}\n";
        echo "  CP ID: {$invoice->cp_id}\n";
        echo "  Issue Date: {$invoice->issue_date}\n";
        echo "  Total Amount: {$invoice->total_amount}\n";
        echo "  Status: {$invoice->status}\n";
        echo "  Is Active Rolling: " . ($invoice->is_active_rolling ? 'Yes' : 'No') . "\n";
        echo "\n";
    }
} else {
    echo "No invoices found. Let's check customer products:\n\n";
    
    $customerProducts = DB::table('customer_to_products as cp')
        ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
        ->where('cp.status', 'active')
        ->where('cp.is_active', 1)
        ->select('c.name', 'cp.cp_id', 'cp.assign_date', 'cp.billing_cycle_months')
        ->get();
        
    foreach($customerProducts as $cp) {
        echo "Customer: {$cp->name}\n";
        echo "  CP ID: {$cp->cp_id}\n";
        echo "  Assign Date: {$cp->assign_date}\n";
        echo "  Billing Cycle: {$cp->billing_cycle_months}\n";
        echo "\n";
    }
}