@extends('layouts.admin')

@section('title', 'Billing & Invoices - Admin Dashboard')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-0 page-title">
            <i class="fas fa-file-invoice me-2 text-primary"></i>All Invoices
        </h2>
        <p class="text-muted mb-0">Dynamic monthly billing summaries based on customer products and payments</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="location.reload()" title="Refresh data">
            <i class="fas fa-sync-alt me-1"></i>Refresh
        </button>
        <button class="btn btn-outline-primary" onclick="exportBillingReport()">
            <i class="fas fa-download me-1"></i>Export Report
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateFromInvoicesModal">
            <i class="fas fa-sync me-1"></i>Generate from Invoices
        </button>
        <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBillingModal">
                <i class="fas fa-plus me-1"></i>Add Manual Billing
            </button> -->
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
                        <div class="h5 mb-0">৳ {{ number_format($currentMonthRevenue ?? 0, 0) }}</div>
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
                        <div class="h5 mb-0">৳ {{ number_format($totalPendingAmount ?? 0, 0) }}</div>
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
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Monthly Billing Overview
                    <span class="badge bg-success ms-2">
                        <i class="fas fa-database me-1"></i>Live Data
                    </span>
                </h5>
                <p class="text-muted mb-0 small">Real-time data from Invoices and Payments tables</p>
            </div>
            <div class="text-end">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Data updates automatically when payments are recorded
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
                        <th>Total Customers</th>
                        <th>Total Amount</th>
                        <th>Received Amount</th>
                        <th>Due Amount</th>
                        <th>Status</th>
                        <th>Monthly Bills</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlySummary as $month)
                    @php
                    $isCurrentMonth = isset($month->is_current_month) ? $month->is_current_month : false;
                    $isFutureMonth = isset($month->is_future_month) ? $month->is_future_month : false;
                    $isDynamic = isset($month->is_dynamic) ? $month->is_dynamic : false;
                    @endphp
                    @if(!$isFutureMonth && ($month->total_customers ?? 0) > 0)
                    <tr class="{{ $isCurrentMonth ? 'table-info' : '' }}" data-month="{{ $month->billing_month }}">
                        <td>
                            <strong>{{ $month->display_month ?? $month->billing_month }}</strong>
                            @if($isCurrentMonth)
                            <br><span class="badge bg-primary">Current Month</span>
                            @endif
                            @if($month->notes ?? false)
                            <br><small class="text-muted">{{ Str::limit($month->notes, 30) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold" title="Total unique customers with invoices this month">
                                {{ number_format($month->total_customers ?? 0) }}
                            </span>
                            @if(($month->total_customers ?? 0) > 0)
                            <br><small class="text-muted">customers</small>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold text-dark" title="Sum of all invoice total_amount for this month (includes previous due + current charges)">
                                ৳ {{ number_format($month->total_amount ?? 0, 0) }}
                            </span>
                            @if(($month->total_amount ?? 0) > 0)
                            <br><small class="text-muted">From {{ \App\Models\Invoice::whereYear('issue_date', \Carbon\Carbon::parse($month->billing_month . '-01')->year)->whereMonth('issue_date', \Carbon\Carbon::parse($month->billing_month . '-01')->month)->count() }} invoices</small>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold text-success" title="Total payments received for this month">
                                ৳ {{ number_format($month->received_amount ?? 0, 0) }}
                            </span>
                            @if(($month->received_amount ?? 0) > 0 && ($month->total_amount ?? 0) > 0)
                            <br>
                            <small class="text-muted">{{ number_format(($month->received_amount / $month->total_amount) * 100, 1) }}% collected</small>
                            @endif
                        </td>
                        <td>
                            @php
                                // Calculate due amount properly: Total - Received = Due
                                $totalAmount = $month->total_amount ?? 0;
                                $receivedAmount = $month->received_amount ?? 0;
                                $calculatedDue = max(0, $totalAmount - $receivedAmount);
                                
                                // Use calculated value for consistency
                                $dueAmount = $calculatedDue;
                            @endphp
                            <span class="fw-bold text-{{ $dueAmount > 0 ? 'danger' : 'success' }}" title="Outstanding amount for this month (Total - Received)">
                                ৳ {{ number_format($dueAmount, 0) }}
                            </span>
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
                            @elseif($status == 'All Paid')
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>Paid
                            </span>
                            @elseif($status == 'Partial')
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
                            @else
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>Unpaid
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
                            <!-- Details Button -->
                            <a href="{{ route('admin.billing.monthly-details', ['month' => $month->billing_month]) }}"
                                class="btn btn-info btn-sm details-btn" Target="_blank">
                                <i class="fas fa-eye me-1"></i>Details
                            </a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <!-- <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="2" class="text-end">TOTALS:</td>
                        <td class="text-dark">
                            ৳ {{ number_format($monthlySummary->where('is_future_month', false)->where('total_customers', '>', 0)->sum('total_amount'), 0) }}
                        </td>
                        <td class="text-success">
                            ৳ {{ number_format($monthlySummary->where('is_future_month', false)->where('total_customers', '>', 0)->sum('received_amount'), 0) }}
                        </td>
                        <td class="text-danger">
                            ৳ {{ number_format($monthlySummary->where('is_future_month', false)->where('total_customers', '>', 0)->sum('due_amount'), 0) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot> -->
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    Showing {{ $monthlySummary->where('is_future_month', false)->where('total_customers', '>', 0)->count() }} monthly summaries with real-time data
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
                        <h4 class="text-success">৳ {{ number_format($currentMonthStats->total_amount ?? 0, 0) }}</h4>
                        <small class="text-muted">Expected Revenue</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info">৳ {{ number_format($currentMonthStats->received_amount ?? 0, 0) }}</h4>
                        <small class="text-muted">Received</small>
                    </div>
                    <div class="col-md-3">
                        @php
                            // Calculate current month due amount properly
                            $currentTotal = $currentMonthStats->total_amount ?? 0;
                            $currentReceived = $currentMonthStats->received_amount ?? 0;
                            $currentDue = max(0, $currentTotal - $currentReceived);
                        @endphp
                        <h4 class="text-warning">৳ {{ number_format($currentDue, 0) }}</h4>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
                <!-- <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>

                        Current month billing is active and will be available here after the month ends
                        Data updates automatically when payments are recorded
                    </small>
                </div> -->
            </div>
        </div>
        <!-- <div class="card-body p-0">
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
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlySummary as $month)
                        @php
                            $isCurrentMonth = isset($month->is_current_month) ? $month->is_current_month : false;
                            $isFutureMonth = isset($month->is_future_month) ? $month->is_future_month : false;
                            $isDynamic = isset($month->is_dynamic) ? $month->is_dynamic : false;
                        @endphp
                        @if(!$isFutureMonth)
                        <tr class="{{ $isCurrentMonth ? 'table-info' : '' }}" data-month="{{ $month->billing_month }}">
                            <td>
                                <strong>{{ $month->display_month ?? $month->billing_month }}</strong>
                                @if($isCurrentMonth)
                                <br><span class="badge bg-primary">Current Month</span>
                                @endif
                                @if($month->notes ?? false)
                                <br><small class="text-muted">{{ Str::limit($month->notes, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold" title="Total unique customers with invoices this month">
                                    {{ number_format($month->total_customers ?? 0) }}
                                </span>
                                @if(($month->total_customers ?? 0) > 0)
                                <br><small class="text-muted">customers</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-dark" title="Sum of all invoice total_amount for this month (includes previous due + current charges)">
                                    ৳ {{ number_format($month->total_amount ?? 0, 0) }}
                                </span>
                                @if(($month->total_amount ?? 0) > 0)
                                <br><small class="text-muted">From {{ \App\Models\Invoice::whereYear('issue_date', \Carbon\Carbon::parse($month->billing_month . '-01')->year)->whereMonth('issue_date', \Carbon\Carbon::parse($month->billing_month . '-01')->month)->count() }} invoices</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-success" title="Total payments received for this month">
                                    ৳ {{ number_format($month->received_amount ?? 0, 0) }}
                                </span>
                                @if(($month->received_amount ?? 0) > 0 && ($month->total_amount ?? 0) > 0)
                                <br>
                                <small class="text-muted">{{ number_format(($month->received_amount / $month->total_amount) * 100, 1) }}% collected</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-{{ ($month->due_amount ?? 0) > 0 ? 'danger' : 'success' }}" title="Outstanding amount for this month">
                                    ৳ {{ number_format($month->due_amount ?? 0, 0) }}
                                </span>
                            </td>
                            <td>
                                @if(($month->is_closed ?? false) || ($month->status ?? '') == 'Closed')
                                    <span class="badge" style="background-color: #6c757d; color: white; padding: 6px 12px; border-radius: 20px;">
                                        <i class="fas fa-lock me-1"></i>Closed
                                    </span>
                                @elseif(($month->status ?? '') == 'All Paid')
                                    <span class="badge" style="background-color: #06d6a0; color: white; padding: 6px 12px; border-radius: 20px;">Paid</span>

                                @elseif(($month->status ?? '') == 'Pending')
                                    <span class="badge" style="background-color: #ffd166; color: black; padding: 6px 12px; border-radius: 20px;">Pending</span>

                                @else
                                    <span class="badge" style="background-color: #ef476f; color: white; padding: 6px 12px; border-radius: 20px;">Overdue</span>

                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.billing.monthly-bills', ['month' => $month->billing_month]) }}" 
                                   class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <!-- Details Button -->
                                <a href="{{ route('admin.billing.monthly-details', ['month' => $month->billing_month]) }}" 
                                   class="btn btn-info btn-sm details-btn" Target="_blank">
                                    <i class="fas fa-eye me-1"></i>Details
                                </a>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                    
                </table>
            </div>
        </div> -->
        <!-- <div class="card-footer bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        Showing {{ $monthlySummary->where('is_future_month', false)->count() }} monthly summaries with real-time data
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Last updated: {{ now()->format('M j, Y g:i A') }}
                    </small>
                </div>
            </div>
        </div> -->
    </div>
</div>

<!-- Data Summary Card -->
<!-- <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-database me-2"></i>Data Source Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                                <h5 class="mb-0">{{ number_format($totalInvoicesCount ?? 0) }}</h5>
                                <small class="text-muted">Total Invoices</small>
                                <div class="mt-2">
                                    <small class="text-success">৳{{ number_format($totalInvoiceAmount ?? 0, 0) }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                <h5 class="mb-0">{{ number_format($totalPaymentsCount ?? 0) }}</h5>
                                <small class="text-muted">Total Payments</small>
                                <div class="mt-2">
                                    <small class="text-success">৳{{ number_format($totalRevenue ?? 0, 0) }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h5 class="mb-0">{{ number_format($totalActiveCustomers ?? 0) }}</h5>
                                <small class="text-muted">Active Customers</small>
                                <div class="mt-2">
                                    <small class="text-info">With Products</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-calendar-check fa-2x text-warning mb-2"></i>
                                <h5 class="mb-0">{{ $monthlySummary->where('is_future_month', false)->count() }}</h5>
                                <small class="text-muted">Billing Months</small>
                                <div class="mt-2">
                                    <small class="text-warning">Tracked</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>How it works:</strong> The table above shows real-time data calculated from your invoices and payments. 
                                <ul class="mb-0 mt-2 small">
                                    <li><strong>Total Customers</strong> = COUNT(DISTINCT c_id) from invoices that month</li>
                                    <li><strong>Total Amount</strong> = SUM(total_amount) from invoices that month</li>
                                    <li><strong>Received Amount</strong> = SUM(received_amount) from invoices that month</li>
                                    <li><strong>Due Amount</strong> = Total Amount - Received Amount</li>
                                </ul>
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small><strong>Note:</strong> Total Amount includes previous due + current month charges, matching the monthly-bills calculation exactly.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>Overall Statistics (All Time):</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Total Invoiced: <strong class="text-primary">৳{{ number_format($totalInvoiceAmount ?? 0, 0) }}</strong> <small class="text-muted">({{ number_format($totalInvoicesCount ?? 0) }} invoices)</small></li>
                                    <li>Total Collected: <strong class="text-success">৳{{ number_format($totalReceivedAmount ?? 0, 0) }}</strong> <small class="text-muted">({{ number_format($totalPaymentsCount ?? 0) }} payments)</small></li>
                                    <li>Collection Rate: <strong class="text-info">{{ $totalInvoiceAmount > 0 ? number_format(($totalReceivedAmount / $totalInvoiceAmount) * 100, 1) : 0 }}%</strong></li>
                                    <li>Outstanding: <strong class="text-danger">৳{{ number_format($totalPendingAmount ?? 0, 0) }}</strong></li>
                                </ul>
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small><strong>Verification:</strong> ৳{{ number_format($totalInvoiceAmount ?? 0, 0) }} - ৳{{ number_format($totalReceivedAmount ?? 0, 0) }} = ৳{{ number_format(($totalInvoiceAmount ?? 0) - ($totalReceivedAmount ?? 0), 0) }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

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
                                <strong class="text-success">৳ {{ number_format($payment->amount ?? 0, 0) }}</strong>
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
                                @php
                                    // Calculate due amount properly for overdue invoices
                                    $totalAmount = $invoice->total_amount ?? 0;
                                    $receivedAmount = $invoice->received_amount ?? 0;
                                    $dueAmount = max(0, $totalAmount - $receivedAmount);
                                @endphp
                                <strong class="text-danger">৳ {{ number_format($dueAmount, 0) }}</strong>
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
                            max="{{ date('Y-m', strtotime('-1 month')) }}">
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
                <h5 class="modal-title">Generate from Invoices & products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.billing.generate-from-invoices') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will automatically generate a billing summary from customer products and payment records for the selected month.
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
                            @if(!$isFuture)
                            <option value="{{ $month }}">
                                {{ $monthName }}{{ $isCurrent ? ' (Current)' : '' }}
                            </option>
                            @endif
                            @endforeach
                            @endif
                        </select>
                        @if(empty($availableMonths) || $availableMonths->isEmpty())
                        <div class="form-text text-warning">No months with billing data available for generation.</div>
                        @else
                        <div class="form-text">Select a month to generate billing summary (future months are excluded)</div>
                        @endif
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
    }

    .details-btn {
        font-weight: 500;
        border-radius: 8px;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .monthly-bill-btn:hover,
    .details-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
</style>
@endsection

@section('scripts')
<script>
    // Validate that received + due = total amount
    document.addEventListener('DOMContentLoaded', function() {
        // ENHANCED: Check for auto-refresh flag from month closing redirect
        try {
            const autoRefreshData = localStorage.getItem('billing_auto_refresh');
            if (autoRefreshData) {
                const data = JSON.parse(autoRefreshData);
                const now = Date.now();
                
                // Only auto-refresh if the flag is recent (within 30 seconds)
                if (data.timestamp && (now - data.timestamp) < 30000) {
                    // Show success message about month closure
                    if (data.message && window.showToast) {
                        showToast('Month Closed Successfully', data.message, 'success');
                    }
                    
                    // Auto-refresh the page after showing the message
                    setTimeout(() => {
                        console.log('Auto-refreshing billing-invoices page after month closure');
                        location.reload();
                    }, 2000);
                }
                
                // Clean up the flag regardless of age
                localStorage.removeItem('billing_auto_refresh');
            }
        } catch (e) {
            console.warn('Error checking auto-refresh flag:', e);
        }

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

        // Download CSV
        const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "billing_report_" + new Date().toISOString().split('T')[0] + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        alert('Billing report exported successfully!');
    }

    // Add hover effect to show data is interactive and verify calculation
    document.querySelectorAll('tbody tr[data-month]').forEach(row => {
        row.addEventListener('mouseenter', function() {
            const month = this.dataset.month;
            const customers = this.querySelector('td:nth-child(2) .fw-bold').textContent.trim();
            const total = this.querySelector('td:nth-child(3) .fw-bold').textContent.trim();
            const received = this.querySelector('td:nth-child(4) .fw-bold').textContent.trim();
            const due = this.querySelector('td:nth-child(5) .fw-bold').textContent.trim();

            // Verify calculation: Total - Received = Due
            const totalNum = parseFloat(total.replace(/[^\d.]/g, '')) || 0;
            const receivedNum = parseFloat(received.replace(/[^\d.]/g, '')) || 0;
            const dueNum = parseFloat(due.replace(/[^\d.]/g, '')) || 0;
            const calculatedDue = Math.max(0, totalNum - receivedNum);
            
            console.log(`Month: ${month} | Customers: ${customers} | Total: ${total} | Received: ${received} | Due: ${due}`);
            console.log(`Verification: ${totalNum} - ${receivedNum} = ${calculatedDue} (Displayed: ${dueNum})`);
            
            if (Math.abs(calculatedDue - dueNum) > 0.01) {
                console.warn(`⚠️ Calculation mismatch for ${month}!`);
            } else {
                console.log(`✅ Calculation correct for ${month}`);
            }
        });
    });

    // Show data source on page load
    console.log('Billing data loaded from database:');
    console.log('- Invoices table: Total amounts and customer counts');
    console.log('- Payments table: Received amounts');
    console.log('- Calculated: Due amounts (Total - Received)');

    // Add visual indicator that data is live
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
        }, 60000); // Update every minute
    }

    // Listen for cross-tab/page notifications to auto-refresh billing list
    function handleBillingClosedNotification(payload) {
        try {
            console.log('Received billing_month_closed notification', payload);
            // Optionally show a small toast/alert before reloading
            if (window.showToast) {
                showToast('Billing Month Closed', `Closed month: ${payload.month}`, 'success');
            }
            // Reload the page after a short delay so the message is visible
            setTimeout(() => location.reload(), 1500);
        } catch (e) {
            console.error('Error handling billing closed notification', e);
            location.reload();
        }
    }

    // Storage event (fires in other tabs/windows)
    window.addEventListener('storage', function(e) {
        if (!e) return;
        if (e.key === 'billing_month_closed' && e.newValue) {
            try {
                const payload = JSON.parse(e.newValue);
                handleBillingClosedNotification(payload);
            } catch (err) {
                console.error('Invalid billing_month_closed payload', err);
            }
        }
    });

    // BroadcastChannel fallback/modern approach
    if (window.BroadcastChannel) {
        try {
            const bc = new BroadcastChannel('billing_channel');
            bc.addEventListener('message', function(ev) {
                if (!ev || !ev.data) return;
                handleBillingClosedNotification(ev.data);
            });
        } catch (err) {
            console.warn('BroadcastChannel not available', err);
        }
    }

    // ENHANCED: Toast notification function for billing-invoices page
    window.showToast = function(title, message, type = 'info') {
        const toastId = 'toast-' + Date.now();
        const icon = type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : type === 'warning' ? 'exclamation-circle' : 'info-circle';
        const bgColor = type === 'success' ? '#06d6a0' : type === 'danger' ? '#ef476f' : type === 'warning' ? '#ffd166' : '#118ab2';

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
</script>
@endsection