@extends('layouts.admin')

@section('title', 'Performa Menu & Produk (Product Sales) — Panel Admin Geprek GLO')

@section('content')
@include('admin.reports.partials.header', [
    'title' => 'Laporan Penjualan Produk & Menu',
    'subtitle' => 'Peringkat menu terlaris, unit terjual, dan nilai penjualan berdasarkan snapshot transaksi.',
    'type' => 'products',
    'period' => $period,
])

<div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--primary);">
        <div class="text-xs text-muted">Total Unit Terjual</div>
        <div style="font-size:1.6rem;font-weight:800;margin-top:4px;">
            {{ number_format($data['summary']['total_units'], 0, ',', '.') }} Unit
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Dari seluruh pesanan valid (non-cancelled)</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--ok);">
        <div class="text-xs text-muted">Total Penjualan Produk (Gross Item)</div>
        <div style="font-size:1.6rem;font-weight:800;color:#2ecc71;margin-top:4px;">
            Rp{{ number_format($data['summary']['total_revenue'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Total akumulasi line total item pesanan</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid #3498db;">
        <div class="text-xs text-muted">Variasi Menu Terjual</div>
        <div style="font-size:1.6rem;font-weight:800;margin-top:4px;">
            {{ $data['summary']['unique_products_sold'] }} Menu / Varian
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Produk dengan minimal 1 transaksi</div>
    </div>
</div>

<section class="card" style="padding:var(--space-md);overflow-x:auto;">
    <table class="table" style="width:100%;">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Menu &amp; Produk</th>
                <th>Varian</th>
                <th style="text-align:right;">Rata-rata Harga</th>
                <th style="text-align:right;">Unit Terjual</th>
                <th style="text-align:right;">Total Omset (Rp)</th>
                <th style="text-align:right;">Kontribusi</th>
            </tr>
        </thead>
        <tbody>
            @php $rankOffset = ($data['items']->currentPage() - 1) * $data['items']->perPage(); @endphp
            @forelse($data['items'] as $index => $item)
                @php
                    $percentage = $data['summary']['total_revenue'] > 0
                        ? round(($item->total_revenue / $data['summary']['total_revenue']) * 100, 1)
                        : 0;
                @endphp
                <tr>
                    <td>
                        <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));font-weight:700;">
                            {{ $rankOffset + $index + 1 }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                    </td>
                    <td>
                        @if($item->variant_name)
                            <span class="badge" style="background:rgba(52, 152, 219, 0.15);color:#3498db;">{{ $item->variant_name }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        Rp{{ number_format($item->avg_price, 0, ',', '.') }}
                    </td>
                    <td style="text-align:right;">
                        <strong style="font-size:1.05rem;">{{ number_format($item->total_quantity, 0, ',', '.') }}</strong>
                    </td>
                    <td style="text-align:right;">
                        <strong class="text-primary">Rp{{ number_format($item->total_revenue, 0, ',', '.') }}</strong>
                    </td>
                    <td style="text-align:right;">
                        <span class="text-xs" style="color:var(--text-muted);font-weight:600;">{{ $percentage }}%</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:var(--space-lg);color:var(--text-muted);">
                        Belum ada penjualan produk pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:var(--space-md);">
        {{ $data['items']->links() }}
    </div>
</section>
@endsection
