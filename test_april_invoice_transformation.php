<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;

echo "=== TESTING APRIL 2025 INVOICE TRANSFORMATION ===\n\n";

// Test the calculation for April 2025
function calculateMonthlyAmounts($assignDate, $billingCycle, $subtotalAmount, $targetMonth) {
    $assignMonth = Carbon::parse($assignDate)->startOfMonth();
    $targetMonthDate = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
    
    $monthsSinceAssign = $assignMonth->diffInMonths($targetMonthDate);
    
    if ($monthsSinceAssign < 0) {
        return ['subtotal' => 0, 'previous_due' => 0, 'total_amount' => 0, 'cycle_number' => 0, 'cycle_position' => 0];
    }
    
    $isBillingCycleMonth = ($monthsSinceAssign % $billingCycle) == 0;
    $cycleNumber = floor($monthsSinceAssign / $billingCycle) + 1;
    $cyclePosition = $monthsSinceAssign % $billingCycle;
    
    if ($monthsSinceAssign == 0) {
        return ['subtotal' => $subtotalAmount, 'previous_due' => 0, 'total_amount' => $subtotalAmount, 'cycle_number' => 1, 'cycle_position' => 0];
    } else if ($isBillingCycleMonth) {
        $completedCycles = floor($monthsSinceAssign / $billingCycle);
        $previousDue = $completedCycles * $subtotalAmount;
        $totalAmount = $subtotalAmount + $previousDue;
        return ['subtotal' => $subtotalAmount, 'previous_due' => $previousDue, 'total_amount' => $totalAmount, 'cycle_number' => $cycleNumber, 'cycle_position' => 0];
    } else {
        $completedCycles = floor($monthsSinceAssign / $billingCycle);
        $totalAmount = ($completedCycles + 1) * $subtotalAmount;
        return ['subtotal' => 0, 'previous_due' => $totalAmount, 'total_amount' => $totalAmount, 'cycle_number' => $cycleNumber, 'cycle_position' => $cyclePosition];
    }
}

// Test April 2025 specifically
$aprilResult = calculateMonthlyAmounts('2025-01-10', 3, 2000, '2025-04');

echo "April 2025 Invoice Transformation:\n";
echo "  Assign Date: 2025-01-10\n";
echo "  Billing Cycle: 3 months\n";
echo "  Target Month: April 2025\n";
echo "  Months Since Assign: " . Carbon::parse('2025-01-10')->startOfMonth()->diffInMonths(Carbon::parse('2025-04-01')) . "\n";
echo "\n";
echo "Expected April 2025 Invoice Display:\n";
echo "  Subtotal: ৳" . number_format($aprilResult['subtotal'], 0) . "\n";
echo "  Previous Due: ৳" . number_format($aprilResult['previous_due'], 0) . "\n";
echo "  Total Amount: ৳" . number_format($aprilResult['total_amount'], 0) . "\n";
echo "  Cycle: #{$aprilResult['cycle_number']}, Position: {$aprilResult['cycle_position']}\n";

echo "\n=== VERIFICATION ===\n";
echo "✓ April is month 3 (billing cycle month)\n";
echo "✓ Should add new subtotal (₹2,000)\n";
echo "✓ Should carry forward previous total as previous_due (₹2,000)\n";
echo "✓ Total should be ₹4,000 (₹2,000 + ₹2,000)\n";

if ($aprilResult['subtotal'] == 2000 && $aprilResult['previous_due'] == 2000 && $aprilResult['total_amount'] == 4000) {
    echo "✅ CORRECT: April transformation logic is working!\n";
} else {
    echo "❌ ERROR: April transformation logic is incorrect!\n";
}

echo "\nThis should fix the Monthly Bills page to show:\n";
echo "- Previous Due: ৳2,000 (instead of ৳0)\n";
echo "- Subtotal: ৳2,000 (correct)\n";
echo "- Total Amount: ৳4,000 (instead of ৳2,000)\n";