<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account - Nanosoft Billing</title>

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
      overflow: auto;
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
      width: 180px; 
      height: 180px; 
      top: 10%; 
      left: 5%; 
      background: rgba(155, 89, 182, 0.15);
    }
    .circle2 { 
      width: 220px; 
      height: 220px; 
      bottom: 10%; 
      right: 5%; 
      animation-delay: 2s;
      background: rgba(241, 196, 15, 0.15);
    }

    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(15px); }
      100% { transform: translateY(0px); }
    }

    /* Logo Row */
    .logo-row {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 15px;
      z-index: 2;
      position: relative;
    }
    .logo img {
      width: 110px;
      margin-right: 12px;
    }
    .logo-title {
      font-size: 1.6rem;
      font-weight: 700;
      background: linear-gradient(135deg, #9b59b6, #f1c40f);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Wider wrapper */
    .wider-wrapper {
      width: 100%;
      max-width: 700px;
      display: flex;
      justify-content: center;
      z-index: 2;
      position: relative;
      margin: 0 auto;
    }

    /* Wider register box - shorter height */
    .wider-register-box {
      width: 100%;
      background: rgba(255,255,255,0.75);
      padding: 30px;
      border-radius: 18px;
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 30px rgba(155, 89, 182, 0.15);
      animation: fadeUp 0.7s ease;
      border: 1px solid rgba(255,255,255,0.3);
      max-height: 520px;
      overflow-y: auto;
    }

    /* Hide scrollbar */
    .wider-register-box::-webkit-scrollbar {
      display: none;
    }
    
    .wider-register-box {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .register-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 6px;
      text-align: center;
    }

    .register-subtitle {
      color: #7f8c8d;
      margin-bottom: 20px;
      font-size: 0.85rem;
      text-align: center;
    }

    .form-control {
      border-radius: 8px;
      padding: 10px 12px;
      border: 1.5px solid #e0e6ed;
      background: rgba(255,255,255,0.95);
      transition: all 0.3s ease;
      font-size: 0.85rem;
    }

    .form-control:focus {
      border-color: #9b59b6;
      box-shadow: 0 0 0 3px rgba(155, 89, 182, 0.1);
    }

    .form-label {
      font-weight: 500;
      color: #2c3e50;
      margin-bottom: 5px;
      font-size: 0.85rem;
    }

    .btn-register-main {
      background: linear-gradient(135deg, #9b59b6, #f1c40f);
      color: #fff;
      padding: 10px;
      border-radius: 8px;
      width: 100%;
      font-size: 0.9rem;
      font-weight: 500;
      border: none;
      box-shadow: 0 4px 15px rgba(155, 89, 182, 0.25);
      transition: all 0.3s ease;
      margin-top: 8px;
    }
    .btn-register-main:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(155, 89, 182, 0.35);
    }

    .btn-login {
      background: transparent;
      color: #9b59b6;
      padding: 8px;
      border-radius: 6px;
      width: 100%;
      font-size: 0.85rem;
      font-weight: 500;
      border: 2px solid #9b59b6;
      transition: all 0.3s ease;
      margin-top: 8px;
    }
    .btn-login:hover {
      background: rgba(155, 89, 182, 0.1);
    }

    .back-home {
      color: #7f8c8d;
      text-decoration: none;
      font-size: 0.8rem;
      transition: color 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .back-home:hover {
      color: #9b59b6;
    }

    .alert {
      border-radius: 8px;
      border: none;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      padding: 10px 12px;
      margin-bottom: 15px;
      font-size: 0.8rem;
    }

    .alert-danger {
      background: rgba(231, 76, 60, 0.1);
      color: #c0392b;
      border-left: 3px solid #e74c3c;
    }

    .alert-success {
      background: rgba(46, 204, 113, 0.1);
      color: #27ae60;
      border-left: 3px solid #2ecc71;
    }

    .register-features {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid rgba(0,0,0,0.05);
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 6px;
      color: #5d6d7e;
      font-size: 0.8rem;
    }

    .feature-item i {
      color: #9b59b6;
      font-size: 0.8rem;
    }

    .required-field::after {
      content: " *";
      color: #e74c3c;
    }

    /* Password strength indicator */
    .password-strength {
      margin-top: 4px;
    }
    
    .strength-bar {
      height: 3px;
      background: #e0e6ed;
      border-radius: 2px;
      overflow: hidden;
      margin-top: 3px;
    }
    
    .strength-fill {
      height: 100%;
      width: 0%;
      transition: width 0.3s ease;
      border-radius: 2px;
    }
    
    .strength-weak {
      background: #e74c3c;
    }
    
    .strength-medium {
      background: #f39c12;
    }
    
    .strength-strong {
      background: #27ae60;
    }

    /* Responsive styles */
    @media(max-width: 768px) {
      .wider-wrapper {
        max-width: 95%;
        padding: 0 10px;
      }
      
      .wider-register-box {
        padding: 25px;
        max-height: none;
        overflow: visible;
      }
      
      .logo-row {
        margin-bottom: 10px;
      }
      
      .logo img {
        width: 100px;
      }
      
      .logo-title {
        font-size: 1.4rem;
      }
      
      body {
        padding: 15px 10px;
        overflow-y: auto;
      }
    }

    @media(max-width: 576px) {
      body {
        padding: 10px;
        justify-content: flex-start;
        padding-top: 20px;
      }
      
      .wider-register-box {
        padding: 20px;
      }
      
      .logo img {
        width: 90px;
        margin-right: 8px;
      }
      
      .logo-title {
        font-size: 1.3rem;
      }
      
      .row {
        margin-left: -4px;
        margin-right: -4px;
      }
      
      .row > [class*="col-"] {
        padding-left: 4px;
        padding-right: 4px;
      }
      
      .register-features .row {
        margin-left: 0;
        margin-right: 0;
      }
      
      .register-features .row > [class*="col-"] {
        padding-left: 0;
        padding-right: 0;
      }
    }

    /* Compact form spacing */
    .mb-3 {
      margin-bottom: 0.8rem !important;
    }
    
    .g-3 {
      --bs-gutter-y: 0.8rem;
      --bs-gutter-x: 0.8rem;
    }
    
    .mt-3 {
      margin-top: 1rem !important;
    }
    
    .mt-2 {
      margin-top: 0.5rem !important;
    }
  </style>
</head>

<body>
  <!-- Floating background circles -->
  <div class="circle circle1"></div>
  <div class="circle circle2"></div>

  <!-- Logo Row -->
  <div class="logo-row">
    <div class="logo">
      <img src="{{ asset('assets/nanosoft logo.png') }}" alt="Nanosoft Logo">
    </div>
    <h2 class="logo-title">Nanosoft Billing</h2>
  </div>

  <!-- Wider Wrapper -->
  <div class="wider-wrapper">
    <!-- WIDER REGISTER BOX -->
    <div class="wider-register-box">
      <h2 class="register-title">
        <i class="fas fa-user-plus me-2"></i>Create Account
      </h2>
      <p class="register-subtitle">Join Nanosoft Billing to manage your services and payments.</p>

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

      <form method="POST" action="{{ route('customer.register.submit') }}" id="registerForm">
        @csrf
        
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label for="name" class="form-label required-field">Full Name</label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="padding: 0 10px;">
                  <i class="fas fa-user text-muted"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0 @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Full name" 
                       required>
              </div>
              @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="mb-3">
              <label for="email" class="form-label required-field">Email</label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="padding: 0 10px;">
                  <i class="fas fa-envelope text-muted"></i>
                </span>
                <input type="email" 
                       class="form-control border-start-0 @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="Email address" 
                       required>
              </div>
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="mb-3">
              <label for="phone" class="form-label required-field">Phone</label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="padding: 0 10px;">
                  <i class="fas fa-phone text-muted"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0 @error('phone') is-invalid @enderror" 
                       id="phone" 
                       name="phone" 
                       value="{{ old('phone') }}" 
                       placeholder="Phone number" 
                       required>
              </div>
              @error('phone')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="mb-3">
              <label for="address" class="form-label required-field">Address</label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="padding: 0 10px;">
                  <i class="fas fa-map-marker-alt text-muted"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0 @error('address') is-invalid @enderror" 
                       id="address" 
                       name="address" 
                       value="{{ old('address') }}" 
                       placeholder="Full address" 
                       required>
              </div>
              @error('address')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="mb-3">
              <label for="password" class="form-label required-field">Password</label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="padding: 0 10px;">
                  <i class="fas fa-lock text-muted"></i>
                </span>
                <input type="password" 
                       class="form-control border-start-0 @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       placeholder="Create password" 
                       required>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="padding: 0 10px;">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div class="password-strength">
                <div class="strength-bar">
                  <div class="strength-fill" id="passwordStrength"></div>
                </div>
                <small class="form-text text-muted">Min. 8 characters with letters & numbers</small>
              </div>
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="mb-3">
              <label for="password_confirmation" class="form-label required-field">Confirm Password</label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="padding: 0 10px;">
                  <i class="fas fa-lock text-muted"></i>
                </span>
                <input type="password" 
                       class="form-control border-start-0" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Confirm password" 
                       required>
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword" style="padding: 0 10px;">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-register-main">
          <i class="fas fa-user-plus me-2"></i>Create Account
        </button>

        <div class="mt-3 text-center">
          <a href="{{ route('customer.login') }}" class="btn btn-login mb-2">
            <i class="fas fa-sign-in-alt me-2"></i>Already have an account? Login
          </a>
          <div class="mt-2">
            <a href="{{ url('/') }}" class="back-home">
              <i class="fas fa-arrow-left me-1"></i>Back to Home
            </a>
          </div>
        </div>
      </form>

      <!-- Compact Features Section -->
      <div class="register-features">
        <p class="small text-muted mb-2">Benefits of creating an account:</p>
        <div class="row">
          <div class="col-6">
            <div class="feature-item">
              <i class="fas fa-check-circle"></i>
              <span>Easy bill payment</span>
            </div>
          </div>
          <div class="col-6">
            <div class="feature-item">
              <i class="fas fa-check-circle"></i>
              <span>24/7 account access</span>
            </div>
          </div>
          <div class="col-6">
            <div class="feature-item">
              <i class="fas fa-check-circle"></i>
              <span>Payment history</span>
            </div>
          </div>
          <div class="col-6">
            <div class="feature-item">
              <i class="fas fa-check-circle"></i>
              <span>Service management</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Password toggle functionality
      function setupPasswordToggle(buttonId, inputId) {
        const toggleBtn = document.getElementById(buttonId);
        const passwordInput = document.getElementById(inputId);
        
        if (toggleBtn && passwordInput) {
          toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (type === 'password') {
              icon.classList.remove('fa-eye-slash');
              icon.classList.add('fa-eye');
            } else {
              icon.classList.remove('fa-eye');
              icon.classList.add('fa-eye-slash');
            }
          });
        }
      }
      
      // Setup password toggles
      setupPasswordToggle('togglePassword', 'password');
      setupPasswordToggle('toggleConfirmPassword', 'password_confirmation');
      
      // Password strength indicator
      const passwordInput = document.getElementById('password');
      const strengthBar = document.getElementById('passwordStrength');
      
      if (passwordInput && strengthBar) {
        passwordInput.addEventListener('input', function() {
          const password = this.value;
          let strength = 0;
          
          if (password.length >= 8) strength += 25;
          if (/[A-Z]/.test(password)) strength += 25;
          if (/[a-z]/.test(password)) strength += 25;
          if (/[0-9]/.test(password)) strength += 25;
          
          strengthBar.style.width = strength + '%';
          
          if (strength < 50) {
            strengthBar.className = 'strength-fill strength-weak';
          } else if (strength < 75) {
            strengthBar.className = 'strength-fill strength-medium';
          } else {
            strengthBar.className = 'strength-fill strength-strong';
          }
        });
      }
      
      // Auto-dismiss alerts after 5 seconds
      setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
        });
      }, 5000);
    });
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>