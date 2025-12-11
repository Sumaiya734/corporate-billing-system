<?php

echo "=== TESTING ACTIVITY DETECTION FIX ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Test the fixed activity detection for February
    echo "1. TESTING FEBRUARY ACTIVITY DETECTION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $month = '2025-02';
    $monthDate = new DateTime($month . '-01');
    
    // Test OLD method (invoices issued IN February)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM invoices 
        WHERE is_active_rolling = 1
        AND YEAR(issue_date) = ?
        AND MONTH(issue_date) = ?
    ");
    $stmt->execute([$monthDate->format('Y'), $monthDate->format('n')]);
    $oldMethodCount = $stmt->fetchColumn();
    
    echo "   OLD method (invoices issued IN February): $oldMethodCount invoices\n";
    
    // Test NEW method (invoices that should APPEAR in February)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        WHERE cp.status = 'active'
        AND cp.is_active = 1
        AND cp.assign_date <= ?
        AND i.is_active_rolling = 1
        AND i.issue_date <= ?
    ");
    $stmt->execute([$monthDate->format('Y-m-t'), $monthDate->format('Y-m-t')]);
    $newMethodCount = $stmt->fetchColumn();
    
    echo "   NEW method (invoices that should APPEAR in February): $newMethodCount invoices\n";
    
    echo "\n   RESULT:\n";
    if ($oldMethodCount > 0) {
        echo "   ✅ OLD method would show February (has invoices issued in February)\n";
    } else {
        echo "   ❌ OLD method would hide February (no invoices issued in February)\n";
    }
    
    if ($newMethodCount > 0) {
        echo "   ✅ NEW method will show February (has invoices that should appear in February)\n";
    } else {
        echo "   ❌ NEW method would hide February (no invoices should appear in February)\n";
    }
    
    // Test for March as well
    echo "\n2. TESTING MARCH ACTIVITY DETECTION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $month = '2025-03';
    $monthDate = new DateTime($month . '-01');
    
    // Test NEW method for March
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        WHERE cp.status = 'active'
        AND cp.is_active = 1
        AND cp.assign_date <= ?
        AND i.is_active_rolling = 1
        AND i.issue_date <= ?
    ");
    $stmt->execute([$monthDate->format('Y-m-t'), $monthDate->format('Y-m-t')]);
    $marchCount = $stmt->fetchColumn();
    
    echo "   March invoices that should appear: $marchCount invoices\n";
    
    // Check if February is closed (required for March to show)
    $stmt = $pdo->prepare("
        SELECT is_closed FROM billing_periods 
        WHERE billing_month = '2025-02'
    ");
    $stmt->execute();
    $isFebClosed = $stmt->fetchColumn();
    
    echo "   February closed status: " . ($isFebClosed ? 'CLOSED' : 'NOT CLOSED') . "\n";
    
    if ($marchCount > 0 && $isFebClosed) {
        echo "   ✅ March should appear (has activity AND February is closed)\n";
    } elseif ($marchCount > 0 && !$isFebClosed) {
        echo "   ❌ March should NOT appear (has activity BUT February is not closed)\n";
    } else {
        echo "   ❌ March should NOT appear (no activity)\n";
    }
    
    // Test the complete flow
    echo "\n3. COMPLETE FLOW TEST:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $months = ['2025-01', '2025-02', '2025-03', '2025-04'];
    
    foreach ($months as $testMonth) {
        $testMonthDate = new DateTime($testMonth . '-01');
        $displayMonth = $testMonthDate->format('F Y');
        
        // Check activity
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM invoices i
            JOIN customer_to_products cp ON i.cp_id = cp.cp_id
            WHERE cp.status = 'active'
            AND cp.is_active = 1
            AND cp.assign_date <= ?
            AND i.is_active_rolling = 1
            AND i.issue_date <= ?
        ");
        $stmt->execute([$testMonthDate->format('Y-m-t'), $testMonthDate->format('Y-m-t')]);
        $hasActivity = $stmt->fetchColumn() > 0;
        
        // Check if previous month is closed (for sequential logic)
        $previousMonth = $testMonthDate->modify('-1 month')->format('Y-m');
        $stmt = $pdo->prepare("
            SELECT is_closed FROM billing_periods 
            WHERE billing_month = ?
        ");
        $stmt->execute([$previousMonth]);
        $isPreviousClosed = $stmt->fetchColumn();
        
        $shouldShow = false;
        if ($testMonth === '2025-01') {
            $shouldShow = true; // Always show assignment month
        } elseif ($testMonth === '2025-12') {
            $shouldShow = true; // Always show current month
        } elseif ($hasActivity && $isPreviousClosed) {
            $shouldShow = true; // Show if has activity and previous month closed
        }
        
        echo "   $displayMonth:\n";
        echo "     - Has activity: " . ($hasActivity ? 'YES' : 'NO') . "\n";
        echo "     - Previous month closed: " . ($isPreviousClosed ? 'YES' : 'NO') . "\n";
        echo "     - Should show: " . ($shouldShow ? 'YES' : 'NO') . "\n\n";
    }
    
    echo "=== TEST COMPLETE ===\n";
    echo "\nEXPECTED BEHAVIOR AFTER FIX:\n";
    echo "✅ February should now appear on billing-invoices page\n";
    echo "✅ February should show the January rolling invoice\n";
    echo "✅ March should NOT appear until February is closed\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}