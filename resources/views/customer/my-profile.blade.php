<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Corporate Billing</title>
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
            padding-top: 56px; /* Height of fixed navbar */
        }

        /* NAVBAR */
        .navbar-brand { font-weight:700; color:var(--primary); display:flex; gap:.6rem; align-items:center; }
        .navbar { 
            background: #ffffff; 
            box-shadow: 0 6px 18px rgba(15,23,42,0.06); 
            padding: .6rem 1rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
        }

        /* TOP AREA */
        .page-header {
            background: #fff;
            border-radius: var(--card-radius);
            padding: 8px;
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
            position: fixed; /* Fixed position for sidebar */
            top: 56px; /* Height of navbar */
            left: 0;
            bottom: 0;
            width: 250px; /* Fixed width */
            overflow-y: auto; /* Enable scrolling */
            z-index: 1000;
        }
        
        /* Adjust main content to accommodate fixed sidebar */
        .main-content {
            margin-left: 250px; /* Same as sidebar width */
            padding: 24px;
            transition: margin-left .28s ease;
        }
        
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -100%;
                width: 80%;
                z-index: 1100;
                top: 56px;
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0 !important;
            }
        }
        
        .sidebar .sidebar-brand { 
            padding: 20px; 
            background: rgba(0,0,0,0.06); 
            display:flex; 
            align-items:center; 
            gap:12px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
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
                .overlay { display:block; opacity:0; }
            }

            /* Prevent admin layout stacking on large screens while resources load */
            @media (min-width: 992px) {
                .admin-layout-row { flex-wrap: nowrap; }
            }
            
        /* Form Styles */
        .form-section {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .form-section h4 {
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: var(--secondary);
        }
        
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .product-card .product-name {
            font-weight: 600;
            color: var(--secondary);
        }
        
        .product-card .product-type {
            font-size: 0.85rem;
            color: var(--muted);
        }
        
        .product-card .product-price {
            font-weight: 700;
            color: var(--primary);
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
                    <i class="fas fa-wifi me-2"></i>Corporate Billing
                </a>
            </div>

            <div class="d-flex align-items-center ms-auto gap-3">
                <div class="d-flex align-items-center">
                    <div>
                        {{ now()->format('g:i A, F j, Y') }}
                    </div> <br>
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
            <div id="sidebar" class="sidebar p-0">
                <div class="sidebar-brand">
                    <h6 class="text-white mb-1"><i class="fas fa-user me-2"></i>My Account</h6>
                    <small class="text-light opacity-75">ID: {{ $customer->customer_id }}</small>
                </div>
                
                <nav class="nav flex-column p-2">
                    <!-- Dashboard -->
                    <a class="nav-link" href="{{ route('customer.dashboard') }}">
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
                        <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item active" href="#">
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
                        <i class="fas fa-phone me-2"></i>Contact
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <main class="col main-content">
                <div class="p-4">
                    <!-- Page Header -->
                    <div class="page-header mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">
                                <i class="fas fa-user-circle me-2"></i>My Profile
                            </h3>
                        </div>
                    </div>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Personal Information Section -->
                        <div class="col-lg-6">
                            <div class="form-section">
                                <h4><i class="fas fa-user me-2"></i>Personal Information</h4>
                                <form method="POST" action="#">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ $customer->name }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="{{ $customer->email }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="{{ $customer->phone }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required>{{ $customer->address }}</textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="connection_address" class="form-label">Connection Address</label>
                                        <textarea class="form-control" id="connection_address" name="connection_address" rows="2">{{ $customer->connection_address }}</textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Changes
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Change Password Section -->
                            <div class="form-section">
                                <h4><i class="fas fa-lock me-2"></i>Change Password</h4>
                                <form method="POST" action="#">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <div class="form-text">Password must be at least 8 characters long.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-1"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Assigned Products Section -->
                        <div class="col-lg-6">
                            <div class="form-section">
                                <h4><i class="fas fa-box me-2"></i>Assigned Services</h4>
                                
                                @if($customer->customerproducts->count() > 0)
                                    @foreach($customer->customerproducts as $customerProduct)
                                        @if($customerProduct->product)
                                            <div class="product-card">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <div class="product-name">{{ $customerProduct->product->name }}</div>
                                                        <div class="product-type">
                                                            <span class="badge bg-{{ $customerProduct->product->isRegular() ? 'primary' : 'success' }}">
                                                                {{ ucfirst($customerProduct->product->product_type) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="product-price">৳{{ number_format($customerProduct->product->monthly_price, 2) }}</div>
                                                        <div class="small text-muted">per month</div>
                                                    </div>
                                                </div>
                                                
                                                <hr class="my-2">
                                                
                                                <div class="row small">
                                                    <div class="col-6">
                                                        <strong>Assigned Date:</strong><br>
                                                        {{ $customerProduct->assign_date ? \Carbon\Carbon::parse($customerProduct->assign_date)->format('M d, Y') : 'N/A' }}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Status:</strong><br>
                                                        {!! $customerProduct->status_badge !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No services assigned yet.</p>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Account Statistics -->
                            <div class="form-section">
                                <h4><i class="fas fa-chart-bar me-2"></i>Account Statistics</h4>
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="stat-card">
                                            <div class="card-body">
                                                <div class="stat-icon text-primary mb-2">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <div class="stat-value">{{ $customer->customerproducts->count() }}</div>
                                                <div class="stat-title">Active Services</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card">
                                            <div class="card-body">
                                                <div class="stat-icon text-success mb-2">
                                                    <i class="fas fa-file-invoice"></i>
                                                </div>
                                                <div class="stat-value">{{ $customer->invoices->count() }}</div>
                                                <div class="stat-title">Total Invoices</div>
                                            </div>
                                        </div>
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
            
            // Handle window resize to adjust sidebar visibility
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });
            
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