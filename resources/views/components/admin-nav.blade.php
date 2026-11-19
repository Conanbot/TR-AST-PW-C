@php
    $navigationGroups = [
        [
            'label' => 'Dashboard',
            'items' => [
                ['label' => 'Overview', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
                ['label' => 'Sales Summary', 'href' => route('admin.dashboard').'#ia-orders'],
                ['label' => 'Active Orders', 'href' => route('admin.dashboard').'#ia-orders'],
                ['label' => 'Quick Actions', 'href' => route('admin.dashboard').'#ia-orders'],
            ],
        ],
        [
            'label' => 'Orders',
            'items' => [
                ['label' => 'All Orders', 'href' => route('admin.orders.index'), 'active' => request()->routeIs('admin.orders.*')],
                ['label' => 'Pending', 'href' => route('admin.orders.index', ['status' => 'pending'])],
                ['label' => 'Confirmed', 'href' => route('admin.orders.index', ['status' => 'confirmed'])],
                ['label' => 'Preparing', 'href' => route('admin.orders.index', ['status' => 'preparing'])],
                ['label' => 'Ready', 'href' => route('admin.orders.index', ['status' => 'ready'])],
                ['label' => 'Completed', 'href' => route('admin.orders.index', ['status' => 'completed'])],
                ['label' => 'Cancelled', 'href' => route('admin.orders.index', ['status' => 'cancelled'])],
            ],
        ],
        [
            'label' => 'Menu Management',
            'items' => [
                ['label' => 'Categories', 'href' => null],
                ['label' => 'Products', 'href' => route('admin.items.index'), 'active' => request()->routeIs('admin.items.*')],
                ['label' => 'Product Variants', 'href' => route('admin.product-config.index'), 'active' => request()->routeIs('admin.product-config.*')],
                ['label' => 'Modifier Groups', 'href' => route('admin.product-config.index'), 'active' => request()->routeIs('admin.product-config.*')],
                ['label' => 'Customization Options', 'href' => route('admin.product-config.index'), 'active' => request()->routeIs('admin.product-config.*')],
            ],
        ],
        [
            'label' => 'Kitchen',
            'items' => [
                ['label' => 'Cooking Queue', 'href' => route('kitchen.index'), 'active' => request()->routeIs('kitchen.*')],
                ['label' => 'Preparing Orders', 'href' => route('kitchen.index')],
                ['label' => 'Ready Orders', 'href' => route('kitchen.index')],
                ['label' => 'Completed Orders', 'href' => route('admin.orders.index', ['status' => 'completed'])],
            ],
        ],
        [
            'label' => 'Inventory',
            'items' => [
                ['label' => 'Stock Overview', 'href' => route('admin.stock.index'), 'active' => request()->routeIs('admin.stock.*')],
                ['label' => 'Ingredients', 'href' => route('admin.inventory.index'), 'active' => request()->routeIs('admin.inventory.index')],
                ['label' => 'Stock In', 'href' => route('admin.inventory.index').'#movement'],
                ['label' => 'Stock Out', 'href' => route('admin.inventory.index').'#movement'],
                ['label' => 'Stock Adjustment', 'href' => route('admin.inventory.index').'#movement'],
                ['label' => 'Low Stock Alerts', 'href' => route('admin.inventory.index').'#low-stock'],
                ['label' => 'Recipes', 'href' => route('admin.inventory.recipes'), 'active' => request()->routeIs('admin.inventory.recipes')],
            ],
        ],
        [
            'label' => 'Purchasing',
            'items' => [
                ['label' => 'Suppliers', 'href' => route('admin.suppliers.index'), 'active' => request()->routeIs('admin.suppliers.*')],
                ['label' => 'Purchase Orders', 'href' => route('admin.purchasing.index'), 'active' => request()->routeIs('admin.purchasing.*')],
                ['label' => 'Purchase History', 'href' => route('admin.purchasing.index')],
                ['label' => 'Goods Received', 'href' => route('admin.purchasing.index')],
            ],
        ],
        [
            'label' => 'Promotions',
            'items' => [
                ['label' => 'Active Promotions', 'href' => route('admin.promos.index'), 'active' => request()->routeIs('admin.promos.*')],
                ['label' => 'Create Promotion', 'href' => route('admin.promos.create')],
                ['label' => 'Discount Rules', 'href' => null],
                ['label' => 'Promotion History', 'href' => null],
            ],
        ],
        [
            'label' => 'Customers & Reviews',
            'items' => [
                ['label' => 'Customers', 'href' => null],
                ['label' => 'Customer Feedback', 'href' => null],
                ['label' => 'Google Reviews', 'href' => null],
            ],
        ],
        [
            'label' => 'Payments',
            'items' => [
                ['label' => 'Payment Overview', 'href' => route('admin.payments.index'), 'active' => request()->routeIs('admin.payments.index')],
                ['label' => 'Payment Transactions', 'href' => route('admin.payments.index')],
                ['label' => 'Pending QRIS Verification', 'href' => route('admin.payments.index', ['status' => 'pending_verification'])],
                ['label' => 'Payment History', 'href' => route('admin.payments.index')],
                ['label' => 'Payment Settings', 'href' => route('admin.payments.settings'), 'active' => request()->routeIs('admin.payments.settings')],
            ],
        ],
        [
            'label' => 'Store Settings',
            'items' => [
                ['label' => 'Store Information', 'href' => route('admin.settings.edit'), 'active' => request()->routeIs('admin.settings.*')],
                ['label' => 'Store Status', 'href' => route('admin.dashboard').'#store-status'],
                ['label' => 'Operating Hours', 'href' => route('admin.settings.edit')],
                ['label' => 'Store Location', 'href' => route('admin.settings.edit')],
                ['label' => 'Delivery Radius', 'href' => route('admin.settings.edit')],
                ['label' => 'Payment Settings', 'href' => route('admin.payments.settings')],
            ],
        ],
        [
            'label' => 'Reports',
            'items' => [
                ['label' => 'Sales Reports', 'href' => null],
                ['label' => 'Order Reports', 'href' => null],
                ['label' => 'Product Performance', 'href' => null],
                ['label' => 'Inventory Reports', 'href' => null],
                ['label' => 'Purchasing Reports', 'href' => null],
            ],
        ],
        [
            'label' => 'User Management',
            'items' => [
                ['label' => 'Administrators', 'href' => null],
                ['label' => 'Cashiers', 'href' => route('admin.cashiers.index'), 'active' => request()->routeIs('admin.cashiers.*')],
                ['label' => 'Kitchen Staff', 'href' => null],
                ['label' => 'Roles & Permissions', 'href' => null],
            ],
        ],
    ];
@endphp

<nav class="admin-nav-groups" aria-label="Navigasi admin">
    @foreach($navigationGroups as $group)
        <div class="admin-nav-group">
            <span class="admin-nav-group-label">{{ $group['label'] }}</span>
            @foreach($group['items'] as $item)
                @if($item['href'])
                    <a href="{{ $item['href'] }}" class="admin-nav-item {{ ($item['active'] ?? false) ? 'active' : '' }}">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="admin-nav-item is-planned" aria-disabled="true" title="Fitur direncanakan untuk fase berikutnya">
                        {{ $item['label'] }}
                        <small>Segera</small>
                    </span>
                @endif
            @endforeach
        </div>
    @endforeach
</nav>
