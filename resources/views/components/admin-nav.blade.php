@php
    $nav = [
        [
            'label' => 'Dashboard',
            'icon' => '◈',
            'items' => [
                ['label' => 'Overview', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
            ],
        ],
        [
            'label' => 'Pesanan',
            'icon' => '◷',
            'items' => [
                ['label' => 'Semua Pesanan', 'href' => route('admin.orders.index'), 'active' => request()->routeIs('admin.orders.*')],
                ['label' => 'Pending', 'href' => route('admin.orders.index', ['status' => 'pending'])],
                ['label' => 'Diproses', 'href' => route('admin.orders.index', ['status' => 'preparing'])],
                ['label' => 'Siap', 'href' => route('admin.orders.index', ['status' => 'ready'])],
                ['label' => 'Selesai', 'href' => route('admin.orders.index', ['status' => 'completed'])],
            ],
        ],
        [
            'label' => 'Menu',
            'icon' => '☰',
            'items' => [
                ['label' => 'Produk', 'href' => route('admin.items.index'), 'active' => request()->routeIs('admin.items.*')],
                ['label' => 'Varian & Modifier', 'href' => route('admin.product-config.index'), 'active' => request()->routeIs('admin.product-config.*')],
                ['label' => 'Stok Cepat', 'href' => route('admin.stock.index'), 'active' => request()->routeIs('admin.stock.*')],
            ],
        ],
        [
            'label' => 'Dapur',
            'icon' => '♨',
            'items' => [
                ['label' => 'Kitchen Display', 'href' => route('kitchen.index'), 'active' => request()->routeIs('kitchen.*')],
                ['label' => 'Antrian Masak', 'href' => route('kitchen.index')],
            ],
        ],
        [
            'label' => 'Inventori',
            'icon' => '◫',
            'items' => [
                ['label' => 'Bahan Baku', 'href' => route('admin.inventory.index'), 'active' => request()->routeIs('admin.inventory.index')],
                ['label' => 'Resep & BOM', 'href' => route('admin.inventory.recipes'), 'active' => request()->routeIs('admin.inventory.recipes')],
            ],
        ],
        [
            'label' => 'Promosi',
            'icon' => '◈',
            'items' => [
                ['label' => 'Semua Promo', 'href' => route('admin.promos.index'), 'active' => request()->routeIs('admin.promos.*')],
                ['label' => 'Buat Promo', 'href' => route('admin.promos.create')],
            ],
        ],
        [
            'label' => 'Pelanggan',
            'icon' => '◉',
            'items' => [
                ['label' => 'Semua Pelanggan', 'href' => route('admin.customers.index'), 'active' => request()->routeIs('admin.customers.*')],
            ],
        ],
        [
            'label' => 'Pembayaran',
            'icon' => '◎',
            'items' => [
                ['label' => 'Transaksi', 'href' => route('admin.payments.index'), 'active' => request()->routeIs('admin.payments.index')],
                ['label' => 'Pengaturan Bayar', 'href' => route('admin.payments.settings'), 'active' => request()->routeIs('admin.payments.settings')],
            ],
        ],
        [
            'label' => 'Pengaturan',
            'icon' => '⚙',
            'items' => [
                ['label' => 'Info Toko', 'href' => route('admin.settings.edit'), 'active' => request()->routeIs('admin.settings.*')],
                ['label' => 'Ganti Password', 'href' => route('admin.password.edit'), 'active' => request()->routeIs('admin.password.*')],
            ],
        ],
        [
            'label' => 'Laporan',
            'icon' => '◐',
            'items' => [
                ['label' => 'Penjualan', 'href' => route('admin.reports.sales'), 'active' => request()->routeIs('admin.reports.sales')],
                ['label' => 'Performa Menu', 'href' => route('admin.reports.products'), 'active' => request()->routeIs('admin.reports.products')],
                ['label' => 'Pembayaran', 'href' => route('admin.reports.payments'), 'active' => request()->routeIs('admin.reports.payments')],
                ['label' => 'Promosi', 'href' => route('admin.reports.promotions'), 'active' => request()->routeIs('admin.reports.promotions')],
                ['label' => 'Inventori', 'href' => route('admin.reports.inventory'), 'active' => request()->routeIs('admin.reports.inventory')],
                ['label' => 'Pelanggan', 'href' => route('admin.reports.customers'), 'active' => request()->routeIs('admin.reports.customers')],
            ],
        ],
        [
            'label' => 'Pengguna',
            'icon' => '◑',
            'items' => [
                ['label' => 'Kasir', 'href' => route('admin.cashiers.index'), 'active' => request()->routeIs('admin.cashiers.*')],
            ],
        ],
    ];
@endphp

<nav class="admin-nav-groups" aria-label="Navigasi admin">
    @foreach($nav as $group)
        <div class="admin-nav-group">
            <span class="admin-nav-group-label">{{ $group['label'] }}</span>
            @foreach($group['items'] as $item)
                <a href="{{ $item['href'] }}" class="admin-nav-item {{ ($item['active'] ?? false) ? 'active' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    @endforeach
</nav>
