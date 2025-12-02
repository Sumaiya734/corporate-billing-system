@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')

<style>
/* ------------------------------ */
/*  PREMIUM ADVANCED THEME STYLES */
/* ------------------------------ */

/* Color Palette (Soft, Eye-Soothing) */
:root {
    --soft-blue: #6EA8FE;
    --soft-purple: #A78BFA;
    --soft-cyan: #67E8F9;
    --soft-lavender: #C4B5FD;
    --soft-green: #86EFAC;
    --soft-orange: #FDBA74;
    --soft-pink: #F9A8D4;
    --dark-text: #1f2937;
}

/* Smooth fade animation */
.fade-in {
    animation: fadeIn 0.9s ease forwards;
    opacity: 0;
}
@keyframes fadeIn {
    to { opacity: 1; }
}

/* Soft slide animation */
.slide-up {
    animation: slideUp 0.8s ease forwards;
    opacity: 0;
    transform: translateY(20px);
}
@keyframes slideUp {
    to { opacity: 1; transform: translateY(0); }
}

/* Animated gradient cards */
.advanced-card {
    border-radius: 18px;
    padding: 28px;
    color: #fff;
    border: none;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: 0.35s ease;
}

.advanced-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 40px rgba(0,0,0,0.12);
}

.gradient-1 { background: linear-gradient(135deg, var(--soft-blue), var(--soft-purple)); }
.gradient-2 { background: linear-gradient(135deg, var(--soft-green), var(--soft-cyan)); }
.gradient-3 { background: linear-gradient(135deg, var(--soft-orange), #F59E0B); }
.gradient-4 { background: linear-gradient(135deg, var(--soft-pink), var(--soft-lavender)); }

.icon-bg {
    position: absolute;
    right: -15px;
    top: -15px;
    font-size: 90px;
    opacity: 0.18;
}

/* Clean white cards */
.glass-card {
    border-radius: 16px;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.35);
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    transition: 0.3s ease;
}

.glass-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.08);
}

/* Quick Action Buttons */
.quick-btn {
    border-radius: 14px !important;
    padding: 18px !important;
    font-weight: 600;
    transition: 0.25s ease;
    background: rgba(255,255,255,0.85);
    border: 2px solid #e5e7eb;
}

.quick-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.10);
    border-color: transparent;
}

.quick-btn i {
    font-size: 32px;
    margin-right: 14px;
}
</style>


<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <h2 class="fw-bold text-dark">
        <i class="fas fa-tachometer-alt text-primary me-2"></i> Dashboard Overview
    </h2>

    <div>
        <button class="btn btn-light border me-2 shadow-sm">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
        <button class="btn btn-primary shadow-sm">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
</div>


<!-- GRADIENT STAT CARDS -->
<div class="row g-4 mb-4">

    <div class="col-xl-3 col-md-6 slide-up" style="animation-delay: .1s">
        <div class="advanced-card gradient-1">
            <div class="icon-bg"><i class="fas fa-users"></i></div>
            <h6>Total Customers</h6>
            <h2>{{ $totalCustomers ?? 0 }}</h2>
            <small>Active subscribers</small>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 slide-up" style="animation-delay: .2s">
        <div class="advanced-card gradient-2">
            <div class="icon-bg"><i class="fas fa-money-bill-wave"></i></div>
            <h6>Monthly Revenue</h6>
            <h2>৳{{ number_format($monthlyRevenue ?? 0, 2) }}</h2>
            <small>Current month</small>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 slide-up" style="animation-delay: .3s">
        <div class="advanced-card gradient-3">
            <div class="icon-bg"><i class="fas fa-clock"></i></div>
            <h6>Pending Bills</h6>
            <h2>{{ $pendingBills ?? 0 }}</h2>
            <small>Awaiting payment</small>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 slide-up" style="animation-delay: .4s">
        <div class="advanced-card gradient-4">
            <div class="icon-bg"><i class="fas fa-cube"></i></div>
            <h6>Active Products</h6>
            <h2>{{ $activeproducts ?? 0 }}</h2>
            <small>Total products</small>
        </div>
    </div>

</div>


<!-- CLEAN WHITE STATS -->
<div class="row g-4 mb-4">

    <div class="col-lg-4 col-md-6 slide-up" style="animation-delay: .5s">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted">Overdue Bills</h6>
                    <h3 class="text-danger fw-bold">{{ $overdueBills ?? 0 }}</h3>
                </div>
                <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 slide-up" style="animation-delay: .6s">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted">Paid Invoices</h6>
                    <h3 class="text-success fw-bold">{{ $paidInvoices ?? 0 }}</h3>
                </div>
                <i class="fas fa-check-circle text-success fa-2x"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 slide-up" style="animation-delay: .7s">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted">New Customers</h6>
                    <h3 class="text-primary fw-bold">{{ $newCustomers ?? 0 }}</h3>
                </div>
                <i class="fas fa-user-plus text-primary fa-2x"></i>
            </div>
        </div>
    </div>

</div>


<!-- QUICK ACTIONS -->
<div class="glass-card mt-4 slide-up" style="animation-delay: .8s">
    <div class="card-header bg-white border-0">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-bolt text-warning me-2"></i> Quick Actions</h5>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.customers.create') }}" class="quick-btn w-100 d-flex align-items-center">
                    <i class="fas fa-user-plus text-primary"></i>
                    <div>
                        Add Customer<br><small class="text-muted">Register new customer</small>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.billing.billing-invoices') }}" class="quick-btn w-100 d-flex align-items-center">
                    <i class="fas fa-file-invoice-dollar text-success"></i>
                    <div>
                        Generate Bills<br><small class="text-muted">Create invoices</small>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="#" class="quick-btn w-100 d-flex align-items-center">
                    <i class="fas fa-chart-line text-info"></i>
                    <div>
                        View Reports<br><small class="text-muted">Financial insights</small>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="#" class="quick-btn w-100 d-flex align-items-center">
                    <i class="fas fa-bell text-warning"></i>
                    <div>
                        Send Alerts<br><small class="text-muted">Payment reminders</small>
                    </div>
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
