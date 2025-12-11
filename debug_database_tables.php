<?php

echo "=== DEBUGGING DATABASE TABLES ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check 1: List all tables
    echo "1. DATABASE TABLES:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    
    // Check 2: Check invoices table
    echo "\n2. INVOICES TABLE:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (in_array('invoices', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM invoices");
        $invoiceCount = $stmt->fetchColumn();
        echo "   Total invoices: $invoiceCount\n";
        
        if ($invoiceCount > 0) {
            $stmt = $pdo->query("
                SELECT 
                    invoice_number,
                    DATE_FORMAT(issue_date, '%Y-%m-%d') as issue_date,
                    total_amount,
                    next_due,
                    is_active_rolling
                FROM invoices 
                ORDER BY issue_date DESC 
                LIMIT 10
            ");
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "   Recent invoices:\n";
            foreach ($invoices as $invoice) {
                echo "   - {$invoice['invoice_number']}: {$invoice['issue_date']}, ";
                echo "Total ৳{$invoice['total_amount']}, Due ৳{$invoice['next_due']}, ";
                echo "Active: {$invoice['is_active_rolling']}\n";
            }
        }
    } else {
        echo "   ❌ invoices table not found!\n";
    }
    
    // Check 3: Check billing_periods table
    echo "\n3. BILLING_PERIODS TABLE:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (in_array('billing_periods', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM billing_periods");
        $periodCount = $stmt->fetchColumn();
        echo "   Total billing periods: $periodCount\n";
        
        if ($periodCount > 0) {
            $stmt = $pdo->query("
                SELECT 
                    billing_month,
                    is_closed,
                    carried_forward,
                    closed_at
                FROM billing_periods 
                ORDER BY billing_month DESC
            ");
            $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "   All billing periods:\n";
            foreach ($periods as $period) {
                $displayMonth = date('F Y', strtotime($period['billing_month'] . '-01'));
                $isClosed = $period['is_closed'] ? 'CLOSED' : 'OPEN';
                echo "   - $displayMonth ({$period['billing_month']}): $isClosed, ";
                echo "Carried ৳" . number_format($period['carried_forward'], 0) . "\n";
            }
        }
    } else {
        echo "   ❌ billing_periods table not found!\n";
    }
    
    // Check 4: Check customer_to_products table
    echo "\n4. CUSTOMER_TO_PRODUCTS TABLE:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (in_array('customer_to_products', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM customer_to_products WHERE status = 'active' AND is_active = 1");
        $activeCount = $stmt->fetchColumn();
        echo "   Active customer products: $activeCount\n";
        
        if ($activeCount > 0) {
            $stmt = $pdo->query("
                SELECT 
                    cp.cp_id,
                    c.name as customer_name,
                    DATE_FORMAT(cp.assign_date, '%Y-%m-%d') as assign_date,
                    cp.billing_cycle_months
                FROM customer_to_products cp
                JOIN customers c ON cp.c_id = c.c_id
                WHERE cp.status = 'active' AND cp.is_active = 1
                ORDER BY cp.assign_date DESC
                LIMIT 5
            ");
            $customerProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "   Recent active assignments:\n";
            foreach ($customerProducts as $cp) {
                echo "   - {$cp['customer_name']}: Assigned {$cp['assign_date']}, ";
                echo "Cycle: {$cp['billing_cycle_months']} months\n";
            }
        }
    } else {
        echo "   ❌ customer_to_products table not found!\n";
    }
    
    // Check 5: Test the original debug query
    echo "\n5. TESTING ORIGINAL DEBUG QUERY:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(issue_date, '%Y-%m') as month,
                DATE_FORMAT(issue_date, '%M %Y') as display_month,
                COUNT(*) as invoice_count
            FROM invoices 
            WHERE is_active_rolling = 1
            GROUP BY DATE_FORMAT(issue_date, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute();
        $monthsWithInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   Query executed successfully\n";
        echo "   Found " . count($monthsWithInvoices) . " months with invoices\n";
        
        foreach ($monthsWithInvoices as $month) {
            echo "   - {$month['display_month']} ({$month['month']}): {$month['invoice_count']} invoices\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Query failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}