@php
    $r = request(); // current request for route matching

    $nav = [
        [
            'label' => 'Dashboard',
            'items' => [
                [
                    'label'  => 'Overview',
                    'href'   => route('admin.dashboard'),
                    'active' => $r->routeIs('admin.dashboard'),
                ],
            ],
        ],
        [
            'label' => 'Pesanan',
            'items' => [
                [
                    'label'  => 'Semua Pesanan',
                    'href'   => route('admin.orders.index'),
                    'active' => $r->routeIs('admin.orders.index') && !$r->filled('status'),
                ],
                [
                    'label'  => 'Pending',
                    'href'   => route('admin.orders.index', ['status' => 'pending']),
                    'active' => $r->routeIs('admin.orders.index') && $r->get('status') === 'pending',
                ],
                [
                    'label'  => 'Preparing',
                    'href'   => route('admin.orders.index', ['status' => 'preparing']),
                    'active' => $r->routeIs('admin.orders.index') && $r->get('status') === 'preparing',
                ],
                [
                    'label'  => 'Detail Pesanan',
                    'href'   => route('admin.orders.index'),
                    'active' => $r->routeIs('admin.orders.show'),
                ],
            ],
        ],
        [
            'label' => 'Menu',
            'items' => [
                [
                    'label'  => 'Produk',
                    'href'   => route('admin.items.index'),
                    'active' => $r->routeIs('admin.items.*'),
                ],
                [
                    'label'  => 'Varian & Modifier',
                    'href'   => route('admin.product-config.index'),
                    'active' => $r->routeIs('admin.product-config.*'),
                ],
                [
                    'label'  => 'Stok Cepat',
                    'href'   => route('admin.stock.index'),
                    'active' => $r->routeIs('admin.stock.*'),
                ],
            ],
        ],
        [
            'label' => 'Dapur',
            'items' => [
                [
                    'label'  => 'Kitchen Display',
                    'href'   => route('kitchen.index'),
                    'active' => $r->routeIs('kitchen.*'),
                ],
            ],
        ],
        [
            'label' => 'Inventori',
            'items' => [
                [
                    'label'  => 'Bahan Baku',
                    'href'   => route('admin.inventory.index'),
                    'active' => $r->routeIs('admin.inventory.index'),
                ],
                [
                    'label'  => 'Resep & BOM',
                    'href'   => route('admin.inventory.recipes'),
                    'active' => $r->routeIs('admin.inventory.recipes'),
                ],
            ],
        ],
        [
            'label' => 'Promosi',
            'items' => [
                [
                    'label'  => 'Semua Promo',
                    'href'   => route('admin.promos.index'),
                    'active' => $r->routeIs('admin.promos.index') || $r->routeIs('admin.promos.show'),
                ],
                [
                    'label'  => 'Buat Promo',
                    'href'   => route('admin.promos.create'),
                    'active' => $r->routeIs('admin.promos.create') || $r->routeIs('admin.promos.edit'),
                ],
            ],
        ],
        [
            'label' => 'Pelanggan',
            'items' => [
                [
                    'label'  => 'Semua Pelanggan',
                    'href'   => route('admin.customers.index'),
                    'active' => $r->routeIs('admin.customers.*'),
                ],
            ],
        ],
        [
            'label' => 'Pengaturan',
            'items' => [
                [
                    'label'  => 'Info Toko',
                    'href'   => route('admin.settings.edit'),
                    'active' => $r->routeIs('admin.settings.*'),
                ],
                [
                    'label'  => 'Ganti Password',
                    'href'   => route('admin.password.edit'),
                    'active' => $r->routeIs('admin.password.*'),
                ],
            ],
        ],
        [
            'label' => 'Laporan',
            'items' => [
                [
                    'label'  => 'Penjualan',
                    'href'   => route('admin.reports.sales'),
                    'active' => $r->routeIs('admin.reports.sales'),
                ],
                [
                    'label'  => 'Performa Menu',
                    'href'   => route('admin.reports.products'),
                    'active' => $r->routeIs('admin.reports.products'),
                ],
                [
                    'label'  => 'Pembayaran',
                    'href'   => route('admin.reports.payments'),
                    'active' => $r->routeIs('admin.reports.payments'),
                ],
                [
                    'label'  => 'Promosi',
                    'href'   => route('admin.reports.promotions'),
                    'active' => $r->routeIs('admin.reports.promotions'),
                ],
                [
                    'label'  => 'Inventori',
                    'href'   => route('admin.reports.inventory'),
                    'active' => $r->routeIs('admin.reports.inventory'),
                ],
                [
                    'label'  => 'Pelanggan',
                    'href'   => route('admin.reports.customers'),
                    'active' => $r->routeIs('admin.reports.customers'),
                ],
            ],
        ],
        [
            'label' => 'Pengguna',
            'items' => [
                [
                    'label'  => 'Kasir',
                    'href'   => route('admin.cashiers.index'),
                    'active' => $r->routeIs('admin.cashiers.*'),
                ],
            ],
        ],
    ];
@endphp

<nav class="admin-nav-groups" aria-label="Navigasi admin">
    @foreach($nav as $group)
        <div class="admin-nav-group">
            <span class="admin-nav-group-label">{{ $group['label'] }}</span>
            @foreach($group['items'] as $item)
                <a href="{{ $item['href'] }}"
                   class="admin-nav-item {{ $item['active'] ? 'active' : '' }}"
                   @if($item['active']) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    @endforeach
</nav>
