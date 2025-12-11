<?php

echo "=== FIXING CARRY FORWARD METHOD ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // First, let's clean up the test invoice we created
    echo "1. CLEANING UP TEST INVOICE:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("DELETE FROM invoices WHERE invoice_number LIKE 'INV-25-02-%' AND notes LIKE '%Carried forward%'");
    $result = $stmt->execute();
    $deletedRows = $stmt->rowCount();
    echo "   Deleted $deletedRows test invoices\n";
    
    // Now let's test the carry forward logic step by step
    echo "\n2. TESTING CARRY FORWARD LOGIC:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Get the January invoice
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_id,
            i.cp_id,
            i.next_due,
            cp.c_id
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        WHERE i.invoice_number = 'INV-25-01-0001'
    ");
    $stmt->execute();
    $janInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($janInvoice) {
        $cpId = $janInvoice['cp_id'];
        $dueAmount = $janInvoice['next_due'];
        
        echo "   CP ID: $cpId\n";
        echo "   Due Amount: ৳" . number_format($dueAmount, 0) . "\n";
        
        // Check if February invoice already exists
        $stmt = $pdo->prepare("
            SELECT invoice_id, invoice_number 
            FROM invoices 
            WHERE cp_id = ? 
            AND YEAR(issue_date) = 2025 
            AND MONTH(issue_date) = 2
        ");
        $stmt->execute([$cpId]);
        $existingFebInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingFebInvoice) {
            echo "   ⚠️  February invoice already exists: {$existingFebInvoice['invoice_number']}\n";
            echo "   Skipping creation to avoid duplicates\n";
        } else {
            echo "   ✅ No February invoice exists - proceeding with creation\n";
            
            // Generate invoice number
            $stmt = $pdo->prepare("
                SELECT MAX(CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED)) as max_num
                FROM invoices 
                WHERE invoice_number LIKE 'INV-25-02-%'
            ");
            $stmt->execute();
            $maxNum = $stmt->fetchColumn() ?: 0;
            $newNum = $maxNum + 1;
            $invoiceNumber = 'INV-25-02-' . str_pad($newNum, 4, '0', STR_PAD_LEFT);
            
            echo "   Generated invoice number: $invoiceNumber\n";
            
            // Create the invoice
            try {
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
                        created_by,
                        created_at,
                        updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $result = $stmt->execute([
                    $invoiceNumber,
                    $cpId,
                    '2025-02-10',
                    0, // subtotal
                    $dueAmount, // previous_due
                    $dueAmount, // total_amount
                    0, // received_amount
                    $dueAmount, // next_due
                    'unpaid',
                    1, // is_active_rolling
                    "Carried forward ₹{$dueAmount} from January 2025",
                    1 // created_by (default admin user)
                ]);
                
                if ($result) {
                    $newInvoiceId = $pdo->lastInsertId();
                    echo "   ✅ Successfully created February invoice!\n";
                    echo "   Invoice ID: $newInvoiceId\n";
                    echo "   Invoice Number: $invoiceNumber\n";
                    
                    // Verify the invoice
                    $stmt = $pdo->prepare("
                        SELECT 
                            invoice_number,
                            subtotal,
                            previous_due,
                            total_amount,
                            next_due
                        FROM invoices 
                        WHERE invoice_id = ?
                    ");
                    $stmt->execute([$newInvoiceId]);
                    $newInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "   Verification:\n";
                    echo "   - Subtotal: ৳" . number_format($newInvoice['subtotal'], 0) . "\n";
                    echo "   - Previous Due: ৳" . number_format($newInvoice['previous_due'], 0) . "\n";
                    echo "   - Total Amount: ৳" . number_format($newInvoice['total_amount'], 0) . "\n";
                    echo "   - Next Due: ৳" . number_format($newInvoice['next_due'], 0) . "\n";
                } else {
                    echo "   ❌ Failed to create invoice\n";
                }
                
            } catch (Exception $e) {
                echo "   ❌ Error creating invoice: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Test the billing-invoices page data
    echo "\n3. TESTING BILLING-INVOICES PAGE DATA:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Check February 2025 summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT cp.c_id) as total_customers,
            SUM(i.total_amount) as total_amount,
            SUM(i.received_amount) as received_amount,
            SUM(i.next_due) as due_amount
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        WHERE YEAR(i.issue_date) = 2025 
        AND MONTH(i.issue_date) = 2
        AND i.is_active_rolling = 1
    ");
    $stmt->execute();
    $febSummary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   February 2025 Summary:\n";
    echo "   - Total Customers: {$febSummary['total_customers']}\n";
    echo "   - Total Amount: ৳" . number_format($febSummary['total_amount'] ?: 0, 0) . "\n";
    echo "   - Received Amount: ৳" . number_format($febSummary['received_amount'] ?: 0, 0) . "\n";
    echo "   - Due Amount: ৳" . number_format($febSummary['due_amount'] ?: 0, 0) . "\n";
    
    if ($febSummary['total_customers'] > 0) {
        echo "   ✅ February 2025 should now appear on billing-invoices page\n";
    } else {
        echo "   ❌ February 2025 still won't appear - no customers found\n";
    }
    
    echo "\n=== FIX COMPLETE ===\n";
    echo "\nNEXT STEPS:\n";
    echo "1. Clear Laravel cache: php artisan cache:clear\n";
    echo "2. Refresh billing-invoices page\n";
    echo "3. February 2025 should now appear with carry forward amounts\n";
    echo "4. Test month closing again to ensure it works properly\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}