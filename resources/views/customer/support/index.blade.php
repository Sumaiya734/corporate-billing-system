@extends('layouts.customer')

@section('title', 'Support Center - Nanosoft')

@section('content')
<div class="support-page">
    <!-- Gradient Header -->
    <div class="header-gradient bg-gradient-primary rounded-4 mb-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center p-4 p-lg-5">
            <div class="mb-4 mb-lg-0">
                <div class="d-flex align-items-center mb-3">
                    <div class="support-icon-wrapper bg-white rounded-3 p-3 shadow-sm me-3">
                        <i class="fas fa-headset fa-2x text-gradient-primary"></i>
                    </div>
                    <div>
                        <h1 class="h2 fw-bold text-white mb-1">Support Center</h1>
                        <p class="text-white-70 mb-0">Expert help for all your software needs</p>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-white-20 rounded-pill px-3 py-1 text-white">
                        <i class="fas fa-shield-alt me-1"></i>Priority Support
                    </span>
                    <span class="badge bg-white-20 rounded-pill px-3 py-1 text-white">
                        <i class="fas fa-clock me-1"></i>24/7 Response
                    </span>
                    <span class="badge bg-white-20 rounded-pill px-3 py-1 text-white">
                        <i class="fas fa-certificate me-1"></i>Guaranteed Help
                    </span>
                </div>
            </div>
            <a href="{{ route('customer.support.create') }}" class="btn btn-white btn-lg shadow-lg">
                <i class="fas fa-plus-circle me-2"></i>New Support Ticket
            </a>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card card-hover border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-semibold mb-2">Open Tickets</h6>
                            <h2 class="fw-bold text-primary display-6 mb-0">{{ $openTickets }}</h2>
                            <div class="progress mt-3" style="height: 4px;">
                               <div class="progress-bar bg-primary" style="width: {{ $totalTickets > 0 ? ($openTickets/$totalTickets)*100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="icon-wrapper bg-soft-primary rounded-3 p-3">
                            <i class="fas fa-clock text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-arrow-up text-success me-1"></i>Active Issues
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stats-card card-hover border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-semibold mb-2">Resolved</h6>
                            <h2 class="fw-bold text-success display-6 mb-0">{{ $resolvedTickets }}</h2>
                            <div class="progress mt-3" style="height: 4px;">
                               <div class="progress-bar bg-success" style="width: {{ $totalTickets > 0 ? ($resolvedTickets/$totalTickets)*100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="icon-wrapper bg-soft-success rounded-3 p-3">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-check text-success me-1"></i>Completed
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stats-card card-hover border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-semibold mb-2">Total Tickets</h6>
                            <h2 class="fw-bold text-warning display-6 mb-0">{{ $totalTickets }}</h2>
                            <div class="progress mt-3" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="icon-wrapper bg-soft-warning rounded-3 p-3">
                            <i class="fas fa-ticket-alt text-warning fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-history text-warning me-1"></i>All Time
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stats-card card-hover border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-semibold mb-2">Avg. Response</h6>
                            <h2 class="fw-bold text-info display-6 mb-0">2.4h</h2>
                            <div class="progress mt-3" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: 85%"></div>
                            </div>
                        </div>
                        <div class="icon-wrapper bg-soft-info rounded-3 p-3">
                            <i class="fas fa-rocket text-info fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-bolt text-info me-1"></i>Fast Support
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Tickets & Actions -->
        <div class="col-lg-8">
            <!-- Enhanced Tickets Table -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div class="mb-3 mb-md-0">
                            <h5 class="fw-bold mb-2">
                                <i class="fas fa-list-check text-primary me-2"></i>My Support Tickets
                            </h5>
                            <p class="text-muted mb-0">Track and manage all your support requests</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="btn-group btn-group-sm shadow-sm me-3">
                                <button class="btn btn-outline-primary active" data-filter="all">
                                    <i class="fas fa-layer-group me-1"></i>All
                                </button>
                                <button class="btn btn-outline-primary" data-filter="open">
                                    <i class="fas fa-clock me-1"></i>Open
                                </button>
                                <button class="btn btn-outline-primary" data-filter="resolved">
                                    <i class="fas fa-check-circle me-1"></i>Resolved
                                </button>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-sort me-1"></i>Sort
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-calendar me-2"></i>Newest First</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-fire me-2"></i>Priority</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-a-z me-2"></i>A-Z</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($tickets->count() > 0)
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </div>
                                        </th>
                                        <th class="py-3">Ticket Details</th>
                                        <th class="py-3">Category</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3">Last Update</th>
                                        <th class="text-end pe-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tickets as $ticket)
                                        <tr class="ticket-row card-hover" data-status="{{ $ticket->status }}" 
                                            data-priority="{{ $ticket->priority }}">
                                            <td class="ps-4">
                                                <div class="form-check">
                                                    <input class="form-check-input ticket-checkbox" type="checkbox" value="{{ $ticket->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <div class="ticket-icon me-3">
                                                        @if($ticket->category == 'technical')
                                                            <div class="rounded-circle bg-soft-danger p-2">
                                                                <i class="fas fa-cog text-danger"></i>
                                                            </div>
                                                        @elseif($ticket->category == 'billing')
                                                            <div class="rounded-circle bg-soft-success p-2">
                                                                <i class="fas fa-credit-card text-success"></i>
                                                            </div>
                                                        @elseif($ticket->category == 'license')
                                                            <div class="rounded-circle bg-soft-primary p-2">
                                                                <i class="fas fa-key text-primary"></i>
                                                            </div>
                                                        @else
                                                            <div class="rounded-circle bg-soft-secondary p-2">
                                                                <i class="fas fa-question-circle text-secondary"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-semibold mb-1">{{ Str::limit($ticket->subject, 45) }}</h6>
                                                        <p class="text-muted small mb-1">{{ Str::limit($ticket->description, 60) }}</p>
                                                        <div class="d-flex align-items-center">
                                                            <small class="text-muted">
                                                                <i class="far fa-id-card me-1"></i>{{ $ticket->ticket_number }}
                                                            </small>
                                                            @if($ticket->priority == 'urgent')
                                                                <span class="badge bg-danger bg-opacity-10 text-danger ms-2">
                                                                    <i class="fas fa-fire me-1"></i>Urgent
                                                                </span>
                                                            @elseif($ticket->priority == 'high')
                                                                <span class="badge bg-warning bg-opacity-10 text-warning ms-2">
                                                                    <i class="fas fa-arrow-up me-1"></i>High
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $categoryConfig = [
                                                        'billing' => ['icon' => 'fa-credit-card', 'color' => 'success'],
                                                        'license' => ['icon' => 'fa-key', 'color' => 'primary'],
                                                        'product' => ['icon' => 'fa-box', 'color' => 'info'],
                                                        'technical' => ['icon' => 'fa-cog', 'color' => 'danger'],
                                                        'account' => ['icon' => 'fa-user', 'color' => 'secondary'],
                                                        'integration' => ['icon' => 'fa-plug', 'color' => 'purple'],
                                                        'other' => ['icon' => 'fa-question-circle', 'color' => 'dark']
                                                    ];
                                                    $cat = $categoryConfig[$ticket->category] ?? $categoryConfig['other'];
                                                @endphp
                                                <span class="badge bg-{{ $cat['color'] }} bg-opacity-10 text-{{ $cat['color'] }} rounded-pill px-3 py-1">
                                                    <i class="fas {{ $cat['icon'] }} me-1"></i>
                                                    {{ ucfirst($ticket->category) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($ticket->status == 'open')
                                                    <span class="badge bg-warning bg-opacity-15 text-warning rounded-pill px-3 py-2">
                                                        <i class="fas fa-clock me-1"></i>Open
                                                    </span>
                                                @elseif($ticket->status == 'in_progress')
                                                    <span class="badge bg-info bg-opacity-15 text-info rounded-pill px-3 py-2">
                                                        <i class="fas fa-cog fa-spin me-1"></i>In Progress
                                                    </span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-3 py-2">
                                                        <i class="fas fa-check-circle me-1"></i>Resolved
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted d-block">{{ $ticket->updated_at->format('M d') }}</small>
                                                <small class="text-muted">{{ $ticket->updated_at->diffForHumans() }}</small>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('customer.support.show', $ticket->id) }}" 
                                                       class="btn btn-outline-primary btn-icon"
                                                       data-bs-toggle="tooltip"
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-outline-secondary btn-icon"
                                                            data-bs-toggle="tooltip"
                                                            title="Share Ticket">
                                                        <i class="fas fa-share-alt"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info btn-icon"
                                                            data-bs-toggle="tooltip"
                                                            title="Add Note">
                                                        <i class="fas fa-sticky-note"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Bulk Actions & Pagination -->
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <select class="form-select form-select-sm w-auto">
                                    <option>Bulk Actions</option>
                                    <option>Mark as Resolved</option>
                                    <option>Change Priority</option>
                                    <option>Archive Tickets</option>
                                </select>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-play me-1"></i>Apply
                                </button>
                            </div>
                            @if($tickets->hasPages())
                                <div>
                                    {{ $tickets->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="card-body">
                        <div class="empty-state text-center py-5">
                            <div class="empty-state-icon mb-4">
                                <i class="fas fa-ticket-alt fa-4x text-muted opacity-25"></i>
                            </div>
                            <h4 class="fw-semibold text-muted mb-3">No Support Tickets Yet</h4>
                            <p class="text-muted mb-4">You haven't submitted any support tickets. Get help with your software issues.</p>
                            <a href="{{ route('customer.support.create') }}" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-plus-circle me-2"></i>Create Your First Ticket
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-history text-secondary me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @for($i = 1; $i <= 3; $i++)
                            <div class="timeline-item d-flex mb-4">
                                <div class="timeline-marker">
                                    <div class="rounded-circle bg-soft-info p-2">
                                        <i class="fas fa-reply text-info"></i>
                                    </div>
                                </div>
                                <div class="timeline-content ms-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <h6 class="fw-semibold mb-0">Support Reply Received</h6>
                                        <small class="text-muted">2 hours ago</small>
                                    </div>
                                    <p class="text-muted small mb-2">Our support team has responded to your ticket #TKT-{{ 1000 + $i }}</p>
                                    <a href="#" class="btn btn-sm btn-outline-primary">View Response</a>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Help & Resources -->
        <div class="col-lg-4">
            <!-- Quick Help Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-life-ring text-primary me-2"></i>Quick Help
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('customer.support.create') }}" 
                           class="btn btn-light d-flex align-items-center p-3 text-start border rounded-3">
                            <div class="btn-icon-wrapper bg-primary bg-opacity-10 rounded-2 p-2 me-3">
                                <i class="fas fa-plus-circle text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Submit New Ticket</h6>
                                <p class="text-muted small mb-0">Get personalized help from our experts</p>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </a>

                        <a href="{{ route('customer.support.faq') }}" 
                           class="btn btn-light d-flex align-items-center p-3 text-start border rounded-3">
                            <div class="btn-icon-wrapper bg-success bg-opacity-10 rounded-2 p-2 me-3">
                                <i class="fas fa-question-circle text-success"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Browse FAQ</h6>
                                <p class="text-muted small mb-0">Find instant answers to common questions</p>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </a>

                        <a href="{{ route('customer.invoices.index') }}" 
                           class="btn btn-light d-flex align-items-center p-3 text-start border rounded-3">
                            <div class="btn-icon-wrapper bg-info bg-opacity-10 rounded-2 p-2 me-3">
                                <i class="fas fa-file-invoice-dollar text-info"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Billing Support</h6>
                                <p class="text-muted small mb-0">Manage invoices and payment issues</p>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </a>

                        <a href="{{ route('customer.products.index') }}" 
                           class="btn btn-light d-flex align-items-center p-3 text-start border rounded-3">
                            <div class="btn-icon-wrapper bg-warning bg-opacity-10 rounded-2 p-2 me-3">
                                <i class="fas fa-box-open text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Product Issues</h6>
                                <p class="text-muted small mb-0">Software installation and licensing help</p>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Support Resources -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-book-open text-success me-2"></i>Support Resources
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $resources = [
                                ['icon' => 'fa-file-pdf', 'color' => 'danger', 'title' => 'User Manuals', 'desc' => 'PDF Guides'],
                                ['icon' => 'fa-video', 'color' => 'purple', 'title' => 'Video Tutorials', 'desc' => 'Step-by-step'],
                                ['icon' => 'fa-code', 'color' => 'info', 'title' => 'API Docs', 'desc' => 'Developer Guide'],
                                ['icon' => 'fa-download', 'color' => 'primary', 'title' => 'Downloads', 'desc' => 'Latest Versions'],
                                ['icon' => 'fa-users', 'color' => 'success', 'title' => 'Community', 'desc' => 'User Forum'],
                                ['icon' => 'fa-blog', 'color' => 'warning', 'title' => 'Blog', 'desc' => 'Tips & Tricks'],
                            ];
                        @endphp
                        
                        @foreach($resources as $resource)
                            <div class="col-6">
                                <a href="#" class="resource-card d-block p-3 text-center border rounded-3 h-100">
                                    <div class="resource-icon mb-2">
                                        <div class="rounded-circle bg-{{ $resource['color'] }} bg-opacity-10 p-3 mx-auto">
                                            <i class="fas {{ $resource['icon'] }} fa-2x text-{{ $resource['color'] }}"></i>
                                        </div>
                                    </div>
                                    <h6 class="fw-semibold mb-1">{{ $resource['title'] }}</h6>
                                    <p class="text-muted small mb-0">{{ $resource['desc'] }}</p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            
        </div>
    </div>
</div>

<style>
    .support-page {
        animation: fadeIn 0.6s ease-out;
    }

    .header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .text-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stats-card {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stats-card:nth-child(1) { border-left-color: #3A7BD5; }
    .stats-card:nth-child(2) { border-left-color: #34C759; }
    .stats-card:nth-child(3) { border-left-color: #FF9500; }
    .stats-card:nth-child(4) { border-left-color: #5AC8FA; }

    .stats-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }

    .stats-card .icon-wrapper {
        transition: transform 0.3s ease;
    }

    .stats-card:hover .icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    .bg-white-20 {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        background-color: #f8fafc;
    }

    .ticket-row {
        border-left: 3px solid transparent;
    }

    .ticket-row[data-priority="urgent"] {
        border-left-color: #FF3B30;
        background-color: rgba(255, 59, 48, 0.02);
    }

    .ticket-row[data-priority="high"] {
        border-left-color: #FF9500;
        background-color: rgba(255, 149, 0, 0.02);
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .bg-opacity-10 { background-color: rgba(var(--bs-primary-rgb), 0.1); }
    .bg-opacity-15 { background-color: rgba(var(--bs-primary-rgb), 0.15); }

    .timeline {
        position: relative;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #e5e7eb, #9ca3af, #e5e7eb);
    }

    .timeline-item {
        position: relative;
    }

    .timeline-marker {
        position: relative;
        z-index: 1;
    }

    .resource-card {
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .resource-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: #3A7BD5 !important;
    }

    .empty-state-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }

    .btn-group .btn.active {
        background: linear-gradient(135deg, #3A7BD5 0%, #00D2FF 100%);
        color: white;
        border: none;
        box-shadow: 0 4px 15px rgba(58, 123, 213, 0.3);
    }

    .progress-bar {
        border-radius: 2px;
    }

    .btn-white {
        background: white;
        color: #3A7BD5;
        border: none;
        font-weight: 600;
    }

    .btn-white:hover {
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .header-gradient {
            padding: 2rem 1rem !important;
        }
        
        .btn-group {
            flex-wrap: wrap;
        }
        
        .table-responsive {
            font-size: 0.875rem;
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

        // Ticket filtering
        const filterButtons = document.querySelectorAll('[data-filter]');
        const ticketRows = document.querySelectorAll('.ticket-row');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter tickets with animation
                ticketRows.forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-status') === filter) {
                        row.style.display = 'table-row';
                        setTimeout(() => {
                            row.style.opacity = '1';
                            row.style.transform = 'translateX(0)';
                        }, 10);
                    } else {
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            row.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });

        // Bulk select all
        const checkAll = document.getElementById('checkAll');
        const ticketCheckboxes = document.querySelectorAll('.ticket-checkbox');
        
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                ticketCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        // Add hover effects
        const cards = document.querySelectorAll('.stats-card, .resource-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Animate progress bars on scroll
        const animateProgressBars = () => {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.transition = 'width 1.5s ease-in-out';
                    bar.style.width = width;
                }, 300);
            });
        };

        // Trigger animation when cards come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateProgressBars();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.stats-card').forEach(card => {
            observer.observe(card);
        });
    });
</script>
@endsection