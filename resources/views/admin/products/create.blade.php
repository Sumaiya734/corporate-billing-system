@extends('layouts.admin')

@section('title', 'Create New Product - Admin Dashboard')

@section('content')

    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="toastContainer"></div>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-plus me-2 text-primary"></i>Create New Product
            </h2>
            <p class="text-muted mb-0">Add a new internet product to your offerings</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Products
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <!-- Create Product Form Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cube me-2"></i>Product Details
                    </h5>
                </div>
                <div class="card-body">
                    <form id="createProductForm">
                        @csrf
                        
                        <div id="createErrors" class="alert alert-danger d-none"></div>

                        <!-- Product Name -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Product Name *</label>
                            <input type="text" 
                                   name="name" 
                                   id="productName"
                                   class="form-control form-control-lg" 
                                   placeholder="e.g., Basic Plan, Premium Speed, Business Product" 
                                   required
                                   autofocus>
                            <div class="form-text">
                                Choose a descriptive name for your product. Must be unique.
                            </div>
                            <div id="nameDuplicateWarning" class="text-warning small mt-1 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="duplicateMessage"></span>
                            </div>
                        </div>

                        <!-- Product Type -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Product Type *</label>
                            <select name="product_type_id" id="productType" class="form-control form-control-lg" required>
                                <option value="">Select Product Type</option>
                                @foreach($productTypes as $type)
                                    <option value="{{ $type->id }}">{{ ucfirst($type->name) }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Select the category for this product.
                            </div>
                        </div>

                        <!-- Monthly Price -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Monthly Price (৳) *</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">৳</span>
                                <input type="number" 
                                       name="monthly_price" 
                                       id="monthlyPrice"
                                       class="form-control" 
                                       placeholder="0.00" 
                                       step="0.01" 
                                       min="0" 
                                       required>
                            </div>
                            <div class="form-text">
                                Enter the monthly subscription price in Bangladeshi Taka.
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Product Description *</label>
                            <textarea name="description" 
                                      id="productDescription"
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Describe the product features, speed, benefits, and any limitations..."
                                      required></textarea>
                            <div class="form-text">
                                Provide detailed information about what this product includes.
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Duplicate Prevention:</strong> The system will check for existing products with the same name.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="createProductBtn">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                <i class="fas fa-plus me-2"></i>Create Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('styles')
<style>
    .form-control-lg {
        padding: 12px 16px;
        font-size: 1.1rem;
    }
    
    .btn-lg {
        padding: 12px 24px;
        font-size: 1.1rem;
    }

    .feature-tag {
        display: inline-block;
        background: #e9ecef;
        padding: 4px 12px;
        margin: 2px;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    .product-preview-item {
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 15px;
        background: #f8f9fa;
    }

    .product-preview-item h6 {
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .is-duplicate {
        border-color: #ffc107 !important;
        background-color: #fffbf0;
    }

    .is-conflict {
        border-color: #fd7e14 !important;
        background-color: #fff4e6;
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
            showError('Security token missing. Please refresh the page.');
            return;
        }
        
        // Get form elements
        const form = document.getElementById('createProductForm');
        const submitBtn = document.getElementById('createProductBtn');
        const spinner = submitBtn.querySelector('.spinner-border');
        const errorDiv = document.getElementById('createErrors');
        
        if (!form) {
            console.error('Create product form not found!');
            return;
        }
        
        // Form submission handler
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('=== FORM SUBMISSION STARTED ===');
            
            // Reset previous errors
            errorDiv.classList.add('d-none');
            errorDiv.innerHTML = '';
            
            // Show loading state
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            
            try {
                // Get form data
                const formData = {
                    name: document.getElementById('productName').value.trim(),
                    product_type_id: document.getElementById('productType').value,
                    description: document.getElementById('productDescription').value.trim(),
                    monthly_price: document.getElementById('monthlyPrice').value,
                    _token: csrfToken
                };
                
                console.log('Form data to send:', formData);
                
                // Basic client-side validation
                if (!formData.name) {
                    throw new Error('Product name is required');
                }
                if (!formData.product_type_id) {
                    throw new Error('Please select a product type');
                }
                if (!formData.description) {
                    throw new Error('Product description is required');
                }
                if (!formData.monthly_price || parseFloat(formData.monthly_price) <= 0) {
                    throw new Error('Please enter a valid monthly price');
                }
                
                // Make API request
                const response = await fetch('{{ route("admin.products.store") }}', {
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
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));
                
                let result;
                try {
                    result = await response.json();
                    console.log('Response data:', result);
                } catch (jsonError) {
                    console.error('Failed to parse JSON response:', jsonError);
                    throw new Error('Server returned invalid response. Please try again.');
                }
                
                if (!response.ok) {
                    // Handle validation errors
                    if (result.errors) {
                        let errorHtml = '<ul class="mb-0">';
                        for (const [field, messages] of Object.entries(result.errors)) {
                            if (Array.isArray(messages)) {
                                messages.forEach(message => {
                                    errorHtml += `<li><strong>${field}:</strong> ${message}</li>`;
                                });
                            } else {
                                errorHtml += `<li><strong>${field}:</strong> ${messages}</li>`;
                            }
                        }
                        errorHtml += '</ul>';
                        errorDiv.innerHTML = errorHtml;
                        errorDiv.classList.remove('d-none');
                    } else if (result.message) {
                        errorDiv.innerHTML = result.message;
                        errorDiv.classList.remove('d-none');
                    } else {
                        errorDiv.innerHTML = 'An unexpected error occurred. Please try again.';
                        errorDiv.classList.remove('d-none');
                    }
                    return;
                }
                
                if (result.success) {
                    // Show success toast
                    showToast('success', result.message || 'Product created successfully!');
                    
                    // Show success message on page
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show shadow-sm mb-4';
                    successAlert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> ${result.message || 'Product created successfully!'}
                        <span class="d-block mt-2">Redirecting to product list... <span class="spinner-border spinner-border-sm ms-2" role="status"></span></span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    form.parentNode.insertBefore(successAlert, form);
                    
                    // Scroll to top to see the alert
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    // Reset form
                    form.reset();
                    
                    // Clear duplicate warnings
                    hideDuplicateWarnings();
                    
                    // Redirect after delay
                    setTimeout(() => {
                        window.location.href = result.redirect_url || '{{ route("admin.products.index") }}';
                    }, 2000);
                } else {
                    errorDiv.innerHTML = result.message || 'Failed to create product';
                    errorDiv.classList.remove('d-none');
                }
                
            } catch (error) {
                console.error('Submission error:', error);
                errorDiv.innerHTML = error.message || 'Network error occurred. Please try again.';
                errorDiv.classList.remove('d-none');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        });
        
        // Real-time duplicate checking
        document.getElementById('productName').addEventListener('input', checkForDuplicates);
        
        // Log form changes for debugging
        document.getElementById('productType').addEventListener('change', function() {
            console.log('Product type selected:', this.value);
        });
        
        document.getElementById('monthlyPrice').addEventListener('input', function() {
            console.log('Monthly price changed:', this.value);
        });
        
        document.getElementById('productName').addEventListener('input', function() {
            console.log('Product name changed:', this.value);
        });
    });
    
    // Toast function
    function showToast(type, message) {
        const toastContainer = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Initialize and show toast
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        } else {
            // Fallback if Bootstrap not loaded
            toast.classList.add('show');
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        // Remove toast after it hides
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }
    
    // Show error function
    function showError(message) {
        const errorDiv = document.getElementById('createErrors');
        if (errorDiv) {
            errorDiv.innerHTML = message;
            errorDiv.classList.remove('d-none');
        }
    }
    
    // Duplicate checking
    let duplicateCheckTimeout = null;
    
    function checkForDuplicates() {
        const productName = document.getElementById('productName').value.trim();
        const nameWarning = document.getElementById('nameDuplicateWarning');
        const duplicateMessage = document.getElementById('duplicateMessage');
        const productNameInput = document.getElementById('productName');
        
        if (productName.length < 2) {
            hideDuplicateWarnings();
            productNameInput.classList.remove('is-duplicate', 'is-conflict');
            return;
        }
        
        clearTimeout(duplicateCheckTimeout);
        duplicateCheckTimeout = setTimeout(async () => {
            try {
                const params = new URLSearchParams({
                    check_duplicate: productName
                });
                
                const response = await fetch(`{{ route('admin.products.index') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    handleDuplicateResponse(data);
                }
            } catch (error) {
                console.error('Duplicate check failed:', error);
            }
        }, 800);
    }
    
    function handleDuplicateResponse(data) {
        const nameWarning = document.getElementById('nameDuplicateWarning');
        const duplicateMessage = document.getElementById('duplicateMessage');
        const productNameInput = document.getElementById('productName');
        
        // Reset warnings
        hideDuplicateWarnings();
        productNameInput.classList.remove('is-duplicate', 'is-conflict');
        
        if (data.duplicates && data.duplicates.name_exact) {
            // Exact name match
            nameWarning.classList.remove('d-none');
            duplicateMessage.textContent = `A product with the exact name "${data.duplicates.name_exact.name}" already exists.`;
            productNameInput.classList.add('is-conflict');
        } else if (data.duplicates && data.duplicates.name_similar) {
            // Similar name match
            nameWarning.classList.remove('d-none');
            duplicateMessage.textContent = `A similar product "${data.duplicates.name_similar.name}" already exists.`;
            productNameInput.classList.add('is-duplicate');
        }
    }
    
    function hideDuplicateWarnings() {
        const nameWarning = document.getElementById('nameDuplicateWarning');
        if (nameWarning) {
            nameWarning.classList.add('d-none');
        }
    }
</script>
@endsection