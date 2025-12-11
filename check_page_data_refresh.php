<?php

echo "=== CHECKING PAGE DATA REFRESH ISSUE ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check current database values
    echo "1. CURRENT DATABASE VALUES:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // February invoice
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
            c.name as customer_name,
            p.name as product_name
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        JOIN products p ON cp.p_id = p.p_id
        WHERE i.invoice_number = 'INV-25-02-0002'
    ");
    $stmt->execute();
    $febInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($febInvoice) {
        echo "   February Invoice (INV-25-02-0002) - Database Values:\n";
        echo "   Customer: {$febInvoice['customer_name']}\n";
        echo "   Product: {$febInvoice['product_name']}\n";
        echo "   Subtotal: ৳" . number_format($febInvoice['subtotal'], 0) . "\n";
        echo "   Previous Due: ৳" . number_format($febInvoice['previous_due'], 0) . "\n";
        echo "   Total Amount: ৳" . number_format($febInvoice['total_amount'], 0) . "\n";
        echo "   Received Amount: ৳" . number_format($febInvoice['received_amount'], 0) . "\n";
        echo "   Next Due: ৳" . number_format($febInvoice['next_due'], 0) . "\n";
        echo "   Status: {$febInvoice['status']}\n";
    }
    
    // January invoice
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
            c.name as customer_name,
            p.name as product_name
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        JOIN products p ON cp.p_id = p.p_id
        WHERE i.invoice_number = 'INV-25-01-0001'
    ");
    $stmt->execute();
    $janInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($janInvoice) {
        echo "\n   January Invoice (INV-25-01-0001) - Database Values:\n";
        echo "   Customer: {$janInvoice['customer_name']}\n";
        echo "   Product: {$janInvoice['product_name']}\n";
        echo "   Subtotal: ৳" . number_format($janInvoice['subtotal'], 0) . "\n";
        echo "   Previous Due: ৳" . number_format($janInvoice['previous_due'], 0) . "\n";
        echo "   Total Amount: ৳" . number_format($janInvoice['total_amount'], 0) . "\n";
        echo "   Received Amount: ৳" . number_format($janInvoice['received_amount'], 0) . "\n";
        echo "   Next Due: ৳" . number_format($janInvoice['next_due'], 0) . "\n";
        echo "   Status: {$janInvoice['status']}\n";
    }
    
    // Check what the monthly summary shows
    echo "\n2. MONTHLY SUMMARY CALCULATION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // February 2025 summary
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
    
    echo "   February 2025 Summary (calculated from database):\n";
    echo "   Total Customers: {$febSummary['total_customers']}\n";
    echo "   Total Amount: ৳" . number_format($febSummary['total_amount'], 0) . "\n";
    echo "   Received Amount: ৳" . number_format($febSummary['received_amount'], 0) . "\n";
    echo "   Due Amount: ৳" . number_format($febSummary['due_amount'], 0) . "\n";
    
    // Check if there are any cached values or transformation issues
    echo "\n3. CHECKING FOR TRANSFORMATION ISSUES:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Check if the MonthlyBillController is applying transformations
    echo "   The monthly-bills page uses transformation logic that might be:\n";
    echo "   - Showing historical amounts instead of current database values\n";
    echo "   - Caching old calculations\n";
    echo "   - Not refreshing after database updates\n";
    
    // Check all February invoices
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_number,
            i.total_amount,
            i.received_amount,
            i.next_due,
            c.name as customer_name
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE YEAR(i.issue_date) = 2025 
        AND MONTH(i.issue_date) = 2
        AND i.is_active_rolling = 1
    ");
    $stmt->execute();
    $allFebInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n   All February 2025 invoices in database:\n";
    foreach ($allFebInvoices as $invoice) {
        echo "   - {$invoice['invoice_number']} ({$invoice['customer_name']}): ";
        echo "Total ৳" . number_format($invoice['total_amount'], 0) . ", ";
        echo "Received ৳" . number_format($invoice['received_amount'], 0) . ", ";
        echo "Due ৳" . number_format($invoice['next_due'], 0) . "\n";
    }
    
    // Check browser cache or session issues
    echo "\n4. POTENTIAL CAUSES:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    echo "   Possible reasons for outdated display:\n";
    echo "   1. ❓ Browser cache - try hard refresh (Ctrl+F5)\n";
    echo "   2. ❓ Laravel cache - application might be caching query results\n";
    echo "   3. ❓ Transformation logic - monthly-bills page transforms data for historical view\n";
    echo "   4. ❓ Session data - old data might be stored in session\n";
    echo "   5. ❓ Database connection - using wrong database or connection\n";
    
    echo "\n=== DIAGNOSIS COMPLETE ===\n";
    echo "\nRECOMMENDED ACTIONS:\n";
    echo "1. Clear browser cache and hard refresh (Ctrl+F5)\n";
    echo "2. Clear Laravel application cache\n";
    echo "3. Check if transformation logic is overriding database values\n";
    echo "4. Verify the pages are reading from correct database\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}