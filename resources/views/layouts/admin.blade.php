<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - NetBill BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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
            <!-- Include Sidebar -->
            @include('admin.admin-sidebar')

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                @yield('content')
            </div>
        </div>
    </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Add this script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hover dropdown functionality
        const dropdowns = document.querySelectorAll('.sidebar .dropdown');
        
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('mouseenter', function() {
                this.querySelector('.dropdown-menu').style.display = 'block';
            });
            
            dropdown.addEventListener('mouseleave', function() {
                this.querySelector('.dropdown-menu').style.display = 'none';
            });
        });

        // Active link highlighting
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    });
    </script>
</body>

</html>