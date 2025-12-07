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

                        <!-- Product ID -->
                        <!-- <div class="mb-4">
                            <label class="form-label fw-semibold">Product ID</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">PROD-</span>
                                <input type="text" 
                                       name="product_code" 
                                       id="productCode"
                                       class="form-control" 
                                       placeholder="Auto-generated"
                                       readonly
                                       style="background-color: #f8f9fa;">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateProductCode()" title="Generate New ID">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Unique 4-digit product identifier (auto-generated)
                            </div>
                        </div> -->

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
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Describe the product features, speed, benefits, and any limitations..."
                                      required></textarea>
                            <div class="form-text">
                                Provide detailed information about what this product includes.
                            </div>
                        </div>


                        <!-- Features (Optional) -->
                        <!-- <div class="mb-4">
                            <label class="form-label fw-semibold">Key Features (Optional)</label>
                            <div class="features-container">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control feature-input" placeholder="e.g., 100 Mbps Speed">
                                    <button type="button" class="btn btn-outline-primary add-feature-btn">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-text">
                                Add key features that make this product attractive to customers.
                            </div>
                        </div> -->

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


            <!-- Product Preview -->
            <!-- <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>Product Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="productPreview" class="text-muted text-center py-4">
                        <i class="fas fa-cube fa-3x mb-3"></i>
                        <p>Your product preview will appear here as you fill out the form.</p>
                    </div>
                </div>
            </div> -->

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

    .feature-tag .remove-feature {
        cursor: pointer;
        margin-left: 8px;
        color: #6c757d;
    }

    .feature-tag .remove-feature:hover {
        color: #dc3545;
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
    // Generate unique 4-digit product code
    function generateProductCode() {
        const code = Math.floor(1000 + Math.random() * 9000);
        document.getElementById('productCode').value = code;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate product code on page load
        generateProductCode();
        
        // CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Toast helper with proper styling
        function showToast(message, type = 'success') {
            const toastId = 'toast-' + Date.now();
            
            // Map type to Bootstrap color classes
            const typeMap = {
                'success': 'success',
                'error': 'danger',
                'danger': 'danger',
                'warning': 'warning',
                'info': 'info'
            };
            
            const bootstrapType = typeMap[type] || 'success';
            const iconMap = {
                'success': 'fa-check-circle',
                'danger': 'fa-exclamation-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            
            const icon = iconMap[type] || 'fa-check-circle';
            
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-bg-${bootstrapType} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas ${icon} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            const container = document.getElementById('toastContainer');
            container.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 3000
            });
            
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
                errorHtml = errors;
            } else if (errors.message) {
                errorHtml = errors.message;
            } else if (errors.errors) {
                errorHtml = Object.values(errors.errors).flat().map(e => `<div>• ${e}</div>`).join('');
            } else {
                errorHtml = Object.values(errors).flat().map(e => `<div>• ${e}</div>`).join('');
            }
            
            containerEl.innerHTML = errorHtml;
        }

        // Clear validation errors
        function clearValidationErrors() {
            const errorsEl = document.getElementById('createErrors');
            if (errorsEl) {
                errorsEl.classList.add('d-none');
                errorsEl.innerHTML = '';
            }
        }

        // Real-time duplicate checking for products (NAME ONLY)
        let duplicateCheckTimeout;
        
        function checkForDuplicates() {
            const productName = document.getElementById('productName').value.trim();
            
            if (productName.length < 2) {
                hideDuplicateWarnings();
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
            document.getElementById('nameDuplicateWarning').classList.add('d-none');
        }

        // Event listeners for real-time duplicate checking (NAME ONLY)
        document.getElementById('productName').addEventListener('input', checkForDuplicates);

        // Form submission
        document.getElementById('createProductForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const btn = document.getElementById('createProductBtn');
            const spinner = btn.querySelector('.spinner-border');
            
            // Disable button and show loading
            btn.disabled = true;
            spinner.classList.remove('d-none');
            clearValidationErrors();

            const formData = new FormData(form);
            
            try {
                const response = await fetch('{{ route("admin.products.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const jsonResponse = await response.json();

                if (!response.ok) {
                    showValidationErrors(document.getElementById('createErrors'), 
                        jsonResponse.errors || jsonResponse.message || 'Failed to create product');
                    return;
                }

                if (jsonResponse.success) {
                    // Show success toast
                    showToast(jsonResponse.message || 'Product created successfully!', 'success');
                    
                    // Show success alert on the page with enhanced styling
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show shadow-sm';
                    successAlert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> ${jsonResponse.message || 'Product created successfully!'}
                        <span class="d-block mt-2">Redirecting to product list... <span class="spinner-border spinner-border-sm ms-2" role="status"></span></span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    form.insertBefore(successAlert, form.firstChild);
                    
                    // Scroll to top to see the alert
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    // Reset form
                    form.reset();
                    generateProductCode();
                    hideDuplicateWarnings();
                    
                    // Redirect to products list after delay
                    setTimeout(() => {
                        const redirectUrl = jsonResponse.redirect_url || '{{ route("admin.products.index") }}';
                        window.location.assign(redirectUrl);
                    }, 1500);
                } else {
                    showValidationErrors(document.getElementById('createErrors'), 
                        jsonResponse.message || 'Unknown error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                showValidationErrors(document.getElementById('createErrors'), 
                    'Network error occurred. Please try again.');
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        });
    });
</script>
@endsection

