<?php

echo "=== CHECKING BILLING PERIODS TABLE ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check 1: What's in the billing_periods table
    echo "1. BILLING PERIODS TABLE CONTENTS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            billing_month,
            is_closed,
            carried_forward,
            closed_at,
            created_at
        FROM billing_periods 
        ORDER BY billing_month ASC
    ");
    $stmt->execute();
    $billingPeriods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($billingPeriods)) {
        echo "   ❌ No records found in billing_periods table!\n";
    } else {
        echo "   Found " . count($billingPeriods) . " records:\n";
        foreach ($billingPeriods as $period) {
            $displayMonth = date('F Y', strtotime($period['billing_month'] . '-01'));
            $isClosed = $period['is_closed'] ? 'Yes' : 'No';
            $carriedForward = number_format($period['carried_forward'], 0);
            echo "   - $displayMonth ({$period['billing_month']}): ";
            echo "Closed $isClosed, Carried ৳$carriedForward\n";
        }
    }
    
    // Check 2: What months have invoices (these should have billing periods)
    echo "\n2. MONTHS WITH INVOICES (should have billing periods):\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(issue_date, '%Y-%m') as month,
            DATE_FORMAT(issue_date, '%M %Y') as display_month,
            COUNT(*) as invoice_count,
            SUM(total_amount) as total_amount,
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
        echo "   - {$month['display_month']} ({$month['month']}): ";
        echo "{$month['invoice_count']} invoices, Due ৳" . number_format($month['due_amount'], 0) . "\n";
    }
    
    // Check 3: Compare and find missing billing periods
    echo "\n3. MISSING BILLING PERIODS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $invoiceMonths = array_column($monthsWithInvoices, 'month');
    $billingPeriodMonths = array_column($billingPeriods, 'billing_month');
    
    $missingPeriods = array_diff($invoiceMonths, $billingPeriodMonths);
    
    if (empty($missingPeriods)) {
        echo "   ✅ All months with invoices have billing periods\n";
    } else {
        echo "   ❌ Missing billing periods for these months:\n";
        foreach ($missingPeriods as $month) {
            $displayMonth = date('F Y', strtotime($month . '-01'));
            echo "   - $displayMonth ($month)\n";
        }
    }
    
    // Check 4: Check if months are being closed properly
    echo "\n4. MONTH CLOSING STATUS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    foreach ($monthsWithInvoices as $month) {
        $monthKey = $month['month'];
        $displayMonth = $month['display_month'];
        
        // Check if this month has a billing period
        $hasBillingPeriod = in_array($monthKey, $billingPeriodMonths);
        
        if ($hasBillingPeriod) {
            // Find the billing period
            $billingPeriod = array_filter($billingPeriods, function($bp) use ($monthKey) {
                return $bp['billing_month'] === $monthKey;
            });
            $billingPeriod = reset($billingPeriod);
            
            $isClosed = $billingPeriod['is_closed'] ? 'CLOSED' : 'OPEN';
            echo "   - $displayMonth: $isClosed";
            if ($billingPeriod['is_closed']) {
                echo " (closed on " . date('Y-m-d', strtotime($billingPeriod['closed_at'])) . ")";
            }
            echo "\n";
        } else {
            echo "   - $displayMonth: NO BILLING PERIOD RECORD\n";
        }
    }
    
    // Check 5: Recommendation
    echo "\n5. ANALYSIS & RECOMMENDATION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (!empty($missingPeriods)) {
        echo "   ❌ ISSUE FOUND: Some months have invoices but no billing period records\n";
        echo "   ❌ This means the month closing process wasn't completed properly\n";
        echo "   ❌ The BillingPeriod::isMonthClosed() method will return false for these months\n";
        echo "   ❌ This causes future months to not appear even when they should\n\n";
        
        echo "   SOLUTION: Create missing billing period records for closed months\n";
        foreach ($missingPeriods as $month) {
            $displayMonth = date('F Y', strtotime($month . '-01'));
            echo "   - Need to create billing period for $displayMonth ($month)\n";
        }
    } else {
        echo "   ✅ All months with invoices have billing period records\n";
        
        $openMonths = array_filter($billingPeriods, function($bp) {
            return !$bp['is_closed'];
        });
        
        if (!empty($openMonths)) {
            echo "   ❌ Some months are still open:\n";
            foreach ($openMonths as $period) {
                $displayMonth = date('F Y', strtotime($period['billing_month'] . '-01'));
                echo "   - $displayMonth ({$period['billing_month']}) is still OPEN\n";
            }
        } else {
            echo "   ✅ All months are properly closed\n";
        }
    }
    
    echo "\n=== CHECK COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}