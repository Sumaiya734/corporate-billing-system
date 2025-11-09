@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')


            <!-- Main Content -->
            
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 mb-0 text-dark">
                            <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard Overview
                        </h2>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                            <button class="btn btn-outline-success btn-sm">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card text-white bg-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Total Customers</h5>
                                            <h2 class="mb-0">{{ $totalCustomers ?? 0 }}</h2>
                                            <small>Active subscribers</small>
                                        </div>
                                        <div class="display-4">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card text-white bg-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Monthly Revenue</h5>
                                            <h2 class="mb-0">৳{{ number_format($monthlyRevenue ?? 0, 2) }}</h2>
                                            <small>Current month</small>
                                        </div>
                                        <div class="display-4">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card text-white bg-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Pending Bills</h5>
                                            <h2 class="mb-0">{{ $pendingBills ?? 0 }}</h2>
                                            <small>Awaiting payment</small>
                                        </div>
                                        <div class="display-4">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card text-white bg-info">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title">Active Packages</h5>
                                            <h2 class="mb-0">{{ $activePackages ?? 0 }}</h2>
                                            <small>Total packages</small>
                                        </div>
                                        <div class="display-4">
                                            <i class="fas fa-cube"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Stats -->
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="card stat-card bg-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Overdue Bills</h6>
                                            <h3 class="text-danger">{{ $overdueBills ?? 0 }}</h3>
                                        </div>
                                        <div class="text-danger">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card stat-card bg-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Paid Invoices</h6>
                                            <h3 class="text-success">{{ $paidInvoices ?? 0 }}</h3>
                                        </div>
                                        <div class="text-success">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card stat-card bg-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">New Customers</h6>
                                            <h3 class="text-primary">{{ $newCustomers ?? 0 }}</h3>
                                        </div>
                                        <div class="text-primary">
                                            <i class="fas fa-user-plus fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-3 col-md-6">
                                            <a href="#" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center py-3">
                                                <i class="fas fa-user-plus fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>Add Customer</strong>
                                                    <br>
                                                    <small>Register new customer</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="#" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center py-3">
                                                <i class="fas fa-file-invoice-dollar fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>Generate Bills</strong>
                                                    <br>
                                                    <small>Create monthly invoices</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="#" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center py-3">
                                                <i class="fas fa-chart-bar fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>View Reports</strong>
                                                    <br>
                                                    <small>Financial analytics</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <a href="#" class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center py-3">
                                                <i class="fas fa-bell fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>Send Alerts</strong>
                                                    <br>
                                                    <small>Payment reminders</small>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
        
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent Bootstrap click toggle (we're using hover)
    document.querySelectorAll('.sidebar .dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', e => e.preventDefault());
    });

    // Active link highlight
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
});
</script>

@endsection