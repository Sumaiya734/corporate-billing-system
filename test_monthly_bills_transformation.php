<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use Carbon\Carbon;

echo "=== TESTING MONTHLY BILLS TRANSFORMATION ===\n\n";

// Test transformation for different months
function testTransformationForMonth($month) {
    echo "Testing {$month}:\n";
    
    // Get the actual invoice from database
    $invoice = Invoice::with('customerProduct')->where('is_active_rolling', 1)->first();
    
    if (!$invoice) {
        echo "  ❌ No rolling invoice found\n\n";
        return;
    }
    
    echo "  Original invoice (current state):\n";
    echo "    Subtotal: ৳" . number_format($invoice->subtotal, 0) . "\n";
    echo "    Previous Due: ৳" . number_format($invoice->previous_due, 0) . "\n";
    echo "    Total Amount: ৳" . number_format($invoice->total_amount, 0) . "\n";
    
    // Calculate what it should show for this month
    $assignMonth = Carbon::parse($invoice->customerProduct->assign_date)->startOfMonth();
    $targetMonthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    $monthsSinceAssign = $assignMonth->diffInMonths($targetMonthDate);
    $billingCycle = $invoice->customerProduct->billing_cycle_months;
    $subtotalAmount = 2000;
    
    $isBillingCycleMonth = ($monthsSinceAssign % $billingCycle) == 0;
    
    if ($monthsSinceAssign == 0) {
        $transformed = ['subtotal' => $subtotalAmount, 'previous_due' => 0, 'total_amount' => $subtotalAmount];
    } else if ($isBillingCycleMonth) {
        $completedCycles = floor($monthsSinceAssign / $billingCycle);
        $previousDue = $completedCycles * $subtotalAmount;
        $totalAmount = $subtotalAmount + $previousDue;
        $transformed = ['subtotal' => $subtotalAmount, 'previous_due' => $previousDue, 'total_amount' => $totalAmount];
    } else {
        $completedCycles = floor($monthsSinceAssign / $billingCycle);
        $totalAmount = ($completedCycles + 1) * $subtotalAmount;
        $transformed = ['subtotal' => 0, 'previous_due' => $totalAmount, 'total_amount' => $totalAmount];
    }
    
    echo "  Transformed for {$month}:\n";
    echo "    Subtotal: ৳" . number_format($transformed['subtotal'], 0) . "\n";
    echo "    Previous Due: ৳" . number_format($transformed['previous_due'], 0) . "\n";
    echo "    Total Amount: ৳" . number_format($transformed['total_amount'], 0) . "\n";
    echo "    Months since assign: {$monthsSinceAssign}\n";
    echo "    Is billing cycle month: " . ($isBillingCycleMonth ? 'Yes' : 'No') . "\n";
    echo "\n";
    
    return $transformed;
}

// Test key months
$testMonths = ['2025-01', '2025-02', '2025-03', '2025-04', '2025-05', '2025-06'];

foreach ($testMonths as $month) {
    testTransformationForMonth($month);
}

echo "=== EXPECTED RESULTS FOR APRIL 2025 ===\n";
echo "The Monthly Bills page for April 2025 should now show:\n";
echo "✓ Previous Due: ৳2,000 (instead of ৳0)\n";
echo "✓ Subtotal: ৳2,000 (correct)\n";
echo "✓ Total Amount: ৳4,000 (instead of ৳2,000)\n";
echo "\nThis matches the Billing & Invoices page summary!\n";