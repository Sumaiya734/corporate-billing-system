<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - NetBill BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* ---------------------------
           Soft Blue / White Theme A
           --------------------------- */
        :root{
            --primary: #3A7BD5;
            --primary-700: #2F63B8;
            --secondary: #2C3E50;
            --muted: #6b7280;
            --bg:rgb(238, 238, 240);
            --card-radius: 14px;
            --glass: rgba(255,255,255,0.85);
        }

        html,body{height:100%;}
        body{
            margin:0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: #1f2937;
            -webkit-font-smoothing:antialiased;
        }

        /* NAVBAR */
        .navbar-brand { font-weight:700; color:var(--primary); display:flex; gap:.6rem; align-items:center; }
        .navbar { background: #ffffff; box-shadow: 0 6px 18px rgba(15,23,42,0.06); padding: .6rem 1rem; }

        /* TOP AREA */
        .page-header {
            background: #fff;
            border-radius: var(--card-radius);
            padding: 20px;
            box-shadow: 0 6px 18px rgba(12, 15, 29, 0.04);
            margin-bottom: 1.25rem;
            border-left: 4px solid rgba(58, 123, 213, 0.08);
        }

        /* SIDEBAR */
        .sidebar {
            background: linear-gradient(180deg,rgb(57, 74, 99) 0%, #263a4f 100%);
            color: #ecf2ff;
            min-height: 100vh;
            padding: 0;
            transition: transform .28s ease;
            position: relative;
        }
        @media (min-width: 992px) {
            /* Keep a consistent fixed width on large screens but use col-lg-auto so
               the grid doesn't allocate a percentage column AND a fixed width. */
            .sidebar { width: 250px; }
            .main-content { padding: 24px; }
        }
        .sidebar .sidebar-brand { padding: 20px; background: rgba(0,0,0,0.06); display:flex; align-items:center; gap:12px; }
        .sidebar .sidebar-brand img { height:36px; width:auto; border-radius:8px; }
        .sidebar .nav-link{
            color: rgba(236,242,255,0.92);
            padding: 12px 18px;
            border-left: 4px solid transparent;
            transition: all .22s ease;
            display:flex;
            gap:.8rem;
            align-items:center;
        }
        .sidebar .nav-link i { color: rgba(255,255,255,0.9); min-width:22px; text-align:center; font-size:1.05rem; }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.04);
            color: #fff;
            padding-left: 22px;
            border-left: 4px solid var(--primary);
            text-decoration:none;
        }
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(58,123,213,0.12), rgba(58,123,213,0.06));
            color: #fff;
            border-left: 4px solid var(--primary);
        }
        .sidebar .dropdown-menu { background: transparent; border: none; box-shadow:none; padding:0; }
        .sidebar .dropdown-item { color: rgba(236,242,255,0.95); padding-left: 46px; border-radius: 0; }
        .sidebar .dropdown-item:hover { background: rgba(255,255,255,0.03); color: #fff; }

        /* MAIN CONTENT */
        .main-content { padding: 24px; margin-left: 0; transition: margin-left .28s ease; }

        /* CARDS & STAT */
        .stat-card {
            border-radius: 12px;
            overflow: hidden;
            border: none;
            transition: transform .28s ease, box-shadow .28s ease;
            background: linear-gradient(180deg, #fff, #fbfdff);
            box-shadow: 0 8px 30px rgba(40,45,62,0.04);
        }
        .stat-card:hover {
            background: linear-gradient(180deg, #fff, #fbfdff);
            transform: translateY(-6px);
            box-shadow: 0 18px 40px rgba(26,32,44,0.07);
        }
        .stat-card .card-body { padding: 20px; }
        .stat-title { font-size: .90rem; color: var(--muted); margin-bottom: .4rem; font-weight:600; }
        .stat-value { font-size: 28px; font-weight:700; color: var(--secondary); }

        /* Gradient badges (for stat cards) */
        .bg-gradient-primary { background: linear-gradient(135deg, #6EA8FE 0%, #3A7BD5 100%); color: #fff; }
        .bg-gradient-success { background: linear-gradient(135deg, #86EFAC 0%, #34D399 100%); color: #fff; }
        .bg-gradient-warning { background: linear-gradient(135deg, #FFD27A 0%, #FB9A64 100%); color: #fff; }
        .bg-gradient-info    { background: linear-gradient(135deg, #A5F3FC 0%, #67E8F9 100%); color:#fff; }

        .stat-icon {
            font-size: 36px;
            opacity: .95;
        }

        /* small animations */
        .fade-in { animation: fadeIn .6s ease both; }
        @keyframes fadeIn { from { opacity:0; transform: translateY(6px);} to { opacity:1; transform:none; } }

        /* TABLE & DATATABLES */
        .table thead th { background: #f1f5f9; border-bottom: none; font-weight:700; color: #2b2d42; }
        .table tbody td { vertical-align: middle; border-top: 1px solid #eff3f6; }

        /* overlay (mobile) */
        .overlay {
            display:none;
            position:fixed;
            inset:0;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            z-index: 999;
            transition: opacity .2s ease;
        }
        .overlay.show { display:block; opacity:1; }

        /* small helpers */
        .btn-ghost { background: transparent; border: 1px solid rgba(58,123,213,0.08); color:var(--primary); border-radius:10px; padding:8px 12px; }
        .notification-badge { position:absolute; top:10px; right:12px; background: #e74c3c; color:#fff; border-radius:50%; width:18px; height:18px; font-size:.72rem; display:flex; align-items:center; justify-content:center; }

        /* responsive adjustments */
        @media (max-width: 991.98px) {
                .sidebar { position: fixed; left: -100%; width: 80%; z-index: 1100; }
                .sidebar.show { left:0; }
                .main-content { margin-left: 0 !important; }
                .overlay { display:block; opacity:0; }
            }

            /* Prevent admin layout stacking on large screens while resources load */
            @media (min-width: 992px) {
                .admin-layout-row { flex-wrap: nowrap; }
                .main-content { flex: 1 1 auto; min-width: 0; margin-top: 56px; }
            }
    </style>

</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light d-lg-none" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <a class="navbar-brand ms-2" href="{{ route('welcome') }}">
                    <i class="fas fa-wifi me-2"></i>NetBill BD
                </a>
            </div>

            <div class="d-flex align-items-center ms-auto gap-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-secondary d-none d-md-block">
                        <div style="font-weight:700">{{ $customer->name }}</div>
                        <small class="text-muted">Customer</small>
                    </div>
                    
                    <form method="POST" action="{{ route('customer.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Logout">
                            <i class="fas fa-right-from-bracket"></i>
                            <span class="d-none d-md-inline ms-1">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- overlay for mobile sidebar -->
    <div class="overlay" id="overlay"></div>

    <div class="container-fluid">
        <div class="row admin-layout-row">
            <!-- Sidebar -->
            <div id="sidebar" class="col-12 col-lg-auto sidebar p-0">
                <div class="sidebar-brand">
                    <h6 class="text-white mb-1"><i class="fas fa-user me-2"></i>My Account</h6>
                    <small class="text-light opacity-75">ID: {{ $customer->customer_id }}</small>
                </div>
                
                <nav class="nav flex-column p-2">
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
                                <i class="fas fa-rocket me-2"></i>Current products
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-sync me-2"></i>Change product
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-plus me-2"></i>Add Special products
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
            <main class="col main-content">
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
                                            <h6 class="card-title text-muted">Active products</h6>
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
                                                    <small>Manage products</small>
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('overlay');
            const body = document.body;

            // Mobile sidebar toggle
            if (sidebarToggle && sidebar && overlay) {
                sidebarToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });

                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });

                // close sidebar on nav link click (mobile)
                sidebar.querySelectorAll('.nav-link, .dropdown-item').forEach(link => {
                    link.addEventListener('click', function () {
                        if (window.innerWidth < 992) {
                            sidebar.classList.remove('show');
                            overlay.classList.remove('show');
                        }
                    });
                });
            }
            
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

            // Auto-close alerts (except those with persistent-alert class)
            document.querySelectorAll('.alert:not(.persistent-alert)').forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 6000);
            });
            
            // Tooltips init
            const tList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tList.map(function (t) { return new bootstrap.Tooltip(t); });
        });
    </script>

</body>
</html>