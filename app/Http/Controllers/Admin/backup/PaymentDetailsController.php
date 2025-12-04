<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\CustomerToProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentDetailsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $productId = $request->get('product_id', 'all');
        $month = $request->get('month', '');

        // Initialize customers collection
        $customers = collect();

        // Only fetch customers when there's a search term
        if ($search) {
            // Base query for customers
            $customersQuery = Customer::query();

            // Apply search filter (customer info + invoice number)
            $customersQuery->where(function ($query) use ($search) {

                // Search in customer fields
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('customer_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                // Search invoice numbers
                $query->orWhereHas('customerProducts.invoices', function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%");
                });
            });

            // Get customers with their products
            $customers = $customersQuery->with(['customerProducts.product'])
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();

        } else {
            // Create an empty paginator when no search
            $customers = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        // Get ALL products for dropdown (when no search or for "all" option)
        $allProducts = Product::orderBy('name')->get();

        // Get customer-specific products (only if we have a search)
        $customerProducts = collect();
        if ($search && $customers->count() > 0) {

            $customerProductIds = collect();

            foreach ($customers as $customer) {
                foreach ($customer->customerProducts as $cp) {
                    $customerProductIds->push($cp->p_id);
                }
            }

            $customerProductIds = $customerProductIds->unique();

            if ($customerProductIds->count() > 0) {
                $customerProducts = Product::whereIn('p_id', $customerProductIds)
                    ->orderBy('name')
                    ->get();
            }
        }

        // Get payment history for each customer
        if ($search && $customers->count() > 0) {

            foreach ($customers as $customer) {

                $paymentQuery = Invoice::whereHas('customerProduct', function ($query) use ($customer, $productId) {
                    $query->where('c_id', $customer->c_id);

                    if ($productId !== 'all') {
                        $query->where('p_id', $productId);
                    }
                });

                // Filter by month if specified
                if ($month) {
                    $paymentQuery->where('issue_date', 'like', "{$month}%");
                }

                $customer->paymentHistory = $paymentQuery
                    ->with(['customerProduct.product'])
                    ->orderBy('issue_date', 'desc')
                    ->get();

                // Summary calculations
                $customer->totalBilled = $customer->paymentHistory->sum('total_amount');
                $customer->totalPaid = $customer->paymentHistory->sum('received_amount');
                $customer->totalDue = $customer->totalBilled - $customer->totalPaid;
                $customer->totalInvoices = $customer->paymentHistory->count();
            }
        }

        // Get unique months for filter
        $months = Invoice::select(DB::raw('DATE_FORMAT(issue_date, "%Y-%m") as month'))
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month');

        return view('admin.payment-details.index', compact(
            'customers',
            'allProducts',
            'customerProducts',
            'months',
            'search',
            'productId',
            'month'
        ));
    }
}
