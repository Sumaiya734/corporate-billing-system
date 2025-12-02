@extends('layouts.admin')

@section('title', 'Customer Billing History - ' . ($customer->user->name ?? 'Customer'))

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-history me-2 text-primary"></i>Customer Billing History
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Billing</a></li>
                    <li class="breadcrumb-item active">Customer History</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <!-- Back Button -->
            <a href="{{ route('admin.billing.monthly-bills') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Monthly Bills
            </a>
            <!-- Print Button -->
            <button class="btn btn-outline-primary" id="printBtn">
                <i class="fas fa-print me-1"></i>Print History
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
                    <h6 class="mb-3">Current products</h6>
                    <span class="badge bg-primary me-2 mb-2">Fast Speed - ৳800/month</span>
                    <span class="badge bg-success me-2 mb-2">Gaming Boost - ৳200/month</span>
                    
                    <div class="mt-3">
                        <h6 class="mb-2">Year Summary (2023)</h6>
                        <div class="d-flex justify-content-between">
                            <span>Total Billed:</span>
                            <strong>৳15,600.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Paid:</span>
                            <strong>৳14,200.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Outstanding:</span>
                            <strong class="text-danger">৳1,400.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title mb-0">Billing History for: <span class="text-primary">2023</span></h5>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary">2021</button>
                        <button type="button" class="btn btn-outline-primary">2022</button>
                        <button type="button" class="btn btn-primary">2023</button>
                        <button type="button" class="btn btn-outline-primary">2024</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing History Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-invoice me-2"></i>Monthly Billing Details
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="billingHistoryTable">
                    <thead class="table-light">
                        <tr>
                            <th>Billing Month</th>
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
                        <!-- January -->
                        <tr>
                            <td>
                                <strong>January 2023</strong><br>
                                <small class="text-muted">Due: 10 Feb 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,230.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,230.50</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due">৳0.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-paid">Paid</span>
                            </td>
                        </tr>
                        
                        <!-- February -->
                        <tr>
                            <td>
                                <strong>February 2023</strong><br>
                                <small class="text-muted">Due: 10 Mar 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,230.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,230.50</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due">৳0.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-paid">Paid</span>
                            </td>
                        </tr>
                        
                        <!-- March -->
                        <tr>
                            <td>
                                <strong>March 2023</strong><br>
                                <small class="text-muted">Due: 10 Apr 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,230.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,230.50</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due">৳0.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-paid">Paid</span>
                            </td>
                        </tr>
                        
                        <!-- April -->
                        <tr>
                            <td>
                                <strong>April 2023</strong><br>
                                <small class="text-muted">Due: 10 May 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,230.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,230.50</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due">৳0.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-paid">Paid</span>
                            </td>
                        </tr>
                        
                        <!-- May -->
                        <tr>
                            <td>
                                <strong>May 2023</strong><br>
                                <small class="text-muted">Due: 10 Jun 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,230.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,230.50</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due">৳0.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-paid">Paid</span>
                            </td>
                        </tr>
                        
                        <!-- June -->
                        <tr>
                            <td>
                                <strong>June 2023</strong><br>
                                <small class="text-muted">Due: 10 Jul 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,230.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,230.50</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due">৳0.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-paid">Paid</span>
                            </td>
                        </tr>
                        
                        <!-- July -->
                        <tr>
                            <td>
                                <strong>July 2023</strong><br>
                                <small class="text-muted">Due: 10 Aug 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,230.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,230.50</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due">৳0.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-paid">Paid</span>
                            </td>
                        </tr>
                        
                        <!-- August -->
                        <tr>
                            <td>
                                <strong>August 2023</strong><br>
                                <small class="text-muted">Due: 10 Sep 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,230.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,000.00</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳230.50</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-pending">Partial</span>
                            </td>
                        </tr>
                        
                        <!-- September -->
                        <tr>
                            <td>
                                <strong>September 2023</strong><br>
                                <small class="text-muted">Due: 10 Oct 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳230.50</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,461.00</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,000.00</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳461.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-pending">Partial</span>
                            </td>
                        </tr>
                        
                        <!-- October -->
                        <tr>
                            <td>
                                <strong>October 2023</strong><br>
                                <small class="text-muted">Due: 10 Nov 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳461.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,691.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,000.00</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳691.50</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-pending">Partial</span>
                            </td>
                        </tr>
                        
                        <!-- November -->
                        <tr>
                            <td>
                                <strong>November 2023</strong><br>
                                <small class="text-muted">Due: 10 Dec 2023</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳691.50</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,922.00</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳1,000.00</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳922.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-pending">Partial</span>
                            </td>
                        </tr>
                        
                        <!-- December -->
                        <tr>
                            <td>
                                <strong>December 2023</strong><br>
                                <small class="text-muted">Due: 10 Jan 2024</small>
                            </td>
                            <td>
                                <div class="services-tags">
                                    <div class="product-line">
                                        <span class="badge bg-primary">Fast Speed</span>
                                        <span class="badge bg-success">Gaming Boost</span>
                                    </div>
                                    <div class="product-line">
                                        <small class="text-muted">৳800 + ৳200 </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,230.50</span>
                                <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳922.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳2,152.50</strong>
                            </td>
                            <td class="text-end">
                                <span class="received-amount-display">৳752.50</span>
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳1,400.00</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-overdue">Overdue</span>
                            </td>
                        </tr>
                        
                        <!-- Year Total Row -->
                        <tr class="year-total">
                            <td class="text-end">
                                <strong>2023 Year Total:</strong>
                            </td>
                            <td>
                                <!-- Empty for services column -->
                            </td>
                            <td class="text-end">
                                <strong>৳14,766.00</strong>
                            </td>
                            <td class="text-end">
                                <strong>-</strong>
                            </td>
                            <td class="text-end">
                                <strong>৳15,600.00</strong>
                            </td>
                            <td class="text-end">
                                <strong>৳14,200.00</strong>
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">৳1,400.00</strong>
                            </td>
                            <td class="text-center">
                                <strong>Summary</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
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

    .services-tags .badge {
        margin-right: 4px;
        font-size: 0.75rem;
    }

    .product-line {
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

    .year-total {
        background-color: #e9f7ef;
        border-left: 4px solid #28a745;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
</style>
@endsection

@section('scripts')
<script>
    // Print Button Functionality
    document.getElementById('printBtn').addEventListener('click', function() {
        alert('Printing billing history for {{ $customer->user->name ?? "Customer" }}');
        // Add actual print functionality here
        // window.print();
    });

    // You can add more JavaScript functionality if needed
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Customer Billing History page loaded for customer: {{ $customer->user->name ?? "Unknown" }}');
    });
</script>
@endsection