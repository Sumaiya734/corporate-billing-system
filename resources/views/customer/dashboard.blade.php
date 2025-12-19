@extends('layouts.customer')

@section('title', 'Customer Dashboard - Nanosoft')

@section('content')
    <div class="customer-dashboard">
        

        <!-- Welcome Banner -->
        <div class="card gradient-card welcome-banner mb-4 border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center">
                            <div class="avatar-icon me-3">
                                <i class="fas fa-user-circle fa-3x text-white opacity-90"></i>
                            </div>
                            <div>
                                <h2 class="h3 mb-1 text-white">
                                    <i class="fas fa-hand-wave me-2"></i>Welcome back, {{ $customer->name }}!
                                </h2>
                                <p class="mb-0 text-white opacity-90">
                                    Here's your account overview and quick access to your products.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <span class="badge bg-light text-primary px-3 py-2">
                            <i class="fas fa-calendar-day me-1"></i> {{ now()->format('l, F j') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Due -->
            <div class="col-xl-3 col-lg-6">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper bg-soft-primary rounded-3 p-3 me-3">
                                <i class="fas fa-file-invoice text-primary fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Due</h6>
                                <h3 class="fw-bold text-primary">৳{{ number_format($totalDue, 2) }}</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge {{ $totalDue > 0 ? 'bg-light text-danger' : 'bg-light text-success' }} px-3 py-1">
                                <i class="fas {{ $totalDue > 0 ? 'fa-exclamation-circle me-1' : 'fa-check-circle me-1' }}"></i>
                                {{ $totalDue > 0 ? 'Unpaid amount' : 'All bills paid' }}
                            </span>
                            <small class="text-muted ms-auto">
                                <i class="fas fa-clock me-1"></i> Updated now
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Products -->
            <div class="col-xl-3 col-lg-6">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper bg-soft-success rounded-3 p-3 me-3">
                                <i class="fas fa-box text-success fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Active Products</h6>
                                <h3 class="fw-bold text-success">{{ $customer->customerproducts->where('is_active', 1)->where('status', 'active')->count() }}</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-success px-3 py-1">
                                <i class="fas fa-check-circle me-1"></i> Products running
                            </span>
                            <a href="{{ route('customer.products.index') ?? '#' }}" class="text-decoration-none ms-auto">
                                <small class="text-primary">
                                    View all <i class="fas fa-arrow-right ms-1"></i>
                                </small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Invoices -->
            <div class="col-xl-3 col-lg-6">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper bg-soft-warning rounded-3 p-3 me-3">
                                <i class="fas fa-file-alt text-warning fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Invoices</h6>
                                <h3 class="fw-bold text-warning">{{ $customer->invoices->count() }}</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-warning px-3 py-1">
                                <i class="fas fa-history me-1"></i> All time
                            </span>
                            <a href="{{ route('customer.invoices.index') ?? '#' }}" class="text-decoration-none ms-auto">
                                <small class="text-primary">
                                    View all <i class="fas fa-arrow-right ms-1"></i>
                                </small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Since -->
            <div class="col-xl-3 col-lg-6">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper bg-soft-info rounded-3 p-3 me-3">
                                <i class="fas fa-calendar-alt text-info fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Member Since</h6>
                                <h3 class="fw-bold text-info">{{ $customer->created_at->format('M Y') }}</h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-info px-3 py-1">
                                <i class="fas fa-award me-1"></i> Loyal customer
                            </span>
                            <small class="text-muted ms-auto">
                                {{ $customer->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Recent Activity Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2 text-primary"></i>Recent Activity
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary active" data-filter="all">
                                    <i class="fas fa-th-large me-1"></i> All
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-filter="invoices">
                                    <i class="fas fa-file-invoice me-1"></i> Invoices
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-filter="payments">
                                    <i class="fas fa-credit-card me-1"></i> Payments
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="activity-timeline">
                            @php
                                $activities = collect();
                                foreach ($invoices->take(5) as $invoice) {
                                    $activities->push([
                                        'type' => 'invoice',
                                        'data' => $invoice,
                                        'date' => $invoice->issue_date
                                    ]);
                                }
                                foreach ($payments->take(5) as $payment) {
                                    $activities->push([
                                        'type' => 'payment',
                                        'data' => $payment,
                                        'date' => $payment->payment_date
                                    ]);
                                }
                                $activities = $activities->sortByDesc('date')->take(6);
                            @endphp

                            @forelse($activities as $activity)
                                <div class="activity-item {{ $activity['type'] }}">
                                    <div class="activity-icon">
                                        @if($activity['type'] == 'invoice')
                                            <i class="fas fa-file-invoice text-info"></i>
                                        @else
                                            <i class="fas fa-credit-card text-success"></i>
                                        @endif
                                    </div>
                                    <div class="activity-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    @if($activity['type'] == 'invoice')
                                                        Invoice #{{ $activity['data']->invoice_number }}
                                                    @else
                                                        Payment #{{ $activity['data']->payment_id }}
                                                    @endif
                                                </h6>
                                                <p class="mb-1 text-muted small">
                                                    @if($activity['type'] == 'invoice')
                                                        Issued: {{ $activity['data']->issue_date->format('M d, Y') }}
                                                        • Status: <span class="badge bg-{{ $activity['data']->status == 'paid' ? 'success' : ($activity['data']->status == 'partial' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($activity['data']->status) }}
                                                        </span>
                                                    @else
                                                        Paid on: {{ $activity['data']->payment_date->format('M d, Y') }}
                                                        • Method: {{ ucfirst($activity['data']->payment_method) }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-1 fw-bold">
                                                    ৳{{ number_format($activity['type'] == 'invoice' ? $activity['data']->total_amount : $activity['data']->amount, 2) }}
                                                </h6>
                                                <small class="text-muted">
                                                    @if($activity['type'] == 'invoice')
                                                        Due: ৳{{ number_format($activity['data']->total_amount - $activity['data']->received_amount, 2) }}
                                                    @else
                                                        Ref: {{ $activity['data']->transaction_id ?? 'N/A' }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-history fa-3x text-muted mb-3 opacity-50"></i>
                                    <p class="text-muted mb-0">No recent activity found</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="{{ route('customer.invoices.index') ?? '#' }}" class="quick-action-card text-decoration-none">
                                    <div class="card border-0 h-100 hover-shadow">
                                        <div class="card-body text-center p-4">
                                            <div class="icon-wrapper bg-soft-primary rounded-circle p-3 mb-3 mx-auto">
                                                <i class="fas fa-file-invoice text-primary fa-2x"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">View Invoices</h6>
                                            <p class="text-muted small mb-0">Check all your bills</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4">
                                <a href="{{ route('customer.payments.index') ?? '#' }}" class="quick-action-card text-decoration-none">
                                    <div class="card border-0 h-100 hover-shadow">
                                        <div class="card-body text-center p-4">
                                            <div class="icon-wrapper bg-soft-success rounded-circle p-3 mb-3 mx-auto">
                                                <i class="fas fa-history text-success fa-2x"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Payment History</h6>
                                            <p class="text-muted small mb-0">View past payments</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4">
                                <a href="{{ route('customer.products.index') ?? '#' }}" class="quick-action-card text-decoration-none">
                                    <div class="card border-0 h-100 hover-shadow">
                                        <div class="card-body text-center p-4">
                                            <div class="icon-wrapper bg-soft-info rounded-circle p-3 mb-3 mx-auto">
                                                <i class="fas fa-box text-info fa-2x"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">My Products</h6>
                                            <p class="text-muted small mb-0">Manage products</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4">
                                <a href="{{ route('customer.profile.index') }}" class="quick-action-card text-decoration-none">
                                    <div class="card border-0 h-100 hover-shadow">
                                        <div class="card-body text-center p-4">
                                            <div class="icon-wrapper bg-soft-warning rounded-circle p-3 mb-3 mx-auto">
                                                <i class="fas fa-user-edit text-warning fa-2x"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Update Profile</h6>
                                            <p class="text-muted small mb-0">Edit information</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4">
                                <a href="{{ route('customer.support.create') ?? '#' }}" class="quick-action-card text-decoration-none">
                                    <div class="card border-0 h-100 hover-shadow">
                                        <div class="card-body text-center p-4">
                                            <div class="icon-wrapper bg-soft-danger rounded-circle p-3 mb-3 mx-auto">
                                                <i class="fas fa-ticket-alt text-danger fa-2x"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Get Support</h6>
                                            <p class="text-muted small mb-0">Raise a ticket</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4">
                                <a href="{{ route('customer.invoices.download', $invoices->first()->id ?? '#') ?? '#' }}" class="quick-action-card text-decoration-none {{ !$invoices->count() ? 'disabled' : '' }}">
                                    <div class="card border-0 h-100 hover-shadow">
                                        <div class="card-body text-center p-4">
                                            <div class="icon-wrapper bg-soft-secondary rounded-circle p-3 mb-3 mx-auto">
                                                <i class="fas fa-download text-secondary fa-2x"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Download Invoice</h6>
                                            <p class="text-muted small mb-0">Latest bill PDF</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Account Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2 text-primary"></i>Account Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="info-list">
                            <div class="info-item d-flex align-items-center mb-3">
                                <div class="info-icon bg-soft-primary rounded-2 p-2 me-3">
                                    <i class="fas fa-id-card text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Customer ID</small>
                                    <span class="fw-bold">{{ $customer->customer_id }}</span>
                                </div>
                            </div>
                            <div class="info-item d-flex align-items-center mb-3">
                                <div class="info-icon bg-soft-success rounded-2 p-2 me-3">
                                    <i class="fas fa-user text-success"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Full Name</small>
                                    <span class="fw-bold">{{ $customer->name }}</span>
                                </div>
                            </div>
                            <div class="info-item d-flex align-items-center mb-3">
                                <div class="info-icon bg-soft-info rounded-2 p-2 me-3">
                                    <i class="fas fa-envelope text-info"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Email Address</small>
                                    <span class="fw-bold">{{ $customer->email }}</span>
                                </div>
                            </div>
                            <div class="info-item d-flex align-items-center mb-3">
                                <div class="info-icon bg-soft-warning rounded-2 p-2 me-3">
                                    <i class="fas fa-phone text-warning"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Phone Number</small>
                                    <span class="fw-bold">{{ $customer->phone }}</span>
                                </div>
                            </div>
                            <div class="info-item d-flex align-items-center">
                                <div class="info-icon bg-soft-secondary rounded-2 p-2 me-3">
                                    <i class="fas fa-map-marker-alt text-secondary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Address</small>
                                    <span class="fw-bold">{{ $customer->address }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Products -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-box me-2 text-success"></i>Active Products
                            </h5>
                            <span class="badge bg-success rounded-pill">
                                {{ $customer->customerproducts->where('is_active', 1)->where('status', 'active')->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($customer->customerproducts->where('is_active', 1)->where('status', 'active')->count() > 0)
                            <div class="product-list">
                                @foreach($customer->customerproducts->where('is_active', 1)->where('status', 'active')->take(3) as $customerProduct)
                                    <div class="product-item mb-3 p-3 rounded-3 border">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="product-icon bg-soft-success rounded-2 p-2 me-3">
                                                <i class="fas fa-cube text-success"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-bold">{{ $customerProduct->product->name ?? 'Unknown Product' }}</h6>
                                                <small class="text-success">
                                                    <i class="fas fa-circle fa-xs me-1"></i> Active
                                                </small>
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            {{ $customerProduct->product->description ?? 'No description available' }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-light text-primary">
                                                ৳{{ number_format($customerProduct->product->monthly_price ?? 0, 2) }}/month
                                            </span>
                                            <a href="#" class="text-decoration-none small">
                                                <i class="fas fa-info-circle me-1"></i> Details
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($customer->customerproducts->where('is_active', 1)->where('status', 'active')->count() > 3)
                                <div class="text-center mt-3">
                                    <a href="{{ route('customer.products.index') ?? '#' }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> View All Products
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <div class="empty-state-icon mb-3">
                                    <i class="fas fa-box fa-3x text-muted opacity-50"></i>
                                </div>
                                <h6 class="text-muted mb-2">No Active Products</h6>
                                <p class="text-muted small mb-0">You don't have any active products yet.</p>
                                <a href="#" class="btn btn-outline-primary btn-sm mt-3">
                                    <i class="fas fa-plus me-1"></i> Browse Products
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .customer-dashboard {
            animation: fadeIn 0.6s ease-out;
        }

        .gradient-card.welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
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
        .bg-soft-danger { background-color: rgba(239, 68, 68, 0.1); }
        .bg-soft-secondary { background-color: rgba(107, 114, 128, 0.1); }

        .activity-timeline {
            position: relative;
            padding: 20px 0;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 28px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
            opacity: 0.2;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s ease;
        }

        .activity-item:hover {
            background-color: #f8fafc;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 56px;
            height: 56px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            position: relative;
            z-index: 1;
        }

        .activity-content {
            flex: 1;
        }

        .quick-action-card .card {
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
        }

        .quick-action-card .card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
        }

        .hover-shadow:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }

        .info-list .info-item {
            transition: transform 0.2s ease;
        }

        .info-list .info-item:hover {
            transform: translateX(5px);
        }

        .product-item {
            transition: all 0.3s ease;
            border-color: #e2e8f0 !important;
        }

        .product-item:hover {
            border-color: #667eea !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
            .activity-timeline::before {
                left: 20px;
            }
            
            .activity-icon {
                width: 40px;
                height: 40px;
                margin-right: 15px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Activity filter
            const filterButtons = document.querySelectorAll('[data-filter]');
            const activityItems = document.querySelectorAll('.activity-item');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter activities
                    activityItems.forEach(item => {
                        if (filter === 'all' || item.classList.contains(filter)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
            
            // Add hover effect to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
@endsection