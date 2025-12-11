<?php

echo "=== DEBUGGING CARRY FORWARD FAILURE ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Get the January invoice that should have been carried forward
    echo "1. JANUARY INVOICE DETAILS:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_id,
            i.invoice_number,
            i.cp_id,
            i.issue_date,
            i.next_due,
            i.is_closed,
            cp.c_id,
            c.name as customer_name
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE i.invoice_number = 'INV-25-01-0001'
    ");
    $stmt->execute();
    $janInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($janInvoice) {
        echo "   Invoice: {$janInvoice['invoice_number']}\n";
        echo "   Customer: {$janInvoice['customer_name']} (c_id: {$janInvoice['c_id']})\n";
        echo "   CP ID: {$janInvoice['cp_id']}\n";
        echo "   Issue Date: {$janInvoice['issue_date']}\n";
        echo "   Next Due: ৳" . number_format($janInvoice['next_due'], 0) . "\n";
        echo "   Is Closed: " . ($janInvoice['is_closed'] ? 'Yes' : 'No') . "\n";
        
        // Check if February invoice should exist for this cp_id
        echo "\n2. CHECKING FOR FEBRUARY INVOICE:\n";
        echo "   " . str_repeat("-", 50) . "\n";
        
        $stmt = $pdo->prepare("
            SELECT 
                invoice_number,
                issue_date,
                previous_due,
                total_amount,
                is_active_rolling
            FROM invoices 
            WHERE cp_id = ? 
            AND YEAR(issue_date) = 2025 
            AND MONTH(issue_date) = 2
        ");
        $stmt->execute([$janInvoice['cp_id']]);
        $febInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($febInvoice) {
            echo "   ✅ February invoice EXISTS: {$febInvoice['invoice_number']}\n";
            echo "   Issue Date: {$febInvoice['issue_date']}\n";
            echo "   Previous Due: ৳" . number_format($febInvoice['previous_due'], 0) . "\n";
            echo "   Total Amount: ৳" . number_format($febInvoice['total_amount'], 0) . "\n";
            echo "   Is Active Rolling: " . ($febInvoice['is_active_rolling'] ? 'Yes' : 'No') . "\n";
        } else {
            echo "   ❌ NO February invoice found for cp_id: {$janInvoice['cp_id']}\n";
            echo "   This confirms the carry forward failed\n";
        }
        
        // Check customer product details
        echo "\n3. CUSTOMER PRODUCT DETAILS:\n";
        echo "   " . str_repeat("-", 50) . "\n";
        
        $stmt = $pdo->prepare("
            SELECT 
                cp_id,
                c_id,
                p_id,
                assign_date,
                billing_cycle_months,
                status,
                is_active
            FROM customer_to_products 
            WHERE cp_id = ?
        ");
        $stmt->execute([$janInvoice['cp_id']]);
        $customerProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customerProduct) {
            echo "   CP ID: {$customerProduct['cp_id']}\n";
            echo "   Customer ID: {$customerProduct['c_id']}\n";
            echo "   Product ID: {$customerProduct['p_id']}\n";
            echo "   Assign Date: {$customerProduct['assign_date']}\n";
            echo "   Billing Cycle: {$customerProduct['billing_cycle_months']} months\n";
            echo "   Status: {$customerProduct['status']}\n";
            echo "   Is Active: " . ($customerProduct['is_active'] ? 'Yes' : 'No') . "\n";
        }
        
        // Simulate the carry forward process
        echo "\n4. SIMULATING CARRY FORWARD PROCESS:\n";
        echo "   " . str_repeat("-", 50) . "\n";
        
        $currentMonthDate = new DateTime('2025-01-01');
        $nextMonthDate = new DateTime('2025-02-01');
        $dueAmount = $janInvoice['next_due'];
        
        echo "   Current Month: " . $currentMonthDate->format('Y-m') . "\n";
        echo "   Next Month: " . $nextMonthDate->format('Y-m') . "\n";
        echo "   Due Amount to carry: ৳" . number_format($dueAmount, 0) . "\n";
        
        // Check if the carryForwardToNextMonth would work
        if ($customerProduct && $dueAmount > 0) {
            echo "   ✅ All conditions met for carry forward\n";
            echo "   Should create invoice for February 2025\n";
            
            // Check what the invoice number would be
            echo "\n   Expected February invoice details:\n";
            echo "   - CP ID: {$janInvoice['cp_id']}\n";
            echo "   - Issue Date: 2025-02-10 (or similar)\n";
            echo "   - Subtotal: ₹0 (no new charges)\n";
            echo "   - Previous Due: ₹" . number_format($dueAmount, 0) . "\n";
            echo "   - Total Amount: ₹" . number_format($dueAmount, 0) . "\n";
            echo "   - Next Due: ₹" . number_format($dueAmount, 0) . "\n";
        } else {
            echo "   ❌ Conditions not met for carry forward\n";
        }
        
        // Check if there are any error logs or issues
        echo "\n5. POTENTIAL ISSUES:\n";
        echo "   " . str_repeat("-", 50) . "\n";
        
        echo "   Possible reasons for carry forward failure:\n";
        echo "   1. Invoice::generateInvoiceNumber() failed\n";
        echo "   2. Invoice::create() failed due to validation\n";
        echo "   3. Database constraint violation\n";
        echo "   4. Auth::id() returned null\n";
        echo "   5. Exception was thrown and caught silently\n";
        
        // Try to manually create the invoice to see what fails
        echo "\n6. MANUAL INVOICE CREATION TEST:\n";
        echo "   " . str_repeat("-", 50) . "\n";
        
        try {
            // Generate invoice number
            $invoiceNumber = 'INV-25-02-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            echo "   Generated invoice number: $invoiceNumber\n";
            
            // Try to insert
            $stmt = $pdo->prepare("
                INSERT INTO invoices (
                    invoice_number,
                    cp_id,
                    issue_date,
                    subtotal,
                    previous_due,
                    total_amount,
                    received_amount,
                    next_due,
                    status,
                    is_active_rolling,
                    notes,
                    created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $invoiceNumber,
                $janInvoice['cp_id'],
                '2025-02-10',
                0, // subtotal
                $dueAmount, // previous_due
                $dueAmount, // total_amount
                0, // received_amount
                $dueAmount, // next_due
                'unpaid',
                1, // is_active_rolling
                "Carried forward ₹{$dueAmount} from January 2025",
                1 // created_by
            ]);
            
            if ($result) {
                echo "   ✅ Manual invoice creation SUCCEEDED\n";
                echo "   This means the carry forward method has a bug\n";
                
                // Get the created invoice ID
                $newInvoiceId = $pdo->lastInsertId();
                echo "   Created invoice ID: $newInvoiceId\n";
            } else {
                echo "   ❌ Manual invoice creation FAILED\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Manual invoice creation ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}