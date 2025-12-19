@extends('layouts.customer')

@section('title', 'Product Details - Nanosoft')

@section('content')
    <div class="product-details-page">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item">
                                <a href="{{ route('customer.products.index') }}" class="text-decoration-none">
                                    <i class="fas fa-box me-1"></i>My Products
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Product Details</li>
                        </ol>
                    </nav>
                    <h1 class="h2 mb-2">
                        <i class="fas fa-info-circle me-2 text-primary"></i>{{ $customerProduct->product->name ?? 'Product Details' }}
                    </h1>
                    <p class="text-muted mb-0">View detailed information about your subscribed product.</p>
                </div>
                <div>
                    <a href="{{ route('customer.products.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Products
                    </a>
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-1"></i> Renew Product
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Product Information -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-box me-2 text-primary"></i>Product Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="product-info-item mb-4">
                                    <label class="form-label text-muted small fw-bold">Product Name</label>
                                    <h5 class="fw-bold text-dark">{{ $customerProduct->product->name ?? 'N/A' }}</h5>
                                </div>
                                
                                <div class="product-info-item mb-4">
                                    <label class="form-label text-muted small fw-bold">Product ID</label>
                                    <p class="mb-0 fw-bold text-primary">{{ $customerProduct->cp_id }}</p>
                                </div>

                                <div class="product-info-item mb-4">
                                    <label class="form-label text-muted small fw-bold">Monthly Price</label>
                                    <h4 class="fw-bold text-success mb-0">
                                        ৳{{ number_format($customerProduct->product->monthly_price ?? 0, 2) }}
                                        <small class="text-muted fs-6">per month</small>
                                    </h4>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="product-info-item mb-4">
                                    <label class="form-label text-muted small fw-bold">Status</label>
                                    <div>
                                        @if($customerProduct->is_active && $customerProduct->status == 'active')
                                            <span class="badge bg-success rounded-pill px-3 py-2 fs-6">
                                                <i class="fas fa-circle fa-xs me-1"></i> Active
                                            </span>
                                        @elseif($customerProduct->is_active && $customerProduct->status == 'pending')
                                            <span class="badge bg-warning rounded-pill px-3 py-2 fs-6">
                                                <i class="fas fa-clock fa-xs me-1"></i> Pending
                                            </span>
                                        @else
                                            <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                                                <i class="fas fa-times-circle fa-xs me-1"></i> Inactive
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="product-info-item mb-4">
                                    <label class="form-label text-muted small fw-bold">Start Date</label>
                                    <p class="mb-0 fw-bold">{{ $customerProduct->created_at->format('M d, Y') }}</p>
                                    <small class="text-muted">{{ $customerProduct->created_at->diffForHumans() }}</small>
                                </div>

                                <div class="product-info-item mb-4">
                                    <label class="form-label text-muted small fw-bold">Last Updated</label>
                                    <p class="mb-0 fw-bold">{{ $customerProduct->updated_at->format('M d, Y') }}</p>
                                    <small class="text-muted">{{ $customerProduct->updated_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>

                        @if($customerProduct->product->description)
                            <div class="product-description mt-4 pt-4 border-top">
                                <label class="form-label text-muted small fw-bold">Description</label>
                                <p class="text-muted mb-0">{{ $customerProduct->product->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Invoice History -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-file-invoice me-2 text-info"></i>Invoice History
                            </h5>
                            <span class="badge bg-info rounded-pill">{{ $customerProduct->invoices->count() }} invoices</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($customerProduct->invoices->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="border-0">Invoice #</th>
                                            <th class="border-0">Issue Date</th>
                                            <th class="border-0">Due Date</th>
                                            <th class="border-0">Amount</th>
                                            <th class="border-0">Status</th>
                                            <th class="border-0 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customerProduct->invoices as $invoice)
                                            <tr>
                                                <td>
                                                    <span class="fw-bold text-primary">#{{ $invoice->invoice_id }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">৳{{ number_format($invoice->total_amount, 2) }}</span>
                                                </td>
                                                <td>
                                                    @if($invoice->status == 'paid')
                                                        <span class="badge bg-success rounded-pill">
                                                            <i class="fas fa-check fa-xs me-1"></i> Paid
                                                        </span>
                                                    @elseif($invoice->status == 'pending')
                                                        <span class="badge bg-warning rounded-pill">
                                                            <i class="fas fa-clock fa-xs me-1"></i> Pending
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger rounded-pill">
                                                            <i class="fas fa-times fa-xs me-1"></i> Overdue
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="#" class="btn btn-outline-primary" title="View Invoice">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-outline-success" title="Download PDF">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="empty-state-icon mb-3">
                                    <i class="fas fa-file-invoice fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="text-muted mb-2">No Invoices Found</h6>
                                <p class="text-muted mb-0">No invoices have been generated for this product yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-2"></i>Renew Product
                            </button>
                            <button type="button" class="btn btn-outline-info">
                                <i class="fas fa-file-invoice me-2"></i>Generate Invoice
                            </button>
                            <button type="button" class="btn btn-outline-warning">
                                <i class="fas fa-pause me-2"></i>Suspend Service
                            </button>
                            <button type="button" class="btn btn-outline-danger">
                                <i class="fas fa-times me-2"></i>Cancel Product
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Statistics -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2 text-success"></i>Product Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted">Total Invoices</small>
                                <h6 class="mb-0 fw-bold">{{ $customerProduct->invoices->count() }}</h6>
                            </div>
                            <div class="icon-wrapper bg-soft-primary rounded-2 p-2">
                                <i class="fas fa-file-invoice text-primary"></i>
                            </div>
                        </div>

                        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted">Total Paid</small>
                                <h6 class="mb-0 fw-bold text-success">
                                    ৳{{ number_format($customerProduct->invoices->where('status', 'paid')->sum('total_amount'), 2) }}
                                </h6>
                            </div>
                            <div class="icon-wrapper bg-soft-success rounded-2 p-2">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        </div>

                        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted">Pending Amount</small>
                                <h6 class="mb-0 fw-bold text-warning">
                                    ৳{{ number_format($customerProduct->invoices->whereIn('status', ['pending', 'overdue'])->sum('total_amount'), 2) }}
                                </h6>
                            </div>
                            <div class="icon-wrapper bg-soft-warning rounded-2 p-2">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>

                        <div class="stat-item d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Service Duration</small>
                                <h6 class="mb-0 fw-bold">{{ $customerProduct->created_at->diffInDays(now()) }} days</h6>
                            </div>
                            <div class="icon-wrapper bg-soft-info rounded-2 p-2">
                                <i class="fas fa-calendar text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-headset me-2 text-info"></i>Need Help?
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Having issues with this product? Our support team is here to help.</p>
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-ticket-alt me-2"></i>Create Support Ticket
                            </a>
                            <a href="#" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-phone me-2"></i>Call Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .product-details-page {
            animation: fadeIn 0.6s ease-out;
        }

        .product-info-item {
            transition: all 0.3s ease;
        }

        .product-info-item:hover {
            transform: translateX(5px);
        }

        .icon-wrapper {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-soft-primary { background-color: rgba(58, 123, 213, 0.1); }
        .bg-soft-success { background-color: rgba(34, 197, 94, 0.1); }
        .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
        .bg-soft-info { background-color: rgba(6, 182, 212, 0.1); }

        .stat-item {
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 0.5rem;
        }

        .stat-item:hover {
            background-color: #f8fafc;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
            transform: translateX(3px);
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: #f8fafc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #6b7280;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .product-details-page .btn-group {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(element => {
                new bootstrap.Tooltip(element);
            });

            // Add hover effects to stat items
            const statItems = document.querySelectorAll('.stat-item');
            statItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8fafc';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });

            // Add row hover effects to invoice table
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8fafc';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>
@endsection