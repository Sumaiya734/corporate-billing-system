@extends('layouts.admin')

@section('title', 'Customer Packages')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-user-tag me-2"></i>Customer Packages</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.customer-to-packages.assign') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Assign Package
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        @if($customer->activeCustomerPackages->count() > 0)
                            @foreach($customer->activeCustomerPackages as $index => $cp)
                                <tr>
                                    @if($index === 0)
                                        <td rowspan="{{ $customer->activeCustomerPackages->count() }}">
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
                                    
                                    <!-- Individual Actions Column -->
                                    <td class="text-center">
                                        <div class="btn-group d-flex justify-content-center gap-1">
                                            @if($cp->cp_id)
                                                <!-- Edit Button -->
                                                <a href="{{ route('admin.customer-to-packages.edit', $cp->cp_id) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Edit Package">
                                                   <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Renew Button -->
                                                <form action="{{ route('admin.customer-to-packages.renew', $cp->cp_id) }}" 
                                                      method="POST" onsubmit="return confirm('Renew this package for customer?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Renew Package">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </form>

                                                @php
                                                    $isActive = $cp->status === 'active';
                                                    $newStatus = $isActive ? 'expired' : 'active';
                                                    $confirmText = $isActive
                                                        ? 'Are you sure you want to pause this package?'
                                                        : 'Are you sure you want to activate this package?';
                                                    $buttonIcon = $isActive ? 'fa-pause' : 'fa-play';
                                                    $buttonTitle = $isActive ? 'Pause Package' : 'Activate Package';
                                                @endphp

                                                <!-- Toggle Status Button -->
                                                <form action="{{ route('admin.customer-to-packages.update', $cp->cp_id) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('{{ $confirmText }}');">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="{{ $newStatus }}">
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-warning"
                                                            title="{{ $buttonTitle }}">
                                                        <i class="fas {{ $buttonIcon }}"></i>
                                                    </button>
                                                </form>

                                                <!-- Delete Button -->
                                                <form action="{{ route('admin.customer-to-packages.destroy', $cp->cp_id) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to remove this package? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Package">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">No actions available</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5>No Customer Packages Found</h5>
                                <p class="text-muted">No packages have been assigned to customers yet.</p>
                                <a href="{{ route('admin.customer-to-packages.assign') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Assign First Package
                                </a>
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
            </div>
            <nav>
                {{ $customers->links() }}
            </nav>
        </div>
    @endif
</div>

<!-- Renewal Modal -->
<div class="modal fade" id="renewalModal" tabindex="-1" aria-labelledby="renewalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renewalModalLabel">Renew Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to renew this package?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    This will extend the billing period by one month.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="renewalForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Renew Package</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Change Package Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="statusMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="statusForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" id="statusInput">
                    <button type="submit" class="btn btn-warning" id="statusConfirmBtn">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this package from the customer?</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Package</button>
                </form>
            </div>
        </div>
    </div>
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
    /* Modal Styles */
    .modal-header {
        background-color: #2c3e50;
        color: white;
    }
    .modal-header .btn-close {
        filter: invert(1);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Renewal Modal Handler
    const renewalButtons = document.querySelectorAll('[data-renewal]');
    renewalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const packageId = this.getAttribute('data-package-id');
            const customerName = this.getAttribute('data-customer-name');
            const packageName = this.getAttribute('data-package-name');
            
            document.getElementById('renewalMessage').innerHTML = 
                `Renew package <strong>${packageName}</strong> for customer <strong>${customerName}</strong>?`;
            
            document.getElementById('renewalForm').action = `/admin/customer-to-packages/${packageId}/renew`;
        });
    });

    // Status Change Modal Handler
    const statusButtons = document.querySelectorAll('[data-status]');
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const packageId = this.getAttribute('data-package-id');
            const customerName = this.getAttribute('data-customer-name');
            const packageName = this.getAttribute('data-package-name');
            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = this.getAttribute('data-new-status');
            
            const action = currentStatus === 'active' ? 'pause' : 'activate';
            const statusText = newStatus === 'active' ? 'active' : 'paused';
            
            document.getElementById('statusMessage').innerHTML = 
                `Are you sure you want to ${action} the package <strong>${packageName}</strong> for customer <strong>${customerName}</strong>?`;
            
            document.getElementById('statusInput').value = newStatus;
            document.getElementById('statusForm').action = `/admin/customer-to-packages/${packageId}`;
            document.getElementById('statusConfirmBtn').textContent = `${action.charAt(0).toUpperCase() + action.slice(1)} Package`;
        });
    });

    // Delete Modal Handler
    const deleteButtons = document.querySelectorAll('[data-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const packageId = this.getAttribute('data-package-id');
            const customerName = this.getAttribute('data-customer-name');
            const packageName = this.getAttribute('data-package-name');
            
            document.getElementById('deleteMessage').innerHTML = 
                `Remove package <strong>${packageName}</strong> from customer <strong>${customerName}</strong>?`;
            
            document.getElementById('deleteForm').action = `/admin/customer-to-packages/${packageId}`;
        });
    });

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endsection