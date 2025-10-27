@extends('layouts.admin')

@section('title', 'View Bill - ' . ($customer->user->name ?? 'Customer'))

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-eye me-2 text-primary"></i>View Bill
            </h2>
            <nav aria-label="breadcrumb">
                
            </nav>
        </div>
        <div class="d-flex gap-2">
            <!-- Back Button -->
            <a href="{{ route('admin.billing.monthly-bills') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Monthly Bills
            </a>
            <!-- Only Print Button -->
            <button class="btn btn-outline-primary" id="printBtn">
                <i class="fas fa-print me-1"></i>Print Bill
            </button>
        </div>
    </div>

    <!-- Customer Info Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-user me-2"></i>Customer Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <div class="customer-avatar me-3">
                            {{ strtoupper(substr($customer->user->name ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $customer->user->name ?? 'N/A' }}</h4>
                            <p class="text-muted mb-0">Customer ID: {{ $customer->id }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Email</small>
                            <p class="mb-2">{{ $customer->user->email ?? 'N/A' }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Phone</small>
                            <p class="mb-2">{{ $customer->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Address</small>
                            <p class="mb-0">{{ $customer->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Current Packages</h6>
                    <span class="badge bg-primary me-2 mb-2">Fast Speed - ৳800/month</span>
                    <span class="badge bg-success me-2 mb-2">Gaming Boost - ৳200/month</span>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Details Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-invoice me-2"></i>Bill Details
            </h5>
        </div>
        <div class="card-body">
            <!-- Date Information -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="d-flex gap-4">
                        <div>
                            <small class="text-muted d-block">Invoice Date</small>
                            <strong>{{ date('d M, Y') }}</strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Billing Month</small>
                            <strong>{{ date('F Y') }}</strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Due Date</small>
                            <strong>{{ date('d M, Y', strtotime('+7 days')) }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="total-summary">
                        <small class="text-muted d-block">Total Amount Due</small>
                        <h4 class="text-success mb-0">৳1,300.00</h4>
                    </div>
                </div>
            </div>

            <!-- Bill Details Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="billTable">
                    <thead class="table-light">
                        <tr>
                            <th>Customer Info</th>
                            <th>Services</th>
                            <th width="120" class="text-end">Bill Amount</th>
                            <th width="120" class="text-end">Previous Due</th>
                            <th width="120" class="text-end">Total</th>
                            <th width="140" class="text-end">Received Amount</th>
                            <th width="120" class="text-end">Next Due</th>
                            <th width="100" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Single Row for the Customer -->
                        <tr>
                            <td>
                                <div class="customer-info-compact">
                                    <strong class="d-block">{{ $customer->user->name ?? 'N/A' }}</strong>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-envelope me-1"></i>{{ $customer->user->email ?? 'N/A' }}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-phone me-1"></i>{{ $customer->phone ?? 'N/A' }}
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $customer->address ?? 'N/A' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="package-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                        
                                    </div>
                                    <div class="package-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳150.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,380.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳950.00</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳430.50</span>
                            </td>
                            <td>
    <span class="badge" style="background-color: #ffd166; color: black; padding: 6px 12px; border-radius: 20px;">Pending</span>
</td>
                                
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Additional Bill Information -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Payment Information</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted d-block">Invoice Number</small>
                                    <strong>INV-{{ date('Ymd') }}-{{ $customer->id }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Issue Date</small>
                                    <strong>{{ date('d M, Y') }}</strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <small class="text-muted d-block">Due Date</small>
                                    <strong>{{ date('d M, Y', strtotime('+7 days')) }}</strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <small class="text-muted d-block">Payment Method</small>
                                    <strong>Bank Transfer</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Bill Calculation</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Service Charge:</span>
                                <strong>৳50.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Regular Package (Fast Speed):</span>
                                <strong>৳800.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Special Packages (Gaming Boost):</span>
                                <strong>৳200.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>৳1,150.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>VAT (7%):</span>
                                <strong>৳80.50</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Previous Due:</span>
                                <strong>৳150.00</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><strong>Total Due:</strong></span>
                                <strong class="text-danger">৳1,380.50</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .badge-paid {
        background-color: #06d6a0 !important;
        color: white !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .badge-pending {
        background-color: #ffd166 !important;
        color: #000 !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .badge-overdue {
        background-color: #ef476f !important;
        color: white !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #4361ee;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .customer-info-compact {
        line-height: 1.3;
        font-size: 0.875rem;
    }

    .customer-info-compact strong {
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .customer-info-compact small {
        font-size: 0.75rem;
    }

    .service-info {
        line-height: 1.2;
    }

    .services-tags .badge {
        margin-right: 4px;
        font-size: 0.75rem;
    }

    .package-line {
        margin-bottom: 4px;
    }

    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
    }

    .total-summary {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    /* Compact customer info styling */
    .customer-info-compact i {
        width: 12px;
        text-align: center;
    }

    .received-amount-display {
        font-weight: 600;
    }

    .bill-amount {
        font-weight: 600;
    }
</style>
@endsection

@section('scripts')
<script>
    // Print Button Functionality
    document.getElementById('printBtn').addEventListener('click', function() {
        alert('Printing bill for {{ $customer->user->name ?? "Customer" }}');
        // Add actual print functionality here
        // window.print();
    });

    // You can add more JavaScript functionality if needed
    document.addEventListener('DOMContentLoaded', function() {
        console.log('View Bill page loaded for customer: {{ $customer->user->name ?? "Unknown" }}');
    });
</script>
@endsection