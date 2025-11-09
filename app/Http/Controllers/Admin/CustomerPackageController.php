<?php
// app/Http\Controllers\Admin\CustomerPackageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Package;
use App\Models\CustomerPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerPackageController extends Controller
{
    /** 🏠 Show all customer packages with search */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $status = $request->get('status');
            $packageType = $request->get('package_type');

            // Build query with search and filters - FIXED: Use customerPackages instead of activeCustomerPackages
            $customersQuery = Customer::with(['customerPackages.package' => function($query) {
                    $query->orderBy('package_type', 'desc');
                }])
                ->whereHas('customerPackages', function($query) use ($search, $status, $packageType) {
                    if ($status) {
                        $query->where('status', $status);
                    }
                    if ($packageType) {
                        $query->whereHas('package', function($q) use ($packageType) {
                            $q->where('package_type', $packageType);
                        });
                    }
                });

            // Apply search filter
            if ($search) {
                $customersQuery->where(function($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%')
                          ->orWhere('phone', 'like', '%' . $search . '%')
                          ->orWhere('customer_id', 'like', '%' . $search . '%');
                });
            }

            $customers = $customersQuery->orderBy('name')->paginate(10);

            // Keep the same stats calculation
            $totalCustomers = Customer::count();
            $activePackages = CustomerPackage::active()->count();
            $monthlyRevenue = DB::table('customer_to_packages as cp')
                ->join('packages as p', 'cp.p_id', '=', 'p.p_id')
                ->where('cp.status', 'active')
                ->where('cp.is_active', 1)
                ->select(DB::raw('COALESCE(SUM(p.monthly_price), 0) as total_revenue'))
                ->first()->total_revenue ?? 0;
            $renewalsDue = CustomerPackage::active()
                ->where('due_date', '<=', now()->addDays(7))
                ->count();

            return view('admin.customer-to-packages.index', compact(
                'customers',
                'totalCustomers',
                'activePackages',
                'monthlyRevenue',
                'renewalsDue'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading customer packages index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load packages data.');
        }
    }

    /** ➕ Assign package to customer */
    public function assign()
    {
        try {
            $customers = Customer::where('is_active', true)
                ->orderBy('name')
                ->get(['c_id', 'name', 'phone', 'email', 'customer_id', 'address']);
            
            $packages = Package::orderBy('package_type')
                ->orderBy('monthly_price')
                ->get(['p_id', 'name', 'monthly_price', 'package_type']);
            
            return view('admin.customer-to-packages.assign', compact('customers', 'packages'));

        } catch (\Exception $e) {
            Log::error('Error loading assign package form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load assignment form.');
        }
    }

    /** 💾 Store assigned packages  */


public function store(Request $request)
{
    // Log the request for debugging
    Log::info('Package assignment request received:', $request->all());

    $request->validate([
        'customer_id' => 'required|exists:customers,c_id',
        'packages' => 'required|array|min:1',
        'packages.*.package_id' => 'required|exists:packages,p_id',
        'packages.*.billing_cycle_months' => 'required|integer|min:1|max:12',
        'packages.*.assign_date' => 'required|date|before_or_equal:today',
    ]);

    $customerId = $request->customer_id;
    $packages = $request->packages;

    try {
        DB::beginTransaction();

        // Check for duplicate packages in the same request
        $packageIds = collect($packages)->pluck('package_id');
        if ($packageIds->count() !== $packageIds->unique()->count()) {
            DB::rollBack();
            return back()->with('error', 'You cannot assign the same package multiple times in the same request.')
                        ->withInput();
        }

        $assignedPackages = [];
        $errors = [];

        foreach ($packages as $index => $packageData) {
            $packageId = $packageData['package_id'];
            
            // Check if package is already assigned to this customer (active or inactive)
            $existingPackage = CustomerPackage::where('c_id', $customerId)
                ->where('p_id', $packageId)
                ->first();

            if ($existingPackage) {
                $packageName = Package::find($packageId)->name ?? 'Unknown Package';
                
                // Check if the existing package is active
                if ($existingPackage->is_active && $existingPackage->status === 'active') {
                    $errors[] = "Package '{$packageName}' is already actively assigned to this customer. Please choose a different package.";
                } else {
                    $errors[] = "Package '{$packageName}' was previously assigned to this customer. Please choose a different package.";
                }
                continue;
            }

            // Create the package assignment
            $customerPackage = CustomerPackage::create([
                'c_id' => $customerId,
                'p_id' => $packageId,
                'assign_date' => $packageData['assign_date'],
                'billing_cycle_months' => $packageData['billing_cycle_months'],
                'status' => 'active',
                'is_active' => 1,
            ]);

            $assignedPackages[] = $customerPackage;
            Log::info("Package assigned successfully:", [
                'customer_id' => $customerId,
                'package_id' => $packageId,
                'cp_id' => $customerPackage->cp_id
            ]);
        }

        if (!empty($errors)) {
            DB::rollBack();
            return back()
                ->with('error', implode(' ', $errors))
                ->withInput();
        }

        if (empty($assignedPackages)) {
            DB::rollBack();
            return back()
                ->with('error', 'No packages were assigned. Please check your selection.')
                ->withInput();
        }

        DB::commit();

        $successMessage = count($assignedPackages) . ' package(s) assigned successfully!';
        return redirect()->route('admin.customer-to-packages.index')
            ->with('success', $successMessage);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Package assignment failed: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return back()
            ->with('error', 'Failed to assign packages: ' . $e->getMessage())
            ->withInput();
    }
}


    // app/Http/Controllers/Admin/CustomerPackageController.php

/** 🔍 Check if package already exists for customer */
public function checkExistingPackage(Request $request)
{
    $request->validate([
        'customer_id' => 'required|exists:customers,c_id',
        'package_id' => 'required|exists:packages,p_id',
    ]);

    try {
        $customerId = $request->customer_id;
        $packageId = $request->package_id;

        $existingPackage = CustomerPackage::where('c_id', $customerId)
            ->where('p_id', $packageId)
            ->first();

        $packageName = Package::find($packageId)->name ?? 'Unknown Package';

        if ($existingPackage) {
            if ($existingPackage->is_active && $existingPackage->status === 'active') {
                return response()->json([
                    'exists' => true,
                    'message' => `This customer already has the "${$packageName}" package actively assigned. Please choose a different package.`
                ]);
            } else {
                return response()->json([
                    'exists' => true,
                    'message' => `This customer previously had the "${$packageName}" package. Please choose a different package.`
                ]);
            }
        }

        return response()->json([
            'exists' => false,
            'message' => 'Package is available for assignment.'
        ]);

    } catch (\Exception $e) {
        Log::error('Error checking existing package: ' . $e->getMessage());
        return response()->json([
            'exists' => false,
            'message' => 'Error checking package availability.'
        ], 500);
    }
}

    /** ✏️ Edit existing package */
    public function edit($id)
    {
        try {
            $customerPackage = CustomerPackage::with(['customer', 'package'])->find($id);

            if (!$customerPackage) {
                return redirect()->route('admin.customer-to-packages.index')
                    ->with('error', 'Package assignment not found.');
            }

            $packages = Package::orderBy('package_type')->orderBy('monthly_price')->get();
            
            return view('admin.customer-to-packages.edit', [
                'customerPackage' => $customerPackage,
                'customer' => $customerPackage->customer, // Pass customer separately
                'package' => $customerPackage->package,   // Pass package separately
                'packages' => $packages
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading package edit form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load edit form.');
        }
    }

    /** 🔄 Update package details or status */
    public function update(Request $request, $id)
    {
        $request->validate([
            'billing_cycle_months' => 'required|integer|min:1|max:12',
            'status' => 'required|in:active,pending,expired',
        ]);

        try {
            $customerPackage = CustomerPackage::find($id);
            
            if (!$customerPackage) {
                return redirect()->route('admin.customer-to-packages.index')
                    ->with('error', 'Package assignment not found.');
            }

            $customerPackage->update([
                'billing_cycle_months' => $request->billing_cycle_months,
                'status' => $request->status,
                'is_active' => $request->status === 'active' ? 1 : 0,
            ]);

            return redirect()->route('admin.customer-to-packages.index')
                ->with('success', 'Package updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating package: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update package.');
        }
    }

    /** 🔄 Toggle package status (active/expired) */
    public function toggleStatus($id)
    {
        try {
            $customerPackage = CustomerPackage::find($id);
            
            if (!$customerPackage) {
                return redirect()->route('admin.customer-to-packages.index')
                    ->with('error', 'Package assignment not found.');
            }

            // Toggle between active and expired
            $newStatus = $customerPackage->status === 'active' ? 'expired' : 'active';
            
            $customerPackage->update([
                'status' => $newStatus,
                'is_active' => $newStatus === 'active' ? 1 : 0,
            ]);

            $action = $newStatus === 'active' ? 'activated' : 'paused';
            
            return redirect()->route('admin.customer-to-packages.index')
                ->with('success', "Package {$action} successfully!");

        } catch (\Exception $e) {
            Log::error('Error toggling package status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to toggle package status.');
        }
    }

    /** ❌ Delete a customer's package */
    public function destroy($id)
    {
        try {
            $customerPackage = CustomerPackage::find($id);
            
            if (!$customerPackage) {
                return redirect()->route('admin.customer-to-packages.index')
                    ->with('error', 'Package assignment not found.');
            }

            $packageName = $customerPackage->package->name ?? 'Unknown Package';
            $customerPackage->delete();

            return redirect()->route('admin.customer-to-packages.index')
                ->with('success', "Package '{$packageName}' removed successfully!");

        } catch (\Exception $e) {
            Log::error('Error deleting package: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete package.');
        }
    }

    /** ♻️ Renew customer package */
    public function renew($id)
    {
        try {
            $customerPackage = CustomerPackage::find($id);
            
            if (!$customerPackage) {
                return redirect()->back()->with('error', 'Package assignment not found.');
            }

            $customerPackage->update([
                'billing_cycle_months' => $customerPackage->billing_cycle_months + 1,
                'status' => 'active',
                'is_active' => 1,
            ]);

            return redirect()->back()->with('success', 'Package renewed successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error renewing package: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to renew package.');
        }
    }

    /** 🔍 Search customers for AJAX requests */
    public function searchCustomers(Request $request)
    {
        try {
            $searchTerm = $request->get('q');
            
            $customers = Customer::where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                          ->orWhere('email', 'like', '%' . $searchTerm . '%')
                          ->orWhere('customer_id', 'like', '%' . $searchTerm . '%');
                })
                ->where('is_active', true)
                ->limit(10)
                ->get(['c_id', 'name', 'phone', 'email', 'customer_id', 'address']);
            
            return response()->json($customers);

        } catch (\Exception $e) {
            Log::error('Error searching customers: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}