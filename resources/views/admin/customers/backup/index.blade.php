@extends('layouts.admin')

@section('title', 'All Customers - NetBill BD')

@section('content')

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-users me-2 text-primary"></i>Customer Management
            </h2>
            <p class="text-muted mb-0">Manage all customer accounts, products, and billing information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-user-plus me-2"></i>Add Customer
            </a>
            <a href="{{ route('admin.customer-to-products.assign') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-user-tag me-2"></i>Assign product
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-2"></i>Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Export CSV</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i>Print Report</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-sync-alt me-2"></i>Refresh Data</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2 fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Customer Statistics Dashboard -->
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 fw-semibold">Total Customers</h6>
                            <h2 class="mb-0 fw-bold text-dark">{{ $totalCustomers }}</h2>
                            <p class="text-success small mb-0 mt-1">
                                <i class="fas fa-arrow-up me-1"></i>12% increase
                            </p>
                        </div>
                        <div class="stat-icon bg-primary-light rounded-circle p-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 fw-semibold">Active Customers</h6>
                            <h2 class="mb-0 fw-bold text-dark">{{ $activeCustomers }}</h2>
                            <p class="text-success small mb-0 mt-1">
                                <i class="fas fa-user-check me-1"></i>{{ number_format(($activeCustomers/$totalCustomers)*100, 1) }}% active rate
                            </p>
                        </div>
                        <div class="stat-icon bg-success-light rounded-circle p-3">
                            <i class="fas fa-user-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 fw-semibold">Due Payments</h6>
                            <h2 class="mb-0 fw-bold text-dark">{{ $customersWithDue }}</h2>
                            <p class="text-danger small mb-0 mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>Requires attention
                            </p>
                        </div>
                        <div class="stat-icon bg-danger-light rounded-circle p-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 fw-semibold">Total Revenue</h6>
                            <h2 class="mb-0 fw-bold text-dark">৳{{ number_format($customers->sum('total_monthly_bill') ?? 0, 2) }}</h2>
                            <p class="text-success small mb-0 mt-1">
                                <i class="fas fa-chart-line me-1"></i>Monthly recurring
                            </p>
                        </div>
                        <div class="stat-icon bg-warning-light rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 d-flex align-items-center">
                <i class="fas fa-filter me-2 text-primary"></i>Advanced Search & Filter
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.customers.index') }}" id="searchForm">
                <div class="row g-3">
                    <div class="col-lg-5">
                        <label class="form-label small fw-semibold text-muted mb-1">Search Customers</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-primary"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control border-start-0 border-end-0" 
                                   placeholder="Name, email, phone, customer ID..." 
                                   value="{{ request('search') }}"
                                   id="searchInput">
                            <button class="input-group-text bg-white border-start-0" type="button" id="clearSearch">
                                <i class="fas fa-times text-muted"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Status Filter</label>
                        <select name="status" class="form-select shadow-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Sort By</label>
                        <select name="sort" class="form-select shadow-sm">
                            <option value="name">Name A-Z</option>
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="due">Due Amount</option>
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary flex-fill shadow-sm">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            @if(request()->has('search') || request()->has('status') || request()->has('sort'))
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Filter Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.customers.index') }}" 
                           class="btn btn-sm btn-outline-primary filter-btn {{ !request()->has('filter') ? 'active' : '' }}">
                            <i class="fas fa-list me-1"></i>All Customers
                            <span class="badge bg-primary ms-1">{{ $totalCustomers }}</span>
                        </a>
                        <a href="{{ route('admin.customers.index', ['filter' => 'active']) }}" 
                           class="btn btn-sm btn-outline-success filter-btn">
                            <i class="fas fa-user-check me-1"></i>Active
                            <span class="badge bg-success ms-1">{{ $activeCustomers }}</span>
                        </a>
                        <a href="{{ route('admin.customers.index', ['filter' => 'inactive']) }}" 
                           class="btn btn-sm btn-outline-secondary filter-btn">
                            <i class="fas fa-user-slash me-1"></i>Inactive
                            <span class="badge bg-secondary ms-1">{{ $inactiveCustomers }}</span>
                        </a>
                        <a href="{{ route('admin.customers.index', ['filter' => 'with_due']) }}" 
                           class="btn btn-sm btn-outline-danger filter-btn">
                            <i class="fas fa-exclamation-triangle me-1"></i>With Due
                            <span class="badge bg-danger ms-1">{{ $customersWithDue }}</span>
                        </a>
                        <a href="{{ route('admin.customers.index', ['filter' => 'new']) }}" 
                           class="btn btn-sm btn-outline-info filter-btn">
                            <i class="fas fa-star me-1"></i>New This Week
                        </a>
                        <a href="{{ route('admin.customers.index', ['filter' => 'high_value']) }}" 
                           class="btn btn-sm btn-outline-warning filter-btn">
                            <i class="fas fa-crown me-1"></i>High Value
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-list text-primary fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0 fw-bold">Customer Directory</h5>
                    <p class="text-muted small mb-0">Showing {{ $customers->total() }} customers in system</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <div class="text-muted small">Page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}</div>
                    <div class="text-primary fw-semibold">{{ $customers->count() }} visible</div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Options
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-columns me-2"></i>Customize View</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Export Data</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i>Quick Preview</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sync-alt me-2"></i>Refresh</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="customersTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Customer Information</th>
                                <th>Active Products</th>
                                <th class="text-center">Billing</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Registration</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $productColors = [
                                    'primary', 'success', 'warning', 'danger', 'info', 
                                    'secondary', 'purple', 'teal', 'indigo', 'pink'
                                ];
                            @endphp
                            @foreach($customers as $customer)
                            @php
                                // Get active products with relationships
                                $activeProducts = $customer->customerproducts
                                    ->where('status', 'active')
                                    ->where('is_active', 1)
                                    ->filter(function($cp) {
                                        return $cp->product !== null;
                                    });
                                
                                // Calculate monthly total using custom price if available
                                $monthlyTotal = $activeProducts->sum(function($cp) {
                                    $price = $cp->product_price ?? $cp->product->monthly_price ?? 0;
                                    return $price;
                                });
                                
                                // Check for due payments
                                $hasDue = $customer->invoices()
                                    ->whereIn('invoices.status', ['unpaid', 'partial'])
                                    ->exists();
                                
                                $totalDue = $customer->invoices()
                                    ->whereIn('invoices.status', ['unpaid', 'partial'])
                                    ->sum(DB::raw('invoices.total_amount - invoices.received_amount'));
                                
                                $isNew = $customer->created_at->gt(now()->subDays(7));
                                $initialLetter = strtoupper(substr($customer->name, 0, 1));
                                
                                // Determine customer tier based on monthly total
                             
                            @endphp
                            <tr class="customer-row {{ $hasDue ? 'payment-due' : '' }} {{ $isNew ? 'new-customer' : '' }} {{ !$customer->is_active ? 'inactive-customer' : '' }}">
                                
                                <!-- Customer Information Column -->
                                <td class="ps-4">
                                    <div class="d-flex align-items-start">
                                        <div class="customer-avatar me-3 position-relative">
                                            <div class="avatar-circle bg-gradient-primary text-white">
                                                {{ $initialLetter }}
                                            </div>
                                            @if($isNew)
                                                <span class="new-badge position-absolute top-0 start-100 translate-middle badge bg-info rounded-pill" style="font-size: 0.6rem;">
                                                    NEW
                                                </span>
                                            @endif
                                           
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <a href="{{ route('admin.customers.show', $customer->c_id) }}" class="text-decoration-none customer-link" Target="_blank">
                                                    <strong class="me-2 text-dark customer-name">{{ $customer->name }}</strong>
                                                </a>
                                                @if(!$customer->is_active)
                                                    <span class="badge bg-secondary badge-sm">Inactive</span>
                                                @endif
                                                
                                            </div>
                                            <div class="customer-details">
                                                <div class="text-muted small mb-1">
                                                    <i class="fas fa-id-card me-1"></i>
                                                    <span class="fw-medium customer-id">{{ $customer->customer_id }}</span>
                                                </div>
                                                <div class="text-muted small mb-1">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    {{ $customer->email ?? 'No email' }}
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="fas fa-phone me-1"></i>
                                                    {{ $customer->phone ?? 'No phone' }}
                                                    @if($customer->address)
                                                        <span class="ms-3">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            {{ Str::limit($customer->address, 20) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Products Column - Now with compact display -->
                                <td>
                                    @if($activeProducts->count() > 0)
                                        <div class="products-container">
                                            @foreach($activeProducts as $index => $cp)
                                            <div class="product-item">
                                                <div class="product-name" title="{{ $cp->product->name ?? 'Unknown Product' }}">
                                                    {{ Str::limit($cp->product->name ?? 'Unknown Product', 25) }}
                                                </div>
                                                <div class="product-price">
                                                    @php
                                                        $price = $cp->product_price ?? $cp->product->monthly_price ?? 0;
                                                    @endphp
                                                    ৳{{ number_format($price, 2) }}
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-2">
                                            <div class="empty-state-icon mb-1">
                                                <i class="fas fa-box-open text-muted opacity-25"></i>
                                            </div>
                                            <small class="text-muted">No products</small>
                                        </div>
                                    @endif
                                </td>

                                <!-- Billing Column -->
                                <td class="text-center">
                                    <div class="billing-card">
                                        <div class="monthly-bill text-center mb-3">
                                            <div class="bill-amount">
                                                <h3 class="mb-0 fw-bold text-success">৳{{ number_format($monthlyTotal, 2) }}</h3>
                                                <small class="text-muted">Monthly Recurring</small>
                                            </div>
                                        </div>
                                        
                                        @if($hasDue && $totalDue > 0)
                                            <div class="due-alert alert alert-danger border-0 shadow-sm py-2 px-3 mb-0">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <i class="fas fa-exclamation-circle me-2"></i>
                                                        <strong class="small">৳{{ number_format($totalDue, 2 ) }} Due</strong>
                                                    </div>
                                                    <a href="#" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @elseif($monthlyTotal > 0)
                                            <div class="payment-status">
                                                <div class="paid-badge bg-success-light text-success p-2 rounded text-center">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    <small class="fw-semibold">All Paid</small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="no-bill text-muted small">
                                                <i class="fas fa-ban me-1"></i>
                                                No billing
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status Column -->
                                <td class="text-center">
                                    <div class="status-container">
                                        <div class="status-badge mb-2">
                                            <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }} rounded-pill px-3 py-2">
                                                <i class="fas fa-{{ $customer->is_active ? 'check-circle' : 'pause-circle' }} me-1"></i>
                                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        <div class="status-indicators">
                                            @if($hasDue)
                                                <div class="status-item danger">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <small class="fw-semibold">Payment Due</small>
                                                </div>
                                            @endif
                                            @if($isNew)
                                                <div class="status-item info">
                                                    <i class="fas fa-bolt me-1"></i>
                                                    <small class="fw-semibold">New Customer</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Registration Column -->
                                <td class="text-center">
                                    <div class="registration-card text-center">
                                        <div class="date-display">
                                            <div class="date-icon mb-2">
                                                <div class="icon-circle bg-primary-light text-primary">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                            </div>
                                            <div class="date-info">
                                                <div class="date fw-bold text-dark">
                                                    {{ $customer->created_at->format('M j, Y') }}
                                                </div>
                                                <small class="text-muted d-block">{{ $customer->created_at->diffForHumans() }}</small>
                                                <!-- <div class="duration-badge bg-light text-muted mt-2 p-1 rounded">
                                                    <small>{{ $customer->created_at->diffInDays(now()) }} days</small>
                                                </div> -->
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Actions Column -->
                                <td class="text-center pe-4">
                                    <div class="action-menu">
                                        <div class="btn-group-vertical">
                                            <!-- View Details -->
                                            <a href="{{ route('admin.customers.show', $customer->c_id) }}" 
                                            class="btn btn-sm btn-outline-primary mb-2 action-btn shadow-sm" 
                                            title="View Details"
                                            data-bs-toggle="tooltip" Target="_blank">
                                                <i class="fas fa-eye"></i>
                                                <span class="d-block small mt-1">View</span>
                                            </a>

                                            <!-- Edit Customer -->
                                            <a href="{{ route('admin.customers.edit', $customer->c_id) }}" 
                                            class="btn btn-sm btn-outline-warning mb-2 action-btn shadow-sm" 
                                            title="Edit Customer"
                                            data-bs-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                                <span class="d-block small mt-1">Edit</span>
                                            </a>

                                            <!-- Deactivate Customer Button -->
                                            <form action="{{ route('admin.customers.toggle-status', $customer->c_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-{{ $customer->is_active ? 'warning' : 'success' }} mb-2 action-btn shadow-sm" 
                                                        title="{{ $customer->is_active ? 'Deactivate Customer' : 'Activate Customer' }}"
                                                        onclick="return confirm('Are you sure you want to {{ $customer->is_active ? 'deactivate' : 'activate' }} this customer?')">
                                                    <i class="fas fa-{{ $customer->is_active ? 'ban' : 'check' }}"></i>
                                                    <span class="d-block small mt-1">{{ $customer->is_active ? 'Deactivate' : 'Activate' }}</span>
                                                </button>
                                            </form>

                                            <!-- Delete Customer Button -->
                                            <button class="btn btn-sm btn-outline-danger action-btn shadow-sm delete-customer-btn"
                                                    title="Delete Customer"
                                                    data-customer-id="{{ $customer->c_id }}"
                                                    data-customer-name="{{ $customer->name }}">
                                                <i class="fas fa-trash"></i>
                                                <span class="d-block small mt-1">Delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($customers->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-4 pb-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="text-muted small mb-2 mb-md-0">
                                Showing <strong>{{ $customers->firstItem() }}</strong> to <strong>{{ $customers->lastItem() }}</strong> of <strong>{{ $customers->total() }}</strong> customers
                            </div>
                            <nav aria-label="Customer pagination" class="pagination-container">
                                {{ $customers->appends(request()->query())->links('pagination.bootstrap-5') }}
                            </nav>
                        </div>
                    </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="empty-state-icon mb-4">
                        <i class="fas fa-users fa-5x text-muted opacity-10"></i>
                    </div>
                    <h3 class="text-muted mb-3">No Customers Found</h3>
                    <p class="text-muted mb-4">
                        @if(request()->has('search') || request()->has('status') || request()->has('filter'))
                            No customers match your current search criteria.
                        @else
                            Get started by adding your first customer to the system.
                        @endif
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-user-plus me-2"></i>Add First Customer
                        </a>
                        @if(request()->has('search') || request()->has('status') || request()->has('filter'))
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-lg shadow-sm">
                                <i class="fas fa-times me-2"></i>Clear Filters
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Customer Insights Footer -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="mb-3 fw-semibold">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>Customer Insights
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="insight-icon bg-primary-light rounded-circle p-2 me-3">
                                    <i class="fas fa-user-check text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Most Common</div>
                                    <div class="fw-bold">{{ $customers->count() > 0 ? $customers->first()->name : 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="insight-icon bg-success-light rounded-circle p-2 me-3">
                                    <i class="fas fa-money-bill-wave text-success"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Average Monthly Bill</div>
                                    <div class="fw-bold">৳{{ number_format($customers->avg('total_monthly_bill') ?? 0, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="insight-icon bg-info-light rounded-circle p-2 me-3">
                                    <i class="fas fa-chart-line text-info"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Growth Rate</div>
                                    <div class="fw-bold text-success">+12.5%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Include Delete Confirmation Modal -->
<x-delete-confirmation-modal />

<style>
    /* Modern Color System */
    :root {
        --primary: #4361ee;
        --secondary: #6c757d;
        --success: #06d6a0;
        --warning: #ffd166;
        --danger: #ef476f;
        --info: #118ab2;
        --purple: #7209b7;
        --teal: #06d6a0;
        --indigo: #3a0ca3;
        --pink: #f72585;
        --light: #f8f9fa;
        --dark: #212529;
        
        /* Light variants */
        --primary-light: rgba(67, 97, 238, 0.1);
        --success-light: rgba(6, 214, 160, 0.1);
        --warning-light: rgba(255, 209, 102, 0.1);
        --danger-light: rgba(239, 71, 111, 0.1);
        --info-light: rgba(17, 138, 178, 0.1);
        --purple-light: rgba(114, 9, 183, 0.1);
        --teal-light: rgba(6, 214, 160, 0.1);
        --indigo-light: rgba(58, 12, 163, 0.1);
        --pink-light: rgba(247, 37, 133, 0.1);
    }

    /* Enhanced Card Styling */
    .card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }

    .hover-lift {
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
    }

    /* Stat Icons */
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .stat-icon:hover {
        transform: scale(1.1);
    }

    /* Customer Avatar */
    .avatar-circle {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        background: linear-gradient(135deg, var(--primary) 0%, #3a0ca3 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }

    .customer-avatar {
        position: relative;
    }

    .new-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.1); }
        100% { transform: translate(-50%, -50%) scale(1); }
    }

    /* Simplified Product Display */
    .product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 10px;
        margin-bottom: 6px;
        background-color: rgba(0,0,0,0.03);
        border-radius: 6px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .product-item:hover {
        background-color: rgba(0,0,0,0.06);
        transform: translateX(3px);
    }

    .product-name {
        font-weight: 500;
        color: #495057;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-right: 8px;
    }

    .product-price {
        font-weight: 600;
        color: var(--success);
        white-space: nowrap;
    }

    /* Color Classes for Products */
    .bg-primary-light { background-color: var(--primary-light) !important; }
    .bg-success-light { background-color: var(--success-light) !important; }
    .bg-warning-light { background-color: var(--warning-light) !important; }
    .bg-danger-light { background-color: var(--danger-light) !important; }
    .bg-info-light { background-color: var(--info-light) !important; }
    .bg-purple-light { background-color: var(--purple-light) !important; }
    .bg-teal-light { background-color: var(--teal-light) !important; }
    .bg-indigo-light { background-color: var(--indigo-light) !important; }
    .bg-pink-light { background-color: var(--pink-light) !important; }

    .border-primary { border-color: var(--primary) !important; }
    .border-success { border-color: var(--success) !important; }
    .border-warning { border-color: var(--warning) !important; }
    .border-danger { border-color: var(--danger) !important; }
    .border-info { border-color: var(--info) !important; }
    .border-purple { border-color: var(--purple) !important; }
    .border-teal { border-color: var(--teal) !important; }
    .border-indigo { border-color: var(--indigo) !important; }
    .border-pink { border-color: var(--pink) !important; }

    .text-primary { color: var(--primary) !important; }
    .text-success { color: var(--success) !important; }
    .text-warning { color: var(--warning) !important; }
    .text-danger { color: var(--danger) !important; }
    .text-info { color: var(--info) !important; }
    .text-purple { color: var(--purple) !important; }
    .text-teal { color: var(--teal) !important; }
    .text-indigo { color: var(--indigo) !important; }
    .text-pink { color: var(--pink) !important; }

    /* Product Icon */
    .product-icon .icon-circle {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    /* Table Styling */
    .table {
        --bs-table-bg: transparent;
    }

    .table th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
    }

    .table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Customer Row States */
    .customer-row {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .customer-row:hover {
        background: linear-gradient(135deg,rgba(248, 250, 252, 0.64) 0%, #f1f5f9 100%);
        border-left-color: var(--primary);
        box-shadow: inset 4px 0 0 var(--primary);
    }

    .payment-due {
        background: linear-gradient(135deg, #fff5f7 0%,rgba(201, 120, 120, 0.52) 100%);
        border-left-color: var(--danger) !important;
    }

    .payment-due:hover {
        background: linear-gradient(135deg, #ffe4e6 0%,rgba(254, 205, 210, 0.8) 100%);
    }

    .new-customer {
        background: linear-gradient(135deg, #f0f9ff 0%,rgba(224, 242, 254, 0.5) 100%);
        border-left-color: var(--info) !important;
    }

        .new-customer:hover {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        }

    .inactive-customer {
        opacity: 0.7;
        background-color: #f8f9fa;
    }

    /* Action Buttons */
    .action-btn {
        width: 50px;
        border-radius: 6px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0;
        font-size: 0.8rem;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Customer Name Link */
    .customer-link:hover .customer-name {
        color: var(--primary) !important;
        text-decoration: underline;
    }

    /* Badge Styling */
    .badge-sm {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
        font-weight: 500;
    }

    .tier-badge {
        animation: glow 2s infinite alternate;
    }

    @keyframes glow {
        from { box-shadow: 0 0 5px rgba(255, 193, 7, 0.5); }
        to { box-shadow: 0 0 10px rgba(255, 193, 7, 0.8); }
    }

    /* Icon Circles */
    .icon-circle {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    /* Status Items */
    .status-item {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        margin-top: 0.3rem;
        font-size: 0.7rem;
    }

    .status-item.danger {
        background-color: var(--danger-light);
        color: var(--danger);
    }

    .status-item.info {
        background-color: var(--info-light);
        color: var(--info);
    }

    /* Bill Amount Animation */
    .bill-amount h3 {
        transition: all 0.3s ease;
        font-size: 1.2rem;
    }

    .bill-amount:hover h3 {
        transform: scale(1.05);
        color: var(--success) !important;
    }

    /* Filter Buttons */
    .filter-btn {
        border-radius: 20px;
        padding: 0.4rem 0.8rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        font-size: 0.85rem;
    }

    .filter-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .filter-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Due Alert */
    .due-alert {
        animation: shake 0.5s ease-in-out;
        border-left: 4px solid var(--danger);
        padding: 0.5rem 0.75rem;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-3px); }
        75% { transform: translateX(3px); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem !important;
        }
        
        .avatar-circle {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .product-item {
            padding: 4px 8px;
            margin-bottom: 4px;
            font-size: 0.8rem;
        }
        
        .action-btn {
            width: 40px;
            padding: 0.4rem 0;
            font-size: 0.7rem;
        }
        
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .bill-amount h3 {
            font-size: 1rem;
        }
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Empty State */
    .empty-state-icon {
        opacity: 0.3;
    }

    /* Pagination Styling */
    .pagination .page-link {
        border-radius: 6px;
        margin: 0 1px;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-weight: 500;
        padding: 0.3rem 0.7rem;
        font-size: 0.9rem;
    }

    .pagination .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    /* Insight Icons */
    .insight-icon {
        transition: all 0.3s ease;
        width: 36px;
        height: 36px;
    }

    .insight-icon:hover {
        transform: rotate(15deg) scale(1.1);
    }

    /* Tooltip Customization */
    .tooltip {
        --bs-tooltip-bg: var(--primary);
        --bs-tooltip-border-radius: 6px;
        --bs-tooltip-font-size: 0.8rem;
    }

    /* Loading Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .customer-row {
        animation: fadeIn 0.5s ease-out forwards;
    }

    /* Compact product container */
    .products-container {
        max-height: 120px;
        overflow-y: auto;
        padding-right: 5px;
    }

    /* Custom scrollbar for products */
    .products-container::-webkit-scrollbar {
        width: 4px;
    }

    .products-container::-webkit-scrollbar-thumb {
        background: #adb5bd;
        border-radius: 2px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Clear search input
    document.getElementById('clearSearch')?.addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('searchForm').submit();
    });

    // Auto-submit form when filters change
    const filters = ['statusFilter', 'sortSelect'];
    filters.forEach(filterId => {
        const filter = document.getElementById(filterId);
        if (filter) {
            filter.addEventListener('change', function() {
                document.getElementById('searchForm').submit();
            });
        }
    });

    // Real-time search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    document.getElementById('searchForm').submit();
                }
            }, 300);
        });
    }

    // Delete Customer with modal confirmation
    document.body.addEventListener('click', function(e) {
        const delBtn = e.target.closest('.delete-customer-btn');
        if (!delBtn) return;
        
        const customerId = delBtn.getAttribute('data-customer-id');
        const customerName = delBtn.getAttribute('data-customer-name');
        
        const message = `Are you sure you want to delete <strong>"${customerName}"</strong>?<br><small class="text-danger">All associated invoices, payments, and product assignments will be permanently removed. This action cannot be undone.</small>`;
        const action = `/admin/customers/${customerId}`;
        const row = delBtn.closest('tr');
        
        if (typeof showDeleteModal === 'function') {
            showDeleteModal(message, action, row, function() {
                // Show success message and reload
                setTimeout(() => {
                    location.reload();
                }, 500);
            });
        } else {
            if (confirm(`Are you sure you want to delete "${customerName}"? All associated data will be permanently removed.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = action;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                
                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    });

    // Product card hover effects
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(8px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Customer row animation on hover
    document.querySelectorAll('.customer-row').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        row.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

    // Add loading state to form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            }
        });
    });

    // Filter button active state
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Handle customer activate/deactivate
    document.querySelectorAll('form[action*="toggle-status"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const customerName = this.closest('tr').querySelector('.customer-name').textContent;
            const isActive = !this.querySelector('button').classList.contains('text-success');
            const action = isActive ? 'deactivate' : 'activate';
            
            if (confirm(`Are you sure you want to ${action} "${customerName}"?`)) {
                this.submit();
            }
        });
    });

    // Calculate and show product count
    document.querySelectorAll('.products-container').forEach(container => {
        const productCards = container.querySelectorAll('.product-card');
        if (productCards.length > 2) {
            const moreText = container.querySelector('.text-center small');
            if (moreText) {
                moreText.textContent = `+${productCards.length - 2} more product${productCards.length - 2 > 1 ? 's' : ''}`;
            }
        }
    });
});
</script>
@endsection