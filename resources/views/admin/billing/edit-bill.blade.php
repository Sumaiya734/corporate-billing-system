@extends('layouts.admin')

@section('title', 'Edit Payment - Admin Dashboard')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-edit me-2 text-primary"></i>Edit Payment
            </h2>
            <p class="text-muted mb-0">Modify payment details for invoice #{{ $payment->invoice->invoice_number ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('admin.billing.monthly-bills', ['month' => \Carbon\Carbon::parse($payment->invoice->issue_date)->format('Y-m')]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Monthly Bills
        </a>
    </div>

    <!-- Payment Edit Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-money-bill-wave me-2"></i>Payment Details
            </h5>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.billing.payment.update', $payment->payment_id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Invoice Information -->
                    <div class="col-md-6">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-file-invoice me-2"></i>Invoice Information
                                </h6>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Invoice #:</strong></p>
                                        <p class="text-muted">{{ $payment->invoice->invoice_number ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Issue Date:</strong></p>
                                        <p class="text-muted">{{ \Carbon\Carbon::parse($payment->invoice->issue_date)->format('M j, Y') ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Customer:</strong></p>
                                        <p class="text-muted">{{ $payment->invoice->customerProduct->customer->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Product:</strong></p>
                                        <p class="text-muted">{{ $payment->invoice->customerProduct->product->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="col-md-6">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-receipt me-2"></i>Payment Information
                                </h6>
                                <hr>
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (৳)</label>
                                    <input type="number" 
                                           step="0" 
                                           min="0" 
                                           max="{{ ($payment->invoice->total_amount ?? 0) + ($payment->amount ?? 0) }}" 
                                           class="form-control" 
                                           id="amount" 
                                           name="amount" 
                                           value="{{ old('amount', $payment->amount) }}" 
                                           required>
                                    <div class="form-text">
                                        Original amount: ৳{{ number_format($payment->amount, 0) }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="cash" {{ old('payment_method', $payment->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="bank_transfer" {{ old('payment_method', $payment->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="mobile_banking" {{ old('payment_method', $payment->payment_method) == 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                                        <option value="card" {{ old('payment_method', $payment->payment_method) == 'card' ? 'selected' : '' }}>Card</option>
                                        <option value="online" {{ old('payment_method', $payment->payment_method) == 'online' ? 'selected' : '' }}>Online</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="payment_date" 
                                           name="payment_date" 
                                           value="{{ old('payment_date', \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d')) }}" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              placeholder="Optional payment notes...">{{ old('notes', $payment->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.billing.monthly-bills', ['month' => \Carbon\Carbon::parse($payment->invoice->issue_date)->format('Y-m')]) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Payment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Payment Section -->
    <div class="card border-danger mt-4">
        <div class="card-header bg-danger text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
            </h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                <strong>Delete this payment:</strong> This action will permanently remove the payment and recalculate the invoice amounts.
            </p>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletePaymentModal">
                <i class="fas fa-trash me-1"></i>Delete Payment
            </button>
        </div>
    </div>
</div>

<!-- Delete Payment Confirmation Modal -->
<div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePaymentModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Payment Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this payment of <strong>৳{{ number_format($payment->amount, 0) }}</strong>?</p>
                <p class="text-danger">
                    <i class="fas fa-info-circle me-1"></i>This action cannot be undone. The invoice amounts will be recalculated.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <form action="{{ route('admin.billing.payment.delete', $payment->payment_id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Yes, Delete Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .page-title {
        color: #2c3e50;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }
    
    .card-header {
        background: white;
        border-bottom: 1px solid #eaeaea;
        border-radius: 12px 12px 0 0 !important;
        padding: 20px 25px;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
    }
    
    .btn {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .alert {
        border-radius: 8px;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.querySelector('form');
        const amountInput = document.getElementById('amount');
        
        form.addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            const maxAmount = parseFloat(amountInput.max);
            
            if (amount < 0) {
                e.preventDefault();
                alert('Payment amount cannot be negative');
                return;
            }
            
            if (amount > maxAmount) {
                e.preventDefault();
                alert(`Payment amount cannot exceed ৳${maxAmount.toFixed(0)}`);
                return;
            }
        });
    });
</script>
@endsection