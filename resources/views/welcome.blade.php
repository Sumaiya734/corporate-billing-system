<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetBill BD - Internet Billing Solution</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .package-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .package-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fas fa-wifi me-2"></i>NetBill BD
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="#packages">Packages</a>
                <a class="nav-link" href="#features">Features</a>
                <a class="nav-link" href="#about">About</a>
                <a class="nav-link" href="{{ route('customer.login') }}">Customer Login</a>
                <a class="nav-link btn btn-primary text-white ms-2" href="{{ route('admin.login') }}">Admin Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Smart Internet Billing Solution</h1>
            <p class="lead mb-4">Automated monthly billing for internet service providers in Bangladesh</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row text-start">
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-bolt"></i> Easy Package Management
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-file-invoice"></i> Automated Monthly Billing
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-mobile-alt"></i> Customer Self-Service Portal
                        </div>
                    </div>
                </div>
            </div>
            <a href="#packages" class="btn btn-light btn-lg mt-3">View Packages</a>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Internet Packages</h2>
                <p class="text-muted">Affordable packages for every need</p>
            </div>

            <div class="row">
                <!-- Regular Packages -->
                <div class="col-md-6 mb-4">
                    <h4 class="text-center mb-4">Regular Packages</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card package-card">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">Basic Speed</h5>
                                    <h3 class="text-success">৳500<small>/month</small></h3>
                                    <p class="text-muted">Perfect for everyday browsing and emails</p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Standard Speed</li>
                                        <li><i class="fas fa-check text-success"></i> Unlimited Data</li>
                                        <li><i class="fas fa-check text-success"></i> 1 Device</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card package-card">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">Fast Speed</h5>
                                    <h3 class="text-success">৳800<small>/month</small></h3>
                                    <p class="text-muted">Great for streaming and downloads</p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> High Speed</li>
                                        <li><i class="fas fa-check text-success"></i> Unlimited Data</li>
                                        <li><i class="fas fa-check text-success"></i> Up to 3 Devices</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Packages -->
                <div class="col-md-6 mb-4">
                    <h4 class="text-center mb-4">Add-on Packages</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card package-card">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">Gaming Boost</h5>
                                    <h3 class="text-success">৳200<small>/month</small></h3>
                                    <p class="text-muted">Low latency for gaming</p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Reduced Ping</li>
                                        <li><i class="fas fa-check text-success"></i> Priority Gaming</li>
                                        <li><i class="fas fa-check text-success"></i> 24/7 Support</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card package-card">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">Streaming Plus</h5>
                                    <h3 class="text-success">৳150<small>/month</small></h3>
                                    <p class="text-muted">Optimized for HD streaming</p>
                                    <ul class="list-unstyled">
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
            <div class="text-center mb-5">
                <h2 class="fw-bold">Why Choose NetBill BD?</h2>
            </div>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h5>Automated Billing</h5>
                    <p class="text-muted">Automatic monthly invoice generation and payment tracking</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h5>Customer Portal</h5>
                    <p class="text-muted">Self-service portal for customers to view bills and make payments</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Real-time Reports</h5>
                    <p class="text-muted">Comprehensive analytics and financial reporting</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Sections -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-user fa-3x text-primary mb-3"></i>
                            <h4>Customer Login</h4>
                            <p class="text-muted">Access your account to view bills and make payments</p>
                            <a href="{{ route('customer.login') }}" class="btn btn-primary">Customer Login</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-cog fa-3x text-success mb-3"></i>
                            <h4>Admin Login</h4>
                            <p class="text-muted">Administrator access for system management</p>
                            <a href="{{ route('admin.login') }}" class="btn btn-success">Admin Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-wifi me-2"></i>NetBill BD</h5>
                    <p>Smart internet billing solution for Bangladesh</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Contact: info@netbillbd.com | +880 1XXX-XXXXXX</p>
                    <p>&copy; 2024 NetBill BD. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>