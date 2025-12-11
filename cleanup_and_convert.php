<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

echo "=== CLEANUP AND CONVERT TO MONTHLY INVOICES ===\n\n";

try {
    DB::beginTransaction();
    
    // Step 1: Delete the incorrectly created invoices (keep only the original)
    echo "Step 1: Cleaning up incorrectly created invoices...\n";
    
    $invoicesToDelete = Invoice::where('invoice_number', 'like', 'INV-25-%-002-%')->get();
    echo "Found " . $invoicesToDelete->count() . " invoices to delete\n";
    
    foreach ($invoicesToDelete as $invoice) {
        echo "   Deleting: {$invoice->invoice_number}\n";
        $invoice->delete();
    }
    
    // Step 2: Reset the original invoice to rolling status
    echo "\nStep 2: Resetting original invoice...\n";
    $originalInvoice = Invoice::where('invoice_number', 'INV-25-03-0001')->first();
    if ($originalInvoice) {
        $originalInvoice->update(['is_active_rolling' => 1]);
        echo "   Reset: {$originalInvoice->invoice_number} to rolling status\n";
    }
    
    DB::commit();
    echo "✅ Cleanup completed\n\n";
    
    // Step 3: Now implement the correct monthly system
    echo "Step 3: Implementing correct monthly invoice system...\n";
    
    // Instead of converting, let's create a proper monthly system
    // We'll keep the original invoice but create separate monthly invoices
    
    if ($originalInvoice) {
        $customerProduct = $originalInvoice->customerProduct;
        $assignDate = \Carbon\Carbon::parse($customerProduct->assign_date);
        $currentDate = \Carbon\Carbon::now();
        
        // Get the payment that was made
        $payment = Payment::where('invoice_id', $originalInvoice->invoice_id)->first();
        $paymentMonth = $payment ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m') : null;
        $paymentAmount = $payment ? $payment->amount : 0;
        
        echo "   Original payment: ৳{$paymentAmount} in month {$paymentMonth}\n";
        
        // Create monthly invoices
        $monthsToCreate = [];
        $tempDate = $assignDate->copy()->startOfMonth();
        
        while ($tempDate <= $currentDate) {
            $monthsToCreate[] = $tempDate->format('Y-m');
            $tempDate->addMonth();
        }
        
        echo "   Creating " . count($monthsToCreate) . " monthly invoices...\n";
        
        $previousDue = 0;
        
        foreach ($monthsToCreate as $index => $month) {
            $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $month);
            $isBillingMonth = ($index % $customerProduct->billing_cycle_months) === 0;
            
            // Calculate subtotal (only in billing months)
            $subtotal = $isBillingMonth ? $originalInvoice->subtotal : 0;
            $totalAmount = $subtotal + $previousDue;
            
            // Check if payment was made in this month
            $monthlyPayment = ($month === $paymentMonth) ? $paymentAmount : 0;
            $nextDue = max(0, $totalAmount - $monthlyPayment);
            
            // Determine status
            $status = 'unpaid';
            if ($monthlyPayment >= $totalAmount) {
                $status = 'paid';
                $nextDue = 0;
            } elseif ($monthlyPayment > 0) {
                $status = 'partial';
            }
            
            // Create monthly invoice
            $monthlyInvoice = new Invoice([
                'invoice_number' => generateMonthlyInvoiceNumber($month, $customerProduct->c_id),
                'cp_id' => $originalInvoice->cp_id,
                'issue_date' => $monthDate->format('Y-m-d'),
                'previous_due' => $previousDue,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'received_amount' => $monthlyPayment,
                'next_due' => $nextDue,
                'status' => $status,
                'notes' => "Monthly invoice for {$monthDate->format('F Y')} - " . 
                          ($isBillingMonth ? "Billing month" : "Carry forward"),
                'created_by' => 1
            ]);
            
            // Save without triggering model events that might set is_active_rolling
            $monthlyInvoice->saveQuietly();
            
            // Explicitly set is_active_rolling to 0
            DB::table('invoices')
                ->where('invoice_id', $monthlyInvoice->invoice_id)
                ->update(['is_active_rolling' => 0]);
            
            // Move payment to this month's invoice if applicable
            if ($month === $paymentMonth && $payment) {
                $payment->update(['invoice_id' => $monthlyInvoice->invoice_id]);
            }
            
            echo "     Created: {$monthlyInvoice->invoice_number} - ৳{$totalAmount} - ৳{$monthlyPayment} paid - ৳{$nextDue} due\n";
            
            // Set previous due for next month
            $previousDue = $nextDue;
        }
        
        // Deactivate the original rolling invoice
        $originalInvoice->update([
            'is_active_rolling' => 0,
            'notes' => ($originalInvoice->notes ?? '') . "\n[CONVERTED] Converted to monthly invoices on " . now()
        ]);
        
        echo "   ✅ Monthly invoice system created successfully!\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

/**
 * Generate monthly invoice number
 */
function generateMonthlyInvoiceNumber($month, $customerId) {
    $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $month);
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

echo "\n=== CLEANUP AND CONVERSION COMPLETE ===\n";