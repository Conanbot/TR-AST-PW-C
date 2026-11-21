@extends('layouts.admin')

@section('title', 'Semua Pesanan — Geprek GLO')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Order Management</span>
        <h1>Semua Pesanan</h1>
        <p class="text-muted">Cari, filter, dan proses semua pesanan dari satu halaman.</p>
    </div>
</div>

<x-filter-panel :action="route('admin.orders.index')">
    <div class="fp-field fp-field-lg">
        <label class="fp-label">Cari nomor / nama</label>
        <input class="fp-input" name="search" value="{{ request('search') }}" placeholder="Nomor pesanan atau nama...">
    </div>
    <div class="fp-field fp-field-sm">
        <label class="fp-label">Status</label>
        <select class="fp-select" name="status">
            <option value="">Semua Status</option>
            @foreach(['pending','confirmed','preparing','ready','completed','cancelled'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
    <div class="fp-field fp-field-sm">
        <label class="fp-label">Layanan</label>
        <select class="fp-select" name="service_method">
            <option value="">Semua</option>
            <option value="dine_in"  @selected(request('service_method') === 'dine_in')>Dine In</option>
            <option value="takeaway" @selected(request('service_method') === 'takeaway')>Takeaway</option>
            <option value="delivery" @selected(request('service_method') === 'delivery')>Delivery</option>
        </select>
    </div>
    <div class="fp-field fp-field-sm">
        <label class="fp-label">Dari tanggal</label>
        <input class="fp-input" type="date" name="date_from" value="{{ request('date_from') }}">
    </div>
    <div class="fp-field fp-field-sm">
        <label class="fp-label">Sampai tanggal</label>
        <input class="fp-input" type="date" name="date_to" value="{{ request('date_to') }}">
    </div>
    <x-slot name="buttons">
        <button class="fp-btn-primary" type="submit">Filter</button>
        @if(request()->anyFilled(['search','status','service_method','date_from','date_to']))
            <a href="{{ route('admin.orders.index') }}" class="fp-btn-reset">Reset</a>
        @endif
    </x-slot>
</x-filter-panel>

<div class="card" style="overflow-x:auto;">
    <table class="table">
        <thead>
            <tr>
                <th>Pesanan</th>
                <th>Pelanggan</th>
                <th>Waktu</th>
                <th>Layanan</th>
                <th>Item</th>
                <th>Total</th>
                <th>Bayar</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
            @php
                $sc = match($order->status) {
                    'pending'   => ['bg'=>'#f39c1215','text'=>'#c87a0a','border'=>'#f39c1240'],
                    'confirmed' => ['bg'=>'#3498db15','text'=>'#1a6fa0','border'=>'#3498db40'],
                    'preparing' => ['bg'=>'#9b59b615','text'=>'#7a3f9d','border'=>'#9b59b640'],
                    'ready'     => ['bg'=>'#2ecc7115','text'=>'#1e8449','border'=>'#2ecc7140'],
                    'completed' => ['bg'=>'#27ae6015','text'=>'#1a7340','border'=>'#27ae6040'],
                    'cancelled' => ['bg'=>'#e74c3c15','text'=>'#c0392b','border'=>'#e74c3c40'],
                    default     => ['bg'=>'var(--surface)','text'=>'var(--text-muted)','border'=>'var(--border)'],
                };
                $pc = match($order->payment_status) {
                    'paid'   => '#1e8449',
                    'unpaid' => '#c87a0a',
                    default  => 'var(--text-muted)',
                };
            @endphp
            <tr>
                <td>
                    <strong style="font-size:0.9rem;">{{ $order->order_number }}</strong>
                    @if($order->table_number)
                        <div class="text-xs text-muted">Meja {{ $order->table_number }}</div>
                    @endif
                </td>
                <td>
                    @if($order->customer_id && auth()->user()?->role === 'admin')
                        <a href="{{ route('admin.customers.show', $order->customer_id) }}" style="color:var(--text);font-weight:600;text-decoration:underline;">{{ $order->customer_name }}</a>
                    @else
                        <strong style="font-size:0.9rem;">{{ $order->customer_name }}</strong>
                    @endif
                    @if($order->customer_phone)
                        <div class="text-xs text-muted">{{ $order->customer_phone }}</div>
                    @endif
                </td>
                <td class="text-xs text-muted" style="white-space:nowrap;">
                    {{ $order->created_at->format('d/m/Y') }}<br>{{ $order->created_at->format('H:i') }}
                </td>
                <td class="text-xs">{{ ucfirst(str_replace('_',' ',$order->service_method)) }}</td>
                <td style="text-align:center;font-weight:700;">{{ $order->items_count }}</td>
                <td><strong style="color:var(--primary);">{{ $order->formatted_total }}</strong></td>
                <td>
                    <span style="font-size:0.78rem;font-weight:700;color:{{ $pc }};">
                        {{ ucfirst($order->payment_status ?? 'unpaid') }}
                    </span>
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:0.74rem;font-weight:700;white-space:nowrap;background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border:1px solid {{ $sc['border'] }};">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.orders.show', $order) }}">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:48px 20px;color:var(--text-muted);">
                    <div style="font-size:1.5rem;margin-bottom:8px;opacity:0.4;"><i class="fa-solid fa-receipt" aria-hidden="true"></i></div>
                    <strong>Belum ada pesanan sesuai filter.</strong>
                    @if(request()->anyFilled(['search','status','service_method','date_from','date_to']))
                        <div style="margin-top:12px;">
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">Reset filter</a>
                        </div>
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">{{ $orders->links() }}</div>
@endsection
