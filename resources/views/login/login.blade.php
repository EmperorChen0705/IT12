<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SubWFour</title>
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <style>
    /* Login form styling */
    .login-box {
      background: #1a1a1a !important;
    }

    .login-header h1,
    .login-header p,
    label {
      color: #ef3535 !important;
    }

    input {
      background: #2a2a2a !important;
      color: #ffffff !important;
      border: 1px solid #ef3535 !important;
    }

    button[type="submit"] {
      background: #ef3535 !important;
      color: #ffffff !important;
      border: 1px solid #d32f2f !important;
    }

    button[type="submit"]:hover {
      background: #d32f2f !important;
    }
  </style>
</head>

<body>
  <div class="login-wrapper">
    <div class="login-box">
      <div class="login-header">
        <h1>SUBWFOUR</h1>
        <p>To hear is to believe.</p>
      </div>

      <form action="{{ route('login.post') }}" method="POST">
        @csrf
        <label for="name">Login:</label>
        <input type="text" id="email" name="email" value="{{ old('name') }}" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
      </form>

      @if ($errors->any())
        <div style="color: #1a1a1a; font-size: 13px; margin-top: 10px;">
          <ul style="padding-left: 20px;">
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