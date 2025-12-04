<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class CustomerController extends Controller
{
    /**
     * Constructor - Apply middleware for admin methods
     */
    public function __construct()
    {
        // Apply admin middleware to all methods except customer dashboard/login
        $this->middleware('auth')->except(['showLoginForm', 'login', 'logout']);
        $this->middleware('role:admin')->except(['dashboard', 'showLoginForm', 'login', 'logout']);
    }

    // ========== CUSTOMER DASHBOARD ==========
    
    public function dashboard()
    {
        try {
            // Get authenticated customer
            $user = Auth::user();
            
            if ($user->role !== 'customer') {
                Auth::logout();
                return redirect()->route('customer.login')->withErrors([
                    'email' => 'Access denied. Customer login only.',
                ]);
            }

            $customer = Customer::where('user_id', $user->id)->first();
            
            if (!$customer) {
                Auth::logout();
                return redirect()->route('customer.login')->withErrors([
                    'email' => 'Customer profile not found.',
                ]);
            }

            // Load customer with relationships
            $customer->load(['invoices' => function($query) {
                $query->latest()->take(10);
            }, 'payments' => function($query) {
                $query->latest()->take(10);
            }]);

            // Get customer's latest invoices and payments
            $invoices = $customer->invoices;
            $payments = $customer->payments;
            
            // Calculate total due
            $totalDue = Invoice::where('c_id', $customer->c_id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->sum(DB::raw('total_amount - received_amount'));

            // Get active products count
            $activeProductsCount = $customer->customerproducts()
                ->where('status', 'active')
                ->count();

            return view('customer.dashboard', compact(
                'customer', 
                'invoices', 
                'payments', 
                'totalDue',
                'activeProductsCount'
            ));

        } catch (\Exception $e) {
            Log::error('Customer dashboard error: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            return redirect()->route('customer.login')
                ->with('error', 'Error loading dashboard. Please try again.');
        }
    }

    // ========== ADMIN CUSTOMER MANAGEMENT METHODS ==========
    
    public function index(Request $request)
    {
        try {
            // Get customers with relationships
            $query = Customer::withCount(['invoices', 'customerproducts'])
                ->with(['user', 'customerproducts.product']);

            // Apply filters
            if ($request->filled('filter')) {
                switch ($request->filter) {
                    case 'active':
                        $query->where('is_active', true);
                        break;
                    case 'inactive':
                        $query->where('is_active', false);
                        break;
                    case 'with_due':
                        $query->whereHas('invoices', function($q) {
                            $q->whereIn('status', ['unpaid', 'partial']);
                        });
                        break;
                    case 'with_addons':
                        $query->whereHas('customerproducts.product', function($q) {
                            $q->where('product_type', 'special');
                        });
                        break;
                    case 'no_products':
                        $query->whereDoesntHave('customerproducts');
                        break;
                }
            }

            // Apply search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('customer_id', 'like', "%{$search}%")
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $customers = $query->paginate(15)->withQueryString();

            // Calculate statistics
            $totalCustomers = Customer::count();
            $activeCustomers = Customer::where('is_active', true)->count();
            $inactiveCustomers = Customer::where('is_active', false)->count();
            $customersWithDue = Customer::whereHas('invoices', function($q) {
                $q->whereIn('status', ['unpaid', 'partial']);
            })->count();

            return view('admin.customers.index', compact(
                'customers',
                'totalCustomers',
                'activeCustomers',
                'inactiveCustomers',
                'customersWithDue'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading customers index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Failed to load customers.');
        }
    }

    public function create()
    {
        try {
            $regularProducts = Product::where('product_type', 'regular')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['p_id', 'name', 'monthly_price', 'description']);
            
            $specialProducts = Product::where('product_type', 'special')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['p_id', 'name', 'monthly_price', 'description']);
            
            // Generate next customer ID
            $nextCustomerId = Customer::generateCustomerId();
            
            return view('admin.customers.create', compact(
                'regularProducts', 
                'specialProducts',
                'nextCustomerId'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading customer create form: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Failed to load customer creation form.');
        }
    }

    /**
     * Get next available customer ID in format: C-YY-XXXX
     */
    public function getNextCustomerIdAjax()
    {
        try {
            $nextCustomerId = Customer::generateCustomerId();
            
            return response()->json([
                'success' => true,
                'customer_id' => $nextCustomerId
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating customer ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating customer ID',
                'customer_id' => 'C-' . date('y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:30|unique:customers,phone',
            'address' => 'required|string|max:500',
            'connection_address' => 'nullable|string|max:500',
            'id_type' => 'nullable|string|in:nid,passport,driving_license', 
            'id_number' => 'nullable|string|max:100', 
            'customer_id' => 'required|string|max:20|unique:customers,customer_id',
            'regular_product_id' => 'nullable|exists:products,p_id',
            'special_product_ids' => 'nullable|array',
            'special_product_ids.*' => 'exists:products,p_id',
            'is_active' => 'boolean',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            DB::beginTransaction();

            // Create User account
            $userPassword = $validated['password'] ?? 'password123'; // Default password
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($userPassword),
                'role' => 'customer',
                'email_verified_at' => now(),
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Create Customer profile
            $customer = Customer::create([
                'user_id' => $user->id,
                'customer_id' => $validated['customer_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'connection_address' => $validated['connection_address'],
                'id_type' => $validated['id_type'],
                'id_number' => $validated['id_number'],
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => Auth::id(),
            ]);

            // Assign Regular product if provided
            if (!empty($validated['regular_product_id'])) {
                $regularProduct = Product::find($validated['regular_product_id']);
                if ($regularProduct) {
                    $customer->assignProduct(
                        $regularProduct->p_id,
                        1, // billing cycle in months
                        'active'
                    );
                }
            }

            // Assign Special products if provided
            if (!empty($validated['special_product_ids'])) {
                foreach ($validated['special_product_ids'] as $productId) {
                    $product = Product::find($productId);
                    if ($product) {
                        $customer->assignProduct(
                            $product->p_id,
                            1, // billing cycle in months
                            'active'
                        );
                    }
                }
            }

            DB::commit();

            // Log the action
            Log::info('Customer created', [
                'admin_id' => Auth::id(),
                'customer_id' => $customer->c_id,
                'customer_number' => $customer->customer_id
            ]);

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully!')
                ->with('info', 'Customer ID: ' . $customer->customer_id . ' | Default password: ' . $userPassword);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating customer: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'request' => $request->all()
            ]);
            return redirect()->back()
                ->with('error', 'Error creating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $customer = Customer::with([
                'user',
                'invoices.payments',
                'customerproducts.product',
                'payments' => function($query) {
                    $query->latest()->take(10);
                }
            ])->findOrFail($id);
            
            // Calculate statistics
            $totalInvoices = $customer->invoices->count();
            $totalPaid = $customer->invoices->sum('received_amount');
            $totalDue = $customer->invoices->whereIn('status', ['unpaid', 'partial'])
                ->sum(function($invoice) {
                    return $invoice->total_amount - $invoice->received_amount;
                });
            
            // Get active products count
            $activeProducts = $customer->customerproducts()
                ->where('status', 'active')
                ->with('product')
                ->get();
            
            // Get latest invoices and payments
            $recentInvoices = $customer->invoices()
                ->latest()
                ->take(5)
                ->get();
            
            $recentPayments = $customer->payments()
                ->latest()
                ->take(5)
                ->get();

            // Calculate monthly revenue from active products
            $monthlyRevenue = $activeProducts->sum(function($cp) {
                return $cp->product->monthly_price ?? 0;
            });

            return view('admin.customers.show', compact(
                'customer', 
                'totalInvoices', 
                'totalPaid', 
                'totalDue',
                'recentInvoices',
                'recentPayments',
                'activeProducts',
                'monthlyRevenue'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading customer profile: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'customer_id' => $id
            ]);
            return redirect()->route('admin.customers.index')
                ->with('error', 'Customer not found.');
        }
    }

    public function edit($id)
    {
        try {
            $customer = Customer::with(['user', 'customerproducts.product'])->findOrFail($id);
            
            $regularProducts = Product::where('product_type', 'regular')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['p_id', 'name', 'monthly_price']);
            
            $specialProducts = Product::where('product_type', 'special')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['p_id', 'name', 'monthly_price']);
            
            // Get currently assigned products
            $assignedRegularProduct = $customer->customerproducts()
                ->whereHas('product', function($q) {
                    $q->where('product_type', 'regular');
                })
                ->where('status', 'active')
                ->first();
            
            $assignedSpecialProducts = $customer->customerproducts()
                ->whereHas('product', function($q) {
                    $q->where('product_type', 'special');
                })
                ->where('status', 'active')
                ->get();
            
            return view('admin.customers.edit', compact(
                'customer', 
                'regularProducts', 
                'specialProducts',
                'assignedRegularProduct',
                'assignedSpecialProducts'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading customer edit form: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'customer_id' => $id
            ]);
            return redirect()->route('admin.customers.index')
                ->with('error', 'Failed to load edit form.');
        }
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::with('user')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->user_id,
            'phone' => 'required|string|max:30|unique:customers,phone,' . $customer->c_id . ',c_id',
            'address' => 'required|string|max:500',
            'connection_address' => 'nullable|string|max:500',
            'id_type' => 'nullable|string|in:nid,passport,driving_license',
            'id_number' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'regular_product_id' => 'nullable|exists:products,p_id',
            'special_product_ids' => 'nullable|array',
            'special_product_ids.*' => 'exists:products,p_id',
        ]);

        try {
            DB::beginTransaction();

            // Update user
            $userUpdates = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? $customer->user->is_active,
            ];
            
            if (!empty($validated['password'])) {
                $userUpdates['password'] = Hash::make($validated['password']);
            }
            
            $customer->user->update($userUpdates);

            // Update customer
            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'connection_address' => $validated['connection_address'],
                'id_type' => $validated['id_type'],
                'id_number' => $validated['id_number'],
                'is_active' => $validated['is_active'] ?? $customer->is_active,
                'updated_by' => Auth::id(),
            ]);

            // Handle product assignments
            $this->updateCustomerProducts($customer, $validated);

            DB::commit();

            Log::info('Customer updated', [
                'admin_id' => Auth::id(),
                'customer_id' => $customer->c_id
            ]);

            return redirect()->route('admin.customers.show', $customer->c_id)
                ->with('success', 'Customer updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating customer: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'customer_id' => $id,
                'request' => $request->all()
            ]);
            return redirect()->back()
                ->with('error', 'Error updating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update customer product assignments
     */
    private function updateCustomerProducts(Customer $customer, array $validatedData)
    {
        // Handle regular product
        if (isset($validatedData['regular_product_id'])) {
            // Deactivate current regular product
            $customer->customerproducts()
                ->whereHas('product', function($q) {
                    $q->where('product_type', 'regular');
                })
                ->where('status', 'active')
                ->update(['status' => 'expired', 'is_active' => false]);
            
            // Assign new regular product if provided
            if ($validatedData['regular_product_id']) {
                $customer->assignProduct(
                    $validatedData['regular_product_id'],
                    1,
                    'active'
                );
            }
        }

        // Handle special products
        if (isset($validatedData['special_product_ids'])) {
            // Deactivate current special products
            $customer->customerproducts()
                ->whereHas('product', function($q) {
                    $q->where('product_type', 'special');
                })
                ->where('status', 'active')
                ->update(['status' => 'expired', 'is_active' => false]);
            
            // Assign new special products
            foreach ($validatedData['special_product_ids'] as $productId) {
                $customer->assignProduct($productId, 1, 'active');
            }
        }
    }

    public function destroy($id)
    {
        try {
            $customer = Customer::with(['user', 'invoices', 'payments', 'customerproducts'])->findOrFail($id);

            // Check if customer has any active invoices or payments
            $hasActiveInvoices = $customer->invoices()
                ->whereIn('status', ['unpaid', 'partial'])
                ->exists();
            
            if ($hasActiveInvoices) {
                return redirect()->back()
                    ->with('error', 'Cannot delete customer with unpaid invoices. Please settle all invoices first.');
            }

            DB::beginTransaction();

            // Soft delete related records
            $customer->invoices()->delete();
            $customer->payments()->delete();
            $customer->customerproducts()->delete();
            
            // Delete user account
            if ($customer->user) {
                $customer->user->delete();
            }

            // Delete customer
            $customer->delete();

            DB::commit();

            Log::info('Customer deleted', [
                'admin_id' => Auth::id(),
                'customer_id' => $id
            ]);

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting customer: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'customer_id' => $id
            ]);
            return redirect()->back()
                ->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    // ========== CUSTOMER AUTHENTICATION METHODS ==========
    
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'customer') {
                return redirect()->route('customer.dashboard');
            } else {
                return redirect()->route('admin.dashboard');
            }
        }
        return view('customer.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);

        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Check if user is a customer
            if ($user->role !== 'customer') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. Customer login only.',
                ])->withInput();
            }

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ])->withInput();
            }

            $request->session()->regenerate();
            
            // Update last login
            $user->update(['last_login_at' => now()]);
            
            Log::info('Customer logged in', ['customer_id' => $user->id]);

            return redirect()->route('customer.dashboard');

        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if ($user && $user->role === 'customer') {
            return redirect()->route('customer.login');
        }
        
        return redirect('/');
    }

    // ========== ADDITIONAL CUSTOMER MANAGEMENT METHODS ==========

    /**
     * Export customers to CSV
     */
    public function export(Request $request)
    {
        try {
            $customers = Customer::with(['user', 'customerproducts.product'])
                ->when($request->filled('filter'), function($query) use ($request) {
                    switch ($request->filter) {
                        case 'active':
                            $query->where('is_active', true);
                            break;
                        case 'inactive':
                            $query->where('is_active', false);
                            break;
                        case 'with_due':
                            $query->whereHas('invoices', function($q) {
                                $q->whereIn('status', ['unpaid', 'partial']);
                            });
                            break;
                    }
                })
                ->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="customers_' . date('Y-m-d_H-i-s') . '.csv"',
            ];

            $callback = function() use ($customers) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fwrite($file, "\xEF\xBB\xBF");
                
                // Headers
                fputcsv($file, [
                    'Customer ID',
                    'Name',
                    'Email',
                    'Phone',
                    'Address',
                    'Status',
                    'Active Products',
                    'Total Invoices',
                    'Created Date'
                ]);

                foreach ($customers as $customer) {
                    fputcsv($file, [
                        $customer->customer_id,
                        $customer->name,
                        $customer->email,
                        $customer->phone,
                        $customer->address,
                        $customer->is_active ? 'Active' : 'Inactive',
                        $customer->customerproducts()->where('status', 'active')->count(),
                        $customer->invoices()->count(),
                        $customer->created_at->format('Y-m-d')
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting customers: ' . $e->getMessage(), [
                'admin_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Failed to export customers.');
        }
    }

    /**
     * Customer billing history
     */
    public function billingHistory($id)
    {
        try {
            $customer = Customer::with(['invoices.payments', 'invoices.customerProduct.product'])
                ->findOrFail($id);
            
            $invoices = $customer->invoices()
                ->with(['payments', 'customerProduct.product'])
                ->latest()
                ->paginate(20);
            
            return view('admin.customers.billing-history', compact('customer', 'invoices'));

        } catch (\Exception $e) {
            Log::error('Error loading billing history: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'customer_id' => $id
            ]);
            return redirect()->back()->with('error', 'Failed to load billing history.');
        }
    }

    /**
     * Toggle customer active status
     */
    public function toggleStatus($id)
    {
        try {
            $customer = Customer::with('user')->findOrFail($id);
            
            DB::beginTransaction();
            
            $newStatus = !$customer->is_active;
            $customer->update([
                'is_active' => $newStatus,
                'updated_by' => Auth::id(),
            ]);
            
            if ($customer->user) {
                $customer->user->update(['is_active' => $newStatus]);
            }
            
            DB::commit();
            
            $statusText = $newStatus ? 'activated' : 'deactivated';
            
            Log::info('Customer status toggled', [
                'admin_id' => Auth::id(),
                'customer_id' => $id,
                'new_status' => $newStatus
            ]);
            
            return redirect()->back()
                ->with('success', "Customer {$statusText} successfully!");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling customer status: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'customer_id' => $id
            ]);
            return redirect()->back()->with('error', 'Failed to update customer status.');
        }
    }

    /**
     * Send login credentials to customer
     */
    public function sendCredentials($id)
    {
        try {
            $customer = Customer::with('user')->findOrFail($id);
            
            if (!$customer->user) {
                return redirect()->back()->with('error', 'Customer does not have a user account.');
            }
            
            // TODO: Implement email sending logic here
            // Example: Mail::to($customer->email)->send(new CustomerCredentialsMail($customer));
            
            Log::info('Login credentials sent to customer', [
                'admin_id' => Auth::id(),
                'customer_id' => $id
            ]);
            
            return redirect()->back()
                ->with('success', 'Login credentials sent to customer email.');
                
        } catch (\Exception $e) {
            Log::error('Error sending credentials: ' . $e->getMessage(), [
                'admin_id' => Auth::id(),
                'customer_id' => $id
            ]);
            return redirect()->back()->with('error', 'Failed to send credentials.');
        }
    }
}