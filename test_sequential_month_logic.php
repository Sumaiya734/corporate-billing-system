<?php

echo "=== TESTING SEQUENTIAL MONTH LOGIC ===\n\n";

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
    
    // Test 1: Check assignment months
    echo "1. ASSIGNMENT MONTHS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE_FORMAT(assign_date, '%Y-%m') as month
        FROM customer_to_products
        WHERE status = 'active' AND is_active = 1
        ORDER BY month ASC
    ");
    $stmt->execute();
    $assignmentMonths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   Assignment months: " . implode(', ', $assignmentMonths) . "\n";
    $firstAssignmentMonth = $assignmentMonths[0] ?? null;
    echo "   First assignment month: $firstAssignmentMonth\n";
    
    // Test 2: Check closed months
    echo "\n2. CLOSED MONTHS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT billing_month, is_closed
        FROM billing_periods 
        ORDER BY billing_month ASC
    ");
    $stmt->execute();
    $closedMonths = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($closedMonths)) {
        echo "   No months are officially closed\n";
    } else {
        foreach ($closedMonths as $month) {
            $displayMonth = date('F Y', strtotime($month['billing_month'] . '-01'));
            $status = $month['is_closed'] ? 'CLOSED' : 'OPEN';
            echo "   - $displayMonth ({$month['billing_month']}): $status\n";
        }
    }
    
    // Test 3: Simulate the sequential logic
    echo "\n3. SEQUENTIAL WORKFLOW SIMULATION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (!$firstAssignmentMonth) {
        echo "   No assignment months found\n";
        exit;
    }
    
    // Generate potential months from assignment to current
    $startDate = new DateTime($firstAssignmentMonth . '-01');
    $endDate = new DateTime($currentMonth . '-01');
    $potentialMonths = [];
    
    while ($startDate <= $endDate) {
        $potentialMonths[] = $startDate->format('Y-m');
        $startDate->modify('+1 month');
    }
    
    echo "   Potential months to check: " . implode(', ', $potentialMonths) . "\n\n";
    
    // Apply sequential logic
    $allowedMonths = [];
    
    foreach ($potentialMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        // Always allow first assignment month
        if ($month === $firstAssignmentMonth) {
            $allowedMonths[] = $month;
            echo "   ✅ $displayMonth: ALLOWED (first assignment month)\n";
            continue;
        }
        
        // Always allow current month
        if ($month === $currentMonth) {
            $allowedMonths[] = $month;
            echo "   ✅ $displayMonth: ALLOWED (current month)\n";
            continue;
        }
        
        // Check if all previous months are closed
        $allPreviousClosed = true;
        $checkDate = new DateTime($firstAssignmentMonth . '-01');
        
        while ($checkDate->format('Y-m') < $month) {
            $checkMonth = $checkDate->format('Y-m');
            
            // Skip first assignment month
            if ($checkMonth !== $firstAssignmentMonth) {
                $stmt = $pdo->prepare("
                    SELECT is_closed FROM billing_periods 
                    WHERE billing_month = ?
                ");
                $stmt->execute([$checkMonth]);
                $isClosed = $stmt->fetchColumn();
                
                if (!$isClosed) {
                    $checkDisplayMonth = date('F Y', strtotime($checkMonth . '-01'));
                    echo "   ❌ $displayMonth: BLOCKED (previous month $checkDisplayMonth not closed)\n";
                    $allPreviousClosed = false;
                    break;
                }
            }
            
            $checkDate->modify('+1 month');
        }
        
        if ($allPreviousClosed) {
            $allowedMonths[] = $month;
            echo "   ✅ $displayMonth: ALLOWED (all previous months closed)\n";
        } else {
            // Stop checking further months (sequential workflow)
            echo "   🛑 Stopping sequential check here\n";
            break;
        }
    }
    
    // Test 4: Final result
    echo "\n4. FINAL RESULT:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    echo "   Months that should appear on billing-invoices page:\n";
    foreach (array_reverse($allowedMonths) as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        echo "   - $displayMonth ($month)\n";
    }
    
    echo "\n   EXPECTED WORKFLOW:\n";
    echo "   1. Initially: Only January 2025 (assignment month)\n";
    echo "   2. After closing January: February 2025 appears\n";
    echo "   3. After closing February: March 2025 appears\n";
    echo "   4. And so on...\n";
    
    // Test 5: Simulate closing January
    echo "\n5. SIMULATION - CLOSING JANUARY:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    echo "   If we close January 2025, what would happen?\n";
    
    // Simulate closing January
    $stmt = $pdo->prepare("
        INSERT INTO billing_periods (billing_month, is_closed, carried_forward, closed_at, created_at, updated_at)
        VALUES ('2025-01', 1, 0, NOW(), NOW(), NOW())
        ON DUPLICATE KEY UPDATE is_closed = 1, closed_at = NOW()
    ");
    $stmt->execute();
    
    echo "   ✅ Simulated closing January 2025\n";
    
    // Re-run the sequential logic
    $newAllowedMonths = [];
    
    foreach ($potentialMonths as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        
        if ($month === $firstAssignmentMonth) {
            $newAllowedMonths[] = $month;
            continue;
        }
        
        if ($month === $currentMonth) {
            $newAllowedMonths[] = $month;
            continue;
        }
        
        $allPreviousClosed = true;
        $checkDate = new DateTime($firstAssignmentMonth . '-01');
        
        while ($checkDate->format('Y-m') < $month) {
            $checkMonth = $checkDate->format('Y-m');
            
            if ($checkMonth !== $firstAssignmentMonth) {
                $stmt = $pdo->prepare("
                    SELECT is_closed FROM billing_periods 
                    WHERE billing_month = ?
                ");
                $stmt->execute([$checkMonth]);
                $isClosed = $stmt->fetchColumn();
                
                if (!$isClosed) {
                    $allPreviousClosed = false;
                    break;
                }
            }
            
            $checkDate->modify('+1 month');
        }
        
        if ($allPreviousClosed) {
            $newAllowedMonths[] = $month;
        } else {
            break;
        }
    }
    
    echo "   After closing January, these months would appear:\n";
    foreach (array_reverse($newAllowedMonths) as $month) {
        $displayMonth = date('F Y', strtotime($month . '-01'));
        echo "   - $displayMonth ($month)\n";
    }
    
    // Clean up test data
    $stmt = $pdo->prepare("DELETE FROM billing_periods WHERE billing_month = '2025-01'");
    $stmt->execute();
    echo "   ✅ Cleaned up test data\n";
    
    echo "\n=== TEST COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}