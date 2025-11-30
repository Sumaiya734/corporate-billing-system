@extends('layouts.admin')

@section('title', 'Product Management - Admin Dashboard')

@section('content')
<div class="container-fluid p-4">
    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="toastContainer"></div>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-cube me-2 text-primary"></i>Product Management
            </h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="exportBtn">
                <i class="fas fa-download me-1"></i>Export
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create Product
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-primary text-white mb-4 stat-card-clickable" data-filter="all" data-action="filter" role="button" title="Click to view all products">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-uppercase mb-2 opacity-85">
                                <i class="fas fa-cubes me-1"></i>Total Products
                            </div>
                            <h2 class="mb-2 fw-bold display-6">{{ $stats['total_products'] ?? 0 }}</h2>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-3" style="height: 6px; background: rgba(255,255,255,0.3);">
                                    <div class="progress-bar bg-white" style="width: 100%"></div>
                                </div>
                                <small class="opacity-85">100% Active</small>
                            </div>
                        </div>
                        <div class="position-relative">
                            <div class="icon-shape bg-white bg-opacity-20 rounded-circle p-3">
                                <i class="fas fa-cubes fa-2x text-white"></i>
                            </div>
                            <div class="position-absolute top-0 end-0 mt-n2 me-n2">
                                <span class="badge bg-white text-primary rounded-pill p-1">
                                    <i class="fas fa-check-circle fa-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-20">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="opacity-75 d-block">Regular</small>
                                <span class="fw-bold">{{ $stats['regular_count'] ?? 0 }}</span>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75 d-block">Special</small>
                                <span class="fw-bold">{{ $stats['special_count'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-success text-white mb-4 stat-card-clickable" data-action="show-types" role="button" title="Click to view all product types">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-uppercase mb-2 opacity-85">
                                <i class="fas fa-layer-group me-1"></i>Product Types
                            </div>
                            <h2 class="mb-2 fw-bold display-6">{{ $stats['total_types'] ?? 0 }}</h2>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-3" style="height: 6px; background: rgba(255,255,255,0.3);">
                                    <div class="progress-bar bg-white" style="width: {{ $stats['type_utilization'] ?? 100 }}%"></div>
                                </div>
                                <small class="opacity-85">{{ $stats['type_utilization'] ?? 100 }}% Used</small>
                            </div>
                        </div>
                        <div class="position-relative">
                            <div class="icon-shape bg-white bg-opacity-20 rounded-circle p-3">
                                <i class="fas fa-layer-group fa-2x text-white"></i>
                            </div>
                            <div class="position-absolute top-0 end-0 mt-n2 me-n2">
                                <span class="badge bg-white text-success rounded-pill p-1">
                                    <i class="fas fa-tags fa-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-20">
                        <div class="text-center">
                            <small class="opacity-75 d-block mb-1">Categories Available</small>
                            <span class="fw-bold">{{ $stats['active_types'] ?? 0 }} Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-warning text-white mb-4 stat-card-clickable" data-action="show-popular" data-product="{{ $stats['most_popular'] ?? '' }}" role="button" title="Click to view most popular product details">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-uppercase mb-2 opacity-85">
                                <i class="fas fa-fire me-1"></i>Most Popular
                            </div>
                            <h3 class="mb-2 fw-bold" style="font-size: 1.8rem; line-height: 1.2;">
                                {{ \Illuminate\Support\Str::limit($stats['most_popular'] ?? 'N/A', 15) }}
                            </h3>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-3" style="height: 6px; background: rgba(255,255,255,0.3);">
                                    <div class="progress-bar bg-white" style="width: {{ $stats['popularity_score'] ?? 75 }}%"></div>
                                </div>
                                <small class="opacity-85">{{ $stats['popularity_score'] ?? 75 }}% Score</small>
                            </div>
                        </div>
                        <div class="position-relative">
                            <div class="icon-shape bg-white bg-opacity-20 rounded-circle p-3">
                                <i class="fas fa-crown fa-2x text-white"></i>
                            </div>
                            <div class="position-absolute top-0 end-0 mt-n2 me-n2">
                                <span class="badge bg-white text-warning rounded-pill p-1">
                                    <i class="fas fa-trending-up fa-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-20">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="opacity-75 d-block">Price</small>
                                <span class="fw-bold">৳{{ number_format($stats['popular_price'] ?? 0, 2) }}</span>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75 d-block">Customers</small>
                                <span class="fw-bold">{{ $stats['popular_customers'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-info text-white stat-card-clickable" data-action="show-customers" role="button" title="Click to view active customers">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-uppercase mb-2 opacity-85">
                                <i class="fas fa-users me-1"></i>Active Customers
                            </div>
                            <h2 class="mb-2 fw-bold display-6">{{ $stats['active_customers'] ?? '0' }}</h2>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-3" style="height: 6px; background: rgba(255,255,255,0.3);">
                                    <div class="progress-bar bg-white" style="width: {{ $stats['customer_growth'] ?? 25 }}%"></div>
                                </div>
                                <small class="opacity-85">+{{ $stats['customer_growth'] ?? 25 }}% Growth</small>
                            </div>
                        </div>
                        <div class="position-relative">
                            <div class="icon-shape bg-white bg-opacity-20 rounded-circle p-3">
                                <i class="fas fa-user-check fa-2x text-white"></i>
                            </div>
                            <div class="position-absolute top-0 end-0 mt-n2 me-n2">
                                <span class="badge bg-white text-info rounded-pill p-1">
                                    <i class="fas fa-bolt fa-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-20">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="opacity-75 d-block">New This Month</small>
                                <span class="fw-bold text-success">+{{ $stats['new_customers'] ?? 0 }}</span>
                            </div>
                            <div class="col-6">
                                <small class="opacity-75 d-block">Retention</small>
                                <span class="fw-bold">{{ $stats['retention_rate'] ?? 95 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>All Products
            </h5>

            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control form-control-sm search-box" placeholder="Search products..." style="min-width: 200px;">
                <div class="btn-group">
                    <!-- <button class="btn btn-sm btn-outline-secondary filter-btn active" data-type="all">All</button>
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-type="regular">Regular</button>
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-type="special">Special</button> -->
                    <!-- <input type="text" class="form-control form-control-sm search-box" placeholder="Filter products..." style="min-width: 200px;"> -->
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">Product ID</th>
                            <th width="120">Product Code</th>
                            <th>Product Name</th>
                            <th>Product Type</th>
                            <th>Description</th>
                            <th width="120" class="text-end">Price</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        @php
                            $typeName = $product->type->name ?? 'Unknown';
                            $typeConfig = \App\Helpers\ProductTypeHelper::getTypeConfig($typeName);
                        @endphp
                        <tr data-type="{{ $product->product_type }}" id="product-row-{{ $product->p_id }}">
                            <td class="fw-bold">{{ $product->p_id }}</td>
                            <td>
                                <span class="badge bg-secondary" style="font-size: 0.85rem; font-family: monospace;">
                                    PROD-{{ $product->product_code }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-icon me-3" style="background-color: {{ $typeConfig['color'] }};" title="{{ ucfirst($typeName) }} Type">
                                        <i class="fas {{ $typeConfig['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                        <small class="text-muted">{{ $typeConfig['description'] ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge product-type-badge" style="background-color: {{ $typeConfig['color'] }}; color: {{ $typeConfig['textColor'] }};">
                                    <i class="fas {{ $typeConfig['icon'] }} me-1"></i>{{ ucfirst($typeName) }}
                                </span>
                            </td>
                            <td>
                                <p class="mb-1">{{ \Illuminate\Support\Str::limit($product->description, 60) }}</p>
                                <small class="text-muted">Created: {{ $product->created_at ? $product->created_at->format('M d, Y') : 'N/A' }}</small>
                            </td>
                           
                            <td class="text-end">
                                <h6 class="text-success mb-0">৳{{ number_format($product->monthly_price, 2) }}<small class="text-muted">/month</small></h6>
                            </td>
                            <td class="text-center action-column">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary edit-product" 
                                            data-id="{{ $product->p_id }}" 
                                            data-name="{{ $product->name }}"
                                            title="Edit Product">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-md-inline ms-1">Edit</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger delete-product" 
                                            data-id="{{ $product->p_id }}" 
                                            data-name="{{ $product->name }}"
                                            title="Delete Product">
                                        <i class="fas fa-trash"></i>
                                        <span class="d-none d-md-inline ms-1">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $products->count() }} of {{ $stats['total_products'] ?? $products->count() }} products
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit product Modal -->
<div class="modal fade" id="editproductModal" tabindex="-1" aria-labelledby="editproductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editproductForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="p_id" id="edit_p_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editproductModalLabel">Edit product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="editproductModalBody">
                    <div class="text-center py-4" id="editLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading product details...</p>
                    </div>

                    <div id="editErrors" class="alert alert-danger d-none"></div>

                    <div id="editFields" style="display:none;">
                        <!-- Product ID (Read-only) -->
                        <div class="mb-3">
                            <label class="form-label">Product ID</label>
                            <div class="input-group">
                                <span class="input-group-text">PROD-</span>
                                <input type="text" id="edit_product_code" class="form-control" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="form-text">Unique product identifier (cannot be changed)</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Type *</label>
                                <select name="product_type_id" id="edit_product_type_id" class="form-control" required>
                                    <option value="">Select Product Type</option>
                                    @foreach($productTypes as $type)
                                        <option value="{{ $type->id }}">{{ ucfirst($type->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (৳/month) *</label>
                                <input type="number" name="monthly_price" id="edit_monthly_price" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateproductBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Update product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Delete Confirmation Modal -->
<x-delete-confirmation-modal />
@endsection

@section('styles')
<style>
    .product-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .product-icon i {
        color: white !important;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .product-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .product-type-badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
    }

    /* Statistics Card Styles */
    .stat-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        position: relative;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    }

    .stat-card-clickable {
        cursor: pointer;
    }

    .stat-card-clickable.active {
        border: 2px solid;
        border-image: linear-gradient(45deg, #667eea, #764ba2) 1;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    /* Gradient Backgrounds */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%) !important;
    }

    /* Icon Shapes */
    .icon-shape {
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        transition: all 0.3s ease !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background-color: rgba(255, 255, 255, 0.2) !important;
    }

    .icon-shape i {
        color: white !important;
        font-size: 2rem !important;
        display: inline-flex !important;
        line-height: 1 !important;
    }

    .icon-shape.rounded-circle {
        border-radius: 50% !important;
    }

    .stat-card:hover .icon-shape {
        transform: scale(1.1) rotate(5deg) !important;
        background-color: rgba(255, 255, 255, 0.3) !important;
    }

    /* Progress Bars */
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }

    /* Badge Styles */
    .badge.rounded-pill {
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    /* Display Numbers */
    .display-6 {
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Hover Effects */
    .stat-card:hover .opacity-85 {
        opacity: 0.95 !important;
    }

    /* Loading Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card {
        animation: fadeInUp 0.6s ease-out;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }

    /* Table Styles */
    .table th {
        border-top: none;
        font-weight: 600;
        color: #2b2d42;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        padding: 16px 12px;
    }

    .action-column {
        white-space: nowrap;
    }

    .action-column .btn-group {
        display: inline-flex;
    }

    .action-column .btn {
        min-width: 36px;
        transition: all 0.2s ease;
    }

    .action-column .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .action-column .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .action-column {
            position: sticky;
            right: 0;
            background-color: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.05);
        }

        .table thead th:last-child {
            position: sticky;
            right: 0;
            background-color: #f8f9fa;
            box-shadow: -2px 0 5px rgba(0,0,0,0.05);
        }

        .stat-card {
            margin-bottom: 1rem;
        }
        
        .display-6 {
            font-size: 2rem;
        }
        
        .icon-shape {
            width: 50px;
            height: 50px;
            padding: 12px !important;
        }
        
        .icon-shape i {
            font-size: 1.2rem !important;
        }
    }

    @media (max-width: 576px) {
        .card-header {
            flex-direction: column;
            gap: 10px;
        }

        .card-header .d-flex {
            width: 100%;
            flex-direction: column;
        }

        .search-box {
            width: 100% !important;
            min-width: auto !important;
        }

        .action-column .btn span {
            display: none !important;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    console.log('🚀 product management script loaded');
    
    (function() {
        console.log('📦 Initializing product management...');
        
        // CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('🔑 CSRF Token:', csrfToken ? 'Found' : 'Missing');

        // Toast helper
        function showToast(message, type = 'success') {
            const toastId = 'toast-' + Date.now();
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            document.getElementById('toastContainer').appendChild(wrapper.firstElementChild);
            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl);
            toast.show();

            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        // Utility to show validation errors
        function showValidationErrors(containerEl, errors) {
            if (!containerEl) return;
            containerEl.classList.remove('d-none');
            if (typeof errors === 'string') {
                containerEl.innerHTML = errors;
                return;
            }
            if (errors.message) {
                containerEl.innerHTML = errors.message;
                return;
            }
            const list = Object.values(errors).flat().map(e => `<div>• ${e}</div>`).join('');
            containerEl.innerHTML = list;
        }

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                filterproducts(this.getAttribute('data-type'));
            });
        });

        // Clickable stat cards
        document.querySelectorAll('.stat-card-clickable').forEach(card => {
            card.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                
                // Update stat card active state
                document.querySelectorAll('.stat-card-clickable').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                // Handle different actions
                switch(action) {
                    case 'filter':
                        const filterType = this.getAttribute('data-filter');
                        // Update filter buttons
                        document.querySelectorAll('.filter-btn').forEach(btn => {
                            if (btn.getAttribute('data-type') === filterType) {
                                btn.classList.add('active');
                            } else {
                                btn.classList.remove('active');
                            }
                        });
                        filterproducts(filterType);
                        document.querySelector('.card-header').scrollIntoView({ behavior: 'smooth', block: 'start' });
                        showToast('Showing All Products', 'info');
                        break;
                        
                    case 'show-types':
                        showProductTypesModal();
                        break;
                        
                    case 'show-popular':
                        const productName = this.getAttribute('data-product');
                        showPopularProductDetails(productName);
                        break;
                        
                    case 'show-customers':
                        showActiveCustomersModal();
                        break;
                }
            });
        });
        
        // Show Product Types Modal
        function showProductTypesModal() {
            const types = @json($productTypes ?? []);
            const products = @json($products ?? []);
            
            let typeBreakdown = '<div class="list-group">';
            types.forEach(type => {
                const count = products.filter(p => p.product_type_id === type.id).length;
                const percentage = products.length > 0 ? ((count / products.length) * 100).toFixed(1) : 0;
                const typeConfig = getDynamicTypeConfig(type.name);
                typeBreakdown += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="product-icon me-3" style="width: 32px; height: 32px; font-size: 0.8rem; background-color: ${typeConfig.color};" title="${type.name}">
                                    <i class="fas ${typeConfig.icon}"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">${type.name.charAt(0).toUpperCase() + type.name.slice(1)}</h6>
                                    <small class="text-muted">${typeConfig.description}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge product-type-badge" style="background-color: ${typeConfig.color}; color: ${typeConfig.textColor};">${count} products</span>
                                <div class="progress mt-1" style="width: 100px; height: 6px;">
                                    <div class="progress-bar" style="width: ${percentage}%; background-color: ${typeConfig.color};"></div>
                                </div>
                                <small class="text-muted">${percentage}%</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            typeBreakdown += '</div>';
            
            showInfoModal('Product Types Breakdown', typeBreakdown);
        }
        
        // Dynamic product type configuration generator
        function getDynamicTypeConfig(typeName) {
            // Predefined configurations for common types
            const predefinedTypes = {
                'regular': { 
                    icon: 'fa-wifi', 
                    color: '#3498db',
                    description: 'Standard internet package'
                },
                'special': { 
                    icon: 'fa-star', 
                    color: '#e74c3c',
                    description: 'Special promotional package'
                },
                'premium': { 
                    icon: 'fa-crown', 
                    color: '#f39c12',
                    description: 'Premium high-speed package'
                },
                'enterprise': { 
                    icon: 'fa-building', 
                    color: '#9b59b6',
                    description: 'Business enterprise solution'
                },
                'business': { 
                    icon: 'fa-briefcase', 
                    color: '#2ecc71',
                    description: 'Business grade service'
                },
                'starter': { 
                    icon: 'fa-seedling', 
                    color: '#1abc9c',
                    description: 'Beginner friendly package'
                },
                'professional': { 
                    icon: 'fa-user-tie', 
                    color: '#e67e22',
                    description: 'Professional grade service'
                },
                'ultimate': { 
                    icon: 'fa-rocket', 
                    color: '#e84393',
                    description: 'Ultimate performance package'
                },
                'custom': { 
                    icon: 'fa-cogs', 
                    color: '#6c5ce7',
                    description: 'Custom tailored solution'
                }
            };
            
            // If type is predefined, return its configuration
            if (predefinedTypes[typeName.toLowerCase()]) {
                const config = predefinedTypes[typeName.toLowerCase()];
                return {
                    ...config,
                    textColor: getContrastColor(config.color)
                };
            }
            
            // Generate dynamic configuration for new types
            return generateDynamicConfig(typeName);
        }
        
        // Generate unique color and icon for new product types
        function generateDynamicConfig(typeName) {
            // Generate consistent color based on type name hash
            const color = generateColorFromString(typeName);
            
            // Select icon based on type name keywords or generate from name
            const icon = generateIconFromString(typeName);
            
            // Generate description based on type name
            const description = generateDescriptionFromString(typeName);
            
            return {
                icon: icon,
                color: color,
                textColor: getContrastColor(color),
                description: description
            };
        }
        
        // Generate consistent color from string
        function generateColorFromString(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }
            
            // Generate HSL color for better consistency and accessibility
            const hue = hash % 360;
            const saturation = 70 + (hash % 20); // 70-90%
            const lightness = 50 + (hash % 15); // 50-65%
            
            return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
        }
        
        // Generate icon based on type name keywords
        function generateIconFromString(typeName) {
            const lowerType = typeName.toLowerCase();
            const iconMap = {
                // Speed related
                'speed': 'fa-tachometer-alt',
                'fast': 'fa-bolt',
                'quick': 'fa-running',
                'rapid': 'fa-wind',
                
                // Business related
                'business': 'fa-briefcase',
                'corporate': 'fa-building',
                'office': 'fa-desktop',
                'company': 'fa-landmark',
                
                // Home related
                'home': 'fa-home',
                'family': 'fa-users',
                'residential': 'fa-house-user',
                
                // Student related
                'student': 'fa-graduation-cap',
                'education': 'fa-book',
                'campus': 'fa-school',
                
                // Gaming related
                'gaming': 'fa-gamepad',
                'game': 'fa-dice',
                'stream': 'fa-video',
                
                // Streaming related
                'streaming': 'fa-film',
                'video': 'fa-video',
                'media': 'fa-photo-video',
                
                // Economic related
                'economic': 'fa-piggy-bank',
                'budget': 'fa-money-bill-wave',
                'cheap': 'fa-tags',
                
                // Professional related
                'professional': 'fa-user-tie',
                'pro': 'fa-award',
                'expert': 'fa-certificate',
                
                // Ultimate related
                'ultimate': 'fa-rocket',
                'extreme': 'fa-fire',
                'maximum': 'fa-chart-line',
                
                // Custom related
                'custom': 'fa-cogs',
                'tailored': 'fa-user-cog',
                'personal': 'fa-user-edit',
                
                // Default fallbacks
                'basic': 'fa-layer-group',
                'standard': 'fa-certificate',
                'advanced': 'fa-microchip'
            };
            
            // Check for keywords in type name
            for (const [keyword, icon] of Object.entries(iconMap)) {
                if (lowerType.includes(keyword)) {
                    return icon;
                }
            }
            
            // Fallback: use first letter or default icon
            const firstLetter = typeName.charAt(0).toLowerCase();
            const letterIcons = {
                'a': 'fa-award', 'b': 'fa-bolt', 'c': 'fa-cube', 'd': 'fa-diamond',
                'e': 'fa-star', 'f': 'fa-flag', 'g': 'fa-gem', 'h': 'fa-heart',
                'i': 'fa-infinity', 'j': 'fa-journal', 'k': 'fa-key', 'l': 'fa-leaf',
                'm': 'fa-magic', 'n': 'fa-network', 'o': 'fa-orbit', 'p': 'fa-palette',
                'q': 'fa-question', 'r': 'fa-rainbow', 's': 'fa-shield', 't': 'fa-trophy',
                'u': 'fa-umbrella', 'v': 'fa-volume', 'w': 'fa-wifi', 'x': 'fa-x-ray',
                'y': 'fa-yin-yang', 'z': 'fa-zap'
            };
            
            return letterIcons[firstLetter] || 'fa-cube';
        }
        
        // Generate description based on type name
        function generateDescriptionFromString(typeName) {
            const lowerType = typeName.toLowerCase();
            
            if (lowerType.includes('basic') || lowerType.includes('starter')) {
                return 'Essential package for basic needs';
            } else if (lowerType.includes('standard') || lowerType.includes('regular')) {
                return 'Standard package for everyday use';
            } else if (lowerType.includes('premium') || lowerType.includes('pro')) {
                return 'Premium package with enhanced features';
            } else if (lowerType.includes('ultimate') || lowerType.includes('extreme')) {
                return 'Ultimate package for maximum performance';
            } else if (lowerType.includes('business') || lowerType.includes('corporate')) {
                return 'Business-grade solution for companies';
            } else if (lowerType.includes('gaming') || lowerType.includes('game')) {
                return 'Optimized for gaming and low latency';
            } else if (lowerType.includes('streaming') || lowerType.includes('media')) {
                return 'Perfect for streaming and media consumption';
            } else if (lowerType.includes('student') || lowerType.includes('education')) {
                return 'Student-friendly package with educational benefits';
            } else if (lowerType.includes('family') || lowerType.includes('home')) {
                return 'Family package for multiple users';
            } else {
                return `${typeName} package with customized features`;
            }
        }
        
        // Get contrasting text color (black or white) for background
        function getContrastColor(hexcolor) {
            // If using HSL, convert to RGB first
            let r, g, b;
            
            if (hexcolor.startsWith('hsl')) {
                const hsl = hexcolor.match(/(\d+)/g);
                const h = hsl[0] / 360;
                const s = hsl[1] / 100;
                const l = hsl[2] / 100;
                
                if (s === 0) {
                    r = g = b = l;
                } else {
                    const hue2rgb = (p, q, t) => {
                        if (t < 0) t += 1;
                        if (t > 1) t -= 1;
                        if (t < 1/6) return p + (q - p) * 6 * t;
                        if (t < 1/2) return q;
                        if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                        return p;
                    };
                    
                    const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                    const p = 2 * l - q;
                    r = hue2rgb(p, q, h + 1/3);
                    g = hue2rgb(p, q, h);
                    b = hue2rgb(p, q, h - 1/3);
                }
                
                r = Math.round(r * 255);
                g = Math.round(g * 255);
                b = Math.round(b * 255);
            } else {
                // Handle hex colors
                hexcolor = hexcolor.replace("#", "");
                r = parseInt(hexcolor.substr(0, 2), 16);
                g = parseInt(hexcolor.substr(2, 2), 16);
                b = parseInt(hexcolor.substr(4, 2), 16);
            }
            
            // Calculate luminance
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
            return luminance > 0.5 ? '#000000' : '#ffffff';
        }
        
        // Show Popular Product Details
        function showPopularProductDetails(productName) {
            if (!productName || productName === 'N/A') {
                showToast('No popular product data available', 'warning');
                return;
            }
            
            const products = @json($products ?? []);
            const product = products.find(p => p.name === productName);
            
            if (!product) {
                showToast('Product details not found', 'warning');
                return;
            }
            
            const typeName = product.type?.name || 'Unknown';
            const typeConfig = getDynamicTypeConfig(typeName);
            
            const content = `
                <div class="card border-0">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="product-icon mx-auto mb-3" style="width: 80px; height: 80px; font-size: 1.5rem; background-color: ${typeConfig.color};">
                                <i class="fas ${typeConfig.icon}"></i>
                            </div>
                            <h4>${product.name}</h4>
                            <span class="badge product-type-badge" style="background-color: ${typeConfig.color}; color: ${typeConfig.textColor};">
                                <i class="fas ${typeConfig.icon} me-1"></i>${typeName.charAt(0).toUpperCase() + typeName.slice(1)}
                            </span>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h5 class="text-success mb-0">৳${parseFloat(product.monthly_price).toFixed(2)}</h5>
                                <small class="text-muted">Monthly Price</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h5 class="mb-0">PROD-${product.product_code}</h5>
                                <small class="text-muted">Product Code</small>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p class="text-muted mb-0">${product.description || 'No description available'}</p>
                        </div>
                        <div class="mb-3">
                            <strong>Type Description:</strong>
                            <p class="text-muted mb-0">${typeConfig.description}</p>
                        </div>
                    </div>
                </div>
            `;
            
            showInfoModal('Most Popular Product', content);
        }
        
        // Show Active Customers Modal
        function showActiveCustomersModal() {
            const customerCount = {{ $stats['active_customers'] ?? 0 }};
            
            const content = `
                <div class="text-center py-4">
                    <div class="avatar-lg bg-info rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h2 class="mb-2">${customerCount}</h2>
                    <p class="text-muted mb-4">Active customers currently using products</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        These customers have active product subscriptions
                    </div>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>View All Customers
                    </a>
                </div>
            `;
            
            showInfoModal('Active Customers', content);
        }
        
        // Generic Info Modal
        function showInfoModal(title, content) {
            const modalHtml = `
                <div class="modal fade" id="infoModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${content}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('infoModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('infoModal'));
            modal.show();
            
            // Clean up after modal is hidden
            document.getElementById('infoModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function filterproducts(type) {
            const rows = document.querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                const match = !type || type === 'all' || rowType === type;
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            
            // Update footer count
            const footerDiv = document.querySelector('.card-footer div');
            if (footerDiv) {
                const totalProducts = document.querySelectorAll('tbody tr').length;
                footerDiv.textContent = `Showing ${visibleCount} of ${totalProducts} products`;
            }
        }

        // Search functionality
        document.querySelector('.search-box')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // EDIT: Open modal with improved error handling
        console.log('✅ Edit button listener attached');
        
        document.body.addEventListener('click', function(e) {
            console.log('👆 Click detected on:', e.target);
            
            const editBtn = e.target.closest('.edit-product');
            if (editBtn) {
                console.log('✏️ Edit button clicked!', editBtn);
                e.preventDefault();
                const pId = editBtn.getAttribute('data-id');
                const productName = editBtn.getAttribute('data-name');
                console.log('📝 product ID:', pId, 'Name:', productName);
                
                // Disable button temporarily
                editBtn.disabled = true;
                const originalHtml = editBtn.innerHTML;
                editBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                openEditModal(pId, productName).finally(() => {
                    editBtn.disabled = false;
                    editBtn.innerHTML = originalHtml;
                });
            }
        });

        async function openEditModal(pId, productName) {
            const errorsEl = document.getElementById('editErrors');
            const loadingEl = document.getElementById('editLoading');
            const fieldsEl = document.getElementById('editFields');
            
            errorsEl.classList.add('d-none');
            loadingEl.style.display = '';
            fieldsEl.style.display = 'none';

            const modalEl = document.getElementById('editproductModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            try {
                const url = `{{ url('admin/products') }}/${pId}`;
                console.log('📡 Fetching product from:', url);
                
                const res = await fetch(url, {
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!res.ok) {
                    const errorData = await res.json().catch(() => ({}));
                    throw new Error(errorData.message || `Failed to fetch product (${res.status})`);
                }
                
                const pkg = await res.json();

                // Populate fields with validation
                document.getElementById('edit_p_id').value = pkg.p_id || pId;
                document.getElementById('edit_product_code').value = pkg.product_code || '';
                document.getElementById('edit_name').value = pkg.name || '';
                document.getElementById('edit_product_type_id').value = pkg.product_type_id || '';
                document.getElementById('edit_monthly_price').value = pkg.monthly_price || '';
                document.getElementById('edit_description').value = pkg.description || '';

                loadingEl.style.display = 'none';
                fieldsEl.style.display = '';
                
                // Update modal title with product name
                document.getElementById('editproductModalLabel').textContent = `Edit Product: ${pkg.name || productName}`;
            } catch (err) {
                console.error('Error loading product:', err);
                loadingEl.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${err.message || 'Failed to load product details. Please try again.'}
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                `;
                showToast('Failed to load product details', 'danger');
            }
        }

        // UPDATE: submit update
        document.getElementById('editproductForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitUpdate();
        });

        async function submitUpdate() {
            const form = document.getElementById('editproductForm');
            const pId = document.getElementById('edit_p_id').value;
            const btn = document.getElementById('updateproductBtn');
            const spinner = btn.querySelector('.spinner-border');
            
            btn.disabled = true;
            spinner.classList.remove('d-none');
            document.getElementById('editErrors').classList.add('d-none');

            const formData = new FormData(form);
            const url = `{{ url('admin/products') }}/${pId}`;
            console.log('📡 Updating product at:', url);

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const json = await res.json();

                if (!res.ok) {
                    showValidationErrors(document.getElementById('editErrors'), json.errors || json.message || 'Failed to update product');
                    return;
                }

                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editproductModal')).hide();
                    showToast(json.message || 'product updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showValidationErrors(document.getElementById('editErrors'), json.message || 'Unknown error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                showValidationErrors(document.getElementById('editErrors'), 'Network error occurred');
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // DELETE: product with modal confirmation
        console.log('✅ Delete button listener attached');
        
        document.body.addEventListener('click', function(e) {
            const delBtn = e.target.closest('.delete-product');
            if (!delBtn) return;
            
            console.log('🗑️ Delete button clicked!', delBtn);
            e.preventDefault();
            const pId = delBtn.getAttribute('data-id');
            const productName = delBtn.getAttribute('data-name');
            console.log('📝 product ID:', pId, 'Name:', productName);
            
            const message = `Are you sure you want to delete <strong>"${productName}"</strong>?<br><small class="text-danger">This action cannot be undone and will remove all associated data.</small>`;
            const action = `{{ url('admin/products') }}/${pId}`;
            const row = document.getElementById(`product-row-${pId}`);
            
            showDeleteModal(message, action, row, updateproductCount);
        });

        // Helper function to update product count
        function updateproductCount() {
            const visibleRows = document.querySelectorAll('tbody tr:not([style*="display: none"])').length;
            const footerDiv = document.querySelector('.card-footer div');
            if (footerDiv) {
                footerDiv.textContent = `Showing ${visibleRows} of ${visibleRows} products`;
            }
            
            // Show empty state if no products
            if (visibleRows === 0) {
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No products found.</td></tr>';
            }
        }

        // EXPORT functionality
        document.getElementById('exportBtn')?.addEventListener('click', function() {
            const rows = Array.from(document.querySelectorAll('table tbody tr:not([style*="display: none"])'));
            const csv = [];
            csv.push(['ID', 'Product Code', 'Name', 'Type', 'Price', 'Description'].join(','));
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td');
                if (cols.length >= 7) {
                    const rowData = [
                        `"${cols[0].textContent.trim()}"`,
                        `"${cols[1].textContent.trim()}"`,
                        `"${cols[2].querySelector('h6') ? cols[2].querySelector('h6').textContent.trim() : cols[2].textContent.trim()}"`,
                        `"${cols[3].textContent.trim()}"`,
                        `"${cols[5].textContent.trim().replace('/month','').replace('৳','').trim()}"`,
                        `"${cols[4].querySelector('p') ? cols[4].querySelector('p').textContent.trim() : cols[4].textContent.trim()}"`
                    ];
                    csv.push(rowData.join(','));
                }
            });
            
            const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
            const a = document.createElement('a');
            a.setAttribute('href', csvContent);
            a.setAttribute('download', `products_export_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(a);
            a.click();
            a.remove();
            showToast('Export started successfully!', 'success');
        });

        // Initialize filter
        filterproducts('all');
        
        console.log('✅ product management initialized successfully');
        console.log('📊 Edit buttons found:', document.querySelectorAll('.edit-product').length);
        console.log('📊 Delete buttons found:', document.querySelectorAll('.delete-product').length);

        // DEBUG: temporary widget to display first product icon classes and computed font-family
        // Remove this block after debugging Font Awesome loading issues
        (function() {
            try {
                const debugContainer = document.createElement('div');
                debugContainer.id = 'fa-debug';
                debugContainer.style.cssText = 'position:fixed;bottom:16px;left:16px;z-index:2000;background:#ffffff;padding:10px;border:1px solid #e3e6ea;border-radius:8px;font-size:12px;color:#2b2d42;box-shadow:0 6px 18px rgba(0,0,0,0.12);max-width:360px;line-height:1.2';
                debugContainer.innerHTML = '<strong style="display:block;margin-bottom:6px">Font Awesome Debug</strong><div id="fa-debug-body">Detecting...</div><div style="margin-top:6px;text-align:right"><button id="fa-debug-close" class="btn btn-sm btn-outline-secondary">Close</button></div>';
                document.body.appendChild(debugContainer);

                const closeBtn = document.getElementById('fa-debug-close');
                closeBtn.addEventListener('click', () => debugContainer.remove());

                const iconEl = document.querySelector('.product-icon i');
                const body = document.getElementById('fa-debug-body');
                if (!iconEl) {
                    body.innerHTML = '<span style="color:#d9534f">No .product-icon i element found in DOM</span>';
                    return;
                }

                const classes = iconEl.className || '(no classes)';
                const computed = window.getComputedStyle(iconEl).getPropertyValue('font-family') || '(no computed font-family)';

                body.innerHTML = `
                    <div><strong>Classes:</strong> <code style="white-space:normal">${classes}</code></div>
                    <div style="margin-top:6px"><strong>Computed font-family:</strong> <code style="white-space:normal">${computed}</code></div>
                    <div style="margin-top:6px;color:#6c757d;font-size:11px">If the font-family does not include "Font Awesome" or similar, the CSS/fonts may not have loaded.</div>
                `;
            } catch (err) {
                console.error('FA debug widget error', err);
            }
        })();

    });
</script>
@endsection