@extends('layouts.customer')

@section('title', 'FAQ - Nanosoft')

@section('content')
<div class="faq-page">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-2">
                    <i class="fas fa-question-circle me-2 text-primary"></i>Frequently Asked Questions
                </h1>
                <p class="text-muted mb-0">Find answers to common questions about our software billing system.</p>
            </div>
            <div>
                <a href="{{ route('customer.support.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Support
                </a>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0" 
                       id="faqSearch" 
                       placeholder="Search for answers...">
                <button class="btn btn-primary" type="button" id="searchButton">
                    <i class="fas fa-search me-1"></i>Search
                </button>
            </div>
        </div>
    </div>

    <!-- FAQ Categories -->
    @foreach($faqs as $category => $questions)
        <div class="card border-0 shadow-sm mb-4" id="{{ $category }}">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">
                    @if($category == 'billing')
                        <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Billing & Invoices
                    @elseif($category == 'products')
                        <i class="fas fa-box me-2 text-success"></i>Products & Licenses
                    @elseif($category == 'technical')
                        <i class="fas fa-cogs me-2 text-warning"></i>Technical Issues
                    @else
                        <i class="fas fa-user-lock me-2 text-info"></i>Account & Access
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordion{{ ucfirst($category) }}">
                    @foreach($questions as $index => $faq)
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header" id="heading{{ $category }}{{ $index }}">
                                <button class="accordion-button collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $category }}{{ $index }}" 
                                        aria-expanded="false" 
                                        aria-controls="collapse{{ $category }}{{ $index }}">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>
                                    {{ $faq['question'] }}
                                </button>
                            </h2>
                            <div id="collapse{{ $category }}{{ $index }}" 
                                 class="accordion-collapse collapse" 
                                 aria-labelledby="heading{{ $category }}{{ $index }}" 
                                 data-bs-parent="#accordion{{ ucfirst($category) }}">
                                <div class="accordion-body">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-lightbulb text-warning fa-lg"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0">{{ $faq['answer'] }}</p>
                                            @if($loop->last)
                                                <div class="mt-3">
                                                    <a href="{{ route('customer.support.create') }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-plus me-1"></i>Still have questions? Submit a ticket
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <!-- Contact Support -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="contact-icon mb-4">
                <i class="fas fa-headset fa-4x text-primary"></i>
            </div>
            <h4 class="mb-3">Still Need Help?</h4>
            <p class="text-muted mb-4">Can't find what you're looking for? Our support team is here to help.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('customer.support.create') }}" class="btn btn-primary">
                    <i class="fas fa-ticket-alt me-1"></i>Submit a Support Ticket
                </a>
                <a href="mailto:support@nanosoft.com" class="btn btn-outline-primary">
                    <i class="fas fa-envelope me-1"></i>Email Support
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .faq-page {
        animation: fadeIn 0.6s ease-out;
    }

    .accordion-button {
        background-color: #f8fafc;
        border-radius: 8px !important;
        font-weight: 500;
        padding: 15px 20px;
        transition: all 0.3s ease;
    }

    .accordion-button:not(.collapsed) {
        background-color: #e8f4ff;
        color: #0d6efd;
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(13, 110, 253, 0.25);
    }

    .accordion-body {
        background-color: #f8fafc;
        border-radius: 0 0 8px 8px;
        padding: 20px;
    }

    .contact-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .contact-icon i {
        color: white;
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
        .accordion-button {
            padding: 12px 15px;
            font-size: 0.95rem;
        }
        
        .accordion-body {
            padding: 15px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FAQ search functionality
        const searchInput = document.getElementById('faqSearch');
        const searchButton = document.getElementById('searchButton');
        
        if (searchInput && searchButton) {
            searchButton.addEventListener('click', function() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                
                if (!searchTerm) {
                    // Show all if search is empty
                    document.querySelectorAll('.accordion-item').forEach(item => {
                        item.style.display = '';
                    });
                    return;
                }
                
                // Search through questions and answers
                document.querySelectorAll('.accordion-item').forEach(item => {
                    const question = item.querySelector('.accordion-button').textContent.toLowerCase();
                    const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
                    
                    if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = '';
                        // Open the accordion if it contains the search term
                        const collapseId = item.querySelector('.accordion-collapse').id;
                        const collapseElement = new bootstrap.Collapse('#' + collapseId, {
                            toggle: false
                        });
                        collapseElement.show();
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
            
            // Also search on Enter key
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    searchButton.click();
                }
            });
        }
        
        // Auto-expand first item of each category
        document.querySelectorAll('.accordion').forEach((accordion, index) => {
            if (index === 0) {
                const firstButton = accordion.querySelector('.accordion-button');
                if (firstButton) {
                    firstButton.click();
                }
            }
        });
    });
</script>
@endsection