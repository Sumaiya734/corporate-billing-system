<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>@yield('title') - Nanosft-Billing</title>
        <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/css/admin.css', 'resources/js/app.js'])
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
        position: fixed;
        top: 56px;
        left: 0;
        bottom: 0;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        width: 80%;
        max-width: 300px;
        border-right: none;
        border-bottom: none;
        min-height: auto;
        overflow-y: auto;
    }
    
    .sidebar.show {
        transform: translateX(0);
        box-shadow: 2px 0 10px rgba(0,0,0,0.3);
    }
    
    .sidebar .nav-link {
        border-left: none;
        border-right: none;
    }
    
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        border-left: none;
        border-right: none;
        padding-left: 20px;
    }
    
    .main-content {
        width: 100%;
        margin-left: 0 !important;
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

/* Overlay for mobile sidebar */
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
}

.overlay.show {
    display: block;
    opacity: 1;
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
    
    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            @include('admin.admin-sidebar')

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                @yield('content')
            </div>
        </div>
    </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Universal Back Button Handler -->
    <script src="{{ asset('js/back-button.js') }}"></script>
    
    <!-- Add this script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
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
        
        // Close sidebar when window is resized to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768 && sidebar && overlay) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    });
    </script>

    @yield('scripts')
</body>

</html>