@extends('layouts.admin')

@section('title', 'Orders — Geprek GLO')

@section('content')
<div class="page-heading">
    <div><span class="eyebrow">Order management</span><h1>Semua pesanan</h1><p class="text-muted">Cari, filter, dan proses pesanan dari satu tempat.</p></div>
</div>

<form method="GET" class="card flex gap-sm" style="padding: 16px; flex-wrap: wrap; margin-bottom: 20px;">
    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Nomor pesanan" style="flex: 1 1 180px;">
    <select class="form-control" name="status"><option value="">Semua status</option>@foreach(['pending','confirmed','preparing','ready','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
    <select class="form-control" name="service_method"><option value="">Semua layanan</option><option value="delivery" @selected(request('service_method') === 'delivery')>Delivery</option><option value="takeaway" @selected(request('service_method') === 'takeaway')>Pickup</option><option value="dine_in" @selected(request('service_method') === 'dine_in')>Dine in</option></select>
    <input class="form-control" type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Tanggal mulai">
    <input class="form-control" type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Tanggal akhir">
    <button class="btn btn-primary" type="submit">Filter</button>
</form>

<div class="card" style="overflow-x: auto;">
    <table class="data-table">
        <thead><tr><th>Order</th><th>Pelanggan</th><th>Waktu</th><th>Layanan</th><th>Item</th><th>Total</th><th>Pembayaran</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                <td><strong>{{ $order->order_number }}</strong></td>
                <td>{{ $order->customer_name }}<br><small class="text-muted">{{ $order->customer_phone }}</small></td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $order->service_method)) }}</td>
                <td>{{ $order->items_count }}</td>
                <td>{{ $order->formatted_total }}</td>
                <td>{{ ucfirst($order->payment_status ?? 'unpaid') }}</td>
                <td><span class="badge badge-promo">{{ ucfirst($order->status) }}</span></td>
                <td><a class="btn btn-secondary btn-sm" href="{{ route('admin.orders.show', $order) }}">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-muted">Belum ada pesanan sesuai filter.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top: 16px;">{{ $orders->links() }}</div>
@endsection
