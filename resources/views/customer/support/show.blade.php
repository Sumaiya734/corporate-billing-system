@extends('layouts.customer')

@section('title', 'Support Ticket #' . $ticket->ticket_number . ' - Nanosoft')

@section('content')
<div class="ticket-detail-page">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-2">
                    <i class="fas fa-ticket-alt me-2 text-primary"></i>Ticket #{{ $ticket->ticket_number }}
                </h1>
                <p class="text-muted mb-0">{{ $ticket->subject }}</p>
            </div>
            <div>
                <a href="{{ route('customer.support.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Tickets
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ticket Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Ticket Details
                        </h5>
                        <div class="ticket-status">
                            @if($ticket->status == 'open')
                                <span class="badge bg-warning rounded-pill px-3 py-2">
                                    <i class="fas fa-clock me-1"></i>Open
                                </span>
                            @elseif($ticket->status == 'in_progress')
                                <span class="badge bg-info rounded-pill px-3 py-2">
                                    <i class="fas fa-cog fa-spin me-1"></i>In Progress
                                </span>
                            @else
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i>Resolved
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Ticket Meta Information -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="meta-item">
                                <label class="text-muted small mb-1">Category</label>
                                <div class="fw-bold">
                                    @php
                                        $categoryLabels = [
                                            'billing' => '💳 Billing & Payments',
                                            'license' => '🔑 License Management',
                                            'product' => '📦 Product Support',
                                            'technical' => '⚙️ Technical Issues',
                                            'account' => '👤 Account Settings',
                                            'integration' => '🔗 API Integration',
                                            'other' => '❓ Other'
                                        ];
                                    @endphp
                                    {{ $categoryLabels[$ticket->category] ?? ucfirst($ticket->category) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="meta-item">
                                <label class="text-muted small mb-1">Priority</label>
                                <div class="fw-bold">
                                    @if($ticket->priority == 'urgent')
                                        <span class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Urgent
                                        </span>
                                    @elseif($ticket->priority == 'high')
                                        <span class="text-warning">
                                            <i class="fas fa-arrow-up me-1"></i>High
                                        </span>
                                    @elseif($ticket->priority == 'normal')
                                        <span class="text-info">
                                            <i class="fas fa-minus me-1"></i>Normal
                                        </span>
                                    @else
                                        <span class="text-success">
                                            <i class="fas fa-arrow-down me-1"></i>Low
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="meta-item">
                                <label class="text-muted small mb-1">Created</label>
                                <div class="fw-bold">{{ $ticket->created_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="meta-item">
                                <label class="text-muted small mb-1">Last Updated</label>
                                <div class="fw-bold">{{ $ticket->updated_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Description -->
                    <div class="ticket-description">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-comment me-2 text-secondary"></i>Description
                        </h6>
                        <div class="description-content p-3 bg-light rounded">
                            <p class="mb-0">{{ nl2br(e($ticket->description)) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversation/Replies -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2 text-primary"></i>Conversation
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($replies->count() > 0)
                        <div class="conversation-list">
                            @foreach($replies as $reply)
                                <div class="reply-item {{ $reply->is_from_customer ? 'customer-reply' : 'support-reply' }}">
                                    <div class="reply-header d-flex justify-content-between align-items-center mb-2">
                                        <div class="reply-author">
                                            <strong>{{ $reply->user->name }}</strong>
                                            <small class="text-muted ms-2">
                                                {{ $reply->is_from_customer ? 'You' : 'Support Team' }}
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            {{ $reply->created_at->format('M d, Y g:i A') }}
                                        </small>
                                    </div>
                                    <div class="reply-content">
                                        <p class="mb-0">{{ nl2br(e($reply->message)) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="empty-conversation-icon mb-3">
                                <i class="fas fa-comments fa-3x text-muted opacity-50"></i>
                            </div>
                            <h6 class="text-muted">No replies yet</h6>
                            <p class="text-muted small">Our support team will respond to your ticket soon.</p>
                        </div>
                    @endif

                    <!-- Reply Form (only if ticket is not resolved) -->
                    @if($ticket->status != 'resolved')
                        <div class="reply-form-container border-top p-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-reply me-2 text-primary"></i>Add Reply
                            </h6>
                            <form action="#" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control"
                                              name="message"
                                              rows="4"
                                              placeholder="Type your reply here..."
                                              required></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i>Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('customer.support.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i>New Ticket
                        </a>
                        <a href="{{ route('customer.support.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-1"></i>All Tickets
                        </a>
                        <a href="{{ route('customer.support.faq') }}" class="btn btn-outline-success">
                            <i class="fas fa-question-circle me-1"></i>Browse FAQ
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ticket Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-info"></i>Ticket Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="status-timeline">
                        <div class="status-item {{ $ticket->status == 'open' || $ticket->status == 'in_progress' || $ticket->status == 'resolved' ? 'active' : '' }}">
                            <div class="status-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="status-content">
                                <strong>Created</strong>
                                <br>
                                <small class="text-muted">{{ $ticket->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>

                        <div class="status-item {{ $ticket->status == 'in_progress' || $ticket->status == 'resolved' ? 'active' : '' }}">
                            <div class="status-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="status-content">
                                <strong>In Progress</strong>
                                <br>
                                <small class="text-muted">Being worked on</small>
                            </div>
                        </div>

                        <div class="status-item {{ $ticket->status == 'resolved' ? 'active' : '' }}">
                            <div class="status-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="status-content">
                                <strong>Resolved</strong>
                                <br>
                                <small class="text-muted">Issue fixed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-phone me-2 text-success"></i>Need Help?
                    </h6>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="contact-item d-flex align-items-center mb-3">
                            <div class="contact-icon bg-soft-primary rounded-2 p-2 me-3">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email Support</small>
                                <strong>support@nanosoft.com</strong>
                            </div>
                        </div>

                        <div class="contact-item d-flex align-items-center mb-3">
                            <div class="contact-icon bg-soft-success rounded-2 p-2 me-3">
                                <i class="fas fa-phone text-success"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Phone Support</small>
                                <strong>+880 XXXX-XXXXXX</strong>
                            </div>
                        </div>

                        <div class="contact-item d-flex align-items-center">
                            <div class="contact-icon bg-soft-warning rounded-2 p-2 me-3">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Response Time</small>
                                <strong>4-8 Hours</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ticket-detail-page {
        animation: fadeIn 0.6s ease-out;
    }

    .meta-item {
        padding: 1rem;
        background-color: #f8fafc;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
    }

    .ticket-description .description-content {
        border-left: 4px solid #3A7BD5;
        background-color: #f8fafc !important;
    }

    .conversation-list {
        max-height: 600px;
        overflow-y: auto;
    }

    .reply-item {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .reply-item.customer-reply {
        background-color: #f0f9ff;
        border-left: 4px solid #3A7BD5;
    }

    .reply-item.support-reply {
        background-color: #f8fafc;
        border-left: 4px solid #10b981;
    }

    .reply-item:last-child {
        border-bottom: none;
    }

    .reply-author strong {
        color: #1e293b;
    }

    .status-timeline {
        position: relative;
    }

    .status-timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e2e8f0;
    }

    .status-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .status-item.active .status-icon {
        background-color: #3A7BD5;
        color: white;
    }

    .status-item.active::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 40px;
        bottom: -1.5rem;
        width: 2px;
        background-color: #3A7BD5;
    }

    .status-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        position: relative;
        z-index: 1;
    }

    .status-content strong {
        color: #1e293b;
    }

    .bg-soft-primary { background-color: rgba(58, 123, 213, 0.1); }
    .bg-soft-success { background-color: rgba(34, 197, 94, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }

    .contact-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-conversation-icon {
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
        .conversation-list {
            max-height: 400px;
        }

        .status-item {
            margin-bottom: 1rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-resize textarea in reply form
        const textarea = document.querySelector('textarea[name="message"]');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // Smooth scroll to reply form if there's a hash
        if (window.location.hash === '#reply') {
            const replyForm = document.querySelector('.reply-form-container');
            if (replyForm) {
                replyForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const textarea = replyForm.querySelector('textarea');
                if (textarea) {
                    textarea.focus();
                }
            }
        }
    });
</script>
@endsection
