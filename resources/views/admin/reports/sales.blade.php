@extends('layouts.admin')

@section('title', 'Laporan Penjualan (Sales Report) — Panel Admin Geprek GLO')

@section('content')
@include('admin.reports.partials.header', [
    'title' => 'Laporan Penjualan',
    'subtitle' => 'Ringkasan omset penjualan, potongan diskon, pendapatan pembayaran diterima, dan riwayat transaksi.',
    'type' => 'sales',
    'period' => $period,
])

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--ok);">
        <div class="text-xs text-muted">Pendapatan Berhasil (Paid)</div>
        <div style="font-size:1.5rem;font-weight:800;color:#2ecc71;margin-top:4px;">
            Rp{{ number_format($data['summary']['paid_revenue'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Uang transaksi yang telah diterima</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--primary);">
        <div class="text-xs text-muted">Net Sales (Semua Order Valid)</div>
        <div style="font-size:1.5rem;font-weight:800;margin-top:4px;">
            Rp{{ number_format($data['summary']['net_sales'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Gross - diskon (non-cancelled)</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--warning);">
        <div class="text-xs text-muted">Total Diskon Promo</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--warning);margin-top:4px;">
            -Rp{{ number_format($data['summary']['total_discount'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Gross: Rp{{ number_format($data['summary']['gross_sales'], 0, ',', '.') }}</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid #3498db;">
        <div class="text-xs text-muted">Average Order Value (AOV)</div>
        <div style="font-size:1.5rem;font-weight:800;margin-top:4px;">
            Rp{{ number_format($data['summary']['aov'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Per transaksi terbayar</div>
    </div>
</div>

<div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-sm) var(--space-md);">
        <span class="text-xs text-muted">Total Volume Order:</span>
        <strong style="font-size:1.1rem;display:block;">{{ $data['summary']['total_orders'] }} Pesanan</strong>
    </div>
    <div class="card" style="padding:var(--space-sm) var(--space-md);">
        <span class="text-xs text-muted">Order Selesai (Completed):</span>
        <strong style="font-size:1.1rem;display:block;color:#10b981;"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $data['summary']['completed_orders'] }} Pesanan</strong>
    </div>
    <div class="card" style="padding:var(--space-sm) var(--space-md);">
        <span class="text-xs text-muted">Order Dibatalkan (Cancelled):</span>
        <strong style="font-size:1.1rem;display:block;color:#ef4444;"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> {{ $data['summary']['cancelled_orders'] }} Pesanan</strong>
    </div>
    <div class="card" style="padding:var(--space-sm) var(--space-md);">
        <span class="text-xs text-muted">Unpaid / Pending Amount:</span>
        <strong style="font-size:1.1rem;display:block;color:var(--warning);">Rp{{ number_format($data['summary']['unpaid_amount'], 0, ',', '.') }}</strong>
    </div>
</div>

{{-- Detail Orders Table --}}
<section class="card" style="padding:var(--space-md);overflow-x:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h3>Rincian Transaksi Pesanan ({{ $data['orders']->total() }})</h3>
    </div>

    <table class="table" style="width:100%;font-size:0.9rem;">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Tanggal & Waktu</th>
                <th>Pelanggan</th>
                <th>Layanan</th>
                <th>Subtotal</th>
                <th>Diskon</th>
                <th>Total</th>
                <th>Status Order</th>
                <th>Status Bayar</th>
                <th>Metode</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['orders'] as $order)
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
                        <div>{{ $order->customer_name }}</div>
                        <small class="text-muted">{{ $order->customer_phone ?: '-' }}</small>
                    </td>
                    <td>
                        {{ ucfirst(str_replace('_', ' ', $order->service_method)) }}
                        <div class="text-xs text-muted">{{ $order->items_count }} item</div>
                    </td>
                    <td>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    <td>
                        @if($order->discount_amount > 0)
                            <span style="color:var(--warning);">-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td><strong>{{ $order->formatted_total }}</strong></td>
                    <td>
                        <span class="badge badge-promo" style="font-size:0.75rem;">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $order->payment_status === 'paid' ? 'badge-open' : 'badge-closed' }}" style="font-size:0.75rem;">
                            {{ ucfirst($order->payment_status ?? 'unpaid') }}
                        </span>
                    </td>
                    <td>
                        <span class="text-xs">{{ strtoupper($order->payment_method ?? '-') }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-sm" title="Buka Detail Order">
                            Lihat
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;padding:var(--space-lg);color:var(--text-muted);">
                        Tidak ada transaksi pesanan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:var(--space-md);">
        {{ $data['orders']->links() }}
    </div>
</section>
@endsection
