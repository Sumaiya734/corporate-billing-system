<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CustomerProductController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\MonthlyBillController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentDetailsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Admin\CustomerToProductController;
use App\Http\Controllers\Admin\BillingReportController;

// ==================== PUBLIC ROUTES ====================

// Welcome/Home routes
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/home', [WelcomeController::class, 'index'])->name('home');

// General login redirect
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Authentication routes (outside auth middleware)
Route::middleware('web')->group(function () {
    // Customer Authentication
    Route::get('/customer/login', [CustomerController::class, 'showLoginForm'])->name('customer.login');
    Route::post('/customer/login', [CustomerController::class, 'login'])->name('customer.login.submit');
    Route::post('/customer/logout', [CustomerController::class, 'logout'])->name('customer.logout');

    // Admin Authentication
    Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// Customer search route for product assignment (public for AJAX)
Route::get('/admin/customers/suggestions', [CustomerProductController::class, 'getCustomerSuggestions'])->name('admin.customers.suggestions');

// Storage access for profile images
Route::get('/storage/{path}', function ($path) {
    // Security check to ensure only customer profile images are served
    if (!str_starts_with($path, 'customers/profiles/') && 
        !str_starts_with($path, 'customers/id_cards/')) {
        abort(403, 'Unauthorized access');
    }
    
    // Check if file exists in storage
    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'File not found');
    }
    
    // Return the file
    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*')->name('storage.serve');

// ==================== ADMIN PROTECTED ROUTES ====================

Route::prefix('admin')->middleware(['web', 'auth'])->name('admin.')->group(function () {
    
    // ==================== DASHBOARD ROUTES ====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData'])->name('dashboard.refresh');
    
    // ==================== PRODUCT MANAGEMENT ROUTES ====================
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::get('/types', [ProductController::class, 'productTypes'])->name('types');
        
        // Test/debug routes
        Route::get('/test', function () {
            return view('admin.products.test2');
        })->name('test2');
        Route::get('/debug/{id}', function ($id) {
            $product = \App\Models\Product::where('p_id', $id)->first();
            return response()->json([
                'found' => $product ? true : false,
                'product' => $product,
                'all_products' => \App\Models\Product::select('p_id', 'name')->get()
            ]);
        })->name('debug');
        
        // CRUD operations
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::post('/add-type', [ProductController::class, 'addProductType'])->name('add-type');
        Route::delete('/delete-type/{id}', [ProductController::class, 'deleteProductType'])->name('delete-type');
        Route::post('/delete-type/{id}', [ProductController::class, 'deleteProductType'])->name('delete-type.post'); // For modal submissions
        
        // Specific edit route
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::post('/{id}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
        
        // Show/Update/Delete routes
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });
    
    // ==================== CUSTOMER MANAGEMENT ROUTES ====================
    Route::prefix('customers')->name('customers.')->group(function () {
        // Special routes first
        Route::get('/next-id', [CustomerController::class, 'getNextCustomerId'])->name('next-id');
        Route::get('/export', [CustomerController::class, 'export'])->name('export');
        Route::patch('/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{customer}/billing-history', [CustomerController::class, 'billingHistory'])->name('billing-history');
        Route::get('/{customer}/profile', [CustomerController::class, 'profile'])->name('profile');
        
        // Customer AJAX store for product assignment
        Route::post('/store-ajax', [CustomerController::class, 'storeAjax'])->name('store-ajax');
        
        // Resource routes (must come last)
        Route::resource('/', CustomerController::class)->parameters([
            '' => 'customer'
        ])->names([
            'index' => 'index',
            'create' => 'create',
            'store' => 'store',
            'show' => 'show',
            'edit' => 'edit',
            'update' => 'update',
            'destroy' => 'destroy'
        ]);
    });
    
    // ==================== CUSTOMER PRODUCTS MANAGEMENT ROUTES ====================
    Route::prefix('customer-to-products')->name('customer-to-products.')->group(function () {
        Route::get('/', [CustomerProductController::class, 'index'])->name('index');
        Route::get('/assign', [CustomerProductController::class, 'assign'])->name('assign');
        Route::get('/check-existing', [CustomerProductController::class, 'checkExistingProduct'])->name('check-existing');
        Route::post('/preview-invoice-numbers', [CustomerProductController::class, 'previewInvoiceNumbers'])->name('preview-invoice-numbers');
        Route::post('/store', [CustomerProductController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CustomerProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomerProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerProductController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/renew', [CustomerProductController::class, 'renew'])->name('renew');
        Route::post('/{id}/toggle-status', [CustomerProductController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // ==================== BILLING MANAGEMENT ROUTES ====================
    Route::prefix('billing')->name('billing.')->group(function () {
        // ===== Main Billing Pages =====
        Route::get('/', [BillingController::class, 'billingInvoices'])->name('index');
        Route::get('/billing-invoices', [BillingController::class, 'billingInvoices'])->name('billing-invoices');
        Route::get('/all-invoices', [BillingController::class, 'allInvoices'])->name('all-invoices');
        
        // ===== Monthly Billing =====
        Route::get('/monthly-bills/{month}', [MonthlyBillController::class, 'monthlyBills'])->name('monthly-bills');
        Route::post('/monthly-bills/{month}', [MonthlyBillController::class, 'handleMonthlyBills'])->name('monthly-bills.handle');
        Route::post('/generate-monthly-bills', [MonthlyBillController::class, 'generateMonthlyBills'])->name('generate-monthly-bills');
        Route::post('/generate-monthly-bills-all', [MonthlyBillController::class, 'generateMonthlyBillsForAll'])->name('generate-monthly-bills-all');
        Route::post('/generate-all-invoices', [MonthlyBillController::class, 'generateAllInvoices'])->name('generate-all-invoices');
        Route::post('/close-month', [MonthlyBillController::class, 'closeMonth'])->name('close-month');
        Route::get('/invoice/{invoiceId}/data', [MonthlyBillController::class, 'getInvoiceData'])->name('invoice.data');
        Route::get('/monthly-details/{month}', [BillingController::class, 'monthlyDetails'])->name('monthly-details');
        
        // ===== Payment Management =====
        Route::post('/record-payment/{invoiceId}', [MonthlyBillController::class, 'recordPayment'])->name('record-payment');
        Route::post('/confirm-user-payment', [MonthlyBillController::class, 'confirmUserPayment'])->name('confirm-user-payment');
        Route::get('/invoices/{invoiceId}/payments', [PaymentController::class, 'getInvoicePayments'])->name('invoice-payments');
        Route::get('/invoice/{invoiceId}/payments', [BillingController::class, 'getInvoicePayments'])->name('invoice.payments');
        Route::delete('/payment/{paymentId}', [BillingController::class, 'deletePayment'])->name('payment.delete');
        Route::get('/payment/{paymentId}/edit', [BillingController::class, 'editPayment'])->name('payment.edit');
        Route::put('/payment/{paymentId}', [BillingController::class, 'updatePayment'])->name('payment.update');
        
        // ===== Invoice Generation =====
        Route::post('/generate-month-invoices', [BillingController::class, 'generateMonthInvoices'])->name('generate-month-invoices');
        Route::post('/generate-from-invoices', [BillingController::class, 'generateFromInvoices'])->name('generate-from-invoices');
        Route::get('/generate-bill/{customerId}', [BillingController::class, 'generateBill'])->name('generate-bill');
        Route::post('/process-bill/{customerId}', [BillingController::class, 'processBillGeneration'])->name('process-bill');
        
        // ===== Individual Invoice Views =====
        Route::get('/view-bill/{id}', [BillingController::class, 'viewBill'])->name('view-bill');
        Route::get('/edit-bill/{id}', [BillingController::class, 'editBill'])->name('edit-bill');
        Route::get('/view-invoice/{invoiceId}', [BillingController::class, 'viewInvoice'])->name('view-invoice');
        
        // ===== Invoice Details and Modals =====
        Route::get('/invoice/{invoiceId}/details', [BillingController::class, 'getInvoiceDetails'])->name('invoice-details');
        Route::get('/invoice/{invoiceId}/html', [BillingController::class, 'getInvoiceHtml'])->name('invoice-html');
        
        // ===== Monthly Billing Summary =====
        Route::get('/month-details/{month}', [BillingController::class, 'monthDetails'])->name('month-details');
        Route::post('/store-monthly', [BillingController::class, 'storeMonthly'])->name('store-monthly');
        Route::get('/edit-monthly/{id}', [BillingController::class, 'editMonthly'])->name('edit-monthly');
        Route::put('/update-monthly/{id}', [BillingController::class, 'updateMonthly'])->name('update-monthly');
        Route::delete('/delete-monthly/{id}', [BillingController::class, 'deleteMonthly'])->name('delete-monthly');
        Route::post('/toggle-lock/{id}', [BillingController::class, 'toggleLock'])->name('toggle-lock');
        
        // ===== Customer Billing Details =====
        Route::get('/customer-billing/{c_id}', [BillingController::class, 'customerBillingDetails'])->name('customer-billing');
        
        // ===== Invoice CRUD Operations =====
        Route::post('/create-invoice', [BillingController::class, 'createInvoice'])->name('create-invoice');
        Route::put('/update-invoice/{invoiceId}', [BillingController::class, 'updateInvoice'])->name('update-invoice');
        Route::delete('/delete-invoice/{invoiceId}', [BillingController::class, 'deleteInvoice'])->name('delete-invoice');
        Route::get('/get-invoice-data/{invoiceId}', [BillingController::class, 'getInvoiceData'])->name('get-invoice-data');
        
        // ===== Billing Reports =====
        Route::get('/reports', [BillingReportController::class, 'index'])->name('reports');
        Route::get('/reports/export', [BillingReportController::class, 'export'])->name('export-reports');
    });
    
    // ==================== REPORTS ROUTES ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/revenue', [ReportController::class, 'revenueReport'])->name('revenue');
        Route::get('/financial-analytics', [ReportController::class, 'financialAnalytics'])->name('financial-analytics');
        Route::get('/customer-statistics', [ReportController::class, 'customerStatistics'])->name('customer-statistics');
        Route::get('/collection-reports', [ReportController::class, 'collectionReports'])->name('collection-reports');
    });
    
    // ==================== PAYMENT DETAILS ROUTES ====================
    Route::prefix('payment-details')->name('payment-details.')->group(function () {
        Route::get('/', [PaymentDetailsController::class, 'index'])->name('index');
        Route::get('/export', [PaymentDetailsController::class, 'export'])->name('export');
    });
});

// ==================== CUSTOMER PROTECTED ROUTES ====================
Route::prefix('customer')->middleware(['auth:customer'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('customer.dashboard');
});

// ==================== DEBUG/DEVELOPMENT ROUTES ====================
Route::get('/debug/setup', function () {
    echo "<h3>Debug Setup</h3>";
    
    $admin = \App\Models\User::where('email', 'admin@netbillbd.com')->first();
    if ($admin) {
        echo "✅ Admin user exists: " . $admin->email . "<br>";
        
        if (\Illuminate\Support\Facades\Hash::check('password', $admin->password)) {
            echo "✅ Password 'password' is correct!<br>";
        } else {
            echo "❌ Password 'password' is wrong!<br>";
        }
    } else {
        echo "❌ Admin user not found!<br>";
    }
});

Route::get('/debug/auth', function () {
    echo "<h3>Auth Status</h3>";
    echo "Auth::check(): " . (\Illuminate\Support\Facades\Auth::check() ? 'TRUE' : 'FALSE') . "<br>";
    
    if (\Illuminate\Support\Facades\Auth::check()) {
        $user = \Illuminate\Support\Facades\Auth::user();
        echo "Logged in as: " . $user->email . "<br>";
    } else {
        echo "Not logged in<br>";
    }
});

Route::get('/test', function () {
    return "Test route is working!";
});

Route::get('/debug/customers', function () {
    $customers = \App\Models\Customer::with('user')->get();
    
    echo "<h3>Customers in Database:</h3>";
    foreach ($customers as $customer) {
        echo "Customer ID: " . $customer->id . "<br>";
        echo "User ID: " . $customer->user_id . "<br>";
        echo "Phone: " . ($customer->phone ?? 'NULL') . "<br>";
        echo "Status: " . ($customer->status ?? 'NULL') . "<br>";
        echo "Registration Date: " . ($customer->registration_date ?? 'NULL') . "<br>";
        
        if ($customer->user) {
            echo "User Name: " . $customer->user->name . "<br>";
            echo "User Email: " . $customer->user->email . "<br>";
        } else {
            echo "❌ User not found for this customer!<br>";
        }
        echo "<hr>";
    }
    
    if ($customers->count() === 0) {
        echo "❌ No customers found in database!";
    }
});

Route::get('/debug/check-customer/{id}', function ($id) {
    $customer = \App\Models\Customer::with('user')->find($id);
    
    if ($customer) {
        echo "✅ Customer found: " . $customer->id . "<br>";
        echo "Name: " . ($customer->user->name ?? 'No user') . "<br>";
        echo "Email: " . ($customer->user->email ?? 'No email') . "<br>";
        echo "Phone: " . ($customer->phone ?? 'No phone') . "<br>";
    } else {
        echo "❌ Customer with ID {$id} not found!<br>";
    }
    
    echo "<br>All Customer IDs in database:<br>";
    $allCustomers = \App\Models\Customer::pluck('id')->toArray();
    echo empty($allCustomers) ? "No customers found" : implode(', ', $allCustomers);
});

Route::get('/debug/routes', function () {
    echo "<h3>Billing Routes:</h3>";
    $routeNames = [
        'admin.billing.index',
        'admin.billing.billing-invoices',
        'admin.billing.all-invoices',
        'admin.billing.monthly-bills',
        'admin.billing.generate-monthly-bills',
        'admin.billing.record-payment',
        'admin.billing.confirm-user-payment',
        'admin.billing.generate-from-invoices',
        'admin.billing.generate-bill',
        'admin.billing.view-bill',
        'admin.billing.invoice-html',
        'admin.billing.store-monthly',
    ];

    foreach ($routeNames as $name) {
        try {
            $url = \Illuminate\Support\Facades\Route::has($name) ? route($name, collect(request()->route()?->parameters())->toArray() ?: []) : 'MISSING';
        } catch (\Exception $ex) {
            $url = 'MISSING';
        }
        echo "<strong>{$name}:</strong> {$url}<br>";
    }
});

Route::get('/debug/customer-to-products-routes', function() {
    echo "<h3>Customer Products Routes:</h3>";
    try {
        echo "admin.customer-to-products.index: " . route('admin.customer-to-products.index') . "<br>";
        echo "✅ Route exists!<br>";
    } catch (Exception $e) {
        echo "❌ admin.customer-to-products.index: " . $e->getMessage() . "<br>";
    }
    
    try {
        echo "admin.customer-to-products.assign: " . route('admin.customer-to-products.assign') . "<br>";
        echo "✅ Route exists!<br>";
    } catch (Exception $e) {
        echo "❌ admin.customer-to-products.assign: " . $e->getMessage() . "<br>";
    }
});

Route::get('/debug/payment-routes', function() {
    echo "<h3>Payment Routes Debug:</h3>";
    
    try {
        $url = route('admin.billing.record-payment', ['invoiceId' => 1]);
        echo "✅ admin.billing.record-payment: " . $url . "<br>";
    } catch (Exception $e) {
        echo "❌ admin.billing.record-payment: " . $e->getMessage() . "<br>";
    }
    
    try {
        $url = route('admin.billing.confirm-user-payment');
        echo "✅ admin.billing.confirm-user-payment: " . $url . "<br>";
    } catch (Exception $e) {
        echo "❌ admin.billing.confirm-user-payment: " . $e->getMessage() . "<br>";
    }
    
    try {
        $url = route('admin.billing.invoice-payments', ['invoiceId' => 1]);
        echo "✅ admin.billing.invoice-payments: " . $url . "<br>";
    } catch (Exception $e) {
        echo "❌ admin.billing.invoice-payments: " . $e->getMessage() . "<br>";
    }
});

Route::get('/debug/reports-routes', function() {
    echo "<h3>Reports Routes Debug:</h3>";
    
    $reportRoutes = [
        'admin.reports.index',
        'admin.reports.revenue',
        'admin.reports.financial-analytics',
        'admin.reports.customer-statistics',
        'admin.reports.collection-reports',
        'admin.billing.reports',
        'admin.billing.export-reports'
    ];

    foreach ($reportRoutes as $route) {
        try {
            $url = route($route);
            echo "✅ {$route}: " . $url . "<br>";
        } catch (Exception $e) {
            echo "❌ {$route}: " . $e->getMessage() . "<br>";
        }
    }
});

Route::get('/debug/customer-routes', function() {
    echo "<h3>Customer Routes Debug:</h3>";
    
    $customerRoutes = [
        'admin.customers.index',
        'admin.customers.create',
        'admin.customers.store',
        'admin.customers.show',
        'admin.customers.edit',
        'admin.customers.update',
        'admin.customers.destroy',
        'admin.customers.next-id',
        'admin.customers.export',
        'admin.customers.toggle-status',
        'admin.customers.billing-history',
        'admin.customers.profile',
        'admin.customers.store-ajax'
    ];

    foreach ($customerRoutes as $route) {
        try {
            if ($route === 'admin.customers.show' || $route === 'admin.customers.edit' || 
                $route === 'admin.customers.update' || $route === 'admin.customers.destroy' ||
                $route === 'admin.customers.toggle-status' || $route === 'admin.customers.billing-history' ||
                $route === 'admin.customers.profile') {
                $url = route($route, ['customer' => 1]);
            } else {
                $url = route($route);
            }
            echo "✅ {$route}: " . $url . "<br>";
        } catch (Exception $e) {
            echo "❌ {$route}: " . $e->getMessage() . "<br>";
        }
    }
});

// ==================== FALLBACK ROUTE ====================
Route::fallback(function () {
    if (request()->is('admin/*')) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('welcome');
});