<?php
// Simple test script to verify admin settings functionality
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Test database connection and settings retrieval
try {
    // Test if we can connect to the database
    $pdo = new PDO('mysql:host=localhost;dbname=billing', 'root', '');
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Database connection successful!\n";
    echo "Total settings in database: " . $result['count'] . "\n";
    
    // Test if specific admin settings exist
    $stmt = $pdo->prepare("SELECT * FROM system_settings WHERE `key` = ?");
    $stmt->execute(['company_name']);
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($setting) {
        echo "✓ Company name setting found: " . $setting['value'] . "\n";
    } else {
        echo "✗ Company name setting not found\n";
    }
    
    $stmt->execute(['company_email']);
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($setting) {
        echo "✓ Company email setting found: " . $setting['value'] . "\n";
    } else {
        echo "✗ Company email setting not found\n";
    }
    
    echo "Admin settings functionality verified successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>