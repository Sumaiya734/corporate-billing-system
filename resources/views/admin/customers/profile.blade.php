@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
@php 
 $customerId = request()->route('id') ?? 1; 
  $invoices = [ 1 => [ 'id' => 1, 'invoice_id' => 'INV-2024-001', 'customer' => [ 'name' => 'John Doe', 'email' => 'john.doe@example.com', 'phone' => '+8801712345678', 'address' => 'Gulshan, Dhaka' ], 'services' => [ ['name' => 'Basic Speed Internet', 'price' => 500], ], 'issue_date' => '2024-01-01', 'due_date' => '2024-01-05', 'status' => 'paid', 'amount' => 588.50, 'breakdown' => [ 'service_charge' => 50, 'regular_package' => 500, 'special_packages' => 0, 'vat' => 38.50, 'discount' => 0 ] ], 2 => [ 'id' => 2, 'invoice_id' => 'INV-2024-002', 'customer' => [ 'name' => 'Alice Smith', 'email' => 'alice.smith@example.com', 'phone' => '+8801812345679', 'address' => 'Uttara, Dhaka' ], 'services' => [ ['name' => 'Fast Speed Internet', 'price' => 800], ['name' => 'Gaming Boost', 'price' => 200], ], 'issue_date' => '2024-01-01', 'due_date' => '2024-01-05', 'status' => 'pending', 'amount' => 1151.50, 'breakdown' => [ 'service_charge' => 50, 'regular_package' => 800, 'special_packages' => 200, 'vat' => 73.50, 'discount' => 0 ] ], 3 => [ 'id' => 3, 'invoice_id' => 'INV-2023-125', 'customer' => [ 'name' => 'Bob Johnson', 'email' => 'bob.johnson@example.com', 'phone' => '+8801912345680', 'address' => 'Banani, Dhaka' ], 'services' => [ ['name' => 'Super Speed Internet', 'price' => 1200], ['name' => 'Streaming Plus', 'price' => 150], ], 'issue_date' => '2023-12-01', 'due_date' => '2023-12-25', 'status' => 'overdue', 'amount' => 1500.50, 'breakdown' => [ 'service_charge' => 50, 'regular_package' => 1200, 'special_packages' => 150, 'vat' => 98.00, 'discount' => 0 ] ], 4 => [ 'id' => 4, 'invoice_id' => 'INV-2023-098', 'customer' => [ 'name' => 'Carol White', 'email' => 'carol.white@example.com', 'phone' => '+8801612345681', 'address' => 'Dhanmondi, Dhaka' ], 'services' => [ ['name' => 'Fast Speed Internet', 'price' => 800], ], 'issue_date' => '2023-11-01', 'due_date' => '2023-11-05', 'status' => 'paid', 'amount' => 909.50, 'breakdown' => [ 'service_charge' => 50, 'regular_package' => 800, 'special_packages' => 0, 'vat' => 59.50, 'discount' => 0 ] ], 5 => [ 'id' => 5, 'invoice_id' => 'INV-2023-076', 'customer' => [ 'name' => 'David Green', 'email' => 'david.green@example.com', 'phone' => '+8801512345682', 'address' => 'Mirpur, Dhaka' ], 'services' => [ ['name' => 'Super Speed Internet', 'price' => 1200], ['name' => 'Family Pack', 'price' => 300], ], 'issue_date' => '2023-10-01', 'due_date' => '2023-10-05', 'status' => 'paid', 'amount' => 1657.50, 'breakdown' => [ 'service_charge' => 50, 'regular_package' => 1200, 'special_packages' => 300, 'vat' => 108.50, 'discount' => 0 ] ] ]; 
   $invoice = $invoices[$customerId] ?? $invoices[1]; $customerName = $invoice['customer']['name'];
 @endphp
<div class="container-fluid p-4" id="invoicePrintable">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2 class="fw-bold mb-1">Invoice Details</h2>
            <div class="text-muted">Invoice #: {{ $invoice['invoice_id'] }}</div>
        </div>
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
            <button class="btn btn-outline-primary">
                <i class="fas fa-download me-2"></i>Download PDF
            </button>
        </div>
    </div>

    <!-- Invoice Info Row -->
    <div class="row g-4 mb-4">
        <!-- Invoice Details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">Invoice Info</h5>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Invoice ID:</td>
                                <td class="fw-semibold">{{ $invoice['invoice_id'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Issue Date:</td>
                                <td>{{ \Carbon\Carbon::parse($invoice['issue_date'])->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Due Date:</td>
                                <td>{{ \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td>
                                    @if($invoice['status'] === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($invoice['status'] === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Amount Due:</td>
                                <td class="fw-bold text-primary fs-5">৳ {{ number_format($invoice['amount'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Billing Details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">Billing Details</h5>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Customer:</td>
                                <td class="fw-semibold">{{ $invoice['customer']['name'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td>{{ $invoice['customer']['email'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phone:</td>
                                <td>{{ $invoice['customer']['phone'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Address:</td>
                                <td>{{ $invoice['customer']['address'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">Services & Charges</h5>
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Amount (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice['services'] as $service)
                        <tr>
                            <td>{{ $service['name'] }}</td>
                            <td class="text-end">{{ number_format($service['price'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>Service Charge</td>
                        <td class="text-end">{{ number_format($invoice['breakdown']['service_charge'], 2) }}</td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Subtotal</td>
                        <td class="text-end">{{ number_format($invoice['breakdown']['regular_package'] + $invoice['breakdown']['special_packages'] + $invoice['breakdown']['service_charge'], 2) }}</td>
                    </tr>
                    <tr>
                        <td>VAT (7%)</td>
                        <td class="text-end">{{ number_format($invoice['breakdown']['vat'], 2) }}</td>
                    </tr>
                    @if($invoice['breakdown']['discount'] > 0)
                        <tr>
                            <td>Discount</td>
                            <td class="text-end text-success">-{{ number_format($invoice['breakdown']['discount'], 2) }}</td>
                        </tr>
                    @endif
                    <tr class="table-primary fw-bold">
                        <td>Total</td>
                        <td class="text-end">৳ {{ number_format($invoice['amount'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Notes -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-2">Payment Information</h6>
            <p class="mb-1">Accepted Methods: Cash, bKash, Nagad, Bank Transfer</p>
            <p class="mb-1">Account: <strong>NetX Internet Services Ltd.</strong> — 1234567890123 (Prime Bank, Gulshan)</p>
            <p class="text-muted small mb-0">
                Thank you for choosing NetX Internet Services. Please ensure payment is made by the due date to avoid service interruption.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center mt-4 pt-3 border-top small text-muted">
        <p class="mb-1"><strong>NetX Internet Services Ltd.</strong></p>
        <p class="mb-0">123 Business Avenue, Dhaka 1212 | billing@netx.com | +880 2 55667788</p>
    </div>
</div>
@endsection

@section('styles')
<style>
/* --------- Invoice Page Styling --------- */
#invoicePrintable {
    max-width: 950px;
    margin: auto;
    background: #fff;
}
.card {
    border-radius: 10px;
}
.table th, .table td {
    vertical-align: middle;
    padding: 0.65rem 0.75rem;
}
.badge {
    font-size: 0.8rem;
    border-radius: 6px;
}
h5.text-primary {
    letter-spacing: 0.3px;
}

/* --------- Print Formatting --------- */
@media print {
    @page {
        size: A4;
        margin: 1cm;
    }

    body {
        background: white;
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    #invoicePrintable {
        width: 100%;
        margin: 0;
        box-shadow: none;
    }

    .no-print,
    .navbar,
    .sidebar,
    footer {
        display: none !important;
    }

    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }

    table, tr, td, th {
        page-break-inside: avoid !important;
    }

    .row {
        margin-bottom: 0.5rem;
    }

    body {
        font-size: 13px !important;
        line-height: 1.4;
    }

    h2, h5 {
        margin-bottom: 0.3rem !important;
    }

    .text-primary {
        color: #000 !important;
    }

    .table-primary {
        background-color: #e9ecef !important;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endsection
