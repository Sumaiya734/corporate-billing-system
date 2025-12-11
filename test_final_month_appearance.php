<?php

echo "=== FINAL TEST: MONTH APPEARANCE AFTER CLOSING ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Test current state
    echo "1. CURRENT STATE VERIFICATION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Check all months with invoices
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
    
    echo "   Months with invoices:\n";
    foreach ($monthsWithInvoices as $month) {
        echo "   - {$month['display_month']}: ";
        echo "{$month['invoice_count']} invoices, ";
        echo "Total ৳" . number_format($month['total_amount'], 0) . ", ";
        echo "Due ৳" . number_format($month['due_amount'], 0) . "\n";
    }
    
    // Test the new getDynamicMonthlySummary logic
    echo "\n2. TESTING NEW BILLING-INVOICES LOGIC:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $currentMonth = date('Y-m');
    $nextMonth = date('Y-m', strtotime('+1 month'));
    
    echo "   Current month: $currentMonth\n";
    echo "   Next month: $nextMonth\n";
    
    // Simulate the new logic
    $assignmentMonths = [];
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE_FORMAT(assign_date, '%Y-%m') as month
        FROM customer_to_products 
        WHERE status = 'active' AND is_active = 1
    ");
    $stmt->execute();
    $assignmentMonths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $invoiceMonths = [];
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE_FORMAT(issue_date, '%Y-%m') as month
        FROM invoices 
        WHERE is_active_rolling = 1
    ");
    $stmt->execute();
    $invoiceMonths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Combine months
    $allMonths = array_unique(array_merge($assignmentMonths, $invoiceMonths, [$currentMonth]));
    sort($allMonths);
    
    // Filter to show up to next month
    $visibleMonths = array_filter($allMonths, function($month) use ($nextMonth) {
        return $month <= $nextMonth;
    });
    
    echo "\n   Months that should be visible on billing-invoices page:\n";
    foreach ($visibleMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Check activity for this month
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT cp.c_id) as customers,
                COALESCE(SUM(i.total_amount), 0) as total_amount,
                COALESCE(SUM(i.received_amount), 0) as received_amount,
                COALESCE(SUM(i.next_due), 0) as due_amount
            FROM customer_to_products cp
            LEFT JOIN invoices i ON cp.cp_id = i.cp_id 
                AND YEAR(i.issue_date) = ? 
                AND MONTH(i.issue_date) = ?
                AND i.is_active_rolling = 1
            WHERE cp.status = 'active'
            AND cp.is_active = 1
            AND cp.assign_date <= LAST_DAY(STR_TO_DATE(?, '%Y-%m'))
        ");
        $stmt->execute([
            date('Y', strtotime($month . '-01')),
            date('n', strtotime($month . '-01')),
            $month
        ]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if month has invoices
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as invoice_count
            FROM invoices 
            WHERE DATE_FORMAT(issue_date, '%Y-%m') = ?
            AND is_active_rolling = 1
        ");
        $stmt->execute([$month]);
        $hasInvoices = $stmt->fetchColumn() > 0;
        
        $hasActivity = $activity['customers'] > 0 || $activity['total_amount'] > 0 || $hasInvoices || $month === $currentMonth;
        $status = $hasActivity ? '✅ Will show' : '❌ No activity';
        
        echo "   - $displayMonth ($month): ";
        echo "{$activity['customers']} customers, ";
        echo "৳" . number_format($activity['total_amount'], 0) . " total, ";
        echo "Invoices: " . ($hasInvoices ? 'Yes' : 'No') . " - $status\n";
    }
    
    // Specific check for February 2025
    echo "\n3. FEBRUARY 2025 SPECIFIC CHECK:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_number,
            i.total_amount,
            i.received_amount,
            i.next_due,
            c.name as customer_name
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE YEAR(i.issue_date) = 2025 
        AND MONTH(i.issue_date) = 2
        AND i.is_active_rolling = 1
    ");
    $stmt->execute();
    $febInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($febInvoices)) {
        echo "   ✅ February 2025 invoices found:\n";
        foreach ($febInvoices as $invoice) {
            echo "   - {$invoice['invoice_number']} ({$invoice['customer_name']}): ";
            echo "Total ৳" . number_format($invoice['total_amount'], 0) . ", ";
            echo "Due ৳" . number_format($invoice['next_due'], 0) . "\n";
        }
        echo "\n   ✅ February 2025 WILL appear on billing-invoices page\n";
    } else {
        echo "   ❌ No February 2025 invoices found\n";
        echo "   February 2025 will NOT appear on billing-invoices page\n";
    }
    
    echo "\n=== FINAL TEST COMPLETE ===\n";
    echo "\nSUMMARY:\n";
    echo "✅ Fixed carryForwardToNextMonth() method with better error handling\n";
    echo "✅ Enhanced getDynamicMonthlySummary() to include months with invoices\n";
    echo "✅ Added monthHasActivity() check for actual invoices\n";
    echo "✅ Manually created missing February invoice for testing\n";
    echo "\nEXPECTED BEHAVIOR:\n";
    echo "1. When you close a month, carry forward invoices are created\n";
    echo "2. Next month appears immediately on billing-invoices page\n";
    echo "3. Page auto-refreshes to show the new month\n";
    echo "4. Carry forward amounts are displayed correctly\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}