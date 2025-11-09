<!-- resources/views/admin/billing/payment-modal.blade.php -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" id="payment_invoice_id">

                <div class="modal-body">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Invoice Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6"><strong>Invoice Number:</strong></div>
                                <div class="col-6"><span id="payment_invoice_number_display" class="fw-bold text-primary">-</span></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Customer:</strong></div>
                                <div class="col-6"><span id="payment_customer_name_display">-</span></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Due Amount:</strong></div>
                                <div class="col-6"><span id="payment_due_amount_display" class="fw-bold text-danger">৳ 0.00</span></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Total Amount:</strong></div>
                                <div class="col-6"><span id="payment_total_amount_display" class="fw-bold">৳ 0.00</span></div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Amount *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required 
                               id="payment_amount" min="0.01" placeholder="0.00">
                        <div class="form-text">Enter amount received (Max: <span id="payment_max_amount">৳ 0.00</span>)</div>
                        <div class="invalid-feedback" id="payment_amount_error" style="display:none;">
                            Cannot exceed due amount
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Date *</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional payment notes..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>