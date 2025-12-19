@extends('layouts.admin')

@section('title', 'Customer Profile - ' . $customer->name)

@section('content')
<div class="container-fluid p-4">
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
    
    @if(session('success') && strpos(session('success'), 'Customer updated successfully') !== false)
        <script>
            // Refresh the page to ensure images are displayed properly after update
            setTimeout(function() {
                window.location.reload();
            }, 1500);
        </script>
    @endif
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Customer Profile</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">{{ $customer->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Billing
            </a>
            <a href="{{ route('admin.customers.edit', $customer->c_id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </a>
            <!--  -->
            <a href="{{ route('admin.customer-to-products.assign') }}" class="btn btn-success">
                <i class="fas fa-user-tag me-2"></i>Assign product
            </a>
            
            <form action="{{ route('admin.customers.toggle-status', $customer->c_id) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-{{ $customer->is_active ? 'warning' : 'success' }}">
                    <i class="fas fa-{{ $customer->is_active ? 'ban' : 'check' }} me-2"></i>
                    {{ $customer->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-file-invoice fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Invoices</h6>
                            <h3 class="mb-0">{{ $totalInvoices }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Paid</h6>
                            <h3 class="mb-0">৳{{ number_format($totalPaid, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="fas fa-exclamation-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Due</h6>
                            <h3 class="mb-0">৳{{ number_format($totalDue, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-box fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Active Products</h6>
                            <h3 class="mb-0">{{ $customer->customerproducts->where('status', 'active')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="row g-4 mb-4">
        <!-- Personal Information -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </h5>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 40%;">Customer ID:</td>
                                <td class="fw-semibold">{{ $customer->customer_id }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Full Name:</td>
                                <td class="fw-semibold">{{ $customer->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td>{{ $customer->email }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phone:</td>
                                <td>{{ $customer->phone }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Address:</td>
                                <td>{{ $customer->address }}</td>
                            </tr>
                            @if($customer->connection_address)
                            <tr>
                                <td class="text-muted">Connection Address:</td>
                                <td>{{ $customer->connection_address }}</td>
                            </tr>
                            @endif
                            @if($customer->id_type)
                            <tr>
                                <td class="text-muted">ID Type:</td>
                                <td>{{ $customer->id_type }}</td>
                            </tr>
                            @endif
                            @if($customer->id_number)
                            <tr>
                                <td class="text-muted">ID Number:</td>
                                <td>{{ $customer->id_number }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td>
                                    @if($customer->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Member Since:</td>
                                <td>{{ $customer->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">NID/ID:</td>
                                <td>{{ $customer->nid_number }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Active Products // All Products -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-primary mb-3">
                            <i class="fas fa-box me-2"></i>All Products 
                            </h5>
                            <a href="{{ route('admin.customer-to-products.index', ['customer_id' => $customer->c_id]) }}" class="btn btn-sm btn-outline-primary">
                                View
                            </a>   
                        </div>                                 
                    @if($customer->customerproducts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($customer->customerproducts as $cp)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 {{ $cp->status !== 'active' ? 'text-decoration-line-through text-muted' : '' }}">{{ $cp->product->name }}</h6>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-tag me-1"></i>{{ ucfirst($cp->product->product_type ?? 'N/A') }}
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-calendar me-1"></i>Billing: 
                                                {{ match($cp->billing_cycle_months ?? 1) {
                                                    1 => 'Monthly',
                                                    3 => 'Quarterly',
                                                    6 => 'Semi-Annual',
                                                    12 => 'Annual',
                                                    default => $cp->billing_cycle_months . ' Months'
                                                } }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary {{ $cp->status !== 'active' ? 'text-decoration-line-through' : '' }}">৳{{ number_format($cp->product->monthly_price ?? 0, 2) }}/mo</div>
                                            @if($cp->billing_cycle_months > 1)
                                                <small class="text-muted d-block">৳{{ number_format(($cp->product->monthly_price ?? 0) * $cp->billing_cycle_months, 2) }}/cycle</small>
                                            @endif
                                            <span class="badge bg-{{ $cp->status === 'active' ? 'success' : 'secondary' }} mt-1">
                                                {{ $cp->status !== 'active' ? 'Deactivated' : ucfirst($cp->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
                            <p>No products assigned yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-primary mb-0">
                    <i class="fas fa-file-invoice me-2"></i>Recent Invoices
                </h5>
                <a href="{{ route('admin.customers.billing-history', $customer->c_id) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            @if($recentInvoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice ID</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentInvoices as $invoice)
                                <tr>
                                    <td class="fw-semibold">{{ $invoice->invoice_id }}</td>
                                    <td>{{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>৳{{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                                    <td>৳{{ number_format($invoice->received_amount ?? 0, 2) }}</td>
                                    <td>
                                        @if($invoice->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($invoice->status === 'partial')
                                            <span class="badge bg-warning text-dark">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->invoice_id)
                                            <a href="{{ route('admin.billing.view-invoice', ['id' => $invoice->invoice_id]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-file-invoice fa-3x mb-3 opacity-50"></i>
                    <p>No invoices found</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">
                <i class="fas fa-money-bill-wave me-2"></i>Recent Payments
            </h5>
            @if($recentPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Payment ID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Invoice</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                                <tr>
                                    <td class="fw-semibold">{{ $payment->payment_id }}</td>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</td>
                                    <td class="text-success fw-bold">৳{{ number_format($payment->amount ?? 0, 2) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($payment->payment_method ?? 'N/A') }}</span>
                                    </td>
                                    <td>
                                        @if($payment->invoice && $payment->invoice_id)
                                            <a href="{{ route('admin.billing.view-invoice', ['id' => $payment->invoice_id]) }}">
                                                {{ $payment->invoice->invoice_number }}
                                            </a>
                                        @elseif($payment->invoice_id)
                                            <span class="text-muted">Invoice #{{ $payment->invoice_id }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" disabled title="Payment details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-money-bill-wave fa-3x mb-3 opacity-50"></i>
                    <p>No payments found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.card {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);

}
.table th, .table td {
    vertical-align: middle;
    padding: 0.75rem;
}
.badge {
    font-size: 0.8rem;
    border-radius: 6px;
    padding: 0.35em 0.65em;
}
h5.text-primary {
    letter-spacing: 0.3px;
}
.list-group-item {
    border-left: 0;
    border-right: 0;
}
.list-group-item:first-child {
    border-top: 0;
}
.list-group-item:last-child {
    border-bottom: 0;
}
/* Deactivated product styling */
.text-decoration-line-through {
    text-decoration: line-through !important;
}
</style>
@endsection
