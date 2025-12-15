@extends('layouts.admin')

@section('title', 'Billing & Invoices - Admin Dashboard')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-0 page-title">
            <i class="fas fa-file-invoice me-2 text-primary"></i>Monthly Billing Summary
        </h2>
        <p class="text-muted mb-0">Monthly billing overview with previous due carry-forward</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="location.reload()" title="Refresh data">
            <i class="fas fa-sync-alt me-1"></i>Refresh
        </button>
        <button class="btn btn-outline-primary" onclick="exportBillingReport()">
            <i class="fas fa-download me-1"></i>Export Report
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateFromInvoicesModal">
            <i class="fas fa-sync me-1"></i>Generate Monthly Invoices
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBillingModal">
            <i class="fas fa-plus me-1"></i>Add Manual Billing
        </button>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start flex-grow-1">
                    <div>
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Active Customers</div>
                        <div class="h5 mb-0">{{ $totalActiveCustomers ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-white-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start flex-grow-1">
                    <div>
                        <div class="text-xs font-weight-bold text-uppercase mb-1">This Month Revenue</div>
                        <div class="h5 mb-0">৳ {{ number_format($currentMonthRevenue ?? 0, 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-white-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start flex-grow-1">
                    <div>
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Payments</div>
                        <div class="h5 mb-0">৳ {{ number_format($totalPendingAmount ?? 0, 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-white-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start flex-grow-1">
                    <div>
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Invoices</div>
                        <div class="h5 mb-0">{{ $totalInvoicesCount ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice fa-2x text-white-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Empty State -->
@if(empty($monthlySummary) || $monthlySummary->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No Billing Data Available</h4>
        <p class="text-muted mb-4">Get started by generating monthly invoices or adding manual billing data.</p>
        <div class="d-flex justify-content-center gap-2">
            @if($hasInvoices ?? false)
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateFromInvoicesModal">
                <i class="fas fa-sync me-1"></i>Generate Monthly Invoices
            </button>
            @endif
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBillingModal">
                <i class="fas fa-plus me-1"></i>Add Manual Billing
            </button>
        </div>
    </div>
</div>
@else
<!-- Billing Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Monthly Billing Overview
                    <span class="badge bg-success ms-2">
                        <i class="fas fa-database me-1"></i>Live Data
                    </span>
                </h5>
                <p class="text-muted mb-0 small">Monthly invoices with previous due carry-forward</p>
            </div>
            <div class="text-end">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Each month shows separate invoice with previous due included
                </small>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Billing Month</th>
                        <th>Customers</th>
                        <th>Total Amount</th>
                        <th>Received</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Bills</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlySummary as $month)
                    @php
                    $isCurrentMonth = isset($month->is_current_month) ? $month->is_current_month : false;
                    $isFutureMonth = isset($month->is_future_month) ? $month->is_future_month : false;
                    $isDynamic = isset($month->is_dynamic) ? $month->is_dynamic : false;
                    $isBillingCycleMonth = isset($month->is_billing_cycle_month) ? $month->is_billing_cycle_month : false;
                    $billingCycle = isset($month->billing_cycle) ? $month->billing_cycle : 1;
                    @endphp
                    
                    @if(!$isFutureMonth && ($month->total_customers ?? 0) > 0)
                    <tr class="{{ $isCurrentMonth ? 'table-info' : '' }}" data-month="{{ $month->billing_month }}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    @if($isCurrentMonth)
                                    <i class="fas fa-calendar-check text-primary"></i>
                                    @elseif($isBillingCycleMonth)
                                    <i class="fas fa-calendar-star text-success"></i>
                                    @else
                                    <i class="fas fa-calendar-alt text-secondary"></i>
                                    @endif
                                </div>
                                <div>
                                    <strong>{{ $month->display_month ?? $month->billing_month }}</strong>
                                    @if($isCurrentMonth)
                                    <div><span class="badge bg-primary">Current Month</span></div>
                                    @endif
                                    @if($isBillingCycleMonth && $billingCycle > 1)
                                    <div class="small text-success">
                                        <i class="fas fa-sync-alt me-1"></i>{{ $billingCycle }}-Month Billing Cycle
                                    </div>
                                    @endif
                                    @if($month->notes ?? false)
                                    <div class="small text-muted mt-1">{{ Str::limit($month->notes, 40) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold" title="Active customers with invoices this month">
                                {{ number_format($month->total_customers ?? 0) }}
                            </div>
                            <div class="small text-muted">customers</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" title="Total invoice amount (new charges + previous due)">
                                ৳ {{ number_format($month->total_amount ?? 0, 0) }}
                            </div>
                            <div class="small text-muted">
                                @if(($month->total_amount ?? 0) > 0)
                                {{ \App\Models\Invoice::whereYear('issue_date', \Carbon\Carbon::parse($month->billing_month . '-01')->year)->whereMonth('issue_date', \Carbon\Carbon::parse($month->billing_month . '-01')->month)->count() }} invoices
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-success" title="Payments received this month">
                                ৳ {{ number_format($month->received_amount ?? 0, 0) }}
                            </div>
                            @if(($month->received_amount ?? 0) > 0 && ($month->total_amount ?? 0) > 0)
                            <div class="small text-muted">
                                {{ number_format(($month->received_amount / $month->total_amount) * 100, 1) }}% collected
                            </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $totalAmount = $month->total_amount ?? 0;
                                $receivedAmount = $month->received_amount ?? 0;
                                $calculatedDue = max(0, $totalAmount - $receivedAmount);
                                $dueAmount = $calculatedDue;
                            @endphp
                            <div class="fw-bold text-{{ $dueAmount > 0 ? 'danger' : 'success' }}" 
                                 title="Outstanding amount (will carry forward to next month)">
                                ৳ {{ number_format($dueAmount, 0) }}
                            </div>
                            @if($dueAmount > 0)
                            <div class="small text-muted">
                                <i class="fas fa-forward me-1"></i>Carry forward
                            </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $status = $month->status ?? '';
                                $isClosed = ($month->is_closed ?? false) || $status == 'Closed';
                            @endphp
                            
                            @if($isClosed)
                            <span class="badge bg-secondary">
                                <i class="fas fa-lock me-1"></i>Closed
                            </span>
                            @elseif($status == 'All Paid' || $status == 'paid' || $status == 'Paid')
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>Paid
                            </span>
                            @elseif($status == 'Partial' || $status == 'partial')
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-hourglass-half me-1"></i>Partial
                            </span>
                            @elseif($status == 'Pending')
                            <span class="badge bg-info">
                                <i class="fas fa-hourglass me-1"></i>Pending
                            </span>
                            @elseif($status == 'No Activity')
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-minus-circle me-1"></i>No Activity
                            </span>
                            @elseif($status == 'confirmed')
                            <span class="badge bg-primary">
                                <i class="fas fa-check me-1"></i>Confirmed
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>Unpaid
                            </span>
                            @endif
                            
                            <!-- Additional status indicators -->
                            @if($dueAmount > 0 && $dueAmount < $totalAmount)
                            <div class="mt-1 small">
                                <span class="badge bg-info">Partial Payment</span>
                            </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.billing.monthly-bills', ['month' => $month->billing_month]) }}"
                                class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                <i class="fas fa-file-invoice-dollar me-1"></i>View Bills
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.billing.monthly-details', ['month' => $month->billing_month]) }}"
                                class="btn btn-info btn-sm details-btn" target="_blank">
                                <i class="fas fa-eye me-1"></i>Details
                            </a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    Showing {{ $monthlySummary->where('is_future_month', false)->where('total_customers', '>', 0)->count() }} monthly summaries
                </small>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Last updated: {{ now()->format('M j, Y g:i A') }}
                </small>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Quick Stats Row -->
<div class="row mt-4">
    <div class="col-md-3 mb-4">
        <div class="card border-left-primary border-left-3 h-100">
            <div class="card-body d-flex flex-column">
                <div class="text-muted small text-uppercase">Total Invoiced</div>
                <div class="h4 mb-0">৳ {{ number_format($totalInvoiceAmount ?? 0, 0) }}</div>
                <small class="text-muted mt-auto">{{ number_format($totalInvoicesCount ?? 0) }} invoices</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-left-success border-left-3 h-100">
            <div class="card-body d-flex flex-column">
                <div class="text-muted small text-uppercase">Total Collected</div>
                <div class="h4 mb-0">৳ {{ number_format($totalReceivedAmount ?? 0, 0) }}</div>
                <small class="text-muted mt-auto">{{ number_format($totalPaymentsCount ?? 0) }} payments</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-left-info border-left-3 h-100">
            <div class="card-body d-flex flex-column">
                <div class="text-muted small text-uppercase">Collection Rate</div>
                <div class="h4 mb-0">{{ number_format($collectionRate ?? 0, 1) }}%</div>
                <small class="text-muted mt-auto">of total invoiced amount</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-left-danger border-left-3 h-100">
            <div class="card-body d-flex flex-column">
                <div class="text-muted small text-uppercase">Outstanding</div>
                <div class="h4 mb-0">৳ {{ number_format($totalPendingAmount ?? 0, 0) }}</div>
                <small class="text-muted mt-auto">across all months</small>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Card -->
<div class="card mt-4 border-info">
    <div class="card-header bg-info text-white">
        <h6 class="card-title mb-0">
            <i class="fas fa-info-circle me-2"></i>How Monthly Billing Works
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Billing Process:</h6>
                <ol class="small">
                    <li><strong>Monthly Invoice Generation:</strong> Each month creates new invoices for all active customers</li>
                    <li><strong>Previous Due Carry-Forward:</strong> Unpaid amounts from previous months are added to new invoices</li>
                    <li><strong>Billing Cycle:</strong> Charges based on customer's billing cycle (1, 3, 6, or 12 months)</li>
                    <li><strong>Payment Recording:</strong> Payments are recorded against specific monthly invoices</li>
                    <li><strong>Auto Carry-Forward:</strong> Unpaid amounts automatically carry forward to next month</li>
                </ol>
            </div>
            <div class="col-md-6">
                <h6>Example Calculation (2-Month Billing Cycle):</h6>
                <div class="bg-light p-3 rounded">
                    <p class="mb-2 small"><strong>Customer with ৳1000/month, 2-month billing cycle:</strong></p>
                    <ul class="mb-0 small">
                        <li><strong>Month 1 (Jan):</strong> ৳2000 (2 months) + Previous Due ৳0 = <strong>৳2000</strong></li>
                        <li><strong>Month 2 (Feb):</strong> ৳0 (non-billing cycle) + Previous Due ৳500 = <strong>৳500</strong></li>
                        <li><strong>Month 3 (Mar):</strong> ৳2000 (new cycle) + Previous Due ৳300 = <strong>৳2300</strong></li>
                        <li><strong>Month 4 (Apr):</strong> ৳0 (non-billing cycle) + Previous Due ৳0 = <strong>৳0</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Payments
                    </h6>
                    <a href="{{ route('admin.billing.all-invoices') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if(empty($recentPayments) || $recentPayments->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-money-bill-wave fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No recent payments found</p>
                </div>
                @else
                <div class="list-group list-group-flush">
                    @foreach($recentPayments as $payment)
                    <div class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        @php
                                            $methodIcon = match($payment->payment_method) {
                                                'cash' => 'money-bill-wave',
                                                'bank_transfer' => 'university',
                                                'mobile_banking' => 'mobile-alt',
                                                'card' => 'credit-card',
                                                'online' => 'globe',
                                                default => 'money-bill-wave'
                                            };
                                        @endphp
                                        <i class="fas fa-{{ $methodIcon }} text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 small">{{ $payment->invoice->customer->name ?? 'Unknown Customer' }}</h6>
                                        <small class="text-muted">{{ $payment->invoice->invoice_number ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success">৳ {{ number_format($payment->amount ?? 0, 0) }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($payment->payment_date ?? now())->format('M j, Y') }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Overdue Invoices
                    </h6>
                    <span class="badge bg-danger">{{ $overdueInvoices->total() ?? 0 }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if(empty($overdueInvoices) || $overdueInvoices->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p class="text-muted mb-0">No overdue invoices</p>
                </div>
                @else
                <div class="list-group list-group-flush">
                    @foreach($overdueInvoices as $invoice)
                    <div class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-file-invoice text-danger"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 small">{{ $invoice->customer->name ?? 'Unknown Customer' }}</h6>
                                        <small class="text-muted">{{ $invoice->invoice_number ?? 'N/A' }}</small>
                                        <div>
                                            <small class="text-muted">Due: {{ \Carbon\Carbon::parse($invoice->issue_date ?? now())->format('M j, Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                @php
                                    $totalAmount = $invoice->total_amount ?? 0;
                                    $receivedAmount = $invoice->received_amount ?? 0;
                                    $dueAmount = max(0, $totalAmount - $receivedAmount);
                                @endphp
                                <div class="fw-bold text-danger">৳ {{ number_format($dueAmount, 0) }}</div>
                                <small class="text-muted">
                                    <span class="badge bg-{{ $invoice->status == 'partial' ? 'warning' : 'danger' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Month Modal -->
<div class="modal fade" id="addBillingModal" tabindex="-1" aria-labelledby="addBillingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Manual Billing Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.billing.store-monthly') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Manual entries are useful for historical data or corrections.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Billing Month *</label>
                        <input type="month" name="billing_month" class="form-control" required
                            min="{{ date('Y-m', strtotime('-2 years')) }}"
                            max="{{ date('Y-m') }}">
                        <div class="form-text">Select month for billing summary</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Customers *</label>
                        <input type="number" name="total_customers" class="form-control" required min="1">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Total Amount (৳) *</label>
                                <input type="number" step="0" name="total_amount" class="form-control" required min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Received Amount (৳) *</label>
                                <input type="number" step="0" name="received_amount" class="form-control" required min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Due Amount (৳) *</label>
                                <input type="number" step="0" name="due_amount" class="form-control" required min="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="All Paid">All Paid</option>
                            <option value="Partial">Partial</option>
                            <option value="Pending">Pending</option>
                            <option value="Unpaid">Unpaid</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes about this billing month"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Billing Summary</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Generate from Invoices Modal -->
<div class="modal fade" id="generateFromInvoicesModal" tabindex="-1" aria-labelledby="generateFromInvoicesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Monthly Invoices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.billing.generate-from-invoices') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will generate invoices for all active customers in the selected month.
                        Previous unpaid amounts will be carried forward automatically.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Month *</label>
                        <select name="billing_month" class="form-select" required>
                            <option value="">-- Select a month --</option>
                            @if(isset($availableMonths) && $availableMonths->isNotEmpty())
                            @foreach($availableMonths as $month)
                            @php
                            try {
                                $monthName = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y');
                                $isCurrent = $month === date('Y-m');
                                $isFuture = $month > date('Y-m');
                            } catch (Exception $e) {
                                continue;
                            }
                            @endphp
                            <option value="{{ $month }}" {{ $isCurrent ? 'selected' : '' }}>
                                {{ $monthName }}{{ $isCurrent ? ' (Current)' : '' }}{{ $isFuture ? ' (Future)' : '' }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                        @if(empty($availableMonths) || $availableMonths->isEmpty())
                        <div class="form-text text-warning">No months available for generation.</div>
                        @else
                        <div class="form-text">Select month to generate invoices</div>
                        @endif
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i>What will happen:</h6>
                        <ul class="mb-0 small">
                            <li>Create new invoices for all active customers</li>
                            <li>Carry forward previous unpaid amounts</li>
                            <li>Calculate charges based on billing cycles</li>
                            <li>Generate separate invoice for each month</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" {{ (empty($availableMonths) || $availableMonths->isEmpty()) ? 'disabled' : '' }}>
                        <i class="fas fa-sync me-1"></i>Generate Invoices
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    :root {
        --primary: rgb(39, 84, 182);
        --success: rgb(6, 214, 75);
        --warning: rgb(218, 233, 81);
        --danger: rgb(221, 23, 23);
        --dark: #2b2d42;
        --light: #f8f9fa;
    }

    body {
        background-color: #f5f7fb;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
    }

    .card.h-100 {
        height: 100%;
    }

    .card-body.d-flex.flex-column {
        flex: 1 1 auto;
    }

    .card:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: white;
        border-bottom: 1px solid #eaeaea;
        border-radius: 12px 12px 0 0 !important;
        padding: 20px 25px;
    }

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #eaeaea;
        background-color: #f8f9fa;
    }

    .table td {
        padding: 14px 12px;
        font-size: 0.9rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    /* Status Badge Styles */
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge.bg-success {
        background-color: #06d6a0 !important;
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .badge.bg-warning {
        background-color: #ffd166 !important;
        color: #000000 !important;
    }

    .badge.bg-info {
        background-color: #118ab2 !important;
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .badge.bg-danger {
        background-color: #ef476f !important;
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .badge.bg-secondary {
        background-color: #6c757d !important;
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .badge.bg-primary {
        background-color: #4361ee !important;
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .badge.bg-light {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        border: 1px solid #dee2e6;
    }

    .monthly-bill-btn {
        font-weight: 500;
        border-radius: 8px;
        white-space: nowrap;
        transition: all 0.3s ease;
        border: 1px solid #4361ee;
        color: #4361ee;
    }

    .details-btn {
        font-weight: 500;
        border-radius: 8px;
        white-space: nowrap;
        transition: all 0.3s ease;
        border: 1px solid #06d6a0;
        color: #06d6a0;
    }

    .monthly-bill-btn:hover {
        background-color: #4361ee;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(67, 97, 238, 0.2);
    }

    .details-btn:hover {
        background-color: #06d6a0;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(6, 214, 160, 0.2);
    }

    .btn-sm {
        border-radius: 8px;
        padding: 5px 10px;
        transition: all 0.2s ease;
    }

    .btn-sm:hover {
        transform: translateY(-1px);
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid #f0f0f0;
        padding: 12px 0;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .text-xs {
        font-size: 0.75rem;
    }

    .text-white-300 {
        opacity: 0.7;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.05);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    .table-info {
        background-color: rgba(67, 97, 238, 0.08) !important;
    }

    .table-info:hover {
        background-color: rgba(67, 97, 238, 0.12) !important;
    }

    .border-left-3 {
        border-left-width: 3px !important;
    }

    .border-left-primary {
        border-left-color: #4361ee !important;
    }

    .border-left-success {
        border-left-color: #06d6a0 !important;
    }

    .border-left-info {
        border-left-color: #118ab2 !important;
    }

    .border-left-danger {
        border-left-color: #ef476f !important;
    }

    .border-left-warning {
        border-left-color: #ffd166 !important;
    }

    /* Hover effects for cards */
    .card.border-left-primary:hover {
        border-left-color: #2a4fd8 !important;
    }

    .card.border-left-success:hover {
        border-left-color: #05c391 !important;
    }

    .card.border-left-info:hover {
        border-left-color: #0f7a9b !important;
    }

    .card.border-left-danger:hover {
        border-left-color: #e63946 !important;
    }

    /* Animation for status changes */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .badge.bg-danger {
        animation: pulse 2s infinite;
    }

    /* Custom scrollbar for table */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table th, .table td {
            padding: 10px 8px;
            font-size: 0.85rem;
        }
        
        .monthly-bill-btn, .details-btn {
            padding: 4px 8px;
            font-size: 0.8rem;
        }
        
        .card-header {
            padding: 15px 20px;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh flag handling
        try {
            const autoRefreshData = localStorage.getItem('billing_auto_refresh');
            if (autoRefreshData) {
                const data = JSON.parse(autoRefreshData);
                const now = Date.now();
                
                if (data.timestamp && (now - data.timestamp) < 30000) {
                    if (data.message && window.showToast) {
                        showToast('Success', data.message, 'success');
                    }
                    
                    setTimeout(() => {
                        console.log('Auto-refreshing billing-invoices page');
                        location.reload();
                    }, 2000);
                }
                
                localStorage.removeItem('billing_auto_refresh');
            }
        } catch (e) {
            console.warn('Error checking auto-refresh flag:', e);
        }

        // Form validation for manual billing
        const totalAmount = document.querySelector('input[name="total_amount"]');
        const receivedAmount = document.querySelector('input[name="received_amount"]');
        const dueAmount = document.querySelector('input[name="due_amount"]');

        function validateAmounts() {
            if (totalAmount && receivedAmount && dueAmount) {
                const total = parseFloat(totalAmount.value) || 0;
                const received = parseFloat(receivedAmount.value) || 0;
                const due = parseFloat(dueAmount.value) || 0;

                if (Math.abs((received + due) - total) > 0) {
                    dueAmount.setCustomValidity('Received amount + Due amount must equal Total amount');
                } else {
                    dueAmount.setCustomValidity('');
                }
            }
        }

        if (totalAmount) totalAmount.addEventListener('input', validateAmounts);
        if (receivedAmount) receivedAmount.addEventListener('input', validateAmounts);
        if (dueAmount) dueAmount.addEventListener('input', validateAmounts);

        // Auto-calculate due amount
        if (totalAmount && receivedAmount && dueAmount) {
            totalAmount.addEventListener('input', function() {
                const total = parseFloat(this.value) || 0;
                const received = parseFloat(receivedAmount.value) || 0;
                dueAmount.value = (total - received).toFixed(2);
            });

            receivedAmount.addEventListener('input', function() {
                const total = parseFloat(totalAmount.value) || 0;
                const received = parseFloat(this.value) || 0;
                dueAmount.value = (total - received).toFixed(2);
            });
        }

        // Add tooltips to table cells
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Highlight current month row
        const currentMonthRow = document.querySelector('tr.table-info');
        if (currentMonthRow) {
            currentMonthRow.style.animation = 'pulse 3s infinite';
        }
    });

    // Export billing report
    function exportBillingReport() {
        const table = document.querySelector('table');
        if (!table) {
            alert('No data available to export!');
            return;
        }

        let csv = [];

        // Get headers
        const headers = [];
        table.querySelectorAll('thead th').forEach(header => {
            headers.push(header.textContent.trim());
        });
        csv.push(headers.join(','));

        // Get rows
        table.querySelectorAll('tbody tr').forEach(row => {
            const rowData = [];
            row.querySelectorAll('td').forEach(cell => {
                let text = cell.textContent.trim();
                text = text.replace(/\s+/g, ' ');
                rowData.push(`"${text}"`);
            });
            csv.push(rowData.join(','));
        });

        // Add summary
        csv.push('');
        csv.push('Summary');
        csv.push(`Total Amount,৳ {{ number_format($monthlySummary->where('is_future_month', false)->where('total_customers', '>', 0)->sum('total_amount'), 0) }}`);
        csv.push(`Total Received,৳ {{ number_format($monthlySummary->where('is_future_month', false)->where('total_customers', '>', 0)->sum('received_amount'), 0) }}`);
        csv.push(`Total Due,৳ {{ number_format($monthlySummary->where('is_future_month', false)->where('total_customers', '>', 0)->sum('due_amount'), 0) }}`);

        // Download CSV
        const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "billing_report_" + new Date().toISOString().split('T')[0] + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Show success message
        if (window.showToast) {
            showToast('Export Successful', 'Billing report exported as CSV', 'success');
        } else {
            alert('Billing report exported successfully!');
        }
    }

    // Add hover effect to show data verification
    document.querySelectorAll('tbody tr[data-month]').forEach(row => {
        row.addEventListener('mouseenter', function() {
            const month = this.dataset.month;
            const customers = this.querySelector('td:nth-child(2) .fw-bold')?.textContent.trim() || '0';
            const total = this.querySelector('td:nth-child(3) .fw-bold')?.textContent.trim() || '0';
            const received = this.querySelector('td:nth-child(4) .fw-bold')?.textContent.trim() || '0';
            const due = this.querySelector('td:nth-child(5) .fw-bold')?.textContent.trim() || '0';

            // Verify calculation
            const totalNum = parseFloat(total.replace(/[^\d.]/g, '')) || 0;
            const receivedNum = parseFloat(received.replace(/[^\d.]/g, '')) || 0;
            const dueNum = parseFloat(due.replace(/[^\d.]/g, '')) || 0;
            const calculatedDue = Math.max(0, totalNum - receivedNum);
            
            console.log(`Month: ${month} | Customers: ${customers} | Total: ${total} | Received: ${received} | Due: ${due}`);
            
            if (Math.abs(calculatedDue - dueNum) > 0) {
                console.warn(`⚠️ Calculation mismatch for ${month}!`);
            } else {
                console.log(`✅ Calculation correct for ${month}`);
            }
        });
    });

    // Auto-update last updated time
    const lastUpdated = document.querySelector('.card-footer small:last-child');
    if (lastUpdated) {
        setInterval(() => {
            const now = new Date();
            const timeStr = now.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            lastUpdated.innerHTML = '<i class="fas fa-clock me-1"></i>Last updated: ' + timeStr;
        }, 60000);
    }

    // Toast notification function
    window.showToast = function(title, message, type = 'info') {
        const toastId = 'toast-' + Date.now();
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'danger' ? 'exclamation-triangle' : 
                    type === 'warning' ? 'exclamation-circle' : 'info-circle';
        const bgColor = type === 'success' ? '#06d6a0' : 
                       type === 'danger' ? '#ef476f' : 
                       type === 'warning' ? '#ffd166' : '#118ab2';

        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999;';
            document.body.appendChild(toastContainer);
        }

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" 
                 style="background-color: ${bgColor}; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center">
                        <i class="fas fa-${icon} me-2"></i>
                        <div>
                            <div class="fw-bold">${title}</div>
                            <div class="small">${message}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();

        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    };

    // Data verification on page load
    console.log('Billing data loaded successfully');
    console.log('System Features:');
    console.log('- Monthly invoice generation');
    console.log('- Previous due carry-forward');
    console.log('- Billing cycle support');
    console.log('- Real-time payment tracking');

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + R to refresh
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            location.reload();
        }
        
        // Ctrl + E to export
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            exportBillingReport();
        }
        
        // Ctrl + G to open generate modal
        if (e.ctrlKey && e.key === 'g') {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('generateFromInvoicesModal'));
            modal.show();
        }
    });

    // Add confirmation for generate invoices
    const generateForm = document.querySelector('form[action*="generate-from-invoices"]');
    if (generateForm) {
        generateForm.addEventListener('submit', function(e) {
            const monthSelect = this.querySelector('select[name="billing_month"]');
            const selectedMonth = monthSelect.options[monthSelect.selectedIndex].text;
            
            if (!confirm(`Are you sure you want to generate invoices for ${selectedMonth}?\n\nThis will create invoices for all active customers and carry forward previous unpaid amounts.`)) {
                e.preventDefault();
            }
        });
    }
</script>
@endsection