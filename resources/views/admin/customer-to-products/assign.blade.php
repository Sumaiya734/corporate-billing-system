@extends('layouts.admin')

@section('title', 'Assign Product')

@section('content')
<!-- Toast Notification Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-plus-circle me-2"></i>Assign Products to Customer</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.customer-to-products.index') }}" class="btn btn-outline-secondary" data-action="navigate">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-user-tag me-2"></i>Product Assignment Form</h5>
                </div>
                <div class="card-body">
                    <form id="assignProductForm" action="{{ route('admin.customer-to-products.store') }}" method="POST">
                        @csrf

                        <!-- Customer Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Select Customer *</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                                        <i class="fas fa-user-plus me-1"></i>New Customer
                                    </button>
                                </div>

                                @if(isset($preSelectedCustomer) && $preSelectedCustomer)
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Pre-selected Customer:</strong> {{ $preSelectedCustomer->name }} ({{ $preSelectedCustomer->customer_id }})
                                        <br><small>This customer was selected from the customer list. You can change the selection below if needed.</small>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <input type="text" class="form-control" id="customerSearch"
                                           placeholder="Search customers by name, phone, email, or ID..."
                                           autocomplete="on">
                                    <div class="form-text">Start typing to show customer list. Click on a customer to select.</div>
                                </div>

                                <div id="customerResults" class="customer-results-container" style="max-height:300px;overflow-y:auto;display:none;">
                                    @foreach($customers as $customer)
                                        <div class="customer-result-item"
                                             data-customer-id="{{ $customer->c_id }}"
                                             data-customer-name="{{ $customer->name }}"
                                             data-customer-phone="{{ $customer->phone ?? 'No phone' }}"
                                             data-customer-email="{{ $customer->email ?? 'No email' }}"
                                             data-customer-customerid="{{ $customer->customer_id }}">
                                            <div class="customer-name">{{ $customer->name }}</div>
                                            <div class="customer-details">
                                                @if($customer->phone)
                                                    <i class="fas fa-phone me-1"></i>{{ $customer->phone }} •
                                                @endif
                                                <i class="fas fa-id-card me-1"></i>ID: {{ $customer->customer_id }}
                                                @if($customer->email)
                                                    • <i class="fas fa-envelope me-1"></i>{{ $customer->email }}
                                                @endif
                                            </div>
                                            <div class="customer-address small text-muted mt-1">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $customer->address ?? 'No address provided' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div id="selectedCustomer" class="selected-customer-card mt-3" style="display:none;">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1" id="selectedCustomerName"></h6>
                                                    <p class="mb-1 text-muted" id="selectedCustomerDetails"></p>
                                                    <small class="text-muted" id="selectedCustomerId"></small>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        onclick="clearCustomerSelection()">
                                                    <i class="fas fa-times"></i> Change
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="customer_id" id="customerId" value="{{ old('customer_id') }}">

                                @error('customer_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Products Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Billing Cycle Info:</strong> The billing cycle starts from the <strong>Due Day</strong> you select.
                                    <ul class="mb-0 mt-2 small">
                                        <li><strong>1 Month:</strong> Bills every month on the due day</li>
                                        <li><strong>3 Months:</strong> Bills every 3 months (e.g., if due day is 5th: Feb 5, May 5, Aug 5, Nov 5)</li>
                                        <li><strong>6 Months:</strong> Bills every 6 months</li>
                                        <li><strong>12 Months:</strong> Bills once a year</li>
                                    </ul>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label fw-bold">Select Products *</label>
                                    <button type="button" class="btn btn-primary btn-sm" id="addProductBtn">
                                        <i class="fas fa-plus me-1"></i>Add Another Product
                                    </button>
                                </div>

                                <div class="products-container" id="productsContainer">
                                    <!-- Initial Product Row -->
                                    <div class="product-row mb-3" data-index="0">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label">Product 1 *</label>
                                                <select class="form-select product-select @error('products.0.product_id') is-invalid @enderror"
                                                        name="products[0][product_id]" data-index="0" required>
                                                    <option value="">Select a product...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->p_id }}"
                                                                data-price="{{ $product->monthly_price }}"
                                                                data-type="{{ $product->product_type }}"
                                                                {{ old('products.0.product_id') == $product->p_id ? 'selected' : '' }}>
                                                            {{ $product->name }} - ৳{{ number_format($product->monthly_price, 2) }}/month
                                                            ({{ ucfirst($product->product_type) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('products.0.product_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Billing cycle *</label>
                                                <select class="form-select billing-months @error('products.0.billing_cycle_months') is-invalid @enderror"
                                                        name="products[0][billing_cycle_months]" data-index="0" required>
                                                    <option value="1" {{ old('products.0.billing_cycle_months', '1') == '1' ? 'selected' : '' }}>1 Month</option>
                                                    <option value="2" {{ old('products.0.billing_cycle_months') == '2' ? 'selected' : '' }}>2 Months</option>
                                                    <option value="3" {{ old('products.0.billing_cycle_months') == '3' ? 'selected' : '' }}>3 Months</option>
                                                    <option value="6" {{ old('products.0.billing_cycle_months') == '6' ? 'selected' : '' }}>6 Months</option>
                                                    <option value="12" {{ old('products.0.billing_cycle_months') == '12' ? 'selected' : '' }}>12 Months</option>
                                                </select>
                                                @error('products.0.billing_cycle_months')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label mb-0">Subtotal</label>
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input use-custom-price" type="checkbox" data-index="0" id="useCustomPrice0">
                                                        <label class="form-check-label small text-muted" for="useCustomPrice0">
                                                            Custom
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>
                                                    <input type="number" class="form-control monthly-price @error('products.0.monthly_price') is-invalid @enderror"
                                                           name="products[0][monthly_price]" 
                                                           data-index="0" 
                                                           value="{{ old('products.0.monthly_price', '0') }}" 
                                                           min="0" step="0.01" readonly>
                                                </div>
                                                <div class="custom-price-badge mt-1" id="customBadge0" style="display:none;">
                                                    <!-- <span class="badge bg-warning text-dark small">
                                                        <i class="fas fa-edit me-1"></i>Custom
                                                    </span> -->
                                                </div>
                                                @error('products.0.monthly_price')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Assign Date *</label>
                                                <input type="date" class="form-control assign-date @error('products.0.assign_date') is-invalid @enderror"
                                                       name="products[0][assign_date]"
                                                       value="{{ old('products.0.assign_date', date('Y-m-d')) }}" data-index="0" required>
                                                @error('products.0.assign_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label mb-0">Due Date *</label>
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input use-custom-due-date" type="checkbox" data-index="0" id="useCustomDueDate0">
                                                        <label class="form-check-label small text-muted" for="useCustomDueDate0">
                                                            Custom
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="date" class="form-control due-date-input @error('products.0.custom_due_date') is-invalid @enderror"
                                                       name="products[0][custom_due_date]" 
                                                       data-index="0" 
                                                       value="{{ old('products.0.custom_due_date', '') }}" 
                                                       readonly required>
                                                @error('products.0.custom_due_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-1">
                                                <label class="form-label">Total</label>
                                                <div class="product-amount" data-index="0">
                                                    <span class="amount-final fw-bold text-success">৳ 0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="summary-card">
                                    <h6 class="summary-title"><i class="fas fa-receipt me-2"></i>Order Summary</h6>
                                    <div class="summary-details" id="summaryDetails">
                                        <div class="summary-product-item" id="productSummary0">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-dark summary-product-name">Product 1</div>
                                                    <div class="text-muted small summary-product-details">
                                                        <span class="summary-price">৳0.00/month</span> × 
                                                        <span class="summary-cycle">1 month</span>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-success summary-product-amount">৳ 0.00</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="summary-divider"></div>
                                    <div class="summary-row total">
                                        <span class="fw-bold">Subtotal Amount:</span>
                                        <span class="fw-bold text-success" id="totalAmount">৳ 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Generation Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="invoice-generation-card">
                                    <h6 class="invoice-title">
                                        <i class="fas fa-file-invoice me-2"></i>Invoice Generation
                                    </h6>
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Auto-Invoice:</strong> Each product will automatically generate a separate invoice upon assignment.
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="generateInvoice" name="generate_invoice" value="1" checked>
                                        <label class="form-check-label fw-bold" for="generateInvoice">
                                            Generate invoices automatically for assigned products
                                        </label>
                                    </div>

                                    <div id="invoicePreview" class="invoice-preview-container">
                                        <div class="invoice-preview-header">
                                            <i class="fas fa-receipt me-2"></i>Invoice Preview
                                        </div>
                                        <div id="invoicePreviewList" class="invoice-preview-list">
                                            <div class="text-muted text-center py-3">
                                                <i class="fas fa-file-invoice fa-2x mb-2 opacity-50"></i>
                                                <div>Select products to see invoice preview</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg w-100" id="submitBtn" disabled>
                                    <i class="fas fa-check me-2"></i>Assign Products & Generate Invoices
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template for new product rows -->
<template id="productOptionsTemplate">
    @foreach($products as $product)
        <option value="{{ $product->p_id }}"
                data-price="{{ $product->monthly_price }}"
                data-type="{{ $product->product_type }}">
            {{ $product->name }} - ৳{{ number_format($product->monthly_price, 2) }}/month
            ({{ ucfirst($product->product_type) }})
        </option>
    @endforeach
</template>

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="newCustomerModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div id="newCustomerError" class="alert alert-danger" style="display:none;"></div>
                <div id="newCustomerSuccess" class="alert alert-success" style="display:none;"></div>
                
                <form id="newCustomerForm">
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
                                    <input type="text" class="form-control" id="name" name="name" required placeholder="Enter full name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address">
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
                                    <input type="text" class="form-control" id="phone" name="phone" required placeholder="Enter phone number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Customer ID</label>
                                    <input type="text" class="form-control" id="customer_id" name="customer_id" placeholder="Auto-generated if left blank">
                                    <small class="text-muted">Leave blank to auto-generate customer ID</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="address" class="form-label required"> Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required placeholder="Enter residential address"></textarea>
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="connection_address" class="form-label required">Connection Address</label>
                                    <textarea class="form-control" id="connection_address" name="connection_address" rows="3" required placeholder="Enter connection installation address"></textarea>
                                </div>
                            </div> -->
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
                                    <select class="form-select" id="id_type" name="id_type">
                                        <option value="">Select ID Type (Optional)</option>
                                        <option value="NID">National ID (NID)</option>
                                        <option value="Passport">Passport</option>
                                        <option value="Driving License">Driving License</option>
                                    </select>
                                    <small class="text-muted">This field is optional</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_number" class="form-label">ID Number</label>
                                    <input type="text" class="form-control" id="id_number" name="id_number" placeholder="Enter ID number">
                                    <small class="text-muted">This field is optional</small>
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
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="is_active">
                                            <span class="status-label">Active Customer</span>
                                            <small class="status-description">Customer account will be active immediately</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveCustomerBtn">
                    <i class="fas fa-save me-1"></i>Create Customer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.page-title {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 3px solid #3498db;
}
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}
.card:hover {
    transform: translateY(-5px);
}
.product-row {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s;
}
.product-row:hover {
    border-color: #3498db;
    background: #f1f3f4;
}
.product-amount {
    font-size: 1.1rem;
    padding: 0.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px;
    text-align: center;
    border: 2px solid #e9ecef;
    min-height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.amount-final {
    font-weight: 700;
    font-size: 1.1rem;
}
.monthly-price {
    font-weight: 600;
    color: #495057;
}
.remove-product-btn {
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.summary-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
}
.summary-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1rem;
}
.summary-product-item {
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s;
}
.summary-product-item:hover {
    border-color: #3498db;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.summary-product-name {
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}
.summary-product-details {
    font-size: 0.85rem;
}
.summary-product-amount {
    font-size: 1.1rem;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.summary-row.total {
    border-bottom: none;
    font-size: 1.2rem;
    color: #2c3e50;
    padding: 1rem 0 0 0;
}
.summary-divider {
    height: 2px;
    background: #3498db;
    margin: 1rem 0;
}
.customer-results-container {
    border: 1px solid #dee2e6;
    border-radius: 5px;
    background: #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.customer-result-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: all 0.2s;
    display: block;
}
.customer-result-item:hover {
    background: #e3f2fd;
    border-left: 3px solid #3498db;
    padding-left: 12px;
}
.customer-result-item:last-child {
    border-bottom: none;
}
.no-results-message {
    padding: 2rem 1rem;
    text-align: center;
}
.no-results-message i {
    opacity: 0.3;
}
.selected-customer-card {
    margin-top: 15px;
}
.customer-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1rem;
}
.customer-details {
    font-size: 0.9rem;
    color: #6c757d;
}
.customer-address {
    font-size: 0.8rem;
}
.product-select option:disabled {
    color: #ccc;
    background: #f8f9fa;
}
.btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
.form-text {
    font-size: 0.8rem;
    color: #6c757d;
}
.invoice-generation-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 10px;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.invoice-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #3498db;
}
.invoice-preview-container {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}
.invoice-preview-header {
    background: #3498db;
    color: white;
    padding: 0.75rem 1rem;
    font-weight: 600;
}
.invoice-preview-list {
    padding: 1rem;
    max-height: 300px;
    overflow-y: auto;
}
.invoice-preview-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s;
}
.invoice-preview-item:hover {
    border-color: #3498db;
    box-shadow: 0 2px 4px rgba(52, 152, 219, 0.1);
}
.invoice-preview-item:last-child {
    margin-bottom: 0;
}
.invoice-number {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    color: #2c3e50;
    font-size: 1.1rem;
}
.invoice-product-name {
    font-weight: 600;
    color: #34495e;
}
.invoice-amount {
    font-weight: 700;
    color: #27ae60;
    font-size: 1.2rem;
}
.invoice-details {
    font-size: 0.85rem;
    color: #7f8c8d;
}
.modal-xl {
    max-width: 1000px;
}
.form-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}
.section-header {
    color: #2c3e50;
    font-weight: 600;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #3498db;
}
.required::after {
    content: " *";
    color: #e74c3c;
}
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
.toast {
    min-width: 300px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
}
.toast-body {
    font-size: 1rem;
    font-weight: 500;
    padding: 1rem;
}
.toast .btn-close-white {
    filter: brightness(0) invert(1);
}
@keyframes slideInAndHighlight {
    0% {
        transform: translateX(-20px);
        opacity: 0;
        background-color: #d4edda;
    }
    50% {
        transform: translateX(0);
        opacity: 1;
        background-color: #d4edda;
    }
    100% {
        background-color: transparent;
    }
}
.invoice-item {
    transition: all 0.3s ease;
}
.invoice-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.monthly-price[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
}
.monthly-price:not([readonly]) {
    background-color: #fff;
    border-color: #ffc107;
    font-weight: 600;
}
.monthly-price.border-warning {
    border-width: 2px;
}
.custom-price-badge {
    font-size: 0.75rem;
}
.use-custom-price:checked {
    background-color: #ffc107;
    border-color: #ffc107;
}
</style>
@endsection

@section('scripts')
<script>
function checkExistingProducts(customerId, productId, index) {
    if (!customerId || !productId) return Promise.resolve(true);
    const baseUrl = '{{ url("/") }}';
    return fetch(`${baseUrl}/admin/customer-to-products/check-existing?customer_id=${customerId}&product_id=${productId}`)
        .then(r => r.json())
        .then(data => {
            const select = document.querySelector(`.product-select[data-index="${index}"]`);
            if (!select) return true;
            
            const row = select.closest('.product-row');
            if (!row) return true;
            
            let warn = row.querySelector('.product-warning');
            if (data.exists) {
                if (!warn) {
                    warn = document.createElement('div');
                    warn.className = 'product-warning alert alert-warning mt-2';
                    row.appendChild(warn);
                }
                warn.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>${data.message}`;
                if (warn) warn.style.display = 'block';
                select.classList.add('is-invalid');
                return false;
            } else {
                if (warn) warn.style.display = 'none';
                select.classList.remove('is-invalid');
                return true;
            }
        })
        .catch(err => {
            console.error('Error checking existing products:', err);
            return true; // Allow submission on error
        });
}

// Global error handler for uncaught errors
window.addEventListener('error', function(e) {
    console.error('Uncaught error in assign page:', e.error);
    // Prevent the error from breaking the page
    return true;
});

// Safe DOM operation helper
function safeSetStyle(element, property, value) {
    try {
        if (element && element.style && typeof element.style[property] !== 'undefined') {
            element.style[property] = value;
            return true;
        }
    } catch (error) {
        console.error('Failed to set style:', error);
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    let productCount = 1;
    let productAmounts = {};
    let selectedProducts = new Set();
    let availableIndexes = [];

    const customerSearch = document.getElementById('customerSearch');
    const customerResults = document.getElementById('customerResults');
    const selectedCustomer = document.getElementById('selectedCustomer');
    const customerIdInput = document.getElementById('customerId');
    const submitBtn = document.getElementById('submitBtn');
    const productOptionsTemplate = document.getElementById('productOptionsTemplate');

    // Check if essential elements exist
    if (!customerSearch || !customerResults || !customerIdInput) {
        console.error('Essential DOM elements not found on assign page');
        return;
    }

    // Check if we have a pre-selected customer from URL parameter
    @if(isset($preSelectedCustomer) && $preSelectedCustomer)
        // Auto-select the pre-selected customer
        selectCustomer(
            '{{ $preSelectedCustomer->c_id }}',
            '{{ $preSelectedCustomer->name }}',
            '{{ $preSelectedCustomer->phone ?? "No phone" }}',
            '{{ $preSelectedCustomer->email ?? "No email" }}',
            '{{ $preSelectedCustomer->customer_id }}'
        );
        
        // Show info toast
        showToast(`Customer "${{ $preSelectedCustomer->name }}" has been pre-selected for product assignment.`);
    @else
        // Check if we need to auto-select a newly created customer
        const newCustomerId = sessionStorage.getItem('newCustomerId');
        if (newCustomerId) {
            const name = sessionStorage.getItem('newCustomerName');
            const phone = sessionStorage.getItem('newCustomerPhone');
            const email = sessionStorage.getItem('newCustomerEmail');
            const custId = sessionStorage.getItem('newCustomerCustomerId');
            
            // Auto-select the customer
            selectCustomer(newCustomerId, name, phone, email, custId);
            
            // Show success toast
            showToast(`Customer "${name}" has been created and selected!`);
            
            // Clear sessionStorage
            sessionStorage.removeItem('newCustomerId');
            sessionStorage.removeItem('newCustomerName');
            sessionStorage.removeItem('newCustomerPhone');
            sessionStorage.removeItem('newCustomerEmail');
            sessionStorage.removeItem('newCustomerCustomerId');
        }
    @endif

    function updateSubmitButton() {
        const hasCustomer = !!customerIdInput.value;
        const productSelects = Array.from(document.querySelectorAll('.product-select'));
        const hasProducts = productSelects.some(sel => sel.value && sel.value !== '');
        const shouldEnable = hasCustomer && hasProducts;
        submitBtn.disabled = !shouldEnable;
        submitBtn.classList.toggle('btn-success', shouldEnable);
        submitBtn.classList.toggle('btn-secondary', !shouldEnable);
    }

    // Customer Search with Auto-filtering
    customerSearch.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        
        if (query.length === 0) {
            if (customerResults) customerResults.style.display = 'none';
            // Reset all items to be visible for next search
            document.querySelectorAll('.customer-result-item').forEach(item => {
                if (item && item.style) item.style.display = 'block';
            });
            return;
        }

        if (customerResults) customerResults.style.display = 'block';
        let hasMatch = false;

        document.querySelectorAll('.customer-result-item').forEach(item => {
            if (!item) return;
            const name = (item.dataset.customerName || '').toLowerCase();
            const phone = (item.dataset.customerPhone || '').toLowerCase();
            const email = (item.dataset.customerEmail || '').toLowerCase();
            const custId = (item.dataset.customerCustomerid || '').toLowerCase();

            const matches = name.includes(query) || phone.includes(query) || email.includes(query) || custId.includes(query);
            
            if (matches) {
                if (item) item.style.display = 'block';
                hasMatch = true;
            } else {
                if (item) item.style.display = 'none';
            }
        });

        // Show "no results" message if no matches
        let noResultsMsg = customerResults.querySelector('.no-results-message');
        if (!hasMatch) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results-message p-3 text-center text-muted';
                noResultsMsg.innerHTML = `
                    <i class="fas fa-search fa-2x mb-2 opacity-50"></i>
                    <div>No customers found for "<strong>${escapeHtml(query)}</strong>"</div>
                    <small class="d-block mt-2">Try searching by name, phone, email, or ID</small>
                `;
                customerResults.appendChild(noResultsMsg);
            } else {
                noResultsMsg.innerHTML = `
                    <i class="fas fa-search fa-2x mb-2 opacity-50"></i>
                    <div>No customers found for "<strong>${escapeHtml(query)}</strong>"</div>
                    <small class="d-block mt-2">Try searching by name, phone, email, or ID</small>
                `;
                if (noResultsMsg) noResultsMsg.style.display = 'block';
            }
        } else {
            if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        }
    });
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Show results when clicking on search input
    customerSearch.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length > 0) {
            if (customerResults) customerResults.style.display = 'block';
        }
    });
    
    // Also trigger search on click to show all results
    customerSearch.addEventListener('click', function() {
        if (this.value.trim().length === 0) {
            // Show all customers when clicking empty search
            if (customerResults) customerResults.style.display = 'block';
            document.querySelectorAll('.customer-result-item').forEach(item => {
                if (item) item.style.display = 'block';
            });
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (customerSearch && customerResults && !customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
            customerResults.style.display = 'none';
        }
    });

    // FIXED: Customer Click Selection
    customerResults.addEventListener('click', function(e) {
        const item = e.target.closest('.customer-result-item');
        if (!item || item.classList.contains('hidden')) return;

        selectCustomer(
            item.dataset.customerId || '',
            item.dataset.customerName || '',
            item.dataset.customerPhone || 'No phone',
            item.dataset.customerEmail || 'No email',
            item.dataset.customerCustomerid || ''
        );
    });

    function getNextAvailableIndex() {
        return availableIndexes.length > 0 ? availableIndexes.shift() : productCount++;
    }

    function setupCustomPriceToggle(idx) {
        const checkbox = document.querySelector(`.use-custom-price[data-index="${idx}"]`);
        const priceInput = document.querySelector(`.monthly-price[data-index="${idx}"]`);
        const badge = document.getElementById(`customBadge${idx}`);
        const billingMonths = document.querySelector(`.billing-months[data-index="${idx}"]`);
        const productSelect = document.querySelector(`.product-select[data-index="${idx}"]`);

        if (!checkbox || !priceInput || !badge) return;

        checkbox.addEventListener('change', function () {
            if (this.checked) {
                priceInput.removeAttribute('readonly');
                priceInput.classList.add('border-warning');
                if (badge) badge.style.display = 'block';
                priceInput.focus();
                priceInput.select();
            } else {
                priceInput.setAttribute('readonly', true);
                priceInput.classList.remove('border-warning');
                if (badge) badge.style.display = 'none';
                // Reset to calculated price
                const months = parseInt(billingMonths.value) || 1;
                const monthly = parseFloat(productSelect.selectedOptions[0]?.dataset.price) || 0;
                priceInput.value = (monthly * months).toFixed(2);
                calculateProductAmount(idx);
                updateInvoicePreview();
            }
        });

        priceInput.addEventListener('input', () => {
            if (!priceInput.hasAttribute('readonly')) {
                calculateProductAmount(idx);
                updateInvoicePreview();
            }
        });
    }

    function setupCustomDueDateToggle(idx) {
        const checkbox = document.querySelector(`.use-custom-due-date[data-index="${idx}"]`);
        const dateInput = document.querySelector(`.due-date-input[data-index="${idx}"]`);
        const assignDateInput = document.querySelector(`.assign-date[data-index="${idx}"]`);
        const billingMonths = document.querySelector(`.billing-months[data-index="${idx}"]`);

        if (!checkbox || !dateInput) return;

        // Store the original date value for reference
        let originalDateValue = '';

        checkbox.addEventListener('change', function () {
            if (this.checked) {
                dateInput.removeAttribute('readonly');
                dateInput.classList.add('border-warning');
                dateInput.focus();
                
                // Store the current date value
                originalDateValue = dateInput.value;
                
                // Add event listener to restrict changes to day only
                dateInput.addEventListener('input', restrictDateChange);
                dateInput.addEventListener('change', restrictDateChange);
            } else {
                dateInput.setAttribute('readonly', true);
                dateInput.classList.remove('border-warning');
                // Remove event listeners
                dateInput.removeEventListener('input', restrictDateChange);
                dateInput.removeEventListener('change', restrictDateChange);
                // Reset to calculated due date
                calculateAndSetDueDate(idx);
            }
        });

        // Function to restrict date changes to day only
        function restrictDateChange(e) {
            if (!originalDateValue) return;
            
            const currentDate = e.target.value;
            if (!currentDate) return;
            
            // Parse dates
            const originalParts = originalDateValue.split('-');
            const currentParts = currentDate.split('-');
            
            if (originalParts.length !== 3 || currentParts.length !== 3) return;
            
            // Keep year and month from original, only allow day to change
            const restrictedDate = `${originalParts[0]}-${originalParts[1]}-${currentParts[2]}`;
            e.target.value = restrictedDate;
        }
    }

    function calculateAndSetDueDate(idx) {
        const assignDateInput = document.querySelector(`.assign-date[data-index="${idx}"]`);
        const billingMonths = document.querySelector(`.billing-months[data-index="${idx}"]`);
        const dateInput = document.querySelector(`.due-date-input[data-index="${idx}"]`);

        if (!assignDateInput || !billingMonths || !dateInput) return;

        const assignDate = assignDateInput.value;
        const months = parseInt(billingMonths.value) || 1;

        if (!assignDate) {
            dateInput.value = '';
            return;
        }

        // Calculate due date by adding billing cycle months to assign date
        const date = new Date(assignDate);
        date.setMonth(date.getMonth() + months);
        
        // Format as YYYY-MM-DD
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        dateInput.value = `${year}-${month}-${day}`;
    }

    document.getElementById('addProductBtn').addEventListener('click', function () {
        const idx = getNextAvailableIndex();
        const row = document.createElement('div');
        row.className = 'product-row mb-3';
        row.dataset.index = idx;
        const displayNumber = document.querySelectorAll('.product-row').length + 1;

        row.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Product ${displayNumber} *</label>
                    <select class="form-select product-select" name="products[${idx}][product_id]" data-index="${idx}" required>
                        <option value="">Select a product...</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Billing cycle *</label>
                    <select class="form-select billing-months" name="products[${idx}][billing_cycle_months]" data-index="${idx}" required>
                        <option value="1">1 Month</option>
                        <option value="2">2 Months</option>
                        <option value="3" selected>3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Subtotal</label>
                        <div class="form-check mb-0">
                            <input class="form-check-input use-custom-price" type="checkbox" data-index="${idx}" id="useCustomPrice${idx}">
                            <label class="form-check-label small text-muted" for="useCustomPrice${idx}">
                                Custom
                            </label>
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" class="form-control monthly-price" 
                               name="products[${idx}][monthly_price]" data-index="${idx}" value="0" min="0" step="0.01" readonly>
                    </div>
                    <div class="custom-price-badge mt-1" id="customBadge${idx}" style="display:none;">
                        <span class="badge bg-warning text-dark small">
                            <i class="fas fa-edit me-1"></i>Custom
                        </span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Assign Date *</label>
                    <input type="date" class="form-control assign-date" name="products[${idx}][assign_date]"
                           value="{{ date('Y-m-d') }}" data-index="${idx}" required>
                </div>
                <div class="col-md-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Due Date *</label>
                        <div class="form-check mb-0">
                            <input class="form-check-input use-custom-due-date" type="checkbox" data-index="${idx}" id="useCustomDueDate${idx}">
                            <label class="form-check-label small text-muted" for="useCustomDueDate${idx}">
                                Custom
                            </label>
                        </div>
                    </div>
                    <input type="date" class="form-control due-date-input" 
                           name="products[${idx}][custom_due_date]" data-index="${idx}" 
                           value="" readonly required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Total</label>
                    <div class="product-amount" data-index="${idx}">
                        <span class="amount-final fw-bold text-success">৳ 0</span>
                    </div>
                </div>
            </div>`;

        document.getElementById('productsContainer').appendChild(row);
        const select = row.querySelector('.product-select');
        select.innerHTML = '<option value="">Select a product...</option>' + productOptionsTemplate.innerHTML;
        updateProductOptions();

        select.addEventListener('change', () => {
            updateSelectedProducts();
            const checkbox = row.querySelector('.use-custom-price');
            if (!checkbox || !checkbox.checked) {
                autoFillTotalPrice(idx);
            }
            calculateProductAmount(idx);
            if (customerIdInput.value && select.value) checkExistingProducts(customerIdInput.value, select.value, idx);
            updateSubmitButton();
            updateInvoicePreview();
        });

        row.querySelector('.monthly-price').addEventListener('input', () => {
            calculateProductAmount(idx);
            updateSubmitButton();
            updateInvoicePreview();
        });

        row.querySelector('.billing-months').addEventListener('change', () => {
            const checkbox = row.querySelector('.use-custom-price');
            if (!checkbox || !checkbox.checked) {
                autoFillTotalPrice(idx);
            }
            calculateProductAmount(idx);
            updateSubmitButton();
            updateInvoicePreview();
            
            // Recalculate due date when billing cycle changes
            const dueDateCheckbox = row.querySelector('.use-custom-due-date');
            if (!dueDateCheckbox || !dueDateCheckbox.checked) {
                calculateAndSetDueDate(idx);
            }
        });

        // Setup custom price toggle for this row
        setupCustomPriceToggle(idx);

        // Setup custom due date toggle for this row
        setupCustomDueDateToggle(idx);

        // Auto-calculate due date when assign date changes
        row.querySelector('.assign-date').addEventListener('change', function() {
            const dueDateCheckbox = row.querySelector('.use-custom-due-date');
            if (!dueDateCheckbox || !dueDateCheckbox.checked) {
                calculateAndSetDueDate(idx);
            }
        });

        const summary = document.createElement('div');
        summary.className = 'summary-product-item';
        summary.id = `productSummary${idx}`;
        summary.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark summary-product-name">Product ${displayNumber}</div>
                    <div class="text-muted small summary-product-details">
                        <span class="summary-price">৳0.00/month</span> × 
                        <span class="summary-cycle">1 month</span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-success summary-product-amount">৳ 0.00</div>
                </div>
            </div>`;
        document.getElementById('summaryDetails').appendChild(summary);

        updateAllProductLabels();
        updateSubmitButton();
        updateInvoicePreview();
    });

    function updateAllProductLabels() {
        document.querySelectorAll('.product-row').forEach((row, i) => {
            const idx = row.dataset.index;
            row.querySelector('.form-label').textContent = `Product ${i + 1} *`;
            updateSummaryItem(idx, i + 1);
        });
    }
    
    function updateSummaryItem(idx, displayNumber) {
        const sum = document.getElementById(`productSummary${idx}`);
        if (!sum) return;
        
        const sel = document.querySelector(`.product-select[data-index="${idx}"]`);
        const monthsSel = document.querySelector(`.billing-months[data-index="${idx}"]`);
        const priceInput = document.querySelector(`.monthly-price[data-index="${idx}"]`);
        const checkbox = document.querySelector(`.use-custom-price[data-index="${idx}"]`);
        
        const productName = sel?.selectedOptions[0]?.text?.split(' - ')[0] || `Product ${displayNumber}`;
        const monthlyPrice = parseFloat(sel?.selectedOptions[0]?.dataset.price) || 0;
        const months = parseInt(monthsSel?.value) || 1;
        const editedTotal = parseFloat(priceInput?.value) || 0;
        const isCustom = checkbox?.checked || false;
        
        const cycleText = months === 1 ? '1 month' : 
                         months === 2 ? '2 months' :
                         months === 3 ? '3 months' :
                         months === 6 ? '6 months' :
                         months === 12 ? '12 months' : `${months} months`;
        
        // Show "Custom" instead of price when custom checkbox is checked
        const priceDisplay = isCustom 
            ? '<span class="badge bg-warning text-dark">Custom</span>' 
            : `৳${monthlyPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}/month`;
        
        sum.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark summary-product-name">${productName}</div>
                    <div class="text-muted small summary-product-details">
                        <span class="summary-price">${priceDisplay}</span> × 
                        <span class="summary-cycle">${cycleText}</span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-success summary-product-amount">৳ ${editedTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                </div>
            </div>`;
    }

    function updateProductOptions() {
        document.querySelectorAll('.product-select').forEach(sel => {
            const cur = sel.value;
            sel.innerHTML = '<option value="">Select a product...</option>' + productOptionsTemplate.innerHTML;
            sel.querySelectorAll('option').forEach(opt => {
                if (opt.value && selectedProducts.has(opt.value) && opt.value !== cur) {
                    opt.disabled = true;
                    opt.innerHTML += ' (already selected)';
                }
            });
            sel.value = cur;
        });
    }

    function updateSelectedProducts() {
        selectedProducts.clear();
        document.querySelectorAll('.product-select').forEach(s => s.value && selectedProducts.add(s.value));
        updateProductOptions();
    }

    function autoFillTotalPrice(idx) {
        const sel = document.querySelector(`.product-select[data-index="${idx}"]`);
        const months = parseInt(document.querySelector(`.billing-months[data-index="${idx}"]`).value) || 1;
        const priceInput = document.querySelector(`.monthly-price[data-index="${idx}"]`);
        const monthly = parseFloat(sel.selectedOptions[0]?.dataset.price) || 0;
        const total = (monthly * months).toFixed(2);
        if (parseFloat(total) > 0) priceInput.value = total;
    }

    function calculateProductAmount(idx) {
        const priceInput = document.querySelector(`.monthly-price[data-index="${idx}"]`);
        const amtDiv = document.querySelector(`.product-amount[data-index="${idx}"]`);

        const finalAmount = parseFloat(priceInput.value) || 0;

        // Update the amount display with 2 decimal places
        amtDiv.querySelector('.amount-final').textContent = `৳ ${finalAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

        productAmounts[idx] = finalAmount;

        const rows = Array.from(document.querySelectorAll('.product-row'));
        const displayNum = rows.findIndex(r => r.dataset.index == idx) + 1;
        
        // Update summary with full details
        updateSummaryItem(idx, displayNum);

        calculateTotal();
    }
    
    function calculateTotal() {
        const total = Object.values(productAmounts).reduce((a, b) => a + b, 0);
        document.getElementById('totalAmount').textContent = `৳ ${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }

    

    document.getElementById('assignProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!customerIdInput.value) {
            alert('Select a customer.');
            return;
        }
        
        const selects = Array.from(document.querySelectorAll('.product-select')).filter(s => s.value);
        if (selects.length === 0) {
            alert('Select at least one product.');
            return;
        }
        
        if (new Set(selects.map(s => s.value)).size !== selects.length) {
            alert('Duplicate products not allowed.');
            return;
        }

        const today = new Date().toISOString().split('T')[0];
        if (Array.from(document.querySelectorAll('.assign-date')).some(i => i.value > today)) {
            alert('Assign date cannot be in the future.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Checking...';

        Promise.all(selects.map(s => checkExistingProducts(customerIdInput.value, s.value, s.dataset.index)))
            .then(results => {
                if (!results.every(r => r)) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Assign Products';
                    alert('Cannot assign: product already exists for this customer.');
                    return;
                }
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Assigning...';
                this.submit();
            });
    });

    const initSel = document.querySelector('.product-select[data-index="0"]');
    const initPrice = document.querySelector('.monthly-price[data-index="0"]');
    const initMonths = document.querySelector('.billing-months[data-index="0"]');
    const initDate = document.querySelector('.assign-date[data-index="0"]');

    initSel?.addEventListener('change', () => {
        updateSelectedProducts();
        const checkbox = document.querySelector('.use-custom-price[data-index="0"]');
        if (!checkbox || !checkbox.checked) {
            autoFillTotalPrice(0);
        }
        calculateProductAmount(0);
        if (customerIdInput.value && initSel.value) checkExistingProducts(customerIdInput.value, initSel.value, 0);
        updateSubmitButton();
        updateInvoicePreview();
    });

    initPrice?.addEventListener('input', () => { 
        calculateProductAmount(0); 
        updateSubmitButton(); 
        updateInvoicePreview();
    });
    
    initMonths?.addEventListener('change', () => { 
        const checkbox = document.querySelector('.use-custom-price[data-index="0"]');
        if (!checkbox || !checkbox.checked) {
            autoFillTotalPrice(0);
        }
        calculateProductAmount(0); 
        updateSubmitButton();
        updateInvoicePreview();
        
        // Recalculate due date when billing cycle changes
        const dueDateCheckbox = document.querySelector('.use-custom-due-date[data-index="0"]');
        if (!dueDateCheckbox || !dueDateCheckbox.checked) {
            calculateAndSetDueDate(0);
        }
    });

    // Setup custom price toggle for initial row
    setupCustomPriceToggle(0);

    // Setup custom due date toggle for initial row
    setupCustomDueDateToggle(0);

    // Auto-calculate due date for initial row
    initDate?.addEventListener('change', function() {
        const dueDateCheckbox = document.querySelector('.use-custom-due-date[data-index="0"]');
        if (!dueDateCheckbox || !dueDateCheckbox.checked) {
            calculateAndSetDueDate(0);
        }
    });
    
    // Update button text based on invoice generation checkbox
    document.getElementById('generateInvoice')?.addEventListener('change', function() {
        const submitBtn = document.getElementById('submitBtn');
        if (this.checked) {
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Assign Products & Generate Invoices';
        } else {
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Assign Products Only';
        }
        updateInvoicePreview();
    });

    if (customerIdInput) customerIdInput.addEventListener('change', () => {
        updateSubmitButton();
        updateInvoicePreview();
    });

    // Calculate initial due date based on assign date and billing cycle
    if (initDate?.value) {
        calculateAndSetDueDate(0);
    }

    updateSelectedProducts();
    if (initSel?.value) { 
        autoFillTotalPrice(0); 
        calculateProductAmount(0); 
    }
    updateSubmitButton();
    
    // Add event listeners for invoice preview updates
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select') || 
            e.target.classList.contains('monthly-price') || 
            e.target.classList.contains('billing-months')) {
            updateInvoicePreview();
        }
    });
    
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('monthly-price')) {
            updateInvoicePreview();
        }
    });
});

function selectCustomer(id, name, phone, email, custId) {
    const customerIdInput = document.getElementById('customerId');
    const selectedCustomerName = document.getElementById('selectedCustomerName');
    const selectedCustomerDetails = document.getElementById('selectedCustomerDetails');
    const selectedCustomerId = document.getElementById('selectedCustomerId');
    const customerSearch = document.getElementById('customerSearch');
    const customerResults = document.getElementById('customerResults');
    const selectedCustomer = document.getElementById('selectedCustomer');
    
    if (!customerIdInput) return;
    
    customerIdInput.value = id;
    
    if (selectedCustomerName) selectedCustomerName.textContent = name;
    
    let details = phone !== 'No phone' ? `Phone: ${phone} • ` : '';
    details += `ID: ${custId}` + (email !== 'No email' ? ` • Email: ${email}` : '');
    if (selectedCustomerDetails) selectedCustomerDetails.textContent = details;
    if (selectedCustomerId) selectedCustomerId.textContent = `Customer ID: ${custId}`;
    if (customerSearch) customerSearch.value = name;
    if (customerResults) customerResults.style.display = 'none';
    if (selectedCustomer) selectedCustomer.style.display = 'block';

    document.querySelectorAll('.product-select').forEach(sel => {
        if (sel && sel.value) checkExistingProducts(id, sel.value, sel.dataset.index);
    });
    
    customerIdInput.dispatchEvent(new Event('change'));
    updateInvoicePreview();
}

function clearCustomerSelection() {
    const customerIdInput = document.getElementById('customerId');
    const selectedCustomer = document.getElementById('selectedCustomer');
    const customerSearch = document.getElementById('customerSearch');
    const customerResults = document.getElementById('customerResults');
    
    if (customerIdInput) customerIdInput.value = '';
    if (selectedCustomer) selectedCustomer.style.display = 'none';
    if (customerSearch) customerSearch.value = '';
    if (customerResults) customerResults.style.display = 'none';
    
    document.querySelectorAll('.product-warning').forEach(w => {
        if (w) w.style.display = 'none';
    });
    document.querySelectorAll('.product-select').forEach(s => {
        if (s) s.classList.remove('is-invalid');
    });
    
    if (customerIdInput) customerIdInput.dispatchEvent(new Event('change'));
    updateInvoicePreview();
}

// Generate invoice preview with real invoice numbers from server
async function updateInvoicePreview() {
    const generateInvoiceCheckbox = document.getElementById('generateInvoice');
    const invoicePreviewList = document.getElementById('invoicePreviewList');
    const customerIdInput = document.getElementById('customerId');
    
    if (!generateInvoiceCheckbox || !generateInvoiceCheckbox.checked || !customerIdInput.value) {
        invoicePreviewList.innerHTML = `
            <div class="text-muted text-center py-3">
                <i class="fas fa-file-invoice fa-2x mb-2 opacity-50"></i>
                <div>${!customerIdInput.value ? 'Select a customer first' : 'Invoice generation is disabled'}</div>
            </div>`;
        return;
    }
    
    const productRows = document.querySelectorAll('.product-row');
    const products = [];
    
    productRows.forEach((row, index) => {
        const select = row.querySelector('.product-select');
        const priceInput = row.querySelector('.monthly-price');
        const monthsSelect = row.querySelector('.billing-months');
        const assignDateInput = row.querySelector('.assign-date');
        const checkbox = row.querySelector('.use-custom-price');
        
        if (select && select.value && priceInput) {
            const productName = select.selectedOptions[0]?.text?.split(' - ')[0] || 'Unknown Product';
            const amount = parseFloat(priceInput.value) || 0;
            const months = parseInt(monthsSelect?.value) || 1;
            const monthlyPrice = parseFloat(select.selectedOptions[0]?.dataset.price) || 0;
            const assignDate = assignDateInput?.value || new Date().toISOString().split('T')[0];
            const isCustom = checkbox?.checked || false;
            
            if (amount > 0) {
                products.push({
                    productName: productName,
                    amount: amount,
                    months: months,
                    monthlyPrice: monthlyPrice,
                    assignDate: assignDate,
                    isCustom: isCustom
                });
            }
        }
    });
    
    if (products.length === 0) {
        invoicePreviewList.innerHTML = `
            <div class="text-muted text-center py-3">
                <i class="fas fa-file-invoice fa-2x mb-2 opacity-50"></i>
                <div>Select products to see invoice preview</div>
            </div>`;
        return;
    }
    
    // Show loading state
    invoicePreviewList.innerHTML = `
        <div class="text-center py-3">
            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
            <div>Generating invoice numbers...</div>
        </div>`;
    
    try {
        // Fetch real invoice numbers from server
        const baseUrl = '{{ url("/") }}';
        const response = await fetch(`${baseUrl}/admin/customer-to-products/preview-invoice-numbers`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                customer_id: customerIdInput.value,
                products: products
            })
        });
        
        if (!response.ok) {
            throw new Error('Failed to generate invoice numbers');
        }
        
        const data = await response.json();
        
        if (!data.success || !data.invoices) {
            throw new Error(data.message || 'Invalid response from server');
        }
        
        // Display invoices with real numbers
        let html = '';
        data.invoices.forEach((invoice, index) => {
            const cycleText = invoice.months === 1 ? '1 month' : 
                             invoice.months === 3 ? '3 months' :
                             invoice.months === 6 ? '6 months' :
                             invoice.months === 12 ? '12 months' : `${invoice.months} months`;
            
            // Show "Custom" badge if custom price is used
            const priceDisplay = invoice.isCustom 
                ? '<span class="badge bg-warning text-dark">Custom</span>' 
                : `৳${invoice.monthly_price.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}/month`;
            
            html += `
                <div class="invoice-preview-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <div class="invoice-number mb-1">
                                <i class="fas fa-file-invoice me-2"></i>${invoice.invoice_number}
                            </div>
                            <div class="invoice-product-name mb-1">${invoice.product_name}</div>
                            <div class="invoice-details">
                                <i class="fas fa-tag me-1"></i>${priceDisplay} × ${cycleText}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="invoice-amount">৳${invoice.amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                            <small class="text-muted">Invoice ${index + 1} of ${data.invoices.length}</small>
                        </div>
                    </div>
                </div>`;
        });
        
        invoicePreviewList.innerHTML = html;
        
    } catch (error) {
        console.error('Error generating invoice preview:', error);
        invoicePreviewList.innerHTML = `
            <div class="text-danger text-center py-3">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <div>Failed to generate invoice numbers</div>
                <small>${error.message}</small>
            </div>`;
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('successToast');
    const toastMessage = document.getElementById('toastMessage');
    
    // Update message
    toastMessage.textContent = message;
    
    // Update color based on type
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    if (type === 'error') {
        toastEl.classList.add('bg-danger');
    } else if (type === 'warning') {
        toastEl.classList.add('bg-warning');
    } else if (type === 'info') {
        toastEl.classList.add('bg-info');
    } else {
        toastEl.classList.add('bg-success');
    }
    
    // Show toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: 4000
    });
    toast.show();
}

// Handle new customer form submission
document.getElementById('saveCustomerBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    
    const form = document.getElementById('newCustomerForm');
    const saveBtn = this;
    const errorDiv = document.getElementById('newCustomerError');
    const successDiv = document.getElementById('newCustomerSuccess');
    
    // Validate required fields
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        if (errorDiv) {
            errorDiv.textContent = 'Please fill in all required fields';
            errorDiv.style.display = 'block';
        }
        return;
    }
    
    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    if (errorDiv) errorDiv.style.display = 'none';
    if (successDiv) successDiv.style.display = 'none';
    
    const formData = new FormData(form);
    
    fetch('{{ route("admin.customers.store-ajax") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show toast notification
            showToast(data.message || 'Customer created successfully!');
            
            // Add new customer to the list
            const customer = data.customer;
            const customerItem = document.createElement('div');
            customerItem.className = 'customer-result-item';
            customerItem.dataset.customerId = customer.c_id;
            customerItem.dataset.customerName = customer.name;
            customerItem.dataset.customerPhone = customer.phone || 'No phone';
            customerItem.dataset.customerEmail = customer.email || 'No email';
            customerItem.dataset.customerCustomerid = customer.customer_id;
            
            customerItem.innerHTML = `
                <div class="customer-name">${customer.name}</div>
                <div class="customer-details">
                    ${customer.phone ? '<i class="fas fa-phone me-1"></i>' + customer.phone + ' •' : ''}
                    <i class="fas fa-id-card me-1"></i>ID: ${customer.customer_id}
                    ${customer.email ? '• <i class="fas fa-envelope me-1"></i>' + customer.email : ''}
                </div>
                <div class="customer-address small text-muted mt-1">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    ${customer.address || 'No address provided'}
                </div>
            `;
            
            // Add highlight animation
            customerItem.style.animation = 'slideInAndHighlight 0.5s ease-out';
            
            document.getElementById('customerResults').prepend(customerItem);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newCustomerModal'));
            if (modal) modal.hide();
            
            // Store customer ID in sessionStorage to auto-select after refresh
            sessionStorage.setItem('newCustomerId', customer.c_id);
            sessionStorage.setItem('newCustomerName', customer.name);
            sessionStorage.setItem('newCustomerPhone', customer.phone || 'No phone');
            sessionStorage.setItem('newCustomerEmail', customer.email || 'No email');
            sessionStorage.setItem('newCustomerCustomerId', customer.customer_id);
            
            // Refresh page to get updated customer list
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            // Show error message
            if (errorDiv) {
                errorDiv.textContent = data.message || 'Failed to create customer';
                errorDiv.style.display = 'block';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.message) {
            errorDiv.textContent = error.message;
        } else if (error.errors) {
            // Handle validation errors
            const errorMessages = Object.values(error.errors).flat().join(', ');
            errorDiv.textContent = errorMessages;
        } else {
            errorDiv.textContent = 'An error occurred while creating the customer';
        }
        if (errorDiv) errorDiv.style.display = 'block';
    })
    .finally(() => {
        // Re-enable button
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Create Customer';
    });
});

// Reset form when modal is closed
document.getElementById('newCustomerModal')?.addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('newCustomerForm');
    const errorDiv = document.getElementById('newCustomerError');
    const successDiv = document.getElementById('newCustomerSuccess');
    
    if (form) form.reset();
    if (errorDiv) errorDiv.style.display = 'none';
    if (successDiv) successDiv.style.display = 'none';
    // Remove validation classes
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
});

// Customer ID will be auto-generated by the backend if left empty
</script>
@endsection