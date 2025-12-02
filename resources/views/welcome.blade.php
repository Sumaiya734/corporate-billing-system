<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Nanosoft Billing</title>
  <link rel="icon" href="{{ asset('assets/nanosoft logo.png') }}" type="image/png">

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{
      --accent-1: #4361EE;
      --accent-2: #7C3AED;
      --muted: #6b7280;
      --success: #10B981;
      --card-radius: 14px;
      --glass: rgba(255,255,255,0.75);
    }

    html,body{height:100%}
    body{
      margin:0;
      font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg, #f5f7fb 0%, #eef2ff 100%);
      color:#0b1220;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    /* NAV */
    .navbar {
      background: rgba(242, 245, 247, 0.9);
      backdrop-filter: blur(6px);
      box-shadow: 0 6px 20px rgba(19, 24, 47, 0.06);
      padding: .6rem 1rem;
    }
    .navbar-brand { font-family: "Poppins"; color:var(--accent-1); font-weight:700; letter-spacing:0.2px; display:flex; align-items:center; gap:.6rem; }
    .nav-link{ color:var(--muted) !important; font-weight:600; padding:.5rem .75rem; }
    .btn-cta{
      background: linear-gradient(90deg,var(--accent-1),var(--accent-2));
      border: none;
      box-shadow: 0 10px 30px rgba(67,97,238,0.14);
      padding:.55rem 1rem;
      color:white;
      border-radius:10px;
      font-weight:700;
    }
    .btn-ghost{
      border:1px solid rgba(67,97,238,0.12);
      color:var(--accent-1);
      background: transparent;
      padding:.45rem .85rem;
      border-radius:10px;
      font-weight:600;
    }

    /* HERO */
    .hero {
      margin-top: .5rem;
      padding:3.2rem 0 2.8rem;
    }

    .hero-container {
      display:flex;
      gap:2rem;
      align-items:center;
      justify-content:space-between;
    }

    .hero-left{
      flex:1 1 52%;
      min-width: 320px;
    }

    .hero-right{
      flex:1 1 42%;
      min-width: 320px;
      border-radius:16px;
      overflow:hidden;
      box-shadow: 0 18px 40px rgba(12,15,29,0.06);
    }

    .hero-right .hero-image{
      width:100%;
      height:420px;
      background-image: url('{{ asset("assets/image1.jpg") }}');
      background-size: cover;
      background-position: center center;
      position:relative;
    }

    /* overlay for small-screen full-bg hero */
    .hero-full-bg {
      display:none;
      background-image: url('{{ asset("assets/image1.jpg") }}');
      background-size: cover;
      background-position:center;
      border-radius: 14px;
      position:relative;
      padding:3.5rem 1.5rem;
      color:white;
      overflow:hidden;
    }
    .hero-full-bg::before{
      content:'';
      position:absolute;
      inset:0;
      background: linear-gradient(180deg, rgba(2,6,23,0.55), rgba(2,6,23,0.55));
      z-index:0;
      pointer-events:none;
    }
    .hero-full-bg .hero-inner{ position:relative; z-index:1; }

    .kicker{
      display:inline-block;
      background: rgba(67,97,238,0.12);
      color:var(--accent-1);
      padding:.35rem .65rem;
      font-weight:700;
      border-radius:999px;
      font-size:.85rem;
      margin-bottom:.65rem;
    }

    h1.hero-title {
      font-family: "Poppins";
      font-size:2.25rem;
      margin:0 0 .6rem;
      line-height:1.02;
      letter-spacing:-0.4px;
    }

    p.hero-lead { color:var(--muted); font-size:1.02rem; margin-bottom:1.25rem; }

    .mini-features { display:flex; gap:1rem; flex-wrap:wrap; margin-top:1.25rem; }
    .mini-card {
      background: linear-gradient(180deg,#fff,#fbfcff);
      border-radius:10px;
      padding:.6rem .9rem;
      display:flex;
      gap:.6rem;
      align-items:center;
      box-shadow: 0 8px 20px rgba(12,15,29,0.04);
      border:1px solid rgba(12,15,29,0.03);
    }
    .mini-card i { font-size:1.1rem; color:var(--accent-1); width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:rgba(67,97,238,0.06); border-radius:10px; }

   /* Products continuous slider */
.product-section {
  margin-top:2rem;
  padding-top: 1rem;
  padding-bottom: 1rem;
}
.product-slider-wrapper {
  overflow:hidden;
  position:relative;
  padding:0.75rem 0;
  background: linear-gradient(180deg, #f5f3ff, #ede9fe);
  border-radius:10px;
  border:1px solid rgba(12,15,29,0.03);
  box-shadow: 0 10px 25px rgba(12,15,29,0.03);
}
.product-slider {
  display:flex;
  gap:15px;
  align-items:stretch;
  transform: translateZ(0);
  animation: scrollProducts var(--scroll-duration,20s) linear infinite;
}
.product-slider:hover { animation-play-state: paused; }
@keyframes scrollProducts {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

.product-card {
  border-radius:10px;
  overflow:hidden;
  min-width:260px;
  flex:0 0 260px;
  background: #fff;
  border:1px solid rgba(12,15,29,0.04);
  transition: transform .25s ease, box-shadow .25s ease;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
}
.product-card:hover { 
  transform: translateY(-6px);
  box-shadow: 0 16px 32px rgba(12,15,29,0.05);
}

.product-card .card-top { 
  padding:0.8rem;
  border-bottom:1px solid rgba(12,15,29,0.03);
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap: .5rem;
}
.product-name { 
  font-weight:700;
  font-size: 0.95rem;
}
.product-price { 
  color:var(--accent-1);
  font-weight:800;
  font-size:1.3rem;

}

.product-body .btn-outline-secondary,
.product-body .btn-cta {
  padding: 0.3rem 0.5rem; /* Smaller padding */
  font-size: 0.85rem; /* Smaller font size */
  min-height: 32px; /* Smaller minimum height */
}

.product-body .btn-outline-secondary {
  border-radius: 8px; /* Slightly smaller radius */
}

.product-body .btn-cta {
  border-radius: 8px; /* Slightly smaller radius */
  padding: 0.3rem 0.6rem; /* Slightly more horizontal padding for CTA */
}

/* For the duplicate cards (aria-hidden) */
.product-card[aria-hidden="true"] .btn-outline-secondary,
.product-card[aria-hidden="true"] .btn-cta {
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
}
.product-body { 
  padding:0.8rem;
  display:flex;
  flex-direction:column;
  gap:.4rem;
  min-height:140px;
}
.product-features li { 
  color:var(--muted);
  margin-bottom:.35rem;
  font-size:.85rem;
}

/* Footer */
footer{ 
  margin-top:2.5rem;
  padding:2rem 0;
  color: #6b7280;
  background:transparent;
}

/* Responsive */
@media (max-width: 992px){
  .hero-container{ 
    flex-direction:column-reverse;
    gap:1.25rem;
  }
  .hero-right{ 
    width:100%;
  }
  .hero-right .hero-image{ 
    height:260px;
    border-radius:12px;
  }
  .hero-left{ 
    width:100%;
  }
}

@media (max-width: 768px){
  .hero-container { 
    display:none;
  }
  .hero-full-bg { 
    display:block;
  }
  .product-card { 
    min-width: 220px;
    flex:0 0 220px;
  }
  .product-slider {
    gap:12px;
  }
}

.badge-cat { 
  font-weight:700;
  font-size:.78rem;
  padding:.35rem .55rem;
  border-radius:999px;
}
  </style>
</head>
<body>

<!-- NAV -->
<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container">
    <a class="navbar-brand" href="#">
      <img src="{{ asset('assets/nanosoft logo.png') }}" alt="logo" style="height:38px; width:38px; object-fit:contain; border-radius:8px;">
      Nanosoft Billing
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="#products">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        <!-- Authentication Dropdown Menu -->
        <li class="nav-item dropdown">
          @auth
            <div class="d-flex align-items-center gap-2">
              <span class="navbar-text text-dark fw-medium">{{ Auth::user()->name }}</span>
              @if(Auth::user()->role === 'customer')
                <a href="{{ route('customer.dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
              @elseif(Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
              @endif
              <form method="POST" action="{{ Auth::user()->role === 'customer' ? route('customer.logout') : route('admin.logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
              </form>
            </div>
          @else
            <a class="btn btn-primary text-white" href="#" id="userMenu"
                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Login
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                <li><a class="dropdown-item" href="{{ route('customer.login') }}">Customer Login</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.login') }}">Admin Login</a></li>
            </ul>
          @endauth
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="container hero">
  <div class="hero-container">
    <div class="hero-left">
      <span class="kicker">Trusted by Nanosoft in Bangladesh</span>
      <h1 class="hero-title">Smart, secure billing</h1>
      <p class="hero-lead">Automated billing, customer portal, revenue reports and easy migrations — a complete billing platform built for performance and scale.</p>

      <div class="mini-features">
        <div class="mini-card">
          <i class="fas fa-invoice"></i>
          <div>
            <div style="font-weight:700">Automated invoices</div>
            <small class="text-muted">Recurring & email-ready</small>
          </div>
        </div>

        <div class="mini-card">
          <i class="fas fa-chart-line"></i>
          <div>
            <div style="font-weight:700">Real-time reports</div>
            <small class="text-muted">Revenue & analytics</small>
          </div>
        </div>

        <div class="mini-card">
          <i class="fas fa-user-friends"></i>
          <div>
            <div style="font-weight:700">Customer portal</div>
            <small class="text-muted">Self-service payments</small>
          </div>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2">
        <a href="#products" class="btn btn-cta">Explore Products <i class="fas fa-arrow-right ms-2"></i></a>
        <a href="#contact" class="btn btn-ghost">Contact Sales</a>
      </div>

      <div class="mt-3 text-muted small">
        <strong>Free demo:</strong> Request a live demo and migrate data easily.
      </div>
    </div>

    <div class="hero-right">
      <div class="hero-image" role="img" aria-label="Nanosoft hero image"></div>
    </div>
  </div>

  <div class="hero-full-bg d-none">
    <div class="hero-inner text-center">
      <img src="{{ asset('assets/nanosoft logo.png') }}" alt="logo" style="height:64px; width:64px; margin-bottom:1rem;">
      <h2 class="fw-bold" style="font-size:1.5rem; margin-bottom:.25rem;">Nanosoft Billing</h2>
      <p class="mb-3 text-white-50">Automated billing & customer portal</p>
      <div>
        <a href="#products" class="btn btn-cta me-2">See products</a>
        <a href="#contact" class="btn btn-ghost">Contact</a>
      </div>
    </div>
  </div>
</section>

<!-- PRODUCTS -->
<section id="products" class="container product-section">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 style="font-weight:800; margin:0">Products/Services</h3>
      <p class="text-muted small mb-0">Simple pricing and flexible products for every customer.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary">All</button>
      <button class="btn btn-sm btn-outline-secondary">Regular</button>
      <button class="btn btn-sm btn-outline-secondary">Premium</button>
    </div>
  </div>

  <div class="product-slider-wrapper" id="productSliderWrapper" data-count="{{ isset($products) ? $products->count() : 0 }}">
    <div class="product-slider" id="productSlider">
      {{-- first pass of products --}}
      @if(isset($products) && $products->count())
        @foreach($products as $product)
          <div class="product-card">
            <div class="card-top">
              <div>
                <div class="product-name">{{ $product->name ?? 'Product' }}</div>
                @if(!empty($product->type_name))
                  <small class="text-muted">Type: {{ $product->type_name }}</small>
                @endif
              </div>

              <div class="text-end">
                <div class="product-price">৳{{ number_format($product->monthly_price ?? 0, 2) }}</div>
                <small class="text-muted">/ month</small>
              </div>
            </div>

            <div class="product-body">
              <ul class="product-features list-unstyled mb-0">
                @if(!empty($product->description))
                  <li><i class="fas fa-info-circle me-2 text-primary"></i> {{ \Illuminate\Support\Str::limit($product->description, 70) }}</li>
                @else
                  <li><i class="fas fa-info-circle me-2 text-primary"></i> No description available</li>
                @endif
                
                <li><i class="fas fa-hashtag me-2 text-primary"></i> Product ID: <strong>{{ $product->p_id }}</strong></li>
                
                @if(!empty($product->type_name))
                  <li><i class="fas fa-tag me-2 text-primary"></i> Category: <strong>{{ ucfirst($product->type_name) }}</strong></li>
                @endif
              </ul>

              <div class="mt-3 d-flex gap-2">
                <button class="btn btn-outline-secondary w-50" data-bs-toggle="modal" data-bs-target="#productModal{{ $product->p_id }}">Details</button>

                @auth
                  @if(Auth::user()->role === 'customer')
                    <a href="{{ route('customer.subscribe', ['id'=> $product->p_id]) }}" class="btn btn-cta w-50">Subscribe</a>
                  @else
                    <a href="#contact" class="btn btn-cta w-50">Contact Sales</a>
                  @endif
                @else
                  <a href="{{ route('customer.login') }}" class="btn btn-cta w-50">Login to Buy</a>
                @endauth
              </div>
            </div>

            <div style="padding:.8rem 1rem; border-top:1px solid rgba(12,15,29,0.03); display:flex; justify-content:space-between; align-items:center;">
              @php
                $categoryColor = 'rgba(67,97,238,0.08)';
                $categoryTextColor = 'var(--accent-1)';
                if($product->type_name === 'special' || $product->type_name === 'Silver' || $product->type_name === 'Diamond' || $product->type_name === 'Platinum') {
                  $categoryColor = 'rgba(16,185,129,0.06)';
                  $categoryTextColor = 'var(--success)';
                } elseif($product->type_name === 'business') {
                  $categoryColor = 'rgba(239,68,68,0.06)';
                  $categoryTextColor = '#ef4444';
                }
              @endphp
              <span class="badge-cat" style="background:{{ $categoryColor }}; color:{{ $categoryTextColor }};">
                {{ $product->type_name ?? 'Product' }}
              </span>
              <small class="text-muted">ID: {{ $product->p_id }}</small>
            </div>
          </div>
        @endforeach

        {{-- duplicate pass for smooth infinite scroll --}}
        @foreach($products as $product)
          <div class="product-card" aria-hidden="true">
            <div class="card-top">
              <div>
                <div class="product-name">{{ $product->name ?? 'Product' }}</div>
              </div>
              <div class="text-end">
                <div class="product-price">৳{{ number_format($product->monthly_price ?? 0, 2) }}</div>
                <small class="text-muted">/ month</small>
              </div>
            </div>
            <div class="product-body">
              <ul class="product-features list-unstyled mb-0">
                @if(!empty($product->description))
                  <li><i class="fas fa-info-circle me-2 text-primary"></i> {{ \Illuminate\Support\Str::limit($product->description, 50) }}</li>
                @endif
              </ul>
              <div class="mt-3 d-flex gap-2">
                <button class="btn btn-outline-secondary w-50" disabled>Details</button>
                <a class="btn btn-cta w-50" href="#contact">Contact</a>
              </div>
            </div>
            <div style="padding:.8rem 1rem; border-top:1px solid rgba(12,15,29,0.03);">
              <span class="badge-cat" style="background:rgba(67,97,238,0.08); color:var(--accent-1);">{{ $product->type_name ?? 'Product' }}</span>
            </div>
          </div>
        @endforeach

      @else
        {{-- Fallback when no products --}}
        <div class="col-12 text-center py-5">
          <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
          <h5>No products available</h5>
          <p class="text-muted">Check back later or contact sales for more information.</p>
        </div>
      @endif
    </div>
  </div>

  <div class="text-center mt-3">
    @if(isset($products))
      <small class="text-muted">Showing {{ $products->count() }} products</small>
    @endif
  </div>
</section>
<!-- CIRCULAR ANIMATED STATISTICS CAROUSEL -->
<section id="statistics" class="container mt-5 mb-5">
  <div class="text-center mb-5">
    <h3 style="font-weight:800; margin:0">Our Achievements</h3>
    <p class="text-muted small mb-0">Numbers that speak for our success</p>
  </div>
  
  <div class="circular-stats-wrapper">
    <div class="circular-stats-slider">
      <!-- Stat 1: Total Customers -->
      <div class="circular-stat-card">
        <div class="circular-progress" data-value="{{ DB::table('customers')->count() }}">
          <div class="circular-progress-circle">
            <svg class="circular-svg" viewBox="0 0 36 36">
              <path class="circular-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path class="circular-progress-path" stroke-dasharray="0, 100"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="circular-progress-value">0</div>
          </div>
        </div>
        <div class="circular-stat-info">
          <i class="fas fa-users"></i>
          <h4>Happy Customers</h4>
          <p>Trusting our billing solutions</p>
        </div>
      </div>

      <!-- Stat 2: Available Products -->
      <div class="circular-stat-card">
        <div class="circular-progress" data-value="{{ DB::table('products')->count() }}">
          <div class="circular-progress-circle">
            <svg class="circular-svg" viewBox="0 0 36 36">
              <path class="circular-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path class="circular-progress-path" stroke-dasharray="0, 100"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="circular-progress-value">0</div>
          </div>
        </div>
        <div class="circular-stat-info">
          <i class="fas fa-boxes"></i>
          <h4>Products & Services</h4>
          <p>Tailored for your needs</p>
        </div>
      </div>

      <!-- Stat 3: Monthly Revenue -->
      <div class="circular-stat-card">
        @php
          $currentMonth = date('Y-m');
          $monthlyRevenue = DB::table('invoices')
            ->where('issue_date', 'LIKE', $currentMonth . '%')
            ->whereIn('status', ['paid', 'partial'])
            ->sum('received_amount');
          $revenueValue = round($monthlyRevenue / 10000); // Scale down for display
        @endphp
        <div class="circular-progress" data-value="{{ min($revenueValue, 100) }}">
          <div class="circular-progress-circle">
            <svg class="circular-svg" viewBox="0 0 36 36">
              <path class="circular-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path class="circular-progress-path" stroke-dasharray="0, 100"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="circular-progress-value">0</div>
          </div>
        </div>
        <div class="circular-stat-info">
          <i class="fas fa-chart-line"></i>
          <h4>Monthly Revenue</h4>
          <p>৳{{ number_format($monthlyRevenue, 0) }}+</p>
        </div>
      </div>

      <!-- Stat 4: Active Services -->
      <div class="circular-stat-card">
        <div class="circular-progress" data-value="{{ DB::table('customer_to_products')->where('status', 'active')->count() }}">
          <div class="circular-progress-circle">
            <svg class="circular-svg" viewBox="0 0 36 36">
              <path class="circular-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path class="circular-progress-path" stroke-dasharray="0, 100"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="circular-progress-value">0</div>
          </div>
        </div>
        <div class="circular-stat-info">
          <i class="fas fa-network-wired"></i>
          <h4>Active Services</h4>
          <p>Running smoothly</p>
        </div>
      </div>

      <!-- Stat 5: Invoices Processed -->
      <div class="circular-stat-card">
        @php
          $totalInvoices = DB::table('invoices')->count();
          $invoicePercentage = min(($totalInvoices / 500) * 100, 100); // Cap at 100%
        @endphp
        <div class="circular-progress" data-value="{{ $invoicePercentage }}">
          <div class="circular-progress-circle">
            <svg class="circular-svg" viewBox="0 0 36 36">
              <path class="circular-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path class="circular-progress-path" stroke-dasharray="0, 100"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="circular-progress-value">0</div>
          </div>
        </div>
        <div class="circular-stat-info">
          <i class="fas fa-file-invoice-dollar"></i>
          <h4>Invoices Processed</h4>
          <p>{{ number_format($totalInvoices) }}+ invoices</p>
        </div>
      </div>

      <!-- Stat 6: Satisfaction Rate -->
      <div class="circular-stat-card">
        <div class="circular-progress" data-value="99">
          <div class="circular-progress-circle">
            <svg class="circular-svg" viewBox="0 0 36 36">
              <path class="circular-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path class="circular-progress-path" stroke-dasharray="0, 100"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="circular-progress-value">0%</div>
          </div>
        </div>
        <div class="circular-stat-info">
          <i class="fas fa-star"></i>
          <h4>Satisfaction Rate</h4>
          <p>Customer happiness</p>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Navigation arrows -->
  <div class="circular-nav text-center mt-4">
    <button class="circular-nav-btn prev">
      <i class="fas fa-chevron-left"></i>
    </button>
    <div class="circular-dots">
      <span class="circular-dot active" data-slide="0"></span>
      <span class="circular-dot" data-slide="1"></span>
      <span class="circular-dot" data-slide="2"></span>
      <span class="circular-dot" data-slide="3"></span>
      <span class="circular-dot" data-slide="4"></span>
      <span class="circular-dot" data-slide="5"></span>
    </div>
    <button class="circular-nav-btn next">
      <i class="fas fa-chevron-right"></i>
    </button>
  </div>
</section>

<style>
  /* Circular Statistics Carousel */
  #statistics {
    margin-bottom: 3rem;
  }
  
  .circular-stats-wrapper {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    overflow: hidden;
    padding: 2rem 0;
  }
  
  .circular-stats-slider {
    display: flex;
    transition: transform 0.5s ease-in-out;
    gap: 40px;
    padding: 0 20px;
  }
  
  .circular-stat-card {
    flex: 0 0 calc(33.333% - 27px);
    min-width: calc(33.333% - 27px);
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 15px 35px rgba(67,97,238,0.1);
    border: 2px solid rgba(67,97,238,0.1);
    text-align: center;
    transition: all 0.4s ease;
  }
  
  .circular-stat-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 25px 50px rgba(67,97,238,0.2);
    border-color: rgba(67,97,238,0.3);
  }
  
  /* Circular Progress */
  .circular-progress {
    position: relative;
    margin: 0 auto 2rem;
    width: 180px;
    height: 180px;
  }
  
  .circular-progress-circle {
    position: relative;
    width: 100%;
    height: 100%;
  }
  
  .circular-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
  }
  
  .circular-bg {
    fill: none;
    stroke: #f0f0f0;
    stroke-width: 3.8;
  }
  
  .circular-progress-path {
    fill: none;
    stroke-width: 3.8;
    stroke-linecap: round;
    stroke: var(--accent-1);
    transition: stroke-dasharray 2s ease-in-out;
  }
  
  .circular-progress-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--accent-1);
  }
  
  /* Stat Info */
  .circular-stat-info {
    margin-top: 1rem;
  }
  
  .circular-stat-info i {
    font-size: 2rem;
    color: var(--accent-2);
    margin-bottom: 0.5rem;
  }
  
  .circular-stat-info h4 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #333;
    margin: 0.5rem 0;
  }
  
  .circular-stat-info p {
    color: var(--muted);
    font-size: 0.9rem;
    margin: 0;
  }
  
  /* Navigation */
  .circular-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2rem;
    margin-top: 2rem;
  }
  
  .circular-nav-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: white;
    border: 2px solid var(--accent-1);
    color: var(--accent-1);
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .circular-nav-btn:hover {
    background: var(--accent-1);
    color: white;
    transform: scale(1.1);
  }
  
  .circular-dots {
    display: flex;
    gap: 10px;
  }
  
  .circular-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ddd;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .circular-dot.active {
    background: var(--accent-1);
    transform: scale(1.3);
  }
  
  .circular-dot:hover {
    background: var(--accent-2);
  }
  
  /* Responsive */
  @media (max-width: 992px) {
    .circular-stat-card {
      flex: 0 0 calc(50% - 20px);
      min-width: calc(50% - 20px);
    }
    
    .circular-progress {
      width: 160px;
      height: 160px;
    }
    
    .circular-progress-value {
      font-size: 2.2rem;
    }
  }
  
  @media (max-width: 768px) {
    .circular-stat-card {
      flex: 0 0 calc(100% - 40px);
      min-width: calc(100% - 40px);
      padding: 1.5rem;
    }
    
    .circular-stats-slider {
      gap: 30px;
    }
    
    .circular-progress {
      width: 140px;
      height: 140px;
      margin-bottom: 1.5rem;
    }
    
    .circular-progress-value {
      font-size: 2rem;
    }
    
    .circular-stat-info i {
      font-size: 1.8rem;
    }
    
    .circular-stat-info h4 {
      font-size: 1.1rem;
    }
    
    .circular-nav-btn {
      width: 40px;
      height: 40px;
      font-size: 1rem;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.circular-stats-slider');
    const cards = document.querySelectorAll('.circular-stat-card');
    const dots = document.querySelectorAll('.circular-dot');
    const prevBtn = document.querySelector('.circular-nav-btn.prev');
    const nextBtn = document.querySelector('.circular-nav-btn.next');
    let currentSlide = 0;
    const totalSlides = cards.length;
    let slidesToShow = 3;
    let isAnimating = false;
    
    // Initialize all counters to 0
    const progressValues = document.querySelectorAll('.circular-progress-value');
    progressValues.forEach(value => {
      if (value.textContent.includes('+')) {
        value.textContent = '0+';
      } else if (value.textContent.includes('৳')) {
        value.textContent = '৳0';
      } else if (value.textContent.includes('%')) {
        value.textContent = '0%';
      } else {
        value.textContent = '0';
      }
    });
    
    // Animate circular progress
    function animateProgress(card, targetValue) {
      const progressPath = card.querySelector('.circular-progress-path');
      const progressValue = card.querySelector('.circular-progress-value');
      const isPercentage = progressValue.textContent.includes('%');
      const hasPlus = progressValue.textContent.includes('+');
      const hasCurrency = progressValue.textContent.includes('৳');
      
      // Set stroke dasharray for circle
      const circumference = 2 * Math.PI * 15.9155;
      const strokeDasharray = (targetValue * circumference) / 100;
      progressPath.style.strokeDasharray = `${strokeDasharray} ${circumference}`;
      
      // Animate counter
      let startValue = 0;
      let endValue = targetValue;
      let duration = 2000; // 2 seconds
      let startTime = null;
      
      function animateCounter(timestamp) {
        if (!startTime) startTime = timestamp;
        const progress = timestamp - startTime;
        const percentage = Math.min(progress / duration, 1);
        
        // Easing function
        const easeOutQuart = 1 - Math.pow(1 - percentage, 4);
        const currentValue = Math.floor(easeOutQuart * endValue);
        
        // Update display
        if (isPercentage) {
          progressValue.textContent = currentValue + '%';
        } else if (hasCurrency) {
          // For revenue, animate to scaled value
          const actualValue = Math.floor(easeOutQuart * ({{ $revenueValue }} * 10000));
          progressValue.textContent = '৳' + Math.floor(actualValue / 10000) * 10000;
        } else if (hasPlus) {
          progressValue.textContent = currentValue + '+';
        } else {
          progressValue.textContent = currentValue;
        }
        
        if (percentage < 1) {
          requestAnimationFrame(animateCounter);
        }
      }
      
      requestAnimationFrame(animateCounter);
    }
    
    // Update slides to show based on screen size
    function updateSlidesToShow() {
      if (window.innerWidth <= 768) {
        slidesToShow = 1;
      } else if (window.innerWidth <= 992) {
        slidesToShow = 2;
      } else {
        slidesToShow = 3;
      }
    }
    
    function updateSlider() {
      if (isAnimating) return;
      isAnimating = true;
      
      const cardWidth = cards[0].offsetWidth + 40; // card width + gap
      const translateX = -currentSlide * cardWidth;
      slider.style.transform = `translateX(${translateX}px)`;
      
      // Update dots
      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
      });
      
      // Animate the visible cards
      setTimeout(() => {
        const startIdx = currentSlide;
        const endIdx = Math.min(currentSlide + slidesToShow, totalSlides);
        
        for (let i = startIdx; i < endIdx; i++) {
          const card = cards[i];
          const targetValue = parseInt(card.querySelector('.circular-progress').dataset.value);
          
          // Reset and animate
          const progressValue = card.querySelector('.circular-progress-value');
          if (progressValue.textContent.includes('+')) {
            progressValue.textContent = '0+';
          } else if (progressValue.textContent.includes('৳')) {
            progressValue.textContent = '৳0';
          } else if (progressValue.textContent.includes('%')) {
            progressValue.textContent = '0%';
          } else {
            progressValue.textContent = '0';
          }
          
          animateProgress(card, targetValue);
        }
        
        isAnimating = false;
      }, 300);
    }
    
    // Initialize
    updateSlidesToShow();
    updateSlider();
    
    // Animate initial visible cards
    setTimeout(() => {
      for (let i = 0; i < Math.min(slidesToShow, totalSlides); i++) {
        const card = cards[i];
        const targetValue = parseInt(card.querySelector('.circular-progress').dataset.value);
        animateProgress(card, targetValue);
      }
    }, 500);
    
    // Navigation
    prevBtn.addEventListener('click', () => {
      if (currentSlide > 0) {
        currentSlide--;
        updateSlider();
      }
    });
    
    nextBtn.addEventListener('click', () => {
      if (currentSlide < totalSlides - slidesToShow) {
        currentSlide++;
        updateSlider();
      }
    });
    
    // Dot navigation
    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        if (index <= totalSlides - slidesToShow) {
          currentSlide = index;
          updateSlider();
        }
      });
    });
    
    // Auto slide every 8 seconds
    let slideInterval = setInterval(() => {
      if (currentSlide < totalSlides - slidesToShow) {
        currentSlide++;
      } else {
        currentSlide = 0;
      }
      updateSlider();
    }, 8000);
    
    // Pause on hover
    slider.addEventListener('mouseenter', () => {
      clearInterval(slideInterval);
    });
    
    slider.addEventListener('mouseleave', () => {
      slideInterval = setInterval(() => {
        if (currentSlide < totalSlides - slidesToShow) {
          currentSlide++;
        } else {
          currentSlide = 0;
        }
        updateSlider();
      }, 8000);
    });
    
    // Update on resize
    window.addEventListener('resize', () => {
      updateSlidesToShow();
      if (currentSlide >= totalSlides - slidesToShow + 1) {
        currentSlide = totalSlides - slidesToShow;
      }
      updateSlider();
    });
    
    // Touch swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    
    slider.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    });
    
    slider.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    });
    
    function handleSwipe() {
      const swipeThreshold = 50;
      const difference = touchStartX - touchEndX;
      
      if (Math.abs(difference) > swipeThreshold) {
        if (difference > 0) {
          // Swipe left - next slide
          if (currentSlide < totalSlides - slidesToShow) {
            currentSlide++;
          }
        } else {
          // Swipe right - previous slide
          if (currentSlide > 0) {
            currentSlide--;
          }
        }
        updateSlider();
      }
    }
  });
</script>

<!-- FEATURES -->
<section id="features" class="container mt-5">
  <div class="row align-items-center g-4">
    <div class="col-lg-6">
      <h4 style="font-weight:700">Everything you need for billing management</h4>
      <p class="text-muted">Billing automation, customer management, revenue reports and secure payment integrations — engineered for simplicity and scale.</p>
      <ul class="list-unstyled">
        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Automated invoices & reminders</li>
        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Customer self-service portal</li>
        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>CSV import and migration tools</li>
        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Detailed accounting exports</li>
      </ul>
    </div>

    <div class="col-lg-6">
      <div class="row g-3">
        <div class="col-sm-6">
          <div class="mini-card">
            <i class="fas fa-lock"></i>
            <div>
              <div style="font-weight:700">Secure</div>
              <small class="text-muted">Encryption & backups</small>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="mini-card">
            <i class="fas fa-cloud-upload-alt"></i>
            <div>
              <div style="font-weight:700">Cloud-ready</div>
              <small class="text-muted">Deploy on-prem or cloud</small>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="mini-card">
            <i class="fas fa-cogs"></i>
            <div>
              <div style="font-weight:700">Customizable</div>
              <small class="text-muted">Billing cycles, discounts</small>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="mini-card">
            <i class="fas fa-headset"></i>
            <div>
              <div style="font-weight:700">Local support</div>
              <small class="text-muted">Bangladesh-based team</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section id="about" class="container mt-5">
  <div class="row g-4 align-items-center">
    <div class="col-lg-6">
      <h4 style="font-weight:800">About Nanosoft Billing</h4>
      <p class="text-muted">Built for businesses in Bangladesh, our billing system focuses on dependable automation and an outstanding customer experience. Whether you're migrating or starting fresh — we make billing easy.</p>
      <a class="btn btn-ghost" href="#contact">Get in touch</a>
    </div>

    <div class="col-lg-6">
      <img src="{{ asset('assets/nanosoft logo.png') }}" alt="about" class="img-fluid rounded" style="box-shadow: 0 18px 40px rgba(12,15,29,0.06); max-width:360px;">
    </div>
  </div>
</section>

<!-- CONTACT -->
<section id="contact" class="container mt-5 mb-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card" style="border-radius:12px; box-shadow: 0 18px 40px rgba(12,15,29,0.06);">
        <div class="card-body p-4">
          <h5 style="font-weight:700">Contact Sales</h5>
          <p class="text-muted">Have questions? We'll respond within one business day.</p>

          <div class="row g-3 mb-3">
            <div class="col-md-4 text-center">
              <i class="fas fa-map-marker-alt fa-2x text-primary mb-2"></i>
              <div class="fw-bold">Dhaka, Bangladesh</div>
              <div class="small text-muted">Local presence</div>
            </div>

            <div class="col-md-4 text-center">
              <i class="fas fa-phone fa-2x text-primary mb-2"></i>
              <div class="fw-bold">+880 123-456-7890</div>
              <div class="small text-muted">Call / WhatsApp</div>
            </div>

            <div class="col-md-4 text-center">
              <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
              <div class="fw-bold">info@nanosoftbilling.com</div>
              <div class="small text-muted">Email us</div>
            </div>
          </div>

          <form action="#" method="POST">
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <input name="name" class="form-control" placeholder="Full name" required>
              </div>
              <div class="col-md-6">
                <input name="email" type="email" class="form-control" placeholder="Email address" required>
              </div>
              <div class="col-12">
                <textarea name="message" class="form-control" placeholder="Tell us about your needs..." rows="4" required></textarea>
              </div>
              <div class="col-12 text-end">
                <button class="btn btn-cta" type="submit">Send message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="container">
  <div class="row align-items-center">
    <div class="col-md-6">
      <div style="font-weight:700">© {{ date('Y') }} Nanosoft Billing</div>
      <div class="small text-muted">Designed with ❤️ for Nanosoft in Bangladesh</div>
    </div>
    <div class="col-md-6 text-md-end">
      <a href="#" class="text-muted me-3">Privacy</a>
      <a href="#" class="text-muted">Terms</a>
    </div>
  </div>
</footer>

<!-- Product modals -->
@if(isset($products) && $products->count())
  @foreach($products as $product)
    <div class="modal fade" id="productModal{{ $product->p_id }}" tabindex="-1" aria-labelledby="productModalLabel{{ $product->p_id }}" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:12px;">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-box me-2"></i> {{ $product->name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <h3 class="mb-1">৳{{ number_format($product->monthly_price ?? 0, 2) }} <small class="text-muted">/ month</small></h3>
                
                @if(!empty($product->description))
                  <p class="text-muted">{{ $product->description }}</p>
                @else
                  <p class="text-muted">No description available for this product.</p>
                @endif

                <ul class="list-unstyled mt-3">
                  <li><i class="fas fa-hashtag me-2 text-muted"></i> <strong>Product ID:</strong> {{ $product->p_id }}</li>
                  
                  @if(!empty($product->type_name))
                    <li><i class="fas fa-tag me-2 text-muted"></i> <strong>Type:</strong> {{ ucfirst($product->type_name) }}</li>
                  @endif
                  
                  <li><i class="fas fa-calendar-alt me-2 text-muted"></i> <strong>Billing:</strong> Monthly</li>
                  <li><i class="fas fa-shield-alt me-2 text-muted"></i> <strong>Support:</strong> Available</li>
                </ul>
              </div>

              <div class="col-md-6">
                <div class="bg-light p-3 rounded">
                  <h6 class="mb-2">Product Information</h6>
                  
                  <div class="mb-2">
                    <strong>Name:</strong> {{ $product->name }}
                  </div>
                  
                  <div class="mb-2">
                    <strong>Monthly Price:</strong> ৳{{ number_format($product->monthly_price ?? 0, 2) }}
                  </div>
                  
                  @if(!empty($product->type_name))
                    <div class="mb-3">
                      <strong>Category:</strong> 
                      @php
                        $badgeClass = 'bg-primary';
                        if($product->type_name === 'special' || $product->type_name === 'Silver' || $product->type_name === 'Diamond' || $product->type_name === 'Platinum') {
                          $badgeClass = 'bg-success';
                        } elseif($product->type_name === 'business') {
                          $badgeClass = 'bg-danger';
                        }
                      @endphp
                      <span class="badge {{ $badgeClass }}">{{ ucfirst($product->type_name) }}</span>
                    </div>
                  @endif

                  <hr>

                  <h6 class="mb-2">Need help?</h6>
                  <p class="small text-muted mb-0">Contact our sales team for pricing, customization or any questions about this product.</p>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <a href="#contact" class="btn btn-cta">Contact Sales</a>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endif

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click', function(e){
      const href = this.getAttribute('href');
      if(href.length > 1){
        e.preventDefault();
        const el = document.querySelector(href);
        if(el) el.scrollIntoView({behavior:'smooth', block:'start'});
      }
    });
  });

  // Set product slider animation speed based on number of visible items
  (function(){
    const wrapper = document.getElementById('productSliderWrapper');
    const slider = document.getElementById('productSlider');
    if(!wrapper || !slider) return;

    const count = parseInt(wrapper.getAttribute('data-count') || '0', 10);
    const secondsPerItem = 4.0;

    if(count <= 0) {
      document.documentElement.style.setProperty('--scroll-duration', '25s');
      return;
    }

    const duration = Math.max(18, Math.round(count * secondsPerItem));
    document.documentElement.style.setProperty('--scroll-duration', duration + 's');

    const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
    if(mq && mq.matches) {
      slider.style.animationPlayState = 'paused';
    }

    document.addEventListener('visibilitychange', function(){
      if(document.hidden) slider.style.animationPlayState = 'paused';
      else slider.style.animationPlayState = 'running';
    });

  })();

  // Make sure mobile layout shows hero-full-bg when small
  function updateHeroVisibility() {
    const fullBg = document.querySelector('.hero-full-bg');
    const split = document.querySelector('.hero-container');
    if(!fullBg || !split) return;
    if(window.innerWidth <= 768){
      fullBg.style.display = 'block';
      split.style.display = 'none';
    } else {
      fullBg.style.display = 'none';
      split.style.display = 'flex';
    }
  }
  window.addEventListener('load', updateHeroVisibility);
  window.addEventListener('resize', updateHeroVisibility);

  // Accessibility: focus first focusable inside modal when shown
  document.querySelectorAll('.modal').forEach(modalEl=>{
    modalEl.addEventListener('shown.bs.modal', function(){
      const focusable = this.querySelector('button, [href], input, textarea, select');
      if(focusable) focusable.focus();
    });
  });
</script>
</body>
</html>