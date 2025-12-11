<?php

echo "=== TESTING MONTH APPEARANCE LOGIC ===\n\n";

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
    
    // Check 1: What months are officially closed
    echo "1. OFFICIALLY CLOSED MONTHS:\n";
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
    
    $closedMonthsList = [];
    foreach ($closedMonths as $month) {
        $displayMonth = date('F Y', strtotime($month['billing_month'] . '-01'));
        $isClosed = $month['is_closed'] ? 'Yes' : 'No';
        echo "   - $displayMonth ({$month['billing_month']}): Closed $isClosed\n";
        
        if ($month['is_closed']) {
            $closedMonthsList[] = $month['billing_month'];
        }
    }
    
    // Check 2: Test the future month visibility logic
    echo "\n2. TESTING FUTURE MONTH VISIBILITY:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Get all possible months (assignment + invoice months)
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
    
    echo "   All possible months: " . implode(', ', $allMonths) . "\n\n";
    
    $visibleMonths = [];
    foreach ($allMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Apply the visibility filter logic
        if ($month <= $currentMonth) {
            $shouldShow = true;
            $reason = "current or past month";
        } else {
            // For future months, only show if previous month is officially closed
            $previousMonth = date('Y-m', strtotime($month . ' -1 month'));
            $isPreviousMonthClosed = in_array($previousMonth, $closedMonthsList);
            
            $shouldShow = $isPreviousMonthClosed;
            $previousDisplayMonth = date('F Y', strtotime($previousMonth . '-01'));
            $reason = $isPreviousMonthClosed ? 
                "previous month ($previousDisplayMonth) is closed" : 
                "previous month ($previousDisplayMonth) NOT closed";
        }
        
        echo "   - $displayMonth ($month): ";
        if ($shouldShow) {
            echo "✅ SHOULD SHOW ($reason)\n";
            $visibleMonths[] = $month;
        } else {
            echo "❌ SHOULD HIDE ($reason)\n";
        }
    }
    
    // Check 3: What months currently appear on the page
    echo "\n3. CURRENT PAGE STATUS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    echo "   Based on your screenshot, these months appear:\n";
    echo "   - July 2025\n";
    echo "   - May 2025\n";
    echo "   - March 2025\n";
    echo "   - February 2025\n";
    echo "   - January 2025\n";
    
    $currentlyShowing = ['2025-07', '2025-05', '2025-03', '2025-02', '2025-01'];
    
    echo "\n   Months that SHOULD show: " . implode(', ', $visibleMonths) . "\n";
    echo "   Months CURRENTLY showing: " . implode(', ', $currentlyShowing) . "\n";
    
    $shouldShowButDont = array_diff($visibleMonths, $currentlyShowing);
    $showButShouldnt = array_diff($currentlyShowing, $visibleMonths);
    
    if (!empty($shouldShowButDont)) {
        echo "   ❌ Missing months: " . implode(', ', $shouldShowButDont) . "\n";
    }
    
    if (!empty($showButShouldnt)) {
        echo "   ❌ Extra months: " . implode(', ', $showButShouldnt) . "\n";
    }
    
    if (empty($shouldShowButDont) && empty($showButShouldnt)) {
        echo "   ✅ Perfect match!\n";
    }
    
    // Check 4: Identify the issue
    echo "\n4. ISSUE ANALYSIS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Check if August should show
    $augustPreviousMonth = '2025-07';
    $isJulyClosed = in_array($augustPreviousMonth, $closedMonthsList);
    echo "   August 2025 analysis:\n";
    echo "   - Previous month (July 2025): " . ($isJulyClosed ? "CLOSED" : "NOT CLOSED") . "\n";
    echo "   - Should August show? " . ($isJulyClosed ? "YES" : "NO") . "\n";
    
    // Check if December should show  
    $decemberPreviousMonth = '2025-11';
    $isNovemberClosed = in_array($decemberPreviousMonth, $closedMonthsList);
    echo "\n   December 2025 analysis:\n";
    echo "   - Previous month (November 2025): " . ($isNovemberClosed ? "CLOSED" : "NOT CLOSED") . "\n";
    echo "   - Should December show? " . ($isNovemberClosed ? "YES (but it's current month anyway)" : "NO (but it's current month so shows anyway)") . "\n";
    
    // Check if there are any future months that shouldn't show
    echo "\n5. RECOMMENDATION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (in_array('2025-08', $visibleMonths) && !in_array('2025-08', $currentlyShowing)) {
        echo "   ❌ August 2025 should show because July is closed\n";
        echo "   ❌ This suggests the fix worked but cache might need clearing\n";
    } else if (!in_array('2025-08', $visibleMonths)) {
        echo "   ✅ August 2025 correctly hidden because July is not closed\n";
    }
    
    if ($currentMonth === '2025-12') {
        echo "   ✅ December 2025 should always show (current month)\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}