<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nanosoft - Internet Billing Solution</title>
    <link rel="icon" href="{{ asset('assets/nanosoft-billing.jpeg') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
   
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="fas fa-wifi me-2"></i>Nanosoft Billing
        </a>

        <!-- Hamburger toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarContent" aria-controls="navbarContent"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#products">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
            </ul>

            <!-- Right-side user menu -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link btn btn-primary text-white" href="#" id="userMenu"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @auth
                            {{ Auth::user()->name }}
                        @else
                            Login
                        @endauth
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <!-- Guest links -->
                        @guest
                            <li><a class="dropdown-item" href="{{ route('customer.login') }}">Customer Login</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.login') }}">Admin Login</a></li>
                        @endguest

                        <!-- Authenticated links (hidden by default) -->
                        @auth
                            <li>
                                <hr class="dropdown-divider">
                                @if(Auth::user()->role === 'customer')
                                    <a class="dropdown-item" href="{{ route('customer.dashboard') }}">Dashboard</a>
                                @elseif(Auth::user()->role === 'admin')
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a>
                                @endif
                                <form method="POST" action="{{ Auth::user()->role === 'customer' ? route('customer.logout') : route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        @endauth
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="false">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="{{ asset('assets/netbill04.jpg') }}" class="d-block w-100" alt="NetBill BD" style="object-fit: cover; height: 400px; width: 100%;">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="display-4 fw-bold mb-4">Smart Internet Billing Solution</h1>
                        <p class="lead mb-4">Automated monthly billing for internet service providers in Bangladesh</p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="row text-start">
                                    <div class="col-md-4 mb-3"><i class="fas fa-bolt"></i> Easy product Management</div>
                                    <div class="col-md-4 mb-3"><i class="fas fa-file-invoice"></i> Automated Monthly Billing</div>
                                    <div class="col-md-4 mb-3"><i class="fas fa-mobile-alt"></i> Customer Self-Service Portal</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('assets/netbill05.png') }}" class="d-block w-100" alt="NetBill BD" style="object-fit: cover; height: 400px; width: 100%;">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="display-4 fw-bold mb-4">Streamlined Customer Management</h1>
                        <p class="lead mb-4">Efficiently manage customer accounts, subscriptions, and billing</p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="row text-start">
                                    <div class="col-md-4 mb-3"><i class="fas fa-users"></i> Customer Database</div>
                                    <div class="col-md-4 mb-3"><i class="fas fa-sync-alt"></i> Subscription Renewals</div>
                                    <div class="col-md-4 mb-3"><i class="fas fa-chart-bar"></i> Usage Analytics</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('assets/netbill06.jpg') }}" class="d-block w-100" alt="NetBill BD" style="object-fit: cover; height: 400px; width: 100%;">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="display-4 fw-bold mb-4">Comprehensive Reporting</h1>
                        <p class="lead mb-4">Detailed financial reports and revenue tracking</p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="row text-start">
                                    <div class="col-md-4 mb-3"><i class="fas fa-file-invoice-dollar"></i> Monthly Invoices</div>
                                    <div class="col-md-4 mb-3"><i class="fas fa-money-bill-wave"></i> Payment Tracking</div>
                                    <div class="col-md-4 mb-3"><i class="fas fa-chart-line"></i> Revenue Analytics</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <!-- CTA Button below carousel -->
        <div class="text-center mt-4">
            <a href="#products" class="btn btn-outline-primary btn-lg px-4">View Products</a>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="products" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Internet products</h2>
            <p class="text-muted">Affordable products for every need</p>
        </div>

        <div class="row">
            <!-- Regular products -->
            <div class="col-md-6 mb-4">
                <h4 class="text-center mb-4">Regular products</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card product-card h-100">
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="card-title text-primary">Basic Speed</h5>
                                <h3 class="text-success">৳500<small>/month</small></h3>
                                <p class="text-muted flex-grow-1">Perfect for everyday browsing and emails</p>
                                <ul class="list-unstyled mt-auto">
                                    <li><i class="fas fa-check text-success"></i> Standard Speed</li>
                                    <li><i class="fas fa-check text-success"></i> Unlimited Data</li>
                                    <li><i class="fas fa-check text-success"></i> 1 Device</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card product-card h-100">
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="card-title text-primary">Fast Speed</h5>
                                <h3 class="text-success">৳800<small>/month</small></h3>
                                <p class="text-muted flex-grow-1">Great for streaming and downloads</p>
                                <ul class="list-unstyled mt-auto">
                                    <li><i class="fas fa-check text-success"></i> High Speed</li>
                                    <li><i class="fas fa-check text-success"></i> Unlimited Data</li>
                                    <li><i class="fas fa-check text-success"></i> Up to 3 Devices</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add-on products -->
            <div class="col-md-6 mb-4">
                <h4 class="text-center mb-4">Add-on products</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card product-card h-100">
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="card-title text-primary">Gaming Boost</h5>
                                <h3 class="text-success">৳200<small>/month</small></h3>
                                <p class="text-muted flex-grow-1">Low latency for gaming</p>
                                <ul class="list-unstyled mt-auto">
                                    <li><i class="fas fa-check text-success"></i> Reduced Ping</li>
                                    <li><i class="fas fa-check text-success"></i> Priority Gaming</li>
                                    <li><i class="fas fa-check text-success"></i> 24/7 Support</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card product-card h-100">
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="card-title text-primary">Streaming Plus</h5>
                                <h3 class="text-success">৳150<small>/month</small></h3>
                                <p class="text-muted flex-grow-1">Optimized for HD streaming</p>
                                <ul class="list-unstyled mt-auto">
                                    <li><i class="fas fa-check text-success"></i> HD Streaming</li>
                                    <li><i class="fas fa-check text-success"></i> Buffer-Free</li>
                                    <li><i class="fas fa-check text-success"></i> Multiple Platforms</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5">
    <div class="container">
        <div class="text-center mb-5"><h2 class="fw-bold">Why Choose NetBill BD?</h2></div>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon"><i class="fas fa-rocket"></i></div>
                <h5>Automated Billing</h5>
                <p class="text-muted">Automatic monthly invoice generation and payment tracking</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon"><i class="fas fa-user-shield"></i></div>
                <h5>Customer Portal</h5>
                <p class="text-muted">Self-service portal for customers to view bills and make payments</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h5>Real-time Reports</h5>
                <p class="text-muted">Comprehensive analytics and financial reporting</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-wifi me-2"></i>Nanosoft Billing</h5>
                <p>Smart internet billing solution for Bangladesh</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p>Contact: info@nanosoftbilling.com <br> 
                Phone: +880 123-456-7890</p>
                <p>© {{ date('Y') }} Nanosoft Billing. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Set the user's name in the dropdown toggle when authenticated
    const userMenuToggle = document.getElementById('userMenu');
    
    // Check if user is authenticated and update the display
    @auth
        userMenuToggle.innerHTML = '{{ Auth::user()->name }} <b class="caret"></b>';
    @endauth
</script>
</body>
</html>