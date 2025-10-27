<!-- resources/views/admin/admin-sidebar.blade.php -->

<!-- Sidebar -->
<div class="col-md-3 col-lg-2 sidebar collapse d-md-block" id="sidebar">
    <div class="sidebar-brand">
        <h5 class="text-white mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h5>
    </div>
    
    <nav class="nav flex-column">
        <!-- Dashboard -->
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>

        <!-- Customer Management -->
        <div class="dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                <i class="fas fa-users me-2"></i>Manage Customers
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item {{ request()->routeIs('admin.customers.create') ? 'active' : '' }}" href="{{ route('admin.customers.create') }}">
                    <i class="fas fa-user-plus me-2"></i>Add New Customer
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
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