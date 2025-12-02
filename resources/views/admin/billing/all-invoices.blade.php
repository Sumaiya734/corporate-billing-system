@extends('layouts.admin')

@section('title', 'All Invoices - Admin Dashboard')

@section('content')
    <div class="page-body p-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 mb-0 page-title">
                    <i class="fas fa-file-invoice me-2 text-primary"></i>All Invoices
                </h2>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                    <i class="fas fa-plus me-1"></i>Create Invoice
                </button>
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
                            <input type="text" 
                                   id="invoiceSearch" 
                                   class="form-control border-start-0 search-box" 
                                   placeholder="Search by invoice #, customer name, email, phone...">
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Try: INV-2024-001, Name, test@example.com, +880...
                        </small>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary filter-btn active">All Invoices</button>
                                <button class="btn btn-outline-secondary filter-btn">Paid</button>
                                <button class="btn btn-outline-secondary filter-btn">Pending</button>
                                <button class="btn btn-outline-secondary filter-btn">Overdue</button>
                            </div>
                            <select class="form-select" style="width: auto;">
                                <option>All Customers</option>
                                <option>Business Customers</option>
                                <option>Individual Customers</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>All Customer Invoices
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
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Billings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Invoice 1 - Single Regular product -->
                            <tr>
                                <td class="fw-bold">#INV-2024-001</td>
                                <td>
                                    <div class="customer-info">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="customer-avatar me-3">JD</div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">John Doe</strong>
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
                                        <div class="product-line">
                                            <span class="badge bg-primary">Basic Speed</span>
                                        </div>
                                        <div class="product-line">
                                            <small class="text-muted">৳500/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="issue-date-info">
                                        <span class="fw-medium">Jan 1, 2024</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="due-date-info">
                                        <span class="fw-medium">Jan 5, 2024</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-paid">Paid</span>
                                </td>
                                <td>
                                    <!-- FIXED: Generate Bill Button -->
                                    <a href="{{ route('admin.billing.generate-bill', ['id' => 1]) }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>Generate Bill
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Button -->
                                        <a href="{{ route('admin.billing.view-invoice', ['id' => 1]) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Edit Button -->
                                        <button class="btn btn-outline-warning" title="Edit" onclick="alert('Edit functionality will be added later')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- Delete Button -->
                                        <button class="btn btn-outline-danger" title="Delete" onclick="alert('Delete functionality will be added later')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Invoice 2 - Regular + Special product -->
                            <tr>
                                <td class="fw-bold">#INV-2024-002</td>
                                <td>
                                    <div class="customer-info">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="customer-avatar me-3" style="background-color: #ef476f;">AS</div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">Alice Smith</strong>
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
                                        <div class="product-line">
                                            <span class="badge bg-success">Fast Speed</span>
                                            <span class="badge bg-warning">Gaming Boost</span>
                                        </div>
                                        <div class="product-line">
                                            <small class="text-muted">৳800 + ৳200/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="issue-date-info">
                                        <span class="fw-medium">Jan 1, 2024</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="due-date-info">
                                        <span class="fw-medium">Jan 5, 2024</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-pending">Pending</span>
                                </td>
                                <td>
                                    <!-- FIXED: Generate Bill Button -->
                                    <a href="{{ route('admin.billing.generate-bill', ['id' => 2]) }}" class="btn btn-outline-warning btn-sm monthly-bill-btn">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>Generate Bill
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.billing.view-invoice', ['id' => 2]) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-warning" title="Edit" onclick="alert('Edit functionality will be added later')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" title="Delete" onclick="alert('Delete functionality will be added later')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Invoice 3 - Regular + Special product -->
                            <tr>
                                <td class="fw-bold">#INV-2023-125</td>
                                <td>
                                    <div class="customer-info">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="customer-avatar me-3" style="background-color: #06d6a0;">BJ</div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">Bob Johnson</strong>
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
                                        <div class="product-line">
                                            <span class="badge bg-danger">Super Speed</span>
                                            <span class="badge bg-info">Streaming Plus</span>
                                        </div>
                                        <div class="product-line">
                                            <small class="text-muted">৳1,200 + ৳150/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="issue-date-info">
                                        <span class="fw-medium">Dec 1, 2023</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="due-date-info">
                                        <span class="fw-medium text-danger">Dec 25, 2023</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-overdue">Overdue</span>
                                </td>
                                <td>
                                    <!-- FIXED: Generate Bill Button -->
                                    <a href="{{ route('admin.billing.generate-bill', ['id' => 3]) }}" class="btn btn-outline-danger btn-sm monthly-bill-btn">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>Generate Bill
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.billing.view-invoice', ['id' => 3]) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-warning" title="Edit" onclick="alert('Edit functionality will be added later')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" title="Delete" onclick="alert('Delete functionality will be added later')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Invoice 4 - Single Regular product -->
                            <tr>
                                <td class="fw-bold">#INV-2023-098</td>
                                <td>
                                    <div class="customer-info">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="customer-avatar me-3" style="background-color: #ff9e00;">CW</div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">Carol White</strong>
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
                                        <div class="product-line">
                                            <span class="badge bg-success">Fast Speed</span>
                                        </div>
                                        <div class="product-line">
                                            <small class="text-muted">৳800/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="issue-date-info">
                                        <span class="fw-medium">Nov 1, 2023</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="due-date-info">
                                        <span class="fw-medium">Nov 5, 2023</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-paid">Paid</span>
                                </td>
                                <td>
                                    <!-- FIXED: Generate Bill Button -->
                                    <a href="{{ route('admin.billing.generate-bill', ['id' => 4]) }}" class="btn btn-outline-success btn-sm monthly-bill-btn">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>Generate Bill
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.billing.view-invoice', ['id' => 4]) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-warning" title="Edit" onclick="alert('Edit functionality will be added later')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" title="Delete" onclick="alert('Delete functionality will be added later')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Invoice 5 - Regular + Special product -->
                            <tr>
                                <td class="fw-bold">#INV-2023-076</td>
                                <td>
                                    <div class="customer-info">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="customer-avatar me-3" style="background-color: #7209b7;">DG</div>
                                            <div class="flex-grow-1">
                                                <strong class="d-block">David Green</strong>
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
                                        <div class="product-line">
                                            <span class="badge bg-danger">Super Speed</span>
                                            <span class="badge bg-purple">Family Pack</span>
                                        </div>
                                        <div class="product-line">
                                            <small class="text-muted">৳1,200 + ৳300/month</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="issue-date-info">
                                        <span class="fw-medium">Oct 1, 2023</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="due-date-info">
                                        <span class="fw-medium">Oct 5, 2023</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-paid">Paid</span>
                                </td>
                                <td>
                                    <!-- FIXED: Generate Bill Button -->
                                    <a href="{{ route('admin.billing.generate-bill', ['id' => 5]) }}" class="btn btn-outline-warning btn-sm monthly-bill-btn">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>Generate Bill
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.billing.view-invoice', ['id' => 5]) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-warning" title="Edit" onclick="alert('Edit functionality will be added later')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" title="Delete" onclick="alert('Delete functionality will be added later')">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                        Showing 1 to 5 of 215 invoices
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

    <!-- Create Invoice Modal -->
    <div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createInvoiceModalLabel">Create New Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="invoiceCreationForm">
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
                                <label class="form-label">Invoice Date</label>
                                <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" value="{{ date('Y-m-d', strtotime('+5 days')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invoice Status</label>
                                <select class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="overdue">Overdue</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Regular product</label>
                                <select class="form-select" id="regularproduct">
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
                                        <h6 class="card-title">Invoice Calculation</h6>
                                        <div class="calculation-breakdown">
                                            <div class="breakdown-item">Service Charge: ৳50</div>
                                            <div class="breakdown-item" id="regularproductDisplay">+ Regular product: ৳800</div>
                                            <div class="breakdown-item" id="specialproductsDisplay">+ Special products: ৳0</div>
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
                    <button type="button" class="btn btn-primary" onclick="createNewInvoice()">Create Invoice</button>
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
    
    /* Status Badge Colors */
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
    
    .product-line {
        margin-bottom: 4px;
    }
    
    .services-tags .badge {
        margin-right: 4px;
        font-size: 0.75rem;
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
    
    /* Service badge colors */
    .badge.bg-primary { background-color: var(--primary) !important; }
    .badge.bg-success { background-color: var(--success) !important; }
    .badge.bg-danger { background-color: var(--danger) !important; }
    .badge.bg-warning { background-color: var(--warning) !important; color: #000; }
    .badge.bg-info { background-color: #17a2b8 !important; }
    .badge.bg-purple { background-color: var(--purple) !important; }
    
    .due-date-info, .issue-date-info {
        min-width: 100px;
    }

    /* Customer avatar colors */
    .customer-avatar[style*="background-color: #ef476f"] { background-color: #ef476f !important; }
    .customer-avatar[style*="background-color: #06d6a0"] { background-color: #06d6a0 !important; }
    .customer-avatar[style*="background-color: #ff9e00"] { background-color: #ff9e00 !important; }
    .customer-avatar[style*="background-color: #7209b7"] { background-color: #7209b7 !important; }
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

    // Invoice Calculation Logic
    function calculateInvoice() {
        const serviceCharge = 50;
        const regularProduct = parseInt(document.getElementById('regularProduct').value);
        const vatRate = 0.07;
        const discountRate = parseInt(document.getElementById('discount').value) / 100;
        
        let specialProducts = 0;
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            specialProducts += parseInt(checkbox.value);
        });
        
        const subtotal = serviceCharge + regularProduct + specialProducts;
        const vatAmount = subtotal * vatRate;
        const discountAmount = subtotal * discountRate;
        const total = subtotal + vatAmount - discountAmount;
        
        // Update display
        document.getElementById('regularProductDisplay').textContent = `+ Regular Product: ৳${regularProduct}`;
        document.getElementById('specialProductsDisplay').textContent = `+ Special Products: ৳${specialProducts}`;
        document.getElementById('vatDisplay').textContent = `+ VAT (7%): ৳${vatAmount.toFixed(2)}`;
        document.getElementById('discountDisplay').textContent = `- Discount (${discountRate * 100}%): ৳${discountAmount.toFixed(2)}`;
        document.getElementById('totalDisplay').textContent = `TOTAL: ৳${total.toFixed(2)}`;
    }

    // Add event listeners for calculation
    document.getElementById('regularproduct').addEventListener('change', calculateInvoice);
    document.getElementById('discount').addEventListener('change', calculateInvoice);
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', calculateInvoice);
    });

    // Create new invoice function
    function createNewInvoice() {
        alert('New invoice creation functionality will be implemented later');
        // In a real application, this would submit the form and create a new invoice
        // For now, we'll just close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('createInvoiceModal'));
        modal.hide();
    }

    // Initial calculation
    calculateInvoice();

    // Debug function to check routes
    function debugRoutes() {
        console.log('Generate Bill Routes:');
        console.log('Customer 1:', "{{ route('admin.billing.generate-bill', ['id' => 1]) }}");
        console.log('Customer 2:', "{{ route('admin.billing.generate-bill', ['id' => 2]) }}");
        console.log('Customer 3:', "{{ route('admin.billing.generate-bill', ['id' => 3]) }}");
    }

    // Call debug on page load
    document.addEventListener('DOMContentLoaded', function() {
        debugRoutes();
    });

    // Invoice Search Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('invoiceSearch');
        const tableRows = document.querySelectorAll('tbody tr');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                tableRows.forEach(row => {
                    // Get all searchable content from the row
                    const invoiceId = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                    const customerInfo = row.querySelector('.customer-info')?.textContent.toLowerCase() || '';
                    const services = row.querySelector('.services-tags')?.textContent.toLowerCase() || '';
                    
                    // Combine all searchable text
                    const searchableText = invoiceId + ' ' + customerInfo + ' ' + services;
                    
                    // Show/hide row based on search
                    if (searchableText.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show "no results" message if all rows are hidden
                const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
                const tbody = document.querySelector('tbody');
                let noResultsRow = document.getElementById('noResultsRow');
                
                if (visibleRows.length === 0 && searchTerm !== '') {
                    if (!noResultsRow) {
                        noResultsRow = document.createElement('tr');
                        noResultsRow.id = 'noResultsRow';
                        noResultsRow.innerHTML = `
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No invoices found</h5>
                                <p class="text-muted">Try searching with different keywords</p>
                            </td>
                        `;
                        tbody.appendChild(noResultsRow);
                    }
                } else if (noResultsRow) {
                    noResultsRow.remove();
                }
            });
            
            // Add clear button functionality
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    this.dispatchEvent(new Event('input'));
                }
            });
        }
    });
</script>
@endsection