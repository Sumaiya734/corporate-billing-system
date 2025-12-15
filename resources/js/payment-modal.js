// Payment Modal Functions
/*
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
        $(document).on('show.bs.modal', '#addPaymentModal', (event) => {
            this.handleModalShow(event);
        });

        // Payment amount validation
        $(document).on('input', '#payment_amount', (e) => {
            this.validatePaymentAmount(e.target);
            this.calculateReceivedAndDue(); // Calculate received amount and next due
        });

        // Payment form submission
        $(document).on('submit', '#addPaymentForm', (e) => {
            this.handlePaymentSubmit(e);
        });

        // Reset payment form when modal is hidden
        $(document).on('hidden.bs.modal', '#addPaymentModal', () => {
            this.resetPaymentForm();
        });

        // Handle edit payment button click
        $(document).on('click', '.edit-payment-btn', (e) => {
            e.preventDefault();
            const button = $(e.currentTarget);
            this.handleEditPayment(button);
        });

        // Handle delete payment button click
        $(document).on('click', '.delete-payment-btn', (e) => {
            e.preventDefault();
            const button = $(e.currentTarget);
            this.handleDeletePayment(button);
        });
    }

    handleModalShow(event) {
        const button = $(event.relatedTarget);
        const invoiceId = button.data('invoice-id');

        console.log('Loading payment data for invoice ID:', invoiceId);
        console.log('Button data:', button.data());

        // Check if we have the essential data from the button
        if (button.data('invoice-number') && button.data('customer-name')) {
            // Use button data directly for immediate display
            this.populateFromButtonData(button);
            // Load existing payments
            if (invoiceId) {
                this.loadExistingPayments(invoiceId);
            }
        } else if (invoiceId) {
            // Fetch from database if we only have invoice ID
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
        $('#payment_invoice_number_display').text('Loading...');
        $('#payment_customer_name_display').text('Loading...');
        $('#payment_customer_email_display').text('Loading...');
        $('#payment_customer_phone_display').text('Loading...');
        $('#payment_total_amount_display').text('৳ 0.00');
        $('#payment_due_amount_display').text('৳ 0.00');
        $('#payment_received_amount_display').text('৳ 0.00');
        $('#payment_status_display').text('Loading...');
        $('#payment_status_display').removeClass().addClass('badge bg-secondary');
    }

    async fetchInvoiceData(invoiceId) {
        // Get the base URL from the current page
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
        const invoiceId = button.data('invoice-id');
        const invoiceNumber = button.data('invoice-number');
        const customerName = button.data('customer-name');
        const customerEmail = button.data('customer-email');
        const customerPhone = button.data('customer-phone');
        const subtotal = button.data('subtotal');
        const previousDue = button.data('previous-due');
        const totalAmount = button.data('total-amount');
        const dueAmount = button.data('due-amount');
        const receivedAmount = button.data('received-amount');
        // Get product-specific data
        const productName = button.data('product-name');
        const productPrice = button.data('product-price');
        const billingCycle = button.data('billing-cycle');

        // Display the specific product instead of fetching all products
        if (productName) {
            this.displaySpecificProduct(productName, productPrice, billingCycle);
        } else if (invoiceId) {
            // Fallback to fetching all products if specific product data is not available
            this.fetchAndDisplayProducts(invoiceId);
        }

        const status = button.data('status');

        console.log('Populating from button data:', {
            invoiceId, invoiceNumber, customerName, customerEmail, customerPhone,
            totalAmount, dueAmount, receivedAmount, status, productName, productPrice, billingCycle
        });

        // Set form action and invoice ID
        if (invoiceId) {
            // Get the base URL from meta tag or current origin
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;

            // Ensure we have the correct path without duplication
            let fullPath = `${baseUrl}/admin/billing/record-payment/${invoiceId}`;
            // Remove any duplicate paths
            fullPath = fullPath.replace(/(\/netbill-bd\/public){2,}/g, '/netbill-bd/public');

            $('#addPaymentForm').attr('action', fullPath);
            $('#payment_invoice_id').val(invoiceId);
        }

        // Populate all fields with button data
        $('#payment_invoice_number_display').text(invoiceNumber || 'N/A');
        $('#payment_customer_name_display').text(customerName || 'N/A');
        $('#payment_customer_email_display').text(customerEmail || 'N/A');
        $('#payment_customer_phone_display').text(customerPhone || 'N/A');
        $('#payment_subtotal_display').text('৳ ' + (parseFloat(subtotal) || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 }));
        $('#payment_previous_due_display').text('৳ ' + (parseFloat(previousDue) || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 }));
        $('#payment_total_amount_display').text('৳ ' + (parseFloat(totalAmount) || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 }));
        $('#payment_due_amount_display').text('৳ ' + (parseFloat(dueAmount) || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 }));
        $('#payment_received_amount_display').text('৳ ' + (parseFloat(receivedAmount) || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 }));

        // Set status badge
        const statusDisplay = $('#payment_status_display');
        const statusText = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'N/A';
        statusDisplay.text(statusText);
        statusDisplay.removeClass().addClass('badge');

        switch (status) {
            case 'paid':
                statusDisplay.addClass('bg-success');
                break;
            case 'partial':
                statusDisplay.addClass('bg-warning text-dark');
                break;
            case 'unpaid':
                statusDisplay.addClass('bg-danger');
                break;
            default:
                statusDisplay.addClass('bg-secondary');
        }

        // Set payment amount to total amount by default (not due amount)
        const paymentAmountField = $('#payment_amount');
        const totalAmt = parseFloat(totalAmount) || 0;
        const dueAmt = parseFloat(dueAmount) || 0;
        paymentAmountField.val(totalAmt.toFixed(2));
        paymentAmountField.attr('max', totalAmt);
        paymentAmountField.attr('min', 0);

        // Update max amount display
        $('#payment_max_amount').text('৳ ' + totalAmt.toLocaleString('en-BD', { minimumFractionDigits: 2 }));

        // Reset validation
        paymentAmountField.removeClass('is-invalid');
        $('#payment_amount_error').hide();

        // Calculate initial received amount and next due
        // Use setTimeout to ensure DOM is fully updated before calculating
        setTimeout(() => {
            this.calculateReceivedAndDue();
        }, 0);

        console.log('Payment modal populated from button data');
    }

    populateModal(invoiceId, invoice) {
        // Set the form action with invoice ID
        // Get the base URL from meta tag or current origin
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;

        // Ensure we have the correct path without duplication
        let fullPath = `${baseUrl}/admin/billing/record-payment/${invoiceId}`;
        // Remove any duplicate paths
        fullPath = fullPath.replace(/(\/netbill-bd\/public){2,}/g, '/netbill-bd/public');

        $('#addPaymentForm').attr('action', fullPath);
        $('#payment_invoice_id').val(invoiceId);

        // Format currency values
        const subtotal = parseFloat(invoice.subtotal) || 0;
        const previousDue = parseFloat(invoice.previous_due) || 0;
        const totalAmount = parseFloat(invoice.total_amount) || 0;
        const dueAmount = parseFloat(invoice.next_due) || 0;
        const receivedAmount = parseFloat(invoice.received_amount) || 0;

        // Populate modal fields with REAL database data
        $('#payment_invoice_number_display').text(invoice.invoice_number || 'N/A');
        $('#payment_customer_name_display').text(invoice.customer.name || 'N/A');
        $('#payment_customer_email_display').text(invoice.customer.email || 'N/A');
        $('#payment_customer_phone_display').text(invoice.customer.phone || 'N/A');
        $('#payment_subtotal_display').text('৳ ' + subtotal.toLocaleString('en-BD', { minimumFractionDigits: 2 }));
        $('#payment_previous_due_display').text('৳ ' + previousDue.toLocaleString('en-BD', { minimumFractionDigits: 2 }));
        $('#payment_total_amount_display').text('৳ ' + totalAmount.toLocaleString('en-BD', { minimumFractionDigits: 2 }));
        $('#payment_due_amount_display').text('৳ ' + dueAmount.toLocaleString('en-BD', { minimumFractionDigits: 2 }));
        $('#payment_received_amount_display').text('৳ ' + receivedAmount.toLocaleString('en-BD', { minimumFractionDigits: 2 }));

        // Set status badge with appropriate color
        const statusDisplay = $('#payment_status_display');
        const statusText = invoice.status ? invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1) : 'N/A';
        statusDisplay.text(statusText);
        statusDisplay.removeClass().addClass('badge');

        switch (invoice.status) {
            case 'paid':
                statusDisplay.addClass('bg-success');
                break;
            case 'partial':
                statusDisplay.addClass('bg-warning text-dark');
                break;
            case 'unpaid':
                statusDisplay.addClass('bg-danger');
                break;
            default:
                statusDisplay.addClass('bg-secondary');
        }

        // Set payment amount to total amount by default (not due amount)
        const paymentAmountField = $('#payment_amount');
        paymentAmountField.val(totalAmount.toFixed(2));
        paymentAmountField.attr('max', totalAmount);
        paymentAmountField.attr('min', 0);

        // Update max amount display
        $('#payment_max_amount').text('৳ ' + totalAmount.toLocaleString('en-BD', { minimumFractionDigits: 2 }));

        // Reset validation
        paymentAmountField.removeClass('is-invalid');
        $('#payment_amount_error').hide();

        // Calculate initial received amount and next due
        // Use setTimeout to ensure DOM is fully updated before calculating
        setTimeout(() => {
            this.calculateReceivedAndDue();
        }, 0);

        // Display products - now we'll show a message that this is for all products
        // since we don't know which specific product when loading from database
        this.displayAllProductsMessage(invoice);

        console.log('Payment modal populated with database data');
    }

    fallbackToButtonData(button) {
        this.populateFromButtonData(button);
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

    async fetchAndDisplayProducts(invoiceId) {
        const productsContainer = $('#payment_products_display');
        productsContainer.html('<div class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading products...</div>');

        try {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
            const response = await fetch(`${baseUrl}/admin/billing/invoice/${invoiceId}/data`);

            if (!response.ok) {
                throw new Error('Failed to fetch invoice data');
            }

            const data = await response.json();

            if (data.success && data.invoice) {
                this.displayProducts(data.invoice);
            } else {
                productsContainer.html('<p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>No product information available</p>');
            }
        } catch (error) {
            console.error('Error fetching products:', error);
            productsContainer.html('<p class="text-warning mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Could not load products</p>');
        }
    }

    displayProducts(invoice) {
        const productsContainer = $('#payment_products_display');

        // Check if invoice has customer with products
        if (invoice.customer && invoice.customer.customer && invoice.customer.customer.customerproducts && invoice.customer.customer.customerproducts.length > 0) {
            let productsHtml = '<div class="row g-2">';

            invoice.customer.customer.customerproducts.forEach((customerProduct, index) => {
                if (customerProduct.product) {
                    const product = customerProduct.product;
                    const monthlyPrice = parseFloat(product.monthly_price) || 0;
                    const billingCycle = customerProduct.billing_cycle_months || 1;
                    const totalPrice = monthlyPrice * billingCycle;

                    productsHtml += `
                        <div class="col-12 col-md-6">
                            <div class="product-item p-2 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-dark">
                                            <i class="fas fa-box-open me-1 text-primary"></i>
                                            ${product.name || 'Unknown Product'}
                                        </h6>
                                        <div class="text-muted small">
                                            <div>৳${monthlyPrice.toLocaleString('en-BD', { minimumFractionDigits: 2 })}/month</div>
                                            ${billingCycle > 1 ? `<div><span class="badge bg-info text-white">×${billingCycle} months cycle</span></div>` : ''}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-success">৳${totalPrice.toLocaleString('en-BD', { minimumFractionDigits: 2 })}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });

            productsHtml += '</div>';
            productsContainer.html(productsHtml);
        } else {
            productsContainer.html('<p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>No products assigned to this invoice</p>');
        }
    }

    displaySpecificProduct(productName, productPrice, billingCycle) {
        const productsContainer = $('#payment_products_display');

        if (productName) {
            const monthlyPrice = parseFloat(productPrice) || 0;
            const cycleMonths = parseInt(billingCycle) || 1;
            const totalPrice = monthlyPrice * cycleMonths;

            const productHtml = `
                <div class="row g-2">
                    <div class="col-12">
                        <div class="product-item p-2 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-dark">
                                        <i class="fas fa-box-open me-1 text-primary"></i>
                                        ${productName}
                                    </h6>
                                    <div class="text-muted small">
                                        <div>৳${monthlyPrice.toLocaleString('en-BD', { minimumFractionDigits: 2 })}/month</div>
                                        ${cycleMonths > 1 ? `<div><span class="badge bg-info text-white">×${cycleMonths} months cycle</span></div>` : ''}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">৳${totalPrice.toLocaleString('en-BD', { minimumFractionDigits: 2 })}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            productsContainer.html(productHtml);
        } else {
            productsContainer.html('<p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>No product information available</p>');
        }
    }

    displayAllProductsMessage(invoice) {
        const productsContainer = $('#payment_products_display');
        productsContainer.html('<p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>This payment will be applied to the invoice which includes all customer products.</p>');
    }

    calculateReceivedAndDue() {
        // Get values
        const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        const dueAmountText = $('#payment_due_amount_display').text();
        const dueAmount = parseFloat(dueAmountText.replace(/[^\d.]/g, '')) || 0;
        const receivedAmountText = $('#payment_received_amount_display').text();
        const currentReceivedAmount = parseFloat(receivedAmountText.replace(/[^\d.]/g, '')) || 0;

        // Calculate new values
        const newReceivedAmount = currentReceivedAmount + paymentAmount;
        const nextDue = Math.max(0, dueAmount - paymentAmount);

        // Update the input fields
        $('#received_amount').val(newReceivedAmount.toFixed(2));
        $('#next_due').val(nextDue.toFixed(2));
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

        // Prevent duplicate submissions
        if (this.isSubmitting) {
            console.log('Form already submitting, ignoring duplicate submission');
            return;
        }

        const form = e.target;
        const submitBtn = $(form).find('button[type="submit"]');

        // Double-check button state
        if (submitBtn.prop('disabled')) {
            return;
        }

        const formData = new FormData(form);
        const originalText = submitBtn.html();
        const isEdit = $('#payment_id').val() !== '';
        const method = $('#payment_method_override').val() || 'POST';

        // Validate amount
        if (!this.validatePaymentForm()) {
            return;
        }

        // Set submission flag and disable button immediately
        this.isSubmitting = true;
        submitBtn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...');
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
                const successMessage = isEdit ? 'Payment updated successfully!' : 'Payment recorded successfully!';
                this.showToast(data.message || successMessage, 'success');
                $('#addPaymentModal').modal('hide');
                // Reload page to show updated data
                setTimeout(() => location.reload(), 1500);
            } else {
                const errorMessage = isEdit ? 'Error updating payment!' : 'Error recording payment!';
                this.showToast(data.message || errorMessage, 'error');
                this.isSubmitting = false;
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('Error processing payment. Please try again.', 'error');
            this.isSubmitting = false;
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    }

    resetPaymentForm() {
        const form = $('#addPaymentForm');
        form.trigger('reset');
        form.attr('action', '');

        // Reset submission flag
        this.isSubmitting = false;

        // Re-enable submit button
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', false);
        submitBtn.html('<i class="fas fa-check me-1"></i>Record Payment');

        // Clear display fields
        $('#payment_invoice_number_display').text('-');
        $('#payment_customer_name_display').text('-');
        $('#payment_customer_email_display').text('-');
        $('#payment_customer_phone_display').text('-');
        $('#payment_subtotal_display').text('৳ 0.00');
        $('#payment_previous_due_display').text('৳ 0.00');
        $('#payment_total_amount_display').text('৳ 0.00');
        $('#payment_due_amount_display').text('৳ 0.00');
        $('#payment_products_display').html('<p class="text-muted mb-0">No products</p>');
        $('#payment_received_amount_display').text('৳ 0.00');
        $('#payment_status_display').text('-');
        $('#payment_status_display').removeClass().addClass('badge bg-secondary');

        // Clear input fields
        $('#received_amount').val('');
        $('#next_due').val('');

        // Reset validation
        $('#payment_amount').removeClass('is-invalid');
        $('#payment_amount_error').hide();

        // Reset to add mode
        $('#paymentModalTitle').text('Add Payment');
        $('#paymentSubmitText').text('Record Payment');
        $('#payment_id').val('');
        $('#payment_method_override').val('POST');

        // Reset tabs to payment form
        const paymentFormTab = document.getElementById('payment-form-tab');
        if (paymentFormTab) {
            paymentFormTab.click();
        }
        $('#existing-payments-tab').removeClass('disabled').attr('disabled', false);
        $('#payment-count-badge').hide();

        // Clear existing payments list
        $('#existingPaymentsList').html(`
            <div class="text-center py-5 text-muted">
                <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No payment records found</p>
                <small>Payments will appear here once recorded</small>
            </div>
        `);
    }

    async loadExistingPayments(invoiceId) {
        console.log('Loading existing payments for invoice:', invoiceId);

        const paymentsList = $('#existingPaymentsList');
        const paymentCountBadge = $('#payment-count-badge');

        try {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
            const url = `${baseUrl}/admin/billing/invoice/${invoiceId}/payments`;
            console.log('Fetching payments from:', url);

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Failed to fetch payments');
            }

            const data = await response.json();
            console.log('Payments data received:', data);

            if (data.success && data.payments && data.payments.length > 0) {
                console.log('Found', data.payments.length, 'payments');

                // Update badge count
                paymentCountBadge.text(data.payments.length).show();

                let paymentsHtml = '<table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Date</th><th>Amount</th><th>Method</th><th>Notes</th><th class="text-center">Actions</th></tr></thead><tbody>';

                data.payments.forEach(payment => {
                    const amount = parseFloat(payment.amount) || 0;
                    const paymentDate = payment.payment_date || 'N/A';
                    const method = payment.payment_method || 'N/A';
                    const notes = payment.note || payment.notes || '-';

                    paymentsHtml += `
                        <tr>
                            <td>${paymentDate}</td>
                            <td class="fw-bold text-success">৳${amount.toLocaleString('en-BD', { minimumFractionDigits: 2 })}</td>
                            <td><span class="badge bg-info">${method}</span></td>
                            <td class="text-muted small">${notes}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-payment-btn" 
                                        data-payment-id="${payment.payment_id}"
                                        data-invoice-id="${invoiceId}"
                                        data-amount="${amount}"
                                        data-payment-date="${paymentDate}"
                                        data-payment-method="${method}"
                                        data-notes="${notes}"
                                        title="Edit Payment">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-payment-btn" 
                                        data-payment-id="${payment.payment_id}"
                                        data-invoice-id="${invoiceId}"
                                        data-amount="${amount}"
                                        data-payment-date="${paymentDate}"
                                        data-payment-method="${method}"
                                        title="Delete Payment">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                paymentsHtml += '</tbody></table>';
                paymentsList.html(paymentsHtml);
                console.log('Payments section displayed');
            } else {
                console.log('No payments found or data.success is false');
                paymentCountBadge.hide();
                paymentsList.html(`
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                        <p class="mb-0">No payment records found</p>
                        <small>Payments will appear here once recorded</small>
                    </div>
                `);
            }
        } catch (error) {
            console.error('Error loading payments:', error);
            paymentCountBadge.hide();
            paymentsList.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load payment history. Please try again.
                </div>
            `);
        }
    }

    handleEditPayment(button) {
        console.log('Edit button clicked!', button.data());

        const paymentId = button.data('payment-id');
        const invoiceId = button.data('invoice-id');
        const amount = button.data('amount');
        const paymentDate = button.data('payment-date');
        const paymentMethod = button.data('payment-method');
        const notes = button.data('notes');

        console.log('Payment data:', { paymentId, invoiceId, amount, paymentDate, paymentMethod, notes });

        // Switch modal to edit mode
        $('#paymentModalTitle').text('Edit Payment');
        $('#paymentSubmitText').text('Update Payment');
        $('#payment_id').val(paymentId);
        $('#payment_method_override').val('PUT');

        // Populate form with payment data
        $('#payment_amount').val(amount);
        $('select[name="payment_method"]').val(paymentMethod);
        $('input[name="payment_date"]').val(paymentDate);
        $('textarea[name="notes"]').val(notes);

        // Update form action for editing
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
        $('#addPaymentForm').attr('action', `${baseUrl}/admin/billing/payment/${paymentId}`);

        console.log('Form action set to:', $('#addPaymentForm').attr('action'));
        console.log('Modal title:', $('#paymentModalTitle').text());

        // Recalculate
        this.calculateReceivedAndDue();

        // Switch to payment form tab and disable existing payments tab during edit
        const paymentFormTab = document.getElementById('payment-form-tab');
        if (paymentFormTab) {
            paymentFormTab.click();
        }
        $('#existing-payments-tab').addClass('disabled').attr('disabled', true);

        console.log('Edit mode activated successfully');
    }

    handleDeletePayment(button) {
        console.log('handleDeletePayment called');

        const paymentId = button.data('payment-id');
        const invoiceId = button.data('invoice-id');
        const amount = button.data('amount');
        const paymentDate = button.data('payment-date');
        const paymentMethod = button.data('payment-method');

        console.log('Delete payment clicked:', { paymentId, invoiceId, amount, paymentDate, paymentMethod });

        // Validate data
        if (!paymentId || !invoiceId) {
            console.error('Missing payment ID or invoice ID');
            alert('Error: Missing payment information. Please refresh the page and try again.');
            return;
        }

        // Store data globally for the modal
        window.deletePaymentData = {
            paymentId: paymentId,
            invoiceId: invoiceId,
            amount: amount,
            paymentDate: paymentDate,
            paymentMethod: paymentMethod,
            button: button
        };

        console.log('Stored delete payment data:', window.deletePaymentData);

        // Update modal content
        try {
            const formattedAmount = parseFloat(amount || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 });
            $('#delete_payment_amount').text('৳ ' + formattedAmount);
            $('#delete_payment_date').text(paymentDate || 'N/A');
            $('#delete_payment_method').text(paymentMethod || 'N/A');
            console.log('Modal content updated');
        } catch (error) {
            console.error('Error updating modal content:', error);
        }

        // Check if modal exists
        const modalElement = document.getElementById('deletePaymentModal');
        if (!modalElement) {
            console.error('Delete payment modal not found in DOM!');
            alert('Error: Delete confirmation modal not found. Please refresh the page.');
            return;
        }

        console.log('Modal element found:', modalElement);

        // Check if Bootstrap is available
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded!');
            alert('Error: Bootstrap library not loaded. Please refresh the page.');
            return;
        }

        console.log('Showing delete payment modal...');

        try {
            // Show modal using Bootstrap 5
            const deleteModal = new bootstrap.Modal(modalElement);
            deleteModal.show();
            console.log('Modal show() called successfully');
        } catch (error) {
            console.error('Error showing modal:', error);
            alert('Error showing modal: ' + error.message);
        }
    }

    showToast(message, type = 'info') {
        // Remove existing toasts
        $('.toast').remove();

        const toastClass = type === 'error' ? 'alert-danger' :
            type === 'success' ? 'alert-success' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

        const iconClass = type === 'success' ? 'fa-check' :
            type === 'error' ? 'fa-exclamation-triangle' :
                type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle';

        const toast = $(`
            <div class="alert ${toastClass} alert-dismissible fade show toast" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            // Check if toast still exists in DOM before trying to fade it out
            if (toast && toast.length > 0 && document.body.contains(toast[0])) {
                toast.fadeOut(300, function () {
                    // Use a more robust check for the element's existence
                    if (this && document.body.contains(this)) {
                        $(this).remove();
                    }
                });
            }
        }, 5000);
    }
}

// Initialize payment modal when document is ready
document.addEventListener('DOMContentLoaded', function () {
    // DISABLED: Not using this implementation
    // new PaymentModal();
});
*/
console.log('Payment modal JS file loaded but functionality is implemented inline in the blade template');

// Global function to execute payment deletion from modal
/* window.executeDeletePayment = async function () {
    console.log('executeDeletePayment called');

    if (!window.deletePaymentData) {
        console.error('No delete payment data found');
        alert('Error: No payment data found. Please try again.');
        return;
    }

    console.log('Delete payment data:', window.deletePaymentData);

    const { paymentId, invoiceId, amount, button } = window.deletePaymentData;

    // Show loading state on modal button
    const confirmBtn = document.getElementById('confirmDeletePaymentBtn');
    const originalBtnHtml = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
    confirmBtn.disabled = true;

    // Also update the table button
    const originalHtml = button.html();
    button.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);

    try {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || window.location.origin;
        const response = await fetch(`${baseUrl}/admin/billing/payment/${paymentId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to delete payment');
        }

        const data = await response.json();

        if (data.success) {
            // Close modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePaymentModal'));
            deleteModal.hide();

            // Show success toast
            const paymentModal = document.querySelector('.payment-modal');
            if (paymentModal && paymentModal.showToast) {
                paymentModal.showToast(data.message || 'Payment deleted successfully!', 'success');
            } else {
                // Fallback toast
                showToast('Payment Deleted Successfully!', 'success', `Payment of ৳${parseFloat(amount).toLocaleString('en-BD', { minimumFractionDigits: 2 })} has been removed.`);
            }

            // Reload page after a short delay
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Error deleting payment!');
        }
    } catch (error) {
        console.error('Error:', error);

        // Show error toast
        if (window.showToast) {
            showToast('Error', error.message || 'Error deleting payment. Please try again.', 'danger');
        } else {
            alert('Error: ' + (error.message || 'Error deleting payment. Please try again.'));
        }

        // Restore buttons
        confirmBtn.innerHTML = originalBtnHtml;
        confirmBtn.disabled = false;
        button.html(originalHtml).prop('disabled', false);
    }
}; */

// Reset delete modal on close
/* $(document).on('hidden.bs.modal', '#deletePaymentModal', function () {
    const confirmBtn = document.getElementById('confirmDeletePaymentBtn');
    if (confirmBtn) {
        confirmBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete Payment';
        confirmBtn.disabled = false;
    }
    window.deletePaymentData = null;
}); */