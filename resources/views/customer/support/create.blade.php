@extends('layouts.customer')

@section('title', 'Create Support Ticket - Nanosoft')

@section('content')
<div class="create-ticket-page">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-2">
                    <i class="fas fa-plus-circle me-2 text-primary"></i>Create Support Ticket
                </h1>
                <p class="text-muted mb-0">Submit a new support request. Our team will respond within 24 hours.</p>
            </div>
            <div>
                <a href="{{ route('customer.support.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Tickets
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-ticket-alt me-2 text-primary"></i>Ticket Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('customer.support.store') }}" method="POST">
                        @csrf

                        <!-- Subject -->
                        <div class="mb-4">
                            <label for="subject" class="form-label fw-bold">
                                Subject <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg @error('subject') is-invalid @enderror"
                                   id="subject"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   placeholder="Brief description of your issue"
                                   required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category and Priority Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="category" class="form-label fw-bold">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg @error('category') is-invalid @enderror"
                                        id="category"
                                        name="category"
                                        required>
                                    <option value="">Select a category</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="priority" class="form-label fw-bold">
                                    Priority <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg @error('priority') is-invalid @enderror"
                                        id="priority"
                                        name="priority"
                                        required>
                                    <option value="">Select priority</option>
                                    @foreach($priorities as $key => $label)
                                        <option value="{{ $key }}" {{ old('priority') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Related Product (if applicable) -->
                        @if($customerProducts->count() > 0)
                        <div class="mb-4">
                            <label for="product_id" class="form-label fw-bold">
                                Related Product (Optional)
                            </label>
                            <select class="form-select form-select-lg @error('product_id') is-invalid @enderror"
                                    id="product_id"
                                    name="product_id">
                                <option value="">Not related to a specific product</option>
                                @foreach($customerProducts as $product)
                                    <option value="{{ $product->cp_id }}" {{ old('product_id') == $product->cp_id ? 'selected' : '' }}>
                                        {{ $product->product->name }} ({{ $product->product->product_type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="8"
                                      placeholder="Please provide detailed information about your issue. Include any error messages, steps to reproduce, and what you were trying to accomplish."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">
                                    Be as specific as possible. Include error messages, screenshots if applicable, and steps to reproduce the issue.
                                </small>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('customer.support.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-1"></i>Submit Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Tips Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Tips for Better Support
                    </h6>
                </div>
                <div class="card-body">
                    <div class="tips-list">
                        <div class="tip-item d-flex align-items-start mb-3">
                            <div class="tip-icon bg-soft-primary rounded-2 p-2 me-3">
                                <i class="fas fa-search text-primary"></i>
                            </div>
                            <div>
                                <strong>Check FAQ First</strong>
                                <br>
                                <small class="text-muted">Many common questions are answered in our FAQ section.</small>
                            </div>
                        </div>

                        <div class="tip-item d-flex align-items-start mb-3">
                            <div class="tip-icon bg-soft-success rounded-2 p-2 me-3">
                                <i class="fas fa-exclamation-triangle text-success"></i>
                            </div>
                            <div>
                                <strong>Be Specific</strong>
                                <br>
                                <small class="text-muted">Include exact error messages and steps to reproduce issues.</small>
                            </div>
                        </div>

                        <div class="tip-item d-flex align-items-start mb-3">
                            <div class="tip-icon bg-soft-info rounded-2 p-2 me-3">
                                <i class="fas fa-images text-info"></i>
                            </div>
                            <div>
                                <strong>Add Screenshots</strong>
                                <br>
                                <small class="text-muted">Visual aids help us understand and resolve issues faster.</small>
                            </div>
                        </div>

                        <div class="tip-item d-flex align-items-start">
                            <div class="tip-icon bg-soft-warning rounded-2 p-2 me-3">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div>
                                <strong>Response Time</strong>
                                <br>
                                <small class="text-muted">We typically respond within 4-8 business hours.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-phone me-2 text-success"></i>Need Immediate Help?
                    </h6>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="contact-item d-flex align-items-center mb-3">
                            <div class="contact-icon bg-soft-primary rounded-2 p-2 me-3">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email Support</small>
                                <strong>support@nanosoft.com</strong>
                            </div>
                        </div>

                        <div class="contact-item d-flex align-items-center mb-3">
                            <div class="contact-icon bg-soft-success rounded-2 p-2 me-3">
                                <i class="fas fa-phone text-success"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Phone Support</small>
                                <strong>+880 XXXX-XXXXXX</strong>
                            </div>
                        </div>

                        <div class="contact-item d-flex align-items-center">
                            <div class="contact-icon bg-soft-warning rounded-2 p-2 me-3">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Business Hours</small>
                                <strong>9:00 AM - 6:00 PM (Sat-Thu)</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .create-ticket-page {
        animation: fadeIn 0.6s ease-out;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #3A7BD5;
        box-shadow: 0 0 0 0.2rem rgba(58, 123, 213, 0.25);
    }

    .form-control-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }

    .form-select-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }

    .tip-item {
        padding: 0.75rem;
        background-color: #f8fafc;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
    }

    .tip-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-soft-primary { background-color: rgba(58, 123, 213, 0.1); }
    .bg-soft-success { background-color: rgba(34, 197, 94, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
    .bg-soft-info { background-color: rgba(6, 182, 212, 0.1); }

    .contact-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3A7BD5 0%, #00d2ff 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(58, 123, 213, 0.4);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .page-header .d-flex {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start !important;
        }

        .tips-list .tip-item {
            margin-bottom: 1rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-resize textarea
        const textarea = document.getElementById('description');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // Form validation enhancement
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    // Scroll to first invalid field
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalid.focus();
                    }
                }
            });
        }

        // Category change handler (could add dynamic content based on category)
        const categorySelect = document.getElementById('category');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                // Could add category-specific help text or fields here
                console.log('Category changed to:', this.value);
            });
        }
    });
</script>
@endsection
