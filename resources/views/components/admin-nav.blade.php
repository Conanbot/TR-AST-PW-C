@php
    $r = request();

    $nav = [
        [
            'label' => 'Overview',
            'items' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'active' => $r->routeIs('admin.dashboard')],
                [
                    'label'  => 'Pesanan',
                    'href'   => route('admin.orders.index'),
                    // active on the list AND on any order detail/show page
                    'active' => $r->routeIs('admin.orders.index') || $r->routeIs('admin.orders.show'),
                ],
            ],
        ],
        [
            'label' => 'Menu Management',
            'items' => [
                ['label' => 'Produk',           'href' => route('admin.items.index'),          'active' => $r->routeIs('admin.items.*')],
                ['label' => 'Stok Cepat',       'href' => route('admin.stock.index'),          'active' => $r->routeIs('admin.stock.*')],
                ['label' => 'Varian & Modifier','href' => route('admin.product-config.index'), 'active' => $r->routeIs('admin.product-config.*')],
            ],
        ],
        [
            'label' => 'Operasional',
            'items' => [
                ['label' => 'Kitchen',    'href' => route('kitchen.index'),          'active' => $r->routeIs('kitchen.*')],
                ['label' => 'Promosi',    'href' => route('admin.promos.index'),     'active' => $r->routeIs('admin.promos.*')],
                ['label' => 'Pelanggan',  'href' => route('admin.customers.index'),  'active' => $r->routeIs('admin.customers.*')],
            ],
        ],
        [
            'label' => 'Pengaturan',
            'items' => [
                ['label' => 'Info Toko',       'href' => route('admin.settings.edit'),  'active' => $r->routeIs('admin.settings.*')],
                ['label' => 'Ganti Password',  'href' => route('admin.password.edit'),  'active' => $r->routeIs('admin.password.*')],
            ],
        ],
        [
            'label' => 'Laporan',
            'items' => [
                ['label' => 'Penjualan',      'href' => route('admin.reports.sales'),       'active' => $r->routeIs('admin.reports.sales')],
                ['label' => 'Performa Menu',  'href' => route('admin.reports.products'),    'active' => $r->routeIs('admin.reports.products')],
                ['label' => 'Pembayaran',     'href' => route('admin.reports.payments'),    'active' => $r->routeIs('admin.reports.payments')],
                ['label' => 'Promosi',        'href' => route('admin.reports.promotions'),  'active' => $r->routeIs('admin.reports.promotions')],
                ['label' => 'Pelanggan',      'href' => route('admin.reports.customers'),   'active' => $r->routeIs('admin.reports.customers')],
            ],
        ],
        [
            'label' => 'User Management',
            'items' => [
                ['label' => 'Kasir', 'href' => route('admin.cashiers.index'), 'active' => $r->routeIs('admin.cashiers.*')],
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
