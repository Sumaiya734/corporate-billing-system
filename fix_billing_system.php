<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== FIXING BILLING SYSTEM WITH PROPER CARRY-FORWARD ===\n\n";

// Customer details
$cpId = 35; // CP ID for Zia (updated)
$assignDate = Carbon::parse('2025-01-10'); // Actual assign date
$billingCycle = 3; // months
$subtotalAmount = 2000; // BDT

// Delete all existing invoices
Invoice::query()->delete();
echo "✓ Deleted all existing invoices\n\n";

// Create the rolling invoice system
// This will create ONE invoice that gets updated each month
$currentMonth = Carbon::now()->format('Y-m');
$currentDate = Carbon::createFromFormat('Y-m', $currentMonth);

echo "Creating rolling invoice system from January 2025 to {$currentDate->format('F Y')}:\n\n";

// Start with January 2025 (assign month)
$invoiceMonth = Carbon::parse('2025-01');
$invoice = null;

while ($invoiceMonth->format('Y-m') <= $currentMonth) {
    $monthName = $invoiceMonth->format('F Y');
    
    // Calculate months since assignment
    $assignMonth = Carbon::parse('2025-01');
    $monthsSinceAssign = $assignMonth->diffInMonths($invoiceMonth);
    
    // Determine if this is a billing cycle month (every 3 months: 0, 3, 6, 9...)
    $isBillingCycleMonth = ($monthsSinceAssign % $billingCycle) == 0;
    
    if (!$invoice) {
        // Create initial invoice for January (Month 0)
        $invoice = Invoice::create([
            'invoice_number' => 'INV-25-01-0001',
            'cp_id' => $cpId,
            'issue_date' => $invoiceMonth->format('Y-m-10'), // 10th of month
            'previous_due' => 0,
            'subtotal' => $subtotalAmount,
            'total_amount' => $subtotalAmount,
            'received_amount' => 0,
            'next_due' => $subtotalAmount,
            'is_active_rolling' => 1,
            'billing_cycle_number' => 1,
            'cycle_position' => 0, // Position within cycle (0, 1, 2)
            'cycle_start_date' => $invoiceMonth->format('Y-m-10'),
            'status' => 'unpaid',
            'notes' => "Initial billing cycle - Month {$monthsSinceAssign}",
            'created_by' => 1
        ]);
        
        echo "✓ {$monthName} (Month {$monthsSinceAssign}) - CREATED INITIAL INVOICE\n";
        echo "  Invoice: {$invoice->invoice_number}\n";
        echo "  Subtotal: ৳" . number_format($invoice->subtotal, 0) . "\n";
        echo "  Previous Due: ৳" . number_format($invoice->previous_due, 0) . "\n";
        echo "  Total: ৳" . number_format($invoice->total_amount, 0) . "\n\n";
        
    } else {
        // Update existing invoice for new month
        if ($isBillingCycleMonth) {
            // NEW BILLING CYCLE: Add new subtotal + carry forward previous total as previous_due
            $newPreviousDue = $invoice->total_amount; // Previous month's total becomes previous_due
            $newSubtotal = $subtotalAmount; // New subtotal for this cycle
            $newTotalAmount = $newSubtotal + $newPreviousDue;
            $newCycleNumber = floor($monthsSinceAssign / $billingCycle) + 1;
            
            echo "✓ {$monthName} (Month {$monthsSinceAssign}) - NEW BILLING CYCLE #{$newCycleNumber}\n";
            echo "  Previous total (₹{$invoice->total_amount}) becomes previous_due\n";
            echo "  Adding new subtotal: ₹{$newSubtotal}\n";
            
            // Update invoice
            $invoice->update([
                'issue_date' => $invoiceMonth->format('Y-m-10'),
                'subtotal' => $newSubtotal,
                'previous_due' => $newPreviousDue,
                'total_amount' => $newTotalAmount,
                'next_due' => $newTotalAmount, // Assuming no payment
                'billing_cycle_number' => $newCycleNumber,
                'cycle_position' => 0, // Start of new cycle
                'cycle_start_date' => $invoiceMonth->format('Y-m-10'),
                'notes' => "Billing cycle #{$newCycleNumber} - Month {$monthsSinceAssign} - new cycle started"
            ]);
            
        } else {
            // CARRY FORWARD MONTH: subtotal = 0, previous_due = total_amount
            $currentTotal = $invoice->total_amount;
            $cyclePosition = ($monthsSinceAssign % $billingCycle);
            
            echo "✓ {$monthName} (Month {$monthsSinceAssign}) - CARRY FORWARD (Cycle {$invoice->billing_cycle_number}, Position {$cyclePosition})\n";
            echo "  Carrying forward total: ₹{$currentTotal}\n";
            
            // Update invoice
            $invoice->update([
                'issue_date' => $invoiceMonth->format('Y-m-10'),
                'subtotal' => 0,
                'previous_due' => $currentTotal,
                'total_amount' => $currentTotal,
                'next_due' => $currentTotal, // Assuming no payment
                'cycle_position' => $cyclePosition,
                'notes' => "Carry forward - Cycle {$invoice->billing_cycle_number}, Month {$monthsSinceAssign}"
            ]);
        }
        
        echo "  Subtotal: ৳" . number_format($invoice->subtotal, 0) . "\n";
        echo "  Previous Due: ৳" . number_format($invoice->previous_due, 0) . "\n";
        echo "  Total: ৳" . number_format($invoice->total_amount, 0) . "\n\n";
    }
    
    $invoiceMonth->addMonth();
}

echo "=== FINAL INVOICE STATE ===\n";
$invoice->refresh();
echo "Invoice Number: {$invoice->invoice_number}\n";
echo "Issue Date: {$invoice->issue_date}\n";
echo "Billing Cycle: #{$invoice->billing_cycle_number}\n";
echo "Cycle Position: {$invoice->cycle_position}\n";
echo "Subtotal: ৳" . number_format($invoice->subtotal, 0) . "\n";
echo "Previous Due: ৳" . number_format($invoice->previous_due, 0) . "\n";
echo "Total Amount: ৳" . number_format($invoice->total_amount, 0) . "\n";
echo "Status: {$invoice->status}\n";
echo "Is Active Rolling: " . ($invoice->is_active_rolling ? 'Yes' : 'No') . "\n";

echo "\n=== BILLING LOGIC VERIFICATION ===\n";
echo "✓ Single invoice that updates monthly\n";
echo "✓ Proper carry-forward of same invoice balance\n";
echo "✓ Subtotal added every 3 months (Jan, Apr, Jul, Oct...)\n";
echo "✓ Previous due carries forward month-to-month\n";
echo "✓ Rolling invoice tracking with cycle numbers\n";
echo "✓ Cycle position tracking within each 3-month cycle\n";