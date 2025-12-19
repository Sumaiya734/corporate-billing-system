@extends('layouts.admin')

@section('title', 'Monthly Bills - Admin Dashboard')

@section('content')
<!-- Toast Notification Container -->
<div id="toastContainer" style="position: fixed; top: 80px; right: 20px; z-index: 9999;"></div>

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
        @if(!($isCurrentMonth ?? false))
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateBillsModal">
            <i class="fas fa-plus me-1"></i>Generate Bills
        </button>
        @endif
        @if(!($isFutureMonth ?? false))
        @if($isMonthClosed ?? false)
        <button class="btn btn-secondary" disabled title="Month already closed">
            <i class="fas fa-lock me-1"></i>Month Closed
        </button>
        @else
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#closeMonthModal">
            <i class="fas fa-lock me-1"></i>Close Month
        </button>
        @endif
        @endif
        <a href="{{ route('admin.billing.billing-invoices') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Billing
        </a>
    </div>
</div>

<!-- Month Status Alert -->
@if($isMonthClosed ?? false)
<div class="alert alert-success mb-4">
    <i class="fas fa-lock me-2"></i>
    <strong>Month Closed:</strong> This billing month has been closed. All outstanding dues have been carried forward to the next billing cycle.
    <div class="mt-2">
        <small><i class="fas fa-check-circle me-1"></i>No further modifications can be made to this month's billing.</small>
    </div>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 bg-primary text-white">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="text-xs font-weight-bold text-uppercase mb-1 opacity-75">
                            <i class="fas fa-users me-1"></i> Overview
                        </div>
                        @php
                        // Calculate customer statistics based on actual invoice data
                        $totalInvoices = $invoices->total() ?? 0;
                        $customersWithDue = $invoices->filter(function($invoice) {
                        return in_array($invoice->status, ['unpaid', 'partial']) && $invoice->next_due > 0;
                        })->count();

                        $fullyPaidCustomers = $invoices->filter(function($invoice) {
                        return $invoice->status === 'paid' || $invoice->next_due <= 0;
                            })->count();

                            $totalCustomers = $customersWithDue + $fullyPaidCustomers;
                            $paidPercentage = $totalCustomers > 0 ? round(($fullyPaidCustomers / $totalCustomers) * 100) : 0;
                            @endphp
                            <div class="h5 mb-2 fw-bold"><i class="fas fa-file-invoice me-1"></i>{{ $totalInvoices }} Total Invoices</div>
                            <div class="small mt-2 pt-2 border-top border-white border-opacity-25 flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-exclamation-circle me-1"></i> Due</span>
                                    <span class="badge bg-warning text-dark fw-bold">{{ $customersWithDue }}</span>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <span><i class="fas fa-check-circle me-1"></i> Paid</span>
                                    <span class="badge bg-success fw-bold">{{ $fullyPaidCustomers }}</span>
                                </div>
                            </div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 bg-success text-white">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between">
                    <div class="flex-grow-1">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Billing Amount</div>
                        @php
                        // Calculate total billing amount from actual invoice data
                        $totalBillingAmount = $invoices->sum('total_amount');
                        @endphp
                        <div class="h5 mb-0">৳ {{ number_format($totalBillingAmount, 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-white-300"></i>
                    </div>
                </div>
                <div class="mt-auto pt-3">
                    <small class="text-white-50">Total amount billed</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 bg-warning text-white">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between">
                    <div class="flex-grow-1">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Payments</div>
                        @php
                        // Calculate pending amount properly: sum of (total_amount - received_amount) for each invoice
                        $pendingAmount = $invoices->sum(function($invoice) {
                        $total = $invoice->total_amount ?? 0;
                        $received = $invoice->received_amount ?? 0;
                        return max(0, $total - $received);
                        });
                        @endphp
                        <div class="h5 mb-0">৳ {{ number_format($pendingAmount, 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-white-300"></i>
                    </div>
                </div>
                <div class="mt-auto pt-3">
                    <small class="text-white-50">Amount yet to be collected</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 bg-info text-white">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between">
                    <div class="flex-grow-1">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Paid Amount</div>
                        @php
                        // Calculate paid amount from actual invoice data
                        $paidAmount = $invoices->sum('received_amount');
                        @endphp
                        <div class="h5 mb-0">৳ {{ number_format($paidAmount, 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-white-300"></i>
                    </div>
                </div>
                <div class="mt-auto pt-3">
                    <small class="text-white-50">Amount already collected</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Legend for Table Colors -->
<div class="alert alert-light border mb-3 persistent-alert">
    <div class="row align-items-center">
        <div class="col-md-8">
            <strong><i class="fas fa-info-circle me-2"></i>Table Legend:</strong>
            <span class="ms-3">
                <span class="badge bg-light text-dark border me-2">Normal</span> Regular billing
            </span>
            <span class="ms-2">
                <span class="badge bg-success me-2">Green Row</span> Advance payment (credit available)
            </span>
            <span class="ms-2">
                <span class="badge bg-warning text-dark me-2">Yellow Row</span> Customer due but no invoice
            </span>
            <span class="ms-2">
                <span class="badge bg-primary me-2">Billing Month</span> New billing cycle month
            </span>
            <span class="ms-2">
                <span class="badge bg-secondary me-2">Carry-Forward</span> Carry-forward month
            </span>
        </div>
        <div class="col-md-4 text-end">
            <small class="text-muted">
                <i class="fas fa-check-double me-1"></i>Advance payments show as "Confirmed"
            </small>
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
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Search customer..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button" onclick="searchTable()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select class="form-select form-select-sm" style="width: 150px;" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="overdue">Overdue</option>
                    <option value="advance">Advance</option>

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
                        <th>Product</th>
                        <th>Subtotal</th>
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
                    @php
                    $customerProduct = $invoice->customerProduct;
                    $customer = $customerProduct ? $customerProduct->customer : null;
                    $product = $customerProduct ? $customerProduct->product : null;
                    @endphp
                    @php
                    // Check for advance payment to add visual indicator
                    $totalAmount = $invoice->total_amount ?? 0;
                    $receivedAmount = $invoice->received_amount ?? 0;
                    $isAdvancePayment = $receivedAmount > $totalAmount && $totalAmount > 0;
                    $rowClass = $isAdvancePayment ? 'table-success' : '';
                    @endphp

                    @php
                    // Calculate due date for display
                    // Note: Filtering is already done in the controller
                    $actualDueDate = null;
                    if ($customerProduct) {
                    $assignDate = \Carbon\Carbon::parse($customerProduct->assign_date);
                    $invoiceMonth = \Carbon\Carbon::parse($month . '-01');
                    $billingCycleMonths = $customerProduct->billing_cycle_months ?? 1;

                    // Calculate the actual due date for this month
                    $dueDay = $customerProduct->due_date ? \Carbon\Carbon::parse($customerProduct->due_date)->day : $assignDate->day;
                    $actualDueDate = $invoiceMonth->copy()->day(min($dueDay, $invoiceMonth->daysInMonth));
                    }
                    @endphp

                    @if($customerProduct && $customer && $product)
                    <tr class="{{ $rowClass }}" data-invoice-id="{{ $invoice->invoice_id }}">
                        @if($customer && $product)
                        {{-- Invoice ID --}}
                        <td class="align-middle border-end">
                            <strong class="text-primary">{{ $invoice->invoice_number }}</strong>
                            <br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M j, Y') }}</small>
                        </td>

                        {{-- Customer Info --}}
                        <td class="align-middle border-end">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <a href="{{ route('admin.customers.show', $customer->c_id) }}" class="text-decoration-none" Target="_blank">
                                        <h6 class="mb-1 text-primary">{{ $customer->name ?? 'N/A' }}</h6>
                                    </a>
                                    <div class="text-muted small">
                                        <div>{{ $customer->email ?? 'N/A' }}</div>
                                        <div>{{ $customer->phone ?? 'N/A' }}</div>
                                        <div class="mt-1">
                                            <span class="badge bg-light text-dark">{{ $customer->customer_id ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    {{-- Billing Cycle Progress Bar and Tooltip --}}
                                    @if($customerProduct)
                                    @php
                                    $assignDate = \Carbon\Carbon::parse($customerProduct->assign_date);
                                    $billingCycleMonths = $customerProduct->billing_cycle_months ?? 1;
                                    $currentDate = \Carbon\Carbon::parse($month . '-01');

                                    // Calculate months since assignment
                                    $monthsSinceAssign = $assignDate->diffInMonths($currentDate);

                                    // Calculate current cycle position
                                    $cyclePosition = $monthsSinceAssign % $billingCycleMonths;

                                    // Calculate next billing date
                                    $cyclesCompleted = floor($monthsSinceAssign / $billingCycleMonths);
                                    $nextBillingDate = $assignDate->copy()->addMonths(($cyclesCompleted + 1) * $billingCycleMonths);

                                    // Determine progress percentage
                                    $progressPercent = ($cyclePosition / $billingCycleMonths) * 100;

                                    // Determine color class based on billing cycle
                                    $colorClass = '';
                                    switch($billingCycleMonths) {
                                    case 1: $colorClass = 'bg-success'; break;
                                    case 2: $colorClass = 'bg-info'; break;
                                    case 3: $colorClass = 'bg-warning'; break;
                                    case 6: $colorClass = 'bg-purple'; break;
                                    case 12: $colorClass = 'bg-danger'; break;
                                    default: $colorClass = 'bg-primary'; break;
                                    }
                                    @endphp

                                    {{-- Tooltip with billing cycle details --}}
                                    <div class="mt-2" data-bs-toggle="tooltip" data-bs-html="true"
                                        title="<strong>Billing Cycle Details:</strong><br>
                                                                Assign Date: {{ $assignDate->format('Y-m-d') }}<br>
                                                                Billing Cycle: {{ $billingCycleMonths }} months<br>
                                                                Next Billing Date: {{ $nextBillingDate->format('Y-m-d') }}<br>
                                                                Cycle Progress: {{ $cyclePosition }}/{{ $billingCycleMonths }} months">

                                        {{-- Progress bar --}}
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="progress" style="height: 10px;">
                                                    <?php echo '<div class="progress-bar ' . $colorClass . '" role="progressbar" style="width: ' . $progressPercent . '%" aria-valuenow="' . $progressPercent . '" aria-valuemin="0" aria-valuemax="100"></div>'; ?>
                                                </div>
                                                <div class="d-flex justify-content-between small text-muted mt-1">
                                                    <span>{{ $cyclePosition }}/{{ $billingCycleMonths }} months</span>
                                                    <span>Next: {{ $nextBillingDate->format('M Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                        </td>

                        {{-- Single Product Info with Due Date --}}
                        <td>
                            <div class="fw-medium text-dark">{{ $product->name ?? 'Unknown Product' }}</div>
                            <div class="text-muted small">
                                @php
                                $monthlyPrice = $product->monthly_price ?? 0;
                                $billingCycle = $customerProduct->billing_cycle_months ?? 1;
                                $customPrice = $customerProduct->custom_price ?? null;
                                $standardPrice = $monthlyPrice * $billingCycle;
                                $isCustomPrice = $customPrice && abs($customPrice - $standardPrice) > 0;
                                // Determine if this is a billing month or carry-forward month
                                $isBillingMonth = ($invoice->subtotal ?? 0) > 0;
                                $isCarryForwardMonth = ($invoice->subtotal ?? 0) == 0 && ($invoice->previous_due ?? 0) > 0;
                                @endphp

                                @if($isCustomPrice)
                                <span class="badge bg-warning text-dark">Custom</span>
                                @if($billingCycle > 1)
                                <span class="badge bg-info ms-1">{{ $billingCycle }} months cycle</span>
                                @endif
                                @else
                                ৳ {{ number_format($monthlyPrice, 2) }}/month
                                @if($billingCycle > 1)
                                <span class="badge bg-info ms-1">{{ $billingCycle }} months cycle</span>
                                @elseif($billingCycle == 1)
                                <span class="badge bg-info ms-1">Monthly</span>
                                @endif
                                @endif
                            </div>
                            <div class="mt-1">
                                <small class="text-success">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    <strong>Due: {{ $actualDueDate->format('M j, Y') }}</strong>
                                </small>
                            </div>
                            <!-- Billing Month / Carry-Forward Badges -->
                            <div class="mt-1">
                                @if($isBillingMonth)
                                <span class="badge bg-primary">Billing Month</span>
                                @elseif($isCarryForwardMonth)
                                <span class="badge bg-secondary">Carry-Forward</span>
                                @endif
                            </div>
                        </td>

                        {{-- Product Amount (from database) --}}
                        <td>
                            <div class="bill-amount">
                                <strong class="text-dark">৳ {{ number_format($invoice->subtotal ?? 0, 0) }}</strong>
                                <br><small class="text-muted">Current charges</small>
                                @if(($invoice->previous_due ?? 0) > 0)
                                @php
                                // Find the oldest unpaid invoice date for this customer
                                $oldestInvoice = \App\Models\Invoice::where('cp_id', $invoice->cp_id)
                                ->where('invoice_id', '<', $invoice->invoice_id)
                                    ->whereIn('status', ['unpaid', 'partial'])
                                    ->orderBy('issue_date', 'asc')
                                    ->first();
                                    $carriedFromDate = $oldestInvoice ? \Carbon\Carbon::parse($oldestInvoice->issue_date)->format('M Y') : 'previous months';
                                    @endphp
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        <strong>Overdue carried from {{ $carriedFromDate }}</strong>
                                    </small>
                                    @endif
                            </div>
                        </td>


                        {{-- Previous Due (from database) --}}
                        <td>
                            <div class="previous-due">
                                <strong class="{{ ($invoice->previous_due ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    ৳ {{ number_format($invoice->previous_due ?? 0, 0) }}
                                </strong>
                                <br><small class="text-muted">Previous due</small>
                            </div>
                        </td>

                        {{-- Total Amount (from database) --}}
                        <td>
                            <div class="total-amount">
                                <strong class="text-success">৳ {{ number_format($invoice->total_amount ?? 0, 0) }}</strong>
                                <br><small class="text-muted">Total due</small>
                                @if($customer && $product)
                                <br>
                                <a href="{{ route('admin.payment-details.index', ['search' => $customer->name, 'product_id' => $product->p_id]) }}"
                                    class="btn btn-sm btn-outline-primary mt-1"
                                    title="View payment details for {{ $customer->name }} - {{ $product->name }}">
                                    <!-- <i class="fas fa-search me-1"></i> -->
                                    Details
                                </a>
                                @endif
                            </div>
                        </td>

                        {{-- Received Amount (from database) --}}
                        <td>
                            <div class="received-amount">
                                @if(($invoice->received_amount ?? 0) > 0)
                                <strong class="text-info">৳ {{ number_format($invoice->received_amount ?? 0, 0) }}</strong>
                                @if(($invoice->total_amount ?? 0) > 0)
                                <br><small class="text-muted">{{ number_format((($invoice->received_amount ?? 0) / ($invoice->total_amount ?? 1)) * 100, 1) }}% paid</small>
                                @endif
                                @else
                                <span class="text-muted">৳ 0.00</span>
                                <br><small class="text-muted">No payment</small>
                                @endif
                            </div>
                        </td>

                        {{-- Next Due (from database) --}}
                        <td>
                            <div class="next-due">
                                @php
                                $nextDue = $invoice->next_due ?? 0;
                                $totalAmount = $invoice->total_amount ?? 0;
                                $receivedAmount = $invoice->received_amount ?? 0;

                                // Check payment status
                                $isAdvancePayment = $receivedAmount > $totalAmount && $totalAmount > 0;
                                $advanceAmount = $isAdvancePayment ? ($receivedAmount - $totalAmount) : 0;
                                $isFullyPaid = $nextDue <= 0 && $receivedAmount> 0;
                                $isPaid = $nextDue <= 0;
                                @endphp
                                @if($isAdvancePayment)
                                <span class="badge bg-success">
                                <i class="fas fa-check-double me-1"></i>Advance Paid
                                </span>
                                <br><small class="text-success">+৳ {{ number_format($advanceAmount, 0) }} credit</small>
                                @elseif($isPaid)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Paid
                                </span>
                                <br><small class="text-muted">{{ $receivedAmount > 0 ? 'Fully paid' : 'No due' }}</small>
                                @else
                                <strong class="text-danger">৳ {{ number_format($nextDue, 0) }}</strong>
                                <br><small class="text-muted">Outstanding</small>
                                @if($receivedAmount > 0)
                                <br><small class="text-info">Partial: ৳{{ number_format($receivedAmount, 0) }} paid</small>
                                @endif
                                @endif
                            </div>
                        </td>
                        {{-- Status (from database) --}}
                        <td class="align-middle">
                            {!! $invoice->status_badge ?? '<span class="badge bg-secondary">Unknown</span>' !!}
                            @if($invoice->status === 'confirmed')
                            <div class="mt-1">
                                <span class="badge bg-info">Due Carried Forward</span>
                            </div>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="align-middle">
                            <div class="d-flex flex-column gap-1">
                                @php
                                $nextDue = $invoice->next_due ?? 0;
                                $totalAmount = $invoice->total_amount ?? 0;
                                $receivedAmount = $invoice->received_amount ?? 0;
                                $billingCycleMonths = $customerProduct->billing_cycle_months ?? 1;

                                // Check if invoice is confirmed
                                $isConfirmed = $invoice->status === 'confirmed';

                                $isFullyPaid = ($receivedAmount >= $totalAmount && $totalAmount > 0) ||
                                ($nextDue <= 0.00 && $invoice->status === 'paid') ||
                                    ($nextDue <= 0.00 && $receivedAmount> 0);

                                        $isAdvancePayment = $receivedAmount > $totalAmount && $totalAmount > 0;
                                        $hasPartialPayment = ($receivedAmount > 0) && !$isFullyPaid && !$isConfirmed;
                                        $isMonthlyBilling = $billingCycleMonths == 1;
                                        @endphp

                                        @if($isMonthClosed ?? false)
                                        {{-- Month Closed --}}
                                        <button class="btn btn-outline-primary btn-sm view-invoice-btn" data-invoice-id="{{ $invoice->invoice_id }}" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal" title="View Invoice">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-secondary btn-sm" disabled title="Month Closed - No payments allowed">
                                            <i class="fas fa-lock"></i> Month Closed
                                        </button>
                                        @elseif($isConfirmed)
                                        {{-- Confirmed Status - Show View + Confirmed (muted) --}}
                                        <button class="btn btn-outline-info btn-sm view-invoice-btn" data-invoice-id="{{ $invoice->invoice_id }}" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal" title="View Invoice">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-secondary btn-sm" disabled title="User payment confirmed - Due carried forward">
                                            <i class="fas fa-check-circle"></i> Confirmed
                                        </button>
                                        @elseif($isFullyPaid)
                                        {{-- Fully Paid --}}
                                        <button class="btn btn-outline-info btn-sm view-invoice-btn" data-invoice-id="{{ $invoice->invoice_id }}" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal" title="View Invoice">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        @if($isMonthlyBilling)
                                        <button class="btn btn-secondary btn-sm" disabled title="Payment Confirmed - Monthly Billing">
                                            <i class="fas fa-check-circle"></i> Confirmed
                                        </button>
                                        @else
                                        <button class="btn btn-success btn-sm" disabled title="{{ $isAdvancePayment ? 'Advance Payment Confirmed' : 'Payment Confirmed - Multi-Month Billing' }}">
                                            <i class="fas fa-check-circle"></i> {{ $isAdvancePayment ? 'Advance Paid' : 'Confirmed' }}
                                        </button>
                                        @endif
                                        @elseif($hasPartialPayment)
                                        {{-- Partial Payment: Show Edit Payment + View + Confirm --}}
                                        <button class="btn btn-warning btn-sm payment-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addPaymentModal"
                                            data-invoice-id="{{ $invoice->invoice_id }}"
                                            data-cp-id="{{ $customerProduct->cp_id }}"
                                            data-invoice-number="{{ $invoice->invoice_number }}"
                                            data-customer-name="{{ e($customer->name ?? 'Customer') }}"
                                            data-customer-email="{{ e($customer->email ?? 'N/A') }}"
                                            data-customer-phone="{{ e($customer->phone ?? 'N/A') }}"
                                            data-subtotal="{{ $invoice->subtotal ?? 0 }}"
                                            data-previous-due="{{ $invoice->previous_due ?? 0 }}"
                                            data-total-amount="{{ $totalAmount }}"
                                            data-due-amount="{{ $nextDue }}"
                                            data-received-amount="{{ $receivedAmount }}"
                                            data-status="{{ $invoice->status }}"
                                            data-product-name="{{ e($product->name ?? 'Unknown Product') }}"
                                            title="Edit Payments">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-outline-info btn-sm view-invoice-btn" data-invoice-id="{{ $invoice->invoice_id }}" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal" title="View Invoice">
                                            <i class="fas fa-eye"></i> View
                                        </button>

                                        {{-- Add Confirm button --}}
                                        <button class="btn btn-warning btn-sm confirm-user-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmUserPaymentModal"
                                            data-invoice-id="{{ $invoice->invoice_id }}"
                                            data-cp-id="{{ $customerProduct->cp_id }}"
                                            data-customer-name="{{ e($customer->name ?? 'Customer') }}"
                                            data-product-name="{{ e($product->name ?? 'Unknown Product') }}"
                                            data-next-due="{{ number_format($nextDue, 2, '.', '') }}"
                                            title="Confirm and close user's month, carry forward remaining amount">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                        @else
                                        {{-- No Payment: Show Pay Now only --}}
                                        <button class="btn btn-primary btn-sm payment-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addPaymentModal"
                                            data-invoice-id="{{ $invoice->invoice_id }}"
                                            data-cp-id="{{ $customerProduct->cp_id }}"
                                            data-invoice-number="{{ $invoice->invoice_number }}"
                                            data-customer-name="{{ e($customer->name ?? 'Customer') }}"
                                            data-customer-email="{{ e($customer->email ?? 'N/A') }}"
                                            data-customer-phone="{{ e($customer->phone ?? 'N/A') }}"
                                            data-subtotal="{{ $invoice->subtotal ?? 0 }}"
                                            data-previous-due="{{ $invoice->previous_due ?? 0 }}"
                                            data-total-amount="{{ $totalAmount }}"
                                            data-due-amount="{{ $nextDue }}"
                                            data-received-amount="{{ $receivedAmount }}"
                                            data-status="{{ $invoice->status }}"
                                            data-product-name="{{ e($product->name ?? 'Unknown Product') }}"
                                            title="Add Payment">
                                            <i class="fas fa-money-bill-wave"></i> Pay Now
                                        </button>
                                        @endif
                            </div>
                        </td>
                        @else
                        <td colspan="11" class="text-center text-danger">
                            Invoice #{{$invoice->invoice_number}} has missing product or customer data.
                        </td>
                        @endif
                    </tr>
                    @else
                    <tr>
                        <td colspan="11" class="text-center text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Invoice #{{$invoice->invoice_number ?? 'N/A'}} has missing customer product data.
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4">
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

                    {{-- Due customers without invoices (only show for past months, not current month) --}}
                    @if(!($isCurrentMonth ?? false) && isset($dueCustomers) && $dueCustomers->isNotEmpty())
                    @php
                    $invoiceCustomerIds = ($invoices ?? collect())->pluck('customerProduct.c_id')->filter()->unique()->toArray();
                    @endphp
                    @foreach($dueCustomers as $dueCustomer)
                    @if(!in_array($dueCustomer->c_id, $invoiceCustomerIds))
                    <tr class="table-warning">
                        <td class="align-middle border-end">
                            <strong class="text-muted">Not Generated</strong>
                        </td>
                        <td class="align-middle border-end">
                            <a href="{{ route('admin.customers.show', $dueCustomer->c_id) }}" class="text-decoration-none" Target="_blank">
                                <h6 class="mb-1 text-primary">{{ $dueCustomer->name ?? 'N/A' }}</h6>
                            </a>
                            <div class="text-muted small">{{ $dueCustomer->customer_id ?? 'N/A' }}</div>
                        </td>
                        <td colspan="9" class="text-center">
                            <div class="alert alert-warning mb-0 py-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>This customer is due for billing but has no invoice.</strong>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <!-- How to read this table: -->
    <div class="card-footer bg-white">
        <div class="row align-items-center">
            <div class="col-md-12 mb-3">
                <div class="alert alert-info mb-0 small persistent-alert">
                    <!-- <div class="alert alert-info mb-4">
                                                            <strong><i class="fas fa-info-circle me-1"></i>How to read this table:</strong>
                                                            <ul class="mb-0 mt-1">
                                                                <li><strong>Product Amount</strong> = Current month charges (from products)</li>
                                                                <li><strong>Previous Due</strong> = Unpaid balance from past months</li>
                                                                <li><strong>Total Invoice</strong> = Product Amount + Previous Due</li>
                                                                <li><strong>Received</strong> = Payments made against this invoice</li>
                                                                <li><strong>Next Due</strong> = Total Invoice - Received (what customer still owes)</li>
                                                            </ul>
                                                        </div> -->
                    <!-- <div class="alert alert-info mb-4">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            <strong>Note:</strong>
                                                            <ul class="mb-0">
                                                                <li>Customers with invoices are shown in the table below</li>
                                                                <li>Customers who are due but don't have invoices yet are highlighted with a warning</li>
                                                                @if(!($isCurrentMonth ?? false))
                                                                <li>Use the "Generate Bills" button to create invoices for all customers or only those who are due</li>
                                                                @else
                                                                <li>Invoices for current month customers are automatically generated</li>
                                                                @endif
                                                                @if(!($isMonthClosed ?? false) && !($isFutureMonth ?? false))
                                                                <li><strong>Remember to close this month</strong> before accessing the next month's billing</li>
                                                                @endif
                                                            </ul>
                                                        </div> -->

                    <div class="mt-2 p-2 bg-light rounded">
                        <strong><i class="fas fa-calculator me-1"></i>Verification:</strong>
                        @php
                        // Calculate amounts from actual invoice data
                        $totalBillingAmount = $invoices->sum('total_amount');
                        $paidAmount = $invoices->sum('received_amount');
                        // Calculate pending amount properly: sum of (total_amount - received_amount) for each invoice
                        $pendingAmount = $invoices->sum(function($invoice) {
                        $total = $invoice->total_amount ?? 0;
                        $received = $invoice->received_amount ?? 0;
                        return max(0, $total - $received);
                        });
                        @endphp
                        <div class="mt-1">
                            Total Billing (৳{{ number_format($totalBillingAmount, 0) }})
                            - Paid (৳{{ number_format($paidAmount, 0) }})
                            = Pending (৳{{ number_format($pendingAmount, 0) }})
                        </div>
                        @php
                        $calculatedPending = $totalBillingAmount - $paidAmount;
                        $isBalanced = abs($calculatedPending - $pendingAmount) < 0;
                            @endphp
                            <div class="mt-1">
                            <span class="badge {{ $isBalanced ? 'bg-success' : 'bg-danger' }}">
                                <i class="fas fa-{{ $isBalanced ? 'check' : 'exclamation-triangle' }} me-1"></i>
                                {{ $isBalanced ? 'Balanced ✓' : 'Mismatch!' }}
                            </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($invoices->hasPages())
        <div class="col-md-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
                    </small>
                </div>
                <div>
                    {{ $invoices->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif

        <div class="col-md-6">
            <small class="text-muted">
                <i class="fas fa-check-circle text-success me-1"></i>
                Showing {{ $invoices->count() }} of {{ $invoices->total() }} invoices for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
            </small>
        </div>
        <div class="col-md-6 text-end">
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
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
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Choose how you want to generate bills for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                </div>

                <form id="generateBillsForm">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <div class="mb-3">
                        <label class="form-label">Billing Month</label>
                        <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}" readonly>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Generation Options</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="generationType" id="dueOnly" value="due_only" checked>
                            <label class="form-check-label" for="dueOnly">
                                <strong>Due Customers Only</strong>
                                <div class="text-muted small">Generate bills only for customers who are due based on their billing cycle</div>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="generationType" id="allCustomers" value="all_customers">
                            <label class="form-check-label" for="allCustomers">
                                <strong>All Active Customers</strong>
                                <div class="text-muted small">Generate bills for all active customers with products (regardless of billing cycle)</div>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Active Customers with products</label>
                        <input type="text" class="form-control" value="{{ $totalDueCustomers ?? 0 }} customers" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="generateBills()">
                    <i class="fas fa-sync me-1"></i>Generate Bills
                </button>
            </div>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="fas fa-print me-1"></i>Print Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Close Month Modal -->
<div class="modal fade" id="closeMonthModal" tabindex="-1" aria-labelledby="closeMonthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-lock me-2"></i>Close Billing Month - {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Closing this month will carry forward all outstanding dues to the next billing cycle.
                </div>

                <!-- Summary Section -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Month Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <small class="text-muted d-block">Total Billing Amount</small>
                                        @php
                                        // Calculate total billing amount from actual invoice data
                                        $totalBillingAmount = $invoices->sum('total_amount');
                                        @endphp
                                        <h4 class="mb-0 text-primary">৳ {{ number_format($totalBillingAmount, 0) }}</h4>
                                    </div>
                                    <i class="fas fa-file-invoice-dollar fa-2x text-primary opacity-50"></i>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <small class="text-muted d-block">Total Paid Amount</small>
                                        @php
                                        // Calculate paid amount from actual invoice data
                                        $paidAmount = $invoices->sum('received_amount');
                                        @endphp
                                        <h4 class="mb-0 text-success">৳ {{ number_format($paidAmount, 0) }}</h4>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-danger text-white rounded">
                                    <div>
                                        <small class="d-block opacity-75">Outstanding Due (To be carried forward)</small>
                                        @php
                                        // Calculate pending amount properly: sum of (total_amount - received_amount) for each invoice
                                        $pendingAmount = $invoices->sum(function($invoice) {
                                        $total = $invoice->total_amount ?? 0;
                                        $received = $invoice->received_amount ?? 0;
                                        return max(0, $total - $received);
                                        });
                                        @endphp
                                        <h3 class="mb-0">৳ {{ number_format($pendingAmount, 0) }}</h3>
                                    </div>
                                    <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Breakdown -->
                        <div class="mt-3 pt-3 border-top">
                            <h6 class="mb-3"><i class="fas fa-users me-2"></i>Customer Status</h6>
                            @php
                            // Calculate customer statistics based on actual invoice data
                            $totalInvoices = $invoices->total() ?? 0;
                            $customersWithDue = $invoices->filter(function($invoice) {
                            return in_array($invoice->status, ['unpaid', 'partial']) && $invoice->next_due > 0;
                            })->count();

                            $fullyPaidCustomers = $invoices->filter(function($invoice) {
                            return $invoice->status === 'paid' || $invoice->next_due <= 0;
                                })->count();
                                @endphp
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <h5 class="mb-0 text-primary">{{ $totalInvoices }}</h5>
                                            <small class="text-muted">Total Invoices</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <h5 class="mb-0 text-success">{{ $fullyPaidCustomers }}</h5>
                                            <small class="text-muted">Fully Paid</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <h5 class="mb-0 text-warning">{{ $customersWithDue }}</h5>
                                            <small class="text-muted">With Dues</small>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Section -->
                <div class="card border-warning">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>What happens when you close this month?</h6>
                        @php
                        // Calculate pending amount properly: sum of (total_amount - received_amount) for each invoice
                        $pendingAmount = $invoices->sum(function($invoice) {
                        $total = $invoice->total_amount ?? 0;
                        $received = $invoice->received_amount ?? 0;
                        return max(0, $total - $received);
                        });
                        @endphp
                        <ul class="mb-3">
                            <li>All outstanding dues (৳{{ number_format($pendingAmount, 0) }}) will be carried forward to next month's invoices</li>
                            <li>This month's billing cycle will be marked as closed</li>
                            <li>Fully paid invoices will remain as completed</li>
                            <li>Unpaid and partial invoices will have their dues transferred to the next billing period</li>
                        </ul>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmCloseMonth">
                            <label class="form-check-label" for="confirmCloseMonth">
                                <strong>I understand that this action will close {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }} billing and carry forward all outstanding dues</strong>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmCloseMonthBtn" onclick="closeMonth()" disabled>
                    <i class="fas fa-lock me-1"></i>Close Month & Carry Forward Dues
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include Separate Payment Modal -->
@include('admin.billing.payment-modal')

<!-- Confirm User Payment Modal -->
<!-- Confirm User Payment Modal -->
<div class="modal fade" id="confirmUserPaymentModal" tabindex="-1" aria-labelledby="confirmUserPaymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Confirm User Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>This will close the billing month for this customer</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Customer</label>
                    <div class="p-2 bg-light rounded" id="confirm_customer_name">-</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Product</label>
                    <div class="p-2 bg-light rounded" id="confirm_product_name">-</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Remaining Amount to Carry Forward</label>
                    <div class="p-3 bg-warning bg-opacity-10 rounded text-center">
                        <h4 class="mb-0 text-warning" id="confirm_next_due">৳ 0.00</h4>
                    </div>
                </div>

                <div class="alert alert-warning mb-0">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>What happens next?</strong>
                    <ul class="mb-0 mt-2">
                        <li>The remaining amount will be carried forward to the next billing cycle</li>
                        <li>This customer's billing for this month will be marked as confirmed</li>
                        <li>No further payments can be added for this month</li>
                        <li>A new subtotal charge will be added in the next billing cycle</li>
                        <li>Due amount will still be visible and added to next payment</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmUserPaymentBtn" onclick="executeConfirmUserPayment()">
                    <i class="fas fa-check me-1"></i>Confirm & Carry Forward
                </button>
            </div>
        </div>
    </div>
</div>

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
        /* Billing cycle colors */
        --cycle-1: #28a745;
        /* Green */
        --cycle-2: #17a2b8;
        /* Blue */
        --cycle-3: #fd7e14;
        /* Orange */
        --cycle-6: #6f42c1;
        /* Purple */
        --cycle-12: #dc3545;
        /* Red */
    }

    /* Custom color classes */
    .bg-purple {
        background-color: var(--cycle-6) !important;
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
        display: flex;
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

    /* Enhanced badge styles for better visibility */
    .badge.bg-success {
        background-color: #06d6a0 !important;
        color: white !important;
        padding: 6px 12px;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(6, 214, 160, 0.3);
    }

    .badge.bg-danger {
        background-color: rgb(247, 50, 50) !important;
        color: white !important;
        padding: 6px 12px;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(239, 71, 111, 0.3);
    }

    .badge.bg-warning {
        background-color: #ffd166 !important;
        color: #000 !important;
        padding: 6px 12px;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(255, 209, 102, 0.3);
    }

    /* Paid button styling */
    .btn-success:disabled {
        background-color: #06d6a0 !important;
        border-color: #06d6a0 !important;
        opacity: 0.8;
    }

    .products-list .product-item {
        padding: 8px;
        border-left: 3px solid var(--primary);
        background-color: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 8px;
    }

    .products-list .product-item:last-child {
        margin-bottom: 0;
    }

    /* Product row grouping styles */
    tbody tr.border-top-0 {
        border-top: 1px dashed #e0e0e0 !important;
    }

    tbody td[rowspan] {
        background-color: #fafbfc;
        border-right: 2px solid #e9ecef;
    }

    tbody tr:hover td[rowspan] {
        background-color: rgba(67, 97, 238, 0.03);
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
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

    /* Toast Notification Styles */
    #toastContainer {
        pointer-events: none;
    }

    #toastContainer .toast-notification {
        pointer-events: all;
    }

    .toast-notification .btn-close {
        opacity: 0.8;
    }

    .toast-notification .btn-close:hover {
        opacity: 1;
    }

    /* Pagination Styles */
    .pagination {
        margin-bottom: 0;
    }

    .pagination .page-link {
        color: var(--primary);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin: 0 4px;
        padding: 8px 12px;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(67, 97, 238, 0.2);
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(67, 97, 238, 0.3);
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .pagination .page-link:focus {
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    /* Advance Payment Row Styling */
    .table-success {
        background-color: rgba(6, 214, 160, 0.1) !important;
        border-left: 4px solid #06d6a0 !important;
    }

    .table-success:hover {
        background-color: rgba(6, 214, 160, 0.15) !important;
    }

    .table-success td {
        border-bottom-color: rgba(6, 214, 160, 0.2) !important;
    }

    /* Advance Payment Badge */
    .badge.bg-success {
        animation: pulse-success 2s infinite;
    }

    @keyframes pulse-success {

        0%,
        100% {
            box-shadow: 0 2px 4px rgba(6, 214, 160, 0.3);
        }

        50% {
            box-shadow: 0 2px 8px rgba(6, 214, 160, 0.5);
        }
    }
</style>
@endsection

@section('scripts')
@vite(['resources/js/app.js'])

<script>
    // Global functions accessible from HTML onclick attributes
    window.exportMonthlyBills = function() {
        alert('Export feature coming soon!');
    };

    window.viewInvoice = function(invoiceId) {
        const baseUrl = "{{ url('admin/billing') }}";
        const viewInvoiceUrl = baseUrl + '/invoice-html/' + invoiceId;
        const contentDiv = document.getElementById('viewInvoiceContent');

        // Show loading
        contentDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Loading invoice...</p></div>';

        fetch(viewInvoiceUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load invoice (Status: ' + response.status + ')');
                }
                return response.text();
            })
            .then(html => {
                if (html && html.trim().length > 0) {
                    contentDiv.innerHTML = html;
                } else {
                    contentDiv.innerHTML = '<div class="alert alert-warning text-center py-4"><i class="fas fa-info-circle fa-2x mb-3"></i><h5>No invoice data</h5><p>The invoice appears to be empty.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading invoice:', error);
                contentDiv.innerHTML = '<div class="alert alert-danger text-center py-4"><i class="fas fa-exclamation-triangle fa-2x mb-3"></i><h5>Failed to load invoice</h5><p>' + error.message + '</p><p class="small text-muted">Invoice ID: ' + invoiceId + '</p></div>';
            });
    };

    window.printInvoice = function() {
        const viewInvoiceContent = document.getElementById('viewInvoiceContent');
        if (!viewInvoiceContent) {
            alert('Invoice content not found');
            return;
        }
        const printContent = viewInvoiceContent.innerHTML;

        // Create a new window with proper styling
        const printWindow = window.open('', '_blank', 'width=800,height=600');

        if (printWindow) {
            printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Invoice</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    body {
                        padding: 20px;
                        font-family: Arial, sans-serif;
                    }
                    .invoice-container {
                        background: white;
                        max-width: 900px;
                        margin: 0 auto;
                    }
                    .invoice-header h2 {
                        color: #2c3e50;
                        font-weight: 700;
                    }
                    .table th {
                        background-color: #f8f9fa;
                        font-weight: 600;
                        color: #2c3e50;
                    }
                    .table-bordered {
                        border: 2px solid #dee2e6;
                    }
                    .table-bordered th,
                    .table-bordered td {
                        border: 1px solid #dee2e6;
                    }
                    @media print {
                        body {
                            padding: 0;
                        }
                        .no-print {
                            display: none !important;
                        }
                    }
                </style>
            </head>
            <body>
                ${printContent}
                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() {
                            window.close();
                        };
                    };
                <\/script>
            </body>
            </html>
        `);
            printWindow.document.close();
        } else {
            // Fallback if popup is blocked
            alert('Please allow popups to print the invoice');
        }
    };

    window.searchTable = function() {
        const searchInput = document.getElementById('searchInput');
        const filter = searchInput.value.trim().toLowerCase();
        const tableRows = document.querySelectorAll('#monthlyBillsTable tbody tr');

        tableRows.forEach(row => {
            // Skip special rows (warning and secondary rows)
            if (row.classList.contains('table-warning') || row.classList.contains('table-secondary')) {
                return;
            }

            // Get searchable content from invoice and customer columns
            const invoiceCell = row.cells[0]; // Invoice ID column
            const customerCell = row.cells[1]; // Customer Info column

            if (!invoiceCell || !customerCell) {
                if (row) row.style.display = 'none';
                return;
            }

            // Get all text content from both cells
            const invoiceText = invoiceCell.textContent.trim().toLowerCase();
            const customerText = customerCell.textContent.trim().toLowerCase();

            // Also get the invoice number specifically from the strong tag
            const invoiceNumberElement = invoiceCell.querySelector('strong');
            const invoiceNumber = invoiceNumberElement ? invoiceNumberElement.textContent.trim().toLowerCase() : '';

            // Combine all searchable text
            const searchableText = `${invoiceText} ${customerText} ${invoiceNumber}`;

            // Debug: log for first row to see what we're searching
            if (row === tableRows[0] && filter !== '') {
                console.log('Search filter:', filter);
                console.log('Invoice text:', invoiceText);
                console.log('Invoice number:', invoiceNumber);
                console.log('Customer text:', customerText.substring(0, 100));
                console.log('Searchable text:', searchableText.substring(0, 200));
            }

            // Show/hide based on search
            if (row) {
                if (filter === '' || searchableText.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });

        // Re-apply status filter after search
        filterTableByStatus();
    };

    window.generateBills = function() {
        // Get form elements with proper error checking
        const form = document.getElementById('generateBillsForm');
        if (!form) {
            console.error('Generate bills form not found');
            alert('Error: Form not found');
            return;
        }

        const monthInput = form.querySelector('input[name="month"]');
        if (!monthInput) {
            console.error('Month input not found');
            alert('Error: Month input not found');
            return;
        }
        const month = monthInput.value;

        const generationTypeInput = form.querySelector('input[name="generationType"]:checked');
        if (!generationTypeInput) {
            console.error('Generation type input not found');
            alert('Error: Please select a generation option');
            return;
        }
        const generationType = generationTypeInput.value;

        let url, message;
        if (generationType === 'all_customers') {
            url = "{{ route('admin.billing.generate-monthly-bills-all') }}";
            message = "Generating bills for all active customers...";
        } else {
            url = "{{ route('admin.billing.generate-monthly-bills') }}";
            message = "Generating bills for due customers only...";
        }

        // Show loading message
        const modal = document.getElementById('generateBillsModal');
        if (!modal) {
            console.error('Generate bills modal not found');
            alert('Error: Modal not found');
            return;
        }

        const originalContent = modal.querySelector('.modal-body').innerHTML;
        modal.querySelector('.modal-body').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">${message}</p>
        </div>
    `;

        // Disable buttons
        modal.querySelectorAll('button').forEach(btn => {
            btn.disabled = true;
        });

        // Submit form via fetch
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: `month=${encodeURIComponent(month)}`
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.json();
                }
            })
            .then(data => {
                // Close modal and refresh page
                const generateBillsModal = bootstrap.Modal.getInstance(document.getElementById('generateBillsModal'));
                if (generateBillsModal) {
                    generateBillsModal.hide();
                }

                // Show success message and reload page
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to generate bills'));
                    // Restore modal content and enable buttons
                    modal.querySelector('.modal-body').innerHTML = originalContent;
                    modal.querySelectorAll('button').forEach(btn => {
                        btn.disabled = false;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Restore modal content and enable buttons
                modal.querySelector('.modal-body').innerHTML = originalContent;
                modal.querySelectorAll('button').forEach(btn => {
                    btn.disabled = false;
                });
                alert('Error generating bills: ' + error.message);
            });
    };

    window.closeMonth = function() {
        const month = "{{ $month }}";
        const closeMonthUrl = "{{ route('admin.billing.close-month') }}";

        // Show loading state
        const confirmBtn = document.getElementById('confirmCloseMonthBtn');
        if (!confirmBtn) {
            console.error('Close month button not found');
            return;
        }
        const originalBtnHtml = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Closing Month...';
        confirmBtn.disabled = true;

        fetch(closeMonthUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: `month=${encodeURIComponent(month)}`
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.json();
                }
            })
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');

                    // Close modal immediately
                    try {
                        const modalEl = document.getElementById('closeMonthModal');
                        const modalInst = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                        if (modalInst) modalInst.hide();
                    } catch (ignore) {}

                    // ENHANCED: Handle redirect and auto-refresh
                    if (data.redirect_to) {
                        // Show redirect message
                        showToast('Redirecting', 'Redirecting to Billing & Invoices page...', 'info');

                        // Set flag for auto-refresh on billing-invoices page
                        if (data.auto_refresh) {
                            localStorage.setItem('billing_auto_refresh', JSON.stringify({
                                timestamp: Date.now(),
                                month_closed: month,
                                message: data.message
                            }));
                        }

                        // Notify other tabs about month closure
                        try {
                            const payload = {
                                month: month,
                                ts: Date.now(),
                                redirect_to: data.redirect_to
                            };
                            localStorage.setItem('billing_month_closed', JSON.stringify(payload));

                            if (window.BroadcastChannel) {
                                try {
                                    const bc = new BroadcastChannel('billing_channel');
                                    bc.postMessage(payload);
                                    bc.close();
                                } catch (e) {
                                    console.warn('BroadcastChannel error', e);
                                }
                            }
                        } catch (err) {
                            console.warn('Notify refresh failed', err);
                        }

                        // Redirect to billing-invoices page after brief delay
                        setTimeout(() => {
                            window.location.href = data.redirect_to;
                        }, 1500);
                    } else {
                        // Fallback: reload current page if no redirect specified
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    throw new Error(data.message || 'Failed to close month');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', error.message, 'danger');
                confirmBtn.innerHTML = originalBtnHtml;
                confirmBtn.disabled = false;
            });
    };

    // Enhanced Toast Notification Function
    window.showToast = function(msg, type = 'info', details = null) {
        const toastId = 'toast-' + Date.now();
        const icon = type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : type === 'warning' ? 'exclamation-circle' : 'info-circle';
        const bgColor = type === 'success' ? '#06d6a0' : type === 'danger' ? '#ef476f' : type === 'warning' ? '#ffd166' : '#118ab2';

        let detailsHtml = '';
        if (details) {
            detailsHtml = '<div class="mt-2 pt-2 border-top border-light" style="font-size: 0.85rem;">' + details + '</div>';
        }

        // Create toast element with inline styles
        const toastElement = document.createElement('div');
        toastElement.id = toastId;
        toastElement.className = 'toast-notification';
        toastElement.style.cssText = 'background: ' + bgColor + '; color: white; padding: 16px 20px; border-radius: 12px; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: slideInRight 0.3s ease-out; max-width: 400px;';

        toastElement.innerHTML = `
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-${icon} fa-lg me-3"></i>
            </div>
            <div class="flex-grow-1">
                <div style="font-weight: 600; margin-bottom: 4px;">${msg}</div>
                ${detailsHtml}
            </div>
            <button type="button" class="btn-close btn-close-white ms-3" onclick="const toast = document.getElementById('${toastId}'); if (toast) toast.remove();" style="font-size: 0.8rem;"></button>
        </div>
    `;

        const toastContainer = document.getElementById('toastContainer');
        if (toastContainer) {
            toastContainer.appendChild(toastElement);
        }

        // Auto remove after 6 seconds
        setTimeout(() => {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }
        }, 6000);
    };

    // Store confirmation data globally
    // let confirmPaymentData = {};

    // Store confirmation data globally
    let confirmPaymentData = {};

    // Function to execute the confirmation WITHOUT page refresh
    window.executeConfirmUserPayment = function() {
        const {
            invoiceId,
            cpId,
            customerName,
            productName,
            nextDue
        } = confirmPaymentData;

        console.log('executeConfirmUserPayment called with data:', {
            invoiceId,
            cpId,
            customerName,
            productName,
            nextDue
        });

        if (!invoiceId || !cpId) {
            showToast('Error', 'Missing invoice data', 'danger');
            return;
        }

        // Show loading state on button
        const confirmBtn = document.getElementById('confirmUserPaymentBtn');
        const originalBtnHtml = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
        confirmBtn.disabled = true;

        // Send AJAX request to confirm user payment
        fetch('{{ route("admin.billing.confirm-user-payment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    invoice_id: invoiceId,
                    cp_id: cpId,
                    next_due: parseFloat(nextDue)
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('AJAX response received:', data);
                if (data.success) {
                    // Close modal properly with backdrop cleanup
                    const modalElement = document.getElementById('confirmUserPaymentModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    // Manually remove any remaining backdrops to prevent screen from staying dim
                    setTimeout(() => {
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(backdrop => {
                            if (backdrop.parentNode) {
                                backdrop.parentNode.removeChild(backdrop);
                            }
                        });
                        document.body.classList.remove('modal-open');
                    }, 100);

                    // Show success toast WITHOUT page refresh
                    let details = `
                <div><strong>Customer:</strong> ${customerName}</div>
                <div><strong>Product:</strong> ${productName}</div>
                <div><strong>Carried Forward:</strong> ৳${parseFloat(nextDue).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
            `;

                    // Add information about next month if available
                    if (data.next_month_url) {
                        details += `
                <div class="mt-2"><strong>Next Month:</strong> <a href="${data.next_month_url}" class="btn btn-sm btn-outline-primary">View Next Month's Billing</a></div>
                <div class="mt-1"><small><i class="fas fa-arrow-right me-1"></i>Next month's invoice has been created with the carried forward amount</small></div>
                <div class="mt-1"><small><i class="fas fa-plus-circle me-1 text-success"></i>A new subtotal charge of ৳${parseFloat(data.next_cycle_subtotal || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})} will be added in the next billing cycle</small></div>
                `;
                    } else {
                        details += `
                <div class="mt-1"><small><i class="fas fa-info-circle me-1"></i>Due amount will be added to next payment</small></div>
                <div class="mt-1"><small><i class="fas fa-plus-circle me-1 text-success"></i>A new subtotal charge of ৳${parseFloat(data.next_cycle_subtotal || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})} will be added in the next billing cycle</small></div>
                `;
                    }

                    showToast('Payment Confirmed Successfully!', 'success', details);

                    // Small delay to ensure modal is fully closed before updating UI
                    setTimeout(() => {
                        // Update UI dynamically WITHOUT page refresh
                        const safeNextDue = parseFloat(nextDue) || 0;
                        console.log('Calling updateUIAfterConfirmation with:', {
                            invoiceId,
                            cpId,
                            customerName,
                            nextDue: safeNextDue
                        });

                        try {
                            updateUIAfterConfirmation(invoiceId, cpId, customerName, safeNextDue);
                        } catch (uiError) {
                            console.error('Error updating UI after confirmation:', uiError);
                            showToast('Payment confirmed successfully! Please refresh the page to see updated display.', 'warning');
                        }
                    }, 300);

                } else {
                    showToast('Error', data.message || 'Failed to confirm user payment', 'danger');
                    // Restore button
                    confirmBtn.innerHTML = originalBtnHtml;
                    confirmBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Network error occurred', 'danger');
                // Restore button
                confirmBtn.innerHTML = originalBtnHtml;
                confirmBtn.disabled = false;
            });
    };
    // Function to update UI after confirmation WITHOUT page refresh
    function updateUIAfterConfirmation(invoiceId, cpId, customerName, nextDue) {
        console.log('Updating UI for invoice:', invoiceId, 'with due:', nextDue);

        try {
            // Force a small delay to ensure DOM is ready
            setTimeout(() => {
                // Find all confirm buttons for this invoice and update them
                const confirmButtons = document.querySelectorAll(`.confirm-user-btn[data-invoice-id="${invoiceId}"]`);
                console.log('Found confirm buttons to update:', confirmButtons.length);

                confirmButtons.forEach((button, index) => {
                    console.log('Updating confirm button', index, button);
                    // Replace with View and Confirmed buttons (muted)
                    const actionCell = button.closest('.d-flex');
                    if (actionCell) {
                        // Clear the action cell
                        actionCell.innerHTML = '';

                        // Add View button
                        const viewButton = document.createElement('button');
                        viewButton.className = 'btn btn-outline-info btn-sm view-invoice-btn mb-1';
                        viewButton.setAttribute('data-invoice-id', invoiceId);
                        viewButton.setAttribute('data-bs-toggle', 'modal');
                        viewButton.setAttribute('data-bs-target', '#viewInvoiceModal');
                        viewButton.title = 'View Invoice';
                        viewButton.innerHTML = '<i class="fas fa-eye"></i> View';

                        // Add Confirmed button (muted)
                        const confirmedButton = document.createElement('button');
                        confirmedButton.className = 'btn btn-secondary btn-sm';
                        confirmedButton.disabled = true;
                        confirmedButton.title = 'User payment confirmed - Due carried forward';
                        confirmedButton.innerHTML = '<i class="fas fa-check-circle"></i> Confirmed';

                        // Add both buttons to the action cell
                        actionCell.appendChild(viewButton);
                        actionCell.appendChild(confirmedButton);
                    }
                });

                // Also update any payment buttons to show the confirmed status
                const paymentButtons = document.querySelectorAll(`.payment-btn[data-invoice-id="${invoiceId}"]`);
                console.log('Found payment buttons to update:', paymentButtons.length);
                paymentButtons.forEach((button, index) => {
                    console.log('Updating payment button', index, button);
                    // Replace payment button with View and Confirmed buttons (muted)
                    const actionCell = button.closest('.d-flex');
                    if (actionCell) {
                        // Clear the action cell
                        actionCell.innerHTML = '';

                        // Add View button
                        const viewButton = document.createElement('button');
                        viewButton.className = 'btn btn-outline-info btn-sm view-invoice-btn mb-1';
                        viewButton.setAttribute('data-invoice-id', invoiceId);
                        viewButton.setAttribute('data-bs-toggle', 'modal');
                        viewButton.setAttribute('data-bs-target', '#viewInvoiceModal');
                        viewButton.title = 'View Invoice';
                        viewButton.innerHTML = '<i class="fas fa-eye"></i> View';

                        // Add Confirmed button (muted)
                        const confirmedButton = document.createElement('button');
                        confirmedButton.className = 'btn btn-secondary btn-sm';
                        confirmedButton.disabled = true;
                        confirmedButton.title = 'User payment confirmed - Due carried forward';
                        confirmedButton.innerHTML = '<i class="fas fa-check-circle"></i> Confirmed';

                        // Add both buttons to the action cell
                        actionCell.appendChild(viewButton);
                        actionCell.appendChild(confirmedButton);
                    }
                });

                // Update the due amount display in the table to show it's carried forward
                const rows = document.querySelectorAll(`tr[data-invoice-id="${invoiceId}"]`);
                console.log('Found rows to update:', rows.length);

                rows.forEach((row, index) => {
                    console.log('Updating row', index, row);

                    // Update next due cell
                    const nextDueCell = row.querySelector('.next-due');
                    if (nextDueCell) {
                        console.log('Updating next due cell:', nextDueCell);
                        // Safely format the nextDue amount
                        const safeNextDue = parseFloat(nextDue) || 0;
                        const formattedAmount = safeNextDue.toLocaleString('en-BD', {
                            minimumFractionDigits: 2
                        });
                        nextDueCell.innerHTML = `
                        <strong class="text-warning">৳ ${formattedAmount}</strong>
                        <br><small class="text-warning"><i class="fas fa-forward me-1"></i>Carried Forward</small>
                    `;
                        // Force reflow
                        nextDueCell.offsetHeight;
                    } else {
                        console.log('Next due cell not found in row', index);
                    }

                    // Update status cell to show confirmed
                    const statusCells = row.querySelectorAll('td');
                    if (statusCells.length >= 9) { // Status is typically the 9th column (0-indexed as 8)
                        const statusCell = statusCells[8];
                        if (statusCell) {
                            console.log('Updating status cell:', statusCell);
                            // Clear the cell and add our own badge
                            statusCell.innerHTML = '';
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-success';
                            badge.innerHTML = '<i class="fas fa-check-double me-1"></i>Confirmed';
                            statusCell.appendChild(badge);
                            // Force reflow
                            statusCell.offsetHeight;
                        }
                    }

                    // Force reflow on the entire row
                    row.offsetHeight;
                });

                // Force a repaint/reflow of the entire table
                const table = document.getElementById('monthlyBillsTable');
                if (table && table.style) {
                    table.style.visibility = 'hidden';
                    // Force reflow
                    table.offsetHeight;
                    table.style.visibility = 'visible';
                }

                // Show a success message to the user
                showToast('Success', 'Payment confirmed and due amount carried forward', 'success');

                // Scroll to the first updated row to make the change visible
                if (rows.length > 0) {
                    // Wait a bit for DOM updates to complete
                    setTimeout(() => {
                        rows[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        // Add a temporary highlight effect
                        rows[0].classList.add('table-success');
                        // Force reflow
                        rows[0].offsetHeight;
                        setTimeout(() => {
                            rows[0].classList.remove('table-success');
                            // Force reflow
                            rows[0].offsetHeight;
                        }, 2000);
                    }, 100);
                }

                console.log('UI updated for invoice:', invoiceId, 'with due:', nextDue);
            }, 100); // Increased delay to ensure everything is ready
        } catch (error) {
            console.error('Error updating UI:', error);
            // Fallback: Show a message and suggest manual refresh
            showToast('Notice', 'Display updated. Please refresh page if changes are not visible.', 'info');
        }
    }

    // Handle confirm user button clicks - populate modal when shown
    document.addEventListener('DOMContentLoaded', function() {
        const confirmUserPaymentModal = document.getElementById('confirmUserPaymentModal');
        if (confirmUserPaymentModal) {
            confirmUserPaymentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('confirm-user-btn')) {
                    const invoiceId = button.getAttribute('data-invoice-id');
                    const cpId = button.getAttribute('data-cp-id');
                    const customerName = button.getAttribute('data-customer-name');
                    const productName = button.getAttribute('data-product-name');
                    const nextDue = button.getAttribute('data-next-due');
                    const rowElement = button.closest('tr');

                    // Store data for later use
                    confirmPaymentData = {
                        invoiceId: invoiceId,
                        cpId: cpId,
                        customerName: customerName,
                        productName: productName,
                        nextDue: nextDue,
                        rowElement: rowElement
                    };

                    // Update modal content
                    const confirmCustomerName = document.getElementById('confirm_customer_name');
                    const confirmProductName = document.getElementById('confirm_product_name');
                    const confirmNextDue = document.getElementById('confirm_next_due');

                    if (confirmCustomerName) confirmCustomerName.textContent = customerName;
                    if (confirmProductName) confirmProductName.textContent = productName;
                    if (confirmNextDue) confirmNextDue.textContent = `৳ ${(parseFloat(nextDue) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                }
            });

            // Reset modal on close
            confirmUserPaymentModal.addEventListener('hidden.bs.modal', function() {
                const confirmBtn = document.getElementById('confirmUserPaymentBtn');
                if (confirmBtn) {
                    confirmBtn.innerHTML = '<i class="fas fa-check me-1"></i>Confirm & Carry Forward';
                    confirmBtn.disabled = false;
                }
                confirmPaymentData = {};
            });

        }
    });

    // Table Filtering Function
    window.filterTableByStatus = function() {
        const statusFilter = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');

        if (!statusFilter) return;

        const statusValue = statusFilter.value.toLowerCase();
        const searchFilter = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const tableRows = document.querySelectorAll('#monthlyBillsTable tbody tr');

        tableRows.forEach(row => {
            // Always show special rows (warning and secondary)
            if (row && row.classList && (row.classList.contains('table-warning') || row.classList.contains('table-secondary'))) {
                row.style.display = '';
                return;
            }

            // Search Filter (by customer name in column 1)
            let matchesSearch = true;
            if (searchFilter !== '') {
                const customerCell = row.cells[1];
                if (customerCell) {
                    const customerText = customerCell.textContent.trim().toLowerCase();
                    matchesSearch = customerText.includes(searchFilter);
                } else {
                    matchesSearch = false;
                }
            }

            // Status Filter (column 8)
            let matchesStatus = true;
            if (statusValue !== 'all') {
                const statusCell = row.cells[8];
                if (statusCell) {
                    const statusBadge = statusCell.querySelector('span.badge');
                    if (statusBadge) {
                        const textContent = statusBadge.textContent.trim().toLowerCase();

                        // Match based on badge text
                        if (statusValue === 'paid') {
                            // Match "Paid" but not "Unpaid" or "Advance Paid"
                            matchesStatus = (textContent === 'paid') && !textContent.includes('unpaid') && !textContent.includes('advance');
                        } else if (statusValue === 'unpaid') {
                            // Match "Unpaid" only
                            matchesStatus = textContent === 'unpaid';
                        } else if (statusValue === 'partial') {
                            // Match "Partial" only
                            matchesStatus = textContent.includes('partial');
                        } else if (statusValue === 'overdue') {
                            // Match rows with "Partial" status (which indicates overdue with partial payment)
                            matchesStatus = textContent.includes('partial') || textContent.includes('overdue');
                        } else if (statusValue === 'advance') {
                            // Match "Advance Paid" or "Advance"
                            matchesStatus = textContent.includes('advance');
                        } else {
                            matchesStatus = false;
                        }
                    } else {
                        matchesStatus = false;
                    }
                } else {
                    matchesStatus = false;
                }
            }

            // Apply visibility
            if (row && row.style) {
                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    };

    // Global error handler for uncaught errors
    window.addEventListener('error', function(e) {
        console.error('Uncaught error:', e.error);
        // Prevent the error from breaking the page
        return true;
    });

    // Safe DOM operation helper
    function safeDOM(operation) {
        try {
            return operation();
        } catch (error) {
            console.error('DOM operation failed:', error);
            return null;
        }
    }

    // DOM Ready Event Listener
    document.addEventListener('DOMContentLoaded', function() {
        // Payment Modal functionality
        const $modal = $('#addPaymentModal');

        // Payment Modal Open Handler
        $modal.on('show.bs.modal', function(e) {
            let $btn = $(e.relatedTarget);
            if (!$btn.hasClass('btn')) {
                $btn = $btn.closest('[data-invoice-id]');
            }

            if (!$btn.length) {
                showToast('Could not identify invoice. Please try again.', 'danger');
                e.preventDefault();
                return false;
            }

            const invoiceId = $btn.data('invoice-id');
            if (!invoiceId || isNaN(invoiceId)) {
                showToast('Invalid invoice ID.', 'danger');
                e.preventDefault();
                return false;
            }

            const cpId = $btn.data('cp-id');
            const productName = $btn.data('product-name') || 'Product';

            // Get data from button attributes
            const invoiceNumber = $btn.data('invoice-number') || '–';
            const customerName = $btn.data('customer-name') || 'Unknown Customer';
            const customerEmail = $btn.data('customer-email') || 'N/A';
            const customerPhone = $btn.data('customer-phone') || 'N/A';
            const subtotal = parseFloat($btn.data('subtotal')) || 0;
            const previousDue = parseFloat($btn.data('previous-due')) || 0;
            const totalAmount = parseFloat($btn.data('total-amount')) || 0;
            const dueAmount = parseFloat($btn.data('due-amount')) || 0;
            const receivedAmount = parseFloat($btn.data('received-amount')) || 0;
            const status = $btn.data('status') || 'unpaid';

            // Set form action and invoice ID
            $('#payment_invoice_id').val(invoiceId);
            $('#payment_cp_id').val(cpId);
            const recordPaymentUrl = "{{ url('admin/billing/record-payment') }}/" + invoiceId;
            $('#addPaymentForm').attr('action', recordPaymentUrl);

            // Update UI with data from button attributes
            $('#payment_invoice_number_display').text(invoiceNumber);
            $('#payment_customer_name_display').text(customerName);
            $('#payment_customer_email_display').text(customerEmail);
            $('#payment_customer_phone_display').text(customerPhone);
            $('#payment_subtotal_display').text(`৳ ${(subtotal || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
            $('#payment_previous_due_display').text(`৳ ${(previousDue || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
            $('#payment_total_amount_display').text(`৳ ${(totalAmount || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
            $('#payment_due_amount_display').text(`৳ ${(dueAmount || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);

            // Update status badge
            const $statusBadge = $('#payment_status_display');
            $statusBadge.removeClass().addClass('badge');
            switch (status.toLowerCase()) {
                case 'paid':
                    $statusBadge.addClass('bg-success').text('Paid');
                    break;
                case 'partial':
                    $statusBadge.addClass('bg-warning text-dark').text('Partial');
                    break;
                case 'unpaid':
                    $statusBadge.addClass('bg-danger').text('Unpaid');
                    break;
                default:
                    $statusBadge.addClass('bg-secondary').text(status);
            }

            // Note: Existing payments are loaded by payment-modal.js automatically

            // Configure amount input
            const $amountInput = $('#payment_amount');
            $amountInput
                .val('')
                .attr({
                    'min': '0',
                    'max': dueAmount.toFixed(2),
                    'step': '0'
                })
                .prop('disabled', dueAmount <= 0)
                .removeClass('is-invalid');

            if (dueAmount <= 0) {
                $amountInput.attr('placeholder', 'Invoice already paid');
            } else {
                $amountInput.attr('placeholder', `Enter amount (0 to ${dueAmount.toFixed(2)})`);
            }

            $('#payment_amount_error').hide();
        });

        // Payment Amount Validation
        $('#payment_amount').on('input', function() {
            const paid = parseFloat(this.value) || 0;
            const dueText = $('#payment_due_amount_display').text();
            const due = parseFloat(dueText.replace(/[^\d.]/g, '')) || 0;

            // Get total amount and current received amount for proper calculation
            const totalText = $('#payment_total_amount_display').text();
            const totalAmount = parseFloat(totalText.replace(/[^\d.]/g, '')) || 0;

            // Calculate new received amount (existing + new payment)
            const currentReceivedText = $('#payment_received_amount_display').text() || '৳ 0';
            const currentReceived = parseFloat(currentReceivedText.replace(/[^\d.]/g, '')) || 0;
            const newTotalReceived = currentReceived + paid;

            // Calculate next due: Total - New Total Received
            const nextDue = Math.max(0, totalAmount - newTotalReceived);
            $('#next_due').val(nextDue.toFixed(2));

            // Validate amount - allow advance payments but warn if excessive
            if (paid > (due + 100)) { // Allow some advance but not excessive
                $(this).addClass('is-invalid');
                $('#payment_amount_error').text(`Amount seems too high. Due: ৳${due.toFixed(2)}`).show();
            } else if (paid < 0) {
                $(this).addClass('is-invalid');
                $('#payment_amount_error').text('Payment amount cannot be negative').show();
            } else {
                $(this).removeClass('is-invalid');
                $('#payment_amount_error').hide();
            }

            // Show advance payment warning if applicable
            if (newTotalReceived > totalAmount && totalAmount > 0) {
                const advance = newTotalReceived - totalAmount;
                $('#payment_amount_error').text(`Note: This will create ৳${advance.toFixed(2)} advance credit`).removeClass('text-danger').addClass('text-info').show();
            }
        });

        // Function to update invoice UI dynamically after payment
        function updateInvoiceUI(invoiceId, newDue, newReceived, newStatus) {
            // Find the row corresponding to this invoice
            const $row = $(`tr[data-invoice-id="${invoiceId}"]`);

            if ($row.length) {
                // Safely parse and validate input parameters
                const safeNewDue = parseFloat(newDue) || 0;
                const safeNewReceived = parseFloat(newReceived) || 0;

                // Get total amount from the table to calculate properly
                const totalAmountText = $row.find('.total-amount strong').text();
                const totalAmount = parseFloat(totalAmountText.replace(/[^\d.]/g, '')) || 0;
                const receivedAmount = safeNewReceived;

                // Calculate next due properly: Total - Received = Due
                const calculatedDue = Math.max(0, totalAmount - receivedAmount);

                // Use calculated value if server value seems incorrect
                const finalDue = (Math.abs(safeNewDue - calculatedDue) > 0) ? calculatedDue : safeNewDue;

                // Helper function to safely format numbers
                function safeFormatNumber(num) {
                    const safeNum = parseFloat(num) || 0;
                    return safeNum.toLocaleString('en-BD', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }

                // Update received amount display
                $row.find('.received-amount strong').text(`৳ ${safeFormatNumber(receivedAmount)}`);

                // Update received amount percentage if total > 0
                if (totalAmount > 0) {
                    const percentage = ((receivedAmount / totalAmount) * 100).toFixed(1);
                    $row.find('.received-amount small').text(`${percentage}% paid`);
                } else {
                    $row.find('.received-amount small').text(receivedAmount > 0 ? 'Payment received' : 'No payment');
                }

                // Update status badge (only if newStatus is provided)
                if (newStatus !== undefined && newStatus !== null) {
                    const $statusCell = $row.find('td').eq(8); // Status is 9th column (0-indexed as 8)
                    let statusClass = 'bg-secondary';
                    let statusText = 'Unknown';

                    // Defensive programming: check if newStatus has toLowerCase method
                    if (newStatus && typeof newStatus === 'string') {
                        switch (newStatus.toLowerCase()) {
                            case 'paid':
                                statusClass = 'bg-success';
                                statusText = 'Paid';
                                break;
                            case 'partial':
                                statusClass = 'bg-warning text-dark';
                                statusText = 'Partial';
                                break;
                            case 'unpaid':
                                statusClass = 'bg-danger';
                                statusText = 'Unpaid';
                                break;
                            case 'confirmed':
                                statusClass = 'bg-info';
                                statusText = 'Confirmed';
                                break;
                        }
                    }

                    $statusCell.html(`<span class="badge ${statusClass}">${statusText}</span>`);
                }

                // Update next due amount display with proper calculation
                const $nextDueElement = $row.find('.next-due');
                $nextDueElement.empty();

                // Check for advance payment
                const isAdvancePayment = receivedAmount > totalAmount && totalAmount > 0;
                const advanceAmount = isAdvancePayment ? (receivedAmount - totalAmount) : 0;

                if (isAdvancePayment) {
                    $nextDueElement.append('<span class="badge bg-success"><i class="fas fa-check-double me-1"></i>Advance Paid</span>');
                    $nextDueElement.append(`<br><small class="text-success">+৳ ${safeFormatNumber(advanceAmount)} credit</small>`);
                } else if (finalDue <= 0) {
                    $nextDueElement.append('<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Paid</span>');
                    $nextDueElement.append('<br><small class="text-muted">Fully paid</small>');
                } else {
                    $nextDueElement.append(`<strong class="text-danger">৳ ${safeFormatNumber(finalDue)}</strong>`);
                    $nextDueElement.append('<br><small class="text-muted">Outstanding</small>');
                    if (receivedAmount > 0) {
                        $nextDueElement.append(`<br><small class="text-info">Partial: ৳${safeFormatNumber(receivedAmount)} paid</small>`);
                    }
                }
            }
        }

        // Payment Form Submission
        $('#addPaymentForm').on('submit', function(e) {
            e.preventDefault();

            // Debug: Log form submission
            console.log('Debug: Payment form submitted');

            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const oldHtml = $btn.html();

            const paid = parseFloat($('#payment_amount').val()) || 0;
            const dueText = $('#payment_due_amount_display').text();
            const due = parseFloat(dueText.replace(/[^\d.]/g, '')) || 0;

            // Debug: Log payment data
            const formData = new FormData(this);
            const formDataObj = {};
            for (let [key, value] of formData.entries()) {
                formDataObj[key] = value;
            }
            console.log('Debug: Payment amount:', paid);
            console.log('Debug: Form data:', formDataObj);

            if (paid < 0) {
                showToast('Payment amount cannot be negative!', 'danger');
                return;
            }
            if (paid > (due + 0)) {
                showToast(`Cannot pay more than due amount (৳${due.toFixed(2)})!`, 'danger');
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
                    // Debug: Log server response
                    console.log('Debug: Server response:', json);

                    if (json.success) {
                        const invoiceNumber = $('#payment_invoice_number_display').text();
                        const customerName = $('#payment_customer_name_display').text();
                        const paidAmount = parseFloat($('#payment_amount').val()) || 0;
                        const invoiceId = $('#payment_invoice_id').val();

                        // Hide modal
                        $('#addPaymentModal').modal('hide');

                        // Debug: Log the data we're passing to updateInvoiceUI
                        console.log('Debug: Updating UI with:', {
                            invoiceId: invoiceId,
                            next_due: json.next_due,
                            received_amount: json.received_amount,
                            status: json.status
                        });

                        // Validate data before calling updateInvoiceUI
                        if (invoiceId && (json.next_due !== undefined || json.received_amount !== undefined)) {
                            try {
                                // Update UI dynamically without page refresh
                                updateInvoiceUI(invoiceId, json.next_due, json.received_amount, json.status);
                            } catch (uiError) {
                                console.error('Error updating UI:', uiError);
                                // Show a fallback message and suggest refresh
                                showToast('Payment saved successfully! Please refresh the page to see updated amounts.', 'warning');
                            }
                        } else {
                            console.warn('Invalid data for UI update, skipping UI update');
                            showToast('Payment saved successfully! Please refresh the page to see updated amounts.', 'warning');
                        }

                        // Show success notification
                        const details = `
                    <div><strong>Invoice:</strong> ${invoiceNumber}</div>
                    <div><strong>Customer:</strong> ${customerName}</div>
                    <div><strong>Amount Paid:</strong> ৳${(paidAmount || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                `;

                        showToast('Payment Recorded Successfully!', 'success', details);

                        // Refresh the page after a short delay to ensure toast is visible
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast(json.message || 'Error saving payment.', 'danger');
                    }
                })
                .catch(err => {
                    // Debug: Log detailed error
                    console.error('Debug: Payment error details:', err);
                    console.error('Debug: Full error object:', JSON.stringify(err, Object.getOwnPropertyNames(err)));
                    showToast('Network error. Try again.', 'danger');
                })
                .finally(() => {
                    $btn.html(oldHtml).prop('disabled', false);
                });
        });

        // Reset Modal on Close
        $modal.on('hidden.bs.modal', function() {
            $('#addPaymentForm')[0].reset();
            $('#addPaymentForm').attr('action', '');
            $('#payment_invoice_id').val('');
            $('#payment_cp_id').val('');
            $('#payment_invoice_number_display').text('-');
            $('#payment_customer_name_display').text('-');
            $('#payment_customer_email_display').text('-');
            $('#payment_customer_phone_display').text('-');
            $('#payment_subtotal_display').text('৳ 0.00');
            $('#payment_previous_due_display').text('৳ 0.00');
            $('#payment_total_amount_display').text('৳ 0.00');
            $('#payment_due_amount_display').text('৳ 0.00');
            $('#payment_status_display').removeClass().addClass('badge bg-secondary').text('-');
            $('#payment_amount').removeClass('is-invalid');
            $('#payment_amount_error').hide();
        });

        // Add CSS animations for toast
        if (!document.getElementById('toast-animations')) {
            const style = document.createElement('style');
            style.id = 'toast-animations';
            style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
            document.head.appendChild(style);
        }

        // Add event listeners for filters
        const statusFilterEl = document.getElementById('statusFilter');
        if (statusFilterEl) {
            statusFilterEl.addEventListener('change', filterTableByStatus);
        }

        const searchInputEl = document.getElementById('searchInput');
        if (searchInputEl) {
            searchInputEl.addEventListener('input', filterTableByStatus);
            searchInputEl.addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    filterTableByStatus();
                }
            });
        }


        // --- Robust enable/disable logic for Close Month button ---
        function setupCloseMonthCheckbox() {
            var confirmCheckbox = document.getElementById('confirmCloseMonth');
            var confirmBtn = document.getElementById('confirmCloseMonthBtn');
            if (!confirmCheckbox || !confirmBtn) return;
            // Attach event
            confirmCheckbox.addEventListener('change', function() {
                confirmBtn.disabled = !this.checked;
            });
            // Fallback: set initial state in case checkbox is already checked
            confirmBtn.disabled = !confirmCheckbox.checked;
            // Reset on modal close
            var closeMonthModal = document.getElementById('closeMonthModal');
            if (closeMonthModal) {
                closeMonthModal.addEventListener('hidden.bs.modal', function() {
                    confirmCheckbox.checked = false;
                    confirmBtn.disabled = true;
                });
            }
        }
        // Run immediately and also on DOMContentLoaded (for dynamic loads)
        setupCloseMonthCheckbox();
        document.addEventListener('DOMContentLoaded', setupCloseMonthCheckbox);





        // Handle view invoice button clicks
        document.querySelectorAll('.view-invoice-btn').forEach(button => {
            button.addEventListener('click', function() {
                const invoiceId = this.getAttribute('data-invoice-id');
                viewInvoice(invoiceId);
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

    });


    // Function to update UI after confirmation WITHOUT page refresh---2
    function updateUIAfterConfirmation(invoiceId, cpId, customerName, nextDue) {
        console.log('Updating UI for invoice:', invoiceId, 'with due:', nextDue);

        try {
            // Find the row corresponding to this invoice
            const $row = $(`tr[data-invoice-id="${invoiceId}"]`);

            if ($row.length) {
                // Update the status cell to show confirmed
                const statusCell = $row.find('td').eq(8); // Status is 9th column
                statusCell.html('<span class="badge bg-info"><i class="fas fa-check-double me-1"></i>Confirmed</span>');

                // Update next due cell
                const nextDueCell = $row.find('.next-due');
                if (nextDueCell.length) {
                    const safeNextDue = parseFloat(nextDue) || 0;
                    const formattedAmount = safeNextDue.toLocaleString('en-BD', {
                        minimumFractionDigits: 2
                    });
                    nextDueCell.html(`
                    <strong class="text-warning">৳ ${formattedAmount}</strong>
                    <br><small class="text-warning"><i class="fas fa-forward me-1"></i>Carried Forward</small>
                `);
                }

                // Update action buttons
                const actionCell = $row.find('td').eq(9); // Action is 10th column
                actionCell.html(`
                <div class="d-flex flex-column gap-1">
                    <button class="btn btn-outline-info btn-sm view-invoice-btn" 
                            data-invoice-id="${invoiceId}" 
                            data-bs-toggle="modal" 
                            data-bs-target="#viewInvoiceModal" 
                            title="View Invoice">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="btn btn-secondary btn-sm" disabled 
                            title="User payment confirmed - Due carried forward">
                        <i class="fas fa-check-circle"></i> Confirmed
                    </button>
                </div>
            `);

                // Add CSS class for confirmed row
                $row.addClass('table-info');

                // Re-attach event listener for view button
                $row.find('.view-invoice-btn').on('click', function() {
                    const invoiceId = $(this).data('invoice-id');
                    viewInvoice(invoiceId);
                });

                console.log('UI updated for invoice:', invoiceId);
            }
        } catch (error) {
            console.error('Error updating UI:', error);
        }
    }


    // Note: loadExistingPayments and payment edit/delete are now handled by the inline JavaScript in payment-modal.blade.php</script>
@endsection