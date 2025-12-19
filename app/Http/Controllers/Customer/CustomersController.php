<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer as CustomerModel;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class CustomersController extends Controller
{
    // ========== CUSTOMER DASHBOARD ==========
    
    public function dashboard()
    {
        // Get authenticated customer
        $user = Auth::user();
        $customer = CustomerModel::where('user_id', $user->id)->first();
        
        if (!$customer) {
            Auth::logout();
            return redirect()->route('customer.login')->withErrors([
                'email' => 'Customer profile not found.',
            ]);
        }

        // Get customer's latest invoices and payments - FIXED TO USE CORRECT RELATIONSHIP
        $invoices = $customer->invoices()->latest()->take(5)->get();
        $payments = $customer->payments()->latest()->take(5)->get();
        $totalDue = $customer->unpaidInvoices()->get()->sum(function($invoice) {
            return $invoice->total_amount - $invoice->received_amount;
        });

        return view('customer.dashboard', compact('customer', 'invoices', 'payments', 'totalDue'));
    }
    
    // ========== CUSTOMER PROFILE ==========
    
    public function profile()
    {
        // Get authenticated customer with relationships
        $user = Auth::user();
        $customer = CustomerModel::with(['user', 'customerproducts.product'])->where('user_id', $user->id)->first();
        
        if (!$customer) {
            Auth::logout();
            return redirect()->route('customer.login')->withErrors([
                'email' => 'Customer profile not found.',
            ]);
        }

        return view('customer.my-profile', compact('customer'));
    }

    // ========== ADMIN CUSTOMER MANAGEMENT METHODS ==========
    
    public function index(Request $request)
    {
        // Get customers with products relationship
        $query = CustomerModel::with(['user', 'invoices', 'customerproducts.product.type']);

        // Apply filters
        switch ($request->get('filter')) {
            case 'active':
                $query->where('is_active', true);
                break;
            case 'inactive':
                $query->where('is_active', false);
                break;
            case 'with_due':
                $query->whereHas('invoices', function($q) {
                    $q->whereIn('invoices.status', ['unpaid', 'partial']);
                });
                break;
            case 'with_addons':
                // Filter for customers with special products
                $query->whereHas('customerproducts.product', function($q) {
                    $q->where('product_type', 'special');
                });
                break;
        }

        // Apply search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_id', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10);

        // Calculate statistics
        $totalCustomers = CustomerModel::count();
        $activeCustomers = CustomerModel::where('is_active', true)->count();
        $inactiveCustomers = CustomerModel::where('is_active', false)->count();
        $customersWithDue = CustomerModel::whereHas('invoices', function($q) {
            $q->whereIn('invoices.status', ['unpaid', 'partial']);
        })->count();

        return view('admin.customers.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'inactiveCustomers',
            'customersWithDue'
        ));
    }

    public function create()
    {
        $regularproducts = product::whereHas('type', function($query) {
            $query->where('name', 'regular');
        })->get();
        
        $specialproducts = product::whereHas('type', function($query) {
            $query->where('name', 'special');
        })->get();
        
        return view('admin.customers.create', compact('regularproducts', 'specialproducts'));
    }

    /**
     * Get next available customer ID in format: C-YY-XXXX
     */
    public function getNextCustomerId()
    {
        try {
            $currentYear = date('y'); // Last 2 digits of year
            
            // Get the last customer ID for this year
            $lastCustomer = CustomerModel::where('customer_id', 'like', "C-{$currentYear}-%")
                ->orderBy('customer_id', 'desc')
                ->first();
            
            if ($lastCustomer && preg_match('/C-\d{2}-(\d{4})/', $lastCustomer->customer_id, $matches)) {
                $lastNumber = intval($matches[1]);
                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                // First customer of the year
                $nextNumber = '0001';
            }
            
            return response()->json([
                'success' => true,
                'next_number' => $nextNumber,
                'year' => $currentYear
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating customer ID',
                'next_number' => str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'phone' => 'required|string|max:30',
        'address' => 'required|string|max:500',
        'connection_address' => 'nullable|string|max:500',
        'id_type' => 'nullable|string|in:NID,Passport,Driving License', 
        'id_number' => 'nullable|string|max:100', 
        'regular_product_id' => 'nullable|exists:products,p_id',
        'special_product_ids' => 'nullable|array',
        'special_product_ids.*' => 'exists:products,p_id',
        'is_active' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Create User account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password'), // Default password
                'role' => 'customer',
                'email_verified_at' => now(),
            ]);

            // Create Customer profile
            $customer = CustomerModel::create([
                'user_id' => $user->id,
                'customer_id' => CustomerModel::generateCustomerId(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'connection_address' => $request->connection_address,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            // Assign Regular product ONLY if provided
        if ($request->filled('regular_product_id')) {
            $regularPkg = product::find($request->regular_product_id);
            if ($regularPkg) {
                $customer->assignproduct(
                    $regularPkg->p_id,
                    1, // billingCycleMonths
                    'active'
                );
            }
        }

           // Assign Special products
        if ($request->filled('special_product_ids')) {
            foreach ($request->special_product_ids as $pkgId) {
                $pkg = product::find($pkgId);
                if ($pkg) {
                    $customer->assignproduct(
                        $pkg->p_id,
                        1, // billingCycleMonths
                        'active'
                    );
                }
            }
        }

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully! Customer ID: ' . $customer->customer_id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $customer = CustomerModel::with(['user', 'invoices.payments', 'customerproducts.product'])->findOrFail($id);
        
        // Calculate statistics
        $totalInvoices = $customer->invoices->count();
        $totalPaid = $customer->invoices->where('status', 'paid')->sum('total_amount');
        $totalDue = $customer->invoices->whereIn('status', ['unpaid', 'partial'])
            ->sum(function($invoice) {
                return $invoice->total_amount - $invoice->received_amount;
            });
        
        // Get latest invoices and payments through invoices
        $recentInvoices = $customer->invoices()->latest()->take(5)->get();
        $recentPayments = Payment::whereIn('invoice_id', $customer->invoices->pluck('invoice_id'))
            ->latest()
            ->take(5)
            ->get();

        return view('admin.customers.profile', compact(
            'customer', 
            'totalInvoices', 
            'totalPaid', 
            'totalDue',
            'recentInvoices',
            'recentPayments'
        ));
    }

    public function edit($id)
    {
        $customer = CustomerModel::with(['user', 'customerproducts.product'])->findOrFail($id);
        
        $regularproducts = product::whereHas('type', function($query) {
            $query->where('name', 'regular');
        })->get();
        
        $specialproducts = product::whereHas('type', function($query) {
            $query->where('name', 'special');
        })->get();
        
        return view('admin.customers.edit', compact('customer', 'regularproducts', 'specialproducts'));
    }

    public function update(Request $request, $id)
    {
        $customer = CustomerModel::with('user')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->user_id,
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'connection_address' => 'nullable|string|max:500',
            'id_type' => 'nullable|string|in:NID,Passport,Driving License',
            'id_number' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Update user
            $customer->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update customer
            $customer->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'connection_address' => $request->connection_address,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'is_active' => $request->has('is_active') ? $request->is_active : $customer->is_active,
            ]);

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $customer = CustomerModel::findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete related records first
            $customer->payments()->delete();
            $customer->invoices()->delete();
            $customer->customerproducts()->delete();
            
            // Delete user if exists
            if ($customer->user) {
                $customer->user->delete();
            }

            // Delete customer
            $customer->delete();

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    // ========== CUSTOMER AUTHENTICATION METHODS ==========
    
    public function showRegistrationForm()
    {
        return view('customer.register');
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // Create User account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]);

            // Create Customer profile
            $customer = CustomerModel::create([
                'user_id' => $user->id,
                'customer_id' => CustomerModel::generateCustomerId(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => true, // New customers are active by default
            ]);

            DB::commit();

            // Log the user in automatically after registration
            Auth::login($user);

            return redirect()->route('customer.dashboard')
                ->with('success', 'Account created successfully! Welcome to NetBill BD.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating account: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->role === 'customer') {
            return redirect()->route('customer.dashboard');
        }
        return view('customer.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Add debugging
        \Illuminate\Support\Facades\Log::info('Login attempt for email: ' . $credentials['email']);
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            \Illuminate\Support\Facades\Log::info('Authentication successful for user ID: ' . $user->id . ' with role: ' . ($user->role ?? 'NULL'));
            
            if ($user->role === 'customer') {
                $request->session()->regenerate();
                \Illuminate\Support\Facades\Log::info('Redirecting customer to dashboard');
                return redirect()->route('customer.dashboard');
            } else {
                Auth::logout();
                \Illuminate\Support\Facades\Log::info('Non-customer user attempted to login to customer area');
                return back()->withErrors([
                    'email' => 'Access denied. Customer login only.',
                ])->withInput();
            }
        }

        \Illuminate\Support\Facades\Log::info('Authentication failed for email: ' . $credentials['email']);
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
    // In your CustomersController.php
public function updateProfile(Request $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $customer = CustomerModel::where('user_id', $user->id)->firstOrFail();

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $customer->user_id,
        'phone' => 'required|string|max:30',
        'address' => 'required|string|max:500',
        'connection_address' => 'nullable|string|max:500',
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
        'id_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
        'id_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
    ]);

    try {
        DB::beginTransaction();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($customer->profile_picture && Storage::disk('public')->exists($customer->profile_picture)) {
                Storage::disk('public')->delete($customer->profile_picture);
            }

            $profilePicturePath = $request->file('profile_picture')->store('customers/profiles', 'public');
            $customer->profile_picture = $profilePicturePath;
        }

        // Handle ID card front upload
        if ($request->hasFile('id_card_front')) {
            // Delete old ID card front if exists
            if ($customer->id_card_front && Storage::disk('public')->exists($customer->id_card_front)) {
                Storage::disk('public')->delete($customer->id_card_front);
            }

            $idCardFrontPath = $request->file('id_card_front')->store('customers/id_cards', 'public');
            $customer->id_card_front = $idCardFrontPath;
        }

        // Handle ID card back upload
        if ($request->hasFile('id_card_back')) {
            // Delete old ID card back if exists
            if ($customer->id_card_back && Storage::disk('public')->exists($customer->id_card_back)) {
                Storage::disk('public')->delete($customer->id_card_back);
            }

            $idCardBackPath = $request->file('id_card_back')->store('customers/id_cards', 'public');
            $customer->id_card_back = $idCardBackPath;
        }

        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Update customer information using update method (preferred for bulk updates)
        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        DB::commit();

        return redirect()->route('customer.profile.index')
            ->with('success', 'Profile updated successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Error updating profile: ' . $e->getMessage())
            ->withInput();
    }
}

public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:8|confirmed',
    ]);
    
    /** @var \App\Models\User $user */
    $user = Auth::user();
    
    // Check current password
    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors(['current_password' => 'Current password is incorrect']);
    }
    
    // Update password
    $user->password = Hash::make($request->new_password);
    $user->save();
    
    return redirect()->route('customer.profile.index')
        ->with('success', 'Password changed successfully!');
}

    // ========== DEBUG METHODS ==========
    public function debugCustomers()
    {
        echo "<h3>Customer Debug Information</h3>";
        
        // Check customers
        $customers = CustomerModel::with('user')->get();
        echo "<h4>All Customers:</h4>";
        
        if ($customers->count() === 0) {
            echo "❌ No customers found!<br>";
        } else {
            foreach ($customers as $cust) {
                echo "Customer ID: " . $cust->c_id . "<br>";
                echo "Customer Name: " . $cust->name . "<br>";
                echo "Customer Email: " . $cust->email . "<br>";
                echo "Phone: " . ($cust->phone ?? 'NULL') . "<br>";
                echo "products Count: " . $cust->customerproducts->count() . "<br>";
                echo "Active: " . ($cust->is_active ? 'YES' : 'NO') . "<br>";
                echo "User exists: " . ($cust->user ? 'YES' : 'NO') . "<br>";
                if ($cust->user) {
                    echo "User Role: " . $cust->user->role . "<br>";
                }
                echo "<hr>";
            }
        }
        
        // Check users from users table
        $usersFromUserTable = User::all();
        echo "<h4>All Users in User Table:</h4>";
        
        if ($usersFromUserTable->count() === 0) {
            echo "❌ No users found in users table!<br>";
        } else {
            foreach ($usersFromUserTable as $user) {
                echo "User ID: " . $user->id . "<br>";
                echo "User Name: " . $user->name . "<br>";
                echo "User Email: " . $user->email . "<br>";
                echo "User Role: " . ($user->role ?? 'NOT SET') . "<br>";
                echo "Created: " . $user->created_at . "<br>";
                echo "<hr>";
            }
        }

        // Check products
        $products = product::all();
        echo "<h4>Available products:</h4>";
        
        if ($products->count() === 0) {
            echo "❌ No products found!<br>";
        } else {
            foreach ($products as $product) {
                echo "product ID: " . $product->p_id . "<br>";
                echo "product Name: " . $product->name . "<br>";
                echo "product Type: " . $product->product_type . "<br>";
                echo "Monthly Price: ৳" . $product->monthly_price . "<br>";
                echo "<hr>";
            }
        }
    }
}