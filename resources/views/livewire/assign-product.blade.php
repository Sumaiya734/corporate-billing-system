<div>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-user-tag me-2"></i>Product Assignment Form</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="submit">
                <!-- Customer Search & Selection -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold">Select Customer *</label>
                        
                        <!-- Customer Search Input -->
                        <div class="mb-3">
                            <input type="text" class="form-control" 
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Search customers by name, phone, or customer ID..."
                                   @if($selectedCustomer) disabled @endif>
                            <div class="form-text">Start typing to search for customers (minimum 2 characters)</div>
                        </div>
                        
                        <!-- Customer Results -->
                        @if($search && !$selectedCustomer && count($customers) > 0)
                            <div class="customer-results-container">
                                @foreach($customers as $customer)
                                    <div class="customer-result-item" 
                                         wire:click="selectCustomer({{ $customer->c_id }})"
                                         wire:key="customer-{{ $customer->c_id }}">
                                        <div class="customer-name">
                                            <i class="fas fa-user me-2"></i>{{ $customer->name }}
                                        </div>
                                        <div class="customer-details">
                                            @if($customer->phone)
                                                <i class="fas fa-phone me-1"></i>{{ $customer->phone }} • 
                                            @endif
                                            <i class="fas fa-id-card me-1"></i>ID: {{ $customer->customer_id }}
                                            @if($customer->email)
                                                • <i class="fas fa-envelope me-1"></i>{{ $customer->email }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        @if($search && !$selectedCustomer && count($customers) === 0)
                            <div class="customer-results-container">
                                <div class="customer-result-item text-muted text-center py-3">
                                    <i class="fas fa-search me-2"></i>No customers found for "{{ $search }}"
                                </div>
                            </div>
                        @endif
                        
                        <!-- Selected Customer Display -->
                        @if($selectedCustomer)
                            <div class="selected-customer-card">
                                <div class="card border-success">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1 text-success">
                                                    <i class="fas fa-check-circle me-2"></i>{{ $selectedCustomer->name }}
                                                </h6>
                                                <p class="mb-1 text-muted">
                                                    @if($selectedCustomer->phone)
                                                        <i class="fas fa-phone me-1"></i>{{ $selectedCustomer->phone }} • 
                                                    @endif
                                                    <i class="fas fa-id-card me-1"></i>ID: {{ $selectedCustomer->customer_id }}
                                                    @if($selectedCustomer->email)
                                                        • <i class="fas fa-envelope me-1"></i>{{ $selectedCustomer->email }}
                                                    @endif
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $selectedCustomer->address ?? 'No address provided' }}
                                                </small>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    wire:click="clearCustomer">
                                                <i class="fas fa-times me-1"></i> Change
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Product Selection with Dynamic Adding/Removing -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold">Select Products *</label>
                            <button type="button" class="btn btn-primary btn-sm" wire:click="addRow">
                                <i class="fas fa-plus me-1"></i>Add Another Product
                            </button>
                        </div>
                        
                        <div class="products-container">
                            @foreach($rows as $index)
                                <div class="product-row mb-3" wire:key="product-row-{{ $index }}">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-5">
                                            <label class="form-label">Product {{ $loop->iteration }} *</label>
                                            <select class="form-select product-select @error('productSelections.' . $index) is-invalid @enderror" 
                                                    wire:model="productSelections.{{ $index }}"
                                                    wire:change="calculateTotal">
                                                <option value="">Select a product...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->p_id }}">
                                                        {{ $product->name }} - ৳{{ number_format($product->monthly_price, 2) }}/month 
                                                        ({{ ucfirst($product->product_type) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('productSelections.' . $index)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Billing Months *</label>
                                            <select class="form-select billing-months @error('billingMonths.' . $index) is-invalid @enderror" 
                                                    wire:model="billingMonths.{{ $index }}"
                                                    wire:change="calculateTotal">
                                                <option value="1">1 Month</option>
                                                <option value="2">2 Months</option>
                                                <option value="3">3 Months</option>
                                                <option value="6">6 Months</option>
                                                <option value="12">12 Months</option>
                                            </select>
                                            @error('billingMonths.' . $index)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Assign Date *</label>
                                            <input type="date" class="form-control assign-date @error('assignDates.' . $index) is-invalid @enderror" 
                                                   wire:model="assignDates.{{ $index }}"
                                                   min="{{ date('Y-m-d') }}"
                                                   max="{{ date('Y-m-d', strtotime('+1 year')) }}">
                                            @error('assignDates.' . $index)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">Amount</label>
                                            <div class="product-amount">
                                                ৳ {{ number_format($this->getProductAmount($index), 2) }}
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-product-btn" 
                                                    wire:click="removeRow({{ $index }})"
                                                    @if(count($rows) === 1) disabled @endif
                                                    title="Remove Product">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(count($rows) === 0)
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please add at least one product to assign.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Summary -->
                @if($totalAmount > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="summary-card">
                            <h6 class="summary-title">
                                <i class="fas fa-receipt me-2"></i>Order Summary
                            </h6>
                            <div class="summary-details">
                                @foreach($rows as $index)
                                    @if(!empty($productSelections[$index]))
                                        @php
                                            $productAmount = $this->getProductAmount($index);
                                        @endphp
                                        @if($productAmount > 0)
                                            <div class="summary-row">
                                                <span>
                                                    <i class="fas fa-cube me-2"></i>
                                                    Product {{ $loop->iteration }} ({{ $billingMonths[$index] ?? 1 }} month{{ $billingMonths[$index] > 1 ? 's' : '' }})
                                                </span>
                                                <span class="fw-bold text-success">৳ {{ number_format($productAmount, 2) }}</span>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row total">
                                <span class="fw-bold fs-6">
                                    <i class="fas fa-tag me-2"></i>Total Amount:
                                </span>
                                <span class="fw-bold fs-5 text-primary">৳ {{ number_format($totalAmount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Submit Button -->
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-lg w-100 py-3" 
                                @if(!$selectedCustomer || $totalAmount === 0) disabled @endif
                                wire:loading.attr="disabled">
                            <div wire:loading.remove wire:target="submit">
                                <i class="fas fa-check-circle me-2"></i>Assign Products
                            </div>
                            <div wire:loading wire:target="submit">
                                <i class="fas fa-spinner fa-spin me-2"></i>Processing...
                            </div>
                        </button>
                        
                        @if(!$selectedCustomer)
                            <div class="text-center text-muted small mt-2">
                                <i class="fas fa-info-circle me-1"></i>Please select a customer to enable assignment
                            </div>
                        @elseif($totalAmount === 0)
                            <div class="text-center text-muted small mt-2">
                                <i class="fas fa-info-circle me-1"></i>Please select at least one product to assign
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .product-row {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .product-row:hover {
        border-color: #3498db;
        background-color: #f1f3f4;
    }
    
    .product-amount {
        font-weight: 700;
        color: #27ae60;
        font-size: 1rem;
        padding: 0.5rem;
        background-color: white;
        border-radius: 5px;
        text-align: center;
        border: 1px solid #dee2e6;
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .summary-card {
        background-color: #f8f9fa;
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
        background-color: #3498db;
        margin: 1rem 0;
    }
    
    .customer-results-container {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        margin-top: 10px;
        background-color: white;
    }
    
    .customer-result-item {
        padding: 10px 15px;
        border-bottom: 1px solid #f8f9fa;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .customer-result-item:hover {
        background-color: #e9ecef;
    }
    
    .customer-result-item:last-child {
        border-bottom: none;
    }
    
    .selected-customer-card {
        margin-top: 15px;
    }
    
    .customer-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .customer-details {
        font-size: 0.9rem;
        color: #6c757d;
    }
</style>
</div>