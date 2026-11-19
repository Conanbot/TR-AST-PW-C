@extends('layouts.admin')

@section('title', 'Detail ' . $order->order_number . ' — Geprek GLO')

@section('content')
<div class="page-heading">
    <div><a href="{{ route('admin.orders.index') }}" class="text-muted">← Semua pesanan</a><h1>{{ $order->order_number }}</h1><p class="text-muted">{{ $order->created_at->format('d F Y, H:i') }} · {{ ucfirst(str_replace('_', ' ', $order->service_method)) }}</p></div>
    <span class="badge badge-promo">{{ ucfirst($order->status) }}</span>
</div>

<div class="grid grid-2 gap-lg">
    <section class="card" style="padding: var(--space-lg);">
        <h2>Rincian pesanan</h2>
        <div class="stack-md" style="margin-top: 16px;">
            @foreach($order->items as $item)
                <div style="border-top: 1px solid var(--border); padding-top: 12px;">
                    <div class="flex justify-between"><strong>{{ $item->quantity }}x {{ $item->item_name }}</strong><strong>Rp{{ number_format($item->line_total, 0, ',', '.') }}</strong></div>
                    @if($item->variant_name)<div class="text-muted text-xs">Varian: {{ $item->variant_name }}</div>@endif
                    @if($item->modifiers->isNotEmpty())<div class="text-muted text-xs">Pilihan: {{ $item->modifiers->map(fn($modifier) => $modifier->group_name . ': ' . $modifier->option_name)->implode(', ') }}</div>@endif
                    @if($item->note)<div class="text-muted text-xs">Catatan: {{ $item->note }}</div>@endif
                </div>
            @endforeach
            <div class="flex justify-between"><span>Subtotal</span><strong>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</strong></div>
            <div class="flex justify-between"><span>Diskon</span><span>-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Biaya antar</span><span>Rp{{ number_format($order->delivery_fee, 0, ',', '.') }}</span></div>
            <div class="flex justify-between" style="border-top: 1px solid var(--border); padding-top: 12px;"><strong>Total</strong><strong class="text-primary">{{ $order->formatted_total }}</strong></div>
        </div>
    </section>
    <section class="card" style="padding: var(--space-lg);">
        <h2>Pelanggan</h2>
        <dl class="stack-sm" style="margin-top: 16px;">
            <div><dt class="text-muted text-xs">Nama</dt><dd>{{ $order->customer_name }}</dd></div>
            <div><dt class="text-muted text-xs">WhatsApp</dt><dd>{{ $order->customer_phone ?: 'Tidak diisi' }}</dd></div>
            <div><dt class="text-muted text-xs">Alamat</dt><dd>{{ $order->customer_address ?: 'Tidak berlaku' }}</dd></div>
            <div><dt class="text-muted text-xs">Catatan</dt><dd>{{ $order->order_notes ?: 'Tidak ada' }}</dd></div>
            @if($order->customer_id && auth()->user()?->role === 'admin')
                <div style="margin-top: 10px;">
                    <a href="{{ route('admin.customers.show', $order->customer_id) }}" class="btn btn-secondary btn-sm">Lihat Profil Pelanggan</a>
                </div>
            @endif
        </dl>
        @if(!in_array($order->status, ['completed', 'cancelled']))
            <form method="POST" action="{{ route('admin.orders.transition', $order) }}" style="margin-top: 20px;" class="stack-sm">
                @csrf @method('PATCH')
                <select class="form-control" name="status" required><option value="">Pilih aksi</option><option value="confirmed">Confirm Order</option><option value="preparing">Start Preparing</option><option value="ready">Mark as Ready</option><option value="completed">Complete Order</option><option value="cancelled">Cancel Order</option></select>
                <input class="form-control" name="cancellation_reason" placeholder="Alasan pembatalan (wajib saat cancel)">
                <button class="btn btn-primary" type="submit">Simpan status</button>
            </form>
        @endif
    </section>
</div>

<section class="card" style="padding: var(--space-lg); margin-top: var(--space-lg);">
    <h2>Riwayat status</h2>
    <div class="stack-sm" style="margin-top: 16px;">
        @forelse($order->statusHistories->sortByDesc('created_at') as $history)
            <div class="flex justify-between" style="border-top: 1px solid var(--border); padding-top: 10px; gap: 12px;">
                <span>{{ ucfirst($history->from_status) }} → <strong>{{ ucfirst($history->to_status) }}</strong>@if($history->note) · {{ $history->note }}@endif</span>
                <small class="text-muted">{{ $history->created_at->format('d/m H:i') }}</small>
            </div>
        @empty
            <p class="text-muted">Belum ada perubahan status.</p>
        @endforelse
    </div>
</section>
@endsection
