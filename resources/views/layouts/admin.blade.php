<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FFF9F1">
    <title>@yield('title', 'Panel Admin — Geprek GLO')</title>
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
    <style>
        /* ── Admin Shell Layout ─────────────────────────── */
        .admin-shell {
            display: flex;
            align-items: stretch;
            min-height: calc(100vh - 72px);
        }

        .admin-sidebar {
            /* Fixed-width, independent scroll */
            flex: 0 0 240px;
            width: 240px;
            position: sticky;
            top: 72px;
            height: calc(100vh - 72px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            /* outer container does NOT scroll */
            border-right: 1px solid var(--border);
            background: var(--surface);
        }

        .admin-sidebar-inner {
            flex: 1 1 0;
            min-height: 0;
            /* crucial: lets flex child shrink below content height */
            overflow-y: auto;
            overflow-x: hidden;
            padding: 18px 12px 12px;
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
        }

        .admin-sidebar-inner::-webkit-scrollbar {
            width: 4px;
        }

        .admin-sidebar-inner::-webkit-scrollbar-track {
            background: transparent;
        }

        .admin-sidebar-inner::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        .admin-sidebar-bottom {
            flex: 0 0 auto;
            padding: 12px 12px 16px;
            border-top: 1px solid var(--border);
            display: grid;
            gap: 8px;
        }

        .admin-console-main {
            flex: 1 1 0;
            min-width: 0;
            padding: 28px clamp(16px, 3vw, 36px) 60px;
            overflow-x: hidden;
        }

        /* ── Nav items ─────────────────────────────────── */
        .admin-nav-groups {
            display: grid;
            gap: 20px;
        }

        .admin-nav-group {
            display: grid;
            gap: 2px;
        }

        .admin-nav-group-label {
            padding: 0 10px 6px;
            color: var(--text-dim);
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .admin-nav-item {
            display: flex;
            align-items: center;
            min-height: 38px;
            padding: 0 10px;
            border-radius: var(--radius-sm);
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.15s ease, color 0.15s ease;
            /* Ensure nothing blocks pointer events */
            pointer-events: auto;
            position: relative;
            z-index: 1;
        }

        .admin-nav-item:hover {
            background: var(--red-100);
            color: var(--primary);
        }

        html[data-theme="dark"] .admin-nav-item:hover {
            background: #49272A;
        }

        .admin-nav-item.active {
            background: var(--red-100);
            color: var(--primary);
            font-weight: 700;
            border-left: 3px solid var(--primary);
            padding-left: 7px;
        }

        html[data-theme="dark"] .admin-nav-item.active {
            background: #49272A;
            color: #FF9B95;
            border-left-color: #FF9B95;
        }

        /* ── Mobile drawer ─────────────────────────────── */
        @media (max-width: 860px) {
            .admin-shell {
                display: block;
            }

            .admin-sidebar {
                display: none;
                width: 0;
            }

            .admin-mobile-btn {
                display: inline-flex !important;
            }

            .admin-mobile-drawer {
                display: flex;
                flex-direction: column;
                position: fixed;
                z-index: 200;
                inset: 0 auto 0 0;
                width: min(300px, 85vw);
                background: var(--surface);
                border-right: 1px solid var(--border);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.18);
                transform: translateX(-105%);
                transition: transform 0.22s ease;
                overflow: hidden;
            }

            .admin-mobile-drawer.open {
                transform: translateX(0);
            }

            .admin-drawer-heading {
                flex: 0 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px;
                border-bottom: 1px solid var(--border);
            }

            .admin-mobile-drawer .admin-nav-scroll {
                flex: 1 1 0;
                min-height: 0;
                overflow-y: auto;
                padding: 16px 12px;
            }

            .admin-console-main {
                padding: 20px 14px 40px;
            }
        }

        /* ── Misc fixes ─────────────────────────────────── */
        .admin-breadcrumb {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 16px;
            color: var(--text-dim);
            font-size: 0.8rem;
        }

        .admin-breadcrumb a {
            color: var(--primary);
            font-weight: 700;
        }

        [style*="color: #FFFFFF"],
        [style*="color: #fff"] {
            color: var(--text) !important;
        }

        .btn-primary [style*="color: #FFFFFF"],
        .btn-primary [style*="color: #fff"] {
            color: #fff !important;
        }
    </style>
    @stack('styles')
</head>

<body class="console-page admin-page">
    @php
    $storeSettings = \App\Models\StoreSetting::current();
    $storeIsOpen = $storeSettings->is_open;
    @endphp

    {{-- ─── Header ─── --}}
    <header class="console-header admin-header" style="position:sticky;top:0;z-index:30;">
        <div class="admin-brand-area">
            <button type="button" id="admin-menu-toggle"
                class="theme-toggle admin-mobile-btn" style="display:none;"
                aria-label="Buka menu admin" aria-controls="admin-mobile-drawer" aria-expanded="false">
                <svg aria-hidden="true" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <a href="{{ route('admin.dashboard') }}" style="display: inline-block;">
                <img src="{{ asset('assets/img/brand/logo.png') }}" alt="Geprek GLO" style="height: 38px; width: auto; object-fit: contain;">
            </a>
            <button type="button" id="header-store-toggle-btn"
                class="store-state {{ $storeIsOpen ? 'is-open' : 'is-closed' }}"
                onclick="toggleStoreStatus()" title="Ubah status kedai">
                <span id="header-store-pulse" class="status-dot"></span>
                <span id="header-store-text">Kedai: {{ $storeIsOpen ? 'BUKA' : 'TUTUP' }}</span>
            </button>
        </div>
        <div class="console-actions">
            <a href="{{ route('home') }}" target="_blank" rel="noreferrer"
                class="btn btn-secondary btn-sm admin-site-link">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                <span>Lihat situs</span>
            </a>
            <button class="theme-toggle" type="button" data-theme-toggle
                aria-label="Ganti tema" title="Ganti tema">
                <i class="fa-solid fa-moon" data-theme-icon aria-hidden="true"></i>
                <span class="theme-toggle-label"></span>
            </button>
            <span class="user-chip">
                <img src="{{ asset('assets/img/avatar-placeholder.svg') }}"
                    alt="Avatar"
                    class="avatar-img"
                    width="34" height="34"
                    style="width:34px;height:34px;border-radius:50%;object-fit:cover;background:#e0e0e0;flex:0 0 34px;">
                <span>{{ auth()->user()->name }}</span>
            </span>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf<button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Keluar</button>
            </form>
        </div>
    </header>

    {{-- ─── Mobile Drawer ─── --}}
    <div id="admin-mobile-drawer" class="admin-mobile-drawer" aria-label="Menu admin">
        <div class="admin-drawer-heading">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-block;">
                <img src="{{ asset('assets/img/brand/logo.png') }}" alt="Geprek GLO" style="height: 38px; width: auto; object-fit: contain;">
            </a>
            <button type="button" id="admin-mobile-close" class="close-btn" aria-label="Tutup menu">×</button>
        </div>
        <div class="admin-nav-scroll">
            <x-admin-nav />
        </div>
    </div>

    {{-- ─── Shell: Sidebar + Main ─── --}}
    <div class="admin-shell">
        <aside class="admin-sidebar" aria-label="Navigasi admin">
            <div class="admin-sidebar-inner">
                <span class="eyebrow admin-sidebar-label" style="display:block;padding:0 10px 12px;font-size:0.65rem;">Kelola kedai</span>
                <x-admin-nav />
            </div>
            <div class="admin-sidebar-bottom">
                <span class="sidebar-note" style="color:var(--text-dim);font-size:0.75rem;line-height:1.5;">Panel pengelola Geprek GLO</span>
                <a href="{{ route('cashier.login') }}" class="btn btn-secondary btn-sm">Buka portal kasir</a>
            </div>
        </aside>

        <main class="admin-console-main">
            <div class="admin-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span aria-hidden="true">/</span>
                <span>{{ Str::headline(request()->segment(2) ?: 'overview') }}</span>
            </div>
            @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-error" role="alert">{{ session('error') }}</div>
            @endif
            @if($errors->any())
            <div class="alert alert-error" role="alert">
                <strong>Periksa kembali formulir:</strong>
                <ul style="margin:4px 0 0 16px;">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
            @endif
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        // ── Mobile drawer ───────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('admin-menu-toggle');
            const drawer = document.getElementById('admin-mobile-drawer');
            const close = document.getElementById('admin-mobile-close');

            if (!toggle || !drawer) return;

            const setOpen = open => {
                drawer.classList.toggle('open', open);
                if (toggle) toggle.setAttribute('aria-expanded', String(open));
                document.body.style.overflow = open ? 'hidden' : '';
            };

            toggle.addEventListener('click', () => setOpen(true));
            close.addEventListener('click', () => setOpen(false));
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') setOpen(false);
            });
            document.addEventListener('click', e => {
                if (drawer.classList.contains('open') &&
                    !drawer.contains(e.target) &&
                    !toggle.contains(e.target)) setOpen(false);
            });
        });

        // ── Store toggle ────────────────────────────────────
        async function toggleStoreStatus() {
            const button = document.getElementById('header-store-toggle-btn');
            const label = document.getElementById('header-store-text');
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                button.disabled = true;
                const resp = await fetch('{{ route("admin.store.toggle") }}', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });
                if (!resp.ok) throw new Error('Gagal memperbarui status.');
                const data = await resp.json();
                button.classList.toggle('is-open', data.is_open);
                button.classList.toggle('is-closed', !data.is_open);
                label.textContent = `Kedai: ${data.is_open ? 'BUKA' : 'TUTUP'}`;
                if (window.showToast) window.showToast(data.message, 'success');
            } catch (e) {
                if (window.showToast) window.showToast('Status kedai gagal diperbarui.', 'error');
            } finally {
                button.disabled = false;
            }
        }
    </script>
    @stack('scripts')
</body>

</html>