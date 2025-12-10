<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use Carbon\Carbon;

echo "=== CREATING MONTHLY INVOICES WITH CARRY FORWARD ===\n\n";

// Delete all existing invoices
Invoice::query()->delete();
echo "Deleted all existing invoices\n\n";

// Customer details
$cpId = 29;
$assignDate = Carbon::parse('2025-05-09');
$billingCycle = 3; // months
$subtotalAmount = 2000; // BDT

// Generate invoices from May to December 2025
$months = [
    '2025-05', '2025-06', '2025-07', '2025-08', 
    '2025-09', '2025-10', '2025-11', '2025-12'
];

$invoiceCounter = 1;
$previousNextDue = 0;

foreach ($months as $month) {
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    
    // Check if this month is the same as assign month (year and month)
    $isAssignMonth = ($monthDate->year == $assignDate->year && $monthDate->month == $assignDate->month);
    
    // Calculate months since assign date
    $monthsSinceAssign = $assignDate->diffInMonths($monthDate);
    
    // Determine if subtotal should be added
    // Subtotal is added only at the START of each NEW billing period
    // Period 0: May (month 0) - add subtotal
    // Period 1: August (month 3) - NO subtotal (end of Period 0)
    // Period 2: November (month 6) - add subtotal (start of new period after Period 0 ended)
    
    if ($isAssignMonth) {
        // First month ever - add subtotal
        $subtotal = $subtotalAmount;
        $previousDue = $previousNextDue;
        echo "  [ASSIGN MONTH - FIRST BILLING]\n";
    } else if ($monthsSinceAssign > 0 && ($monthsSinceAssign % $billingCycle) == 0) {
        // This is a billing cycle month (every 3 months: Aug, Nov, Feb, etc.)
        // Determine if it's the END of a period or START of new period
        $periodNumber = $monthsSinceAssign / $billingCycle;
        
        // Pattern: Period 1 ends at month 3 (Aug), Period 2 starts at month 6 (Nov), Period 2 ends at month 9 (Feb), etc.
        // So: month 3, 9, 15... = END of period (no subtotal)
        //     month 6, 12, 18... = START of new period (add subtotal)
        
        if ($periodNumber % 2 == 1) {
            // Odd period number (1, 3, 5...) = END of period
            // Month 3 (Aug), Month 9 (Feb), etc.
            $subtotal = 0;
            $previousDue = $previousNextDue;
            echo "  [DUE MONTH - END OF PERIOD]\n";
        } else {
            // Even period number (2, 4, 6...) = START of new period
            // Month 6 (Nov), Month 12 (May next year), etc.
            $subtotal = $subtotalAmount;
            $previousDue = $previousNextDue;
            echo "  [NEW PERIOD START - ADD SUBTOTAL]\n";
        }
    } else {
        // Regular carry-forward month
        $subtotal = 0;
        $previousDue = $previousNextDue;
    }
    
    $totalAmount = $subtotal + $previousDue;
    $nextDue = $totalAmount; // Assuming no payment
    
    // Create invoice
    $invoiceNumber = 'INV-25-' . $monthDate->format('m') . '-' . str_pad($invoiceCounter, 4, '0', STR_PAD_LEFT);
    
    $invoice = Invoice::create([
        'invoice_number' => $invoiceNumber,
        'cp_id' => $cpId,
        'issue_date' => $monthDate->format('Y-m-09'), // 9th of each month
        'previous_due' => $previousDue,
        'subtotal' => $subtotal,
        'total_amount' => $totalAmount,
        'received_amount' => 0,
        'next_due' => $nextDue,
        'status' => 'unpaid',
        'notes' => ($subtotal > 0) ? 'New billing cycle start' : 'Carry forward',
        'created_by' => 1
    ]);
    
    echo "✓ {$monthDate->format('F Y')}: {$invoiceNumber}\n";
    echo "  Subtotal: " . number_format($subtotal, 0) . " | Previous Due: " . number_format($previousDue, 0) . " | Total: " . number_format($totalAmount, 0) . "\n";
    echo "\n";
    
    // Update for next iteration
    $previousNextDue = $nextDue;
    $invoiceCounter++;
}

echo "=== DONE ===\n";
echo "\nExpected monthly summary:\n";
echo "  May: 2,000 BDT (billing month)\n";
echo "  June: 2,000 BDT (carry forward)\n";
echo "  July: 2,000 BDT (carry forward)\n";
echo "  August: 2,000 BDT (billing month, replaces May)\n";
echo "  September: 2,000 BDT (carry forward)\n";
echo "  October: 2,000 BDT (carry forward)\n";
echo "  November: 4,000 BDT (billing month)\n";
echo "  December: 4,000 BDT (carry forward)\n";
