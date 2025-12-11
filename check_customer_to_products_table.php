<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select('DESCRIBE customer_to_products');
    echo "customer_to_products table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field}: {$column->Type}" . ($column->Null === 'NO' ? ' (NOT NULL)' : '') . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}