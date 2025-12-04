@extends('layouts.admin')

@section('title', 'Add New Customer')

@section('content')

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 text-dark fw-bold mb-1">
                <i class="fas fa-user-plus me-2 text-primary"></i>Add New Customer
            </h1>
            <p class="text-muted mb-0">Register a new customer with complete profile and service details.</p>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-light border">
            <i class="fas fa-arrow-left me-1"></i>Back to Customers
        </a>
    </div>

    <!-- Customer Form -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0 text-primary">
                <i class="fas fa-user-circle me-2"></i>Customer Registration Form
            </h2>
        </div>
        <div class="card-body p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-start" role="alert">
                    <i class="fas fa-check-circle fa-lg me-2 mt-1"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle fa-lg me-2 mt-1"></i>
                        <div>
                            <strong>Please correct the following errors:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data" id="customerForm">
                @csrf

                <!-- Profile & ID Images -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <i class="fas fa-user me-1 text-muted"></i>
                                Profile Picture <span class="text-danger ms-1">*</span>
                            </label>
                            <div class="border rounded-3 p-3 bg-light position-relative" id="profilePreviewContainer">
                                <div class="text-center py-4" id="profilePlaceholder">
                                    <i class="fas fa-user-circle fa-3x text-secondary mb-2"></i>
                                    <p class="text-muted small mb-0">No image selected</p>
                                </div>
                                <img id="profilePreview" class="img-fluid d-none" alt="Profile Preview">
                                <div class="mt-2 text-start">
                                    <small class="text-muted">
                                        JPG, PNG, or GIF • Max 2MB
                                    </small>
                                </div>
                            </div>
                            <input type="file" class="form-control mt-2 @error('profile_image') is-invalid @enderror"
                                   id="profile_image" name="profile_image" accept="image/*"
                                   onchange="previewImage(this, 'profilePreview', 'profilePlaceholder', 'profileFileName')">
                            <div class="mt-1" id="profileFileName" style="min-height: 1.2rem;"></div>
                            @error('profile_image')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <i class="fas fa-id-card me-1 text-muted"></i>
                                ID Document <span class="text-danger ms-1">*</span>
                            </label>
                            <div class="border rounded-3 p-3 bg-light position-relative" id="idPreviewContainer">
                                <div class="text-center py-4" id="idPlaceholder">
                                    <i class="fas fa-file-image fa-3x text-secondary mb-2"></i>
                                    <p class="text-muted small mb-0">No document selected</p>
                                </div>
                                <img id="idPreview" class="img-fluid d-none" alt="ID Preview">
                                <div class="mt-2 text-start">
                                    <small class="text-muted">
                                        Clear image of NID/Passport • Max 3MB
                                    </small>
                                </div>
                            </div>
                            <input type="file" class="form-control mt-2 @error('id_image') is-invalid @enderror"
                                   id="id_image" name="id_image" accept="image/*"
                                   onchange="previewImage(this, 'idPreview', 'idPlaceholder', 'idFileName')">
                            <div class="mt-1" id="idFileName" style="min-height: 1.2rem;"></div>
                            @error('id_image')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Basic & Contact Info -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" required placeholder="e.g. Md. Imran Hossain">
                            @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" placeholder="customer@example.com">
                            @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   name="phone" value="{{ old('phone') }}" required placeholder="+8801XXXXXXXXX">
                            @error('phone')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Customer ID</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('customer_id') is-invalid @enderror"
                                       name="customer_id" value="{{ old('customer_id') }}" 
                                       placeholder="C-25-0001 (Auto-generated)" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="generateCustomerId()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Auto-generated from name/phone. Click refresh to regenerate.</small>
                            @error('customer_id')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Residential Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      name="address" rows="3" required 
                                      placeholder="House #, Road, Area, City">{{ old('address') }}</textarea>
                            @error('address')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Connection Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('connection_address') is-invalid @enderror" 
                                      name="connection_address" rows="3" required 
                                      placeholder="Where service will be installed">{{ old('connection_address') }}</textarea>
                            @error('connection_address')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Identity & Account -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ID Type</label>
                        <select class="form-select @error('id_type') is-invalid @enderror" name="id_type">
                            <option value="">Select ID Type</option>
                            <option value="NID" {{ old('id_type') == 'NID' ? 'selected' : '' }}>National ID (NID)</option>
                            <option value="Passport" {{ old('id_type') == 'Passport' ? 'selected' : '' }}>Passport</option>
                            <option value="Driving License" {{ old('id_type') == 'Driving License' ? 'selected' : '' }}>Driving License</option>
                            <option value="Birth Certificate" {{ old('id_type') == 'Birth Certificate' ? 'selected' : '' }}>Birth Certificate</option>
                        </select>
                        @error('id_type')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">ID Number</label>
                            <input type="text" class="form-control @error('id_number') is-invalid @enderror"
                                   name="id_number" value="{{ old('id_number') }}" 
                                   placeholder="e.g. 1987 1234 5678">
                            @error('id_number')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mb-4 p-3 bg-light rounded-2">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_active">
                        Activate Account Immediately
                    </label>
                    <p class="text-muted small mb-0 mt-1">
                        Customer will be able to access services right after registration.
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="d-flex flex-wrap gap-2 justify-content-between pt-3 border-top mt-4">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-light">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">
                            <i class="fas fa-eraser me-1"></i> Clear
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i> Create Customer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>


<style>
.form-label {
    font-size: 0.95rem;
    color: #374151;
}
.form-control, .form-select {
    border-color: #d1d5db;
    padding: 0.625rem 0.875rem;
}
.form-control:focus, .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.card {
    border: 1px solid #e5e7eb;
}
.card-header {
    background-color: #fff;
    border-bottom: 1px solid #e5e7eb;
}
.btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
}
.btn-primary {
    background-color: #2563eb;
    border-color: #2563eb;
}
.btn-primary:hover {
    background-color: #1d4ed8;
    border-color: #1d4ed8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image Preview with filename
    window.previewImage = function(input, imgId, placeholderId, fileNameId) {
        const file = input.files[0];
        const preview = document.getElementById(imgId);
        const placeholder = document.getElementById(placeholderId);
        const fileNameDiv = document.getElementById(fileNameId);
        
        if (file) {
            const maxSize = input.id === 'profile_image' ? 2 * 1024 * 1024 : 3 * 1024 * 1024;
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            
            if (file.size > maxSize) {
                alert(`File too large. Max size: ${maxSize === 2097152 ? '2' : '3'}MB.`);
                input.value = '';
                fileNameDiv.textContent = '';
                return;
            }
            
            if (!validTypes.includes(file.type)) {
                alert('Invalid file type. Only JPG, PNG, GIF allowed.');
                input.value = '';
                fileNameDiv.textContent = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = e => preview.src = e.target.result;
            reader.readAsDataURL(file);
            
            preview.classList.remove('d-none');
            placeholder.classList.add('d-none');
            fileNameDiv.textContent = `Selected: ${file.name}`;
        } else {
            preview.classList.add('d-none');
            placeholder.classList.remove('d-none');
            fileNameDiv.textContent = '';
        }
    };

    // Generate Customer ID
    window.generateCustomerId = async function() {
        const btn = document.querySelector('button[onclick="generateCustomerId()"]');
        const input = document.querySelector('[name="customer_id"]');
        const originalIcon = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        try {
            const res = await fetch('{{ route("admin.customers.next-id") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await res.json();
            const year = new Date().getFullYear().toString().slice(-2);
            input.value = `C-${year}-${data.next_number.toString().padStart(4, '0')}`;
        } catch (e) {
            input.value = 'C-' + new Date().getFullYear().toString().slice(-2) + '-' + Math.floor(Math.random()*9000+1000);
        } finally {
            btn.innerHTML = originalIcon;
            btn.disabled = false;
        }
    };

    // Clear Form
    window.clearForm = function() {
        if (confirm('Clear all entered data?')) {
            document.getElementById('customerForm').reset();
            ['profile', 'id'].forEach(type => {
                document.getElementById(`${type}Preview`).classList.add('d-none');
                document.getElementById(`${type}Placeholder`).classList.remove('d-none');
                document.getElementById(`${type}FileName`).textContent = '';
            });
        }
    };
});
</script>
@endsection