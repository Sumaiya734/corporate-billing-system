<?php

echo "=== TESTING FEBRUARY 2025 ACCESS ===\n\n";

$host = 'localhost';
$dbname = 'billing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Test 1: Check if February can be accessed
    echo "1. FEBRUARY 2025 ACCESS CHECK:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $month = '2025-02';
    $monthDate = new DateTime($month . '-01');
    $currentMonth = date('Y-m');
    
    echo "   Target month: $month\n";
    echo "   Current month: $currentMonth\n";
    echo "   Is future month: " . ($month > $currentMonth ? 'Yes' : 'No') . "\n";
    echo "   Is current month: " . ($month === $currentMonth ? 'Yes' : 'No') . "\n";
    
    // Check if previous month (January) is closed
    $previousMonth = '2025-01';
    $stmt = $pdo->prepare("
        SELECT is_closed FROM billing_periods 
        WHERE billing_month = ?
    ");
    $stmt->execute([$previousMonth]);
    $isPreviousClosed = $stmt->fetchColumn();
    
    echo "   Previous month (January): " . ($isPreviousClosed ? 'CLOSED' : 'NOT CLOSED') . "\n";
    echo "   Can access February: " . ($isPreviousClosed || $month === $currentMonth ? 'YES' : 'NO') . "\n";
    
    // Test 2: Check what invoices should appear in February
    echo "\n2. INVOICES THAT SHOULD APPEAR IN FEBRUARY:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Simulate the MonthlyBillController query
    $stmt = $pdo->prepare("
        SELECT 
            i.invoice_id,
            i.invoice_number,
            i.issue_date,
            i.subtotal,
            i.previous_due,
            i.total_amount,
            i.received_amount,
            i.next_due,
            i.status,
            i.is_active_rolling,
            cp.assign_date,
            c.name as customer_name
        FROM invoices i
        JOIN customer_to_products cp ON i.cp_id = cp.cp_id
        JOIN customers c ON cp.c_id = c.c_id
        WHERE cp.status = 'active' 
        AND cp.is_active = 1
        AND cp.assign_date <= ?
        AND i.is_active_rolling = 1
        AND i.issue_date <= ?
        ORDER BY i.issue_date DESC
    ");
    $stmt->execute([$monthDate->format('Y-m-t'), $monthDate->format('Y-m-t')]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($invoices)) {
        echo "   ❌ No invoices found for February\n";
        echo "   This means February monthly-bills page will be empty\n";
    } else {
        foreach ($invoices as $invoice) {
            echo "   Invoice: {$invoice['invoice_number']}\n";
            echo "   - Customer: {$invoice['customer_name']}\n";
            echo "   - Issue Date: {$invoice['issue_date']}\n";
            echo "   - Assign Date: {$invoice['assign_date']}\n";
            echo "   - Total: ৳" . number_format($invoice['total_amount'], 0) . "\n";
            echo "   - Next Due: ৳" . number_format($invoice['next_due'], 0) . "\n";
            echo "   - Status: {$invoice['status']}\n\n";
        }
    }
    
    // Test 3: Check customers who should appear in February
    echo "3. CUSTOMERS WHO SHOULD APPEAR IN FEBRUARY:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            c.c_id,
            c.name,
            c.customer_id,
            cp.assign_date,
            cp.billing_cycle_months
        FROM customers c
        JOIN customer_to_products cp ON c.c_id = cp.c_id
        WHERE cp.status = 'active'
        AND cp.is_active = 1
        AND c.is_active = 1
        AND cp.assign_date <= ?
        ORDER BY c.name
    ");
    $stmt->execute([$monthDate->format('Y-m-t')]);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($customers)) {
        echo "   ❌ No customers found for February\n";
    } else {
        foreach ($customers as $customer) {
            echo "   Customer: {$customer['name']} (ID: {$customer['customer_id']})\n";
            echo "   - Assigned: {$customer['assign_date']}\n";
            echo "   - Billing Cycle: {$customer['billing_cycle_months']} months\n";
            
            // Check if this customer should be billed in February
            $assignDate = new DateTime($customer['assign_date']);
            $monthsSinceAssign = $assignDate->diff($monthDate)->m + ($assignDate->diff($monthDate)->y * 12);
            $shouldBill = ($monthsSinceAssign % $customer['billing_cycle_months']) == 0;
            
            echo "   - Months since assign: $monthsSinceAssign\n";
            echo "   - Should bill in February: " . ($shouldBill ? 'YES' : 'NO') . "\n\n";
        }
    }
    
    // Test 4: Check if February should show on billing-invoices page
    echo "4. FEBRUARY ON BILLING-INVOICES PAGE:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Check if February has any activity
    $hasInvoices = !empty($invoices);
    $hasCustomers = !empty($customers);
    $hasActivity = $hasInvoices || $hasCustomers;
    
    echo "   Has invoices: " . ($hasInvoices ? 'YES' : 'NO') . "\n";
    echo "   Has customers: " . ($hasCustomers ? 'YES' : 'NO') . "\n";
    echo "   Has activity: " . ($hasActivity ? 'YES' : 'NO') . "\n";
    echo "   Should appear on billing-invoices: " . ($hasActivity ? 'YES' : 'NO') . "\n";
    
    echo "\n=== TEST COMPLETE ===\n";
    echo "\nCONCLUSION:\n";
    if ($hasActivity) {
        echo "✅ February should appear on billing-invoices page\n";
        echo "✅ February monthly-bills page should be accessible\n";
        echo "✅ February should show the January rolling invoice\n";
    } else {
        echo "❌ February has no activity - won't appear on billing-invoices page\n";
        echo "❌ This is likely why the user doesn't see February\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}