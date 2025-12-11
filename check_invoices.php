<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\Customer;
use Carbon\Carbon;

echo "=== CHECKING INVOICE DATA ===\n\n";

// Check invoices for May to December 2025
$months = ['2025-05', '2025-08', '2025-09', '2025-10', '2025-11', '2025-12'];

foreach ($months as $month) {
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "MONTH: " . $monthDate->format('F Y') . " ($month)\n";
    echo str_repeat('=', 70) . "\n";
    
    // Get invoices issued in this month
    $invoices = Invoice::whereYear('issue_date', $monthDate->year)
        ->whereMonth('issue_date', $monthDate->month)
        ->with('customerProduct.customer')
        ->get();
    
    if ($invoices->isEmpty()) {
        echo "No invoices issued in this month.\n";
    } else {
        foreach ($invoices as $invoice) {
            $customerName = $invoice->customerProduct->customer->name ?? 'Unknown';
            
            echo "\nInvoice: {$invoice->invoice_number}\n";
            echo "  Customer: $customerName\n";
            echo "  Issue Date: {$invoice->issue_date->format('Y-m-d')}\n";
            echo "  Previous Due: ৳" . number_format($invoice->previous_due, 2) . "\n";
            echo "  Subtotal: ৳" . number_format($invoice->subtotal, 2) . "\n";
            echo "  Total Amount: ৳" . number_format($invoice->total_amount, 2) . "\n";
            echo "  Received: ৳" . number_format($invoice->received_amount, 2) . "\n";
            echo "  Next Due: ৳" . number_format($invoice->next_due, 2) . "\n";
            echo "  Status: {$invoice->status}\n";
        }
    }
    
    // Also check for unpaid invoices from previous months that should appear here
    $carriedForward = Invoice::where('issue_date', '<', $monthDate->startOfMonth())
        ->whereIn('status', ['unpaid', 'partial', 'confirmed'])
        ->where('next_due', '>', 0)
        ->with('customerProduct.customer')
        ->get();
    
    if (!$carriedForward->isEmpty()) {
        echo "\n--- CARRIED FORWARD FROM PREVIOUS MONTHS ---\n";
        foreach ($carriedForward as $invoice) {
            $customerName = $invoice->customerProduct->customer->name ?? 'Unknown';
            echo "  {$invoice->invoice_number} ({$customerName}): ৳" . number_format($invoice->next_due, 2) . " unpaid\n";
        }
    }
}

echo "\n\n=== SUMMARY ===\n";
echo "Total Invoices: " . Invoice::count() . "\n";
echo "Unpaid Invoices: " . Invoice::whereIn('status', ['unpaid', 'partial'])->count() . "\n";
echo "Total Unpaid Amount: ৳" . number_format(Invoice::whereIn('status', ['unpaid', 'partial'])->sum('next_due'), 2) . "\n";
