<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingReportController extends Controller
{
    /**
     * Display billing reports page with filters
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customerProduct.customer', 'customerProduct.product'])
            ->select([
                'invoices.*',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'customers.phone as customer_phone',
                'customers.customer_id as customer_code',
                'products.name as product_name',
                'customer_to_products.billing_cycle_months',
                'customer_to_products.custom_price'
            ])
            ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
            ->join('customers', 'customer_to_products.c_id', '=', 'customers.c_id')
            ->join('products', 'customer_to_products.p_id', '=', 'products.p_id');

        // Apply filters
        $query = $this->applyFilters($query, $request);

        // Get totals for statistics
        $totals = $this->getReportTotals($query);

        // Paginate results
        $invoices = $query->orderBy('invoices.issue_date', 'desc')
            ->paginate(25)
            ->appends($request->all());

        // Prepare filter data for view
        $filterData = $this->prepareFilterData($request);

        return view('admin.billing.reports', compact('invoices', 'totals', 'filterData'));
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, Request $request)
    {
        // Date range filters
        if ($request->filled('date_range')) {
            $range = $request->input('date_range');
            
            switch ($range) {
                case 'today':
                    $query->whereDate('invoices.issue_date', Carbon::today());
                    break;
                    
                case 'this_week':
                    $query->whereBetween('invoices.issue_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                    
                case 'this_month':
                    $query->whereMonth('invoices.issue_date', Carbon::now()->month)
                        ->whereYear('invoices.issue_date', Carbon::now()->year);
                    break;
                    
                case 'last_month':
                    $query->whereMonth('invoices.issue_date', Carbon::now()->subMonth()->month)
                        ->whereYear('invoices.issue_date', Carbon::now()->subMonth()->year);
                    break;
                    
                case 'last_3_months':
                    $query->where('invoices.issue_date', '>=', Carbon::now()->subMonths(3));
                    break;
                    
                case 'last_6_months':
                    $query->where('invoices.issue_date', '>=', Carbon::now()->subMonths(6));
                    break;
                    
                case 'this_year':
                    $query->whereYear('invoices.issue_date', Carbon::now()->year);
                    break;
            }
        }

        // Custom date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('invoices.issue_date', [
                $request->input('from_date'),
                $request->input('to_date')
            ]);
        }

        // Due status filter
        if ($request->filled('due_status')) {
            $dueStatus = $request->input('due_status');
            
            if ($dueStatus === 'due_only') {
                $query->whereColumn('invoices.total_amount', '>', 'invoices.received_amount')
                    ->where(function($q) {
                        $q->where('invoices.status', 'unpaid')
                          ->orWhere('invoices.status', 'partial');
                    });
            } elseif ($dueStatus === 'paid_only') {
                $query->whereColumn('invoices.total_amount', '<=', 'invoices.received_amount')
                    ->orWhere('invoices.status', 'paid');
            } elseif ($dueStatus === 'overdue') {
                $query->whereColumn('invoices.total_amount', '>', 'invoices.received_amount')
                    ->where('invoices.issue_date', '<', Carbon::now()->subDays(30));
            }
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_to_products.c_id', $request->input('customer_id'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('invoices.status', $request->input('status'));
        }

        // Search by invoice number or customer name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoices.invoice_number', 'LIKE', "%{$search}%")
                  ->orWhere('customers.name', 'LIKE', "%{$search}%")
                  ->orWhere('customers.customer_id', 'LIKE', "%{$search}%")
                  ->orWhere('customers.phone', 'LIKE', "%{$search}%");
            });
        }

        // Product filter
        if ($request->filled('product_id')) {
            $query->where('customer_to_products.p_id', $request->input('product_id'));
        }

        // Billing cycle filter
        if ($request->filled('billing_cycle')) {
            $query->where('customer_to_products.billing_cycle_months', $request->input('billing_cycle'));
        }

        return $query;
    }

    /**
     * Get report totals
     */
    private function getReportTotals($query)
    {
        $cloneQuery = clone $query;
        
        return $cloneQuery->select(
            DB::raw('COUNT(*) as total_invoices'),
            DB::raw('SUM(invoices.subtotal) as total_subtotal'),
            DB::raw('SUM(invoices.previous_due) as total_previous_due'),
            DB::raw('SUM(invoices.total_amount) as total_amount'),
            DB::raw('SUM(invoices.received_amount) as total_received'),
            DB::raw('SUM(CASE WHEN invoices.total_amount > invoices.received_amount THEN 1 ELSE 0 END) as due_invoices_count'),
            DB::raw('SUM(CASE WHEN invoices.received_amount >= invoices.total_amount THEN 1 ELSE 0 END) as paid_invoices_count')
        )->first();
    }

    /**
     * Prepare filter data for view
     */
    private function prepareFilterData(Request $request)
    {
        return [
            'date_range' => $request->input('date_range', ''),
            'from_date' => $request->input('from_date', ''),
            'to_date' => $request->input('to_date', ''),
            'due_status' => $request->input('due_status', ''),
            'customer_id' => $request->input('customer_id', ''),
            'status' => $request->input('status', ''),
            'search' => $request->input('search', ''),
            'product_id' => $request->input('product_id', ''),
            'billing_cycle' => $request->input('billing_cycle', ''),
            'customers' => Customer::active()->get(),
            'products' => Product::all(),
        ];
    }

    /**
     * Export reports to Excel
     */
    public function export(Request $request)
    {
        $query = Invoice::with(['customerProduct.customer', 'customerProduct.product'])
            ->select([
                'invoices.*',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'customers.phone as customer_phone',
                'customers.customer_id as customer_code',
                'products.name as product_name'
            ])
            ->join('customer_to_products', 'invoices.cp_id', '=', 'customer_to_products.cp_id')
            ->join('customers', 'customer_to_products.c_id', '=', 'customers.c_id')
            ->join('products', 'customer_to_products.p_id', '=', 'products.p_id');

        $query = $this->applyFilters($query, $request);
        $invoices = $query->orderBy('invoices.issue_date', 'desc')->get();

        // Add company header information
        $reportTitle = 'Nanosoft Monthly Billing Report';
        $generatedDate = Carbon::now()->format('F d, Y');
        
        // Return CSV with enhanced header
        return response()->streamDownload(function() use ($invoices, $reportTitle, $generatedDate, $request) {
            $handle = fopen('php://output', 'w');
            
            // Add company header
            fputcsv($handle, [$reportTitle]);
            fputcsv($handle, ['Generated on: ' . $generatedDate]);
            
            // Add filter information if any
            if ($request->hasAny(['date_range', 'from_date', 'to_date', 'customer_id', 'status', 'product_id'])) {
                $filters = [];
                if ($request->date_range) $filters[] = 'Period: ' . ucfirst(str_replace('_', ' ', $request->date_range));
                if ($request->from_date && $request->to_date) $filters[] = 'From: ' . $request->from_date . ' To: ' . $request->to_date;
                if ($request->customer_id) {
                    $customer = Customer::find($request->customer_id);
                    if ($customer) $filters[] = 'Customer: ' . $customer->name;
                }
                if ($request->status) $filters[] = 'Status: ' . ucfirst($request->status);
                if ($request->product_id) {
                    $product = Product::find($request->product_id);
                    if ($product) $filters[] = 'Product: ' . $product->name;
                }
                
                if (!empty($filters)) {
                    fputcsv($handle, ['Filters: ' . implode(', ', $filters)]);
                }
            }
            
            // Empty line for spacing
            fputcsv($handle, []);
            
            // Add column headers
            fputcsv($handle, [
                'Invoice ID', 'Invoice Date', 'Customer Name', 'Customer ID', 'Phone',
                'Product', 'Subtotal', 'Previous Due', 'Total Amount', 
                'Received Amount', 'Next Due', 'Status'
            ]);
            
            // Add rows
            foreach ($invoices as $invoice) {
                $nextDue = max(0, ($invoice->total_amount - $invoice->received_amount));
                
                fputcsv($handle, [
                    $invoice->invoice_number,
                    $invoice->issue_date,
                    $invoice->customer_name,
                    $invoice->customer_code,
                    $invoice->customer_phone,
                    $invoice->product_name,
                    $invoice->subtotal,
                    $invoice->previous_due,
                    $invoice->total_amount,
                    $invoice->received_amount,
                    $nextDue,
                    ucfirst($invoice->status)
                ]);
            }
            
            fclose($handle);
        }, 'billing-report-' . Carbon::now()->format('Y-m-d') . '.csv');
    }
}