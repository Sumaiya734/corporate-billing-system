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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closePaymentModalBtn"></button>
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
                                            <small class="text-muted">Due: <span id="payment_due_amount_helper" class="fw-bold text-danger">৳ 0.00</span></small>
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
                                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
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
                                    <tbody id="existingPaymentsTableBody">
                                        <!-- Payments will be loaded here dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light rounded-bottom">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal" id="cancelPaymentBtn">
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
        border-radius: 12px;
    }
    .nav-pills .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border-color: #4e73df;
        font-weight: 600;
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeDeletePaymentModalBtn"></button>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelDeletePaymentBtn">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeletePaymentBtn">
                    <i class="fas fa-trash me-1"></i>Delete Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Fixed Payment Modal Functions
class PaymentModal {
    constructor() {
        this.isSubmitting = false;
        this.editPaymentAmountListener = null;
        this.deleteModalInstance = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeDeleteModal();
        this.handleModalStacking(); // Add this line
    }

    bindEvents() {
        // Handle payment modal show event
        document.addEventListener('show.bs.modal', (event) => {
            if (event.target.id === 'addPaymentModal') {
                try {
                    this.handleModalShow(event);
                } catch (error) {
                    console.error('Error in handleModalShow:', error);
                    if (typeof this.showToast === 'function') {
                        this.showToast('Error opening payment modal. Please refresh and try again.', 'error');
                    }
                }
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
            // Ensure backdrop is removed when any modal is hidden
            this.ensureBackdropRemoved();
        });

        // Handle edit payment button click - using event delegation
        document.addEventListener('click', (e) => {
            // Edit payment button - check if it's the exact button or child
            if (e.target.classList.contains('edit-payment-btn') || e.target.closest('.edit-payment-btn')) {
                e.preventDefault();
                const button = e.target.classList.contains('edit-payment-btn') ? e.target : e.target.closest('.edit-payment-btn');
                if (!button.hasAttribute('data-handled')) {
                    button.setAttribute('data-handled', 'true');
                    console.log('Edit payment button clicked:', button);
                    this.handleEditPayment(button);
                    // Remove the attribute after processing to allow future clicks
                    setTimeout(() => button.removeAttribute('data-handled'), 100);
                }
            }
            
            // Delete payment button - check if it's the exact button or child
            if (e.target.classList.contains('delete-payment-btn') || e.target.closest('.delete-payment-btn')) {
                e.preventDefault();
                const button = e.target.classList.contains('delete-payment-btn') ? e.target : e.target.closest('.delete-payment-btn');
                if (!button.hasAttribute('data-handled')) {
                    button.setAttribute('data-handled', 'true');
                    console.log('Delete payment button clicked:', button);
                    this.handleDeletePayment(button);
                    // Remove the attribute after processing to allow future clicks
                    setTimeout(() => button.removeAttribute('data-handled'), 100);
                }
            }
        });

        // Tab switching functionality
        document.addEventListener('shown.bs.tab', (event) => {
            const activeTab = event.target;
            this.handleTabSwitch(activeTab);
        });

        // Add event listener for cancel/close buttons to ensure proper modal dismissal
        document.addEventListener('click', (e) => {
            // Handle payment modal cancel/close buttons
            if (e.target.id === 'cancelPaymentBtn' || e.target.closest('#cancelPaymentBtn') || 
                e.target.id === 'closePaymentModalBtn' || e.target.closest('#closePaymentModalBtn')) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
                if (modal) {
                    modal.hide();
                }
                this.ensureBackdropRemoved();
            }
            
            // Handle delete payment modal cancel/close buttons
            if (e.target.id === 'cancelDeletePaymentBtn' || e.target.closest('#cancelDeletePaymentBtn') ||
                e.target.id === 'closeDeletePaymentModalBtn' || e.target.closest('#closeDeletePaymentModalBtn')) {
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePaymentModal'));
                if (deleteModal) {
                    deleteModal.hide();
                }
                // Don't remove backdrop here - let the main payment modal handle it
                // The delete modal is a child modal, so when it closes, the backdrop should remain for parent
            }
        });
    }

    initializeDeleteModal() {
        // Initialize delete modal event listener
        const confirmDeleteBtn = document.getElementById('confirmDeletePaymentBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                this.executeDeletePayment();
            });
        }
    }

    handleTabSwitch(activeTab) {
        const submitBtn = document.getElementById('paymentSubmitBtn');
        const submitText = document.getElementById('paymentSubmitText');
        
        if (!submitBtn || !submitText) {
            return;
        }

        if (activeTab.id === 'payment-form-tab' || activeTab.id === 'existing-payments-tab') {
            submitText.textContent = 'Record Payment';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
        }
    }

    handleModalShow(event) {
        const button = event.relatedTarget;
        
        if (!button) {
            console.error('No button found - modal opened programmatically?');
            this.setLoadingState();
            return;
        }
        
        const invoiceId = button.dataset?.invoiceId;
        
        console.log('Payment modal opening for invoice:', invoiceId);
        
        if (!invoiceId) {
            console.error('Missing invoice ID in button data');
            this.setLoadingState();
            if (typeof this.showToast === 'function') {
                this.showToast('Missing invoice ID. Please refresh the page and try again.', 'error');
            }
            return;
        }
        
        // Populate from button data first
        if (button.dataset.invoiceNumber && button.dataset.customerName) {
            console.log('Populating from button data...');
            this.populateFromButtonData(button);
            this.loadExistingPayments(invoiceId);
        } else {
            console.log('Button data incomplete, fetching from database...');
            this.setLoadingState();
            this.fetchInvoiceData(invoiceId)
                .then(invoice => {
                    console.log('Invoice data loaded from database:', invoice);
                    this.populateModal(invoiceId, invoice);
                    this.loadExistingPayments(invoiceId);
                })
                .catch(error => {
                    console.error('Error fetching invoice data:', error);
                    this.fallbackToButtonData(button);
                });
        }
    }

    setLoadingState() {
        const elements = {
            'payment_invoice_number_display': 'Loading...',
            'payment_customer_name_display': 'Loading...',
            'payment_total_amount_display': '৳ 0.00',
            'payment_due_amount_display': '৳ 0.00',
            'payment_status_display': 'Loading...'
        };

        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = elements[id];
                if (id === 'payment_status_display') {
                    element.className = 'badge bg-secondary';
                }
            }
        });
    }

    async fetchInvoiceData(invoiceId) {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
        const response = await fetch(`${baseUrl}/admin/billing/invoice/${invoiceId}/data`);
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
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
            
            const form = document.getElementById('addPaymentForm');
            if (form) {
                form.action = fullPath;
            }
            const invoiceIdField = document.getElementById('payment_invoice_id');
            if (invoiceIdField) {
                invoiceIdField.value = invoiceId;
            }
        }
        
        // Populate display fields
        const displayData = {
            'payment_invoice_number_display': invoiceNumber || 'N/A',
            'payment_customer_name_display': customerName || 'N/A',
            'payment_total_amount_display': '৳ ' + (parseFloat(totalAmount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2}),
            'payment_due_amount_display': '৳ ' + (parseFloat(dueAmount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2}),
            'payment_due_amount_helper': '৳ ' + (parseFloat(dueAmount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2})
        };

        Object.keys(displayData).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = displayData[id];
            }
        });
        
        // Set status badge
        const statusDisplay = document.getElementById('payment_status_display');
        if (statusDisplay) {
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
        }
        
        // Set payment amount field
        const paymentAmountField = document.getElementById('payment_amount');
        if (paymentAmountField) {
            const totalAmt = parseFloat(totalAmount) || 0;
            paymentAmountField.value = totalAmt.toFixed(2);
            paymentAmountField.max = totalAmt;
            paymentAmountField.min = 0.01;
            
            // Reset validation
            paymentAmountField.classList.remove('is-invalid');
            const paymentAmountError = document.getElementById('payment_amount_error');
            if (paymentAmountError) {
                paymentAmountError.style.display = 'none';
            }
            
            // Calculate initial values
            setTimeout(() => {
                this.calculateReceivedAndDue();
            }, 0);
        }
        
        console.log('Payment modal populated from button data');
    }

    calculateReceivedAndDue() {
        const paymentAmount = parseFloat(document.getElementById('payment_amount')?.value) || 0;
        const isEditing = document.getElementById('payment_id')?.value !== '';
        const nextDueField = document.getElementById('next_due');
        
        if (!nextDueField) return;
        
        if (isEditing) {
            const totalAmountText = document.getElementById('payment_total_amount_display')?.textContent;
            const totalAmount = totalAmountText ? parseFloat(totalAmountText.replace(/[^\d.]/g, '')) || 0 : 0;
            const nextDue = Math.max(0, totalAmount - paymentAmount);
            nextDueField.value = nextDue.toFixed(2);
        } else {
            const dueAmountText = document.getElementById('payment_due_amount_display')?.textContent;
            const dueAmount = dueAmountText ? parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0 : 0;
            const nextDue = Math.max(0, dueAmount - paymentAmount);
            nextDueField.value = nextDue.toFixed(2);
        }
    }

    validatePaymentAmount(input) {
        const paymentAmount = parseFloat(input.value) || 0;
        const totalAmountText = document.getElementById('payment_total_amount_display')?.textContent;
        const totalAmount = totalAmountText ? parseFloat(totalAmountText.replace(/[^\d.]/g, '')) || 0 : 0;
        
        if (paymentAmount > totalAmount) {
            input.classList.add('is-invalid');
            const paymentAmountError = document.getElementById('payment_amount_error');
            if (paymentAmountError) {
                paymentAmountError.style.display = 'block';
                paymentAmountError.textContent = `Payment amount cannot exceed total invoice amount (৳${totalAmount.toFixed(2)})`;
            }
        } else {
            input.classList.remove('is-invalid');
            const paymentAmountError = document.getElementById('payment_amount_error');
            if (paymentAmountError) {
                paymentAmountError.style.display = 'none';
            }
        }
    }

    populateModal(invoiceId, invoice) {
        console.log('Populating modal with invoice data:', invoice);
        
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
        let fullPath = `${baseUrl}/admin/billing/record-payment/${invoiceId}`;
        
        const form = document.getElementById('addPaymentForm');
        if (form) {
            form.action = fullPath;
        }
        
        const invoiceIdField = document.getElementById('payment_invoice_id');
        if (invoiceIdField) {
            invoiceIdField.value = invoiceId;
        }
        
        const displayData = {
            'payment_invoice_number_display': invoice.invoice_number || 'N/A',
            'payment_customer_name_display': invoice.customer_name || 'N/A',
            'payment_total_amount_display': '৳ ' + (parseFloat(invoice.total_amount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2}),
            'payment_due_amount_display': '৳ ' + (parseFloat(invoice.next_due) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2}),
            'payment_due_amount_helper': '৳ ' + (parseFloat(invoice.next_due) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2})
        };

        Object.keys(displayData).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = displayData[id];
            }
        });
        
        const statusDisplay = document.getElementById('payment_status_display');
        if (statusDisplay) {
            statusDisplay.textContent = invoice.status ? invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1) : 'N/A';
            statusDisplay.className = 'badge';
            
            switch(invoice.status) {
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
        }
    }

    fallbackToButtonData(button) {
        if (button) {
            this.populateFromButtonData(button);
        }
    }

   // In the handleEditPayment function, add this line to prevent duplicate loading:
handleEditPayment(button) {
    const paymentId = button.dataset.paymentId;
    const amount = button.dataset.amount;
    const paymentMethod = button.dataset.paymentMethod;
    const paymentDate = button.dataset.paymentDate;
    const notes = button.dataset.notes || '';
    
    console.log('Editing payment:', { paymentId, amount, paymentMethod, paymentDate, notes });
    
    // Switch to payment form tab
    const paymentFormTab = document.getElementById('payment-form-tab');
    if (paymentFormTab) {
        const tab = new bootstrap.Tab(paymentFormTab);
        tab.show();
    }
    
    // Populate form with payment data
    document.getElementById('payment_id').value = paymentId;
    document.getElementById('payment_amount').value = parseFloat(amount).toFixed(2);
    document.querySelector('select[name="payment_method"]').value = paymentMethod;
    document.querySelector('input[name="payment_date"]').value = paymentDate;
    document.querySelector('textarea[name="notes"]').value = notes;
    
    // Change form to PUT method for update
    document.getElementById('payment_method_override').value = 'PUT';
    
    // Update form action to include payment ID
    const form = document.getElementById('addPaymentForm');
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
    form.action = `${baseUrl}/admin/billing/payment/${paymentId}`;
    
    // Update button text and styling
    document.getElementById('paymentSubmitText').textContent = 'Update Payment';
    const submitBtn = document.getElementById('paymentSubmitBtn');
    if (submitBtn) {
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-primary');
    }
    
    // Get total amount from display
    const totalAmountText = document.getElementById('payment_total_amount_display').textContent;
    const totalAmount = parseFloat(totalAmountText.replace(/[^\d.]/g, '')) || 0;
    
    // Calculate and display the new due amount
    const editedPayment = parseFloat(amount) || 0;
    const newDue = Math.max(0, totalAmount - editedPayment);
    
    // Update the next due field
    document.getElementById('next_due').value = newDue.toFixed(2);
    
    // Set max value for payment amount
    const paymentAmountField = document.getElementById('payment_amount');
    paymentAmountField.max = totalAmount.toFixed(2);
    
    // Clear any validation errors
    paymentAmountField.classList.remove('is-invalid');
    const paymentAmountError = document.getElementById('payment_amount_error');
    if (paymentAmountError) {
        paymentAmountError.style.display = 'none';
    }
    
    // Remove any existing event listeners to prevent duplicates
    if (this.editPaymentAmountListener) {
        paymentAmountField.removeEventListener('input', this.editPaymentAmountListener);
    }
    
    // Add event listener to recalculate due when user changes amount
    this.editPaymentAmountListener = () => {
        this.calculateReceivedAndDue();
    };
    paymentAmountField.addEventListener('input', this.editPaymentAmountListener);
    
    // Show info message
    this.showToast('Payment loaded for editing. Modify the amount and click "Update Payment" to save changes.', 'info');
    
    // Trigger input event to recalculate due when user changes amount
    paymentAmountField.focus();
}

// Also update the displayExistingPayments function to properly clear the table:
displayExistingPayments(payments) {
    const tbody = document.getElementById('existingPaymentsTableBody');
    const badge = document.getElementById('payment-count-badge');
    
    if (badge) {
        badge.textContent = payments.length;
        badge.classList.remove('bg-secondary');
        badge.classList.add('bg-primary');
    }
    
    // Clear the table body completely before adding new rows
    if (tbody) {
        tbody.innerHTML = ''; // Clear any existing content
        
        if (payments.length === 0) {
            this.displayNoPayments();
            return;
        }
        
        // Add each payment row
        payments.forEach(payment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <small class="text-muted">${new Date(payment.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</small>
                </td>
                <td>
                    <strong class="text-success">৳ ${parseFloat(payment.amount).toLocaleString('en-BD', {minimumFractionDigits: 2})}</strong>
                </td>
                <td>
                    <span class="badge bg-light text-dark">${payment.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                </td>
                <td>
                    <small class="text-muted">${payment.note || payment.notes || '-'}</small>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary edit-payment-btn" 
                                data-payment-id="${payment.payment_id}"
                                data-amount="${payment.amount}"
                                data-payment-method="${payment.payment_method}"
                                data-payment-date="${payment.payment_date}"
                                data-notes="${payment.note || payment.notes || ''}"
                                title="Edit Payment">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger delete-payment-btn" 
                                data-payment-id="${payment.payment_id}"
                                data-amount="${payment.amount}"
                                data-payment-method="${payment.payment_method}"
                                data-payment-date="${payment.payment_date}"
                                title="Delete Payment">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
}

// And update the displayNoPayments function:
displayNoPayments() {
    const tbody = document.getElementById('existingPaymentsTableBody');
    const badge = document.getElementById('payment-count-badge');
    
    if (badge) {
        badge.textContent = '0';
        badge.classList.remove('bg-primary');
        badge.classList.add('bg-secondary');
    }
    
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0 small">No payment history found</p>
                </td>
            </tr>
        `;
    }
}

// Also add a fix to prevent duplicate event listeners in the bindEvents method:
bindEvents() {
    // Use event delegation with proper checks to prevent duplicate handlers
    document.addEventListener('click', (e) => {
        // Edit payment button - check if it's the exact button or child
        if (e.target.classList.contains('edit-payment-btn') || e.target.closest('.edit-payment-btn')) {
            e.preventDefault();
            const button = e.target.classList.contains('edit-payment-btn') ? e.target : e.target.closest('.edit-payment-btn');
            if (!button.hasAttribute('data-handled')) {
                button.setAttribute('data-handled', 'true');
                console.log('Edit payment button clicked:', button);
                this.handleEditPayment(button);
                // Remove the attribute after processing to allow future clicks
                setTimeout(() => button.removeAttribute('data-handled'), 100);
            }
        }
        
        // Delete payment button - check if it's the exact button or child
        if (e.target.classList.contains('delete-payment-btn') || e.target.closest('.delete-payment-btn')) {
            e.preventDefault();
            const button = e.target.classList.contains('delete-payment-btn') ? e.target : e.target.closest('.delete-payment-btn');
            if (!button.hasAttribute('data-handled')) {
                button.setAttribute('data-handled', 'true');
                console.log('Delete payment button clicked:', button);
                this.handleDeletePayment(button);
                // Remove the attribute after processing to allow future clicks
                setTimeout(() => button.removeAttribute('data-handled'), 100);
            }
        }
    });

    // Handle payment modal show event
    document.addEventListener('show.bs.modal', (event) => {
        if (event.target.id === 'addPaymentModal') {
            try {
                this.handleModalShow(event);
            } catch (error) {
                console.error('Error in handleModalShow:', error);
                if (typeof this.showToast === 'function') {
                    this.showToast('Error opening payment modal. Please refresh and try again.', 'error');
                }
            }
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
        // Ensure backdrop is removed when any modal is hidden
        this.ensureBackdropRemoved();
    });

    // Handle edit payment button click - using event delegation
    document.addEventListener('click', (e) => {
        // Edit payment button - check if it's the exact button or child
        if (e.target.classList.contains('edit-payment-btn') || e.target.closest('.edit-payment-btn')) {
            e.preventDefault();
            const button = e.target.classList.contains('edit-payment-btn') ? e.target : e.target.closest('.edit-payment-btn');
            if (!button.hasAttribute('data-handled')) {
                button.setAttribute('data-handled', 'true');
                console.log('Edit payment button clicked:', button);
                this.handleEditPayment(button);
                // Remove the attribute after processing to allow future clicks
                setTimeout(() => button.removeAttribute('data-handled'), 100);
            }
        }
        
        // Delete payment button - check if it's the exact button or child
        if (e.target.classList.contains('delete-payment-btn') || e.target.closest('.delete-payment-btn')) {
            e.preventDefault();
            const button = e.target.classList.contains('delete-payment-btn') ? e.target : e.target.closest('.delete-payment-btn');
            if (!button.hasAttribute('data-handled')) {
                button.setAttribute('data-handled', 'true');
                console.log('Delete payment button clicked:', button);
                this.handleDeletePayment(button);
                // Remove the attribute after processing to allow future clicks
                setTimeout(() => button.removeAttribute('data-handled'), 100);
            }
        }
    });

    // Tab switching functionality
    document.addEventListener('shown.bs.tab', (event) => {
        const activeTab = event.target;
        this.handleTabSwitch(activeTab);
    });

    // Add event listener for cancel/close buttons to ensure proper modal dismissal
    document.addEventListener('click', (e) => {
        // Handle payment modal cancel/close buttons
        if (e.target.id === 'cancelPaymentBtn' || e.target.closest('#cancelPaymentBtn') || 
            e.target.id === 'closePaymentModalBtn' || e.target.closest('#closePaymentModalBtn')) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
            if (modal) {
                modal.hide();
            }
            this.ensureBackdropRemoved();
        }
        
        // Handle delete payment modal cancel/close buttons
        if (e.target.id === 'cancelDeletePaymentBtn' || e.target.closest('#cancelDeletePaymentBtn') ||
            e.target.id === 'closeDeletePaymentModalBtn' || e.target.closest('#closeDeletePaymentModalBtn')) {
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePaymentModal'));
            if (deleteModal) {
                deleteModal.hide();
            }
            // Don't remove backdrop here - let the main payment modal handle it
            // The delete modal is a child modal, so when it closes, the backdrop should remain for parent
        }
    });
}

    handleDeletePayment(button) {
        const paymentId = button.dataset.paymentId;
        const amount = button.dataset.amount;
        const paymentMethod = button.dataset.paymentMethod;
        const paymentDate = button.dataset.paymentDate;
        
        console.log('Deleting payment:', { paymentId, amount, paymentMethod, paymentDate });
        
        // Validate required data
        if (!paymentId || !amount) {
            console.error('Missing required payment data for deletion');
            this.showToast('Error: Missing payment information. Please refresh and try again.', 'error');
            return;
        }
        
        // Store payment ID for deletion
        this.deletePaymentId = paymentId;
        
        // Populate delete modal
        const deletePaymentAmount = document.getElementById('delete_payment_amount');
        if (deletePaymentAmount) {
            deletePaymentAmount.textContent = '৳ ' + parseFloat(amount).toLocaleString('en-BD', {minimumFractionDigits: 2});
        }
        
        const deletePaymentDate = document.getElementById('delete_payment_date');
        if (deletePaymentDate) {
            deletePaymentDate.textContent = new Date(paymentDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        
        const deletePaymentMethod = document.getElementById('delete_payment_method');
        if (deletePaymentMethod) {
            deletePaymentMethod.textContent = paymentMethod ? paymentMethod.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-';
        }
        
        // Show delete modal
        const deleteModalElement = document.getElementById('deletePaymentModal');
        if (deleteModalElement) {
            if (!this.deleteModalInstance) {
                this.deleteModalInstance = new bootstrap.Modal(deleteModalElement);
            }
            this.deleteModalInstance.show();
        } else {
            console.error('Delete payment modal element not found');
            this.showToast('Error: Delete confirmation dialog not found. Please refresh and try again.', 'error');
        }
    }

    async executeDeletePayment() {
        const paymentId = this.deletePaymentId;
        
        if (!paymentId) {
            this.showToast('Payment ID not found', 'error');
            return;
        }
        
        const confirmBtn = document.getElementById('confirmDeletePaymentBtn');
        const originalHtml = confirmBtn ? confirmBtn.innerHTML : 'Delete Payment';
        if (confirmBtn) {
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
            confirmBtn.disabled = true;
        }
        
        try {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
            const url = `${baseUrl}/admin/billing/payment/${paymentId}`;
            
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Close delete modal properly
                const deleteModalElement = document.getElementById('deletePaymentModal');
                if (deleteModalElement) {
                    const deleteModal = bootstrap.Modal.getInstance(deleteModalElement);
                    if (deleteModal) {
                        deleteModal.hide();
                    }
                }
                
                // Also close the main payment modal
                const paymentModalElement = document.getElementById('addPaymentModal');
                if (paymentModalElement) {
                    const paymentModal = bootstrap.Modal.getInstance(paymentModalElement);
                    if (paymentModal) {
                        paymentModal.hide();
                    }
                }
                
                // Clean up backdrop completely
                this.ensureBackdropRemoved();
                
                // Show success message
                this.showToast(data.message || 'Payment deleted successfully', 'success');
                
                // Reload page after short delay
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showToast(data.message || 'Error deleting payment', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('Network error occurred', 'error');
        } finally {
            // Always restore button state
            if (confirmBtn) {
                confirmBtn.innerHTML = originalHtml;
                confirmBtn.disabled = false;
            }
        }
    }

    loadExistingPayments(invoiceId) {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
        const url = `${baseUrl}/admin/billing/invoice/${invoiceId}/payments`;
        
        console.log('Loading existing payments from:', url);
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Non-JSON response');
            }
            return response.json();
        })
        .then(data => {
            console.log('Payments loaded:', data);
            
            if (data && data.success && data.payments) {
                this.displayExistingPayments(data.payments);
            } else {
                this.displayNoPayments();
            }
        })
        .catch(error => {
            console.error('Error loading payments:', error);
            this.displayNoPayments();
        });
    }

    displayExistingPayments(payments) {
        const tbody = document.getElementById('existingPaymentsTableBody');
        const badge = document.getElementById('payment-count-badge');
        
        if (badge) {
            badge.textContent = payments.length;
            badge.classList.remove('bg-secondary');
            badge.classList.add('bg-primary');
        }
        
        if (payments.length === 0) {
            this.displayNoPayments();
            return;
        }
        
        if (tbody) {
            tbody.innerHTML = payments.map(payment => `
                <tr>
                    <td>
                        <small class="text-muted">${new Date(payment.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</small>
                    </td>
                    <td>
                        <strong class="text-success">৳ ${parseFloat(payment.amount).toLocaleString('en-BD', {minimumFractionDigits: 2})}</strong>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">${payment.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                    </td>
                    <td>
                        <small class="text-muted">${payment.note || payment.notes || '-'}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary edit-payment-btn" 
                                    data-payment-id="${payment.payment_id}"
                                    data-amount="${payment.amount}"
                                    data-payment-method="${payment.payment_method}"
                                    data-payment-date="${payment.payment_date}"
                                    data-notes="${payment.note || payment.notes || ''}"
                                    title="Edit Payment">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger delete-payment-btn" 
                                    data-payment-id="${payment.payment_id}"
                                    data-amount="${payment.amount}"
                                    data-payment-method="${payment.payment_method}"
                                    data-payment-date="${payment.payment_date}"
                                    title="Delete Payment">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    }

    displayNoPayments() {
        const tbody = document.getElementById('existingPaymentsTableBody');
        const badge = document.getElementById('payment-count-badge');
        
        if (badge) {
            badge.textContent = '0';
            badge.classList.remove('bg-primary');
            badge.classList.add('bg-secondary');
        }
        
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0 small">No payment history found</p>
                    </td>
                </tr>
            `;
        }
    }

    hideModalWithBackdropRemoval(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Try to use Bootstrap's modal instance first
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        } else {
            // Fallback: manually hide modal and backdrop
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        
        // Always clean up backdrop and body classes
        this.ensureBackdropRemoved();
    }
}

ensureBackdropRemoved() {
    // Remove any leftover backdrop elements
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        backdrop.remove();
    });
    
    // Remove modal-open class from body and reset padding
    document.body.classList.remove('modal-open');
    document.body.style.paddingRight = '';
    document.body.style.overflow = '';
}

// Add a method to handle modal stacking properly:
handleModalStacking() {
    // When delete modal opens, ensure proper z-index stacking
    document.addEventListener('show.bs.modal', (e) => {
        if (e.target.id === 'deletePaymentModal') {
            // Increase z-index for delete modal to appear above payment modal
            e.target.style.zIndex = '1060';
            
            // Adjust backdrop z-index
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                if (parseInt(backdrop.style.zIndex) === 1050) {
                    backdrop.style.zIndex = '1059';
                }
            });
        }
    });

    // When delete modal closes, restore z-index values
    document.addEventListener('hidden.bs.modal', (e) => {
        if (e.target.id === 'deletePaymentModal') {
            // Reset z-index
            e.target.style.zIndex = '';
            
            // Restore backdrop z-index for main modal
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                if (parseInt(backdrop.style.zIndex) === 1059) {
                    backdrop.style.zIndex = '1050';
                }
            });
        }
    });
}

    handlePaymentSubmit(e) {
        e.preventDefault();
        
        if (this.isSubmitting) {
            return;
        }
        
        const form = e.target;
        const submitBtn = document.getElementById('paymentSubmitBtn');
        const originalHtml = submitBtn.innerHTML;
        const isEditing = document.getElementById('payment_id').value !== '';
        
        // Validate payment amount
        const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
        
        if (paymentAmount <= 0) {
            this.showToast('Payment amount must be greater than zero', 'error');
            return;
        }
        
        // Validation: payment cannot exceed total invoice amount
        const totalAmountText = document.getElementById('payment_total_amount_display').textContent;
        const totalAmount = parseFloat(totalAmountText.replace(/[^\d.]/g, '')) || 0;
        
        if (paymentAmount > totalAmount + 0.01) {
            this.showToast(`Payment amount cannot exceed total invoice amount (৳${totalAmount.toFixed(2)})`, 'error');
            return;
        }
        
        this.isSubmitting = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
        submitBtn.disabled = true;
        
        const formData = new FormData(form);
        
        // Determine the correct method and URL based on whether we're editing or creating
        const method = isEditing ? 'PUT' : 'POST';
        let url = form.action;
        
        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast(data.message || (isEditing ? 'Payment updated successfully' : 'Payment processed successfully'), 'success');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
                if (modal) {
                    modal.hide();
                    // Ensure backdrop is removed
                    this.ensureBackdropRemoved();
                }
                
                // Reload page after short delay
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showToast(data.message || (isEditing ? 'Error updating payment' : 'Error processing payment'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showToast(isEditing ? 'Network error occurred while updating payment' : 'Network error occurred while processing payment', 'error');
        })
        .finally(() => {
            this.isSubmitting = false;
            submitBtn.innerHTML = originalHtml;
            submitBtn.disabled = false;
        });
    }

    resetPaymentForm() {
        const form = document.getElementById('addPaymentForm');
        if (form) {
            form.reset();
        }
        
        // Reset hidden fields
        document.getElementById('payment_id').value = '';
        document.getElementById('payment_method_override').value = 'POST';
        
        // Reset button
        const submitText = document.getElementById('paymentSubmitText');
        const submitBtn = document.getElementById('paymentSubmitBtn');
        if (submitText) submitText.textContent = 'Record Payment';
        if (submitBtn) {
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
        }
        
        // Clear validation
        const paymentAmountField = document.getElementById('payment_amount');
        if (paymentAmountField) {
            paymentAmountField.classList.remove('is-invalid');
            paymentAmountField.removeEventListener('input', this.editPaymentAmountListener);
            this.editPaymentAmountListener = null;
        }
        
        const paymentAmountError = document.getElementById('payment_amount_error');
        if (paymentAmountError) {
            paymentAmountError.style.display = 'none';
        }
        
        // Reset next_due field
        const nextDueField = document.getElementById('next_due');
        if (nextDueField) {
            nextDueField.value = '';
        }
        
        // Switch back to payment form tab
        const paymentFormTab = document.getElementById('payment-form-tab');
        if (paymentFormTab) {
            const tab = new bootstrap.Tab(paymentFormTab);
            tab.show();
        }
        
        // Ensure backdrop is removed
        this.ensureBackdropRemoved();
    }

    showToast(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    }
}

// Initialize payment modal when document is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.paymentModalInitialized) {
        return;
    }
    
    try {
        window.paymentModalInitialized = true;
        window.paymentModalInstance = new PaymentModal();
        console.log('Payment modal initialized successfully');
    } catch (error) {
        console.error('Error initializing payment modal:', error);
        window.paymentModalInitialized = false;
    }
});
</script>