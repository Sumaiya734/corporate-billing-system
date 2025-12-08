<div class="invoice-container p-4">
    <div class="invoice-header text-center mb-4">
        <h2 class="mb-1">Nanosoft Billing - Invoice</h2>
        <p class="text-muted mb-0">Invoice #{{ $invoice->invoice_number ?? 'N/A' }}</p>
        <p class="text-muted small">Issue Date: {{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('F j, Y') : 'N/A' }}</p>
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">
        <tbody>
            <tr>
                <!-- Bill To Column -->
                <td style="vertical-align: top; padding-right: 15px; width: 50%;">
                    <div style="color: #6c757d; text-transform: uppercase; font-size: 12px; margin-bottom: 5px;">Bill To:</div>
                    @php
                        $customer = $invoice->customerProduct ? $invoice->customerProduct->customer : null;
                    @endphp
                    <div style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">
                        {{ $customer ? $customer->name : 'N/A' }}
                    </div>
                    <div style="font-size: 12px; line-height: 1.4;">
                        <strong>ID:</strong> {{ $customer ? ($customer->customer_id ?? 'N/A') : 'N/A' }}<br>
                        <strong>Email:</strong> {{ $customer ? ($customer->email ?? 'N/A') : 'N/A' }}<br>
                        <strong>Phone:</strong> {{ $customer ? ($customer->phone ?? 'N/A') : 'N/A' }}<br>
                        <strong>Address:</strong> {{ $customer ? ($customer->address ?? 'N/A') : 'N/A' }}
                    </div>
                </td>

                <!-- Invoice Details Column -->
                <td style="vertical-align: top; text-align: right; width: 50%;">
                    <div style="color: #6c757d; text-transform: uppercase; font-size: 12px; margin-bottom: 5px;">Invoice Details:</div>
                    <div style="font-size: 12px; line-height: 1.4;">
                        <strong>Invoice #:</strong> {{ $invoice->invoice_number ?? 'N/A' }}<br>
                        <strong>Issue Date:</strong> {{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('M j, Y') : 'N/A' }}<br>

                        @if($invoice->customerProduct)
                            @php
                                $assignDate = $invoice->customerProduct->assign_date ? \Carbon\Carbon::parse($invoice->customerProduct->assign_date) : null;
                                $dueDay = $invoice->customerProduct->due_date 
                                    ? \Carbon\Carbon::parse($invoice->customerProduct->due_date)->day 
                                    : ($assignDate ? $assignDate->day : 1);
                                $issueMonth = $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date) : \Carbon\Carbon::now();
                                $dueDate = $issueMonth->copy()->day(min($dueDay, $issueMonth->daysInMonth));
                            @endphp
                            <strong>Due Date:</strong> {{ $dueDate->format('M j, Y') }}<br>
                        @endif

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
                        
                        // Check if custom price is used
                        $customPrice = $customerProduct->custom_price ?? null;
                        $standardPrice = $monthlyPrice * $billingCycle;
                        $isCustomPrice = $customPrice && abs($customPrice - $standardPrice) > 0.01;
                        
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
                                {{ $billingCycle }} {{ $billingCycleText }}
                            </span>
                        </td>
                        <td class="text-end align-middle">
                            @if($isCustomPrice)
                                <span class="badge bg-warning text-dark">Custom</span>
                                <br>
                                <small class="text-muted">/{{ $billingCycle }} month{{ $billingCycle > 1 ? 's' : '' }}</small>
                            @else
                                <strong>৳ {{ number_format($monthlyPrice, 2) }}</strong>
                                <br>
                                <small class="text-muted">/month</small>
                            @endif
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
                @if(isset($invoice->previous_due) && $invoice->previous_due > 0)
                <tr>
                    <td colspan="3" class="text-end"><strong>Previous Due:</strong></td>
                    <td class="text-end text-warning"><strong>৳ {{ number_format($invoice->previous_due, 2) }}</strong></td>
                </tr>
                @endif

                <tr class="table-light">
                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                    <td class="text-end"><strong>৳ {{ number_format($invoice->total_amount ?? 0, 2) }}</strong></td>
                </tr>

                @if(isset($invoice->received_amount) && $invoice->received_amount > 0)
                <tr>
                    <td colspan="3" class="text-end"><strong>Received Amount:</strong></td>
                    <td class="text-end text-success"><strong>৳ {{ number_format($invoice->received_amount, 2) }}</strong></td>
                </tr>
                @endif

                {{-- Always show Due Amount (calculated) --}}
                @php
                    $dueAmount = ($invoice->total_amount ?? 0) - ($invoice->received_amount ?? 0);
                    $dueAmount = max(0, $dueAmount); // Ensure it's not negative
                @endphp
                <tr class="{{ $dueAmount > 0 ? 'table-warning' : '' }}">
                    <td colspan="3" class="text-end"><strong>Amount Due:</strong></td>
                    <td class="text-end {{ $dueAmount > 0 ? 'text-danger' : 'text-muted' }}">
                        <strong>৳ {{ number_format($dueAmount, 2) }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

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