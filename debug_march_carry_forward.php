<?php

echo "=== DEBUGGING MARCH CARRY FORWARD ISSUE ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check February invoices (should be closed and carry forward to March)
    echo "1. FEBRUARY 2025 INVOICES (SHOULD CARRY TO MARCH):\n";
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
            i.cp_id
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE YEAR(i.issue_date) = 2025 
        AND MONTH(i.issue_date) = 2
        AND i.is_active_rolling = 1
        ORDER BY i.invoice_number
    ");
    $stmt->execute();
    $febInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalFebDue = 0;
    foreach ($febInvoices as $invoice) {
        echo "   - {$invoice['invoice_number']} ({$invoice['customer_name']})\n";
        echo "     Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
        echo "     Received: ৳" . number_format($invoice['received_amount'], 0) . "\n";
        echo "     Next Due: ৳" . number_format($invoice['next_due'], 0) . " (should carry to March)\n";
        echo "     Status: {$invoice['status']}\n";
        echo "     Is Closed: " . ($invoice['is_closed'] ? 'Yes' : 'No') . "\n";
        echo "     CP ID: {$invoice['cp_id']}\n";
        
        if ($invoice['next_due'] > 0) {
            $totalFebDue += $invoice['next_due'];
        }
        echo "     " . str_repeat("-", 30) . "\n";
    }
    
    echo "   Total February due that should carry to March: ৳" . number_format($totalFebDue, 0) . "\n";
    
    // Check March invoices
    echo "\n2. MARCH 2025 INVOICES (SHOULD HAVE CARRY FORWARD):\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_number,
            i.subtotal,
            i.previous_due,
            i.total_amount,
            i.received_amount,
            i.next_due,
            i.status,
            c.name as customer_name,
            i.cp_id,
            i.notes
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE YEAR(i.issue_date) = 2025 
        AND MONTH(i.issue_date) = 3
        AND i.is_active_rolling = 1
        ORDER BY i.invoice_number
    ");
    $stmt->execute();
    $marInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalMarPreviousDue = 0;
    foreach ($marInvoices as $invoice) {
        echo "   - {$invoice['invoice_number']} ({$invoice['customer_name']})\n";
        echo "     Subtotal: ৳" . number_format($invoice['subtotal'], 0) . " (new charges)\n";
        echo "     Previous Due: ৳" . number_format($invoice['previous_due'], 0) . " (from February)\n";
        echo "     Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
        echo "     Received: ৳" . number_format($invoice['received_amount'], 0) . "\n";
        echo "     Next Due: ৳" . number_format($invoice['next_due'], 0) . "\n";
        echo "     Status: {$invoice['status']}\n";
        echo "     CP ID: {$invoice['cp_id']}\n";
        echo "     Notes: " . substr($invoice['notes'] ?? '', 0, 100) . "...\n";
        
        $totalMarPreviousDue += $invoice['previous_due'];
        echo "     " . str_repeat("-", 30) . "\n";
    }
    
    echo "   Total March previous_due: ৳" . number_format($totalMarPreviousDue, 0) . "\n";
    
    // Check if carry forward matches
    echo "\n3. CARRY FORWARD VERIFICATION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    echo "   February next_due: ৳" . number_format($totalFebDue, 0) . "\n";
    echo "   March previous_due: ৳" . number_format($totalMarPreviousDue, 0) . "\n";
    echo "   Difference: ৳" . number_format(abs($totalFebDue - $totalMarPreviousDue), 0) . "\n";
    
    if (abs($totalFebDue - $totalMarPreviousDue) < 1) {
        echo "   ✅ Carry forward is CORRECT\n";
    } else {
        echo "   ❌ Carry forward is INCORRECT\n";
        
        // Check if February is closed
        $stmt = $pdo->prepare("
            SELECT is_closed, carried_forward 
            FROM billing_periods 
            WHERE billing_month = '2025-02'
        ");
        $stmt->execute();
        $febPeriod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($febPeriod) {
            echo "   February billing period:\n";
            echo "   - Is Closed: " . ($febPeriod['is_closed'] ? 'Yes' : 'No') . "\n";
            echo "   - Carried Forward: ৳" . number_format($febPeriod['carried_forward'], 0) . "\n";
        } else {
            echo "   ❌ February billing period not found - month not closed yet\n";
        }
    }
    
    // Check what the monthly-bills page would show for March
    echo "\n4. WHAT MARCH MONTHLY-BILLS PAGE SHOWS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Check if transformation is being applied incorrectly
    foreach ($marInvoices as $invoice) {
        echo "   Invoice: {$invoice['invoice_number']}\n";
        echo "   Database values:\n";
        echo "   - Subtotal: ৳" . number_format($invoice['subtotal'], 0) . "\n";
        echo "   - Previous Due: ৳" . number_format($invoice['previous_due'], 0) . "\n";
        echo "   - Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
        
        // Check if this matches what you see on the page
        echo "   Expected on monthly-bills page:\n";
        echo "   - Should show Previous Due: ৳" . number_format($invoice['previous_due'], 0) . "\n";
        echo "   - Should show Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
        
        if ($invoice['previous_due'] != $totalFebDue && $totalFebDue > 0) {
            echo "   ❌ ISSUE: Previous due doesn't match February carry forward\n";
        }
    }
    
    // Check customer product details for March
    echo "\n5. CUSTOMER PRODUCT BILLING CYCLE CHECK:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    foreach ($marInvoices as $invoice) {
        $stmt = $pdo->prepare("
            SELECT 
                assign_date,
                billing_cycle_months
            FROM customer_to_products 
            WHERE cp_id = ?
        ");
        $stmt->execute([$invoice['cp_id']]);
        $cp = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cp) {
            $assignDate = new DateTime($cp['assign_date']);
            $marchDate = new DateTime('2025-03-01');
            $monthsDiff = $assignDate->diff($marchDate)->m + ($assignDate->diff($marchDate)->y * 12);
            
            echo "   Customer: {$invoice['customer_name']}\n";
            echo "   Assign Date: {$cp['assign_date']}\n";
            echo "   Billing Cycle: {$cp['billing_cycle_months']} months\n";
            echo "   Months since assign: $monthsDiff\n";
            echo "   Should have new charges in March: " . (($monthsDiff % $cp['billing_cycle_months']) == 0 ? 'Yes' : 'No') . "\n";
            echo "   " . str_repeat("-", 25) . "\n";
        }
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}