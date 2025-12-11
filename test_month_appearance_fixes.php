<?php

echo "=== TESTING MONTH APPEARANCE FIXES ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Test 1: Check current billing periods status
    echo "1. CURRENT BILLING PERIODS STATUS:\n";
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
    $billingPeriods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($billingPeriods as $period) {
        $displayMonth = date('F Y', strtotime($period['billing_month'] . '-01'));
        $status = $period['is_closed'] ? 'CLOSED' : 'OPEN';
        echo "   - $displayMonth: $status";
        if ($period['is_closed']) {
            echo " (Carried Forward: ৳" . number_format($period['carried_forward'], 0) . ")";
        }
        echo "\n";
    }
    
    // Test 2: Simulate the sequential month logic
    echo "\n2. SEQUENTIAL MONTH LOGIC SIMULATION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Get first assignment month
    $stmt = $pdo->prepare("
        SELECT MIN(DATE_FORMAT(assign_date, '%Y-%m')) as first_month
        FROM customer_to_products
        WHERE status = 'active' AND is_active = 1
    ");
    $stmt->execute();
    $firstAssignmentMonth = $stmt->fetchColumn();
    
    echo "   First assignment month: $firstAssignmentMonth\n";
    
    if ($firstAssignmentMonth) {
        $allowedMonths = [$firstAssignmentMonth];
        echo "   ✅ $firstAssignmentMonth: ALLOWED (assignment month)\n";
        
        // Check sequential months
        $checkDate = new DateTime($firstAssignmentMonth . '-01');
        $checkDate->modify('+1 month');
        $currentMonth = date('Y-m');
        
        while ($checkDate->format('Y-m') <= $currentMonth) {
            $checkMonth = $checkDate->format('Y-m');
            $previousMonth = $checkDate->format('Y-m');
            $checkDate->modify('-1 month');
            $previousMonth = $checkDate->format('Y-m');
            $checkDate->modify('+1 month');
            
            // Check if previous month is closed
            $stmt = $pdo->prepare("
                SELECT is_closed FROM billing_periods 
                WHERE billing_month = ?
            ");
            $stmt->execute([$previousMonth]);
            $isPreviousClosed = $stmt->fetchColumn();
            
            $displayMonth = date('F Y', strtotime($checkMonth . '-01'));
            
            if ($isPreviousClosed) {
                $allowedMonths[] = $checkMonth;
                echo "   ✅ $displayMonth: ALLOWED (previous month closed)\n";
            } else {
                $previousDisplayMonth = date('F Y', strtotime($previousMonth . '-01'));
                echo "   ❌ $displayMonth: BLOCKED (previous month $previousDisplayMonth not closed)\n";
                break; // Stop at first blocked month
            }
            
            $checkDate->modify('+1 month');
        }
        
        echo "\n   Final allowed months: " . implode(', ', $allowedMonths) . "\n";
    }
    
    // Test 3: What should happen when February is closed
    echo "\n3. EXPECTED BEHAVIOR WHEN FEBRUARY IS CLOSED:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    echo "   1. User goes to February 2025 monthly-bills page\n";
    echo "   2. User clicks 'Close Month' button\n";
    echo "   3. System processes all February invoices\n";
    echo "   4. System creates billing_periods record for February with is_closed = 1\n";
    echo "   5. System carries forward unpaid amounts to March\n";
    echo "   6. System redirects to billing-invoices page\n";
    echo "   7. billing-invoices page now shows:\n";
    echo "      - January 2025: CLOSED\n";
    echo "      - February 2025: CLOSED\n";
    echo "      - March 2025: OPEN (newly appeared)\n";
    
    // Test 4: Check if there are any February invoices to close
    echo "\n4. FEBRUARY 2025 INVOICES STATUS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            invoice_id,
            invoice_number,
            total_amount,
            received_amount,
            next_due,
            status,
            is_closed
        FROM invoices 
        WHERE YEAR(issue_date) = 2025 AND MONTH(issue_date) = 2
        ORDER BY invoice_id ASC
    ");
    $stmt->execute();
    $febInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($febInvoices)) {
        echo "   ❌ No February 2025 invoices found\n";
        echo "   This might be why February doesn't appear on monthly-bills page\n";
        echo "   Need to generate invoices for February first\n";
    } else {
        foreach ($febInvoices as $invoice) {
            echo "   Invoice: {$invoice['invoice_number']}\n";
            echo "   - Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
            echo "   - Received: ৳" . number_format($invoice['received_amount'], 0) . "\n";
            echo "   - Next Due: ৳" . number_format($invoice['next_due'], 0) . "\n";
            echo "   - Status: {$invoice['status']}\n";
            echo "   - Is Closed: " . ($invoice['is_closed'] ? 'Yes' : 'No') . "\n\n";
        }
    }
    
    // Test 5: Check rolling invoice system
    echo "5. ROLLING INVOICE SYSTEM CHECK:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            invoice_id,
            invoice_number,
            DATE_FORMAT(issue_date, '%Y-%m') as month,
            subtotal,
            previous_due,
            total_amount,
            next_due,
            status,
            is_active_rolling
        FROM invoices 
        WHERE is_active_rolling = 1
        ORDER BY issue_date ASC
    ");
    $stmt->execute();
    $rollingInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rollingInvoices)) {
        echo "   ❌ No rolling invoices found\n";
    } else {
        echo "   Rolling invoices found:\n";
        foreach ($rollingInvoices as $invoice) {
            $displayMonth = date('F Y', strtotime($invoice['month'] . '-01'));
            echo "   - {$invoice['invoice_number']} ($displayMonth)\n";
            echo "     * Subtotal: ৳" . number_format($invoice['subtotal'], 0) . "\n";
            echo "     * Previous Due: ৳" . number_format($invoice['previous_due'], 0) . "\n";
            echo "     * Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
            echo "     * Next Due: ৳" . number_format($invoice['next_due'], 0) . "\n";
            echo "     * Status: {$invoice['status']}\n\n";
        }
    }
    
    echo "=== TEST COMPLETE ===\n";
    echo "\nNEXT STEPS:\n";
    echo "1. Go to billing-invoices page - should only show January and February\n";
    echo "2. Go to February monthly-bills page\n";
    echo "3. Close February month\n";
    echo "4. Should redirect to billing-invoices page\n";
    echo "5. Should now show January (closed), February (closed), March (open)\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}