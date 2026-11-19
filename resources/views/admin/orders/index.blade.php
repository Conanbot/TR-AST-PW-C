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

{{-- Filter Bar --}}
<form method="GET" class="card" style="padding:16px;margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:1 1 160px;">
        <label class="form-label text-xs">Cari nomor / nama</label>
        <input class="form-input" name="search" value="{{ request('search') }}" placeholder="Nomor pesanan atau nama...">
    </div>
    <div style="flex:0 0 150px;">
        <label class="form-label text-xs">Status</label>
        <select class="form-input" name="status">
            <option value="">Semua Status</option>
            @foreach(['pending','confirmed','preparing','ready','completed','cancelled'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:0 0 140px;">
        <label class="form-label text-xs">Layanan</label>
        <select class="form-input" name="service_method">
            <option value="">Semua</option>
            <option value="dine_in" @selected(request('service_method') === 'dine_in')>Dine In</option>
            <option value="takeaway" @selected(request('service_method') === 'takeaway')>Takeaway</option>
            <option value="delivery" @selected(request('service_method') === 'delivery')>Delivery</option>
        </select>
    </div>
    <div style="flex:0 0 140px;">
        <label class="form-label text-xs">Dari tanggal</label>
        <input class="form-input" type="date" name="date_from" value="{{ request('date_from') }}">
    </div>
    <div style="flex:0 0 140px;">
        <label class="form-label text-xs">Sampai tanggal</label>
        <input class="form-input" type="date" name="date_to" value="{{ request('date_to') }}">
    </div>
    <div style="display:flex;gap:8px;align-items:flex-end;">
        <button class="btn btn-primary" type="submit">Filter</button>
        @if(request()->hasAny(['search','status','service_method','date_from','date_to']))
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </div>
</form>

{{-- Status filter pills --}}
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;">
    @php
        $pills = [
            '' => 'Semua',
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        $currentStatus = request('status', '');
    @endphp
    @foreach($pills as $val => $label)
        <a href="{{ route('admin.orders.index', array_merge(request()->except('status','page'), $val ? ['status' => $val] : [])) }}"
           class="btn btn-sm {{ $currentStatus === $val ? 'btn-primary' : 'btn-secondary' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

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
                $statusColors = [
                    'pending'   => ['bg'=>'#f39c1222','text'=>'#f39c12','border'=>'#f39c1244'],
                    'confirmed' => ['bg'=>'#3498db22','text'=>'#3498db','border'=>'#3498db44'],
                    'preparing' => ['bg'=>'#9b59b622','text'=>'#9b59b6','border'=>'#9b59b644'],
                    'ready'     => ['bg'=>'#2ecc7122','text'=>'#2ecc71','border'=>'#2ecc7144'],
                    'completed' => ['bg'=>'#27ae6022','text'=>'#27ae60','border'=>'#27ae6044'],
                    'cancelled' => ['bg'=>'#e74c3c22','text'=>'#e74c3c','border'=>'#e74c3c44'],
                ];
                $sc = $statusColors[$order->status] ?? ['bg'=>'var(--surface)','text'=>'var(--text-muted)','border'=>'var(--border)'];

                $payColors = [
                    'paid'   => '#2ecc71',
                    'unpaid' => '#f39c12',
                ];
                $pc = $payColors[$order->payment_status] ?? 'var(--text-muted)';
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
                        <a href="{{ route('admin.customers.show', $order->customer_id) }}"
                           style="color:var(--text);font-weight:600;text-decoration:underline;">{{ $order->customer_name }}</a>
                    @else
                        <strong style="font-size:0.9rem;">{{ $order->customer_name }}</strong>
                    @endif
                    @if($order->customer_phone)
                        <div class="text-xs text-muted">{{ $order->customer_phone }}</div>
                    @endif
                </td>
                <td class="text-xs text-muted">{{ $order->created_at->format('d/m/Y') }}<br>{{ $order->created_at->format('H:i') }}</td>
                <td class="text-xs">{{ ucfirst(str_replace('_',' ',$order->service_method)) }}</td>
                <td style="text-align:center;font-weight:700;">{{ $order->items_count }}</td>
                <td><strong style="color:var(--primary);">{{ $order->formatted_total }}</strong></td>
                <td>
                    <span style="font-size:0.78rem;font-weight:700;color:{{ $pc }};">
                        {{ ucfirst($order->payment_status ?? 'unpaid') }}
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border:1px solid {{ $sc['border'] }};font-size:0.75rem;white-space:nowrap;">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>
                    <a class="btn btn-secondary btn-sm" href="{{ route('admin.orders.show', $order) }}">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">
                    <div style="font-size:1.5rem;margin-bottom:8px;">📋</div>
                    Belum ada pesanan sesuai filter.
                    @if(request()->hasAny(['search','status','service_method','date_from','date_to']))
                        <div style="margin-top:8px;"><a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">Reset filter</a></div>
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $orders->links() }}
</div>
@endsection
