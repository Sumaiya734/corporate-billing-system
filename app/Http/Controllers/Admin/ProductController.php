<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('type')
            ->orderBy('created_at', 'desc') // Latest products first
            ->orderBy('p_id', 'desc') // Then by ID descending
            ->get();
        $productTypes = ProductType::all();
        $stats = $this->getProductStats();

        return view('admin.products.index', compact('products', 'stats', 'productTypes'));
    }

    public function create()
    {
        $productTypes = ProductType::all();
        return view('admin.products.create', compact('productTypes'));
    }

    public function store(Request $request)
    {
        Log::info('Product creation request received', [
            'method' => $request->method(),
            'url' => $request->url(),
            'all_data' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'x_requested_with' => $request->header('X-Requested-With'),
        ]);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:120',
            'product_type_id' => 'required|exists:product_type,id',
            'description' => 'required|string',
            'monthly_price' => 'required|numeric|min:0',
        ]);
        
        Log::info('Product validation passed', $validatedData);

        try {
            // Remove created_at and updated_at from the data since Laravel handles them automatically
            $productData = [
                'name' => $validatedData['name'],
                'product_type_id' => $validatedData['product_type_id'],
                'description' => $validatedData['description'],
                'monthly_price' => $validatedData['monthly_price'],
            ];
            
            Log::info('Creating product with data', $productData);
            
            $product = Product::create($productData);
            
            Log::info('Product created successfully', ['product_id' => $product->p_id]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create product: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        Log::info('=== SHOW METHOD CALLED ===', [
            'id' => $id,
            'type' => gettype($id),
            'request_url' => request()->url(),
            'request_method' => request()->method()
        ]);
        
        try {
            Log::info('Fetching product', ['id' => $id]);
            
            $product = Product::with('type')->where('p_id', $id)->firstOrFail();
            
            Log::info('Product found', ['product' => $product->toArray()]);
            
            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Failed to fetch product', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Product not found: ' . $e->getMessage()
            ], 404);
        }
    }

    public function edit($id)
{
    Log::info('=== EDIT METHOD CALLED ===', [
        'id' => $id,
        'request_url' => request()->url(),
        'request_method' => request()->method()
    ]);
    
    try {
        Log::info('Fetching product for edit', ['id' => $id]);
        
        $product = Product::with('type')->where('p_id', $id)->firstOrFail();
        
        Log::info('Product found for edit', ['product' => $product->toArray()]);
        
        return response()->json($product);
    } catch (\Exception $e) {
        Log::error('Failed to fetch product for edit', [
            'id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Product not found: ' . $e->getMessage()
        ], 404);
    }
}
    public function update(Request $request, $id)
    {
        Log::info('Product update request received', [
            'method' => $request->method(),
            'url' => $request->url(),
            'product_id' => $id,
            'all_data' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'x_requested_with' => $request->header('X-Requested-With'),
        ]);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:120',
            'product_type_id' => 'required|exists:product_type,id',
            'description' => 'required|string',
            'monthly_price' => 'required|numeric|min:0',
        ]);
        
        Log::info('Product update validation passed', $validatedData);

        try {
            $product = Product::where('p_id', $id)->firstOrFail();
            
            // Remove updated_at from the data since Laravel handles it automatically
            $productData = [
                'name' => $validatedData['name'],
                'product_type_id' => $validatedData['product_type_id'],
                'description' => $validatedData['description'],
                'monthly_price' => $validatedData['monthly_price'],
            ];
            
            Log::info('Updating product with data', $productData);
            
            $product->update($productData);
            
            Log::info('Product updated successfully', ['product_id' => $product->p_id]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update product: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Deleting product', ['id' => $id]);
            
            $product = Product::where('p_id', $id)->firstOrFail();

            $assignedCount = DB::table('customer_to_products')
                ->where('p_id', $id)
                ->where('status', 'active')
                ->count();

            if ($assignedCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product. It is currently assigned to ' . $assignedCount . ' active customer(s).'
                ], 400);
            }

            $product->delete();
            
            Log::info('Product deleted successfully', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete product', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getProductStats()
    {
        $totalCustomers = DB::table('customer_to_products')
            ->where('status', 'active')
            ->count();

        // Get total product types
        $totalTypes = ProductType::count();

        // Get most popular product name
        $mostPopularProduct = $this->getMostPopularProduct();
        $mostPopularName = $mostPopularProduct ? $mostPopularProduct->name : 'N/A';

        // Get regular and special product stats
        $regularType = ProductType::where('name', 'regular')->first();
        $specialType = ProductType::where('name', 'special')->first();

        $regularProducts = $regularType ? Product::where('product_type_id', $regularType->id)->get() : collect();
        $specialProducts = $specialType ? Product::where('product_type_id', $specialType->id)->get() : collect();

        return [
            'total_products' => Product::count(),
            'total_types' => $totalTypes,
            'regular_products' => $regularProducts->count(),
            'special_products' => $specialProducts->count(),
            'active_customers' => $totalCustomers,
            'average_price' => Product::avg('monthly_price') ?? 0,
            'most_popular' => $mostPopularName,
            'most_popular_product' => $mostPopularProduct,
            'price_range_regular' => [
                'min' => $regularProducts->min('monthly_price') ?? 0,
                'max' => $regularProducts->max('monthly_price') ?? 0,
            ],
            'price_range_special' => [
                'min' => $specialProducts->min('monthly_price') ?? 0,
                'max' => $specialProducts->max('monthly_price') ?? 0,
            ],
        ];
    }

    private function getMostPopularProduct()
    {
        $popularProduct = DB::table('customer_to_products as cp')
            ->join('products as p', 'cp.p_id', '=', 'p.p_id')
            ->where('cp.status', 'active')
            ->select('p.p_id', 'p.name', DB::raw('COUNT(cp.cp_id) as customer_count'))
            ->groupBy('p.p_id', 'p.name')
            ->orderByDesc('customer_count')
            ->first();

        return $popularProduct ?: null;
    }

    // -------------------------
    // Product Type Management
    // -------------------------

    // Update your product type methods in ProductController
    public function productTypes()
    {
        $productTypes = ProductType::withCount('products')->orderBy('name')->get();
        
        // Calculate product counts for each type
        $productCounts = [];
        foreach ($productTypes as $type) {
            $productCounts[$type->name] = $type->products_count;
        }
        
        return view('admin.products.types', compact('productTypes', 'productCounts'));
    }

    public function addProductType(Request $request)
    {
        // Debug the incoming request
        Log::info('Add Product Type Request:', [
            'method' => $request->method(),
            'url' => $request->url(),
            'all_data' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'x_requested_with' => $request->header('X-Requested-With'),
            'is_ajax' => $request->ajax(),
        ]);
        
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:50|unique:product_type,name',
                'descriptions' => 'nullable|string|max:500',
            ]);
            
            Log::info('Product type validation passed', $validatedData);

            Log::info('Creating product type: ' . $validatedData['name']);
            
            $type = ProductType::create([
                'name' => $validatedData['name'],
                'descriptions' => $validatedData['descriptions'] ?? null,
            ]);

            Log::info('Product type created successfully: ' . $type->id);

            return response()->json([
                'success' => true,
                'message' => 'Product type added successfully!',
                'type' => $type
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Product type validation failed: ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create product type: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteProductType($id)
    {
        try {
            $type = ProductType::findOrFail($id);

            // Check if this is a protected type
            if (in_array($type->name, ['regular', 'special'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete protected product types (regular, special).'
                ], 400);
            }

            // Delete products belonging to this type
            $type->products()->delete();
            $type->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product type and associated products deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product type: ' . $e->getMessage()
            ], 500);
        }
    }
}