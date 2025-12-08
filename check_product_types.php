<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $types = DB::table('product_type')->get();
    echo "Product Types in Database:\n";
    echo "========================\n";
    foreach($types as $type) {
        echo $type->id . ': ' . $type->name;
        if ($type->descriptions) {
            echo ' (' . $type->descriptions . ')';
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>