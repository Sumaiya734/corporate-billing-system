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
            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#customersMenu">
                <i class="fas fa-users me-2"></i>Manage Customers
            </a>
            <div class="collapse submenu" id="customersMenu">
                <a class="dropdown-item {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                    <i class="fas fa-list me-2"></i>All Customers
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.customers.create') ? 'active' : '' }}" href="{{ route('admin.customers.create') }}">
                    <i class="fas fa-user-plus me-2"></i>Add New Customer
                </a>
            </div>
        </div>

        <!-- Billing & Invoices -->
        <div class="dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#billingMenu">
                <i class="fas fa-file-invoice-dollar me-2"></i>Billings
            </a>
            <div class="collapse submenu" id="billingMenu">
                <a class="dropdown-item {{ request()->routeIs('admin.billing.billing-invoices') ? 'active' : '' }}" href="{{ route('admin.billing.billing-invoices') }}">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Billing & Invoices
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.billing.monthly-bills') ? 'active' : '' }}" href="{{ route('admin.billing.monthly-bills', ['month' => date('Y-m')]) }}">
                    <i class="fas fa-calendar me-2"></i>Monthly Bills
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.billing.all-invoices') ? 'active' : '' }}" href="{{ route('admin.billing.all-invoices') }}">
                    <i class="fas fa-file-invoice me-2"></i>All Invoices
                </a>
            </div>
        </div>

        <!-- Package Management -->
        <div class="dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#packageMenu">
                <i class="fas fa-cube me-2"></i>Product Management
            </a>
            <div class="collapse submenu" id="packageMenu">
                <a class="dropdown-item {{ request()->routeIs('admin.packages.index') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}">
                    <i class="fas fa-list me-2"></i>All Products
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-plus me-2"></i>Create New Product
                </a>
            </div>
        </div>

        <!-- Customer Packages -->
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.customer-to-packages.*') ? 'active' : '' }}" href="{{ route('admin.customer-to-packages.index') }}">
                <i class="fas fa-box me-2"></i>Customer to Products
            </a>
        </div>

        <!-- Reports & Analytics -->
        <div class="dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#reportsMenu">
                <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
            </a>
            <div class="collapse submenu" id="reportsMenu">
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
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#notificationsMenu">
                <i class="fas fa-envelope me-2"></i>Notifications
            </a>
            <div class="collapse submenu" id="notificationsMenu">
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
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#settingsMenu">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
            <div class="collapse submenu" id="settingsMenu">
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

<style>
.submenu {
    padding-left: 15px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.submenu.show {
    max-height: 500px;
}

.dropdown-item {
    display: block;
    padding: 8px 35px;
    color: #e4f2ff;
    text-decoration: none;
    font-size: 0.9rem;
    border-radius: 6px;
    transition: 0.2s;
    margin-bottom: 2px;
    background: none;
    border: none;
    width: 100%;
    text-align: left;
}

.dropdown-item:hover,
.dropdown-item.active {
    background-color: rgba(255, 255, 255, 0.15);
    color: #fff;
}

/* Dropdown arrow rotation */
.dropdown-toggle::after {
    transition: transform 0.3s ease;
    float: right;
    margin-top: 8px;
}

.dropdown-toggle[aria-expanded="true"]::after {
    transform: rotate(90deg);
}

/* Remove default dropdown styling */
.dropdown-menu {
    background: transparent;
    border: none;
    box-shadow: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-close other dropdowns when one is opened
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-bs-target');
            
            // Close all other dropdowns
            dropdownToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherTargetId = otherToggle.getAttribute('data-bs-target');
                    const otherTarget = document.querySelector(otherTargetId);
                    if (otherTarget && otherTarget.classList.contains('show')) {
                        otherTarget.classList.remove('show');
                    }
                }
            });
        });
    });

    // Add active class management
    const navLinks = document.querySelectorAll('.nav-link, .dropdown-item');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
        });
    });
});
</script>