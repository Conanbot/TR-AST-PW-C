<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin — Geprek GLO Salatiga')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Design Tokens & CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <style>
        .admin-sidebar {
            width: 250px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            min-height: calc(100vh - var(--header-height));
            padding: var(--space-md);
            display: none;
            flex-direction: column;
            gap: 6px;
        }
        @media (min-width: 900px) {
            .admin-sidebar { display: flex; }
            .admin-mobile-btn { display: none !important; }
        }
        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            color: var(--text-muted);
            font-size: 0.925rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .admin-nav-item:hover, .admin-nav-item.active {
            background: var(--surface-card);
            color: #FFFFFF;
        }
        .admin-nav-item.active {
            color: var(--primary);
            font-weight: 600;
            border-left: 3px solid var(--primary);
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .admin-table th {
            text-align: left;
            padding: 12px 14px;
            background: var(--surface);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
        }
        .admin-table td {
            padding: 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .admin-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Admin Top Bar -->
    <header class="site-header">
        <div class="container flex items-center justify-between">
            <div class="flex items-center gap-md">
                <button type="button" id="admin-menu-toggle" class="btn btn-secondary btn-sm admin-mobile-btn" aria-label="Buka Menu Admin">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                    </svg>
                </button>

                <a href="{{ route('admin.dashboard') }}" style="text-decoration: none;">
                    <span style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800; color: #FFFFFF;">
                        ADMIN <span style="color: var(--primary);">GLO</span>
                    </span>
                </a>

                <!-- One-Tap Store Status Toggle (FR-18, FR-19, US-11) -->
                @php
                    $storeSettings = \App\Models\StoreSetting::current();
                    $storeIsOpen = $storeSettings->is_open ?? true;
                @endphp
                <button type="button" id="header-store-toggle-btn" 
                        class="btn btn-sm {{ $storeIsOpen ? 'badge-open' : 'badge-closed' }}" 
                        onclick="toggleStoreStatus()" 
                        style="border-radius: var(--radius-pill); padding: 4px 12px; font-size: 0.8rem;"
                        title="Klik untuk mengubah status Buka / Tutup">
                    <span id="header-store-pulse" class="pulse-dot {{ $storeIsOpen ? '' : 'closed' }}"></span>
                    <span id="header-store-text">Kedai: {{ $storeIsOpen ? 'BUKA' : 'TUTUP' }}</span>
                </button>
            </div>

            <div class="flex items-center gap-sm">
                <a href="{{ route('home') }}" target="_blank" class="btn btn-secondary btn-sm" title="Lihat Website Publik">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span style="display: none; @media (min-width: 640px) { display: inline; }">Lihat Web</span>
                </a>

                <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: #FF5A5F;" title="Keluar dari Panel Admin">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Admin Mobile Drawer Nav -->
    <div id="admin-mobile-drawer" class="mobile-nav">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <span style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; color: #FFFFFF;">
                MENU <span style="color: var(--primary);">ADMIN</span>
            </span>
            <button type="button" id="admin-mobile-close" class="close-btn" aria-label="Tutup Menu">&times;</button>
        </div>
        <div class="flex flex-col gap-sm">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Dashboard</a>
            <a href="{{ route('admin.stock.index') }}" class="admin-nav-item {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">⚡ Kelola Stok Cepat</a>
            <a href="{{ route('admin.items.index') }}" class="admin-nav-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">🍗 Manajemen Menu</a>
            <a href="{{ route('admin.promos.index') }}" class="admin-nav-item {{ request()->routeIs('admin.promos.*') ? 'active' : '' }}">🎉 Banner Promo</a>
            <a href="{{ route('admin.settings.edit') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">⚙️ Pengaturan Kedai</a>
        </div>
        <div style="margin-top: auto; padding-top: 18px; border-top: 1px solid var(--border);">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary" style="width: 100%; color: #FF5A5F;">
                    🚪 Keluar (Logout)
                </button>
            </form>
        </div>
    </div>

    <!-- Main Admin Container -->
    <div style="display: flex; flex: 1 0 auto;">
        <!-- Desktop Sidebar -->
        <aside class="admin-sidebar">
            <div class="text-xs text-dim" style="padding: 6px 14px; text-transform: uppercase; font-weight: 600;">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>📊</span> Dashboard
            </a>
            <a href="{{ route('admin.stock.index') }}" class="admin-nav-item {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">
                <span>⚡</span> Kelola Stok Cepat
            </a>
            <a href="{{ route('admin.items.index') }}" class="admin-nav-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">
                <span>🍗</span> Manajemen Menu
            </a>
            <a href="{{ route('admin.promos.index') }}" class="admin-nav-item {{ request()->routeIs('admin.promos.*') ? 'active' : '' }}">
                <span>🎉</span> Banner Promo
            </a>
            
            <div class="text-xs text-dim" style="padding: 14px 14px 6px 14px; text-transform: uppercase; font-weight: 600;">Sistem</div>
            <a href="{{ route('admin.settings.edit') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <span>⚙️</span> Pengaturan Kedai
            </a>
            <a href="{{ route('home') }}" target="_blank" class="admin-nav-item">
                <span>🌐</span> Kunjungi Situs
            </a>
        </aside>

        <!-- Main Content Area -->
        <main style="flex: 1; padding: var(--space-lg); max-width: 1200px; width: 100%; margin: 0 auto;">
            <!-- Flash Messages -->
            @if(session('success'))
                <div style="background: rgba(46, 204, 113, 0.15); border: 1px solid rgba(46, 204, 113, 0.4); border-radius: var(--radius-md); padding: 12px 18px; margin-bottom: 20px; color: var(--ok); display: flex; align-items: center; justify-content: space-between;">
                    <span>✓ {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="color: var(--ok);">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div style="background: rgba(215, 38, 61, 0.15); border: 1px solid rgba(215, 38, 61, 0.4); border-radius: var(--radius-md); padding: 12px 18px; margin-bottom: 20px; color: #FF7B89; display: flex; align-items: center; justify-content: space-between;">
                    <span>✕ {{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="color: #FF7B89;">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div style="background: rgba(215, 38, 61, 0.15); border: 1px solid rgba(215, 38, 61, 0.4); border-radius: var(--radius-md); padding: 12px 18px; margin-bottom: 20px; color: #FF7B89;">
                    <strong style="display: block; margin-bottom: 6px;">Harap periksa kesalahan berikut:</strong>
                    <ul style="padding-left: 20px; font-size: 0.875rem;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Core Scripts -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        // Admin Mobile Drawer logic
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('admin-menu-toggle');
            const drawer = document.getElementById('admin-mobile-drawer');
            const closeBtn = document.getElementById('admin-mobile-close');

            if (toggleBtn && drawer) {
                toggleBtn.addEventListener('click', () => {
                    drawer.classList.add('open');
                    document.body.style.overflow = 'hidden';
                });
            }
            if (closeBtn && drawer) {
                closeBtn.addEventListener('click', () => {
                    drawer.classList.remove('open');
                    document.body.style.overflow = '';
                });
            }
        });

        // Fast One-Tap Store Status Toggle (FR-18, FR-19, US-11)
        async function toggleStoreStatus() {
            const btn = document.getElementById('header-store-toggle-btn');
            const pulse = document.getElementById('header-store-pulse');
            const text = document.getElementById('header-store-text');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                btn.disabled = true;
                const response = await fetch('{{ route("admin.store.toggle") }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Gagal mengubah status kedai');

                const data = await response.json();
                if (data.success) {
                    if (data.is_open) {
                        btn.className = 'btn btn-sm badge-open';
                        pulse.className = 'pulse-dot';
                        text.textContent = 'Kedai: BUKA';
                    } else {
                        btn.className = 'btn btn-sm badge-closed';
                        pulse.className = 'pulse-dot closed';
                        text.textContent = 'Kedai: TUTUP';
                    }
                    window.showToast(data.message, 'success');
                }
            } catch (err) {
                console.error(err);
                window.showToast('Gagal mengubah status kedai. Silakan coba lagi.', 'error');
            } finally {
                btn.disabled = false;
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
