@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">
            <i class="fas fa-cog me-2"></i>Billing System Settings
        </h1>
        <div class="d-flex">
            <button type="button" class="btn btn-secondary me-2" id="resetForm">
                <i class="fas fa-redo me-1"></i> Reset
            </button>
            <button type="submit" form="settingsForm" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form id="settingsForm" method="POST" action="{{ route('admin.admin-settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Admin Information Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-shield me-2"></i>Admin Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <!-- Admin Avatar -->
                                <div class="text-center">
                                    <div class="profile-image-container mb-3">
                                        @if(isset($settings['admin_avatar']) && $settings['admin_avatar'])
                                            <img src="{{ asset('storage/' . $settings['admin_avatar']) }}" 
                                                 alt="Admin Avatar" 
                                                 class="img-thumbnail rounded-circle profile-preview"
                                                 id="adminAvatarPreview"
                                                 style="width: 150px; height: 150px; object-fit: cover;">
                                        @else
                                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-light border"
                                                 style="width: 150px; height: 150px; margin: 0 auto;">
                                                <i class="fas fa-user fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-2">
                                        <label for="admin_avatar" class="btn btn-sm btn-outline-primary mb-1">
                                            <i class="fas fa-camera me-1"></i> Change Photo
                                            <input type="file" 
                                                   id="admin_avatar" 
                                                   name="admin_avatar" 
                                                   accept="image/*" 
                                                   class="d-none"
                                                   onchange="previewImage(this, 'adminAvatarPreview')">
                                        </label>
                                        @if(isset($settings['admin_avatar']) && $settings['admin_avatar'])
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeAdminAvatar">
                                            <i class="fas fa-trash me-1"></i> Remove
                                        </button>
                                        <input type="hidden" name="remove_admin_avatar" id="removeAdminAvatarFlag" value="0">
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-2">Max size: 5MB</small>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_name" class="form-label">Full Name *</label>
                                        <input type="text" 
                                               class="form-control @error('admin_name') is-invalid @enderror" 
                                               id="admin_name" 
                                               name="admin_name" 
                                               value="{{ old('admin_name', $settings['admin_name'] ?? Auth::user()->name ?? '') }}"
                                               required>
                                        @error('admin_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_email" class="form-label">Email Address *</label>
                                        <input type="email" 
                                               class="form-control @error('admin_email') is-invalid @enderror" 
                                               id="admin_email" 
                                               name="admin_email" 
                                               value="{{ old('admin_email', $settings['admin_email'] ?? Auth::user()->email ?? '') }}"
                                               required>
                                        @error('admin_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_phone" class="form-label">Phone Number</label>
                                        <input type="tel" 
                                               class="form-control @error('admin_phone') is-invalid @enderror" 
                                               id="admin_phone" 
                                               name="admin_phone" 
                                               value="{{ old('admin_phone', $settings['admin_phone'] ?? '') }}">
                                        @error('admin_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_role" class="form-label">Role</label>
                                        <input type="text" 
                                               class="form-control @error('admin_role') is-invalid @enderror" 
                                               id="admin_role" 
                                               name="admin_role" 
                                               value="{{ old('admin_role', $settings['admin_role'] ?? 'System Administrator') }}"
                                               readonly>
                                        @error('admin_role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Digital Signature -->
                                <div class="mb-3">
                                    <label for="admin_signature" class="form-label">Digital Signature</label>
                                    <div class="border rounded p-3 mb-2" style="min-height: 100px;">
                                        @if(isset($settings['admin_signature']) && $settings['admin_signature'])
                                            <img src="{{ asset('storage/' . $settings['admin_signature']) }}" 
                                                 alt="Signature" 
                                                 class="signature-preview"
                                                 id="signaturePreview"
                                                 style="max-height: 80px;">
                                        @else
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-signature fa-2x mb-2"></i>
                                                <p class="mb-0">No signature uploaded</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <label for="admin_signature" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-upload me-1"></i> Upload Signature
                                            <input type="file" 
                                                   id="admin_signature" 
                                                   name="admin_signature" 
                                                   accept="image/*" 
                                                   class="d-none"
                                                   onchange="previewImage(this, 'signaturePreview')">
                                        </label>
                                        @if(isset($settings['admin_signature']) && $settings['admin_signature'])
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeSignature">
                                            <i class="fas fa-trash me-1"></i> Remove Signature
                                        </button>
                                        <input type="hidden" name="remove_admin_signature" id="removeSignatureFlag" value="0">
                                        @endif
                                    </div>
                                    <small class="text-muted">Upload a transparent PNG signature for invoices (Max: 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- General Settings Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-building me-2"></i>Company Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="logo-image-container mb-3">
                                        @if(isset($settings['company_logo']) && $settings['company_logo'])
                                            <img src="{{ asset('storage/' . $settings['company_logo']) }}" 
                                                 alt="Company Logo" 
                                                 class="img-thumbnail logo-preview"
                                                 id="companyLogoPreview"
                                                 style="max-height: 120px; max-width: 200px;">
                                        @else
                                            <div class="logo-placeholder border rounded d-flex align-items-center justify-content-center bg-light"
                                                 style="width: 200px; height: 120px; margin: 0 auto;">
                                                <i class="fas fa-building fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-2">
                                        <label for="company_logo" class="btn btn-sm btn-outline-primary mb-1">
                                            <i class="fas fa-image me-1"></i> Change Logo
                                            <input type="file" 
                                                   id="company_logo" 
                                                   name="company_logo" 
                                                   accept="image/*" 
                                                   class="d-none"
                                                   onchange="previewImage(this, 'companyLogoPreview')">
                                        </label>
                                        @if(isset($settings['company_logo']) && $settings['company_logo'])
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeCompanyLogo">
                                            <i class="fas fa-trash me-1"></i> Remove
                                        </button>
                                        <input type="hidden" name="remove_company_logo" id="removeCompanyLogoFlag" value="0">
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-2">Recommended: 300x180px, Max: 2MB</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="company_name" class="form-label">Company Name *</label>
                                        <input type="text" 
                                               class="form-control @error('company_name') is-invalid @enderror" 
                                               id="company_name" 
                                               name="company_name" 
                                               value="{{ old('company_name', $settings['company_name'] ?? '') }}"
                                               required>
                                        @error('company_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company_email" class="form-label">Billing Email *</label>
                                        <input type="email" 
                                               class="form-control @error('company_email') is-invalid @enderror" 
                                               id="company_email" 
                                               name="company_email" 
                                               value="{{ old('company_email', $settings['company_email'] ?? '') }}"
                                               required>
                                        @error('company_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="company_phone" class="form-label">Phone Number</label>
                                        <input type="tel" 
                                               class="form-control @error('company_phone') is-invalid @enderror" 
                                               id="company_phone" 
                                               name="company_phone" 
                                               value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                                        @error('company_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company_website" class="form-label">Website</label>
                                        <input type="url" 
                                               class="form-control @error('company_website') is-invalid @enderror" 
                                               id="company_website" 
                                               name="company_website" 
                                               value="{{ old('company_website', $settings['company_website'] ?? '') }}"
                                               placeholder="https://">
                                        @error('company_website')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="invoice_prefix" class="form-label">Invoice Prefix</label>
                                        <input type="text" 
                                               class="form-control @error('invoice_prefix') is-invalid @enderror" 
                                               id="invoice_prefix" 
                                               name="invoice_prefix" 
                                               value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV-') }}"
                                               placeholder="e.g., INV-">
                                        @error('invoice_prefix')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="invoice_start_number" class="form-label">Invoice Start Number</label>
                                        <input type="number" 
                                               class="form-control @error('invoice_start_number') is-invalid @enderror" 
                                               id="invoice_start_number" 
                                               name="invoice_start_number" 
                                               value="{{ old('invoice_start_number', $settings['invoice_start_number'] ?? '1001') }}"
                                               min="1">
                                        @error('invoice_start_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="company_address" class="form-label">Company Address</label>
                                    <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                              id="company_address" 
                                              name="company_address" 
                                              rows="3">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                                    @error('company_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax Settings Card -->
               
                <!-- Invoice Settings Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-invoice me-2"></i>Invoice & Payment Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_terms" class="form-label">Default Payment Terms (Days)</label>
                                <input type="number" 
                                       class="form-control @error('payment_terms') is-invalid @enderror" 
                                       id="payment_terms" 
                                       name="payment_terms" 
                                       value="{{ old('payment_terms', $settings['payment_terms'] ?? 30) }}"
                                       min="1">
                                @error('payment_terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Currency Settings -->
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Default Currency</label>
                                <select class="form-control @error('currency') is-invalid @enderror" 
                                        id="currency" 
                                        name="currency">
                                    <option value="USD" {{ (old('currency', $settings['currency'] ?? 'USD') == 'USD') ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ (old('currency', $settings['currency'] ?? 'USD') == 'EUR') ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ (old('currency', $settings['currency'] ?? 'USD') == 'GBP') ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="CAD" {{ (old('currency', $settings['currency'] ?? 'USD') == 'CAD') ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                    <option value="AUD" {{ (old('currency', $settings['currency'] ?? 'USD') == 'AUD') ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                    <option value="JPY" {{ (old('currency', $settings['currency'] ?? 'USD') == 'JPY') ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                    <option value="BDT" {{ (old('currency', $settings['currency'] ?? 'USD') == 'BDT') ? 'selected' : '' }}>BDT - Bangladeshi Taka</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Late Payment Fee Settings -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="late_fee_enabled" class="form-label">Late Payment Fee</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="late_fee_enabled" 
                                           name="late_fee_enabled" 
                                           value="1"
                                           {{ old('late_fee_enabled', $settings['late_fee_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="late_fee_enabled">
                                        Enable late payment fee
                                    </label>
                                </div>
                                <div id="lateFeeSettings" class="ps-4 {{ old('late_fee_enabled', $settings['late_fee_enabled'] ?? false) ? '' : 'd-none' }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="late_fee_type" class="form-label">Fee Type</label>
                                            <select class="form-control" id="late_fee_type" name="late_fee_type">
                                                <option value="percentage" {{ (old('late_fee_type', $settings['late_fee_type'] ?? 'percentage') == 'percentage') ? 'selected' : '' }}>Percentage</option>
                                                <option value="fixed" {{ (old('late_fee_type', $settings['late_fee_type'] ?? 'percentage') == 'fixed') ? 'selected' : '' }}>Fixed Amount</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="late_fee_amount" class="form-label">Amount</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="late_fee_amount" 
                                                   name="late_fee_amount" 
                                                   value="{{ old('late_fee_amount', $settings['late_fee_amount'] ?? 0) }}"
                                                   step="0.01"
                                                   min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="auto_reminders" class="form-label">Payment Reminders</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="auto_reminders" 
                                           name="auto_reminders" 
                                           value="1"
                                           {{ old('auto_reminders', $settings['auto_reminders'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_reminders">
                                        Send automatic payment reminders
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Admin Statistics Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line me-2"></i>Admin Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-primary mb-1">{{ $stats['total_invoices'] ?? 0 }}</h3>
                                    <small class="text-muted">Total Invoices</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-success mb-1">{{ $stats['paid_invoices'] ?? 0 }}</h3>
                                    <small class="text-muted">Paid Invoices</small>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-warning mb-1">{{ $stats['pending_invoices'] ?? 0 }}</h3>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 bg-light">
                                    <h3 class="text-danger mb-1">{{ $stats['overdue_invoices'] ?? 0 }}</h3>
                                    <small class="text-muted">Overdue</small>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Last Login</label>
                            <p class="mb-1">
                                <i class="fas fa-clock me-2 text-muted"></i>
                                {{ $settings['last_login'] ?? 'Never' }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Created</label>
                            <p class="mb-1">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                {{ $settings['account_created'] ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">System Version</label>
                            <p class="mb-1">
                                <i class="fas fa-code-branch me-2 text-muted"></i>
                                {{ $settings['system_version'] ?? '1.0.0' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-credit-card me-2"></i>Payment Methods
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="payment_bank_transfer" 
                                       name="payment_methods[]" 
                                       value="bank_transfer"
                                       {{ in_array('bank_transfer', old('payment_methods', $settings['payment_methods'] ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_bank_transfer">
                                    Bank Transfer
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="payment_credit_card" 
                                       name="payment_methods[]" 
                                       value="credit_card"
                                       {{ in_array('credit_card', old('payment_methods', $settings['payment_methods'] ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_credit_card">
                                    Credit Card
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="payment_bkash" 
                                       name="payment_methods[]" 
                                       value="bkash"
                                       {{ in_array('bkash', old('payment_methods', $settings['payment_methods'] ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_bkash">
                                    Bkash
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="payment_nagad" 
                                       name="payment_methods[]" 
                                       value="nagad"
                                       {{ in_array('nagad', old('payment_methods', $settings['payment_methods'] ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_nagad">
                                    Nagad
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="payment_cash" 
                                       name="payment_methods[]" 
                                       value="cash"
                                       {{ in_array('cash', old('payment_methods', $settings['payment_methods'] ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_cash">
                                    Cash
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="bank_details" class="form-label">Bank Account Details</label>
                            <textarea class="form-control @error('bank_details') is-invalid @enderror" 
                                      id="bank_details" 
                                      name="bank_details" 
                                      rows="4">{{ old('bank_details', $settings['bank_details'] ?? '') }}</textarea>
                            @error('bank_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notification Settings Card -->
                
                <!-- Invoice Template Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-palette me-2"></i>Invoice Template
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="invoice_theme" class="form-label">Invoice Theme</label>
                            <select class="form-control @error('invoice_theme') is-invalid @enderror" 
                                    id="invoice_theme" 
                                    name="invoice_theme">
                                <option value="light" {{ (old('invoice_theme', $settings['invoice_theme'] ?? 'light') == 'light') ? 'selected' : '' }}>Light Theme</option>
                                <option value="dark" {{ (old('invoice_theme', $settings['invoice_theme'] ?? 'light') == 'dark') ? 'selected' : '' }}>Dark Theme</option>
                                <option value="modern" {{ (old('invoice_theme', $settings['invoice_theme'] ?? 'light') == 'modern') ? 'selected' : '' }}>Modern Theme</option>
                                <option value="classic" {{ (old('invoice_theme', $settings['invoice_theme'] ?? 'light') == 'classic') ? 'selected' : '' }}>Classic Theme</option>
                                <option value="professional" {{ (old('invoice_theme', $settings['invoice_theme'] ?? 'light') == 'professional') ? 'selected' : '' }}>Professional</option>
                                <option value="minimal" {{ (old('invoice_theme', $settings['invoice_theme'] ?? 'light') == 'minimal') ? 'selected' : '' }}>Minimal</option>
                            </select>
                            @error('invoice_theme')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="invoice_footer" class="form-label">Invoice Footer Text</label>
                            <textarea class="form-control @error('invoice_footer') is-invalid @enderror" 
                                      id="invoice_footer" 
                                      name="invoice_footer" 
                                      rows="3">{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}</textarea>
                            @error('invoice_footer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="invoice_notes" class="form-label">Default Invoice Notes</label>
                            <textarea class="form-control @error('invoice_notes') is-invalid @enderror" 
                                      id="invoice_notes" 
                                      name="invoice_notes" 
                                      rows="3">{{ old('invoice_notes', $settings['invoice_notes'] ?? '') }}</textarea>
                            @error('invoice_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Global function for image preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                // Show the image and hide placeholder
                const placeholder = preview.parentElement.querySelector('.avatar-placeholder, .logo-placeholder');
                if (placeholder) placeholder.classList.add('d-none');
            }
        }
        
        reader.readAsDataURL(file);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Late fee toggle
    const lateFeeToggle = document.getElementById('late_fee_enabled');
    const lateFeeSettings = document.getElementById('lateFeeSettings');
    
    if (lateFeeToggle) {
        lateFeeToggle.addEventListener('change', function() {
            if (this.checked) {
                lateFeeSettings.classList.remove('d-none');
            } else {
                lateFeeSettings.classList.add('d-none');
            }
        });
    }

    // Add tax type
    const addTaxTypeBtn = document.getElementById('addTaxType');
    const taxTypesContainer = document.getElementById('taxTypesContainer');
    
    if (addTaxTypeBtn) {
        addTaxTypeBtn.addEventListener('click', function() {
            const index = taxTypesContainer.children.length;
            const template = `
                <div class="tax-type-item row mb-2">
                    <div class="col-md-5">
                        <input type="text" 
                               class="form-control" 
                               name="tax_types[${index}][name]" 
                               placeholder="Tax Name (e.g., VAT, GST)">
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   name="tax_types[${index}][rate]" 
                                   placeholder="Rate"
                                   step="0.01"
                                   min="0">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-tax-type">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            taxTypesContainer.insertAdjacentHTML('beforeend', template);
            attachRemoveTaxTypeListeners();
        });
    }

    // Remove tax type
    function attachRemoveTaxTypeListeners() {
        document.querySelectorAll('.remove-tax-type').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.tax-type-item').remove();
            });
        });
    }

    // Initial attachment
    attachRemoveTaxTypeListeners();

    // Remove admin avatar
    const removeAdminAvatarBtn = document.getElementById('removeAdminAvatar');
    const removeAdminAvatarFlag = document.getElementById('removeAdminAvatarFlag');
    
    if (removeAdminAvatarBtn) {
        removeAdminAvatarBtn.addEventListener('click', function() {
            const preview = document.getElementById('adminAvatarPreview');
            const placeholder = preview.parentElement.querySelector('.avatar-placeholder');
            
            if (preview) {
                preview.classList.add('d-none');
                preview.src = '';
            }
            
            if (placeholder) {
                placeholder.classList.remove('d-none');
            }
            
            removeAdminAvatarFlag.value = '1';
            document.getElementById('admin_avatar').value = '';
            this.disabled = true;
        });
    }

    // Remove company logo
    const removeCompanyLogoBtn = document.getElementById('removeCompanyLogo');
    const removeCompanyLogoFlag = document.getElementById('removeCompanyLogoFlag');
    
    if (removeCompanyLogoBtn) {
        removeCompanyLogoBtn.addEventListener('click', function() {
            const preview = document.getElementById('companyLogoPreview');
            const placeholder = preview.parentElement.querySelector('.logo-placeholder');
            
            if (preview) {
                preview.classList.add('d-none');
                preview.src = '';
            }
            
            if (placeholder) {
                placeholder.classList.remove('d-none');
            }
            
            removeCompanyLogoFlag.value = '1';
            document.getElementById('company_logo').value = '';
            this.disabled = true;
        });
    }

    // Remove signature
    const removeSignatureBtn = document.getElementById('removeSignature');
    const removeSignatureFlag = document.getElementById('removeSignatureFlag');
    
    if (removeSignatureBtn) {
        removeSignatureBtn.addEventListener('click', function() {
            const preview = document.getElementById('signaturePreview');
            const container = preview.parentElement;
            
            if (preview) {
                preview.remove();
            }
            
            // Add placeholder
            const placeholder = document.createElement('div');
            placeholder.className = 'text-center text-muted py-4';
            placeholder.innerHTML = `
                <i class="fas fa-signature fa-2x mb-2"></i>
                <p class="mb-0">No signature uploaded</p>
            `;
            container.appendChild(placeholder);
            
            removeSignatureFlag.value = '1';
            document.getElementById('admin_signature').value = '';
            this.disabled = true;
        });
    }

    // Reset form
    const resetBtn = document.getElementById('resetForm');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to reset all changes? This action cannot be undone.')) {
                document.getElementById('settingsForm').reset();
                // Reset image previews
                document.querySelectorAll('img.profile-preview, img.logo-preview, img.signature-preview').forEach(img => {
                    img.src = '';
                    img.classList.add('d-none');
                });
                // Show placeholders
                document.querySelectorAll('.avatar-placeholder, .logo-placeholder').forEach(placeholder => {
                    placeholder.classList.remove('d-none');
                });
                // Reset remove flags
                document.querySelectorAll('input[type="hidden"][name^="remove_"]').forEach(input => {
                    input.value = '0';
                });
                // Enable remove buttons
                document.querySelectorAll('button[id^="remove"]').forEach(btn => {
                    btn.disabled = false;
                });
                // Trigger change event on checkboxes
                document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                    cb.dispatchEvent(new Event('change'));
                });
            }
        });
    }

    // Form submission validation
    const form = document.getElementById('settingsForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasErrors = false;
            
            // Validate tax rates
            document.querySelectorAll('input[name^="tax_types"]').forEach(input => {
                if (input.type === 'number') {
                    const value = parseFloat(input.value);
                    if (value < 0 || value > 100) {
                        input.classList.add('is-invalid');
                        hasErrors = true;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                }
            });

            // Validate file sizes
            const avatarInput = document.getElementById('admin_avatar');
            const logoInput = document.getElementById('company_logo');
            const signatureInput = document.getElementById('admin_signature');
            
            const validateFileSize = (input, maxSizeMB) => {
                if (input.files.length > 0) {
                    const fileSizeMB = input.files[0].size / (5120 * 5120);
                    if (fileSizeMB > maxSizeMB) {
                        alert(`File size for ${input.name} should be less than ${maxSizeMB}MB`);
                        hasErrors = true;
                    }
                }
            };
            
            validateFileSize(avatarInput, 2);
            validateFileSize(logoInput, 2);
            validateFileSize(signatureInput, 0.5);

            if (hasErrors) {
                e.preventDefault();
                alert('Please fix the errors before submitting.');
            }
        });
    }

    // Auto-format phone number
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d+)/, '($1) $2');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d+)/, '($1) $2-$3');
            }
            e.target.value = value;
        });
    });
});
</script>

<style>
.profile-image-container {
    position: relative;
}

.avatar-placeholder, .logo-placeholder {
    transition: all 0.3s ease;
}

.tax-type-item {
    align-items: center;
    transition: all 0.3s ease;
}

.tax-type-item:hover {
    background-color: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.card {
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.signature-preview {
    max-width: 100%;
    height: auto;
    background: white;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    color: white;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
}

/* Custom file input styling */
input[type="file"] + label {
    cursor: pointer;
    transition: all 0.3s ease;
}

input[type="file"]:focus + label {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-image-container {
        margin-bottom: 20px;
    }
    
    .tax-type-item .col-md-2 {
        margin-top: 10px;
    }
    
    .card-body {
        padding: 15px;
    }
}
</style>
@endpush