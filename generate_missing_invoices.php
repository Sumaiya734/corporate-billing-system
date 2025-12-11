<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\Customerproduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

echo "=== GENERATING MISSING INVOICES ===\n\n";

// Login as admin (user ID 1)
Auth::loginUsingId(1);

// Get all active customer products
$customerProducts = Customerproduct::where('status', 'active')
    ->where('is_active', 1)
    ->with(['product', 'customer'])
    ->get();

echo "Found " . $customerProducts->count() . " active customer products\n\n";

$months = ['2025-08', '2025-11']; // August and November (May created manually)

foreach ($months as $month) {
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "GENERATING INVOICES FOR: " . $monthDate->format('F Y') . "\n";
    echo str_repeat('=', 70) . "\n\n";
    
    $generated = 0;
    $skipped = 0;
    
    foreach ($customerProducts as $cp) {
        $assignDate = Carbon::parse($cp->assign_date);
        $billingCycle = $cp->billing_cycle_months;
        
        // Check if this month is a billing month for this customer
        $monthsDiff = $assignDate->diffInMonths($monthDate);
        $isDueMonth = ($monthsDiff >= 0 && $monthsDiff % $billingCycle == 0);
        
        if (!$isDueMonth) {
            continue; // Skip if not a billing month
        }
        
        // Check if invoice already exists for this month
        $existingInvoice = Invoice::where('cp_id', $cp->cp_id)
            ->whereYear('issue_date', $monthDate->year)
            ->whereMonth('issue_date', $monthDate->month)
            ->first();
        
        if ($existingInvoice) {
            echo "  SKIP: {$cp->customer->name} - Invoice already exists ({$existingInvoice->invoice_number})\n";
            $skipped++;
            continue;
        }
        
        // Get the FIRST invoice for this customer product to use as reference for subtotal
        $firstInvoice = Invoice::where('cp_id', $cp->cp_id)
            ->orderBy('issue_date', 'asc')
            ->first();
        
        // Use the subtotal from the first invoice
        // If no first invoice exists, this means we're creating the very first one
        // In that case, we need to get the subtotal from somewhere else (manual input or calculation)
        if ($firstInvoice) {
            $productAmount = $firstInvoice->subtotal;
        } else {
            // For the first invoice, we need to determine the subtotal
            // This should come from the original invoice or be calculated
            // For now, let's skip if there's no reference invoice
            echo "  SKIP: {$cp->customer->name} - No reference invoice found. Please create the first invoice manually.\n";
            $skipped++;
            continue;
        }
        
        // Calculate which billing period this month belongs to
        // Period number = months since assign_date / billing_cycle
        $monthsSinceAssign = $assignDate->diffInMonths($monthDate);
        $periodNumber = floor($monthsSinceAssign / $billingCycle);
        
        // Check if there's already an invoice from this SAME period
        $periodStartMonth = $assignDate->copy()->addMonths($periodNumber * $billingCycle);
        $periodEndMonth = $periodStartMonth->copy()->addMonths($billingCycle);
        
        $samePeriodInvoice = Invoice::where('cp_id', $cp->cp_id)
            ->where('issue_date', '>=', $periodStartMonth)
            ->where('issue_date', '<', $periodEndMonth)
            ->where('issue_date', '<', $monthDate->startOfMonth())
            ->first();
        
        if ($samePeriodInvoice) {
            // This is for the SAME billing period, so no previous_due
            // Cancel the previous invoice from the same period
            $samePeriodInvoice->update([
                'status' => 'cancelled',
                'notes' => ($samePeriodInvoice->notes ?? '') . ' [Replaced by ' . $monthDate->format('F Y') . ' invoice - same billing period]'
            ]);
            
            $previousDue = 0;
            echo "  NOTE: {$cp->customer->name} - Same billing period as {$samePeriodInvoice->invoice_number}\n";
            echo "        Period {$periodNumber}: {$periodStartMonth->format('M Y')} - {$periodEndMonth->format('M Y')}\n";
            echo "        Cancelled previous invoice (same period)\n";
            echo "        No previous_due added (same period)\n";
        } else {
            // This is a NEW billing period, so include previous_due from ALL earlier invoices
            $previousDue = Invoice::where('cp_id', $cp->cp_id)
                ->where('issue_date', '<', $periodStartMonth)
                ->whereIn('status', ['unpaid', 'partial', 'confirmed'])
                ->where('next_due', '>', 0)
                ->sum('next_due');
            
            if ($previousDue > 0) {
                echo "  NOTE: {$cp->customer->name} - New billing period {$periodNumber}\n";
                echo "        Period: {$periodStartMonth->format('M Y')} - {$periodEndMonth->format('M Y')}\n";
                echo "        Including previous_due: ৳" . number_format($previousDue, 2) . "\n";
            } else {
                echo "  NOTE: {$cp->customer->name} - New billing period {$periodNumber}\n";
                echo "        Period: {$periodStartMonth->format('M Y')} - {$periodEndMonth->format('M Y')}\n";
            }
        }
        
        $totalAmount = $productAmount + $previousDue;
        
        // Generate invoice number
        $year = $monthDate->format('y');
        $monthNum = $monthDate->format('m');
        $prefix = 'INV-' . $year . '-' . $monthNum . '-';
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        $invoiceNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'cp_id' => $cp->cp_id,
            'issue_date' => $monthDate->format('Y-m-d'),
            'previous_due' => $previousDue,
            'subtotal' => $productAmount,
            'total_amount' => $totalAmount,
            'received_amount' => 0,
            'next_due' => $totalAmount,
            'status' => 'unpaid',
            'notes' => 'Auto-generated for billing cycle',
            'created_by' => 1
        ]);
        
        echo "  ✓ CREATED: {$cp->customer->name} - {$invoiceNumber}\n";
        echo "    Subtotal: ৳" . number_format($productAmount, 2) . "\n";
        echo "    Previous Due: ৳" . number_format($previousDue, 2) . "\n";
        echo "    Total: ৳" . number_format($totalAmount, 2) . "\n\n";
        
        $generated++;
    }
    
    echo "\nSummary for {$monthDate->format('F Y')}:\n";
    echo "  Generated: $generated invoices\n";
    echo "  Skipped: $skipped invoices\n";
}

echo "\n\n=== DONE ===\n";
echo "Refresh your billing page to see the updated amounts!\n";
