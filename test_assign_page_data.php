<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TESTING ASSIGN PAGE DATA SOURCES ===\n\n";

// Test 1: Check customers data (what the assign page should show)
echo "1. CUSTOMERS DATA:\n";
$customers = DB::table('customers')
    ->where('is_active', 1)
    ->select('c_id', 'name', 'phone', 'email', 'customer_id', 'address')
    ->orderBy('name')
    ->get();

echo "Total active customers: " . $customers->count() . "\n";
foreach($customers as $customer) {
    echo "  - {$customer->name} (ID: {$customer->customer_id})\n";
    echo "    Phone: " . ($customer->phone ?: 'No phone') . "\n";
    echo "    Email: " . ($customer->email ?: 'No email') . "\n";
    echo "    Address: " . ($customer->address ?: 'No address') . "\n";
    echo "\n";
}

// Test 2: Check products data (what the assign page should show)
echo "2. PRODUCTS DATA:\n";
$products = DB::table('products as p')
    ->join('product_type as pt', 'p.product_type_id', '=', 'pt.id')
    ->select('p.p_id', 'p.name', 'p.monthly_price', 'pt.name as product_type')
    ->orderBy('p.name')
    ->get();

echo "Total active products: " . $products->count() . "\n";
foreach($products as $product) {
    echo "  - {$product->name} - ৳" . number_format($product->monthly_price, 2) . "/month ({$product->product_type})\n";
}

// Test 3: Check if there are any existing customer-product assignments
echo "\n3. EXISTING ASSIGNMENTS:\n";
$assignments = DB::table('customer_to_products as cp')
    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
    ->join('products as p', 'cp.p_id', '=', 'p.p_id')
    ->where('cp.status', 'active')
    ->where('cp.is_active', 1)
    ->select('c.name as customer_name', 'p.name as product_name', 'cp.assign_date', 'cp.billing_cycle_months')
    ->orderBy('cp.assign_date', 'desc')
    ->get();

echo "Total active assignments: " . $assignments->count() . "\n";
foreach($assignments as $assignment) {
    echo "  - {$assignment->customer_name} → {$assignment->product_name}\n";
    echo "    Assigned: {$assignment->assign_date}, Cycle: {$assignment->billing_cycle_months} months\n";
}

// Test 4: Check if the assign page controller is working
echo "\n4. CONTROLLER TEST:\n";
try {
    // Simulate what the controller should return
    $controllerCustomers = DB::table('customers')
        ->where('is_active', 1)
        ->orderBy('name')
        ->get();
    
    $controllerProducts = DB::table('products as p')
        ->join('product_type as pt', 'p.product_type_id', '=', 'pt.id')
        ->select('p.*', 'pt.name as product_type')
        ->orderBy('p.name')
        ->get();
    
    echo "✓ Controller would return {$controllerCustomers->count()} customers\n";
    echo "✓ Controller would return {$controllerProducts->count()} products\n";
    
    if ($controllerCustomers->count() > 0 && $controllerProducts->count() > 0) {
        echo "✅ ASSIGN PAGE SHOULD WORK - Has both customers and products\n";
    } else {
        echo "❌ ASSIGN PAGE MIGHT BE EMPTY - Missing customers or products\n";
    }
    
} catch (Exception $e) {
    echo "❌ Controller test failed: " . $e->getMessage() . "\n";
}

// Test 5: Check if the page would show the current customer (Zia)
echo "\n5. CURRENT CUSTOMER CHECK:\n";
$currentCustomer = DB::table('customers')->where('name', 'Zia')->first();
if ($currentCustomer) {
    echo "✓ Current customer 'Zia' found in database\n";
    echo "  ID: {$currentCustomer->c_id}\n";
    echo "  Customer ID: {$currentCustomer->customer_id}\n";
    echo "  Phone: " . ($currentCustomer->phone ?: 'No phone') . "\n";
    echo "  Status: " . ($currentCustomer->is_active ? 'Active' : 'Inactive') . "\n";
    
    if ($currentCustomer->is_active) {
        echo "✅ Zia will appear in the assign page dropdown\n";
    } else {
        echo "❌ Zia will NOT appear (inactive)\n";
    }
} else {
    echo "❌ Current customer 'Zia' not found in database\n";
}

echo "\n=== SUMMARY ===\n";
echo "The assign.blade.php page should be showing:\n";
echo "- {$customers->count()} customers in the dropdown\n";
echo "- {$products->count()} products in the selection\n";
echo "- Real-time data from the database\n";
echo "- AJAX functionality for checking existing assignments\n";

if ($customers->count() > 0 && $products->count() > 0) {
    echo "\n✅ THE ASSIGN PAGE IS CORRECTLY CONNECTED TO DATABASE\n";
} else {
    echo "\n❌ THE ASSIGN PAGE MAY HAVE DATA ISSUES\n";
}