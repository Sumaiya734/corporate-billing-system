<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - Nanosoft Billing</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <!-- FONT AWESOME (single canonical CDN) -->
    <!-- Use a single version only to avoid conflicts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Chart.js (for analytics) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Vite / App assets (keep if your project uses vite) -->
    @if (app()->environment('local') || app()->environment('development'))
        {{-- during development --}}
        @vite(['resources/sass/app.scss', 'resources/css/admin.css', 'resources/js/app.js'])
    @else
        {{-- in production you might compile assets differently --}}
        @vite(['resources/sass/app.scss', 'resources/css/admin.css', 'resources/js/app.js'])
    @endif

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
        /* Use Bootstrap grid for layout spacing; remove fixed margin-left which caused double offset
           when the sidebar is rendered as a grid column. Keep only padding here so content aligns
           with the sidebar column naturally. */
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

    @stack('styles')
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light d-lg-none" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <a class="navbar-brand ms-2" href="{{ route('home') }}">
                    <img src="{{ asset('assets/nanosoft logo.png') }}" alt="Nanosoft" style="height:36px; width:auto; margin-right:8px;">
                    
                </a>
            </div>

            <div class="d-flex align-items-center ms-auto gap-3">
                <div class="d-none d-md-flex align-items-center text-muted small">
                    <!-- <i class="fa-regular fa-clock me-2"></i> -->
                     {{ now()->format('g:i A, F j, Y') }}
                </div>

                <div class="d-flex align-items-center">
                    <div class="me-3 text-secondary d-none d-md-block">
                        <div style="font-weight:700">{{ Auth::user()->name }}</div>
                        <small class="text-muted">Administrator</small>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
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
                {{-- Navigation (you can keep your existing admin-sidebar include if you prefer) --}}
                @if (View::exists('admin.admin-sidebar'))
                    @include('admin.admin-sidebar')
                @else
                    <nav class="nav flex-column p-2">
                        <a class="nav-link active" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                        <a class="nav-link" href="{{ route('admin.customers.index') ?? '#' }}"><i class="fa-solid fa-users"></i> Customers</a>
                        <a class="nav-link" href="{{ route('admin.products.index') ?? '#' }}"><i class="fa-solid fa-boxes-stacked"></i> Products</a>
                        <a class="nav-link" href="{{ route('admin.billing.index') ?? '#' }}"><i class="fa-solid fa-file-invoice-dollar"></i> Billing</a>
                        <a class="nav-link" href="#"><i class="fa-solid fa-chart-simple"></i> Reports</a>
                        <a class="nav-link" href="#"><i class="fa-solid fa-gear"></i> Settings</a>
                    </nav>
                @endif
            </div>

            <!-- Main Content -->
            <main class="col main-content">
                    {{-- flash messages, errors --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i> {!! session('success') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> {!! session('error') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    

                    {{-- yield main content from child view --}}
                    @yield('content')
            </main>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>

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

            // DataTables auto init (tables with data-datatable="true")
            document.querySelectorAll('table[data-datatable="true"]').forEach(function (tableEl) {
                $(tableEl).DataTable({
                    pageLength: 10,
                    responsive: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search...",
                        paginate: {
                            previous: "<i class='fa-solid fa-chevron-left'></i>",
                            next: "<i class='fa-solid fa-chevron-right'></i>"
                        }
                    }
                });
            });
        });
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
