<?php

echo "=== DEBUGGING CARRY FORWARD AMOUNTS ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get the specific invoices mentioned
    echo "1. DETAILED ANALYSIS OF SPECIFIC INVOICES:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // January invoice
    $stmt = $pdo->prepare("
        SELECT 
            invoice_number,
            issue_date,
            subtotal,
            previous_due,
            total_amount,
            received_amount,
            next_due,
            status,
            notes,
            is_closed,
            closed_at
        FROM invoices 
        WHERE invoice_number = 'INV-25-01-0001'
    ");
    $stmt->execute();
    $janInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($janInvoice) {
        echo "   JANUARY INVOICE (INV-25-01-0001):\n";
        echo "   Issue Date: {$janInvoice['issue_date']}\n";
        echo "   Subtotal: ৳" . number_format($janInvoice['subtotal'], 0) . "\n";
        echo "   Previous Due: ৳" . number_format($janInvoice['previous_due'], 0) . "\n";
        echo "   Total Amount: ৳" . number_format($janInvoice['total_amount'], 0) . "\n";
        echo "   Received Amount: ৳" . number_format($janInvoice['received_amount'], 0) . "\n";
        echo "   Next Due: ৳" . number_format($janInvoice['next_due'], 0) . "\n";
        echo "   Status: {$janInvoice['status']}\n";
        echo "   Is Closed: " . ($janInvoice['is_closed'] ? 'Yes' : 'No') . "\n";
        if ($janInvoice['closed_at']) {
            echo "   Closed At: {$janInvoice['closed_at']}\n";
        }
        echo "   Notes: " . ($janInvoice['notes'] ?? 'None') . "\n";
        echo "\n";
    }
    
    // February invoice
    $stmt = $pdo->prepare("
        SELECT 
            invoice_number,
            issue_date,
            subtotal,
            previous_due,
            total_amount,
            received_amount,
            next_due,
            status,
            notes
        FROM invoices 
        WHERE invoice_number = 'INV-25-02-0002'
    ");
    $stmt->execute();
    $febInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($febInvoice) {
        echo "   FEBRUARY INVOICE (INV-25-02-0002):\n";
        echo "   Issue Date: {$febInvoice['issue_date']}\n";
        echo "   Subtotal: ৳" . number_format($febInvoice['subtotal'], 0) . "\n";
        echo "   Previous Due: ৳" . number_format($febInvoice['previous_due'], 0) . "\n";
        echo "   Total Amount: ৳" . number_format($febInvoice['total_amount'], 0) . "\n";
        echo "   Received Amount: ৳" . number_format($febInvoice['received_amount'], 0) . "\n";
        echo "   Next Due: ৳" . number_format($febInvoice['next_due'], 0) . "\n";
        echo "   Status: {$febInvoice['status']}\n";
        echo "   Notes: " . ($febInvoice['notes'] ?? 'None') . "\n";
        echo "\n";
    }
    
    // Analysis
    echo "2. CARRY FORWARD ANALYSIS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if ($janInvoice && $febInvoice) {
        echo "   Expected carry forward: ৳" . number_format($janInvoice['next_due'], 0) . " (from Jan next_due)\n";
        echo "   Actual previous_due in Feb: ৳" . number_format($febInvoice['previous_due'], 0) . "\n";
        echo "   Difference: ৳" . number_format($febInvoice['previous_due'] - $janInvoice['next_due'], 0) . "\n";
        
        if ($febInvoice['previous_due'] > $janInvoice['next_due']) {
            echo "   ❌ ISSUE: February invoice has MORE previous_due than expected\n";
            echo "   This suggests either:\n";
            echo "   - Multiple carry forwards happened\n";
            echo "   - There was already some previous_due in February\n";
            echo "   - Carry forward logic added to existing amount instead of replacing\n";
        } else if ($febInvoice['previous_due'] == $janInvoice['next_due']) {
            echo "   ✅ CORRECT: Carry forward amount matches exactly\n";
        } else {
            echo "   ❌ ISSUE: February invoice has LESS previous_due than expected\n";
        }
    }
    
    // Check for multiple invoices for same customer in February
    echo "\n3. CHECKING FOR MULTIPLE FEBRUARY INVOICES:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    if ($janInvoice) {
        // Get cp_id from January invoice
        $stmt = $pdo->prepare("SELECT cp_id FROM invoices WHERE invoice_number = 'INV-25-01-0001'");
        $stmt->execute();
        $cpId = $stmt->fetchColumn();
        
        if ($cpId) {
            $stmt = $pdo->prepare("
                SELECT 
                    invoice_number,
                    issue_date,
                    subtotal,
                    previous_due,
                    total_amount,
                    created_at
                FROM invoices 
                WHERE cp_id = ? 
                AND YEAR(issue_date) = 2025 
                AND MONTH(issue_date) = 2
                ORDER BY created_at ASC
            ");
            $stmt->execute([$cpId]);
            $febInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "   Found " . count($febInvoices) . " February invoices for same customer product:\n";
            foreach ($febInvoices as $i => $invoice) {
                echo "   " . ($i + 1) . ". {$invoice['invoice_number']} - Created: {$invoice['created_at']}\n";
                echo "      Previous Due: ৳" . number_format($invoice['previous_due'], 0) . "\n";
                echo "      Subtotal: ৳" . number_format($invoice['subtotal'], 0) . "\n";
                echo "      Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
            }
            
            if (count($febInvoices) > 1) {
                echo "   ❌ ISSUE: Multiple February invoices found - this could cause carry forward problems\n";
            }
        }
    }
    
    // Check billing period record
    echo "\n4. CHECKING BILLING PERIOD RECORD:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            billing_month,
            total_amount,
            received_amount,
            carried_forward,
            total_invoices,
            affected_invoices,
            notes,
            closed_at
        FROM billing_periods 
        WHERE billing_month = '2025-01'
    ");
    $stmt->execute();
    $billingPeriod = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($billingPeriod) {
        echo "   Billing Period for 2025-01:\n";
        echo "   Total Amount: ৳" . number_format($billingPeriod['total_amount'], 0) . "\n";
        echo "   Received Amount: ৳" . number_format($billingPeriod['received_amount'], 0) . "\n";
        echo "   Carried Forward: ৳" . number_format($billingPeriod['carried_forward'], 0) . "\n";
        echo "   Total Invoices: {$billingPeriod['total_invoices']}\n";
        echo "   Affected Invoices: {$billingPeriod['affected_invoices']}\n";
        echo "   Closed At: {$billingPeriod['closed_at']}\n";
        echo "   Notes: " . ($billingPeriod['notes'] ?? 'None') . "\n";
        
        if ($billingPeriod['carried_forward'] != $janInvoice['next_due']) {
            echo "   ❌ MISMATCH: Billing period shows ৳" . number_format($billingPeriod['carried_forward'], 0) . 
                 " carried forward, but invoice next_due was ৳" . number_format($janInvoice['next_due'], 0) . "\n";
        }
    }
    
    // Check all payments for this customer
    echo "\n5. ALL PAYMENTS FOR THIS CUSTOMER:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            p.amount,
            p.payment_date,
            p.payment_method,
            i.invoice_number,
            p.created_at
        FROM payments p
        JOIN invoices i ON p.invoice_id = i.invoice_id
        WHERE i.invoice_number IN ('INV-25-01-0001', 'INV-25-02-0002')
        ORDER BY p.payment_date ASC
    ");
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($payments as $payment) {
        echo "   Payment: ৳" . number_format($payment['amount'], 0) . 
             " on {$payment['payment_date']} for {$payment['invoice_number']}\n";
        echo "   Method: {$payment['payment_method']}, Created: {$payment['created_at']}\n";
    }
    
    echo "\n=== DIAGNOSIS SUMMARY ===\n";
    
    if ($janInvoice && $febInvoice) {
        if ($febInvoice['previous_due'] == $janInvoice['next_due']) {
            echo "✅ Carry forward is working correctly\n";
            echo "The ৳" . number_format($janInvoice['next_due'], 0) . " from January was properly carried to February\n";
        } else {
            echo "❌ Carry forward has an issue\n";
            echo "Expected: ৳" . number_format($janInvoice['next_due'], 0) . "\n";
            echo "Actual: ৳" . number_format($febInvoice['previous_due'], 0) . "\n";
            echo "Difference: ৳" . number_format(abs($febInvoice['previous_due'] - $janInvoice['next_due']), 0) . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}