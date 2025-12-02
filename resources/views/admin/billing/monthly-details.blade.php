@extends('layouts.admin')

@section('title', 'Monthly Billing Details - ' . $month)

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-chart-bar me-2 text-primary"></i>Monthly Billing Details - {{ \Carbon\Carbon::parse($month)->format('F Y') }}
            </h2>
            <p class="text-muted mb-0">Complete breakdown of customers, products, and billing for this month</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Billing
            </a>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print Report
            </button>
        </div>
    </div>

    

    <!-- Customer Details -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2"></i>Customer Details ({{ $totalCustomers }} customers)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Products</th>
                            <th>Monthly Cost</th>
                            <th>Customer Since</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Separate new and existing customers
                            $newCustomersData = [];
                            $existingCustomersData = [];
                            
                            foreach($customerData as $customerId => $data) {
                                if ($data['customer_info']['is_new']) {
                                    $newCustomersData[$customerId] = $data;
                                } else {
                                    $existingCustomersData[$customerId] = $data;
                                }
                            }
                        @endphp

                        <!-- New Customers Section -->
                        @if(count($newCustomersData) > 0)
                        <tr class="table-success">
                            <td colspan="7" class="text-center">
                                <strong>
                                    <i class="fas fa-user-plus me-2"></i>
                                    NEW CUSTOMERS ({{ count($newCustomersData) }})
                                </strong>
                            </td>
                        </tr>
                        @foreach($newCustomersData as $customerId => $data)
                        @php
                            $customer = $data['customer_info'];
                            $products = $data['products'];
                            $totalCustomerCost = collect($products)->sum('monthly_price');
                        @endphp
                        <tr class="bg-light">
                            <td>
                                <strong>{{ $customer['customer_id'] }}</strong>
                                <br><span class="badge bg-success">NEW</span>
                            </td>
                            <td>
                                <strong>{{ $customer['name'] }}</strong>
                                <br><small class="text-muted">{{ Str::limit($customer['address'], 30) }}</small>
                            </td>
                            <td>
                                <div><small>{{ $customer['email'] }}</small></div>
                                <div><small>{{ $customer['phone'] }}</small></div>
                            </td>
                            <td>
                                @if(count($products) > 0)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">No Products</span>
                                @endif
                            </td>
                            <td>
                                @if(count($products) > 0)
                                    @foreach($products as $product)
                                    <span class="badge bg-primary mb-1">
                                        {{ $product['product_name'] }} 
                                        (৳{{ number_format($product['monthly_price'], 0) }})
                                    </span>
                                    <br>
                                    @endforeach
                                @else
                                    <span class="text-muted">No products assigned</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">৳ {{ number_format($totalCustomerCost, 0) }}</strong>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($customer['created_at'])->format('M j, Y') }}
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($customer['created_at'])->diffForHumans() }}
                                </small>
                            </td>
                        </tr>
                        @endforeach
                        @endif

                        <!-- Existing Customers Section -->
                        @if(count($existingCustomersData) > 0)
                        <tr class="table-info">
                            <td colspan="7" class="text-center">
                                <strong>
                                    <i class="fas fa-user-check me-2"></i>
                                    EXISTING CUSTOMERS ({{ count($existingCustomersData) }})
                                </strong>
                            </td>
                        </tr>
                        @foreach($existingCustomersData as $customerId => $data)
                        @php
                            $customer = $data['customer_info'];
                            $products = $data['products'];
                            $totalCustomerCost = collect($products)->sum('monthly_price');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $customer['customer_id'] }}</strong>
                            </td>
                            <td>
                                <strong>{{ $customer['name'] }}</strong>
                                <br><small class="text-muted">{{ Str::limit($customer['address'], 30) }}</small>
                            </td>
                            <td>
                                <div><small>{{ $customer['email'] }}</small></div>
                                <div><small>{{ $customer['phone'] }}</small></div>
                            </td>
                            <td>
                                @if(count($products) > 0)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">No Products</span>
                                @endif
                            </td>
                            <td>
                                @if(count($products) > 0)
                                    @foreach($products as $product)
                                    <span class="badge bg-primary mb-1">
                                        {{ $product['product_name'] }} 
                                        (৳{{ number_format($product['monthly_price'], 0) }})
                                    </span>
                                    <br>
                                    @endforeach
                                @else
                                    <span class="text-muted">No products assigned</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">৳ {{ number_format($totalCustomerCost, 0) }}</strong>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($customer['created_at'])->format('M j, Y') }}
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($customer['created_at'])->diffForHumans() }}
                                </small>
                            </td>
                        </tr>
                        @endforeach
                        @endif

                        <!-- No Customers Message -->
                        @if(count($customerData) == 0)
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                <h5 class="text-muted">No Customers Found</h5>
                                <p class="text-muted">No customer data available for {{ \Carbon\Carbon::parse($month)->format('F Y') }}</p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Products Summary -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cube me-2"></i>Products Summary
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $productSummary = [];
                        foreach($customerData as $data) {
                            foreach($data['products'] as $product) {
                                $productName = $product['product_name'];
                                if (!isset($productSummary[$productName])) {
                                    $productSummary[$productName] = [
                                        'count' => 0,
                                        'revenue' => 0,
                                        'type' => $product['product_type']
                                    ];
                                }
                                $productSummary[$productName]['count']++;
                                $productSummary[$productName]['revenue'] += $product['monthly_price'];
                            }
                        }
                    @endphp
                    
                    @if(count($productSummary) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($productSummary as $productName => $stats)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ $productName }}</h6>
                                    <small class="text-muted">{{ $stats['type'] ?? 'No Type' }}</small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-primary">{{ $stats['count'] }} customers</strong>
                                    <br>
                                    <small class="text-success">৳ {{ number_format($stats['revenue'], 0) }}/month</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center py-3">No products assigned this month</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-receipt me-2"></i>Recent Payments
                    </h6>
                </div>
                <div class="card-body">
                    @if($payments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($payments->take(5) as $payment)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ $payment->invoice_number }}</h6>
                                    <small class="text-muted">{{ $payment->payment_method }}</small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">৳ {{ number_format($payment->amount, 0) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M j, Y') }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center py-3">No payments this month</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Info -->
    <div class="card mt-4 bg-light">
        <div class="card-body text-center">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Report period: {{ $startDate->format('F j, Y') }} to {{ $endDate->format('F j, Y') }} | 
                Generated on: {{ now()->format('M j, Y g:i A') }}
            </small>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .details-btn {
        font-weight: 500;
        border-radius: 8px;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .details-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .bg-light {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    .table-success td {
        background-color: #d1eddd !important;
        font-weight: 600;
    }

    .table-info td {
        background-color: #d1ecf1 !important;
        font-weight: 600;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Monthly details page loaded for: {{ $month }}');
        
        // Add smooth scrolling to sections
        const sectionHeaders = document.querySelectorAll('tr[class*="table-"]');
        sectionHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const sectionType = this.classList.contains('table-success') ? 'new' : 'existing';
                const customers = this.parentElement.querySelectorAll('tr:not([class*="table-"])');
                
                customers.forEach(customer => {
                    if (customer.style.display === 'none') {
                        customer.style.display = 'table-row';
                    } else {
                        customer.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endsection