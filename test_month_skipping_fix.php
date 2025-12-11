<?php

echo "=== TESTING MONTH SKIPPING FIX ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Test the new getCustomersForMonth logic
    echo "1. TESTING NEW getCustomersForMonth LOGIC:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $testMonths = ['2025-08', '2025-12'];
    $currentMonth = date('Y-m');
    
    foreach ($testMonths as $month) {
        $monthDate = date('Y-m-d', strtotime($month . '-01'));
        $monthEndDate = date('Y-m-t', strtotime($month . '-01'));
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        echo "\n   Testing $displayMonth ($month):\n";
        
        // Simulate the new getCustomersForMonth logic
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.c_id, c.name, c.customer_id
            FROM customers c
            JOIN customer_to_products cp ON c.c_id = cp.c_id
            LEFT JOIN invoices i ON cp.cp_id = i.cp_id AND i.is_active_rolling = 1
            WHERE c.is_active = 1
            AND cp.status = 'active'
            AND cp.is_active = 1
            AND cp.assign_date <= ?
            AND (
                -- Condition 1: Has invoices issued in this month
                (YEAR(i.issue_date) = YEAR(?) AND MONTH(i.issue_date) = MONTH(?))
                OR
                -- Condition 2: Has active rolling invoices (carry forward)
                (i.issue_date <= ? AND i.next_due > 0)
                OR
                -- Condition 3: For current month, show all active customers
                (? = ?)
            )
        ");
        $stmt->execute([
            $monthEndDate,
            $monthDate, $monthDate,
            $monthEndDate,
            $month, $currentMonth
        ]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   Customers found: " . count($customers) . "\n";
        foreach ($customers as $customer) {
            echo "   - {$customer['name']} (ID: {$customer['customer_id']})\n";
        }
        
        // Check what invoices exist for this customer in this month
        if (!empty($customers)) {
            $customerIds = array_column($customers, 'c_id');
            $placeholders = str_repeat('?,', count($customerIds) - 1) . '?';
            
            $stmt = $pdo->prepare("
                SELECT 
                    i.invoice_number,
                    DATE_FORMAT(i.issue_date, '%Y-%m-%d') as issue_date,
                    i.total_amount,
                    i.received_amount,
                    i.next_due,
                    i.status
                FROM invoices i
                JOIN customer_to_products cp ON i.cp_id = cp.cp_id
                WHERE cp.c_id IN ($placeholders)
                AND i.is_active_rolling = 1
                AND (
                    (YEAR(i.issue_date) = YEAR(?) AND MONTH(i.issue_date) = MONTH(?))
                    OR (i.issue_date <= ? AND i.next_due > 0)
                )
                ORDER BY i.issue_date DESC
            ");
            $params = array_merge($customerIds, [$monthDate, $monthDate, $monthEndDate]);
            $stmt->execute($params);
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "   Relevant invoices: " . count($invoices) . "\n";
            foreach ($invoices as $invoice) {
                echo "   - {$invoice['invoice_number']}: Issue {$invoice['issue_date']}, ";
                echo "Total ৳{$invoice['total_amount']}, Due ৳{$invoice['next_due']}, Status: {$invoice['status']}\n";
            }
        }
    }
    
    // Test 2: Simulate the complete getDynamicMonthlySummary flow
    echo "\n2. SIMULATING COMPLETE FLOW:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Get all months that should appear
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE_FORMAT(issue_date, '%Y-%m') as month
        FROM invoices 
        WHERE is_active_rolling = 1
        UNION
        SELECT DISTINCT DATE_FORMAT(assign_date, '%Y-%m') as month
        FROM customer_to_products
        WHERE status = 'active' AND is_active = 1
        UNION
        SELECT ? as month
        ORDER BY month ASC
    ");
    $stmt->execute([$currentMonth]);
    $allMonths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   All possible months: " . implode(', ', $allMonths) . "\n";
    
    $visibleMonths = [];
    foreach ($allMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Apply visibility filter (current or past months, or future if previous is closed)
        if ($month <= $currentMonth) {
            $shouldShow = true;
            $reason = "current or past month";
        } else {
            // Check if previous month is closed
            $previousMonth = date('Y-m', strtotime($month . ' -1 month'));
            $stmt = $pdo->prepare("SELECT is_closed FROM billing_periods WHERE billing_month = ?");
            $stmt->execute([$previousMonth]);
            $isPreviousMonthClosed = $stmt->fetchColumn();
            
            $shouldShow = (bool)$isPreviousMonthClosed;
            $reason = $isPreviousMonthClosed ? "previous month closed" : "previous month not closed";
        }
        
        if ($shouldShow) {
            // Test activity for this month
            $monthDate = date('Y-m-d', strtotime($month . '-01'));
            $monthEndDate = date('Y-m-t', strtotime($month . '-01'));
            
            // Get customers using new logic
            $stmt = $pdo->prepare("
                SELECT DISTINCT c.c_id
                FROM customers c
                JOIN customer_to_products cp ON c.c_id = cp.c_id
                LEFT JOIN invoices i ON cp.cp_id = i.cp_id AND i.is_active_rolling = 1
                WHERE c.is_active = 1
                AND cp.status = 'active'
                AND cp.is_active = 1
                AND cp.assign_date <= ?
                AND (
                    (YEAR(i.issue_date) = YEAR(?) AND MONTH(i.issue_date) = MONTH(?))
                    OR (i.issue_date <= ? AND i.next_due > 0)
                    OR (? = ?)
                )
            ");
            $stmt->execute([
                $monthEndDate,
                $monthDate, $monthDate,
                $monthEndDate,
                $month, $currentMonth
            ]);
            $customerCount = $stmt->rowCount();
            
            // Check if month has activity
            $hasActivity = $customerCount > 0 || $month === $currentMonth;
            
            if ($hasActivity) {
                $visibleMonths[] = $month;
                echo "   ✅ $displayMonth: VISIBLE ($reason, $customerCount customers)\n";
            } else {
                echo "   ❌ $displayMonth: HIDDEN (no activity)\n";
            }
        } else {
            echo "   ❌ $displayMonth: HIDDEN ($reason)\n";
        }
    }
    
    echo "\n3. FINAL RESULT:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    echo "   Months that should appear: " . implode(', ', array_reverse($visibleMonths)) . "\n";
    echo "   Expected from screenshot: 2025-07, 2025-05, 2025-03, 2025-02, 2025-01\n";
    
    $expectedFromScreenshot = ['2025-07', '2025-05', '2025-03', '2025-02', '2025-01'];
    $missing = array_diff(array_reverse($visibleMonths), $expectedFromScreenshot);
    $extra = array_diff($expectedFromScreenshot, array_reverse($visibleMonths));
    
    if (empty($missing) && empty($extra)) {
        echo "   ✅ Perfect match!\n";
    } else {
        if (!empty($missing)) {
            echo "   ➕ New months that should appear: " . implode(', ', $missing) . "\n";
        }
        if (!empty($extra)) {
            echo "   ➖ Months that shouldn't appear: " . implode(', ', $extra) . "\n";
        }
    }
    
    echo "\n=== TEST COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}