<!-- Sidebar -->
<div id="sidebar" class="sidebar p-0 d-flex flex-column">
    <div class="sidebar-brand p-3">
        <div class="d-flex align-items-center mb-2">
            <div class="avatar-icon me-2">
                <i class="fas fa-user-circle fa-lg text-white"></i>
            </div>
            <div>
                <h6 class="text-white mb-0">{{ Str::limit($customer->name, 15) }}</h6>
                <small class="text-light opacity-75">Customer ID: {{ $customer->customer_id }}</small>
            </div>
        </div>
        <div class="status-indicator bg-success rounded-pill px-3 py-1 d-inline-block mt-2">
            <i class="fas fa-circle fa-xs me-1"></i>
            <small class="text-white">Active Account</small>
        </div>
    </div>
    
    <!-- Scrollable Navigation -->
    <div class="flex-grow-1 overflow-y-auto sidebar-scroll">
        <nav class="nav flex-column p-3">
            <!-- Dashboard -->
            <a class="nav-link @if(request()->routeIs('customer.dashboard')) active @endif" href="{{ route('customer.dashboard') }}">
                <i class="fas fa-home me-2"></i>Dashboard
            </a>

            <!-- Invoices -->
            <a class="nav-link @if(request()->routeIs('customer.invoices.*')) active @endif" href="{{ route('customer.invoices.index') ?? '#' }}">
                <i class="fas fa-file-invoice me-2"></i>Invoices
            </a>

            <!-- Payments -->
            <a class="nav-link @if(request()->routeIs('customer.payments.*')) active @endif" href="{{ route('customer.payments.index') ?? '#' }}">
                <i class="fas fa-credit-card me-2"></i>Payments
            </a>

            <!-- My Products -->
            <a class="nav-link @if(request()->routeIs('customer.products.*')) active @endif" href="{{ route('customer.products.index') ?? '#' }}">
                <i class="fas fa-box me-2"></i>My Products
            </a>

            <!-- My Profile -->
            <a class="nav-link @if(request()->routeIs('customer.profile.*')) active @endif" href="{{ route('customer.profile.index') }}">
                <i class="fas fa-user me-2"></i>My Profile
            </a>

            <!-- Support Center -->
            <a class="nav-link @if(request()->routeIs('customer.support.*')) active @endif" href="{{ route('customer.support.index') }}">
                <i class="fas fa-life-ring me-2"></i>Support Center
            </a>

            <!-- Contact -->
            <a class="nav-link @if(request()->routeIs('customer.contact.*')) active @endif" href="{{ route('customer.contact.index') }}">
                <i class="fas fa-phone me-2"></i>Contact
            </a>

            
        </nav>
    </div>
</div>

<style>
    /* Custom scrollbar styling */
    .sidebar-scroll {
        scrollbar-width: thin; /* Firefox */
        scrollbar-color: rgba(255,255,255,0.1) transparent; /* Firefox */
    }

    /* For WebKit browsers (Chrome, Safari, Edge) */
    .sidebar-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(255,255,255,0.1);
        border-radius: 3px;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background-color: rgba(255,255,255,0.2);
    }

    .sidebar-scroll::-webkit-scrollbar-thumb:active {
        background-color: rgba(255,255,255,0.3);
    }

    /* Hide scrollbar when not hovering */
    .sidebar-scroll::-webkit-scrollbar {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-scroll:hover::-webkit-scrollbar {
        opacity: 1;
    }

    /* For Firefox - always thin scrollbar */
    .sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.1) transparent;
    }

    /* Smooth scrolling */
    .sidebar-scroll {
        scroll-behavior: smooth;
    }
</style>