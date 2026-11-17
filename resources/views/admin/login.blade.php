<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — Geprek GLO Salatiga</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: var(--space-md); background: radial-gradient(circle at 50% 30%, rgba(215, 38, 61, 0.15), transparent 70%), var(--bg);">

    <div class="card" style="width: 100%; max-width: 420px; padding: var(--space-xl); box-shadow: var(--shadow-lg);">
        <div class="text-center" style="margin-bottom: var(--space-lg);">
            <span style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: #FFFFFF; letter-spacing: -0.5px;">
                GEPREK <span style="color: var(--primary);">GLO</span>
            </span>
            <h2 style="font-size: 1.25rem; margin-top: 6px;">Portal Pengelola Kedai</h2>
            <p class="text-xs text-muted" style="margin-top: 4px;">
                Silakan masuk untuk mengelola status kedai, stok menu, harga, dan promo.
            </p>
        </div>

        @if(session('info'))
            <div style="background: rgba(52, 152, 219, 0.15); border: 1px solid rgba(52, 152, 219, 0.4); border-radius: var(--radius-md); padding: 10px 14px; margin-bottom: 16px; font-size: 0.85rem; color: #70B8FF;">
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: rgba(215, 38, 61, 0.15); border: 1px solid rgba(215, 38, 61, 0.4); border-radius: var(--radius-md); padding: 10px 14px; margin-bottom: 16px; font-size: 0.85rem; color: #FF7B89;">
                @foreach($errors->all() as $err)
                    <div>✕ {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" value="{{ old('username') }}" placeholder="Masukkan username admin" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>

            <div class="form-group flex items-center justify-between" style="margin-top: -4px;">
                <label class="form-check" style="font-size: 0.85rem; color: var(--text-muted);">
                    <input type="checkbox" name="remember">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                Masuk ke Panel Admin
            </button>
        </form>

        <div class="text-center" style="margin-top: var(--space-lg); padding-top: var(--space-md); border-top: 1px solid var(--border);">
            <a href="{{ route('home') }}" class="text-xs text-muted hover-white">
                &larr; Kembali ke Halaman Utama Website
            </a>
            <div class="text-xs text-dim" style="margin-top: 10px;">
                Akun Demo Pengujian: <code>admin</code> / <code>admin123</code>
            </div>
        </div>
    </div>

</body>
</html>
