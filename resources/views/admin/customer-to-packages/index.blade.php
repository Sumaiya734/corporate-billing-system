@extends('layouts.admin')

@section('title', 'Customer Packages')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-user-tag me-2"></i>Customer to Products</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.customer-to-packages.assign') }}" class="btn btn-primary">
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
    <div class="row mb-4">
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
                <div class="stats-number">{{ $activePackages }}</div>
                <div class="stats-label">Active Packages</div>
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
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.customer-to-packages.index') }}" method="GET" id="searchForm">
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
                        <label for="status" class="form-label">Package Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="package_type" class="form-label">Package Type</label>
                        <select class="form-select" id="package_type" name="package_type">
                            <option value="">All Types</option>
                            <option value="regular" {{ request('package_type') == 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="special" {{ request('package_type') == 'special' ? 'selected' : '' }}>Special</option>
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
                @if(request()->hasAny(['search', 'status', 'package_type']))
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
                            @if(request('package_type'))
                                <span class="badge bg-warning me-2 mb-1">
                                    Type: {{ ucfirst(request('package_type')) }}
                                    <a href="javascript:void(0)" onclick="removeFilter('package_type')" class="text-white ms-1">
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

    <!-- Customer Packages Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Customer Info</th>
                        <th>Package List</th>
                        <th>Package Price</th>
                        <th>Assign Date</th>
                        <th>Billing Months</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        @if($customer->customerPackages->count() > 0)
                            @foreach($customer->customerPackages as $index => $cp)
                                <tr>
                                    @if($index === 0)
                                        <td rowspan="{{ $customer->customerPackages->count() }}">
                                            <div class="customer-name">{{ $customer->name }}</div>
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
                                    
                                    <td class="package-cell">
                                        <div class="package-badge {{ optional($cp->package)->package_type === 'regular' ? 'regular-package' : 'special-package' }}">
                                            {{ optional($cp->package)->name ?? 'Unknown Package' }}
                                            @if(optional($cp->package)->package_type === 'regular')
                                                <small class="d-block text-muted">Main Package</small>
                                            @else
                                                <small class="d-block text-muted">Add-on</small>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td class="price-cell">
                                        <div><span class="currency">৳</span> {{ number_format(optional($cp->package)->monthly_price ?? 0, 2) }}</div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div>{{ \Carbon\Carbon::parse($cp->assign_date)->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($cp->assign_date)->diffForHumans() }}</small>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="billing-months">{{ $cp->billing_cycle_months }} Month{{ $cp->billing_cycle_months > 1 ? 's' : '' }}</div>
                                    </td>
                                    
                                    <td class="price-cell">
                                        <div class="total-price">
                                            <span class="currency">৳</span> 
                                            {{ number_format((optional($cp->package)->monthly_price ?? 0) * $cp->billing_cycle_months, 2) }}
                                        </div>
                                    </td>
                                    
                                    <!-- Individual Status Column -->
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
                                    
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5>No Customer Packages Found</h5>
                                <p class="text-muted">
                                    @if(request()->hasAny(['search', 'status', 'package_type']))
                                        No packages found matching your search criteria.
                                    @else
                                        No packages have been assigned to customers yet.
                                    @endif
                                </p>
                                @if(request()->hasAny(['search', 'status', 'package_type']))
                                    <a href="{{ route('admin.customer-to-packages.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Clear Search
                                    </a>
                                @else
                                    <a href="{{ route('admin.customer-to-packages.assign') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Assign First Package
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
                @if(request()->hasAny(['search', 'status', 'package_type']))
                    <span class="badge bg-info ms-2">Filtered Results</span>
                @endif
            </div>
            <nav>
                {{ $customers->withQueryString()->links('pagination::bootstrap-5') }}
            </nav>
        </div>
    @endif
</div>

<style>
    .page-title {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 1.5rem;
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
        transform: translateY(-5px);
    }
    .table-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .table {
        border: 2px solid #dee2e6;
        margin-bottom: 0;
    }
    .table th {
        border: 2px solid #dee2e6;
        font-weight: 600;
        padding: 15px;
        text-align: center;
        vertical-align: middle;
        background: #2c3e50;
    }
    .table td {
        padding: 15px;
        vertical-align: middle;
        border: 2px solid #dee2e6;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .package-badge {
        border-radius: 20px;
        padding: 8px 15px;
        margin: 2px;
        display: inline-block;
        font-size: 0.85rem;
        border: 1px solid;
        text-align: center;
        min-width: 120px;
    }
    .regular-package {
        background-color: #e3f2fd;
        color: #1976d2;
        border-color: #bbdefb;
    }
    .special-package {
        background-color: #fff3e0;
        color: #f57c00;
        border-color: #ffe0b2;
    }
    .customer-name {
        font-weight: 600;
        color: #2c3e50;
    }
    .customer-email {
        font-size: 0.85rem;
        color: #7f8c8d;
    }
    .billing-months {
        font-weight: 600;
        color: #2c3e50;
        padding: 5px 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
        display: inline-block;
    }
    .total-price {
        font-weight: 700;
        color: #27ae60;
    }
    .action-btn {
        border-radius: 20px;
        padding: 5px 10px;
        font-weight: 500;
        width: 100px;
        font-size: 0.75rem;
    }
    .stats-card {
        text-align: center;
        padding: 20px;
    }
    .stats-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: #3498db;
    }
    .stats-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
    }
    .stats-label {
        color: #7f8c8d;
        font-size: 0.9rem;
    }
    .price-cell {
        text-align: center;
    }
    .package-cell {
        text-align: center;
    }
    .currency {
        font-weight: 600;
        color: #2c3e50;
    }
    .btn-group {
        display: flex;
        flex-wrap: nowrap;
        gap: 4px;
    }
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
    }
    .filter-badge {
        cursor: pointer;
    }
    /* Pagination Styles */
    .pagination {
        margin-bottom: 0;
    }
    .page-link {
        color: #2c3e50;
        border: 1px solid #dee2e6;
    }
    .page-item.active .page-link {
        background-color: #2c3e50;
        border-color: #2c3e50;
    }
    .page-link:hover {
        color: #2c3e50;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    .package-warning {
    font-size: 0.8rem;
    padding: 0.5rem;
    border-radius: 5px;
    margin-top: 0.5rem;
}

.package-warning i {
    color: #856404;
}

.is-duplicate-package {
    border-color: #dc3545 !important;
    background-color: #f8d7da !important;
}

.customer-results-container {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    background: #fff;
}

.customer-result-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background 0.2s;
}

.customer-result-item:hover {
    background: #e9ecef;
}

.customer-result-item:last-child {
    border-bottom: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear search function
    window.clearSearch = function() {
        document.getElementById('search').value = '';
        document.getElementById('searchForm').submit();
    }

    // Remove individual filter
    window.removeFilter = function(param) {
        const url = new URL(window.location.href);
        url.searchParams.delete(param);
        window.location.href = url.toString();
    }

    // Auto-submit form when select filters change
    document.getElementById('status').addEventListener('change', function() {
        document.getElementById('searchForm').submit();
    });

    document.getElementById('package_type').addEventListener('change', function() {
        document.getElementById('searchForm').submit();
    });

    // Debounced search
    let searchTimeout;
    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('searchForm').submit();
        }, 500);
    });

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
});
</script>
@endsection