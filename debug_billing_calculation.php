<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customerproduct;
use Carbon\Carbon;

// Test for a specific customer to see the billing pattern
$testCustomerId = 1; // Sumaiya Akter

echo "=== DEBUGGING BILLING CALCULATION ===\n\n";

// Get customer products
$customerProducts = Customerproduct::where('c_id', $testCustomerId)
    ->where('status', 'active')
    ->where('is_active', 1)
    ->with('product')
    ->get();

echo "Customer ID: $testCustomerId\n";
echo "Products:\n";
foreach ($customerProducts as $cp) {
    echo "  - Product: {$cp->product->name}\n";
    echo "    Assign Date: {$cp->assign_date}\n";
    echo "    Billing Cycle: {$cp->billing_cycle_months} months\n";
    echo "    Monthly Price: {$cp->product->monthly_price} BDT\n\n";
}

// Test months from January 2025 to December 2025
echo "\n=== MONTHLY BILLING BREAKDOWN ===\n\n";

for ($month = 1; $month <= 12; $month++) {
    $monthDate = Carbon::create(2025, $month, 1);
    $monthStr = $monthDate->format('Y-m');
    
    echo "Month: {$monthDate->format('F Y')} ($monthStr)\n";
    echo str_repeat('-', 60) . "\n";
    
    $totalInstallment = 0;
    $totalCarryForward = 0;
    
    foreach ($customerProducts as $cp) {
        $assignDate = Carbon::parse($cp->assign_date);
        $billingCycle = $cp->billing_cycle_months;
        $monthlyPrice = $cp->product->monthly_price;
        
        // Check if should pay in this month
        $isAssignedMonth = ($assignDate->year == $monthDate->year && $assignDate->month == $monthDate->month);
        $monthsDiff = $assignDate->diffInMonths($monthDate);
        $isDueMonth = ($monthsDiff >= 0 && $monthsDiff % $billingCycle == 0);
        
        // Calculate installment
        $installment = 0;
        if ($isAssignedMonth || $isDueMonth) {
            if ($billingCycle == 1) {
                $installment = $monthlyPrice;
            } else {
                $installment = $monthlyPrice * $billingCycle;
            }
        }
        
        echo "  Product: {$cp->product->name}\n";
        echo "    Assigned: {$assignDate->format('Y-m-d')}\n";
        echo "    Months Diff: $monthsDiff\n";
        echo "    Is Assigned Month: " . ($isAssignedMonth ? 'YES' : 'NO') . "\n";
        echo "    Is Due Month: " . ($isDueMonth ? 'YES' : 'NO') . "\n";
        echo "    Installment: $installment BDT\n";
        
        $totalInstallment += $installment;
    }
    
    echo "\n  TOTAL INSTALLMENT: $totalInstallment BDT\n";
    echo "  (Carry forward would be added separately)\n\n";
}

echo "\n=== EXPECTED PATTERN ===\n";
echo "For a 3-month billing cycle product assigned in June:\n";
echo "  - June (assigned): Charge 3 months advance\n";
echo "  - July: No new charge (within paid period)\n";
echo "  - August: No new charge (within paid period)\n";
echo "  - September (due): Charge 3 months for Sep-Nov\n";
echo "  - October: No new charge (within paid period)\n";
echo "  - November: No new charge (within paid period)\n";
echo "  - December (due): Charge 3 months for Dec-Feb\n";
echo "  - And so on...\n";
