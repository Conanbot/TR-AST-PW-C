@extends('layouts.admin')

@section('title', 'Dashboard Pengelola — Geprek GLO Salatiga')

@section('content')
<!-- Header & Quick Toggle Bar -->
<div class="card" style="padding: var(--space-lg); margin-bottom: var(--space-xl); background: var(--surface-card); border-left: 4px solid var(--primary);">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin-bottom: 4px;">Halo, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-muted">
                Kelola status kedai, ketersediaan stok, harga, dan banner promo dengan mudah dari perangkat ponsel atau komputer Anda.
            </p>
        </div>

        <!-- Big Store Status Toggle Button (FR-18, FR-19, US-11) -->
        <div style="display: flex; align-items: center; gap: 12px; background: var(--surface); padding: 8px 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
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

<!-- 4 Summary Metric Cards (FR-18) -->
<div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom: var(--space-xl);">
    <div class="card" style="padding: var(--space-md);">
        <div class="text-xs text-muted">Total Menu Terdaftar</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #FFFFFF; margin-top: 4px;">{{ $totalMenu }}</div>
        <div class="text-xs text-dim" style="margin-top: 2px;">Item makanan &amp; minuman</div>
    </div>

    <div class="card" style="padding: var(--space-md); border-bottom: 3px solid var(--ok);">
        <div class="text-xs text-muted">Menu Tersedia</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--ok); margin-top: 4px;">{{ $availableMenu }}</div>
        <div class="text-xs text-dim" style="margin-top: 2px;">Dapat dipesan pelanggan</div>
    </div>

    <div class="card" style="padding: var(--space-md); border-bottom: 3px solid {{ $outOfStockMenu > 0 ? 'var(--primary)' : 'var(--off)' }};">
        <div class="text-xs text-muted">Menu Habis (Kosong)</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: {{ $outOfStockMenu > 0 ? 'var(--primary)' : 'var(--off)' }}; margin-top: 4px;">
            {{ $outOfStockMenu }}
        </div>
        <div class="text-xs text-dim" style="margin-top: 2px;">Tombol tambah dinonaktifkan</div>
    </div>

    <div class="card" style="padding: var(--space-md); border-bottom: 3px solid var(--warning);">
        <div class="text-xs text-muted">Banner Promo Aktif</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--warning); margin-top: 4px;">{{ $activePromos }}</div>
        <div class="text-xs text-dim" style="margin-top: 2px;">Tampil di halaman beranda</div>
    </div>
</div>

<section class="surface-panel admin-orders-panel" style="margin-bottom: var(--space-xl);">
    <div class="panel-heading" style="margin-bottom: 14px;">
        <div>
            <span class="eyebrow">Aktivitas terbaru</span>
            <h2>Pesanan masuk</h2>
        </div>
        <a href="{{ route('admin.cashiers.index') }}" class="btn btn-secondary btn-sm">Kelola {{ $cashierCount }} akun kasir</a>
    </div>
    @if($recentOrders->isEmpty())
        <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">＋</span><h3>Belum ada pesanan tercatat</h3><p>Pesanan online dan pesanan kasir akan muncul di sini.</p></div>
    @else
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead><tr><th>Nomor</th><th>Pelanggan</th><th>Jenis layanan</th><th>Menu</th><th>Status</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong><div class="text-xs text-muted">{{ $order->created_at->format('d M, H:i') }}</div></td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->service_method === 'dine_in' ? 'Makan di tempat' : ($order->service_method === 'takeaway' ? 'Bawa pulang' : 'Pesan antar') }}</td>
                            <td>{{ $order->items->sum('quantity') }} item</td>
                            <td><span class="order-status status-{{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span></td>
                            <td><strong>{{ $order->formatted_total }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<!-- Out of Stock Alert & Management Section (FR-20) -->
<div class="card" style="padding: var(--space-lg); margin-bottom: var(--space-xl);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-md);">
        <div>
            <h3 style="font-size: 1.2rem;">Daftar Menu yang Sedang Habis</h3>
            <p class="text-xs text-muted">Menu di bawah ini otomatis tidak dapat dimasukkan ke keranjang oleh pelanggan.</p>
        </div>
        <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm">Buka Pengatur Stok Lengkap &rarr;</a>
    </div>

    @if($outOfStockItems->isEmpty())
        <div style="text-align: center; padding: 30px; background: var(--surface); border-radius: var(--radius-md); color: var(--ok);">
            ✓ Seluruh menu saat ini berstatus <strong>TERSEDIA</strong> untuk dipesan.
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outOfStockItems as $item)
                    <tr>
                        <td>
                            <strong style="color: var(--text);">{{ $item->name }}</strong>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(255,255,255,0.06);">{{ $item->category->name ?? '-' }}</span>
                        </td>
                        <td style="color: var(--primary); font-weight: 600;">
                            {{ $item->formatted_price }}
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm" onclick="quickToggleItemStock({{ $item->id }}, this)">
                                Jadikan Tersedia
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Chicken Cuts & Drinks Status Overview (FR-07, FR-08, FR-20) -->
<div class="grid grid-cols-1 grid-cols-2 gap-lg" style="margin-bottom: var(--space-xl);">
    <!-- Potongan Ayam -->
    <div class="card" style="padding: var(--space-lg);">
        <h4 style="margin-bottom: 12px; font-size: 1.05rem;">🍗 Status Stok Potongan Ayam</h4>
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

    <!-- Opsi Minuman Paket -->
    <div class="card" style="padding: var(--space-lg);">
        <h4 style="margin-bottom: 12px; font-size: 1.05rem;">🥤 Status Opsi Minuman Paket</h4>
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

<!-- Quick Actions Shortcuts -->
<div class="card" style="padding: var(--space-lg);">
    <h4 style="margin-bottom: 12px; font-size: 1.05rem;">Pintasan Tindakan Cepat</h4>
    <div class="flex gap-md flex-wrap">
        <a href="{{ route('admin.items.create') }}" class="btn btn-primary">
            + Tambah Menu Baru
        </a>
        <a href="{{ route('admin.promos.create') }}" class="btn btn-secondary">
            + Tambah Banner Promo
        </a>
        <a href="{{ route('admin.settings.edit') }}" class="btn btn-secondary">
            ⚙️ Ubah Info Toko &amp; WhatsApp
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function quickToggleItemStock(id, btn) {
        if (btn.disabled) return;
        btn.disabled = true;
        try {
            const res = await fetch(`{{ url('/admin/items') }}/${id}/availability`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);
            const data = await res.json();
            if (data.success) {
                window.showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                throw new Error(data.message || 'Gagal mengubah status stok.');
            }
        } catch (e) {
            console.error(e);
            window.showToast('Gagal mengubah status stok.', 'error');
            btn.disabled = false;
        }
    }

    async function quickToggleCut(id, badge) {
        if (badge.disabled) return;
        badge.disabled = true;
        try {
            const res = await fetch(`{{ url('/admin/cuts') }}/${id}/availability`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);
            const data = await res.json();
            if (data.success) {
                if (data.is_available) {
                    badge.className = 'badge badge-open';
                    badge.textContent = 'Tersedia';
                } else {
                    badge.className = 'badge badge-closed';
                    badge.textContent = 'Habis';
                }
                window.showToast(data.message, 'success');
            } else {
                throw new Error(data.message || 'Gagal mengubah status.');
            }
        } catch (e) {
            console.error(e);
            window.showToast('Gagal mengubah status potongan.', 'error');
        } finally {
            badge.disabled = false;
        }
    }

    async function quickToggleDrink(id, badge) {
        if (badge.disabled) return;
        badge.disabled = true;
        try {
            const res = await fetch(`{{ url('/admin/drinks') }}/${id}/availability`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);
            const data = await res.json();
            if (data.success) {
                if (data.is_available) {
                    badge.className = 'badge badge-open';
                    badge.textContent = 'Tersedia';
                } else {
                    badge.className = 'badge badge-closed';
                    badge.textContent = 'Habis';
                }
                window.showToast(data.message, 'success');
            } else {
                throw new Error(data.message || 'Gagal mengubah status.');
            }
        } catch (e) {
            console.error(e);
            window.showToast('Gagal mengubah status minuman.', 'error');
        } finally {
            badge.disabled = false;
        }
    }
</script>
@endpush
