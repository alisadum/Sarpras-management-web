<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Admin | Blast</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      min-height: 100vh;
      background: url('{{ asset('images/loginn.jpg') }}') no-repeat center center;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
      animation: fadeIn 1s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .form-box {
      background-color: rgba(255, 255, 255, 0.93);
      backdrop-filter: blur(12px);
      padding: 50px 40px;
      border-radius: 20px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
      animation: slideUp 0.8s ease forwards;
    }

    @keyframes slideUp {
      from { transform: translateY(30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .form-box h2 {
      font-size: 28px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 8px;
    }

    .form-box p {
      font-size: 14px;
      color: #4b5563;
      margin-bottom: 30px;
    }

    .form-box input {
      width: 100%;
      padding: 14px 16px;
      margin-bottom: 18px;
      border: 1px solid #d1d5db;
      border-radius: 12px;
      font-size: 15px;
      transition: all 0.3s ease;
    }

    .form-box input:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
      outline: none;
    }

    .form-box button {
      background: linear-gradient(135deg, #6366f1, #4f46e5);
      color: white;
      padding: 14px 18px;
      font-size: 16px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: 0.3s ease;
      width: 100%;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      position: relative;
      overflow: hidden;
    }

    .form-box button:hover {
      background: linear-gradient(135deg, #4f46e5, #4338ca);
      transform: translateY(-1px);
    }

    .form-box button::after {
      content: "→";
      font-size: 18px;
      transform: translateX(-5px);
      transition: transform 0.3s ease;
    }

    .form-box button:hover::after {
      transform: translateX(5px);
    }

    .error {
      background-color: #ffe0e0;
      color: #b00020;
      padding: 10px;
      margin-top: 20px;
      border-radius: 6px;
      font-size: 14px;
    }

    @media (max-width: 768px) {
      body {
        padding: 20px;
      }

      .form-box {
        margin: 0;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Login Admin</h2>
    <p>Masuk ke sistem admin</p>
    <form method="POST" action="{{ route('admin.login') }}">
      @csrf
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Sign In</button>
    </form>

    @if ($errors->any())
      <div class="error">{{ $errors->first() }}</div>
    @endif
  </div>
</body>
</html>
