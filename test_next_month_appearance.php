<?php

echo "=== TESTING NEXT MONTH APPEARANCE AFTER CLOSING ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Test 1: Check current months with invoices
    echo "1. MONTHS WITH INVOICES (CURRENT STATE):\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(issue_date, '%Y-%m') as month,
            DATE_FORMAT(issue_date, '%M %Y') as display_month,
            COUNT(*) as invoice_count,
            SUM(total_amount) as total_amount,
            SUM(received_amount) as received_amount,
            SUM(next_due) as due_amount
        FROM invoices 
        WHERE is_active_rolling = 1
        GROUP BY DATE_FORMAT(issue_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute();
    $monthsWithInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Months that currently have invoices:\n";
    foreach ($monthsWithInvoices as $month) {
        echo "   - {$month['display_month']} ({$month['month']}): ";
        echo "{$month['invoice_count']} invoices, ";
        echo "Total ৳" . number_format($month['total_amount'], 0) . ", ";
        echo "Due ৳" . number_format($month['due_amount'], 0) . "\n";
    }
    
    // Test 2: Simulate what happens after month closing
    echo "\n2. SIMULATION: AFTER CLOSING JANUARY 2025:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Check if January is closed
    $stmt = $pdo->prepare("
        SELECT is_closed, carried_forward 
        FROM billing_periods 
        WHERE billing_month = '2025-01'
    ");
    $stmt->execute();
    $janStatus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($janStatus && $janStatus['is_closed']) {
        echo "   January 2025 is CLOSED\n";
        echo "   Carried forward: ৳" . number_format($janStatus['carried_forward'], 0) . "\n";
        
        // Check if February has carry forward invoices
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as invoice_count,
                SUM(previous_due) as total_previous_due,
                SUM(total_amount) as total_amount
            FROM invoices 
            WHERE YEAR(issue_date) = 2025 
            AND MONTH(issue_date) = 2
            AND is_active_rolling = 1
            AND previous_due > 0
        ");
        $stmt->execute();
        $febCarryForward = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   February 2025 carry forward invoices: {$febCarryForward['invoice_count']}\n";
        echo "   Total previous due: ৳" . number_format($febCarryForward['total_previous_due'], 0) . "\n";
        echo "   Total amount: ৳" . number_format($febCarryForward['total_amount'], 0) . "\n";
        
        if ($febCarryForward['invoice_count'] > 0) {
            echo "   ✅ February should appear on billing-invoices page\n";
        } else {
            echo "   ❌ February might not appear - no carry forward invoices found\n";
        }
    } else {
        echo "   January 2025 is NOT closed yet\n";
        echo "   After closing January, February should appear with carry forward invoices\n";
    }
    
    // Test 3: Check the new logic
    echo "\n3. TESTING NEW LOGIC:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $currentMonth = date('Y-m');
    $nextMonth = date('Y-m', strtotime('+1 month'));
    
    echo "   Current month: $currentMonth\n";
    echo "   Next month: $nextMonth\n";
    
    // Check if next month has invoices
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as invoice_count
        FROM invoices 
        WHERE DATE_FORMAT(issue_date, '%Y-%m') = ?
        AND is_active_rolling = 1
    ");
    $stmt->execute([$nextMonth]);
    $nextMonthInvoices = $stmt->fetchColumn();
    
    echo "   Next month invoices: $nextMonthInvoices\n";
    
    if ($nextMonthInvoices > 0) {
        echo "   ✅ Next month should appear on billing-invoices page\n";
    } else {
        echo "   ⚠️  Next month has no invoices yet - will appear after month closing\n";
    }
    
    // Test 4: Check all months that should appear
    echo "\n4. MONTHS THAT SHOULD APPEAR ON BILLING-INVOICES PAGE:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Get assignment months
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE_FORMAT(assign_date, '%Y-%m') as month
        FROM customer_to_products 
        WHERE status = 'active' AND is_active = 1
        ORDER BY month ASC
    ");
    $stmt->execute();
    $assignmentMonths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get months with invoices
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE_FORMAT(issue_date, '%Y-%m') as month
        FROM invoices 
        WHERE is_active_rolling = 1
        ORDER BY month ASC
    ");
    $stmt->execute();
    $invoiceMonths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Combine and filter
    $allMonths = array_unique(array_merge($assignmentMonths, $invoiceMonths, [$currentMonth]));
    sort($allMonths);
    
    // Filter to show up to next month
    $maxMonth = date('Y-m', strtotime('+1 month'));
    $visibleMonths = array_filter($allMonths, function($month) use ($maxMonth) {
        return $month <= $maxMonth;
    });
    
    echo "   Months that should be visible:\n";
    foreach ($visibleMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Check if month has activity
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT c.c_id) as customers,
                COALESCE(SUM(i.total_amount), 0) as total_amount,
                COALESCE(SUM(i.received_amount), 0) as received_amount,
                COALESCE(SUM(i.next_due), 0) as due_amount
            FROM customers c
            JOIN customer_to_products cp ON c.c_id = cp.c_id
            LEFT JOIN invoices i ON cp.cp_id = i.cp_id 
                AND YEAR(i.issue_date) = ? 
                AND MONTH(i.issue_date) = ?
                AND i.is_active_rolling = 1
            WHERE c.is_active = 1
            AND cp.status = 'active'
            AND cp.is_active = 1
            AND cp.assign_date <= LAST_DAY(STR_TO_DATE(?, '%Y-%m'))
        ");
        $stmt->execute([
            date('Y', strtotime($month . '-01')),
            date('n', strtotime($month . '-01')),
            $month
        ]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $hasActivity = $activity['customers'] > 0 || $activity['total_amount'] > 0;
        $status = $hasActivity ? '✅ Will show' : '❌ No activity';
        
        echo "   - $displayMonth ($month): {$activity['customers']} customers, ৳" . number_format($activity['total_amount'], 0) . " total - $status\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    echo "\nEXPECTED BEHAVIOR:\n";
    echo "1. When you close January 2025, February 2025 should appear immediately\n";
    echo "2. February will show carry forward invoices with previous_due amounts\n";
    echo "3. The billing-invoices page will auto-refresh and show the new month\n";
    echo "4. Next month appears because it now has invoices (carry forward)\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}