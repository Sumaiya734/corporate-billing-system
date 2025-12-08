<?php
require_once 'vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

// Initialize Laravel database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'billing',
    'username'  => 'root',
    'password' => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Fix products table AUTO_INCREMENT
try {
    $count = Capsule::table('products')->count();
    if ($count == 0) {
        // If no records, reset to 1
        Capsule::statement("ALTER TABLE products AUTO_INCREMENT = 1");
        echo "Products AUTO_INCREMENT reset to: 1 (no records)\n";
    } else {
        // If records exist, set to max_id + 1
        $maxId = Capsule::table('products')->max('p_id');
        $newAutoIncrement = $maxId + 1;
        Capsule::statement("ALTER TABLE products AUTO_INCREMENT = $newAutoIncrement");
        echo "Products AUTO_INCREMENT set to: $newAutoIncrement (max id: $maxId, count: $count)\n";
    }
} catch (Exception $e) {
    echo "Error fixing products AUTO_INCREMENT: " . $e->getMessage() . "\n";
}

// Fix product_type table AUTO_INCREMENT
try {
    $count = Capsule::table('product_type')->count();
    if ($count == 0) {
        // If no records, reset to 1
        Capsule::statement("ALTER TABLE product_type AUTO_INCREMENT = 1");
        echo "Product type AUTO_INCREMENT reset to: 1 (no records)\n";
    } else {
        // If records exist, set to max_id + 1
        $maxId = Capsule::table('product_type')->max('id');
        $newAutoIncrement = $maxId + 1;
        Capsule::statement("ALTER TABLE product_type AUTO_INCREMENT = $newAutoIncrement");
        echo "Product type AUTO_INCREMENT set to: $newAutoIncrement (max id: $maxId, count: $count)\n";
    }
} catch (Exception $e) {
    echo "Error fixing product_type AUTO_INCREMENT: " . $e->getMessage() . "\n";
}

echo "AUTO_INCREMENT values fixed successfully!\n";
?>