<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/regis.css') }}">
    <style>
        .alert-error { background: #fee2e2; border: 1px solid #dc2626; color: #991b1b; padding: 10px 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .register-footer { margin-top: 16px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <h1>Register {{ config('app.name') }}</h1>
            <p>Silakan isi data untuk membuat akun customer baru.</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST">
            @csrf
            <div class="register-group">
                <label for="name">Nama Lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="register-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email" required>
            </div>
            <div class="register-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="Minimal 8 karakter" required>
            </div>
            <div class="register-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Masukkan ulang password" required>
            </div>
            <button type="submit">Daftar</button>
        </form>
        <p class="register-footer">
            Sudah memiliki akun? Silakan <a href="{{ route('login') }}">login</a>
        </p>
    </div>
</body>
</html>
