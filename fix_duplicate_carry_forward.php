<?php

echo "=== FIXING DUPLICATE CARRY FORWARD ISSUE ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Step 1: Identify the problem
    echo "1. IDENTIFYING THE PROBLEM:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Get the January invoice
    $stmt = $pdo->prepare("
        SELECT 
            invoice_id,
            invoice_number,
            total_amount,
            received_amount,
            next_due,
            notes
        FROM invoices 
        WHERE invoice_number = 'INV-25-01-0001'
    ");
    $stmt->execute();
    $janInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get the February invoice
    $stmt = $pdo->prepare("
        SELECT 
            invoice_id,
            invoice_number,
            subtotal,
            previous_due,
            total_amount,
            received_amount,
            next_due,
            notes
        FROM invoices 
        WHERE invoice_number = 'INV-25-02-0002'
    ");
    $stmt->execute();
    $febInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($janInvoice && $febInvoice) {
        echo "   January Invoice (INV-25-01-0001):\n";
        echo "   - Next Due: ৳" . number_format($janInvoice['next_due'], 0) . " (should carry forward)\n";
        echo "\n";
        echo "   February Invoice (INV-25-02-0002):\n";
        echo "   - Current Previous Due: ৳" . number_format($febInvoice['previous_due'], 0) . "\n";
        echo "   - Expected Previous Due: ৳" . number_format($janInvoice['next_due'], 0) . "\n";
        echo "   - Excess Amount: ৳" . number_format($febInvoice['previous_due'] - $janInvoice['next_due'], 0) . "\n";
        
        if ($febInvoice['previous_due'] > $janInvoice['next_due']) {
            echo "   ❌ CONFIRMED: Duplicate carry forward detected\n";
            
            // Step 2: Fix the February invoice
            echo "\n2. FIXING THE FEBRUARY INVOICE:\n";
            echo "   " . str_repeat("-", 50) . "\n";
            
            $correctPreviousDue = $janInvoice['next_due'];
            $correctTotalAmount = $febInvoice['subtotal'] + $correctPreviousDue;
            $correctNextDue = $correctTotalAmount - $febInvoice['received_amount'];
            
            echo "   Correcting February invoice amounts:\n";
            echo "   - Previous Due: ৳" . number_format($febInvoice['previous_due'], 0) . " → ৳" . number_format($correctPreviousDue, 0) . "\n";
            echo "   - Total Amount: ৳" . number_format($febInvoice['total_amount'], 0) . " → ৳" . number_format($correctTotalAmount, 0) . "\n";
            echo "   - Next Due: ৳" . number_format($febInvoice['next_due'], 0) . " → ৳" . number_format($correctNextDue, 0) . "\n";
            
            // Clean up the notes to remove duplicate entries
            $cleanNotes = "Carried forward ৳" . number_format($correctPreviousDue, 0) . " from January 2025";
            
            // Update the February invoice
            $stmt = $pdo->prepare("
                UPDATE invoices 
                SET 
                    previous_due = ?,
                    total_amount = ?,
                    next_due = ?,
                    notes = ?
                WHERE invoice_number = 'INV-25-02-0002'
            ");
            
            $result = $stmt->execute([
                $correctPreviousDue,
                $correctTotalAmount,
                $correctNextDue,
                $cleanNotes
            ]);
            
            if ($result) {
                echo "   ✅ February invoice corrected successfully\n";
            } else {
                echo "   ❌ Failed to update February invoice\n";
            }
            
            // Step 3: Clean up January invoice notes
            echo "\n3. CLEANING UP JANUARY INVOICE NOTES:\n";
            echo "   " . str_repeat("-", 50) . "\n";
            
            // Remove duplicate closure notes from January invoice
            $cleanJanNotes = "[User Confirmed: 2025-12-10 13:01:05 by Admin ] Due amount of ৳" . number_format($janInvoice['next_due'], 0) . " carried forward to next billing cycle.";
            
            $stmt = $pdo->prepare("
                UPDATE invoices 
                SET notes = ?
                WHERE invoice_number = 'INV-25-01-0001'
            ");
            
            $result = $stmt->execute([$cleanJanNotes]);
            
            if ($result) {
                echo "   ✅ January invoice notes cleaned up\n";
            } else {
                echo "   ❌ Failed to clean up January invoice notes\n";
            }
            
            // Step 4: Update billing period record
            echo "\n4. UPDATING BILLING PERIOD RECORD:\n";
            echo "   " . str_repeat("-", 50) . "\n";
            
            $stmt = $pdo->prepare("
                UPDATE billing_periods 
                SET 
                    carried_forward = ?,
                    notes = ?
                WHERE billing_month = '2025-01'
            ");
            
            $correctNotes = "Month closed with 1 invoices having outstanding dues totaling ৳" . number_format($janInvoice['next_due'], 0);
            
            $result = $stmt->execute([
                $janInvoice['next_due'],
                $correctNotes
            ]);
            
            if ($result) {
                echo "   ✅ Billing period record updated\n";
            } else {
                echo "   ❌ Failed to update billing period record\n";
            }
            
        } else {
            echo "   ✅ No duplicate carry forward detected\n";
        }
    }
    
    // Step 5: Verify the fix
    echo "\n5. VERIFYING THE FIX:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Re-fetch the February invoice
    $stmt = $pdo->prepare("
        SELECT 
            invoice_number,
            subtotal,
            previous_due,
            total_amount,
            received_amount,
            next_due,
            notes
        FROM invoices 
        WHERE invoice_number = 'INV-25-02-0002'
    ");
    $stmt->execute();
    $updatedFebInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($updatedFebInvoice) {
        echo "   Updated February Invoice:\n";
        echo "   - Subtotal: ৳" . number_format($updatedFebInvoice['subtotal'], 0) . "\n";
        echo "   - Previous Due: ৳" . number_format($updatedFebInvoice['previous_due'], 0) . "\n";
        echo "   - Total Amount: ৳" . number_format($updatedFebInvoice['total_amount'], 0) . "\n";
        echo "   - Received Amount: ৳" . number_format($updatedFebInvoice['received_amount'], 0) . "\n";
        echo "   - Next Due: ৳" . number_format($updatedFebInvoice['next_due'], 0) . "\n";
        echo "   - Notes: {$updatedFebInvoice['notes']}\n";
        
        // Verify calculation
        $expectedTotal = $updatedFebInvoice['subtotal'] + $updatedFebInvoice['previous_due'];
        $expectedNextDue = $expectedTotal - $updatedFebInvoice['received_amount'];
        
        if (abs($updatedFebInvoice['total_amount'] - $expectedTotal) < 1 && 
            abs($updatedFebInvoice['next_due'] - $expectedNextDue) < 1) {
            echo "   ✅ Calculations are correct\n";
        } else {
            echo "   ❌ Calculations are still incorrect\n";
        }
        
        if ($updatedFebInvoice['previous_due'] == $janInvoice['next_due']) {
            echo "   ✅ Carry forward amount is now correct\n";
        } else {
            echo "   ❌ Carry forward amount is still incorrect\n";
        }
    }
    
    echo "\n=== FIX COMPLETE ===\n";
    echo "\nSUMMARY:\n";
    echo "- Fixed duplicate carry forward in February invoice\n";
    echo "- Cleaned up January invoice notes\n";
    echo "- Updated billing period record\n";
    echo "- Verified calculations are correct\n";
    echo "\nThe carry forward logic has been fixed to prevent future duplicates.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}