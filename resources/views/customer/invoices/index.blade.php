@extends('layouts.customer')

@section('title', 'My Invoices - Nanosoft')

@section('content')
<div class="invoices-page">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-2">
                    <i class="fas fa-file-invoice me-2 text-primary"></i>My Invoices
                </h1>
                <p class="text-muted mb-0">View and manage all your billing invoices.</p>
            </div>
            <div>
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        @php
            $totalAmount = $invoices->sum('total_amount');
            $totalPaid = $invoices->sum('received_amount');
            $totalDue = $totalAmount - $totalPaid;
        @endphp
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-soft-primary rounded-3 p-3 me-3">
                            <i class="fas fa-file-invoice text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Invoices</h6>
                            <h3 class="fw-bold text-primary">{{ $invoices->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-soft-success rounded-3 p-3 me-3">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Paid Amount</h6>
                            <h3 class="fw-bold text-success">৳{{ number_format($totalPaid, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-soft-warning rounded-3 p-3 me-3">
                            <i class="fas fa-clock text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Due Amount</h6>
                            <h3 class="fw-bold text-warning">৳{{ number_format($totalDue, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-secondary"></i>Invoice List
            </h5>
        </div>
        <div class="card-body">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Product</th>
                                <th>Issue Date</th>
                                <th>Total Amount</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Status</th>
                                <!-- <th class="text-end">Actions</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $invoice->invoice_number }}</div>
                                        <small class="text-muted">ID: {{ $invoice->invoice_id }}</small>
                                    </td>
                                    <td>
                                        {{ $invoice->customerProduct->product->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $invoice->issue_date->format('M d, Y') }}
                                    </td>
                                    <td class="fw-bold">
                                        ৳{{ number_format($invoice->total_amount, 2) }}
                                    </td>
                                    <td class="text-success fw-bold">
                                        ৳{{ number_format($invoice->received_amount, 2) }}
                                    </td>
                                    <td class="text-danger fw-bold">
                                        ৳{{ number_format($invoice->total_amount - $invoice->received_amount, 2) }}
                                    </td>
                                    <td>
                                        @if($invoice->status == 'paid')
                                            <span class="badge bg-success rounded-pill px-3 py-1">
                                                <i class="fas fa-check-circle me-1"></i> Paid
                                            </span>
                                        @elseif($invoice->status == 'partial')
                                            <span class="badge bg-warning rounded-pill px-3 py-1">
                                                <i class="fas fa-clock me-1"></i> Partial
                                            </span>
                                        @else
                                            <span class="badge bg-danger rounded-pill px-3 py-1">
                                                <i class="fas fa-times-circle me-1"></i> Unpaid
                                            </span>
                                        @endif
                                    </td>
                                    <!-- <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('customer.invoices.show', $invoice->invoice_id) }}" 
                                               class="btn btn-outline-primary"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customer.invoices.download', $invoice->invoice_id) }}" 
                                               class="btn btn-outline-success"
                                               data-bs-toggle="tooltip"
                                               title="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if($invoice->status != 'paid')
                                                <button type="button" 
                                                        class="btn btn-outline-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#payModal{{ $invoice->invoice_id }}"
                                                        title="Make Payment">
                                                    <i class="fas fa-credit-card"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td> -->
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($invoices->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <p class="mb-0 text-muted">
                                Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
                            </p>
                        </div>
                        <div>
                            {{ $invoices->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="empty-state-icon mb-4">
                        <i class="fas fa-file-invoice fa-4x text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Invoices Found</h4>
                    <p class="text-muted mb-4">You don't have any invoices yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .invoices-page {
        animation: fadeIn 0.6s ease-out;
    }

    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .icon-wrapper {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-soft-primary { background-color: rgba(58, 123, 213, 0.1); }
    .bg-soft-success { background-color: rgba(34, 197, 94, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
    .bg-soft-info { background-color: rgba(6, 182, 212, 0.1); }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8fafc;
        transform: translateX(5px);
    }

    .empty-state-icon {
        width: 100px;
        height: 100px;
        background: #f8fafc;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .btn-group {
            flex-wrap: wrap;
            gap: 0.25rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    });
</script>
@endsection