<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\MonthlyBillController;
use Illuminate\Http\Request;

echo "=== TESTING MONTHLY BILL CONTROLLER ===\n\n";

// Create controller instance
$controller = new MonthlyBillController();

// Test months
$months = ['2025-01', '2025-02', '2025-03', '2025-04', '2025-05', '2025-06', '2025-07', '2025-08', '2025-09', '2025-10', '2025-11', '2025-12'];

foreach ($months as $month) {
    $monthName = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y');
    
    // Use reflection to call private method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('calculateHistoricalAmountsForMonth');
    $method->setAccessible(true);
    
    try {
        // Get invoices for this month (we'll pass empty collection for now)
        $invoices = collect([
            (object)[
                'customerProduct' => (object)[
                    'assign_date' => '2025-01-10',
                    'billing_cycle_months' => 3
                ]
            ]
        ]);
        
        $result = $method->invoke($controller, $month, $invoices);
        
        echo "{$monthName}:\n";
        echo "  Total Amount: ৳" . number_format($result['total_amount'], 0) . "\n";
        echo "  Received: ৳" . number_format($result['received_amount'], 0) . "\n";
        echo "  Due: ৳" . number_format($result['due_amount'], 0) . "\n";
        echo "\n";
        
    } catch (Exception $e) {
        echo "{$monthName}: Error - {$e->getMessage()}\n\n";
    }
}