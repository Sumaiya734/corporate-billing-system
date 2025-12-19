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
use App\Http\Controllers\Admin\BillingReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Customer\CustomersController;
use App\Http\Controllers\Customer\CustomerProductsController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\PayController;
use App\Http\Controllers\Customer\SupportController;
use App\Http\Controllers\Customer\ContactController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;

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
    Route::get('/customer/login', [App\Http\Controllers\Customer\CustomersController::class, 'showLoginForm'])->name('customer.login');
    Route::post('/customer/login', [App\Http\Controllers\Customer\CustomersController::class, 'login'])->name('customer.login.submit');
    Route::post('/customer/logout', [App\Http\Controllers\Customer\CustomersController::class, 'logout'])->name('customer.logout');
    Route::get('/customer/register', [App\Http\Controllers\Customer\CustomersController::class, 'showRegistrationForm'])->name('customer.register');
    Route::post('/customer/register', [App\Http\Controllers\Customer\CustomersController::class, 'register'])->name('customer.register.submit');
    
    // Admin Authentication
    Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// ==================== ADMIN ROUTES ====================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData'])->name('dashboard.refresh');
    
    // ===== BILLING ROUTES =====
    Route::prefix('billing')->name('billing.')->group(function () {
        // Main billing pages
        Route::get('/', [BillingController::class, 'billingInvoices'])->name('index');
        Route::get('/billing-invoices', [BillingController::class, 'billingInvoices'])->name('billing-invoices');
        Route::get('/all-invoices', [BillingController::class, 'allInvoices'])->name('all-invoices');
        
        // Invoice generation and management
        Route::get('/billing-invoices/generate', [BillingController::class, 'generateBillingInvoices'])->name('generate-billing-invoices');
        Route::post('/billing-invoices/store', [BillingController::class, 'storeBillingInvoices'])->name('store-billing-invoices');
        Route::get('/billing-invoices/{id}', [BillingController::class, 'showBillingInvoice'])->name('show-billing-invoice');
        Route::get('/billing-invoices/{id}/download', [BillingController::class, 'downloadBillingInvoice'])->name('download-billing-invoice');
        Route::get('/billing-invoices/{id}/print', [BillingController::class, 'printBillingInvoice'])->name('print-billing-invoice');
        Route::get('/billing-invoices/{id}/confirm', [BillingController::class, 'confirmBillingInvoice'])->name('confirm-billing-invoice');
        Route::post('/billing-invoices/{id}/confirm', [BillingController::class, 'processConfirmBillingInvoice'])->name('process-confirm-billing-invoice');
        
        // Monthly bills
        Route::get('/monthly-bills/{month?}', [MonthlyBillController::class, 'monthlyBills'])->name('monthly-bills');
        Route::get('/monthly-details/{month}', [BillingController::class, 'monthlyDetails'])->name('monthly-details');
        
        // Bill generation and management
        Route::get('/generate-bill/{id}', [BillingController::class, 'generateBill'])->name('generate-bill');
        Route::post('/process-bill-generation/{customerId}', [BillingController::class, 'processBillGeneration'])->name('process-bill-generation');
        Route::get('/view-bill/{id}', [BillingController::class, 'viewBill'])->name('view-bill');
        Route::get('/view-invoice/{id}', [BillingController::class, 'viewBill'])->name('view-invoice');
        Route::get('/edit-bill/{id}', [BillingController::class, 'editBill'])->name('edit-bill');
        
        // Invoice operations
        Route::get('/invoice-html/{invoiceId}', [BillingController::class, 'getInvoiceHtml'])->name('invoice-html');
        Route::post('/record-payment/{invoiceId}', [BillingController::class, 'recordPayment'])->name('record-payment');
        Route::put('/update-invoice/{invoiceId}', [BillingController::class, 'updateInvoice'])->name('update-invoice');
        
        // Payment operations
        Route::get('/invoice-payments/{invoiceId}', [BillingController::class, 'getInvoicePayments'])->name('invoice-payments');
        Route::delete('/payment/{paymentId}', [BillingController::class, 'deletePayment'])->name('payment.delete');
        Route::get('/payment/{paymentId}/edit', [BillingController::class, 'editPayment'])->name('payment.edit');
        Route::put('/payment/{paymentId}', [BillingController::class, 'updatePayment'])->name('payment.update');
        
        // Monthly operations
        Route::post('/store-monthly', [BillingController::class, 'storeMonthly'])->name('store-monthly');
        Route::post('/generate-from-invoices', [BillingController::class, 'generateFromInvoices'])->name('generate-from-invoices');
        Route::post('/generate-monthly-bills', [MonthlyBillController::class, 'generateMonthlyBills'])->name('generate-monthly-bills');
        Route::post('/generate-monthly-bills-all', [MonthlyBillController::class, 'generateMonthlyBillsForAll'])->name('generate-monthly-bills-all');
        Route::post('/close-month', [BillingController::class, 'closeMonth'])->name('close-month');
        Route::post('/confirm-user-payment', [BillingController::class, 'confirmUserPayment'])->name('confirm-user-payment');
        
        // Reports
        Route::get('/reports', [BillingReportController::class, 'index'])->name('reports');
        Route::get('/export-reports', [BillingReportController::class, 'export'])->name('export-reports');
        
        // AJAX endpoints
        Route::get('/invoice-data/{invoiceId}', [BillingController::class, 'getInvoiceData'])->name('invoice-data');
    });

    // ===== MONTHLY BILL ROUTES =====
    Route::prefix('monthly-bills')->name('monthly-bills.')->group(function () {
        Route::get('/', [MonthlyBillController::class, 'index'])->name('index');
        Route::get('/create', [MonthlyBillController::class, 'create'])->name('create');
        Route::post('/', [MonthlyBillController::class, 'store'])->name('store');
        Route::get('/{id}', [MonthlyBillController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [MonthlyBillController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MonthlyBillController::class, 'update'])->name('update');
        Route::delete('/{id}', [MonthlyBillController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [MonthlyBillController::class, 'download'])->name('download');
        Route::get('/{id}/print', [MonthlyBillController::class, 'print'])->name('print');
    });

    // ===== CUSTOMER PRODUCT ROUTES =====
    Route::prefix('customer-products')->name('customer-products.')->group(function () {
        Route::get('/', [CustomerProductController::class, 'index'])->name('index');
        Route::get('/create', [CustomerProductController::class, 'create'])->name('create');
        Route::post('/', [CustomerProductController::class, 'store'])->name('store');
        Route::get('/{id}', [CustomerProductController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CustomerProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomerProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerProductController::class, 'destroy'])->name('destroy');
    });

    // ===== CUSTOMER ROUTES =====
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/next-id', [CustomerController::class, 'getNextCustomerId'])->name('next-id');
        Route::get('/export', [CustomerController::class, 'export'])->name('export');
        Route::post('/store-ajax', [CustomerProductController::class, 'storeCustomer'])->name('store-ajax');
        Route::get('/{id}/billing-history', [CustomerController::class, 'billingHistory'])->name('billing-history');
        Route::get('/{id}/profile', [CustomerController::class, 'profile'])->name('profile');
        Route::patch('/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('toggle-status');
    });
    Route::resource('customers', CustomerController::class);
    
    // ===== CUSTOMER TO PRODUCT ROUTES =====
    Route::prefix('customer-to-products')->name('customer-to-products.')->group(function () {
        Route::get('/', [CustomerProductController::class, 'index'])->name('index');
        Route::get('/create', [CustomerProductController::class, 'create'])->name('create');
        Route::get('/assign', [CustomerProductController::class, 'assign'])->name('assign');
        Route::post('/', [CustomerProductController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CustomerProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomerProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerProductController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [CustomerProductController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ===== PRODUCT ROUTES =====
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/types', [ProductController::class, 'productTypes'])->name('types');
        Route::post('/add-type', [ProductController::class, 'addProductType'])->name('add-type');
    });
    Route::resource('products', ProductController::class);

    // ===== PAYMENT ROUTES =====
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/create', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PaymentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PaymentController::class, 'update'])->name('update');
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
    });

    // ===== PAYMENT DETAILS ROUTES =====
    Route::prefix('payment-details')->name('payment-details.')->group(function () {
        Route::get('/', [PaymentDetailsController::class, 'index'])->name('index');
        Route::get('/create', [PaymentDetailsController::class, 'create'])->name('create');
        Route::post('/', [PaymentDetailsController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentDetailsController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PaymentDetailsController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PaymentDetailsController::class, 'update'])->name('update');
        Route::delete('/{id}', [PaymentDetailsController::class, 'destroy'])->name('destroy');
    });

    // ===== REPORT ROUTES =====
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/billing', [BillingReportController::class, 'index'])->name('billing.index');
        Route::get('/billing/generate', [BillingReportController::class, 'generate'])->name('billing.generate');
        Route::get('/revenue', [ReportController::class, 'revenueReport'])->name('revenue');
        Route::get('/financial-analytics', [ReportController::class, 'financialAnalytics'])->name('financial-analytics');
        Route::get('/customer-statistics', [ReportController::class, 'customerStatistics'])->name('customer-statistics');
        Route::get('/collection-reports', [ReportController::class, 'collectionReports'])->name('collection-reports');
        Route::get('/customers', [ReportController::class, 'customerReport'])->name('customers');
        Route::get('/products', [ReportController::class, 'productReport'])->name('products');
        Route::get('/payments', [ReportController::class, 'paymentReport'])->name('payments');
    });

    // ===== SETTINGS ROUTES =====
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update', [SettingsController::class, 'update'])->name('update');
        Route::get('/admin', [AdminSettingsController::class, 'index'])->name('admin.index');
        Route::post('/admin/update', [AdminSettingsController::class, 'update'])->name('admin.update');
    });
    
    // ===== ADMIN SETTINGS ROUTES (Aliases for backward compatibility) =====
    Route::prefix('admin-settings')->name('admin-settings.')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
        Route::match(['POST', 'PUT'], '/update', [AdminSettingsController::class, 'update'])->name('update');
    });

    // ===== SUPPORT TICKET ROUTES =====
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [AdminSupportController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminSupportController::class, 'show'])->name('show');
        Route::post('/{id}/status', [AdminSupportController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/priority', [AdminSupportController::class, 'updatePriority'])->name('update-priority');
        Route::get('/recent', [AdminSupportController::class, 'getRecentTickets'])->name('recent');
    });
});

// ==================== CUSTOMER ROUTES ====================
Route::prefix('customer')->name('customer.')->middleware(['auth', 'customer'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Customer\CustomersController::class, 'dashboard'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [App\Http\Controllers\Customer\CustomersController::class, 'profile'])->name('profile.index');
    Route::put('/profile/update', [App\Http\Controllers\Customer\CustomersController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [App\Http\Controllers\Customer\CustomersController::class, 'changePassword'])->name('profile.change-password');
    
    // Customer Products
    Route::resource('customer-products', CustomerProductsController::class);
    Route::resource('products', CustomerProductsController::class);
    
    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('download');
        Route::get('/{id}/print', [InvoiceController::class, 'print'])->name('print');
    });
    
    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Customer\PayController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Customer\PayController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Customer\PayController::class, 'store'])->name('store');
        Route::get('/{id}/download', [\App\Http\Controllers\Customer\PayController::class, 'download'])->name('download');
    });
    
   
    
    // ===== SUPPORT ROUTES =====
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('/create', [SupportController::class, 'create'])->name('create');
        Route::post('/', [SupportController::class, 'store'])->name('store');
        Route::get('/{id}', [SupportController::class, 'show'])->name('show');
        Route::get('/faq', [SupportController::class, 'faq'])->name('faq');
    });

    // ===== CONTACT ROUTES =====
    Route::prefix('contact')->name('contact.')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::post('/', [ContactController::class, 'submit'])->name('submit');
        Route::post('/appointment', [ContactController::class, 'scheduleAppointment'])->name('appointment');
        Route::get('/appointment/slots', [ContactController::class, 'getTimeSlots'])->name('slots');
        Route::post('/emergency', [ContactController::class, 'emergencyContact'])->name('emergency');
        Route::get('/brochure', [ContactController::class, 'downloadBrochure'])->name('brochure');
        Route::get('/info', [ContactController::class, 'getContactInfo'])->name('info');
    });

    // ===== PROFILE UPDATE ROUTES =====
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', function() {
            $user = \Illuminate\Support\Facades\Auth::user();
            $customer = \App\Models\Customer::where('user_id', $user->id)->first();
            return view('customer.profile.index', compact('customer'));
        })->name('index');

        Route::put('/update', [\App\Http\Controllers\Customer\CustomersController::class, 'updateProfile'])->name('update');
        Route::post('/change-password', [\App\Http\Controllers\Customer\CustomersController::class, 'changePassword'])->name('change-password');
    });
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