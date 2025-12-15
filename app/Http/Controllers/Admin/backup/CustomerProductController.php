<?php
// app/Http\Controllers\Admin\CustomerProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerProduct;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerProductController extends Controller
{
    /** 🏠 Show all customer products with search */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $status = $request->get('status');
            $productType = $request->get('product_type');

            // Build query with search and filters
            $customersQuery = Customer::with([
                    'customerProducts.product' => function($query) {
                        $query->orderBy('product_type_id', 'desc');
                    }, 
                    'customerProducts.invoices' => function($query) {
                        $query->orderBy('issue_date', 'desc')->take(3); // Limit invoices to last 3
                    }
                ])
                ->whereHas('customerProducts', function($query) use ($search, $status, $productType) {
                    if ($status) {
                        $query->where('status', $status);
                    }
                    
                    if ($productType) {
                        $query->whereHas('product', function($q) use ($productType) {
                            $q->where('product_type', $productType);
                        });
                    }
                })
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('customer_id', 'like', "%{$search}%")
                          ->orWhereHas('customerProducts.product', function($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%");
                          });
                    });
                })
                ->orderBy('name');

            $customers = $customersQuery->paginate(15);
            
            // Calculate statistics using efficient queries
            $totalActiveProducts = CustomerProduct::where('status', 'active')->where('is_active', 1)->count();
            $totalPendingProducts = CustomerProduct::where('status', 'pending')->count();
            $totalExpiredProducts = CustomerProduct::where('status', 'expired')->count();
            $totalPausedProducts = CustomerProduct::where('status', 'paused')->count();
            
            // Calculate total customers with products
            $totalCustomers = Customer::whereHas('customerProducts')->count();
            
            // Calculate active customers
            $activeCustomers = Customer::where('is_active', true)->count();
            
            // Calculate inactive customers
            $inactiveCustomers = Customer::where('is_active', false)->count();
            
            // Calculate customers with due payments
            $customersWithDue = Customer::whereHas('invoices', function($q) {
                $q->whereIn('invoices.status', ['unpaid', 'partial'])->where('invoices.next_due', '>', 0);
            })->count();
            
            // Calculate active products count (already calculated above)
            $activeProducts = $totalActiveProducts;
            
            // Calculate monthly revenue from active customer products
            $monthlyRevenue = CustomerProduct::where('status', 'active')
                ->where('is_active', 1)
                ->get()
                ->sum(function ($cp) {
                    // Calculate the actual monthly amount
                    if ($cp->custom_price !== null && $cp->custom_price > 0) {
                        // Custom price is total for the billing cycle
                        return $cp->custom_price / max(1, $cp->billing_cycle_months);
                    } else {
                        // Use product's monthly price
                        return $cp->product->monthly_price ?? 0;
                    }
                });
            
            // Calculate renewals due (products expiring in the next 30 days)
            $renewalsDue = CustomerProduct::where('status', 'active')
                ->where('is_active', 1)
                ->whereBetween('due_date', [now(), now()->addDays(30)])
                ->count();

            return view('admin.customer-to-products.index', compact(
                'customers', 
                'totalActiveProducts', 
                'totalPendingProducts', 
                'totalExpiredProducts', 
                'totalPausedProducts',
                'totalCustomers', 
                'activeCustomers', 
                'inactiveCustomers', 
                'customersWithDue', 
                'activeProducts', 
                'monthlyRevenue', 
                'renewalsDue'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading customer products: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load customer products.');
        }
    }

    /** ➕ Assign product to customer */
    public function assign(Request $request)
    {
        try {
            $customers = Customer::where('is_active', true)
                ->orderBy('name')
                ->get(['c_id', 'name', 'phone', 'email', 'customer_id', 'address']);
            
            $products = Product::orderBy('product_type_id')->orderBy('monthly_price')->get();
            
            return view('admin.customer-to-products.assign', compact('customers', 'products'));
        } catch (\Exception $e) {
            Log::error('Error loading assign product form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load assignment form.');
        }
    }

    /** 🔍 Get customer invoice data for AJAX requests */
    public function getCustomerInvoices(Request $request)
    {
        try {
            $customerId = $request->get('customer_id');
            
            if (!$customerId) {
                return response()->json(['invoices' => []]);
            }
            
            $invoices = Invoice::whereHas('customerProduct', function ($query) use ($customerId) {
                    $query->where('c_id', $customerId);
                })
                ->select('invoice_id', 'invoice_number', 'issue_date', 'subtotal', 'total_amount', 'received_amount', 'status')
                ->orderBy('issue_date', 'desc')
                ->limit(10) // Limit to last 10 invoices
                ->get();
            
            return response()->json(['invoices' => $invoices]);
        } catch (\Exception $e) {
            Log::error('Error fetching customer invoices: ' . $e->getMessage());
            return response()->json(['invoices' => []], 500);
        }
    }

    /** 💾 Store assigned products */
    public function store(Request $request)
    {
        // Log the request for debugging
        Log::info('Product assignment request received:', $request->all());

        $request->validate([
            'customer_id' => 'required|exists:customers,c_id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,p_id',
            'products.*.billing_cycle_months' => 'required|integer|min:1|max:12',
            'products.*.assign_date' => 'required|date|before_or_equal:today',
            'products.*.due_date_day' => 'required|integer|min:1|max:28',
            'products.*.custom_price' => 'nullable|numeric|min:0',
        ]);

        $customerId = $request->customer_id;
        $products = $request->products;

        try {
            DB::beginTransaction();

            // Check for duplicate products in the same request
            $productIds = collect($products)->pluck('product_id');
            if ($productIds->count() !== $productIds->unique()->count()) {
                DB::rollBack();
                return back()->with('error', 'You cannot assign the same product multiple times in the same request.')
                            ->withInput();
            }

            $assignedProducts = [];
            $errors = [];
            $invoicesGenerated = [];

            foreach ($products as $index => $productData) {
                $productId = $productData['product_id'];
                $product = Product::find($productId);
                
                if (!$product) {
                    $errors[] = "Product not found (ID: {$productId}).";
                    continue;
                }
                
                // Check if product is already assigned to this customer (active or inactive)
                $existingProduct = CustomerProduct::where('c_id', $customerId)
                    ->where('p_id', $productId)
                    ->first();

                if ($existingProduct) {
                    $productName = $product->name;
                    
                    // Check if the existing product is active
                    if ($existingProduct->is_active && $existingProduct->status === 'active') {
                        $errors[] = "Product '{$productName}' is already actively assigned to this customer. Please choose a different product.";
                    } else {
                        $errors[] = "Product '{$productName}' was previously assigned to this customer. Please choose a different product.";
                    }
                    continue;
                }

                // Calculate due_date based on assign_date and due_date_day
                $assignDate = Carbon::parse($productData['assign_date']);
                $dueDateDay = (int) $productData['due_date_day'];
                $billingCycleMonths = (int) $productData['billing_cycle_months'];
                
                // Calculate the first due date
                // If assign date day is after due date day, due date is in next month
                $dueDate = $assignDate->copy();
                
                // Set the day to the specified due date day
                // If the day doesn't exist in the month (e.g., 31st in February), use the last day of the month
                $daysInMonth = $dueDate->daysInMonth;
                $effectiveDueDay = min($dueDateDay, $daysInMonth);
                
                if ($assignDate->day > $effectiveDueDay) {
                    // Assign date is after due date day, so first due date is next month
                    $dueDate->addMonth()->day($effectiveDueDay);
                } else {
                    // Assign date is on or before due date day
                    $dueDate->day($effectiveDueDay);
                }
                
                // Calculate product price
                $customPrice = isset($productData['custom_price']) && $productData['custom_price'] > 0 
                    ? (float) $productData['custom_price'] 
                    : null;
                
                // Calculate effective price
                $effectivePrice = $customPrice !== null 
                    ? $customPrice 
                    : ($product->monthly_price * $billingCycleMonths);
                
                // Generate unique customer-product ID in format: C-YY-XXXX-PYY
                $year = date('y'); // Last 2 digits of year
                $customerSequence = str_pad($customerId, 4, '0', STR_PAD_LEFT);
                $customerProductId = "C-{$year}-{$customerSequence}-P{$productId}";
                
                // Create the product assignment
                $customerProduct = CustomerProduct::create([
                    'c_id' => $customerId,
                    'p_id' => $productId,
                    'custom_price' => $customPrice,
                    'product_price' => $effectivePrice, // Store the total price for this billing cycle
                    'customer_product_id' => $customerProductId,
                    'assign_date' => $assignDate->format('Y-m-d'),
                    'billing_cycle_months' => $billingCycleMonths,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'status' => 'active',
                    'is_active' => 1,
                ]);

                $assignedProducts[] = $customerProduct;
                Log::info("Product assigned successfully:", [
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'cp_id' => $customerProduct->cp_id,
                    'assign_date' => $assignDate->format('Y-m-d'),
                    'due_date' => $dueDate->format('Y-m-d'),
                    'product_price' => $effectivePrice
                ]);
                
                // Automatically generate invoices for current and future billing periods
                $generatedInvoices = $this->generateAutomaticInvoices($customerProduct, $customerId);
                $invoicesGenerated = array_merge($invoicesGenerated, $generatedInvoices);
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()
                    ->with('error', implode(' ', $errors))
                    ->withInput();
            }

            if (empty($assignedProducts)) {
                DB::rollBack();
                return back()
                    ->with('error', 'No products were assigned. Please check your selection.')
                    ->withInput();
            }

            DB::commit();

            $successMessage = count($assignedProducts) . ' product(s) assigned successfully!';
            if (!empty($invoicesGenerated)) {
                $invoiceNumbers = collect($invoicesGenerated)->pluck('invoice_number')->implode(', ');
                $successMessage .= ' ' . count($invoicesGenerated) . ' invoice(s) automatically generated: ' . $invoiceNumbers;
            }
            
            return redirect()->route('admin.customers.index')
                ->with('success', $successMessage)
                ->with('invoices_generated', $invoicesGenerated);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product assignment failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()
                ->with('error', 'Failed to assign products: ' . $e->getMessage())
                ->withInput();
        }
    }

    /** 
     * Automatically generate invoices for a customer product
     * This method generates invoices for current and future billing periods
     */
    private function generateAutomaticInvoices($customerProduct, $customerId)
    {
        $generatedInvoices = [];
        $firstInvoiceId = null;
        
        try {
            Log::info('Starting automatic invoice generation', [
                'customer_product_id' => $customerProduct->cp_id,
                'customer_id' => $customerId
            ]);
            
            $assignDate = Carbon::parse($customerProduct->assign_date);
            $dueDate = Carbon::parse($customerProduct->due_date);
            $billingCycleMonths = $customerProduct->billing_cycle_months;
            
            // Get the customer and product details
            $customer = Customer::find($customerId);
            $product = Product::find($customerProduct->p_id);
            
            if (!$customer || !$product) {
                Log::warning('Customer or product not found for invoice generation', [
                    'customer_id' => $customerId,
                    'product_id' => $customerProduct->p_id
                ]);
                return $generatedInvoices;
            }
            
            // Determine the next billing date (first due date)
            $nextBillingDate = $dueDate->copy();
            
            // Generate invoices for up to 12 months (1 year)
            $monthsToGenerate = 12;
            
            for ($i = 0; $i < $monthsToGenerate; $i++) {
                // Calculate billing date for this period
                $billingDate = $nextBillingDate->copy()->addMonths($i * $billingCycleMonths);
                
                // Stop if billing date is more than 1 year from now
                if ($billingDate->greaterThan(now()->addYear())) {
                    break;
                }
                
                Log::info('Checking billing for period', [
                    'iteration' => $i,
                    'billing_date' => $billingDate->format('Y-m-d'),
                    'billing_cycle_months' => $billingCycleMonths
                ]);
                
                // Check if invoice already exists for this billing period
                $existingInvoice = Invoice::where('cp_id', $customerProduct->cp_id)
                    ->whereDate('issue_date', $billingDate->format('Y-m-d'))
                    ->first();
                
                if (!$existingInvoice) {
                    Log::info('No existing invoice found, creating new one', [
                        'product_id' => $customerProduct->cp_id,
                        'billing_date' => $billingDate->format('Y-m-d')
                    ]);
                    
                    // Generate invoice for this period
                    $invoice = $this->createInvoiceForPeriod($customerProduct, $product, $billingDate);
                    if ($invoice) {
                        $generatedInvoices[] = $invoice;
                        
                        // Store the first invoice ID to link back to customer_product
                        if ($firstInvoiceId === null) {
                            $firstInvoiceId = $invoice->invoice_id;
                        }
                        
                        Log::info('Invoice created successfully', [
                            'invoice_id' => $invoice->invoice_id,
                            'invoice_number' => $invoice->invoice_number
                        ]);
                    } else {
                        Log::warning('Failed to create invoice', [
                            'product_id' => $customerProduct->cp_id,
                            'billing_date' => $billingDate->format('Y-m-d')
                        ]);
                    }
                } else {
                    Log::info('Invoice already exists for this period', [
                        'existing_invoice_id' => $existingInvoice->invoice_id,
                        'invoice_number' => $existingInvoice->invoice_number
                    ]);
                    
                    // If this is the first invoice found, use it
                    if ($firstInvoiceId === null) {
                        $firstInvoiceId = $existingInvoice->invoice_id;
                    }
                }
            }
            
            // Update customer_product with the first invoice_id
            if ($firstInvoiceId !== null) {
                $customerProduct->update(['invoice_id' => $firstInvoiceId]);
                Log::info('Updated customer_product with first invoice_id', [
                    'cp_id' => $customerProduct->cp_id,
                    'invoice_id' => $firstInvoiceId
                ]);
            }
            
            Log::info('Automatic invoice generation completed', [
                'customer_product_id' => $customerProduct->cp_id,
                'invoices_generated' => count($generatedInvoices),
                'first_invoice_id' => $firstInvoiceId
            ]);
        } catch (\Exception $e) {
            Log::error('Automatic invoice generation failed: ' . $e->getMessage());
        }
        
        return $generatedInvoices;
    }
    
    /**
     * Create invoice for a specific billing period
     */
    private function createInvoiceForPeriod($customerProduct, $product, $issueDate)
    {
        try {
            Log::info('Creating invoice for period', [
                'customer_product_id' => $customerProduct->cp_id,
                'product_id' => $product->p_id,
                'issue_date' => $issueDate->format('Y-m-d')
            ]);
            
            // Check if invoice already exists for this period
            $existingInvoice = Invoice::where('cp_id', $customerProduct->cp_id)
                ->whereDate('issue_date', $issueDate->format('Y-m-d'))
                ->first();
                
            if ($existingInvoice) {
                Log::info('Invoice already exists for this period', [
                    'invoice_id' => $existingInvoice->invoice_id,
                    'invoice_number' => $existingInvoice->invoice_number
                ]);
                return $existingInvoice;
            }
            
            // Calculate invoice amount
            // ONLY use custom_price - no calculated price or fallback logic
            if ($customerProduct->custom_price !== null && $customerProduct->custom_price > 0) {
                $subtotal = (float) $customerProduct->custom_price;
            }
            // If no custom price is set, subtotal remains 0 (no fallback to calculated price)
            
            // No service charge or VAT for now - keep it simple
            $serviceCharge = 0.00;
            $vatPercentage = 0.00;
            $vatAmount = 0.00;
            $totalAmount = $subtotal;
            
            Log::info('Calculated invoice amounts', [
                'subtotal' => $subtotal,
                'service_charge' => $serviceCharge,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount
            ]);
            
            // Get previous due amount from unpaid invoices for this customer product
            $previousDue = Invoice::where('cp_id', $customerProduct->cp_id)
                ->where('status', '!=', 'paid')
                ->where('next_due', '>', 0)
                ->sum('next_due');
                
            $totalAmount += $previousDue;
            
            Log::info('Previous due calculation', [
                'previous_due' => $previousDue,
                'total_amount_with_due' => $totalAmount
            ]);
            
            // Generate unique invoice number
            $invoiceNumber = $this->generateInvoiceNumber();
            
            Log::info('Generated invoice number', ['invoice_number' => $invoiceNumber]);
            
            // Create the invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'cp_id' => $customerProduct->cp_id,
                'issue_date' => $issueDate->format('Y-m-d'),
                'due_date' => $issueDate->copy()->addDays(7)->format('Y-m-d'), // Due 7 days after issue
                'previous_due' => $previousDue,
                'service_charge' => $serviceCharge,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => $vatAmount,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'received_amount' => 0,
                'next_due' => $totalAmount,
                'status' => 'unpaid',
                'notes' => "Auto-generated invoice for {$product->name} - Billing cycle: {$customerProduct->billing_cycle_months} month(s)",
                'created_by' => Auth::id() ?? 1 // Use authenticated user or default to 1
            ]);
            
            Log::info("Auto-generated invoice {$invoice->invoice_number} for customer product {$customerProduct->cp_id}");
            
            return $invoice;
        } catch (\Exception $e) {
            Log::error('Failed to create invoice for period: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate unique invoice number with locking to prevent duplicates
     */
    private function generateInvoiceNumber()
    {
        DB::beginTransaction();
        
        try {
            $prefix = 'INV';
            $year = date('Y');
            
            // Get the last invoice with locking
            $lastInvoice = Invoice::whereYear('created_at', $year)
                ->lockForUpdate()
                ->orderBy('invoice_id', 'desc')
                ->first();
            
            if ($lastInvoice && preg_match('/INV-\d{4}-(\d+)/', $lastInvoice->invoice_number, $matches)) {
                $lastNumber = intval($matches[1]);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
            
            DB::commit();
            return $prefix . '-' . $year . '-' . $newNumber;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating invoice number: ' . $e->getMessage());
            // Fallback to timestamp-based number
            return 'INV-' . date('Y') . '-' . time();
        }
    }

    /** 🔍 Check if product already exists for customer */
    public function checkExistingProduct(Request $request)
    {
        try {
            $customerId = $request->get('customer_id');
            $productId = $request->get('product_id');
            
            if (!$customerId || !$productId) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Invalid request parameters.'
                ]);
            }

            $existingProduct = CustomerProduct::where('c_id', $customerId)
                ->where('p_id', $productId)
                ->first();
                
            $productName = Product::find($productId)->name ?? 'Unknown product';

            if ($existingProduct) {
                if ($existingProduct->is_active && $existingProduct->status === 'active') {
                    return response()->json([
                        'exists' => true,
                        'message' => 'This customer already has the "' . $productName . '" product actively assigned. Please choose a different product.'
                    ]);
                } else {
                    return response()->json([
                        'exists' => true,
                        'message' => 'This customer previously had the "' . $productName . '" product. Please choose a different product.'
                    ]);
                }
            }

            return response()->json([
                'exists' => false,
                'message' => 'Product is available for assignment.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking existing product: ' . $e->getMessage());
            return response()->json([
                'exists' => false,
                'message' => 'Error checking product availability.'
            ], 500);
        }
    }

    /** ✏️ Edit existing product */
    public function edit($id)
    {
        try {
            $customerProduct = CustomerProduct::with(['customer', 'product'])->find($id);
            
            if (!$customerProduct) {
                return redirect()->route('admin.customer-to-products.index')
                    ->with('error', 'Product assignment not found.');
            }

            $products = Product::orderBy('product_type_id')->orderBy('monthly_price')->get();
            
            return view('admin.customer-to-products.edit', [
                'customerProduct' => $customerProduct,
                'customer' => $customerProduct->customer,
                'product' => $customerProduct->product,
                'products' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading product edit form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load edit form.');
        }
    }

    /** 🔄 Update product details or status */
    public function update(Request $request, $id)
    {
        $request->validate([
            'assign_date' => 'required|date',
            'due_date_day' => 'required|integer|min:1|max:28',
            'billing_cycle_months' => 'required|integer|min:1|max:12',
            'status' => 'required|in:active,pending,expired,paused',
            'custom_price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            $customerProduct = CustomerProduct::with('product')->find($id);
            
            if (!$customerProduct) {
                return redirect()->route('admin.customer-to-products.index')
                    ->with('error', 'Product assignment not found.');
            }

            // Calculate due_date based on assign_date and due_date_day
            $assignDate = Carbon::parse($request->assign_date);
            $dueDateDay = (int) $request->due_date_day;
            $billingCycleMonths = (int) $request->billing_cycle_months;
            
            // Calculate the due date
            $dueDate = $assignDate->copy();
            $daysInMonth = $dueDate->daysInMonth;
            $effectiveDueDay = min($dueDateDay, $daysInMonth);
            
            if ($assignDate->day > $effectiveDueDay) {
                $dueDate->addMonth()->day($effectiveDueDay);
            } else {
                $dueDate->day($effectiveDueDay);
            }
            
            // Calculate new product price
            $customPrice = isset($request->custom_price) && $request->custom_price > 0 
                ? (float) $request->custom_price 
                : null;
            
            // Calculate effective price - ONLY use custom_price, no fallback
            $effectivePrice = $customPrice !== null 
                ? $customPrice 
                : 0;
            
            // Update the customer product
            $customerProduct->update([
                'assign_date' => $assignDate->format('Y-m-d'),
                'billing_cycle_months' => $billingCycleMonths,
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => $request->status,
                'is_active' => $request->status === 'active' ? 1 : 0,
                'custom_price' => $customPrice,
                'product_price' => $effectivePrice,
            ]);

            // If status changed to active, generate invoices if needed
            if ($request->status === 'active') {
                $this->generateAutomaticInvoices($customerProduct, $customerProduct->c_id);
            }

            DB::commit();

            return redirect()->route('admin.customer-to-products.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update product.');
        }
    }

    /** 🔄 Toggle product status (active/paused) */
    public function toggleStatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $customerProduct = CustomerProduct::find($id);
            
            if (!$customerProduct) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product assignment not found.'
                    ], 404);
                }
                return redirect()->route('admin.customer-to-products.index')
                    ->with('error', 'Product assignment not found.');
            }

            // Toggle between active and paused
            $newStatus = $customerProduct->status === 'active' ? 'paused' : 'active';
            
            $customerProduct->update([
                'status' => $newStatus,
                'is_active' => $newStatus === 'active' ? 1 : 0,
            ]);

            // If activating, generate invoices
            if ($newStatus === 'active') {
                $this->generateAutomaticInvoices($customerProduct, $customerProduct->c_id);
            }

            DB::commit();

            $action = $newStatus === 'active' ? 'resumed' : 'paused';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Product {$action} successfully!",
                    'new_status' => $newStatus
                ]);
            }
            
            return redirect()->route('admin.customer-to-products.index')
                ->with('success', "Product {$action} successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling product status: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to toggle product status.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to toggle product status.');
        }
    }

    /** ❌ Delete a customer's product */
    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $customerProduct = CustomerProduct::find($id);
            
            if (!$customerProduct) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product assignment not found.'
                    ], 404);
                }
                return redirect()->route('admin.customer-to-products.index')
                    ->with('error', 'Product assignment not found.');
            }

            $productName = $customerProduct->product->name ?? 'Unknown product';
            
            // First delete related invoices
            Invoice::where('cp_id', $customerProduct->cp_id)->delete();
            
            // Then delete the customer product
            $customerProduct->delete();

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Product '{$productName}' removed successfully!"
                ]);
            }

            return redirect()->route('admin.customer-to-products.index')
                ->with('success', "Product '{$productName}' removed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete product.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete product.');
        }
    }

    /** ♻️ Renew customer product */
    public function renew($id)
    {
        try {
            DB::beginTransaction();
            
            $customerProduct = CustomerProduct::with('product')->find($id);
            
            if (!$customerProduct) {
                return redirect()->back()->with('error', 'Product assignment not found.');
            }

            // Extend the billing cycle by adding months
            $newDueDate = Carbon::parse($customerProduct->due_date)
                ->addMonths($customerProduct->billing_cycle_months);
            
            $customerProduct->update([
                'due_date' => $newDueDate->format('Y-m-d'),
                'status' => 'active',
                'is_active' => 1,
            ]);

            // Generate invoice for the renewed period
            $this->createInvoiceForPeriod(
                $customerProduct, 
                $customerProduct->product, 
                $newDueDate
            );

            DB::commit();

            return redirect()->route('admin.customer-to-products.index')
                ->with('success', 'Product renewed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error renewing product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to renew product.');
        }
    }

    /**
     * Preview invoice numbers before assignment
     */
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
                
                // Calculate due date (7 days from assign date)
                $dueDate = Carbon::parse($productData['assignDate'])->addDays(7);
                
                $invoices[] = [
                    'invoice_number' => $invoiceNumber,
                    'product_name' => $productData['productName'],
                    'total_amount' => $totalAmount,
                    'months' => $months,
                    'monthly_price' => $monthlyPrice,
                    'assign_date' => $productData['assignDate'],
                    'due_date' => $dueDate->format('Y-m-d')
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

    /** ➕ Store new customer via AJAX */
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
}