@extends('layouts.admin')

@section('title', 'Billing & Invoices - Admin Dashboard')

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-file-invoice me-2 text-primary"></i>Billing & Invoices
            </h2>
            <p class="text-muted mb-0">Dynamic monthly billing summaries based on customer packages and payments</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportBillingReport()">
                <i class="fas fa-download me-1"></i>Export Report
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateFromInvoicesModal">
                <i class="fas fa-sync me-1"></i>Generate from Invoices
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
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
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
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Monthly Revenue</div>
                            <div class="h5 mb-0">৳ {{ number_format($currentMonthRevenue ?? 0, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-white-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Payments</div>
                            <div class="h5 mb-0">৳ {{ number_format($totalPendingAmount ?? 0, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-white-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Previous Month Bills</div>
                            <div class="h5 mb-0">{{ $previousMonthBillsCount ?? 0 }}</div>
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
            <p class="text-muted mb-4">Get started by generating billing summaries from existing invoices or adding manual billing data.</p>
            <div class="d-flex justify-content-center gap-2">
                @if($hasInvoices ?? false)
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateFromInvoicesModal">
                    <i class="fas fa-sync me-1"></i>Generate from Invoices
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
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Monthly Billing Overview 
            </h5>
            <p class="text-muted mb-0 small">Showing completed months only</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Billing Month</th>
                            <th>Total Customers</th>
                            <th>Total Amount</th>
                            <th>Received Amount</th>
                            <th>Due Amount</th>
                            <th>Status</th>
                            <th>Monthly Bills</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlySummary as $month)
                        @php
                            $isCurrentMonth = isset($month->is_current_month) ? $month->is_current_month : false;
                            $isFutureMonth = isset($month->is_future_month) ? $month->is_future_month : false;
                            $isDynamic = isset($month->is_dynamic) ? $month->is_dynamic : false;
                        @endphp
                        @if(!$isCurrentMonth && !$isFutureMonth) {{-- Hide current and future months --}}
                        <tr>
                            <td>
                                <strong>{{ $month->display_month ?? $month->billing_month }}</strong>
                                @if($month->notes ?? false)
                                <br><small class="text-muted">{{ Str::limit($month->notes, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold">{{ $month->total_customers ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">৳ {{ number_format($month->total_amount ?? 0, 2) }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success">৳ {{ number_format($month->received_amount ?? 0, 2) }}</span>
                                @if(($month->received_amount ?? 0) > 0 && ($month->total_amount ?? 0) > 0)
                                <br>
                                <small class="text-muted">{{ number_format(($month->received_amount / $month->total_amount) * 100, 1) }}% collected</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-warning">৳ {{ number_format($month->due_amount ?? 0, 2) }}</span>
                            </td>
                            <td>
                                @if(($month->status ?? '') == 'All Paid')
                                    <span class="badge badge-paid">
                                        <i class="fas fa-check-circle me-1"></i>Paid
                                    </span>
                                @elseif(($month->status ?? '') == 'Pending')
                                    <span class="badge badge-pending">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </span>
                                @else
                                    <span class="badge badge-overdue">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.billing.monthly-bills', ['month' => $month->billing_month]) }}" 
                                   class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if(!$isDynamic)
                                    <a href="{{ route('admin.billing.edit-monthly', $month->id) }}" 
                                       class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.billing.delete-monthly', $month->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                onclick="return confirm('Are you sure you want to delete this billing summary?')"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <button class="btn btn-outline-info btn-sm" title="Dynamic data - Auto calculated" disabled>
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    @endif
                                    
                                    {{-- Lock/Unlock Button --}}
                                    @if(isset($month->is_locked))
                                    <form action="{{ route('admin.billing.toggle-lock', $month->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" 
                                                title="{{ $month->is_locked ? 'Unlock' : 'Lock' }}">
                                            <i class="fas {{ $month->is_locked ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
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
                        Showing {{ $monthlySummary->where('is_current_month', false)->where('is_future_month', false)->count() }} completed monthly summaries
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Current and future months are hidden | Last updated: {{ now()->format('M j, Y g:i A') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Current Month Summary Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0 text-dark">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Current Month ({{ date('F Y') }}) - Active Billing
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary">{{ $currentMonthStats->total_customers ?? 0 }}</h4>
                            <small class="text-muted">Active Customers</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success">৳ {{ number_format($currentMonthStats->total_amount ?? 0, 2) }}</h4>
                            <small class="text-muted">Expected Revenue</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info">৳ {{ number_format($currentMonthStats->received_amount ?? 0, 2) }}</h4>
                            <small class="text-muted">Received</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning">৳ {{ number_format($currentMonthStats->due_amount ?? 0, 2) }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Current month billing is active and will be available here after the month ends
                        </small>
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
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Payments
                    </h6>
                </div>
                <div class="card-body">
                    @if(empty($recentPayments) || $recentPayments->isEmpty())
                        <p class="text-muted text-center py-3">No recent payments found</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentPayments as $payment)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ $payment->customer->name ?? 'Unknown Customer' }}</h6>
                                        <small class="text-muted">{{ $payment->invoice->invoice_number ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-success">৳ {{ number_format($payment->amount ?? 0, 2) }}</strong>
                                        <br>
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
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Overdue Invoices
                    </h6>
                </div>
                <div class="card-body">
                    @if(empty($overdueInvoices) || $overdueInvoices->isEmpty())
                        <p class="text-muted text-center py-3">No overdue invoices</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($overdueInvoices as $invoice)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ $invoice->customer->name ?? 'Unknown Customer' }}</h6>
                                        <small class="text-muted">{{ $invoice->invoice_number ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-danger">৳ {{ number_format($invoice->next_due ?? 0, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">Issued: {{ \Carbon\Carbon::parse($invoice->issue_date ?? now())->format('M j, Y') }}</small>
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
                        Manual entries are useful for historical data or corrections. For current months, use "Generate from Invoices".
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Billing Month *</label>
                        <input type="month" name="billing_month" class="form-control" required 
                               min="{{ date('Y-m', strtotime('-2 years')) }}" 
                               max="{{ date('Y-m', strtotime('-1 month')) }}"> {{-- Max is previous month --}}
                        <div class="form-text">Select a completed month (current and future months are not allowed for manual entry)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Customers *</label>
                        <input type="number" name="total_customers" class="form-control" required min="1">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Total Amount (৳) *</label>
                                <input type="number" step="0.01" name="total_amount" class="form-control" required min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Received Amount (৳) *</label>
                                <input type="number" step="0.01" name="received_amount" class="form-control" required min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Due Amount (৳) *</label>
                                <input type="number" step="0.01" name="due_amount" class="form-control" required min="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="All Paid">All Paid</option>
                            <option value="Pending">Pending</option>
                            <option value="Overdue">Overdue</option>
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
                <h5 class="modal-title">Generate from Invoices & Packages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.billing.generate-from-invoices') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will automatically generate a billing summary from customer packages and payment records for the selected month.
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
                                @if(!$isCurrent && !$isFuture) {{-- Hide current and future months from selection --}}
                                <option value="{{ $month }}">
                                    {{ $monthName }}
                                </option>
                                @endif
                                @endforeach
                            @endif
                        </select>
                        @if(empty($availableMonths) || $availableMonths->isEmpty())
                        <div class="form-text text-warning">No months with billing data available for generation.</div>
                        @else
                        <div class="form-text">Select a completed month to generate billing summary (current and future months are excluded)</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_service_charge" id="include_service_charge" checked>
                            <label class="form-check-label" for="include_service_charge">
                                Include service charge (৳ 50) and VAT (5%)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" {{ (empty($availableMonths) || $availableMonths->isEmpty()) ? 'disabled' : '' }}>
                        <i class="fas fa-sync me-1"></i>Generate Summary
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
        --primary: #4361ee;
        --success: #06d6a0;
        --warning: #ffd166;
        --danger: #ef476f;
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
    }

    .table td {
        padding: 14px 12px;
        font-size: 0.9rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .badge-paid {
        background-color: var(--success);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-pending {
        background-color: var(--warning);
        color: black;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-overdue {
        background-color: var(--danger);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .monthly-bill-btn {
        font-weight: 500;
        border-radius: 8px;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .monthly-bill-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
</style>
@endsection

@section('scripts')
<script>
    // Validate that received + due = total amount
    document.addEventListener('DOMContentLoaded', function() {
        const totalAmount = document.querySelector('input[name="total_amount"]');
        const receivedAmount = document.querySelector('input[name="received_amount"]');
        const dueAmount = document.querySelector('input[name="due_amount"]');
        
        function validateAmounts() {
            if (totalAmount && receivedAmount && dueAmount) {
                const total = parseFloat(totalAmount.value) || 0;
                const received = parseFloat(receivedAmount.value) || 0;
                const due = parseFloat(dueAmount.value) || 0;
                
                if (Math.abs((received + due) - total) > 0.01) {
                    dueAmount.setCustomValidity('Received amount + Due amount must equal Total amount');
                } else {
                    dueAmount.setCustomValidity('');
                }
            }
        }
        
        if (totalAmount) totalAmount.addEventListener('input', validateAmounts);
        if (receivedAmount) receivedAmount.addEventListener('input', validateAmounts);
        if (dueAmount) dueAmount.addEventListener('input', validateAmounts);

        // Auto-calculate due amount when total or received changes
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
    });

    function exportBillingReport() {
        // Simple export functionality - you can enhance this with proper CSV/Excel export
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
                // Remove action buttons and get clean text
                let text = cell.textContent.trim();
                // Remove multiple spaces and newlines
                text = text.replace(/\s+/g, ' ');
                rowData.push(`"${text}"`);
            });
            csv.push(rowData.join(','));
        });
        
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
        alert('Billing report exported successfully!');
    }

    // Auto-refresh data every 5 minutes
    setInterval(() => {
        console.log('Auto-refreshing billing data...');
        // You can implement actual refresh logic here if needed
    }, 300000);
</script>
@endsection