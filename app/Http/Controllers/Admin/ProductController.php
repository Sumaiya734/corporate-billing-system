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
    public function index(Request $request)
    {
        // Handle duplicate checking request
        if ($request->has('check_duplicate')) {
            $productName = $request->get('check_duplicate');
            
            // Check for exact name match
            $exactMatch = Product::where('name', $productName)->first();
            
            // Check for similar names (case insensitive partial match)
            $similarMatch = Product::where('name', 'LIKE', '%' . $productName . '%')
                ->where('name', '!=', $productName)
                ->first();
            
            $duplicates = [];
            if ($exactMatch) {
                $duplicates['name_exact'] = $exactMatch;
            }
            if ($similarMatch) {
                $duplicates['name_similar'] = $similarMatch;
            }
            
            return response()->json([
                'success' => true,
                'duplicates' => $duplicates
            ]);
        }
        
        $products = Product::with('type')
            ->orderBy('created_at', 'desc')
            ->orderBy('p_id', 'desc')
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
        Log::info('=== PRODUCT STORE METHOD START ===');
        Log::info('Request method: ' . $request->method());
        Log::info('Request URL: ' . $request->url());
        Log::info('Request data:', $request->all());
        Log::info('Headers:', [
            'Content-Type' => $request->header('Content-Type'),
            'Accept' => $request->header('Accept'),
            'X-Requested-With' => $request->header('X-Requested-With'),
        ]);
        
        // Log available product types for debugging
        $availableTypes = ProductType::pluck('id', 'name')->toArray();
        Log::info('Available product types in database:', $availableTypes);
        Log::info('Request product_type_id: ' . $request->input('product_type_id'));
        
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:120|unique:products,name',
                'product_type_id' => 'required|exists:product_type,id',
                'description' => 'required|string',
                'monthly_price' => 'required|numeric|min:0',
            ]);
            
            Log::info('✅ Validation passed:', $validatedData);
            
            // Double-check that product_type_id exists
            $typeExists = DB::table('product_type')->where('id', $validatedData['product_type_id'])->exists();
            if (!$typeExists) {
                Log::error('❌ Product type does not exist in database:', ['id' => $validatedData['product_type_id']]);
                return response()->json([
                    'success' => false,
                    'message' => 'Selected product type does not exist.',
                    'errors' => ['product_type_id' => ['The selected product type is invalid.']]
                ], 422);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation failed:', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed. Please check your input.',
                'debug' => [
                    'product_type_id_received' => $request->input('product_type_id'),
                    'product_types_in_db' => $availableTypes,
                ]
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Exception during validation: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 500);
        }

        try {
            // Create product data array
            $productData = [
                'name' => $validatedData['name'],
                'product_type_id' => $validatedData['product_type_id'],
                'description' => $validatedData['description'],
                'monthly_price' => $validatedData['monthly_price'],
            ];
            
            Log::info('Creating product with data:', $productData);
            
            // Create the product
            $product = Product::create($productData);
            
            Log::info('✅ Product created successfully!', [
                'product_id' => $product->p_id,
                'product_name' => $product->name,
                'full_product' => $product->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'product' => $product,
                'redirect_url' => route('admin.products.index')
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Failed to create product: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'product_data' => $productData ?? null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
                'debug' => [
                    'error_type' => get_class($e),
                    'product_data' => $productData ?? null,
                ]
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
            'type' => gettype($id),
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
        
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:120|unique:products,name,' . $id . ',p_id',
                'product_type_id' => 'required|exists:product_type,id',
                'description' => 'required|string',
                'monthly_price' => 'required|numeric|min:0',
            ]);
            
            Log::info('Product update validation passed', $validatedData);
            
            $product = Product::where('p_id', $id)->firstOrFail();
            
            // Update product data
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Update validation failed:', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
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

        $totalTypes = ProductType::count();

        $mostPopularProduct = $this->getMostPopularProduct();
        $mostPopularName = $mostPopularProduct ? $mostPopularProduct->name : 'N/A';

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

    public function productTypes()
    {
        $productTypes = ProductType::withCount('products')->orderBy('name')->get();
        
        $productCounts = [];
        foreach ($productTypes as $type) {
            $productCounts[$type->name] = $type->products_count;
        }
        
        return view('admin.products.types', compact('productTypes', 'productCounts'));
    }

    public function addProductType(Request $request)
    {
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

            $productCount = $type->products()->count();
            
            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete product type. There are {$productCount} product(s) associated with this type. Please delete all associated products first.",
                    'product_count' => $productCount
                ], 400);
            }

            $type->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product type deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product type: ' . $e->getMessage()
            ], 500);
        }
    }
}