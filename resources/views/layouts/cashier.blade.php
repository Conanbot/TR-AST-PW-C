<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kasir — Geprek GLO')</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('glo-theme') || 'light'; } catch (error) { document.documentElement.dataset.theme = 'light'; }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glo.css') }}">
</head>
<body class="console-page cashier-page">
    <header class="console-header">
        <a class="brand-lockup" href="{{ route('cashier.dashboard') }}"><span class="brand-mark">G</span><span>Geprek <b>GLO</b></span></a>
        <nav class="console-nav" aria-label="Navigasi kasir"><a class="active" href="{{ route('cashier.dashboard') }}">Pesanan &amp; meja</a><a href="{{ route('menu') }}" target="_blank">Katalog publik</a></nav>
        <div class="console-actions">
            <span class="user-chip"><span class="avatar-dot">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span>{{ auth()->user()->name }}</span></span>
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema"><span aria-hidden="true">◐</span></button>
            <form action="{{ route('cashier.logout') }}" method="POST">@csrf<button class="btn btn-secondary btn-sm" type="submit">Keluar</button></form>
        </div>
    </header>
    <main class="console-main">
        @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-error" role="alert">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
