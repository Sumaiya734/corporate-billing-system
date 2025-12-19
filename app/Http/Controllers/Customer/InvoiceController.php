<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the customer's invoices.
     */
    public function index(Request $request)
    {
        // Get authenticated customer
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        // Get invoices query
        $query = Invoice::whereHas('customerProduct', function($query) use ($customer) {
            $query->where('c_id', $customer->c_id);
        })
        ->with(['customerProduct.product', 'payments'])
        ->orderBy('issue_date', 'desc');
        
        // Apply filters if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('month') && $request->month !== 'all') {
            $query->whereMonth('issue_date', $request->month);
        }
        
        if ($request->has('year') && $request->year !== 'all') {
            $query->whereYear('issue_date', $request->year);
        }
        
        // Get paginated results
        $invoices = $query->paginate(15);
        
        // Calculate totals
        $totalInvoices = $invoices->total();
        $totalAmount = $query->sum('total_amount');
        $totalPaid = $query->sum('received_amount');
        $totalDue = $totalAmount - $totalPaid;
        
        // Get status counts for stats
        $statusCounts = Invoice::whereHas('customerProduct', function($query) use ($customer) {
            $query->where('c_id', $customer->c_id);
        })
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();
        
        // Get months and years for filters
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        $years = Invoice::whereHas('customerProduct', function($query) use ($customer) {
            $query->where('c_id', $customer->c_id);
        })
        ->select(DB::raw('YEAR(issue_date) as year'))
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year')
        ->toArray();
        
        return view('customer.invoices.index', compact(
            'customer',
            'invoices',
            'totalInvoices',
            'totalAmount',
            'totalPaid',
            'totalDue',
            'statusCounts',
            'months',
            'years'
        ));
    }

    /**
     * Display the specified invoice.
     */
    public function show($id)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        $invoice = Invoice::where('invoice_id', $id)
            ->whereHas('customerProduct', function($query) use ($customer) {
                $query->where('c_id', $customer->c_id);
            })
            ->with([
                'customerProduct.product',
                'payments' => function($query) {
                    $query->orderBy('payment_date', 'desc');
                },
                'customerProduct.customer'
            ])
            ->firstOrFail();
        
        return view('customer.invoices.show', compact('customer', 'invoice'));
    }

    /**
     * Download invoice as PDF.
     */
    public function download($id)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        $invoice = Invoice::where('invoice_id', $id)
            ->whereHas('customerProduct', function($query) use ($customer) {
                $query->where('c_id', $customer->c_id);
            })
            ->with([
                'customerProduct.product',
                'customerProduct.customer'
            ])
            ->firstOrFail();
        
        $pdf = PDF::loadView('customer.invoices.pdf', compact('invoice'));
        
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Print invoice.
     */
    public function print($id)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        $invoice = Invoice::where('invoice_id', $id)
            ->whereHas('customerProduct', function($query) use ($customer) {
                $query->where('c_id', $customer->c_id);
            })
            ->with([
                'customerProduct.product',
                'customerProduct.customer'
            ])
            ->firstOrFail();
        
        return view('customer.invoices.print', compact('invoice'));
    }
}