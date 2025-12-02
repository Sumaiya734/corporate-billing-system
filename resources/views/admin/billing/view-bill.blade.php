@extends('layouts.admin')
@section('title', 'View Invoice')

@section('content')
@php
    $customerId = request()->route('id') ?? 1;
    $invoices = [ 
        // Your invoice data here - make sure you have at least one invoice
        1 => [
            'invoice_id' => 'INV-001',
            'issue_date' => '2024-01-15',
            'due_date' => '2024-01-30',
            'status' => 'pending',
            'amount' => 1850.00,
            'customer' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+880 123456789',
                'address' => '123 Main Street, Dhaka'
            ],
            'services' => [
                ['name' => 'Basic Speed Internet', 'price' => 1500.00]
            ],
            'breakdown' => [
                'service_charge' => 50.00,
                'regular_product' => 1500.00,
                'special_products' => 0.00,
                'vat' => 108.50
            ]
        ],
        2 => [
            'invoice_id' => 'INV-002',
            'issue_date' => '2024-01-16',
            'due_date' => '2024-01-31',
            'status' => 'paid',
            'amount' => 2200.00,
            'customer' => [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '+880 987654321',
                'address' => '456 Oak Avenue, Chittagong'
            ],
            'services' => [
                ['name' => 'Premium Speed Internet', 'price' => 2000.00]
            ],
            'breakdown' => [
                'service_charge' => 50.00,
                'regular_product' => 2000.00,
                'special_products' => 0.00,
                'vat' => 143.50
            ]
        ]
    ];
    
    // Check if the customerId exists in invoices, otherwise use the first invoice
    $invoice = isset($invoices[$customerId]) ? $invoices[$customerId] : (count($invoices) > 0 ? reset($invoices) : null);
    
    // If no invoices exist, handle this case
    if (!$invoice) {
        abort(404, 'Invoice not found');
    }
@endphp

<!-- NON-PRINTABLE HEADER -->
<div class="no-print container-fluid p-3 border-bottom">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Invoice – {{ $invoice['customer']['name'] }}</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="window.print()">Print</button>
            <button class="btn btn-primary btn-sm" id="downloadPdf">Download PDF</button>
            <a href="{{ route('admin.billing.all-invoices') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>
</div>

<!-- PRINTABLE AREA -->
<div id="printableArea">
    <div class="container-fluid p-3">
        <div class="card border-0">
            <div class="card-body">

                <!-- Header -->
                <div class="row mb-3">
                    <div class="col-6">
                        <h3 class="h5 mb-0 fw-bold">INVOICE</h3>
                        <p class="mb-0 small text-muted">#{{ $invoice['invoice_id'] }}</p>
                        
                    </div>
                    <div class="col-6 text-end">
                       
                    </div>
                </div>

                <!-- SIDE-BY-SIDE BOXES -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold text-primary mb-2">Invoice Info</h6>
                            <table class="table table-borderless table-sm small">
                                <tr><td class="text-muted pe-3">ID:</td><td class="fw-bold">{{ $invoice['invoice_id'] }}</td></tr>
                                <tr><td class="text-muted">Issue:</td><td>{{ \Carbon\Carbon::parse($invoice['issue_date'])->format('M d, Y') }}</td></tr>
                                <tr><td class="text-muted">Due:</td><td>{{ \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') }}</td></tr>
                                <tr><td class="text-muted">Status:</td>
                                    <td>
                                        @if($invoice['status']==='paid')   <span class="badge badge-paid">Paid</span>
                                        @elseif($invoice['status']==='pending') <span class="badge badge-pending">Pending</span>
                                        @else                                   <span class="badge badge-overdue">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><td class="text-muted">Amount:</td><td class="fw-bold number_format($invoice['amount'],0) }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold text-primary mb-2">Billing Details</h6>
                            <table class="table table-borderless table-sm small">
                                <tr><td class="text-muted pe-3">Customer:</td><td class="fw-bold">{{ $invoice['customer']['name'] }}</td></tr>
                                <tr><td class="text-muted">Email:</td><td>{{ $invoice['customer']['email'] }}</td></tr>
                                <tr><td class="text-muted">Phone:</td><td>{{ $invoice['customer']['phone'] }}</td></tr>
                                <tr><td class="text-muted">Address:</td><td>{{ $invoice['customer']['address'] }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Services Table (compact) -->
                <div class="mb-3">
                    <h6 class="text-uppercase text-muted mb-1 small">Services & Charges</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered small">
                            <thead class="table-light">
                                <tr><th>Description</th><th class="text-end" style="width:90px;">Amount (৳)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Basic Speed Internet</td><td class="text-end">{{ number_format($invoice['services'][0]['price'],0) }}</td></tr>
                                <tr><td>Service Charge</td><td class="text-end">50.00</td></tr>
                                <tr class="table-light"><td><strong>Subtotal</strong></td><td class="text-end"><strong>{{ number_format($invoice['breakdown']['service_charge']+$invoice['breakdown']['regular_product']+$invoice['breakdown']['special_products'],2) }}</strong></td></tr>
                                <tr><td>VAT (7%)</td><td class="text-end">{{ number_format($invoice['breakdown']['vat'],0) }}</td></tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr><td class="fw-bold">Total</td><td class="text-end fw-bold text-primary">৳ {{ number_format($invoice['amount'],2) }}</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="mb-2">
                    <h6 class="text-uppercase text-muted mb-1 small">Payment Information</h6>
                    <div class="bg-light p-2 rounded small">
                        <p class="mb-1"><strong>Accepted:</strong> Cash, bKash, Nagad, Bank Transfer</p>
                        <p class="mb-0"><strong>Account:</strong> NetX Internet Services Ltd. | 1234567890123 | Prime Bank, Gulshan</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center border-top pt-1 small text-muted">
                    <p class="mb-0"><strong>NetX Internet Services Ltd.</strong> | 123 Business Ave, Dhaka 1212</p>
                    <p class="mb-0">+880 2 55667788 | billing@netx.com | www.netx.com</p>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- NON-PRINTABLE ACTIONS -->
<div class="no-print container-fluid p-3">
    <!-- Add any additional non-printable content here -->
</div>
@endsection

@section('styles')
<style>
    .badge-paid   { background:#28a745; color:#fff; padding:2px 6px; border-radius:12px; font-size:0.65rem; }
    .badge-pending{ background:#ffc107; color:#212529; padding:2px 6px; border-radius:12px; font-size:0.65rem; }
    .badge-overdue{ background:#dc3545; color:#fff; padding:2px 6px; border-radius:12px; font-size:0.65rem; }

    @media print {
        /* 1. Hide everything except printable area */
        body > *:not(#printableArea),
        .no-print,
        .no-print * { display:none !important; }

        #printableArea,
        #printableArea * { visibility:visible !important; }

        /* 2. FORCE SIDE-BY-SIDE – TINY BOXES */
        #printableArea .info-row {
            display:flex !important;
            flex-wrap:nowrap !important;
            gap:2px !important;
            margin-bottom:3px !important;
        }
        #printableArea .info-col {
            flex:1 !important;
            min-width:0 !important;
        }

        /* 3. ULTRA-TINY BOXES (Invoice Info + Billing Details) */
        #printableArea .info-col .bg-light {
            padding:2px 4px !important;
            border-radius:2px !important;
            font-size:5.5pt !important;
            line-height:1.1 !important;
        }

        #printableArea .info-col h6 {
            font-size:6pt !important;
            margin:0 0 1px 0 !important;
            font-weight:600 !important;
        }

        #printableArea .info-col table {
            margin:0 !important;
            font-size:4pt !important;
        }

        #printableArea .info-col table td {
            padding:0 2px !important;
            white-space:nowrap !important;
        }

        #printableArea .info-col table td:first-child {
            color:#555 !important;
            min-width:38px !important;
        }

        /* 4. PAGE & GLOBAL TINY SIZES */
        @page { size:A4 portrait; margin:5mm; }

        html,body{
            background:#fff !important;
            margin:0 !important;
            padding:0 !important;
            font-size:4pt !important;
            line-height:0.7 !important;
        }

        .container-fluid{ padding:0 !important; width:100% !important; }
        .card{ border:none !important; box-shadow:none !important; margin:0 !important; }
        .card-body{ padding:3px !important; }

        h1,h2,h3,h4,h5,h6{ font-size:7.5pt !important; margin:1px 0 !important; }
        p,td,th,small,span,li{ font-size:5.8pt !important; margin:0.5px 0 !important; }
        .table{ font-size:5.8pt !important; margin-bottom:0 !important; }
        .table td,.table th{ padding:1px 2px !important; }

        .bg-light{ background:#f8f9fa !important; }

        /* 5. KILL BOOTSTRAP GRID IN PRINT */
        .row, .col-*, .col-md-*, .col-lg-*, .col-sm-* {
            display:block !important;
            flex:none !important;
            width:auto !important;
            padding:0 !important;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.getElementById('downloadPdf').addEventListener('click',()=>window.print());
</script>
@endsection