@extends('layouts.admin')

@section('title', 'Edit Invoice - Admin Dashboard')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-edit me-2 text-primary"></i>Edit Invoice
            </h2>
            <p class="text-muted mb-0">Modify invoice details for #{{ $invoice->invoice_number ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('admin.billing.monthly-bills', ['month' => \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m')]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Monthly Bills
        </a>
    </div>

    <!-- Invoice Edit Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-invoice me-2"></i>Invoice Details
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

            <form action="{{ route('admin.billing.update-invoice', $invoice->invoice_id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Invoice Information -->
                    <div class="col-md-6">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Invoice #:</strong></p>
                                        <p class="text-muted">{{ $invoice->invoice_number ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Issue Date:</strong></p>
                                        <p class="text-muted">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M j, Y') ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Customer:</strong></p>
                                        <p class="text-muted">{{ $invoice->customerProduct->customer->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Product:</strong></p>
                                        <p class="text-muted">{{ $invoice->customerProduct->product->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Amounts -->
                    <div class="col-md-6">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-money-bill-wave me-2"></i>Financial Details
                                </h6>
                                <hr>
                                <div class="mb-3">
                                    <label for="subtotal" class="form-label">Subtotal (৳)</label>
                                    <input type="number" 
                                           step="1" 
                                           min="0" 
                                           class="form-control" 
                                           id="subtotal" 
                                           name="subtotal" 
                                           value="{{ round(old('subtotal', $invoice->subtotal)) }}" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="previous_due" class="form-label">Previous Due (৳)</label>
                                    <input type="number" 
                                           step="1" 
                                           min="0" 
                                           class="form-control" 
                                           id="previous_due" 
                                           name="previous_due" 
                                           value="{{ round(old('previous_due', $invoice->previous_due)) }}" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="total_amount" class="form-label">Total Amount (৳)</label>
                                    <input type="number" 
                                           step="1" 
                                           min="0" 
                                           class="form-control" 
                                           id="total_amount" 
                                           name="total_amount" 
                                           value="{{ round(old('total_amount', $invoice->total_amount)) }}" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="received_amount" class="form-label">Received Amount (৳)</label>
                                    <input type="number" 
                                           step="1" 
                                           min="0" 
                                           class="form-control" 
                                           id="received_amount" 
                                           name="received_amount" 
                                           value="{{ round(old('received_amount', $invoice->received_amount)) }}" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="next_due" class="form-label">Next Due (৳)</label>
                                    <input type="number" 
                                           step="1" 
                                           min="0" 
                                           class="form-control" 
                                           id="next_due" 
                                           name="next_due" 
                                           value="{{ round(old('next_due', $invoice->next_due)) }}" 
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-sticky-note me-2"></i>Notes
                                </h6>
                                <hr>
                                <div class="mb-3">
                                    <textarea class="form-control" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              placeholder="Invoice notes...">{{ old('notes', $invoice->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.billing.monthly-bills', ['month' => \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m')]) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Invoice
                        </button>
                    </div>
                </div>
            </form>
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