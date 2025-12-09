@extends('layouts.admin')

@section('title', 'Financial Analytics')
@section('title-icon', 'fa-chart-pie')
@section('subtitle', 'Comprehensive Financial Performance Insights')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">Financial Analytics</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <button class="btn btn-outline-primary" id="exportReport">
            <i class="fas fa-download me-2"></i>Export
        </button>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
    </div>
@endsection

@section('content')
<div class="page-body">
    @php
        use Illuminate\Support\Str;
        // Determine first billing month: use earliest of first invoice date and first customer registration
        $firstInvoiceDate = \DB::table('invoices')->whereNotNull('issue_date')->min('issue_date');
        $firstCustomerDate = \DB::table('customers')->whereNotNull('created_at')->min('created_at');
        // Choose earliest available date
        $earliestDate = null;
        if ($firstInvoiceDate) {
            $earliestDate = \Carbon\Carbon::parse($firstInvoiceDate);
        }
        if ($firstCustomerDate) {
            $cust = \Carbon\Carbon::parse($firstCustomerDate);
            if (is_null($earliestDate) || $cust->lt($earliestDate)) {
                $earliestDate = $cust;
            }
        }
        // Fallback to 11 months ago if no dates found
        if (is_null($earliestDate)) {
            $startMonth = \Carbon\Carbon::now()->subMonths(11)->format('Y-m');
        } else {
            $startMonth = $earliestDate->format('Y-m');
        }
        $currentMonth = \Carbon\Carbon::now()->format('Y-m');

        // Build full month list
        $allMonths = [];
        $start = \Carbon\Carbon::createFromFormat('Y-m', $startMonth);
        $end = \Carbon\Carbon::createFromFormat('Y-m', $currentMonth);
        while ($start->lte($end)) {
            $allMonths[] = $start->format('Y-m');
            $start->addMonth();
        }

        // Normalize monthlyTrend (which may be a collection of ['month','revenue','collected'])
        $monthlyByMonth = collect($monthlyTrend ?? [])->keyBy('month');
        $completedMonthlyTrend = collect($allMonths)->map(function($month) use ($monthlyByMonth) {
            if ($monthlyByMonth->has($month)) {
                return $monthlyByMonth[$month];
            }
            return (object) [
                'month' => $month,
                'revenue' => 0,
                'collected' => 0,
            ];
        });

        // Precompute JS-friendly arrays
        $chartLabels = $completedMonthlyTrend->pluck('month')->map(function($m) {
            return \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y');
        })->values()->all();
        $chartRevenue = $completedMonthlyTrend->pluck('revenue')->map(function($v) { return (float) $v; })->values()->all();
        $chartCollected = $completedMonthlyTrend->pluck('collected')->map(function($v) { return (float) $v; })->values()->all();

        // Product labels/data for distribution chart
        if (isset($revenueByProduct) && $revenueByProduct->count() > 0) {
            $productLabels = $revenueByProduct->pluck('product_name')->map(function($n) { return Str::limit($n, 20); })->toArray();
            $productData = $revenueByProduct->pluck('revenue')->map(function($v){ return (float) $v; })->toArray();
        } else {
            $productLabels = ['No Data'];
            $productData = [100];
        }
    @endphp
    <!-- Key Financial Metrics -->
    <div class="row mb-4">
        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($totals->total_revenue ?? 0, 2) }}
                            </div>
                            <div class="mt-2 text-success">
                                <small>
                                    <i class="fas fa-arrow-up me-1"></i>
                                    12.5% from last period
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Collected -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Collected
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($totals->total_collected ?? 0, 2) }}
                            </div>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Collection Rate: {{ $totals->total_revenue > 0 ? number_format(($totals->total_collected / $totals->total_revenue) * 100, 1) : 0 }}%
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Collection -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Collection
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($totals->total_pending ?? 0, 2) }}
                            </div>
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    Awaiting payment
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Transaction -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Transaction
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format(($totals->total_revenue ?? 0) / max(1, ($topCustomers->count())), 2) }}
                            </div>
                            <div class="mt-2 text-info">
                                <small>
                                    <i class="fas fa-chart-line me-1"></i>
                                    Per customer
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line fa-lg me-2"></i>Revenue Trend
                        <small class="text-muted ms-2">({{ \Carbon\Carbon::createFromFormat('Y-m', $startMonth)->format('M Y') }} - {{ \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('M Y') }})</small>
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#">View Details</a>
                            <a class="dropdown-item" href="#">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie fa-lg me-2"></i>Revenue by Product Category
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="revenueDistributionChart" height="250"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @if(isset($revenueByProduct) && $revenueByProduct->count() > 0)
                            @foreach($revenueByProduct as $index => $product)
                            <span class="me-3">
                                <i class="fas fa-circle me-1" style="color: {{ $index == 0 ? '#4e73df' : ($index == 1 ? '#1cc88a' : ($index == 2 ? '#36b9cc' : '#f6c23e')) }}"></i>
                                {{ Str::limit($product->product_name, 15) }}
                            </span>
                            @endforeach
                        @else
                            <span class="text-muted">No product data available</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers & Performance Metrics -->
    <div class="row">
        <!-- Top Customers -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy fa-lg me-2"></i>Top 10 Customers by Revenue
                    </h6>
                    <span class="badge bg-primary">{{ $topCustomers->count() }} Customers</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="topCustomersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Customer</th>
                                    <th>Total Billed</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $index => $customer)
                                @php
                                    $balance = $customer->total_billed - $customer->total_paid;
                                    $status = $balance == 0 ? 'Paid' : ($balance == $customer->total_billed ? 'Unpaid' : 'Partial');
                                    $statusClass = $status == 'Paid' ? 'success' : ($status == 'Unpaid' ? 'danger' : 'warning');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-{{ $index < 3 ? 'primary' : 'secondary' }} me-2">
                                                #{{ $index + 1 }}
                                            </span>
                                            @if($index < 3)
                                            <i class="fas fa-crown text-warning"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 35px; height: 35px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $customer->name }}</div>
                                                <small class="text-muted">{{ $customer->customer_id ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-primary">৳{{ number_format($customer->total_billed, 2) }}</td>
                                    <td class="text-success">৳{{ number_format($customer->total_paid ?? 0, 2) }}</td>
                                    <td class="text-{{ $statusClass }}">৳{{ number_format($balance, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $statusClass }}">
                                            <i class="fas fa-{{ $status == 'Paid' ? 'check' : ($status == 'Unpaid' ? 'times' : 'clock') }} me-1"></i>
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-lg-4">
            <!-- Collection Efficiency -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bullseye fa-lg me-2"></i>Collection Efficiency
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="position-relative d-inline-block">
                            <canvas id="collectionGauge" width="200" height="120"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <div class="h4 mb-0">{{ $totals->total_revenue > 0 ? number_format(($totals->total_collected / $totals->total_revenue) * 100, 1) : 0 }}%</div>
                                <small class="text-muted">Collection Rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4 text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 mb-1 text-success">৳{{ number_format($totals->total_collected ?? 0, 2) }}</div>
                                <small class="text-muted">Collected</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-1 text-warning">৳{{ number_format($totals->total_pending ?? 0, 2) }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar fa-lg me-2"></i>Performance Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Customer Growth</span>
                            <span class="fw-bold text-success">+15%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 75%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Revenue Growth</span>
                            <span class="fw-bold text-primary">+12.5%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 65%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Collection Rate</span>
                            <span class="fw-bold text-info">{{ $totals->total_revenue > 0 ? number_format(($totals->total_collected / $totals->total_revenue) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ $totals->total_revenue > 0 ? ($totals->total_collected / $totals->total_revenue) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                    <div class="h6 mb-0">{{ $topCustomers->count() }}</div>
                                    <small class="text-muted">Top Customers</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <i class="fas fa-receipt fa-2x text-success mb-2"></i>
                                    <div class="h6 mb-0">{{ $completedMonthlyTrend->count() ?? 0 }}</div>
                                    <small class="text-muted">Active Months</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Analytics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-analytics fa-lg me-2"></i>Financial Health Indicators
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-tachometer-alt fa-2x text-primary mb-2"></i>
                                <div class="h5 mb-1">Excellent</div>
                                <small class="text-muted">Financial Health</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-arrow-up fa-2x text-success mb-2"></i>
                                <div class="h5 mb-1">Growing</div>
                                <small class="text-muted">Revenue Trend</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                                <div class="h5 mb-1">Stable</div>
                                <small class="text-muted">Risk Level</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-star fa-2x text-info mb-2"></i>
                                <div class="h5 mb-1">A+</div>
                                <small class="text-muted">Performance Grade</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* General Style Enhancements */
body {
    background-color: #f8f9fa; /* Lighter gray background */
    color: #343a40;
}

.card {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 1.5rem; /* Ensure consistent spacing */
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.card .card-header {
    background-color: #fff;
    border-bottom: 1px solid #e9ecef;
    font-weight: 600;
    color: #343a40;
    border-radius: 0.75rem 0.75rem 0 0;
    padding: 1rem 1.5rem;
}

/* Stat Card Modernization */
.stat-card.border-left-primary { border-left: 4px solid #4e73df; }
.stat-card.border-left-success { border-left: 4px solid #1cc88a; }
.stat-card.border-left-warning { border-left: 4px solid #f6c23e; }
.stat-card.border-left-info { border-left: 4px solid #36b9cc; }

.stat-card .card-body {
    padding: 1.5rem;
}

.stat-card .text-xs {
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.stat-card .h5 {
    font-size: 1.75rem;
    font-weight: 700;
}

/* Chart Containers */
.chart-area {
    position: relative;
    width: 100%;
    height: 300px;
}
.chart-pie {
    position: relative;
    width: 100%;
    height: 250px;
}
.chart-bar {
    position: relative;
    height: 100px;
    width: 100%;
}

/* Table Enhancements */
.table-responsive {
    border-radius: 0.5rem;
    overflow-x: auto;
}
.table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.85rem;
    color: #6c757d;
}
.table tbody tr:hover {
    background-color: #f1f3f5;
}
.table td, .table th {
    vertical-align: middle;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
}
.table tbody tr:last-child td {
    border-bottom: none;
}
.table .badge {
    font-size: 0.8rem;
    padding: 0.4em 0.7em;
    font-weight: 600;
}

/* Progress Bar */
.progress {
    height: 10px;
    border-radius: 50rem;
    background-color: #e9ecef;
}
.progress-bar {
    border-radius: 50rem;
}

/* Pagination */
.pagination { 
    margin-bottom: 0; 
}
.pagination .page-link {
    border-radius: 0.375rem !important;
    margin: 0 3px;
    border: 1px solid #dee2e6;
    color: #4e73df;
    font-weight: 500;
}
.pagination .page-item.active .page-link {
    background-color: #4e73df;
    border-color: #4e73df;
    color: #fff;
    box-shadow: 0 2px 6px rgba(78, 115, 223, 0.3);
}
.pagination .page-link:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}
.page-item.disabled .page-link {
    color: #858796;
}

/* List Group */
.list-group-item { 
    border: none; 
    border-bottom: 1px solid #eaecf4;
    padding: 1rem 0;
}
.list-group-item:last-child { 
    border-bottom: none; 
}

/* Loading Spinner for export buttons */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.loading-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueTrendChart');
    if (revenueCtx) {
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Total Revenue',
                    data: @json($chartRevenue),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Collected Amount',
                    data: @json($chartCollected),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ৳' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '৳' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Revenue Distribution Chart
    const distributionCtx = document.getElementById('revenueDistributionChart');
    if (distributionCtx) {
        const distributionChart = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: @json($productLabels),
                datasets: [{
                    data: @json($productData),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b', '#6a6c7a', '#4a4c5a'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ৳' + context.parsed.toLocaleString();
                            }
                        }
                    }
                },
                cutout: '70%',
            },
        });
    }

    // Collection Gauge Chart
    const gaugeCtx = document.getElementById('collectionGauge');
    if (gaugeCtx) {
        const collectionRate = {{ $totals->total_revenue > 0 ? ($totals->total_collected / $totals->total_revenue) * 100 : 0 }};
        const gaugeChart = new Chart(gaugeCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [collectionRate, 100 - collectionRate],
                    backgroundColor: [collectionRate >= 80 ? '#1cc88a' : collectionRate >= 60 ? '#f6c23e' : '#e74a3c', '#eaecf4'],
                    borderWidth: 0,
                }]
            },
            options: {
                circumference: 180,
                rotation: -90,
                cutout: '70%',
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

    // Initialize DataTable for Top Customers
    const customersTable = document.getElementById('topCustomersTable');
    if (customersTable) {
        $('#topCustomersTable').DataTable({
            pageLength: 10,
            order: [[2, 'desc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search customers...",
                paginate: {
                    previous: "<i class='fas fa-chevron-left'></i>",
                    next: "<i class='fas fa-chevron-right'></i>"
                }
            }
        });
    }

    // Export functionality
    document.getElementById('exportReport')?.addEventListener('click', function() {
        this.innerHTML = '<span class="loading-spinner me-2"></span>Exporting...';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-download me-2"></i>Export';
            // Simulate download
            const link = document.createElement('a');
            link.href = '#';
            link.download = 'financial-analytics-report.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }, 2000);
    });
});
</script>
@endpush