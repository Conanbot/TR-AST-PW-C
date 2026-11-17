<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FFF9F1">
    <title>@yield('title', 'Panel Admin — Geprek GLO')</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('glo-theme') || 'light'; } catch (error) { document.documentElement.dataset.theme = 'light'; }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
    @stack('styles')
</head>
<body class="console-page admin-page">
    @php
        $storeSettings = \App\Models\StoreSetting::current();
        $storeIsOpen = $storeSettings->is_open;
    @endphp
    <header class="console-header admin-header">
        <div class="admin-brand-area">
            <button type="button" id="admin-menu-toggle" class="theme-toggle admin-mobile-btn" aria-label="Buka menu admin" aria-controls="admin-mobile-drawer" aria-expanded="false">
                <svg aria-hidden="true" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a class="brand-lockup" href="{{ route('admin.dashboard') }}"><span class="brand-mark">G</span><span>Geprek <b>GLO</b></span></a>
            <button type="button" id="header-store-toggle-btn" class="store-state {{ $storeIsOpen ? 'is-open' : 'is-closed' }}" onclick="toggleStoreStatus()" title="Ubah status kedai">
                <span id="header-store-pulse" class="status-dot"></span>
                <span id="header-store-text">Kedai: {{ $storeIsOpen ? 'BUKA' : 'TUTUP' }}</span>
            </button>
        </div>
        <div class="console-actions">
            <a href="{{ route('home') }}" target="_blank" rel="noreferrer" class="btn btn-secondary btn-sm admin-site-link">Lihat situs</a>
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema"><span aria-hidden="true">◐</span><span class="theme-toggle-label">Tema</span></button>
            <span class="user-chip"><span class="avatar-dot">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span>{{ auth()->user()->name }}</span></span>
            <form action="{{ route('admin.logout') }}" method="POST">@csrf<button type="submit" class="btn btn-secondary btn-sm">Keluar</button></form>
        </div>
    </header>

    <div id="admin-mobile-drawer" class="mobile-nav admin-mobile-drawer" aria-label="Menu admin">
        <div class="admin-drawer-heading">
            <a class="brand-lockup" href="{{ route('admin.dashboard') }}"><span class="brand-mark">G</span><span>Menu <b>admin</b></span></a>
            <button type="button" id="admin-mobile-close" class="close-btn" aria-label="Tutup menu">×</button>
        </div>
        <nav class="admin-nav-links" aria-label="Navigasi admin">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Ikhtisar</a>
            <a href="{{ route('admin.stock.index') }}" class="admin-nav-item {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">Stok menu</a>
            <a href="{{ route('admin.items.index') }}" class="admin-nav-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">Menu</a>
            <a href="{{ route('admin.promos.index') }}" class="admin-nav-item {{ request()->routeIs('admin.promos.*') ? 'active' : '' }}">Promo</a>
            <a href="{{ route('admin.cashiers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.cashiers.*') ? 'active' : '' }}">Akun kasir</a>
            <a href="{{ route('admin.settings.edit') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Pengaturan kedai</a>
            <a href="{{ route('admin.password.edit') }}" class="admin-nav-item {{ request()->routeIs('admin.password.*') ? 'active' : '' }}">Ubah password</a>
        </nav>
    </div>

    <div class="admin-shell">
        <aside class="admin-sidebar">
            <span class="eyebrow admin-sidebar-label">Kelola kedai</span>
            <nav class="admin-nav-links" aria-label="Navigasi admin">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="admin-nav-icon" aria-hidden="true">⌂</span>Ikhtisar</a>
                <a href="{{ route('admin.stock.index') }}" class="admin-nav-item {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}"><span class="admin-nav-icon" aria-hidden="true">◉</span>Stok menu</a>
                <a href="{{ route('admin.items.index') }}" class="admin-nav-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}"><span class="admin-nav-icon" aria-hidden="true">▦</span>Menu</a>
                <a href="{{ route('admin.promos.index') }}" class="admin-nav-item {{ request()->routeIs('admin.promos.*') ? 'active' : '' }}"><span class="admin-nav-icon" aria-hidden="true">✦</span>Promo</a>
                <a href="{{ route('admin.cashiers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.cashiers.*') ? 'active' : '' }}"><span class="admin-nav-icon" aria-hidden="true">♙</span>Akun kasir</a>
                <a href="{{ route('admin.settings.edit') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><span class="admin-nav-icon" aria-hidden="true">⚙</span>Pengaturan kedai</a>
                <a href="{{ route('admin.password.edit') }}" class="admin-nav-item {{ request()->routeIs('admin.password.*') ? 'active' : '' }}"><span class="admin-nav-icon" aria-hidden="true">🔒</span>Ubah password</a>
            </nav>
            <div class="admin-sidebar-bottom">
                <span class="sidebar-note">Panel pengelola Geprek GLO</span>
                <a href="{{ route('cashier.login') }}" class="btn btn-secondary">Buka portal kasir</a>
            </div>
        </aside>

        <main class="admin-console-main">
            @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-error" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-error" role="alert"><strong>Periksa kembali formulir:</strong><ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('admin-menu-toggle');
        const drawer = document.getElementById('admin-mobile-drawer');
        const close = document.getElementById('admin-mobile-close');
        const setOpen = open => {
            drawer.classList.toggle('open', open);
            toggle.setAttribute('aria-expanded', String(open));
            document.body.style.overflow = open ? 'hidden' : '';
        };
        toggle.addEventListener('click', () => setOpen(true));
        close.addEventListener('click', () => setOpen(false));
        document.addEventListener('keydown', event => { if (event.key === 'Escape') setOpen(false); });
        document.addEventListener('click', event => {
            if (drawer.classList.contains('open') && !drawer.contains(event.target) && !toggle.contains(event.target)) {
                setOpen(false);
            }
        });
    });

    async function toggleStoreStatus() {
        const button = document.getElementById('header-store-toggle-btn');
        const pulse = document.getElementById('header-store-pulse');
        const label = document.getElementById('header-store-text');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        try {
            button.disabled = true;
            const response = await fetch('{{ route("admin.store.toggle") }}', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Status kedai gagal diperbarui.');
            const data = await response.json();
            button.classList.toggle('is-open', data.is_open);
            button.classList.toggle('is-closed', !data.is_open);
            label.textContent = `Kedai: ${data.is_open ? 'BUKA' : 'TUTUP'}`;
            window.showToast(data.message, 'success');
        } catch (error) {
            console.error(error);
            window.showToast('Status kedai gagal diperbarui. Coba lagi.', 'error');
        } finally {
            button.disabled = false;
        }
    }
    </script>
    @stack('scripts')
</body>
</html>
