<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;

/**
 * Calculate what the invoice amounts should be for each month
 * Based on the billing logic: subtotal added every 3 months, carry forward in between
 */
function calculateMonthlyAmounts($assignDate, $billingCycle, $subtotalAmount, $targetMonth) {
    $assignMonth = Carbon::parse($assignDate)->startOfMonth();
    $targetMonthDate = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
    
    // Calculate months since assignment
    $monthsSinceAssign = $assignMonth->diffInMonths($targetMonthDate);
    
    if ($monthsSinceAssign < 0) {
        // Target month is before assignment
        return [
            'subtotal' => 0,
            'previous_due' => 0,
            'total_amount' => 0
        ];
    }
    
    // Calculate how many billing cycles have completed
    $completedCycles = floor($monthsSinceAssign / $billingCycle);
    $monthInCurrentCycle = $monthsSinceAssign % $billingCycle;
    
    if ($monthInCurrentCycle == 0) {
        // This is a billing cycle start month
        $subtotal = $subtotalAmount;
        $previousDue = $completedCycles * $subtotalAmount;
        $totalAmount = $subtotal + $previousDue;
    } else {
        // This is a carry forward month
        $subtotal = 0;
        $totalAmount = ($completedCycles + 1) * $subtotalAmount;
        $previousDue = $totalAmount;
    }
    
    return [
        'subtotal' => $subtotal,
        'previous_due' => $previousDue,
        'total_amount' => $totalAmount,
        'months_since_assign' => $monthsSinceAssign,
        'completed_cycles' => $completedCycles,
        'month_in_cycle' => $monthInCurrentCycle
    ];
}

// Test the function
$assignDate = '2025-02-10';
$billingCycle = 3;
$subtotalAmount = 2000;

$months = [
    '2025-02', '2025-03', '2025-04', '2025-05', 
    '2025-06', '2025-07', '2025-08', '2025-09', 
    '2025-10', '2025-11', '2025-12'
];

echo "=== MONTHLY AMOUNT CALCULATION ===\n\n";
echo "Assign Date: {$assignDate}\n";
echo "Billing Cycle: {$billingCycle} months\n";
echo "Subtotal Amount: ৳{$subtotalAmount}\n\n";

foreach ($months as $month) {
    $amounts = calculateMonthlyAmounts($assignDate, $billingCycle, $subtotalAmount, $month);
    $monthName = Carbon::createFromFormat('Y-m', $month)->format('F Y');
    
    $cycleType = ($amounts['month_in_cycle'] == 0) ? 'BILLING CYCLE' : 'CARRY FORWARD';
    
    echo "{$monthName} (Month {$amounts['months_since_assign']}) - {$cycleType}\n";
    echo "  Subtotal: ৳" . number_format($amounts['subtotal'], 0) . "\n";
    echo "  Previous Due: ৳" . number_format($amounts['previous_due'], 0) . "\n";
    echo "  Total Amount: ৳" . number_format($amounts['total_amount'], 0) . "\n";
    echo "\n";
}