<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF9F1">
    <title>@yield('title', 'Masuk — Geprek GLO')</title>
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
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glo.css') }}">
</head>

<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-story">
            <a href="{{ route('home') }}" style="display: inline-block;">
                <img src="{{ asset('assets/img/brand/logo.png') }}" alt="Geprek GLO" style="height: 52px; width: auto; object-fit: contain;">
            </a>
            <div class="auth-story-copy">
                <span class="eyebrow">Blotongan, Salatiga</span>
                <h1>Ayam geprek,<br>hari jadi seru.</h1>
                <p>Menu ayam crispy dan geprek pedas untuk teman makan di kampus maupun di rumah.</p>
            </div>
            <div class="auth-food-art" aria-hidden="true">
                <div class="art-sun"></div>
                <div class="art-plate">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Geprek GLO" class="art-plate-logo">
                </div>
                <div class="art-chili">PEDAS<br>ASIK</div>
            </div>
            <p class="auth-location">Menu andalan tersedia mulai Rp3.000.</p>
        </section>
        <section class="auth-panel">
            <div class="auth-panel-top">
                <a class="auth-back" href="{{ route('home') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali ke beranda</a>
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema">
                    <i class="fa-solid fa-moon" data-theme-icon aria-hidden="true"></i>
                    <span class="theme-toggle-label"></span>
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