<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BillingController;

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

// Admin Protected Routes
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Customer Management
    Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('admin.customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('admin.customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('admin.customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy');
    
    // Billing Routes - FIXED: Using consistent parameter names
    // Provide the expected route name 'admin.billing.invoices' (views reference this)
    Route::get('/billing/billing-invoices', [BillingController::class, 'billingInvoices'])->name('admin.billing.invoices');
    Route::get('/billing/monthly-bills', [BillingController::class, 'monthlyBills'])->name('admin.billing.monthly-bills');
    Route::get('/billing/all-invoices', [BillingController::class, 'allInvoices'])->name('admin.billing.all-invoices');
    // Customer routes
    

    // Use {id} for all routes for consistency
    Route::get('/billing/generate-bill/{id}', [BillingController::class, 'generateBill'])->name('admin.billing.generate-bill');
    Route::get('/billing/view-bill/{id}', [BillingController::class, 'viewBill'])->name('admin.billing.view-bill');
    Route::get('/billing/view-invoice/{id}', [BillingController::class, 'viewInvoice'])->name('admin.billing.view-invoice');
    Route::get('/admin/customers/{id}', [CustomerController::class, 'profile'])->name('admin.customers.show');
Route::get('/admin/billing/view-bill/{id}', [BillingController::class, 'viewBill'])->name('admin.billing.view-bill');
    // Invoice Management Routes
    Route::post('/billing/create-invoice', [BillingController::class, 'createInvoice'])->name('admin.billing.create-invoice');
    Route::put('/billing/update-invoice/{invoiceId}', [BillingController::class, 'updateInvoice'])->name('admin.billing.update-invoice');
    Route::delete('/billing/delete-invoice/{invoiceId}', [BillingController::class, 'deleteInvoice'])->name('admin.billing.delete-invoice');
    Route::get('/billing/export-invoices', [BillingController::class, 'exportInvoices'])->name('admin.billing.export-invoices');
    Route::get('/billing/get-invoice-data/{invoiceId}', [BillingController::class, 'getInvoiceData'])->name('admin.billing.get-invoice-data');
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
    $routes = [
        'admin.billing.monthly-bills' => route('admin.billing.monthly-bills'),
        'admin.billing.all-invoices' => route('admin.billing.all-invoices'),
        'admin.billing.generate-bill' => route('admin.billing.generate-bill', ['id' => 1]),
        'admin.billing.view-bill' => route('admin.billing.view-bill', ['id' => 1]),
        'admin.billing.view-invoice' => route('admin.billing.view-invoice', ['id' => 1]),
    ];
    
    foreach ($routes as $name => $url) {
        echo "<strong>{$name}:</strong> {$url}<br>";
    }
});