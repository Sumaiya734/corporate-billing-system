@extends('layouts.admin')

@section('title', 'Support Tickets')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">
            <i class="fas fa-ticket-alt text-primary me-2"></i>Support Tickets
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted small mb-1">Total Tickets</h6>
                    <h3 class="mb-0 text-primary">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted small mb-1">Open</h6>
                    <h3 class="mb-0 text-warning">{{ $stats['open'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted small mb-1">In Progress</h6>
                    <h3 class="mb-0 text-info">{{ $stats['in_progress'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted small mb-1">Resolved</h6>
                    <h3 class="mb-0 text-success">{{ $stats['resolved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted small mb-1">Urgent</h6>
                    <h3 class="mb-0 text-danger">{{ $stats['urgent'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.support.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search by ticket #, subject, customer..." value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($tickets->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="15%">Ticket #</th>
                            <th width="20%">Customer</th>
                            <th width="25%">Subject</th>
                            <th width="10%">Category</th>
                            <th width="10%">Priority</th>
                            <th width="10%">Status</th>
                            <th width="10%">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('admin.support.show', $ticket->id) }}" class="text-decoration-none fw-medium">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td>
                                @if($ticket->customer)
                                    {{ $ticket->customer->name }}
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($ticket->subject, 40) }}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    {{ ucfirst($ticket->category) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $ticket->priority == 'urgent' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : ($ticket->priority == 'medium' ? 'info' : 'secondary')) }}-subtle text-{{ $ticket->priority == 'urgent' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : ($ticket->priority == 'medium' ? 'info' : 'secondary')) }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $ticket->status == 'open' ? 'warning' : ($ticket->status == 'in_progress' ? 'info' : ($ticket->status == 'resolved' ? 'success' : 'secondary')) }}-subtle text-{{ $ticket->status == 'open' ? 'warning' : ($ticket->status == 'in_progress' ? 'info' : ($ticket->status == 'resolved' ? 'success' : 'secondary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($tickets->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} results
                    </div>
                    <div>
                        {{ $tickets->links() }}
                    </div>
                </div>
            </div>
            @endif
            @else
            <div class="text-center py-5">
                <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No support tickets found</h5>
                <p class="text-muted">Try adjusting your filters or search criteria</p>
            </div>
            @endif
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