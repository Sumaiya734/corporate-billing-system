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
            <p class="text-muted mb-0">Manage all customer accounts, products, and billing information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Add Customer
            </a>
            <a href="{{ route('admin.customer-to-products.assign') }}" class="btn btn-success">
                <i class="fas fa-user-tag me-2"></i>Assign product
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
                                <th>Products</th>
                                <th class="text-center">Monthly Bill</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Registration</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Predefined color palette for product boxes
                                $productColors = [
                                    'color-1' => ['bg' => '#f0f9ff', 'border' => '#e3e8ff', 'text' => '#0369a1'],  // Light blue
                                    'color-2' => ['bg' => '#f0fdf4', 'border' => '#dcfce7', 'text' => '#166534'],  // Light green
                                    'color-3' => ['bg' => '#fef2f2', 'border' => '#fee2e2', 'text' => '#991b1b'],  // Light red
                                    'color-4' => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'text' => '#9a3412'],  // Light orange
                                    'color-5' => ['bg' => '#faf5ff', 'border' => '#f3e8ff', 'text' => '#7c2d12'],  // Light purple
                                    'color-6' => ['bg' => '#eff6ff', 'border' => '#dbeafe', 'text' => '#1e40af'],  // Blue
                                    'color-7' => ['bg' => '#ecfdf5', 'border' => '#d1fae5', 'text' => '#065f46'],  // Green
                                    'color-8' => ['bg' => '#fef3c7', 'border' => '#fde68a', 'text' => '#92400e'],  // Yellow
                                ];
                                
                                // Sort customers collection manually
                                $sortedCustomers = $customers->sortByDesc(function($customer) {
                                    $isNew = $customer->created_at->gt(now()->subDays(7));
                                    $hasDue = $customer->invoices()
                                        ->whereIn('invoices.status', ['unpaid', 'partial'])
                                        ->exists();
                                    
                                    // Priority order: 1. New customers, 2. Due customers, 3. Others
                                    if ($isNew) return 3; // Highest priority
                                    if ($hasDue) return 2; // Medium priority
                                    return 1; // Lowest priority
                                });
                            @endphp
                            
                            @foreach($sortedCustomers as $customer)
                            @php
                                // Get active products with relationships
                                $activeproducts = $customer->customerproducts
                                    ->where('status', 'active')
                                    ->where('is_active', 1)
                                    ->filter(function($cp) {
                                        return $cp->product !== null; // Only include products that exist
                                    });

                                // Calculate monthly total using custom price if available
                                $monthlyTotal = $activeproducts->sum(function($cp) {
                                    // Use custom price if set, otherwise use product's monthly price
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
                                data-has-due="{{ $hasDue ? 'yes' : 'no' }}"
                                data-is-new="{{ $isNew ? 'yes' : 'no' }}"
                                data-priority="{{ $isNew ? 'new' : ($hasDue ? 'due' : 'normal') }}">
                                
                                <!-- Customer Information Column with Profile Picture -->
                                <td class="ps-4">
                                    <div class="d-flex align-items-start">
                                        <div class="customer-avatar me-3 position-relative">
                                            @if($customer->profile_picture)
                                                <img src="{{ asset('storage/' . $customer->profile_picture) }}" 
                                                     alt="{{ $customer->name }}"
                                                     class="avatar-image"
                                                     onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20100%20100%22%3E%3Crect%20width%3D%22100%22%20height%3D%22100%22%20fill%3D%22%230d6efd%22%2F%3E%3Ctext%20x%3D%2250%22%20y%3D%2255%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%20fill%3D%22white%22%20font-size%3D%2240%22%3E{{ $initialLetter }}%3C%2Ftext%3E%3C%2Fsvg%3E'">
                                            @else
                                                <div class="avatar-circle bg-primary text-white">
                                                    {{ $initialLetter }}
                                                </div>
                                            @endif
                                            @if($isNew)
                                                <span class="position-absolute top-0 start-100 translate-middle badge bg-info" style="font-size: 0.5rem;">
                                                    NEW
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <a href="{{ route('admin.customers.show', $customer->c_id) }}" class="text-decoration-none" Target="_blank">
                                                    <strong class="me-2 text-dark">{{ $customer->name }}</strong>
                                                </a>
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

                                <!-- Products Column with Scrollable Container and Colorful Boxes -->
                                <td>
                                    @if($activeproducts->count() > 0)
                                        <div class="products-scroll-container">
                                            <div class="products-list">
                                                @foreach($activeproducts as $index => $cp)
                                                    @php
                                                        // Cycle through colors based on product index
                                                        $colorIndex = ($index % 8) + 1;
                                                        $colorClass = "product-color-{$colorIndex}";
                                                        $colorConfig = $productColors["color-{$colorIndex}"];
                                                    @endphp
                                                    <div class="product-item {{ $colorClass }}" 
                                                         style="--product-bg: {{ $colorConfig['bg'] }}; 
                                                                --product-border: {{ $colorConfig['border'] }};
                                                                --product-text: {{ $colorConfig['text'] }};">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-box me-2 product-icon"></i>
                                                                <div>
                                                                    <div class="product-name fw-semibold text-dark small">
                                                                        {{ $cp->product->name ?? 'Unknown Product' }}
                                                                    </div>
                                                                    <div class="product-price text-success small">
                                                                        @php
                                                                            $price = $cp->product_price ?? $cp->product->monthly_price ?? 0;
                                                                            $isCustomPrice = $cp->product_price && $cp->product_price != ($cp->product->monthly_price ?? 0);
                                                                        @endphp
                                                                        ৳{{ number_format($price, 2) }}/month
                                                                        @if($isCustomPrice)
                                                                            <span class="badge bg-info" style="font-size: 0.65rem;">Custom</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($activeproducts->count() > 2)
                                                <div class="products-count small text-muted text-center mt-2">
                                                    <i class="fas fa-cube me-1"></i>
                                                    {{ $activeproducts->count() }} product(s) - Scroll to see more
                                                </div>
                                            @else
                                                <div class="products-count small text-muted text-center mt-2">
                                                    <i class="fas fa-cube me-1"></i>
                                                    {{ $activeproducts->count() }} product(s)
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="no-product text-center py-2">
                                            <i class="fas fa-exclamation-triangle text-warning fa-lg mb-2"></i>
                                            <div class="text-muted small">No Active Products</div>
                                            <a href="{{ route('admin.customer-to-products.assign') }}" class="btn btn-sm btn-outline-primary mt-1">
                                                Assign Product
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
                                        data-bs-toggle="tooltip" Target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Edit Customer -->
                                        <a href="{{ route('admin.customers.edit', $customer->c_id) }}" 
                                        class="btn btn-sm btn-outline-warning action-btn" 
                                        title="Edit Customer"
                                        data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Delete Customer -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger action-btn delete-customer-btn"
                                                title="Delete Customer"
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

<!-- Include Delete Confirmation Modal -->
<x-delete-confirmation-modal />

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

/* Fix all rows to same height */
.table tbody tr {
    height: 140px; /* Fixed row height for consistency */
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

.avatar-image {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 2px solid #f8f9fa;
}

.customer-avatar {
    position: relative;
}

/* Customer Name Link Styling */
.flex-grow-1 a:hover strong {
    color: #0d6efd !important;
    text-decoration: underline !important;
}

/* Scrollable Products Container */
.products-scroll-container {
    height: 110px; /* Fixed height for all product containers */
    display: flex;
    flex-direction: column;
}

.products-list {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 5px;
    margin-bottom: 5px;
}

/* Product Item Styling with Colorful Backgrounds */
.product-item {
    border-radius: 8px;
    padding: 0.6rem;
    margin-bottom: 0.5rem;
    border: 1px solid var(--product-border);
    background: var(--product-bg);
    transition: all 0.2s ease;
}

.product-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-color: var(--product-text);
}

.product-icon {
    color: var(--product-text) !important;
    font-size: 0.9rem;
}

.product-name {
    font-size: 0.8rem;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}

.product-price {
    font-size: 0.75rem;
    font-weight: 500;
}

.products-count {
    font-size: 0.7rem;
    padding-top: 3px;
    border-top: 1px dashed #dee2e6;
}

/* Color Classes for Product Boxes */
.product-color-1 {
    --product-bg: #f0f9ff;
    --product-border: #e3e8ff;
    --product-text: #0369a1;
}

.product-color-2 {
    --product-bg: #f0fdf4;
    --product-border: #dcfce7;
    --product-text: #166534;
}

.product-color-3 {
    --product-bg: #fef2f2;
    --product-border: #fee2e2;
    --product-text: #991b1b;
}

.product-color-4 {
    --product-bg: #fff7ed;
    --product-border: #fed7aa;
    --product-text: #9a3412;
}

.product-color-5 {
    --product-bg: #faf5ff;
    --product-border: #f3e8ff;
    --product-text: #7c2d12;
}

.product-color-6 {
    --product-bg: #eff6ff;
    --product-border: #dbeafe;
    --product-text: #1e40af;
}

.product-color-7 {
    --product-bg: #ecfdf5;
    --product-border: #d1fae5;
    --product-text: #065f46;
}

.product-color-8 {
    --product-bg: #fef3c7;
    --product-border: #fde68a;
    --product-text: #92400e;
}

/* Custom Scrollbar for Products List */
.products-list::-webkit-scrollbar {
    width: 4px;
}

.products-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.products-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.products-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
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
    min-width: 120px;
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
    
    .avatar-circle, .avatar-image {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .action-buttons {
        min-width: auto;
    }
    
    .table tbody tr {
        height: auto; /* Auto height on mobile */
    }
    
    .products-scroll-container {
        height: 100px; /* Smaller height on mobile */
    }
    
    .product-name {
        max-width: 120px;
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

    // Delete Customer with modal confirmation
    document.body.addEventListener('click', function(e) {
        const delBtn = e.target.closest('.delete-customer-btn');
        if (!delBtn) return;
        
        const customerId = delBtn.getAttribute('data-customer-id');
        const customerName = delBtn.getAttribute('data-customer-name');
        
        const message = `Are you sure you want to delete <strong>"${customerName}"</strong>?<br><small class="text-danger">All associated invoices, payments, and product assignments will be permanently removed. This action cannot be undone.</small>`;
        const action = `/admin/customers/${customerId}`;
        const row = delBtn.closest('tr');
        
        showDeleteModal(message, action, row, function() {
            // Reload page after successful deletion to update stats
            setTimeout(() => location.reload(), 500);
        });
    });

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

    // Sort table by priority (New customers first, then Due customers, then Others)
    function sortTableByPriority() {
        const table = document.getElementById('customersTable');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const aPriority = a.getAttribute('data-priority');
            const bPriority = b.getAttribute('data-priority');
            
            // Priority order: new > due > normal
            const priorityOrder = { 'new': 3, 'due': 2, 'normal': 1 };
            const aScore = priorityOrder[aPriority] || 1;
            const bScore = priorityOrder[bPriority] || 1;
            
            return bScore - aScore; // Descending order
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