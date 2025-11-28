<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get total active customers
        $totalCustomers = DB::table('customers')
            ->where('is_active', 1)
            ->count();

        // Get monthly revenue (current month)
        $currentMonth = now()->format('Y-m');
        $monthlyRevenue = DB::table('invoices')
            ->where('status', 'paid')
            ->where(DB::raw("DATE_FORMAT(issue_date, '%Y-%m')"), $currentMonth)
            ->sum('received_amount');

        // Get pending bills
        $pendingBills = DB::table('invoices')
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        // Get active products
        $activeproducts = DB::table('customer_to_products')
            ->where('is_active', 1)
            ->where('status', 'active')
            ->count();

        // Get overdue bills
        $overdueBills = DB::table('invoices')
            ->where('status', 'unpaid')
            ->where('issue_date', '<', now()->subDays(30))
            ->count();

        // Get paid invoices count
        $paidInvoices = DB::table('invoices')
            ->where('status', 'paid')
            ->count();

        // Get new customers (this month)
        $newCustomers = DB::table('customers')
            ->where('is_active', 1)
            ->where(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), $currentMonth)
            ->count();

        // Get revenue data for chart (last 12 months)
        $revenueData = DB::table('invoices')
            ->select(
                DB::raw("DATE_FORMAT(issue_date, '%Y-%m') as month"),
                DB::raw("SUM(received_amount) as revenue")
            )
            ->where('status', 'paid')
            ->where('issue_date', '>=', now()->subMonths(11))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get product distribution
        $productDistribution = DB::table('customer_to_products as cp')
            ->join('products as p', 'cp.p_id', '=', 'p.p_id')
            ->select('p.name', DB::raw('COUNT(*) as count'))
            ->where('cp.is_active', 1)
            ->where('cp.status', 'active')
            ->groupBy('p.name', 'p.p_id')
            ->get();

        // Get recent activity
        $recentActivity = DB::table('invoices as i')
            ->join('customer_to_products as cp', 'i.cp_id', '=', 'cp.cp_id')
            ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
            ->select(
                'c.name as customer_name',
                'i.status',
                'i.issue_date',
                DB::raw("'Invoice' as type")
            )
            ->where('i.issue_date', '>=', now()->subDays(7))
            ->unionAll(
                DB::table('customer_to_products as cp')
                    ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
                    ->join('products as p', 'cp.p_id', '=', 'p.p_id')
                    ->select(
                        'c.name as customer_name',
                        DB::raw("'active' as status"),
                        'cp.created_at as issue_date',
                        DB::raw("CONCAT('product: ', p.name) as type")
                    )
                    ->where('cp.created_at', '>=', now()->subDays(7))
            )
            ->orderBy('issue_date', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalCustomers',
            'monthlyRevenue',
            'pendingBills',
            'activeproducts',
            'overdueBills',
            'paidInvoices',
            'newCustomers',
            'revenueData',
            'productDistribution',
            'recentActivity'
        ));
    }

    public function refreshData()
    {
        // This method can be used for AJAX refresh
        $data = [
            'totalCustomers' => DB::table('customers')->where('is_active', 1)->count(),
            'monthlyRevenue' => DB::table('invoices')
                ->where('status', 'paid')
                ->where(DB::raw("DATE_FORMAT(issue_date, '%Y-%m')"), now()->format('Y-m'))
                ->sum('received_amount'),
            'pendingBills' => DB::table('invoices')->whereIn('status', ['unpaid', 'partial'])->count(),
            'activeproducts' => DB::table('customer_to_products')->where('is_active', 1)->where('status', 'active')->count(),
        ];

        return response()->json($data);
    }
}