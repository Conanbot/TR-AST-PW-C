@extends('layouts.admin')

@section('title', 'Laporan Promosi (Promotion Report) — Panel Admin Geprek GLO')

@section('content')
@include('admin.reports.partials.header', [
    'title' => 'Laporan Efektivitas Promosi & Voucher',
    'subtitle' => 'Analisis penggunaan promo, total potongan diskon yang diberikan, dan penetrasi pelanggan.',
    'type' => 'promotions',
    'period' => $period,
])

<div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);">
        <div class="text-xs text-muted">Total Diskon Diberikan</div>
        <div style="font-size:1.6rem;font-weight:800;color:var(--warning);margin-top:4px;">
            Rp{{ number_format($data['summary']['total_discount'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Akumulasi subsidi promo kepada pemesan</div>
    </div>

    <div class="card" style="padding:var(--space-md);">
        <div class="text-xs text-muted">Frekuensi Pemakaian Voucher</div>
        <div style="font-size:1.6rem;font-weight:800;margin-top:4px;">
            {{ number_format($data['summary']['total_usages'], 0, ',', '.') }} Kali
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Klaim berhasil saat proses checkout</div>
    </div>

    <div class="card" style="padding:var(--space-md);">
        <div class="text-xs text-muted">Promo Aktif Digunakan</div>
        <div style="font-size:1.6rem;font-weight:800;color:#2ecc71;margin-top:4px;">
            {{ $data['summary']['promos_used_count'] }} Kode Promo
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Varian promo dengan minimal 1 penggunaan</div>
    </div>
</div>

<section class="card" style="padding:var(--space-md);margin-bottom:var(--space-lg);overflow-x:auto;">
    <h3 style="margin-bottom:12px;">Performa Tiap Kode Promo</h3>
    <table class="table" style="width:100%;">
        <thead>
            <tr>
                <th>Kode Promo</th>
                <th style="text-align:right;">Frekuensi Dipakai</th>
                <th style="text-align:right;">Pelanggan Unik</th>
                <th style="text-align:right;">Total Nilai Diskon</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['breakdown'] as $promo)
                <tr>
                    <td>
                        <strong style="font-size:1.05rem;color:var(--primary);letter-spacing:0.5px;">{{ $promo->code }}</strong>
                    </td>
                    <td style="text-align:right;">
                        <strong>{{ $promo->usage_count }} kali</strong>
                    </td>
                    <td style="text-align:right;">
                        <span class="text-muted">{{ $promo->unique_customers }} pemesan</span>
                    </td>
                    <td style="text-align:right;">
                        <strong class="text-warning" style="color:var(--warning);">Rp{{ number_format($promo->total_discount, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:var(--space-md);color:var(--text-muted);">
                        Belum ada kode promo yang digunakan pada rentang waktu ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

<section class="card" style="padding:var(--space-md);overflow-x:auto;">
    <h3 style="margin-bottom:12px;">Riwayat Pemakaian Promo</h3>
    <table class="table" style="width:100%;font-size:0.9rem;">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Kode</th>
                <th>Order #</th>
                <th>Nomor Pemesan</th>
                <th>Diskon Diberikan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['usages'] as $usage)
                <tr>
                    <td>
                        <div>{{ $usage->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $usage->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        <span class="badge badge-promo">{{ $usage->code }}</span>
                    </td>
                    <td>
                        @if($usage->order)
                            <a href="{{ route('admin.orders.show', $usage->order) }}" style="text-decoration:none;font-weight:600;color:var(--text);">
                                {{ $usage->order->order_number }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        {{ $usage->customer_phone ?: ($usage->order?->customer_phone ?: '-') }}
                    </td>
                    <td>
                        <strong style="color:var(--warning);">Rp{{ number_format($usage->discount_amount, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:var(--space-lg);color:var(--text-muted);">
                        Tidak ada catatan penggunaan promo pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:var(--space-md);">
        {{ $data['usages']->links() }}
    </div>
</section>
@endsection
