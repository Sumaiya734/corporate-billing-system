@extends('layouts.admin')

@section('title', 'Generate Bill - ' . ($customer->name ?? 'Customer'))

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Generate Bill - {{ $customer->name ?? 'Customer' }}
            </h2>
            <p class="text-muted mb-0">Customer ID: {{ $customer->c_id }} | Phone: {{ $customer->phone ?? 'N/A' }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="printBtn">
                <i class="fas fa-print me-1"></i>Print
            </button>
            <button class="btn btn-outline-success" id="saveBtn">
                <i class="fas fa-save me-1"></i>Save
            </button>
            <button class="btn btn-outline-warning" id="confirmPaidBtn">
                <i class="fas fa-check-circle me-1"></i>Confirm Paid
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
                            <p class="text-muted mb-0">Customer ID: {{ $customer->customer_id ?? $customer->c_id }}</p>
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
                    @php
                        $activeproducts = DB::table('customer_to_products as cp')
                            ->join('products as p', 'cp.p_id', '=', 'p.p_id')
                            ->where('cp.c_id', $customer->c_id)
                            ->where('cp.status', 'active')
                            ->select('p.name', 'p.product_type', 'cp.custom_price', 'cp.billing_cycle_months')
                            ->get();
                        
                        $totalMonthly = $activeproducts->sum(function($product) {
                            // Use custom price if set, otherwise calculate from product's monthly price
                            if ($product->custom_price !== null) {
                                return $product->custom_price / max(1, $product->billing_cycle_months);
                            }
                            return 0; // This should ideally get the product's monthly price, but we don't have it in this query
                        });
                    @endphp
                    
                    @if($activeproducts->count() > 0)
                        @foreach($activeproducts as $product)
                            <span class="badge bg-{{ $product->product_type == 'regular' ? 'primary' : 'warning' }} me-2 mb-2">
                                {{ $product->name }} - ৳{{ number_format($product->custom_price ?? 0, 0) }}
                                <small>({{ $product->billing_cycle_months }} month cycle)</small>
                            </span>
                        @endforeach
                    @else
                        <p class="text-muted">No active products</p>
                    @endif
                    
                    <div class="mt-3">
                        <h6 class="mb-2">Billing Summary</h6>
                        @php
                            $customerInvoices = DB::table('invoices')
                                ->where('customer_id', $customer->c_id)
                                ->get();
                            
                            $totalInvoices = $customerInvoices->count();
                            $pendingAmount = $customerInvoices->where('status', 'unpaid')->sum('total_amount') - $customerInvoices->where('status', 'unpaid')->sum('received_amount');
                        @endphp
                        <p class="mb-1">Total Invoices: {{ $totalInvoices }}</p>
                        <p class="mb-0">Pending Amount: ৳{{ number_format($pendingAmount, 0) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Generation Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-calculator me-2"></i>Billing History & Invoice Generation for {{ $customer->name ?? 'Customer' }}
            </h5>
        </div>
        <div class="card-body">
            <!-- Bill Generation Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="billTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th width="120">Month</th>
                            <th>Services/products</th>
                            <th width="120" class="text-end">Bill Amount</th>
                            <th width="120" class="text-end">Previous Due</th>
                            <th width="120" class="text-end">Total</th>
                            <th width="140" class="text-end">Received Amount</th>
                            <th width="120" class="text-end">Next Due</th>
                            <th width="100" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Get customer's invoices with product information
                            $customerInvoices = DB::table('invoices as i')
                                ->leftJoin('customer_to_products as cp', function($join) {
                                    $join->on('i.customer_id', '=', 'cp.c_id')
                                         ->on(DB::raw("DATE_FORMAT(i.issue_date, '%Y-%m')"), '=', DB::raw("DATE_FORMAT(cp.assign_date, '%Y-%m')"));
                                })
                                ->leftJoin('products as p', 'cp.p_id', '=', 'p.p_id')
                                ->where('i.customer_id', $customer->c_id)
                                ->select(
                                    'i.*',
                                    'p.name as product_name',
                                    'p.product_type',
                                    'cp.custom_price',
                                    DB::raw("GROUP_CONCAT(p.name SEPARATOR ', ') as all_products")
                                )
                                ->groupBy('i.id')
                                ->orderBy('i.issue_date', 'desc')
                                ->get();

                            // Get due months based on billing cycles for future billing
                            $dueMonths = DB::table('customer_to_products as cp')
                                ->select(
                                    DB::raw("DATE_FORMAT(
                                        DATE_ADD(
                                            cp.assign_date, 
                                            INTERVAL (FLOOR(DATEDIFF(DATE_FORMAT(NOW(), '%Y-%m-01'), cp.assign_date) / 30 / cp.billing_cycle_months) * cp.billing_cycle_months) MONTH
                                        ), 
                                        '%Y-%m'
                                    ) as due_month"),
                                    DB::raw('SUM(cp.custom_price) as total_amount'),
                                    DB::raw("GROUP_CONCAT(p.name SEPARATOR ', ') as products")
                                )
                                ->join('products as p', 'cp.p_id', '=', 'p.p_id')
                                ->where('cp.c_id', $customer->c_id)
                                ->where('cp.status', 'active')
                                ->whereRaw('cp.assign_date <= NOW()')
                                ->groupBy('due_month')
                                ->orderBy('due_month', 'desc')
                                ->get();

                            $allBillingData = collect();
                            
                            // Combine existing invoices and future due months
                            foreach ($dueMonths as $dueMonth) {
                                $existingInvoice = $customerInvoices->first(function($invoice) use ($dueMonth) {
                                    return date('Y-m', strtotime($invoice->issue_date)) === $dueMonth->due_month;
                                });
                                
                                if ($existingInvoice) {
                                    $allBillingData->push($existingInvoice);
                                } else {
                                    // For pending invoices, just show the product amount without extra charges
                                    $allBillingData->push((object)[
                                        'id' => null,
                                        'invoice_number' => 'PENDING-' . $dueMonth->due_month,
                                        'issue_date' => $dueMonth->due_month . '-01',
                                        'due_date' => date('Y-m-d', strtotime($dueMonth->due_month . '-01 +10 days')),
                                        'billing_month' => date('F Y', strtotime($dueMonth->due_month . '-01')),
                                        'subtotal' => $dueMonth->total_amount,
                                        'service_charge' => 0,
                                        'vat_amount' => 0,
                                        'total_amount' => $dueMonth->total_amount,
                                        'received_amount' => 0,
                                        'next_due' => $dueMonth->total_amount,
                                        'status' => 'pending',
                                        'product_name' => $dueMonth->products,
                                        'all_products' => $dueMonth->products
                                    ]);
                                }
                            }

                            // Add any existing invoices that might not be in due months (for completeness)
                            foreach ($customerInvoices as $invoice) {
                                $monthKey = date('Y-m', strtotime($invoice->issue_date));
                                if (!$allBillingData->contains(function($item) use ($monthKey) {
                                    return date('Y-m', strtotime($item->issue_date)) === $monthKey;
                                })) {
                                    $allBillingData->push($invoice);
                                }
                            }

                            $allBillingData = $allBillingData->sortByDesc('issue_date');
                        @endphp

                        @foreach($allBillingData as $invoice)
                        @php
                            $isPending = $invoice->id === null;
                            $isOverdue = !$isPending && $invoice->status === 'unpaid' && strtotime($invoice->due_date) < time();
                            
                            // Use real invoice data
                            $totalAmount = $invoice->total_amount ?? 0;
                            $receivedAmount = $invoice->received_amount ?? 0;
                            $nextDue = $invoice->next_due ?? ($totalAmount - $receivedAmount);
                            $previousDue = $invoice->previous_due ?? 0;
                            
                            // Calculate product amount (current charges only)
                            $productAmount = $totalAmount - $previousDue;
                            
                            // Determine status
                            $isPaid = !$isPending && ($nextDue < 0);
                            $isPartial = !$isPending && !$isPaid && $receivedAmount > 0;
                            
                            $statusClass = 'badge-pending';
                            $statusText = 'Pending';
                            
                            if ($isPaid) {
                                $statusClass = 'badge-paid';
                                $statusText = 'Paid';
                            } elseif ($isOverdue) {
                                $statusClass = 'badge-overdue';
                                $statusText = 'Overdue';
                            } elseif ($isPartial) {
                                $statusClass = 'badge-pending';
                                $statusText = 'Partial';
                            }
                        @endphp
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="row-checkbox" 
                                       data-amount="{{ $totalAmount }}" 
                                       data-month="{{ date('Y-m', strtotime($invoice->issue_date)) }}" 
                                       data-invoice="{{ $invoice->invoice_number }}"
                                       {{ $isPaid ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <div class="month-info">
                                    <strong class="d-block">{{ date('F Y', strtotime($invoice->issue_date)) }}</strong>
                                    <small class="text-muted d-block {{ $isOverdue ? 'text-danger' : '' }}">
                                        Due: {{ date('d M', strtotime($invoice->due_date)) }}
                                    </small>
                                    <small class="text-muted">{{ $invoice->invoice_number }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="service-info">
                                    <strong>{{ $invoice->all_products ?? $invoice->product_name ?? 'product' }}</strong>
                                    <small class="text-muted d-block">
                                        @if($invoice->product_type)
                                            {{ ucfirst($invoice->product_type) }} product
                                        @else
                                            Multiple products
                                        @endif
                                    </small>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳{{ number_format($productAmount, 0) }}</span>
                                <br><small class="text-muted">Current month</small>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳{{ number_format($previousDue, 0) }}</span>
                                @if($previousDue > 0)
                                <br><small class="text-muted">From past</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳{{ number_format($totalAmount, 0) }}</strong>
                                <br><small class="text-muted">Total invoice</small>
                            </td>
                            <td class="text-end">
                                @if($isPending)
                                    <span class="text-muted">Not Generated</span>
                                @else
                                    <input type="number" class="form-control form-control-sm received-amount" 
                                           value="{{ number_format($receivedAmount, 0) }}" 
                                           min="0" max="{{ $totalAmount }}" 
                                           step="0"
                                           data-invoice-id="{{ $invoice->id }}"
                                           readonly>
                                    <small class="text-muted">Payments made</small>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($isPaid)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Paid
                                    </span>
                                @else
                                    <span class="next-due 
                                        @if($nextDue <= 0) text-success
                                        @elseif($nextDue > 0 && $nextDue <= ($totalAmount * 0.5)) text-warning
                                        @else text-danger
                                        @endif">
                                        ৳{{ number_format($nextDue, 0) }}
                                    </span>
                                @endif
                                <br><small class="text-muted">Outstanding</small>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                        </tr>
                        @endforeach

                        @if($allBillingData->count() === 0)
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-file-invoice-dollar fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No billing data found for this customer.</p>
                                <small class="text-muted">Assign products to generate bills.</small>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        @php
                            $totalBillAmount = $allBillingData->sum('total_amount');
                            $totalReceived = $allBillingData->sum('received_amount');
                            $totalNextDue = $totalBillAmount - $totalReceived;
                        @endphp
                        <tr>
                            <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                            <td class="text-end"><strong id="totalBillAmount">৳{{ number_format($totalBillAmount, 0) }}</strong></td>
                            <td class="text-end"><strong id="totalPreviousDue">৳0.00</strong></td>
                            <td class="text-end"><strong id="totalAmount">৳{{ number_format($totalBillAmount, 0) }}</strong></td>
                            <td class="text-end"><strong id="totalReceived">৳{{ number_format($totalReceived, 0) }}</strong></td>
                            <td class="text-end"><strong id="totalNextDue" class="{{ $totalNextDue > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($totalNextDue, 0) }}</strong></td>
                            <td class="text-center"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- New Invoice Generation Form -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2 text-success"></i>Generate New Invoice
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.billing.process-bill-generation', $customer->c_id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Billing Month *</label>
                                    <input type="month" name="billing_month" class="form-control" required 
                                           value="{{ date('Y-m') }}"
                                           min="{{ date('Y-m', strtotime('-1 year')) }}" 
                                           max="{{ date('Y-m', strtotime('+1 year')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Due Date *</label>
                                    <input type="date" name="due_date" class="form-control" required 
                                           value="{{ date('Y-m-d', strtotime('+10 days')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="1" placeholder="Optional notes for this invoice"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_notification" id="sendNotification" checked>
                                <label class="form-check-label" for="sendNotification">
                                    Send notification to customer
                                </label>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <i class="fas fa-arrow-left me-1"></i>Back
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-file-invoice me-1"></i>Generate New Invoice
                                </button>
                            </div>
                        </div>
                    </form>
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

    .month-info {
        line-height: 1.3;
        font-size: 0.875rem;
    }

    .month-info strong {
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .month-info small {
        font-size: 0.75rem;
    }

    .service-info {
        line-height: 1.2;
    }

    .service-info strong {
        font-size: 0.9rem;
    }

    .service-info small {
        font-size: 0.75rem;
    }

    .received-amount {
        text-align: right;
        width: 100px;
        display: inline-block;
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

    .row-checkbox:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Status colors for next due */
    .text-success { color: #06d6a0 !important; }
    .text-warning { color: #ffd166 !important; }
    .text-danger { color: #ef476f !important; }

    /* Selected row styling */
    .table tbody tr.selected {
        background-color: rgba(67, 97, 238, 0.05);
    }

    .table tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.02);
    }
</style>
@endsection

@section('scripts')
<script>
    // Initialize variables
    let selectedRows = new Set();

    // Select All Checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox:not(:disabled)');
        const allRows = document.querySelectorAll('tbody tr');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedRows.add(checkbox);
                checkbox.closest('tr').classList.add('selected');
            } else {
                selectedRows.delete(checkbox);
                checkbox.closest('tr').classList.remove('selected');
            }
        });
        
        updateFooterTotals();
        updateActionButtons();
    });

    // Individual Row Checkbox
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.disabled) return;
            
            const row = this.closest('tr');
            
            if (this.checked) {
                selectedRows.add(this);
                row.classList.add('selected');
            } else {
                selectedRows.delete(this);
                row.classList.remove('selected');
            }
            
            updateSelectAllCheckbox();
            updateFooterTotals();
            updateActionButtons();
        });
    });

    // Update "Select All" checkbox based on individual checkboxes
    function updateSelectAllCheckbox() {
        const checkboxes = document.querySelectorAll('.row-checkbox:not(:disabled)');
        const selectAll = document.getElementById('selectAll');
        
        if (checkboxes.length === 0) {
            selectAll.checked = false;
            return;
        }
        
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        const someChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        
        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
    }

    // Update action buttons state based on selection
    function updateActionButtons() {
        const hasSelection = selectedRows.size > 0;
        const buttons = ['printBtn', 'saveBtn', 'confirmPaidBtn'];
        
        buttons.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.disabled = !hasSelection;
            }
        });
    }

    // Received Amount Input Handler
    document.querySelectorAll('.received-amount').forEach(input => {
        input.addEventListener('input', function() {
            updateRowCalculations(this);
            updateFooterTotals();
            updateRowStatus(this);
        });
        
        // Initialize row status on page load
        updateRowStatus(input);
    });

    // Update row calculations when received amount changes
    function updateRowCalculations(input) {
        const row = input.closest('tr');
        const billAmount = parseFloat(row.querySelector('.bill-amount').textContent.replace('৳', '').replace(',', ''));
        const previousDue = parseFloat(row.querySelector('.previous-due').textContent.replace('৳', '').replace(',', ''));
        const receivedAmount = parseFloat(input.value) || 0;
        
        const total = billAmount + previousDue;
        const nextDue = total - receivedAmount;
        
        row.querySelector('.total-amount').textContent = `৳${total.toFixed(0)}`;
        row.querySelector('.next-due').textContent = `৳${nextDue.toFixed(0)}`;
        
        // Update text color based on next due
        const nextDueElement = row.querySelector('.next-due');
        nextDueElement.className = 'next-due';
        
        if (nextDue === 0) {
            nextDueElement.classList.add('text-success');
        } else if (nextDue > 0 && nextDue <= total * 0.5) {
            nextDueElement.classList.add('text-warning');
        } else {
            nextDueElement.classList.add('text-danger');
        }
    }

    // Update row status based on payment
    function updateRowStatus(input) {
        const row = input.closest('tr');
        const billAmount = parseFloat(row.querySelector('.bill-amount').textContent.replace('৳', '').replace(',', ''));
        const previousDue = parseFloat(row.querySelector('.previous-due').textContent.replace('৳', '').replace(',', ''));
        const receivedAmount = parseFloat(input.value) || 0;
        const total = billAmount + previousDue;
        
        const statusBadge = row.querySelector('.badge');
        statusBadge.classList.remove('badge-paid', 'badge-pending', 'badge-overdue');
        
        if (receivedAmount >= total) {
            statusBadge.textContent = 'Paid';
            statusBadge.classList.add('badge-paid');
        } else if (receivedAmount > 0) {
            statusBadge.textContent = 'Partial';
            statusBadge.classList.add('badge-pending');
        } else {
            // Check if due date has passed
            const dueText = row.querySelector('.month-info .text-danger');
            if (dueText) {
                statusBadge.textContent = 'Overdue';
                statusBadge.classList.add('badge-overdue');
            } else {
                statusBadge.textContent = 'Pending';
                statusBadge.classList.add('badge-pending');
            }
        }
    }

    // Update footer totals
    function updateFooterTotals() {
        let totalBillAmount = 0;
        let totalPreviousDue = 0;
        let totalAmount = 0;
        let totalReceived = 0;
        let totalNextDue = 0;

        document.querySelectorAll('tbody tr').forEach(row => {
            totalBillAmount += parseFloat(row.querySelector('.bill-amount').textContent.replace('৳', '').replace(',', ''));
            totalPreviousDue += parseFloat(row.querySelector('.previous-due').textContent.replace('৳', '').replace(',', ''));
            totalAmount += parseFloat(row.querySelector('.total-amount').textContent.replace('৳', '').replace(',', ''));
            
            const receivedInput = row.querySelector('.received-amount');
            if (receivedInput) {
                totalReceived += parseFloat(receivedInput.value) || 0;
            }
            
            totalNextDue += parseFloat(row.querySelector('.next-due').textContent.replace('৳', '').replace(',', ''));
        });

        document.getElementById('totalBillAmount').textContent = `৳${totalBillAmount.toFixed(0)}`;
        document.getElementById('totalPreviousDue').textContent = `৳${totalPreviousDue.toFixed(0)}`;
        document.getElementById('totalAmount').textContent = `৳${totalAmount.toFixed(0)}`;
        document.getElementById('totalReceived').textContent = `৳${totalReceived.toFixed(0)}`;
        
        const totalNextDueElement = document.getElementById('totalNextDue');
        totalNextDueElement.textContent = `৳${totalNextDue.toFixed(0)}`;
        totalNextDueElement.className = 'text-end';
        totalNextDueElement.classList.add(totalNextDue > 0 ? 'text-danger' : 'text-success');
    }

    // Action Buttons
    document.getElementById('printBtn').addEventListener('click', function() {
        if (selectedRows.size === 0) {
            alert('Please select at least one bill to print.');
            return;
        }
        const selectedMonths = Array.from(selectedRows).map(checkbox => checkbox.getAttribute('data-month'));
        const selectedInvoices = Array.from(selectedRows).map(checkbox => checkbox.getAttribute('data-invoice'));
        
        alert(`Printing ${selectedRows.size} selected bills:\nMonths: ${selectedMonths.join(', ')}\nInvoices: ${selectedInvoices.join(', ')}`);
    });

    document.getElementById('saveBtn').addEventListener('click', function() {
        if (selectedRows.size === 0) {
            alert('Please select at least one bill to save.');
            return;
        }
        
        const selectedInvoices = Array.from(selectedRows).map(checkbox => checkbox.getAttribute('data-invoice'));
        alert(`Saving ${selectedRows.size} selected bills as draft:\n${selectedInvoices.join(', ')}`);
    });

    document.getElementById('confirmPaidBtn').addEventListener('click', function() {
        if (selectedRows.size === 0) {
            alert('Please select at least one bill to confirm payment.');
            return;
        }
        
        const totalAmount = document.getElementById('totalAmount').textContent;
        const selectedInvoices = Array.from(selectedRows).map(checkbox => checkbox.getAttribute('data-invoice'));
        
        if (confirm(`Confirm payment of ${totalAmount} for ${selectedRows.size} selected bills?

Invoices:
${selectedInvoices.join('
')}`)) {
            alert('Payment confirmed successfully for selected bills!');
            // Here you would typically make an AJAX call to update the database
        }
    });

    // Initialize calculations on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all calculations
        document.querySelectorAll('.received-amount').forEach(input => {
            updateRowCalculations(input);
        });
        updateFooterTotals();
        
        // Initialize action buttons
        updateActionButtons();
        
        console.log('Generate Bill page loaded for Customer ID: {{ $customer->c_id }}');
        console.log('Customer Name: {{ $customer->name ?? "N/A" }}');
    });
</script>
@endsection