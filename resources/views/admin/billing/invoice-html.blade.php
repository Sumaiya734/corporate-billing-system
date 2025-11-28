<div class="invoice-container p-4">
    <div class="invoice-header text-center mb-4">
        <h2 class="mb-1">Nanosoft Billing - Invoice</h2>
        <p class="text-muted mb-0">Invoice #{{ $invoice->invoice_number }}</p>
        <p class="text-muted small">Issue Date: {{ \Carbon\Carbon::parse($invoice->issue_date)->format('F j, Y') }}</p>
        
        <!-- @if($invoice->customerProduct && $invoice->customerProduct->product)
            <div class="alert alert-info mt-3 mb-0" style="background-color: #e7f3ff; border-color: #b3d9ff;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Product:</strong> {{ $invoice->customerProduct->product->name }}
                <span class="badge bg-primary ms-2">
                    {{ match($invoice->customerProduct->billing_cycle_months ?? 1) {
                        1 => 'Monthly',
                        3 => 'Quarterly',
                        6 => 'Semi-Annual',
                        12 => 'Annual',
                        default => ($invoice->customerProduct->billing_cycle_months ?? 1) . '-Month'
                    } }}
                </span>
            </div>
        @endif -->
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">
        <tbody>
            <tr>
                <!-- Bill To Column -->
                <td style="vertical-align: top; padding-right: 15px; width: 50%;">
                    <div style="color: #6c757d; text-transform: uppercase; font-size: 12px; margin-bottom: 5px;">Bill To:</div>
                    @php
                        $customer = $invoice->customerProduct->customer ?? null;
                    @endphp
                    <div style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">
                        {{ $customer->name ?? 'N/A' }}
                    </div>
                    <div style="font-size: 12px; line-height: 1.4;">
                        <strong>ID:</strong> {{ $customer->customer_id ?? 'N/A' }}<br>
                        <strong>Email:</strong> {{ $customer->email ?? 'N/A' }}<br>
                        <strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }}<br>
                        <strong>Address:</strong> {{ $customer->address ?? 'N/A' }}
                    </div>
                </td>

                <!-- Invoice Details Column -->
                <td style="vertical-align: top; text-align: right; width: 50%;">
                    <div style="color: #6c757d; text-transform: uppercase; font-size: 12px; margin-bottom: 5px;">Invoice Details:</div>
                    <div style="font-size: 12px; line-height: 1.4;">
                        <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Issue Date:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('M j, Y') }}<br>

                        @if($invoice->customerProduct)
                            @php
                                $assignDate = \Carbon\Carbon::parse($invoice->customerProduct->assign_date);
                                $dueDay = $invoice->customerProduct->due_date 
                                    ? \Carbon\Carbon::parse($invoice->customerProduct->due_date)->day 
                                    : $assignDate->day;
                                $issueMonth = \Carbon\Carbon::parse($invoice->issue_date);
                                $dueDate = $issueMonth->copy()->day(min($dueDay, $issueMonth->daysInMonth));
                            @endphp
                            <strong>Due Date:</strong> {{ $dueDate->format('M j, Y') }}<br>
                        @endif

                        <strong>Status:</strong>
                        @php
                            $status = $invoice->status;
                            switch($status) {
                                case 'paid':
                                    $badgeClass = 'bg-success';
                                    break;
                                case 'partial':
                                    $badgeClass = 'bg-warning';
                                    break;
                                default:
                                    $badgeClass = 'bg-danger';
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }} ms-1">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Product/Service</th>
                    <th class="text-center">Cycle</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if($invoice->customerProduct && $invoice->customerProduct->product)
                    @php
                        $customerProduct = $invoice->customerProduct;
                        $product = $customerProduct->product;
                        $monthlyPrice = $product->monthly_price ?? 0;
                        $billingCycle = $customerProduct->billing_cycle_months ?? 1;
                        $amount = $invoice->subtotal ?? ($monthlyPrice * $billingCycle);
                        
                        // Get billing cycle text
                        $billingCycleText = match($billingCycle) {
                            1 => 'Monthly',
                            3 => 'Quarterly',
                            6 => 'Semi-Annual',
                            12 => 'Annual',
                            default => "{$billingCycle}-Month"
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <i class="fas fa-box text-primary"></i>
                                </div>
                                <div>
                                    <strong class="d-block" style="font-size: 1.1em;">{{ $product->name ?? 'Unknown Product' }}</strong>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-calendar-alt me-1"></i>{{ $billingCycleText }}
                                    </small>
                                    @if($customerProduct->due_date)
                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock me-1"></i>Due: {{ \Carbon\Carbon::parse($customerProduct->due_date)->day }}{{ \Carbon\Carbon::parse($customerProduct->due_date)->day == 1 ? 'st' : (\Carbon\Carbon::parse($customerProduct->due_date)->day == 2 ? 'nd' : (\Carbon\Carbon::parse($customerProduct->due_date)->day == 3 ? 'rd' : 'th')) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge bg-info" style="font-size: 0.9em;">
                                {{ $billingCycle }}M
                            </span>
                        </td>
                        <td class="text-end align-middle">
                            <strong>৳ {{ number_format($monthlyPrice, 2) }}</strong>
                            <br>
                            <small class="text-muted">/month</small>
                        </td>
                        <td class="text-end align-middle">
                            <strong style="font-size: 1.1em;">৳ {{ number_format($amount, 2) }}</strong>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                            <br>
                            Product info not available
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                @if(($invoice->previous_due ?? 0) > 0)
                <tr>
                    <td colspan="3" class="text-end"><strong>Previous Due:</strong></td>
                    <td class="text-end text-warning"><strong>৳ {{ number_format($invoice->previous_due, 2) }}</strong></td>
                </tr>
                @endif
                <tr class="table-light">
                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                    <td class="text-end"><strong>৳ {{ number_format($invoice->total_amount ?? 0, 2) }}</strong></td>
                </tr>
                @if(($invoice->received_amount ?? 0) > 0)
                <tr>
                    <td colspan="3" class="text-end"><strong>Received Amount:</strong></td>
                    <td class="text-end text-success"><strong>৳ {{ number_format($invoice->received_amount, 2) }}</strong></td>
                </tr>
                @endif
                @if(($invoice->next_due ?? 0) > 0)
                <tr class="table-warning">
                    <td colspan="3" class="text-end"><strong>Amount Due:</strong></td>
                    <td class="text-end text-danger"><strong>৳ {{ number_format($invoice->next_due, 2) }}</strong></td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>

    @if($invoice->payments && $invoice->payments->count() > 0)
    <div class="payment-history mb-4">
        <h6 class="text-uppercase text-muted mb-3" style="font-size: 14px;">
            <i class="fas fa-history me-2"></i>Recent Payment
            @if($invoice->customerProduct && $invoice->customerProduct->product)
                <small class="text-muted">(for {{ $invoice->customerProduct->product->name }})</small>
            @endif
        </h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="font-size: 13px;"><i class="fas fa-calendar me-1"></i>Date</th>
                        <th style="font-size: 13px;"><i class="fas fa-money-bill-wave me-1"></i>Amount</th>
                        <th style="font-size: 13px;"><i class="fas fa-credit-card me-1"></i>Method</th>
                        <th style="font-size: 13px;"><i class="fas fa-sticky-note me-1"></i>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $latestPayment = $invoice->payments->sortByDesc('payment_date')->first();
                    @endphp
                    <tr>
                        <td style="font-size: 13px;">{{ \Carbon\Carbon::parse($latestPayment->payment_date)->format('M j, Y') }}</td>
                        <td style="font-size: 13px;"><strong class="text-success">৳ {{ number_format($latestPayment->amount, 2) }}</strong></td>
                        <td style="font-size: 13px;">
                            <span class="badge bg-secondary" style="font-size: 11px;">{{ ucfirst($latestPayment->payment_method) }}</span>
                        </td>
                        <td style="font-size: 13px;">{{ \Illuminate\Support\Str::limit($latestPayment->note ?? '-', 20) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($invoice->customerProduct && $invoice->customerProduct->product)
    <div class="alert alert-light border mb-4 p-3">
        <table class="table table-bordered table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th colspan="2" class="py-2">
                        <h6 class="mb-0" style="font-size: 15px;"><i class="fas fa-info-circle text-info me-2"></i>Invoice Information</h6>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="50%" class="py-2 px-3">
                        <p class="small mb-2"><strong>Product:</strong> {{ \Illuminate\Support\Str::limit($invoice->customerProduct->product->name, 30) }}</p>
                        <p class="small mb-2"><strong>Cycle:</strong> 
                            {{ match($invoice->customerProduct->billing_cycle_months ?? 1) {
                                1 => 'Monthly',
                                3 => 'Quarterly',
                                6 => 'Semi-Annual',
                                12 => 'Annual',
                                default => ($invoice->customerProduct->billing_cycle_months ?? 1) . ' Months'
                            } }}
                        </p>
                        <p class="small mb-0"><strong>Assigned:</strong> {{ \Carbon\Carbon::parse($invoice->customerProduct->assign_date)->format('M j, Y') }}</p>
                    </td>
                    <td width="50%" class="py-2 px-3">
                        <p class="small mb-2"><strong>Charges:</strong> ৳{{ number_format($invoice->subtotal ?? 0, 2) }}</p>
                        @if(($invoice->previous_due ?? 0) > 0)
                        <p class="small mb-2"><strong>Prev Due:</strong> <span class="text-warning">৳{{ number_format($invoice->previous_due, 2) }}</span></p>
                        @endif
                        <p class="small mb-2"><strong>Total:</strong> <span class="text-primary">৳{{ number_format($invoice->total_amount ?? 0, 2) }}</span></p>
                        @if(($invoice->received_amount ?? 0) > 0)
                        <p class="small mb-2"><strong>Paid:</strong> <span class="text-success">৳{{ number_format($invoice->received_amount, 2) }}</span></p>
                        @endif
                        @if(($invoice->next_due ?? 0) > 0)
                        <p class="small mb-0"><strong>Due:</strong> <span class="text-danger">৳{{ number_format($invoice->next_due, 2) }}</span></p>
                        @else
                        <p class="small mb-0"><strong>Status:</strong> <span class="badge bg-success" style="font-size: 11px;">Paid</span></p>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <div class="invoice-footer text-center mt-4 pt-3 border-top">
        <p class="text-muted small mb-0" style="font-size: 12px;">Thank you for your business! For any queries, please contact us.</p>
    </div>
</div>

<style>
    .invoice-container {
        background: white;
        max-width: 900px;
        margin: 0 auto;
        font-size: 16px;
    }
    
    .invoice-header h2 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 8px;
        font-size: 28px;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
        padding: 10px;
    }
    
    .table-sm th,
    .table-sm td {
        padding: 7px;
    }
    
    .small {
        font-size: 14px;
    }
    
    .badge {
        font-size: 12px;
        padding: 5px 8px;
    }
    
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
        
        .table-bordered th,
        .table-bordered td {
            padding: 6px;
        }
        
        .small {
            font-size: 12px;
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
        
        .btn, .modal-footer {
            display: none !important;
        }
        
        /* Ensure content fits on one page */
        body {
            zoom: 0.9;
        }
    }
    
    @media screen and (max-width: 768px) {
        .invoice-container {
            padding: 20px;
            font-size: 15px;
        }
    }
</style>
