<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NetBill BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        
       .sidebar {
    background: #2c3e50;
    min-height: 100vh;
    padding: 0;
    transition: all 0.3s;
    border-right: 3px solid #2c3e50; /* Containing border */
    position: relative;
    overflow: hidden; /* Prevent content from overflowing */
}

.sidebar .nav-link {
    color: #ecf0f1;
    padding: 12px 20px;
    border-bottom: 1px solid #34495e;
    border-left: 3px solid transparent; /* Consistent left border */
    border-right: none;
    margin: 0;
    transition: all 0.3s;
    display: block;
    width: 100%;
}

.sidebar .nav-link:hover {
    background: #34495e;
    color: #3498db;
    padding-left: 25px;
    border-left: 3px solid #3498db; /* Hover border */
}

.sidebar .nav-link.active {
    background: #3498db;
    color: white;
    border-left: 4px solid #2980b9;
    border-right: none;
}

.sidebar .dropdown-menu {
    background: #34495e;
    border: none;
    border-radius: 0;
    margin: 0;
    padding: 0;
    width: 100%;
    box-shadow: none;
}

.sidebar .dropdown-item {
    color: #ecf0f1;
    padding: 10px 20px 10px 40px;
    border-bottom: 1px solid #2c3e50;
    margin: 0;
    width: 100%;
}

.sidebar .dropdown-item:hover {
    background: #3498db;
    color: white;
    border-bottom: 1px solid #3498db;
}

.sidebar .dropdown-item:last-child {
    border-bottom: none;
}

.sidebar-brand {
    padding: 20px;
    background: #34495e;
    border-bottom: 1px solid #2c3e50;
    margin: 0;
}

.main-content {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 0;
}

.stat-card {
    border-radius: 10px;
    transition: transform 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.stat-card:hover {
    transform: translateY(-5px);
}

.navbar-brand {
    font-weight: bold;
}

/* Fix for dropdown toggle alignment */
.sidebar .dropdown-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar .dropdown-toggle::after {
    margin-left: auto;
}

/* Ensure proper mobile behavior */
@media (max-width: 767.98px) {
    .sidebar {
        border-right: none;
        border-bottom: 3px solid #2c3e50;
    }
    
    .sidebar .nav-link {
        border-left: none;
        border-right: none;
    }
    
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        border-left: none;
        border-right: none;
    }
}

/* Fix for nested dropdown items */
.sidebar .dropdown-menu .dropdown-item {
    border-left: 2px solid transparent;
}

.sidebar .dropdown-menu .dropdown-item:hover {
    border-left: 2px solid #3498db;
    padding-left: 38px; /* Compensate for border */
}

/* Remove any external borders from the container */
.container-fluid {
    padding-left: 0;
    padding-right: 0;
}

.row {
    margin-left: 0;
    margin-right: 0;
}

/* Ensure sidebar column doesn't overflow */
.col-md-3.col-lg-2.sidebar {
    padding-left: 0;
    padding-right: 0;
}
/* Smooth hover dropdown animation */
.sidebar .dropdown-menu {
    display: block;
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-5px);
    transition: all 0.3s ease;
    background: #34495e;
    border-left: 3px solid transparent;
}

/* Show when hovered */
.sidebar .dropdown:hover > .dropdown-menu {
    max-height: 500px; /* enough to fit menu items */
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    border-left: 3px solid #3498db;
}

/* Add subtle shadow */
.sidebar .dropdown-menu.show, 
.sidebar .dropdown:hover > .dropdown-menu {
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

/* Dropdown items */
.sidebar .dropdown-item {
    color: #ecf0f1;
    padding: 10px 20px 10px 40px;
    border-bottom: 1px solid #2c3e50;
    transition: background 0.3s, padding-left 0.3s;
}

.sidebar .dropdown-item:hover {
    background: #3498db;
    color: #fff;
    padding-left: 45px;
}

/* Rotate arrow icon when open */
.sidebar .dropdown-toggle i.fa-chevron-right {
    transition: transform 0.3s ease;
}

.sidebar .dropdown:hover .dropdown-toggle i.fa-chevron-right {
    transform: rotate(90deg);
}

        
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-dark d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-wifi me-2"></i>NetBill BD Admin
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="fas fa-user-circle me-1"></i>Welcome, {{ Auth::user()->name }}
                </span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar collapse d-md-block" id="sidebar">
                <div class="sidebar-brand">
                    <h5 class="text-white mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h5>
                </div>
                
                <nav class="nav flex-column">
                    <!-- Dashboard -->
                    <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>

                    <!-- Customer Management -->
                    
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-users me-2"></i>Manage Customers
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('admin.customers.create') }}">
                                <i class="fas fa-user-plus me-2"></i>Add New Customer
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.customers.index') }}">
                                <i class="fas fa-list me-2"></i>All Customers
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-chart-bar me-2"></i>Customer Reports
                            </a>
                        </div>
                    </div>

                    <!-- Billing & Invoices -->
                    <div class="dropdown">
                        <a class="nav-link" href="{{ route('admin.billing.invoices') }}">
    <i class="fas fa-file-invoice-dollar me-2"></i>Billing & Invoices
</a>

                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('admin.billing.monthly-bills') }}">
                                <i class="fas fa-cogs me-2"></i>Monthly Bills
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.billing.all-invoices') }}">
                                <i class="fas fa-file-invoice me-2"></i>All Invoices
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-clock me-2"></i>Pending Payments
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-exclamation-triangle me-2"></i>Overdue Bills
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-check-circle me-2"></i>Paid Invoices
                            </a>
                        </div>
                    </div>

                    <!-- Package Management -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-cube me-2"></i>Package Management
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-bullseye me-2"></i>Regular Packages
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-star me-2"></i>Special Packages
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-plus me-2"></i>Create New Package
                            </a>
                        </div>
                    </div>

                    <!-- Reports & Analytics -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-money-bill-wave me-2"></i>Revenue Reports
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-chart-line me-2"></i>Financial Analytics
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-users me-2"></i>Customer Statistics
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-clipboard-list me-2"></i>Collection Reports
                            </a>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-envelope me-2"></i>Notifications
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-bell me-2"></i>Send Reminders
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-bullhorn me-2"></i>Announcements
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-envelope me-2"></i>Email Templates
                            </a>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user-cog me-2"></i>Admin Settings
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-credit-card me-2"></i>Payment Settings
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-shield-alt me-2"></i>System Settings
                            </a>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
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

   
</body>
</html>