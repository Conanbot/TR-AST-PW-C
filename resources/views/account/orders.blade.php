@extends('layouts.account')

@section('title', 'Riwayat Pesanan — Geprek GLO')

@section('content')
<section class="account-welcome">
    <div>
        <span class="eyebrow">Riwayat transaksi</span>
        <h1>Riwayat Pesanan Saya</h1>
        <p>Semua pesanan yang Anda buat saat masuk ke akun tersimpan dan dapat dipantau di sini.</p>
    </div>
    <a href="{{ route('menu') }}" class="btn btn-primary">
        Pesan menu <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
</section>

<section class="account-orders">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">Daftar pembelian</span>
            <h2>Total Pesanan</h2>
        </div>
        <span class="count-badge">{{ $orders->total() }}</span>
    </div>

    @forelse($orders as $order)
        <article class="customer-order-card">
            <div class="customer-order-heading">
                <div>
                    <span class="order-number">{{ $order->order_number }}</span>
                    <span class="order-date">{{ $order->created_at->format('d M Y, H:i') }}</span>
                </div>
                <span class="order-status status-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
            </div>
            <div class="customer-order-items">
                @foreach($order->items as $item)
                    <div>
                        <span>{{ $item->quantity }}× {{ $item->item_name }}@if($item->variant_name) ({{ $item->variant_name }})@endif</span>
                        <strong>Rp{{ number_format($item->line_total, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
            <div class="customer-order-summary" style="flex-wrap: wrap; gap: 10px;">
                <span>
                    {{ $order->service_method === 'dine_in' ? 'Makan di tempat' : ($order->service_method === 'takeaway' ? 'Bawa pulang' : 'Pesan antar') }}
                    · {{ strtoupper($order->payment_method ?: 'cash') }}
                </span>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <strong>{{ $order->formatted_total }}</strong>
                    <a href="{{ route('account.orders.track', $order) }}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-route" aria-hidden="true"></i>
                        Lacak Pesanan
                    </a>
                </div>
            </div>
        </article>
    @empty
        <div class="empty-state account-empty">
            <span class="empty-state-mark" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
            <h3>Belum ada riwayat pesanan</h3>
            <p>Pesanan Anda akan tampil di sini setelah Anda checkout dari Katalog Menu.</p>
            <a href="{{ route('menu') }}" class="btn btn-primary">Lihat Katalog Menu</a>
        </div>
    @endforelse

    @if($orders->hasPages())
        <div class="pagination-wrap">{{ $orders->links() }}</div>
    @endif
</section>
@endsection
