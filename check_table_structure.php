<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING TABLE STRUCTURE ===\n\n";

try {
    echo "Invoices table columns:\n";
    $columns = DB::select('DESCRIBE invoices');
    foreach($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
    
    echo "\nSample invoice data:\n";
    $invoice = DB::table('invoices')->first();
    if ($invoice) {
        foreach($invoice as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    } else {
        echo "  No invoices found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}