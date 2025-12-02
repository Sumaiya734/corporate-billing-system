@extends('layouts.admin')

@section('title', 'Product Management - Admin Dashboard')

@section('content')
<div class="page-body p-4">
    <!-- Font Awesome Test -->
    <div style="display: none; position: fixed; top: 10px; right: 10px; z-index: 9999; background: white; padding: 10px; border-radius: 5px; border: 1px solid #ccc;" id="faTest">
        <i class="fa-solid fa-check" style="color: green;"></i> FA Loaded
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const test = document.getElementById('faTest');
                if (test) {
                    const computed = window.getComputedStyle(test.querySelector('i'));
                    const font = computed.fontFamily;
                    if (font.includes('Font Awesome')) {
                        console.log('✅ Font Awesome loaded:', font);
                        test.style.display = 'block';
                        setTimeout(() => test.style.display = 'none', 3000);
                    } else {
                        console.warn('❌ Font Awesome not detected');
                        test.innerHTML = '<i class="fas fa-times" style="color: red;"></i> FA NOT Loaded';
                        test.style.display = 'block';
                    }
                }
            }, 1000);
        });
    </script>

    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="toastContainer"></div>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <span class="text-primary me-2">📦</span>Product Management
            </h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="exportBtn">
                <span class="me-1">📥</span>Export
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <span class="me-1">➕</span>Create Product
            </a>
        </div>
    </div>

    <!-- Statistics Cards - Using EMOJI fallback -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-primary text-white mb-4 stat-card-clickable" data-filter="all" data-action="filter" role="button" title="Click to view all products">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-muted mb-2">Total Products</h6>
                            <h3 class="mb-0">{{ $stats['total_products'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle text-white d-flex align-items-center justify-content-center">
                            <span style="font-size: 1.5rem;">📦</span>
                        </div>
                    </div>
                    <p class="text-white mt-3 mb-0">
                        <span class="me-1">✅</span> All active products
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-success text-white mb-4 stat-card-clickable" data-action="show-types" role="button" title="Click to view all product types">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-muted mb-2">Total Types</h6>
                            <h3 class="mb-0">{{ $stats['total_types'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle text-white d-flex align-items-center justify-content-center">
                            <span style="font-size: 1.5rem;">🏷️</span>
                        </div>
                    </div>
                    <p class="text-white-muted mt-3 mb-0">
                        <span class="me-1">ℹ️</span> Product categories available
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-info text-white mb-4 stat-card-clickable" data-action="show-popular" data-product="{{ $stats['most_popular'] ?? '' }}" role="button" title="Click to view most popular product details">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-whitemuted mb-2">Most Popular</h6>
                            <h3 class="mb-0" style="font-size: 1.5rem;">{{ $stats['most_popular'] ?? 'N/A' }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle text-white d-flex align-items-center justify-content-center">
                            <span style="font-size: 1.5rem;">🔥</span>
                        </div>
                    </div>
                    <p class="text-white muted mt-3 mb-0">
                        <span class="me-1">📈</span> Top selling product
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-secondary text-white stat-card-clickable" data-action="show-customers" role="button" title="Click to view active customers">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-muted mb-2">Active Customers</h6>
                            <h3 class="mb-0">{{ $stats['active_customers'] ?? 'N/A' }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle text-white d-flex align-items-center justify-content-center">
                            <span style="font-size: 1.5rem;">👥</span>
                        </div>
                    </div>
                    <p class="text-white mt-3 mb-0">
                        <span class="me-1">⬆️</span> Using products
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <span class="me-2">📋</span>All Products
            </h5>

            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control form-control-sm search-box" placeholder="Search products..." style="min-width: 200px;">
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
                        <tr data-type="{{ $product->product_type }}" id="product-row-{{ $product->p_id }}">
                            <td class="fw-bold">{{ $product->p_id }}</td>
                            <td>
                                <span class="badge bg-secondary" style="font-size: 0.85rem; font-family: monospace;">
                                    PROD-{{ $product->product_code }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-icon me-3 {{ $product->isRegular() ? 'bg-primary' : 'bg-warning' }}">
                                        <span>{{ $product->isRegular() ? '📶' : '⭐' }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeName = $product->type->name ?? 'Unknown';
                                    $badgeClass = match(strtolower($typeName)) {
                                        'regular' => 'bg-primary',
                                        'special' => 'bg-warning text-dark',
                                        'premium' => 'bg-success',
                                        'enterprise' => 'bg-info',
                                        'custom' => 'bg-purple text-white',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($typeName) }}</span>
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
                                        <span>✏️</span>
                                        <span class="d-none d-md-inline ms-1">Edit</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger delete-product" 
                                            data-id="{{ $product->p_id }}" 
                                            data-name="{{ $product->name }}"
                                            title="Delete Product">
                                        <span>🗑️</span>
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
    /* Font Awesome backup styles */
    .fa-icon-fallback {
        font-family: "Segoe UI Emoji", "Apple Color Emoji", "Noto Color Emoji", sans-serif;
    }
    
    .product-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .bg-orange {
        background-color: #fd7e14 !important;
    }

    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-card-clickable {
        cursor: pointer;
    }

    .stat-card-clickable:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .stat-card-clickable.active {
        border: 2px solid #4361ee;
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .avatar-sm {
        width: 50px;
        height: 50px;
    }

    .filter-btn.active {
        background-color: #4361ee;
        color: white;
    }

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
                typeBreakdown += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${type.name.charAt(0).toUpperCase() + type.name.slice(1)}</h6>
                                <small class="text-muted">${count} products (${percentage}%)</small>
                            </div>
                            <div class="progress" style="width: 100px; height: 8px;">
                                <div class="progress-bar bg-primary" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    </div>
                `;
            });
            typeBreakdown += '</div>';
            
            showInfoModal('Product Types Breakdown', typeBreakdown);
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
            
            const content = `
                <div class="card border-0">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg bg-success rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <span style="font-size: 2rem;">🔥</span>
                            </div>
                            <h4>${product.name}</h4>
                            <span class="badge bg-${product.type?.name === 'regular' ? 'primary' : 'warning'}">${product.type?.name || 'N/A'}</span>
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
                        <span style="font-size: 2rem;">👥</span>
                    </div>
                    <h2 class="mb-2">${customerCount}</h2>
                    <p class="text-muted mb-4">Active customers currently using products</p>
                    <div class="alert alert-info">
                        <span class="me-2">ℹ️</span>
                        These customers have active product subscriptions
                    </div>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-primary">
                        <span class="me-2">👥</span>View All Customers
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
                editBtn.innerHTML = '<span class="me-1">⏳</span>';
                
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
                        <span class="me-2">⚠️</span>
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

    });
</script>
@endsection