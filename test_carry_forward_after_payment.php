<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Payment;

echo "=== TESTING CARRY FORWARD AFTER PAYMENT ===\n\n";

try {
    // Test scenario: Find an invoice with partial payment
    echo "1. FINDING INVOICES WITH PARTIAL PAYMENTS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $invoicesWithPayments = Invoice::with(['customerProduct.customer', 'payments'])
        ->where('received_amount', '>', 0)
        ->where('next_due', '>', 0)
        ->where('is_active_rolling', 1)
        ->get();
    
    echo "   Found " . $invoicesWithPayments->count() . " invoices with partial payments\n\n";
    
    foreach ($invoicesWithPayments->take(3) as $invoice) {
        $customer = $invoice->customerProduct->customer ?? null;
        
        echo "   Invoice: {$invoice->invoice_number}\n";
        echo "   Customer: " . ($customer->name ?? 'Unknown') . "\n";
        echo "   Total Amount: ৳" . number_format($invoice->total_amount, 0) . "\n";
        echo "   Received Amount: ৳" . number_format($invoice->received_amount, 0) . "\n";
        echo "   Next Due: ৳" . number_format($invoice->next_due, 0) . "\n";
        echo "   Status: {$invoice->status}\n";
        
        // Show payments for this invoice
        $payments = $invoice->payments;
        echo "   Payments (" . $payments->count() . "):\n";
        foreach ($payments as $payment) {
            echo "     - ৳" . number_format($payment->amount, 0) . " on " . Carbon::parse($payment->payment_date)->format('M j, Y') . "\n";
        }
        
        echo "   " . str_repeat("-", 30) . "\n";
    }
    
    // Test 2: Check calculation logic
    echo "\n2. TESTING CALCULATION LOGIC:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if ($invoicesWithPayments->count() > 0) {
        $testInvoice = $invoicesWithPayments->first();
        
        echo "   Test Invoice: {$testInvoice->invoice_number}\n";
        echo "   Database Values:\n";
        echo "     total_amount: ৳" . number_format($testInvoice->total_amount, 0) . "\n";
        echo "     received_amount: ৳" . number_format($testInvoice->received_amount, 0) . "\n";
        echo "     next_due: ৳" . number_format($testInvoice->next_due, 0) . "\n";
        
        // Calculate what next_due should be
        $calculatedNextDue = $testInvoice->total_amount - $testInvoice->received_amount;
        echo "   Calculated next_due: ৳" . number_format($calculatedNextDue, 0) . "\n";
        
        if (abs($testInvoice->next_due - $calculatedNextDue) < 1) {
            echo "   ✅ next_due calculation is CORRECT\n";
        } else {
            echo "   ❌ next_due calculation is INCORRECT\n";
            echo "   Difference: ৳" . number_format(abs($testInvoice->next_due - $calculatedNextDue), 0) . "\n";
        }
    }
    
    // Test 3: Simulate carry forward
    echo "\n3. SIMULATING CARRY FORWARD LOGIC:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if ($invoicesWithPayments->count() > 0) {
        $testInvoice = $invoicesWithPayments->first();
        $currentMonth = Carbon::parse($testInvoice->issue_date)->format('Y-m');
        $nextMonth = Carbon::parse($testInvoice->issue_date)->addMonth()->format('Y-m');
        
        echo "   Current Month: $currentMonth\n";
        echo "   Next Month: $nextMonth\n";
        echo "   Amount to carry forward: ৳" . number_format($testInvoice->next_due, 0) . "\n";
        
        // Check if next month invoice already exists
        $nextMonthInvoice = Invoice::where('cp_id', $testInvoice->cp_id)
            ->where('is_active_rolling', 1)
            ->whereYear('issue_date', Carbon::parse($testInvoice->issue_date)->addMonth()->year)
            ->whereMonth('issue_date', Carbon::parse($testInvoice->issue_date)->addMonth()->month)
            ->first();
        
        if ($nextMonthInvoice) {
            echo "   Next month invoice EXISTS: {$nextMonthInvoice->invoice_number}\n";
            echo "   Current previous_due: ৳" . number_format($nextMonthInvoice->previous_due, 0) . "\n";
            echo "   Current subtotal: ৳" . number_format($nextMonthInvoice->subtotal, 0) . "\n";
            echo "   Current total_amount: ৳" . number_format($nextMonthInvoice->total_amount, 0) . "\n";
            
            // Simulate what would happen after carry forward
            $newPreviousDue = $nextMonthInvoice->previous_due + $testInvoice->next_due;
            $newTotalAmount = $nextMonthInvoice->subtotal + $newPreviousDue;
            
            echo "   After carry forward:\n";
            echo "     new previous_due: ৳" . number_format($newPreviousDue, 0) . "\n";
            echo "     new total_amount: ৳" . number_format($newTotalAmount, 0) . "\n";
        } else {
            echo "   Next month invoice does NOT exist - would create new one\n";
            echo "   New invoice would have:\n";
            echo "     subtotal: ৳0 (no new charges)\n";
            echo "     previous_due: ৳" . number_format($testInvoice->next_due, 0) . " (carried forward)\n";
            echo "     total_amount: ৳" . number_format($testInvoice->next_due, 0) . "\n";
        }
    }
    
    // Test 4: Check recent payments
    echo "\n4. CHECKING RECENT PAYMENTS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $recentPayments = Payment::with(['invoice.customerProduct.customer'])
        ->orderBy('payment_date', 'desc')
        ->take(5)
        ->get();
    
    foreach ($recentPayments as $payment) {
        $invoice = $payment->invoice;
        $customer = $invoice->customerProduct->customer ?? null;
        
        echo "   Payment: ৳" . number_format($payment->amount, 0) . " on " . Carbon::parse($payment->payment_date)->format('M j, Y') . "\n";
        echo "   Invoice: {$invoice->invoice_number}\n";
        echo "   Customer: " . ($customer->name ?? 'Unknown') . "\n";
        echo "   Invoice after payment:\n";
        echo "     total_amount: ৳" . number_format($invoice->total_amount, 0) . "\n";
        echo "     received_amount: ৳" . number_format($invoice->received_amount, 0) . "\n";
        echo "     next_due: ৳" . number_format($invoice->next_due, 0) . "\n";
        echo "   " . str_repeat("-", 25) . "\n";
    }
    
    // Test 5: Check if month closing has been tested
    echo "\n5. MONTH CLOSING STATUS CHECK:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $testMonth = '2025-01';
    $isMonthClosed = DB::table('billing_periods')
        ->where('billing_month', $testMonth)
        ->where('is_closed', 1)
        ->exists();
    
    echo "   Month $testMonth is " . ($isMonthClosed ? 'CLOSED' : 'OPEN') . "\n";
    
    if (!$isMonthClosed) {
        echo "   ⚠️  Month is still open - carry forward won't happen until month is closed\n";
        echo "   To test carry forward:\n";
        echo "   1. Make a partial payment on an invoice\n";
        echo "   2. Close the month using the 'Close Month' button\n";
        echo "   3. Check next month's invoices for carried forward amounts\n";
    } else {
        echo "   Month is closed - checking carry forward results...\n";
        
        $nextMonth = '2025-02';
        $carriedForwardInvoices = Invoice::with(['customerProduct.customer'])
            ->whereYear('issue_date', 2025)
            ->whereMonth('issue_date', 2)
            ->where('previous_due', '>', 0)
            ->get();
        
        echo "   Found " . $carriedForwardInvoices->count() . " invoices with carried forward amounts in $nextMonth\n";
        
        foreach ($carriedForwardInvoices as $invoice) {
            $customer = $invoice->customerProduct->customer ?? null;
            echo "     {$invoice->invoice_number} - " . ($customer->name ?? 'Unknown') . " - Previous Due: ৳" . number_format($invoice->previous_due, 0) . "\n";
        }
    }
    
    echo "\n=== CARRY FORWARD TEST COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}