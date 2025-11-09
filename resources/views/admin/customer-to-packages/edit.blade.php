@extends('layouts.admin')

@section('title', 'Edit Customer Package')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-edit me-2"></i>Edit Customer Package</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.customer-to-packages.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Packages
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">Edit Package Assignment</h5>
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

                    <!-- Package Info -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-secondary">
                                <h6><i class="fas fa-cube me-2"></i>Package Information</h6>
                                <strong>Package:</strong> {{ $package->name ?? 'N/A' }}<br>
                                <strong>Type:</strong> {{ ucfirst($package->package_type ?? 'N/A') }}<br>
                                <strong>Original Price:</strong> ৳{{ number_format($package->monthly_price ?? 0, 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- FIXED FORM ACTION - Use cp_id as the route parameter -->
                    <form action="{{ route('admin.customer-to-packages.update', $customerPackage->cp_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_cycle_months" class="form-label">Billing Cycle (Months) *</label>
                                <input type="number" class="form-control @error('billing_cycle_months') is-invalid @enderror" 
                                       id="billing_cycle_months" name="billing_cycle_months" 
                                       value="{{ old('billing_cycle_months', $customerPackage->billing_cycle_months) }}" min="1" max="12" required>
                                @error('billing_cycle_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $customerPackage->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status', $customerPackage->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="expired" {{ old('status', $customerPackage->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign Date</label>
                                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($customerPackage->assign_date)->format('M d, Y') }}" readonly>
                                <small class="text-muted">Original assignment date</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($customerPackage->due_date)->format('M d, Y') }}" readonly>
                                <small class="text-muted">Calculated due date</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-calculator me-2"></i>Total Amount</h6>
                                    <strong>Monthly:</strong> ৳{{ number_format($package->monthly_price ?? 0, 2) }}<br>
                                    <strong>Total for <span id="billing-months-display">{{ $customerPackage->billing_cycle_months }}</span> month(s):</strong> 
                                    ৳<span id="total-display">{{ number_format(($package->monthly_price ?? 0) * $customerPackage->billing_cycle_months, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.customer-to-packages.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-warning">Update Package</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const billingMonthsInput = document.getElementById('billing_cycle_months');
    const billingMonthsDisplay = document.getElementById('billing-months-display');
    const totalDisplay = document.getElementById('total-display');
    const monthlyPrice = {{ $package->monthly_price ?? 0 }};

    function updateTotals() {
        const months = parseInt(billingMonthsInput.value) || 1;
        const total = monthlyPrice * months;
        
        if (billingMonthsDisplay) {
            billingMonthsDisplay.textContent = months;
        }
        if (totalDisplay) {
            totalDisplay.textContent = total.toFixed(2);
        }
    }

    if (billingMonthsInput) {
        billingMonthsInput.addEventListener('input', updateTotals);
        // Initialize on page load
        updateTotals();
    }
});
</script>

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