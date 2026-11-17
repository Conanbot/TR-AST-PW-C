<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF9F1">
    <title>@yield('title', 'Masuk — Geprek GLO')</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('glo-theme') || 'light'; } catch (error) { document.documentElement.dataset.theme = 'light'; }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-story">
            <a class="brand-lockup" href="{{ route('home') }}">
                <span class="brand-mark">G</span>
                <span>Geprek <b>GLO</b></span>
            </a>
            <div class="auth-story-copy">
                <span class="eyebrow">Blotongan, Salatiga</span>
                <h1>Ayam geprek,<br>hari jadi seru.</h1>
                <p>Menu ayam crispy dan geprek pedas untuk teman makan di kampus maupun di rumah.</p>
            </div>
            <div class="auth-food-art" aria-hidden="true">
                <div class="art-sun"></div>
                <div class="art-plate"><span>GLO</span></div>
                <div class="art-chili">PEDAS<br>ASIK</div>
            </div>
            <p class="auth-location">Menu andalan tersedia mulai Rp3.000.</p>
        </section>
        <section class="auth-panel">
            <div class="auth-panel-top">
                <a class="auth-back" href="{{ route('home') }}">← Kembali ke beranda</a>
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema">
                    <span aria-hidden="true">◐</span><span class="theme-toggle-label">Tema</span>
                </button>
            </div>
            <div class="auth-form-wrap">
                @yield('auth-form')
            </div>
            <p class="auth-help">Butuh bantuan? Tanyakan langsung kepada tim Geprek GLO.</p>
        </section>
    </main>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
