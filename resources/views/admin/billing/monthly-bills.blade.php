@extends('layouts.admin')

@section('title', 'Monthly Bills - Admin Dashboard')

@section('content')
    <div class="container-fluid p-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 mb-0 page-title">
                    <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Monthly Bills
                </h2>
                
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.billing.invoices') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                <button class="btn btn-outline-primary">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateBillModal">
                    <i class="fas fa-plus me-1"></i>Generate Bill
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-2">Total Revenue</h6>
                                <h3 class="mb-0">৳ 45,250</h3>
                            </div>
                            <div class="avatar-sm bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <p class="text-success mt-3 mb-0">
                            <i class="fas fa-arrow-up me-1"></i> 15.2% from last month
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-2">Pending Bills</h6>
                                <h3 class="mb-0">12</h3>
                            </div>
                            <div class="avatar-sm bg-warning rounded-circle text-white d-flex align-items-center justify-content-center">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <p class="text-danger mt-3 mb-0">
                            <i class="fas fa-exclamation-circle me-1"></i> 3 overdue bills
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-2">Paid Bills</h6>
                                <h3 class="mb-0">38</h3>
                            </div>
                            <div class="avatar-sm bg-success rounded-circle text-white d-flex align-items-center justify-content-center">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <p class="text-success mt-3 mb-0">
                            <i class="fas fa-arrow-up me-1"></i> 10 more than last month
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-2">Avg. Bill Amount</h6>
                                <h3 class="mb-0">৳ 905 <small class="text-muted">/month</small></h3>
                            </div>
                            <div class="avatar-sm bg-info rounded-circle text-white d-flex align-items-center justify-content-center">
                                <i class="fas fa-calculator"></i>
                            </div>
                        </div>
                        <p class="text-success mt-3 mb-0">
                            <i class="fas fa-arrow-up me-1"></i> Higher than last month
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 search-box" placeholder="Search bills, customers...">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary filter-btn active">All Bills</button>
                                <button class="btn btn-outline-secondary filter-btn">Paid</button>
                                <button class="btn btn-outline-secondary filter-btn">Pending</button>
                                <button class="btn btn-outline-secondary filter-btn">Overdue</button>
                            </div>
                            <select class="form-select" style="width: auto;">
                                <option>All Customers</option>
                                <option>Business Customers</option>
                                <option>Individual Customers</option>
                            </select>
                            <input type="month" class="form-control" style="width: auto;" value="2024-01">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bills Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Monthly Bills - January 2024
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice ID</th>
                                <th>Customer Info</th>
                                <th>Services</th>
                                <th>Bill Amount</th>
                                <th>Previous Due</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Bill 1 - Single Regular Package -->
                            <tr>
                                <td class="fw-bold">#INV-2024-001</td>
                                <!-- In your bills table, replace the customer info section -->
<td>
    <div class="customer-info">
        <div class="d-flex align-items-start mb-2">
            <div class="customer-avatar me-3">JD</div>
            <div class="flex-grow-1">
                <!-- Make the customer name clickable -->
                <a href="{{ route('admin.customers.show', 1) }}" class="text-decoration-none">
                  <strong class="d-block text-primary hover-underline">John Doe</strong>
                </a>
                <small class="text-muted d-block">
                    <i class="fas fa-envelope me-1"></i>john.doe@example.com
                </small>
                <small class="text-muted d-block">
                    <i class="fas fa-phone me-1"></i>+8801712345678
                </small>
                <small class="text-muted">
                    <i class="fas fa-map-marker-alt me-1"></i>Gulshan, Dhaka
                </small>
            </div>
        </div>
    </div>
</td>
                                <td>
                                    <div class="services-tags">
                                        <div class="package-line">
                                            <span class="badge bg-primary">Basic Speed</span>
                                        </div>
                                        <div class="package-line">
                                            <small class="text-muted">৳500/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="bill-amount">
                                        <span class="fw-medium">৳550</span>
                                        <small class="text-muted d-block">Service: ৳50</small>
                                        <small class="text-muted d-block">Package: ৳500</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">৳0</span>
                                </td>
                                <td>
                                    <span class="fw-bold">৳550</span>
                                </td>
                                 <td>
    <span class="badge" style="background-color: #06d6a0; color: white; padding: 6px 12px; border-radius: 20px;">Paid</span>
</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Button -->
                                        <a href="{{ route('admin.billing.view-bill', 1) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                       
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Bill 2 - Regular + Special Package -->
                            <tr>
                                <td class="fw-bold">#INV-2024-002</td>
                                <!-- For Alice Smith -->
<td>
    <div class="customer-info">
        <div class="d-flex align-items-start mb-2">
            <div class="customer-avatar me-3" style="background-color: #ef476f;">AS</div>
            <div class="flex-grow-1">
                <a href="{{ route('admin.customers.show', 2) }}" class="text-decoration-none">
                    <strong class="d-block text-primary hover-underline">Alice Smith</strong>
                </a>
                <small class="text-muted d-block">
    <i class="fas fa-envelope me-1"></i>alice.smith@example.com
</small>
<small class="text-muted d-block">
    <i class="fas fa-phone me-1"></i>+8801812345679
</small>
<small class="text-muted">
    <i class="fas fa-map-marker-alt me-1"></i>Uttara, Dhaka
</small>
            </div>
        </div>
    </div>
</td>
                                <td>
                                    <div class="services-tags">
                                        <div class="package-line">
                                            <span class="badge bg-success">Fast Speed</span>
                                            <span class="badge bg-warning">Gaming Boost</span>
                                        </div>
                                        <div class="package-line">
                                            <small class="text-muted">৳800 + ৳200/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="bill-amount">
                                        <span class="fw-medium">৳1,050</span>
                                        <small class="text-muted d-block">Service: ৳50</small>
                                        <small class="text-muted d-block">Package: ৳800</small>
                                        <small class="text-muted d-block">Add-on: ৳200</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">৳0</span>
                                </td>
                                <td>
                                    <span class="fw-bold">৳1,050</span>
                                </td>
                               <td>
    <span class="badge" style="background-color: #ffd166; color: black; padding: 6px 12px; border-radius: 20px;">Pending</span>
</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.billing.view-bill', 2) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-warning" title="Edit" onclick="alert('Edit functionality will be added later')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Bill 3 - Regular + Special Package -->
                            <tr>
                                <td class="fw-bold">#INV-2024-003</td>
                                <td>
                                    <div class="customer-info">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="customer-avatar me-3" style="background-color: #06d6a0;">BJ</div>
                                            <div class="flex-grow-1">
                                                <a href="{{ route('admin.customers.show', 3) }}" class="text-decoration-none">
    <strong class="d-block text-primary hover-underline">Bob Johnson</strong>
</a>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-envelope me-1"></i>bob.johnson@example.com
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-phone me-1"></i>+8801912345680
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Banani, Dhaka
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="services-tags">
                                        <div class="package-line">
                                            <span class="badge bg-danger">Super Speed</span>
                                            <span class="badge bg-info">Streaming Plus</span>
                                        </div>
                                        <div class="package-line">
                                            <small class="text-muted">৳1,200 + ৳150/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="bill-amount">
                                        <span class="fw-medium">৳1,400</span>
                                        <small class="text-muted d-block">Service: ৳50</small>
                                        <small class="text-muted d-block">Package: ৳1,200</small>
                                        <small class="text-muted d-block">Add-on: ৳150</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium text-danger">৳500</span>
                                </td>
                                <td>
                                    <span class="fw-bold">৳1,900</span>
                                </td>
                                <td>
    <span class="badge" style="background-color: #ef476f; color: white; padding: 6px 12px; border-radius: 20px;">Overdue</span>
</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.billing.view-bill', 3) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-warning" title="Edit" onclick="alert('Edit functionality will be added later')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Bill 4 - Single Regular Package -->
                            <tr>
                                <td class="fw-bold">#INV-2024-004</td>
                                <td>
                                    <div class="customer-info">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="customer-avatar me-3" style="background-color: #ff9e00;">CW</div>
                                            <div class="flex-grow-1">
                                                <a href="{{ route('admin.customers.show', 4) }}" class="text-decoration-none">
    <strong class="d-block text-primary hover-underline">Carol White</strong>
</a>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-envelope me-1"></i>carol.white@example.com
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-phone me-1"></i>+8801612345681
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Dhanmondi, Dhaka
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="services-tags">
                                        <div class="package-line">
                                            <span class="badge bg-success">Fast Speed</span>
                                        </div>
                                        <div class="package-line">
                                            <small class="text-muted">৳800/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="bill-amount">
                                        <span class="fw-medium">৳850</span>
                                        <small class="text-muted d-block">Service: ৳50</small>
                                        <small class="text-muted d-block">Package: ৳800</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">৳0</span>
                                </td>
                                <td>
                                    <span class="fw-bold">৳850</span>
                                </td>
                                 <td>
    <span class="badge" style="background-color: #06d6a0; color: white; padding: 6px 12px; border-radius: 20px;">Paid</span>
</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.billing.view-bill', 4) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                       
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Bill 5 - Regular + Special Package -->
                            <tr>
                                <td class="fw-bold">#INV-2024-005</td>
                                <td>
                                    <div class="customer-info">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="customer-avatar me-3" style="background-color: #7209b7;">DG</div>
                                            <div class="flex-grow-1">
                                               <a href="{{ route('admin.customers.show', 5) }}" class="text-decoration-none">
    <strong class="d-block text-primary hover-underline">David Green</strong>
</a>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-envelope me-1"></i>david.green@example.com
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-phone me-1"></i>+8801512345682
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Mirpur, Dhaka
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="services-tags">
                                        <div class="package-line">
                                            <span class="badge bg-danger">Super Speed</span>
                                            <span class="badge bg-purple">Family Pack</span>
                                        </div>
                                        <div class="package-line">
                                            <small class="text-muted">৳1,200 + ৳300/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="bill-amount">
                                        <span class="fw-medium">৳1,550</span>
                                        <small class="text-muted d-block">Service: ৳50</small>
                                        <small class="text-muted d-block">Package: ৳1,200</small>
                                        <small class="text-muted d-block">Add-on: ৳300</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">৳0</span>
                                </td>
                                <td>
                                    <span class="fw-bold">৳1,550</span>
                                </td>
                                 <td>
    <span class="badge" style="background-color: #06d6a0; color: white; padding: 6px 12px; border-radius: 20px;">Paid</span>
</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.billing.view-bill', 5) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                       
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing 1 to 5 of 50 bills
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Bill Modal -->
    <div class="modal fade" id="generateBillModal" tabindex="-1" aria-labelledby="generateBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateBillModalLabel">Generate Monthly Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="billCalculationForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Customer</label>
                                <select class="form-select">
                                    <option value="1">John Doe</option>
                                    <option value="2">Alice Smith</option>
                                    <option value="3">Bob Johnson</option>
                                    <option value="4">Carol White</option>
                                    <option value="5">David Green</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Billing Month</label>
                                <input type="month" class="form-control" value="2024-01">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Regular Package</label>
                                <select class="form-select" id="regularPackage">
                                    <option value="500">Basic Speed (৳500)</option>
                                    <option value="800" selected>Fast Speed (৳800)</option>
                                    <option value="1200">Super Speed (৳1,200)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Special Add-ons</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="200" id="gamingBoost">
                                    <label class="form-check-label" for="gamingBoost">
                                        Gaming Boost (৳200)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="150" id="streamingPlus">
                                    <label class="form-check-label" for="streamingPlus">
                                        Streaming Plus (৳150)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="300" id="familyPack">
                                    <label class="form-check-label" for="familyPack">
                                        Family Pack (৳300)
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Discount</label>
                                <select class="form-select discount-select" id="discount">
                                    <option value="0">0%</option>
                                    <option value="5">5%</option>
                                    <option value="10">10%</option>
                                    <option value="15">15%</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Bill Calculation</h6>
                                        <div class="calculation-breakdown">
                                            <div class="breakdown-item">Service Charge: ৳50</div>
                                            <div class="breakdown-item" id="regularPackageDisplay">+ Regular Package: ৳800</div>
                                            <div class="breakdown-item" id="specialPackagesDisplay">+ Special Packages: ৳0</div>
                                            <div class="breakdown-item" id="vatDisplay">+ VAT (7%): ৳59.50</div>
                                            <div class="breakdown-item" id="discountDisplay">- Discount (0%): ৳0</div>
                                            <div class="total-amount" id="totalDisplay">TOTAL: ৳909.50</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="redirectToGenerateBill()">Generate Bill</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    :root {
        --primary: #4361ee;
        --success: #06d6a0;
        --warning: #ffd166;
        --danger: #ef476f;
        --dark: #2b2d42;
        --light: #f8f9fa;
        --purple: #7209b7;
    }
    
    body {
        background-color: #f5f7fb;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }
    
    .card-header {
        background: white;
        border-bottom: 1px solid #eaeaea;
        border-radius: 12px 12px 0 0 !important;
        padding: 20px 25px;
    }
    
    .stat-card {
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    /* Fixed Status Badge Colors */
    .badge-paid {
        background-color: #06d6a0 !important;
        color: white !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .badge-pending {
        background-color: #ffd166 !important;
        color: #000 !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .badge-overdue {
        background-color: #ef476f !important;
        color: white !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table td {
        vertical-align: middle;
        padding: 16px 12px;
    }
    
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .customer-info {
        min-width: 250px;
    }
    
    .services-tags {
        min-width: 150px;
    }
    
    .package-line {
        margin-bottom: 4px;
    }
    
    .services-tags .badge {
        margin-right: 4px;
        font-size: 0.75rem;
    }
    
    .bill-amount {
        min-width: 120px;
    }
    
    .btn-primary {
        background-color: var(--primary);
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
    }
    
    .btn-sm {
        border-radius: 6px;
        padding: 6px 12px;
    }
    
    .filter-btn.active {
        background-color: var(--primary);
        color: white;
    }
    
    .monthly-bill-btn {
        min-width: 120px;
        font-weight: 600;
    }
    
    .search-box {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 10px 15px;
    }
    
    .page-title {
        color: var(--dark);
        font-weight: 700;
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0;
    }
    
    .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
    }
    
    .calculation-breakdown {
        font-size: 0.8rem;
        color: #6c757d;
        line-height: 1.4;
    }
    
    .breakdown-item {
        margin-bottom: 2px;
    }
    
    .total-amount {
        font-weight: 700;
        color: var(--dark);
        font-size: 1rem;
        border-top: 1px solid #dee2e6;
        padding-top: 4px;
        margin-top: 4px;
    }
    
    .discount-select {
        max-width: 100px;
        display: inline-block;
    }
    .hover-underline:hover {
    text-decoration: underline !important;
}

.customer-info a:hover {
    color: #0056b3 !important;
}
    
    /* Service badge colors */
    .badge.bg-primary { background-color: var(--primary) !important; }
    .badge.bg-success { background-color: var(--success) !important; }
    .badge.bg-danger { background-color: var(--danger) !important; }
    .badge.bg-warning { background-color: var(--warning) !important; color: #000; }
    .badge.bg-info { background-color: #17a2b8 !important; }
    .badge.bg-purple { background-color: var(--purple) !important; }
    
    .due-date-info {
        min-width: 100px;
    }
</style>
@endsection

@section('scripts')
<script>
    // Simple filter button activation
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    // Bill Calculation Logic
    function calculateBill() {
        const serviceCharge = 50;
        const regularPackage = parseInt(document.getElementById('regularPackage').value);
        const vatRate = 0.07;
        const discountRate = parseInt(document.getElementById('discount').value) / 100;
        
        let specialPackages = 0;
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            specialPackages += parseInt(checkbox.value);
        });
        
        const subtotal = serviceCharge + regularPackage + specialPackages;
        const vatAmount = subtotal * vatRate;
        const discountAmount = subtotal * discountRate;
        const total = subtotal + vatAmount - discountAmount;
        
        // Update display
        document.getElementById('regularPackageDisplay').textContent = `+ Regular Package: ৳${regularPackage}`;
        document.getElementById('specialPackagesDisplay').textContent = `+ Special Packages: ৳${specialPackages}`;
        document.getElementById('vatDisplay').textContent = `+ VAT (7%): ৳${vatAmount.toFixed(2)}`;
        document.getElementById('discountDisplay').textContent = `- Discount (${discountRate * 100}%): ৳${discountAmount.toFixed(2)}`;
        document.getElementById('totalDisplay').textContent = `TOTAL: ৳${total.toFixed(2)}`;
    }

    // Add event listeners for calculation
    document.getElementById('regularPackage').addEventListener('change', calculateBill);
    document.getElementById('discount').addEventListener('change', calculateBill);
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', calculateBill);
    });

    // Redirect to generate bill page from modal
    function redirectToGenerateBill() {
        const customerSelect = document.querySelector('#generateBillModal select');
        const customerId = customerSelect.value;
        window.location.href = `/admin/billing/generate-bill/${customerId}`;
    }

    // Initial calculation
    calculateBill();
</script>
@endsection