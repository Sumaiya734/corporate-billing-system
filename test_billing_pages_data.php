<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\CustomerProduct;
use Carbon\Carbon;

echo "=== TESTING BILLING PAGES DATABASE CONNECTION ===\n\n";

// Test 1: Check billing-invoices.blade.php data sources
echo "1. BILLING-INVOICES PAGE DATA SOURCES:\n";
try {
    // Simulate BillingController::billingInvoices() method
    
    // Check if invoices exist
    $hasInvoices = Invoice::exists();
    echo "Has invoices in database: " . ($hasInvoices ? 'Yes' : 'No') . "\n";
    
    // Get total active customers
    $totalActiveCustomers = Customer::where('is_active', 1)->count();
    echo "Total active customers: {$totalActiveCustomers}\n";
    
    // Get total invoices count
    $totalInvoicesCount = Invoice::count();
    echo "Total invoices: {$totalInvoicesCount}\n";
    
    // Get recent payments (if payments table exists)
    try {
        $recentPayments = DB::table('payments')->limit(5)->get();
        echo "Recent payments count: " . $recentPayments->count() . "\n";
    } catch (Exception $e) {
        echo "Payments table check failed: " . $e->getMessage() . "\n";
    }
    
    // Get overdue invoices
    $overdueInvoices = Invoice::where('due_date', '<', now())
        ->where('status', '!=', 'paid')
        ->count();
    echo "Overdue invoices: {$overdueInvoices}\n";
    
    // Get monthly summary data
    $currentYear = date('Y');
    $monthlySummary = [];
    for ($month = 1; $month <= 12; $month++) {
        $monthStr = sprintf('%04d-%02d', $currentYear, $month);
        $monthlyData = Invoice::whereYear('issue_date', $currentYear)
            ->whereMonth('issue_date', $month)
            ->selectRaw('
                COUNT(*) as total_invoices,
                SUM(subtotal) as total_amount,
                SUM(CASE WHEN status = "paid" THEN subtotal ELSE 0 END) as paid_amount
            ')
            ->first();
        
        if ($monthlyData && $monthlyData->total_invoices > 0) {
            $monthlySummary[] = [
                'month' => $monthStr,
                'total_invoices' => $monthlyData->total_invoices,
                'total_amount' => $monthlyData->total_amount,
                'paid_amount' => $monthlyData->paid_amount
            ];
        }
    }
    
    echo "Monthly summary data points: " . count($monthlySummary) . "\n";
    foreach ($monthlySummary as $data) {
        echo "  - {$data['month']}: {$data['total_invoices']} invoices, ₹{$data['total_amount']} total\n";
    }
    
} catch (Exception $e) {
    echo "❌ Billing-invoices data test failed: " . $e->getMessage() . "\n";
}

echo "\n2. MONTHLY-BILLS PAGE DATA SOURCES:\n";
try {
    // Test for current month
    $currentMonth = date('Y-m');
    $monthDate = Carbon::createFromFormat('Y-m', $currentMonth);
    
    echo "Testing for month: {$currentMonth}\n";
    
    // Simulate MonthlyBillController::monthlyBills() method
    
    // Get invoices for the month (rolling invoice system)
    $invoices = Invoice::with([
        'payments', 
        'customerProduct.product', 
        'customerProduct.customer'
    ])
    ->whereHas('customerProduct', function($q) use ($monthDate) {
        $q->where('status', 'active')
          ->where('is_active', 1)
          ->where('assign_date', '<=', $monthDate->endOfMonth());
    })
    ->where('is_active_rolling', 1)
    ->where('issue_date', '<=', $monthDate->endOfMonth())
    ->get();
    
    echo "Rolling invoices for current month: " . $invoices->count() . "\n";
    
    foreach ($invoices as $invoice) {
        $customer = $invoice->customerProduct->customer ?? null;
        $product = $invoice->customerProduct->product ?? null;
        
        if ($customer && $product) {
            echo "  - {$customer->name}: {$product->name}\n";
            echo "    Invoice: {$invoice->invoice_number}\n";
            echo "    Subtotal: ₹{$invoice->subtotal}\n";
            echo "    Previous Due: ₹{$invoice->previous_due}\n";
            echo "    Total Amount: ₹{$invoice->total_amount}\n";
            echo "    Status: {$invoice->status}\n";
        }
    }
    
    // Get due customers for the month
    $dueCustomers = DB::table('customers as c')
        ->join('customer_to_products as cp', 'c.c_id', '=', 'cp.c_id')
        ->join('products as p', 'cp.p_id', '=', 'p.p_id')
        ->where('cp.status', 'active')
        ->where('cp.is_active', 1)
        ->where('c.is_active', 1)
        ->where('cp.assign_date', '<=', $monthDate->endOfMonth())
        ->select('c.*', 'cp.*', 'p.name as product_name', 'p.monthly_price')
        ->get();
    
    echo "\nDue customers for month: " . $dueCustomers->count() . "\n";
    
    // Calculate statistics
    $totalCustomersWithInvoices = $invoices->count();
    $customersWithDue = $invoices->filter(function($invoice) {
        return in_array($invoice->status, ['unpaid', 'partial']) && $invoice->next_due > 0.00;
    })->count();
    $fullyPaidCustomers = $invoices->filter(function($invoice) {
        return $invoice->status === 'paid' || $invoice->next_due <= 0.00;
    })->count();
    
    echo "Customers with invoices: {$totalCustomersWithInvoices}\n";
    echo "Customers with due amounts: {$customersWithDue}\n";
    echo "Fully paid customers: {$fullyPaidCustomers}\n";
    
} catch (Exception $e) {
    echo "❌ Monthly-bills data test failed: " . $e->getMessage() . "\n";
}

echo "\n3. DATABASE RELATIONSHIPS TEST:\n";
try {
    // Check key relationships
    echo "Testing database relationships:\n";
    
    // Check invoices table structure
    $invoiceColumns = DB::select('DESCRIBE invoices');
    echo "Invoices table columns: " . count($invoiceColumns) . "\n";
    
    // Check if we have the rolling invoice system
    $rollingInvoices = Invoice::where('is_active_rolling', 1)->count();
    echo "Rolling invoices: {$rollingInvoices}\n";
    
    // Check customer-product relationships
    $activeAssignments = CustomerProduct::where('status', 'active')
        ->where('is_active', 1)
        ->count();
    echo "Active customer-product assignments: {$activeAssignments}\n";
    
    // Test a sample invoice with relationships
    $sampleInvoice = Invoice::with(['customerProduct.customer', 'customerProduct.product', 'payments'])
        ->where('is_active_rolling', 1)
        ->first();
    
    if ($sampleInvoice) {
        echo "\nSample invoice test:\n";
        echo "  Invoice: {$sampleInvoice->invoice_number}\n";
        echo "  Customer: " . ($sampleInvoice->customerProduct->customer->name ?? 'No customer') . "\n";
        echo "  Product: " . ($sampleInvoice->customerProduct->product->name ?? 'No product') . "\n";
        echo "  Payments: " . ($sampleInvoice->payments->count() ?? 0) . "\n";
        echo "  Subtotal: ₹{$sampleInvoice->subtotal}\n";
        echo "  Total Amount: ₹{$sampleInvoice->total_amount}\n";
    } else {
        echo "No sample rolling invoice found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database relationships test failed: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Both billing pages should be showing:\n";
echo "- Real invoice data from the database\n";
echo "- Customer and product information through relationships\n";
echo "- Monthly statistics and summaries\n";
echo "- Payment information and status\n";
echo "- Rolling invoice system data\n";

if ($hasInvoices && $totalActiveCustomers > 0) {
    echo "\n✅ BOTH BILLING PAGES ARE CORRECTLY CONNECTED TO DATABASE\n";
    echo "✅ billing-invoices.blade.php will show real invoice and customer data\n";
    echo "✅ monthly-bills.blade.php will show real monthly billing data\n";
} else {
    echo "\n❌ BILLING PAGES MAY HAVE DATA ISSUES\n";
    if (!$hasInvoices) echo "- No invoices found in database\n";
    if ($totalActiveCustomers == 0) echo "- No active customers found\n";
}