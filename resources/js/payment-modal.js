// Payment Modal Functions
class PaymentModal {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Handle payment modal show event
        $(document).on('show.bs.modal', '#addPaymentModal', (event) => {
            this.handleModalShow(event);
        });

        // Payment amount validation
        $(document).on('input', '#payment_amount', (e) => {
            this.validatePaymentAmount(e.target);
        });

        // Payment form submission
        $(document).on('submit', '#addPaymentForm', (e) => {
            this.handlePaymentSubmit(e);
        });

        // Reset payment form when modal is hidden
        $(document).on('hidden.bs.modal', '#addPaymentModal', () => {
            this.resetPaymentForm();
        });
    }

    handleModalShow(event) {
        const button = $(event.relatedTarget);
        const invoiceId = button.data('invoice-id');
        
        console.log('Loading payment data for invoice ID:', invoiceId);
        
        // Show loading state
        this.setLoadingState();
        
        // Fetch invoice data directly from database
        this.fetchInvoiceData(invoiceId)
            .then(invoice => {
                console.log('Invoice data loaded from database:', invoice);
                this.populateModal(invoiceId, invoice);
            })
            .catch(error => {
                console.error('Error fetching invoice data from database:', error);
                this.showToast('Error loading invoice data from database. Please try again.', 'error');
                this.fallbackToButtonData(button);
            });
    }

    setLoadingState() {
        $('#payment_invoice_number_display').text('Loading...');
        $('#payment_customer_name_display').text('Loading...');
        $('#payment_total_amount_display').text('Loading...');
        $('#payment_due_amount_display').text('Loading...');
    }

    async fetchInvoiceData(invoiceId) {
        const response = await fetch(`/admin/billing/invoice/${invoiceId}/data`);
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

    populateModal(invoiceId, invoice) {
        // Set the form action with invoice ID
        $('#addPaymentForm').attr('action', `/admin/billing/record-payment/${invoiceId}`);

        // Populate modal fields with REAL database data
        $('#payment_invoice_number_display').text(invoice.invoice_number || 'N/A');
        $('#payment_customer_name_display').text(invoice.customer.name || 'N/A');
        $('#payment_total_amount_display').text('৳ ' + (parseFloat(invoice.total_amount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2}));
        $('#payment_due_amount_display').text('৳ ' + (parseFloat(invoice.next_due) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2}));
        
        // Set payment amount to due amount by default
        const paymentAmountField = $('#payment_amount');
        const dueAmount = parseFloat(invoice.next_due) || 0;
        paymentAmountField.val(dueAmount.toFixed(2));
        paymentAmountField.attr('max', dueAmount);
        paymentAmountField.attr('min', 0.01);
        
        // Reset validation
        paymentAmountField.removeClass('is-invalid');
        $('#payment_amount_error').hide();
        
        console.log('Payment modal populated with database data');
    }

    fallbackToButtonData(button) {
        const invoiceNumber = button.data('invoice-number');
        const customerName = button.data('customer-name');
        const totalAmount = button.data('total-amount');
        const dueAmount = button.data('due-amount');
        
        $('#payment_invoice_number_display').text(invoiceNumber || 'Error');
        $('#payment_customer_name_display').text(customerName || 'Error');
        $('#payment_total_amount_display').text('৳ ' + (parseFloat(totalAmount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2}));
        $('#payment_due_amount_display').text('৳ ' + (parseFloat(dueAmount) || 0).toLocaleString('en-BD', {minimumFractionDigits: 2}));
    }

    validatePaymentAmount(input) {
        const paymentAmount = parseFloat($(input).val()) || 0;
        const dueAmountText = $('#payment_due_amount_display').text();
        const dueAmount = parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0;
        
        if (paymentAmount > dueAmount) {
            $(input).addClass('is-invalid');
            $('#payment_amount_error').show();
        } else {
            $(input).removeClass('is-invalid');
            $('#payment_amount_error').hide();
        }
    }

    validatePaymentForm() {
        const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        const dueAmountText = $('#payment_due_amount_display').text();
        const dueAmount = parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0;
        
        if (paymentAmount > dueAmount) {
            this.showToast('Payment amount cannot exceed due amount!', 'error');
            return false;
        }
        
        if (paymentAmount <= 0) {
            this.showToast('Payment amount must be greater than 0!', 'error');
            return false;
        }

        return true;
    }

    async handlePaymentSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = $(form).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Validate amount
        if (!this.validatePaymentForm()) {
            return;
        }

        // Show loading
        submitBtn.html('<div class="loading-spinner"></div> Processing...');
        submitBtn.prop('disabled', true);
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.success) {
                this.showToast(data.message || 'Payment recorded successfully!', 'success');
                $('#addPaymentModal').modal('hide');
                // Reload page to show updated data
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showToast(data.message || 'Error recording payment!', 'error');
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('Error processing payment. Please try again.', 'error');
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    }

    resetPaymentForm() {
        const form = $('#addPaymentForm');
        form.trigger('reset');
        form.attr('action', '');
        
        // Clear display fields
        $('#payment_invoice_number_display').text('-');
        $('#payment_customer_name_display').text('-');
        $('#payment_total_amount_display').text('৳ 0.00');
        $('#payment_due_amount_display').text('৳ 0.00');
        
        // Reset validation
        $('#payment_amount').removeClass('is-invalid');
        $('#payment_amount_error').hide();
    }

    showToast(message, type = 'info') {
        // Remove existing toasts
        $('.toast').remove();
        
        const toast = $(`
            <div class="alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show toast">
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
}

// Initialize payment modal when document is ready
document.addEventListener('DOMContentLoaded', function() {
    new PaymentModal();
});