@extends('layouts.admin')

@section('title', 'Laporan Pembayaran (Payment Report) — Panel Admin Geprek GLO')

@section('content')
@include('admin.reports.partials.header', [
    'title' => 'Laporan Transaksi Pembayaran',
    'subtitle' => 'Audit penerimaan dana berdasarkan metode (Cash, QRIS, COD) dan verifikasi status pembayaran.',
    'type' => 'payments',
    'period' => $period,
])

{{-- KPI Summary --}}
<div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--ok);">
        <div class="text-xs text-muted">Total Pembayaran Berhasil (Paid)</div>
        <div style="font-size:1.5rem;font-weight:800;color:#2ecc71;margin-top:4px;">
            Rp{{ number_format($data['summary']['total_paid_amount'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">{{ $data['summary']['total_paid_count'] }} transaksi terverifikasi</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--warning);">
        <div class="text-xs text-muted">Menunggu Verifikasi / Pending</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--warning);margin-top:4px;">
            {{ $data['summary']['total_pending_count'] }} Transaksi
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">QRIS pending atau menunggu kasir</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--primary);">
        <div class="text-xs text-muted">Total Transaksi Masuk</div>
        <div style="font-size:1.5rem;font-weight:800;margin-top:4px;">
            {{ $data['payments']->total() }} Transaksi
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Keseluruhan percobaan pembayaran</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid #e74c3c;">
        <div class="text-xs text-muted">Pembayaran Ditolak</div>
        <div style="font-size:1.5rem;font-weight:800;color:#e74c3c;margin-top:4px;">
            {{ $data['summary']['total_rejected_count'] }} Transaksi
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Bukti QRIS tidak valid / ditolak</div>
    </div>
</div>

{{-- Method Breakdown Cards --}}
<div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-bottom:var(--space-lg);">
    @forelse($data['by_method'] as $method)
        @php
            $methodName = match($method->method_code) {
                'cash_pickup' => 'Cash on Pickup',
                'qris' => 'QRIS Dinamis / Statis',
                'cod' => 'Cash on Delivery (COD)',
                default => strtoupper($method->method_code)
            };
        @endphp
        <div class="card" style="padding:var(--space-md);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <strong>{{ $methodName }}</strong>
                <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));">{{ $method->total_count }} Transaksi</span>
            </div>
            <div style="font-size:1.3rem;font-weight:700;color:#2ecc71;">
                Rp{{ number_format($method->paid_amount, 0, ',', '.') }}
            </div>
            <div class="text-xs text-muted" style="display:flex;gap:12px;margin-top:8px;">
                <span>✓ {{ $method->paid_count }} Berhasil</span>
                <span>⏳ {{ $method->pending_count }} Pending</span>
                @if($method->rejected_count > 0)
                    <span style="color:#e74c3c;">✕ {{ $method->rejected_count }} Ditolak</span>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="grid-column:1/-1;padding:var(--space-md);text-align:center;color:var(--text-muted);">
            Belum ada rincian metode pembayaran pada periode ini.
        </div>
    @endforelse
</div>

{{-- Transaction Table --}}
<section class="card" style="padding:var(--space-md);overflow-x:auto;">
    <h3 style="margin-bottom:12px;">Riwayat Transaksi Pembayaran</h3>
    <table class="table" style="width:100%;font-size:0.9rem;">
        <thead>
            <tr>
                <th>Referensi</th>
                <th>Order #</th>
                <th>Pelanggan</th>
                <th>Metode</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Waktu Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['payments'] as $payment)
                <tr>
                    <td>
                        <strong style="font-family:monospace;">{{ $payment->payment_reference }}</strong>
                    </td>
                    <td>
                        @if($payment->order)
                            <a href="{{ route('admin.orders.show', $payment->order) }}" style="text-decoration:none;font-weight:600;color:var(--text);">
                                {{ $payment->order->order_number }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        {{ $payment->order?->customer_name ?: '-' }}
                    </td>
                    <td>
                        <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));">
                            {{ match($payment->method_code) { 'cash_pickup' => 'Cash', 'qris' => 'QRIS', 'cod' => 'COD', default => $payment->method_code } }}
                        </span>
                    </td>
                    <td>
                        <strong class="text-primary">Rp{{ number_format($payment->amount, 0, ',', '.') }}</strong>
                    </td>
                    <td>
                        <span class="badge {{ $payment->status === 'paid' ? 'badge-open' : ($payment->status === 'rejected' ? 'badge-closed' : 'badge-promo') }}" style="font-size:0.75rem;">
                            {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                        </span>
                    </td>
                    <td>
                        <div>{{ $payment->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:var(--space-lg);color:var(--text-muted);">
                        Belum ada transaksi pembayaran pada rentang tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:var(--space-md);">
        {{ $data['payments']->links() }}
    </div>
</section>
@endsection
