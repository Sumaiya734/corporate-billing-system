<?php
// app/Http/Controllers/Admin/CustomerProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerProduct;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            // Build query with search and filters - FIXED: Use customerproducts instead of activeCustomerproducts
            $customersQuery = Customer::with(['customerProducts.product' => function($query) {
                    $query->orderBy('product_type_id', 'desc');
                }, 'customerProducts.invoices'])
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
            
            $totalActiveProducts = CustomerProduct::where('status', 'active')->where('is_active', 1)->count();
            $totalPendingProducts = CustomerProduct::where('status', 'pending')->count();
            $totalExpiredProducts = CustomerProduct::where('status', 'expired')->count();
            
            // Calculate total customers with products
            $totalCustomers = $customers->count();
            
            // Calculate active products count
            $activeProducts = CustomerProduct::where('status', 'active')->where('is_active', 1)->count();
            
            // Calculate monthly revenue from active customer products
            $monthlyRevenue = CustomerProduct::where('status', 'active')
                ->where('is_active', 1)
                ->get()
                ->sum(function ($cp) {
                    return $cp->product_price;
                });
            
            // Calculate renewals due (products expiring in the next 30 days)
            $renewalsDue = CustomerProduct::where('status', 'active')
                ->where('is_active', 1)
                ->whereBetween('due_date', [now(), now()->addDays(30)])
                ->count();

            return view('admin.customer-to-products.index', compact('customers', 'totalActiveProducts', 'totalPendingProducts', 'totalExpiredProducts', 'totalCustomers', 'activeProducts', 'monthlyRevenue', 'renewalsDue'));
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
                ->get();
            
            return response()->json(['invoices' => $invoices]);
        } catch (\Exception $e) {
            Log::error('Error fetching customer invoices: ' . $e->getMessage());
            return response()->json(['invoices' => []], 500);
        }
    }

    /** 💾 Store assigned products  */
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
                
                // Check if product is already assigned to this customer (active or inactive)
                $existingProduct = CustomerProduct::where('c_id', $customerId)
                    ->where('p_id', $productId)
                    ->first();

                if ($existingProduct) {
                    $productName = Product::find($productId)->name ?? 'Unknown product';
                    
                    // Check if the existing product is active
                    if ($existingProduct->is_active && $existingProduct->status === 'active') {
                        $errors[] = "Product '{$productName}' is already actively assigned to this customer. Please choose a different product.";
                    } else {
                        $errors[] = "Product '{$productName}' was previously assigned to this customer. Please choose a different product.";
                    }
                    continue;
                }

                // Calculate due_date based on assign_date and due_date_day
                $assignDate = $productData['assign_date'];
                $dueDateDay = (int) $productData['due_date_day'];
                $billingCycleMonths = (int) $productData['billing_cycle_months'];
                
                // Calculate the due date as the end of billing period with the specified day
                $assignDateCarbon = \Carbon\Carbon::parse($assignDate);
                $dueDate = $assignDateCarbon->copy()->addMonths($billingCycleMonths);
                $dueDate->day($dueDateDay);
                
                // Generate unique customer-product ID in format: C-YY-XXXX-PYY
                $year = date('y'); // Last 2 digits of year
                $customerSequence = str_pad($customerId, 4, '0', STR_PAD_LEFT);
                $customerProductId = "C-{$year}-{$customerSequence}-P{$productId}";
                
                // Create the product assignment
                $customerProduct = CustomerProduct::create([
                    'c_id' => $customerId,
                    'p_id' => $productId,
                    'custom_price' => $productData['monthly_price'] ?? null,
                    'customer_product_id' => $customerProductId,
                    'assign_date' => $assignDate,
                    'billing_cycle_months' => $billingCycleMonths,
                    'due_date' => $dueDate,
                    'status' => 'active',
                    'is_active' => 1,
                ]);

                $assignedProducts[] = $customerProduct;
                Log::info("Product assigned successfully:", [
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'cp_id' => $customerProduct->cp_id
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
            
            // Calculate the end date for invoice generation (6 months from now)
            $endDate = Carbon::now()->addMonths(6);
            
            // Generate invoices for each billing period
            $currentPeriodStart = $assignDate->copy();
            
            // Generate invoice for the current period if it's due
            $currentMonthStart = Carbon::now()->startOfMonth();
            
            // Generate invoices for up to 6 months
            for ($i = 0; $i < 6; $i++) {
                $invoiceMonth = $currentMonthStart->copy()->addMonths($i);
                
                Log::info('Checking billing for month', [
                    'iteration' => $i,
                    'invoice_month' => $invoiceMonth->format('Y-m')
                ]);
                
                // Check if this product should be billed in this month
                if ($this->shouldBillInMonth($customerProduct, $invoiceMonth)) {
                    Log::info('Product should be billed in this month', [
                        'product_id' => $customerProduct->cp_id,
                        'month' => $invoiceMonth->format('Y-m')
                    ]);
                    
                    // Check if invoice already exists for this period
                    $existingInvoice = Invoice::where('cp_id', $customerProduct->cp_id)
                        ->whereYear('issue_date', $invoiceMonth->year)
                        ->whereMonth('issue_date', $invoiceMonth->month)
                        ->first();
                    
                    if (!$existingInvoice) {
                        Log::info('No existing invoice found, creating new one', [
                            'product_id' => $customerProduct->cp_id,
                            'month' => $invoiceMonth->format('Y-m')
                        ]);
                        
                        // Generate invoice for this period
                        $invoice = $this->createInvoiceForPeriod($customerProduct, $product, $invoiceMonth);
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
                                'month' => $invoiceMonth->format('Y-m')
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
                } else {
                    Log::info('Product should NOT be billed in this month', [
                        'product_id' => $customerProduct->cp_id,
                        'month' => $invoiceMonth->format('Y-m')
                    ]);
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
     * Determine if a customer product should be billed in a specific month
     */
    private function shouldBillInMonth($customerProduct, $billingMonth)
    {
        try {
            $assignDate = Carbon::parse($customerProduct->assign_date);
            $billingCycleMonths = $customerProduct->billing_cycle_months;
            
            Log::info('Checking if product should be billed', [
                'product_id' => $customerProduct->cp_id,
                'assign_date' => $assignDate->format('Y-m-d'),
                'billing_month' => $billingMonth->format('Y-m'),
                'billing_cycle_months' => $billingCycleMonths
            ]);
            
            // Product must be assigned before or during the billing month
            if ($assignDate->greaterThan($billingMonth->copy()->endOfMonth())) {
                Log::info('Product assigned after billing month, not billing', [
                    'assign_date' => $assignDate->format('Y-m-d'),
                    'billing_month_end' => $billingMonth->copy()->endOfMonth()->format('Y-m-d')
                ]);
                return false;
            }
            
            // For monthly billing, always bill if assigned in or before this month
            if ($billingCycleMonths == 1) {
                Log::info('Monthly billing product, should bill');
                return true;
            }
            
            // For other cycles, check if this month is a billing month
            // Special case: If the product was assigned this month, we should bill for it immediately
            if ($assignDate->year == $billingMonth->year && $assignDate->month == $billingMonth->month) {
                Log::info('Product assigned this month, should bill immediately');
                return true;
            }
            
            // For products with longer billing cycles, check if this is a billing month
            // Calculate months difference between assign date and billing month
            $monthsSinceAssign = $assignDate->diffInMonths($billingMonth);
            
            Log::info('Months since assignment calculation', [
                'months_since_assign' => $monthsSinceAssign,
                'billing_cycle_months' => $billingCycleMonths,
                'remainder' => ($monthsSinceAssign % $billingCycleMonths)
            ]);
            
            // Check if this month is a multiple of the billing cycle from the assign date
            $shouldBill = ($monthsSinceAssign % $billingCycleMonths) === 0;
            Log::info('Should bill result', ['result' => $shouldBill]);
            return $shouldBill;
        } catch (\Exception $e) {
            Log::error('Error checking if product should be billed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create invoice for a specific billing period
     */
    private function createInvoiceForPeriod($customerProduct, $product, $billingPeriodEnd)
    {
        try {
            Log::info('Creating invoice for period', [
                'customer_product_id' => $customerProduct->cp_id,
                'product_id' => $product->p_id,
                'billing_period' => $billingPeriodEnd->format('Y-m')
            ]);
            
            // Check if invoice already exists for this period
            $existingInvoice = Invoice::where('cp_id', $customerProduct->cp_id)
                ->whereYear('issue_date', $billingPeriodEnd->year)
                ->whereMonth('issue_date', $billingPeriodEnd->month)
                ->first();
                
            if ($existingInvoice) {
                Log::info('Invoice already exists for this period', [
                    'invoice_id' => $existingInvoice->invoice_id,
                    'invoice_number' => $existingInvoice->invoice_number
                ]);
                return $existingInvoice;
            }
            
            // Calculate amounts
            // Use custom price if set, otherwise use product's standard price
            if ($customerProduct->custom_price !== null) {
                $productAmount = (float) $customerProduct->custom_price;
            } else {
                $productAmount = $product->monthly_price * $customerProduct->billing_cycle_months;
            }
            
            $serviceCharge = 50.00; // Default service charge
            $vatPercentage = 5.00;  // Default VAT
            
            $subtotal = $productAmount + $serviceCharge;
            $vatAmount = $subtotal * ($vatPercentage / 100);
            $totalAmount = $subtotal + $vatAmount;
            
            Log::info('Calculated invoice amounts', [
                'product_amount' => $productAmount,
                'service_charge' => $serviceCharge,
                'vat_amount' => $vatAmount,
                'subtotal' => $subtotal,
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
                'issue_date' => $billingPeriodEnd->format('Y-m-d'),
                'previous_due' => $previousDue,
                'service_charge' => $serviceCharge,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => $vatAmount,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'received_amount' => 0,
                'next_due' => $totalAmount,
                'status' => 'unpaid',
                'notes' => "Auto-generated invoice for {$product->name} - Due for " . $billingPeriodEnd->format('F Y'),
                'created_by' => 1
            ]);
            
            Log::info("Auto-generated invoice {$invoice->invoice_number} for customer product {$customerProduct->cp_id}");
            
            return $invoice;
        } catch (\Exception $e) {
            Log::error('Failed to create invoice for period: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $lastInvoice = Invoice::whereYear('created_at', $year)->latest('invoice_id')->first();

        if ($lastInvoice && preg_match('/INV-\d{4}-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $year . '-' . $newNumber;
    }

    // app/Http/Controllers/Admin/CustomerProductController.php

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
                'customer' => $customerProduct->customer, // Pass customer separately
                'product' => $customerProduct->product,   // Pass product separately
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
            'status' => 'required|in:active,pending,expired',
        ]);

        try {
            $customerProduct = CustomerProduct::find($id);
            
            if (!$customerProduct) {
                return redirect()->route('admin.customer-to-products.index')
                    ->with('error', 'Product assignment not found.');
            }

            // Calculate due_date based on assign_date and due_date_day
            $assignDate = $request->assign_date;
            $dueDateDay = (int) $request->due_date_day;
            $billingCycleMonths = (int) $request->billing_cycle_months;
            
            // Calculate the due date as the end of billing period with the specified day
            $assignDateCarbon = \Carbon\Carbon::parse($assignDate);
            $dueDate = $assignDateCarbon->copy()->addMonths($billingCycleMonths);
            $dueDate->day($dueDateDay);
            
            $customerProduct->update([
                'assign_date' => $assignDate,
                'billing_cycle_months' => $billingCycleMonths,
                'due_date' => $dueDate,
                'status' => $request->status,
                'is_active' => $request->status === 'active' ? 1 : 0,
                'custom_price' => $request->custom_total_amount ?? $customerProduct->custom_price,
            ]);

            return redirect()->route('admin.customer-to-products.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update product.');
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
            $customerProduct->delete();

            return redirect()->route('admin.customer-to-products.index')
                ->with('success', "Product '{$productName}' removed successfully!");

        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
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

    /**
     * Preview invoice numbers before assignment
     * This generates the actual invoice numbers that will be used
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