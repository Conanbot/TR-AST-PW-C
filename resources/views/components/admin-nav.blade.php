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
                ['label' => 'All Orders', 'href' => route('admin.dashboard').'#ia-orders'],
                ['label' => 'Pending', 'href' => route('admin.dashboard').'#ia-orders'],
                ['label' => 'Confirmed', 'href' => null],
                ['label' => 'Preparing', 'href' => route('admin.dashboard').'#ia-kitchen'],
                ['label' => 'Ready', 'href' => route('admin.dashboard').'#ia-kitchen'],
                ['label' => 'Completed', 'href' => route('admin.dashboard').'#ia-kitchen'],
                ['label' => 'Cancelled', 'href' => null],
            ],
        ],
        [
            'label' => 'Menu Management',
            'items' => [
                ['label' => 'Categories', 'href' => null],
                ['label' => 'Products', 'href' => route('admin.items.index'), 'active' => request()->routeIs('admin.items.*')],
                ['label' => 'Product Variants', 'href' => null],
                ['label' => 'Modifier Groups', 'href' => null],
                ['label' => 'Customization Options', 'href' => route('admin.stock.index'), 'active' => request()->routeIs('admin.stock.*')],
            ],
        ],
        [
            'label' => 'Kitchen',
            'items' => [
                ['label' => 'Cooking Queue', 'href' => route('admin.dashboard').'#ia-kitchen'],
                ['label' => 'Preparing Orders', 'href' => route('admin.dashboard').'#ia-kitchen'],
                ['label' => 'Ready Orders', 'href' => route('admin.dashboard').'#ia-kitchen'],
                ['label' => 'Completed Orders', 'href' => route('admin.dashboard').'#ia-kitchen'],
            ],
        ],
        [
            'label' => 'Inventory',
            'items' => [
                ['label' => 'Stock Overview', 'href' => route('admin.stock.index'), 'active' => request()->routeIs('admin.stock.*')],
                ['label' => 'Ingredients', 'href' => null],
                ['label' => 'Stock In', 'href' => null],
                ['label' => 'Stock Out', 'href' => null],
                ['label' => 'Stock Adjustment', 'href' => null],
                ['label' => 'Low Stock Alerts', 'href' => null],
                ['label' => 'Recipes', 'href' => null],
            ],
        ],
        [
            'label' => 'Purchasing',
            'items' => [
                ['label' => 'Suppliers', 'href' => null],
                ['label' => 'Purchase Orders', 'href' => null],
                ['label' => 'Purchase History', 'href' => null],
                ['label' => 'Goods Received', 'href' => null],
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
            'label' => 'Store Settings',
            'items' => [
                ['label' => 'Store Information', 'href' => route('admin.settings.edit'), 'active' => request()->routeIs('admin.settings.*')],
                ['label' => 'Store Status', 'href' => route('admin.dashboard').'#store-status'],
                ['label' => 'Operating Hours', 'href' => route('admin.settings.edit')],
                ['label' => 'Store Location', 'href' => route('admin.settings.edit')],
                ['label' => 'Delivery Radius', 'href' => route('admin.settings.edit')],
                ['label' => 'Payment Settings', 'href' => null],
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
