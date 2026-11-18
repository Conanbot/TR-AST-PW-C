@extends('layouts.admin')

@section('title', 'Dashboard Pengelola — Geprek GLO Salatiga')

@section('content')
<!-- Header & Quick Toggle Bar -->
<div class="card" style="padding: var(--space-lg); margin-bottom: var(--space-lg); background: var(--surface-card); border-left: 4px solid var(--primary);">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <div class="flex items-center gap-xs" style="margin-bottom: 4px;">
                <span class="badge badge-promo" style="font-size: 0.75rem;">Information Architecture Dashboard</span>
                <span class="text-xs text-muted">Sistem Manajemen Operasional Terpadu</span>
            </div>
            <h2 style="font-size: 1.5rem; margin-bottom: 4px;">Halo, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-muted">
                Pantau pesanan masuk, antrean dapur, ringkasan penjualan, menu &amp; add-ons, serta catatan khusus pelanggan.
            </p>
        </div>

        <!-- Big Store Status Toggle Button (FR-18, FR-19, US-11) -->
        <div id="store-status" style="display: flex; align-items: center; gap: 12px; background: var(--surface); padding: 8px 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <div>
                <span class="text-xs text-muted block">Status Kedai Publik:</span>
                <strong id="dashboard-store-label" class="{{ $settings->is_open ? 'text-ok' : 'text-off' }}" style="font-size: 1.1rem;">
                    {{ $settings->is_open ? 'SEDANG BUKA' : 'SEDANG TUTUP' }}
                </strong>
            </div>
            <button type="button" class="btn btn-sm {{ $settings->is_open ? 'btn-secondary' : 'btn-primary' }}" onclick="toggleStoreStatus()">
                {{ $settings->is_open ? 'Tutup Kedai' : 'Buka Kedai' }}
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" role="alert" style="background: rgba(39, 174, 96, 0.15); border: 1px solid var(--ok); color: #2ecc71; padding: 12px 16px; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: var(--space-lg);">
        {{ session('success') }}
    </div>
@endif

<!-- Information Architecture Quick Navigation Tabs -->
<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: var(--space-xl); background: var(--surface); padding: 8px; border-radius: var(--radius-md); border: 1px solid var(--border);">
    <a href="#ia-orders" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm);">
        1. Orders &amp; Sales Overview
    </a>
    <a href="#ia-kitchen" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm);">
        2. Kitchen (Cooking Queue)
    </a>
    <a href="#ia-menu" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm);">
        3. Menu &amp; Add-Ons
    </a>
    <a href="#ia-support" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm);">
        4. Support &amp; Order Notes
    </a>
</div>

<!-- ==========================================
     IA SECTION 1: ORDERS & SALES OVERVIEW
     (Daily Revenue, Peak Hours, Best Sellers, Order Volume, Active Orders, Quick Actions)
     ========================================== -->
<section id="ia-orders" style="margin-bottom: var(--space-2xl);">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px;">
        <div>
            <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Information Architecture: Orders</span>
            <h3 style="font-size: 1.35rem; margin-top: 2px;">Sales Overview &amp; Active Orders</h3>
        </div>
        <div class="flex gap-xs">
            <a href="{{ route('admin.cashiers.index') }}" class="btn btn-secondary btn-sm">Akun Kasir ({{ $cashierCount }})</a>
            <a href="{{ route('admin.settings.edit') }}" class="btn btn-secondary btn-sm">Pengaturan</a>
        </div>
    </div>

    <!-- 4 Sales Overview Metric Cards (Daily Revenue, Peak Hours, Best Sellers, Order Volume) -->
    <div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom: var(--space-lg);">
        <div class="card" style="padding: var(--space-md); border-top: 3px solid var(--ok);">
            <div class="text-xs text-muted">Daily Revenue (Hari Ini)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #2ecc71; margin-top: 4px;">
                Rp{{ number_format($dailyRevenue, 0, ',', '.') }}
            </div>
            <div class="text-xs text-dim" style="margin-top: 2px;">Total omset pesanan masuk</div>
        </div>

        <div class="card" style="padding: var(--space-md); border-top: 3px solid var(--primary);">
            <div class="text-xs text-muted">Order Volume</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #FFFFFF; margin-top: 4px;">
                {{ $orderVolume }} Pesanan
            </div>
            <div class="text-xs text-dim" style="margin-top: 2px;">Transaksi tercatat di sistem</div>
        </div>

        <div class="card" style="padding: var(--space-md); border-top: 3px solid var(--warning);">
            <div class="text-xs text-muted">Peak Hours (Jam Sibuk)</div>
            <div style="font-size: 1.05rem; font-weight: 700; color: var(--warning); margin-top: 8px;">
                {{ $peakHours }}
            </div>
            <div class="text-xs text-dim" style="margin-top: 4px;">Jam ramai pesanan kedai</div>
        </div>

        <div class="card" style="padding: var(--space-md); border-top: 3px solid #3498db;">
            <div class="text-xs text-muted">Best Sellers Teratas</div>
            <div style="margin-top: 6px; display: flex; flex-direction: column; gap: 4px;">
                @forelse($bestSellers->take(2) as $bs)
                    <div class="flex justify-between items-center text-xs">
                        <strong style="color: var(--text);">{{ Str::limit($bs->item_name, 18) }}</strong>
                        <span class="badge" style="background: rgba(255,255,255,0.08); font-size: 0.7rem;">{{ $bs->total_qty }}x</span>
                    </div>
                @empty
                    <div class="text-xs text-muted">Paket Super Komplit, Ayam Geprek</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Active Orders Table (Quick Actions) -->
    <div class="card" style="padding: var(--space-md);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h4 style="font-size: 1.1rem; color: #FFFFFF;">Pesanan Aktif Terbaru (Active Orders)</h4>
            <span class="badge badge-open">{{ $recentOrders->count() }} Pesanan Tercatat</span>
        </div>

        @if($recentOrders->isEmpty())
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                Belum ada pesanan aktif tercatat.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nomor &amp; Waktu</th>
                            <th>Pelanggan</th>
                            <th>Layanan &amp; Meja</th>
                            <th>Rincian Menu</th>
                            <th>Catatan Pesanan</th>
                            <th>Status Dapur</th>
                            <th>Total</th>
                            <th>Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                                <div class="text-xs text-muted">{{ $order->created_at->format('d M, H:i') }}</div>
                            </td>
                            <td>
                                <strong style="color: var(--text);">{{ $order->customer_name }}</strong>
                                @if($order->customer_phone)
                                    <div class="text-xs text-muted">{{ $order->customer_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.06);">
                                    {{ $order->service_method === 'dine_in' ? 'Dine-In' : ($order->service_method === 'takeaway' ? 'Takeaway' : 'Delivery') }}
                                </span>
                                @if($order->table_number)
                                    <div class="text-xs" style="color: var(--primary); font-weight: 600; margin-top: 2px;">
                                        {{ $order->table_number }}
                                    </div>
                                @endif
                            </td>
                            <td style="max-width: 220px;">
                                <div class="text-xs" style="line-height: 1.4;">
                                    @foreach($order->items as $itm)
                                        <div>&bull; {{ $itm->quantity }}x {{ $itm->item_name }}
                                            @if($itm->chicken_cut)<span class="text-muted">({{ $itm->chicken_cut }})</span>@endif
                                            @if($itm->drink_option)<span class="text-muted">({{ $itm->drink_option }})</span>@endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="max-width: 180px;">
                                @if($order->order_notes)
                                    <span class="badge" style="background: rgba(243, 156, 18, 0.15); color: var(--warning); font-size: 0.75rem; border: 1px solid rgba(243,156,18,0.3);">
                                        {{ $order->order_notes }}
                                    </span>
                                @else
                                    <span class="text-xs text-dim">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $kStatus = $order->kitchen_status ?? 'queued';
                                @endphp
                                <span class="badge {{ $kStatus === 'ready' ? 'badge-open' : ($kStatus === 'prepared' ? 'badge-promo' : 'badge-closed') }}" style="text-transform: uppercase; font-size: 0.75rem;">
                                    {{ $kStatus }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--primary);">{{ $order->formatted_total }}</strong>
                            </td>
                            <td>
                                <form action="{{ route('admin.orders.kitchen-status', $order) }}" method="POST" style="display: flex; gap: 4px;">
                                    @csrf
                                    @method('PATCH')
                                    @if($kStatus === 'queued')
                                        <input type="hidden" name="kitchen_status" value="prepared">
                                        <button type="submit" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 2px 8px;" title="Mulai proses masak di dapur">Mulai Masak</button>
                                    @elseif($kStatus === 'prepared')
                                        <input type="hidden" name="kitchen_status" value="ready">
                                        <button type="submit" class="btn btn-sm" style="background: #27ae60; color: #fff; font-size: 0.75rem; padding: 2px 8px;" title="Tandai pesanan matang & siap saji">Siap Saji</button>
                                    @elseif($kStatus === 'ready')
                                        <input type="hidden" name="kitchen_status" value="completed">
                                        <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 2px 8px;" title="Tandai pesanan telah selesai disajikan">Selesai</button>
                                    @else
                                        <span class="text-xs text-ok">Selesai</span>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

<!-- ==========================================
     IA SECTION 2: KITCHEN (COOKING QUEUE)
     (Cooking Queue: Queued, Prepared, Ready, Ready Orders, Completed Orders)
     ========================================== -->
<section id="ia-kitchen" style="margin-bottom: var(--space-2xl);">
    <div style="margin-bottom: 12px;">
        <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Information Architecture: Kitchen</span>
        <h3 style="font-size: 1.35rem; margin-top: 2px;">Cooking Queue &amp; Ready Orders</h3>
        <p class="text-sm text-muted">Alur kerja tim dapur: Antrean Masuk (Queued) &rarr; Sedang Dimasak (Prepared) &rarr; Siap Disajikan (Ready).</p>
    </div>

    <div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-bottom: var(--space-lg);">
        <!-- Column 1: Queued -->
        <div class="card" style="padding: var(--space-md); border-top: 4px solid var(--off);">
            <div class="flex justify-between items-center" style="margin-bottom: 12px;">
                <h4 style="font-size: 1rem; color: #FFFFFF;">1. Queued (Menunggu)</h4>
                <span class="badge" style="background: rgba(255,255,255,0.08);">{{ $queuedOrders->count() }}</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($queuedOrders as $qOrder)
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px;">
                    <div class="flex justify-between items-start" style="margin-bottom: 4px;">
                        <strong style="color: var(--text); font-size: 0.9rem;">{{ $qOrder->order_number }}</strong>
                        <span class="badge" style="font-size: 0.7rem; background: rgba(255,255,255,0.06);">{{ $qOrder->table_number ?: $qOrder->service_method }}</span>
                    </div>
                    <div class="text-xs text-muted" style="margin-bottom: 6px;">{{ $qOrder->customer_name }} &middot; {{ $qOrder->created_at->format('H:i') }}</div>
                    
                    <div class="text-xs" style="margin-bottom: 8px; border-top: 1px dashed var(--border); padding-top: 6px;">
                        @foreach($qOrder->items as $it)
                            <div>&bull; {{ $it->quantity }}x {{ $it->item_name }} @if($it->chicken_cut)({{ $it->chicken_cut }})@endif</div>
                        @endforeach
                    </div>

                    @if($qOrder->order_notes)
                        <div class="text-xs" style="color: var(--warning); margin-bottom: 6px;">
                            Note: {{ $qOrder->order_notes }}
                        </div>
                    @endif

                    <form action="{{ route('admin.orders.kitchen-status', $qOrder) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="kitchen_status" value="prepared">
                        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center; font-size: 0.8rem; padding: 4px;">
                            Mulai Masak &rarr;
                        </button>
                    </form>
                </div>
                @empty
                <div class="text-xs text-muted" style="text-align: center; padding: 20px;">
                    Tidak ada antrean pesanan baru.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Prepared -->
        <div class="card" style="padding: var(--space-md); border-top: 4px solid var(--primary);">
            <div class="flex justify-between items-center" style="margin-bottom: 12px;">
                <h4 style="font-size: 1rem; color: #FFFFFF;">2. Prepared (Sedang Dimasak)</h4>
                <span class="badge badge-promo">{{ $preparedOrders->count() }}</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($preparedOrders as $pOrder)
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px;">
                    <div class="flex justify-between items-start" style="margin-bottom: 4px;">
                        <strong style="color: var(--text); font-size: 0.9rem;">{{ $pOrder->order_number }}</strong>
                        <span class="badge" style="font-size: 0.7rem; background: rgba(215,38,61,0.2); color: var(--primary);">{{ $pOrder->table_number ?: $pOrder->service_method }}</span>
                    </div>
                    <div class="text-xs text-muted" style="margin-bottom: 6px;">{{ $pOrder->customer_name }}</div>

                    <div class="text-xs" style="margin-bottom: 8px; border-top: 1px dashed var(--border); padding-top: 6px;">
                        @foreach($pOrder->items as $it)
                            <div>&bull; {{ $it->quantity }}x {{ $it->item_name }} @if($it->chicken_cut)({{ $it->chicken_cut }})@endif</div>
                            @if($it->note)<div class="text-dim" style="padding-left: 8px; font-style: italic;">"{{ $it->note }}"</div>@endif
                        @endforeach
                    </div>

                    <form action="{{ route('admin.orders.kitchen-status', $pOrder) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="kitchen_status" value="ready">
                        <button type="submit" class="btn btn-sm" style="width: 100%; justify-content: center; background: #27ae60; color: #fff; font-size: 0.8rem; padding: 4px;">
                            Tandai Siap Saji &rarr;
                        </button>
                    </form>
                </div>
                @empty
                <div class="text-xs text-muted" style="text-align: center; padding: 20px;">
                    Tidak ada menu yang sedang dimasak saat ini.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Column 3: Ready Orders -->
        <div class="card" style="padding: var(--space-md); border-top: 4px solid var(--ok);">
            <div class="flex justify-between items-center" style="margin-bottom: 12px;">
                <h4 style="font-size: 1rem; color: #FFFFFF;">3. Ready Orders (Siap Disajikan)</h4>
                <span class="badge badge-open">{{ $readyOrders->count() }}</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($readyOrders as $rOrder)
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px;">
                    <div class="flex justify-between items-start" style="margin-bottom: 4px;">
                        <strong style="color: #2ecc71; font-size: 0.9rem;">{{ $rOrder->order_number }}</strong>
                        <span class="badge badge-open" style="font-size: 0.7rem;">{{ $rOrder->table_number ?: 'Takeaway' }}</span>
                    </div>
                    <div class="text-xs text-muted" style="margin-bottom: 6px;">{{ $rOrder->customer_name }} &middot; {{ $rOrder->formatted_total }}</div>

                    <div class="text-xs" style="margin-bottom: 8px; border-top: 1px dashed var(--border); padding-top: 6px;">
                        @foreach($rOrder->items as $it)
                            <div>&bull; {{ $it->quantity }}x {{ $it->item_name }}</div>
                        @endforeach
                    </div>

                    <form action="{{ route('admin.orders.kitchen-status', $rOrder) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="kitchen_status" value="completed">
                        <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; font-size: 0.8rem; padding: 4px;">
                            Selesaikan Pesanan &check;
                        </button>
                    </form>
                </div>
                @empty
                <div class="text-xs text-muted" style="text-align: center; padding: 20px;">
                    Tidak ada pesanan yang siap disajikan.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     IA SECTION 3: MENU CATEGORIES & ADD-ONS
     (Menu Item, Food Item, Add-Ons)
     ========================================== -->
<section id="ia-menu" style="margin-bottom: var(--space-2xl);">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px;">
        <div>
            <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Information Architecture: Menu Categories</span>
            <h3 style="font-size: 1.35rem; margin-top: 2px;">Menu Items, Food Items &amp; Add-Ons</h3>
        </div>
        <div class="flex gap-xs">
            <a href="{{ route('admin.items.index') }}" class="btn btn-primary btn-sm">+ Kelola Semua Menu</a>
            <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm">Kontrol Stok</a>
        </div>
    </div>

    <!-- 3 Metrics for Menu Categories -->
    <div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom: var(--space-md);">
        <div class="card" style="padding: var(--space-md);">
            <div class="text-xs text-muted">Total Menu Items</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #FFFFFF; margin-top: 4px;">{{ $totalMenu }}</div>
            <div class="text-xs text-dim">Semua kategori menu</div>
        </div>
        <div class="card" style="padding: var(--space-md); border-top: 3px solid var(--primary);">
            <div class="text-xs text-muted">Food Items (Makanan)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary); margin-top: 4px;">{{ $foodItemsCount }}</div>
            <div class="text-xs text-dim">Paket, aneka ayam &amp; sup</div>
        </div>
        <div class="card" style="padding: var(--space-md); border-top: 3px solid var(--ok);">
            <div class="text-xs text-muted">Menu Tersedia</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--ok); margin-top: 4px;">{{ $availableMenu }}</div>
            <div class="text-xs text-dim">Dapat dipesan pelanggan</div>
        </div>
        <div class="card" style="padding: var(--space-md); border-top: 3px solid {{ $outOfStockMenu > 0 ? 'var(--off)' : 'var(--border)' }};">
            <div class="text-xs text-muted">Menu Habis (Stok Kosong)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: {{ $outOfStockMenu > 0 ? 'var(--off)' : '#FFFFFF' }}; margin-top: 4px;">{{ $outOfStockMenu }}</div>
            <div class="text-xs text-dim">Dinonaktifkan di web</div>
        </div>
    </div>

    <!-- Add-Ons Section Table (From IA) -->
    <div class="card" style="padding: var(--space-md); margin-bottom: var(--space-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div>
                <h4 style="font-size: 1.05rem; color: #FFFFFF;">Daftar Pilihan Add-Ons (Tambahan Kuliner)</h4>
                <p class="text-xs text-muted">Pilihan pelengkap makanan sesuai arsitektur informasi Geprek GLO.</p>
            </div>
            <span class="badge" style="background: rgba(255,255,255,0.06);">6 Add-Ons Terdaftar</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama Add-On</th>
                        <th>Kategori / Jenis</th>
                        <th>Tambahan Harga</th>
                        <th>Status Layanan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($addOns as $addon)
                    <tr>
                        <td><strong style="color: var(--text);">{{ $addon['name'] }}</strong></td>
                        <td><span class="badge" style="background: rgba(255,255,255,0.06); font-size: 0.75rem;">{{ $addon['type'] }}</span></td>
                        <td style="color: var(--primary); font-weight: 700;">+Rp{{ number_format($addon['price'], 0, ',', '.') }}</td>
                        <td><span class="badge badge-open">Tersedia</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- ==========================================
     IA SECTION 4: SUPPORT & NOTES
     (Order Notes: Priority Service, Add Cutlery, Birthday Message, Table Change; Customer Request; Staff Messages)
     ========================================== -->
<section id="ia-support" style="margin-bottom: var(--space-2xl);">
    <div style="margin-bottom: 12px;">
        <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Information Architecture: Support / Notes</span>
        <h3 style="font-size: 1.35rem; margin-top: 2px;">Order Notes, Customer Requests &amp; Staff Messages</h3>
    </div>

    <div class="grid grid-cols-1 grid-cols-2 gap-md">
        <!-- Order Notes & Customer Requests -->
        <div class="card" style="padding: var(--space-md);">
            <h4 style="font-size: 1.05rem; margin-bottom: 10px; color: #FFFFFF;">
                Order Notes &amp; Customer Requests Terbaru
            </h4>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @forelse($ordersWithNotes as $nOrder)
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px;">
                    <div class="flex justify-between items-center" style="margin-bottom: 4px;">
                        <strong style="color: var(--text); font-size: 0.85rem;">{{ $nOrder->order_number }} ({{ $nOrder->customer_name }})</strong>
                        <span class="text-xs text-muted">{{ $nOrder->table_number ?: $nOrder->service_method }}</span>
                    </div>
                    @if($nOrder->order_notes)
                        <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px;">
                            @foreach(explode(',', $nOrder->order_notes) as $tag)
                                <span class="badge" style="background: rgba(243,156,18,0.15); color: var(--warning); font-size: 0.7rem; border: 1px solid rgba(243,156,18,0.3);">
                                    {{ trim($tag) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    @if($nOrder->staff_notes)
                        <div class="text-xs text-dim" style="margin-top: 4px; font-style: italic;">
                            Pesan Staf: {{ $nOrder->staff_notes }}
                        </div>
                    @endif
                </div>
                @empty
                <div class="text-xs text-muted" style="text-align: center; padding: 20px;">
                    Belum ada pesanan dengan catatan khusus pelanggan.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Staff Messages (Kitchen Delay, Item Unavailable) -->
        <div class="card" style="padding: var(--space-md);">
            <h4 style="font-size: 1.05rem; margin-bottom: 10px; color: #FFFFFF;">
                Staff Messages &amp; Pengumuman Kedai
            </h4>
            <p class="text-xs text-muted" style="margin-bottom: 14px;">
                Informasi penting antarstaf untuk mengantisipasi lonjakan antrean atau bahan habis.
            </p>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Staff Alert 1: Kitchen Delay -->
                <div style="background: rgba(243, 156, 18, 0.1); border: 1px solid rgba(243, 156, 18, 0.3); border-radius: var(--radius-md); padding: 12px;">
                    <div class="flex items-center gap-xs" style="margin-bottom: 4px;">
                        <span class="badge" style="background: var(--warning); color: #000; font-weight: 700; font-size: 0.7rem;">Kitchen Delay</span>
                        <strong style="color: var(--warning); font-size: 0.85rem;">Status Antrean Dapur</strong>
                    </div>
                    <p class="text-xs text-muted" style="line-height: 1.4;">
                        @if($queuedOrders->count() + $preparedOrders->count() >= 5)
                            Antrean dapur sedang padat ({{ $queuedOrders->count() + $preparedOrders->count() }} pesanan berjalan). Perkiraan waktu masak 15 - 20 menit.
                        @else
                            Kapasitas dapur normal. Pesanan dapat diproses tepat waktu dalam 5 - 10 menit.
                        @endif
                    </p>
                </div>

                <!-- Staff Alert 2: Item Unavailable -->
                <div style="background: {{ $outOfStockMenu > 0 ? 'rgba(215, 38, 61, 0.1)' : 'rgba(39, 174, 96, 0.1)' }}; border: 1px solid {{ $outOfStockMenu > 0 ? 'rgba(215, 38, 61, 0.3)' : 'rgba(39, 174, 96, 0.3)' }}; border-radius: var(--radius-md); padding: 12px;">
                    <div class="flex items-center gap-xs" style="margin-bottom: 4px;">
                        <span class="badge {{ $outOfStockMenu > 0 ? 'badge-closed' : 'badge-open' }}" style="font-size: 0.7rem;">
                            {{ $outOfStockMenu > 0 ? 'Item Unavailable' : 'Stock Ready' }}
                        </span>
                        <strong style="font-size: 0.85rem; color: {{ $outOfStockMenu > 0 ? 'var(--primary)' : 'var(--ok)' }};">
                            Ketersediaan Stok Bahan
                        </strong>
                    </div>
                    <p class="text-xs text-muted" style="line-height: 1.4;">
                        @if($outOfStockMenu > 0)
                            Terdapat <strong>{{ $outOfStockMenu }} menu</strong> yang berstatus HABIS. Pastikan beri tahu kasir dan perbarui di pengatur stok.
                        @else
                            Seluruh menu, potongan ayam, dan minuman berstatus TERSEDIA.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Potongan Ayam & Minuman Quick Toggles -->
<div class="grid grid-cols-1 grid-cols-2 gap-lg" style="margin-bottom: var(--space-xl);">
    <div class="card" style="padding: var(--space-lg);">
        <h4 style="margin-bottom: 12px; font-size: 1.05rem;">Status Stok Potongan Ayam</h4>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($chickenCuts as $cut)
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface); padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <span style="font-weight: 500;">{{ $cut->name }}</span>
                <button type="button" 
                        class="badge {{ $cut->is_available ? 'badge-open' : 'badge-closed' }}" 
                        onclick="quickToggleCut({{ $cut->id }}, this)"
                        style="cursor: pointer; border: 1px solid currentColor;">
                    {{ $cut->is_available ? 'Tersedia' : 'Habis' }}
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card" style="padding: var(--space-lg);">
        <h4 style="margin-bottom: 12px; font-size: 1.05rem;">Status Opsi Minuman Paket</h4>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($drinkOptions as $drink)
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface); padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <span style="font-weight: 500;">{{ $drink->name }}</span>
                <button type="button" 
                        class="badge {{ $drink->is_available ? 'badge-open' : 'badge-closed' }}" 
                        onclick="quickToggleDrink({{ $drink->id }}, this)"
                        style="cursor: pointer; border: 1px solid currentColor;">
                    {{ $drink->is_available ? 'Tersedia' : 'Habis' }}
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function toggleStoreStatus() {
        try {
            const res = await fetch("{{ route('admin.store.toggle') }}", {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                const label = document.getElementById('dashboard-store-label');
                if (label) {
                    label.textContent = data.is_open ? 'SEDANG BUKA' : 'SEDANG TUTUP';
                    label.className = data.is_open ? 'text-ok' : 'text-off';
                }
                const headerText = document.getElementById('header-store-text');
                if (headerText) {
                    headerText.textContent = 'Kedai: ' + (data.is_open ? 'BUKA' : 'TUTUP');
                }
                alert(data.message);
                window.location.reload();
            }
        } catch (err) {
            console.error(err);
            alert('Gagal mengubah status kedai.');
        }
    }

    async function quickToggleCut(id, btn) {
        try {
            const res = await fetch(`/admin/cuts/${id}/availability`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                btn.className = 'badge ' + (data.is_available ? 'badge-open' : 'badge-closed');
                btn.textContent = data.is_available ? 'Tersedia' : 'Habis';
            }
        } catch (err) { console.error(err); }
    }

    async function quickToggleDrink(id, btn) {
        try {
            const res = await fetch(`/admin/drinks/${id}/availability`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                btn.className = 'badge ' + (data.is_available ? 'badge-open' : 'badge-closed');
                btn.textContent = data.is_available ? 'Tersedia' : 'Habis';
            }
        } catch (err) { console.error(err); }
    }
</script>
@endpush
