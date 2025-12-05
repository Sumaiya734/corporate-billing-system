@extends('layouts.admin')

@section('title', 'Edit Customer Product')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-edit me-2"></i>Edit Customer Product</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.customer-to-products.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">Edit Product Assignment</h5>
                </div>
                <div class="card-body">
                    <!-- Customer Info -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-user me-2"></i>Customer Information</h6>
                                <strong>Name:</strong> {{ $customer->name ?? 'N/A' }}<br>
                                <strong>Email:</strong> {{ $customer->email ?? 'No email' }}<br>
                                <strong>Customer ID:</strong> {{ $customer->customer_id ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-secondary">
                                <h6><i class="fas fa-cube me-2"></i>Product Information</h6>
                                <strong>Product:</strong> {{ $product->name ?? 'N/A' }}<br>
                                <strong>Type:</strong> {{ ucfirst($product->product_type ?? 'N/A') }}<br>
                                <strong>Monthly Price:</strong> ৳{{ number_format($product->monthly_price ?? 0, 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- FIXED FORM ACTION - Use cp_id as the route parameter -->
                    <form action="{{ route('admin.customer-to-products.update', $customerProduct->cp_id) }}" method="POST" id="editProductForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="assign_date" class="form-label">Assign Date *</label>
                                <input type="date" class="form-control @error('assign_date') is-invalid @enderror" 
                                       id="assign_date" name="assign_date" 
                                       value="{{ old('assign_date', \Carbon\Carbon::parse($customerProduct->assign_date)->format('Y-m-d')) }}" required>
                                @error('assign_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="due_day" class="form-label">Due Day *</label>
                                <select class="form-select @error('due_day') is-invalid @enderror" id="due_day" name="due_day" required>
                                    @for($i = 1; $i <= 28; $i++)
                                        <option value="{{ $i }}" {{ old('due_day', $customerProduct->due_date ? \Carbon\Carbon::parse($customerProduct->due_date)->format('j') : (\Carbon\Carbon::parse($customerProduct->assign_date)->format('j') > 28 ? 28 : \Carbon\Carbon::parse($customerProduct->assign_date)->format('j'))) == $i ? 'selected' : '' }}>
                                            {{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('due_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Day of the month when payment is due</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Billing Cycle (Months) *</label>
                                <input type="number" class="form-control @error('billing_cycle_months') is-invalid @enderror" 
                                       id="billing_cycle_months" name="billing_cycle_months" 
                                       value="{{ old('billing_cycle_months', $customerProduct->billing_cycle_months) }}" min="1" max="12" required>
                                @error('billing_cycle_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $customerProduct->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status', $customerProduct->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="expired" {{ old('status', $customerProduct->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- NEW: Custom Total Amount Section -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0"><i class="fas fa-money-bill me-2"></i>Custom Pricing</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="custom_total_amount" class="form-label">Total Amount (৳)</label>
                                                <input type="number" step="0.01" min="0" 
                                                       class="form-control @error('custom_total_amount') is-invalid @enderror" 
                                                       id="custom_total_amount" name="custom_total_amount" 
                                                       value="{{ old('custom_total_amount', $customerProduct->custom_price ?? (($product->monthly_price ?? 0) * $customerProduct->billing_cycle_months)) }}">
                                                @error('custom_total_amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">
                                                    Current: ৳{{ number_format($customerProduct->custom_price ?? 0, 2) }}
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="use_subtotal" name="use_subtotal" {{ old('use_subtotal', !$customerProduct->custom_price) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="use_subtotal">
                                                        Use calculated total
                                                    </label>
                                                </div>
                                                <div class="mt-2">
                                                    <small class="text-muted" id="effective-monthly-info">
                                                        Effective monthly: ৳<span id="effective-monthly-amount"></span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-calculator me-2"></i>Final Amount Summary</h6>
                                    <strong>Billing Period:</strong> <span id="billing-months-display">{{ $customerProduct->billing_cycle_months }}</span> month(s)<br>
                                    <strong>Monthly Price:</strong> ৳{{ number_format($customerProduct->product_price, 2) }}<br>
                                    <strong class="h6">Final Total Amount:</strong> 
                                    ৳<span id="final-total-display" class="h6">
                                        
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.customer-to-products.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-warning">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden input to pass due day value to JavaScript -->
<input type="hidden" id="current-due-day" value="{{ isset($customerProduct) && $customerProduct->due_date ? \Carbon\Carbon::parse($customerProduct->due_date)->format('j') : '15' }}">
<input type="hidden" id="original-monthly-price" value="{{ $product->monthly_price ?? 0 }}">

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
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const billingMonthsInput = document.getElementById('billing_cycle_months');
    const billingMonthsDisplay = document.getElementById('billing-months-display');
    const finalTotalDisplay = document.getElementById('final-total-display');
    const customTotalInput = document.getElementById('custom_total_amount');
    const useSubtotalCheckbox = document.getElementById('use_subtotal');
    const effectiveMonthlyInfo = document.getElementById('effective-monthly-amount');
    const originalMonthlyPrice = parseFloat(document.getElementById('original-monthly-price').value);
    const assignDateInput = document.getElementById('assign_date');
    const dueDaySelect = document.getElementById('due_day');
    const form = document.getElementById('editProductForm');
    const currentDueDayInput = document.getElementById('current-due-day');

    function calculateTotals() {
        const months = parseInt(billingMonthsInput.value) || 1;
        const calculatedTotal = originalMonthlyPrice * months;
        
        // Update billing months display
        if (billingMonthsDisplay) {
            billingMonthsDisplay.textContent = months;
        }
        
        // Determine final total
        let finalTotal;
        
        if (useSubtotalCheckbox && useSubtotalCheckbox.checked) {
            finalTotal = calculatedTotal;
            if (customTotalInput) {
                customTotalInput.disabled = true;
            }
        } else {
            finalTotal = customTotalInput && customTotalInput.value ? parseFloat(customTotalInput.value) : calculatedTotal;
            if (customTotalInput) {
                customTotalInput.disabled = false;
            }
        }
        
        // Update final total display
        if (finalTotalDisplay) {
            finalTotalDisplay.textContent = finalTotal.toFixed(2);
        }
        
        // Calculate and display effective monthly amount
        const effectiveMonthly = finalTotal / months;
        if (effectiveMonthlyInfo) {
            effectiveMonthlyInfo.textContent = effectiveMonthly.toFixed(2);
        }
        
        return {
            finalTotal: finalTotal,
            effectiveMonthly: effectiveMonthly
        };
    }

    // Set the due day select to match the current due date
    if (currentDueDayInput && currentDueDayInput.value) {
        const currentDueDay = parseInt(currentDueDayInput.value);
        if (dueDaySelect && dueDaySelect.querySelector('option[value="' + currentDueDay + '"]')) {
            dueDaySelect.value = currentDueDay;
        }
    }

    // Use subtotal checkbox functionality
    if (useSubtotalCheckbox) {
        useSubtotalCheckbox.addEventListener('change', function() {
            if (this.checked) {
                if (customTotalInput) {
                    customTotalInput.value = '';
                    customTotalInput.disabled = true;
                }
            } else {
                if (customTotalInput) {
                    customTotalInput.disabled = false;
                    // Set custom total to calculated total if empty
                    if (!customTotalInput.value) {
                        const months = parseInt(billingMonthsInput.value) || 1;
                        customTotalInput.value = (originalMonthlyPrice * months).toFixed(2);
                    }
                }
            }
            calculateTotals();
        });

        // Initialize checkbox state
        if (customTotalInput && (!customTotalInput.value || parseFloat(customTotalInput.value) === (originalMonthlyPrice * parseInt(billingMonthsInput.value)))) {
            useSubtotalCheckbox.checked = true;
            customTotalInput.disabled = true;
        } else {
            useSubtotalCheckbox.checked = false;
            if (customTotalInput) {
                customTotalInput.disabled = false;
            }
        }
    }

    // Event listeners for calculations
    if (billingMonthsInput) {
        billingMonthsInput.addEventListener('input', calculateTotals);
    }
    if (customTotalInput) {
        customTotalInput.addEventListener('input', calculateTotals);
    }

    // Add form submission logging for debugging
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form is being submitted');
            console.log('Due day value:', dueDaySelect ? dueDaySelect.value : 'N/A');
            console.log('Custom total amount:', customTotalInput ? customTotalInput.value : 'N/A');
            
            // Ensure the custom total is properly set before submission
            if (useSubtotalCheckbox && useSubtotalCheckbox.checked) {
                if (customTotalInput) {
                    customTotalInput.value = '';
                }
            }
        });
    }

    // Initialize on page load
    calculateTotals();
});
</script>
@endsection