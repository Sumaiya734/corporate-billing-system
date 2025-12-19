@extends('layouts.admin')

@section('title', 'Support Ticket Details - ' . $ticket->ticket_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">
            <i class="fas fa-ticket-alt text-primary me-2"></i>Ticket Details: {{ $ticket->ticket_number }}
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.support.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i>Back to Tickets
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Ticket Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $ticket->subject }}</h5>
                        <div>
                            <span class="badge bg-{{ $ticket->priority == 'urgent' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : ($ticket->priority == 'medium' ? 'info' : 'secondary')) }}-subtle text-{{ $ticket->priority == 'urgent' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : ($ticket->priority == 'medium' ? 'info' : 'secondary')) }}">
                                {{ ucfirst($ticket->priority) }} Priority
                            </span>
                            <span class="badge bg-{{ $ticket->status == 'open' ? 'warning' : ($ticket->status == 'in_progress' ? 'info' : ($ticket->status == 'resolved' ? 'success' : 'secondary')) }}-subtle text-{{ $ticket->status == 'open' ? 'warning' : ($ticket->status == 'in_progress' ? 'info' : ($ticket->status == 'resolved' ? 'success' : 'secondary')) }} ms-2">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted small mb-2">Description</h6>
                        <p class="mb-0">{{ $ticket->description }}</p>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-2">Category</h6>
                            <p class="mb-0">
                                <span class="badge bg-secondary-subtle text-secondary">
                                    {{ ucfirst($ticket->category) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-2">Submitted</h6>
                            <p class="mb-0">{{ $ticket->created_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($ticket->customer)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $ticket->customer->name }}</h6>
                            <p class="text-muted mb-0 small">{{ $ticket->customer->email }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted small mb-2">Customer ID</h6>
                        <p class="mb-0">{{ $ticket->customer->customer_id }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted small mb-2">Phone</h6>
                        <p class="mb-0">{{ $ticket->customer->phone ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted small mb-2">Status</h6>
                        <p class="mb-0">
                            @if($ticket->customer->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            @endif
                        </p>
                    </div>
                    @else
                    <p class="text-muted">Customer information not available</p>
                    @endif
                </div>
            </div>

            <!-- Update Ticket -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Update Ticket
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.support.update-status', $ticket->id) }}" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small">Status</label>
                            <select name="status" class="form-select">
                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                    
                    <form method="POST" action="{{ route('admin.support.update-priority', $ticket->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $ticket->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Update Priority</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge.bg-warning-subtle {
    background-color: #fef3c7 !important;
    color: #92400e !important;
}

.badge.bg-info-subtle {
    background-color: #dbeafe !important;
    color: #1e40af !important;
}

.badge.bg-danger-subtle {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
}

.badge.bg-success-subtle {
    background-color: #d1fae5 !important;
    color: #065f46 !important;
}

.badge.bg-secondary-subtle {
    background-color: #e5e7eb !important;
    color: #4b5563 !important;
}
</style>
@endsection