@extends('layouts.admin')

@section('title', 'Billing & Invoices - Admin Dashboard')

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-file-invoice me-2 text-primary"></i>Billing & Invoices
            </h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary">
                <i class="fas fa-download me-1"></i>Export Report
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBillingModal">
                <i class="fas fa-plus me-1"></i>Add Month Billing
            </button>
        </div>
    </div>

    <!-- Billing Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>All Monthly Billing Overview
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Billing Month</th>
                            <th>Total Customers</th>
                            <th>Total Amount</th>
                            <th>Received Amount</th>
                            <th>Due Amount</th>
                            <th>Status</th>
                            <th>Monthly Billings</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1 -->
                        <tr>
                            <td><strong>January 2025</strong></td>
                            <td>50</td>
                            <td>৳ 45,2500</td>
                            <td>৳ 45,250</td>
                            <td>৳ 0</td>
                            <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                    
                                </div>
                            </td>
                        </tr>

                        <!-- Row 2 -->
                        <tr>
                            <td><strong>February 2025</strong></td>
                            <td>55</td>
                            <td>৳ 49,500</td>
                            <td>৳ 46,000</td>
                            <td>৳ 3,500</td>
                            <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                    
                                </div>
                            </td>
                        </tr>

                        <!-- Row 3 -->
                        <tr>
                            <td><strong>March 2025</strong></td>
                            <td>58</td>
                            <td>৳ 52,000</td>
                            <td>৳ 48,000</td>
                            <td>৳ 4,000</td>
                            <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                    
                                </div>
                            </td>
                        </tr>

                        
                        <!-- Row 4 -->
                        <tr>
                            <td><strong>April 2025</strong></td>
                            <td>60</td>
                            <td>৳ 55,000</td>
                            <td>৳ 50,000</td>
                            <td>৳ 5,000</td>
                            <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                   
                                </div>
                            </td>
                        </tr>
                       
                         <!-- Row 5 -->
                        <tr>
                            <td><strong>May 2025</strong></td>
                            <td>60</td>
                            <td>৳ 55,000</td>
                            <td>৳ 55,000</td>
                            <td>৳ 0</td>
                            <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                    
                                </div>
                            </td>
                        </tr>
                         <!-- Row 5 -->
                        <tr>
                            <td><strong>June 2025</strong></td>
                            <td>60</td>
                            <td>৳ 55,000</td>
                            <td>৳ 55,000</td>
                            <td>৳ 0</td>
                            <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                   
                                </div>
                            </td>
                        </tr>
                         <!-- Row 5 -->
                        <tr>
                            <td><strong>July 2025</strong></td>
                            <td>60</td>
                            <td>৳ 55,000</td>
                            <td>৳ 50,000</td>
                            <td>৳ 5,000</td>
                            <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                    
                                </div>
                            </td>
                        </tr>
                         <!-- Row 6 -->
                        <tr>
                            <td><strong>August 2025</strong></td>
                            <td>60</td>
                            <td>৳ 55,000</td>
                            <td>৳ 50,000</td>
                            <td>৳ 5,000</td>
                           <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                    
                                </div>
                            </td>
                        </tr>
                         <!-- Row 7 -->
                        <tr>
                            <td><strong>September 2025</strong></td>
                            <td>60</td>
                            <td>৳ 55,000</td>
                            <td>৳ 55,000</td>
                            <td>৳ 0</td>
                            <td><span class="badge" style="background-color:#28a745; color:#fff;">Paid</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                   
                                </div>
                            </td>
                        </tr>
                         <!-- Row 8 -->
                        <tr>
                            <td><strong>October 2025</strong></td>
                            <td>60</td>
                            <td>৳ 55,000</td>
                            <td>৳ 50,000</td>
                            <td>৳ 5,000</td>
                            <td><span class="badge badge-overdue" style="background-color:#ffc107; color:#000;">Pending</span></td>

                            <td>
                                <a href="{{ url('admin/billing/monthly-bills') }}" class="btn btn-outline-primary btn-sm monthly-bill-btn">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Monthly Bills
                                </a>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-outline-warning"><i class="fas fa-edit"></i></button>
                                    
                                </div>
                            </td>
                        </tr>
                       
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-center">
            <small class="text-muted">Showing 1 to 10 of 12 months</small>
        </div>
    </div>
</div>

<!-- Add Month Modal -->
<div class="modal fade" id="addBillingModal" tabindex="-1" aria-labelledby="addBillingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Monthly Billing Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Billing Month</label>
                        <input type="month" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Customers</label>
                        <input type="number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Received Amount</label>
                        <input type="number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Amount</label>
                        <input type="number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>All Paid</option>
                            <option>Pending</option>
                            <option>Overdue</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
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

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        padding: 14px 12px;
        font-size: 0.9rem;
    }

    .badge-paid {
        background-color: var(--success);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
    }

    .badge-pending {
        background-color: var(--warning);
        color: black;
        padding: 6px 12px;
        border-radius: 20px;
    }

    .badge-overdue {
        background-color: var(--danger);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
    }

    .monthly-bill-btn {
        font-weight: 500;
        border-radius: 8px;
    }

    .btn-sm {
        border-radius: 8px;
        padding: 5px 10px;
    }
</style>
@endsection
