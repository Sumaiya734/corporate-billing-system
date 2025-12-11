<?php

echo "=== TESTING STRICT MONTH LOGIC ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    $currentMonth = date('Y-m');
    echo "Current month: $currentMonth\n\n";
    
    // Test 1: Check what months exist
    echo "1. EXISTING MONTHS:\n";
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
    
    echo "   Assignment months: " . implode(', ', $assignmentMonths) . "\n";
    
    // Get invoice months
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE_FORMAT(issue_date, '%Y-%m') as month
        FROM invoices
        WHERE is_active_rolling = 1
        ORDER BY month ASC
    ");
    $stmt->execute();
    $invoiceMonths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   Invoice months: " . (empty($invoiceMonths) ? 'None' : implode(', ', $invoiceMonths)) . "\n";
    
    // Combine all months
    $allMonths = array_unique(array_merge($assignmentMonths, $invoiceMonths, [$currentMonth]));
    sort($allMonths);
    
    echo "   All possible months: " . implode(', ', $allMonths) . "\n";
    
    // Test 2: Apply strict filtering
    echo "\n2. APPLYING STRICT FILTERING:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $visibleMonths = [];
    
    foreach ($allMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Apply the strict logic
        if ($month === $currentMonth) {
            $shouldShow = true;
            $reason = "current month";
        } elseif ($month < $currentMonth) {
            $shouldShow = true;
            $reason = "past month";
        } else {
            // Future month - check if previous month is closed
            $previousMonth = date('Y-m', strtotime($month . ' -1 month'));
            
            $stmt = $pdo->prepare("
                SELECT is_closed FROM billing_periods 
                WHERE billing_month = ?
            ");
            $stmt->execute([$previousMonth]);
            $isPreviousMonthClosed = $stmt->fetchColumn();
            
            $shouldShow = (bool)$isPreviousMonthClosed;
            $previousDisplayMonth = date('F Y', strtotime($previousMonth . '-01'));
            $reason = $isPreviousMonthClosed ? 
                "previous month ($previousDisplayMonth) is closed" : 
                "previous month ($previousDisplayMonth) NOT closed";
        }
        
        echo "   - $displayMonth ($month): ";
        if ($shouldShow) {
            echo "✅ SHOW ($reason)\n";
            $visibleMonths[] = $month;
        } else {
            echo "❌ HIDE ($reason)\n";
        }
    }
    
    // Test 3: Check activity for visible months
    echo "\n3. CHECKING ACTIVITY FOR VISIBLE MONTHS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $finalMonths = [];
    
    foreach ($visibleMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Check if month has customers
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT c.c_id) as customer_count
            FROM customers c
            JOIN customer_to_products cp ON c.c_id = cp.c_id
            WHERE c.is_active = 1
            AND cp.status = 'active'
            AND cp.is_active = 1
            AND cp.assign_date <= LAST_DAY(STR_TO_DATE(?, '%Y-%m'))
        ");
        $stmt->execute([$month]);
        $customerCount = $stmt->fetchColumn();
        
        // Check if month has invoices
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as invoice_count
            FROM invoices
            WHERE is_active_rolling = 1
            AND YEAR(issue_date) = YEAR(STR_TO_DATE(?, '%Y-%m'))
            AND MONTH(issue_date) = MONTH(STR_TO_DATE(?, '%Y-%m'))
        ");
        $stmt->execute([$month, $month]);
        $invoiceCount = $stmt->fetchColumn();
        
        $hasActivity = $customerCount > 0 || $invoiceCount > 0 || $month === $currentMonth;
        
        echo "   - $displayMonth: ";
        echo "Customers: $customerCount, Invoices: $invoiceCount, ";
        echo "Activity: " . ($hasActivity ? 'Yes' : 'No') . "\n";
        
        if ($hasActivity) {
            $finalMonths[] = $month;
        }
    }
    
    // Test 4: Final result
    echo "\n4. FINAL RESULT:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    echo "   Months that should appear on billing-invoices page:\n";
    $reversedMonths = array_reverse($finalMonths);
    foreach ($reversedMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        echo "   - $displayMonth ($month)\n";
    }
    
    echo "\n   STRICT RULES APPLIED:\n";
    echo "   ✅ Current month always shows\n";
    echo "   ✅ Past months show if they have activity\n";
    echo "   ✅ Future months ONLY show if previous month is officially closed\n";
    echo "   ✅ No month shows without actual activity (customers or invoices)\n";
    
    // Test 5: Create sample billing period to test
    echo "\n5. TESTING WITH SAMPLE CLOSED MONTH:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Let's say we close January 2025
    $testMonth = '2025-01';
    $nextMonth = '2025-02';
    
    echo "   If we close January 2025, should February 2025 appear?\n";
    
    // Simulate closing January
    $stmt = $pdo->prepare("
        INSERT INTO billing_periods (billing_month, is_closed, carried_forward, closed_at, created_at, updated_at)
        VALUES (?, 1, 0, NOW(), NOW(), NOW())
        ON DUPLICATE KEY UPDATE is_closed = 1, closed_at = NOW()
    ");
    $stmt->execute([$testMonth]);
    
    echo "   ✅ Simulated closing January 2025\n";
    
    // Check if February should now show
    $stmt = $pdo->prepare("
        SELECT is_closed FROM billing_periods 
        WHERE billing_month = ?
    ");
    $stmt->execute([$testMonth]);
    $isJanuaryClosed = $stmt->fetchColumn();
    
    echo "   - January 2025 closed: " . ($isJanuaryClosed ? 'Yes' : 'No') . "\n";
    echo "   - February 2025 should show: " . ($isJanuaryClosed ? 'Yes' : 'No') . "\n";
    
    // Clean up test data
    $stmt = $pdo->prepare("DELETE FROM billing_periods WHERE billing_month = ?");
    $stmt->execute([$testMonth]);
    echo "   ✅ Cleaned up test data\n";
    
    echo "\n=== TEST COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}