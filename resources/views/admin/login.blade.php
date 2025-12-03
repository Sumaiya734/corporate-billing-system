<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Nanosoft Billing</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      background: linear-gradient(135deg, #eef3ff, #d9e4ff);
      font-family: 'Poppins', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 25px;
      overflow: hidden;
    }

    /* Floating background circles */
    .circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.45);
      filter: blur(60px);
      animation: float 8s ease-in-out infinite;
    }
    .circle1 { width: 260px; height: 260px; top: -60px; left: -40px; }
    .circle2 { width: 320px; height: 320px; bottom: -90px; right: -50px; animation-delay: 2s; }

    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(25px); }
      100% { transform: translateY(0px); }
    }

    /* Logo Row */
    .logo-row {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 25px;
      z-index: 2;
    }
    .logo img {
      width: 155px;
      margin-right: 15px;
    }

    .login-wrapper {
      max-width: 1100px;
      width: 100%;
      display: flex;
      gap: 40px;
      align-items: center;
      z-index: 2;
    }

    /* Glassy login box */
    .login-box {
      flex: 0.8;
      background: rgba(255,255,255,0.55);
      padding: 40px;
      border-radius: 20px;
      backdrop-filter: blur(15px);
      box-shadow: 0 10px 35px rgba(0,0,0,0.12);
      animation: fadeUp 0.7s ease;
    }

    .image-box {
      flex: 1.2;
      height: 480px;
      border-radius: 22px;
      background: url("{{ asset('assets/image2.jpg') }}") no-repeat center center/cover;
      box-shadow: 0 15px 45px rgba(0,0,0,0.20);
      animation: fadeUp 0.9s ease;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-title {
      font-size: 1.7rem;
      font-weight: 600;
      color: #003087;
    }

    .form-control {
      border-radius: 12px;
      padding: 12px;
      border: 1px solid #c7d9ff;
      background: rgba(255,255,255,0.8);
    }

    .btn-login {
      background: #0057da;
      color: #fff;
      padding: 12px;
      border-radius: 12px;
      width: 100%;
      font-size: 1rem;
      border: none;
      box-shadow: 0 4px 22px rgba(0,102,255,0.40);
      transition: 0.3s;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 28px rgba(0,102,255,0.55);
    }

    @media(max-width: 900px) {
      .login-wrapper {
        flex-direction: column;
        gap: 30px;
      }
      .image-box {
        height: 300px;
        width: 100%;
        background: url('{{ asset("assets/image1.jpg") }}') no-repeat center center/cover;
      }
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
    <h2 class="fw-bold text-primary">Nanosoft Billing</h2>
  </div>

  <!-- Login Wrapper -->
  <div class="login-wrapper">
    <!-- LEFT LOGIN BOX -->
    <div class="login-box">
      <h2 class="login-title mb-3">Admin Login</h2>
      <p class="text-muted mb-3">Secure access to the Nanosoft Billing System</p>

      @if($errors->any())
        <div class="alert alert-danger py-2">
          <ul class="mb-0 small">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('admin.login.submit') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label fw-bold">Email Address</label>
          <input type="email" class="form-control" name="email" required value="{{ old('email') }}">
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <button class="btn btn-login mt-2">Login</button>
        <div class="mt-3 text-center">
          <a href="{{ url('/') }}" style="text-decoration:none; color:#0057da;">← Back to Home</a>
        </div>
      </form>
    </div>

    <!-- RIGHT IMAGE BOX -->
    <div class="image-box"></div>
  </div>
</body>
</html>
