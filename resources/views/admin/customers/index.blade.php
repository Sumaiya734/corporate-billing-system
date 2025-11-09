@extends('layouts.admin')

@section('title', 'All Customers - NetBill BD')

@section('content')
<div class="p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark">
                <i class="fas fa-users me-2 text-primary"></i>Customer Management
            </h2>
            <p class="text-muted mb-0">Manage all customer accounts, packages, and billing information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Add Customer
            </a>
            <a href="{{ route('admin.customer-to-packages.assign') }}" class="btn btn-success">
                <i class="fas fa-user-tag me-2"></i>Assign Package
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-2"></i>Actions
                </button>
                <ul class="dropdown-menu">
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
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2 fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Customer Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-4 border-start-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Customers</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalCustomers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-4 border-start-success shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Active Customers</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $activeCustomers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-4 border-start-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Inactive Customers</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $inactiveCustomers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-4 border-start-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Due Payments</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $customersWithDue }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2 text-primary"></i>Search & Filter
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.customers.index') }}" id="searchForm">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control border-start-0" 
                                   placeholder="Search customers by name, email, phone, or ID..." 
                                   value="{{ request('search') }}"
                                   id="searchInput">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <select name="status" class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            @if(request()->has('search') || request()->has('status'))
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
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.customers.index') }}" 
                   class="btn btn-sm btn-outline-primary {{ !request()->has('filter') ? 'active' : '' }}">
                    <i class="fas fa-list me-1"></i>All Customers
                </a>
                <a href="{{ route('admin.customers.index', ['filter' => 'active']) }}" 
                   class="btn btn-sm btn-outline-success">
                    <i class="fas fa-user-check me-1"></i>Active
                </a>
                <a href="{{ route('admin.customers.index', ['filter' => 'inactive']) }}" 
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-user-slash me-1"></i>Inactive
                </a>
                <a href="{{ route('admin.customers.index', ['filter' => 'with_due']) }}" 
                   class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-exclamation-triangle me-1"></i>With Due
                </a>
                <a href="{{ route('admin.customers.index', ['filter' => 'with_addons']) }}" 
                   class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-bolt me-1"></i>With Add-ons
                </a>
                <a href="{{ route('admin.customers.index', ['filter' => 'new']) }}" 
                   class="btn btn-sm btn-outline-info">
                    <i class="fas fa-star me-1"></i>New This Week
                </a>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0 d-flex align-items-center">
                <i class="fas fa-list me-2 text-primary"></i>Customer Directory
                <span class="badge bg-primary ms-2">{{ $customers->total() }}</span>
                @if($customersWithDue > 0)
                    <span class="badge bg-danger ms-1">{{ $customersWithDue }} Due</span>
                @endif
            </h5>
            <div class="d-flex align-items-center">
                <span class="text-muted small me-3">
                    Showing {{ $customers->firstItem() ?? 0 }}-{{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }}
                </span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Options
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-columns me-2"></i>Customize Columns</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Export Data</a></li>
                        <li><hr class="dropdown-divider"></li>
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
                                <th class="ps-4">Customer</th>
                                <th>Packages</th>
                                <th class="text-center">Monthly Bill</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Registration</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                            @php
                                // Use Eloquent relationships for clean data access
                                $activePackages = $customer->customerPackages()
                                    ->with('package')
                                    ->where('status', 'active')
                                    ->where('is_active', 1)
                                    ->get();

                                $regularPackage = $activePackages->firstWhere('package.package_type', 'regular');
                                $specialPackages = $activePackages->where('package.package_type', 'special');
                                
                                $hasRegularPackage = (bool) $regularPackage;
                                $hasSpecialPackages = $specialPackages->count() > 0;
                                $monthlyTotal = $activePackages->sum('package_price');
                                
                                // Check for due payments
                                $hasDue = $customer->invoices()
                                    ->whereIn('status', ['unpaid', 'partial'])
                                    ->exists();
                                
                                $totalDue = $customer->invoices()
                                    ->whereIn('status', ['unpaid', 'partial'])
                                    ->sum(DB::raw('total_amount - received_amount'));
                                
                                $isNew = $customer->created_at->gt(now()->subDays(7));
                                
                                // Determine row styling
                                $rowClasses = [];
                                if ($hasDue) $rowClasses[] = 'payment-due-row';
                                if ($isNew) $rowClasses[] = 'new-customer-row';
                                if (!$customer->is_active) $rowClasses[] = 'inactive-customer-row';
                                
                                $rowClass = implode(' ', $rowClasses);
                                $initialLetter = strtoupper(substr($customer->name, 0, 1));
                            @endphp
                            <tr class="{{ $rowClass }}" 
                                data-customer-id="{{ $customer->c_id }}" 
                                data-status="{{ $customer->is_active ? 'active' : 'inactive' }}" 
                                data-has-addons="{{ $hasSpecialPackages ? 'yes' : 'no' }}"
                                data-has-due="{{ $hasDue ? 'yes' : 'no' }}"
                                data-is-new="{{ $isNew ? 'yes' : 'no' }}">
                                
                                <!-- Customer Information Column -->
                                <td class="ps-4">
                                    <div class="d-flex align-items-start">
                                        <div class="customer-avatar me-3 position-relative">
                                            <div class="avatar-circle bg-primary text-white">
                                                {{ $initialLetter }}
                                            </div>
                                            @if($isNew)
                                                <span class="position-absolute top-0 start-100 translate-middle badge bg-info" style="font-size: 0.5rem;">
                                                    NEW
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <strong class="me-2">{{ $customer->name }}</strong>
                                                @if(!$customer->is_active)
                                                    <span class="badge bg-secondary badge-sm">Inactive</span>
                                                @endif
                                            </div>
                                            <div class="customer-details">
                                                <div class="text-muted small mb-1">
                                                    <i class="fas fa-id-card me-1"></i>
                                                    <span class="fw-medium">{{ $customer->customer_id }}</span>
                                                </div>
                                                <div class="text-muted small mb-1">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    {{ $customer->email ?? 'No email' }}
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="fas fa-phone me-1"></i>
                                                    {{ $customer->phone ?? 'No phone' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Packages Column -->
                                <td>
                                    @if($hasRegularPackage)
                                        <!-- Main Package -->
                                        <div class="main-package-card mb-2">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-wifi text-primary me-2"></i>
                                                    <div>
                                                        <div class="package-name fw-semibold text-dark">
                                                            {{ $regularPackage->package->name ?? 'Unknown Package' }}
                                                        </div>
                                                        <div class="package-price text-success small">
                                                            ৳{{ number_format($regularPackage->package_price, 2) }}/month
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge bg-primary badge-sm">Main</span>
                                            </div>
                                        </div>

                                        <!-- Add-on Packages -->
                                        @if($hasSpecialPackages)
                                            <div class="addons-section">
                                                <div class="addons-header small text-muted mb-1">
                                                    <i class="fas fa-bolt me-1"></i>Add-ons ({{ $specialPackages->count() }})
                                                </div>
                                                <div class="addons-list">
                                                    @foreach($specialPackages as $specialPackage)
                                                    <div class="addon-item">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <span class="addon-name small">
                                                                {{ $specialPackage->package->name ?? 'Unknown Add-on' }}
                                                            </span>
                                                            <span class="addon-price text-warning small fw-semibold">
                                                                +৳{{ number_format($specialPackage->package_price, 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="no-package text-center py-2">
                                            <i class="fas fa-exclamation-triangle text-warning fa-lg mb-2"></i>
                                            <div class="text-muted small">No Active Package</div>
                                            <a href="{{ route('admin.customer-to-packages.assign') }}" class="btn btn-sm btn-outline-primary mt-1">
                                                Assign Package
                                            </a>
                                        </div>
                                    @endif
                                </td>

                                <!-- Billing Column -->
                                <td class="text-center">
                                    <div class="billing-info">
                                        <div class="monthly-total">
                                            <strong class="text-success fs-6">৳{{ number_format($monthlyTotal, 2) }}</strong>
                                            <div class="text-muted small">Monthly</div>
                                        </div>
                                        
                                        @if($hasDue && $totalDue > 0)
                                            <div class="due-amount mt-2">
                                                <div class="alert alert-danger py-1 px-2 mb-0 border-0">
                                                    <small class="fw-semibold">
                                                        <i class="fas fa-exclamation-circle me-1"></i>
                                                        ৳{{ number_format($totalDue, 2 ) }} due
                                                    </small>
                                                </div>
                                            </div>
                                        @elseif($monthlyTotal > 0)
                                            <div class="payment-status mt-2">
                                                <span class="badge bg-success badge-sm">
                                                    <i class="fas fa-check me-1"></i>Paid
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status Column -->
                                <td class="text-center">
                                    <div class="status-indicators">
                                        <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }} mb-1">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        @if($hasDue)
                                            <div class="due-indicator small text-danger">
                                                <i class="fas fa-clock me-1"></i>Payment Due
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Registration Column -->
                                <td class="text-center">
                                    <div class="registration-info">
                                        <div class="date fw-semibold text-dark">
                                            {{ $customer->created_at->format('M j, Y') }}
                                        </div>
                                        <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>

                                <!-- Actions Column -->
                               <td class="text-center pe-4">
    <div class="action-buttons d-flex justify-content-center gap-1">
        <!-- View Details -->
        <a href="{{ route('admin.customers.show', $customer->c_id) }}" 
           class="btn btn-sm btn-outline-info action-btn" 
           title="View Details"
           data-bs-toggle="tooltip">
            <i class="fas fa-eye"></i>
        </a>

        <!-- Edit Customer -->
        <a href="{{ route('admin.customers.edit', $customer->c_id) }}" 
           class="btn btn-sm btn-outline-warning action-btn" 
           title="Edit Customer"
           data-bs-toggle="tooltip">
            <i class="fas fa-edit"></i>
        </a>

        <!-- Toggle Status -->
        <form action="{{ route('admin.customers.toggle-status', $customer->c_id) }}" 
              method="POST" 
              class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" 
                    class="btn btn-sm btn-outline-{{ $customer->is_active ? 'warning' : 'success' }} action-btn" 
                    title="{{ $customer->is_active ? 'Deactivate' : 'Activate' }}"
                    data-bs-toggle="tooltip">
                <i class="fas fa-{{ $customer->is_active ? 'pause' : 'play' }}"></i>
            </button>
        </form>

        <!-- Delete Customer -->
        <button type="button" 
                class="btn btn-sm btn-outline-danger action-btn"
                title="Delete Customer"
                data-bs-toggle="modal" 
                data-bs-target="#deleteCustomerModal"
                data-customer-id="{{ $customer->c_id }}"
                data-customer-name="{{ $customer->name }}">
            <i class="fas fa-trash"></i>
        </button>

    </div>
</td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($customers->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-3">
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
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-users fa-4x text-muted opacity-25"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Customers Found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->has('search') || request()->has('status') || request()->has('filter'))
                            No customers match your current search criteria.
                        @else
                            Get started by adding your first customer to the system.
                        @endif
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Add First Customer
                        </a>
                        @if(request()->has('search') || request()->has('status') || request()->has('filter'))
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear Filters
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Customer Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title text-danger" id="deleteCustomerModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-warning border-0 mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <p class="mt-3 mb-0">
                    Are you sure you want to delete <strong id="deleteCustomerName" class="text-danger"></strong>?
                    All associated invoices, payments, and package assignments will be permanently removed.
                </p>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteCustomerForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Customer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Professional Table Styling */
.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
}

.table td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-color: #f1f3f4;
}

/* Avatar Styling */
.avatar-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.customer-avatar {
    position: relative;
}

/* Package Card Styling */
.main-package-card {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
    border: 1px solid #e3e8ff;
    border-radius: 8px;
    padding: 0.75rem;
}

.package-name {
    font-size: 0.875rem;
    line-height: 1.3;
}

.package-price {
    font-size: 0.8rem;
    font-weight: 500;
}

/* Add-ons Styling */
.addons-section {
    margin-top: 0.5rem;
}

.addons-header {
    font-size: 0.75rem;
    font-weight: 500;
}

.addons-list {
    max-height: 80px;
    overflow-y: auto;
}

.addon-item {
    background: #fff;
    border-radius: 6px;
    padding: 0.4rem 0.6rem;
    margin-bottom: 0.25rem;
    border: 1px solid #f1f3f4;
    transition: all 0.2s ease;
}

.addon-item:hover {
    background: #f8f9fa;
    border-color: #e9ecef;
}

.addon-name {
    font-size: 0.8rem;
}

/* Status Badges */
.badge-sm {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

/* Row States */
.payment-due-row {
    background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%) !important;
    border-left: 4px solid #dc3545 !important;
}

.payment-due-row:hover {
    background: linear-gradient(135deg, #ffeaea 0%, #ffd6d6 100%) !important;
}

.new-customer-row {
    background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%) !important;
    border-left: 4px solid #0dcaf0 !important;
}

.new-customer-row:hover {
    background: linear-gradient(135deg, #e6f7ff 0%, #d1f0ff 100%) !important;
}

.inactive-customer-row {
    background-color: #f8f9fa !important;
    opacity: 0.7;
}

/* Action Buttons */
.action-buttons {
    min-width: 160px;
}

.action-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Hover Effects */
.table-hover tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 1;
    position: relative;
}

/* Custom Scrollbar */
.addons-list::-webkit-scrollbar {
    width: 4px;
}

.addons-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.addons-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.addons-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Quick Filter Buttons */
.btn-group-sm > .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

/* Empty State */
.empty-state-icon {
    opacity: 0.5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .avatar-circle {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .action-buttons {
        min-width: auto;
    }
}

/* Card Border Colors */
.border-start-primary { border-left-color: #0d6efd !important; }
.border-start-success { border-left-color: #198754 !important; }
.border-start-warning { border-left-color: #ffc107 !important; }
.border-start-danger { border-left-color: #dc3545 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Delete Customer Modal
    const deleteCustomerModal = document.getElementById('deleteCustomerModal');
    if (deleteCustomerModal) {
        deleteCustomerModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const customerId = button.getAttribute('data-customer-id');
            const customerName = button.getAttribute('data-customer-name');
            
            const modal = this;
            modal.querySelector('#deleteCustomerName').textContent = customerName;
            modal.querySelector('#deleteCustomerForm').action = `/admin/customers/${customerId}`;
        });
    }

    // Auto-submit form when status filter changes
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            document.getElementById('searchForm').submit();
        });
    }

    // Real-time search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    document.getElementById('searchForm').submit();
                }
            }, 500);
        });
    }

    // Sort table by priority (due payments first, then new customers)
    function sortTableByPriority() {
        const table = document.getElementById('customersTable');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const aHasDue = a.getAttribute('data-has-due') === 'yes';
            const bHasDue = b.getAttribute('data-has-due') === 'yes';
            const aIsNew = a.getAttribute('data-is-new') === 'yes';
            const bIsNew = b.getAttribute('data-is-new') === 'yes';

            // Due payments first
            if (aHasDue && !bHasDue) return -1;
            if (!aHasDue && bHasDue) return 1;
            
            // Then new customers
            if (aIsNew && !bIsNew) return -1;
            if (!aIsNew && bIsNew) return 1;
            
            return 0;
        });

        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }

    // Sort table on page load
    sortTableByPriority();

    // Add loading state to buttons
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            }
        });
    });
});
</script>
@endsection