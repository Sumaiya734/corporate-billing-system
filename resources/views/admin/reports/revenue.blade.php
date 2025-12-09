@extends('layouts.admin')

@section('title', 'Revenue Analytics Dashboard')
@section('title-icon', 'fa-chart-line')
@section('subtitle', 'Comprehensive revenue performance and financial insights')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">Revenue Analytics</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <button class="btn btn-outline-primary" id="exportRevenueReport">
            <i class="fas fa-download me-2"></i>Export
        </button>
        <button class="btn btn-success" id="refreshRevenueData">
            <i class="fas fa-sync-alt me-2"></i>Refresh Data
        </button>
    </div>
@endsection

@section('content')

    @php
        // Get the first customer registration date to determine start month
        $firstCustomerDate = \DB::table('customers')->min('created_at');
        $startMonth = $firstCustomerDate ? \Carbon\Carbon::parse($firstCustomerDate)->format('Y-m') : date('Y-m');
        $currentMonth = date('Y-m');
        
        $yrRev = optional($yearlyTotals)->yearly_revenue ?? 0;
        $yrCol = optional($yearlyTotals)->yearly_collected ?? 0;
        $yrPend = optional($yearlyTotals)->yearly_pending ?? 0;
        $curMonthRev = optional($currentMonthData)->month_revenue ?? 0;
        $curMonthCol = optional($currentMonthData)->month_collected ?? 0;

        // Build a complete, chronologically ordered list of months from startMonth to currentMonth
        $allMonths = [];
        $start = \Carbon\Carbon::createFromFormat('Y-m', $startMonth);
        $end = \Carbon\Carbon::createFromFormat('Y-m', $currentMonth);
        while ($start->lte($end)) {
            $allMonths[] = $start->format('Y-m');
            $start->addMonth();
        }

        // Index revenue data by month for fast lookup
        $revenueByMonth = $revenueData->keyBy('month');

        // Fill missing months with zeroed data
        $completedRevenueData = collect($allMonths)->map(function($month) use ($revenueByMonth) {
            if ($revenueByMonth->has($month)) {
                return $revenueByMonth[$month];
            }
            return (object) [
                'month' => $month,
                'total_revenue' => 0,
                'collected' => 0,
                'pending' => 0,
            ];
        });

        // Use this for all charts and tables
        $sortedRevenueData = $completedRevenueData;
        // Precompute chart labels and colors for JS (avoid closures in @json)
        $chartLabels = $sortedRevenueData->pluck('month')->map(function($month) {
            return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');
        })->values()->all();
        $barLabels = $sortedRevenueData->pluck('month')->map(function($month) {
            return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M');
        })->values()->all();
        $collectionRates = $sortedRevenueData->map(function($row) {
            return $row->total_revenue > 0 ? ($row->collected / $row->total_revenue) * 100 : 0;
        })->values()->all();
        $barColors = $sortedRevenueData->map(function($row) {
            $rate = $row->total_revenue > 0 ? ($row->collected / $row->total_revenue) * 100 : 0;
            return $rate >= 80 ? '#1cc88a' : ($rate >= 60 ? '#f6c23e' : '#e74a3c');
        })->values()->all();
    @endphp

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <!-- Total Revenue Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Revenue (Year {{ date('Y') }})
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($yrRev, 2) }}
                            </div>
                            <div class="mt-2 text-success">
                                <small>
                                    <i class="fas fa-arrow-up me-1"></i>
                                    {{ number_format($revenueGrowth, 1) }}% vs last month
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

        <!-- Collected Revenue Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Collected Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($yrCol, 2) }}
                            </div>
                            <div class="mt-2">
                                <small class="text-success">
                                    Collection Rate: {{ $yrRev > 0 ? number_format(($yrCol / $yrRev) * 100, 1) : 0 }}%
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

        <!-- Pending Amount Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Collection
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($yrPend, 2) }}
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

        <!-- Current Month Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Current Month ({{ date('F Y') }})
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($curMonthRev, 2) }}
                            </div>
                            <div class="mt-2 text-info">
                                <small>
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $curMonthRev > 0 ? number_format(($curMonthCol / $curMonthRev) * 100, 1) : 0 }}% collected
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Visualizations -->
    <div class="row mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line fa-lg me-2"></i>Monthly Revenue Trend
                        <small class="text-muted ms-2">
                            ({{ \Carbon\Carbon::parse($startMonth . '-01')->format('M Y') }} - {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('M Y') }})
                        </small>
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#" id="exportChartData">Export Chart Data</a>
                            <a class="dropdown-item" href="#" id="toggleChartView">Toggle View</a>
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

        <!-- Collection Efficiency Gauge -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bullseye fa-lg me-2"></i>Collection Efficiency
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="position-relative d-inline-block">
                            <canvas id="collectionEfficiencyGauge" width="200" height="120"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <div class="h4 mb-0 text-{{ $yrRev > 0 ? (($yrCol / $yrRev) * 100 >= 80 ? 'success' : (($yrCol / $yrRev) * 100 >= 60 ? 'warning' : 'danger')) : 'secondary' }}">
                                    {{ $yrRev > 0 ? number_format(($yrCol / $yrRev) * 100, 1) : 0 }}%
                                </div>
                                <small class="text-muted">Collection Rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4 text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 mb-1 text-success">৳{{ number_format($yrCol, 2) }}</div>
                                <small class="text-muted">Collected</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-1 text-warning">৳{{ number_format($yrPend, 2) }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Performance Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar fa-lg me-2"></i>Monthly Performance Breakdown
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="monthlyPerformanceChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Revenue Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table fa-lg me-2"></i>Detailed Monthly Revenue Breakdown
                <small class="text-muted ms-2">(Chronological Order)</small>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="revenueTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Month</th>
                            <th>Total Billed</th>
                            <th>Collected</th>
                            <th>Pending</th>
                            <th>Collection Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sortedRevenueData as $row)
                        @php
                            $collectionRate = $row->total_revenue > 0 ? ($row->collected / $row->total_revenue) * 100 : 0;
                            $statusClass = $collectionRate >= 80 ? 'success' : ($collectionRate >= 60 ? 'warning' : 'danger');
                            $statusIcon = $collectionRate >= 80 ? 'fa-check-circle' : ($collectionRate >= 60 ? 'fa-exclamation-circle' : 'fa-times-circle');
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ \Carbon\Carbon::createFromFormat('Y-m', $row->month)->format('F Y') }}</td>
                            <td>৳{{ number_format($row->total_revenue, 2) }}</td>
                            <td>
                                <span class="text-success fw-bold">৳{{ number_format($row->collected, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-warning">৳{{ number_format($row->pending, 2) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $statusClass }}" 
                                             role="progressbar" style="width: {{ $collectionRate }}%;" 
                                             aria-valuenow="{{ $collectionRate }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="fw-bold">{{ number_format($collectionRate, 1) }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusClass }}">
                                    <i class="fas {{ $statusIcon }} me-1"></i>
                                    {{ $collectionRate >= 80 ? 'Excellent' : ($collectionRate >= 60 ? 'Good' : 'Needs Attention') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th class="text-end">Total:</th>
                            <th class="text-primary">৳{{ number_format($sortedRevenueData->sum('total_revenue'), 2) }}</th>
                            <th class="text-success">৳{{ number_format($sortedRevenueData->sum('collected'), 2) }}</th>
                            <th class="text-warning">৳{{ number_format($sortedRevenueData->sum('pending'), 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Reporting Period</div>
                            <div class="h5 mb-0">{{ $sortedRevenueData->count() }} Months</div>
                            <small class="opacity-75">
                                {{ \Carbon\Carbon::parse($startMonth . '-01')->format('M Y') }} - {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('M Y') }}
                            </small>
                        </div>
                        <i class="fas fa-calendar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Avg. Monthly Revenue</div>
                            <div class="h5 mb-0">
                                ৳{{ $sortedRevenueData->count() > 0 ? number_format($sortedRevenueData->avg('total_revenue'), 2) : '0.00' }}
                            </div>
                        </div>
                        <i class="fas fa-chart-bar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Best Month</div>
                            <div class="h5 mb-0">
                                ৳{{ number_format($sortedRevenueData->max('total_revenue') ?? 0, 2) }}
                            </div>
                        </div>
                        <i class="fas fa-trophy fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
              </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Growth Trend</div>
                            <div class="h5 mb-0">{{ number_format($revenueGrowth, 1) }}%</div>
                        </div>
                        <i class="fas fa-arrow-up fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
    // Revenue Trend Chart - UNIQUE TO REVENUE PAGE
    const revenueCtx = document.getElementById('revenueTrendChart');
    if (revenueCtx) {
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Total Billed',
                    data: @json($sortedRevenueData->pluck('total_revenue')),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                }, {
                    label: 'Collected',
                    data: @json($sortedRevenueData->pluck('collected')),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
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

    // Collection Efficiency Gauge - UNIQUE TO REVENUE PAGE
    const efficiencyCtx = document.getElementById('collectionEfficiencyGauge');
    if (efficiencyCtx) {
        const collectionRate = {{ $yrRev > 0 ? ($yrCol / $yrRev) * 100 : 0 }};
        const efficiencyChart = new Chart(efficiencyCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [collectionRate, 100 - collectionRate],
                    backgroundColor: [
                        collectionRate >= 80 ? '#1cc88a' : collectionRate >= 60 ? '#f6c23e' : '#e74a3c',
                        '#eaecf4'
                    ],
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

    // Monthly Performance Chart - UNIQUE TO REVENUE PAGE
    const performanceCtx = document.getElementById('monthlyPerformanceChart');
    if (performanceCtx) {
        const performanceChart = new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: @json($barLabels),
                datasets: [{
                    label: 'Collection Rate %',
                    data: @json($collectionRates),
                    backgroundColor: @json($barColors),
                    borderColor: 'transparent',
                    borderWidth: 0
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize DataTable - Set to chronological order (ascending)
    const revenueTable = document.getElementById('revenueTable');
    if (revenueTable) {
        $('#revenueTable').DataTable({
            pageLength: 10,
            order: [[0, 'asc']], // Chronological order (oldest first)
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search months...",
                paginate: {
                    previous: "<i class='fas fa-chevron-left'></i>",
                    next: "<i class='fas fa-chevron-right'></i>"
                }
            }
        });
    }

    // Refresh button functionality
    const refreshBtn = document.getElementById('refreshRevenueData');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const originalText = this.innerHTML;
            icon.className = 'fas fa-spinner fa-spin me-2';
            this.disabled = true;
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        });
    }

    // Export functionality
    const exportBtn = document.getElementById('exportRevenueReport');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const btn = this;
            btn.innerHTML = '<span class="loading-spinner me-2"></span>Exporting...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-download me-2"></i>Export';
                btn.disabled = false;
                // Simulate download
                const link = document.createElement('a');
                link.href = '#';
                link.download = 'revenue-report-' + new Date().toISOString().split('T')[0] + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }, 2000);
        });
    }
});
</script>
@endpush