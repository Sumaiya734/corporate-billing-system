@extends('layouts.admin')

@section('title', 'Edit Customer - NetBill BD')

@section('content')
<div class="p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1 text-dark">
                <i class="fas fa-user-edit me-2 text-primary"></i>Edit Customer
            </h2>
            <p class="text-muted mb-0">Update customer information and details</p>
        </div>
        <div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Customers
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2 fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Customer Information Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 d-flex align-items-center">
                <i class="fas fa-user-circle me-2 text-primary"></i>Customer Information
                <span class="badge bg-primary ms-2">{{ $customer->customer_id }}</span>
                <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }} ms-1">
                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                </span>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.customers.update', $customer->c_id) }}" id="editCustomerForm">
                @csrf
                @method('PUT')

                <!-- Customer ID Display -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info border-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-id-card fa-lg me-3"></i>
                                <div>
                                    <strong>Customer ID:</strong> 
                                    <span class="fw-bold text-dark">{{ $customer->customer_id }}</span>
                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </h6>
                    </div>

                    <!-- Name Field -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-signature me-1 text-muted"></i>Full Name
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $customer->name) }}" 
                                   placeholder="Enter customer's full name" 
                                   required>
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Previous: <span class="fw-semibold">{{ $customer->name }}</span></small>
                    </div>

                    <!-- Email Field -->
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1 text-muted"></i>Email Address
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-at text-muted"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $customer->email) }}" 
                                   placeholder="Enter email address" 
                                   required>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Previous: <span class="fw-semibold">{{ $customer->email ?? 'Not set' }}</span></small>
                    </div>

                    <!-- Phone Field -->
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone me-1 text-muted"></i>Phone Number
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-mobile-alt text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $customer->phone) }}" 
                                   placeholder="Enter phone number" 
                                   required>
                        </div>
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Previous: <span class="fw-semibold">{{ $customer->phone ?? 'Not set' }}</span></small>
                    </div>

                    <!-- Status Field -->
                    <div class="col-md-6 mb-3">
                        <label for="is_active" class="form-label">
                            <i class="fas fa-toggle-on me-1 text-muted"></i>Account Status
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-circle text-{{ $customer->is_active ? 'success' : 'secondary' }}"></i>
                            </span>
                            <select class="form-select @error('is_active') is-invalid @enderror" 
                                    id="is_active" 
                                    name="is_active">
                                <option value="1" {{ old('is_active', $customer->is_active) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !old('is_active', $customer->is_active) ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        @error('is_active')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Current status: 
                            <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </small>
                    </div>
                </div>

                <!-- Address Information Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-map-marker-alt me-2"></i>Address Information
                        </h6>
                    </div>

                    <!-- Address Field -->
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">
                            <i class="fas fa-home me-1 text-muted"></i>Primary Address
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-map-pin text-muted"></i>
                            </span>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3" 
                                      placeholder="Enter primary address" 
                                      required>{{ old('address', $customer->address) }}</textarea>
                        </div>
                        @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Previous: <span class="fw-semibold">{{ $customer->address ?? 'Not set' }}</span></small>
                    </div>

                    <!-- Connection Address Field -->
                    <div class="col-12 mb-3">
                        <label for="connection_address" class="form-label">
                            <i class="fas fa-network-wired me-1 text-muted"></i>Connection Address
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-wifi text-muted"></i>
                            </span>
                            <textarea class="form-control @error('connection_address') is-invalid @enderror" 
                                      id="connection_address" 
                                      name="connection_address" 
                                      rows="3" 
                                      placeholder="Enter connection address (optional)">{{ old('connection_address', $customer->connection_address) }}</textarea>
                        </div>
                        @error('connection_address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Previous: <span class="fw-semibold">{{ $customer->connection_address ?? 'Not set' }}</span></small>
                    </div>
                </div>

                <!-- Identification Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-id-card me-2"></i>Identification Details
                        </h6>
                    </div>

                    <!-- ID Type Field -->
                    <div class="col-md-6 mb-3">
                        <label for="id_type" class="form-label">
                            <i class="fas fa-passport me-1 text-muted"></i>ID Type
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-id-card text-muted"></i>
                            </span>
                            <select class="form-select @error('id_type') is-invalid @enderror" 
                                    id="id_type" 
                                    name="id_type">
                                <option value="">Select ID Type</option>
                                <option value="NID" {{ old('id_type', $customer->id_type) == 'NID' ? 'selected' : '' }}>National ID (NID)</option>
                                <option value="Passport" {{ old('id_type', $customer->id_type) == 'Passport' ? 'selected' : '' }}>Passport</option>
                                <option value="Driving License" {{ old('id_type', $customer->id_type) == 'Driving License' ? 'selected' : '' }}>Driving License</option>
                            </select>
                        </div>
                        @error('id_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Previous: <span class="fw-semibold">{{ $customer->id_type ?? 'Not set' }}</span></small>
                    </div>

                    <!-- ID Number Field -->
                    <div class="col-md-6 mb-3">
                        <label for="id_number" class="form-label">
                            <i class="fas fa-hashtag me-1 text-muted"></i>ID Number
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-barcode text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('id_number') is-invalid @enderror" 
                                   id="id_number" 
                                   name="id_number" 
                                   value="{{ old('id_number', $customer->id_number) }}" 
                                   placeholder="Enter ID number">
                        </div>
                        @error('id_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Previous: <span class="fw-semibold">{{ $customer->id_number ?? 'Not set' }}</span></small>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.customers.show', $customer->c_id) }}" 
                                   class="btn btn-outline-info me-2">
                                    <i class="fas fa-eye me-2"></i>View Profile
                                </a>
                                <a href="{{ route('admin.customers.index') }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                            <div>
                                <button type="reset" class="btn btn-outline-warning me-2">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Update Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editCustomerForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    });
});
</script>
@endsection