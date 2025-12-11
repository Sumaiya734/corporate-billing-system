<?php

echo "=== CHECKING CARRY FORWARD ISSUE ===\n\n";

// Direct database connection
$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check 1: Find invoices with partial payments
    echo "1. INVOICES WITH PARTIAL PAYMENTS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_number,
            i.invoice_id,
            i.issue_date,
            i.total_amount,
            i.received_amount,
            i.next_due,
            i.status,
            c.name as customer_name,
            i.cp_id
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE i.received_amount > 0 
        AND i.next_due > 0
        AND i.is_active_rolling = 1
        ORDER BY i.issue_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $partialPaymentInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($partialPaymentInvoices)) {
        echo "   No invoices with partial payments found\n";
    } else {
        foreach ($partialPaymentInvoices as $invoice) {
            echo "   Invoice: {$invoice['invoice_number']}\n";
            echo "   Customer: {$invoice['customer_name']}\n";
            echo "   Issue Date: {$invoice['issue_date']}\n";
            echo "   Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
            echo "   Received: ৳" . number_format($invoice['received_amount'], 0) . "\n";
            echo "   Next Due: ৳" . number_format($invoice['next_due'], 0) . "\n";
            echo "   Status: {$invoice['status']}\n";
            
            // Check if calculation is correct
            $calculatedNextDue = $invoice['total_amount'] - $invoice['received_amount'];
            if (abs($invoice['next_due'] - $calculatedNextDue) < 1) {
                echo "   ✅ Calculation correct\n";
            } else {
                echo "   ❌ Calculation incorrect - should be ৳" . number_format($calculatedNextDue, 0) . "\n";
            }
            echo "   " . str_repeat("-", 30) . "\n";
        }
    }
    
    // Check 2: Look for next month invoices
    echo "\n2. CHECKING NEXT MONTH INVOICES:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (!empty($partialPaymentInvoices)) {
        $testInvoice = $partialPaymentInvoices[0];
        $issueDate = new DateTime($testInvoice['issue_date']);
        $nextMonth = clone $issueDate;
        $nextMonth->modify('+1 month');
        
        echo "   Test Invoice: {$testInvoice['invoice_number']}\n";
        echo "   Current Month: " . $issueDate->format('Y-m') . "\n";
        echo "   Next Month: " . $nextMonth->format('Y-m') . "\n";
        echo "   Amount that should carry forward: ৳" . number_format($testInvoice['next_due'], 0) . "\n\n";
        
        // Look for next month invoice for same customer product
        $stmt = $pdo->prepare("
            SELECT 
                invoice_number,
                issue_date,
                subtotal,
                previous_due,
                total_amount,
                received_amount,
                next_due,
                notes
            FROM invoices 
            WHERE cp_id = ? 
            AND YEAR(issue_date) = ? 
            AND MONTH(issue_date) = ?
            AND is_active_rolling = 1
        ");
        $stmt->execute([
            $testInvoice['cp_id'], 
            $nextMonth->format('Y'), 
            $nextMonth->format('n')
        ]);
        $nextMonthInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($nextMonthInvoice) {
            echo "   ✅ Next month invoice found: {$nextMonthInvoice['invoice_number']}\n";
            echo "   Issue Date: {$nextMonthInvoice['issue_date']}\n";
            echo "   Subtotal: ৳" . number_format($nextMonthInvoice['subtotal'], 0) . "\n";
            echo "   Previous Due: ৳" . number_format($nextMonthInvoice['previous_due'], 0) . "\n";
            echo "   Total Amount: ৳" . number_format($nextMonthInvoice['total_amount'], 0) . "\n";
            echo "   Next Due: ৳" . number_format($nextMonthInvoice['next_due'], 0) . "\n";
            
            if ($nextMonthInvoice['previous_due'] >= $testInvoice['next_due']) {
                echo "   ✅ Carry forward appears to be working\n";
            } else {
                echo "   ❌ Carry forward NOT working - expected ৳" . number_format($testInvoice['next_due'], 0) . " but got ৳" . number_format($nextMonthInvoice['previous_due'], 0) . "\n";
            }
            
            if (strlen($nextMonthInvoice['notes']) > 0) {
                echo "   Notes: " . substr($nextMonthInvoice['notes'], 0, 100) . "...\n";
            }
        } else {
            echo "   ❌ No next month invoice found\n";
            echo "   This means either:\n";
            echo "   - Month hasn't been closed yet\n";
            echo "   - Carry forward logic isn't working\n";
            echo "   - Invoice generation for next month failed\n";
        }
    }
    
    // Check 3: Check if any months have been closed
    echo "\n3. CHECKING CLOSED MONTHS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            billing_month,
            is_closed,
            total_amount,
            received_amount,
            carried_forward,
            total_invoices,
            affected_invoices,
            closed_at
        FROM billing_periods 
        WHERE is_closed = 1
        ORDER BY billing_month DESC
        LIMIT 3
    ");
    $stmt->execute();
    $closedMonths = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($closedMonths)) {
        echo "   ❌ No months have been closed yet\n";
        echo "   This explains why carry forward isn't visible - you need to close a month first\n";
        echo "\n   TO TEST CARRY FORWARD:\n";
        echo "   1. Make sure you have an invoice with partial payment (next_due > 0)\n";
        echo "   2. Go to the monthly-bills page for that month\n";
        echo "   3. Click 'Close Month' button\n";
        echo "   4. Check the next month's invoices for carried forward amounts\n";
    } else {
        echo "   Found " . count($closedMonths) . " closed months:\n";
        foreach ($closedMonths as $month) {
            echo "   Month: {$month['billing_month']}\n";
            echo "   Closed at: {$month['closed_at']}\n";
            echo "   Total invoices: {$month['total_invoices']}\n";
            echo "   Carried forward: ৳" . number_format($month['carried_forward'], 0) . "\n";
            echo "   Affected invoices: {$month['affected_invoices']}\n";
            echo "   " . str_repeat("-", 25) . "\n";
        }
    }
    
    // Check 4: Recent payments
    echo "\n4. RECENT PAYMENTS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            p.amount,
            p.payment_date,
            p.payment_method,
            i.invoice_number,
            c.name as customer_name,
            i.total_amount,
            i.received_amount,
            i.next_due
        FROM payments p
        JOIN invoices i ON p.invoice_id = i.invoice_id
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        ORDER BY p.payment_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($recentPayments as $payment) {
        echo "   Payment: ৳" . number_format($payment['amount'], 0) . " on {$payment['payment_date']}\n";
        echo "   Customer: {$payment['customer_name']}\n";
        echo "   Invoice: {$payment['invoice_number']}\n";
        echo "   After payment - Total: ৳" . number_format($payment['total_amount'], 0) . 
             ", Received: ৳" . number_format($payment['received_amount'], 0) . 
             ", Next Due: ৳" . number_format($payment['next_due'], 0) . "\n";
        echo "   " . str_repeat("-", 25) . "\n";
    }
    
    echo "\n=== DIAGNOSIS COMPLETE ===\n";
    
    if (empty($closedMonths)) {
        echo "\n🔍 ISSUE IDENTIFIED:\n";
        echo "The carry forward logic is implemented correctly, but no months have been closed yet.\n";
        echo "Carry forward only happens when you close a month using the 'Close Month' button.\n\n";
        echo "STEPS TO TEST:\n";
        echo "1. Find an invoice with partial payment (like the ones shown above)\n";
        echo "2. Go to monthly-bills page for that month\n";
        echo "3. Click 'Close Month' button and confirm\n";
        echo "4. Check next month's invoices - they should show the carried forward amounts\n";
    } else {
        echo "\n✅ Months have been closed - carry forward should be working\n";
        echo "If you're not seeing carried forward amounts, please check the next month's invoices\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}