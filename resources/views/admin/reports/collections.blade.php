@extends('layouts.admin')

@section('title', 'Collection Analytics')
@section('title-icon', 'fa-money-bill-wave')
@section('subtitle', 'Payment Collection Performance & Insights')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">Collection Analytics</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <button class="btn btn-outline-primary" id="exportCollectionReport">
            <i class="fas fa-download me-2"></i>Export
        </button>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#collectionSummaryModal">
            <i class="fas fa-chart-pie me-2"></i>Summary
        </button>
    </div>
@endsection

@section('content')
<div class="page-body">
    <!-- Collection Overview Cards -->
    <div class="row mb-4">
        <!-- Total Collected -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Collected
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($totalCollected, 2) }}
                            </div>
                            <div class="mt-2 text-success">
                                <small>
                                    <i class="fas fa-arrow-up me-1"></i>
                                    15.2% from last period
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Collection -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Collection
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($todayCollected ?? 0, 2) }}
                            </div>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    {{ \Carbon\Carbon::today()->format('M d, Y') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month Collection -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                This Month Collection
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($monthCollected ?? 0, 2) }}
                            </div>
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="fas fa-chart-line me-1"></i>
                                    {{ \Carbon\Carbon::now()->format('F Y') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Collection -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Collection/Day
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format(($totalCollected ?? 0) / max(1, ($collections->total() / 30)), 2) }}
                            </div>
                            <div class="mt-2 text-info">
                                <small>
                                    <i class="fas fa-calculator me-1"></i>
                                    Last 30 days average
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Collection Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line fa-lg me-2"></i>Collection Trend (Last 30 Days)
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
                        <canvas id="collectionTrendChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card fa-lg me-2"></i>Payment Methods
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="paymentMethodChart" height="250"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @if(isset($paymentSummary) && $paymentSummary->count() > 0)
                            @foreach($paymentSummary as $index => $method)
                            <span class="me-2 d-block mb-1">
                                <i class="fas fa-circle me-1" style="color: {{ $index == 0 ? '#4e73df' : ($index == 1 ? '#1cc88a' : ($index == 2 ? '#36b9cc' : '#f6c23e')) }}"></i>
                                {{ ucfirst($method->payment_method) }} ({{ $method->transaction_count }})
                            </span>
                            @endforeach
                        @else
                            <span class="text-muted">No payment data available</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Performance & Recent Transactions -->
    <div class="row">
        <!-- Recent Collections -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list fa-lg me-2"></i>Recent Collection Transactions
                    </h6>
                    <span class="badge bg-primary">{{ $collections->total() }} Transactions</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="collectionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Customer</th>
                                    <th>Invoice</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Transaction ID</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collections as $payment)
                                @php
                                    $paymentDate = \Carbon\Carbon::parse($payment->payment_date);
                                    $isToday = $paymentDate->isToday();
                                    $isRecent = $paymentDate->gt(now()->subDays(3));
                                    $statusClass = $isToday ? 'success' : ($isRecent ? 'info' : 'secondary');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-{{ $statusClass }} rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 35px; height: 35px;">
                                                <i class="fas fa-{{ $isToday ? 'check' : 'clock' }} text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $paymentDate->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $paymentDate->format('h:i A') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $payment->customer_name }}</div>
                                        <small class="text-muted">{{ $payment->customer_id ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-file-invoice me-1"></i>
                                            {{ $payment->invoice_number }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">৳{{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->payment_method == 'cash' ? 'success' : ($payment->payment_method == 'bank' ? 'primary' : 'warning') }}">
                                            <i class="fas fa-{{ $payment->payment_method == 'cash' ? 'money-bill' : ($payment->payment_method == 'bank' ? 'university' : 'mobile') }} me-1"></i>
                                            {{ ucfirst($payment->payment_method) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted font-monospace">
                                            {{ $payment->transaction_id ?? 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusClass }}">
                                            <i class="fas fa-{{ $isToday ? 'check-circle' : 'clock' }} me-1"></i>
                                            {{ $isToday ? 'Today' : ($isRecent ? 'Recent' : 'Processed') }}
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

        <!-- Collection Performance & Summary -->
        <div class="col-lg-4">
            <!-- Collection Performance -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tachometer-alt fa-lg me-2"></i>Collection Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <canvas id="collectionPerformanceGauge" width="200" height="120"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <div class="h4 mb-0 text-success">78%</div>
                                <small class="text-muted">Efficiency</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">On-time Collection</span>
                            <span class="fw-bold text-success">85%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 85%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Digital Payments</span>
                            <span class="fw-bold text-primary">65%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 65%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Collection Growth</span>
                            <span class="fw-bold text-info">15.2%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Collection Stats -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt fa-lg me-2"></i>Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-sync fa-2x text-primary mb-2"></i>
                                <div class="h6 mb-0">{{ $collections->count() }}</div>
                                <small class="text-muted">This Page</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <i class="fas fa-calendar-week fa-2x text-success mb-2"></i>
                                <div class="h6 mb-0">{{ $paymentSummary->sum('transaction_count') ?? 0 }}</div>
                                <small class="text-muted">This Month</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <i class="fas fa-user-check fa-2x text-warning mb-2"></i>
                                <div class="h6 mb-0">{{ $collections->unique('customer_id')->count() }}</div>
                                <small class="text-muted">Unique Payers</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                                <div class="h6 mb-0">94%</div>
                                <small class="text-muted">Success Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Collection Summary Modal -->
<div class="modal fade" id="collectionSummaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-pie me-2"></i>Collection Summary Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Payment Methods Breakdown</h6>
                        <canvas id="modalPaymentChart" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h6>Daily Collection Average</h6>
                        <div class="text-center py-4">
                            <div class="h1 text-primary">৳{{ number_format(($totalCollected ?? 0) / max(1, ($collections->total() / 30)), 2) }}</div>
                            <p class="text-muted">Per day (30-day average)</p>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Highest Single Collection</span>
                                <strong class="text-success">৳{{ number_format($collections->max('amount') ?? 0, 2) }}</strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Average Collection</span>
                                <strong class="text-primary">৳{{ number_format($collections->avg('amount') ?? 0, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Export Summary</button>
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
    // Collection Trend Chart (Sample data - replace with actual data)
    const trendCtx = document.getElementById('collectionTrendChart');
    if (trendCtx) {
        const trendChart = new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: Array.from({length: 30}, (_, i) => {
                    const date = new Date();
                    date.setDate(date.getDate() - (29 - i));
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Daily Collection',
                    data: Array.from({length: 30}, () => Math.floor(Math.random() * 50000) + 10000),
                    backgroundColor: '#4e73df',
                    borderColor: '#2e59d9',
                    borderWidth: 1,
                    borderRadius: 4
                }]
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
                                return 'Collection: ৳' + context.parsed.y.toLocaleString();
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

    // Payment Method Chart
    const paymentCtx = document.getElementById('paymentMethodChart');
    if (paymentCtx && {{ isset($paymentSummary) && $paymentSummary->count() > 0 ? 'true' : 'false' }}) {
        const paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: {!! isset($paymentSummary) ? json_encode($paymentSummary->pluck('payment_method')->map(function($method) {
                    return ucfirst($method);
                })) : json_encode([]) !!},
                datasets: [{
                    data: {!! isset($paymentSummary) ? json_encode($paymentSummary->pluck('total_amount')) : json_encode([]) !!},
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619'],
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

    // Collection Performance Gauge
    const performanceCtx = document.getElementById('collectionPerformanceGauge');
    if (performanceCtx) {
        const performanceChart = new Chart(performanceCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [78, 22],
                    backgroundColor: ['#1cc88a', '#eaecf4'],
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

    // Initialize DataTable for Collections
    const collectionsTable = document.getElementById('collectionsTable');
    if (collectionsTable) {
        $('#collectionsTable').DataTable({
            pageLength: 10,
            order: [[0, 'desc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search collections...",
                paginate: {
                    previous: "<i class='fas fa-chevron-left'></i>",
                    next: "<i class='fas fa-chevron-right'></i>"
                }
            }
        });
    }

    // Export functionality
    document.getElementById('exportCollectionReport')?.addEventListener('click', function() {
        const btn = this;
        btn.innerHTML = '<span class="loading-spinner me-2"></span>Exporting...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-download me-2"></i>Export';
            btn.disabled = false;
            // Simulate download
            const link = document.createElement('a');
            link.href = '#';
            link.download = 'collection-report-' + new Date().toISOString().split('T')[0] + '.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }, 2000);
    });

    // Modal payment chart
    const modalPaymentCtx = document.getElementById('modalPaymentChart');
    if (modalPaymentCtx && {{ isset($paymentSummary) && $paymentSummary->count() > 0 ? 'true' : 'false' }}) {
        const modalPaymentChart = new Chart(modalPaymentCtx, {
            type: 'pie',
            data: {
                labels: {!! isset($paymentSummary) ? json_encode($paymentSummary->pluck('payment_method')->map(function($method) {
                    return ucfirst($method);
                })) : json_encode([]) !!},
                datasets: [{
                    data: {!! isset($paymentSummary) ? json_encode($paymentSummary->pluck('total_amount')) : json_encode([]) !!},
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>
@endpush