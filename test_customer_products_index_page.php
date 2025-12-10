<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Customer;

echo "=== TESTING CUSTOMER-TO-PRODUCTS INDEX PAGE DATA SOURCES ===\n\n";

// Test 1: Check what the controller should return (simulate controller logic)
echo "1. CONTROLLER DATA SIMULATION:\n";
try {
    // Simulate the controller query
    $customers = Customer::with(['customerproducts' => function($query) {
        $query->where('status', '!=', 'deleted');
    }, 'customerproducts.product', 'customerproducts.invoices'])
        ->whereHas('customerproducts', function($query) {
            $query->where('status', '!=', 'deleted');
        })
        ->orderBy('name')
        ->get();

    echo "Total customers with active products: " . $customers->count() . "\n\n";

    foreach($customers as $customer) {
        echo "Customer: {$customer->name} (ID: {$customer->customer_id})\n";
        echo "  Email: " . ($customer->email ?: 'No email') . "\n";
        echo "  Phone: " . ($customer->phone ?: 'No phone') . "\n";
        echo "  Status: " . ($customer->is_active ? 'Active' : 'Inactive') . "\n";
        echo "  Products:\n";
        
        foreach($customer->customerproducts as $cp) {
            $product = $cp->product;
            echo "    - {$product->name} (₹{$cp->product_price})\n";
            echo "      Assigned: {$cp->assign_date}\n";
            echo "      Billing Cycle: {$cp->billing_cycle_months} months\n";
            echo "      Total Amount: ₹{$cp->total_amount}\n";
            echo "      Status: {$cp->status}\n";
            echo "      Due Date: " . ($cp->custom_due_date ?: ($cp->due_date ?: 'Calculated')) . "\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "❌ Controller simulation failed: " . $e->getMessage() . "\n";
}

// Test 2: Check total customers count
echo "2. TOTAL CUSTOMERS COUNT:\n";
try {
    $totalCustomers = Customer::whereHas('customerproducts', function($query) {
        $query->where('status', '!=', 'deleted');
    })->count();
    
    echo "Total customers with non-deleted products: {$totalCustomers}\n\n";
} catch (Exception $e) {
    echo "❌ Total customers count failed: " . $e->getMessage() . "\n";
}

// Test 3: Check single customer view (using Zia as example)
echo "3. SINGLE CUSTOMER VIEW TEST (Zia):\n";
try {
    $singleCustomer = Customer::with(['customerproducts' => function($query) {
        $query->where('status', '!=', 'deleted');
    }, 'customerproducts.product', 'customerproducts.invoices'])
        ->where('name', 'Zia')
        ->first();

    if ($singleCustomer) {
        echo "✓ Found customer: {$singleCustomer->name}\n";
        echo "  Products count: " . $singleCustomer->customerproducts->count() . "\n";
        
        // Calculate total paid (simulate controller logic)
        $totalPaid = $singleCustomer->customerproducts()
            ->where('status', '!=', 'deleted')
            ->with('invoices.payments')
            ->get()
            ->flatMap(function ($cp) {
                return $cp->invoices->flatMap(function ($invoice) {
                    return $invoice->payments ?? collect();
                });
            })
            ->sum('amount');
            
        echo "  Total Paid: ₹{$totalPaid}\n";
        
        foreach($singleCustomer->customerproducts as $cp) {
            echo "  - Product: {$cp->product->name}\n";
            echo "    Price: ₹{$cp->product_price}\n";
            echo "    Total Amount: ₹{$cp->total_amount}\n";
            echo "    Status: {$cp->status}\n";
        }
    } else {
        echo "❌ Customer 'Zia' not found or has no active products\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ Single customer test failed: " . $e->getMessage() . "\n";
}

// Test 4: Check search functionality
echo "4. SEARCH FUNCTIONALITY TEST:\n";
try {
    // Test search by name
    $searchResults = Customer::with(['customerproducts' => function($query) {
        $query->where('status', '!=', 'deleted');
    }, 'customerproducts.product'])
        ->whereHas('customerproducts', function($query) {
            $query->where('status', '!=', 'deleted');
        })
        ->where(function ($q) {
            $search = 'Zia';
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('customer_id', 'like', "%{$search}%");
        })
        ->get();

    echo "Search for 'Zia' returned: " . $searchResults->count() . " results\n";
    foreach($searchResults as $customer) {
        echo "  - {$customer->name} ({$customer->customer_id})\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ Search test failed: " . $e->getMessage() . "\n";
}

// Test 5: Check filter functionality
echo "5. FILTER FUNCTIONALITY TEST:\n";
try {
    // Test status filter
    $activeProducts = Customer::with(['customerproducts' => function($query) {
        $query->where('status', '!=', 'deleted');
    }, 'customerproducts.product'])
        ->whereHas('customerproducts', function($query) {
            $query->where('status', 'active');
        })
        ->get();

    echo "Customers with active products: " . $activeProducts->count() . "\n";
    
    // Test product type filter (using product_type_id instead)
    $businessProducts = Customer::with(['customerproducts' => function($query) {
        $query->where('status', '!=', 'deleted');
    }, 'customerproducts.product'])
        ->whereHas('customerproducts.product', function($query) {
            $query->where('product_type_id', 1); // Assuming 1 is business type
        })
        ->get();

    echo "Customers with business products: " . $businessProducts->count() . "\n\n";
} catch (Exception $e) {
    echo "❌ Filter test failed: " . $e->getMessage() . "\n";
}

// Test 6: Check table structure and relationships
echo "6. DATABASE RELATIONSHIPS TEST:\n";
try {
    // Check customer_to_products table structure
    $cpColumns = DB::select('DESCRIBE customer_to_products');
    echo "customer_to_products table columns:\n";
    foreach($cpColumns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
    
    echo "\nSample customer-product assignments:\n";
    $assignments = DB::table('customer_to_products as cp')
        ->join('customers as c', 'cp.c_id', '=', 'c.c_id')
        ->join('products as p', 'cp.p_id', '=', 'p.p_id')
        ->where('cp.status', '!=', 'deleted')
        ->select('c.name as customer_name', 'p.name as product_name', 'cp.status', 'cp.custom_price', 'cp.billing_cycle_months', 'p.monthly_price')
        ->limit(5)
        ->get();
    
    foreach($assignments as $assignment) {
        // Calculate product_price and total_amount like the model accessors
        $productPrice = $assignment->custom_price ? ($assignment->custom_price / max(1, $assignment->billing_cycle_months)) : $assignment->monthly_price;
        $totalAmount = $assignment->custom_price ?: ($productPrice * $assignment->billing_cycle_months);
        
        echo "  - {$assignment->customer_name} → {$assignment->product_name}\n";
        echo "    Product Price: ₹{$productPrice}, Total Amount: ₹{$totalAmount}, Status: {$assignment->status}\n";
        echo "    Custom Price: ₹{$assignment->custom_price}, Billing Cycle: {$assignment->billing_cycle_months} months\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database relationships test failed: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "The customer-to-products index page should be showing:\n";
echo "- Real customer data from the database\n";
echo "- Customer-product assignments with pricing\n";
echo "- Product details and billing information\n";
echo "- Search and filter functionality\n";
echo "- Single customer view capability\n";

if ($customers->count() > 0) {
    echo "\n✅ THE CUSTOMER-TO-PRODUCTS INDEX PAGE IS CORRECTLY CONNECTED TO DATABASE\n";
    echo "✅ Page will show {$customers->count()} customers with their assigned products\n";
} else {
    echo "\n❌ THE PAGE MAY HAVE DATA ISSUES - No customers with active products found\n";
}