<div class="invoice-container p-4">
    <!-- Invoice Header -->
    <div class="invoice-header text-center mb-4">
        <div class="company-logo mb-3">
            <h2 class="mb-1 text-primary">Nanosoft</h2>
            <div class="text-muted small">Monthly Billing Invoice</div>
        </div>
        <div class="invoice-meta">
            <h4 class="mb-1">Invoice #{{ $invoice->invoice_number ?? 'N/A' }}</h4>
            <div class="text-muted">
                Issue Date: {{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('F j, Y') : 'N/A' }}
                @php
                $billingCycle = $invoice->customerProduct->billing_cycle_months ?? 1;
                $assignDate = \Carbon\Carbon::parse($invoice->customerProduct->assign_date ?? now());
                $issueDate = \Carbon\Carbon::parse($invoice->issue_date ?? now());
                $monthsDiff = $assignDate->diffInMonths($issueDate);
                $isBillingCycleMonth = ($monthsDiff % $billingCycle == 0);
                @endphp
                <span class="ms-2 badge {{ $isBillingCycleMonth ? 'bg-primary' : 'bg-secondary' }}">
                    {{ $isBillingCycleMonth ? 'Billing Cycle Month' : 'Carry-Forward Month' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Invoice Info Table -->
    <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 25px; background: #f8f9fa; border-radius: 8px; overflow: hidden;">
        <tbody>
            <tr>
                <!-- Bill To Column -->
                <td style="vertical-align: top; padding: 20px; width: 50%; border-right: 1px solid #dee2e6;">
                    <div style="color: #4361ee; font-weight: 600; text-transform: uppercase; font-size: 12px; margin-bottom: 10px; letter-spacing: 0.5px;">
                        <i class="fas fa-user-circle me-2"></i>Bill To:
                    </div>
                    @php
                    $customer = $invoice->customerProduct ? $invoice->customerProduct->customer : null;
                    @endphp
                    <div style="font-size: 18px; font-weight: bold; margin-bottom: 8px; color: #2b2d42;">
                        {{ $customer ? $customer->name : 'N/A' }}
                    </div>
                    <div style="font-size: 13px; line-height: 1.5; color: #6c757d;">
                        <div class="mb-1">
                            <i class="fas fa-id-card me-2 text-primary"></i>
                            <strong>ID:</strong> {{ $customer ? ($customer->customer_id ?? 'N/A') : 'N/A' }}
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <strong>Email:</strong> {{ $customer ? ($customer->email ?? 'N/A') : 'N/A' }}
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            <strong>Phone:</strong> {{ $customer ? ($customer->phone ?? 'N/A') : 'N/A' }}
                        </div>
                        <div>
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            <strong>Address:</strong> {{ $customer ? ($customer->address ?? 'N/A') : 'N/A' }}
                        </div>
                    </div>
                </td>

                <!-- Invoice Details Column -->
                <td style="vertical-align: top; padding: 20px; width: 50%;">
                    <div style="color: #4361ee; font-weight: 600; text-transform: uppercase; font-size: 12px; margin-bottom: 10px; letter-spacing: 0.5px;">
                        <i class="fas fa-file-invoice me-2"></i>Invoice Details:
                    </div>
                    <div style="font-size: 13px; line-height: 1.5; color: #2b2d42;">
                        <div class="mb-1">
                            <i class="fas fa-hashtag me-2 text-primary"></i>
                            <strong>Invoice #:</strong> {{ $invoice->invoice_number ?? 'N/A' }}
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            <strong>Issue Date:</strong> {{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('M j, Y') : 'N/A' }}
                        </div>

                        @if($invoice->customerProduct)
                        @php
                        $assignDate = $invoice->customerProduct->assign_date ? \Carbon\Carbon::parse($invoice->customerProduct->assign_date) : null;
                        $dueDay = $invoice->customerProduct->due_date
                        ? \Carbon\Carbon::parse($invoice->customerProduct->due_date)->day
                        : ($assignDate ? $assignDate->day : 1);
                        $issueMonth = $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date) : \Carbon\Carbon::now();
                        $dueDate = $issueMonth->copy()->day(min($dueDay, $issueMonth->daysInMonth));
                        @endphp
                        <div class="mb-1">
                            <i class="fas fa-calendar-check me-2 text-primary"></i>
                            <strong>Due Date:</strong> {{ $dueDate->format('M j, Y') }}
                        </div>
                        @endif

                        <div class="mb-1">
                            <i class="fas fa-sync-alt me-2 text-primary"></i>
                            <strong>Billing Cycle:</strong>
                            <span class="badge bg-info ms-1">
                                {{ $billingCycle }} month{{ $billingCycle > 1 ? 's' : '' }}
                            </span>
                        </div>

                        <div class="mb-1">
                            <i class="fas fa-cube me-2 text-primary"></i>
                            <strong>Product:</strong> {{ $invoice->customerProduct->product->name ?? 'N/A' }}
                        </div>

                        <div>
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            <strong>Status:</strong>
                            @php
                            $status = $invoice->status ?? 'unknown';
                            switch($status) {
                            case 'paid':
                            $badgeClass = 'bg-success';
                            break;
                            case 'partial':
                            $badgeClass = 'bg-warning';
                            break;
                            case 'confirmed':
                            $badgeClass = 'bg-primary';
                            break;
                            default:
                            $badgeClass = 'bg-danger';
                            }
                            @endphp
                            <span class="badge {{ $badgeClass }} ms-1">
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Charges Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered" style="border: 1px solid #dee2e6; font-size: 14px;">
            <thead class="table-light" style="background-color: #f8f9fa;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6;">Description</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: center;">Cycle</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: right;">Rate</th>
                    <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->customerProduct && $invoice->customerProduct->product)
                @php
                $customerProduct = $invoice->customerProduct;
                $product = $customerProduct->product;
                $monthlyPrice = $product->monthly_price ?? 0;
                $billingCycle = $customerProduct->billing_cycle_months ?? 1;

                // Calculate subtotal based on billing cycle month
                if ($isBillingCycleMonth) {
                $subtotal = $monthlyPrice * $billingCycle;
                } else {
                $subtotal = 0; // No new charges in carry-forward months
                }

                // Get billing cycle text
                $billingCycleText = match($billingCycle) {
                1 => 'Monthly',
                3 => 'Quarterly',
                6 => 'Semi-Annual',
                12 => 'Annual',
                default => "{$billingCycle} Months"
                };
                @endphp

                <!-- Product Charges Row -->
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 12px; vertical-align: middle;">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-box fa-lg text-primary"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 15px; color: #2b2d42;">
                                    {{ $product->name ?? 'Product Service' }}
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted d-block">
                                        <i class="fas fa-calendar-alt me-1"></i>{{ $billingCycleText }} Billing
                                    </small>
                                    @if($customerProduct->due_date)
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-clock me-1"></i>Due Day: {{ \Carbon\Carbon::parse($customerProduct->due_date)->day }}{{ \Carbon\Carbon::parse($customerProduct->due_date)->day == 1 ? 'st' : (\Carbon\Carbon::parse($customerProduct->due_date)->day == 2 ? 'nd' : (\Carbon\Carbon::parse($customerProduct->due_date)->day == 3 ? 'rd' : 'th')) }} of each month
                                    </small>
                                    @endif
                                    @if($invoice->customerProduct->assign_date)
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-calendar-plus me-1"></i>Assigned: {{ \Carbon\Carbon::parse($invoice->customerProduct->assign_date)->format('M j, Y') }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 12px; text-align: center; vertical-align: middle;">
                        <span class="badge bg-info" style="font-size: 12px; padding: 6px 10px;">
                            {{ $billingCycleText }}
                        </span>
                        <div class="mt-1 small text-muted">
                            {{ $isBillingCycleMonth ? 'Cycle Month' : 'Carry-Forward' }}
                        </div>
                    </td>
                    <td style="padding: 12px; text-align: right; vertical-align: middle;">
                        <div style="font-weight: 600; color: #2b2d42;">৳ {{ number_format($monthlyPrice, 2) }}</div>
                        <div class="small text-muted">per month</div>
                    </td>
                    <td style="padding: 12px; text-align: right; vertical-align: middle;">
                        @if($isBillingCycleMonth && $subtotal > 0)
                        <div style="font-size: 16px; font-weight: 700; color: #2b2d42;">
                            ৳ {{ number_format($subtotal, 2) }}
                        </div>
                        <div class="small text-muted">
                            {{ $billingCycle }} month{{ $billingCycle > 1 ? 's' : '' }} × ৳{{ number_format($monthlyPrice, 2) }}
                        </div>
                        @else
                        <div style="color: #6c757d; font-style: italic;">No new charges</div>
                        <div class="small text-muted">Carry-forward month</div>
                        @endif
                    </td>
                </tr>
                @else
                <tr>
                    <td colspan="4" style="padding: 40px; text-align: center; color: #6c757d;">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <div>Product information not available</div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Amounts Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <table class="table table-bordered" style="border: 1px solid #dee2e6; font-size: 14px;">
                <tbody>
                    <!-- Previous Due (if any) -->
                    @if($invoice->previous_due > 0)
                    <tr>
                        <td style="padding: 10px; font-weight: 600; color: #6c757d;">
                            <i class="fas fa-forward me-2 text-warning"></i>Previous Due Amount
                            @if(!$isBillingCycleMonth)
                            <div class="small text-muted mt-1">Carried forward from previous month(s)</div>
                            @endif
                        </td>
                        <td style="padding: 10px; text-align: right; font-weight: 700; color: #f39c12;">
                            ৳ {{ number_format($invoice->previous_due, 2) }}
                        </td>
                    </tr>
                    @endif

                    <!-- New Charges (only in billing cycle months) -->
                    @if($isBillingCycleMonth && $invoice->subtotal > 0)
                    <tr>
                        <td style="padding: 10px; font-weight: 600; color: #6c757d;">
                            <i class="fas fa-plus-circle me-2 text-primary"></i>New Charges
                            <div class="small text-muted mt-1">
                                {{ $billingCycle }} month{{ $billingCycle > 1 ? 's' : '' }} billing cycle
                            </div>
                        </td>
                        <td style="padding: 10px; text-align: right; font-weight: 700; color: #2b2d42;">
                            ৳ {{ number_format($invoice->subtotal, 2) }}
                        </td>
                    </tr>
                    @endif

                    <!-- Subtotal Separator -->
                    <tr style="border-top: 2px solid #dee2e6;">
                        <td style="padding: 10px; font-weight: 700; color: #2b2d42; background-color: #f8f9fa;">
                            <i class="fas fa-calculator me-2"></i>TOTAL AMOUNT
                        </td>
                        <td style="padding: 10px; text-align: right; font-weight: 800; font-size: 16px; color: #2b2d42; background-color: #f8f9fa;">
                            ৳ {{ number_format($invoice->total_amount, 2) }}
                        </td>
                    </tr>

                    <!-- Received Amount -->
                    @if($invoice->received_amount > 0)
                    <tr>
                        <td style="padding: 10px; font-weight: 600; color: #6c757d;">
                            <i class="fas fa-check-circle me-2 text-success"></i>Amount Paid
                            @if($invoice->payments && $invoice->payments->count() > 0)
                            <div class="small text-muted mt-1">
                                {{ $invoice->payments->count() }} payment{{ $invoice->payments->count() > 1 ? 's' : '' }} received
                                @php
                                    // Get the most recent payment
                                    $latestPayment = $invoice->payments->sortByDesc('payment_date')->first();
                                @endphp
                                @if($latestPayment && $latestPayment->payment_date)
                                <br>
                                <small>Latest payment: {{ \Carbon\Carbon::parse($latestPayment->payment_date)->format('M d, Y') }}</small>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td style="padding: 10px; text-align: right; font-weight: 700; color: #27ae60;">
                            ৳ {{ number_format($invoice->received_amount, 2) }}
                        </td>
                    </tr>
                    @endif
                    <!-- Due Amount (Most Important) -->
                    @php
                    $dueAmount = $invoice->total_amount - $invoice->received_amount;
                    $dueAmount = max(0, $dueAmount);
                    $isOverdue = $dueAmount > 0;
                    @endphp
                    <tr class="{{ $isOverdue ? 'overdue-row' : 'paid-row' }}">
                        <td class="due-amount-cell {{ $isOverdue ? 'overdue-text' : 'paid-text' }}">
                            <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-check-circle' }} me-2"></i>
                            AMOUNT DUE
                            @if($isOverdue)
                            <div class="small mt-1 overdue-text">
                                <i class="fas fa-info-circle me-1"></i>
                                This amount will be added to next month's invoice
                            </div>
                            @endif
                        </td>
                        <td class="due-amount-value {{ $isOverdue ? 'overdue-text' : 'paid-text' }}">
                            ৳ {{ number_format($dueAmount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Payment History -->
    <!-- @if($invoice->payments && $invoice->payments->count() > 0)
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white" style="border-bottom: none;">
            <h6 class="mb-0">
                <i class="fas fa-history me-2"></i>Payment History
                <span class="badge bg-light text-primary ms-2">{{ $invoice->payments->count() }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 13px;">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th style="padding: 10px;">Date</th>
                            <th style="padding: 10px;">Method</th>
                            <th style="padding: 10px; text-align: right;">Amount</th>
                            <th style="padding: 10px;">Reference</th>
                            <th style="padding: 10px;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 10px; color: #2b2d42;">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                            </td>
                            <td style="padding: 10px;">
                                @php
                                $methodColors = [
                                'cash' => 'success',
                                'bank_transfer' => 'info',
                                'mobile_banking' => 'primary',
                                'card' => 'warning',
                                'online' => 'danger'
                                ];
                                $methodColor = $methodColors[$payment->payment_method] ?? 'secondary';
                                $methodIcons = [
                                'cash' => 'money-bill-wave',
                                'bank_transfer' => 'university',
                                'mobile_banking' => 'mobile-alt',
                                'card' => 'credit-card',
                                'online' => 'globe'
                                ];
                                $methodIcon = $methodIcons[$payment->payment_method] ?? 'money-bill-wave';
                                @endphp
                                <span class="badge bg-{{ $methodColor }}">
                                    <i class="fas fa-{{ $methodIcon }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                </span>
                            </td>
                            <td style="padding: 10px; text-align: right; font-weight: 600; color: #27ae60;">
                                ৳ {{ number_format($payment->amount, 2) }}
                            </td>
                            <td style="padding: 10px; color: #6c757d; font-family: monospace;">
                                {{ $payment->transaction_id ?? 'N/A' }}
                            </td>
                            <td style="padding: 10px; color: #6c757d;">
                                {{ $payment->note ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif -->

    <!-- Important Notes -->
    <!-- @if($invoice->notes || $dueAmount > 0)
    <div class="alert {{ $dueAmount > 0 ? 'alert-warning' : 'alert-info' }} mb-4">
        <h6 class="alert-heading mb-2" style="font-weight: 600;">
            <i class="fas fa-info-circle me-2"></i>Invoice Notes & Information
        </h6>
        <div style="font-size: 13px;">
            @if($invoice->notes)
            <div class="mb-2">{{ $invoice->notes }}</div>
            @endif
            
            @if($dueAmount > 0)
            <div class="mb-1">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Important:</strong> Unpaid amount of <strong class="text-danger">৳ {{ number_format($dueAmount, 2) }}</strong> will be automatically carried forward to next month's invoice.
            </div>
            @endif
            
            <div class="mt-2">
                <i class="fas fa-calendar-alt me-2"></i>
                <strong>Billing Type:</strong> 
                @if($isBillingCycleMonth)
                <span class="badge bg-primary">Billing Cycle Month</span> - New charges added this month
                @else
                <span class="badge bg-secondary">Carry-Forward Month</span> - No new charges, only previous due
                @endif
            </div>
            
            <div class="mt-2 small text-muted">
                <i class="fas fa-clock me-2"></i>
                Invoice generated on {{ \Carbon\Carbon::parse($invoice->created_at ?? now())->format('F j, Y \a\t g:i A') }}
                @if($invoice->created_by)
                by {{ \App\Models\User::find($invoice->created_by)->name ?? 'System' }}
                @endif
            </div>
        </div>
    </div>
    @endif -->

    <!-- Invoice Footer -->
    <!-- <div class="invoice-footer text-center mt-5 pt-4 border-top">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="small text-muted mb-1">For Support</div>
                <div style="font-weight: 600; color: #2b2d42;">
                    <i class="fas fa-phone me-1 text-primary"></i> +880 XXX-XXXXXXX
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="small text-muted mb-1">Email</div>
                <div style="font-weight: 600; color: #2b2d42;">
                    <i class="fas fa-envelope me-1 text-primary"></i> support@nanosoft.com
                </div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted mb-1">Website</div>
                <div style="font-weight: 600; color: #2b2d42;">
                    <i class="fas fa-globe me-1 text-primary"></i> www.nanosoft.com
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <p class="text-muted small mb-0" style="font-size: 12px; line-height: 1.6;">
                <strong>Thank you for your business!</strong> Please make payment by the due date to avoid late fees. 
                Unpaid amounts will be carried forward to the next billing cycle automatically.
                For any queries regarding this invoice, please contact our support team.
            </p>
        </div>
        
        <div class="mt-3">
            <div class="text-muted" style="font-size: 11px;">
                <i class="fas fa-print me-1"></i>
                This is a computer-generated invoice. No signature required.
            </div>
        </div>
    </div> -->
</div>

<style>
    .invoice-container {
        background: white;
        max-width: 1000px;
        margin: 0 auto;
        font-size: 15px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #2b2d42;
    }

    .invoice-header {
        border-bottom: 2px solid #4361ee;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }

    .company-logo h2 {
        color: #4361ee;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .invoice-meta h4 {
        color: #2b2d42;
        font-weight: 700;
        font-size: 22px;
    }

    .table {
        margin-bottom: 20px;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2b2d42;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 13px;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }

    .table-sm th,
    .table-sm td {
        padding: 8px 10px;
    }

    .badge {
        font-size: 11px;
        padding: 5px 8px;
        border-radius: 4px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .alert {
        border-radius: 8px;
        border: none;
        font-size: 13px;
    }

    .alert-warning {
        background-color: rgba(255, 193, 7, 0.1);
        border-left: 4px solid #ffc107;
    }

    .alert-info {
        background-color: rgba(23, 162, 184, 0.1);
        border-left: 4px solid #17a2b8;
    }

    /* Print Styles */
    @media print {
        .invoice-container {
            max-width: 100%;
            padding: 15px;
            font-size: 14px;
        }

        .invoice-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .invoice-meta h4 {
            font-size: 18px;
        }

        .table-bordered th,
        .table-bordered td {
            padding: 6px;
        }

        .badge {
            font-size: 10px;
            padding: 3px 6px;
        }

        .mb-4 {
            margin-bottom: 15px !important;
        }

        .mt-5 {
            margin-top: 20px !important;
        }

        .alert {
            padding: 10px;
            font-size: 12px;
        }

        /* Hide buttons and unnecessary elements */
        .btn,
        .modal-footer,
        .no-print {
            display: none !important;
        }

        /* Ensure content fits on one page */
        body {
            margin: 0;
            padding: 10px;
            font-size: 12px;
            zoom: 0.85;
        }

        /* Force page breaks */
        .page-break {
            page-break-before: always;
        }

        /* Remove background colors for better printing */
        .table-light,
        .bg-light,
        .bg-primary,
        .bg-success,
        .bg-warning,
        .bg-danger,
        .bg-info {
            background-color: #fff !important;
            color: #000 !important;
        }

        .table-bordered {
            border: 1px solid #000 !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
        }

        /* Ensure text is dark for printing */
        .text-primary,
        .text-success,
        .text-warning,
        .text-danger,
        .text-info {
            color: #000 !important;
        }
    }

    /* Screen Responsive Styles */
    @media screen and (max-width: 768px) {
        .invoice-container {
            padding: 15px;
            font-size: 14px;
        }

        .invoice-header {
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .invoice-meta h4 {
            font-size: 18px;
        }

        .table th,
        .table td {
            padding: 8px;
            font-size: 13px;
        }

        .row.justify-content-end .col-md-6 {
            width: 100%;
        }

        .badge {
            font-size: 10px;
            padding: 4px 6px;
        }

        .alert {
            font-size: 12px;
            padding: 12px;
        }

        .invoice-footer .row {
            flex-direction: column;
        }

        .invoice-footer .col-md-4 {
            margin-bottom: 15px;
        }
    }

    /* Animation for important amounts */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.02);
        }

        100% {
            transform: scale(1);
        }
    }

    tr[style*="background-color: rgba(231, 76, 60, 0.1)"] {
        animation: pulse 2s ease-in-out infinite;
    }

    /* Custom styling for due amount */
    .due-amount-highlight {
        position: relative;
    }

    .due-amount-highlight::after {
        content: "";
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border: 2px solid #e74c3c;
        border-radius: 4px;
        pointer-events: none;
    }

    /* Overdue and paid row styling */
    .overdue-row {
        background-color: rgba(231, 76, 60, 0.1);
        /* animation: pulse 2s ease-in-out infinite; */
    }

    .paid-row {
        background-color: rgba(46, 204, 113, 0.1);
    }

    .overdue-text {
        color: #e74c3c !important;
    }

    .paid-text {
        color: #2ecc71 !important;
    }

    .due-amount-cell,
    .due-amount-value {
        padding: 12px;
        font-weight: 700;
    }

    .due-amount-value {
        text-align: right;
        font-weight: 800;
        font-size: 18px;
    }
</style>