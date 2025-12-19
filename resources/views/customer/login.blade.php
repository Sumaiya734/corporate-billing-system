<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Login - Nanosoft Billing</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      background: linear-gradient(135deg, #f0f8ff, #e6f2ff);
      font-family: 'Poppins', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
      overflow: hidden;
      position: relative;
    }

    /* Floating background circles */
    .circle {
      position: fixed;
      border-radius: 50%;
      background: rgba(255,255,255,0.45);
      filter: blur(60px);
      animation: float 8s ease-in-out infinite;
      z-index: 0;
    }
    .circle1 { 
      width: 200px; 
      height: 200px; 
      top: -40px; 
      left: -30px; 
      background: rgba(52, 152, 219, 0.15);
    }
    .circle2 { 
      width: 250px; 
      height: 250px; 
      bottom: -70px; 
      right: -40px; 
      animation-delay: 2s;
      background: rgba(46, 204, 113, 0.15);
    }

    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(20px); }
      100% { transform: translateY(0px); }
    }

    /* Logo Row - Made smaller */
    .logo-row {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 15px;
      z-index: 2;
      position: relative;
    }
    .logo img {
      width: 120px;
      margin-right: 12px;
    }
    .logo-title {
      font-size: 1.7rem;
      font-weight: 700;
      background: linear-gradient(135deg, #3498db, #2ecc71);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Compact wrapper */
    .compact-wrapper {
      width: 100%;
      max-width: 1000px;
      display: flex;
      gap: 30px;
      align-items: center;
      justify-content: center;
      z-index: 2;
      position: relative;
      margin: 0 auto;
    }

    /* Compact login box */
    .compact-login-box {
      flex: 0.85;
      background: rgba(255,255,255,0.7);
      padding: 35px;
      border-radius: 20px;
      backdrop-filter: blur(12px);
      box-shadow: 0 10px 35px rgba(52, 152, 219, 0.12);
      animation: fadeUp 0.7s ease;
      border: 1px solid rgba(255,255,255,0.3);
      max-height: 520px;
      overflow-y: auto;
    }

    /* Hide scrollbar for Chrome, Safari and Opera */
    .compact-login-box::-webkit-scrollbar {
      display: none;
    }
    
    /* Hide scrollbar for IE, Edge and Firefox */
    .compact-login-box {
      -ms-overflow-style: none;  /* IE and Edge */
      scrollbar-width: none;  /* Firefox */
    }

    .compact-image-box {
      flex: 1.15;
      height: 450px;
      border-radius: 20px;
      background: url("https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80") no-repeat center center/cover;
      box-shadow: 0 12px 35px rgba(0,0,0,0.12);
      animation: fadeUp 0.9s ease;
      position: relative;
      overflow: hidden;
    }

    .compact-image-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(52, 152, 219, 0.2), rgba(46, 204, 113, 0.2));
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-title {
      font-size: 1.6rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 6px;
    }

    .login-subtitle {
      color: #7f8c8d;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px 14px;
      border: 1.5px solid #e0e6ed;
      background: rgba(255,255,255,0.9);
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .form-control:focus {
      border-color: #3498db;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .form-label {
      font-weight: 500;
      color: #2c3e50;
      margin-bottom: 6px;
      font-size: 0.9rem;
    }

    .btn-login {
      background: linear-gradient(135deg, #3498db, #2ecc71);
      color: #fff;
      padding: 12px;
      border-radius: 10px;
      width: 100%;
      font-size: 0.95rem;
      font-weight: 500;
      border: none;
      box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
      transition: all 0.3s ease;
      margin-top: 8px;
    }
    .btn-login:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
    }

    .btn-register {
      background: transparent;
      color: #3498db;
      padding: 10px;
      border-radius: 8px;
      width: 100%;
      font-size: 0.9rem;
      font-weight: 500;
      border: 2px solid #3498db;
      transition: all 0.3s ease;
      margin-top: 8px;
    }
    .btn-register:hover {
      background: rgba(52, 152, 219, 0.1);
    }

    .back-home {
      color: #7f8c8d;
      text-decoration: none;
      font-size: 0.85rem;
      transition: color 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    .back-home:hover {
      color: #3498db;
    }

    .alert {
      border-radius: 10px;
      border: none;
      box-shadow: 0 3px 12px rgba(0,0,0,0.06);
      padding: 12px 15px;
      margin-bottom: 15px;
      font-size: 0.85rem;
    }

    .alert-danger {
      background: rgba(231, 76, 60, 0.1);
      color: #c0392b;
      border-left: 4px solid #e74c3c;
    }

    .alert-success {
      background: rgba(46, 204, 113, 0.1);
      color: #27ae60;
      border-left: 4px solid #2ecc71;
    }

    .login-features {
      margin-top: 20px;
      padding-top: 15px;
      border-top: 1px solid rgba(0,0,0,0.06);
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
      color: #5d6d7e;
      font-size: 0.85rem;
    }

    .feature-item i {
      color: #3498db;
      font-size: 0.85rem;
    }

    /* Responsive styles */
    @media(max-width: 900px) {
      .compact-wrapper {
        flex-direction: column;
        gap: 25px;
        max-width: 500px;
      }
      
      .compact-login-box {
        width: 100%;
        padding: 30px;
        max-height: none;
        overflow: visible;
      }
      
      .compact-image-box {
        width: 100%;
        height: 250px;
        order: -1;
      }
      
      .logo-row {
        margin-bottom: 10px;
      }
      
      .logo img {
        width: 100px;
      }
      
      .logo-title {
        font-size: 1.5rem;
      }
    }

    @media(max-width: 576px) {
      body {
        padding: 15px;
      }
      
      .compact-login-box {
        padding: 25px;
      }
      
      .compact-image-box {
        height: 200px;
      }
      
      .logo img {
        width: 90px;
        margin-right: 10px;
      }
      
      .logo-title {
        font-size: 1.3rem;
      }
    }

    /* Ensure no overflow */
    .compact-login-box form {
      margin-bottom: 0;
    }
  </style>
</head>

<body>
  <!-- Floating background lights -->
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>

  <!-- Logo Row -->
  <div class="logo-row">
    <div class="logo">
      <img src="{{ asset('assets/nanosoft logo.png') }}" alt="Nanosoft Logo">
    </div>
    <h2 class="logo-title">Nanosoft Billing</h2>
  </div>

  <!-- Compact Wrapper -->
  <div class="compact-wrapper">
    <!-- LEFT COMPACT LOGIN BOX -->
    <div class="compact-login-box">
      <h2 class="login-title">
        <i class="fas fa-user me-2"></i>Customer Login
      </h2>
      <p class="login-subtitle">Welcome back! Please login to access your account.</p>

      <!-- Display validation errors -->
      @if ($errors->any())
        <div class="alert alert-danger">
          <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-circle me-2 mt-1"></i>
            <div>
              <strong>Please fix the following errors:</strong>
              <ul class="mb-0 mt-2 small">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      @endif
      
      <!-- Display session error messages -->
      @if (session('error'))
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle me-2"></i>
          {{ session('error') }}
        </div>
      @endif

      <!-- Display success messages -->
      @if (session('success'))
        <div class="alert alert-success">
          <i class="fas fa-check-circle me-2"></i>
          {{ session('success') }}
        </div>
      @endif

      <form method="POST" action="{{ route('customer.login.submit') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0">
              <i class="fas fa-envelope text-muted"></i>
            </span>
            <input type="email" 
                   class="form-control border-start-0" 
                   name="email" 
                   value="{{ old('email') }}" 
                   placeholder="Enter your email" 
                   required>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0">
              <i class="fas fa-lock text-muted"></i>
            </span>
            <input type="password" 
                   class="form-control border-start-0" 
                   name="password" 
                   placeholder="Enter your password" 
                   required>
          </div>
        </div>

        <button type="submit" class="btn btn-login">
          <i class="fas fa-sign-in-alt me-2"></i>Login to Your Account
        </button>

        <div class="mt-3 text-center">
          <a href="{{ route('customer.register') }}" class="btn btn-register mb-2">
            <i class="fas fa-user-plus me-2"></i>Create New Account
          </a>
          <div class="mt-2">
            <a href="{{ url('/') }}" class="back-home">
              <i class="fas fa-arrow-left me-1"></i>Back to Home
            </a>
          </div>
        </div>
      </form>

      <!-- Compact Features Section -->
      <div class="login-features">
        <p class="small text-muted mb-2">As a customer you can:</p>
        <div class="row">
          <div class="col-6">
            <div class="feature-item">
              <i class="fas fa-check-circle"></i>
              <span>View invoices</span>
            </div>
          </div>
          <div class="col-6">
            <div class="feature-item">
              <i class="fas fa-check-circle"></i>
              <span>Pay online</span>
            </div>
          </div>
          <div class="col-6">
            <div class="feature-item">
              <i class="fas fa-check-circle"></i>
              <span>Track payments</span>
            </div>
          </div>
          <div class="col-6">
            <div class="feature-item">
              <i class="fas fa-check-circle"></i>
              <span>Manage products</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT COMPACT IMAGE BOX -->
    <div class="compact-image-box"></div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-dismiss alerts after 4 seconds
      setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
        });
      }, 4000);
    });
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>