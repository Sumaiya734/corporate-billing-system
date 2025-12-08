<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $products = DB::table('products')->get();
    echo "Products in Database: " . count($products) . "\n";
    echo "========================\n";
    foreach($products as $product) {
        echo $product->p_id . ': ' . $product->name . ' (Type ID: ' . $product->product_type_id . ")\n";
    }
    
    echo "\nProduct Types with Product Counts:\n";
    echo "==================================\n";
    $types = DB::table('product_type')->get();
    foreach($types as $type) {
        $count = DB::table('products')->where('product_type_id', $type->id)->count();
        echo $type->id . ': ' . $type->name . ' (' . $count . " products)\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>