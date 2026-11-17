<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF9F1">
    <title>@yield('title', 'Akun Pelanggan — Geprek GLO')</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('glo-theme') || 'light'; } catch (error) { document.documentElement.dataset.theme = 'light'; }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
</head>
<body class="console-page account-page">
    <header class="console-header">
        <a class="brand-lockup" href="{{ route('home') }}"><span class="brand-mark">G</span><span>Geprek <b>GLO</b></span></a>
        <nav class="console-nav" aria-label="Navigasi akun"><a class="active" href="{{ route('account.dashboard') }}">Pesanan saya</a><a href="{{ route('menu') }}">Pesan menu</a></nav>
        <div class="console-actions">
            <span class="user-chip"><span class="avatar-dot">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span>{{ auth()->user()->name }}</span></span>
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema"><span aria-hidden="true">◐</span></button>
            <form action="{{ route('account.logout') }}" method="POST">@csrf<button class="btn btn-secondary btn-sm" type="submit">Keluar</button></form>
        </div>
    </header>
    <main class="account-main">
        @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @yield('content')
    </main>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
