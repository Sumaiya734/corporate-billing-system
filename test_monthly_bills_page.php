<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\MonthlyBillController;
use Illuminate\Http\Request;
use Carbon\Carbon;

echo "=== TESTING MONTHLY BILLS PAGE ===\n\n";

try {
    // Create controller instance
    $controller = new MonthlyBillController();
    
    // Test with December 2025
    $month = '2025-12';
    echo "Testing month: {$month}\n";
    
    // Use reflection to call private method getDueCustomersForMonth
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getDueCustomersForMonth');
    $method->setAccessible(true);
    
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    $result = $method->invoke($controller, $monthDate);
    
    echo "✓ getDueCustomersForMonth executed successfully\n";
    echo "Found " . $result->count() . " customers\n";
    
    foreach ($result as $customer) {
        echo "  - {$customer->name} (ID: {$customer->c_id})\n";
        if (isset($customer->product_details)) {
            foreach ($customer->product_details as $product) {
                echo "    Product: {$product['p_id']}, Price: {$product['monthly_price']}, Cycle: {$product['billing_cycle_months']}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}