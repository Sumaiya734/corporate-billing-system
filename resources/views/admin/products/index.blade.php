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
                <span class="text-primary me-2">📦</span>Product Management
            </h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="exportBtn">
                <span class="me-1">Export</span>
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <span class="me-1">+</span>Create Product
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-primary text-white mb-4" role="button">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white mb-2">Total Products</h6>
                            <h3 class="mb-0 counter">{{ $stats['total_products'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle text-white d-flex align-items-center justify-content-center">
                            <span style="font-size: 1.5rem;">📦</span>
                        </div>
                    </div>
                    <p class="text-white-50 mt-3 mb-0">
                        <span class="me-1">✅</span> All active products
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white mb-2">Total Types</h6>
                            <h3 class="mb-0 counter">{{ $stats['total_types'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle text-white d-flex align-items-center justify-content-center">
                            <span style="font-size: 1.5rem;">🏷️</span>
                        </div>
                    </div>
                    <p class="text-white-50 mt-3 mb-0">
                        <span class="me-1">ℹ️</span> Product categories available
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white mb-2">Most Popular</h6>
                            <h3 class="mb-0 counter" style="font-size: 1.5rem;">{{ $stats['most_popular'] ?? 'N/A' }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle text-white d-flex align-items-center justify-content-center">
                            <span style="font-size: 1.5rem;">🔥</span>
                        </div>
                    </div>
                    <p class="text-white-50 mt-3 mb-0">
                        <span class="me-1">📈</span> Top selling product
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-secondary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white mb-2">Active Customers</h6>
                            <h3 class="mb-0 counter">{{ $stats['active_customers'] ?? 'N/A' }}</h3>
                        </div>
                        <div class="avatar-sm rounded-circle text-white d-flex align-items-center justify-content-center">
                            <span style="font-size: 1.5rem;">👥</span>
                        </div>
                    </div>
                    <p class="text-white-50 mt-3 mb-0">
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
                        <tr data-type="{{ $product->product_type_id }}" id="product-row-{{ $product->p_id }}">
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
                                    <button type="button" class="btn btn-outline-primary edit-product-btn" 
                                            data-id="{{ $product->p_id }}" 
                                            data-name="{{ $product->name }}"
                                            title="Edit Product">
                                        <span>✏️</span>
                                        <span class="d-none d-md-inline ms-1">Edit</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger delete-product-btn" 
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

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editProductForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="p_id" id="edit_p_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="editProductModalBody">
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
                    <button type="submit" class="btn btn-primary" id="updateProductBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Simple Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">Are you sure you want to delete this product?</p>
                <input type="hidden" id="deleteProductId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

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
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    /* Statistics Cards */
    .stat-card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .stat-card .card-title {
        font-size: 0.875rem;
        font-weight: 500;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }
    
    .stat-card .avatar-sm {
        width: 3rem;
        height: 3rem;
        font-size: 1.5rem;
    }
    
    .stat-card h3 {
        font-weight: 600;
        font-size: 1.5rem;
    }
    
    .stat-card p {
        font-size: 0.875rem;
        margin-bottom: 0;
    }
    
    .stat-card .counter {
        transition: all 0.3s ease-in-out;
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

    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }

        .action-column .btn span.d-none {
            display: none !important;
        }
    }

    @media (max-width: 576px) {
        .card-header {
            flex-direction: column;
            gap: 10px;
        }

        .search-box {
            width: 100% !important;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Product management page loaded');
        
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
                        <div class="toast-body">${message}</div>
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

        // Debug function to test AJAX call
        async function testAjaxCall(url, method = 'GET') {
            console.log(`Testing ${method} request to: ${url}`);
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                console.log(`Response status: ${response.status}`);
                console.log(`Response headers:`, Object.fromEntries(response.headers.entries()));
                const data = await response.json();
                console.log(`Response data:`, data);
                return { success: true, data: data, status: response.status };
            } catch (error) {
                console.error(`AJAX error:`, error);
                return { success: false, error: error };
            }
        }

        // EDIT PRODUCT FUNCTIONALITY
        document.querySelectorAll('.edit-product-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                console.log('=== EDIT BUTTON CLICKED ===');
                console.log('Product ID:', productId);
                console.log('Product Name:', productName);
                
                // Test the route first
                console.log('Testing edit route...');
                const editRoute = `/admin/products/${productId}/edit`;
                const testResult = await testAjaxCall(editRoute);
                
                if (!testResult.success) {
                    console.log('Edit route failed, trying show route...');
                    const showRoute = `/admin/products/${productId}`;
                    await testAjaxCall(showRoute);
                }
                
                // Now open the modal
                openEditModal(productId, productName);
            });
        });

        async function openEditModal(productId, productName) {
            console.log('=== OPENING EDIT MODAL ===');
            console.log('Product ID:', productId);
            
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
                // First try the edit route
                let url = `/admin/products/${productId}/edit`;
                console.log('Fetching from edit route:', url);
                
                let response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                // If edit route fails, try the show route
                if (!response.ok) {
                    console.log(`Edit route failed with status ${response.status}, trying show route...`);
                    url = `/admin/products/${productId}`;
                    console.log('Fetching from show route:', url);
                    
                    response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                }
                
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error text:', errorText);
                    throw new Error(`Failed to fetch product data: ${response.status}`);
                }
                
                const product = await response.json();
                console.log('Product data received:', product);
                
                // Check if we got valid product data
                if (!product) {
                    throw new Error('No product data received');
                }
                
                // Populate form fields
                document.getElementById('edit_p_id').value = product.p_id || productId;
                document.getElementById('edit_product_code').value = product.product_code || '';
                document.getElementById('edit_name').value = product.name || '';
                document.getElementById('edit_product_type_id').value = product.product_type_id || '';
                document.getElementById('edit_monthly_price').value = product.monthly_price || '';
                document.getElementById('edit_description').value = product.description || '';
                
                // Update modal title
                document.getElementById('editProductModalLabel').textContent = `Edit Product: ${product.name || productName}`;
                
                // Show form fields
                loadingEl.style.display = 'none';
                fieldsEl.style.display = '';
                
                console.log('Modal populated successfully');
                
            } catch (error) {
                console.error('Error loading product:', error);
                
                // Show error in modal
                loadingEl.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>Failed to load product details</h6>
                        <p>Error: ${error.message}</p>
                        <p>Please try the following:</p>
                        <ol>
                            <li>Check if the product exists</li>
                            <li>Check your network connection</li>
                            <li>Refresh the page and try again</li>
                        </ol>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                `;
                
                showToast('Failed to load product details. Check console for details.', 'danger');
            }
        }

        // Handle edit form submission
        document.getElementById('editProductForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('=== FORM SUBMISSION STARTED ===');
            
            const productId = document.getElementById('edit_p_id').value;
            const submitBtn = document.getElementById('updateProductBtn');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            document.getElementById('editErrors').classList.add('d-none');
            
            const formData = new FormData(this);
            const url = `/admin/products/${productId}`;
            
            console.log('Updating product at:', url);
            console.log('Form data:', Object.fromEntries(formData.entries()));
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Update response:', data);
                
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'));
                    modal.hide();
                    showToast(data.message || 'Product updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    // Show validation errors
                    if (data.errors) {
                        let errorHtml = '';
                        Object.values(data.errors).forEach(errors => {
                            errors.forEach(error => {
                                errorHtml += `<div>• ${error}</div>`;
                            });
                        });
                        document.getElementById('editErrors').innerHTML = errorHtml;
                        document.getElementById('editErrors').classList.remove('d-none');
                    } else {
                        document.getElementById('editErrors').innerHTML = `<div>• ${data.message || 'Update failed'}</div>`;
                        document.getElementById('editErrors').classList.remove('d-none');
                    }
                }
            } catch (error) {
                console.error('Update error:', error);
                document.getElementById('editErrors').innerHTML = `<div>• Network error occurred: ${error.message}</div>`;
                document.getElementById('editErrors').classList.remove('d-none');
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                console.log('=== FORM SUBMISSION COMPLETED ===');
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
                const response = await fetch(`/admin/products/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                console.log('Delete response:', data);
                
                if (data.success) {
                    // Close modal and remove row
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                    modal.hide();
                    
                    if (row) {
                        row.remove();
                        showToast(data.message || 'Product deleted successfully!', 'success');
                        
                        // Update product count
                        const visibleRows = document.querySelectorAll('tbody tr').length;
                        const footerDiv = document.querySelector('.card-footer div');
                        if (footerDiv) {
                            footerDiv.textContent = `Showing ${visibleRows} of ${visibleRows} products`;
                        }
                        
                        if (visibleRows === 0) {
                            document.querySelector('tbody').innerHTML = 
                                '<tr><td colspan="7" class="text-center py-4">No products found.</td></tr>';
                        }
                    }
                } else {
                    showToast(data.message || 'Failed to delete product', 'danger');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('Network error occurred while deleting', 'danger');
            } finally {
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Delete';
            }
        });

        // Search functionality
        const searchBox = document.querySelector('.search-box');
        if (searchBox) {
            searchBox.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                document.querySelectorAll('tbody tr').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        // Export functionality
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
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
        }

        console.log('Product management initialized');
        
        // Test the routes on page load
        setTimeout(async () => {
            console.log('=== TESTING ROUTES ===');
            
            // Test edit route with first product
            const firstProductId = document.querySelector('.edit-product-btn')?.getAttribute('data-id');
            if (firstProductId) {
                console.log('Testing routes for product ID:', firstProductId);
                
                const editRoute = `/admin/products/${firstProductId}/edit`;
                console.log('Testing edit route:', editRoute);
                await testAjaxCall(editRoute);
                
                const showRoute = `/admin/products/${firstProductId}`;
                console.log('Testing show route:', showRoute);
                await testAjaxCall(showRoute);
            }
        }, 1000);
    });
</script>
@endsection