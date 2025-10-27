<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - NetBill BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    .sidebar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 0;
        transition: all 0.3s;
    }
    .sidebar .nav-link {
        color: #ffffff;
        padding: 12px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        transition: all 0.3s;
    }
    .sidebar .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: #ffffff;
        padding-left: 25px;
    }
    .sidebar .nav-link.active {
        background: rgba(255,255,255,0.2);
        color: white;
        border-left: 4px solid #ffffff;
    }

    /* --- Dropdown Animation --- */
    .sidebar .dropdown-menu {
        display: block;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-5px);
        transition: all 0.3s ease;
        background: rgba(86, 101, 115, 0.95);
        border: none;
        border-radius: 0;
        backdrop-filter: blur(10px);
    }
    .sidebar .dropdown:hover > .dropdown-menu {
        max-height: 500px;
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        border-left: 3px solid #ffffff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .sidebar .dropdown-item {
        color: #ffffff;
        padding: 10px 20px 10px 40px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        transition: all 0.3s ease;
    }
    .sidebar .dropdown-item:hover {
        background: rgba(255,255,255,0.2);
        color: white;
        padding-left: 45px;
    }

    /* Dropdown arrow rotation */
    .sidebar .dropdown-toggle i.fa-chevron-right {
        transition: transform 0.3s ease;
    }
    .sidebar .dropdown:hover .dropdown-toggle i.fa-chevron-right {
        transform: rotate(90deg);
    }

    .sidebar-brand {
        padding: 25px 20px;
        background: rgba(0,0,0,0.1);
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .main-content {
        background: #f8f9fa;
        min-height: 100vh;
    }

    .stat-card {
        border-radius: 15px;
        transition: transform 0.3s ease;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-left: 5px solid;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }

    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
    }

    .quick-action-btn {
        transition: all 0.3s ease;
        border-radius: 10px;
        padding: 15px;
    }
    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
</style>

</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-light d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand fw-bold" href="{{ route('customer.dashboard') }}">
                <i class="fas fa-wifi me-2"></i>NetBill BD
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="fas fa-user-circle me-1"></i>Welcome, {{ $customer->name }}
                </span>
                <form method="POST" action="{{ route('customer.logout') }}">
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
                    <h6 class="text-white mb-1"><i class="fas fa-user me-2"></i>My Account</h6>
                    <small class="text-light opacity-75">ID: {{ $customer->customer_id }}</small>
                </div>
                
                <nav class="nav flex-column">
                    <!-- Dashboard -->
                    <a class="nav-link active" href="{{ route('customer.dashboard') }}">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>

                    <!-- My Bills & Payments -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-file-invoice me-2"></i>My Bills & Payments
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-credit-card me-2"></i>Current Bill
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-history me-2"></i>Payment History
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-archive me-2"></i>Invoice Archive
                            </a>
                        </div>
                    </div>

                    <!-- My Services -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-wifi me-2"></i>My Services
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-rocket me-2"></i>Current Packages
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-sync me-2"></i>Change Package
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-plus me-2"></i>Add Special Packages
                            </a>
                        </div>
                    </div>

                    <!-- My Profile -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-edit me-2"></i>Personal Information
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-bell me-2"></i>Notification Settings
                            </a>
                        </div>
                    </div>

                    <!-- Support Center -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-life-ring me-2"></i>Support Center
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-ticket-alt me-2"></i>Raise a Ticket
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-list me-2"></i>My Tickets
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-question-circle me-2"></i>Help & FAQ
                            </a>
                        </div>
                    </div>

                    <!-- Contact Us -->
                    <a class="nav-link" href="#">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <!-- Welcome Card -->
                    <div class="card welcome-card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="card-title mb-2">
                                        <i class="fas fa-hand-wave me-2"></i>Welcome back, {{ $customer->name }}!
                                    </h3>
                                    <p class="card-text mb-0 opacity-90">
                                        Here's your account overview and quick access to your services.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="display-4 opacity-75">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-left-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Current Bill</h6>
                                            <h3 class="text-primary">৳0.00</h3>
                                            <small class="text-muted">Due in 15 days</small>
                                        </div>
                                        <div class="text-primary">
                                            <i class="fas fa-file-invoice fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-left-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Active Packages</h6>
                                            <h3 class="text-success">1</h3>
                                            <small class="text-muted">Services active</small>
                                        </div>
                                        <div class="text-success">
                                            <i class="fas fa-wifi fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-left-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Support Tickets</h6>
                                            <h3 class="text-warning">0</h3>
                                            <small class="text-muted">Open requests</small>
                                        </div>
                                        <div class="text-warning">
                                            <i class="fas fa-ticket-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-left-info">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Member Since</h6>
                                            <h3 class="text-info">{{ $customer->created_at->format('M Y') }}</h3>
                                            <small class="text-muted">Loyal customer</small>
                                        </div>
                                        <div class="text-info">
                                            <i class="fas fa-calendar-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Account Information -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-circle me-2 text-primary"></i>Account Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-id-card me-2 text-muted"></i>Customer ID</strong>
                                            <p class="mb-0">{{ $customer->customer_id }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-user me-2 text-muted"></i>Full Name</strong>
                                            <p class="mb-0">{{ $customer->name }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-envelope me-2 text-muted"></i>Email Address</strong>
                                            <p class="mb-0">{{ $customer->email }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-phone me-2 text-muted"></i>Phone Number</strong>
                                            <p class="mb-0">{{ $customer->phone }}</p>
                                        </div>
                                        <div class="col-12">
                                            <strong><i class="fas fa-map-marker-alt me-2 text-muted"></i>Address</strong>
                                            <p class="mb-0">{{ $customer->address }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-primary quick-action-btn w-100 d-flex align-items-center">
                                                <i class="fas fa-credit-card fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>Pay Bill</strong>
                                                    <br>
                                                    <small>Current invoice</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-success quick-action-btn w-100 d-flex align-items-center">
                                                <i class="fas fa-history fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>Payment History</strong>
                                                    <br>
                                                    <small>View past bills</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-info quick-action-btn w-100 d-flex align-items-center">
                                                <i class="fas fa-wifi fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>My Services</strong>
                                                    <br>
                                                    <small>Manage packages</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-warning quick-action-btn w-100 d-flex align-items-center">
                                                <i class="fas fa-user-edit fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>Update Profile</strong>
                                                    <br>
                                                    <small>Edit information</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-danger quick-action-btn w-100 d-flex align-items-center">
                                                <i class="fas fa-ticket-alt fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>Get Support</strong>
                                                    <br>
                                                    <small>Raise a ticket</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-secondary quick-action-btn w-100 d-flex align-items-center">
                                                <i class="fas fa-download fa-2x me-3"></i>
                                                <div class="text-start">
                                                    <strong>Download Invoice</strong>
                                                    <br>
                                                    <small>Latest bill PDF</small>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2 text-info"></i>Recent Activity
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                        <p>No recent activity to display</p>
                                        <small>Your recent bills, payments, and service changes will appear here.</small>
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
    // Disable click dropdown behavior (use hover instead)
    document.querySelectorAll('.sidebar .dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', e => e.preventDefault());
    });

    // Add active state for current page
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

    // Auto-collapse sidebar on small screens
    if (window.innerWidth < 768) {
        document.getElementById('sidebar').classList.remove('show');
    }
});
</script>

</body>
</html>