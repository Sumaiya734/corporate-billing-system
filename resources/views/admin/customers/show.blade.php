@extends('layouts.admin')

@section('title', 'Customer Profile - ' . $customer->name)

@section('content')
<div class="container-fluid p-4">
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
                <i class="fas fa-arrow-left me-1"></i>Back to Customers
            </a>
            <a href="{{ route('admin.customers.edit', $customer->c_id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </a>
            <a href="{{ route('admin.customer-to-products.assign', ['customer_id' => $customer->c_id]) }}" class="btn btn-success">
                <i class="fas fa-user-tag me-2"></i>Assign Product
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

    <!-- Customer Info Card -->
    <div class="row mb-4">
        <!-- Profile Picture and ID Cards Section -->
        <div class="col-lg-4 mb-4">
            <!-- Profile Picture Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    @if($customer->profile_picture)
                        <img src="{{ asset('storage/' . $customer->profile_picture) }}" 
                             alt="{{ $customer->name }}" 
                             class="rounded-circle mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #f8f9fa;">
                    @else
                        <div class="avatar-circle bg-gradient-primary text-white mx-auto mb-3" 
                             style="width: 150px; height: 150px; font-size: 3.5rem; line-height: 150px;">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $customer->name }}</h4>
                    <p class="text-muted mb-3">{{ $customer->customer_id }}</p>
                    <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }} px-3 py-2 fs-6">
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <!-- ID Cards Section -->
            @if($customer->id_card_front || $customer->id_card_back)
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>ID Cards</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($customer->id_card_front)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-2">Front Side</label>
                            <div class="id-card-container">
                                <img src="{{ asset('storage/' . $customer->id_card_front) }}" 
                                     alt="ID Card Front" 
                                     class="img-fluid rounded border"
                                     style="max-height: 150px; width: 100%; object-fit: cover;">
                                <div class="mt-2 text-center">
                                    <a href="{{ asset('storage/' . $customer->id_card_front) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-expand me-1"></i>View Full
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($customer->id_card_back)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small d-block mb-2">Back Side</label>
                            <div class="id-card-container">
                                <img src="{{ asset('storage/' . $customer->id_card_back) }}" 
                                     alt="ID Card Back" 
                                     class="img-fluid rounded border"
                                     style="max-height: 150px; width: 100%; object-fit: cover;">
                                <div class="mt-2 text-center">
                                    <a href="{{ asset('storage/' . $customer->id_card_back) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-expand me-1"></i>View Full
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($customer->id_type && $customer->id_number)
                        <div class="col-12 mt-3">
                            <div class="alert alert-info py-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="fw-bold">ID Type:</small>
                                        <p class="mb-0">{{ ucfirst($customer->id_type) }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="fw-bold">ID Number:</small>
                                        <p class="mb-0">{{ $customer->id_number }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Contact Information Section -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contact Information</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i>Print Info</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Export Details</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Info -->
                        <div class="col-md-6 mb-4">
                            <div class="info-card bg-light p-3 rounded h-100">
                                <h6 class="text-muted mb-3"><i class="fas fa-user-circle me-2"></i>Basic Information</h6>
                                <div class="mb-3">
                                    <label class="text-muted small d-block">Email</label>
                                    <p class="mb-0">
                                        @if($customer->email)
                                            <i class="fas fa-envelope me-2 text-primary"></i>
                                            <a href="mailto:{{ $customer->email }}" class="text-decoration-none">{{ $customer->email }}</a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small d-block">Phone</label>
                                    <p class="mb-0">
                                        @if($customer->phone)
                                            <i class="fas fa-phone me-2 text-primary"></i>
                                            <a href="tel:{{ $customer->phone }}" class="text-decoration-none">{{ $customer->phone }}</a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-muted small d-block">Registration Date</label>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar me-2 text-primary"></i>
                                        {{ $customer->created_at->format('M d, Y') }}
                                        <small class="text-muted ms-2">({{ $customer->created_at->diffForHumans() }})</small>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Address Info -->
                        <div class="col-md-6 mb-4">
                            <div class="info-card bg-light p-3 rounded h-100">
                                <h6 class="text-muted mb-3"><i class="fas fa-map-marked-alt me-2"></i>Address Information</h6>
                                <div class="mb-3">
                                    <label class="text-muted small d-block">Residential Address</label>
                                    <p class="mb-0">
                                        @if($customer->address)
                                            <i class="fas fa-home me-2 text-primary"></i>
                                            {{ $customer->address }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="text-muted small d-block">Connection Address</label>
                                    <p class="mb-0">
                                        @if($customer->connection_address)
                                            <i class="fas fa-network-wired me-2 text-primary"></i>
                                            {{ $customer->connection_address }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Active Products ({{ $customer->customerproducts->where('status', 'active')->count() }})</h5>
            <a href="{{ route('admin.customer-to-products.assign', ['customer_id' => $customer->c_id]) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Assign New Product
            </a>
        </div>
        <div class="card-body">
            @if($customer->customerproducts->where('status', 'active')->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Billing Cycle</th>
                                <th>Status</th>
                                <th>Assigned Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->customerproducts->where('status', 'active') as $cp)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-box text-primary me-3"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $cp->product->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $cp->product->description ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold text-success">৳{{ number_format($cp->custom_price ?? $cp->product->monthly_price ?? 0, 2) }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $cp->billing_cycle_months ?? 1 }} Month(s)
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $cp->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($cp->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($cp->assign_date)
                                        {{ \Carbon\Carbon::parse($cp->assign_date)->format('M d, Y') }}
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($cp->assign_date)->diffForHumans() }}</div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.customer-to-products.edit', $cp->cp_id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.customer-to-products.destroy', $cp->cp_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Remove" onclick="return confirm('Are you sure you want to remove this product?')">
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
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-box-open fa-3x text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-muted mb-2">No Active Products</h5>
                    <p class="text-muted mb-4">This customer doesn't have any active products assigned yet.</p>
                    <a href="{{ route('admin.customer-to-products.assign', ['customer_id' => $customer->c_id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Assign First Product
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Recent Invoices ({{ $customer->invoices->count() }})</h5>
            <a href="{{ route('admin.invoices.create', ['customer_id' => $customer->c_id]) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Create Invoice
            </a>
        </div>
        <div class="card-body">
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
                                <tr class="{{ $invoice->status === 'unpaid' && $invoice->due_date < now() ? 'table-danger' : '' }}">
                                    <td class="fw-semibold">{{ $invoice->invoice_id }}</td>
                                    <td>{{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                                        @if($invoice->status === 'unpaid' && $invoice->due_date < now())
                                            <div class="small text-danger">Overdue</div>
                                        @endif
                                    </td>
                                    <td>৳{{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                                    <td>৳{{ number_format($invoice->received_amount ?? 0, 2) }}</td>
                                    <td>
                                        @if($invoice->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($invoice->status === 'partial')
                                            <span class="badge bg-warning text-dark">Partial</span>
                                        @elseif($invoice->status === 'overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-secondary">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.billing.view-invoice', ['invoiceId' => $invoice->i_id]) }}" class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.billing.edit-invoice', $invoice->i_id) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.billing.download-invoice', $invoice->i_id) }}" class="btn btn-outline-success" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($customer->invoices->count() > 5)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.customers.billing-history', $customer->c_id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>View All Invoices
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-file-invoice-dollar fa-3x text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-muted mb-2">No Invoices Found</h5>
                    <p class="text-muted mb-4">No invoices have been generated for this customer yet.</p>
                    <a href="{{ route('admin.invoices.create', ['customer_id' => $customer->c_id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create First Invoice
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Recent Payments</h5>
            <a href="{{ route('admin.billing.create-payment', ['customer_id' => $customer->c_id]) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Record Payment
            </a>
        </div>
        <div class="card-body">
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
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                                <tr>
                                    <td class="fw-semibold">#{{ $payment->payment_id }}</td>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</td>
                                    <td class="fw-bold text-success">৳{{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment->invoice)
                                            <a href="{{ route('admin.billing.view-invoice', ['invoiceId' => $payment->invoice_id]) }}" class="text-decoration-none">
                                                {{ $payment->invoice->invoice_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="#" class="btn btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-success" title="Download Receipt">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($customer->payments->count() > 5)
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>View All Payments
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-money-bill-wave fa-3x text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-muted mb-2">No Payments Found</h5>
                    <p class="text-muted mb-4">No payment records found for this customer.</p>
                    <a href="{{ route('admin.billing.create-payment', ['customer_id' => $customer->c_id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Record First Payment
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Avatar Circle */
.avatar-circle {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* ID Card Container */
.id-card-container {
    background: white;
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Info Cards */
.info-card {
    transition: all 0.3s ease;
    border: 1px solid #f1f3f4;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Empty State */
.empty-state-icon {
    opacity: 0.4;
}

/* Table Improvements */
.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
}

/* Badge Styling */
.badge {
    padding: 0.35em 0.65em;
    font-weight: 500;
}

/* Button Groups */
.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

/* Overdue invoice styling */
.table-danger {
    background-color: rgba(220, 53, 69, 0.05);
}

/* Responsive Design */
@media (max-width: 768px) {
    .avatar-circle {
        width: 100px !important;
        height: 100px !important;
        font-size: 2.5rem !important;
        line-height: 100px !important;
    }
    
    .card-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start !important;
    }
    
    .card-header .btn {
        align-self: stretch;
    }
    
    .d-flex.gap-2 {
        flex-wrap: wrap;
    }
    
    .btn-group {
        flex-wrap: nowrap;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add confirmation for deactivate/activate button
    const toggleStatusForm = document.querySelector('form[action*="toggle-status"]');
    if (toggleStatusForm) {
        toggleStatusForm.addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            const action = button.textContent.trim();
            const confirmed = confirm(`Are you sure you want to ${action.toLowerCase()} this customer?`);
            if (!confirmed) {
                e.preventDefault();
            }
        });
    }
    
    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
</script>
@endsection