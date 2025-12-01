<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>@yield('title', 'Dashboard') - Nanosoft Billing</title>
    
    <!-- Font Awesome: prefer local copy if present, else CDN -->
    @php $faLocal = public_path('vendor/fontawesome/css/all.min.css'); @endphp
    @if (file_exists($faLocal))
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    @else
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endif
    <!-- Ensure fonts load using absolute paths when serving locally -->
    <style>
        /* Override @font-face sources to absolute paths so browser requests match server files */
        @font-face { font-family: "Font Awesome 6 Brands"; font-style: normal; font-weight: 400; font-display: swap; src: url('/vendor/fontawesome/webfonts/fa-brands-400.woff2') format('woff2'), url('/vendor/fontawesome/webfonts/fa-brands-400.ttf') format('truetype'); }
        @font-face { font-family: "Font Awesome 6 Free"; font-style: normal; font-weight: 400; font-display: swap; src: url('/vendor/fontawesome/webfonts/fa-regular-400.woff2') format('woff2'), url('/vendor/fontawesome/webfonts/fa-regular-400.ttf') format('truetype'); }
        @font-face { font-family: "Font Awesome 6 Free"; font-style: normal; font-weight: 900; font-display: swap; src: url('/vendor/fontawesome/webfonts/fa-solid-900.woff2') format('woff2'), url('/vendor/fontawesome/webfonts/fa-solid-900.ttf') format('truetype'); }
        /* Back-compat names used in some CSS variants */
        @font-face { font-family: "Font Awesome 5 Free"; font-style: normal; font-weight: 900; font-display: swap; src: url('/vendor/fontawesome/webfonts/fa-solid-900.woff2') format('woff2'), url('/vendor/fontawesome/webfonts/fa-solid-900.ttf') format('truetype'); }
        @font-face { font-family: "Font Awesome 5 Free"; font-style: normal; font-weight: 400; font-display: swap; src: url('/vendor/fontawesome/webfonts/fa-regular-400.woff2') format('woff2'), url('/vendor/fontawesome/webfonts/fa-regular-400.ttf') format('truetype'); }
        @font-face { font-family: "FontAwesome"; font-style: normal; font-weight: 400; font-display: swap; src: url('/vendor/fontawesome/webfonts/fa-v4compatibility.woff2') format('woff2'), url('/vendor/fontawesome/webfonts/fa-v4compatibility.ttf') format('truetype'); }
    </style>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    @vite(['resources/sass/app.scss', 'resources/css/admin.css', 'resources/js/app.js'])
   <style>
    :root {
        --primary-color: #3498db;
        --secondary-color: #2c3e50;
        --accent-color: #2980b9;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --light-bg: #f8f9fa;
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 80px;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--light-bg);
        overflow-x: hidden;
        color: #2b2d42 !important; /* Ensure default text is readable */
    }
    
    /* FIX: Ensure all text in statistic cards is visible */
    .stat-card {
        color: #ffffff !important; /* Force white text on colored backgrounds */
    }
    
    .stat-card * {
        color: inherit !important; /* Force inherit for all child elements */
    }
    
    .stat-card .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
    }
    
    .stat-card .text-success {
        color: #ffffff !important;
    }
    
    /* Fix Font Awesome icon display */
    .fa, .fas, .far, .fal, .fab, .fa-solid, .fa-regular, .fa-light, .fa-brands {
        font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "FontAwesome", sans-serif !important;
        font-weight: 900 !important;
        font-style: normal !important;
        display: inline-block !important;
        line-height: 1 !important;
    }
    
    /* Ensure icons are properly sized and visible */
    .product-icon i,
    .icon-shape i {
        font-size: 1rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Stat card icons should be large (fa-2x) - 2rem */
    .stat-card i {
        font-size: 2rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Card header icons should be medium (fa-lg) - 1.25rem */
    .card-header i {
        font-size: 1.25rem !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }

    /* Page Header Styles */
    .page-header {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border-bottom: 2px solid #f0f0f0;
    }

    .page-header-content {
        flex: 1;
    }

    .page-header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: flex-start;
    }

    .page-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: rgba(52, 152, 219, 0.1);
        border-radius: 12px;
    }

    .page-title {
        color: #2b2d42;
        font-weight: 700;
        margin: 0;
    }

    .breadcrumb-dark {
        background: transparent;
        padding: 0;
        margin: 0;
    }

    .breadcrumb-dark .breadcrumb-item {
        color: #6c757d;
    }

    .breadcrumb-dark .breadcrumb-item.active {
        color: #2b2d42;
        font-weight: 600;
    }

    .breadcrumb-dark .breadcrumb-item a {
        color: #3498db;
        text-decoration: none;
    }

    .breadcrumb-dark .breadcrumb-item a:hover {
        color: #2980b9;
        text-decoration: underline;
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 16px;
        }

        .page-header-actions {
            justify-content: flex-start;
            width: 100%;
            margin-top: 16px;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
        }
    }
    
    .sidebar {
        background: #2c3e50;
        min-height: 100vh;
        padding: 0;
        transition: all 0.3s;
        border-right: 3px solid #2c3e50;
        position: relative;
        overflow: hidden;
    }

    .sidebar .nav-link {
        color: #ecf0f1;
        padding: 12px 20px;
        border-bottom: 1px solid #34495e;
        border-left: 3px solid transparent;
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
        border-left: 3px solid #3498db;
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

    /* Cards */
    .stat-card {
        border-radius: 15px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        background: white;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    }
    
    .stat-card .card-body {
        padding: 1.5rem;
    }
    
    /* Gradient backgrounds for statistic cards */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
        color: white !important;
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
        color: white !important;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%) !important;
        color: white !important;
    }

    /* Custom Button Styles */
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
    }
    
    /* Table Styles */
    .table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .table th {
        background: linear-gradient(135deg, var(--secondary-color), #34495e);
        color: white;
        border: none;
        padding: 15px;
        font-weight: 600;
    }
    
    .table td {
        color: #2b2d42;
        padding: 12px 15px;
        vertical-align: middle;
        border-color: #f1f3f4;
    }
    
    /* Progress Bars */
    .progress {
        border-radius: 10px;
        height: 20px;
        background: #f1f3f4;
    }
    
    .progress-bar {
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    /* Mobile Responsive */
    @media (max-width: 767.98px) {
        .sidebar {
            transform: translateX(-100%);
            width: 85%;
            max-width: 300px;
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0 !important;
            width: 100%;
        }
        
        .overlay.show {
            display: block;
            opacity: 1;
        }
    }
    
    @media (min-width: 768px) {
        .sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-collapsed .sidebar .nav-link span,
        .sidebar-collapsed .sidebar .dropdown-toggle::after {
            display: none;
        }
        
        .sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            padding: 15px;
            margin: 2px 10px;
        }
        
        .sidebar-collapsed .sidebar .nav-link i {
            margin-right: 0;
            font-size: 1.3rem;
        }
        
        .sidebar-collapsed .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .sidebar-collapsed .sidebar-brand h5 {
            display: none;
        }
    }
    
    /* Overlay for mobile */
    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
        backdrop-filter: blur(5px);
    }
    
    .overlay.show {
        display: block;
        opacity: 1;
    }
    
    /* Loading Animation */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Notification Badge */
    .notification-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: var(--danger-color);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-dark d-md-none" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
                <a class="navbar-brand" href="{{ route('home') }}">
                    <i class="fas fa-money-bill-wave"></i>
                        Nanosoft-Billing
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
    
     <!
    
    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            @include('admin.admin-sidebar')

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
               
                
               
                
                <!-- Main Content Area -->
                <div class="container-fluid">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Please fix the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <!-- Page Header -->
                    @if(View::hasSection('title') || View::hasSection('title-icon') || View::hasSection('header-actions'))
                    <div class="page-header mb-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="page-header-content">
                                @if(View::hasSection('breadcrumb'))
                                <nav aria-label="breadcrumb" class="mb-3">
                                    <ol class="breadcrumb breadcrumb-dark">
                                        @yield('breadcrumb')
                                    </ol>
                                </nav>
                                @endif
                                
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    @if(View::hasSection('title-icon'))
                                    <div class="page-icon">
                                        <i class="fas @yield('title-icon') fa-2x text-primary"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <h1 class="page-title h2 mb-0">
                                            @yield('title', 'Dashboard')
                                        </h1>
                                        @if(View::hasSection('subtitle'))
                                        <p class="text-muted mt-1 mb-0">
                                            @yield('subtitle')
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if(View::hasSection('header-actions'))
                            <div class="page-header-actions">
                                @yield('header-actions')
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <!-- Page Content -->
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Universal Back Button Handler -->
    <script src="{{ asset('js/back-button.js') }}"></script>
    
    <!-- Main Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCollapse = document.getElementById('sidebarCollapse');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const body = document.body;
        
        // Mobile sidebar functionality
        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
            
            // Close sidebar when clicking overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
            
            // Close sidebar when clicking a link on mobile
            const sidebarLinks = sidebar.querySelectorAll('.nav-link, .dropdown-item');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                });
            });
        }
        
        // Desktop sidebar collapse
        if (sidebarCollapse && sidebar) {
            sidebarCollapse.addEventListener('click', function() {
                body.classList.toggle('sidebar-collapsed');
                const icon = this.querySelector('i');
                if (body.classList.contains('sidebar-collapsed')) {
                    icon.className = 'fas fa-chevron-right';
                } else {
                    icon.className = 'fas fa-chevron-left';
                }
            });
        }
        
        // Auto-close alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Add loading state to buttons with loading class
        document.querySelectorAll('.btn-loading').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="loading-spinner me-2"></span>Loading...';
                this.disabled = true;
                
                // Reset after 3 seconds (for demo)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            });
        });
        
        // Close sidebar when window is resized to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768 && sidebar && overlay) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
        
        // Initialize DataTables if any table has the data-datatable attribute
        document.querySelectorAll('table[data-datatable="true"]').forEach(table => {
            $(table).DataTable({
                pageLength: 10,
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    paginate: {
                        previous: "<i class='fas fa-chevron-left'></i>",
                        next: "<i class='fas fa-chevron-right'></i>"
                    }
                }
            });
        });
    });
    </script>

    @stack('styles')
    @stack('scripts')
    @yield('scripts')
</body>
</html>