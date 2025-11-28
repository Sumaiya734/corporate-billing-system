@extends('layouts.admin')

@section('title', 'Assign product - Store')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-plus-circle me-2"></i>Assign product to Customer</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.customer-to-products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to products
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

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-user-tag me-2"></i>product Assignment Form</h5>
                </div>
                <div class="card-body">
                    <form id="assignproductForm" action="{{ route('admin.customer-to-products.store') }}" method="POST">
                        @csrf

                        <!-- Customer Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Select Customer *</label>

                                <div class="mb-3">
                                    <input type="text" class="form-control" id="customerSearch"
                                           placeholder="Search customers by name, phone, email, or ID...">
                                    <div class="form-text">Start typing to show the list</div>
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

                        <!-- products -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label fw-bold">Select products *</label>
                                    <button type="button" class="btn btn-primary btn-sm" id="addproductBtn">
                                        <i class="fas fa-plus me-1"></i>Add Another product
                                    </button>
                                </div>

                                <div class="products-container" id="productsContainer">
                                    <div class="product-row mb-3" data-index="0">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-5">
                                                <label class="form-label">product 1 *</label>
                                                <select class="form-select product-select @error('products.0.product_id') is-invalid @enderror"
                                                        name="products[0][product_id]" data-index="0" required>
                                                    <option value="">Select a product...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->p_id }}"
                                                                data-price="{{ $product->monthly_price }}"
                                                                data-type="{{ $product->product_type }}"
                                                                {{ old('products.0.product_id') == $product->p_id ? 'selected' : '' }}>
                                                            {{ $product->name }} - ৳{{ number_format($product->monthly_price,2) }}/month
                                                            ({{ ucfirst($product->product_type) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('products.0.product_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Billing Months *</label>
                                                <select class="form-select billing-months @error('products.0.billing_cycle_months') is-invalid @enderror"
                                                        name="products[0][billing_cycle_months]" data-index="0" required>
                                                    <option value="1" {{ old('products.0.billing_cycle_months','1')=='1'?'selected':'' }}>1 Month</option>
                                                    <option value="2" {{ old('products.0.billing_cycle_months')=='2'?'selected':'' }}>2 Months</option>
                                                    <option value="3" {{ old('products.0.billing_cycle_months')=='3'?'selected':'' }}>3 Months</option>
                                                    <option value="6" {{ old('products.0.billing_cycle_months')=='6'?'selected':'' }}>6 Months</option>
                                                    <option value="12" {{ old('products.0.billing_cycle_months')=='12'?'selected':'' }}>12 Months</option>
                                                </select>
                                                @error('products.0.billing_cycle_months')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Assign Date *</label>
                                                <input type="date" class="form-control assign-date @error('products.0.assign_date') is-invalid @enderror"
                                                       name="products[0][assign_date]"
                                                       value="{{ old('products.0.assign_date', date('Y-m-d')) }}" data-index="0" required>
                                                @error('products.0.assign_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-1">
                                                <label class="form-label">Amount</label>
                                                <div class="product-amount" data-index="0">৳ 0</div>
                                            </div>

                                            <div class="col-md-1">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-product-btn" disabled>
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="summary-card">
                                    <h6 class="summary-title">Order Summary</h6>
                                    <div class="summary-details" id="summaryDetails">
                                        <div class="summary-row" id="productSummary0">
                                            <span>product 1:</span><span>৳ 0</span>
                                        </div>
                                    </div>
                                    <div class="summary-divider"></div>
                                    <div class="summary-row total">
                                        <span class="fw-bold">Total Amount:</span>
                                        <span class="fw-bold" id="totalAmount">৳ 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg w-100" id="submitBtn" disabled>
                                    <i class="fas fa-check me-2"></i>Assign products
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="productOptionsTemplate">
    @foreach($products as $product)
        <option value="{{ $product->p_id }}"
                data-price="{{ $product->monthly_price }}"
                data-type="{{ $product->product_type }}">
            {{ $product->name }} - ৳{{ number_format($product->monthly_price,2) }}/month
            ({{ ucfirst($product->product_type) }})
        </option>
    @endforeach
</template>

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
        font-weight: 700;
        color: #27ae60;
        font-size: 1rem;
        padding: 0.5rem;
        background: #fff;
        border-radius: 5px;
        text-align: center;
        border: 1px solid #dee2e6;
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
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
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    .summary-row.total {
        border-bottom: none;
        font-size: 1.1rem;
        color: #2c3e50;
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
    }
    .customer-result-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f8f9fa;
        cursor: pointer;
        transition: background 0.2s;
    }
    .customer-result-item:hover {
        background: #e9ecef;
    }
    .customer-result-item:last-child {
        border-bottom: none;
    }
    .customer-result-item.hidden {
        display: none;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let productCount = 1;
    let productAmounts = {};
    let selectedproducts = new Set();

    const customerSearch = document.getElementById('customerSearch');
    const customerResults = document.getElementById('customerResults');
    const selectedCustomer = document.getElementById('selectedCustomer');
    const customerIdInput = document.getElementById('customerId');
    const submitBtn = document.getElementById('submitBtn');
    const productOptionsTemplate = document.getElementById('productOptionsTemplate');

    // ---------- 1. Hide list on load ----------
    customerResults.style.display = 'none';
    document.querySelectorAll('.customer-result-item').forEach(i => i.classList.add('hidden'));

    // ---------- 2. Show / filter on typing ----------
    customerSearch.addEventListener('input', function () {
        const query = this.value.trim();

        if (query.length === 0) {
            customerResults.style.display = 'none';
            document.querySelectorAll('.customer-result-item').forEach(i => i.classList.add('hidden'));
            return;
        }

        customerResults.style.display = 'block';
        let hasMatch = false;

        document.querySelectorAll('.customer-result-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            const matches = text.includes(query.toLowerCase());
            item.classList.toggle('hidden', !matches);
            if (matches) hasMatch = true;
        });

        if (!hasMatch) {
            customerResults.innerHTML = `
                <div class="p-3 text-center text-muted border rounded">
                    <i class="fas fa-search fa-2x mb-2 opacity-50"></i>
                    <div>No customers found for "${query}"</div>
                </div>`;
        }
    });

    // ---------- 3. Click to select ----------
    document.querySelectorAll('.customer-result-item').forEach(item => {
        item.addEventListener('click', function () {
            const id   = this.dataset.customerId;
            const name = this.dataset.customerName;
            const phone= this.dataset.customerPhone;
            const email= this.dataset.customerEmail;
            const custId = this.dataset.customerCustomerid;
            selectCustomer(id, name, phone, email, custId);
        });
    });

    // ---------- 4. Add product ----------
    document.getElementById('addproductBtn').addEventListener('click', function () {
        const idx = productCount++;
        const row = document.createElement('div');
        row.className = 'product-row mb-3';
        row.dataset.index = idx;

        row.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">product ${idx + 1} *</label>
                    <select class="form-select product-select" name="products[${idx}][product_id]" data-index="${idx}" required>
                        <option value="">Select a product...</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Billing Months *</label>
                    <select class="form-select billing-months" name="products[${idx}][billing_cycle_months]" data-index="${idx}" required>
                        <option value="1">1 Month</option>
                        <option value="2">2 Months</option>
                        <option value="3" selected>3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assign Date *</label>
                    <input type="date" class="form-control assign-date" name="products[${idx}][assign_date]"
                           value="{{ date('Y-m-d') }}" data-index="${idx}" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Amount</label>
                    <div class="product-amount" data-index="${idx}">৳ 0</div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-product-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>`;

        document.getElementById('productsContainer').appendChild(row);
        const select = row.querySelector('.product-select');
        select.innerHTML = '<option value="">Select a product...</option>' + productOptionsTemplate.innerHTML;
        updateproductOptions();

        select.addEventListener('change', () => { updateSelectedproducts(); calculateproductAmount(idx); });
        row.querySelector('.billing-months').addEventListener('change', () => calculateproductAmount(idx));
        row.querySelector('.remove-product-btn').addEventListener('click', () => removeproduct(idx));

        const summary = document.createElement('div');
        summary.className = 'summary-row';
        summary.id = `productSummary${idx}`;
        summary.innerHTML = `<span>product ${idx + 1}:</span><span>৳ 0</span>`;
        document.getElementById('summaryDetails').appendChild(summary);

        updateRemoveButtons();
    });

    // ---------- 5. Remove product ----------
    function removeproduct(idx) {
        document.querySelector(`.product-row[data-index="${idx}"]`)?.remove();
        document.getElementById(`productSummary${idx}`)?.remove();
        delete productAmounts[idx];
        updateSelectedproducts();
        calculateTotal();
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const btns = document.querySelectorAll('.remove-product-btn');
        btns.forEach(b => b.disabled = btns.length <= 1);
    }

    // ---------- 6. Unique product selection ----------
    function updateproductOptions() {
        document.querySelectorAll('.product-select').forEach(sel => {
            const cur = sel.value;
            sel.innerHTML = '<option value="">Select a product...</option>' + productOptionsTemplate.innerHTML;
            Array.from(sel.options).forEach(opt => {
                if (opt.value && selectedproducts.has(opt.value) && opt.value !== cur) {
                    opt.disabled = true;
                }
            });
            sel.value = cur;
        });
    }

    function updateSelectedproducts() {
        selectedproducts.clear();
        document.querySelectorAll('.product-select').forEach(s => s.value && selectedproducts.add(s.value));
        updateproductOptions();
    }

    // ---------- 7. Amount calculation ----------
    function calculateproductAmount(idx) {
        const sel   = document.querySelector(`.product-select[data-index="${idx}"]`);
        const months= document.querySelector(`.billing-months[data-index="${idx}"]`).value;
        const amtEl = document.querySelector(`.product-amount[data-index="${idx}"]`);
        const sumEl = document.getElementById(`productSummary${idx}`);

        const price = sel.selectedOptions[0]?.dataset.price || 0;
        const total = price * months;

        productAmounts[idx] = total;
        amtEl.textContent = `৳ ${total.toLocaleString()}`;
        if (sumEl) sumEl.innerHTML = `<span>product ${+idx + 1}:</span><span>৳ ${total.toLocaleString()}</span>`;
        calculateTotal();
    }

    function calculateTotal() {
        const tot = Object.values(productAmounts).reduce((a, b) => a + b, 0);
        document.getElementById('totalAmount').textContent = `৳ ${tot.toLocaleString()}`;
    }

    // ---------- 8. Initial events ----------
    document.querySelector('.product-select[data-index="0"]').addEventListener('change', () => { updateSelectedproducts(); calculateproductAmount(0); });
    document.querySelector('.billing-months[data-index="0"]').addEventListener('change', () => calculateproductAmount(0));
    document.querySelector('.remove-product-btn').addEventListener('click', () => removeproduct(0));

    // ---------- 9. Form submit validation ----------
    document.getElementById('assignproductForm').addEventListener('submit', function(e) {
        console.log('Form submit triggered');
        
        if (!customerIdInput.value) {
            e.preventDefault();
            alert('Please select a customer.');
            return;
        }
        
        const selects = document.querySelectorAll('.product-select');
        const filled = Array.from(selects).filter(s => s.value);
        
        if (filled.length === 0) {
            e.preventDefault();
            alert('Please select at least one product.');
            return;
        }
        
        const selectedValues = filled.map(s => s.value);
        if (new Set(selectedValues).size !== selectedValues.length) {
            e.preventDefault();
            alert('You cannot assign the same product twice.');
            return;
        }
        
        console.log('Form validation passed, submitting...');
    });

    // Update submit button state
    function updateSubmitButton() {
        const hasCustomer = !!customerIdInput.value;
        const hasproducts = document.querySelectorAll('.product-select[value!=""]').length > 0;
        submitBtn.disabled = !(hasCustomer && hasproducts);
    }

    // Listen for customer selection changes
    customerIdInput.addEventListener('change', updateSubmitButton);

    // Initial calculations
    updateSelectedproducts();
    calculateproductAmount(0);
    updateSubmitButton();
});

// ---------- Helper functions ----------
function selectCustomer(id, name, phone, email, custId) {
    document.getElementById('customerId').value = id;
    document.getElementById('selectedCustomerName').textContent = name;
    let details = '';
    if (phone !== 'No phone') details += `Phone: ${phone} • `;
    details += `ID: ${custId}`;
    if (email !== 'No email') details += ` • Email: ${email}`;
    document.getElementById('selectedCustomerDetails').textContent = details;
    document.getElementById('selectedCustomerId').textContent = `Customer ID: ${custId}`;
    document.getElementById('customerSearch').value = name;
    document.getElementById('customerResults').style.display = 'none';
    document.getElementById('selectedCustomer').style.display = 'block';
    document.getElementById('submitBtn').disabled = false;
}

function clearCustomerSelection() {
    document.getElementById('customerId').value = '';
    document.getElementById('selectedCustomer').style.display = 'none';
    document.getElementById('customerSearch').value = '';
    document.getElementById('customerResults').style.display = 'none';
    document.getElementById('submitBtn').disabled = true;
    // keep items hidden until next typing
    document.querySelectorAll('.customer-result-item').forEach(i => i.classList.add('hidden'));
}
</script>
@endsection