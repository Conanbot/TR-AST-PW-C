@extends('layouts.account')

@section('title', 'Pesanan Saya — Geprek GLO')

@section('content')
<section class="account-welcome">
    <div>
        <span class="eyebrow">Akun pelanggan</span>
        <h1>Halo, {{ auth()->user()->name }}!</h1>
        <p>Pesanan yang dibuat saat masuk ke akun akan tersimpan di sini.</p>
    </div>
    <a href="{{ route('menu') }}" class="btn btn-primary">Pesan menu <span aria-hidden="true">→</span></a>
</section>

<section class="account-orders">
    <div class="panel-heading">
        <div><span class="eyebrow">Riwayat transaksi</span><h2>Pesanan saya</h2></div>
        <span class="count-badge">{{ $orders->total() }}</span>
    </div>

    @forelse($orders as $order)
        <article class="customer-order-card">
            <div class="customer-order-heading">
                <div><span class="order-number">{{ $order->order_number }}</span><span class="order-date">{{ $order->created_at->format('d M Y, H:i') }}</span></div>
                <span class="order-status status-{{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span>
            </div>
            <div class="customer-order-items">
                @foreach($order->items as $item)
                    <div><span>{{ $item->quantity }}× {{ $item->item_name }}</span><strong>Rp{{ number_format($item->line_total, 0, ',', '.') }}</strong></div>
                @endforeach
            </div>
            <div class="customer-order-summary">
                <span>{{ $order->service_method === 'dine_in' ? 'Makan di tempat' : ($order->service_method === 'takeaway' ? 'Bawa pulang' : 'Pesan antar') }}</span>
                <strong>{{ $order->formatted_total }}</strong>
            </div>
        </article>
    @empty
        <div class="empty-state account-empty">
            <span class="empty-state-mark" aria-hidden="true">＋</span>
            <h3>Belum ada pesanan</h3>
            <p>Pesanan Anda akan tampil di sini setelah checkout saat sudah masuk ke akun.</p>
            <a href="{{ route('menu') }}" class="btn btn-primary">Lihat menu</a>
        </div>
    @endforelse

    @if($orders->hasPages())
        <div class="pagination-wrap">{{ $orders->links() }}</div>
    @endif
</section>
@endsection
