@extends('layouts.admin')

@section('title', 'All Customers - Nanosft Billing')

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
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
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


    <!-- Customer Products Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Customer Info</th>
                        <th>Product List</th>
                        <th>Product Price</th>
                        <th>Assign Date</th>
                        <th>Billing Months</th>
                        <th>Subtotal Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        @if($customer->customerProducts->count() > 0)
                            @foreach($customer->customerProducts as $index => $cp)
                                <tr>
                                    @if($index === 0)
                                        <td rowspan="{{ $customer->customerProducts->count() }}">
                                             <a href="{{ route('admin.customers.show', $customer->c_id) }}" class="text-decoration-none" Target="_blank">
                                            <div class="customer-name text-primary fw-bold">{{ $customer->name }}</div>
                                                </a>
                                            <div class="customer-email">{{ $customer->email ?? 'No email' }}</div>
                                            <small class="text-muted">ID: {{ $customer->customer_id }}</small>
                                            <div class="mt-2">
                                                <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                    {{ $customer->is_active ? 'Active Customer' : 'Inactive Customer' }}
                                                </span>
                                            </div>
                                        </td>
                                    @endif
                                    
                                    <td class="product-cell">
                                        <div class="product-badge {{ optional($cp->product)->product_type === 'regular' ? 'regular-product' : 'special-product' }}">
                                            {{ optional($cp->product)->name ?? 'Unknown product' }}
                                            @if(optional($cp->product)->product_type === 'regular')
                                                <small class="d-block text-muted">Main product</small>
                                            @else
                                                <small class="d-block text-muted">Add-on</small>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td class="price-cell">
                                        <div><span class="currency">৳</span> {{ number_format($cp->product_price, 2) }}</div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div>{{ \Carbon\Carbon::parse($cp->assign_date)->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($cp->assign_date)->diffForHumans() }}</small>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="billing-months">{{ $cp->billing_cycle_months }} Month{{ $cp->billing_cycle_months > 1 ? 's' : '' }}</div>
                                    </td>
                                    
                                    <!-- Total Amount from Assignment -->
                                    <td class="price-cell">
                                        <div class="total-price">
                                            <strong class="text-dark">৳ {{ number_format($cp->total_amount, 2) }}</strong>
                                            <div class="text-muted small">
                                                {{ $cp->billing_cycle_months }} month{{ $cp->billing_cycle_months > 1 ? 's' : '' }} × ৳{{ number_format($cp->product_price, 2) }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- FIXED: Due Date - shows saved value or default -->
                                    <td class="text-center">
                                        <div class="due-day">
                                            @if($cp->due_date)
                                                {{ \Carbon\Carbon::parse($cp->due_date)->format('M d, Y') }}
                                            @else
                                                @php
                                                    $assignDate = \Carbon\Carbon::parse($cp->assign_date);
                                                    $billingCycleMonths = $cp->billing_cycle_months ?? 1;
                                                    $defaultDay = $assignDate->day > 28 ? 28 : $assignDate->day;
                                                    $displayDate = $assignDate->copy()->addMonths($billingCycleMonths)->day($defaultDay);
                                                @endphp
                                                <span class="text-muted">{{ $displayDate->format('M d, Y') }}*</span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="text-center">
                                        @php
                                            $statusClass = [
                                                'active' => 'bg-success',
                                                'pending' => 'bg-warning',
                                                'expired' => 'bg-danger',
                                                'paused' => 'bg-info'
                                            ][$cp->status] ?? 'bg-secondary';
                                            
                                            $statusIcons = [
                                                'active' => 'fa-check-circle',
                                                'pending' => 'fa-clock',
                                                'expired' => 'fa-times-circle',
                                                'paused' => 'fa-pause-circle'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClass }} status-badge">
                                            <i class="fas {{ $statusIcons[$cp->status] ?? 'fa-question-circle' }} me-1"></i>
                                            {{ ucfirst($cp->status) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="text-center">
                                        <div class="btn-group d-flex justify-content-center gap-1">
                                            @if($cp->cp_id)
                                                <a href="{{ route('admin.customer-to-products.edit', $cp->cp_id) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Edit product">
                                                   <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Pause/Resume button -->
                                                <form action="{{ route('admin.customer-to-products.toggle-status', $cp->cp_id) }}" 
                                                      method="POST" 
                                                      class="d-inline" 
                                                      id="toggle-status-form-{{ $cp->cp_id }}">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="button" 
                                                            class="btn btn-sm {{ $cp->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} toggle-status-btn" 
                                                            title="{{ $cp->status === 'active' ? 'Pause product' : 'Resume product' }}"
                                                            data-cp-id="{{ $cp->cp_id }}"
                                                            data-product-name="{{ optional($cp->product)->name ?? 'Unknown product' }}"
                                                            data-customer-name="{{ $customer->name }}"
                                                            data-current-status="{{ $cp->status }}">
                                                        <i class="fas {{ $cp->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                </form>

                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger delete-btn" 
                                                        title="Delete product"
                                                        data-product-name="{{ optional($cp->product)->name ?? 'Unknown product' }}"
                                                        data-customer-name="{{ $customer->name }}"
                                                        data-action="{{ route('admin.customer-to-products.destroy', $cp->cp_id) }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <span class="text-muted small">No actions</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5>No Customer Products Found</h5>
                                <p class="text-muted">
                                    @if(request()->hasAny(['search', 'status', 'product_type']))
                                        No products found matching your search criteria.
                                    @else
                                        No products have been assigned to customers yet.
                                    @endif
                                </p>
                                @if(request()->hasAny(['search', 'status', 'product_type']))
                                    <a href="{{ route('admin.customer-to-products.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Clear Search
                                    </a>
                                @else
                                    <a href="{{ route('admin.customer-to-products.assign') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Assign First Product
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers
                @if(request()->hasAny(['search', 'status', 'product_type']))
                    <span class="badge bg-info ms-2">Filtered Results</span>
                @endif
            </div>
            <nav aria-label="Customer pagination" class="pagination-container">
                {{ $customers->appends(request()->query())->links('pagination.bootstrap-5') }}
            </nav>
        </div>
    @endif

  

<!-- <style>
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

            /* Product Cards - Color Variations */
            .product-card {
                border-radius: 10px;
                border-left: 4px solid;
                transition: all 0.3s ease;
                overflow: hidden;
            }

        .product-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
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
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
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
            padding: 1.2rem 0.75rem;
        }

        .table td {
            padding: 1.2rem 0.75rem;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Customer Row States */
        .customer-row {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }   

        .customer-row:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-left-color: var(--primary);
            box-shadow: inset 4px 0 0 var(--primary);
        }

        .payment-due {
            background: linear-gradient(135deg, #fff5f7 0%, #fed7e2 100%);
            border-left-color: var(--danger) !important;
        }

        .payment-due:hover {
            background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);
        }

        .new-customer {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
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
            width: 60px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 0;
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
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
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
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* Status Items */
        .status-item {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            margin-top: 0.5rem;
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
        }

        .bill-amount:hover h3 {
            transform: scale(1.05);
            color: var(--success) !important;
        }

        /* Filter Buttons */
        .filter-btn {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
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
            
            .product-card {
                margin-bottom: 0.5rem;
            }
                    
            .action-btn {
                width: 50px;
                padding: 0.5rem 0;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

            ::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 4px;
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
            border-radius: 8px;
            margin: 0 2px;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-weight: 500;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* Insight Icons */
        .insight-icon {
            transition: all 0.3s ease;
        }

        .insight-icon:hover {
            transform: rotate(15deg) scale(1.1);
        }
                
        /* Tooltip Customization */
        .tooltip {
            --bs-tooltip-bg: var(--primary);
            --bs-tooltip-border-radius: 8px;
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .customer-row {
            animation: fadeIn 0.5s ease-out forwards;
        }
</style> -->

<style>
    /* Modern Color System */
    :root {
        --primary: #4361ee;
        --secondary: #6c757d;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --light: #f8f9fa;
        --dark: #1f2937;
        
        /* Light variants */
        --primary-light: #eef2ff;
        --success-light: #d1fae5;
        --warning-light: #fef3c7;
        --danger-light: #fee2e2;
        --info-light: #dbeafe;
    }

    /* Base Card Styling */
    .card {
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    /* Customer Table Styles */
    #customersTable {
        border-collapse: separate;
        border-spacing: 0;
    }

    #customersTable thead th {
        background-color: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        color: #4b5563;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        white-space: nowrap;
    }

    #customersTable tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
    }

    /* Customer Avatar */
    .customer-avatar .avatar-circle {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        background: linear-gradient(135deg, var(--primary) 0%, #3b82f6 100%);
        color: white;
    }

    /* Product Cards */
    .product-card {
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }

    .product-card:hover {
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.1);
    }

    /* Status Badges */
    .status-badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Action Buttons */
    .action-btn {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
    }

    .action-btn:hover {
        border-color: var(--primary);
        background-color: var(--primary-light);
        color: var(--primary);
        transform: translateY(-1px);
    }

    /* Empty States */
    .no-product {
        background-color: #f9fafb;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
    }

    .empty-state-icon {
        color: #9ca3af;
        margin-bottom: 1rem;
    }

    /* Payment Status */
    .due-alert {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 1px solid #fecaca;
        border-radius: 0.5rem;
        padding: 0.5rem;
        font-size: 0.75rem;
    }

    .paid-badge {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: 1px solid #6ee7b7;
        border-radius: 0.5rem;
        padding: 0.5rem;
        font-size: 0.75rem;
    }

    /* Filter Buttons */
    .filter-btn {
        border-radius: 0.5rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #e5e7eb;
        background: white;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .filter-btn:hover {
        border-color: var(--primary);
        background-color: var(--primary-light);
    }

    .filter-btn.active {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Stat Cards */
    .stat-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255, 255, 255, 0.5);
    }

    /* Product Type Colors */
    .bg-primary-light { background-color: var(--primary-light) !important; }
    .bg-success-light { background-color: var(--success-light) !important; }
    .bg-warning-light { background-color: var(--warning-light) !important; }
    .bg-danger-light { background-color: var(--danger-light) !important; }
    .bg-info-light { background-color: var(--info-light) !important; }

    .border-primary { border-left-color: var(--primary) !important; }
    .border-success { border-left-color: var(--success) !important; }
    .border-warning { border-left-color: var(--warning) !important; }
    .border-danger { border-left-color: var(--danger) !important; }
    .border-info { border-left-color: var(--info) !important; }

    /* Customer Row States */
    .customer-row {
        transition: all 0.2s ease;
    }

    .customer-row:hover {
        background-color: #f9fafb;
    }

    .payment-due {
        border-left: 3px solid var(--danger);
        background-color: #fef2f2;
    }

    .new-customer {
        border-left: 3px solid var(--info);
        background-color: #eff6ff;
    }

    .inactive-customer {
        opacity: 0.6;
    }

    /* Insights Cards */
    .insight-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--primary-light);
        color: var(--primary);
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    /* Pagination */
    .pagination .page-link {
        border-radius: 0.5rem;
        margin: 0 0.125rem;
        border: 1px solid #e5e7eb;
        color: #4b5563;
        min-width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem !important;
        }
        
        .avatar-circle {
            width: 2rem;
            height: 2rem;
            font-size: 0.75rem;
        }
        
        .action-btn {
            width: 2rem;
            height: 2rem;
            font-size: 0.75rem;
        }
        
        #customersTable {
            font-size: 0.875rem;
        }
        
        #customersTable thead th,
        #customersTable tbody td {
            padding: 0.75rem 0.5rem;
        }
    }

    /* Scrollbar Styling */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .customer-row {
        animation: fadeIn 0.3s ease-out;
    }

    /* Dropdown Menus */
    .dropdown-menu {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 0.5rem;
    }

    .dropdown-item {
        border-radius: 0.25rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #f9fafb;
    }
    
    /* Badge Styles */
    .badge {
        font-size: 0.6875rem;
        font-weight: 500;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    /* Form Controls */
    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    /* Alert Messages */
    .alert {
        border-radius: 0.5rem;
        border: 1px solid transparent;
        padding: 1rem 1.25rem;
    }

    .alert-success {
        background-color: #d1fae5;
        border-color: #a7f3d0;
        color: #065f46;
    }

    .alert-danger {
        background-color: #fee2e2;
        border-color: #fecaca;
        color: #991b1b;
    }
</style>

<!-- Toast Notification Container -->
<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 10001; width: 350px;"></div>

<!-- Include Delete Confirmation Modal -->
<x-delete-confirmation-modal />

<script>
    // Toast Notification Function
    function showToast(title, message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer');
        
        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
            border-left: 4px solid ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            max-width: 350px;
        `;
        
        toast.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h6 style="margin: 0 0 0.25rem 0; color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};">${title}</h6>
                    <p style="margin: 0; font-size: 0.875rem; color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; font-size: 1.25rem; font-weight: bold; color: #000; opacity: 0.5; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) toast.remove();
                }, 300);
            }
        }, 5000);
    }
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

    // Handle toggle status button clicks
    document.body.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.toggle-status-btn');
        if (toggleBtn) {
            e.preventDefault();
            
            const cpId = toggleBtn.getAttribute('data-cp-id');
            const productName = toggleBtn.getAttribute('data-product-name');
            const customerName = toggleBtn.getAttribute('data-customer-name');
            const currentStatus = toggleBtn.getAttribute('data-current-status');
            const form = document.getElementById(`toggle-status-form-${cpId}`);
            
            const newStatus = currentStatus === 'active' ? 'pause' : 'resume';
            
            // Show confirmation modal
            if (typeof showDeleteModal !== 'undefined') {
                const message = `Are you sure you want to <strong>${newStatus}</strong> "<strong>${productName}</strong>" for "<strong>${customerName}</strong>"?`;
                showDeleteModal(message, null, null, function() {
                    // Trigger form submission which will be handled by our custom handler
                    const event = new Event('submit', { cancelable: true, bubbles: true });
                    form.dispatchEvent(event);
                });
            } else {
                // Fallback to browser confirm if modal not available
                if (confirm(`Are you sure you want to ${newStatus} "${productName}" for "${customerName}"?`)) {
                    // Trigger form submission which will be handled by our custom handler
                    const event = new Event('submit', { cancelable: true, bubbles: true });
                    form.dispatchEvent(event);
                }
            }
            return;
        }
        
        // Handle delete button clicks
        const delBtn = e.target.closest('.delete-btn');
        if (delBtn) {
            e.preventDefault();
            
            const productName = delBtn.getAttribute('data-product-name');
            const customerName = delBtn.getAttribute('data-customer-name');
            const action = delBtn.getAttribute('data-action');
            
            // Show confirmation modal
            if (typeof showDeleteModal !== 'undefined') {
                const message = `Are you sure you want to <strong>delete</strong> "<strong>${productName}</strong>" for "<strong>${customerName}</strong>"? This action cannot be undone.`;
                showDeleteModal(message, action, null, null);
            } else {
                // Fallback to browser confirm if modal not available
                if (confirm(`Are you sure you want to delete "${productName}" for "${customerName}"? This action cannot be undone.`)) {
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

    // Save original button text for toggle buttons
    document.querySelectorAll('form[id^="toggle-status-form-"] button[type="submit"]').forEach(button => {
        button.dataset.originalText = button.innerHTML;
    });
    
    // Add loading state to form submissions and handle response
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check if this is our toggle status form
            if (this.id && this.id.startsWith('toggle-status-form-')) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                }
                
                const formData = new FormData(this);
                const action = this.action || window.location.href;
                
                fetch(action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        if (typeof showToast !== 'undefined') {
                            showToast('Success', data.message || 'Status updated successfully', 'success');
                        }
                        
                        // Reload page to reflect changes
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        // Show error message
                        if (typeof showToast !== 'undefined') {
                            showToast('Error', data.message || 'Failed to update status', 'error');
                        }
                        
                        // Re-enable button
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = submitBtn.dataset.originalText || '<i class="fas fa-pause me-1"></i>Pause';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof showToast !== 'undefined') {
                        showToast('Error', 'An error occurred while updating status', 'error');
                    }
                    
                    // Re-enable button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || '<i class="fas fa-pause me-1"></i>Pause';
                    }
                });
                
                return;
            }
            
            // Default loading state for other forms
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