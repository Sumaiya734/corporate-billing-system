@extends('layouts.admin')
@section('title', 'Customer Details - ' . $customer->name)
@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-user me-2 text-primary"></i>{{ $customer->name }}
            </h2>
            <p class="text-muted mb-0">Customer ID: {{ $customer->customer_id }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
            <a href="{{ route('admin.customers.edit', $customer->c_id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Customer
            </a>
        </div>
    </div>

    <!-- Customer Info Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    @if($customer->profile_picture)
                        <img src="{{ asset('storage/' . $customer->profile_picture) }}" alt="{{ $customer->name }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="avatar-circle bg-gradient-primary text-white mx-auto mb-3" style="width: 120px; height: 120px; font-size: 3rem; line-height: 120px;">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $customer->name }}</h4>
                    <p class="text-muted mb-3">{{ $customer->customer_id }}</p>
                    <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }} px-3 py-2">
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email</label>
                            <p class="mb-0"><i class="fas fa-envelope me-2"></i>{{ $customer->email ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Phone</label>
                            <p class="mb-0"><i class="fas fa-phone me-2"></i>{{ $customer->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small">Address</label>
                            <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>{{ $customer->address ?? 'N/A' }}</p>
                        </div>
                        @if($customer->connection_address)
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small">Connection Address</label>
                            <p class="mb-0"><i class="fas fa-network-wired me-2"></i>{{ $customer->connection_address }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="text-muted small">Registration Date</label>
                            <p class="mb-0"><i class="fas fa-calendar me-2"></i>{{ $customer->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Active Products ({{ $customer->customerproducts->where('is_active', true)->count() }})</h5>
        </div>
        <div class="card-body">
            @if($customer->customerproducts->where('is_active', true)->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Billing Cycle</th>
                                <th>Status</th>
                                <th>Assigned Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->customerproducts->where('is_active', true) as $cp)
                            <tr>
                                <td>{{ $cp->product->name ?? 'N/A' }}</td>
                                <td>৳{{ number_format($cp->product_price ?? $cp->product->monthly_price ?? 0, 2) }}</td>
                                <td>{{ $cp->billing_cycle_months ?? 1 }} Month(s)</td>
                                <td><span class="badge bg-{{ $cp->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($cp->status) }}</span></td>
                                <td>{{ $cp->assign_date ? \Carbon\Carbon::parse($cp->assign_date)->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">No active products assigned</p>
            @endif
        </div>
    </div>

    <!-- Invoices -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Recent Invoices ({{ $customer->invoices->count() }})</h5>
        </div>
        <div class="card-body">
            @if($customer->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->invoices->take(10) as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>৳{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>৳{{ number_format($invoice->received_amount, 2) }}</td>
                                <td>৳{{ number_format($invoice->next_due, 2) }}</td>
                                <td><span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'danger') }}">{{ ucfirst($invoice->status) }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">No invoices found</p>
            @endif
        </div>
    </div>

    <!-- Payments -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Recent Payments ({{ $customer->payments->count() }})</h5>
        </div>
        <div class="card-body">
            @if($customer->payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->payments->take(10) as $payment)
                            <tr>
                                <td>#{{ $payment->payment_id }}</td>
                                <td>৳{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td><span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($payment->status) }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">No payments found</p>
            @endif
        </div>
    </div>
</div>
@endsection
