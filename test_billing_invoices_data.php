<?php

echo "=== TESTING BILLING-INVOICES PAGE DATA ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Test the new logic for February 2025
    echo "1. TESTING NEW LOGIC FOR FEBRUARY 2025:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $month = '2025-02';
    $monthDate = new DateTime($month . '-01');
    
    // Get customers who have invoices in February 2025 (new logic)
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.c_id, c.name, c.customer_id
        FROM customers c
        JOIN customer_to_products cp ON c.c_id = cp.c_id
        JOIN invoices i ON cp.cp_id = i.cp_id
        WHERE c.is_active = 1
        AND cp.status = 'active'
        AND cp.is_active = 1
        AND i.is_active_rolling = 1
        AND YEAR(i.issue_date) = ?
        AND MONTH(i.issue_date) = ?
    ");
    $stmt->execute([$monthDate->format('Y'), $monthDate->format('n')]);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Customers with invoices in February 2025:\n";
    $customerIds = [];
    foreach ($customers as $customer) {
        echo "   - {$customer['name']} (ID: {$customer['c_id']})\n";
        $customerIds[] = $customer['c_id'];
    }
    
    // Get actual invoices for these customers in February 2025
    if (!empty($customerIds)) {
        $placeholders = str_repeat('?,', count($customerIds) - 1) . '?';
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
            WHERE cp.c_id IN ($placeholders)
            AND YEAR(i.issue_date) = ?
            AND MONTH(i.issue_date) = ?
            AND i.is_active_rolling = 1
        ");
        $params = array_merge($customerIds, [$monthDate->format('Y'), $monthDate->format('n')]);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n   Invoices for February 2025:\n";
        $totalAmount = 0;
        $receivedAmount = 0;
        $dueAmount = 0;
        
        foreach ($invoices as $invoice) {
            echo "   - {$invoice['invoice_number']} ({$invoice['customer_name']}): ";
            echo "Total ৳" . number_format($invoice['total_amount'], 0) . ", ";
            echo "Received ৳" . number_format($invoice['received_amount'], 0) . ", ";
            echo "Due ৳" . number_format($invoice['next_due'], 0) . "\n";
            
            $totalAmount += $invoice['total_amount'];
            $receivedAmount += $invoice['received_amount'];
            $dueAmount += $invoice['next_due'];
        }
        
        echo "\n   FEBRUARY 2025 SUMMARY (New Logic):\n";
        echo "   Total Customers: " . count($customers) . "\n";
        echo "   Total Amount: ৳" . number_format($totalAmount, 0) . "\n";
        echo "   Received Amount: ৳" . number_format($receivedAmount, 0) . "\n";
        echo "   Due Amount: ৳" . number_format($dueAmount, 0) . "\n";
    }
    
    // Test January 2025 as well
    echo "\n2. TESTING JANUARY 2025 FOR COMPARISON:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $janMonth = '2025-01';
    $janMonthDate = new DateTime($janMonth . '-01');
    
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
        WHERE YEAR(i.issue_date) = ?
        AND MONTH(i.issue_date) = ?
        AND i.is_active_rolling = 1
    ");
    $stmt->execute([$janMonthDate->format('Y'), $janMonthDate->format('n')]);
    $janInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   January 2025 invoices:\n";
    $janTotalAmount = 0;
    $janReceivedAmount = 0;
    $janDueAmount = 0;
    
    foreach ($janInvoices as $invoice) {
        echo "   - {$invoice['invoice_number']} ({$invoice['customer_name']}): ";
        echo "Total ৳" . number_format($invoice['total_amount'], 0) . ", ";
        echo "Received ৳" . number_format($invoice['received_amount'], 0) . ", ";
        echo "Due ৳" . number_format($invoice['next_due'], 0) . "\n";
        
        $janTotalAmount += $invoice['total_amount'];
        $janReceivedAmount += $invoice['received_amount'];
        $janDueAmount += $invoice['next_due'];
    }
    
    echo "\n   JANUARY 2025 SUMMARY:\n";
    echo "   Total Customers: " . count($janInvoices) . "\n";
    echo "   Total Amount: ৳" . number_format($janTotalAmount, 0) . "\n";
    echo "   Received Amount: ৳" . number_format($janReceivedAmount, 0) . "\n";
    echo "   Due Amount: ৳" . number_format($janDueAmount, 0) . "\n";
    
    // Check if the carry forward is correct
    echo "\n3. CARRY FORWARD VERIFICATION:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    echo "   January next_due: ৳" . number_format($janDueAmount, 0) . "\n";
    echo "   February previous_due should be: ৳" . number_format($janDueAmount, 0) . "\n";
    
    // Get February invoice previous_due
    $stmt = $pdo->prepare("
        SELECT SUM(previous_due) as total_previous_due
        FROM invoices 
        WHERE YEAR(issue_date) = 2025 
        AND MONTH(issue_date) = 2
        AND is_active_rolling = 1
    ");
    $stmt->execute();
    $febPreviousDue = $stmt->fetchColumn();
    
    echo "   February actual previous_due: ৳" . number_format($febPreviousDue, 0) . "\n";
    
    if (abs($janDueAmount - $febPreviousDue) < 1) {
        echo "   ✅ Carry forward is CORRECT\n";
    } else {
        echo "   ❌ Carry forward is INCORRECT\n";
        echo "   Difference: ৳" . number_format(abs($janDueAmount - $febPreviousDue), 0) . "\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    echo "\nEXPECTED RESULTS ON BILLING-INVOICES PAGE:\n";
    echo "- February 2025: " . count($customers) . " customers, ৳" . number_format($totalAmount, 0) . " total, ৳" . number_format($dueAmount, 0) . " due\n";
    echo "- January 2025: " . count($janInvoices) . " customers, ৳" . number_format($janTotalAmount, 0) . " total, ৳" . number_format($janDueAmount, 0) . " due\n";
    echo "\nAfter clearing cache and refreshing, the billing-invoices page should show these values.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}