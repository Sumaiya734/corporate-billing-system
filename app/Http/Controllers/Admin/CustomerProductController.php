<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerProduct;
use App\Models\Invoice;

class CustomerProductController extends Controller
{
    /**
     * Constructor - Apply middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_customer_products', ['only' => ['index', 'edit']]);
        $this->middleware('permission:create_customer_products', ['only' => ['assign', 'store', 'storeCustomer']]);
        $this->middleware('permission:edit_customer_products', ['only' => ['update', 'toggleStatus', 'renew']]);
        $this->middleware('permission:delete_customer_products', ['only' => ['destroy']]);
    }

    /** 📋 Display customer products listing */
    public function index(Request $request)
    {
        try {
            $query = Customer::with(['customerproducts.product', 'customerproducts.invoices'])
                ->whereHas('customerproducts');

            // Single customer view
            if ($request->has('customer_id')) {
                $customerId = (int) $request->customer_id;
                $query->where('c_id', $customerId);
            } else {
                // Apply filters for general view
                if ($request->filled('search')) {
                    $search = Str::lower($request->search);
                    $query->where(function ($q) use ($search) {
                        $q->where(DB::raw('LOWER(name)'), 'like', "%{$search}%")
                          ->orWhere(DB::raw('LOWER(email)'), 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('customer_id', 'like', "%{$search}%");
                    });
                }

                if ($request->filled('status')) {
                    $status = $request->status;
                    $query->whereHas('customerproducts', function ($q) use ($status) {
                        $q->where('status', $status);
                    });
                }

                if ($request->filled('product_type')) {
                    $productType = $request->product_type;
                    $query->whereHas('customerproducts.product', function ($q) use ($productType) {
                        $q->where('product_type', $productType);
                    });
                }
            }

            $customers = $query->orderBy('name')->paginate(15)->withQueryString();
            
            // Get total customers count
            $totalCustomers = Customer::whereHas('customerproducts')->count();

            // For single customer view, calculate total paid
            $totalPaid = 0;
            if ($request->has('customer_id') && $customers->count() === 1) {
                $customer = $customers->first();
                // Calculate total paid through invoices
                $totalPaid = $customer->customerproducts()
                    ->with('invoices.payments')
                    ->get()
                    ->flatMap(function ($cp) {
                        return $cp->invoices->flatMap(function ($invoice) {
                            return $invoice->payments->where('status', 'completed');
                        });
                    })
                    ->sum('amount');
            }

            return view('admin.customer-to-products.index', compact('customers', 'totalPaid', 'totalCustomers'));

        } catch (\Exception $e) {
            Log::error('Error loading customer products: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Failed to load customer products.');
        }
    }

    /** ➕ Show product assignment form */
    public function assign()
    {
        try {
            $products = Product::where('status', 'active')
                ->orderBy('name')
                ->get(['p_id', 'name', 'price', 'product_type']);
                
            $customers = Customer::where('is_active', true)
                ->orderBy('name')
                ->get(['c_id', 'name', 'customer_id', 'phone']);
                
            return view('admin.customer-to-products.assign', compact('products', 'customers'));

        } catch (\Exception $e) {
            Log::error('Error loading assign form: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Failed to load assignment form.');
        }
    }

    /** 🔍 Check if customer already has this product */
    public function checkExistingProduct(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|integer|exists:customers,c_id',
                'product_id' => 'required|integer|exists:products,p_id',
            ]);

            $exists = CustomerProduct::where('c_id', $validated['customer_id'])
                ->where('p_id', $validated['product_id'])
                ->where('status', 'active')
                ->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists,
                'message' => $exists ? 'Customer already has this active product.' : 'Product can be assigned.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking existing product: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to check product assignment.'
            ], 500);
        }
    }

    /** 📄 Preview invoice numbers before assignment */
    public function previewInvoiceNumbers(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|integer|exists:customers,c_id',
                'products' => 'required|array|min:1',
                'products.*.productId' => 'required|integer|exists:products,p_id',
                'products.*.productName' => 'required|string|max:255',
                'products.*.amount' => 'required|numeric|min:0',
                'products.*.months' => 'required|integer|min:1',
                'products.*.monthlyPrice' => 'required|numeric|min:0',
                'products.*.assignDate' => 'required|date'
            ]);

            $customerId = $validated['customer_id'];
            $products = $validated['products'];
            $invoices = [];

            // Check for duplicate product assignments
            foreach ($products as $productData) {
                $exists = CustomerProduct::where('c_id', $customerId)
                    ->where('p_id', $productData['productId'])
                    ->where('status', 'active')
                    ->exists();
                
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer already has one or more of these products assigned.'
                    ], 422);
                }
            }

            foreach ($products as $index => $productData) {
                // Generate unique invoice number
                $invoiceNumber = $this->generateInvoiceNumber();
                
                // Calculate the effective monthly price
                $totalAmount = (float) $productData['amount'];
                $months = (int) $productData['months'];
                $monthlyPrice = $months > 0 ? round($totalAmount / $months, 2) : 0;
                
                $invoices[] = [
                    'invoice_number' => $invoiceNumber,
                    'product_id' => $productData['productId'],
                    'product_name' => $productData['productName'],
                    'amount' => $totalAmount,
                    'months' => $months,
                    'monthly_price' => $monthlyPrice,
                    'assign_date' => $productData['assignDate']
                ];
            }

            return response()->json([
                'success' => true,
                'invoices' => $invoices
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing invoice numbers: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /** 💾 Store new customer product assignments */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'customer_id' => 'required|integer|exists:customers,c_id',
                'products' => 'required|array|min:1',
                'products.*.productId' => 'required|integer|exists:products,p_id',
                'products.*.price' => 'required|numeric|min:0',
                'products.*.months' => 'required|integer|min:1',
                'products.*.total' => 'required|numeric|min:0',
                'products.*.assignDate' => 'required|date',
                'products.*.dueDate' => 'nullable|date|after_or_equal:products.*.assignDate',
                'products.*.invoiceNumber' => 'required|string|unique:invoices,invoice_id'
            ]);

            $customerId = $validated['customer_id'];
            $products = $validated['products'];
            
            $assignedProducts = [];
            $invoiceData = [];

            // Verify all invoice numbers are unique
            $invoiceNumbers = array_column($products, 'invoiceNumber');
            if (count($invoiceNumbers) !== count(array_unique($invoiceNumbers))) {
                throw new \Exception('Duplicate invoice numbers detected.');
            }

            foreach ($products as $productData) {
                // Check for existing active assignment
                $existing = CustomerProduct::where('c_id', $customerId)
                    ->where('p_id', $productData['productId'])
                    ->where('status', 'active')
                    ->first();
                
                if ($existing) {
                    throw new \Exception('Customer already has an active assignment for one of these products.');
                }

                // Get product details for custom price calculation
                $product = Product::find($productData['productId']);
                $customPrice = $productData['total'] / $productData['months']; // Monthly custom price

                // Create customer product assignment
                $customerProduct = CustomerProduct::create([
                    'c_id' => $customerId,
                    'p_id' => $productData['productId'],
                    'original_price' => $product->price ?? 0,
                    'custom_price' => $customPrice, // Store monthly custom price
                    'assign_date' => $productData['assignDate'],
                    'billing_cycle_months' => $productData['months'],
                    'due_date' => $productData['dueDate'] ?? null,
                    'status' => 'active',
                    'is_active' => true,
                    'assigned_by' => Auth::id(),
                ]);

                // Store for response
                $assignedProducts[] = $customerProduct;

                // Create invoice record
                $invoice = Invoice::create([
                    'invoice_id' => $productData['invoiceNumber'],
                    'cp_id' => $customerProduct->cp_id,
                    'c_id' => $customerId,
                    'issue_date' => $productData['assignDate'],
                    'due_date' => $productData['dueDate'] ?? null,
                    'subtotal' => $productData['total'],
                    'total_amount' => $productData['total'],
                    'received_amount' => 0,
                    'next_due' => $productData['total'],
                    'status' => 'unpaid',
                    'created_by' => Auth::id(),
                ]);

                $invoiceData[] = $invoice;
            }

            DB::commit();

            // Log the assignment
            Log::info('Products assigned to customer', [
                'user_id' => Auth::id(),
                'customer_id' => $customerId,
                'products_count' => count($products),
                'product_ids' => array_column($products, 'productId')
            ]);

            return redirect()->route('admin.customer-to-products.index', ['customer_id' => $customerId])
                ->with('success', count($products) . ' product(s) assigned successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning products: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to assign products: ' . $e->getMessage())
                ->withInput();
        }
    }

    /** ✏️ Show edit form for customer product */
    public function edit($id)
    {
        try {
            $customerProduct = CustomerProduct::with([
                'customer', 
                'product', 
                'invoices.payments'
            ])->findOrFail($id);
            
            // Calculate total paid for this product
            $totalPaid = $customerProduct->invoices
                ->flatMap(function ($invoice) {
                    return $invoice->payments->where('status', 'completed');
                })
                ->sum('amount');
            
            return view('admin.customer-to-products.edit', compact('customerProduct', 'totalPaid'));
            
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'customer_product_id' => $id
            ]);
            return redirect()->route('admin.customer-to-products.index')
                ->with('error', 'Failed to load edit form.');
        }
    }

    /** 🔄 Update customer product */
    public function update(Request $request, $id)
    {
        try {
            $customerProduct = CustomerProduct::findOrFail($id);
            
            $validated = $request->validate([
                'custom_price' => 'required|numeric|min:0',
                'billing_cycle_months' => 'required|integer|min:1',
                'assign_date' => 'required|date',
                'due_date' => 'nullable|date|after_or_equal:assign_date',
                'status' => 'required|in:active,pending,expired,suspended'
            ]);

            // Calculate total amount
            $totalAmount = $validated['custom_price'] * $validated['billing_cycle_months'];

            DB::beginTransaction();
            
            $customerProduct->update([
                'custom_price' => $validated['custom_price'],
                'billing_cycle_months' => $validated['billing_cycle_months'],
                'assign_date' => $validated['assign_date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'is_active' => $validated['status'] === 'active',
                'updated_by' => Auth::id(),
            ]);

            // Update associated invoices if they exist
            $customerProduct->invoices()->update([
                'issue_date' => $validated['assign_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $totalAmount,
                'total_amount' => $totalAmount,
                'next_due' => $totalAmount - $customerProduct->invoices->sum('received_amount'),
            ]);

            DB::commit();

            Log::info('Customer product updated', [
                'user_id' => Auth::id(),
                'customer_product_id' => $id,
                'updates' => $validated
            ]);

            return redirect()->route('admin.customer-to-products.index', ['customer_id' => $customerProduct->c_id])
                ->with('success', 'Product assignment updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating customer product: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'customer_product_id' => $id,
                'request' => $request->all()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to update product assignment.')
                ->withInput();
        }
    }

    /** 🔄 Toggle product status (active/suspended) */
    public function toggleStatus($id)
    {
        try {
            $customerProduct = CustomerProduct::find($id);
            
            if (!$customerProduct) {
                return redirect()->route('admin.customer-to-products.index')
                    ->with('error', 'Product assignment not found.');
            }

            DB::beginTransaction();
            
            // Toggle between active and suspended
            $newStatus = $customerProduct->status === 'active' ? 'suspended' : 'active';
            
            $customerProduct->update([
                'status' => $newStatus,
                'is_active' => $newStatus === 'active' ? 1 : 0,
                'updated_by' => Auth::id(),
            ]);

            // Also update associated invoices status
            $customerProduct->invoices()->where('status', '!=', 'paid')->update([
                'status' => $newStatus === 'active' ? 'unpaid' : 'on_hold'
            ]);

            DB::commit();

            $action = $newStatus === 'active' ? 'activated' : 'suspended';
            
            Log::info('Customer product status toggled', [
                'user_id' => Auth::id(),
                'customer_product_id' => $id,
                'old_status' => $customerProduct->status,
                'new_status' => $newStatus
            ]);
            
            return redirect()->route('admin.customer-to-products.index')
                ->with('success', "Product {$action} successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling product status: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'customer_product_id' => $id
            ]);
            return redirect()->back()->with('error', 'Failed to toggle product status.');
        }
    }

    /** ❌ Delete a customer's product */
    public function destroy($id)
    {
        try {
            $customerProduct = CustomerProduct::find($id);
            
            if (!$customerProduct) {
                return redirect()->route('admin.customer-to-products.index')
                    ->with('error', 'Product assignment not found.');
            }

            // Check if there are any payments made
            $totalPaid = $customerProduct->invoices->sum('received_amount');
            if ($totalPaid > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete product with existing payments. Please deactivate instead.');
            }

            $customerId = $customerProduct->c_id;
            $productName = $customerProduct->product->name ?? 'Unknown product';
            
            DB::beginTransaction();
            
            // Delete associated invoices first
            $customerProduct->invoices()->delete();
            $customerProduct->delete();

            DB::commit();

            Log::info('Customer product deleted', [
                'user_id' => Auth::id(),
                'customer_product_id' => $id,
                'product_name' => $productName
            ]);

            return redirect()->route('admin.customer-to-products.index', ['customer_id' => $customerId])
                ->with('success', "Product '{$productName}' removed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'customer_product_id' => $id
            ]);
            return redirect()->back()->with('error', 'Failed to delete product.');
        }
    }

    /** ♻️ Renew customer product */
    public function renew($id)
    {
        try {
            $customerProduct = CustomerProduct::find($id);
            
            if (!$customerProduct) {
                return redirect()->back()->with('error', 'Product assignment not found.');
            }

            DB::beginTransaction();
            
            $customerProduct->update([
                'billing_cycle_months' => $customerProduct->billing_cycle_months + 1,
                'due_date' => $customerProduct->due_date ? 
                    date('Y-m-d', strtotime($customerProduct->due_date . ' +1 month')) : null,
                'status' => 'active',
                'is_active' => 1,
                'updated_by' => Auth::id(),
            ]);

            // Create a new invoice for the renewal
            $newInvoiceNumber = $this->generateInvoiceNumber();
            $totalAmount = $customerProduct->custom_price; // Monthly amount
            
            Invoice::create([
                'invoice_id' => $newInvoiceNumber,
                'cp_id' => $customerProduct->cp_id,
                'c_id' => $customerProduct->c_id,
                'issue_date' => now(),
                'due_date' => $customerProduct->due_date,
                'subtotal' => $totalAmount,
                'total_amount' => $totalAmount,
                'received_amount' => 0,
                'next_due' => $totalAmount,
                'status' => 'unpaid',
                'created_by' => Auth::id(),
                'is_renewal' => true,
            ]);

            DB::commit();

            Log::info('Customer product renewed', [
                'user_id' => Auth::id(),
                'customer_product_id' => $id
            ]);

            return redirect()->route('admin.customer-to-products.index', ['customer_id' => $customerProduct->c_id])
                ->with('success', 'Product renewed successfully! New invoice generated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error renewing product: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'customer_product_id' => $id
            ]);
            return redirect()->back()->with('error', 'Failed to renew product.');
        }
    }

    /** 👤 Store new customer via AJAX */
    public function storeCustomer(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'customer_id' => 'required|string|max:50|unique:customers,customer_id',
                'phone' => 'required|string|max:20|unique:customers,phone',
                'email' => 'nullable|email|max:255|unique:customers,email',
                'address' => 'nullable|string|max:500',
            ]);

            $customer = Customer::create([
                'name' => $validated['name'],
                'customer_id' => $validated['customer_id'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            Log::info('New customer created', [
                'user_id' => Auth::id(),
                'customer_id' => $customer->c_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'customer' => [
                    'c_id' => $customer->c_id,
                    'name' => $customer->name,
                    'customer_id' => $customer->customer_id,
                    'phone' => $customer->phone,
                    'email' => $customer->email
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /** 🔍 Get customer suggestions for AJAX */
    public function getCustomerSuggestions(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }

            $search = Str::lower($query);
            $customers = Customer::where(function($q) use ($search) {
                    $q->where(DB::raw('LOWER(name)'), 'like', "%{$search}%")
                      ->orWhere(DB::raw('LOWER(email)'), 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('customer_id', 'like', "%{$search}%");
                })
                ->where('is_active', true)
                ->limit(10)
                ->get(['c_id', 'name', 'phone', 'email', 'customer_id', 'address']);
            
            return response()->json($customers);

        } catch (\Exception $e) {
            Log::error('Error searching customers: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'query' => $request->get('q')
            ]);
            return response()->json([], 500);
        }
    }

    /** 🧾 Get customer invoices for AJAX */
    public function getCustomerInvoices($customerId)
    {
        try {
            $customerId = (int) $customerId;
            
            if (!$customerId) {
                return response()->json([], 400);
            }

            $invoices = Invoice::where('c_id', $customerId)
                ->with(['customerProduct.product', 'payments'])
                ->orderBy('issue_date', 'desc')
                ->get()
                ->map(function ($invoice) {
                    return [
                        'invoice_id' => $invoice->invoice_id,
                        'product_name' => $invoice->customerProduct->product->name ?? 'N/A',
                        'issue_date' => $invoice->issue_date,
                        'due_date' => $invoice->due_date,
                        'total_amount' => $invoice->total_amount,
                        'received_amount' => $invoice->received_amount,
                        'status' => $invoice->status,
                        'payments' => $invoice->payments->count()
                    ];
                });

            return response()->json($invoices);

        } catch (\Exception $e) {
            Log::error('Error fetching customer invoices: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'customer_id' => $customerId
            ]);
            return response()->json([], 500);
        }
    }

    /** 📊 Get customer product statistics */
    public function getCustomerStats($customerId)
    {
        try {
            $customerId = (int) $customerId;
            
            $stats = [
                'total_products' => CustomerProduct::where('c_id', $customerId)->count(),
                'active_products' => CustomerProduct::where('c_id', $customerId)->where('status', 'active')->count(),
                'total_invoices' => Invoice::where('c_id', $customerId)->count(),
                'total_paid' => Invoice::where('c_id', $customerId)->sum('received_amount'),
                'total_pending' => Invoice::where('c_id', $customerId)->sum('next_due'),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching customer stats: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'customer_id' => $customerId
            ]);
            return response()->json(['success' => false], 500);
        }
    }

    /** Generate unique invoice number */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = date('Ym');
        
        // Use advisory lock to prevent duplicate invoice numbers
        $lockName = 'invoice_number_generation_' . $date;
        DB::statement("SELECT GET_LOCK('{$lockName}', 5)");
        
        try {
            $lastInvoice = Invoice::where('invoice_id', 'like', "{$prefix}{$date}%")
                ->orderBy('invoice_id', 'desc')
                ->first();

            if ($lastInvoice) {
                $lastNumber = (int) substr($lastInvoice->invoice_id, -4);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            return "{$prefix}{$date}{$newNumber}";
        } finally {
            DB::statement("SELECT RELEASE_LOCK('{$lockName}')");
        }
    }
}