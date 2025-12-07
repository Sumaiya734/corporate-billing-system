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
            <form method="POST" action="{{ route('admin.customers.update', $customer->c_id) }}" id="editCustomerForm" enctype="multipart/form-data">
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

                <!-- Profile Picture Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-camera me-2"></i>Profile Picture
                        </h6>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <!-- Current Profile Picture -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    @if($customer->profile_picture)
                                        <img src="{{ asset('storage/' . $customer->profile_picture) }}" 
                                             alt="{{ $customer->name }}" 
                                             class="img-thumbnail rounded-circle img-lightbox-trigger" 
                                             style="width: 150px; height: 150px; object-fit: cover;"
                                             data-full-src="{{ asset('storage/' . $customer->profile_picture) }}"
                                             data-caption="{{ $customer->name }} - Profile Picture">
                                    @else
                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" 
                                             style="width: 150px; height: 150px; border: 2px dashed #dee2e6;">
                                            <i class="fas fa-user fa-4x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-muted mb-0">Current Profile Picture</p>
                                @if($customer->profile_picture)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info mt-2 img-lightbox-btn"
                                            data-full-src="{{ asset('storage/' . $customer->profile_picture) }}"
                                            data-caption="{{ $customer->name }} - Profile Picture">
                                        <i class="fas fa-expand me-1"></i>View Full Image
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8 mb-3">
                        <!-- Profile Picture Upload -->
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">
                                <i class="fas fa-image me-1 text-muted"></i>Upload New Profile Picture
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-upload text-muted"></i>
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
                            </small>
                        </div>
                        
                        <!-- Image Preview -->
                        <div class="mb-3">
                            <label class="form-label">Image Preview</label>
                            <div id="profilePreview" class="border rounded p-3 text-center" style="display: none;">
                                <img id="profilePreviewImage" 
                                     src="" 
                                     alt="Preview" 
                                     class="img-fluid rounded" 
                                     style="max-height: 200px;">
                                <p class="text-muted mt-2 mb-0">Selected image preview</p>
                            </div>
                            <div id="noProfilePreview" class="border rounded p-5 text-center">
                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No image selected</p>
                            </div>
                        </div>
                        
                        <!-- Remove Profile Picture Option -->
                        @if($customer->profile_picture)
                            <div class="form-check mb-3">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="remove_profile_picture" 
                                       name="remove_profile_picture" 
                                       value="1">
                                <label class="form-check-label text-danger" for="remove_profile_picture">
                                    <i class="fas fa-trash-alt me-1"></i>Remove current profile picture
                                </label>
                            </div>
                        @endif
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

                <!-- ID Card Upload Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-file-upload me-2"></i>ID Card Upload
                        </h6>
                    </div>
                    
                    <!-- Current ID Card -->
                    @if($customer->id_card_front || $customer->id_card_back)
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-id-card me-2 text-muted"></i>Current ID Cards
                                    </h6>
                                    
                                    @if($customer->id_card_front)
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Front Side</label>
                                            <div class="border rounded p-2 text-center">
                                                <img src="{{ asset('storage/' . $customer->id_card_front) }}" 
                                                     alt="ID Card Front" 
                                                     class="img-fluid rounded img-lightbox-trigger" 
                                                     style="max-height: 150px;"
                                                     data-full-src="{{ asset('storage/' . $customer->id_card_front) }}"
                                                     data-caption="ID Card - Front Side">
                                                <div class="mt-2">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info img-lightbox-btn me-1"
                                                            data-full-src="{{ asset('storage/' . $customer->id_card_front) }}"
                                                            data-caption="ID Card - Front Side">
                                                        <i class="fas fa-expand me-1"></i>View
                                                    </button>
                                                    <a href="{{ asset('storage/' . $customer->id_card_front) }}" 
                                                       download 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($customer->id_card_back)
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Back Side</label>
                                            <div class="border rounded p-2 text-center">
                                                <img src="{{ asset('storage/' . $customer->id_card_back) }}" 
                                                     alt="ID Card Back" 
                                                     class="img-fluid rounded img-lightbox-trigger" 
                                                     style="max-height: 150px;"
                                                     data-full-src="{{ asset('storage/' . $customer->id_card_back) }}"
                                                     data-caption="ID Card - Back Side">
                                                <div class="mt-2">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info img-lightbox-btn me-1"
                                                            data-full-src="{{ asset('storage/' . $customer->id_card_back) }}"
                                                            data-caption="ID Card - Back Side">
                                                        <i class="fas fa-expand me-1"></i>View
                                                    </button>
                                                    <a href="{{ asset('storage/' . $customer->id_card_back) }}" 
                                                       download 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Remove ID Cards Option -->
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="remove_id_cards" 
                                               name="remove_id_cards" 
                                               value="1">
                                        <label class="form-check-label text-danger" for="remove_id_cards">
                                            <i class="fas fa-trash-alt me-1"></i>Remove all ID card images
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- New ID Card Upload -->
                    <div class="{{ $customer->id_card_front || $customer->id_card_back ? 'col-md-6' : 'col-12' }} mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-upload me-2 text-muted"></i>Upload New ID Cards
                                </h6>
                                
                                <!-- ID Card Front -->
                                <div class="mb-3">
                                    <label for="id_card_front" class="form-label">
                                        <i class="fas fa-id-card-alt me-1 text-muted"></i>ID Card Front Side
                                    </label>
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
                                    
                                    <!-- Front Preview -->
                                    <div id="frontPreviewContainer" class="mt-2" style="display: none;">
                                        <label class="form-label small">Front Preview:</label>
                                        <div class="border rounded p-2 text-center">
                                            <img id="frontPreviewImage" 
                                                 src="" 
                                                 alt="Front Preview" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 100px;">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- ID Card Back -->
                                <div class="mb-3">
                                    <label for="id_card_back" class="form-label">
                                        <i class="fas fa-id-card-alt me-1 text-muted"></i>ID Card Back Side
                                    </label>
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
                                    
                                    <!-- Back Preview -->
                                    <div id="backPreviewContainer" class="mt-2" style="display: none;">
                                        <label class="form-label small">Back Preview:</label>
                                        <div class="border rounded p-2 text-center">
                                            <img id="backPreviewImage" 
                                                 src="" 
                                                 alt="Back Preview" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 100px;">
                                        </div>
                                    </div>
                                </div>
                                
                                <small class="form-text text-muted d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Max file size: 10MB per image | Allowed formats: JPG, PNG, GIF
                                </small>
                            </div>
                        </div>
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

<!-- Lightbox Modal -->
<div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-labelledby="imageLightboxModalLabel" aria-hidden="true" data-bs-focus="false" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageLightboxModalLabel">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="lightboxImage" src="" alt="Full size image" class="img-fluid">
                <p id="lightboxCaption" class="mt-2 mb-0 text-muted"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .file-upload-container {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .file-upload-container:hover {
        border-color: #3498db;
        background-color: #f8f9fa;
    }
    
    .file-upload-container.drag-over {
        border-color: #28a745;
        background-color: #e8f5e9;
    }
    
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        object-fit: contain;
    }
    
    .remove-image-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        z-index: 10;
    }
    
    /* Lightbox trigger cursor */
    .img-lightbox-trigger {
        cursor: zoom-in;
    }
    
    /* Modal image styling */
    #lightboxImage {
        max-height: 80vh;
        object-fit: contain;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editCustomerForm');
    const submitBtn = document.getElementById('submitBtn');
    const profilePictureInput = document.getElementById('profile_picture');
    const profilePreview = document.getElementById('profilePreview');
    const profilePreviewImage = document.getElementById('profilePreviewImage');
    const noProfilePreview = document.getElementById('noProfilePreview');
    const idCardFrontInput = document.getElementById('id_card_front');
    const idCardBackInput = document.getElementById('id_card_back');
    const frontPreviewContainer = document.getElementById('frontPreviewContainer');
    const frontPreviewImage = document.getElementById('frontPreviewImage');
    const backPreviewContainer = document.getElementById('backPreviewContainer');
    const backPreviewImage = document.getElementById('backPreviewImage');
    
    // Lightbox modal elements
    let lightboxModalInstance = null;
    const lightboxModalElement = document.getElementById('imageLightboxModal');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxCaption = document.getElementById('lightboxCaption');
    
    // Initialize modal without focus trap
    if (lightboxModalElement) {
        lightboxModalInstance = new bootstrap.Modal(lightboxModalElement, {
            focus: false,
            backdrop: 'static'
        });
    }
    
    // Lightbox trigger function
    function openLightbox(src, caption) {
        if (src && lightboxImage) {
            lightboxImage.src = src;
            lightboxCaption.textContent = caption || '';
            if (lightboxModalInstance) {
                lightboxModalInstance.show();
            }
        }
    }
    
    // Add event listeners for lightbox triggers
    document.querySelectorAll('.img-lightbox-trigger, .img-lightbox-btn').forEach(element => {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            const src = this.getAttribute('data-full-src');
            const caption = this.getAttribute('data-caption');
            openLightbox(src, caption);
        });
    });
    
    // Form submission handler
    if (submitBtn && form) {
        // Remove any existing event listeners by cloning and replacing the button
        const newSubmitBtn = submitBtn.cloneNode(true);
        submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
        
        // Track submission state
        let isSubmitting = false;
        
        // Add our own click handler with proper event management
        newSubmitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            // Check if form is already being submitted
            if (isSubmitting) {
                return;
            }
            
            // Mark form as submitting
            isSubmitting = true;
            
            // Disable button and show loading state
            newSubmitBtn.disabled = true;
            newSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            
            // Submit the form
            form.submit();
        });
    }

    // Profile picture preview
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (profilePreviewImage) {
                        profilePreviewImage.src = e.target.result;
                    }
                    if (profilePreview) {
                        profilePreview.style.display = 'block';
                    }
                    if (noProfilePreview) {
                        noProfilePreview.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                if (profilePreview) {
                    profilePreview.style.display = 'none';
                }
                if (noProfilePreview) {
                    noProfilePreview.style.display = 'block';
                }
            }
        });
    }

    // ID Card Front preview
    if (idCardFrontInput) {
        idCardFrontInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (frontPreviewImage) {
                        frontPreviewImage.src = e.target.result;
                    }
                    if (frontPreviewContainer) {
                        frontPreviewContainer.style.display = 'block';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                if (frontPreviewContainer) {
                    frontPreviewContainer.style.display = 'none';
                }
            }
        });
    }

    // ID Card Back preview
    if (idCardBackInput) {
        idCardBackInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (backPreviewImage) {
                        backPreviewImage.src = e.target.result;
                    }
                    if (backPreviewContainer) {
                        backPreviewContainer.style.display = 'block';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                if (backPreviewContainer) {
                    backPreviewContainer.style.display = 'none';
                }
            }
        });
    }

    // Reset form previews
    if (form) {
        form.addEventListener('reset', function() {
            if (profilePreview) {
                profilePreview.style.display = 'none';
            }
            if (noProfilePreview) {
                noProfilePreview.style.display = 'block';
            }
            if (frontPreviewContainer) {
                frontPreviewContainer.style.display = 'none';
            }
            if (backPreviewContainer) {
                backPreviewContainer.style.display = 'none';
            }
            
            // Reset remove checkboxes
            const removeProfileCheckbox = document.getElementById('remove_profile_picture');
            const removeIdCardsCheckbox = document.getElementById('remove_id_cards');
            
            if (removeProfileCheckbox) {
                removeProfileCheckbox.checked = false;
            }
            if (removeIdCardsCheckbox) {
                removeIdCardsCheckbox.checked = false;
            }
        });
    }
    
    // Handle modal hidden event to prevent focus issues
    if (lightboxModalElement) {
        lightboxModalElement.addEventListener('hidden.bs.modal', function () {
            // Blur any focused elements to prevent focus traps
            if (document.activeElement) {
                document.activeElement.blur();
            }
        });
    }
});
</script>
@endsection