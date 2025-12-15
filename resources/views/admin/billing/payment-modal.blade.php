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
                                            <input type="number" step="0" name="amount" class="form-control border-start-0" required
                                                id="payment_amount" min="0" placeholder="0.00">
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
                                            <input type="number" step="0" name="next_due" class="form-control border-start-0"
                                                id="next_due" min="0" placeholder="0.00" style="background-color: #f8f9fa;">
                                        </div>
                                        <div class="form-text text-muted">Amount remaining after this payment (editable)</div>
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
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Payment Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Add any additional notes about this payment..."></textarea>
                            </div>

                            <!-- Carry Forward Checkbox -->
                            <div class="mb-0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="carryForwardCheckbox" name="carry_forward" checked>
                                    <label class="form-check-label" for="carryForwardCheckbox">
                                        Carry forward remaining due to next month
                                    </label>
                                </div>
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

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        border-color: #4e73df;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 10;
    }

    /* Ensure proper modal stacking */
    .modal-backdrop {
        z-index: 1040;
    }

    .modal {
        z-index: 1050;
    }

    #deletePaymentModal {
        z-index: 1060;
    }

    #deletePaymentModal .modal-backdrop {
        z-index: 1059;
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

                <!-- <div class="alert alert-warning mb-0">
                    <strong><i class="fas fa-info-circle me-2"></i>What happens next?</strong>
                    <ul class="mb-0 mt-2">
                        <li>This payment record will be permanently deleted</li>
                        <li>The invoice balance will be recalculated</li>
                        <li>The due amount will be updated accordingly</li>
                    </ul>
                </div> -->
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
            this.backdropCleanupScheduled = false;
            this.eventsBound = false; // Track if events are bound
            this.formSubmissionBound = false; // Track if form submission is bound
            this.init();
        }

        init() {
            this.bindEvents();
            this.initializeDeleteModal();
        }

        bindEvents() {
            // Prevent duplicate event binding
            if (this.eventsBound) {
                console.log('Events already bound, skipping...');
                return;
            }
            this.eventsBound = true;

            console.log('Binding payment modal events...');

            // Payment modal show event - use document level listener to avoid duplicates
            document.addEventListener('show.bs.modal', (event) => {
                if (event.target.id === 'addPaymentModal') {
                    console.log('Payment modal show event triggered');
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

            // Payment form submission - IMPROVED: Ensure only one listener
            this.bindFormSubmission();

            // Reset payment form when modal is hidden
            document.addEventListener('hidden.bs.modal', (event) => {
                if (event.target.id === 'addPaymentModal') {
                    this.resetPaymentForm();
                }
            });

            // Single event delegation for all dynamic buttons
            document.addEventListener('click', (e) => {
                const target = e.target;

                // Edit payment button
                const editBtn = target.closest('.edit-payment-btn');
                if (editBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Edit button clicked via delegation');
                    this.handleEditPayment(editBtn);
                    return;
                }

                // Delete payment button
                const deleteBtn = target.closest('.delete-payment-btn');
                if (deleteBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Delete button clicked via delegation');
                    this.handleDeletePayment(deleteBtn);
                    return;
                }
            });

            // Handle modal close buttons
            this.setupModalCloseHandlers();
        }

        bindFormSubmission() {
            // Ensure only one form submission handler
            if (this.formSubmissionBound) {
                console.log('Form submission already bound, skipping...');
                return;
            }

            const paymentForm = document.getElementById('addPaymentForm');
            if (paymentForm) {
                // Remove any existing event listeners by cloning and replacing
                const newForm = paymentForm.cloneNode(true);
                paymentForm.parentNode.replaceChild(newForm, paymentForm);

                // Add the event listener to the new form
                const finalForm = document.getElementById('addPaymentForm');
                finalForm.addEventListener('submit', (e) => {
                    console.log('Form submit event triggered');
                    this.handlePaymentSubmit(e);
                });

                this.formSubmissionBound = true;
                console.log('Form submission handler bound successfully');
            }
        }

        setupModalCloseHandlers() {
            // Payment modal close buttons - FIXED: Use Bootstrap's native data-bs-dismiss
            // No need for custom handlers since we're using Bootstrap's built-in functionality

            // Delete modal close buttons - FIXED: Use simpler approach
            const cancelDeletePaymentBtn = document.getElementById('cancelDeletePaymentBtn');
            const closeDeletePaymentModalBtn = document.getElementById('closeDeletePaymentModalBtn');

            if (cancelDeletePaymentBtn && !cancelDeletePaymentBtn.hasAttribute('data-listener-added')) {
                cancelDeletePaymentBtn.setAttribute('data-listener-added', 'true');
                cancelDeletePaymentBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.hideDeleteModal();
                });
            }

            if (closeDeletePaymentModalBtn && !closeDeletePaymentModalBtn.hasAttribute('data-listener-added')) {
                closeDeletePaymentModalBtn.setAttribute('data-listener-added', 'true');
                closeDeletePaymentModalBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.hideDeleteModal();
                });
            }
        }

        initializeDeleteModal() {
            const confirmDeleteBtn = document.getElementById('confirmDeletePaymentBtn');
            if (confirmDeleteBtn && !confirmDeleteBtn.hasAttribute('data-listener-added')) {
                confirmDeleteBtn.setAttribute('data-listener-added', 'true');
                confirmDeleteBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.executeDeletePayment();
                });
            }
        }

        hideDeleteModal() {
            console.log('Hiding delete modal');

            const deleteModalElement = document.getElementById('deletePaymentModal');
            if (deleteModalElement) {
                let modalInstance = bootstrap.Modal.getInstance(deleteModalElement);

                if (modalInstance) {
                    console.log('Hiding delete modal via Bootstrap instance');
                    modalInstance.hide();
                } else {
                    console.log('Manually hiding delete modal');
                    deleteModalElement.style.display = 'none';
                    deleteModalElement.classList.remove('show');

                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => {
                        if (backdrop.parentNode) {
                            backdrop.parentNode.removeChild(backdrop);
                        }
                    });

                    document.body.classList.remove('modal-open');
                }
            }
        }

        handleModalShow(event) {
            console.log('handleModalShow called');

            const button = event.relatedTarget;

            if (!button) {
                console.log('Modal opened without button - may be programmatic');
                return;
            }

            const invoiceId = button.getAttribute('data-invoice-id');

            console.log('Payment modal opening for invoice:', invoiceId);

            if (!invoiceId) {
                console.error('Missing invoice ID');
                this.showToast('Missing invoice information', 'error');
                return;
            }

            this.populateFromButtonData(button);
            this.loadExistingPayments(invoiceId);
        }

        populateFromButtonData(button) {
            const invoiceId = button.getAttribute('data-invoice-id');
            const invoiceNumber = button.getAttribute('data-invoice-number');
            const customerName = button.getAttribute('data-customer-name');
            const totalAmount = button.getAttribute('data-total-amount');
            const dueAmount = button.getAttribute('data-due-amount');
            const status = button.getAttribute('data-status');
            const cpId = button.getAttribute('data-cp-id');

            console.log('Populating from button data:', {
                invoiceId,
                invoiceNumber,
                customerName,
                totalAmount,
                dueAmount,
                status,
                cpId
            });

            // Set form action and invoice ID
            if (invoiceId) {
                const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
                const form = document.getElementById('addPaymentForm');
                if (form) {
                    form.action = `${baseUrl}/admin/billing/record-payment/${invoiceId}`;
                }

                const invoiceIdField = document.getElementById('payment_invoice_id');
                if (invoiceIdField) {
                    invoiceIdField.value = invoiceId;
                }

                // Set cp_id if available
                const cpIdField = document.getElementById('payment_cp_id');
                if (cpIdField && cpId) {
                    cpIdField.value = cpId;
                }
            }

            // Populate display fields
            const displayData = {
                'payment_invoice_number_display': invoiceNumber || 'N/A',
                'payment_customer_name_display': customerName || 'N/A',
                'payment_total_amount_display': '৳ ' + (parseFloat(totalAmount) || 0).toLocaleString('en-BD'),
                'payment_due_amount_display': '৳ ' + (parseFloat(dueAmount) || 0).toLocaleString('en-BD'),
                'payment_due_amount_helper': '৳ ' + (parseFloat(dueAmount) || 0).toLocaleString('en-BD')
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

                switch (status) {
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
                const dueAmt = parseFloat(dueAmount) || 0;
                paymentAmountField.value = dueAmt > 0 ? dueAmt.toFixed(0) : '';
                paymentAmountField.max = dueAmt;
                paymentAmountField.min = 0;

                // Reset validation
                paymentAmountField.classList.remove('is-invalid');
                const paymentAmountError = document.getElementById('payment_amount_error');
                if (paymentAmountError) {
                    paymentAmountError.style.display = 'none';
                }

                // Calculate initial values
                this.calculateReceivedAndDue();
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
                nextDueField.value = nextDue.toFixed(0);
            } else {
                const dueAmountText = document.getElementById('payment_due_amount_display')?.textContent;
                const dueAmount = dueAmountText ? parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0 : 0;
                const nextDue = Math.max(0, dueAmount - paymentAmount);
                nextDueField.value = nextDue.toFixed(0);
            }
        }

        validatePaymentAmount(input) {
            const paymentAmount = parseFloat(input.value) || 0;
            const dueAmountText = document.getElementById('payment_due_amount_display')?.textContent;
            const dueAmount = dueAmountText ? parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0 : 0;

            if (paymentAmount > dueAmount) {
                input.classList.add('is-invalid');
                const paymentAmountError = document.getElementById('payment_amount_error');
                if (paymentAmountError) {
                    paymentAmountError.style.display = 'block';
                    paymentAmountError.textContent = `Payment amount cannot exceed due amount (৳${dueAmount.toFixed(0)})`;
                }
            } else {
                input.classList.remove('is-invalid');
                const paymentAmountError = document.getElementById('payment_amount_error');
                if (paymentAmountError) {
                    paymentAmountError.style.display = 'none';
                }
            }
        }

        handleEditPayment(button) {
            console.log('Edit payment button clicked');

            const paymentId = button.getAttribute('data-payment-id');
            const amount = button.getAttribute('data-amount');
            const paymentMethod = button.getAttribute('data-payment-method');
            const paymentDate = button.getAttribute('data-payment-date');
            const notes = button.getAttribute('data-notes') || '';

            console.log('Editing payment:', {
                paymentId,
                amount,
                paymentMethod,
                paymentDate,
                notes
            });

            // Validate required data
            if (!paymentId) {
                console.error('Missing payment ID for editing');
                this.showToast('Error: Missing payment ID', 'error');
                return;
            }

            if (!amount) {
                console.error('Missing payment amount for editing');
                this.showToast('Error: Missing payment amount', 'error');
                return;
            }

            // Switch to payment form tab
            const paymentFormTab = document.getElementById('payment-form-tab');
            if (paymentFormTab) {
                const tab = new bootstrap.Tab(paymentFormTab);
                tab.show();
            }

            // Populate form with payment data
            document.getElementById('payment_id').value = paymentId;
            document.getElementById('payment_amount').value = parseFloat(amount).toFixed(2);

            const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
            if (paymentMethodSelect && paymentMethod) {
                // Handle cases where payment method might have underscores or spaces
                const normalizedMethod = paymentMethod.toLowerCase().replace(/\s+/g, '_');
                paymentMethodSelect.value = normalizedMethod;
            }

            const paymentDateInput = document.querySelector('input[name="payment_date"]');
            if (paymentDateInput && paymentDate) {
                // Format the date properly for the input field
                const formattedDate = new Date(paymentDate).toISOString().split('T')[0];
                paymentDateInput.value = formattedDate;
            }

            const notesTextarea = document.querySelector('textarea[name="notes"]');
            if (notesTextarea) {
                notesTextarea.value = notes;
            }

            // Change form to PUT method for update
            document.getElementById('payment_method_override').value = 'PUT';

            // Update form action to include payment ID
            const form = document.getElementById('addPaymentForm');
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
            if (form && paymentId) {
                form.action = `${baseUrl}/admin/billing/payment/${paymentId}`;
            }

            // Update button text and styling
            const submitText = document.getElementById('paymentSubmitText');
            const submitBtn = document.getElementById('paymentSubmitBtn');
            if (submitText) submitText.textContent = 'Update Payment';
            if (submitBtn) {
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
            }

            // Calculate and display the new due amount
            this.calculateReceivedAndDue();

            console.log('Payment loaded for editing');
            this.showToast('Payment loaded for editing. Modify details and click "Update Payment" to save.', 'info');
        }
        handleDeletePayment(button) {
            const paymentId = button.getAttribute('data-payment-id');
            const amount = button.getAttribute('data-amount');
            const paymentMethod = button.getAttribute('data-payment-method');
            const paymentDate = button.getAttribute('data-payment-date');

            console.log('Deleting payment:', {
                paymentId,
                amount,
                paymentMethod,
                paymentDate
            });

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
                deletePaymentAmount.textContent = '৳ ' + parseFloat(amount).toLocaleString('en-BD');
            }

            const deletePaymentDate = document.getElementById('delete_payment_date');
            if (deletePaymentDate) {
                deletePaymentDate.textContent = new Date(paymentDate).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }

            const deletePaymentMethod = document.getElementById('delete_payment_method');
            if (deletePaymentMethod) {
                deletePaymentMethod.textContent = paymentMethod ? paymentMethod.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-';
            }

            // Show delete modal using Bootstrap's modal system
            const deleteModalElement = document.getElementById('deletePaymentModal');
            if (deleteModalElement) {
                // Get or create the Bootstrap modal instance
                let deleteModalInstance = bootstrap.Modal.getInstance(deleteModalElement);
                if (!deleteModalInstance) {
                    console.log('Creating new Bootstrap modal instance for delete modal');
                    deleteModalInstance = new bootstrap.Modal(deleteModalElement, {
                        backdrop: true,
                        keyboard: true
                    });
                }
                deleteModalInstance.show();
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

            const deleteModalElement = document.getElementById('deletePaymentModal');
            let deleteModalInstance = deleteModalElement ? bootstrap.Modal.getInstance(deleteModalElement) : null;

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
                    // Close delete modal properly using Bootstrap's method
                    if (deleteModalInstance) {
                        deleteModalInstance.hide();
                    }

                    // Also close the main payment modal
                    const paymentModalElement = document.getElementById('addPaymentModal');
                    const paymentModalInstance = paymentModalElement ? bootstrap.Modal.getInstance(paymentModalElement) : null;
                    if (paymentModalInstance) {
                        paymentModalInstance.hide();
                    }

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
            if (!invoiceId) return;

            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
            const url = `${baseUrl}/admin/billing/invoice/${invoiceId}/payments`;

            console.log('Loading payments from:', url);

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data?.success && data.payments) {
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
                badge.className = 'badge bg-primary ms-1';
            }

            if (!tbody) return;

            if (payments.length === 0) {
                this.displayNoPayments();
                return;
            }

            tbody.innerHTML = payments.map(payment => `
            <tr>
                <td>
                    <small class="text-muted">${new Date(payment.payment_date).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    })}</small>
                </td>
                <td>
                    <strong class="text-success">৳ ${parseFloat(payment.amount).toLocaleString('en-BD')}</strong>
                </td>
                <td>
                    <span class="badge bg-light text-dark border">
                        ${(payment.payment_method || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </span>
                </td>
                <td>
                    <small class="text-muted">${payment.note || payment.notes || '-'}</small>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary edit-payment-btn" 
                                data-payment-id="${payment.payment_id || payment.id}"
                                data-amount="${parseFloat(payment.amount).toFixed(2)}"
                                data-payment-method="${payment.payment_method}"
                                data-payment-date="${payment.payment_date}"
                                data-notes="${payment.note || payment.notes || ''}"
                                title="Edit Payment">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger delete-payment-btn" 
                                data-payment-id="${payment.payment_id || payment.id}"
                                data-amount="${parseFloat(payment.amount).toFixed(2)}"
                                data-payment-method="${payment.payment_method}"
                                data-payment-date="${payment.payment_date}"
                                data-notes="${payment.note || payment.notes || ''}"
                                title="Delete Payment">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        }

        displayNoPayments() {
            const tbody = document.getElementById('existingPaymentsTableBody');
            const badge = document.getElementById('payment-count-badge');

            if (badge) {
                badge.textContent = '0';
                badge.className = 'badge bg-secondary ms-1';
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

        handlePaymentSubmit(e) {
            // FIXED: Prevent multiple submissions with additional safeguards
            e.preventDefault();

            // Additional check to prevent double submissions
            if (this.isSubmitting) {
                console.log('Submission already in progress, ignoring duplicate click');
                return;
            }

            // Set submitting flag immediately
            this.isSubmitting = true;

            console.log('Payment form submission started');

            // Add a small delay to ensure the flag is set before proceeding
            setTimeout(() => {
                const form = e.target;
                const submitBtn = document.getElementById('paymentSubmitBtn');

                // Double-check the submitting flag
                if (!submitBtn || submitBtn.disabled) {
                    console.log('Button already disabled, ignoring submission');
                    this.isSubmitting = false;
                    return;
                }

                const originalHtml = submitBtn.innerHTML;
                const isEditing = document.getElementById('payment_id').value !== '';
                const methodOverride = document.getElementById('payment_method_override').value;

                // Validate payment amount
                const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
                const dueAmountText = document.getElementById('payment_due_amount_display').textContent;
                const dueAmount = dueAmountText ? parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0 : 0;

                if (paymentAmount < 0) {
                    this.showToast('Payment amount cannot be negative', 'error');
                    this.isSubmitting = false;
                    return;
                }

                if (paymentAmount > dueAmount + 0) {
                    this.showToast(`Payment amount cannot exceed due amount (৳${dueAmount.toFixed(0)})`, 'error');
                    this.isSubmitting = false;
                    return;
                }

                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
                submitBtn.disabled = true;

                const formData = new FormData(form);

                // Ensure the method override is properly included
                if (isEditing) {
                    formData.set('_method', 'PUT');
                }

                // Get the form action URL
                let formAction = form.action;

                // For editing, we need to make sure we're sending to the right endpoint
                if (isEditing) {
                    const paymentId = document.getElementById('payment_id').value;
                    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
                    formAction = `${baseUrl}/admin/billing/payment/${paymentId}`;
                }

                fetch(formAction, {
                        method: 'POST', // Always use POST, let Laravel handle method spoofing
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Payment submission response:', data);

                        if (data.success) {
                            this.showToast(data.message || 'Operation completed successfully', 'success');

                            const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
                            if (modal) {
                                modal.hide();
                            }

                            setTimeout(() => location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'Operation failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showToast(error.message || 'Network error occurred', 'error');
                    })
                    .finally(() => {
                        this.isSubmitting = false;
                        if (submitBtn) {
                            submitBtn.innerHTML = originalHtml;
                            submitBtn.disabled = false;
                        }
                        console.log('Payment form submission completed');
                    });
            }, 10); // Small delay to ensure flag is properly set
        }
        resetPaymentForm() {
            const form = document.getElementById('addPaymentForm');
            if (form) {
                form.reset();
                const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
                const invoiceId = document.getElementById('payment_invoice_id').value;
                if (invoiceId) {
                    form.action = `${baseUrl}/admin/billing/record-payment/${invoiceId}`;
                }
            }

            // Reset all payment-specific fields
            document.getElementById('payment_id').value = '';
            document.getElementById('payment_method_override').value = 'POST';

            const submitText = document.getElementById('paymentSubmitText');
            const submitBtn = document.getElementById('paymentSubmitBtn');
            if (submitText) submitText.textContent = 'Record Payment';
            if (submitBtn) {
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-success');
            }

            const paymentAmountField = document.getElementById('payment_amount');
            if (paymentAmountField) {
                paymentAmountField.classList.remove('is-invalid');
                paymentAmountField.value = ''; // Clear the value
            }

            const paymentAmountError = document.getElementById('payment_amount_error');
            if (paymentAmountError) {
                paymentAmountError.style.display = 'none';
            }

            const nextDueField = document.getElementById('next_due');
            if (nextDueField) {
                nextDueField.value = '';
            }

            // Reset payment method selection
            const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
            if (paymentMethodSelect) {
                paymentMethodSelect.value = 'cash'; // Reset to default
            }

            // Reset notes textarea
            const notesTextarea = document.querySelector('textarea[name="notes"]');
            if (notesTextarea) {
                notesTextarea.value = '';
            }

            // Reset payment date to today
            const paymentDateInput = document.querySelector('input[name="payment_date"]');
            if (paymentDateInput) {
                paymentDateInput.value = new Date().toISOString().split('T')[0];
            }

            // Reset display fields
            const displayFields = [
                'payment_invoice_number_display',
                'payment_customer_name_display',
                'payment_total_amount_display',
                'payment_due_amount_display',
                'payment_due_amount_helper'
            ];

            displayFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.textContent = '-';
                }
            });

            // Reset status badge
            const statusDisplay = document.getElementById('payment_status_display');
            if (statusDisplay) {
                statusDisplay.textContent = '-';
                statusDisplay.className = 'badge bg-secondary';
            }

            const paymentFormTab = document.getElementById('payment-form-tab');
            if (paymentFormTab) {
                const tab = new bootstrap.Tab(paymentFormTab);
                tab.show();
            }

            // Clear existing payments table
            const existingPaymentsTableBody = document.getElementById('existingPaymentsTableBody');
            if (existingPaymentsTableBody) {
                existingPaymentsTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0 small">No payment history found</p>
                    </td>
                </tr>
            `;
            }

            // Reset payment count badge
            const paymentCountBadge = document.getElementById('payment-count-badge');
            if (paymentCountBadge) {
                paymentCountBadge.textContent = '0';
                paymentCountBadge.className = 'badge bg-secondary ms-1';
            }
        }
        showToast(message, type = 'info') {
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
            } else {
                alert(message);
            }
        }
    }

    // Initialize when DOM is ready
    function initializePaymentModal() {
        // Add debug information
        console.log('Initializing payment modal...');

        if (window.paymentModalInitialized) {
            console.log('Payment modal already initialized, skipping...');
            return;
        }

        try {
            const paymentModal = document.getElementById('addPaymentModal');
            const paymentForm = document.getElementById('addPaymentForm');

            if (!paymentModal || !paymentForm) {
                console.log('Payment modal elements not found, retrying...');
                setTimeout(initializePaymentModal, 100);
                return;
            }

            window.paymentModalInitialized = true;
            window.paymentModalInstance = new PaymentModal();
            console.log('Payment modal initialized successfully');

            // Add a visible indicator that the modal is initialized
            if (typeof jQuery !== 'undefined') {
                console.log('jQuery is available');
            } else {
                console.log('jQuery is NOT available');
            }

            // Dispatch a custom event to notify that the payment modal is ready
            document.dispatchEvent(new CustomEvent('paymentModalReady', {
                detail: {
                    initialized: true
                }
            }));
        } catch (error) {
            console.error('Error initializing payment modal:', error);
            setTimeout(initializePaymentModal, 500);
        }
    } // Initialize only once
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM content loaded, initializing payment modal...');
            initializePaymentModal();
        });
    } else {
        console.log('Document already loaded, initializing payment modal...');
        initializePaymentModal();
    }

    // Additional initialization for cases where DOMContentLoaded might have already fired
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        console.log('Document state is complete/interactive, initializing payment modal immediately...');
        setTimeout(initializePaymentModal, 100);
    }