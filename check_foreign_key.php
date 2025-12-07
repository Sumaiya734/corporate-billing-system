<?php
require_once 'vendor/autoload.php';

// Create a test to check foreign key constraint
$app = require_once 'bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Check if the foreign key constraint exists
    $result = DB::select("
        SELECT 
            CONSTRAINT_NAME,
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'invoices'
        AND COLUMN_NAME = 'cp_id'
        AND REFERENCED_TABLE_NAME = 'customer_to_products'
        AND REFERENCED_COLUMN_NAME = 'cp_id'
    ");
    
    if (!empty($result)) {
        echo "SUCCESS: Foreign key constraint exists!\n";
        echo "Constraint details:\n";
        foreach ($result as $row) {
            echo "- Constraint Name: " . $row->CONSTRAINT_NAME . "\n";
            echo "- Table: " . $row->TABLE_NAME . "\n";
            echo "- Column: " . $row->COLUMN_NAME . "\n";
            echo "- Referenced Table: " . $row->REFERENCED_TABLE_NAME . "\n";
            echo "- Referenced Column: " . $row->REFERENCED_COLUMN_NAME . "\n";
        }
    } else {
        echo "WARNING: Foreign key constraint not found.\n";
    }
    
    // Also check if the cp_id column exists in invoices table
    $columnExists = DB::select("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'invoices'
        AND COLUMN_NAME = 'cp_id'
    ");
    
    if (!empty($columnExists)) {
        echo "\nINFO: cp_id column exists in invoices table.\n";
    } else {
        echo "\nWARNING: cp_id column does not exist in invoices table.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}