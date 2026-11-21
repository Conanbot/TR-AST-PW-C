<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kasir — Geprek GLO')</title>
    <script>
        try {
            document.documentElement.dataset.theme = localStorage.getItem('glo-theme') || 'light';
        } catch (e) {
            document.documentElement.dataset.theme = 'light';
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glo.css') }}">
    @stack('styles')
</head>

<body class="console-page cashier-page">
    <header class="console-header" style="position:sticky;top:0;z-index:20;">
        <a href="{{ route('cashier.dashboard') }}" style="display: inline-block;">
            <img src="{{ asset('assets/img/brand/logo.png') }}" alt="Geprek GLO" style="height: 38px; width: auto; object-fit: contain;">
        </a>
        <nav class="console-nav" aria-label="Navigasi kasir">
            <a href="{{ route('cashier.dashboard') }}"
                class="{{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">
                POS Kasir
            </a>
            <a href="{{ route('cashier.payments.index') }}"
                class="{{ request()->routeIs('cashier.payments.*') ? 'active' : '' }}">
                Pembayaran
            </a>
            <a href="{{ route('menu') }}" target="_blank" rel="noreferrer">Katalog Publik</a>
        </nav>
        <div class="console-actions">
            <span class="user-chip">
                <img src="{{ asset('assets/img/avatar-placeholder.svg') }}"
                    alt="Avatar"
                    class="avatar-img"
                    width="34" height="34"
                    style="width:34px;height:34px;border-radius:50%;object-fit:cover;background:#e0e0e0;flex:0 0 34px;">
                <span>{{ auth()->user()->name }}</span>
            </span>
            <button class="theme-toggle" type="button" data-theme-toggle
                aria-label="Ganti tema" title="Ganti tema">
                <i class="fa-solid fa-moon" data-theme-icon aria-hidden="true"></i>
                <span class="theme-toggle-label"></span>
            </button>
            <form action="{{ route('cashier.logout') }}" method="POST">
                @csrf<button class="btn btn-secondary btn-sm" type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Keluar</button>
            </form>
        </div>
    </header>
    <main class="console-main">
        @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" role="alert">{{ session('error') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>