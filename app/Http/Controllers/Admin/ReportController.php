<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function revenueReport()
    {
        try {
            // Monthly revenue data
            $revenueData = DB::table('invoices')
                ->select(
                    DB::raw('DATE_FORMAT(issue_date, "%Y-%m") as month'), 
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('SUM(received_amount) as collected'),
                    DB::raw('SUM(total_amount - received_amount) as pending')
                )
                ->whereNotNull('issue_date')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();

            // Current year totals
            $currentYear = date('Y');
            $yearlyTotals = DB::table('invoices')
                ->select(
                    DB::raw('SUM(total_amount) as yearly_revenue'),
                    DB::raw('SUM(received_amount) as yearly_collected'),
                    DB::raw('SUM(total_amount - received_amount) as yearly_pending')
                )
                ->whereYear('issue_date', $currentYear)
                ->first();

            // Current month data
            $currentMonth = date('Y-m');
            $currentMonthData = DB::table('invoices')
                ->select(
                    DB::raw('SUM(total_amount) as month_revenue'),
                    DB::raw('SUM(received_amount) as month_collected'),
                    DB::raw('SUM(total_amount - received_amount) as month_pending')
                )
                ->where('issue_date', 'like', $currentMonth . '%')
                ->first();

            // Revenue growth calculation
            $previousMonth = date('Y-m', strtotime('-1 month'));
            $previousMonthData = DB::table('invoices')
                ->select(
                    DB::raw('SUM(total_amount) as prev_month_revenue'),
                    DB::raw('SUM(received_amount) as prev_month_collected')
                )
                ->where('issue_date', 'like', $previousMonth . '%')
                ->first();

            $revenueGrowth = 0;
            if ($previousMonthData && $previousMonthData->prev_month_revenue > 0) {
                $revenueGrowth = (($currentMonthData->month_revenue ?? 0) - $previousMonthData->prev_month_revenue) / $previousMonthData->prev_month_revenue * 100;
            }

            // Payment method distribution
            $paymentMethods = DB::table('payments')
                ->select(
                    'payment_method',
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('COUNT(*) as transaction_count')
                )
                ->whereYear('payment_date', $currentYear)
                ->groupBy('payment_method')
                ->get();

            return view('admin.reports.revenue', compact(
                'revenueData',
                'yearlyTotals',
                'currentMonthData',
                'revenueGrowth',
                'paymentMethods'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating revenue report: ' . $e->getMessage());
        }
    }

    public function financialAnalytics()
    {
        try {
            // Total revenue, collected, pending
            $totals = DB::table('invoices')->select(
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(received_amount) as total_collected'),
                DB::raw('SUM(total_amount - received_amount) as total_pending')
            )->first();

            // Top 10 customers by total billed
            $topCustomers = DB::table('invoices')
                ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
                ->join('customers', 'customer_to_products.c_id', '=', 'customers.c_id')
                ->select(
                    'customers.name',
                    'customers.customer_id',
                    DB::raw('SUM(invoices.total_amount) as total_billed'),
                    DB::raw('SUM(invoices.received_amount) as total_paid'),
                    DB::raw('SUM(invoices.total_amount - invoices.received_amount) as total_balance')
                )
                ->groupBy('customers.c_id', 'customers.name', 'customers.customer_id')
                ->orderByDesc('total_billed')
                ->limit(10)
                ->get();

            // Monthly revenue trend (last 12 months)
            $monthlyTrend = DB::table('invoices')
                ->select(
                    DB::raw('DATE_FORMAT(issue_date, "%Y-%m") as month'),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('SUM(received_amount) as collected')
                )
                ->where('issue_date', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Revenue by product category
            $revenueByProduct = DB::table('invoices')
                ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
                ->join('products', 'customer_to_products.p_id', '=', 'products.p_id')
                ->select(
                    'products.name as product_name',
                    DB::raw('SUM(invoices.total_amount) as revenue')
                )
                ->groupBy('products.p_id', 'products.name')
                ->orderByDesc('revenue')
                ->limit(8)
                ->get();

            return view('admin.reports.financial', compact(
                'totals',
                'topCustomers',
                'monthlyTrend',
                'revenueByProduct'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating financial analytics: ' . $e->getMessage());
        }
    }

    public function customerStatistics()
    {
        try {
            $totalCustomers = DB::table('customers')->count();
            $activeCustomers = DB::table('customers')->where('is_active', 1)->count();
            $inactiveCustomers = $totalCustomers - $activeCustomers;

            // New customers by month (last 6 months)
            $newCustomers = DB::table('customers')
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Customers with unpaid/partial invoices
            $customersWithDues = DB::table('invoices')
                ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
                ->join('customers', 'customer_to_products.c_id', '=', 'customers.c_id')
                ->whereNotIn('invoices.status', ['paid', 'cancelled'])
                ->select(
                    'customers.name',
                    'customers.customer_id',
                    'customers.phone',
                    DB::raw('SUM(invoices.total_amount - invoices.received_amount) as total_due'),
                    DB::raw('COUNT(invoices.invoice_id) as pending_invoices')
                )
                ->groupBy('customers.c_id', 'customers.name', 'customers.customer_id', 'customers.phone')
                ->orderByDesc('total_due')
                ->limit(15)
                ->get();

            // Customer location distribution
            $customerLocations = DB::table('customers')
                ->select(
                    DB::raw('COALESCE(SUBSTRING_INDEX(SUBSTRING_INDEX(address, ",", -2), ",", 1), "Unknown") as city'),
                    DB::raw('COUNT(*) as customer_count')
                )
                ->groupBy('city')
                ->orderByDesc('customer_count')
                ->limit(10)
                ->get();

            return view('admin.reports.customers', compact(
                'totalCustomers',
                'activeCustomers',
                'inactiveCustomers',
                'newCustomers',
                'customersWithDues',
                'customerLocations'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating customer statistics: ' . $e->getMessage());
        }
    }

    public function collectionReports()
    {
        try {
            // Check if transaction_id column exists
            $columns = DB::getSchemaBuilder()->getColumnListing('payments');
            $hasTransactionId = in_array('transaction_id', $columns);

            // Build the select query based on available columns
            $selectColumns = [
                'payments.payment_id',
                'payments.payment_date',
                'payments.amount',
                'payments.payment_method',
                'customers.name as customer_name',
                'customers.customer_id',
                'invoices.invoice_number',
                'invoices.total_amount'
            ];

            // Add transaction_id only if it exists
            if ($hasTransactionId) {
                $selectColumns[] = 'payments.transaction_id';
            }

            // Get payments with customer and invoice info
            $collections = DB::table('payments')
                ->join('invoices', 'payments.invoice_id', '=', 'invoices.invoice_id')
                ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
                ->join('customers', 'customer_to_products.c_id', '=', 'customers.c_id')
                ->select($selectColumns)
                ->orderBy('payments.payment_date', 'desc')
                ->paginate(20);

            $totalCollected = DB::table('payments')->sum('amount');
            
            // Today's collections
            $todayCollected = DB::table('payments')
                ->whereDate('payment_date', today())
                ->sum('amount');

            // This month collections
            $monthCollected = DB::table('payments')
                ->whereYear('payment_date', date('Y'))
                ->whereMonth('payment_date', date('m'))
                ->sum('amount');

            // Payment method summary
            $paymentSummary = DB::table('payments')
                ->select(
                    'payment_method',
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('COUNT(*) as transaction_count')
                )
                ->whereYear('payment_date', date('Y'))
                ->groupBy('payment_method')
                ->get();

            return view('admin.reports.collections', compact(
                'collections',
                'totalCollected',
                'todayCollected',
                'monthCollected',
                'paymentSummary',
                'hasTransactionId'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating collection reports: ' . $e->getMessage());
        }
    }

    public function index()
    {
        try {
            // Quick stats for reports dashboard
            $quickStats = DB::table('invoices')
                ->select(
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('SUM(received_amount) as total_collected'),
                    DB::raw('SUM(total_amount - received_amount) as total_pending'),
                    DB::raw('COUNT(DISTINCT customer_to_products.c_id) as total_customers')
                )
                ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
                ->first();

            // Recent payments
            $recentPayments = DB::table('payments')
                ->join('invoices', 'payments.invoice_id', '=', 'invoices.invoice_id')
                ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
                ->join('customers', 'customer_to_products.c_id', '=', 'customers.c_id')
                ->select(
                    'payments.payment_date',
                    'payments.amount',
                    'customers.name as customer_name',
                    'invoices.invoice_number'
                )
                ->orderBy('payments.payment_date', 'desc')
                ->limit(5)
                ->get();

            // Monthly revenue for chart
            $monthlyRevenue = DB::table('invoices')
                ->select(
                    DB::raw('DATE_FORMAT(issue_date, "%Y-%m") as month'),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('SUM(received_amount) as collected')
                )
                ->where('issue_date', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return view('admin.reports.index', compact(
                'quickStats',
                'recentPayments',
                'monthlyRevenue'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error loading reports dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Export reports data
     */
    public function exportReport(Request $request, $type)
    {
        try {
            $data = [];
            $filename = '';
            
            switch ($type) {
                case 'revenue':
                    $data = DB::table('invoices')
                        ->select(
                            DB::raw('DATE_FORMAT(issue_date, "%Y-%m") as month'), 
                            DB::raw('SUM(total_amount) as total_revenue'),
                            DB::raw('SUM(received_amount) as collected'),
                            DB::raw('SUM(total_amount - received_amount) as pending')
                        )
                        ->whereNotNull('issue_date')
                        ->groupBy('month')
                        ->orderBy('month', 'desc')
                        ->get();
                    $filename = 'revenue-report-' . date('Y-m-d') . '.csv';
                    break;
                    
                case 'customers':
                    $data = DB::table('customers')
                        ->select('name', 'customer_id', 'phone', 'email', 'address', 'is_active', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->get();
                    $filename = 'customers-report-' . date('Y-m-d') . '.csv';
                    break;
                    
                case 'collections':
                    $data = DB::table('payments')
                        ->join('invoices', 'payments.invoice_id', '=', 'invoices.invoice_id')
                        ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
                        ->join('customers', 'customer_to_products.c_id', '=', 'customers.c_id')
                        ->select(
                            'payments.payment_date',
                            'payments.amount',
                            'payments.payment_method',
                            'customers.name as customer_name',
                            'invoices.invoice_number'
                        )
                        ->orderBy('payments.payment_date', 'desc')
                        ->get();
                    $filename = 'collections-report-' . date('Y-m-d') . '.csv';
                    break;
            }
            
            // In a real application, you would generate CSV or Excel file here
            // For now, we'll return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Export functionality would generate: ' . $filename,
                'data_count' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time dashboard data via AJAX
     */
    public function getDashboardData()
    {
        try {
            $todayRevenue = DB::table('invoices')
                ->whereDate('issue_date', today())
                ->sum('total_amount');
                
            $todayCollections = DB::table('payments')
                ->whereDate('payment_date', today())
                ->sum('amount');
                
            $activeCustomers = DB::table('customers')
                ->where('is_active', 1)
                ->count();
                
            $pendingInvoices = DB::table('invoices')
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'today_revenue' => $todayRevenue,
                    'today_collections' => $todayCollections,
                    'active_customers' => $activeCustomers,
                    'pending_invoices' => $pendingInvoices,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }
}