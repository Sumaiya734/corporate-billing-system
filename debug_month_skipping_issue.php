<?php

echo "=== DEBUGGING MONTH SKIPPING ISSUE ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check 1: What months have invoices
    echo "1. MONTHS WITH INVOICES:\n";
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
    
    echo "   All months with invoices:\n";
    foreach ($monthsWithInvoices as $month) {
        echo "   - {$month['display_month']} ({$month['month']}): ";
        echo "{$month['invoice_count']} invoices, ";
        echo "Total ৳" . number_format($month['total_amount'], 0) . ", ";
        echo "Due ৳" . number_format($month['due_amount'], 0) . "\n";
    }
    
    // Check 2: What months have customer assignments
    echo "\n2. MONTHS WITH CUSTOMER ASSIGNMENTS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(assign_date, '%Y-%m') as month,
            DATE_FORMAT(assign_date, '%M %Y') as display_month,
            COUNT(*) as assignment_count
        FROM customer_to_products 
        WHERE status = 'active' AND is_active = 1
        GROUP BY DATE_FORMAT(assign_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute();
    $assignmentMonths = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Months with customer assignments:\n";
    foreach ($assignmentMonths as $month) {
        echo "   - {$month['display_month']} ({$month['month']}): ";
        echo "{$month['assignment_count']} assignments\n";
    }
    
    // Check 3: What months are closed
    echo "\n3. MONTHS THAT ARE CLOSED:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            billing_month,
            is_closed,
            carried_forward,
            closed_at
        FROM billing_periods 
        ORDER BY billing_month ASC
    ");
    $stmt->execute();
    $closedMonths = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($closedMonths)) {
        echo "   No months are officially closed\n";
    } else {
        echo "   Officially closed months:\n";
        foreach ($closedMonths as $month) {
            $displayMonth = date('F Y', strtotime($month['billing_month'] . '-01'));
            echo "   - $displayMonth ({$month['billing_month']}): ";
            echo "Closed " . ($month['is_closed'] ? 'Yes' : 'No') . ", ";
            echo "Carried Forward ৳" . number_format($month['carried_forward'], 0) . "\n";
        }
    }
    
    // Check 4: Simulate the getDynamicMonthlySummary logic
    echo "\n4. SIMULATING BILLING-INVOICES PAGE LOGIC:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $currentMonth = date('Y-m');
    echo "   Current month: $currentMonth\n";
    
    // Get assignment months
    $assignmentMonthsList = array_column($assignmentMonths, 'month');
    
    // Get invoice months
    $invoiceMonthsList = array_column($monthsWithInvoices, 'month');
    
    // Combine all months
    $allMonths = array_unique(array_merge($assignmentMonthsList, $invoiceMonthsList, [$currentMonth]));
    sort($allMonths);
    
    echo "\n   All possible months (before filtering):\n";
    foreach ($allMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        echo "   - $displayMonth ($month)\n";
    }
    
    // Apply the filtering logic
    echo "\n   Applying visibility filter:\n";
    $visibleMonths = [];
    
    foreach ($allMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Check if month should be visible
        if ($month <= $currentMonth) {
            echo "   - $displayMonth: ✅ VISIBLE (current or past month)\n";
            $visibleMonths[] = $month;
        } else {
            // For future months, check if previous month is closed
            $previousMonth = date('Y-m', strtotime($month . ' -1 month'));
            
            $stmt = $pdo->prepare("
                SELECT is_closed FROM billing_periods 
                WHERE billing_month = ?
            ");
            $stmt->execute([$previousMonth]);
            $isPreviousMonthClosed = $stmt->fetchColumn();
            
            $previousDisplayMonth = date('F Y', strtotime($previousMonth . '-01'));
            
            if ($isPreviousMonthClosed) {
                echo "   - $displayMonth: ✅ VISIBLE (previous month $previousDisplayMonth is closed)\n";
                $visibleMonths[] = $month;
            } else {
                echo "   - $displayMonth: ❌ HIDDEN (previous month $previousDisplayMonth not closed)\n";
            }
        }
    }
    
    // Check 5: Check activity for visible months
    echo "\n5. CHECKING ACTIVITY FOR VISIBLE MONTHS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $finalVisibleMonths = [];
    
    foreach ($visibleMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Check if month has activity
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as invoice_count
            FROM invoices 
            WHERE DATE_FORMAT(issue_date, '%Y-%m') = ?
            AND is_active_rolling = 1
        ");
        $stmt->execute([$month]);
        $hasInvoices = $stmt->fetchColumn() > 0;
        
        // Check if month has customers
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT cp.c_id) as customer_count
            FROM customer_to_products cp
            WHERE cp.status = 'active'
            AND cp.is_active = 1
            AND cp.assign_date <= LAST_DAY(STR_TO_DATE(?, '%Y-%m'))
        ");
        $stmt->execute([$month]);
        $hasCustomers = $stmt->fetchColumn() > 0;
        
        $hasActivity = $hasInvoices || $hasCustomers || $month === $currentMonth;
        
        echo "   - $displayMonth: ";
        echo "Invoices: " . ($hasInvoices ? 'Yes' : 'No') . ", ";
        echo "Customers: " . ($hasCustomers ? 'Yes' : 'No') . ", ";
        echo "Activity: " . ($hasActivity ? 'Yes' : 'No') . "\n";
        
        if ($hasActivity) {
            $finalVisibleMonths[] = $month;
        }
    }
    
    // Check 6: Compare with what you see on the page
    echo "\n6. FINAL COMPARISON:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    echo "   Months that SHOULD appear on billing-invoices page:\n";
    foreach (array_reverse($finalVisibleMonths) as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Get summary for this month
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT cp.c_id) as customers,
                COALESCE(SUM(i.total_amount), 0) as total_amount,
                COALESCE(SUM(i.received_amount), 0) as received_amount,
                COALESCE(SUM(i.next_due), 0) as due_amount
            FROM customer_to_products cp
            LEFT JOIN invoices i ON cp.cp_id = i.cp_id 
                AND DATE_FORMAT(i.issue_date, '%Y-%m') = ?
                AND i.is_active_rolling = 1
            WHERE cp.status = 'active'
            AND cp.is_active = 1
            AND cp.assign_date <= LAST_DAY(STR_TO_DATE(?, '%Y-%m'))
        ");
        $stmt->execute([$month, $month]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   - $displayMonth: ";
        echo "{$summary['customers']} customers, ";
        echo "৳" . number_format($summary['total_amount'], 0) . " total, ";
        echo "৳" . number_format($summary['due_amount'], 0) . " due\n";
    }
    
    echo "\n   What you see on the page (from screenshot):\n";
    echo "   - July 2025\n";
    echo "   - May 2025\n";
    echo "   - March 2025\n";
    echo "   - February 2025\n";
    echo "   - January 2025\n";
    
    echo "\n7. ANALYSIS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $expectedMonths = array_reverse($finalVisibleMonths);
    $actualMonths = ['2025-07', '2025-05', '2025-03', '2025-02', '2025-01'];
    
    echo "   Expected months: " . implode(', ', $expectedMonths) . "\n";
    echo "   Actual months: " . implode(', ', $actualMonths) . "\n";
    
    $missingMonths = array_diff($expectedMonths, $actualMonths);
    $extraMonths = array_diff($actualMonths, $expectedMonths);
    
    if (!empty($missingMonths)) {
        echo "   ❌ Missing months: " . implode(', ', $missingMonths) . "\n";
    }
    
    if (!empty($extraMonths)) {
        echo "   ❌ Extra months: " . implode(', ', $extraMonths) . "\n";
    }
    
    if (empty($missingMonths) && empty($extraMonths)) {
        echo "   ✅ Months match perfectly\n";
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}