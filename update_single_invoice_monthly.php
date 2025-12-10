<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\Customerproduct;
use Carbon\Carbon;

echo "=== UPDATING SINGLE INVOICE MONTHLY ===\n\n";

// Customer details (from check_customer_billing.php)
$cpId = 34; // CP ID for Imteaz (updated)
$assignDate = Carbon::parse('2025-01-10'); // Actual assign date (updated)
$billingCycle = 3; // months
$subtotalAmount = 2000; // BDT (corrected amount)

// Delete existing invoice and start fresh
Invoice::where('cp_id', $cpId)->delete();
echo "✓ Deleted existing invoices for fresh start\n\n";

// Create initial invoice for January (Month 0)
$invoice = Invoice::create([
    'invoice_number' => 'INV-25-01-0001',
    'cp_id' => $cpId,
    'issue_date' => $assignDate->format('Y-m-d'),
    'previous_due' => 0,
    'subtotal' => $subtotalAmount,
    'total_amount' => $subtotalAmount,
    'received_amount' => 0,
    'next_due' => $subtotalAmount,
    'is_active_rolling' => 1, // Set active rolling flag
    'status' => 'unpaid',
    'notes' => 'Initial billing cycle - Month 0',
    'created_by' => 1
]);
echo "✓ Created initial invoice: {$invoice->invoice_number}\n";

// Current month
$currentMonth = Carbon::now()->format('Y-m');
$currentDate = Carbon::createFromFormat('Y-m', $currentMonth);

echo "Simulating monthly updates from February 2025 to {$currentDate->format('F Y')}:\n\n";

// Simulate monthly updates
$months = [];
$startMonth = Carbon::parse('2025-01');
$month = $startMonth->copy();

while ($month->format('Y-m') <= $currentMonth) {
    $months[] = $month->format('Y-m');
    $month->addMonth();
}

foreach ($months as $monthStr) {
    $monthDate = Carbon::createFromFormat('Y-m', $monthStr);
    
    // Calculate months since assignment (using start of month for accurate counting)
    $assignMonth = $assignDate->startOfMonth();
    $currentMonth = $monthDate->startOfMonth();
    $monthsSinceAssign = $assignMonth->diffInMonths($currentMonth);
    
    // Skip month 0 (February) since we already created it
    if ($monthsSinceAssign == 0) {
        echo "✓ {$monthDate->format('F Y')} (Month {$monthsSinceAssign}) - INITIAL MONTH\n";
        echo "  Subtotal: {$invoice->subtotal}\n";
        echo "  Previous due: {$invoice->previous_due}\n";
        echo "  Total amount: {$invoice->total_amount}\n\n";
        continue;
    }
    
    // Determine if subtotal should be added (every 3 months starting from month 0)
    $shouldAddSubtotal = ($monthsSinceAssign % $billingCycle) == 0;
    
    if ($shouldAddSubtotal) {
        // NEW BILLING CYCLE: Add subtotal + carry forward previous total as previous_due
        $newPreviousDue = $invoice->total_amount; // Previous month's total becomes previous_due
        $newSubtotal = $subtotalAmount; // New subtotal for this cycle
        $newTotalAmount = $newSubtotal + $newPreviousDue;
        
        echo "✓ {$monthDate->format('F Y')} (Month {$monthsSinceAssign}) - NEW BILLING CYCLE\n";
        echo "  Previous total becomes previous_due: {$invoice->total_amount} → {$newPreviousDue}\n";
        echo "  Adding new subtotal: {$newSubtotal}\n";
        echo "  New total: {$newSubtotal} + {$newPreviousDue} = {$newTotalAmount}\n";
        
        // Update invoice
        $invoice->update([
            'subtotal' => $newSubtotal,
            'previous_due' => $newPreviousDue,
            'total_amount' => $newTotalAmount,
            'next_due' => $newTotalAmount, // Assuming no payment
            'notes' => "Billing cycle month {$monthsSinceAssign} - new cycle started"
        ]);
        
    } else {
        // CARRY FORWARD MONTH: subtotal = 0, previous_due = total_amount
        $currentTotal = $invoice->total_amount;
        
        echo "✓ {$monthDate->format('F Y')} (Month {$monthsSinceAssign}) - CARRY FORWARD\n";
        echo "  Subtotal: {$invoice->subtotal} → 0\n";
        echo "  Previous due: {$invoice->previous_due} → {$currentTotal}\n";
        echo "  Total amount: {$currentTotal} (unchanged)\n";
        
        // Update invoice
        $invoice->update([
            'subtotal' => 0,
            'previous_due' => $currentTotal,
            'total_amount' => $currentTotal,
            'next_due' => $currentTotal, // Assuming no payment
            'notes' => "Carry forward month {$monthsSinceAssign}"
        ]);
    }
    
    echo "  Final state: subtotal={$invoice->subtotal}, previous_due={$invoice->previous_due}, total={$invoice->total_amount}\n\n";
}

echo "=== FINAL INVOICE STATE ===\n";
$invoice->refresh();
echo "Invoice Number: {$invoice->invoice_number}\n";
echo "Subtotal: ৳" . number_format($invoice->subtotal, 0) . "\n";
echo "Previous Due: ৳" . number_format($invoice->previous_due, 0) . "\n";
echo "Total Amount: ৳" . number_format($invoice->total_amount, 0) . "\n";
echo "Status: {$invoice->status}\n";
echo "Notes: {$invoice->notes}\n";

echo "\n=== EXPECTED MONTHLY DISPLAY ===\n";
echo "January 2025: ৳2,000 (subtotal 2,000 + previous_due 0) - Cycle 1 start\n";
echo "February 2025: ৳2,000 (subtotal 0 + previous_due 2,000) - Carry forward\n";
echo "March 2025: ৳2,000 (subtotal 0 + previous_due 2,000) - Cycle 1 end\n";
echo "April 2025: ৳4,000 (subtotal 2,000 + previous_due 2,000) - Cycle 2 start\n";
echo "May 2025: ৳4,000 (subtotal 0 + previous_due 4,000) - Carry forward\n";
echo "June 2025: ৳4,000 (subtotal 0 + previous_due 4,000) - Cycle 2 end\n";
echo "July 2025: ৳6,000 (subtotal 2,000 + previous_due 4,000) - Cycle 3 start\n";
echo "August 2025: ৳6,000 (subtotal 0 + previous_due 6,000) - Carry forward\n";
echo "September 2025: ৳6,000 (subtotal 0 + previous_due 6,000) - Cycle 3 end\n";
echo "October 2025: ৳8,000 (subtotal 2,000 + previous_due 6,000) - Cycle 4 start\n";
echo "November 2025: ৳8,000 (subtotal 0 + previous_due 8,000) - Carry forward\n";
echo "December 2025: ৳8,000 (subtotal 0 + previous_due 8,000) - Carry forward\n";
