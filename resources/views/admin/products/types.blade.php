@extends('layouts.admin')

@section('title', 'Product Types - Admin Dashboard')

@section('content')

    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="toastContainer"></div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deleteModalBody">
                    <!-- Dynamic content will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
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
                    <form id="createProductTypeForm">
                        @csrf
                        
                        <div id="createErrors" class="alert alert-danger d-none"></div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Product Type Name *</label>
                            <input type="text" 
                                   name="name" 
                                   id="productTypeName"
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
                                      id="productTypeDescription"
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
                    <div class="row" id="productTypesContainer">
                        @foreach($productTypes as $type)
                        <div class="col-md-12 mb-3" id="productType-{{ $type->id }}">
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
                                <button class="btn btn-sm btn-outline-danger delete-product-type-btn" 
                                        data-id="{{ $type->id }}" 
                                        data-name="{{ $type->name }}"
                                        data-product-count="{{ $productCounts[$type->name] ?? 0 }}"
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
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            console.error('CSRF token not found!');
            showToast('Security token missing. Please refresh the page.', 'error');
            return;
        }
        
        // Variables for delete modal
        let deleteTypeId = null;
        let deleteTypeElement = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        
        // Toast function
        function showToast(message, type = 'success') {
            const typeClasses = {
                'success': 'bg-success text-white',
                'error': 'bg-danger text-white',
                'warning': 'bg-warning text-dark',
                'info': 'bg-info text-white'
            };
            
            const toastClass = typeClasses[type] || typeClasses.success;
            const toastId = 'toast-' + Date.now();
            const wrapper = document.createElement('div');
            
            wrapper.innerHTML = `
                <div id="${toastId}" class="toast ${toastClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                          type === 'error' ? 'exclamation-circle' : 
                                          type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-${type === 'warning' ? '' : 'white'} me-2 m-auto" 
                                data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            document.getElementById('toastContainer').appendChild(wrapper.firstElementChild);
            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
            
            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        }
        
        // Show validation errors
        function showValidationErrors(containerEl, errors) {
            if (!containerEl) return;
            
            containerEl.classList.remove('d-none');
            let errorHtml = '';
            
            if (typeof errors === 'string') {
                errorHtml = `<div>• ${errors}</div>`;
            } else if (errors.message) {
                errorHtml = `<div>• ${errors.message}</div>`;
            } else if (errors.errors) {
                // Laravel validation errors
                for (const [field, messages] of Object.entries(errors.errors)) {
                    if (Array.isArray(messages)) {
                        messages.forEach(message => {
                            errorHtml += `<div>• <strong>${field}:</strong> ${message}</div>`;
                        });
                    }
                }
            } else if (Array.isArray(errors)) {
                errors.forEach(error => {
                    errorHtml += `<div>• ${error}</div>`;
                });
            }
            
            containerEl.innerHTML = errorHtml;
        }
        
        // CREATE: Product Type Form Submission
        const createForm = document.getElementById('createProductTypeForm');
        if (createForm) {
            createForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                console.log('Product type form submission started');
                
                const btn = document.getElementById('createProductTypeBtn');
                const spinner = btn?.querySelector('.spinner-border');
                const errorContainer = document.getElementById('createErrors');
                
                // Show loading state
                if (btn) btn.disabled = true;
                if (spinner) spinner.classList.remove('d-none');
                if (errorContainer) errorContainer.classList.add('d-none');
                
                try {
                    // Get form data
                    const formData = {
                        name: document.getElementById('productTypeName').value.trim(),
                        descriptions: document.getElementById('productTypeDescription').value.trim(),
                        _token: csrfToken
                    };
                    
                    console.log('Form data:', formData);
                    
                    // Client-side validation
                    if (!formData.name) {
                        throw new Error('Product type name is required');
                    }
                    
                    // Make API request
                    const response = await fetch('{{ route("admin.products.add-type") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    console.log('Response status:', response.status);
                    
                    const result = await response.json();
                    console.log('Response data:', result);
                    
                    if (!response.ok) {
                        // Handle validation errors
                        if (result.errors) {
                            showValidationErrors(errorContainer, result.errors);
                        } else if (result.message) {
                            showValidationErrors(errorContainer, result.message);
                        }
                        return;
                    }
                    
                    if (result.success) {
                        // Show success message
                        showToast(result.message || 'Product type added successfully!', 'success');
                        
                        // Reset form
                        createForm.reset();
                        
                        // Reload page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showValidationErrors(errorContainer, result.message || 'Failed to add product type');
                    }
                    
                } catch (error) {
                    console.error('Submission error:', error);
                    showValidationErrors(errorContainer, error.message || 'Network error occurred');
                } finally {
                    // Reset button state
                    if (btn) btn.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                }
            });
        }
        
        // DELETE: Setup delete button handlers
        document.querySelectorAll('.delete-product-type-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                deleteTypeId = this.getAttribute('data-id');
                const typeName = this.getAttribute('data-name');
                const productCount = parseInt(this.getAttribute('data-product-count')) || 0;
                deleteTypeElement = document.getElementById(`productType-${deleteTypeId}`);
                
                console.log('Delete clicked:', { deleteTypeId, typeName, productCount });
                
                // Prepare modal content
                let modalContent = '';
                
                if (productCount > 0) {
                    modalContent = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning!</strong> This product type has ${productCount} associated product(s).
                        </div>
                        <p>Are you sure you want to delete the product type <strong>"${typeName}"</strong>?</p>
                        <p class="text-danger"><small>This will also remove this type from all associated products.</small></p>
                    `;
                } else {
                    modalContent = `
                        <p>Are you sure you want to delete the product type <strong>"${typeName}"</strong>?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    `;
                }
                
                // Update modal content
                document.getElementById('deleteModalBody').innerHTML = modalContent;
                
                // Show modal
                deleteModal.show();
            });
        });
        
        // DELETE: Confirm button handler
        document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
            if (!deleteTypeId) return;
            
            console.log('Confirm delete for ID:', deleteTypeId);
            
            const confirmBtn = this;
            const originalText = confirmBtn.innerHTML;
            
            // Show loading state
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                Deleting...
            `;
            
            try {
                const response = await fetch(`/admin/products/delete-type/${deleteTypeId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                console.log('Delete response status:', response.status);
                
                const result = await response.json();
                console.log('Delete response data:', result);
                
                if (response.ok && result.success) {
                    // Show success message
                    showToast(result.message || 'Product type deleted successfully!', 'success');
                    
                    // Remove element from DOM
                    if (deleteTypeElement) {
                        deleteTypeElement.remove();
                    }
                    
                    // Hide modal
                    deleteModal.hide();
                    
                    // Check if no product types left
                    const remainingTypes = document.querySelectorAll('#productTypesContainer .col-md-12').length;
                    if (remainingTypes === 0) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                    
                } else {
                    // Show error message
                    const errorMessage = result.message || 'Failed to delete product type';
                    showToast(errorMessage, 'error');
                    
                    // Reset button state
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = originalText;
                }
                
            } catch (error) {
                console.error('Delete error:', error);
                showToast('Network error occurred. Please try again.', 'error');
                
                // Reset button state
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            } finally {
                // Reset variables
                deleteTypeId = null;
                deleteTypeElement = null;
            }
        });
        
        // Reset delete variables when modal is closed
        document.getElementById('deleteConfirmationModal').addEventListener('hidden.bs.modal', function() {
            deleteTypeId = null;
            deleteTypeElement = null;
            
            // Reset confirm button
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = `
                <i class="fas fa-trash me-1"></i>Delete
            `;
        });
        
        // Log for debugging
        console.log('Product Types page loaded successfully');
        console.log('CSRF Token available:', !!csrfToken);
        console.log('Delete buttons found:', document.querySelectorAll('.delete-product-type-btn').length);
        
    });
</script>
@endsection