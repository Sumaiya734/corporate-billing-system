<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== DEBUGGING MONTHLY SUMMARY ===\n\n";

$months = ['2025-05', '2025-08', '2025-09', '2025-10', '2025-11', '2025-12'];

foreach ($months as $month) {
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "MONTH: " . $monthDate->format('F Y') . " ($month)\n";
    echo str_repeat('=', 70) . "\n";
    
    // Replicate the getCustomersForMonth logic
    $customers = DB::table('customers as c')
        ->join('customer_to_products as cp', 'c.c_id', '=', 'cp.c_id')
        ->where('c.is_active', 1)
        ->where('cp.status', 'active')
        ->where('cp.is_active', 1)
        ->where(function($query) use ($monthDate) {
            // Condition 1: Assigned in this month (advance payment)
            $query->where(function($q) use ($monthDate) {
                $q->whereYear('cp.assign_date', $monthDate->year)
                  ->whereMonth('cp.assign_date', $monthDate->month);
            })
            // Condition 2: Due in this month (based on billing cycle)
            ->orWhere(function($q) use ($monthDate) {
                $q->where('cp.assign_date', '<=', $monthDate->endOfMonth())
                  ->whereRaw('
                      PERIOD_DIFF(
                          DATE_FORMAT(?, "%Y%m"),
                          DATE_FORMAT(cp.assign_date, "%Y%m")
                      ) % cp.billing_cycle_months = 0
                  ', [$monthDate->format('Y-m-01')])
                  ->whereRaw('
                          PERIOD_DIFF(
                              DATE_FORMAT(?, "%Y%m"),
                              DATE_FORMAT(cp.assign_date, "%Y%m")
                          ) >= 0
                      ', [$monthDate->format('Y-m-01')])
                  ->whereRaw('DATE_FORMAT(cp.assign_date, "%Y-%m") != ?', [$monthDate->format('Y-m')]);
            })
            // Condition 3: Has unpaid invoices that were issued BEFORE this month
            ->orWhere(function($q) use ($monthDate) {
                $q->whereExists(function($existsQuery) use ($monthDate) {
                    $existsQuery->select(DB::raw(1))
                        ->from('invoices as i')
                        ->join('customer_to_products as cp2', 'i.cp_id', '=', 'cp2.cp_id')
                        ->whereColumn('cp2.c_id', 'c.c_id')
                        ->whereIn('i.status', ['unpaid', 'partial', 'confirmed'])
                        ->where('i.next_due', '>', 0)
                        ->where('i.issue_date', '<', $monthDate->startOfMonth());
                })
                ->where('cp.assign_date', '<', $monthDate->startOfMonth());
            });
        })
        ->distinct('c.c_id')
        ->select('c.c_id', 'c.name', 'c.customer_id', 'cp.assign_date', 'cp.billing_cycle_months')
        ->get();
    
    echo "Customers Found: " . $customers->count() . "\n";
    
    foreach ($customers as $customer) {
        echo "  - {$customer->name} (ID: {$customer->c_id})\n";
        echo "    Assign Date: {$customer->assign_date}\n";
        echo "    Billing Cycle: {$customer->billing_cycle_months} months\n";
        
        // Check which condition matched
        $assignDate = Carbon::parse($customer->assign_date);
        $isAssigned = ($assignDate->year == $monthDate->year && $assignDate->month == $monthDate->month);
        $monthsDiff = $assignDate->diffInMonths($monthDate);
        $isDue = ($monthsDiff >= 0 && $monthsDiff % $customer->billing_cycle_months == 0 && !$isAssigned);
        
        echo "    Months Diff: $monthsDiff\n";
        echo "    Is Assigned Month: " . ($isAssigned ? 'YES' : 'NO') . "\n";
        echo "    Is Due Month: " . ($isDue ? 'YES' : 'NO') . "\n";
        
        // Check for unpaid invoices
        $unpaidInvoices = DB::table('invoices as i')
            ->join('customer_to_products as cp2', 'i.cp_id', '=', 'cp2.cp_id')
            ->where('cp2.c_id', $customer->c_id)
            ->whereIn('i.status', ['unpaid', 'partial', 'confirmed'])
            ->where('i.next_due', '>', 0)
            ->where('i.issue_date', '<', $monthDate->startOfMonth())
            ->count();
        
        echo "    Has Unpaid Invoices: " . ($unpaidInvoices > 0 ? "YES ($unpaidInvoices)" : 'NO') . "\n";
    }
}
