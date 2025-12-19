@extends('layouts.customer')

@section('title', 'Payment History - Nanosoft')

@section('content')
<div class="payments-page">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-2">
                    <i class="fas fa-credit-card me-2 text-success"></i>Payment History
                </h1>
                <p class="text-muted mb-0">View all your payment transactions and receipts.</p>
            </div>
            <div>
                <a href="{{ route('customer.invoices.index') }}" class="btn btn-outline-primary me-2">
                    <i class="fas fa-file-invoice me-1"></i> View Invoices
                </a>
                <a href="{{ route('customer.payments.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Make Payment
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-soft-success rounded-3 p-3 me-3">
                            <i class="fas fa-credit-card text-success fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Payments</h6>
                            <h3 class="fw-bold text-success">{{ $totalPayments }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-soft-primary rounded-3 p-3 me-3">
                            <i class="fas fa-money-bill-wave text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Amount</h6>
                            <h3 class="fw-bold text-primary">৳{{ number_format($totalAmount, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-soft-warning rounded-3 p-3 me-3">
                            <i class="fas fa-building-columns text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Bank Payments</h6>
                            <h3 class="fw-bold text-warning">{{ $methodCounts['bank'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-soft-info rounded-3 p-3 me-3">
                            <i class="fas fa-mobile-alt text-info fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Mobile Payments</h6>
                            <h3 class="fw-bold text-info">{{ $methodCounts['mobile'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customer.payments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Payment Method</label>
                    <select name="method" class="form-select">
                        <option value="all" {{ request('method') == 'all' ? 'selected' : '' }}>All Methods</option>
                        <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank" {{ request('method') == 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="card" {{ request('method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="mobile" {{ request('method') == 'mobile' ? 'selected' : '' }}>Mobile Banking</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="all" {{ request('month') == 'all' ? 'selected' : '' }}>All Months</option>
                        @foreach($months as $key => $month)
                            <option value="{{ $key }}" {{ request('month') == $key ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="all" {{ request('year') == 'all' ? 'selected' : '' }}>All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('customer.payments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                <i class="fas fa-history me-2 text-secondary"></i>Payment Transactions
            </h5>
        </div>
        <div class="card-body">
            @if($payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="border-0">Payment ID</th>
                                <th class="border-0">Invoice</th>
                                <th class="border-0">Payment Date</th>
                                <th class="border-0">Amount</th>
                                <th class="border-0">Method</th>
                                <th class="border-0">Transaction ID</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td>
                                        <div class="fw-bold">#{{ $payment->payment_id }}</div>
                                        <small class="text-muted">{{ $payment->payment_date->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $payment->invoice->invoice_number ?? 'N/A' }}</div>
                                        <small class="text-muted">
                                            {{ $payment->invoice->customerProduct->product->name ?? 'Product' }}
                                        </small>
                                    </td>
                                    <td>
                                        {{ $payment->payment_date->format('M d, Y') }}
                                    </td>
                                    <td class="fw-bold text-success">
                                        ৳{{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td>
                                        @if($payment->payment_method == 'bank')
                                            <span class="badge bg-primary rounded-pill px-3 py-1">
                                                <i class="fas fa-building-columns me-1"></i> Bank
                                            </span>
                                        @elseif($payment->payment_method == 'card')
                                            <span class="badge bg-info rounded-pill px-3 py-1">
                                                <i class="fas fa-credit-card me-1"></i> Card
                                            </span>
                                        @elseif($payment->payment_method == 'mobile')
                                            <span class="badge bg-success rounded-pill px-3 py-1">
                                                <i class="fas fa-mobile-alt me-1"></i> Mobile
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-1">
                                                <i class="fas fa-money-bill me-1"></i> Cash
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($payment->transaction_id)
                                            <code>{{ $payment->transaction_id }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success rounded-pill px-3 py-1">
                                            <i class="fas fa-check-circle me-1"></i> Completed
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('customer.payments.show', $payment->payment_id) }}" 
                                               class="btn btn-outline-primary"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customer.payments.download', $payment->payment_id) }}" 
                                               class="btn btn-outline-success"
                                               data-bs-toggle="tooltip"
                                               title="Download Receipt">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="{{ route('customer.invoices.show', $payment->invoice_id) }}" 
                                               class="btn btn-outline-info"
                                               data-bs-toggle="tooltip"
                                               title="View Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($payments->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <p class="mb-0 text-muted">
                                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} payments
                            </p>
                        </div>
                        <div>
                            {{ $payments->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="empty-state-icon mb-4">
                        <i class="fas fa-credit-card fa-4x text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Payments Found</h4>
                    <p class="text-muted mb-4">You haven't made any payments yet.</p>
                    <a href="{{ route('customer.payments.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Make Your First Payment
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Payments Summary -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Recent Payments (Last 6 Months)
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentPayments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentPayments as $payment)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Payment #{{ $payment->payment_id }}</h6>
                                            <small class="text-muted">
                                                {{ $payment->payment_date->format('F d, Y') }} • 
                                                Invoice #{{ $payment->invoice->invoice_number ?? 'N/A' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <h6 class="mb-1 text-success">৳{{ number_format($payment->amount, 2) }}</h6>
                                            <small class="text-muted text-capitalize">{{ $payment->payment_method }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <p class="text-muted mb-0">No recent payments found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-percentage me-2 text-warning"></i>Payment Methods
                    </h6>
                </div>
                <div class="card-body">
                    <div class="method-stats">
                        @php
                            $totalMethods = array_sum($methodCounts);
                        @endphp
                        
                        @foreach($methodCounts as $method => $count)
                            @php
                                $percentage = $totalMethods > 0 ? round(($count / $totalMethods) * 100) : 0;
                                $colors = [
                                    'cash' => 'bg-secondary',
                                    'bank' => 'bg-primary',
                                    'card' => 'bg-info',
                                    'mobile' => 'bg-success',
                                ];
                                $icons = [
                                    'cash' => 'fas fa-money-bill',
                                    'bank' => 'fas fa-building-columns',
                                    'card' => 'fas fa-credit-card',
                                    'mobile' => 'fas fa-mobile-alt',
                                ];
                            @endphp
                            
                            <div class="method-item mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-medium text-capitalize">
                                        <i class="{{ $icons[$method] ?? 'fas fa-circle' }} me-2"></i>{{ $method }}
                                    </span>
                                    <span>{{ $count }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $colors[$method] ?? 'bg-secondary' }}" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .payments-page {
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

    .method-item .progress {
        border-radius: 10px;
        overflow: hidden;
    }

    .method-item .progress-bar {
        border-radius: 10px;
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

        // Animate progress bars
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
                bar.style.transition = 'width 1s ease';
            }, 300);
        });
    });
</script>
@endsection