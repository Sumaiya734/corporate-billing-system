<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerProduct;
use App\Models\Invoice;

class CustomerProductController extends Controller
{
    /** 📋 Display customer products listing */
    public function index(Request $request)
    {
        try {
            $query = Customer::with(['customerproducts' => function($query) {
                // Exclude deleted products
                $query->where('status', '!=', 'deleted');
            }, 'customerproducts.product', 'customerproducts.invoices'])
                ->whereHas('customerproducts', function($query) {
                    // Only include customers with non-deleted products
                    $query->where('status', '!=', 'deleted');
                });

            // Single customer view
            if ($request->has('customer_id')) {
                $query->where('c_id', $request->customer_id);
            } else {
                // Apply filters for general view
                if ($request->search) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('customer_id', 'like', "%{$search}%");
                    });
                }

                if ($request->status) {
                    $query->whereHas('customerproducts', function ($q) use ($request) {
                        $q->where('status', $request->status);
                    });
                }

                if ($request->product_type) {
                    $query->whereHas('customerproducts.product', function ($q) use ($request) {
                        $q->where('product_type', $request->product_type);
                    });
                }
            }

            $customers = $query->orderBy('name')->paginate(15)->withQueryString();
            
            // Get total customers count with non-deleted products
            $totalCustomers = Customer::whereHas('customerproducts', function($query) {
                $query->where('status', '!=', 'deleted');
            })->count();

            // For single customer view, calculate total paid
            $totalPaid = 0;
            if ($request->has('customer_id') && $customers->count() === 1) {
                $customer = $customers->first();
                // Calculate total paid through invoices for non-deleted products
                $totalPaid = $customer->customerproducts()
                    ->where('status', '!=', 'deleted')
                    ->with('invoices.payments')
                    ->get()
                    ->flatMap(function ($cp) {
                        return $cp->invoices->flatMap(function ($invoice) {
                            return $invoice->payments;
                        });
                    })
                    ->sum('amount');
            }

            return view('admin.customer-to-products.index', compact('customers', 'totalPaid', 'totalCustomers'));
        } catch (\Exception $e) {
            Log::error('Error loading customer products: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load customer products.');
        }
    }

    /** ➕ Show product assignment form */
    public function assign()
    {
        try {
            $products = Product::orderBy('name')->get();
            
            $customers = Customer::where('is_active', true)
                ->orderBy('name')
                ->get();
                
            return view('admin.customer-to-products.assign', compact('products', 'customers'));
        } catch (\Exception $e) {
            Log::error('Error loading assign form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load assignment form.');
        }
    }

    /** 🔍 Check if customer already has this product */
    public function checkExistingProduct(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required|exists:customers,c_id',
                'product_id' => 'required|exists:products,p_id',
            ]);

            $exists = CustomerProduct::where('c_id', $request->customer_id)
                ->where('p_id', $request->product_id)
                ->where('status', 'active')
                ->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking existing product: ' . $e->getMessage());
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
            $request->validate([
                'customer_id' => 'required|exists:customers,c_id',
                'products' => 'required|array|min:1',
                'products.*.productName' => 'required|string',
                'products.*.amount' => 'required|numeric|min:0',
                'products.*.months' => 'required|integer|min:1',
                'products.*.monthlyPrice' => 'required|numeric|min:0',
                'products.*.assignDate' => 'required|date'
            ]);

            $customerId = $request->customer_id;
            $products = $request->products;
            $invoices = [];

            foreach ($products as $index => $productData) {
                // Generate a temporary invoice number for preview
                $invoiceNumber = $this->generateInvoiceNumber();
                
                // Calculate the effective monthly price
                $totalAmount = (float) $productData['amount'];
                $months = (int) $productData['months'];
                $monthlyPrice = $months > 0 ? $totalAmount / $months : 0;
                
                $invoices[] = [
                    'invoice_number' => $invoiceNumber,
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
            Log::error('Error previewing invoice numbers: ' . $e->getMessage());
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
            // Log the incoming request for debugging
            Log::info('Product assignment request received', [
                'customer_id' => $request->customer_id,
                'products_count' => count($request->products ?? []),
                'products_data' => $request->products
            ]);
            
            DB::beginTransaction();
            
            $request->validate([
                'customer_id' => 'required|exists:customers,c_id',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,p_id',
                'products.*.monthly_price' => 'required|numeric|min:0',
                'products.*.billing_cycle_months' => 'required|integer|min:1',
                'products.*.assign_date' => 'required|date',
                'products.*.custom_due_date' => 'required|date'
            ]);

            $customerId = $request->customer_id;
            $products = $request->products;
            
            $assignedProducts = [];
            $invoiceData = [];

            foreach ($products as $productData) {
                // Log each product being processed
                Log::info('Processing product assignment', $productData);
                
                // Create customer product assignment
                // Calculate due_date (always auto-calculated from assign_date + billing_cycle_months)
                $calculatedDueDate = $this->calculateDueDate(
                    $productData['assign_date'], 
                    $productData['billing_cycle_months']
                );
                
                // custom_due_date: from form (either user's custom choice or auto-calculated value)
                $customDueDate = $productData['custom_due_date'];
                
                $customerProduct = CustomerProduct::create([
                    'c_id' => $customerId,
                    'p_id' => $productData['product_id'],
                    'custom_price' => $productData['monthly_price'], // Store total as custom price
                    'assign_date' => $productData['assign_date'],
                    'billing_cycle_months' => $productData['billing_cycle_months'],
                    'due_date' => $calculatedDueDate, // Always auto-calculated
                    'custom_due_date' => $customDueDate, // From form (user's choice or calculated)
                    'status' => 'active',
                    'is_active' => true,
                ]);

                // Store for response
                $assignedProducts[] = $customerProduct;

                // Create invoice record
                $invoice = Invoice::create([
                    'invoice_id' => $this->generateInvoiceNumber(),
                    'cp_id' => $customerProduct->cp_id,
                    'c_id' => $customerId,
                    'issue_date' => $productData['assign_date'],
                    'due_date' => $customDueDate, // Use custom_due_date (from form)
                    'subtotal' => $productData['monthly_price'],
                    'total_amount' => $productData['monthly_price'],
                    'received_amount' => 0,
                    'next_due' => $productData['monthly_price'],
                    'status' => 'unpaid'
                ]);

                $invoiceData[] = $invoice;
            }

            DB::commit();
            
            Log::info('Products assigned successfully', [
                'customer_id' => $customerId,
                'assigned_products_count' => count($assignedProducts)
            ]);

            return redirect()->route('admin.customer-to-products.index', ['customer_id' => $customerId])
                ->with('success', count($products) . ' product(s) assigned successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning products: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to assign products: ' . $e->getMessage())->withInput();
        }
    }

    /** ✏️ Show edit form for customer product */
    public function edit($id)
    {
        try {
            $customerProduct = CustomerProduct::with(['customer', 'product', 'invoices'])->findOrFail($id);
            
            // Extract customer and product for easier access in the view
            $customer = $customerProduct->customer;
            $product = $customerProduct->product;
            
            return view('admin.customer-to-products.edit', compact('customerProduct', 'customer', 'product'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage());
            return redirect()->route('admin.customer-to-products.index')
                ->with('error', 'Failed to load edit form.');
        }
    }

    /** 🔄 Update customer product */
    public function update(Request $request, $id)
    {
        try {
            $customerProduct = CustomerProduct::findOrFail($id);
            
            $request->validate([
                'billing_cycle_months' => 'required|integer|min:1',
                'assign_date' => 'required|date',
                'due_date' => 'nullable|date',
                'status' => 'required|in:active,pending,expired'
            ]);

            $customerProduct->update([
                'custom_price' => $request->total_amount, // Store total as custom price
                'billing_cycle_months' => $request->billing_cycle_months,
                'assign_date' => $request->assign_date,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'is_active' => $request->status === 'active'
            ]);

            // Update associated invoices if they exist
            $customerProduct->invoices()->update([
                'issue_date' => $request->assign_date,
                'due_date' => $request->due_date,
                'subtotal' => $request->total_amount,
                'total_amount' => $request->total_amount,
            ]);

            return redirect()->route('admin.customer-to-products.index', ['customer_id' => $customerProduct->c_id])
                ->with('success', 'Product assignment updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating customer product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update product assignment.')->withInput();
        }
    }

    /** 🔄 Toggle product status (active/expired) */
    public function toggleStatus($id)
    {
        try {
            $customerProduct = CustomerProduct::find($id);
            
            if (!$customerProduct) {
                return redirect()->route('admin.customer-to-products.index')
                    ->with('error', 'Product assignment not found.');
            }

            // Toggle between active and expired
            $newStatus = $customerProduct->status === 'active' ? 'expired' : 'active';
            
            $customerProduct->update([
                'status' => $newStatus,
                'is_active' => $newStatus === 'active' ? 1 : 0,
            ]);

            $action = $newStatus === 'active' ? 'activated' : 'paused';
            
            return redirect()->route('admin.customer-to-products.index')
                ->with('success', "Product {$action} successfully!");

        } catch (\Exception $e) {
            Log::error('Error toggling product status: ' . $e->getMessage());
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

            $productName = $customerProduct->product->name ?? 'Unknown product';
            
            // Soft delete the customer product to preserve for history
            $customerProduct->delete();

            return redirect()->route('admin.customer-to-products.index')
                ->with('success', "Product '{$productName}' removed successfully! (Preserved for history)");

        } catch (\Exception $e) {
            Log::error('Error marking product as deleted: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to remove product.');
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

            $customerProduct->update([
                'billing_cycle_months' => $customerProduct->billing_cycle_months + 1,
                'status' => 'active',
                'is_active' => 1,
            ]);

            return redirect()->route('admin.customer-to-products.index')
                ->with('success', 'Product renewed successfully!');

        } catch (\Exception $e) {
            Log::error('Error renewing product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to renew product.');
        }
    }

    /** 👤 Store new customer via AJAX */
    public function storeCustomer(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'customer_id' => 'required|string|max:50|unique:customers,customer_id',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:500',
            ]);

            $customer = Customer::create([
                'name' => $request->name,
                'customer_id' => $request->customer_id,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'customer' => $customer
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            
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
            $query = $request->get('q');
            
            if (!$query || strlen($query) < 2) {
                return response()->json([]);
            }

            $customers = Customer::where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('customer_id', 'like', "%{$query}%");
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

    /** 🧾 Get customer invoices for AJAX */
    public function getCustomerInvoices($customerId)
    {
        try {
            $invoices = Invoice::where('c_id', $customerId)
                ->with('customerProduct.product')
                ->orderBy('issue_date', 'desc')
                ->get();

            return response()->json($invoices);

        } catch (\Exception $e) {
            Log::error('Error fetching customer invoices: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /** Generate unique invoice number */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = date('Ym');
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
    }

    /** Calculate due date based on assign date and billing cycle months */
    private function calculateDueDate($assignDate, $billingCycleMonths)
    {
        $date = new \DateTime($assignDate);
        $date->modify("+{$billingCycleMonths} months");
        return $date->format('Y-m-d');
    }
}