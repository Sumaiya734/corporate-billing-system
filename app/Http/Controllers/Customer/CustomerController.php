<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer as CustomerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class CustomerController extends Controller
{
    // ========== CUSTOMER AUTHENTICATION METHODS ==========
    
    public function showLoginForm()
    {
        if (Auth::guard('customer')->check()) {
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

        if (Auth::guard('customer')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('customer.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.dashboard', compact('customer'));
    }

    // ========== ADMIN CUSTOMER MANAGEMENT METHODS ==========
    
    public function index()
    {
        $customers = CustomerModel::with('user')
                    ->latest()
                    ->get();
        
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:500',
            'connection_address' => 'required|string|max:500',
            'id_type' => 'required|string|in:nid,passport,driving_license',
            'id_number' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Create User account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'customer',
                'email_verified_at' => now(),
            ]);

            // Create Customer profile
            CustomerModel::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'address' => $request->address,
                'connection_address' => $request->connection_address,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'status' => 'active',
                'registration_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully! Customer ID: ' . $user->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $customer = CustomerModel::with('user')->findOrFail($id);
        return view('admin.customers.profile', compact('customer'));
    }

    public function edit($id)
    {
        $customer = CustomerModel::with('user')->findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = CustomerModel::with('user')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:users,name,' . $customer->user_id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->user_id,
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:500',
            'connection_address' => 'required|string|max:500',
            'id_type' => 'required|string|in:nid,passport,driving_license',
            'id_number' => 'required|string|max:50',
            'status' => 'required|string|in:active,inactive,suspended',
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
                'phone' => $request->phone,
                'address' => $request->address,
                'connection_address' => $request->connection_address,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'status' => $request->status,
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
        $customer = CustomerModel::with('user')->findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete user account first
            if ($customer->user) {
                $customer->user->delete();
            }
            
            // Then delete customer profile
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

    // ========== DEBUG METHOD ==========
    public function debug()
    {
        echo "<h3>Debug Customers</h3>";
        
        // Check customers from customers table
        $customersFromCustomerTable = CustomerModel::with('user')->get();
        echo "<h4>From Customer Table (used in index):</h4>";
        
        if ($customersFromCustomerTable->count() === 0) {
            echo "❌ No customers found in customers table!<br>";
        } else {
            foreach ($customersFromCustomerTable as $cust) {
                echo "Customer ID: " . $cust->id . "<br>";
                echo "User ID: " . $cust->user_id . "<br>";
                echo "Phone: " . ($cust->phone ?? 'NULL') . "<br>";
                echo "Status: " . ($cust->status ?? 'NULL') . "<br>";
                echo "Registration Date: " . ($cust->registration_date ?? 'NULL') . "<br>";
                echo "User exists: " . ($cust->user ? 'YES' : 'NO') . "<br>";
                if ($cust->user) {
                    echo "User Name: " . $cust->user->name . "<br>";
                    echo "User Email: " . $cust->user->email . "<br>";
                } else {
                    echo "❌ User record missing for customer!<br>";
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
                echo "User Type: " . ($user->user_type ?? 'NOT SET') . "<br>";
                echo "Created: " . $user->created_at . "<br>";
                echo "<hr>";
            }
        }
    }
   public function profile($id)
{
    // Static customer data - no database needed
    $customers = [
        1 => [
            'id' => 1,
            'name' => 'Test Customer',
            'email' => 'customer@netbillbd.com',
            'phone' => '+8801712345678',
            'address' => 'Dhaka, Bangladesh',
            'connection_address' => 'House 123, Road 5, Dhaka',
            'join_date' => '2024-01-15',
            'status' => 'Active',
            'id_type' => 'nid',
            'id_number' => '1990123456789',
            'package' => 'Basic Internet Package',
            'monthly_bill' => '৳850',
            'bandwidth' => '20 Mbps',
            'payment_method' => 'Credit Card',
            'next_billing_date' => 'February 1, 2024'
        ],
        2 => [
            'id' => 2,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+8801712345678',
            'address' => 'Gulshan, Dhaka',
            'connection_address' => 'House 12, Road 5, Gulshan 1',
            'join_date' => '2023-01-15',
            'status' => 'Active',
            'id_type' => 'nid',
            'id_number' => '1990123456789',
            'package' => 'Basic Speed',
            'monthly_bill' => '৳550',
            'bandwidth' => '10 Mbps',
            'payment_method' => 'Credit Card',
            'next_billing_date' => 'February 1, 2024'
        ],
        3 => [
            'id' => 3,
            'name' => 'Alice Smith',
            'email' => 'alice.smith@example.com',
            'phone' => '+8801812345679',
            'address' => 'Uttara, Dhaka',
            'connection_address' => 'House 25, Sector 7, Uttara',
            'join_date' => '2023-02-20',
            'status' => 'Active',
            'id_type' => 'passport',
            'id_number' => 'AB1234567',
            'package' => 'Fast Speed + Gaming Boost',
            'monthly_bill' => '৳1,050',
            'bandwidth' => '25 Mbps',
            'payment_method' => 'Bank Transfer',
            'next_billing_date' => 'February 1, 2024'
        ],
        4 => [
            'id' => 4,
            'name' => 'Bob Johnson',
            'email' => 'bob.johnson@example.com',
            'phone' => '+8801912345680',
            'address' => 'Banani, Dhaka',
            'connection_address' => 'House 8, Road 11, Banani',
            'join_date' => '2023-03-10',
            'status' => 'Active',
            'id_type' => 'driving_license',
            'id_number' => 'DL789456123',
            'package' => 'Super Speed + Streaming Plus',
            'monthly_bill' => '৳1,400',
            'bandwidth' => '50 Mbps',
            'payment_method' => 'Credit Card',
            'next_billing_date' => 'February 1, 2024'
        ],
        5 => [
            'id' => 5,
            'name' => 'Carol White',
            'email' => 'carol.white@example.com',
            'phone' => '+8801612345681',
            'address' => 'Dhanmondi, Dhaka',
            'connection_address' => 'House 15, Road 2, Dhanmondi',
            'join_date' => '2023-04-05',
            'status' => 'Active',
            'id_type' => 'nid',
            'id_number' => '1995123456789',
            'package' => 'Fast Speed',
            'monthly_bill' => '৳850',
            'bandwidth' => '25 Mbps',
            'payment_method' => 'Mobile Banking',
            'next_billing_date' => 'February 1, 2024'
        ]
    ];

    // Check if customer exists in our static data
    if (!isset($customers[$id])) {
        abort(404, 'Customer not found');
    }

    $customer = $customers[$id];

    return view('admin.customers.profile', compact('customer'));
}
}