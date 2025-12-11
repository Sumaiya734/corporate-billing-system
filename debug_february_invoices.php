<?php

echo "=== DEBUGGING FEBRUARY INVOICES ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check all February 2025 invoices
    echo "1. ALL FEBRUARY 2025 INVOICES:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_number,
            i.issue_date,
            i.subtotal,
            i.previous_due,
            i.total_amount,
            i.received_amount,
            i.next_due,
            i.status,
            i.is_active_rolling,
            c.name as customer_name,
            i.notes
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE YEAR(i.issue_date) = 2025 
        AND MONTH(i.issue_date) = 2
        ORDER BY i.invoice_number
    ");
    $stmt->execute();
    $febInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($febInvoices)) {
        echo "   ❌ NO February 2025 invoices found!\n";
        echo "   This means carry forward didn't create invoices properly.\n";
    } else {
        echo "   Found " . count($febInvoices) . " February 2025 invoices:\n";
        foreach ($febInvoices as $invoice) {
            echo "   - {$invoice['invoice_number']} ({$invoice['customer_name']})\n";
            echo "     Issue Date: {$invoice['issue_date']}\n";
            echo "     Subtotal: ৳" . number_format($invoice['subtotal'], 0) . "\n";
            echo "     Previous Due: ৳" . number_format($invoice['previous_due'], 0) . "\n";
            echo "     Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
            echo "     Next Due: ৳" . number_format($invoice['next_due'], 0) . "\n";
            echo "     Status: {$invoice['status']}\n";
            echo "     Is Active Rolling: " . ($invoice['is_active_rolling'] ? 'Yes' : 'No') . "\n";
            echo "     Notes: " . substr($invoice['notes'] ?? '', 0, 100) . "...\n";
            echo "     " . str_repeat("-", 30) . "\n";
        }
    }
    
    // Check January invoices to see what should have been carried forward
    echo "\n2. JANUARY 2025 INVOICES (SHOULD BE CLOSED):\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_number,
            i.total_amount,
            i.received_amount,
            i.next_due,
            i.status,
            i.is_closed,
            c.name as customer_name,
            i.notes
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE YEAR(i.issue_date) = 2025 
        AND MONTH(i.issue_date) = 1
        ORDER BY i.invoice_number
    ");
    $stmt->execute();
    $janInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalCarryForward = 0;
    foreach ($janInvoices as $invoice) {
        echo "   - {$invoice['invoice_number']} ({$invoice['customer_name']})\n";
        echo "     Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
        echo "     Received: ৳" . number_format($invoice['received_amount'], 0) . "\n";
        echo "     Next Due: ৳" . number_format($invoice['next_due'], 0) . " (should carry forward)\n";
        echo "     Status: {$invoice['status']}\n";
        echo "     Is Closed: " . ($invoice['is_closed'] ? 'Yes' : 'No') . "\n";
        
        if ($invoice['next_due'] > 0) {
            $totalCarryForward += $invoice['next_due'];
        }
        echo "     " . str_repeat("-", 30) . "\n";
    }
    
    echo "   Total amount that should carry forward: ৳" . number_format($totalCarryForward, 0) . "\n";
    
    // Check billing period
    echo "\n3. BILLING PERIOD STATUS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            billing_month,
            is_closed,
            carried_forward,
            total_invoices,
            affected_invoices,
            closed_at,
            notes
        FROM billing_periods 
        WHERE billing_month = '2025-01'
    ");
    $stmt->execute();
    $billingPeriod = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($billingPeriod) {
        echo "   January 2025 Billing Period:\n";
        echo "   Is Closed: " . ($billingPeriod['is_closed'] ? 'Yes' : 'No') . "\n";
        echo "   Carried Forward: ৳" . number_format($billingPeriod['carried_forward'], 0) . "\n";
        echo "   Total Invoices: {$billingPeriod['total_invoices']}\n";
        echo "   Affected Invoices: {$billingPeriod['affected_invoices']}\n";
        echo "   Closed At: {$billingPeriod['closed_at']}\n";
        echo "   Notes: {$billingPeriod['notes']}\n";
    }
    
    // Check if there's a mismatch
    echo "\n4. ANALYSIS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if (empty($febInvoices) && $totalCarryForward > 0) {
        echo "   ❌ ISSUE FOUND: January has ৳" . number_format($totalCarryForward, 0) . " to carry forward\n";
        echo "   but no February invoices were created!\n";
        echo "\n   POSSIBLE CAUSES:\n";
        echo "   1. carryForwardToNextMonth() method failed\n";
        echo "   2. Invoice creation failed silently\n";
        echo "   3. Month closing process didn't complete properly\n";
        echo "   4. Database transaction was rolled back\n";
    } elseif (!empty($febInvoices)) {
        $febTotalPreviousDue = array_sum(array_column($febInvoices, 'previous_due'));
        echo "   February invoices exist with ৳" . number_format($febTotalPreviousDue, 0) . " previous due\n";
        
        if (abs($febTotalPreviousDue - $totalCarryForward) < 1) {
            echo "   ✅ Carry forward amounts match!\n";
        } else {
            echo "   ❌ Carry forward mismatch:\n";
            echo "   Expected: ৳" . number_format($totalCarryForward, 0) . "\n";
            echo "   Actual: ৳" . number_format($febTotalPreviousDue, 0) . "\n";
        }
    } else {
        echo "   ℹ️  No carry forward needed - all January invoices are paid\n";
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}