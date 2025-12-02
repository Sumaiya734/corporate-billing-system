@extends('layouts.admin')

@section('title', 'Customer Billing History - ' . ($customer->name ?? 'Customer'))

@section('content')
<div class="page-body p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-history me-2 text-primary"></i>Customer Billing History
            </h2>
           
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.billing.monthly-bills') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Monthly Bills
            </a>
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
                            {{ strtoupper(substr($customer->name ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $customer->name ?? 'N/A' }}</h4>
                            <p class="text-muted mb-0">Customer ID: {{ $customer->id }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Email</small>
                            <p class="mb-2">{{ $customer->email ?? 'N/A' }}</p>
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
                        @php
                            $yearlyTotal = collect($bills)->sum('total_amount');
                            $yearlyPaid = collect($bills)->sum('paid_amount');
                            $outstanding = $yearlyTotal - $yearlyPaid;
                        @endphp
                        <h6 class="mb-2">Year Summary ({{ date('Y') }})</h6>
                        <div class="d-flex justify-content-between">
                            <span>Total Billed:</span>
                            <strong>৳{{ number_format($yearlyTotal, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Paid:</span>
                            <strong>৳{{ number_format($yearlyPaid, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Outstanding:</span>
                            <strong class="text-danger">৳{{ number_format($outstanding, 2) }}</strong>
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
                    <h5 class="card-title mb-0">Billing History for: <span class="text-primary">{{ date('Y') }}</span></h5>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        @foreach([2021, 2022, 2023, 2024] as $year)
                            <button type="button" class="btn {{ date('Y') == $year ? 'btn-primary' : 'btn-outline-primary' }}">
                                {{ $year }}
                            </button>
                        @endforeach
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
                        @foreach($bills as $bill)
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($bill->billing_month)->format('F Y') }}</strong><br>
                                    <small class="text-muted">Due: {{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <div class="services-tags">
                                        <div class="product-line">
                                            @foreach($bill->products as $product)
                                                <span class="badge bg-primary">{{ $product->name }}</span>
                                            @endforeach
                                        </div>
                                        <div class="product-line">
                                            <small class="text-muted">
                                                ৳{{ number_format($bill->product_amount, 2) }} 
                                                @if($bill->addon_amount > 0)
                                                    + ৳{{ number_format($bill->addon_amount, 2) }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="bill-amount">৳{{ number_format($bill->total_amount, 2) }}</span>
                                    <small class="text-muted d-block">
                                        (Service: ৳{{ number_format($bill->service_charge, 2) }} + 
                                        product: ৳{{ number_format($bill->product_amount, 2) }}
                                        @if($bill->addon_amount > 0)
                                            + Add-ons: ৳{{ number_format($bill->addon_amount, 2) }}
                                        @endif
                                        + VAT: ৳{{ number_format($bill->vat_amount, 2) }})
                                    </small>
                                </td>
                                <td class="text-end">
                                    <span class="previous-due">৳{{ number_format($bill->previous_due, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <strong class="total-amount">৳{{ number_format($bill->grand_total, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    <span class="received-amount-display">৳{{ number_format($bill->paid_amount, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    @if($bill->due_amount > 0)
                                        <span class="next-due text-danger">৳{{ number_format($bill->due_amount, 2) }}</span>
                                    @else
                                        <span class="next-due">৳{{ number_format($bill->due_amount, 2) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($bill->status === 'paid')
                                        <span class="badge" style="background-color: #06d6a0; color: white; padding: 6px 12px; border-radius: 20px;">Paid</span>

                                    @elseif($bill->status === 'partial')
                                        <span class="badge" style="background-color: #ffd166; color: black; padding: 6px 12px; border-radius: 20px;">Pending</span>

                                    @else
                                       <span class="badge" style="background-color: #ef476f; color: white; padding: 6px 12px; border-radius: 20px;">Overdue</span
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        
                        <!-- Year Total Row -->
                        <tr class="year-total">
                            <td class="text-end">
                                <strong>{{ date('Y') }} Year Total:</strong>
                            </td>
                            <td>
                                <!-- Empty for services column -->
                            </td>
                            <td class="text-end">
                                <strong>৳{{ number_format(collect($bills)->sum('total_amount'), 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong>-</strong>
                            </td>
                            <td class="text-end">
                                <strong>৳{{ number_format(collect($bills)->sum('grand_total'), 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong>৳{{ number_format(collect($bills)->sum('paid_amount'), 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">৳{{ number_format(collect($bills)->sum('due_amount'), 2) }}</strong>
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
    document.getElementById('printBtn').addEventListener('click', function() {
        alert('Printing billing history for {{ $customer->name ?? "Customer" }}');
        // window.print();
    });
</script>
@endsection