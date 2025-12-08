<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::query()->with(['customerproducts.product', 'invoices']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->search($search);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        $customers = $query->latest()->paginate(20);

        // Calculate statistics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $inactiveCustomers = Customer::where('is_active', false)->count();
        $customersWithProducts = Customer::whereHas('customerproducts', function($q) {
            $q->where('customer_to_products.status', 'active')->where('customer_to_products.is_active', true);
        })->count();
        $customersWithDue = Customer::whereHas('invoices', function($q) {
            $q->whereIn('invoices.status', ['unpaid', 'partial'])->where('invoices.next_due', '>', 0);
        })->count();
        $newCustomersCount = Customer::where('created_at', '>=', now()->subDays(7))->count();

        return view('admin.customers.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'inactiveCustomers',
            'customersWithProducts',
            'customersWithDue',
            'newCustomersCount'
        ));
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'connection_address' => 'nullable|string',
            'id_type' => 'nullable|string|in:NID,Passport,Driving License',
            'id_number' => 'nullable|string|max:100',
            'customer_id' => 'nullable|string|max:50|unique:customers,customer_id',
            'is_active' => 'nullable|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'id_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'id_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);
        try {
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $validated['profile_picture'] = $request->file('profile_picture')->store('customers/profiles', 'public');
            }

            // Handle ID card front upload
            if ($request->hasFile('id_card_front')) {
                $validated['id_card_front'] = $request->file('id_card_front')->store('customers/id_cards', 'public');
            }

            // Handle ID card back upload
            if ($request->hasFile('id_card_back')) {
                $validated['id_card_back'] = $request->file('id_card_back')->store('customers/id_cards', 'public');
            }

            $validated['is_active'] = $request->has('is_active') ? 1 : 0;

            $customer = Customer::create($validated);

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully!');

        } catch (\Exception $e) {
            Log::error('Customer creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified customer
     */
    public function show($id)
    {
        $customer = Customer::with(['customerproducts.product', 'invoices.payments'])
            ->findOrFail($id);
        
        // Calculate statistics
        $totalInvoices = $customer->invoices->count();
        $totalPaid = $customer->invoices->sum('received_amount');
        $totalDue = $customer->invoices->sum(function($invoice) {
            return $invoice->total_amount - ($invoice->received_amount ?? 0);
        });
        
        // In your controller
        $newCustomersCount = Customer::where('created_at', '>=', now()->subDays(7))->count();

        // Get recent invoices (limit to 5 most recent)
        $recentInvoices = $customer->invoices()->latest()->take(5)->get();
        
        // Get recent payments through invoices
        $recentPayments = $customer->invoices()->with('payments')->get()
            ->pluck('payments')->flatten()->sortByDesc('payment_date')->take(5);
        
        return view('admin.customers.show', compact('customer', 'totalInvoices', 'totalPaid', 'totalDue', 'recentInvoices', 'recentPayments', 'newCustomersCount'));
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $id . ',c_id',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'connection_address' => 'nullable|string',
            'id_type' => 'nullable|string|in:NID,Passport,Driving License',
            'id_number' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'id_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'id_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'remove_profile_picture' => 'nullable|boolean',
            'remove_id_cards' => 'nullable|boolean',
        ]);
        try {
            // Handle profile picture removal
            if ($request->has('remove_profile_picture') && $customer->profile_picture) {
                Storage::disk('public')->delete($customer->profile_picture);
                $validated['profile_picture'] = null;
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture
                if ($customer->profile_picture) {
                    Storage::disk('public')->delete($customer->profile_picture);
                }
                $validated['profile_picture'] = $request->file('profile_picture')->store('customers/profiles', 'public');
            }

            // Handle ID cards removal
            if ($request->has('remove_id_cards')) {
                if ($customer->id_card_front) {
                    Storage::disk('public')->delete($customer->id_card_front);
                }
                if ($customer->id_card_back) {
                    Storage::disk('public')->delete($customer->id_card_back);
                }
                $validated['id_card_front'] = null;
                $validated['id_card_back'] = null;
            }

            // Handle ID card front upload
            if ($request->hasFile('id_card_front')) {
                // Delete old ID card front
                if ($customer->id_card_front) {
                    Storage::disk('public')->delete($customer->id_card_front);
                }
                $validated['id_card_front'] = $request->file('id_card_front')->store('customers/id_cards', 'public');
            }

            // Handle ID card back upload
            if ($request->hasFile('id_card_back')) {
                // Delete old ID card back
                if ($customer->id_card_back) {
                    Storage::disk('public')->delete($customer->id_card_back);
                }
                $validated['id_card_back'] = $request->file('id_card_back')->store('customers/id_cards', 'public');
            }

            $validated['is_active'] = $request->has('is_active') ? 1 : 0;

            // Remove checkbox fields from update data
            unset($validated['remove_profile_picture'], $validated['remove_id_cards']);

            $customer->update($validated);

            return redirect()->route('admin.customers.edit', $customer->c_id)
                ->with('success', 'Customer updated successfully!');

        } catch (\Exception $e) {
            Log::error('Customer update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // Delete associated images
            if ($customer->profile_picture) {
                Storage::disk('public')->delete($customer->profile_picture);
            }
            if ($customer->id_card_front) {
                Storage::disk('public')->delete($customer->id_card_front);
            }
            if ($customer->id_card_back) {
                Storage::disk('public')->delete($customer->id_card_back);
            }

            $customer->delete();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Customer deletion error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    /**
     * Toggle customer status
     */
    public function toggleStatus($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->toggleActivation();

            return redirect()->back()
                ->with('success', 'Customer status updated successfully!');

        } catch (\Exception $e) {
            Log::error('Customer status toggle error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating customer status: ' . $e->getMessage());
        }
    }

    /**
     * Get next customer ID
     */
    public function getNextCustomerId()
    {
        return response()->json([
            'customer_id' => Customer::generateCustomerId()
        ]);
    }

    /**
     * Export customers
     */
    public function export()
    {
        // TODO: Implement export functionality
        return redirect()->back()->with('info', 'Export functionality coming soon!');
    }

    /**
     * Customer billing history
     */
    public function billingHistory($id)
    {
        $customer = Customer::with(['invoices'])->findOrFail($id);
        return view('admin.customers.billing-history', compact('customer'));
    }

    /**
     * Customer profile
     */
    public function profile($id)
    {
        $customer = Customer::with(['customerproducts.product', 'invoices.payments'])
            ->findOrFail($id);
        
        // Calculate statistics
        $totalInvoices = $customer->invoices->count();
        $totalPaid = $customer->invoices->sum('received_amount');
        $totalDue = $customer->invoices->sum(function($invoice) {
            return $invoice->total_amount - ($invoice->received_amount ?? 0);
        });
        
        // Get recent invoices (limit to 5 most recent)
        $recentInvoices = $customer->invoices()->latest()->take(5)->get();
        
        // Get recent payments through invoices
        $recentPayments = $customer->invoices()->with('payments')->get()
            ->pluck('payments')->flatten()->sortByDesc('payment_date')->take(5);
        
        return view('admin.customers.profile', compact('customer', 'totalInvoices', 'totalPaid', 'totalDue', 'recentInvoices', 'recentPayments'));
    }
}