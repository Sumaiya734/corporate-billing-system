<?php
// Simple script to test if system settings table exists and has data
echo "Testing system settings...\n";

// Assuming we're in the Laravel environment
require_once 'vendor/autoload.php';

try {
    // Create a simple PDO connection (you'll need to adjust credentials)
    $pdo = new PDO('mysql:host=localhost;dbname=corporate_billing', 'root', '');
    $stmt = $pdo->query("SELECT * FROM system_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($settings)) {
        echo "No settings found in database\n";
    } else {
        echo "Found " . count($settings) . " settings:\n";
        foreach ($settings as $setting) {
            echo "- {$setting['key']}: {$setting['value']} ({$setting['description']})\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>