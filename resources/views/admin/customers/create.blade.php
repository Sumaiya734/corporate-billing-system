@extends('layouts.admin')

@section('title', 'Add New Customer')

@section('content')
<div class="p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-dark">
                <i class="fas fa-user-plus me-2 text-primary"></i>Add New Customer
            </h2>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Customers
        </a>
    </div>

    <!-- Customer Form -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 text-primary">
                <i class="fas fa-user-circle me-2"></i>Customer Information
            </h5>
        </div>
        <div class="card-body p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Basic Information Section -->
                <div class="form-section mb-4">
                    <h6 class="section-header mb-3">
                        <i class="fas fa-user me-2"></i>Basic Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label required">Full Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required 
                                       placeholder="Enter full name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="form-section mb-4">
                    <h6 class="section-header mb-3">
                        <i class="fas fa-phone me-2"></i>Contact Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label required">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" required 
                                       placeholder="Enter phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Customer ID</label>
                                <input type="text" class="form-control @error('customer_id') is-invalid @enderror" 
                                       id="customer_id" name="customer_id" value="{{ old('customer_id') }}" 
                                       placeholder="Auto-generated if left blank">
                                <small class="text-muted">Leave blank to auto-generate customer ID</small>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address" class="form-label required"> Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="3" required 
                                          placeholder="Enter residential address">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- <div class="mb-3">
                                <label for="connection_address" class="form-label required">Connection Address</label>
                                <textarea class="form-control @error('connection_address') is-invalid @enderror" 
                                          id="connection_address" name="connection_address" rows="3" required 
                                          placeholder="Enter connection installation address">{{ old('connection_address') }}</textarea>
                                @error('connection_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> -->
                        </div>
                    </div>
                </div>

                <!-- Images Section -->
                <div class="form-section mb-4">
                    <h6 class="section-header mb-3">
                        <i class="fas fa-camera me-2"></i>Images
                    </h6>
                    <div class="row">
                        <!-- Profile Picture -->
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-user-circle me-2 text-muted"></i>Profile Picture
                                    </h6>
                                    <div class="mb-3">
                                        <label for="profile_picture" class="form-label">Upload Profile Picture</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-image text-muted"></i>
                                            </span>
                                            <input type="file" 
                                                   class="form-control @error('profile_picture') is-invalid @enderror" 
                                                   id="profile_picture" 
                                                   name="profile_picture" 
                                                   accept="image/*">
                                        </div>
                                        @error('profile_picture')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Max file size: 10MB | Allowed formats: JPG, PNG, GIF
                                        </small>                                    </div>
                                    <!-- Profile Preview -->
                                    <div id="profilePreviewContainer" style="display: none;">
                                        <label class="form-label">Preview:</label>
                                        <div class="border rounded p-2 text-center">
                                            <img id="profilePreviewImage" 
                                                 src="" 
                                                 alt="Profile Preview" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 150px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ID Card Front -->
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-id-card me-2 text-muted"></i>ID Card Front
                                    </h6>
                                    <div class="mb-3">
                                        <label for="id_card_front" class="form-label">Upload Front Side</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-image text-muted"></i>
                                            </span>
                                            <input type="file" 
                                                   class="form-control @error('id_card_front') is-invalid @enderror" 
                                                   id="id_card_front" 
                                                   name="id_card_front" 
                                                   accept="image/*">
                                        </div>
                                        @error('id_card_front')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Front Preview -->
                                    <div id="frontPreviewContainer" style="display: none;">
                                        <label class="form-label">Preview:</label>
                                        <div class="border rounded p-2 text-center">
                                            <img id="frontPreviewImage" 
                                                 src="" 
                                                 alt="Front Preview" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 150px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ID Card Back -->
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-id-card me-2 text-muted"></i>ID Card Back
                                    </h6>
                                    <div class="mb-3">
                                        <label for="id_card_back" class="form-label">Upload Back Side</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-image text-muted"></i>
                                            </span>
                                            <input type="file" 
                                                   class="form-control @error('id_card_back') is-invalid @enderror" 
                                                   id="id_card_back" 
                                                   name="id_card_back" 
                                                   accept="image/*">
                                        </div>
                                        @error('id_card_back')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Back Preview -->
                                    <div id="backPreviewContainer" style="display: none;">
                                        <label class="form-label">Preview:</label>
                                        <div class="border rounded p-2 text-center">
                                            <img id="backPreviewImage" 
                                                 src="" 
                                                 alt="Back Preview" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 150px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Identity Information Section -->
                <div class="form-section mb-4">
                    <h6 class="section-header mb-3">
                        <i class="fas fa-id-card me-2"></i>Identity Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_type" class="form-label">ID Type</label>
                                <select class="form-select @error('id_type') is-invalid @enderror" 
                                        id="id_type" name="id_type">
                                    <option value="">Select ID Type (Optional)</option>
                                    <option value="NID" {{ old('id_type') == 'NID' ? 'selected' : '' }}>National ID (NID)</option>
                                    <option value="Passport" {{ old('id_type') == 'Passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="Driving License" {{ old('id_type') == 'Driving License' ? 'selected' : '' }}>Driving License</option>
                                </select>
                                <small class="text-muted">This field is optional</small>
                                @error('id_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_number" class="form-label">ID Number</label>
                                <input type="text" class="form-control @error('id_number') is-invalid @enderror" 
                                       id="id_number" name="id_number" value="{{ old('id_number') }}" 
                                       placeholder="Enter ID number">
                                <small class="text-muted">This field is optional</small>
                                @error('id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="form-section mb-4">
                    <h6 class="section-header mb-3">
                        <i class="fas fa-cog me-2"></i>Account Settings
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="account-status-card">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <span class="status-label">Active Customer</span>
                                        <small class="status-description">Customer account will be active immediately</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-1"></i>Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 2rem;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.section-header {
    color: #2c3e50;
    font-weight: 600;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #3498db;
    background: linear-gradient(135deg, #3498db, #2980b9);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.required::after {
    content: " *";
    color: #e74c3c;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

/* Account Status Card */
.account-status-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
}

.form-check-input:checked {
    background-color: #27ae60;
    border-color: #27ae60;
}

.status-label {
    font-weight: 600;
    color: #2c3e50;
    display: block;
}

.status-description {
    color: #7f8c8d;
    display: block;
    margin-top: 0.25rem;
}

/* Buttons */
.btn {
    border-radius: 8px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-lg {
    padding: 0.875rem 2.5rem;
    font-size: 1.1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-outline-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-section {
        padding: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate customer ID in format: C-YY-XXXX
    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    const customerIdInput = document.getElementById('customer_id');

    async function generateCustomerId() {
        if ((nameInput.value || phoneInput.value) && !customerIdInput.value) {
            try {
                // Get current year's last 2 digits
                const year = new Date().getFullYear().toString().slice(-2);
                
                // Fetch the next available customer number from server
                const response = await fetch('{{ route("admin.customers.next-id") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    customerIdInput.value = `C-${year}-${data.next_number}`;
                } else {
                    // Fallback to random number if server request fails
                    const randomNum = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                    customerIdInput.value = `C-${year}-${randomNum}`;
                }
            } catch (error) {
                console.error('Error generating customer ID:', error);
                // Fallback to random number
                const year = new Date().getFullYear().toString().slice(-2);
                const randomNum = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                customerIdInput.value = `C-${year}-${randomNum}`;
            }
        }
    }

    nameInput.addEventListener('blur', generateCustomerId);
    phoneInput.addEventListener('blur', generateCustomerId);

    // Form validation enhancement
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });

    // Real-time validation
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
        });
    });

    // Image preview functionality
    const profilePictureInput = document.getElementById('profile_picture');
    const idCardFrontInput = document.getElementById('id_card_front');
    const idCardBackInput = document.getElementById('id_card_back');
    
    const profilePreviewContainer = document.getElementById('profilePreviewContainer');
    const frontPreviewContainer = document.getElementById('frontPreviewContainer');
    const backPreviewContainer = document.getElementById('backPreviewContainer');
    
    const profilePreviewImage = document.getElementById('profilePreviewImage');
    const frontPreviewImage = document.getElementById('frontPreviewImage');
    const backPreviewImage = document.getElementById('backPreviewImage');

    // Profile picture preview
    profilePictureInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreviewImage.src = e.target.result;
                profilePreviewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            profilePreviewContainer.style.display = 'none';
        }
    });

    // ID Card Front preview
    idCardFrontInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                frontPreviewImage.src = e.target.result;
                frontPreviewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            frontPreviewContainer.style.display = 'none';
        }
    });

    // ID Card Back preview
    idCardBackInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                backPreviewImage.src = e.target.result;
                backPreviewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            backPreviewContainer.style.display = 'none';
        }
    });
});
</script>
@endsection