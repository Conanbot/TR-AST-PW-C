@extends('layouts.cashier')

@section('title', 'Kasir & Pesanan — Geprek GLO')

@section('content')
@php
    $occupiedTables = $orders
        ->where('service_method', 'dine_in')
        ->whereIn('status', ['pending', 'preparing', 'ready'])
        ->pluck('table_number')
        ->filter()
        ->all();
@endphp

<section class="page-heading">
    <div>
        <span class="eyebrow">Area kerja kasir</span>
        <h1>Pesanan &amp; meja</h1>
        <p>Pilih meja, tambahkan menu, lalu kirim pesanan ke dapur.</p>
    </div>
    <div class="store-state {{ $settings->is_open ? 'is-open' : 'is-closed' }}">
        <span class="status-dot"></span>
        <span>Kedai {{ $settings->is_open ? 'buka' : 'tutup' }}</span>
    </div>
</section>

<div class="cashier-workspace">
    <section class="cashier-main-column" aria-label="Pilih meja dan menu">
        <section class="surface-panel table-panel">
            <div class="panel-heading">
                <div>
                    <span class="eyebrow">Lantai utama</span>
                    <h2>Pilih meja</h2>
                </div>
                <div class="table-legend"><span><i class="legend-free"></i>Kosong</span><span><i class="legend-busy"></i>Terisi</span></div>
            </div>
            <div class="table-grid" role="group" aria-label="Pilih meja untuk pesanan dine-in">
                @for($table = 1; $table <= 12; $table++)
                    @php $tableNumber = str_pad((string) $table, 2, '0', STR_PAD_LEFT); @endphp
                    <button class="table-tile {{ in_array($tableNumber, $occupiedTables, true) ? 'is-occupied' : '' }}" type="button" data-table="{{ $tableNumber }}" aria-pressed="false" {{ in_array($tableNumber, $occupiedTables, true) ? 'disabled' : '' }}>
                        <span class="table-seat seat-top"></span>
                        <span class="table-number">{{ $tableNumber }}</span>
                        <span class="table-label">{{ in_array($tableNumber, $occupiedTables, true) ? 'Terisi' : 'Meja' }}</span>
                        <span class="table-seat seat-bottom"></span>
                    </button>
                @endfor
            </div>
        </section>

        <section class="surface-panel menu-panel">
            <div class="panel-heading menu-heading">
                <div>
                    <span class="eyebrow">Dapur Geprek GLO</span>
                    <h2>Pilih menu</h2>
                </div>
                <label class="search-control">
                    <span class="sr-only">Cari menu</span>
                    <input type="search" id="cashier-search" placeholder="Cari menu..." autocomplete="off">
                </label>
            </div>
            <div class="category-pills" role="group" aria-label="Filter kategori menu">
                <button class="category-pill is-active" type="button" data-filter="all">Semua</button>
                @foreach($categories as $category)
                    <button class="category-pill" type="button" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
                @endforeach
            </div>
            <div class="cashier-menu-grid" id="cashier-menu-grid">
                @foreach($categories as $category)
                    @foreach($category->menuItems as $item)
                        <article class="cashier-menu-item" data-category="{{ $category->slug }}" data-name="{{ mb_strtolower($item->name) }}">
                            <div class="menu-item-art" style="overflow: hidden; padding: 0; aspect-ratio: 4 / 3;">
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                @else
                                    <span aria-hidden="true">{{ mb_substr($item->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="menu-item-copy">
                                <span class="menu-item-category">{{ $category->name }}</span>
                                <h3>{{ $item->name }}</h3>
                                <div class="menu-item-footer">
                                    <strong>{{ $item->formatted_price }}</strong>
                                    <button type="button" class="add-menu-button" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $item->price }}" aria-label="Tambah {{ $item->name }}">+</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                @endforeach
            </div>
            <p class="menu-empty-state" id="menu-empty-state" hidden>Menu tidak ditemukan. Coba kata kunci lain.</p>
        </section>
    </section>

    <aside class="cashier-order-column" aria-label="Rincian pesanan">
        <form action="{{ route('cashier.orders.store') }}" method="POST" class="surface-panel order-ticket" id="cashier-order-form">
            @csrf
            <div class="ticket-heading">
                <div><span class="eyebrow">Pesanan baru</span><h2>Rincian order</h2></div>
                <span class="order-icon" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
            </div>
            <div class="form-group">
                <label class="form-label" for="cashier-customer-name">Nama pelanggan</label>
                <input class="form-control" id="cashier-customer-name" name="customer_name" placeholder="Nama pemesan" value="{{ old('customer_name') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="cashier-customer-phone">Nomor telepon <span class="text-dim">(opsional)</span></label>
                <input class="form-control" id="cashier-customer-phone" name="customer_phone" inputmode="tel" placeholder="Nomor yang bisa dihubungi">
            </div>
            <div class="form-group">
                <span class="form-label">Jenis pesanan</span>
                <div class="service-choice">
                    <label><input type="radio" name="service_method" value="dine_in" checked><span>Makan di tempat</span></label>
                    <label><input type="radio" name="service_method" value="takeaway"><span>Bawa pulang</span></label>
                </div>
            </div>
            <input type="hidden" name="table_number" id="cashier-table-number">
            <input type="hidden" name="payment_method" value="cash_pickup">
            <input type="hidden" name="items" id="cashier-order-items">
            <div class="ticket-items" id="cashier-order-items-list" aria-live="polite">
                <div class="ticket-empty"><span class="ticket-empty-mark" aria-hidden="true"><i class="fa-solid fa-plus"></i></span><p>Belum ada menu.<br>Pilih menu untuk mulai.</p></div>
            </div>
            <div class="ticket-total">
                <span>Subtotal</span><strong id="cashier-order-total">Rp0</strong>
            </div>
            <button type="submit" class="btn btn-primary order-submit" id="cashier-submit" disabled>Kirim pesanan ke dapur <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            <p class="ticket-note">Harga diambil langsung dari katalog kedai.</p>
        </form>

        <section class="surface-panel active-orders-panel">
            <div class="panel-heading"><div><span class="eyebrow">Antrean</span><h2>Pesanan aktif</h2></div><span class="count-badge">{{ $orders->count() }}</span></div>
            @forelse($orders as $order)
                <article class="active-order-card">
                    <div class="active-order-top">
                        <div><strong>{{ $order->order_number }}</strong><span>{{ $order->customer_name }} · {{ $order->service_method === 'dine_in' ? 'Meja ' . ($order->table_number ?: '—') : 'Bawa pulang' }}</span></div>
                        <span class="order-status status-{{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span>
                    </div>
                    <p class="active-order-items">{{ $order->items->map(fn ($item) => $item->quantity . '× ' . $item->item_name)->implode(', ') }}</p>
                    <div class="active-order-bottom">
                        <strong>{{ $order->formatted_total }}</strong>
                        <form method="POST" action="{{ route('cashier.orders.status', $order) }}">
                            @csrf @method('PATCH')
                            <label class="sr-only" for="status-{{ $order->id }}">Ubah status pesanan {{ $order->order_number }}</label>
                            <select class="status-select" id="status-{{ $order->id }}" name="status" onchange="this.form.submit()">
                                <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>Siapkan</option>
                                <option value="ready" {{ $order->status === 'ready' ? 'selected' : '' }}>Siap</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Batalkan</option>
                            </select>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state"><span class="empty-state-mark" aria-hidden="true"><i class="fa-solid fa-check"></i></span><h3>Belum ada antrean</h3><p>Pesanan baru akan muncul di sini.</p></div>
            @endforelse
        </section>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const escapeHtml = value => String(value).replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[character]));
    const cart = new Map();
    const form = document.getElementById('cashier-order-form');
    const itemsField = document.getElementById('cashier-order-items');
    const itemsList = document.getElementById('cashier-order-items-list');
    const totalLabel = document.getElementById('cashier-order-total');
    const submitButton = document.getElementById('cashier-submit');
    const tableField = document.getElementById('cashier-table-number');
    const serviceInputs = [...document.querySelectorAll('input[name="service_method"]')];

    function renderCart() {
        const items = [...cart.values()];
        itemsField.value = JSON.stringify(items.map(({id, qty, note}) => ({id, qty, note})));
        itemsList.innerHTML = items.length
            ? items.map(item => `<div class="ticket-line"><div><strong>${escapeHtml(item.name)}</strong><span>${formatRupiah(item.price)} / item</span></div><div class="quantity-control"><button type="button" data-change="-1" data-id="${item.id}" aria-label="Kurangi ${escapeHtml(item.name)}"><i class="fa-solid fa-minus" aria-hidden="true"></i></button><span>${item.qty}</span><button type="button" data-change="1" data-id="${item.id}" aria-label="Tambah ${escapeHtml(item.name)}"><i class="fa-solid fa-plus" aria-hidden="true"></i></button></div></div>`).join('')
            : '<div class="ticket-empty"><span class="ticket-empty-mark" aria-hidden="true"><i class="fa-solid fa-plus"></i></span><p>Belum ada menu.<br>Pilih menu untuk mulai.</p></div>';
        const total = items.reduce((sum, item) => sum + item.price * item.qty, 0);
        totalLabel.textContent = formatRupiah(total);
        submitButton.disabled = items.length === 0;
        if (serviceInputs.find(input => input.checked)?.value === 'dine_in' && !tableField.value) {
            submitButton.disabled = true;
        }
    }

    document.querySelectorAll('.add-menu-button').forEach(button => button.addEventListener('click', () => {
        const id = Number(button.dataset.id);
        const current = cart.get(id) || {id, name: button.dataset.name, price: Number(button.dataset.price), qty: 0};
        current.qty += 1;
        cart.set(id, current);
        renderCart();
    }));

    itemsList.addEventListener('click', event => {
        const button = event.target.closest('[data-change]');
        if (!button) return;
        const item = cart.get(Number(button.dataset.id));
        if (!item) return;
        item.qty += Number(button.dataset.change);
        if (item.qty <= 0) cart.delete(item.id);
        renderCart();
    });

    document.querySelectorAll('.table-tile:not(:disabled)').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('.table-tile').forEach(table => {
            table.classList.remove('is-selected');
            table.setAttribute('aria-pressed', 'false');
        });
        button.classList.add('is-selected');
        button.setAttribute('aria-pressed', 'true');
        tableField.value = button.dataset.table;
        renderCart();
    }));

    serviceInputs.forEach(input => input.addEventListener('change', () => {
        const isDineIn = serviceInputs.find(option => option.checked)?.value === 'dine_in';
        document.querySelector('.table-panel').classList.toggle('is-muted', !isDineIn);
        renderCart();
    }));

    document.querySelectorAll('.category-pill').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('.category-pill').forEach(pill => pill.classList.remove('is-active'));
        button.classList.add('is-active');
        filterMenu();
    }));
    document.getElementById('cashier-search').addEventListener('input', filterMenu);

    function filterMenu() {
        const filter = document.querySelector('.category-pill.is-active').dataset.filter;
        const search = document.getElementById('cashier-search').value.trim().toLocaleLowerCase('id');
        let visible = 0;
        document.querySelectorAll('.cashier-menu-item').forEach(item => {
            const matches = (filter === 'all' || item.dataset.category === filter) && item.dataset.name.includes(search);
            item.hidden = !matches;
            if (matches) visible += 1;
        });
        document.getElementById('menu-empty-state').hidden = visible > 0;
    }

    form.addEventListener('submit', event => {
        if (!itemsField.value || itemsField.value === '[]') {
            event.preventDefault();
            window.showToast('Pilih setidaknya satu menu untuk pesanan.', 'error');
        }
    });
    renderCart();
})();
</script>
@endpush
