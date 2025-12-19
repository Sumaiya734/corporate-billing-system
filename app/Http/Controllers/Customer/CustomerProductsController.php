<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerProductsController extends Controller
{
    public function index()
    {
        // Get authenticated customer
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        // Get all customer products with product details
        $customerProducts = CustomerProduct::where('c_id', $customer->c_id)
            ->with(['product' => function($query) {
                $query->select('p_id', 'name', 'description', 'monthly_price');
            }])
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Calculate stats
        $activeCount = CustomerProduct::where('c_id', $customer->c_id)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->count();
        
        $totalMonthly = CustomerProduct::where('c_id', $customer->c_id)
            ->where('is_active', 1)
            ->with('product')
            ->get()
            ->sum(function($cp) {
                return $cp->product->monthly_price ?? 0;
            });
        
        return view('customer.products.index', compact('customer', 'customerProducts', 'activeCount', 'totalMonthly'));
    }

    public function show($id)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        $customerProduct = CustomerProduct::where('cp_id', $id)
            ->where('c_id', $customer->c_id)
            ->with(['product', 'invoices' => function($query) {
                $query->orderBy('issue_date', 'desc');
            }])
            ->firstOrFail();
        
        return view('customer.products.show', compact('customer', 'customerProduct'));
    }
}