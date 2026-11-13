<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Katalog') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('produk.index') }}">{{ config('app.name') }}</a>
        <nav>
            <a href="{{ route('produk.index') }}" class="{{ request()->routeIs('produk.*') ? 'active' : '' }}">Katalog Produk</a>

            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('admin.ekspedisi.index') }}" class="{{ request()->routeIs('admin.ekspedisi.*') ? 'active' : '' }}">Ekspedisi</a>
                    <a href="{{ route('admin.voucher.index') }}" class="{{ request()->routeIs('admin.voucher.*') ? 'active' : '' }}">Voucher</a>
                @else
                    <a href="{{ route('checkout.index') }}" class="{{ request()->routeIs('checkout.*') ? 'active' : '' }}">Checkout</a>
                    <a href="{{ route('profil.edit') }}" class="{{ request()->routeIs('profil.*') ? 'active' : '' }}">Profil</a>
                    <a href="{{ route('alamat.index') }}" class="{{ request()->routeIs('alamat.*') ? 'active' : '' }}">Alamat</a>
                @endif
            @endauth
        </nav>

        @auth
            <span class="who">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <nav><button type="submit">Logout</button></nav>
            </form>
        @else
            <nav>
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Daftar</a>
            </nav>
        @endauth
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Periksa kembali data yang dimasukkan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
