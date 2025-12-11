<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Models\CustomerProduct;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== IMPLEMENTING MONTHLY INVOICE SYSTEM ===\n\n";

try {
    DB::beginTransaction();
    
    // Step 1: Convert existing rolling invoices to monthly invoices
    echo "Step 1: Converting rolling invoices to monthly invoices...\n";
    
    $rollingInvoices = Invoice::where('is_active_rolling', 1)->get();
    
    foreach ($rollingInvoices as $rollingInvoice) {
        echo "Converting invoice: {$rollingInvoice->invoice_number}\n";
        
        $customerProduct = CustomerProduct::find($rollingInvoice->cp_id);
        if (!$customerProduct) continue;
        
        $assignDate = Carbon::parse($customerProduct->assign_date);
        $currentDate = Carbon::now();
        
        // Generate monthly invoices from assignment date to current date
        $monthsToGenerate = [];
        $tempDate = $assignDate->copy()->startOfMonth();
        
        while ($tempDate <= $currentDate) {
            $monthsToGenerate[] = $tempDate->format('Y-m');
            $tempDate->addMonth();
        }
        
        echo "   Generating " . count($monthsToGenerate) . " monthly invoices\n";
        
        // Create separate invoice for each month
        $previousDue = 0;
        $totalPaidSoFar = 0;
        
        // Get all payments for this rolling invoice
        $allPayments = Payment::where('invoice_id', $rollingInvoice->invoice_id)
            ->orderBy('payment_date')
            ->get();
        
        foreach ($monthsToGenerate as $index => $month) {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
            $isAssignmentMonth = $monthDate->format('Y-m') === $assignDate->format('Y-m');
            $isBillingMonth = ($index % $customerProduct->billing_cycle_months) === 0;
            
            // Calculate subtotal for this month
            $subtotal = 0;
            if ($isAssignmentMonth || $isBillingMonth) {
                $subtotal = $rollingInvoice->subtotal; // Use original subtotal
            }
            
            // Calculate total amount
            $totalAmount = $subtotal + $previousDue;
            
            // Calculate payments made in this month
            $monthPayments = $allPayments->filter(function($payment) use ($monthDate) {
                return Carbon::parse($payment->payment_date)->format('Y-m') === $monthDate->format('Y-m');
            });
            
            $monthlyPaymentAmount = $monthPayments->sum('amount');
            $totalPaidSoFar += $monthlyPaymentAmount;
            
            // Calculate next due
            $nextDue = max(0, $totalAmount - $monthlyPaymentAmount);
            
            // Determine status
            $status = 'unpaid';
            if ($monthlyPaymentAmount >= $totalAmount) {
                $status = 'paid';
                $nextDue = 0;
            } elseif ($monthlyPaymentAmount > 0) {
                $status = 'partial';
            }
            
            // Create monthly invoice
            $monthlyInvoice = Invoice::create([
                'invoice_number' => generateMonthlyInvoiceNumber($month, $customerProduct->c_id),
                'cp_id' => $rollingInvoice->cp_id,
                'issue_date' => $monthDate->format('Y-m-d'),
                'previous_due' => $previousDue,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'received_amount' => $monthlyPaymentAmount,
                'next_due' => $nextDue,
                'status' => $status,
                'notes' => "Monthly invoice for {$monthDate->format('F Y')}",
                'created_by' => 1
            ]);
            
            // Explicitly set is_active_rolling to 0 after creation
            DB::table('invoices')
                ->where('invoice_id', $monthlyInvoice->invoice_id)
                ->update(['is_active_rolling' => 0]);
            
            // Move payments to this monthly invoice
            foreach ($monthPayments as $payment) {
                $payment->update(['invoice_id' => $monthlyInvoice->invoice_id]);
            }
            
            echo "     Created: {$monthlyInvoice->invoice_number} - {$month} - ৳{$totalAmount} - ৳{$monthlyPaymentAmount} - ৳{$nextDue}\n";
            
            // Set previous due for next month
            $previousDue = $nextDue;
        }
        
        // Deactivate the rolling invoice (keep original status, just mark as inactive)
        $rollingInvoice->update([
            'is_active_rolling' => 0,
            'notes' => ($rollingInvoice->notes ?? '') . "\n[CONVERTED] Converted to monthly invoices on " . now()
        ]);
        
        echo "   ✅ Converted successfully\n\n";
    }
    
    DB::commit();
    echo "✅ Monthly invoice system implemented successfully!\n\n";
    
    // Verify the conversion
    echo "=== VERIFICATION ===\n";
    $monthlyInvoices = Invoice::where('is_active_rolling', 0)
        ->where('status', '!=', 'converted')
        ->count();
    $rollingInvoices = Invoice::where('is_active_rolling', 1)->count();
    
    echo "Monthly Invoices: {$monthlyInvoices}\n";
    echo "Rolling Invoices: {$rollingInvoices}\n";
    
    if ($rollingInvoices === 0) {
        echo "✅ All invoices converted to monthly system\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

/**
 * Generate monthly invoice number
 */
function generateMonthlyInvoiceNumber($month, $customerId) {
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    $year = $monthDate->format('y');
    $monthNum = $monthDate->format('m');
    
    // Format: INV-YY-MM-CID-XXX (e.g., INV-25-03-002-001)
    $prefix = "INV-{$year}-{$monthNum}-" . str_pad($customerId, 3, '0', STR_PAD_LEFT) . "-";
    
    // Find the highest number for this month and customer
    $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
        ->orderBy('invoice_number', 'desc')
        ->first();
    
    if ($lastInvoice) {
        $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }
    
    return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
}

echo "\n=== IMPLEMENTATION COMPLETE ===\n";