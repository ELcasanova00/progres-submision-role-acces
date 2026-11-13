<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        body { margin: 0; background: #f1f4f8; font-family: Arial, sans-serif; }
        .alert { padding: 10px 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #d1fae5; border: 1px solid #10b981; color: #065f46; }
        .alert-error { background: #fee2e2; border: 1px solid #dc2626; color: #991b1b; }
        .login-card p { margin-top: 16px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <h1>Login {{ config('app.name') }}</h1>
                <p>Masuk sebagai Admin atau Customer.</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit">Login</button>

                <p>Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
                <p><a href="{{ route('produk.index') }}">Lihat katalog sebagai tamu</a></p>
            </form>
        </div>
    </div>
</body>
</html>
