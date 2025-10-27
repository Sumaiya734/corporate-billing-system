@extends('layouts.admin')

@section('title', 'All Customers')

@section('content')
<div class="p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-dark">
                <i class="fas fa-users me-2 text-primary"></i>All Customers
            </h2>
            <nav aria-label="breadcrumb">
                
            </nav>
        </div>
        
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Customer Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $customers->count() }}</h4>
                    <small>Total Customers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            @php
                $activeCount = $customers->where('is_active', 1)->count();
            @endphp
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $activeCount }}</h4>
                    <small>Active Customers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            @php
                $inactiveCount = $customers->where('is_active', 0)->count();
            @endphp
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $inactiveCount }}</h4>
                    <small>Inactive Customers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            @php
                $suspendedCount = $customers->where('status', 'suspended')->count();
            @endphp
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $suspendedCount }}</h4>
                    <small>Suspended Customers</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Customer List
            </h5>
            <div class="d-flex">
                <input type="text" class="form-control form-control-sm me-2" placeholder="Search customers..." id="searchInput">
                <button class="btn btn-sm btn-outline-secondary" type="button" id="filterButton">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="customersTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Customer Info</th>
                                <th>Services</th>
                                <th>Monthly Bill</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                            <tr>
                                <td class="fw-bold">
                                    <small class="text-muted d-block">#{{ $customer->id }}</small>
                                    {{ $customer->customer_id }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-start">
                                        <div class="avatar-sm bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                       <div class="flex-grow-1">
    <strong class="d-block mb-1">{{ $customer->name }}</strong>
    <div class="text-muted small mb-1">
        <i class="fas fa-envelope me-1"></i>{{ $customer->email }}
    </div>
    <div class="text-muted small mb-1">
        <i class="fas fa-phone me-1"></i>{{ $customer->phone ?? 'No phone' }}
    </div>
    <div class="text-muted small">
        <i class="fas fa-map-marker-alt me-1"></i>{{ $customer->address ?? 'No address' }}
    </div>
</div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        // Get services for this customer from related table
                                        $services = $customer->services ?? $customer->subscriptions ?? collect();
                                        $serviceNames = [];
                                        
                                        if ($services instanceof \Illuminate\Database\Eloquent\Collection) {
                                            $serviceNames = $services->pluck('name')->toArray();
                                        } elseif (is_array($services)) {
                                            $serviceNames = array_column($services, 'name');
                                        }
                                        
                                        $servicesCount = count($serviceNames);
                                    @endphp
                                    
                                    @if($servicesCount > 0)
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-info me-1 mb-1" title="{{ implode(', ', $serviceNames) }}">
                                                {{ $servicesCount }} Service{{ $servicesCount > 1 ? 's' : '' }}
                                            </span>
                                            <small class="text-muted">
                                                {{ implode(', ', array_slice($serviceNames, 0, 2)) }}
                                                @if($servicesCount > 2)
                                                    +{{ $servicesCount - 2 }} more
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">No Services</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Calculate monthly bill from related services/subscriptions
                                        $monthlyBill = 0;
                                        
                                        // Method 1: If calculating from services
                                        if (isset($customer->services)) {
                                            $monthlyBill = $customer->services->sum('monthly_price');
                                        }
                                        // Method 2: If calculating from subscriptions
                                        elseif (isset($customer->subscriptions)) {
                                            $monthlyBill = $customer->subscriptions->sum('monthly_fee');
                                        }
                                        // Method 3: If customer has a direct monthly_bill field
                                        elseif (isset($customer->monthly_bill)) {
                                            $monthlyBill = $customer->monthly_bill;
                                        }
                                    @endphp
                                    
                                    <div class="d-flex flex-column">
                                        <strong class="text-success fs-6">৳{{ number_format($monthlyBill, 2) }}</strong>
                                        <small class="text-muted">per month</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $status = $customer->is_active ? 'active' : 'inactive';
                                        $statusColors = [
                                            'active' => 'success',
                                            'inactive' => 'secondary',
                                            'suspended' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }} px-2 py-1">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($customer->created_at)
                                        <div class="d-flex flex-column">
                                            <span>{{ $customer->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                                        </div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-info me-1" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this customer?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h4>No Customers Found</h4>
                    <p class="text-muted">You haven't added any customers yet.</p>
                    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Add Your First Customer
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-sm {
    font-size: 16px;
    font-weight: bold;
}
.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
}
.badge {
    font-size: 0.75em;
}
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.03);
}
</style>

<script>
// Simple search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#customersTable tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});
</script>
@endsection