<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF9F1">
    <title>@yield('title', 'Akun Pelanggan — Geprek GLO')</title>
    <script>
        try {
            document.documentElement.dataset.theme = localStorage.getItem('glo-theme') || 'light';
        } catch (error) {
            document.documentElement.dataset.theme = 'light';
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}?v={{ filemtime(public_path('assets/css/tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}?v={{ filemtime(public_path('assets/css/pos.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glo.css') }}?v={{ filemtime(public_path('assets/css/glo.css')) }}">
</head>

<body class="console-page account-page">
    <header class="console-header">
        <a href="{{ route('menu') }}" style="display: inline-block;"><img src="{{ asset('assets/img/brand/logo.png') }}" alt="Geprek GLO" style="height: 38px; width: auto; object-fit: contain;"></a>
        <nav class="console-nav" aria-label="Navigasi akun">
            <a class="{{ request()->routeIs('menu') ? 'active' : '' }}" href="{{ route('menu') }}">Katalog Menu</a>
            <a class="{{ request()->routeIs('account.orders') ? 'active' : '' }}" href="{{ route('account.orders') }}">Riwayat Pesanan</a>
            <a class="{{ request()->routeIs('account.dashboard') ? 'active' : '' }}" href="{{ route('account.dashboard') }}">Akun saya</a>
        </nav>
        <div class="console-actions">
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema"><i class="fa-solid fa-moon" data-theme-icon aria-hidden="true"></i></button>
            <a href="{{ route('account.dashboard') }}" class="btn btn-secondary btn-sm account-shortcut" title="Lihat Akun &amp; Riwayat Pesanan">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                {{ Str::limit(auth()->user()->name, 16) }}
            </a>
            <form action="{{ route('account.logout') }}" method="POST">@csrf<button class="btn btn-secondary btn-sm" type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Keluar</button></form>
        </div>
    </header>
    <main class="account-main">
        @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @yield('content')
    </main>
    <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
</body>

</html>