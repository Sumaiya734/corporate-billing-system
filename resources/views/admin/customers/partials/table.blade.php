@php
use Illuminate\Support\Facades\DB;
@endphp

@if($customers->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="customersTable">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Customer</th>
                    <th>products</th>
                    <th class="text-center">Monthly Bill</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Registration</th>
                    <th class="text-center pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                @php
                    // Get active products with relationships
                    $activeproducts = $customer->customerproducts
                        ->where('status', 'active')
                        ->where('is_active', 1)
                        ->filter(function($cp) {
                            return $cp->product !== null; // Only include products that exist
                        });

                    $regularproduct = null;
                    $specialproducts = collect();
                    
                    foreach($activeproducts as $cp) {
                        // Check product_type_id: 1 = regular, 2 = special (or use product_type field if it exists)
                        $productType = $cp->product->product_type ?? null;
                        
                        if ($productType === 'regular' || $cp->product->product_type_id == 1) {
                            $regularproduct = $cp;
                        } elseif ($productType === 'special' || $cp->product->product_type_id == 2) {
                            $specialproducts->push($cp);
                        } else {
                            // If no type is set, treat first product as regular
                            if (!$regularproduct) {
                                $regularproduct = $cp;
                            } else {
                                $specialproducts->push($cp);
                            }
                        }
                    }
                    
                    $hasRegularproduct = (bool) $regularproduct;
                    $hasSpecialproducts = $specialproducts->count() > 0;
                    
                    // Calculate monthly total using custom price if available
                    $monthlyTotal = $activeproducts->sum(function($cp) {
                        // Use custom price if set, otherwise use product's monthly price
                        $price = $cp->product_price ?? $cp->product->monthly_price ?? 0;
                        return $price;
                    });
                    
                    // Check for due payments - consistent with controller logic
                    $hasDue = $customer->invoices()
                        ->whereIn('invoices.status', ['unpaid', 'partial'])
                        ->whereRaw('invoices.total_amount > COALESCE(invoices.received_amount, 0)')
                        ->exists();
                    
                    $totalDue = $customer->invoices()
                        ->whereIn('invoices.status', ['unpaid', 'partial'])
                        ->sum(DB::raw('invoices.total_amount - invoices.received_amount'));
                    
                    $isNew = $customer->created_at->gt(now()->subDays(7));
                    
                    // Determine row styling
                    $rowClasses = [];
                    if ($hasDue) $rowClasses[] = 'payment-due-row';
                    if ($isNew) $rowClasses[] = 'new-customer-row';
                    if (!$customer->is_active) $rowClasses[] = 'inactive-customer-row';
                    
                    $rowClass = implode(' ', $rowClasses);
                    $initialLetter = strtoupper(substr($customer->name, 0, 1));
                @endphp
                <tr class="{{ $rowClass }}" 
                    data-customer-id="{{ $customer->c_id }}" 
                    data-status="{{ $customer->is_active ? 'active' : 'inactive' }}" 
                    data-has-addons="{{ $hasSpecialproducts ? 'yes' : 'no' }}"
                    data-has-due="{{ $hasDue ? 'yes' : 'no' }}"
                    data-is-new="{{ $isNew ? 'yes' : 'no' }}">
                    
                    <!-- Customer Information Column -->
                    <td class="ps-4">
                        <div class="d-flex align-items-start">
                            <div class="customer-avatar me-3 position-relative">
                                @if($customer->profile_picture)
                                    <img src="{{ asset('storage/' . $customer->profile_picture) }}" 
                                         alt="{{ $customer->name }}" 
                                         class="avatar-circle bg-primary text-white"
                                         style="width: 64px; height: 64px; object-fit: cover;">
                                @else
                                    <div class="avatar-circle bg-primary text-white" style="width: 64px; height: 64px; font-size: 1.5rem;">
                                        {{ $initialLetter }}
                                    </div>
                                @endif
                                @if($isNew)
                                    <span class="position-absolute top-0 start-100 translate-middle badge bg-info" style="font-size: 0.5rem;">
                                        NEW
                                    </span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <a href="{{ route('admin.customers.show', $customer->c_id) }}" class="text-decoration-none" target="_blank">
                                        <strong class="me-2 text-dark">{{ $customer->name }}</strong>
                                    </a>
                                    @if(!$customer->is_active)
                                        <span class="badge bg-secondary badge-sm">Inactive</span>
                                    @endif
                                </div>
                                <div class="customer-details">
                                    <div class="text-muted small mb-1">
                                        <i class="fas fa-id-card me-1"></i>
                                        <span class="fw-medium">{{ $customer->customer_id }}</span>
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="fas fa-envelope me-1"></i>
                                        {{ $customer->email ?? 'No email' }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-phone me-1"></i>
                                        {{ $customer->phone ?? 'No phone' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- products Column -->
                    <td>
                        @if($activeproducts->count() > 0)
                            <!-- Show all products by name and price only -->
                            <div class="products-list">
                                @foreach($activeproducts as $cp)
                                    <div class="product-item mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-box text-primary me-2"></i>
                                                <div>
                                                    <div class="product-name fw-semibold text-dark small">
                                                        {{ $cp->product->name ?? 'Unknown product' }}
                                                    </div>
                                                    <div class="product-price text-success small">
                                                        @php
                                                            $price = $cp->product_price ?? $cp->product->monthly_price ?? 0;
                                                            $billingCycle = $cp->billing_cycle_months ?? 1;
                                                            $displayBilling = match($billingCycle) {
                                                                1 => 'Monthly',
                                                                3 => '3 Months',
                                                                6 => '6 Months',
                                                                12 => 'Annual',
                                                                default => $billingCycle . ' Month' . ($billingCycle > 1 ? 's' : '')
                                                            };
                                                        @endphp
                                                        ৳{{ number_format($price, 2) }}/{{$displayBilling}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="no-product text-center py-2">
                                <i class="fas fa-exclamation-triangle text-warning fa-lg mb-2"></i>
                                <div class="text-muted small">No Active product</div>
                                <a href="{{ route('admin.customer-to-products.assign') }}" class="btn btn-sm btn-outline-primary mt-1">
                                    Assign product
                                </a>
                            </div>
                        @endif
                    </td>

                    <!-- Billing Column -->
                    <td class="text-center">
                        <div class="billing-info">
                            <div class="monthly-total">
                                <strong class="text-success fs-6">৳{{ number_format($monthlyTotal, 2) }}</strong>
                                <div class="text-muted small">Monthly</div>
                            </div>
                            
                            @if($hasDue && $totalDue > 0)
                                <!-- <div class="due-amount mt-2">
                                    <div class="alert alert-danger py-1 px-2 mb-0 border-0">
                                        <small class="fw-semibold">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            ৳{{ number_format($totalDue, 2 ) }} due
                                        </small>
                                    </div>
                                </div> -->
                            @elseif($monthlyTotal > 0)
                                <div class="payment-status mt-2">
                                    <span class="badge bg-success badge-sm">
                                        <i class="fas fa-check me-1"></i>Paid
                                    </span>
                                </div>
                            @endif
                        </div>
                    </td>

                    <!-- Status Column -->
                    <td class="text-center">
                        <div class="status-indicators">
                            <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }} mb-1">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($hasDue)
                                <div class="due-indicator small text-danger">
                                    <i class="fas fa-clock me-1"></i>Payment Due
                                </div>
                            @endif
                        </div>
                    </td>

                    <!-- Registration Column -->
                    <td class="text-center">
                        <div class="registration-info">
                            <div class="date fw-semibold text-dark">
                                {{ $customer->created_at->format('M j, Y') }}
                            </div>
                            <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                        </div>
                    </td>

                    <!-- Actions Column -->
                   <td class="text-center pe-4">
                        <div class="action-buttons d-flex justify-content-center gap-1">
                            <!-- View Details -->
                            <a href="{{ route('admin.customers.show', $customer->c_id) }}" 
                            class="btn btn-sm btn-outline-info action-btn" 
                            title="View Details"
                            data-bs-toggle="tooltip" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>

                            <!-- Edit Customer -->
                            <a href="{{ route('admin.customers.edit', $customer->c_id) }}" 
                            class="btn btn-sm btn-outline-warning action-btn" 
                            title="Edit Customer"
                            data-bs-toggle="tooltip">
                                <i class="fas fa-edit"></i>
                            </a>

                            <!-- Toggle Status -->
                            <button type="button" 
                                class="btn btn-sm btn-outline-{{ $customer->is_active ? 'warning' : 'success' }} action-btn toggle-status-btn" 
                                title="{{ $customer->is_active ? 'Deactivate' : 'Activate' }}"
                                data-bs-toggle="tooltip"
                                data-customer-id="{{ $customer->c_id }}"
                                data-customer-name="{{ $customer->name }}"
                                data-current-status="{{ $customer->is_active ? 'active' : 'inactive' }}"
                                data-action-url="{{ route('admin.customers.toggle-status', $customer->c_id) }}">
                                <i class="fas fa-{{ $customer->is_active ? 'pause' : 'play' }}"></i>
                            </button>

                            <!-- Delete Customer -->
                            <!-- <button type="button" 
                                    class="btn btn-sm btn-outline-danger action-btn delete-customer-btn"
                                    title="Delete Customer"
                                    data-customer-id="{{ $customer->c_id }}"
                                    data-customer-name="{{ $customer->name }}">
                                <i class="fas fa-trash"></i>
                            </button> -->

                        </div>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($customers->hasPages())
        <div class="card-footer bg-white border-top-0 pt-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small mb-2 mb-md-0">
                    Showing <strong>{{ $customers->firstItem() }}</strong> to <strong>{{ $customers->lastItem() }}</strong> of <strong>{{ $customers->total() }}</strong> customers
                </div>
                <nav aria-label="Customer pagination" class="pagination-container">
                    {{ $customers->appends(request()->query())->links('pagination.bootstrap-5') }}
                </nav>
            </div>
        </div>
    @endif

@else
    <!-- Empty State -->
    <div class="text-center py-5">
        <div class="empty-state-icon mb-3">
            <i class="fas fa-users fa-4x text-muted opacity-25"></i>
        </div>
        <h4 class="text-muted mb-2">No Customers Found</h4>
        <p class="text-muted mb-4">
            @if(request()->has('search') || request()->has('status') || request()->has('filter'))
                No customers match your current search criteria.
            @else
                Get started by adding your first customer to the system.
            @endif
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Add First Customer
            </a>
            @if(request()->has('search') || request()->has('status') || request()->has('filter'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear Filters
                </a>
            @endif
        </div>
    </div>
@endif