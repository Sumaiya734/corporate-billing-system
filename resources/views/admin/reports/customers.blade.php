@extends('layouts.admin')

@section('title', 'Customer Analytics')
@section('title-icon', 'fa-users')
@section('subtitle', 'Comprehensive Customer Insights & Behavior Analysis')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active" aria-current="page">Customer Analytics</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <button class="btn btn-outline-primary" id="exportCustomerReport">
            <i class="fas fa-download me-2"></i>Export
        </button>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
    </div>
@endsection

@section('content')
<div class="page-body">
    <!-- Customer Overview Cards -->
    <div class="row mb-4">
        <!-- Total Customers -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Customers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCustomers }}</div>
                            <div class="mt-2 text-success">
                                <small>
                                    <i class="fas fa-arrow-up me-1"></i>
                                    {{ $newCustomers->sum('count') }} new this period
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Customers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeCustomers }}</div>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $totalCustomers > 0 ? number_format(($activeCustomers / $totalCustomers) * 100, 1) : 0 }}% Active Rate
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inactive Customers -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Inactive Customers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inactiveCustomers ?? ($totalCustomers - $activeCustomers) }}</div>
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    Needs attention
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Growth Rate -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Growth Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                +{{ $newCustomers->sum('count') }}
                            </div>
                            <div class="mt-2 text-info">
                                <small>
                                    <i class="fas fa-chart-line me-1"></i>
                                    Last 6 months
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Customer Growth Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line fa-lg me-2"></i>Customer Growth Trend (Last 6 Months)
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
                        <canvas id="customerGrowthChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie fa-lg me-2"></i>Customer Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="customerDistributionChart" height="250"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="me-3">
                            <i class="fas fa-circle me-1 text-success"></i>
                            Active ({{ $activeCustomers }})
                        </span>
                        <span class="me-3">
                            <i class="fas fa-circle me-1 text-warning"></i>
                            Inactive ({{ $inactiveCustomers ?? ($totalCustomers - $activeCustomers) }})
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Location & Dues Section -->
    <div class="row">
        <!-- Customers with Outstanding Dues -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-triangle fa-lg me-2"></i>Customers with Outstanding Dues
                    </h6>
                    <span class="badge bg-danger">{{ $customersWithDues->count() }} Customers</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="duesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Customer ID</th>
                                    <th>Phone</th>
                                    <th>Total Due</th>
                                    <th>Pending Invoices</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customersWithDues as $customer)
                                @php
                                    $dueAmount = $customer->total_due ?? $customer->next_due ?? 0;
                                    $pendingInvoices = $customer->pending_invoices ?? 1;
                                    $statusClass = $dueAmount > 10000 ? 'danger' : ($dueAmount > 5000 ? 'warning' : 'info');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 35px; height: 35px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $customer->name }}</div>
                                                <small class="text-muted">Last activity</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-muted">{{ $customer->customer_id ?? 'N/A' }}</td>
                                    <td>
                                        @if($customer->phone)
                                            <i class="fas fa-phone me-1 text-muted"></i>
                                            {{ $customer->phone }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-danger">৳{{ number_format($dueAmount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ $pendingInvoices }} invoice{{ $pendingInvoices > 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusClass }}">
                                            <i class="fas fa-{{ $dueAmount > 10000 ? 'exclamation-triangle' : 'clock' }} me-1"></i>
                                            {{ $dueAmount > 10000 ? 'High' : ($dueAmount > 5000 ? 'Medium' : 'Low') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-envelope me-1"></i>Remind
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                        <h5 class="text-muted">No Outstanding Dues</h5>
                                        <p class="text-muted">All customers are up to date with their payments.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Location & Quick Stats -->
        <div class="col-lg-4">
            <!-- Customer Locations -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marker-alt fa-lg me-2"></i>Customer Locations
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($customerLocations) && $customerLocations->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($customerLocations as $location)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-map-pin text-primary me-2"></i>
                                    {{ $location->city ?? 'Unknown' }}
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $location->customer_count }}</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-map fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Location data not available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Customer Stats -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar fa-lg me-2"></i>Customer Health Score
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <canvas id="customerHealthGauge" width="200" height="120"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <div class="h4 mb-0 text-success">85%</div>
                                <small class="text-muted">Health Score</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Retention Rate</span>
                            <span class="fw-bold text-success">92%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 92%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Satisfaction Score</span>
                            <span class="fw-bold text-primary">88%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 88%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Payment Compliance</span>
                            <span class="fw-bold text-info">78%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: 78%"></div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <i class="fas fa-user-plus fa-2x text-success mb-2"></i>
                                    <div class="h6 mb-0">{{ $newCustomers->sum('count') }}</div>
                                    <small class="text-muted">New This Period</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                    <div class="h6 mb-0">A-</div>
                                    <small class="text-muted">Overall Grade</small>
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
                        <i class="fas fa-analytics fa-lg me-2"></i>Customer Engagement Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-comments fa-2x text-primary mb-2"></i>
                                <div class="h5 mb-1">94%</div>
                                <small class="text-muted">Response Rate</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-clock fa-2x text-success mb-2"></i>
                                <div class="h5 mb-1">2.1h</div>
                                <small class="text-muted">Avg. Response Time</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-thumbs-up fa-2x text-warning mb-2"></i>
                                <div class="h5 mb-1">4.7/5</div>
                                <small class="text-muted">Satisfaction Score</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-redo fa-2x text-info mb-2"></i>
                                <div class="h5 mb-1">76%</div>
                                <small class="text-muted">Repeat Rate</small>
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
    // Customer Growth Chart
    const growthCtx = document.getElementById('customerGrowthChart');
    if (growthCtx) {
        const chartLabels = {!! json_encode($newCustomers->pluck('month')->map(function($month) {
                    return \Carbon\Carbon::createFromDate($month . '-01')->format('M Y');
                })->toArray()) !!};
        const chartData = {!! json_encode($newCustomers->pluck('count')->toArray()) !!};
        
        const growthChart = new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'New Customers',
                    data: chartData,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Customer Distribution Chart
    const distributionCtx = document.getElementById('customerDistributionChart');
    if (distributionCtx) {
        const activeCount = {{ $activeCustomers }};
        const inactiveCount = {{ $inactiveCustomers ?? ($totalCustomers - $activeCustomers) }};
        
        const distributionChart = new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active Customers', 'Inactive Customers'],
                datasets: [{
                    data: [activeCount, inactiveCount],
                    backgroundColor: ['#1cc88a', '#f6c23e'],
                    hoverBackgroundColor: ['#17a673', '#f4b619'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%',
            },
        });
    }

    // Customer Health Gauge Chart
    const healthCtx = document.getElementById('customerHealthGauge');
    if (healthCtx) {
        const healthChart = new Chart(healthCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [85, 15],
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

    // Initialize DataTable for Dues
    const duesTable = document.getElementById('duesTable');
    if (duesTable) {
        $('#duesTable').DataTable({
            pageLength: 10,
            order: [[3, 'desc']],
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
    document.getElementById('exportCustomerReport')?.addEventListener('click', function() {
        const btn = this;
        btn.innerHTML = '<span class="loading-spinner me-2"></span>Exporting...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-download me-2"></i>Export';
            btn.disabled = false;
            // Simulate download
            const link = document.createElement('a');
            link.href = '#';
            link.download = 'customer-analytics-report.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }, 2000);
    });

    // Add reminder functionality
    document.querySelectorAll('.btn-outline-primary').forEach(btn => {
        btn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check me-1"></i>Sent';
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 2000);
            }, 1500);
        });
    });
});
</script>
@endpush