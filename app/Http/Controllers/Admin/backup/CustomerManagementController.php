<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class CustomerManagementController extends Controller
{
    /**
     * Display all customers
     */
    public function index()
    {
        $customers = User::where('user_type', 'customer')
                        ->with('customer')
                        ->latest()
                        ->get();
        
        return view('admin.customers.index', compact('customers'));
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
            // Create User account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'customer',
                'email_verified_at' => now(),
            ]);

            // Create Customer profile
            Customer::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'address' => $request->address,
                'connection_address' => $request->connection_address,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'status' => 'active',
                'registration_date' => now(),
            ]);

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully! Customer ID: ' . $user->id);

        } catch (\Exception $e) {
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
        $customer = User::where('user_type', 'customer')
                       ->with('customer')
                       ->findOrFail($id);
        
        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit($id)
    {
        $customer = User::where('user_type', 'customer')
                       ->with('customer')
                       ->findOrFail($id);
        
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        $user = User::where('user_type', 'customer')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:500',
            'connection_address' => 'required|string|max:500',
            'id_type' => 'required|string|in:nid,passport,driving_license',
            'id_number' => 'required|string|max:50',
            'status' => 'required|string|in:active,inactive,suspended',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $user->customer->update([
            'phone' => $request->phone,
            'address' => $request->address,
            'connection_address' => $request->connection_address,
            'id_type' => $request->id_type,
            'id_number' => $request->id_number,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified customer
     */
    public function destroy($id)
    {
        $user = User::where('user_type', 'customer')->findOrFail($id);
        
        // Delete customer profile first
        if ($user->customer) {
            $user->customer->delete();
        }
        
        // Then delete user account
        $user->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }
}