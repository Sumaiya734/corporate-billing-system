<!-- resources/views/admin/billing/payment-modal.blade.php -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" id="payment_invoice_id">
                <input type="hidden" name="cp_id" id="payment_cp_id">

                <div class="modal-body">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-file-invoice me-2"></i>Invoice Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Responsive grid for invoice information -->
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-nowrap">Invoice Number:</span>
                                        <span id="payment_invoice_number_display" class="text-primary fw-bold text-break">-</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-nowrap">Status:</span>
                                        <span id="payment_status_display" class="badge bg-secondary text-break">-</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-nowrap">Customer:</span>
                                        <span id="payment_customer_name_display" class="fw-bold text-break text-end">-</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-nowrap">Email:</span>
                                        <span id="payment_customer_email_display" class="text-break text-end text-muted">-</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-nowrap">Phone:</span>
                                        <span id="payment_customer_phone_display" class="text-break text-end text-muted">-</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-nowrap">Subtotal:</span>
                                        <span id="payment_subtotal_display" class="fw-bold text-primary text-break">৳ 0.00</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-nowrap">Previous Due:</span>
                                        <span id="payment_previous_due_display" class="fw-bold text-warning text-break">৳ 0.00</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-nowrap">Total Amount:</span>
                                        <span id="payment_total_amount_display" class="fw-bold text-success text-break">৳ 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Amount * <small class="text-muted">(any amount from ৳0.01 to due)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" name="amount" class="form-control" required 
                                           id="payment_amount" min="0.01" placeholder="Enter any amount">
                                </div>
                                <div class="form-text">
                                    Due Amount: <span id="payment_due_amount_display" class="fw-bold text-danger">৳ 0.00</span>
                                    <span id="payment_amount_helper" class="ms-2"></span>
                                </div>
                                <div class="invalid-feedback" id="payment_amount_error" style="display:none;">
                                    Cannot exceed due amount
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Next Due <small class="text-muted">(after payment)</small></label>
                                <input type="number" step="0.01" name="next_due" class="form-control" 
                                       id="next_due" min="0" placeholder="0.00" readonly>
                                <div class="form-text">Remaining amount after this payment</div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Method *</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Select Method</option>
                                    <option value="cash" selected>Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_banking">Mobile Banking</option>
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="online">Online Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional payment notes..."></textarea>
                    </div>

                    <!-- Existing Payments Section -->
                    <div id="existingPaymentsSection" style="display: none;">
                        <hr class="my-4">
                        <h6 class="mb-3">
                            <i class="fas fa-history me-2"></i>Previous Payments
                            <small class="text-muted">(Click delete to remove wrong payments)</small>
                        </h6>
                        <div id="existingPaymentsList" class="table-responsive">
                            <!-- Payments will be loaded here -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>