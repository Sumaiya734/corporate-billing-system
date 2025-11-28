<!-- resources/views/admin/billing/payment-modal.blade.php -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient-primary text-white rounded-top">
                <div class="d-flex align-items-center">
                    <div class="payment-icon-container me-3">
                        <i class="fas fa-credit-card fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="paymentModalTitle">Process Payment</h5>
                        <small class="opacity-75">Record payment for outstanding invoice</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="addPaymentForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="payment_method_override" value="POST">
                <input type="hidden" name="invoice_id" id="payment_invoice_id">
                <input type="hidden" name="cp_id" id="payment_cp_id">
                <input type="hidden" name="payment_id" id="payment_id">

                <div class="modal-body p-0">
                    <!-- Compact Invoice Summary -->
                    <div class="p-3 border-bottom bg-light">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-invoice text-primary me-2"></i>
                                    <div>
                                        <div class="fw-bold text-dark" id="payment_invoice_number_display">-</div>
                                        <small class="text-muted" id="payment_customer_name_display">-</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-muted small">Total Amount</div>
                                    <div class="fw-bold text-success" id="payment_total_amount_display">৳ 0.00</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-muted small">Due Amount</div>
                                    <div class="fw-bold text-danger" id="payment_due_amount_display">৳ 0.00</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <span class="badge bg-secondary" id="payment_status_display">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <div class="px-3 pt-3">
                        <ul class="nav nav-pills nav-fill" id="paymentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="payment-form-tab" data-bs-toggle="pill" 
                                        data-bs-target="#payment-form" type="button" role="tab" 
                                        aria-controls="payment-form" aria-selected="true">
                                    <i class="fas fa-money-bill-wave me-2"></i>Record Payment
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="existing-payments-tab" data-bs-toggle="pill" 
                                        data-bs-target="#existing-payments" type="button" role="tab" 
                                        aria-controls="existing-payments" aria-selected="false">
                                    <i class="fas fa-history me-2"></i>Payment History
                                    <span class="badge bg-secondary ms-1" id="payment-count-badge">0</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content p-3" id="paymentTabsContent">
                        <!-- Payment Form Tab -->
                        <div class="tab-pane fade show active" id="payment-form" role="tabpanel" aria-labelledby="payment-form-tab">
                            <div class="row g-3">
                                <!-- Payment Amount -->
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">৳</span>
                                            <input type="number" step="0.01" name="amount" class="form-control border-start-0" required 
                                                   id="payment_amount" min="0.01" placeholder="0.00">
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <small class="text-muted">Due: <span id="payment_due_amount_display" class="fw-bold text-danger">৳ 0.00</span></small>
                                            <small id="payment_amount_helper" class="text-muted"></small>
                                        </div>
                                        <div class="invalid-feedback" id="payment_amount_error" style="display:none;">
                                            Payment amount cannot exceed the due amount
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Next Due -->
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Remaining Balance</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">৳</span>
                                            <input type="number" step="0.01" name="next_due" class="form-control border-start-0" 
                                                   id="next_due" min="0" placeholder="0.00" readonly>
                                        </div>
                                        <div class="form-text text-muted">Amount remaining after this payment</div>
                                    </div>
                                </div>
                                
                                <!-- Payment Method -->
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" class="form-select" required>
                                            <option value="">Select Payment Method</option>
                                            <option value="cash" selected>Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="mobile_banking">Mobile Banking</option>
                                            <option value="card">Credit/Debit Card</option>
                                            <option value="online">Online Payment</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Payment Date -->
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Payment Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Add any additional notes about this payment..."></textarea>
                            </div>
                        </div>

                        <!-- Existing Payments Tab -->
                        <div class="tab-pane fade" id="existing-payments" role="tabpanel" aria-labelledby="existing-payments-tab">
                            <div class="alert alert-warning py-2 px-3 mb-3 d-flex align-items-center" style="font-size: 0.85rem;">
                                <i class="fas fa-info-circle me-2"></i>
                                <span><strong>Need to correct a payment?</strong> Use the action buttons to edit or delete</span>
                            </div>
                            <div id="existingPaymentsList" class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Notes</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                                                <p class="mb-0 small">No payment history found</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light rounded-bottom">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success px-3" id="paymentSubmitBtn">
                        <i class="fas fa-check-circle me-1"></i><span id="paymentSubmitText">Record Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .payment-icon-container {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        border: none;
        font-weight: 600;
    }
    
    .btn-success:hover {
        background: linear-gradient(135deg, #17a673 0%, #0e6647 100%);
        transform: translateY(-1px);
    }
    
    .nav-pills .nav-link {
        color: #6c757d;
        font-weight: 500;
        border: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
        margin: 0 2px;
    }
    
    .nav-pills .nav-link:hover {
        color: #4e73df;
        background-color: rgba(78, 115, 223, 0.05);
    }
    
    .nav-pills .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border-color: #4e73df;
        font-weight: 600;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #6c757d;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem 0.75rem;
    }
    
    .table td {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        border-color: #4e73df;
    }
    
    .sticky-top {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 10;
    }
</style>

<!-- Delete Payment Confirmation Modal -->
<div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash-alt me-2"></i>Delete Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Payment Amount</label>
                    <div class="p-3 bg-light rounded text-center">
                        <h4 class="mb-0 text-danger" id="delete_payment_amount">৳ 0.00</h4>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Payment Date</label>
                    <div class="p-2 bg-light rounded" id="delete_payment_date">-</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Payment Method</label>
                    <div class="p-2 bg-light rounded" id="delete_payment_method">-</div>
                </div>
                
                <div class="alert alert-warning mb-0">
                    <strong><i class="fas fa-info-circle me-2"></i>What happens next?</strong>
                    <ul class="mb-0 mt-2">
                        <li>This payment record will be permanently deleted</li>
                        <li>The invoice balance will be recalculated</li>
                        <li>The due amount will be updated accordingly</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeletePaymentBtn" onclick="executeDeletePayment()">
                    <i class="fas fa-trash me-1"></i>Delete Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Updated Payment Modal Functions with Bootstrap 5 compatibility
class PaymentModal {
    constructor() {
        this.isSubmitting = false;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Handle payment modal show event
        document.addEventListener('show.bs.modal', (event) => {
            if (event.target.id === 'addPaymentModal') {
                this.handleModalShow(event);
            }
        });

        // Payment amount validation
        document.addEventListener('input', (e) => {
            if (e.target.id === 'payment_amount') {
                this.validatePaymentAmount(e.target);
                this.calculateReceivedAndDue();
            }
        });

        // Payment form submission
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'addPaymentForm') {
                this.handlePaymentSubmit(e);
            }
        });

        // Reset payment form when modal is hidden
        document.addEventListener('hidden.bs.modal', (event) => {
            if (event.target.id === 'addPaymentModal') {
                this.resetPaymentForm();
            }
        });

        // Handle edit payment button click
        document.addEventListener('click', (e) => {
            if (e.target.closest('.edit-payment-btn')) {
                e.preventDefault();
                const button = e.target.closest('.edit-payment-btn');
                this.handleEditPayment(button);
            }
            
            if (e.target.closest('.delete-payment-btn')) {
                e.preventDefault();
                const button = e.target.closest('.delete-payment-btn');
                this.handleDeletePayment(button);
            }
        });

        // Tab switching functionality
        document.addEventListener('shown.bs.tab', (event) => {
            const activeTab = event.target;
            this.handleTabSwitch(activeTab);
        });
    }

    handleTabSwitch(activeTab) {
        const submitBtn = document.getElementById('paymentSubmitBtn');
        const submitText = document.getElementById('paymentSubmitText');
        
        if (activeTab.id === 'payment-form-tab') {
            submitText.textContent = 'Record Payment';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
        } else if (activeTab.id === 'existing-payments-tab') {
            submitText.textContent = 'Update Payment';
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-primary');
        }
    }

    handleModalShow(event) {
        const button = event.relatedTarget;
        const invoiceId = button?.dataset?.invoiceId;
        
        console.log('Loading payment data for invoice ID:', invoiceId);
        
        if (button?.dataset?.invoiceNumber && button?.dataset?.customerName) {
            this.populateFromButtonData(button);
            if (invoiceId) {
                this.loadExistingPayments(invoiceId);
            }
        } else if (invoiceId) {
            this.setLoadingState();
            this.fetchInvoiceData(invoiceId)
                .then(invoice => {
                    console.log('Invoice data loaded from database:', invoice);
                    this.populateModal(invoiceId, invoice);
                    this.loadExistingPayments(invoiceId);
                })
                .catch(error => {
                    console.error('Error fetching invoice data from database:', error);
                    this.showToast('Error loading invoice data from database. Please try again.', 'error');
                    this.fallbackToButtonData(button);
                });
        } else {
            this.setLoadingState();
            this.showToast('Missing invoice data. Please try again.', 'error');
        }
    }

    setLoadingState() {
        document.getElementById('payment_invoice_number_display').textContent = 'Loading...';
        document.getElementById('payment_customer_name_display').textContent = 'Loading...';
        document.getElementById('payment_total_amount_display').textContent = '৳ 0.00';
        document.getElementById('payment_due_amount_display').textContent = '৳ 0.00';
        document.getElementById('payment_status_display').textContent = 'Loading...';
        document.getElementById('payment_status_display').className = 'badge bg-secondary';
    }

    async fetchInvoiceData(invoiceId) {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
        const response = await fetch(`${baseUrl}/admin/billing/invoice/${invoiceId}/data`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        if (data.success) {
            return data.invoice;
        } else {
            throw new Error(data.message || 'Failed to fetch invoice data');
        }
    }

    populateFromButtonData(button) {
        const invoiceId = button.dataset.invoiceId;
        const invoiceNumber = button.dataset.invoiceNumber;
        const customerName = button.dataset.customerName;
        const totalAmount = button.dataset.totalAmount;
        const dueAmount = button.dataset.dueAmount;
        const status = button.dataset.status;
        
        console.log('Populating from button data:', {
            invoiceId, invoiceNumber, customerName, totalAmount, dueAmount, status
        });
        
        // Set form action and invoice ID
        if (invoiceId) {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
            let fullPath = `${baseUrl}/admin/billing/record-payment/${invoiceId}`;
            fullPath = fullPath.replace(/(\/netbill-bd\/public){2,}/g, '/netbill-bd/public');
            
            document.getElementById('addPaymentForm').action = fullPath;
            document.getElementById('payment_invoice_id').value = invoiceId;
        }
        
        // Populate fields
        document.getElementById('payment_invoice_number_display').textContent = invoiceNumber || 'N/A';
        document.getElementById('payment_customer_name_display').textContent = customerName || 'N/A';
        document.getElementById('payment_total_amount_display').textContent = '৳ ' + (parseFloat(totalAmount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2});
        document.getElementById('payment_due_amount_display').textContent = '৳ ' + (parseFloat(dueAmount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2});
        
        // Set status badge
        const statusDisplay = document.getElementById('payment_status_display');
        const statusText = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'N/A';
        statusDisplay.textContent = statusText;
        statusDisplay.className = 'badge';
        
        switch(status) {
            case 'paid':
                statusDisplay.classList.add('bg-success');
                break;
            case 'partial':
                statusDisplay.classList.add('bg-warning', 'text-dark');
                break;
            case 'unpaid':
                statusDisplay.classList.add('bg-danger');
                break;
            default:
                statusDisplay.classList.add('bg-secondary');
        }
        
        // Set payment amount
        const paymentAmountField = document.getElementById('payment_amount');
        const totalAmt = parseFloat(totalAmount) || 0;
        paymentAmountField.value = totalAmt.toFixed(2);
        paymentAmountField.max = totalAmt;
        paymentAmountField.min = 0.01;
        
        // Reset validation
        paymentAmountField.classList.remove('is-invalid');
        document.getElementById('payment_amount_error').style.display = 'none';
        
        // Calculate initial values
        setTimeout(() => {
            this.calculateReceivedAndDue();
        }, 0);
        
        console.log('Payment modal populated from button data');
    }

    // ... rest of your methods remain the same with vanilla JS conversions where needed
    // (convert jQuery selectors to vanilla JS, etc.)

    calculateReceivedAndDue() {
        const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
        const dueAmountText = document.getElementById('payment_due_amount_display').textContent;
        const dueAmount = parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0;
        const nextDue = Math.max(0, dueAmount - paymentAmount);
        
        document.getElementById('next_due').value = nextDue.toFixed(2);
    }

    validatePaymentAmount(input) {
        const paymentAmount = parseFloat(input.value) || 0;
        const dueAmountText = document.getElementById('payment_due_amount_display').textContent;
        const dueAmount = parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0;
        
        if (paymentAmount > dueAmount) {
            input.classList.add('is-invalid');
            document.getElementById('payment_amount_error').style.display = 'block';
        } else {
            input.classList.remove('is-invalid');
            document.getElementById('payment_amount_error').style.display = 'none';
        }
    }

    // ... continue converting other methods to vanilla JS
}

// Initialize payment modal when document is ready
document.addEventListener('DOMContentLoaded', function() {
    new PaymentModal();
});
</script>