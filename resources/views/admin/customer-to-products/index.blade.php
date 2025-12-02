@extends('layouts.admin')

@section('title', 'Customer Products')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-user-tag me-2"></i>Customer to Products</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.customer-to-products.assign') }}" class="btn btn-primary" style="margin:10px">
                <i class="fas fa-plus me-2"></i>Assign Products
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <!-- <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card">
                <i class="fas fa-users stats-icon"></i>
                <div class="stats-number">{{ $totalCustomers }}</div>
                <div class="stats-label">Total Customers</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card">
                <i class="fas fa-cube stats-icon"></i>
                <div class="stats-number">{{ $activeProducts }}</div>
                <div class="stats-label">Active Products</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card">
                <i class="fas fa-taka-sign stats-icon"></i>
                <div class="stats-number">৳ {{ number_format($monthlyRevenue, 2) }}</div>
                <div class="stats-label">Monthly Revenue</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card">
                <i class="fas fa-sync stats-icon"></i>
                <div class="stats-number">{{ $renewalsDue }}</div>
                <div class="stats-label">Renewals Due</div>
            </div>
        </div>
    </div> -->

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.customer-to-products.index') }}" method="GET" id="searchForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Customers</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   placeholder="Search by name, email, phone, or customer ID..."
                                   value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Product Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="product_type" class="form-label">Product Type</label>
                        <select class="form-select" id="product_type" name="product_type">
                            <option value="">All Types</option>
                            <option value="regular" {{ request('product_type') == 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="special" {{ request('product_type') == 'special' ? 'selected' : '' }}>Special</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                    </div>
                </div>
                @if(request()->hasAny(['search', 'status', 'product_type']))
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center flex-wrap">
                            
                            @if(request('search'))
                                <span class="badge bg-primary me-2 mb-1">
                                    Search: "{{ request('search') }}"
                                    <a href="javascript:void(0)" onclick="removeFilter('search')" class="text-white ms-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif
                            @if(request('status'))
                                <span class="badge bg-info me-2 mb-1">
                                    Status: {{ ucfirst(request('status')) }}
                                    <a href="javascript:void(0)" onclick="removeFilter('status')" class="text-white ms-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif
                            @if(request('product_type'))
                                <span class="badge bg-warning me-2 mb-1">
                                    Type: {{ ucfirst(request('product_type')) }}
                                    <a href="javascript:void(0)" onclick="removeFilter('product_type')" class="text-white ms-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif
                            
                        </div>
                    </div>
                </div>
                @endif
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
                                                'expired' => 'bg-danger'
                                            ][$cp->status] ?? 'bg-secondary';
                                            
                                            $statusIcons = [
                                                'active' => 'fa-check-circle',
                                                'pending' => 'fa-clock',
                                                'expired' => 'fa-times-circle'
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
            <nav>
                {{ $customers->withQueryString()->links('pagination::bootstrap-5') }}
            </nav>
        </div>
    @endif
    
    <!-- Legend for default due dates -->
    <div class="mt-3">
        <small class="text-muted">
            <sup>*</sup> Default due date calculated from assign date
        </small>
    </div>
</div>

<!-- Include Delete Confirmation Modal Component -->
<x-delete-confirmation-modal />

<style>
    .page-title {
        color: #2c3e50;
        font-weight: 700;
        margin: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid #3498db;
    }
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .table-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .table {
        border: 2px solid #dee2e6;
        margin-bottom: 0;
        table-layout: auto; /* Changed from fixed to auto */
    }
    .table th {
        border: 2px solid #dee2e6;
        font-weight: 600;
        padding: 12px 10px;
        text-align: center;
        vertical-align: middle;
        background: #2c3e50;
        color: white;
        font-size: 0.85rem;
    }
    .table td {
        padding: 12px 10px;
        vertical-align: middle;
        border: 2px solid #dee2e6;
        font-size: 0.875rem;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .product-badge {
        border-radius: 20px;
        padding: 8px 15px;
        margin: 2px;
        display: inline-block;
        font-size: 0.85rem;
        border: 1px solid;
        text-align: center;
        min-width: 120px;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .regular-product {
        background-color: #e3f2fd;
        color: #1976d2;
        border-color: #bbdefb;
    }
    .special-product {
        background-color: #fff3e0;
        color: #f57c00;
        border-color: #ffe0b2;
    }
    .customer-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1rem;
    }
    .customer-email {
        font-size: 0.85rem;
        color: #7f8c8d;
    }
    .due-day {
        font-weight: 600;
        color: #27ae60;
        font-size: 0.9rem;
    }
    .due-day sup {
        font-size: 0.65rem;
        top: -0.3em;
    }
    .billing-months {
        font-weight: 500;
        color: #34495e;
    }
    .total-price {
        text-align: right;
    }
    .total-price .text-dark {
        font-size: 1rem;
    }
    .total-price .text-muted {
        font-size: 0.75rem;
    }
    .price-cell {
        text-align: right;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 6px 10px;
    }
    .stats-card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    .stats-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 2rem;
        opacity: 0.2;
    }
    .stats-number {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 10px 0;
        color: #2c3e50;
    }
    .stats-label {
        font-size: 0.9rem;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .form-label {
        font-weight: 500;
        color: #2c3e50;
    }
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    /* Modal animation */
    @keyframes modalSlideIn {
        from {
            transform: scale(0.8);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table th, .table td {
            padding: 8px 5px;
            font-size: 0.75rem;
        }
        .product-badge {
            min-width: 80px;
            padding: 5px 10px;
            font-size: 0.7rem;
        }
        .stats-number {
            font-size: 1.4rem;
        }
        .page-title {
            font-size: 1.5rem;
        }
    }
    @media (max-width: 576px) {
        .table th, .table td {
            padding: 6px 4px;
            font-size: 0.7rem;
        }
        .product-badge {
            min-width: 60px;
            padding: 4px 8px;
            font-size: 0.65rem;
        }
        .stats-number {
            font-size: 1.2rem;
        }
        .stats-label {
            font-size: 0.75rem;
        }
    }
</style>

<script>
    // Simple toast notification function
    function showToast(title, message, type) {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            `;
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.style.cssText = `
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            min-width: 300px;
            animation: slideIn 0.3s, fadeOut 0.5s 2.5s forwards;
        `;
        
        // Set colors based on type
        switch(type) {
            case 'success':
                toast.style.backgroundColor = '#28a745';
                break;
            case 'error':
                toast.style.backgroundColor = '#dc3545';
                break;
            case 'warning':
                toast.style.backgroundColor = '#ffc107';
                toast.style.color = '#212529';
                break;
            default:
                toast.style.backgroundColor = '#17a2b8';
        }
        
        toast.innerHTML = `
            <strong>${title}</strong><br>
            <small>${message}</small>
        `;
        
        toastContainer.appendChild(toast);
        
        // Remove toast after animation
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    function removeFilter(filterName) {
        const url = new URL(window.location);
        url.searchParams.delete(filterName);
        window.location = url.toString();
    }
    
    function clearSearch() {
        document.getElementById('search').value = '';
        document.getElementById('searchForm').submit();
    }
    
    // Auto-submit on filter change
    document.addEventListener('DOMContentLoaded', function() {
        ['status', 'product_type'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', () => document.getElementById('searchForm').submit());
        });
        
        // Handle delete button clicks
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const productName = this.getAttribute('data-product-name');
                const customerName = this.getAttribute('data-customer-name');
                const action = this.getAttribute('data-action');
                const row = this.closest('tr');
                
                // Use the global showDeleteModal function from the component
                showDeleteModal(
                    `Are you sure you want to remove <strong>"${productName}"</strong> from <strong>"${customerName}"</strong>?<br><small class="text-danger">This action cannot be undone.</small>`,
                    action,
                    row,
                    null // No callback needed
                );
            });
        });
    });
</script>
@endsection