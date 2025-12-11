<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

echo "=== DEBUGGING CONVERSION ISSUE ===\n\n";

try {
    // Check all invoices
    $allInvoices = Invoice::all();
    echo "Total invoices in database: " . $allInvoices->count() . "\n\n";
    
    foreach ($allInvoices as $invoice) {
        echo "Invoice: {$invoice->invoice_number}\n";
        echo "   ID: {$invoice->invoice_id}\n";
        echo "   Issue Date: {$invoice->issue_date}\n";
        echo "   Status: {$invoice->status}\n";
        echo "   Is Active Rolling: " . ($invoice->is_active_rolling ? 'Yes' : 'No') . "\n";
        echo "   Total: ৳{$invoice->total_amount}\n";
        echo "   Received: ৳{$invoice->received_amount}\n";
        echo "   Next Due: ৳{$invoice->next_due}\n\n";
    }
    
    // Check if there are any invoices with is_active_rolling = 0
    $monthlyInvoices = Invoice::where('is_active_rolling', 0)->get();
    echo "Invoices with is_active_rolling = 0: " . $monthlyInvoices->count() . "\n";
    
    // Check the database structure
    $columns = DB::select("DESCRIBE invoices");
    echo "\nInvoice table columns:\n";
    foreach ($columns as $column) {
        echo "   {$column->Field} - {$column->Type}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";