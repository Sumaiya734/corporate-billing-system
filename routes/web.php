<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\CustomerPackageController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Admin\MonthlyBillController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\InvoiceController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Customer Authentication Routes
Route::get('/customer/login', [CustomerController::class, 'showLoginForm'])->name('customer.login');
Route::post('/customer/login', [CustomerController::class, 'login'])->name('customer.login.submit');
Route::post('/customer/logout', [CustomerController::class, 'logout'])->name('customer.logout');

// Admin Authentication Routes
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes - SINGLE CLEAN GROUP
Route::prefix('admin')->middleware(['web', 'auth'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData'])->name('dashboard.refresh');
    
    // Package Management
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{id}', [PackageController::class, 'show'])->name('packages.show');
    Route::put('/packages/{id}', [PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{id}', [PackageController::class, 'destroy'])->name('packages.destroy');
    Route::post('/packages/{id}/toggle-status', [PackageController::class, 'toggleStatus'])->name('packages.toggle-status');

    // Customer Management
    Route::resource('customers', CustomerController::class)->parameters([
        'customers' => 'customer'
    ]);
    Route::patch('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::get('/customers/{customer}/billing-history', [CustomerController::class, 'billingHistory'])->name('customers.billing-history');
    Route::get('/customers/{customer}/profile', [CustomerController::class, 'profile'])->name('customers.profile');
    
    // Customer search route for package assignment
    Route::get('/customers/search', [CustomerPackageController::class, 'searchCustomers'])->name('customers.search');

    // Add export route
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');

    // Customer Packages Management
    Route::get('/customer-to-packages', [CustomerPackageController::class, 'index'])->name('customer-to-packages.index');
    Route::get('/customer-to-packages/assign', [CustomerPackageController::class, 'assign'])->name('customer-to-packages.assign');
    Route::post('/customer-to-packages/store', [CustomerPackageController::class, 'store'])->name('customer-to-packages.store'); 
    Route::get('/customer-to-packages/{id}/edit', [CustomerPackageController::class, 'edit'])->name('customer-to-packages.edit');
    Route::put('/customer-to-packages/{id}', [CustomerPackageController::class, 'update'])->name('customer-to-packages.update');
    Route::delete('/customer-to-packages/{id}', [CustomerPackageController::class, 'destroy'])->name('customer-to-packages.destroy');
    Route::post('/customer-to-packages/{id}/renew', [CustomerPackageController::class, 'renew'])->name('customer-to-packages.renew');
    
    // Billing Routes - CLEANED UP AND FIXED
    Route::prefix('billing')->name('billing.')->group(function () {
        // Main billing pages
        Route::get('/', [BillingController::class, 'billingInvoices'])->name('index');
        Route::get('/billing-invoices', [BillingController::class, 'billingInvoices'])->name('billing-invoices');
        Route::get('/all-invoices', [BillingController::class, 'allInvoices'])->name('all-invoices');
        
        // Monthly billing
        Route::get('/monthly-bills/{month}', [MonthlyBillController::class, 'monthlyBills'])->name('monthly-bills');
        Route::post('/monthly-bills/{month}', [MonthlyBillController::class, 'handleMonthlyBills'])->name('monthly-bills.handle');
        Route::post('/generate-monthly-bills', [MonthlyBillController::class, 'generateMonthlyBills'])->name('generate-monthly-bills');
        Route::get('/invoice/{invoiceId}/data', [MonthlyBillController::class, 'getInvoiceData'])->name('invoice.data');
        Route::post('/send-reminder', [MonthlyBillController::class, 'sendReminder'])->name('send-reminder');
        
        // Payment Routes - FIXED
        
        Route::post('/record-payment/{invoice}', [PaymentController::class, 'recordPayment'])->name('record-payment');
         Route::get('/invoices/{invoiceId}/payments', [PaymentController::class, 'getInvoicePayments'])->name('invoice-payments');
        // Monthly billing summary
        Route::get('/month-details/{month}', [BillingController::class, 'monthDetails'])->name('month-details');
        
        // Invoice generation
        Route::post('/generate-month-invoices', [BillingController::class, 'generateMonthInvoices'])->name('generate-month-invoices');
        Route::post('/generate-from-invoices', [BillingController::class, 'generateFromInvoices'])->name('generate-from-invoices');
        
        // Individual invoice management
        Route::get('/generate-bill/{customerId}', [BillingController::class, 'generateBill'])->name('generate-bill');
        Route::post('/process-bill/{customerId}', [BillingController::class, 'processBillGeneration'])->name('process-bill');
        Route::get('/view-bill/{id}', [BillingController::class, 'viewBill'])->name('view-bill');
        Route::get('/view-invoice/{invoiceId}', [BillingController::class, 'viewInvoice'])->name('view-invoice');
        
        // Invoice details for modal
        Route::get('/invoice/{invoiceId}/details', [BillingController::class, 'getInvoiceDetails'])->name('invoice-details');
        
        // Monthly billing summary management
        Route::post('/store-monthly', [BillingController::class, 'storeMonthly'])->name('store-monthly');
        Route::get('/edit-monthly/{id}', [BillingController::class, 'editMonthly'])->name('edit-monthly');
        Route::put('/update-monthly/{id}', [BillingController::class, 'updateMonthly'])->name('update-monthly');
        Route::delete('/delete-monthly/{id}', [BillingController::class, 'deleteMonthly'])->name('delete-monthly');
        Route::post('/toggle-lock/{id}', [BillingController::class, 'toggleLock'])->name('toggle-lock');
        
        // Customer billing details
        Route::get('/customer-billing/{c_id}', [BillingController::class, 'customerBillingDetails'])->name('customer-billing');
        
        // Additional invoice routes
        Route::post('/create-invoice', [BillingController::class, 'createInvoice'])->name('create-invoice');
        Route::put('/update-invoice/{invoiceId}', [BillingController::class, 'updateInvoice'])->name('update-invoice');
        Route::delete('/delete-invoice/{invoiceId}', [BillingController::class, 'deleteInvoice'])->name('delete-invoice');
        Route::get('/get-invoice-data/{invoiceId}', [BillingController::class, 'getInvoiceData'])->name('get-invoice-data');
    });
});

// Customer Protected Routes
Route::prefix('customer')->middleware(['auth:customer'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('customer.dashboard');
});

// Debug routes
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

// Test route
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

// Debug route to check specific customer
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
        'admin.billing.add-payment', // may not exist; will be reported as MISSING
        'admin.billing.send-reminder',
        'admin.billing.generate-from-invoices',
        'admin.billing.generate-bill',
        'admin.billing.view-bill',
        'admin.billing.store-monthly',
    ];

    foreach ($routeNames as $name) {
        try {
            $url = \Illuminate\Support\Facades\Route::has($name) ? route($name, collect(request()->route()?->parameters())->toArray() ?: []) : 'MISSING';
        } catch (\Exception $ex) {
            // If generating the route fails for any reason, mark as missing
            $url = 'MISSING';
        }
        echo "<strong>{$name}:</strong> {$url}<br>";
    }
});

// Debug route for customer packages
Route::get('/debug/customer-to-packages-routes', function() {
    echo "<h3>Customer Packages Routes:</h3>";
    try {
        echo "admin.customer-to-packages.index: " . route('admin.customer-to-packages.index') . "<br>";
        echo "✅ Route exists!<br>";
    } catch (Exception $e) {
        echo "❌ admin.customer-to-packages.index: " . $e->getMessage() . "<br>";
    }
    
    try {
        echo "admin.customer-to-packages.assign: " . route('admin.customer-to-packages.assign') . "<br>";
        echo "✅ Route exists!<br>";
    } catch (Exception $e) {
        echo "❌ admin.customer-to-packages.assign: " . $e->getMessage() . "<br>";
    }
});

// Debug route to check payment routes
Route::get('/debug/payment-routes', function() {
    echo "<h3>Payment Routes Debug:</h3>";
    
    try {
        $url = route('admin.billing.add-payment');
        echo "✅ admin.billing.add-payment: " . $url . "<br>";
    } catch (Exception $e) {
        echo "❌ admin.billing.add-payment: " . $e->getMessage() . "<br>";
    }
    
    try {
        $url = route('admin.billing.invoice-payments', ['invoiceId' => 1]);
        echo "✅ admin.billing.invoice-payments: " . $url . "<br>";
    } catch (Exception $e) {
        echo "❌ admin.billing.invoice-payments: " . $e->getMessage() . "<br>";
    }
});