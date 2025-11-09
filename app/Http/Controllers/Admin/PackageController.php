<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::all();
        $stats = $this->getPackageStats();
        
        return view('admin.packages.index', compact('packages', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'package_type' => 'required|string|max:50',
            'description' => 'required|string',
            'monthly_price' => 'required|numeric|min:0',
        ]);

        try {
            $package = Package::create([
                'name' => $request->name,
                'package_type' => $request->package_type,
                'description' => $request->description,
                'monthly_price' => $request->monthly_price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Package created successfully!',
                'package' => $package
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create package: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $package = Package::where('p_id', $id)->firstOrFail();
        return response()->json($package);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'package_type' => 'required|string|max:50',
            'description' => 'required|string',
            'monthly_price' => 'required|numeric|min:0',
        ]);

        try {
            $package = Package::where('p_id', $id)->firstOrFail();
            
            $package->update([
                'name' => $request->name,
                'package_type' => $request->package_type,
                'description' => $request->description,
                'monthly_price' => $request->monthly_price,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Package updated successfully!',
                'package' => $package
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update package: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $package = Package::where('p_id', $id)->firstOrFail();
            
            // Check if package is assigned to any customers before deletion
            $assignedCount = DB::table('customer_to_packages')
                ->where('p_id', $id)
                ->where('status', 'active')
                ->count();
                
            if ($assignedCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete package. It is currently assigned to ' . $assignedCount . ' active customer(s).'
                ], 400);
            }

            $package->delete();

            return response()->json([
                'success' => true,
                'message' => 'Package deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete package: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getPackageStats()
    {
        $totalCustomers = DB::table('customer_to_packages')
            ->where('status', 'active')
            ->count();

        $regularPriceRange = Package::where('package_type', 'regular')
            ->selectRaw('COALESCE(MIN(monthly_price), 0) as min_price, COALESCE(MAX(monthly_price), 0) as max_price')
            ->first();

        $specialPriceRange = Package::where('package_type', 'special')
            ->selectRaw('COALESCE(MIN(monthly_price), 0) as min_price, COALESCE(MAX(monthly_price), 0) as max_price')
            ->first();

        return [
            'total_packages' => Package::count(),
            'regular_packages' => Package::where('package_type', 'regular')->count(),
            'special_packages' => Package::where('package_type', 'special')->count(),
            'active_customers' => $totalCustomers,
            'average_price' => Package::avg('monthly_price') ?? 0,
            'price_range_regular' => [
                'min' => $regularPriceRange->min_price ?? 0,
                'max' => $regularPriceRange->max_price ?? 0
            ],
            'price_range_special' => [
                'min' => $specialPriceRange->min_price ?? 0,
                'max' => $specialPriceRange->max_price ?? 0
            ],
            'most_popular_package' => $this->getMostPopularPackage()
        ];
    }

    private function getMostPopularPackage()
    {
        $popularPackage = DB::table('customer_to_packages as cp')
            ->join('packages as p', 'cp.p_id', '=', 'p.p_id')
            ->where('cp.status', 'active')
            ->select('p.p_id', 'p.name', DB::raw('COUNT(cp.cp_id) as customer_count'))
            ->groupBy('p.p_id', 'p.name')
            ->orderByDesc('customer_count')
            ->first();

        return $popularPackage ?: null;
    }
}