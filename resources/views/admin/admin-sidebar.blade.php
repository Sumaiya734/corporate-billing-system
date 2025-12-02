<!-- resources/views/admin/admin-sidebar.blade.php -->

<!-- Sidebar fragment (included into layout's column) -->
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
            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#customersMenu" aria-expanded="{{ request()->routeIs('admin.customers.*') ? 'true' : 'false' }}">
                <i class="fas fa-users me-2"></i>Manage Customers
            </a>
            <div class="collapse submenu {{ request()->routeIs('admin.customers.*') ? 'show' : '' }}" id="customersMenu">
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
            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#billingMenu" aria-expanded="{{ request()->routeIs('admin.billing.*') ? 'true' : 'false' }}">
                <i class="fas fa-file-invoice-dollar me-2"></i>Billings
            </a>
            <div class="collapse submenu {{ request()->routeIs('admin.billing.*') ? 'show' : '' }}" id="billingMenu">
                <a class="dropdown-item {{ request()->routeIs('admin.billing.billing-invoices') ? 'active' : '' }}" href="{{ route('admin.billing.billing-invoices') }}">
                    <i class="fas fa-file-invoice-dollar me-2"></i>All Invoices
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.billing.monthly-bills') ? 'active' : '' }}" href="{{ route('admin.billing.monthly-bills', ['month' => date('Y-m')]) }}">
                    <i class="fas fa-calendar me-2"></i>Previous Month Bills
                </a>
                
            </div>
        </div>

        <!-- Product Management -->
        <div class="dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#productMenu" aria-expanded="{{ request()->routeIs('admin.products.*') ? 'true' : 'false' }}">
                <i class="fas fa-cube me-2"></i>Product Management
            </a>
            <div class="collapse submenu {{ request()->routeIs('admin.products.*') ? 'show' : '' }}" id="productMenu">
                <a class="dropdown-item {{ request()->routeIs('admin.products.index') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                    <i class="fas fa-list me-2"></i>All Products
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.products.types') ? 'active' : '' }}" href="{{ route('admin.products.types') }}">
                    <i class="fas fa-plus me-2"></i>Create Product Type
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.products.create') ? 'active' : '' }}" href="{{ route('admin.products.create') }}">
                    <i class="fas fa-plus me-2"></i>Create New Product
                </a>
            </div>
        </div>

        <!-- Customer Products -->
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.customer-to-products.index') ? 'active' : '' }}" href="{{ route('admin.customer-to-products.index') }}">
                <i class="fas fa-box me-2"></i>Customer to Products
            </a>
        </div>


        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.payment-details.index') ? 'active' : '' }}" href="{{ route('admin.payment-details.index') }}">
                <i class="fas fa-box me-2"></i>Payment Details
            </a>
        </div>
        
      <!-- Reports & Analytics -->
   <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="collapse" data-bs-target="#reportsMenu" aria-expanded="{{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }}" aria-controls="reportsMenu">
                <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
            </a>
            <div class="collapse submenu {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" id="reportsMenu">
                <a class="dropdown-item {{ request()->routeIs('admin.reports.revenue') ? 'active' : '' }}" href="{{ route('admin.reports.revenue') }}">
                    <i class="fas fa-money-bill-wave me-2"></i>Revenue Reports
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.reports.financial-analytics') ? 'active' : '' }}" href="{{ route('admin.reports.financial-analytics') }}">
                    <i class="fas fa-chart-line me-2"></i>Financial Analytics
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.reports.customer-statistics') ? 'active' : '' }}" href="{{ route('admin.reports.customer-statistics') }}">
                    <i class="fas fa-users me-2"></i>Customer Statistics
                </a>
                <a class="dropdown-item {{ request()->routeIs('admin.reports.collection-reports') ? 'active' : '' }}" href="{{ route('admin.reports.collection-reports') }}">
                    <i class="fas fa-clipboard-list me-2"></i>Collection Reports
                </a>
            </div>
        </div>

    

        <!-- Settings -->
        <div class="dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#settingsMenu" aria-expanded="false">
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

<style>
 
.submenu {
    padding-left: 15px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}
.sidebar .nav-link {
    font-size: 0.9rem;  /* make sidebar text a little smaller */
}

.submenu.show {
    max-height: 500px;
}

.dropdown-item {
    display: block;
    padding: 8px 35px;
    color: #e4f2ff;
    text-decoration: none;
    font-size: 0.8rem;
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

/* Mobile styles */
@media (max-width: 767.98px) {
    .sidebar {
        position: fixed;
        top: 56px;
        left: 0;
        bottom: 0;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        overflow-y: auto;
        width: 80%;
        max-width: 300px;
    }
    
    .sidebar.show {
        transform: translateX(0);
        box-shadow: 2px 0 10px rgba(0,0,0,0.3);
    }
    
    .main-content {
        width: 100%;
        margin-left: 0 !important;
    }
}

/* Ensure active states are properly highlighted */
.sidebar .nav-link.active {
    background: #3498db !important;
    color: white !important;
    border-left: 4px solid #2980b9 !important;
}

.sidebar .dropdown-item.active {
    background-color: rgba(52, 152, 219, 0.3) !important;
    color: #fff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggle clicks
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle[data-bs-toggle="collapse"]');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetSelector = this.getAttribute('data-bs-target');
            const target = document.querySelector(targetSelector);
            
            if (target) {
                const isCurrentlyOpen = target.classList.contains('show');
                
                // Toggle the current dropdown
                if (isCurrentlyOpen) {
                    target.classList.remove('show');
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    target.classList.add('show');
                    this.setAttribute('aria-expanded', 'true');
                }
            }
        });
    });

    // Set active states on page load based on server-side rendering
    // The blade templates already handle this with {{ request()->routeIs() }}
    // This script just ensures dropdowns stay open if they contain active items
    const activeDropdownItems = document.querySelectorAll('.dropdown-item.active');
    
    activeDropdownItems.forEach(item => {
        const parentDropdown = item.closest('.dropdown');
        if (parentDropdown) {
            const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
            const submenu = item.closest('.submenu');
            
            if (dropdownToggle && submenu) {
                submenu.classList.add('show');
                dropdownToggle.setAttribute('aria-expanded', 'true');
            }
        }
    });
    
    // Also check for active dropdown toggles (when on a route that matches the dropdown)
    const activeDropdownToggles = document.querySelectorAll('.dropdown-toggle.active');
    
    activeDropdownToggles.forEach(toggle => {
        const targetSelector = toggle.getAttribute('data-bs-target');
        const target = document.querySelector(targetSelector);
        
        if (target && !target.classList.contains('show')) {
            target.classList.add('show');
            toggle.setAttribute('aria-expanded', 'true');
        }
    });
});
</script>