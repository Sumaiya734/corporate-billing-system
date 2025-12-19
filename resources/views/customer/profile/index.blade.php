@extends('layouts.customer')

@section('title', 'My Profile - Nanosoft')

@section('content')
<div class="profile-page">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h2 mb-2">
                <i class="fas fa-user-circle me-2 text-primary"></i>My Profile
            </h1>
        </div>
        <p class="text-muted mb-0">Manage your personal information and account settings.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Personal Information Card -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2 text-primary"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $customer->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $customer->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $customer->phone) }}" 
                                       required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Customer ID</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="customer_id" 
                                       value="{{ $customer->customer_id }}" 
                                       readonly>
                            </div>
                            
                            <div class="col-12">
                                <label for="address" class="form-label">Address *</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3" 
                                          required>{{ old('address', $customer->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                           

                            <!-- Profile Picture Upload -->
                            <div class="col-md-6">
                                <label for="profile_picture" class="form-label">Profile Picture</label>
                                <input type="file"
                                       class="form-control @error('profile_picture') is-invalid @enderror"
                                       id="profile_picture"
                                       name="profile_picture"
                                       accept="image/*">
                                @error('profile_picture')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload a profile picture (max 10MB, JPEG/PNG/GIF)</div>
                                @if($customer->profile_picture)
                                    <div class="mt-2">
                                        <small class="text-muted">Current:</small><br>
                                        <img src="{{ asset('storage/' . $customer->profile_picture) }}"
                                             alt="Profile Picture"
                                             class="img-thumbnail"
                                             style="max-width: 100px; max-height: 100px;">
                                    </div>
                                @endif
                            </div>

                            <!-- ID Card Front Upload -->
                            <div class="col-md-6">
                                <label for="id_card_front" class="form-label">ID Card Front</label>
                                <input type="file"
                                       class="form-control @error('id_card_front') is-invalid @enderror"
                                       id="id_card_front"
                                       name="id_card_front"
                                       accept="image/*">
                                @error('id_card_front')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload ID card front image (max 10MB, JPEG/PNG/GIF)</div>
                                @if($customer->id_card_front)
                                    <div class="mt-2">
                                        <small class="text-muted">Current:</small><br>
                                        <img src="{{ asset('storage/' . $customer->id_card_front) }}"
                                             alt="ID Card Front"
                                             class="img-thumbnail"
                                             style="max-width: 100px; max-height: 100px;">
                                    </div>
                                @endif
                            </div>

                            <!-- ID Card Back Upload -->
                            <div class="col-12">
                                <label for="id_card_back" class="form-label">ID Card Back</label>
                                <input type="file"
                                       class="form-control @error('id_card_back') is-invalid @enderror"
                                       id="id_card_back"
                                       name="id_card_back"
                                       accept="image/*">
                                @error('id_card_back')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload ID card back image (max 10MB, JPEG/PNG/GIF)</div>
                                @if($customer->id_card_back)
                                    <div class="mt-2">
                                        <small class="text-muted">Current:</small><br>
                                        <img src="{{ asset('storage/' . $customer->id_card_back) }}"
                                             alt="ID Card Back"
                                             class="img-thumbnail"
                                             style="max-width: 100px; max-height: 100px;">
                                    </div>
                                @endif
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Changes
                                </button>
                                <a href="{{ route('customer.profile.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2 text-warning"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('customer.profile.change-password') }}">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="current_password" class="form-label">Current Password *</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password *</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('new_password') is-invalid @enderror" 
                                           id="new_password" 
                                           name="new_password" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('new_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="new_password_confirmation" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="new_password_confirmation" 
                                           name="new_password_confirmation" 
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Password must be at least 8 characters long and include uppercase, lowercase, and numbers.</small>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key me-1"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Account Info -->
        <div class="col-lg-5">
            <!-- Profile Summary Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        @if($customer->profile_picture)
                            <img src="{{ asset('storage/' . $customer->profile_picture) }}"
                                 alt="Profile Picture"
                                 class="profile-picture-display rounded-circle"
                                 style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #667eea;">
                        @else
                            <div class="avatar-circle">
                                <i class="fas fa-user-circle fa-4x text-primary"></i>
                            </div>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $customer->name }}</h4>
                    <p class="text-muted mb-3">{{ $customer->email }}</p>
                    
                    <div class="profile-info">
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Customer ID:</span>
                            <span class="fw-bold">{{ $customer->customer_id }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Account Status:</span>
                            <span class="badge bg-success rounded-pill px-3 py-1">
                                <i class="fas fa-circle fa-xs me-1"></i> Active
                            </span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Member Since:</span>
                            <span class="fw-bold">{{ $customer->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Last Updated:</span>
                            <span class="fw-bold">{{ $customer->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Products Card -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2 text-success"></i>Assigned Products
                    </h5>
                </div>
                <div class="card-body">
                    @if($customer->customerproducts->where('is_active', 1)->where('status', 'active')->count() > 0)
                        <div class="assigned-products">
                            @foreach($customer->customerproducts->where('is_active', 1)->where('status', 'active')->take(3) as $customerProduct)
                                <div class="product-item mb-3 p-3 rounded-3 border">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="product-icon bg-soft-success rounded-2 p-2 me-3">
                                            <i class="fas fa-box text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold">{{ $customerProduct->product->name ?? 'Unknown Product' }}</h6>
                                            <small class="text-success">
                                                <i class="fas fa-circle fa-xs me-1"></i> Active
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-light text-primary">
                                            ৳{{ number_format($customerProduct->product->monthly_price ?? 0, 2) }}/month
                                        </span>
                                        <small class="text-muted">
                                            {{ $customerProduct->created_at->format('M Y') }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($customer->customerproducts->where('is_active', 1)->where('status', 'active')->count() > 3)
                            <div class="text-center">
                                <a href="{{ route('customer.products.index') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> View All Products
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <div class="empty-state-icon mb-2">
                                <i class="fas fa-box fa-3x text-muted opacity-50"></i>
                            </div>
                            <p class="text-muted mb-0">No active products assigned</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-info"></i>Account Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="stat-card p-3 rounded-3 border">
                                <div class="stat-icon text-primary mb-2">
                                    <i class="fas fa-box fa-2x"></i>
                                </div>
                                <div class="stat-value fw-bold">
                                    {{ $customer->customerproducts->where('is_active', 1)->where('status', 'active')->count() }}
                                </div>
                                <div class="stat-title text-muted">Active Products</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 rounded-3 border">
                                <div class="stat-icon text-success mb-2">
                                    <i class="fas fa-file-invoice fa-2x"></i>
                                </div>
                                <div class="stat-value fw-bold">
                                    {{ $customer->invoices->count() }}
                                </div>
                                <div class="stat-title text-muted">Total Invoices</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 rounded-3 border">
                                <div class="stat-icon text-warning mb-2">
                                    <i class="fas fa-credit-card fa-2x"></i>
                                </div>
                                <div class="stat-value fw-bold">
                                    {{ $customer->payments->count() }}
                                </div>
                                <div class="stat-title text-muted">Payments</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 rounded-3 border">
                                <div class="stat-icon text-info mb-2">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                </div>
                                <div class="stat-value fw-bold">
                                    {{ $customer->created_at->diffInMonths(now()) }}
                                </div>
                                <div class="stat-title text-muted">Months with us</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-page {
        animation: fadeIn 0.6s ease-out;
    }

    .avatar-circle {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .avatar-circle i {
        color: white;
    }

    .product-item {
        transition: all 0.3s ease;
        border-color: #e2e8f0 !important;
    }

    .product-item:hover {
        border-color: #667eea !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .product-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-soft-success { background-color: rgba(34, 197, 94, 0.1); }

    .stat-card {
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        opacity: 0.8;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        background: #f8fafc;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
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
        .avatar-circle {
            width: 80px;
            height: 80px;
        }
        
        .avatar-circle i {
            font-size: 3rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality
        function setupPasswordToggle(buttonId, inputId) {
            const toggleBtn = document.getElementById(buttonId);
            const passwordInput = document.getElementById(inputId);
            
            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle eye icon
                    const icon = this.querySelector('i');
                    if (type === 'password') {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                });
            }
        }
        
        // Setup password toggles
        setupPasswordToggle('toggleCurrentPassword', 'current_password');
        setupPasswordToggle('toggleNewPassword', 'new_password');
        setupPasswordToggle('toggleConfirmPassword', 'new_password_confirmation');
        
        // Auto-close alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endsection