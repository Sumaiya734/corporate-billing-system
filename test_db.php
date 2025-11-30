<?php
require_once 'vendor/autoload.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database configuration
$config = [
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'dbname' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}", 
        $config['username'], 
        $config['password']
    );
    
    // Check if service_charge column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM invoices LIKE 'service_charge'");
    $result = $stmt->fetchAll();
    
    if (count($result) > 0) {
        echo "SUCCESS: service_charge column exists\n";
    } else {
        echo "ERROR: service_charge column does not exist\n";
    }
    
    // Check if vat_percentage column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM invoices LIKE 'vat_percentage'");
    $result = $stmt->fetchAll();
    
    if (count($result) > 0) {
        echo "SUCCESS: vat_percentage column exists\n";
    } else {
        echo "ERROR: vat_percentage column does not exist\n";
    }
    
    // Check if vat_amount column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM invoices LIKE 'vat_amount'");
    $result = $stmt->fetchAll();
    
    if (count($result) > 0) {
        echo "SUCCESS: vat_amount column exists\n";
    } else {
        echo "ERROR: vat_amount column does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}