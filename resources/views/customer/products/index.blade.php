@extends('layouts.customer')

@section('title', 'My Products - Nanosoft')

@section('content')
    <div class="products-page">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">
                        <i class="fas fa-box me-2 text-primary"></i>My Products
                    </h1>
                    <p class="text-muted mb-0">Manage and view all your subscribed products.</p>
                </div>
                <div>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Product
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-lg-6">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-soft-primary rounded-3 p-3 me-3">
                                <i class="fas fa-boxes text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Products</h6>
                                <h3 class="fw-bold text-primary">{{ $customerProducts->total() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-soft-success rounded-3 p-3 me-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Active</h6>
                                <h3 class="fw-bold text-success">{{ $activeCount }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-soft-info rounded-3 p-3 me-3">
                                <i class="fas fa-calendar-alt text-info fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Monthly Cost</h6>
                                <h3 class="fw-bold text-info">৳{{ number_format($totalMonthly, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-soft-warning rounded-3 p-3 me-3">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Last Updated</h6>
                                <h3 class="fw-bold text-warning">{{ now()->format('M d') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2 text-secondary"></i>All Products
                </h5>
            </div>
            <div class="card-body">
                @if($customerProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="border-0">Product</th>
                                    <th class="border-0">Description</th>
                                    <th class="border-0">Monthly Price</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Start Date</th>
                                    <th class="border-0 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customerProducts as $cp)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-soft-primary rounded-2 p-2 me-3">
                                                    <i class="fas fa-box text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $cp->product->name ?? 'N/A' }}</h6>
                                                    <small class="text-muted">Product ID: {{ $cp->cp_id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-muted small">
                                                {{ Str::limit($cp->product->description ?? 'No description', 50) }}
                                            </p>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 fw-bold text-primary">
                                                ৳{{ number_format($cp->product->monthly_price ?? 0, 2) }}
                                            </h6>
                                            <small class="text-muted">per month</small>
                                        </td>
                                        <td>
                                            @if($cp->is_active && $cp->status == 'active')
                                                <span class="badge bg-success rounded-pill px-3 py-1">
                                                    <i class="fas fa-circle fa-xs me-1"></i> Active
                                                </span>
                                            @elseif($cp->is_active && $cp->status == 'pending')
                                                <span class="badge bg-warning rounded-pill px-3 py-1">
                                                    <i class="fas fa-clock fa-xs me-1"></i> Pending
                                                </span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-3 py-1">
                                                    <i class="fas fa-times-circle fa-xs me-1"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted d-block">{{ $cp->created_at->format('M d, Y') }}</small>
                                            <small class="text-muted">{{ $cp->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('customer.products.show', $cp->cp_id) }}" 
                                                   class="btn btn-outline-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="#" 
                                                   class="btn btn-outline-success"
                                                   data-bs-toggle="tooltip"
                                                   title="View Invoices">
                                                    <i class="fas fa-file-invoice"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#renewModal{{ $cp->cp_id }}"
                                                        title="Renew Product">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($customerProducts->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="mb-0 text-muted">
                                    Showing {{ $customerProducts->firstItem() }} to {{ $customerProducts->lastItem() }} of {{ $customerProducts->total() }} products
                                </p>
                            </div>
                            <div>
                                {{ $customerProducts->links() }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="fas fa-box fa-4x text-muted opacity-50"></i>
                        </div>
                        <h4 class="text-muted mb-3">No Products Found</h4>
                        <p class="text-muted mb-4">You haven't subscribed to any products yet.</p>
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Browse Products
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Categories -->
        <div class="row mt-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-tags me-2 text-info"></i>Product Categories
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-wifi text-primary me-2"></i>
                                    <span>Internet Services</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">3</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-phone text-success me-2"></i>
                                    <span>Voice Services</span>
                                </div>
                                <span class="badge bg-success rounded-pill">2</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-shield-alt text-warning me-2"></i>
                                    <span>Security Products</span>
                                </div>
                                <span class="badge bg-warning rounded-pill">1</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-server text-info me-2"></i>
                                    <span>Hosting Services</span>
                                </div>
                                <span class="badge bg-info rounded-pill">1</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-line me-2 text-success"></i>Product Usage
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="usage-item p-3 rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold">Bandwidth Usage</h6>
                                        <span class="badge bg-info">75% used</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: 75%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2">45GB of 60GB used this month</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="usage-item p-3 rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold">Storage Usage</h6>
                                        <span class="badge bg-success">40% used</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 40%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2">20GB of 50GB used</small>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-chart-bar me-1"></i> View Detailed Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .products-page {
            animation: fadeIn 0.6s ease-out;
        }

        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-soft-primary { background-color: rgba(58, 123, 213, 0.1); }
        .bg-soft-success { background-color: rgba(34, 197, 94, 0.1); }
        .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
        .bg-soft-info { background-color: rgba(6, 182, 212, 0.1); }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
            transform: translateX(5px);
        }

        .empty-state-icon {
            width: 100px;
            height: 100px;
            background: #f8fafc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .usage-item {
            transition: all 0.3s ease;
            border-color: #e2e8f0 !important;
        }

        .usage-item:hover {
            border-color: #667eea !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .progress {
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 10px;
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
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .btn-group {
                flex-wrap: wrap;
                gap: 0.25rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                new bootstrap.Tooltip(tooltip);
            });

            // Add row hover effects
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8fafc';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });

            // Progress bar animation
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                    bar.style.transition = 'width 1.5s ease';
                }, 300);
            });
        });
    </script>
@endsection