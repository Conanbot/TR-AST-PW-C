@extends('layouts.app')

@section('title', 'Lacak Pesanan ' . $order->order_number . ' — Geprek GLO')

@section('content')
<section class="home-section">
    <div class="container" style="max-width: 760px;">
        <div class="home-kicker">Pesanan {{ $order->order_number }}</div>
        <h1 style="margin: 14px 0 8px;">Status pesanan Anda</h1>
        <p class="text-muted">Halaman ini hanya dapat diakses melalui tautan aman pesanan Anda.</p>

        <div class="card" style="padding: var(--space-lg); margin-top: var(--space-xl);">
            <div class="flex justify-between items-center" style="gap: 16px; margin-bottom: var(--space-lg);">
                <div>
                    <span class="text-xs text-muted">Status</span>
                    <h2 style="margin-top: 4px; text-transform: capitalize;">{{ $order->status }}</h2>
                </div>
                <span class="badge {{ in_array($order->status, ['completed', 'ready']) ? 'badge-open' : ($order->status === 'cancelled' ? 'badge-closed' : 'badge-promo') }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <div class="flex flex-col gap-sm">
                @foreach($order->items as $item)
                    <div class="flex justify-between" style="gap: 16px; border-top: 1px solid var(--border); padding-top: 10px;">
                        <span>{{ $item->quantity }}x {{ $item->item_name }}</span>
                        <strong>{{ $item->formatted_line_total ?? 'Rp' . number_format($item->line_total, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between" style="border-top: 1px solid var(--border); margin-top: 16px; padding-top: 16px;">
                <strong>Total</strong>
                <strong class="text-primary">{{ $order->formatted_total }}</strong>
            </div>
        </div>
        <div class="card" style="padding: var(--space-lg); margin-top: var(--space-lg);">
            <h2>Perjalanan pesanan</h2>
            <ol class="stack-sm" style="margin-top: 16px; padding-left: 20px;">
                @foreach(['pending' => 'Pesanan diterima', 'confirmed' => 'Pesanan dikonfirmasi', 'preparing' => 'Sedang diproses', 'ready' => 'Siap diambil/diantar', 'completed' => 'Pesanan selesai', 'cancelled' => 'Pesanan dibatalkan'] as $status => $label)
                    @php
                        $reached = $order->status === $status || $order->statusHistories->contains('to_status', $status);
                    @endphp
                    <li style="{{ $reached ? 'font-weight: 700; color: var(--primary);' : 'color: var(--text-muted);' }}">{{ $label }}</li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
@endsection
