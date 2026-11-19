@extends('layouts.admin')

@section('title', 'Detail Pelanggan: ' . $customer->name . ' — Panel Admin Geprek GLO')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:var(--space-lg);flex-wrap:wrap;">
    <div>
        <div class="text-xs text-muted" style="margin-bottom:4px;">
            <a href="{{ route('admin.customers.index') }}" class="text-muted" style="text-decoration:none;">← Kembali ke Direktori Pelanggan</a>
        </div>
        <h1 style="display:inline-flex;align-items:center;gap:10px;margin:0;">
            {{ $customer->name }}
            <span class="badge {{ $customer->status === 'active' ? 'badge-open' : 'badge-closed' }}" style="font-size:0.55em;vertical-align:middle;">
                {{ $customer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
            </span>
        </h1>
        <p class="text-muted" style="margin-top:4px;">
            Tercatat sejak {{ $customer->created_at->format('d F Y, H:i') }} · ID #{{ $customer->id }}
        </p>
    </div>
    <div style="display:flex;gap:8px;">
        <form method="POST" action="{{ route('admin.customers.toggle', $customer) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-secondary btn-sm">
                {{ $customer->status === 'active' ? 'Nonaktifkan Pelanggan' : 'Aktifkan Pelanggan' }}
            </button>
        </form>
    </div>
</div>

{{-- Summary KPI Cards --}}
<div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);">
        <div class="text-xs text-muted">Total Belanja (Paid)</div>
        <div style="font-size:1.5rem;font-weight:700;color:var(--primary);margin-top:4px;">
            {{ $statistics['formatted_total_spending'] }}
        </div>
        <div class="text-xs text-muted" style="margin-top:4px;">Hanya transaksi selesai/berbayar</div>
    </div>
    <div class="card" style="padding:var(--space-md);">
        <div class="text-xs text-muted">Rata-rata Nilai Pesanan (AOV)</div>
        <div style="font-size:1.5rem;font-weight:700;margin-top:4px;">
            {{ $statistics['formatted_average_order_value'] }}
        </div>
        <div class="text-xs text-muted" style="margin-top:4px;">Per transaksi berhasil</div>
    </div>
    <div class="card" style="padding:var(--space-md);">
        <div class="text-xs text-muted">Total Pesanan</div>
        <div style="font-size:1.5rem;font-weight:700;margin-top:4px;">
            {{ $statistics['total_orders'] }}
        </div>
        <div class="text-xs text-muted" style="margin-top:4px;">
            <span style="color:#10b981;">✓ {{ $statistics['completed_orders'] }} selesai</span> ·
            <span style="color:#ef4444;">✕ {{ $statistics['cancelled_orders'] }} dibatalkan</span>
        </div>
    </div>
    <div class="card" style="padding:var(--space-md);">
        <div class="text-xs text-muted">Pesanan Terakhir</div>
        <div style="font-size:1.15rem;font-weight:600;margin-top:4px;">
            {{ $statistics['last_order_date'] ? $statistics['last_order_date']->format('d/m/Y H:i') : 'Belum ada pesanan' }}
        </div>
        <div class="text-xs text-muted" style="margin-top:4px;">
            Order pertama: {{ $statistics['first_order_date'] ? $statistics['first_order_date']->format('d/m/Y') : '-' }}
        </div>
    </div>
</div>

<div class="grid grid-cols-1 grid-cols-2 gap-lg" style="align-items:start;">
    {{-- Edit Customer Profile Form --}}
    <section class="card" style="padding:var(--space-lg);">
        <h2 style="font-size:1.2rem;margin-bottom:12px;">Profil & Catatan Pelanggan</h2>
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" style="display:grid;gap:12px;">
            @csrf
            @method('PUT')

            <div>
                <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Nama Lengkap *</label>
                <input class="form-input" name="name" value="{{ old('name', $customer->name) }}" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Nomor Telepon (Asli) *</label>
                    <input class="form-input" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                </div>
                <div>
                    <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Nomor Ternormalisasi</label>
                    <input class="form-input" value="{{ $customer->normalized_phone }}" readonly style="background:var(--surface-soft, rgba(0,0,0,0.03));cursor:not-allowed;">
                </div>
            </div>

            <div>
                <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Email (Opsional)</label>
                <input class="form-input" type="email" name="email" value="{{ old('email', $customer->email) }}">
            </div>

            <div>
                <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Status Akun</label>
                <select class="form-input" name="status" required>
                    <option value="active" @selected(old('status', $customer->status) === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div>
                <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Catatan Internal Admin (Opsional)</label>
                <textarea class="form-input" name="notes" rows="4" placeholder="Misal: langganan porsi pedas level 5, alergi tertentu, catatan pengantaran...">{{ old('notes', $customer->notes) }}</textarea>
                <small class="text-xs text-muted">Catatan ini hanya terlihat oleh staf internal dan tidak tampak di sisi pemesan.</small>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:6px;">Simpan Perubahan Profil</button>
        </form>
    </section>

    {{-- Transaction History --}}
    <section class="card" style="padding:var(--space-lg);overflow-x:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h2 style="font-size:1.2rem;margin:0;">Riwayat Transaksi</h2>
            <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));">
                {{ $orders->total() }} pesanan tercatat
            </span>
        </div>

        <table class="table" style="width:100%;font-size:0.9rem;">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Waktu</th>
                    <th>Layanan & Item</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Bayar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" style="font-weight:600;text-decoration:none;color:var(--text);">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $order->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <div><strong>{{ ucfirst(str_replace('_', ' ', $order->service_method)) }}</strong></div>
                            <small class="text-muted">{{ $order->items_count }} item</small>
                        </td>
                        <td>
                            <strong>{{ $order->formatted_total }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-promo" style="font-size:0.75rem;">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $order->payment_status === 'paid' ? 'badge-open' : 'badge-closed' }}" style="font-size:0.75rem;">
                                {{ ucfirst($order->payment_status ?? 'unpaid') }}
                            </span>
                            <div class="text-xs text-muted">{{ strtoupper($order->payment_method ?? '-') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-sm" title="Buka Detail Order">
                                Buka
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:var(--space-md);color:var(--text-muted);">
                            Pelanggan ini belum memiliki riwayat transaksi order.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:var(--space-md);">
            {{ $orders->links() }}
        </div>
    </section>
</div>
@endsection
