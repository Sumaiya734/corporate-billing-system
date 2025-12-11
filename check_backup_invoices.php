<?php

echo "=== CHECKING BACKUP INVOICES ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check backup invoices
    $stmt = $pdo->query("SELECT COUNT(*) FROM invoices_backup");
    $backupCount = $stmt->fetchColumn();
    echo "Invoices in backup table: $backupCount\n\n";
    
    if ($backupCount > 0) {
        $stmt = $pdo->query("
            SELECT 
                invoice_number,
                DATE_FORMAT(issue_date, '%Y-%m-%d') as issue_date,
                total_amount,
                next_due,
                is_active_rolling,
                status
            FROM invoices_backup 
            ORDER BY issue_date DESC 
            LIMIT 10
        ");
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Recent backup invoices:\n";
        foreach ($invoices as $invoice) {
            echo "- {$invoice['invoice_number']}: {$invoice['issue_date']}, ";
            echo "Total ৳{$invoice['total_amount']}, Due ৳{$invoice['next_due']}, ";
            echo "Active: {$invoice['is_active_rolling']}, Status: {$invoice['status']}\n";
        }
        
        // Check months in backup
        echo "\nMonths with backup invoices:\n";
        $stmt = $pdo->query("
            SELECT 
                DATE_FORMAT(issue_date, '%Y-%m') as month,
                DATE_FORMAT(issue_date, '%M %Y') as display_month,
                COUNT(*) as invoice_count
            FROM invoices_backup 
            WHERE is_active_rolling = 1
            GROUP BY DATE_FORMAT(issue_date, '%Y-%m')
            ORDER BY month ASC
        ");
        $months = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($months as $month) {
            echo "- {$month['display_month']} ({$month['month']}): {$month['invoice_count']} invoices\n";
        }
    }
    
    echo "\n=== CHECK COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}