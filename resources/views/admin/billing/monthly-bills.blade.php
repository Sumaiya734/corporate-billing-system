@extends('layouts.admin')

@section('title', 'Monthly Bills - Admin Dashboard')

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Monthly Bills - {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
            </h2>
            <p class="text-muted mb-0">Manage and view all customer bills for the selected month</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportMonthlyBills()">
                <i class="fas fa-download me-1"></i>Export Report
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateBillsModal">
                <i class="fas fa-plus me-1"></i>Generate Bills
            </button>
            <a href="{{ route('admin.billing.billing-invoices') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Billing
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Customers</div>
                            <div class="h5 mb-0">{{ $totalCustomers ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-white-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Billing Amount</div>
                            <div class="h5 mb-0">৳ {{ number_format($totalBillingAmount ?? 0, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-white-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Payments</div>
                            <div class="h5 mb-0">৳ {{ number_format($pendingAmount ?? 0, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-white-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Paid Amount</div>
                            <div class="h5 mb-0">৳ {{ number_format($paidAmount ?? 0, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-white-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Bills Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Monthly Bills for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                </h5>
                <div class="d-flex gap-2">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" class="form-control" placeholder="Search..." id="searchInput">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="form-select form-select-sm" style="width: 150px;" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" id="monthlyBillsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Customer Info</th>
                            <th>Packages</th>
                            <th>Bill Amount</th>
                            <th>Previous Due</th>
                            <th>Total Amount</th>
                            <th>Received Amount</th>
                            <th>Next Due</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices ?? [] as $invoice)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $invoice->invoice_number }}</strong>
                                <br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M j, Y') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $invoice->customer->name ?? 'N/A' }}</h6>
                                        <div class="text-muted small">
                                            <div>{{ $invoice->customer->email ?? 'N/A' }}</div>
                                            <div>{{ $invoice->customer->phone ?? 'N/A' }}</div>
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark">{{ $invoice->customer->customer_id ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="packages-list">
                                    @php
                                        $customerPackages = $invoice->customer->customerPackages ?? collect();
                                    @endphp
                                    @if($customerPackages->count() > 0)
                                        @foreach($customerPackages as $customerPackage)
                                            @if($customerPackage->package)
                                            <div class="package-item mb-2">
                                                <div class="fw-medium text-dark">{{ $customerPackage->package->name ?? 'Unknown Package' }}</div>
                                                <div class="text-muted small">
                                                    ৳ {{ number_format($customerPackage->package->monthly_price ?? 0, 2) }}/month
                                                    @if($customerPackage->billing_cycle_months > 1)
                                                    <span class="badge bg-info">({{ $customerPackage->billing_cycle_months }} months)</span>
                                                    @endif
                                                    <div class="mt-1">
                                                        <small class="text-muted">
                                                            Status: 
                                                            <span class="badge bg-{{ $customerPackage->status == 'active' ? 'success' : 'warning' }}">
                                                                {{ $customerPackage->status }}
                                                            </span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-muted">No packages assigned</span>
                                    @endif
                                </div>
                            </td>
                            
                            <td>
                                <div class="bill-amount">
                                    <strong class="text-dark">৳ {{ number_format(($invoice->total_amount ?? 0) - ($invoice->previous_due ?? 0), 2) }}</strong>
                                    <div class="text-muted small">
                                        <div>Service: ৳ {{ number_format($invoice->service_charge ?? 0, 2) }}</div>
                                        @if(($invoice->vat_amount ?? 0) > 0)
                                        <div>VAT: ৳ {{ number_format($invoice->vat_amount ?? 0, 2) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="previous-due">
                                    @if(($invoice->previous_due ?? 0) > 0)
                                        <strong class="text-warning">৳ {{ number_format($invoice->previous_due ?? 0, 2) }}</strong>
                                        <div class="text-muted small">From previous bills</div>
                                    @else
                                        <span class="text-success">-</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="total-amount">
                                    <strong class="text-success">৳ {{ number_format($invoice->total_amount ?? 0, 2) }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="received-amount">
                                    @if(($invoice->received_amount ?? 0) > 0)
                                        <strong class="text-info">৳ {{ number_format($invoice->received_amount ?? 0, 2) }}</strong>
                                        @if(($invoice->total_amount ?? 0) > 0)
                                        <div class="text-muted small">
                                            {{ number_format((($invoice->received_amount ?? 0) / ($invoice->total_amount ?? 1)) * 100, 1) }}% paid
                                        </div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="next-due">
                                    @if(($invoice->next_due ?? 0) > 0)
                                        <strong class="text-danger">৳ {{ number_format($invoice->next_due ?? 0, 2) }}</strong>
                                    @else
                                        <span class="text-success">Paid</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($invoice->status == 'paid')
                                    <span class="badge badge-paid">
                                        <i class="fas fa-check-circle me-1"></i>Paid
                                    </span>
                                @elseif($invoice->status == 'unpaid')
                                    <span class="badge badge-pending">
                                        <i class="fas fa-clock me-1"></i>Unpaid
                                    </span>
                                @elseif($invoice->status == 'partial')
                                    <span class="badge badge-partial">
                                        <i class="fas fa-hourglass-half me-1"></i>Partial
                                    </span>
                                    <div class="text-muted small mt-1">
                                        ৳ {{ number_format($invoice->next_due ?? 0, 2) }} remaining
                                    </div>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-times me-1"></i>Cancelled
                                    </span>
                                @endif
                            </td>
                            <!--  DYNAMIC ACTION BUTTONS -->
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    {{-- View Button: Always available --}}
                                    <button class="btn btn-outline-primary btn-sm"
                                         data-bs-toggle="modal" 
                                        data-bs-target="#viewInvoiceModal"
                                        onclick="viewInvoice({{ $invoice->invoice_id }})"
                                        title="View Invoice">
                                        <i class="fas fa-eye"></i> View
                                    </button>

                                    {{-- Payment Button: Only for unpaid or partial invoices --}}
                                    @if(in_array($invoice->status, ['unpaid', 'partial']))
                                        <button class="btn btn-outline-success btn-sm payment-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addPaymentModal"
                                                data-invoice-id="{{ $invoice->invoice_id }}"
                                                data-invoice-number="{{ $invoice->invoice_number }}"
                                                data-customer-name="{{ e($invoice->customer->name ?? 'Customer') }}"
                                                data-customer-email="{{ e($invoice->customer->email ?? '') }}"
                                                data-customer-phone="{{ e($invoice->customer->phone ?? '') }}"
                                                data-total-amount="{{ $invoice->total_amount ?? 0 }}"
                                                data-due-amount="{{ $invoice->next_due ?? 0 }}"
                                                data-received-amount="{{ $invoice->received_amount ?? 0 }}"
                                                data-status="{{ $invoice->status }}"
                                                title="Add Payment">
                                            <i class="fas fa-money-bill-wave"></i> Payment
                                        </button>
                                    @else
                                        <button class="btn btn-outline-success btn-sm" disabled title="Invoice already paid">
                                            <i class="fas fa-check me-1"></i> Paid
                                        </button>
                                    @endif

                                    {{-- Reminder Button: Only for unpaid or partial invoices --}}
                                    @if(in_array($invoice->status, ['unpaid', 'partial']))
                                        <button class="btn btn-outline-info btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#sendReminderModal"
                                                data-invoice-id="{{ $invoice->invoice_id }}"
                                                data-customer-name="{{ e($invoice->customer->name ?? 'Customer') }}"
                                                data-customer-email="{{ e($invoice->customer->email ?? '') }}"
                                                title="Send Payment Reminder">
                                            <i class="fas fa-bell"></i> Reminder
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm" disabled title="No reminder needed">
                                            <i class="fas fa-bell-slash me-1"></i> No Reminder
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                                    <h5>No bills found for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</h5>
                                    <p>Generate bills for this month to get started.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateBillsModal">
                                        <i class="fas fa-plus me-1"></i>Generate Bills
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        Showing {{ ($invoices ?? collect())->count() }} bills for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Last updated: {{ now()->format('M j, Y g:i A') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Bills Modal -->
<div class="modal fade" id="generateBillsModal" tabindex="-1" aria-labelledby="generateBillsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Monthly Bills</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.billing.generate-monthly-bills') }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will generate bills for all active customers with packages in {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Billing Month</label>
                        <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Active Customers with Packages</label>
                        <input type="text" class="form-control" value="{{ $totalCustomers ?? 0 }} customers" readonly>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_service_charge" id="include_service_charge" checked>
                            <label class="form-check-label" for="include_service_charge">
                                Include service charge (৳ {{ $systemSettings['fixed_monthly_charge'] ?? 50 }}) and VAT ({{ $systemSettings['vat_percentage'] ?? 5 }}%)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sync me-1"></i>Generate Bills
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Invoice Modal -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewInvoiceContent">
                <!-- Content will be loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="fas fa-print me-1"></i>Print Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Send Reminder Modal -->
<div class="modal fade" id="sendReminderModal" tabindex="-1" aria-labelledby="sendReminderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Payment Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendReminderForm" action="{{ route('admin.billing.send-reminder') }}" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" id="reminder_invoice_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" id="reminder_customer_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="reminder_customer_email" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reminder Type</label>
                        <select name="reminder_type" class="form-select" required>
                            <option value="payment_due">Payment Due</option>
                            <option value="overdue">Overdue Payment</option>
                            <option value="friendly">Friendly Reminder</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required placeholder="Custom message for the customer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-paper-plane me-1"></i>Send Reminder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Separate Payment Modal -->
@include('admin.billing.payment-modal')
@endsection

@section('styles')
<style>
    :root {
        --primary: #4361ee;
        --success: #06d6a0;
        --warning: #ffd166;
        --danger: #ef476f;
        --info: #118ab2;
        --dark: #2b2d42;
        --light: #f8f9fa;
    }

    body {
        background-color: #f5f7fb;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #eaeaea;
        padding: 12px 8px;
        background-color: #f8f9fa;
    }

    .table td {
        padding: 16px 8px;
        font-size: 0.9rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .badge-paid {
        background-color: var(--success);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-pending {
        background-color: var(--warning);
        color: black;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-partial {
        background-color: var(--info);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .packages-list .package-item {
        padding: 8px;
        border-left: 3px solid var(--primary);
        background-color: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 8px;
    }

    .packages-list .package-item:last-child {
        margin-bottom: 0;
    }

    .btn-sm {
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.8rem;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.05);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    }

    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
</style>
@endsection

@section('scripts')
@vite(['resources/js/app.js'])

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $modal = $('#addPaymentModal');

    // --------------------------------------------------------------
    // 1. MODAL OPEN → Fill data from button (even if click on <i>)
    // --------------------------------------------------------------
  $modal.on('show.bs.modal', function (e) {
    let $btn = $(e.relatedTarget);
    if (!$btn.hasClass('btn')) {
        $btn = $btn.closest('[data-invoice-id]');
    }

    if (!$btn.length) {
        showToast('Could not identify invoice. Please try again.', 'danger');
        return $modal.modal('hide');
    }

    const invoiceId = $btn.data('invoice-id');
    if (!invoiceId || isNaN(invoiceId)) {
        showToast('Invalid invoice ID.', 'danger');
        return $modal.modal('hide');
    }

    // Reset UI and show loading state
    $('#payment_invoice_id').val(invoiceId);
    // More robust base URL detection
    let basePath = window.location.origin;
    // Check if we're in a subdirectory setup
    if (window.location.pathname.includes('/ik/netbill-bd/')) {
        if (window.location.pathname.includes('/public/')) {
            basePath += '/ik/netbill-bd/public';
        } else {
            basePath += '/ik/netbill-bd';
        }
    }
    $('#addPaymentForm').attr('action', basePath + '/admin/billing/record-payment/' + invoiceId);

    const placeholders = {
        number: 'Loading…',
        customer: 'Loading…',
        total: '৳ 0.00',
        due: '৳ 0.00'
    };

    $('#payment_invoice_number_display').text(placeholders.number);
    $('#payment_customer_name_display').text(placeholders.customer);
    $('#payment_total_amount_display').text(placeholders.total);
    $('#payment_due_amount_display').text(placeholders.due);
    $('#payment_amount').val('').prop('disabled', true); // disable until data loads

    // Fetch fresh invoice data
    fetch(basePath + '/admin/billing/invoice/' + invoiceId + '/data', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(json => {
        if (!json.success || !json.invoice) {
            throw new Error(json.message || 'Invalid invoice data received.');
        }

        const inv = json.invoice;
        const invoiceNumber = inv.invoice_number || '–';
        const customerName = inv.customer?.name?.trim() || 'Unknown Customer';
        const totalAmount = parseFloat(inv.total_amount) || 0;
        const dueAmount = parseFloat(inv.next_due) || (totalAmount - (parseFloat(inv.received_amount) || 0));

        // Update UI with real data
        $('#payment_invoice_number_display').text(invoiceNumber);
        $('#payment_customer_name_display').text(customerName);
        $('#payment_total_amount_display').text(`৳ ${totalAmount.toLocaleString('en-BD', { minimumFractionDigits: 2 })}`);
        $('#payment_due_amount_display').text(`৳ ${dueAmount.toLocaleString('en-BD', { minimumFractionDigits: 2 })}`);

        // Configure amount input
        const $amountInput = $('#payment_amount');
        $amountInput
            .val(dueAmount > 0 ? dueAmount.toFixed(2) : '')
            .attr({
                'min': '0.01',
                'max': dueAmount,
                'step': '0.01'
            })
            .prop('disabled', dueAmount <= 0)
            .removeClass('is-invalid');

        if (dueAmount <= 0) {
            $amountInput.attr('placeholder', 'Invoice already paid');
        } else {
            $amountInput.attr('placeholder', 'Enter amount to pay');
        }

        $('#payment_amount_error').hide();
    })
    .catch(err => {
        console.error('Failed to load invoice data:', err);
        const msg = err.message.includes('404') 
            ? 'Invoice not found.' 
            : 'Failed to load invoice. Try again.';
        
        showToast(msg, 'danger');

        // Fallback UI (safe defaults)
        $('#payment_invoice_number_display').text('Error');
        $('#payment_customer_name_display').text('—');
        $('#payment_total_amount_display').text('৳ 0.00');
        $('#payment_due_amount_display').text('৳ 0.00');
        $('#payment_amount').prop('disabled', true).val('');
    });
});

    // --------------------------------------------------------------
    // 2. VALIDATE PAYMENT AMOUNT
    // --------------------------------------------------------------
    $('#payment_amount').on('input', function () {
        const paid = parseFloat(this.value) || 0;
        const dueText = $('#payment_due_amount_display').text();
        const due = parseFloat(dueText.replace(/[^\d.]/g, '')) || 0;

        if (paid > due) {
            $(this).addClass('is-invalid');
            $('#payment_amount_error').show();
        } else {
            $(this).removeClass('is-invalid');
            $('#payment_amount_error').hide();
        }
    });

    // --------------------------------------------------------------
    // 3. SUBMIT PAYMENT VIA AJAX
    // --------------------------------------------------------------
    $('#addPaymentForm').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const oldHtml = $btn.html();

        const paid = parseFloat($('#payment_amount').val()) || 0;
        const dueText = $('#payment_due_amount_display').text();
        const due = parseFloat(dueText.replace(/[^\d.]/g, '')) || 0;

        if (paid <= 0) {
            showToast('Amount must be greater than 0!', 'danger');
            return;
        }
        if (paid > due) {
            showToast('Cannot pay more than due amount!', 'danger');
            return;
        }

        $btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...').prop('disabled', true);

        fetch($form.attr('action'), {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.json())
        .then(json => {
            if (json.success) {
                showToast(json.message || 'Payment recorded!', 'success');
                $modal.modal('hide');
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(json.message || 'Error saving payment.', 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Network error. Try again.', 'danger');
        })
        .finally(() => {
            $btn.html(oldHtml).prop('disabled', false);
        });
    });

    // --------------------------------------------------------------
    // 4. RESET MODAL ON CLOSE
    // --------------------------------------------------------------
    $modal.on('hidden.bs.modal', function () {
        $('#addPaymentForm')[0].reset();
        $('#addPaymentForm').attr('action', '');
        $('#payment_invoice_id').val('');
        $('#payment_invoice_number_display').text('-');
        $('#payment_customer_name_display').text('-');
        $('#payment_total_amount_display').text('৳ 0.00');
        $('#payment_due_amount_display').text('৳ 0.00');
        $('#payment_amount').removeClass('is-invalid');
        $('#payment_amount_error').hide();
    });

    // --------------------------------------------------------------
    // 5. TOAST
    // --------------------------------------------------------------
    function showToast(msg, type = 'info') {
        $('.toast').remove();
        const icon = type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle';
        const $t = $(`
            <div class="alert alert-${type} alert-dismissible fade show position-fixed toast" style="top:1rem;right:1rem;z-index:9999;">
                <i class="fas fa-${icon} me-2"></i>${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`);
        $('body').append($t);
        setTimeout(() => $t.alert('close'), 5000);
    }

    // ------------------------------
    // OPTIONAL: Placeholder for export & view functions
    // ------------------------------
    window.exportMonthlyBills = function() {
        // Implement export logic (e.g., CSV/Excel via AJAX or redirect)
        alert('Export feature coming soon!');
    };

    window.viewInvoice = function(invoiceId) {
        fetch(`/admin/billing/invoice/${invoiceId}/html`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.text())
        .then(html => {
            document.getElementById('viewInvoiceContent').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('viewInvoiceContent').innerHTML = '<div class="text-center text-danger py-4">Failed to load invoice.</div>';
        });
    };

    window.printInvoice = function() {
        const printContent = document.getElementById('viewInvoiceContent').innerHTML;
        const original = document.body.innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = original;
        location.reload(); // restore JS event listeners
    };
});
</script>
@endsection