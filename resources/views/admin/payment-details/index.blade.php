@extends('layouts.admin')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold text-primary mb-2">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Payment Details & History
                </h1>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Search customers and view their complete payment history across all products
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                    <button type="button" class="btn btn-outline-secondary">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-search-dollar me-2"></i>Search & Filter Payments
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.payment-details.index') }}" method="GET" class="row g-3" id="searchForm">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fas fa-user me-1"></i>Search Customer
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text"
                                       name="search"
                                       class="form-control border-start-0"
                                       placeholder="Name, ID, Phone, Invoice or Email..."
                                       value="{{ request('search') }}"
                                       id="customerSearch">
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-lightbulb me-1"></i>
                                Search for customers first, then filter by their products
                            </small>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fas fa-box me-1"></i>Filter by Product
                            </label>
                            <select name="product_id" class="form-select" id="productFilter">
                                <option value="all" {{ request('product_id') == 'all' ? 'selected' : '' }}>
                                    📦 All Products
                                </option>

                                @if($search && $customerProducts->count() > 0)
                                    <!-- Show only customer's products when search is active -->
                                    <optgroup label="🎯 Customer's Products">
                                        @foreach($customerProducts as $product)
                                        <option value="{{ $product->p_id }}"
                                                {{ request('product_id') == $product->p_id ? 'selected' : '' }}>
                                            {{ $product->name }} (৳{{ number_format($product->price) }})
                                        </option>
                                        @endforeach
                                    </optgroup>

                                    <!-- Show other products as disabled -->
                                    @if($allProducts->count() > $customerProducts->count())
                                        <optgroup label="📋 Other Products (not assigned)">
                                            @foreach($allProducts as $product)
                                                @if(!$customerProducts->contains('p_id', $product->p_id))
                                                <option value="{{ $product->p_id }}" disabled>
                                                    {{ $product->name }} (৳{{ number_format($product->price) }})
                                                </option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                    @endif

                                @else
                                    <!-- Show all products when no search -->
                                    @foreach($allProducts as $product)
                                    <option value="{{ $product->p_id }}"
                                            {{ request('product_id') == $product->p_id ? 'selected' : '' }}>
                                        {{ $product->name }} (৳{{ number_format($product->price) }})
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted mt-1 d-block" id="productHelp">
                                @if($search && $customerProducts->count() > 0)
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    Showing {{ $customerProducts->count() }} product(s) assigned to searched customers
                                @elseif($search)
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    No products found for searched customers
                                @else
                                    <i class="fas fa-search me-1"></i>
                                    Search for customers to see their specific products
                                @endif
                            </small>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fas fa-calendar-alt me-1"></i>Filter by Month
                            </label>
                            <select name="month" class="form-select">
                                <option value="">📅 All Months</option>
                                @foreach($months as $month)
                                <option value="{{ $month }}"
                                        {{ request('month') == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($month)->format('F Y') }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-6 d-flex align-items-end justify-content-start gap-2 mt-3 mt-md-0">
                            <button type="submit" class="btn btn-primary flex-grow-1 py-2 fw-bold">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                            @if(request('search') || request('product_id') != 'all' || request('month'))
                            <a href="{{ route('admin.payment-details.index') }}" class="btn btn-outline-danger flex-grow-1 py-2 fw-bold">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    @if($search)
        @if($customers->count() > 0)
            <!-- Search Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">Search Results</h6>
                                <p class="mb-0 small">
                                    Showing <strong class="text-primary">{{ $customers->total() }}</strong> customer(s) found
                                    @if(request('search'))
                                        for search: "<strong class="text-dark">{{ request('search') }}</strong>"
                                    @endif
                                    @if(request('product_id') != 'all')
                                        @php
                                            $selectedProduct = $allProducts->firstWhere('p_id', request('product_id'))
                                                            ?? $customerProducts->firstWhere('p_id', request('product_id'));
                                        @endphp
                                        @if($selectedProduct)
                                            for product: "<strong class="text-dark">{{ $selectedProduct->name }}</strong>"
                                        @endif
                                    @endif
                                    @if(request('month'))
                                        for month: "<strong class="text-dark">{{ \Carbon\Carbon::parse(request('month'))->format('F Y') }}</strong>"
                                    @endif
                                </p>
                                @if($search && $customerProducts->count() > 0)
                                    <div class="mt-1 small">
                                        <i class="fas fa-box me-1"></i>
                                        Customers have <strong class="text-success">{{ $customerProducts->count() }}</strong> unique product(s) assigned
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Cards -->
            @foreach($customers as $customer)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow border-0">
                        <!-- Customer Header -->
                        <div class="card-header bg-gradient-customer text-white py-3">
                            <div class="row align-items-center">
                                <div class="col-md-8 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="customer-avatar bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 fw-bold">
                                                {{ $customer->name }}
                                                <small class="fs-6 opacity-75">({{ $customer->customer_id }})</small>
                                            </h5>
                                            <div class="customer-contact">
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="text-white-75 small">
                                                        <i class="fas fa-phone me-1"></i>
                                                        <strong>{{ $customer->phone }}</strong>
                                                    </span>
                                                    <span class="text-white-75 small">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        <strong>{{ $customer->email }}</strong>
                                                    </span>
                                                    @if($customer->address)
                                                    <span class="text-white-75 small">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        {{ $customer->address }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="customer-summary">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <div class="bg-white bg-opacity-25 rounded p-2 text-center">
                                                    <div class="small fw-bold text-white">Total Billed</div>
                                                    <div class="h5 fw-bold mb-0">৳{{ number_format($customer->totalBilled, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="bg-success bg-opacity-25 rounded p-2 text-center">
                                                    <div class="small fw-bold text-white">Paid</div>
                                                    <div class="h5 fw-bold mb-0">৳{{ number_format($customer->totalPaid, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="bg-warning bg-opacity-25 rounded p-2 text-center">
                                                    <div class="small fw-bold text-white">Due</div>
                                                    <div class="h5 fw-bold mb-0">৳{{ number_format($customer->totalDue, 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Body -->
                        <div class="card-body p-3">
                            <!-- Assigned Products -->
                            @if($customer->customerProducts->count() > 0)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6 class="fw-bold mb-2 text-primary">
                                            <i class="fas fa-box-open me-2"></i>
                                            Assigned Products
                                            <span class="badge bg-primary ms-1">{{ $customer->customerProducts->count() }}</span>
                                        </h6>
                                        <div class="row">
                                            @foreach($customer->customerProducts as $customerProduct)
                                            <div class="col-xl-3 col-lg-4 col-md-6 mb-2">
                                                <div class="card product-card h-100 border-0 shadow-sm
                                                    {{ request('product_id') == $customerProduct->p_id ? 'border-primary border-2' : '' }}" 
                                                    style="cursor: pointer;" 
                                                    data-product-id="{{ $customerProduct->p_id }}" 
                                                    data-product-name="{{ $customerProduct->product->name ?? 'Unknown Product' }}">
                                                    <div class="card-body p-2">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <div>
                                                                <small class="text-muted d-block mb-1">
                                                                    {{ $customerProduct->customer_product_id ?? 'N/A' }}
                                                                </small>
                                                                <h6 class="card-title mb-1 fw-bold">
                                                                    {{ $customerProduct->product->name ?? 'Unknown Product' }}
                                                                </h6>
                                                            </div>
                                                            <span class="badge bg-primary">
                                                                ৳{{ number_format($customerProduct->custom_price ?? ($customerProduct->product->price ?? 0)) }}
                                                            </span>
                                                        </div>

                                                        <div class="product-details">
                                                            <div class="mb-1">
                                                                <small class="text-muted d-block">
                                                                    <i class="fas fa-calendar me-1"></i>
                                                                    @if($customerProduct->assign_date)
                                                                        {{ \Carbon\Carbon::parse($customerProduct->assign_date)->format('d M Y') }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                    @if($customerProduct->due_date)
                                                                        <i class="fas fa-arrow-right mx-1"></i>
                                                                        {{ \Carbon\Carbon::parse($customerProduct->due_date)->format('d M Y') }}
                                                                    @endif
                                                                </small>
                                                            </div>

                                                            <div class="d-flex flex-wrap gap-1">
                                                                <span class="badge bg-{{ $customerProduct->status == 'active' ? 'success' : ($customerProduct->status == 'pending' ? 'warning' : 'secondary') }}">
                                                                    {{ ucfirst($customerProduct->status) }}
                                                                </span>
                                                                @if(!$customerProduct->is_active)
                                                                    <span class="badge bg-danger">Inactive</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Payment History -->
                            @if($customer->paymentHistory->count() > 0)
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="fw-bold mb-2 text-primary">
                                            <i class="fas fa-history me-2"></i>
                                            Payment History
                                            <span class="badge bg-primary ms-1">{{ $customer->paymentHistory->count() }} invoices</span>
                                        </h6>
                                        <div class="table-responsive rounded shadow-sm border">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th class="py-2 px-3">Invoice #</th>
                                                        <th class="py-2 px-3">Product</th>
                                                        <th class="py-2 px-3">Subtotal</th>
                                                        <th class="py-2 px-3">Start Date</th>
                                                        <th class="py-2 px-3">End Date</th>
                                                        <th class="py-2 px-3 text-end">
                                                            Total Amount
                                                            <div class="small text-muted fw-normal">(due+subtotal)</div>
                                                        </th>
                                                        <th class="py-2 px-3 text-end">Paid</th>
                                                        <th class="py-2 px-3 text-end">Due</th>
                                                        <th class="py-2 px-3">Status</th>
                                                    </tr>                                       
                                                </thead>
                                                <tbody>
                                                    @foreach($customer->paymentHistory as $invoice)
                                                    @php
                                                        $product = $invoice->customerProduct->product ?? null;
                                                        $isSelectedProduct = request('product_id') != 'all' &&
                                                                        request('product_id') == ($product->p_id ?? null);

                                                        // Determine billing cycle months from customer_to_products if available
                                                        $billingCycleMonths = $invoice->customerProduct->billing_cycle_months ?? null;

                                                        // Determine a reliable billing month to display (fallback):
                                                        // prefer `billing_cycle_month` then `billing_month` then issue_date
                                                        $rawBilling = $invoice->billing_cycle_month ?? $invoice->billing_month ?? null;
                                                        if (!$rawBilling && !empty($invoice->issue_date)) {
                                                            $rawBilling = \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m');
                                                        }

                                                        $displayBillingMonth = null;
                                                        if ($rawBilling) {
                                                            try {
                                                                $displayBillingMonth = \Carbon\Carbon::parse($rawBilling)->format('F Y');
                                                            } catch (\Exception $e) {
                                                                $displayBillingMonth = null;
                                                            }
                                                        }

                                                        // Final label to show under product name: prefer billingCycleMonths, else invoice month
                                                        $displayBilling = null;
                                                        if (!is_null($billingCycleMonths) && $billingCycleMonths !== '') {
                                                            $displayBilling = (int)$billingCycleMonths === 1 ? 'Monthly' : ($billingCycleMonths . ' months');
                                                        } elseif ($displayBillingMonth) {
                                                            $displayBilling = $displayBillingMonth;
                                                        }

                                                        // Get subtotal and total_amount directly from invoice (no recalculation)
                                                        $subtotal = $invoice->subtotal ?? 0;
                                                        $totalAmount = $invoice->total_amount ?? 0;
                                                        
                                                        // Calculate start and end dates based on the invoice issue date and billing cycle
                                                        $startDate = null;
                                                        $endDate = null;
                                                        
                                                        if (!empty($invoice->issue_date)) {
                                                            $issueDate = \Carbon\Carbon::parse($invoice->issue_date);
                                                            $startDate = $issueDate->copy()->startOfMonth();
                                                            $endDate = $issueDate->copy()->endOfMonth();
                                                            
                                                            // If we have billing cycle information, adjust the end date
                                                            if ($billingCycleMonths && $billingCycleMonths > 1) {
                                                                $endDate = $startDate->copy()->addMonths($billingCycleMonths)->subDay();
                                                            }
                                                        }
                                                    @endphp
                                                    @php
                                                        // Check if this invoice matches the search term (for highlighting)
                                                        $isMatchingInvoice = false;
                                                        if ($search && stripos($invoice->invoice_number, $search) !== false) {
                                                            $isMatchingInvoice = true;
                                                        }
                                                    @endphp
                                                    <tr class="{{ $isSelectedProduct ? 'table-info' : '' }} {{ $isMatchingInvoice ? 'table-warning' : '' }} align-middle">
                                                        <td class="py-2 px-3">
                                                            <a href="#" class="text-decoration-none fw-bold text-primary">
                                                                {{ $invoice->invoice_number }}
                                                                @if($isMatchingInvoice)
                                                                    <span class="badge bg-warning text-dark ms-2">
                                                                        <i class="fas fa-search me-1"></i>Matched
                                                                    </span>
                                                                @endif
                                                            </a>
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            @if($product)
                                                                <div class="d-flex align-items-start">
                                                                    <div class="product-icon bg-primary bg-opacity-10 text-primary rounded-circle p-1 me-2">
                                                                        <i class="fas fa-box"></i>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold mb-1">{{ $product->name }}</div>
                                                                        @if(!empty($displayBilling))
                                                                            <div class="billing-month-container mt-1">
                                                                                <small class="text-muted">
                                                                                    <i class="fas fa-calendar-alt me-1 text-info"></i>
                                                                                    <span class="fst-italic text-info fw-medium">
                                                                                        {{ $displayBilling }}
                                                                                    </span>
                                                                                </small>
                                                                            </div>
                                                                        @endif
                                                                        @if($isSelectedProduct)
                                                                            <div class="mt-1">
                                                                                <span class="badge bg-primary">Filtered</span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="text-muted">N/A</div>
                                                                @if($billingMonth)
                                                                    <div class="billing-month-container mt-1">
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-calendar-alt me-1 text-info"></i>
                                                                            <span class="fst-italic text-info fw-medium">
                                                                                {{ \Carbon\Carbon::parse($billingMonth)->format('F Y') }}
                                                                            </span>
                                                                        </small>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td class="py-2 px-3 text-end">
                                                            <div class="fw-bold">৳{{ number_format($subtotal, 2) }}</div>
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            @if($startDate)
                                                                <div class="fw-bold">{{ $startDate->format('d M Y') }}</div>
                                                                <small class="text-muted">{{ $startDate->format('F Y') }}</small>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            @if($endDate)
                                                                <div class="fw-bold {{ $endDate->isPast() ? 'text-danger' : 'text-success' }}">
                                                                    {{ $endDate->format('d M Y') }}
                                                                </div>
                                                                <small class="text-muted">{{ $endDate->format('F Y') }}</small>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 px-3 text-end">
                                                            <div class="fw-bold">৳{{ number_format($totalAmount, 2) }}</div>
                                                        </td>
                                                        <td class="py-2 px-3 text-end">
                                                            <div class="text-success fw-bold">৳{{ number_format($invoice->received_amount, 2) }}</div>
                                                        </td>
                                                        <td class="py-2 px-3 text-end">
                                                            @php
                                                                $dueAmount = $totalAmount - $invoice->received_amount;
                                                            @endphp
                                                            <div class="text-danger fw-bold">৳{{ number_format($dueAmount, 2) }}</div>
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            @php
                                                                $statusClass = [
                                                                    'paid' => 'success',
                                                                    'unpaid' => 'danger',
                                                                    'partial' => 'warning',
                                                                    'cancelled' => 'secondary'
                                                                ][$invoice->status] ?? 'secondary';
                                                            @endphp
                                                            <span class="badge bg-{{ $statusClass }} py-1 px-2">
                                                                <i class="fas fa-{{ $invoice->status == 'paid' ? 'check-circle' : ($invoice->status == 'unpaid' ? 'times-circle' : 'exclamation-circle') }} me-1"></i>
                                                                {{ ucfirst($invoice->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    @php
                                                        $subtotalSum = $customer->paymentHistory->sum('subtotal') ?? 0;
                                                        $totalAmountSum = $customer->paymentHistory->sum('total_amount') ?? 0;
                                                    @endphp
                                                    <tr>
                                                        <td colspan="2" class="py-2 px-3 text-end fw-bold">Totals:</td>
                                                        <td class="py-2 px-3 text-end fw-bold">৳{{ number_format($subtotalSum, 2) }}</td>
                                                        <td colspan="2" class="py-2 px-3 text-end fw-bold"></td>
                                                        <td class="py-2 px-3 text-end fw-bold">৳{{ number_format($totalAmountSum, 2) }}</td>
                                                        <td class="py-2 px-3 text-end fw-bold text-success">৳{{ number_format($customer->totalPaid, 2) }}</td>
                                                        <td class="py-2 px-3 text-end fw-bold text-danger">৳{{ number_format($customer->totalDue, 2) }}</td>
                                                        <td class="py-2 px-3"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning border-0 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <div>
                                            <h6 class="alert-heading mb-1">No Payment History Found</h6>
                                            <p class="mb-0 small">
                                                No payment history found for this customer with the current filters.
                                                Try adjusting your search criteria or clear filters to see all records.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Pagination -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        <nav aria-label="Page navigation">
                            {{ $customers->links() }}
                        </nav>
                    </div>
                </div>
            </div>

        @else
            <!-- No Results -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow border-0">
                        <div class="card-body text-center py-4">
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-users fa-4x text-muted opacity-25"></i>
                            </div>
                            <h5 class="text-muted mb-2">No Customers Found</h5>
                            <p class="text-muted mb-3 small">
                                @if(request('search'))
                                    No customers found for search: "<strong class="text-dark">{{ request('search') }}</strong>"
                                    <br>
                                    <small>Try using different keywords or check for spelling errors.</small>
                                @else
                                    No customers available in the system.
                                    <br>
                                    <small>Start by adding customers to the system.</small>
                                @endif
                            </p>
                            <a href="{{ route('admin.payment-details.index') }}" class="btn btn-primary">
                                <i class="fas fa-redo me-1"></i> Clear Search
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Initial State - Prompt to Search -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow border-0">
                    <div class="card-body text-center py-5">
                        <div class="search-prompt-icon mb-4">
                            <i class="fas fa-search-dollar fa-5x text-primary opacity-25"></i>
                        </div>
                        <h3 class="text-primary mb-3">Search for Customer Payment Details</h3>
                        <p class="text-muted mb-4 lead">
                            Enter a customer name, ID, phone number, invoice number or email in the search field above to view their payment history and details.
                        </p>
                        <div class="feature-highlights d-flex flex-wrap justify-content-center gap-4 mb-4">
                            <div class="feature-item">
                                <i class="fas fa-user-check text-success me-2"></i>
                                <span class="fw-medium">Customer Information</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-box-open text-info me-2"></i>
                                <span class="fw-medium">Assigned Products</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-file-invoice-dollar text-warning me-2"></i>
                                <span class="fw-medium">Payment History</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-chart-line text-danger me-2"></i>
                                <span class="fw-medium">Financial Summary</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="btn btn-primary btn-lg" onclick="document.getElementById('customerSearch').focus()">
                                <i class="fas fa-search me-2"></i>Start Searching
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Custom Styles -->
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --light-bg: #f8f9fa;
        --dark-text: #2b2d42;
        --muted-text: #6c757d;
        --border-color: #dee2e6;
        --card-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    body {
        background-color: #f5f7fa;
        font-size: 1rem;
    }

    .page-header {
        background: #ffffff;
        padding: 1.2rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 3px solid var(--secondary-color);
        box-shadow: var(--card-shadow);
    }

    .page-header h1 {
        font-size: 1.4rem;
        color: var(--dark-text);
        margin-bottom: 0.5rem;
    }

    .page-header p {
        font-size: 0.95rem;
        color: var(--muted-text);
        margin-bottom: 0;
    }

    .card {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .card-header {
        padding: 0.9rem 1.2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .card-body {
        padding: 1.2rem;
    }

    .bg-gradient-primary {
        background: var(--primary-color);
    }

    .bg-gradient-customer {
        background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
    }

    .customer-avatar {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .customer-header h5 {
        font-size: 1.15rem;
        font-weight: 600;
        color: white;
        margin-bottom: 0.25rem;
    }

    .customer-header small {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .customer-contact {
        font-size: 0.9rem;
    }

    .customer-summary .h5 {
        font-size: 1.05rem;
        font-weight: 600;
    }

    /* Table Styles */
    .table {
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .table-dark {
        background: var(--primary-color);
    }

    .table-dark th {
        border: none;
        font-weight: 500;
        font-size: 0.85rem;
        padding: 0.6rem 0.9rem;
    }

    .table td, .table th {
        padding: 0.6rem 0.9rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
    }

    .table-info {
        background-color: rgba(52, 152, 219, 0.1);
    }

    /* Highlighted invoice animation */
    @keyframes highlightPulse {
        0% { background-color: rgba(243, 157, 18, 0.47); }
        50% { background-color: rgba(243, 157, 18, 0.71); }
        100% { background-color: rgba(243, 157, 18, 0.47); }
    }

    /* .table-warning {
        animation: highlightPulse 2s infinite;
    } */

    /* Badge Styles */
    .badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
        font-weight: 500;
    }

    .badge.bg-primary {
        background: var(--secondary-color) !important;
    }

    .badge.bg-success {
        background: var(--success-color) !important;
    }

    .badge.bg-warning {
        background: var(--warning-color) !important;
        color: white;
    }

    .badge.bg-danger {
        background: var(--danger-color) !important;
    }

    /* Billing Month Style */
    .billing-month-container {
        display: flex;
        align-items: center;
    }

    .billing-month-container .text-muted {
        display: flex;
        align-items: center;
        color: #0d6efd !important;
        font-weight: 500;
    }

    .billing-month-container .text-info {
        color: #0d6efd !important;
    }

    .billing-month-container .fst-italic {
        font-style: normal;
        font-weight: 600;
        background-color: rgba(13, 110, 253, 0.08);
        padding: 0.1rem 0.4rem;
        border-radius: 3px;
        border-left: 2px solid #0d6efd;
    }

    /* Form Styles */
    .form-control, .form-select {
        font-size: 0.9rem;
        padding: 0.4rem 0.7rem;
        border-radius: 4px;
        border: 1px solid var(--border-color);
    }

    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.1rem rgba(52, 152, 219, 0.25);
    }

    .input-group .input-group-text {
        font-size: 0.9rem;
        padding: 0.4rem 0.7rem;
        border-radius: 4px 0 0 4px;
    }

    /* Button Styles */
    .btn {
        padding: 0.5rem 0.9rem;
        font-size: 0.85rem;
        border-radius: 4px;
        font-weight: 500;
    }

    .btn-primary {
        background: var(--secondary-color);
        border: 1px solid var(--secondary-color);
    }

    .btn-outline-primary {
        color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-outline-primary:hover {
        background: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    /* Alert Styles */
    .alert {
        border-radius: 6px;
        border: none;
        padding: 0.9rem 1.2rem;
        font-size: 0.95rem;
    }

    .alert-info {
        background: #e3f2fd;
        color: #1565c0;
        border-left: 3px solid #2196f3;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left: 3px solid #ffc107;
    }

    /* Product Card */
    .product-card {
        border-radius: 6px;
        border: 1px solid var(--border-color);
        transition: all 0.2s ease-in-out;
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        border-color: var(--secondary-color);
    }

    .product-card .card-title {
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .product-card .card-body {
        padding: 0.9rem;
    }

    /* Empty State */
    .empty-state-icon i {
        font-size: 3rem;
    }

    /* Product Icon */
    .product-icon {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    /* Feature Highlights */
    .feature-highlights {
        font-size: 0.95rem;
        color: var(--muted-text);
    }

    .feature-item {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.9rem;
        background: rgba(52, 152, 219, 0.1);
        border-radius: 4px;
        font-size: 0.85rem;
    }

    .feature-item i {
        font-size: 0.95rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 1.4rem;
        }

        .customer-header h5 {
            font-size: 1.15rem;
        }

        .customer-contact {
            font-size: 0.9rem;
        }

        .table th, .table td {
            padding: 0.5rem 0.6rem;
            font-size: 0.9rem;
        }

        .btn {
            padding: 0.4rem 0.7rem;
            font-size: 0.85rem;
        }

        .feature-highlights {
            font-size: 0.95rem;
        }

        .feature-item {
            padding: 0.4rem 0.7rem;
            font-size: 0.95rem;
        }

        .badge {
            font-size: 0.75rem;
        }

        .billing-month-container .fst-italic {
            font-size: 0.75rem;
            padding: 0.05rem 0.3rem;
        }
    }

    /* Pagination */
    .pagination {
        --bs-pagination-padding-x: 0.6rem;
        --bs-pagination-padding-y: 0.3rem;
        --bs-pagination-font-size: 0.95rem;
        --bs-pagination-border-radius: 4px;
    }

    .page-link {
        border: 1px solid var(--border-color);
        margin: 0 2px;
        color: var(--secondary-color);
    }

    .page-item.active .page-link {
        background: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    /* Tooltip */
    .tooltip {
        --bs-tooltip-font-size: 0.95rem;
    }

    /* Search Prompt */
    .search-prompt-icon i {
        font-size: 4rem;
    }

    h3 {
        font-size: 1.25rem;
    }

    .lead {
        font-size: 1rem;
    }

    .small, small {
        font-size: 0.75em;
    }

</style>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Form elements
        const searchInput = document.getElementById('customerSearch');
        const productFilter = document.getElementById('productFilter');
        const searchForm = document.getElementById('searchForm');

        // Auto-submit when product filter changes
        if (productFilter) {
            productFilter.addEventListener('change', function() {
                if (this.value !== '{{ request("product_id") }}') {
                    searchForm.submit();
                }
            });
        }

        // Auto-submit when month filter changes
        const monthSelect = document.querySelector('select[name="month"]');
        if (monthSelect) {
            monthSelect.addEventListener('change', function() {
                if (this.value !== '{{ request("month") }}') {
                    searchForm.submit();
                }
            });
        }

        // Clear product filter when search changes
        if (searchInput) {
            let timeoutId;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    if (this.value.trim() !== '{{ request("search") }}') {
                        productFilter.value = 'all';
                    }
                }, 500);
            });

            // Auto-focus
            searchInput.focus();
            if (searchInput.value) {
                searchInput.select();
            }
        }

        // Add animation to table rows
        const tableRows = document.querySelectorAll('.table tbody tr');
        tableRows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.03}s`;
            row.classList.add('animate__animated', 'animate__fadeInUp');
        });

        // Print functionality
        document.querySelector('.btn-outline-secondary')?.addEventListener('click', function() {
            window.print();
        });

        // Export functionality (placeholder)
        document.querySelector('.btn-outline-primary')?.addEventListener('click', function() {
            alert('Export functionality will be implemented soon!');
        });

        // Handle product card clicks to filter payment history
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                
                // Update the product filter
                if (productFilter) {
                    productFilter.value = productId;
                    
                    // Submit the form to filter results
                    searchForm.submit();
                }
            });
        });
    });
</script>

<!-- Add Animate.css for animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
@endsection