<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SubWFour</title>
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <style>
    /* Override login form styling to match design */

    .login-box {
      background: #2a2a2a !important;
      border: 1px solid #3a3a3a;
      padding: 50px 60px;
    }

    .login-header {
      width: 100%;
      margin-bottom: 30px;
    }

    .brand-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 20px;
    }

    .brand-line {
      flex: 1;
      height: 3px;
      background: linear-gradient(90deg, transparent, #ef4444, transparent);
      position: relative;
    }

    .brand-line::before {
      content: '';
      position: absolute;
      top: -4px;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, #dc2626, transparent);
    }

    .login-header h1 {
      color: #ef4444 !important;
      font-size: 32px;
      font-weight: 700;
      margin: 0;
      white-space: nowrap;
      letter-spacing: 2px;
    }

    .login-header p {
      color: #d1d5db !important;
      font-size: 16px;
      margin: 0;
      text-align: center;
      font-weight: 300;
    }

    label {
      color: #e5e7eb !important;
      font-size: 16px;
      font-weight: 400;
      margin-bottom: 8px;
    }

    input[type="text"],
    input[type="password"] {
      background: #1f1f1f !important;
      color: #ffffff !important;
      border: 1px solid #404040 !important;
      border-radius: 8px;
      padding: 14px 16px;
      font-size: 15px;
      transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      outline: none;
      border-color: #606060 !important;
    }

    button[type="submit"] {
      background: #16a34a !important;
      color: #ffffff !important;
      border: none !important;
      border-radius: 8px;
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      margin-top: 10px;
      transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
      background: #15803d !important;
    }

    .error-messages {
      background: #7f1d1d;
      border: 1px solid #991b1b;
      border-radius: 6px;
      padding: 12px 16px;
      margin-top: 15px;
    }

    .error-messages ul {
      margin: 0;
      padding-left: 20px;
      color: #fca5a5;
      font-size: 13px;
    }

    .login-footer {
      margin-top: 25px;
    }
  </style>
</head>

<body>
  <div class="login-wrapper">
    <div class="login-box">
      <div class="login-header">
        <div class="brand-container">
          <div class="brand-line"></div>
          <h1>SubWFour</h1>
          <div class="brand-line"></div>
        </div>
        <p>To hear is to believe.</p>
      </div>

      <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <label for="email">Login:</label>
        <input type="text" id="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
      </form>

      @if ($errors->any())
        <div class="error-messages">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="login-footer">
        <img src="{{ asset('images/morel.png') }}" alt="Morel Logo">
      </div>
    </div>
  </div>
</body>

</html>