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
                                       placeholder="Name, ID, Phone or Email..."
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
                        
                        <div class="col-lg-2 col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                        </div>
                        
                        @if(request('search') || request('product_id') != 'all' || request('month'))
                        <div class="col-12 mt-2">
                            <a href="{{ route('admin.payment-details.index') }}" class="btn btn-outline-danger">
                                <i class="fas fa-times me-1"></i> Clear All Filters
                            </a>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
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
                                                {{ request('product_id') == $customerProduct->p_id ? 'border-primary border-2' : '' }}">
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
                                                    <th class="py-2 px-3">Issue Date</th>
                                                    <th class="py-2 px-3">Due Date</th>
                                                    <th class="py-2 px-3 text-end">Amount</th>
                                                    <th class="py-2 px-3 text-end">Paid</th>
                                                    <th class="py-2 px-3 text-end">Due</th>
                                                    <th class="py-2 px-3">Status</th>
                                                    <th class="py-2 px-3">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($customer->paymentHistory as $invoice)
                                                @php
                                                    $product = $invoice->customerProduct->product ?? null;
                                                    $isSelectedProduct = request('product_id') != 'all' && 
                                                                    request('product_id') == ($product->p_id ?? null);
                                                @endphp
                                                <tr class="{{ $isSelectedProduct ? 'table-info' : '' }} align-middle">
                                                    <td class="py-2 px-3">
                                                        <a href="#" class="text-decoration-none fw-bold text-primary">
                                                            {{ $invoice->invoice_number }}
                                                        </a>
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        @if($product)
                                                            <div class="d-flex align-items-center">
                                                                <div class="product-icon bg-primary bg-opacity-10 text-primary rounded-circle p-1 me-2">
                                                                    <i class="fas fa-box"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold">{{ $product->name }}</div>
                                                                    @if($isSelectedProduct)
                                                                        <span class="badge bg-primary">Filtered</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        @if($invoice->issue_date)
                                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M Y') }}</div>
                                                            <small class="text-muted">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('h:i A') }}</small>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        @if($invoice->customerProduct && $invoice->customerProduct->due_date)
                                                            <div class="fw-bold {{ \Carbon\Carbon::parse($invoice->customerProduct->due_date)->isPast() ? 'text-danger' : 'text-success' }}">
                                                                {{ \Carbon\Carbon::parse($invoice->customerProduct->due_date)->format('d M Y') }}
                                                            </div>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 text-end">
                                                        <div class="fw-bold">৳{{ number_format($invoice->total_amount, 2) }}</div>
                                                    </td>
                                                    <td class="py-2 px-3 text-end">
                                                        <div class="text-success fw-bold">৳{{ number_format($invoice->received_amount, 2) }}</div>
                                                    </td>
                                                    <td class="py-2 px-3 text-end">
                                                        <div class="text-danger fw-bold">৳{{ number_format($invoice->total_amount - $invoice->received_amount, 2) }}</div>
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
                                                    <td class="py-2 px-3">
                                                        @if($invoice->notes)
                                                            <div class="notes-container">
                                                                <small class="text-muted" data-bs-toggle="tooltip" title="{{ $invoice->notes }}">
                                                                    <i class="fas fa-sticky-note me-1"></i>
                                                                    {{ Str::limit($invoice->notes, 30) }}
                                                                </small>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="4" class="py-2 px-3 text-end fw-bold">Totals:</td>
                                                    <td class="py-2 px-3 text-end fw-bold">৳{{ number_format($customer->totalBilled, 2) }}</td>
                                                    <td class="py-2 px-3 text-end fw-bold text-success">৳{{ number_format($customer->totalPaid, 2) }}</td>
                                                    <td class="py-2 px-3 text-end fw-bold text-danger">৳{{ number_format($customer->totalDue, 2) }}</td>
                                                    <td colspan="2" class="py-2 px-3"></td>
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
</div>

<!-- Custom Styles -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --customer-gradient: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
        --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }
    
    body {
        background-color: #f8fafc;
    }
    
    .page-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(143, 148, 251, 0.1) 100%);
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border-left: 4px solid #667eea;
    }
    
    .page-header h1 {
        font-size: 1.75rem;
        color: #2d3748;
    }
    
    .page-header p {
        font-size: 0.95rem;
        color: #718096;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
    }
    
    .bg-gradient-primary {
        background: var(--primary-gradient);
    }
    
    .bg-gradient-customer {
        background: var(--customer-gradient);
    }
    
    .customer-avatar {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
    }
    
    .customer-header h5 {
        font-size: 1.25rem;
        font-weight: 700;
        color: white;
    }
    
    .customer-header small {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .customer-contact {
        font-size: 0.9rem;
    }
    
    .customer-summary .h5 {
        font-size: 1.1rem !important;
        font-weight: 700;
    }
    
    /* Table Styles */
    .table-dark {
        background: var(--customer-gradient);
    }
    
    .table-dark th {
        border: none;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.75rem 1rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transform: scale(1.002);
        transition: all 0.2s ease;
    }
    
    .table-info {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(143, 148, 251, 0.1) 100%);
        border-left: 3px solid #667eea;
    }
    
    /* Badge Styles */
    .badge {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .badge.bg-primary {
        background: var(--primary-gradient) !important;
        border: none;
    }
    
    .badge.bg-success {
        background: var(--success-gradient) !important;
        border: none;
    }
    
    .badge.bg-warning {
        background: var(--warning-gradient) !important;
        border: none;
        color: #1a202c;
    }
    
    /* Form Styles */
    .form-control, .form-select {
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .input-group .input-group-text {
        font-size: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 8px 0 0 8px;
    }
    
    /* Button Styles */
    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: var(--primary-gradient);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.25);
    }
    
    /* Alert Styles */
    .alert {
        border-radius: 10px;
        border: none;
        padding: 1rem;
    }
    
    .alert-info {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1565c0;
        border-left: 4px solid #2196f3;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    /* Product Card */
    .product-card {
        border-radius: 10px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    .product-card:hover {
        border-color: #667eea;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.15);
    }
    
    .product-card .card-title {
        font-size: 1rem;
        color: #2d3748;
    }
    
    /* Empty State */
    .empty-state-icon {
        opacity: 0.5;
    }
    
    /* Product Icon */
    .product-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .customer-header h5 {
            font-size: 1.1rem;
        }
        
        .customer-contact {
            font-size: 0.8rem;
        }
        
        .table th, .table td {
            padding: 0.5rem;
            font-size: 0.8rem;
        }
        
        .btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
        }
    }
    
    /* Pagination */
    .pagination {
        --bs-pagination-padding-x: 0.75rem;
        --bs-pagination-padding-y: 0.5rem;
        --bs-pagination-font-size: 0.9rem;
        --bs-pagination-border-radius: 8px;
    }
    
    .page-link {
        border: none;
        margin: 0 3px;
        color: #667eea;
        font-weight: 500;
    }
    
    .page-link:hover {
        background-color: rgba(102, 126, 234, 0.1);
        color: #4e54c8;
    }
    
    .page-item.active .page-link {
        background: var(--primary-gradient);
        border: none;
    }
    
    /* Tooltip */
    .tooltip {
        --bs-tooltip-font-size: 0.85rem;
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
    });
</script>

<!-- Add Animate.css for animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
@endsection