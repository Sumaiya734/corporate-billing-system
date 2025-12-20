@extends('layouts.admin')

@section('title', 'Billing Reports - Admin Dashboard')

@section('content')
<!-- Toast Notification Container -->
<div id="toastContainer" style="position: fixed; top: 80px; right: 20px; z-index: 9999;"></div>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-0 page-title">
            <i class="fas fa-chart-line me-2 text-primary"></i>Billing Reports
        </h2>
        <p class="text-muted mb-0">Generate detailed billing reports with advanced filters</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="printReport()">
            <i class="fas fa-print me-1"></i>Print Report
        </button>
        <a href="{{ route('admin.billing.export-reports', request()->all()) }}" class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i>Export Excel
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-soft-primary py-3">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>Filter Reports
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.billing.reports') }}" method="GET" id="reportFilterForm">
            <div class="row g-3">
                <!-- Quick Date Range -->
                <div class="col-md-4">
                    <label class="form-label text-primary fw-medium">Quick Date Range</label>
                    <select class="form-select form-select-sm" name="date_range" id="dateRange">
                        <option value="">Select Range</option>
                        <option value="today" {{ $filterData['date_range'] == 'today' ? 'selected' : '' }}>Today ({{ now()->format('M j, Y') }})</option>
                        <option value="this_week" {{ $filterData['date_range'] == 'this_week' ? 'selected' : '' }}>This Week ({{ now()->startOfWeek()->format('M j') }} - {{ now()->endOfWeek()->format('M j, Y') }})</option>
                        <option value="this_month" {{ $filterData['date_range'] == 'this_month' ? 'selected' : '' }}>This Month ({{ now()->format('F Y') }})</option>
                        <option value="last_month" {{ $filterData['date_range'] == 'last_month' ? 'selected' : '' }}>Last Month ({{ now()->subMonth()->format('F Y') }})</option>
                        <option value="last_3_months" {{ $filterData['date_range'] == 'last_3_months' ? 'selected' : '' }}>Last 3 Months ({{ now()->subMonths(2)->startOfMonth()->format('M Y') }} - {{ now()->format('M Y') }})</option>
                        <option value="last_6_months" {{ $filterData['date_range'] == 'last_6_months' ? 'selected' : '' }}>Last 6 Months ({{ now()->subMonths(5)->startOfMonth()->format('M Y') }} - {{ now()->format('M Y') }})</option>
                        <option value="this_year" {{ $filterData['date_range'] == 'this_year' ? 'selected' : '' }}>This Year ({{ now()->format('Y') }})</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <!-- Custom Date Range (initially hidden) -->
                <div class="col-md-4" id="customDateRange" style="display: none;">
                    <label class="form-label text-primary fw-medium">From Date</label>
                    <input type="date" class="form-control form-control-sm" name="from_date" 
                           value="{{ $filterData['from_date'] }}" id="fromDate">
                </div>
                <div class="col-md-4" id="customDateRangeTo" style="display: none;">
                    <label class="form-label text-primary fw-medium">To Date</label>
                    <input type="date" class="form-control form-control-sm" name="to_date" 
                           value="{{ $filterData['to_date'] }}" id="toDate">
                </div>

                <!-- Due Status -->
                <div class="col-md-4">
                    <label class="form-label text-primary fw-medium">Due Status</label>
                    <select class="form-select form-select-sm" name="due_status">
                        <option value="">All Invoices</option>
                        <option value="due_only" {{ $filterData['due_status'] == 'due_only' ? 'selected' : '' }}>Due Customers Only</option>
                        <option value="paid_only" {{ $filterData['due_status'] == 'paid_only' ? 'selected' : '' }}>Paid Invoices Only</option>
                        <option value="overdue" {{ $filterData['due_status'] == 'overdue' ? 'selected' : '' }}>Overdue (30+ days)</option>
                    </select>
                </div>

                <!-- Customer -->
                <div class="col-md-4">
                    <label class="form-label text-primary fw-medium">Customer</label>
                    <select class="form-select form-select-sm" name="customer_id">
                        <option value="">All Customers</option>
                        @foreach($filterData['customers'] as $customer)
                        <option value="{{ $customer->c_id }}" {{ $filterData['customer_id'] == $customer->c_id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->customer_id }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-4">
                    <label class="form-label text-primary fw-medium">Invoice Status</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Status</option>
                        <option value="paid" {{ $filterData['status'] == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ $filterData['status'] == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ $filterData['status'] == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="confirmed" {{ $filterData['status'] == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    </select>
                </div>

                <!-- Product -->
                <div class="col-md-4">
                    <label class="form-label text-primary fw-medium">Product</label>
                    <select class="form-select form-select-sm" name="product_id">
                        <option value="">All Products</option>
                        @foreach($filterData['products'] as $product)
                        <option value="{{ $product->p_id }}" {{ $filterData['product_id'] == $product->p_id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Billing Cycle -->
                <div class="col-md-4">
                    <label class="form-label text-primary fw-medium">Billing Cycle</label>
                    <select class="form-select form-select-sm" name="billing_cycle">
                        <option value="">All Cycles</option>
                        <option value="1" {{ $filterData['billing_cycle'] == '1' ? 'selected' : '' }}>Monthly</option>
                        <option value="2" {{ $filterData['billing_cycle'] == '2' ? 'selected' : '' }}>Bi-Monthly (2 Months)</option>
                        <option value="3" {{ $filterData['billing_cycle'] == '3' ? 'selected' : '' }}>Quarterly (3 Months)</option>
                        <option value="6" {{ $filterData['billing_cycle'] == '6' ? 'selected' : '' }}>Half Yearly (6 Months)</option>
                        <option value="12" {{ $filterData['billing_cycle'] == '12' ? 'selected' : '' }}>Yearly (12 Months)</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label text-primary fw-medium">Search</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="search" placeholder="Invoice No, Customer, Phone..." 
                               value="{{ $filterData['search'] }}">
                        <button class="btn btn-outline-primary" type="button" onclick="clearSearch()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-12 mt-3">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                            <i class="fas fa-redo me-1"></i>Reset Filters
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search me-1"></i>Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Print Header (Hidden on screen, visible in print) -->
<div class="print-header" style="display: none;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #6c63ff; margin: 0 0 5px 0;">Nanosoft</h2>
        <p style="margin: 0; font-size: 16px; font-weight: bold;">Monthly Billing Report</p>
        <p style="margin: 0; font-size: 12px; color: #666;">Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        @php
            $filterText = [];
            if(request('date_range')) {
                $dateRange = request('date_range');
                $now = now();
                switch($dateRange) {
                    case 'today':
                        $filterText[] = 'Date Range: ' . $now->format('F j, Y');
                        break;
                    case 'this_week':
                        $start = $now->copy()->startOfWeek();
                        $end = $now->copy()->endOfWeek();
                        $filterText[] = 'Date Range: ' . $start->format('M j') . ' - ' . $end->format('M j, Y');
                        break;
                    case 'this_month':
                        $filterText[] = 'Date Range: ' . $now->format('F Y');
                        break;
                    case 'last_month':
                        $lastMonth = $now->copy()->subMonth();
                        $filterText[] = 'Date Range: ' . $lastMonth->format('F Y');
                        break;
                    case 'last_3_months':
                        $start = $now->copy()->subMonths(2)->startOfMonth();
                        $end = $now->copy()->endOfMonth();
                        $filterText[] = 'Date Range: ' . $start->format('M Y') . ' - ' . $end->format('M Y');
                        break;
                    case 'last_6_months':
                        $start = $now->copy()->subMonths(5)->startOfMonth();
                        $end = $now->copy()->endOfMonth();
                        $filterText[] = 'Date Range: ' . $start->format('M Y') . ' - ' . $end->format('M Y');
                        break;
                    case 'this_year':
                        $filterText[] = 'Date Range: ' . $now->format('Y');
                        break;
                    default:
                        $filterText[] = 'Date Range: ' . ucfirst(str_replace('_', ' ', $dateRange));
                }
            }
            if(request('from_date') && request('to_date')) $filterText[] = 'From: ' . request('from_date') . ' To: ' . request('to_date');
            if(request('due_status')) $filterText[] = 'Due Status: ' . ucfirst(str_replace('_', ' ', request('due_status')));
            if(request('customer_id')) $filterText[] = 'Customer: ' . ($filterData['customers']->firstWhere('c_id', request('customer_id'))->name ?? 'N/A');
            if(request('status')) $filterText[] = 'Status: ' . ucfirst(request('status'));
            if(request('product_id')) $filterText[] = 'Product: ' . ($filterData['products']->firstWhere('p_id', request('product_id'))->name ?? 'N/A');
            if(request('billing_cycle')) $filterText[] = 'Billing Cycle: ' . request('billing_cycle') . ' months';
            if(request('search')) $filterText[] = 'Search: ' . request('search');
        @endphp
        @if(count($filterText) > 0)
        <p style="margin: 5px 0 0 0; font-size: 10px; color: #666;"> {{ implode(' | ', $filterText) }}</p>
        @endif
    </div>
</div>

<!-- Report Table -->
<div class="card shadow-sm border-0" id="reportTable">
    <div class="card-header bg-white py-3 table-search-section">
        <div class="d-flex justify-content-between align-items-center">
            <!-- <h5 class="card-title mb-0">
                <i class="fas fa-table me-2"></i>Billing Report
                @if(request()->hasAny(['date_range', 'from_date', 'to_date', 'due_status', 'customer_id', 'status', 'search']))
                <small class="text-muted ms-2">(Filtered Results)</small>
                @endif
            </h5>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" class="form-control" placeholder="Search in table..." id="tableSearch">
                    <button class="btn btn-outline-secondary" type="button" onclick="searchTable()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select class="form-select form-select-sm" style="width: 150px;" id="tableStatusFilter">
                    <option value="all">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                </select>
            </div> -->
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="billingTable">
                <thead class="table-light">
                    <tr>
                        <th class="border-end">Invoice ID</th>
                        <th class="border-end">Customer Info</th>
                        <th class="border-end">Product</th>
                        <th class="border-end text-end">Subtotal</th>
                        <th class="border-end text-end">Previous Due</th>
                        <th class="border-end text-end">Total Amount</th>
                        <th class="border-end text-end">Received Amount</th>
                        <th class="border-end text-end">Next Due</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr data-status="{{ $invoice->status }}">
                        <td class="border-end">
                            <div class="fw-bold text-primary">{{ $invoice->invoice_number }}</div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M j, Y') }}</small>
                        </td>
                        <td class="border-end">
                            <div class="fw-medium">{{ $invoice->customer_name ?? 'N/A' }}</div>
                            <div class="text-muted small">
                                {{ $invoice->customer_phone ?? 'N/A' }}
                                @if($invoice->customer_code)
                                <span class="ms-2 badge bg-soft-info">{{ $invoice->customer_code }}</span>
                                @endif
                            </div>
                            <div class="text-muted small">{{ $invoice->customer_email ?? 'N/A' }}</div>
                        </td>
                        <td class="border-end">
                            <div class="fw-medium">{{ $invoice->product_name ?? 'Unknown Product' }}</div>
                            @if($invoice->billing_cycle_months > 1)
                            <span class="badge bg-soft-info">{{ $invoice->billing_cycle_months }} months cycle</span>
                            @endif
                        </td>
                        <td class="border-end text-end">
                            <div class="fw-bold">৳ {{ number_format($invoice->subtotal, 2) }}</div>
                        </td>
                        <td class="border-end text-end">
                            <div class="{{ $invoice->previous_due > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                ৳ {{ number_format($invoice->previous_due, 2) }}
                            </div>
                        </td>
                        <td class="border-end text-end">
                            <div class="fw-bold text-success">৳ {{ number_format($invoice->total_amount, 2) }}</div>
                        </td>
                        <td class="border-end text-end">
                            <div class="fw-bold text-info">৳ {{ number_format($invoice->received_amount, 2) }}</div>
                            @if($invoice->total_amount > 0)
                            <div class="text-muted small">
                                {{ number_format(($invoice->received_amount / $invoice->total_amount) * 100, 1) }}% paid
                            </div>
                            @endif
                        </td>
                        <td class="border-end text-end">
                            @php
                                $nextDue = max(0, $invoice->total_amount - $invoice->received_amount);
                                $isAdvance = $invoice->received_amount > $invoice->total_amount;
                            @endphp
                            @if($isAdvance)
                                <span class="badge bg-success">Advance Paid</span>
                                <div class="text-success small">
                                    +৳ {{ number_format($invoice->received_amount - $invoice->total_amount, 2) }}
                                </div>
                            @elseif($nextDue == 0)
                                <span class="badge bg-success">Paid</span>
                            @else
                                <div class="fw-bold text-danger">৳ {{ number_format($nextDue, 2) }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            @switch($invoice->status)
                                @case('paid')
                                    <span class="badge bg-success">Paid</span>
                                    @break
                                @case('unpaid')
                                    <span class="badge bg-danger">Unpaid</span>
                                    @break
                                @case('partial')
                                    <span class="badge bg-warning text-dark">Partial</span>
                                    @break
                                @case('confirmed')
                                    <span class="badge bg-info">Confirmed</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $invoice->status }}</span>
                            @endswitch
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                <h5>No invoices found</h5>
                                <p>Try adjusting your filters to see results</p>
                                <button class="btn btn-outline-primary" onclick="resetFilters()">
                                    <i class="fas fa-redo me-1"></i>Reset Filters
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    
                    <!-- Total Summary Row - ONLY AT THE END -->
                    @if($invoices->count() > 0)
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td colspan="3" class="text-end border-end">
                            <strong>GRAND TOTAL:</strong>
                        </td>
                        <td class="text-end border-end">
                            <strong>৳ {{ number_format($totals->total_subtotal ?? 0, 2) }}</strong>
                        </td>
                        <td class="text-end border-end">
                            <strong>৳ {{ number_format($totals->total_previous_due ?? 0, 2) }}</strong>
                        </td>
                        <td class="text-end border-end">
                            <strong>৳ {{ number_format($totals->total_amount ?? 0, 2) }}</strong>
                        </td>
                        <td class="text-end border-end">
                            <strong>৳ {{ number_format($totals->total_received ?? 0, 2) }}</strong>
                        </td>
                        <td class="text-end border-end">
                            @php
                                $totalNextDue = max(0, ($totals->total_amount ?? 0) - ($totals->total_received ?? 0));
                            @endphp
                            <strong>৳ {{ number_format($totalNextDue, 2) }}</strong>
                        </td>
                        <td class="text-center"></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">
                    Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
                </small>
            </div>
            <div>
                {{ $invoices->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Report Summary -->
@if($invoices->count() > 0)
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-soft-info">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Payment Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <canvas id="paymentChart" height="150"></canvas>
                    </div>
                    <div class="col-6">
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-success rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                <span>Paid Invoices: {{ $totals->paid_invoices_count ?? 0 }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="badge bg-warning rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                <span>Partial Invoices: {{ $invoices->where('status', 'partial')->count() }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="badge bg-danger rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                <span>Unpaid Invoices: {{ $invoices->where('status', 'unpaid')->count() }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="badge bg-info rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                <span>Confirmed: {{ $invoices->where('status', 'confirmed')->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-soft-success">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Collection Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="p-3 bg-soft-primary rounded">
                            <h4 class="mb-0 text-primary">{{ $invoices->count() }}</h4>
                            <small class="text-muted">Total Invoices</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-soft-success rounded">
                            @php
                                $collectionRate = ($totals->total_amount ?? 0) > 0 
                                    ? round((($totals->total_received ?? 0) / ($totals->total_amount ?? 1)) * 100, 1)
                                    : 0;
                            @endphp
                            <h4 class="mb-0 text-success">{{ $collectionRate }}%</h4>
                            <small class="text-muted">Collection Rate</small>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 data-collection-rate="{{ $collectionRate }}">
                                {{ $collectionRate }}%
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small">
                            <span class="text-muted">৳ 0</span>
                            <span class="text-muted">Target: 100%</span>
                            <span class="text-muted">৳ {{ number_format($totals->total_amount ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('styles')
<style>
    :root {
        --primary: #6c63ff;
        --secondary: #a8a4e6;
        --success: #4caf50;
        --warning: #ff9800;
        --danger: #f44336;
        --info: #2196f3;
        --light: #f8f9fa;
        --soft-primary: rgba(108, 99, 255, 0.1);
        --soft-success: rgba(76, 175, 80, 0.1);
        --soft-warning: rgba(255, 152, 0, 0.1);
        --soft-danger: rgba(244, 67, 54, 0.1);
        --soft-info: rgba(33, 150, 243, 0.1);
    }

    body {
        background-color: #f5f7fb;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        background: white;
        border-bottom: 1px solid #eef2f7;
        border-radius: 12px 12px 0 0 !important;
        padding: 1rem 1.5rem;
    }

    .bg-soft-primary {
        background-color: var(--soft-primary) !important;
        border-color: rgba(108, 99, 255, 0.2) !important;
    }

    .bg-soft-success {
        background-color: var(--soft-success) !important;
        border-color: rgba(76, 175, 80, 0.2) !important;
    }

    .bg-soft-warning {
        background-color: var(--soft-warning) !important;
        border-color: rgba(255, 152, 0, 0.2) !important;
    }

    .bg-soft-danger {
        background-color: var(--soft-danger) !important;
        border-color: rgba(244, 67, 54, 0.2) !important;
    }

    .bg-soft-info {
        background-color: var(--soft-info) !important;
        border-color: rgba(33, 150, 243, 0.2) !important;
    }

    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        color: #5a6c7d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #eef2f7;
        padding: 12px 16px;
        background-color: #f8fafc;
    }

    .table td {
        padding: 16px;
        font-size: 0.9rem;
        vertical-align: middle;
        border-bottom: 1px solid #eef2f7;
        color: #4a5568;
    }

    .table tbody tr:hover {
        background-color: rgba(108, 99, 255, 0.04);
    }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .btn {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-sm {
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.85rem;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
    }

    .progress {
        border-radius: 10px;
        background-color: #e2e8f0;
    }

    .progress-bar {
        border-radius: 10px;
    }

    /* Print styles */
    @media print {
        body * {
            visibility: hidden;
        }
        
        #reportTable,
        #reportTable *,
        .print-header,
        .print-header * {
            visibility: visible;
        }
        
        #reportTable {
            position: absolute;
            left: 0;
            top: 80px;
            width: 100%;
            margin-top: 80px; /* Adjust for fixed header */
        }
        
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            position: fixed;
            top: 0;
            left: 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: white;
            z-index: 1000;
        }
        
        .card-header, 
        .card-footer, 
        .btn, 
        .filter-section,
        .page-title,
        .row.mt-4,
        .table-search-section,
        .pagination,
        #toastContainer,
        .card:not(#reportTable) {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table {
            border: 1px solid #000 !important;
            page-break-inside: auto;
        }
        
        th, td {
            border: 1px solid #000 !important;
            padding: 8px !important;
            color: #000 !important;
            font-size: 11px !important;
        }
        
        tr {
            page-break-inside: avoid;
        }
        
        /* Prevent page break in the middle of total row */
        tr:last-child {
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
        }
        
        thead {
            display: table-header-group;
        }
        
        .badge {
            border: 1px solid #000 !important;
            color: #000 !important;
            background-color: transparent !important;
        }
        
        .text-primary, .text-success, .text-danger, .text-info, .text-warning {
            color: #000 !important;
        }
    }

    /* Toast notifications */
    .toast-notification {
        background: white;
        border-left: 4px solid var(--primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        animation: slideInRight 0.3s ease-out;
        max-width: 350px;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    /* Status badges */
    .badge.bg-success {
        background-color: var(--success) !important;
    }

    .badge.bg-danger {
        background-color: var(--danger) !important;
    }

    .badge.bg-warning {
        background-color: var(--warning) !important;
        color: #000 !important;
    }

    .badge.bg-info {
        background-color: var(--info) !important;
    }
</style>
@endsection

@section('scripts')
@vite(['resources/js/app.js'])
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Handle date range selection
    const dateRangeSelect = document.getElementById('dateRange');
    const customDateRange = document.getElementById('customDateRange');
    const customDateRangeTo = document.getElementById('customDateRangeTo');
    
    if (dateRangeSelect) {
        dateRangeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.style.display = 'block';
                customDateRangeTo.style.display = 'block';
            } else {
                customDateRange.style.display = 'none';
                customDateRangeTo.style.display = 'none';
                document.getElementById('fromDate').value = '';
                document.getElementById('toDate').value = '';
            }
        });
        
        // Initialize visibility
        if (dateRangeSelect.value === 'custom') {
            customDateRange.style.display = 'block';
            customDateRangeTo.style.display = 'block';
        }
    }

    // Initialize Chart.js if data exists
    /* Chart.js initialization handled separately with Blade conditionals */
    
    // Set progress bar width dynamically
    const progressBar = document.querySelector('.progress-bar[data-collection-rate]');
    if (progressBar) {
        const rate = progressBar.getAttribute('data-collection-rate');
        progressBar.style.width = rate + '%';
    }
    
    // Table search functionality
    const tableSearchInput = document.getElementById('tableSearch');
    if (tableSearchInput) {
        tableSearchInput.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                searchTable();
            }
        });
    }

    // Table status filter
    const tableStatusFilter = document.getElementById('tableStatusFilter');
    if (tableStatusFilter) {
        tableStatusFilter.addEventListener('change', filterTable);
    }
});

// Global functions
function searchTable() {
    const searchInput = document.getElementById('tableSearch');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#reportTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterTable() {
    const statusFilter = document.getElementById('tableStatusFilter');
    const selectedStatus = statusFilter.value;
    const rows = document.querySelectorAll('#reportTable tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        
        if (selectedStatus === 'all' || rowStatus === selectedStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function clearSearch() {
    document.querySelector('input[name="search"]').value = '';
    document.getElementById('reportFilterForm').submit();
}

function resetFilters() {
    // Reset all form fields
    const form = document.getElementById('reportFilterForm');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.type === 'text' || input.type === 'date') {
            input.value = '';
        } else if (input.type === 'select-one') {
            input.selectedIndex = 0;
        }
    });
    
    // Hide custom date range
    document.getElementById('customDateRange').style.display = 'none';
    document.getElementById('customDateRangeTo').style.display = 'none';
    
    // Submit form
    form.submit();
}

// Custom print function for report
function printReport() {
    // Get report content
    const printHeaderElement = document.querySelector('.print-header');
    let printHeader = '';
    if (printHeaderElement) {
        // Temporarily remove display: none to get clean HTML
        printHeaderElement.style.display = 'block';
        printHeader = printHeaderElement.outerHTML;
        printHeaderElement.style.display = 'none'; // Restore original state
    }
    const reportTable = document.querySelector('#reportTable')?.outerHTML || '';
    
    // Create print document
    const printContent = `
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title>Billing Report - {{ now()->format('Y-m-d') }}</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    color: #000;
                    padding: 10mm;
                    line-height: 1.4;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }

                .print-header {
                    margin: 0 auto 20px auto;
                    text-align: center;
                    max-width: 800px;
                }

                .print-header h2 {
                    color: #6c63ff;
                    margin: 0 0 5px 0;
                    font-size: 24px;
                }

                .print-header p {
                    margin: 0 0 5px 0;
                    font-size: 12px;
                    color: #666;
                    margin: 2px 0;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    page-break-inside: auto;
                }

                th {
                    background-color: #f2f2f2 !important;
                    font-weight: bold;
                    text-align: left;
                    padding: 8px;
                    border: 1px solid #000;
                    font-size: 10px;
                    text-transform: uppercase;
                }

                td {
                    padding: 6px 8px;
                    border: 1px solid #000;
                    vertical-align: middle;
                    font-size: 10px;
                }

                tr:last-child {
                    background-color: #f0f0f0 !important;
                    font-weight: bold;
                    page-break-inside: avoid !important;
                }

                .text-end {
                    text-align: right;
                }

                .text-center {
                    text-align: center;
                }

                .fw-bold {
                    font-weight: bold;
                }

                @page {
                    size: landscape;
                    margin: 10mm;
                }

                /* Enhanced header styles */
                .print-header div[style*='border-bottom'] {
                    border-bottom: 3px solid #6c63ff !important;
                    padding-bottom: 15px;
                    margin-bottom: 20px;
                }

                .print-header div[style*='display: flex'] {
                    display: flex !important;
                    justify-content: center !important;
                    align-items: flex-start !important;
                }

                .print-header div[style*='text-align: right'] {
                    text-align: right !important;
                }

                .print-header div[style*='background-color: #6c63ff'] {
                    background-color: #6c63ff !important;
                    color: white !important;
                    padding: 8px 15px !important;
                    border-radius: 5px !important;
                    display: inline-block !important;
                }

                .print-header div[style*='width: 50%'] {
                    width: 50% !important;
                }

                .print-header h4 {
                    margin: 0 0 10px 0 !important;
                    color: #333 !important;
                }

                .print-header div[style*='display: flex; justify-content: space-between'] {
                    display: flex !important;
                    justify-content: space-between !important;
                    margin-bottom: 20px !important;
                }
            </style>
        </head>
        <body>
            ${printHeader}
            ${reportTable}
            
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 500);
                    
                    window.onafterprint = function() {
                        window.close();
                    };
                };
            <\/script>
        </body>
        </html>
    `;
    
    // Open print window
    const printWindow = window.open('', '_blank', 'width=1200,height=800');
    if (!printWindow) {
        alert('Please allow popups for printing');
        return;
    }
    
    printWindow.document.write(printContent);
    printWindow.document.close();
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastId = 'toast-' + Date.now();
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-triangle' : 
                 type === 'warning' ? 'exclamation-circle' : 'info-circle';
    
    const color = type === 'success' ? '#4caf50' : 
                  type === 'error' ? '#f44336' : 
                  type === 'warning' ? '#ff9800' : '#2196f3';
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast-notification';
    toast.style.borderLeftColor = color;
    
    toast.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="fas fa-${icon} me-3" style="color: ${color};"></i>
            <div class="flex-grow-1">
                <div class="fw-medium">${message}</div>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="document.getElementById('${toastId}').remove()"></button>
        </div>
    `;
    
    const container = document.getElementById('toastContainer');
    if (container) {
        container.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            const element = document.getElementById(toastId);
            if (element) {
                element.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => element.remove(), 300);
            }
        }, 5000);
    }
}
</script>
@endsection