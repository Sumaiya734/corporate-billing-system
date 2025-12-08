@extends('layouts.admin')

@section('title', 'Product Types - Admin Dashboard')

@section('content')

    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="toastContainer"></div>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-tags me-2 text-primary"></i>Product Types
            </h2>
            <p class="text-muted mt-2">Manage product types that categorize your products.</p>
        </div>
        <div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Create Product Type Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Create New Product Type
                    </h5>
                </div>
                <div class="card-body">
                    <form id="createProductTypeForm" method="POST" action="{{ route('admin.products.add-type') }}">
                        @csrf
                        
                        <div id="createErrors" class="alert alert-danger d-none"></div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Product Type Name *</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control form-control-lg" 
                                   placeholder="Enter product type (e.g., premium, business, basic)" 
                                   required
                                   autofocus>
                            <div class="form-text">
                                This will be used as the product type when creating new products.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="descriptions" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Enter a description for this product type (optional)"></textarea>
                            <div class="form-text">
                                Provide a brief description of this product type.
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>How it works:</strong> Product types categorize your products. You can assign products to these types when creating or editing them.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="createProductTypeBtn">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                <i class="fas fa-plus me-2"></i>Add Product Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Existing Product Types -->
            @if($productTypes->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Existing Product Types
                        <span class="badge bg-primary ms-2">{{ $productTypes->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($productTypes as $type)
                        <div class="col-md-12 mb-3" data-id="{{ $type->id }}">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                <div class="d-flex align-items-center">
                                    <div class="product-type-icon me-3 {{ $type->name === 'regular' ? 'bg-primary' : ($type->name === 'special' ? 'bg-warning' : 'bg-success') }}">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-capitalize">{{ $type->name }}</h6>
                                        @if($type->descriptions)
                                            <small class="text-muted d-block">{{ $type->descriptions }}</small>
                                        @endif
                                        <small class="text-muted">{{ $productCounts[$type->name] ?? 0 }} products</small>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-danger delete-product-type" 
                                        data-id="{{ $type->id }}" 
                                        data-name="{{ $type->name }}"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Product Types Found</h5>
                    <p class="text-muted">Start by adding your first product type above.</p>
                </div>
            </div>
            @endif
        </div>
    </div>


@endsection

@section('styles')
<style>
    .product-type-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .form-control-lg {
        padding: 12px 16px;
        font-size: 1.1rem;
    }
    
    .btn-lg {
        padding: 12px 24px;
        font-size: 1.1rem;
    }

    .border-rounded {
        border-radius: 12px;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Test if showDeleteModal function is available
        if (typeof showDeleteModal !== 'function') {
            console.error('showDeleteModal function is not available. Make sure the delete confirmation modal is properly included.');
        }
        
        // CSRF token with error handling
        let csrfToken = null;
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            csrfToken = csrfMeta.getAttribute('content');
        } else {
            console.error('CSRF meta tag not found');
        }

        // Toast helper with color coding
        function showToast(message, type = 'success') {
            // Map types to Bootstrap color classes
            const typeMap = {
                'success': 'success',
                'error': 'danger',
                'danger': 'danger',
                'warning': 'warning',
                'info': 'info'
            };
            
            const bootstrapType = typeMap[type] || 'success';
            const toastId = 'toast-' + Date.now();
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div id="${toastId}" class="toast align-items-center text-bg-${bootstrapType} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${type === 'success' ? '<i class="fas fa-check-circle me-2"></i>' : ''}
                            ${type === 'error' || type === 'danger' ? '<i class="fas fa-exclamation-circle me-2"></i>' : ''}
                            ${type === 'warning' ? '<i class="fas fa-exclamation-triangle me-2"></i>' : ''}
                            ${type === 'info' ? '<i class="fas fa-info-circle me-2"></i>' : ''}
                            ${message}
                        </div>
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
            
            let errorHtml = '';
            if (typeof errors === 'string') {
                errorHtml = errors;
            } else if (errors.message) {
                errorHtml = errors.message;
            } else if (errors.errors) {
                // Laravel validation errors
                errorHtml = Object.values(errors.errors).flat().map(e => `<div>• ${e}</div>`).join('');
            } else {
                // Other error formats
                errorHtml = Object.values(errors).flat().map(e => `<div>• ${e}</div>`).join('');
            }
            
            containerEl.innerHTML = errorHtml;
        }

        // CREATE: submit form
        const createForm = document.getElementById('createProductTypeForm');
        if (createForm) {
            createForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                console.log('product type form submit event triggered');
                
                const form = e.target;
                const btn = document.getElementById('createProductTypeBtn');
                const spinner = btn?.querySelector('.spinner-border');
                const errorContainer = document.getElementById('createErrors');
                
                // Disable button and show spinner
                if (btn) btn.disabled = true;
                if (spinner) spinner.classList.remove('d-none');
                if (errorContainer) errorContainer.classList.add('d-none');

                const formData = new FormData(form);
                const productName = formData.get('name');
                const productDescriptions = formData.get('descriptions');
                
                console.log('product type name:', productName);
                console.log('product type descriptions:', productDescriptions);

                try {
                    const response = await fetch('{{ route("admin.products.add-type") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            name: productName,
                            descriptions: productDescriptions,
                            _token: csrfToken
                        })
                    });

                    console.log('Response status:', response.status);
                    const jsonResponse = await response.json();
                    console.log('Response JSON:', jsonResponse);

                    if (!response.ok) {
                        const errorMessage = jsonResponse.errors ? 
                            Object.values(jsonResponse.errors).flat().join(', ') : 
                            jsonResponse.message || 'Failed to add product type';
                        
                        showValidationErrors(errorContainer, errorMessage);
                        showToast('Error: ' + errorMessage, 'error');
                        return;
                    }

                    if (jsonResponse.success) {
                        showToast(jsonResponse.message || 'Product type added successfully!', 'success');
                        form.reset();
                        
                        // Reload the page to show the new product type
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        const errorMessage = jsonResponse.message || 'Unknown error occurred';
                        showValidationErrors(errorContainer, errorMessage);
                        showToast('Error: ' + errorMessage, 'error');
                    }
                } catch (error) {
                    console.error('Network error:', error);
                    const errorMessage = 'Network error occurred: ' + error.message;
                    showValidationErrors(errorContainer, errorMessage);
                    showToast('Error: ' + errorMessage, 'error');
                } finally {
                    // Re-enable button and hide spinner
                    if (btn) btn.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                }
            });
        } else {
            console.error('product type form not found');
        }

        // DELETE: product type with modal confirmation
        document.body.addEventListener('click', function(e) {
            const delBtn = e.target.closest('.delete-product-type');
            if (!delBtn) return;
            
            const typeId = delBtn.getAttribute('data-id');
            const typeName = delBtn.getAttribute('data-name');
            
            const message = `Are you sure you want to delete the product type <strong>"${typeName}"</strong>?<br><small class="text-danger">This action cannot be undone.</small>`;
            const action = `/admin/products/delete-type/${typeId}`;
            const row = delBtn.closest('.col-md-12');
            
            showDeleteModal(message, action, row);
        });

    });
</script>

<!-- Include Delete Confirmation Modal -->
<x-delete-confirmation-modal />

@endsection