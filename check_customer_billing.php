<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\Customerproduct;
use Carbon\Carbon;

echo "=== CHECKING CUSTOMER BILLING SETUP ===\n\n";

// Find the customer with the invoice
$customer = Customer::where('name', 'Imteaz')->first();

if (!$customer) {
    echo "Customer 'Imteaz' not found!\n";
    exit;
}

echo "Customer: {$customer->name} (ID: {$customer->c_id})\n";
echo "Customer ID: {$customer->customer_id}\n\n";

// Get customer products
$customerProducts = Customerproduct::where('c_id', $customer->c_id)
    ->where('status', 'active')
    ->where('is_active', 1)
    ->with('product')
    ->get();

echo "Active Products:\n";
echo str_repeat('-', 70) . "\n";

foreach ($customerProducts as $cp) {
    echo "Product: {$cp->product->name}\n";
    echo "  CP ID: {$cp->cp_id}\n";
    echo "  Assign Date: {$cp->assign_date}\n";
    echo "  Billing Cycle: {$cp->billing_cycle_months} months\n";
    echo "  Monthly Price: ৳{$cp->product->monthly_price}\n";
    echo "  Due Date: {$cp->due_date}\n";
    echo "  Status: {$cp->status}\n\n";
    
    // Calculate when invoices should be generated
    $assignDate = Carbon::parse($cp->assign_date);
    $billingCycle = $cp->billing_cycle_months;
    
    echo "  Expected Invoice Months:\n";
    for ($i = 0; $i <= 12; $i++) {
        $invoiceMonth = $assignDate->copy()->addMonths($i * $billingCycle);
        if ($invoiceMonth->year == 2025) {
            $monthsDiff = $assignDate->diffInMonths($invoiceMonth);
            $isDue = ($monthsDiff % $billingCycle) == 0;
            echo "    - {$invoiceMonth->format('F Y')} (Month $monthsDiff)";
            if ($isDue) {
                echo " ✓ BILLING MONTH";
            }
            echo "\n";
        }
    }
    echo "\n";
}
