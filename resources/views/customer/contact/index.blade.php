@extends('layouts.customer')

@section('title', 'Contact Us - Nanosoft')

@section('content')
<div class="contact-page">
    <!-- Compact Hero Section -->
    <div class="hero-section bg-soft-primary rounded-3 mb-4 overflow-hidden position-relative border border-soft">
        <div class="container position-relative z-2">
            <div class="row align-items-center py-4">
                <div class="col-lg-7">
                    <div class="hero-content">
                        <div class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 mb-3 d-inline-flex align-items-center">
                            <i class="fas fa-headset me-2 fs-6"></i>24/7 Support
                        </div>
                        <h1 class="h2 fw-bold mb-2 text-dark">Get In Touch</h1>
                        <p class="text-muted mb-4">We're here to help you succeed. Reach out for support, sales inquiries, or partnership opportunities.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="#contact-form" class="btn btn-primary btn-md px-4">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </a>
                            <a href="tel:+880XXXXXXXXXX" class="btn btn-outline-primary btn-md px-4">
                                <i class="fas fa-phone me-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="text-center">
                        <div class="contact-icon-wrapper bg-primary-soft rounded-3 p-3 d-inline-block">
                            <i class="fas fa-comments fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compact Contact Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card contact-stat border border-soft h-100">
                <div class="card-body text-center p-3">
                    <div class="stat-icon rounded-circle bg-blue-soft p-2 mb-2 mx-auto" style="width: 50px; height: 50px;">
                        <i class="fas fa-phone-volume fa-lg text-blue"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1 fs-6">+880 XXXXXX</h5>
                    <p class="text-muted small mb-1">Support Hotline</p>
                    <small class="text-success d-flex align-items-center justify-content-center">
                        <span class="dot bg-success me-1"></span>Available Now
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card contact-stat border border-soft h-100">
                <div class="card-body text-center p-3">
                    <div class="stat-icon rounded-circle bg-green-soft p-2 mb-2 mx-auto" style="width: 50px; height: 50px;">
                        <i class="fas fa-envelope fa-lg text-green"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1 fs-6">4 Hours</h5>
                    <p class="text-muted small mb-1">Avg. Response</p>
                    <small class="text-muted">Within 24h guaranteed</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card contact-stat border border-soft h-100">
                <div class="card-body text-center p-3">
                    <div class="stat-icon rounded-circle bg-orange-soft p-2 mb-2 mx-auto" style="width: 50px; height: 50px;">
                        <i class="fas fa-headset fa-lg text-orange"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1 fs-6">98.7%</h5>
                    <p class="text-muted small mb-1">Satisfaction</p>
                    <small class="text-muted">5,000+ reviews</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card contact-stat border border-soft h-100">
                <div class="card-body text-center p-3">
                    <div class="stat-icon rounded-circle bg-purple-soft p-2 mb-2 mx-auto" style="width: 50px; height: 50px;">
                        <i class="fas fa-globe fa-lg text-purple"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1 fs-6">24/7</h5>
                    <p class="text-muted small mb-1">Global Support</p>
                    <small class="text-muted">Always here</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Contact Form -->
        <div class="col-lg-8">
            <div class="card border border-soft mb-4">
                <div class="card-header bg-white border-bottom border-soft py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-primary-soft rounded-2 p-2 me-3">
                            <i class="fas fa-paper-plane text-primary"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold mb-0 text-dark" id="contact-form">Send Message</h2>
                            <p class="text-muted small mb-0">We'll respond as soon as possible</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form id="contactForm" method="POST" action="{{ route('customer.contact.submit') }}">
                        @csrf
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label small fw-semibold text-muted mb-1">
                                        <i class="fas fa-user me-1"></i>Full Name
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name"
                                           value="{{ old('name', auth()->user()->name) }}"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label small fw-semibold text-muted mb-1">
                                        <i class="fas fa-envelope me-1"></i>Email Address
                                    </label>
                                    <input type="email" 
                                           class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email"
                                           value="{{ old('email', auth()->user()->email) }}"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="subject" class="form-label small fw-semibold text-muted mb-1">
                                        <i class="fas fa-tag me-1"></i>Subject
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-sm @error('subject') is-invalid @enderror" 
                                           id="subject" 
                                           name="subject"
                                           value="{{ old('subject') }}"
                                           required>
                                    @error('subject')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="department" class="form-label small fw-semibold text-muted mb-1">
                                        <i class="fas fa-building me-1"></i>Department
                                    </label>
                                    <select class="form-select form-select-sm @error('department') is-invalid @enderror" 
                                            id="department" 
                                            name="department"
                                            required>
                                        <option value="" selected disabled>Select...</option>
                                        <option value="technical" {{ old('department') == 'technical' ? 'selected' : '' }}>
                                            Technical Support
                                        </option>
                                        <option value="sales" {{ old('department') == 'sales' ? 'selected' : '' }}>
                                            Sales
                                        </option>
                                        <option value="billing" {{ old('department') == 'billing' ? 'selected' : '' }}>
                                            Billing
                                        </option>
                                        <option value="other" {{ old('department') == 'other' ? 'selected' : '' }}>
                                            Other
                                        </option>
                                    </select>
                                    @error('department')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-group">
                                <label for="message" class="form-label small fw-semibold text-muted mb-1">
                                    <i class="fas fa-comment-dots me-1"></i>Message
                                </label>
                                <textarea class="form-control form-control-sm @error('message') is-invalid @enderror" 
                                          id="message" 
                                          name="message"
                                          rows="4"
                                          required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Please provide detailed information to help us serve you better.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3 align-items-center mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="priority" name="priority">
                                    <label class="form-check-label small" for="priority">
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                        Mark as Urgent
                                    </label>
                                </div>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" checked>
                                    <label class="form-check-label small" for="newsletter">
                                        Subscribe to updates
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="card border border-soft">
                <div class="card-header bg-white border-bottom border-soft py-3">
                    <h3 class="h6 fw-bold mb-0 text-dark">
                        <i class="fas fa-question-circle text-primary me-2"></i>Frequently Asked Questions
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="faqAccordion">
                        @foreach($faqs as $index => $faq)
                            <div class="accordion-item border-soft">
                                <h2 class="accordion-header">
                                    <button class="accordion-button py-3 collapsed small" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#faq{{ $index }}">
                                        <i class="fas fa-question-circle text-primary me-3"></i>
                                        {{ $faq['question'] }}
                                    </button>
                                </h2>
                                <div id="faq{{ $index }}" 
                                     class="accordion-collapse collapse" 
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body py-3 small">
                                        {{ $faq['answer'] }}
                                        @if(isset($faq['link']))
                                            <a href="{{ $faq['link'] }}" class="btn btn-sm btn-outline-primary mt-2">
                                                Learn More
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Contact Info -->
        <div class="col-lg-4">
            <!-- Contact Details Card -->
            <div class="card border border-soft mb-4">
                <div class="card-header bg-white border-bottom border-soft py-3">
                    <h3 class="h6 fw-bold mb-0 text-dark">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>Contact Information
                    </h3>
                </div>
                <div class="card-body p-0">
                    <!-- Map Preview -->
                    <div class="map-preview position-relative" style="height: 160px; background: linear-gradient(135deg, #a8c6ff 0%, #c2b0ff 100%);">
                        <div class="map-overlay d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <i class="fas fa-map-marked-alt fa-2x text-white mb-2"></i>
                                <p class="text-white small mb-0">Dhaka, Bangladesh</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Details -->
                    <div class="p-3">
                        <div class="contact-item d-flex align-items-start mb-3 pb-2 border-bottom border-soft">
                            <div class="contact-icon bg-blue-soft rounded-2 p-2 me-3">
                                <i class="fas fa-map-pin fa-sm text-blue"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1 small">Address</h6>
                                <p class="text-muted small mb-0">
                                    Nano I nformation Technology<br>
                                    Level 5, Dhaka 1212
                                </p>
                            </div>
                        </div>
                        
                        <div class="contact-item d-flex align-items-start mb-3 pb-2 border-bottom border-soft">
                            <div class="contact-icon bg-green-soft rounded-2 p-2 me-3">
                                <i class="fas fa-clock fa-sm text-green"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1 small">Business Hours</h6>
                                <p class="text-muted small mb-0">
                                    Sun-Thu: 9AM-6PM<br>
                                    Fri-Sat: 10AM-4PM
                                </p>
                            </div>
                        </div>
                        
                        <div class="contact-item d-flex align-items-start mb-3">
                            <div class="contact-icon bg-purple-soft rounded-2 p-2 me-3">
                                <i class="fas fa-door-open fa-sm text-purple"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1 small">Visit Us</h6>
                                <p class="text-muted small mb-2">Schedule an appointment</p>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-calendar-check me-1"></i>Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departments Card -->
            <div class="card border border-soft mb-4">
                <div class="card-header bg-white border-bottom border-soft py-3">
                    <h3 class="h6 fw-bold mb-0 text-dark">
                        <i class="fas fa-sitemap text-primary me-2"></i>Contact Departments
                    </h3>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        @foreach($departments as $dept)
                            <div class="col-12 mb-2">
                                <a href="mailto:{{ $dept['email'] }}" 
                                   class="department-link d-flex align-items-center p-2 border border-soft rounded-2 text-decoration-none hover-lift">
                                    <div class="dept-icon me-3">
                                        <div class="rounded-circle {{ $dept['bgClass'] }} p-2">
                                            <i class="fas {{ $dept['icon'] }} fa-sm {{ $dept['textClass'] }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-semibold mb-0 small text-dark">{{ $dept['name'] }}</h6>
                                        <small class="text-muted">{{ $dept['desc'] }}</small>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted ms-2"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Help Card -->
            <div class="card border border-soft bg-light">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start">
                        <div class="icon-wrapper bg-warning-soft rounded-2 p-2 me-3">
                            <i class="fas fa-bolt text-warning"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Need Immediate Help?</h6>
                            <p class="text-muted small mb-2">Contact emergency support</p>
                            <a href="tel:+880XXXXXXXXXX" class="btn btn-sm btn-warning w-100">
                                <i class="fas fa-phone-alt me-2"></i>Emergency: +880 XXXXXX
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Chat Button -->
    <div class="live-chat-widget position-fixed bottom-3 end-3">
        <button class="btn btn-primary btn-sm rounded-pill shadow-sm px-3 py-2 d-flex align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#chatModal">
            <i class="fas fa-comment-dots me-2"></i>
            <span class="small fw-semibold">Live Chat</span>
            <span class="live-dot ms-2"></span>
        </button>
    </div>

    <!-- Chat Modal -->
    <div class="modal fade" id="chatModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border border-soft">
                <div class="modal-header bg-white border-bottom border-soft py-3">
                    <h5 class="modal-title h6 fw-bold text-dark">
                        <i class="fas fa-headset text-primary me-2"></i>Live Chat
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="chat-window">
                        <div class="chat-header p-3 border-bottom border-soft">
                            <div class="d-flex align-items-center">
                                <div class="chat-avatar rounded-circle bg-success d-flex align-items-center justify-content-center me-2" 
                                     style="width: 32px; height: 32px;">
                                    <i class="fas fa-user text-white fa-xs"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 small fw-semibold text-dark">Support Agent</h6>
                                    <small class="text-success">
                                        <span class="dot bg-success me-1"></span>Online
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="chat-body p-3" style="height: 250px; overflow-y: auto;">
                            <div class="chat-message bot mb-3">
                                <div class="message-bubble bg-light rounded-3 p-2">
                                    <p class="mb-1 small">Hello! How can I help you today? 😊</p>
                                    <small class="text-muted">Just now</small>
                                </div>
                            </div>
                        </div>
                        <div class="chat-footer p-3 border-top border-soft">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" placeholder="Type your message...">
                                <button class="btn btn-primary btn-sm" type="button">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .contact-page {
        animation: fadeIn 0.6s ease-out;
    }

    /* Soft Color Palette */
    .bg-soft-primary {
        background-color: #f0f4ff !important;
    }

    .bg-primary-soft {
        background-color: rgba(102, 126, 234, 0.1) !important;
    }

    .bg-blue-soft {
        background-color: rgba(58, 123, 213, 0.1) !important;
    }

    .bg-green-soft {
        background-color: rgba(52, 199, 89, 0.1) !important;
    }

    .bg-orange-soft {
        background-color: rgba(255, 149, 0, 0.1) !important;
    }

    .bg-purple-soft {
        background-color: rgba(118, 75, 162, 0.1) !important;
    }

    .bg-warning-soft {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .text-blue {
        color: #3A7BD5 !important;
    }

    .text-green {
        color: #34C759 !important;
    }

    .text-orange {
        color: #FF9500 !important;
    }

    .text-purple {
        color: #764ba2 !important;
    }

    .border-soft {
        border-color: #e9ecef !important;
    }

    /* Card Styles */
    .card {
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .contact-stat {
        transition: transform 0.3s ease;
    }

    .contact-stat:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        transition: transform 0.3s ease;
    }

    .contact-stat:hover .stat-icon {
        transform: scale(1.1);
    }

    /* Form Styles */
    .form-control, .form-select {
        border-color: #dee2e6;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #3A7BD5;
        box-shadow: 0 0 0 0.2rem rgba(58, 123, 213, 0.1);
    }

    /* Department Links */
    .department-link {
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .department-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        border-color: #3A7BD5 !important;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
    }

    /* Chat Styles */
    .live-chat-widget {
        z-index: 1000;
    }

    .live-dot {
        width: 6px;
        height: 6px;
        background-color: #34C759;
        border-radius: 50%;
        display: inline-block;
        animation: pulse 2s infinite;
    }

    .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .chat-avatar {
        width: 32px;
        height: 32px;
    }

    .message-bubble {
        max-width: 85%;
    }

    /* Map Preview */
    .map-preview {
        cursor: pointer;
        transition: opacity 0.3s ease;
    }

    .map-preview:hover {
        opacity: 0.9;
    }

    /* Button Styles */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }

    /* Accordion */
    .accordion-button {
        background-color: #f8fafc;
        color: #495057;
        font-size: 0.875rem;
    }

    .accordion-button:not(.collapsed) {
        background-color: rgba(102, 126, 234, 0.08);
        color: #667eea;
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.1);
    }

    /* Animations */
    @keyframes float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(52, 199, 89, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(52, 199, 89, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(52, 199, 89, 0);
        }
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

    /* Responsive */
    @media (max-width: 768px) {
        .hero-section {
            padding: 1.5rem 1rem !important;
        }
        
        .hero-section h1 {
            font-size: 1.5rem !important;
        }
        
        .live-chat-widget {
            bottom: 1rem;
            right: 1rem;
        }
        
        .card-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 576px) {
        .contact-stat {
            margin-bottom: 0.5rem;
        }
        
        .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form submission
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                submitBtn.disabled = true;
                
                // Simulate delay
                setTimeout(() => {
                    submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Message Sent!';
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-success');
                    
                    // Reset after 2 seconds
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('btn-success');
                        submitBtn.classList.add('btn-primary');
                        contactForm.reset();
                    }, 2000);
                }, 1000);
                
                e.preventDefault();
            });
        }

        // Live chat functionality
        const chatInput = document.querySelector('#chatModal input[type="text"]');
        const chatBody = document.querySelector('.chat-body');
        const sendBtn = document.querySelector('#chatModal .btn-primary');

        function addMessage(text, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${isUser ? 'user' : 'bot'} mb-3`;
            
            const bubbleClass = isUser ? 'bg-primary text-white' : 'bg-light';
            const alignClass = isUser ? 'ms-auto' : '';
            
            messageDiv.innerHTML = `
                <div class="message-bubble ${bubbleClass} rounded-3 p-2 ${alignClass}" style="max-width: 85%">
                    <p class="mb-1 small">${text}</p>
                    <small class="${isUser ? 'text-white-70' : 'text-muted'}">Just now</small>
                </div>
            `;
            
            chatBody.appendChild(messageDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        if (sendBtn && chatInput) {
            sendBtn.addEventListener('click', function() {
                const message = chatInput.value.trim();
                if (message) {
                    addMessage(message, true);
                    chatInput.value = '';
                    
                    // Simulate bot response
                    setTimeout(() => {
                        const responses = [
                            "Thanks for your message! How can I assist you?",
                            "I understand. Let me check that for you.",
                            "Our team will respond shortly.",
                            "Is there anything specific you need help with?"
                        ];
                        addMessage(responses[Math.floor(Math.random() * responses.length)]);
                    }, 800);
                }
            });

            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendBtn.click();
                }
            });
        }

        // Animate elements on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.contact-stat, .card').forEach(el => {
            observer.observe(el);
        });

        // Map click
        const mapPreview = document.querySelector('.map-preview');
        if (mapPreview) {
            mapPreview.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('locationModal'));
                modal.show();
            });
        }
    });
</script>

<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border border-soft">
            <div class="modal-header bg-white border-bottom border-soft py-3">
                <h5 class="modal-title h6 fw-bold text-dark">
                    <i class="fas fa-map-marked-alt text-primary me-2"></i>Our Location
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="text-center p-4" style="background: linear-gradient(135deg, #f0f4ff 0%, #e8ecff 100%);">
                    <i class="fas fa-map fa-3x text-primary mb-3"></i>
                    <h6 class="fw-bold mb-2">Nano Information Technology</h6>
                    <p class="text-muted small mb-3">Level 5, Dhaka 1212, Bangladesh</p>
                    <a href="#" class="btn btn-sm btn-primary">
                        <i class="fas fa-directions me-2"></i>Get Directions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection