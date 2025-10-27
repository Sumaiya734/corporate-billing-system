@extends('layouts.admin')

@section('title', 'Generate Bill - ' . ($customer->user->name ?? 'Customer'))

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Generate Bill - {{ $customer->user->name ?? 'Customer' }}
            </h2>
            <p class="text-muted mb-0">Customer ID: {{ $customer->id }} | Showing all monthly bills</p>
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
                    <h6 class="mb-3">Current Subscriptions</h6>
                    @if($customer->subscriptions && $customer->subscriptions->count() > 0)
                        @foreach($customer->subscriptions as $subscription)
                            @if(isset($subscription->package))
                            <span class="badge bg-primary me-2 mb-2">
                                {{ $subscription->package->name }} - ৳{{ $subscription->monthly_price }}
                            </span>
                            @endif
                        @endforeach
                    @else
                        <p class="text-muted">No active subscriptions</p>
                    @endif
                    
                    <div class="mt-3">
                        <h6 class="mb-2">Billing Summary</h6>
                        <p class="mb-1">Total Invoices: {{ $customer->invoices ? $customer->invoices->count() : 0 }}</p>
                        <p class="mb-0">Pending Amount: ৳{{ $customer->invoices ? $customer->invoices->where('status', 'pending')->sum('total_amount') : 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Generation Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-calculator me-2"></i>Monthly Bills for {{ $customer->user->name ?? 'Customer' }}
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
                        <!-- Row 1: January 2024 -->
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="row-checkbox" data-amount="800" data-month="2024-01" data-invoice="INV-{{ $customer->id }}-2024-001">
                            </td>
                            <td>
                                <div class="month-info">
                                    <strong class="d-block">January 2024</strong>
                                    <small class="text-muted d-block">Due: 05 Jan</small>
                                    <small class="text-muted">INV-{{ $customer->id }}-2024-001</small>
                                </div>
                            </td>
                            <td>
                                <div class="service-info">
                                    <strong>Fast Speed Internet</strong>
                                    <small class="text-muted d-block">Regular Package - 20 Mbps</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳800.00</span>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳800.00</strong>
                            </td>
                            <td class="text-end">
                                <input type="number" class="form-control form-control-sm received-amount" value="800.00" min="0" step="0.01">
                            </td>
                            <td class="text-end">
                                <span class="next-due text-success">৳0.00</span>
                            </td>
                            <td>
    <span class="badge" style="background-color: #06d6a0; color: white; padding: 6px 12px; border-radius: 20px;">Paid</span>
</td>
                                
                                <td></td>
                        </tr>
                        
                        <!-- Row 2: February 2024 -->
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="row-checkbox" data-amount="1000" data-month="2024-02" data-invoice="INV-{{ $customer->id }}-2024-002">
                            </td>
                            <td>
                                <div class="month-info">
                                    <strong class="d-block">February 2024</strong>
                                    <small class="text-muted d-block">Due: 05 Feb</small>
                                    <small class="text-muted">INV-{{ $customer->id }}-2024-002</small>
                                </div>
                            </td>
                            <td>
                                <div class="service-info">
                                    <strong>Fast Speed + Gaming Boost</strong>
                                    <small class="text-muted d-block">Regular + Special Package</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,000.00</span>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳0.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,000.00</strong>
                            </td>
                            <td class="text-end">
                                <input type="number" class="form-control form-control-sm received-amount" value="500.00" min="0" step="0.01">
                            </td>
                            <td class="text-end">
                                <span class="next-due text-warning">৳500.00</span>
                            </td>
                             <td>
    <span class="badge" style="background-color: #ffd166; color: black; padding: 6px 12px; border-radius: 20px;">Pending</span>
</td>
                               
                               
                        </tr>
                        
                        <!-- Row 3: March 2024 -->
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="row-checkbox" data-amount="1150" data-month="2024-03" data-invoice="INV-{{ $customer->id }}-2024-003">
                            </td>
                            <td>
                                <div class="month-info">
                                    <strong class="d-block">March 2024</strong>
                                    <small class="text-muted d-block text-danger">Due: 05 Mar</small>
                                    <small class="text-muted">INV-{{ $customer->id }}-2024-003</small>
                                </div>
                            </td>
                            <td>
                                <div class="service-info">
                                    <strong>Fast Speed + Gaming + Streaming</strong>
                                    <small class="text-muted d-block">Regular + Special Packages</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,150.00</span>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳500.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳1,650.00</strong>
                            </td>
                            <td class="text-end">
                                <input type="number" class="form-control form-control-sm received-amount" value="0.00" min="0" step="0.01">
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳1,650.00</span>
                            </td>
                             <td>
    <span class="badge" style="background-color: #ef476f; color: white; padding: 6px 12px; border-radius: 20px;">Overdue</span>
</td>
                               
                               
                        </tr>
                        
                        <!-- Row 4: April 2024 -->
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="row-checkbox" data-amount="1150" data-month="2024-04" data-invoice="INV-{{ $customer->id }}-2024-004">
                            </td>
                            <td>
                                <div class="month-info">
                                    <strong class="d-block">April 2024</strong>
                                    <small class="text-muted d-block">Due: 05 Apr</small>
                                    <small class="text-muted">INV-{{ $customer->id }}-2024-004</small>
                                </div>
                            </td>
                            <td>
                                <div class="service-info">
                                    <strong>Fast Speed + Gaming + Streaming</strong>
                                    <small class="text-muted d-block">Regular + Special Packages</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,150.00</span>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳1,650.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳2,800.00</strong>
                            </td>
                            <td class="text-end">
                                <input type="number" class="form-control form-control-sm received-amount" value="0.00" min="0" step="0.01">
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳2,800.00</span>
                            </td>
                             <td>
    <span class="badge" style="background-color: #ffd166; color: black; padding: 6px 12px; border-radius: 20px;">Pending</span>
</td>
                               
                                <td></td>
                        </tr>
                        
                        <!-- Row 5: May 2024 -->
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="row-checkbox" data-amount="1150" data-month="2024-05" data-invoice="INV-{{ $customer->id }}-2024-005">
                            </td>
                            <td>
                                <div class="month-info">
                                    <strong class="d-block">May 2024</strong>
                                    <small class="text-muted d-block">Due: 05 May</small>
                                    <small class="text-muted">INV-{{ $customer->id }}-2024-005</small>
                                </div>
                            </td>
                            <td>
                                <div class="service-info">
                                    <strong>Fast Speed + Gaming + Streaming</strong>
                                    <small class="text-muted d-block">Regular + Special Packages</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="bill-amount">৳1,150.00</span>
                            </td>
                            <td class="text-end">
                                <span class="previous-due">৳2,800.00</span>
                            </td>
                            <td class="text-end">
                                <strong class="total-amount">৳3,950.00</strong>
                            </td>
                            <td class="text-end">
                                <input type="number" class="form-control form-control-sm received-amount" value="0.00" min="0" step="0.01">
                            </td>
                            <td class="text-end">
                                <span class="next-due text-danger">৳3,950.00</span>
                            </td>
                           <td>
    <span class="badge" style="background-color: #ffd166; color: black; padding: 6px 12px; border-radius: 20px;">Pending</span>
</td>
                               
                                
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                            <td class="text-end"><strong id="totalBillAmount">৳5,250.00</strong></td>
                            <td class="text-end"><strong id="totalPreviousDue">৳4,950.00</strong></td>
                            <td class="text-end"><strong id="totalAmount">৳10,200.00</strong></td>
                            <td class="text-end"><strong id="totalReceived">৳1,300.00</strong></td>
                            <td class="text-end"><strong id="totalNextDue" class="text-danger">৳8,900.00</strong></td>
                            <td class="text-center"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="sendNotification">
                    <label class="form-check-label" for="sendNotification">
                        Send notification to customer
                    </label>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.billing.all-invoices') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Invoices
                    </a>
                    <button type="button" class="btn btn-primary" onclick="generateFinalBill()">
                        <i class="fas fa-file-invoice me-1"></i>Generate Selected Bills
                    </button>
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

    .total-summary {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .row-checkbox {
        transform: scale(0.8);
        cursor: pointer;
    }

    #selectAll {
        transform: scale(0.8);
        cursor: pointer;
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

    // Select All Checkbox - COMPLETELY FIXED
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const allRows = document.querySelectorAll('tbody tr');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedRows.add(checkbox);
                // Add selected class to row
                checkbox.closest('tr').classList.add('selected');
            } else {
                selectedRows.delete(checkbox);
                // Remove selected class from row
                checkbox.closest('tr').classList.remove('selected');
            }
        });
        
        updateFooterTotals();
        updateActionButtons();
    });

    // Individual Row Checkbox - COMPLETELY FIXED
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            
            if (this.checked) {
                selectedRows.add(this);
                row.classList.add('selected');
            } else {
                selectedRows.delete(this);
                row.classList.remove('selected');
            }
            
            // Update "Select All" checkbox state
            updateSelectAllCheckbox();
            updateFooterTotals();
            updateActionButtons();
        });
    });

    // Update "Select All" checkbox based on individual checkboxes
    function updateSelectAllCheckbox() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
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
        
        row.querySelector('.total-amount').textContent = `৳${total.toFixed(2)}`;
        row.querySelector('.next-due').textContent = `৳${nextDue.toFixed(2)}`;
        
        // Update text color based on next due
        const nextDueElement = row.querySelector('.next-due');
        nextDueElement.classList.remove('text-success', 'text-warning', 'text-danger');
        
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
            statusBadge.textContent = 'Pending';
            statusBadge.classList.add('badge-pending');
        } else {
            // Check if due date has passed (simplified logic)
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
            totalReceived += parseFloat(row.querySelector('.received-amount').value) || 0;
            totalNextDue += parseFloat(row.querySelector('.next-due').textContent.replace('৳', '').replace(',', ''));
        });

        document.getElementById('totalBillAmount').textContent = `৳${totalBillAmount.toFixed(2)}`;
        document.getElementById('totalPreviousDue').textContent = `৳${totalPreviousDue.toFixed(2)}`;
        document.getElementById('totalAmount').textContent = `৳${totalAmount.toFixed(2)}`;
        document.getElementById('totalReceived').textContent = `৳${totalReceived.toFixed(2)}`;
        document.getElementById('totalNextDue').textContent = `৳${totalNextDue.toFixed(2)}`;
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
        // Add actual print functionality here
    });

    document.getElementById('saveBtn').addEventListener('click', function() {
        if (selectedRows.size === 0) {
            alert('Please select at least one bill to save.');
            return;
        }
        
        const selectedInvoices = Array.from(selectedRows).map(checkbox => checkbox.getAttribute('data-invoice'));
        alert(`Saving ${selectedRows.size} selected bills as draft:\n${selectedInvoices.join(', ')}`);
        // Add save functionality here
    });

    document.getElementById('confirmPaidBtn').addEventListener('click', function() {
        if (selectedRows.size === 0) {
            alert('Please select at least one bill to confirm payment.');
            return;
        }
        
        const totalAmount = document.getElementById('totalAmount').textContent;
        const selectedInvoices = Array.from(selectedRows).map(checkbox => checkbox.getAttribute('data-invoice'));
        
        if (confirm(`Confirm payment of ${totalAmount} for ${selectedRows.size} selected bills?\n\nInvoices:\n${selectedInvoices.join('\n')}`)) {
            alert('Payment confirmed successfully for selected bills!');
            // Add payment confirmation logic here
        }
    });

    // Generate Final Bill
    function generateFinalBill() {
        if (selectedRows.size === 0) {
            alert('Please select at least one bill to generate.');
            return;
        }

        const selectedMonths = Array.from(selectedRows).map(checkbox => checkbox.getAttribute('data-month'));
        const selectedInvoices = Array.from(selectedRows).map(checkbox => checkbox.getAttribute('data-invoice'));
        const sendNotification = document.getElementById('sendNotification').checked;
        
        alert(`Generating bills for customer {{ $customer->user->name ?? 'Customer' }}:\n\nMonths: ${selectedMonths.join(', ')}\nInvoices: ${selectedInvoices.join(', ')}\n\n${sendNotification ? 'Customer notification will be sent.' : 'No notification will be sent.'}`);
        
        // Simulate processing
        setTimeout(() => {
            window.location.href = '{{ route("admin.billing.monthly-bills") }}';
        }, 1500);
    }

    // Initialize calculations on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all calculations
        document.querySelectorAll('.received-amount').forEach(input => {
            updateRowCalculations(input);
        });
        updateFooterTotals();
        
        // Initialize action buttons
        updateActionButtons();
        
        // Auto-select all rows on page load for convenience
        document.getElementById('selectAll').checked = true;
        document.getElementById('selectAll').dispatchEvent(new Event('change'));
        
        console.log('Generate Bill page loaded for Customer ID: {{ $customer->id }}');
        console.log('Customer Name: {{ $customer->user->name ?? "N/A" }}');
    });
</script>
@endsection