@extends('layouts.admin')

@section('title', 'Product Management - Admin Dashboard')

@section('content')
    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="toastContainer"></div>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-cubes me-2 text-primary"></i>Product Management
            </h2>
            <p class="text-muted mt-2">Manage all your internet service products in one place</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.types') }}" class="btn btn-outline-secondary">
                <i class="fas fa-tags me-1"></i>Product Types
            </a>
            <button class="btn btn-outline-primary" id="exportBtn">
                <i class="fas fa-download me-1"></i>Export
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create Product
            </a>
        </div>
    </div>

    <!-- Statistics Cards with Soft Colors -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 stat-card border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center flex-grow-1">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Products</h6>
                            <h2 class="mb-0 display-6 fw-bold text-primary">{{ $stats['total_products'] ?? 0 }}</h2>
                        </div>
                        <div class="avatar-lg rounded-circle d-flex align-items-center justify-content-center" 
                             style="background: rgba(33, 150, 243, 0.1); color: #2196F3;">
                            <i class="fas fa-cubes fa-3x"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top mt-auto" style="border-color: rgba(33, 150, 243, 0.2) !important;">
                        <small class="text-muted">
                            <i class="fas fa-check-circle me-1 text-success"></i>All active products in system
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 stat-card border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center flex-grow-1">
                        <div>
                            <h6 class="card-title text-muted mb-2">Product Types</h6>
                            <h2 class="mb-0 display-6 fw-bold text-success">{{ $stats['total_types'] ?? 0 }}</h2>
                        </div>
                        <div class="avatar-lg rounded-circle d-flex align-items-center justify-content-center" 
                             style="background: rgba(76, 175, 80, 0.1); color: #4CAF50;">
                            <i class="fas fa-tags fa-3x"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top mt-auto" style="border-color: rgba(76, 175, 80, 0.2) !important;">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1 text-info"></i>Product categories available
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 stat-card border-0 shadow-sm" style="background: linear-gradient(135deg, #fff3e0 0%, #ffecb3 100%);">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center flex-grow-1">
                        <div>
                            <h6 class="card-title text-muted mb-2">Most Popular</h6>
                            @if($stats['most_popular_product'] ?? false)
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <h5 class="mb-0 fw-bold text-warning">{{ $stats['most_popular'] }}</h5>
                                    </div>
                                    <span class="badge" style="background: rgba(255, 152, 0, 0.1); color: #FF9800;">
                                        {{ $stats['most_popular_product']->customer_count ?? 0 }} customers
                                    </span>
                                </div>
                            @else
                                <h5 class="mb-0 fw-bold text-warning">{{ $stats['most_popular'] ?? 'N/A' }}</h5>
                            @endif
                        </div>
                        <div class="avatar-lg rounded-circle d-flex align-items-center justify-content-center" 
                             style="background: rgba(255, 152, 0, 0.1); color: #FF9800;">
                            <i class="fas fa-fire fa-3x"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top mt-auto" style="border-color: rgba(255, 152, 0, 0.2) !important;">
                        <small class="text-muted">
                            <i class="fas fa-chart-line me-1 text-danger"></i>Top selling product
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 stat-card border-0 shadow-sm" style="background: linear-gradient(135deg, #e0f7fa 0%, #e1f5fe 100%);">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center flex-grow-1">
                        <div>
                            <h6 class="card-title text-muted mb-2">Active Customers</h6>
                            <h2 class="mb-0 display-6 fw-bold text-info">{{ $stats['active_customers'] ?? '0' }}</h2>
                        </div>
                        <div class="avatar-lg rounded-circle d-flex align-items-center justify-content-center" 
                             style="background: rgba(3, 169, 244, 0.1); color: #03A9F4;">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top mt-auto" style="border-color: rgba(3, 169, 244, 0.2) !important;">
                        <small class="text-muted">
                            <i class="fas fa-user-check me-1 text-primary"></i>Currently using products
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Price Statistics Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar-lg rounded-circle d-flex align-items-center justify-content-center" 
                                         style="background: linear-gradient(135deg, #fce4ec 0%, #f3e5f5 100%); color: #E91E63;">
                                        <i class="fas fa-money-bill-wave fa-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Average Price</h6>
                                    <h4 class="mb-0 fw-bold text-danger">
                                        ৳{{ number_format($stats['average_price'] ?? 0, 2) }}
                                    </h4>
                                    <small class="text-muted">Average monthly price across all products</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar-lg rounded-circle d-flex align-items-center justify-content-center" 
                                         style="background: linear-gradient(135deg, #f3e5f5 0%, #e8eaf6 100%); color: #9C27B0;">
                                        <i class="fas fa-arrow-up fa-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Highest Price</h6>
                                    <h4 class="mb-0 fw-bold" style="color: #9C27B0;">
                                        ৳{{ number_format($products->max('monthly_price') ?? 0, 2) }}
                                    </h4>
                                    <small class="text-muted">Most expensive product</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar-lg rounded-circle d-flex align-items-center justify-content-center" 
                                         style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); color: #4CAF50;">
                                        <i class="fas fa-arrow-down fa-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Lowest Price</h6>
                                    <h4 class="mb-0 fw-bold text-success">
                                        ৳{{ number_format($products->min('monthly_price') ?? 0, 2) }}
                                    </h4>
                                    <small class="text-muted">Most affordable product</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2 text-primary"></i>All Products
            </h5>

            <div class="d-flex gap-2 align-items-center">
                <div class="input-group input-group-sm" style="min-width: 250px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 search-box" placeholder="Search products...">
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80" class="ps-4">ID</th>
                            <th>Product Name</th>
                            <th>Product Type</th>
                            <th>Description</th>
                            <th width="130" class="text-end">Price</th>
                            <th width="140" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr data-type="{{ $product->product_type_id }}" id="product-row-{{ $product->p_id }}">
                            <td class="ps-4 fw-bold text-primary">{{ $product->p_id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $productType = strtolower($product->type->name ?? 'unknown');
                                        // Icon mapping based on product type
                                        $productIcon = match($productType) {
                                            'basic' => 'bolt',
                                            'standard' => 'wifi',
                                            'premium' => 'gem',
                                            'business' => 'building',
                                            'enterprise' => 'network-wired',
                                            'corporate' => 'briefcase',
                                            'residential' => 'home',
                                            'commercial' => 'store',
                                            'fiber' => 'broadcast-tower',
                                            'wireless' => 'satellite',
                                            default => 'cube'
                                        };
                                        // Color mapping
                                        $iconColor = match($productType) {
                                            'basic' => '#607D8B',
                                            'standard' => '#2196F3',
                                            'premium' => '#FF9800',
                                            'business' => '#9C27B0',
                                            'enterprise' => '#00BCD4',
                                            'corporate' => '#3F51B5',
                                            'residential' => '#4CAF50',
                                            'commercial' => '#795548',
                                            'fiber' => '#E91E63',
                                            'wireless' => '#FF5722',
                                            default => '#757575'
                                        };
                                    @endphp
                                    <div class="product-icon me-3" style="background: rgba(var(--color-rgb), 0.1);">
                                        <i class="fas fa-{{ $productIcon }}" style="color: {{ $iconColor }};"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                        <small class="text-muted">Created: {{ $product->created_at ? $product->created_at->format('M d, Y') : 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeName = $product->type->name ?? 'Unknown';
                                    // Badge color mapping
                                    $badgeColor = match(strtolower($typeName)) {
                                        'basic' => '#607D8B',
                                        'standard' => '#2196F3',
                                        'premium' => '#FF9800',
                                        'business' => '#9C27B0',
                                        'enterprise' => '#00BCD4',
                                        'corporate' => '#3F51B5',
                                        'residential' => '#4CAF50',
                                        'commercial' => '#795548',
                                        'fiber' => '#E91E63',
                                        'wireless' => '#FF5722',
                                        default => '#757575'
                                    };
                                @endphp
                                <span class="badge rounded-pill" style="background-color: {{ $badgeColor }}; color: white;">
                                    {{ ucfirst($typeName) }}
                                </span>
                            </td>
                            <td>
                                <p class="mb-1 text-muted" style="font-size: 0.9rem;">
                                    {{ \Illuminate\Support\Str::limit($product->description, 70) }}
                                </p>
                            </td>
                           
                            <td class="text-end">
                                <h6 class="text-success mb-0">
                                    <strong>৳{{ number_format($product->monthly_price, 2) }}</strong>
                                    <small class="text-muted d-block">per month</small>
                                </h6>
                            </td>
                            <td class="text-center action-column">
                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary border-end-0 edit-product-btn" 
                                            data-id="{{ $product->p_id }}" 
                                            data-name="{{ $product->name }}"
                                            title="Edit Product">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-md-inline ms-1">Edit</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger delete-product-btn" 
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
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-cubes fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Products Found</h5>
                                    <p class="text-muted mb-0">Create your first product to get started</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ $products->count() }} of {{ $stats['total_products'] ?? $products->count() }} products
                    </small>
                </div>
                <div>
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Updated {{ now()->format('h:i A') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="editProductForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="p_id" id="edit_p_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editProductModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Product
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="editProductModalBody">
                    <div class="text-center py-5" id="editLoading">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading product details...</p>
                    </div>

                    <div id="editErrors" class="alert alert-danger d-none"></div>

                    <div id="editFields" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Product Name *</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                                <small class="form-text text-muted">Enter a descriptive name for your product</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Product Type *</label>
                                <select name="product_type_id" id="edit_product_type_id" class="form-select" required>
                                    <option value="">Select Product Type</option>
                                    @foreach($productTypes as $type)
                                        <option value="{{ $type->id }}">{{ ucfirst($type->name) }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Choose the product category</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Monthly Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" name="monthly_price" id="edit_monthly_price" 
                                           class="form-control" step="0.01" min="0" required>
                                </div>
                                <small class="form-text text-muted">Enter monthly price in Taka</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Created Date</label>
                                <input type="text" class="form-control bg-light" id="edit_created_at" readonly>
                                <small class="form-text text-muted">Product creation date</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description *</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="4" required></textarea>
                            <small class="form-text text-muted">Describe the product features and benefits</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="updateProductBtn">
                        <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                        <i class="fas fa-save me-1"></i>Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                </div>
                <p class="text-center" id="deleteMessage">Are you sure you want to delete this product?</p>
                <input type="hidden" id="deleteProductId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                    <i class="fas fa-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* Product Icon */
    .product-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .product-icon:hover {
        transform: scale(1.1);
    }

    /* Statistics Cards */
    .stat-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .stat-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .stat-card .card-body > div:last-child {
        margin-top: auto;
    }    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
        opacity: 0.7;
    }
    
    .stat-card .card-title {
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #666;
    }
    
    .stat-card .avatar-lg {
        width: 70px;
        height: 70px;
        font-size: 2rem;
        border-radius: 50%;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .stat-card:hover .avatar-lg {
        transform: scale(1.1);
    }
    
    .stat-card h2, .stat-card h5 {
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    
    .stat-card .border-top {
        border-top: 1px solid rgba(0,0,0,0.05) !important;
        margin-top: auto;
    }
    
    /* Ensure consistent spacing */
    .stat-card .mt-3.pt-2 {
        margin-top: auto !important;
        padding-top: 1rem !important;
    }

    /* Table Styles */
    .table th {
        border-top: none;
        font-weight: 600;
        color: #2b2d42;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 12px;
        background-color: #f8f9fa;
    }

    .table td {
        vertical-align: middle;
        padding: 20px 12px;
        border-bottom: 1px solid #f1f3f6;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transform: translateX(2px);
    }

    .action-column {
        white-space: nowrap;
    }

    .action-column .btn-group {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .action-column .btn-group:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    
    .action-column .btn {
        border-radius: 0;
        padding: 8px 16px;
        transition: all 0.3s ease;
    }
    
    .action-column .btn:first-child {
        border-radius: 8px 0 0 8px;
    }
    
    .action-column .btn:last-child {
        border-radius: 0 8px 8px 0;
    }
    
    .action-column .btn:hover {
        transform: translateY(-1px);
    }

    /* Badge Styles */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        padding: 5px 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Price Statistics */
    .card .avatar-lg {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .card .avatar-lg:hover {
        transform: rotate(5deg) scale(1.05);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #f1f3f6;
        }
        
        .action-column .btn span.d-none {
            display: none !important;
        }
        
        .action-column .btn-group {
            display: flex;
            width: 100%;
        }
        
        .action-column .btn {
            flex: 1;
            text-align: center;
            padding: 6px 8px;
        }
        
        .product-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .stat-card .avatar-lg {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
    }

    @media (max-width: 576px) {
        .card-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch !important;
        }
        
        .card-header .input-group {
            width: 100% !important;
        }
        
        .search-box {
            flex: 1;
        }
        
        .stat-card .card-body {
            padding: 1.25rem;
        }
        
        .stat-card h2 {
            font-size: 1.5rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Product management page loaded');
        
        // Check if there's a success message in the URL (from redirect)
        const urlParams = new URLSearchParams(window.location.search);
        const successMessage = urlParams.get('success');
        if (successMessage) {
            showToast(decodeURIComponent(successMessage), 'success');
            
            // Remove the success parameter from URL without reloading
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
        
        // CSRF token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('CSRF Token:', csrfToken);
        
        // Toast notification function
        function showToast(message, type = 'success') {
            const toastId = 'toast-' + Date.now();
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            document.getElementById('toastContainer').appendChild(wrapper.firstElementChild);
            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        // EDIT PRODUCT FUNCTIONALITY
        document.querySelectorAll('.edit-product-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                console.log('Edit button clicked for product:', productId, productName);
                openEditModal(productId, productName);
            });
        });

        async function openEditModal(productId, productName) {
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
            
            // Reset modal state
            const loadingEl = document.getElementById('editLoading');
            const fieldsEl = document.getElementById('editFields');
            const errorsEl = document.getElementById('editErrors');
            
            loadingEl.style.display = '';
            fieldsEl.style.display = 'none';
            errorsEl.classList.add('d-none');
            
            try {
                const url = `{{ url('admin/products') }}/${productId}/edit`;
                console.log('Fetching product data from:', url);
                
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const product = await response.json();
                console.log('Product loaded:', product);
                
                // Populate form fields
                document.getElementById('edit_p_id').value = product.p_id;
                document.getElementById('edit_name').value = product.name;
                document.getElementById('edit_product_type_id').value = product.product_type_id;
                document.getElementById('edit_monthly_price').value = product.monthly_price;
                document.getElementById('edit_description').value = product.description;
                document.getElementById('edit_created_at').value = product.created_at ? 
                    new Date(product.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) : 'N/A';
                
                // Update modal title
                document.getElementById('editProductModalLabel').innerHTML = 
                    `<i class="fas fa-edit me-2"></i>Edit Product: ${product.name}`;
                
                // Show form fields
                loadingEl.style.display = 'none';
                fieldsEl.style.display = '';
                
            } catch (error) {
                console.error('Error loading product:', error);
                
                loadingEl.innerHTML = `
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-circle me-2"></i>Failed to load product details</h6>
                        <p class="mb-2">${error.message}</p>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                `;
                
                showToast('Failed to load product details', 'danger');
            }
        }

        // Handle edit form submission
        document.getElementById('editProductForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const productId = document.getElementById('edit_p_id')?.value;
            const submitBtn = document.getElementById('updateProductBtn');
            const spinner = submitBtn?.querySelector('.spinner-border');
            const errorsEl = document.getElementById('editErrors');
            
            if (!productId || !submitBtn) {
                console.error('Required elements not found');
                return;
            }
            
            submitBtn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            if (errorsEl) errorsEl.classList.add('d-none');
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch(`{{ url('admin/products') }}/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'));
                    if (modal) modal.hide();
                    showToast(data.message || 'Product updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    // Show validation errors
                    let errorHtml = '<ul class="mb-0 ps-3">';
                    if (data.errors) {
                        Object.values(data.errors).forEach(errors => {
                            errors.forEach(error => errorHtml += `<li>${error}</li>`);
                        });
                    } else {
                        errorHtml += `<li>${data.message || 'Update failed'}</li>`;
                    }
                    errorHtml += '</ul>';
                    
                    if (errorsEl) {
                        errorsEl.innerHTML = errorHtml;
                        errorsEl.classList.remove('d-none');
                    }
                }
            } catch (error) {
                console.error('Update error:', error);
                if (errorsEl) {
                    errorsEl.innerHTML = `<div><i class="fas fa-exclamation-circle me-2"></i>Network error: ${error.message}</div>`;
                    errorsEl.classList.remove('d-none');
                }
            } finally {
                submitBtn.disabled = false;
                if (spinner) spinner.classList.add('d-none');
            }
        });

        // DELETE PRODUCT FUNCTIONALITY
        document.querySelectorAll('.delete-product-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                console.log('Delete clicked for product:', productId, productName);
                
                // Set up delete confirmation modal
                document.getElementById('deleteMessage').innerHTML = 
                    `Are you sure you want to delete <strong>"${productName}"</strong>?<br>
                    <small class="text-danger">This action cannot be undone.</small>`;
                document.getElementById('deleteProductId').value = productId;
                
                // Show delete confirmation modal
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                deleteModal.show();
            });
        });

        // Handle delete confirmation
        document.getElementById('confirmDeleteBtn')?.addEventListener('click', async function() {
            const productId = document.getElementById('deleteProductId').value;
            const deleteBtn = this;
            const row = document.getElementById(`product-row-${productId}`);
            
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';
            
            try {
                const response = await fetch(`{{ url('admin/products') }}/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-HTTP-Method-Override': 'DELETE',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                console.log('Delete response:', data);
                
                if (data.success) {
                    // Close modal and remove row
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                    modal.hide();
                    
                    if (row) {
                        // Add fade out effect
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        
                        setTimeout(() => {
                            row.remove();
                            showToast(data.message || 'Product deleted successfully!', 'success');
                            
                            // Update product count
                            const visibleRows = document.querySelectorAll('tbody tr:not([style*="display: none"])').length;
                            const footerDiv = document.querySelector('.card-footer div');
                            if (footerDiv) {
                                footerDiv.innerHTML = `
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Showing ${visibleRows} of ${visibleRows} products
                                    </small>
                                `;
                            }
                            
                            if (visibleRows === 0) {
                                document.querySelector('tbody').innerHTML = `
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="fas fa-cubes fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No Products Found</h5>
                                                <p class="text-muted mb-0">Create your first product to get started</p>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            }
                        }, 300);
                    }
                } else {
                    showToast(data.message || 'Failed to delete product', 'danger');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('Network error occurred while deleting', 'danger');
            } finally {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
            }
        });

        // Search functionality
        const searchBox = document.querySelector('.search-box');
        if (searchBox) {
            searchBox.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                let visibleCount = 0;
                
                document.querySelectorAll('tbody tr').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const isVisible = text.includes(searchTerm);
                    row.style.display = isVisible ? '' : 'none';
                    
                    if (isVisible) visibleCount++;
                });
                
                // Update count in footer
                const footerDiv = document.querySelector('.card-footer div');
                if (footerDiv) {
                    footerDiv.innerHTML = `
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Showing ${visibleCount} of {{ $stats['total_products'] ?? $products->count() }} products
                        </small>
                    `;
                }
            });
        }

        // Export functionality
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                const rows = Array.from(document.querySelectorAll('table tbody tr:not([style*="display: none"])'));
                const csv = [];
                
                // Header row
                csv.push(['ID', 'Name', 'Type', 'Price', 'Description', 'Created Date'].join(','));
                
                // Data rows
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td');
                    if (cols.length >= 6) {
                        const rowData = [
                            `"${cols[0].textContent.trim()}"`,
                            `"${cols[1].querySelector('h6') ? cols[1].querySelector('h6').textContent.trim() : cols[1].textContent.trim()}"`,
                            `"${cols[2].textContent.trim()}"`,
                            `"${cols[4].querySelector('strong') ? cols[4].querySelector('strong').textContent.trim() : cols[4].textContent.trim().replace('/month','').replace('৳','').trim()}"`,
                            `"${cols[3].querySelector('p') ? cols[3].querySelector('p').textContent.trim() : cols[3].textContent.trim()}"`,
                            `"${cols[1].querySelector('small') ? cols[1].querySelector('small').textContent.replace('Created:','').trim() : ''}"`
                        ];
                        csv.push(rowData.join(','));
                    }
                });
                
                // Create and download CSV
                const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
                const a = document.createElement('a');
                a.setAttribute('href', csvContent);
                a.setAttribute('download', `products_export_${new Date().toISOString().split('T')[0]}.csv`);
                document.body.appendChild(a);
                a.click();
                a.remove();
                showToast('Export completed successfully!', 'success');
            });
        }

        // Add interactive effects to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                const icon = this.querySelector('.avatar-lg');
                if (icon) {
                    icon.style.transform = 'scale(1.1) rotate(5deg)';
                }
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                const icon = this.querySelector('.avatar-lg');
                if (icon) {
                    icon.style.transform = 'scale(1) rotate(0deg)';
                }
            });
        });

        console.log('Product management initialized');
    });
</script>