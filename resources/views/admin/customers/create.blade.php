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
            <nav aria-label="breadcrumb">
                
            </nav>
        </div>
        
    </div>

    <!-- Customer Form -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-circle me-2"></i>Customer Information
            </h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf
                
                <div class="form-section">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>Basic Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label required">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ old('name') }}" required placeholder="Enter full name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label required">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="{{ old('email') }}" required placeholder="Enter email address">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label required">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required placeholder="Enter password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label required">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation" 
                                       name="password_confirmation" required placeholder="Confirm password">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-phone me-2"></i>Contact Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label required">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="{{ old('phone') }}" required placeholder="Enter phone number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address" class="form-label required">Residential Address</label>
                                <textarea class="form-control" id="address" name="address" 
                                          rows="2" required placeholder="Enter residential address">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="connection_address" class="form-label required">Connection Address</label>
                                <textarea class="form-control" id="connection_address" name="connection_address" 
                                          rows="2" required placeholder="Enter connection installation address">{{ old('connection_address') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-id-card me-2"></i>Identity Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_type" class="form-label required">ID Type</label>
                                <select class="form-select" id="id_type" name="id_type" required>
                                    <option value="">Select ID Type</option>
                                    <option value="nid" {{ old('id_type') == 'nid' ? 'selected' : '' }}>National ID (NID)</option>
                                    <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="driving_license" {{ old('id_type') == 'driving_license' ? 'selected' : '' }}>Driving License</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_number" class="form-label required">ID Number</label>
                                <input type="text" class="form-control" id="id_number" name="id_number" 
                                       value="{{ old('id_number') }}" required placeholder="Enter ID number">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    
    form.addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Passwords do not match!');
            password.focus();
        }
    });

    confirmPassword.addEventListener('input', function() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.style.borderColor = 'red';
        } else {
            confirmPassword.style.borderColor = '#ced4da';
        }
    });
});
</script>
@endsection