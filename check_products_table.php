<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING PRODUCTS TABLE STRUCTURE ===\n\n";

try {
    $columns = DB::select('DESCRIBE products');
    echo "Products table columns:\n";
    foreach($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
    
    echo "\nSample products data:\n";
    $products = DB::table('products as p')
        ->join('product_type as pt', 'p.product_type_id', '=', 'pt.id')
        ->select('p.p_id', 'p.name', 'p.monthly_price', 'pt.name as product_type')
        ->limit(5)
        ->get();
    
    foreach($products as $product) {
        echo "  - {$product->name} - ৳{$product->monthly_price}/month ({$product->product_type})\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}