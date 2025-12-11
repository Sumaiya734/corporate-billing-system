<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CUSTOMER_TO_PRODUCTS TABLE STRUCTURE ===\n\n";

$columns = DB::select('DESCRIBE customer_to_products');
echo "Columns:\n";
foreach($columns as $col) {
    echo "  - {$col->Field} ({$col->Type})\n";
}

echo "\nSample data:\n";
$sample = DB::table('customer_to_products')->limit(3)->get();
foreach($sample as $row) {
    echo "  cp_id: {$row->cp_id}, c_id: {$row->c_id}, p_id: {$row->p_id}\n";
    echo "  custom_price: {$row->custom_price}, status: {$row->status}\n";
    echo "  assign_date: {$row->assign_date}, billing_cycle_months: {$row->billing_cycle_months}\n";
    echo "\n";
}

// Check if there are calculated fields used in the view
echo "Checking what fields the view expects:\n";
$cp = DB::table('customer_to_products as cp')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->join('products as p', 'cp.p_id', '=', 'p.p_id')
    ->select('c.name as customer_name', 'p.name as product_name', 'cp.status', 'cp.custom_price', 'p.monthly_price')
    ->first();

if ($cp) {
    echo "  - customer_name: {$cp->customer_name}\n";
    echo "  - product_name: {$cp->product_name}\n";
    echo "  - status: {$cp->status}\n";
    echo "  - custom_price: {$cp->custom_price}\n";
    echo "  - monthly_price: {$cp->monthly_price}\n";
}