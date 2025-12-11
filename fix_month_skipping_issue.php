<?php

echo "=== FIXING MONTH SKIPPING ISSUE ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check 1: Analyze the getCustomersForMonth logic issue
    echo "1. ANALYZING getCustomersForMonth LOGIC ISSUE:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Test August 2025 - should show but doesn't
    $month = '2025-08';
    echo "   Testing August 2025 ($month):\n";
    
    // Current restrictive query (what's causing the issue)
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.c_id, c.name, c.customer_id
        FROM customers c
        JOIN customer_to_products cp ON c.c_id = cp.c_id
        JOIN invoices i ON cp.cp_id = i.cp_id
        WHERE c.is_active = 1
        AND cp.status = 'active'
        AND cp.is_active = 1
        AND i.is_active_rolling = 1
        AND YEAR(i.issue_date) = 2025
        AND MONTH(i.issue_date) = 8
    ");
    $stmt->execute();
    $restrictiveResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Current restrictive query results: " . count($restrictiveResults) . " customers\n";
    foreach ($restrictiveResults as $customer) {
        echo "   - {$customer['name']} (ID: {$customer['customer_id']})\n";
    }
    
    // Check what invoices exist for August 2025
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_id,
            i.invoice_number,
            DATE_FORMAT(i.issue_date, '%Y-%m-%d') as issue_date,
            c.name as customer_name,
            i.total_amount,
            i.next_due
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE i.is_active_rolling = 1
        AND YEAR(i.issue_date) = 2025
        AND MONTH(i.issue_date) = 8
    ");
    $stmt->execute();
    $augustInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n   Invoices issued in August 2025: " . count($augustInvoices) . "\n";
    foreach ($augustInvoices as $invoice) {
        echo "   - Invoice {$invoice['invoice_number']} for {$invoice['customer_name']}\n";
        echo "     Issue Date: {$invoice['issue_date']}, Amount: ৳{$invoice['total_amount']}, Due: ৳{$invoice['next_due']}\n";
    }
    
    // Check if there are rolling invoices that should be visible in August
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_id,
            i.invoice_number,
            DATE_FORMAT(i.issue_date, '%Y-%m-%d') as issue_date,
            c.name as customer_name,
            c.customer_id,
            i.total_amount,
            i.next_due,
            i.status
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE i.is_active_rolling = 1
        AND c.is_active = 1
        AND cp.status = 'active'
        AND cp.is_active = 1
        AND (
            -- Invoices issued in August 2025
            (YEAR(i.issue_date) = 2025 AND MONTH(i.issue_date) = 8)
            OR
            -- Rolling invoices that should be visible in August (issued before but still active)
            (i.issue_date <= '2025-08-31' AND i.next_due > 0)
        )
    ");
    $stmt->execute();
    $rollingInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n   Rolling invoices that should be visible in August 2025: " . count($rollingInvoices) . "\n";
    foreach ($rollingInvoices as $invoice) {
        echo "   - Invoice {$invoice['invoice_number']} for {$invoice['customer_name']} ({$invoice['customer_id']})\n";
        echo "     Issue Date: {$invoice['issue_date']}, Status: {$invoice['status']}, Due: ৳{$invoice['next_due']}\n";
    }
    
    // Check 2: Test December 2025 (current month)
    echo "\n2. TESTING DECEMBER 2025 (CURRENT MONTH):\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $month = '2025-12';
    echo "   Testing December 2025 ($month):\n";
    
    // Current restrictive query for December
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.c_id, c.name, c.customer_id
        FROM customers c
        JOIN customer_to_products cp ON c.c_id = cp.c_id
        JOIN invoices i ON cp.cp_id = i.cp_id
        WHERE c.is_active = 1
        AND cp.status = 'active'
        AND cp.is_active = 1
        AND i.is_active_rolling = 1
        AND YEAR(i.issue_date) = 2025
        AND MONTH(i.issue_date) = 12
    ");
    $stmt->execute();
    $decemberRestrictive = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Current restrictive query results: " . count($decemberRestrictive) . " customers\n";
    
    // Check if there are active customers who should appear in December
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.c_id, c.name, c.customer_id
        FROM customers c
        JOIN customer_to_products cp ON c.c_id = cp.c_id
        WHERE c.is_active = 1
        AND cp.status = 'active'
        AND cp.is_active = 1
        AND cp.assign_date <= '2025-12-31'
    ");
    $stmt->execute();
    $activeCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Active customers who should appear in December: " . count($activeCustomers) . "\n";
    foreach ($activeCustomers as $customer) {
        echo "   - {$customer['name']} (ID: {$customer['customer_id']})\n";
    }
    
    // Check 3: Proposed fix
    echo "\n3. PROPOSED FIX FOR getCustomersForMonth:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    echo "   The current method only looks for invoices issued in the specific month.\n";
    echo "   For a rolling invoice system, we need to show customers who:\n";
    echo "   1. Have invoices issued in this month, OR\n";
    echo "   2. Have active rolling invoices (carry forward), OR\n";
    echo "   3. Are active customers (for current month)\n\n";
    
    // Test the improved query for August 2025
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.c_id, c.name, c.customer_id
        FROM customers c
        JOIN customer_to_products cp ON c.c_id = cp.c_id
        LEFT JOIN invoices i ON cp.cp_id = i.cp_id AND i.is_active_rolling = 1
        WHERE c.is_active = 1
        AND cp.status = 'active'
        AND cp.is_active = 1
        AND cp.assign_date <= '2025-08-31'
        AND (
            -- Has invoices issued in this month
            (YEAR(i.issue_date) = 2025 AND MONTH(i.issue_date) = 8)
            OR
            -- Has active rolling invoices (carry forward)
            (i.issue_date <= '2025-08-31' AND i.next_due > 0)
            OR
            -- For current month, show all active customers
            ('2025-08' = DATE_FORMAT(NOW(), '%Y-%m'))
        )
    ");
    $stmt->execute();
    $improvedAugust = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Improved query for August 2025: " . count($improvedAugust) . " customers\n";
    foreach ($improvedAugust as $customer) {
        echo "   - {$customer['name']} (ID: {$customer['customer_id']})\n";
    }
    
    // Test the improved query for December 2025
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.c_id, c.name, c.customer_id
        FROM customers c
        JOIN customer_to_products cp ON c.c_id = cp.c_id
        LEFT JOIN invoices i ON cp.cp_id = i.cp_id AND i.is_active_rolling = 1
        WHERE c.is_active = 1
        AND cp.status = 'active'
        AND cp.is_active = 1
        AND cp.assign_date <= '2025-12-31'
        AND (
            -- Has invoices issued in this month
            (YEAR(i.issue_date) = 2025 AND MONTH(i.issue_date) = 12)
            OR
            -- Has active rolling invoices (carry forward)
            (i.issue_date <= '2025-12-31' AND i.next_due > 0)
            OR
            -- For current month, show all active customers
            ('2025-12' = DATE_FORMAT(NOW(), '%Y-%m'))
        )
    ");
    $stmt->execute();
    $improvedDecember = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n   Improved query for December 2025: " . count($improvedDecember) . " customers\n";
    foreach ($improvedDecember as $customer) {
        echo "   - {$customer['name']} (ID: {$customer['customer_id']})\n";
    }
    
    echo "\n4. SUMMARY:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    echo "   ❌ Current getCustomersForMonth method is too restrictive\n";
    echo "   ❌ Only shows customers with invoices issued in specific month\n";
    echo "   ❌ Misses customers with carry-forward invoices\n";
    echo "   ❌ Misses current month customers without invoices\n";
    echo "   ✅ Need to update method to include all relevant customers\n";
    
    echo "\n=== ANALYSIS COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}