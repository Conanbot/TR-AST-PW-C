@extends('layouts.admin')

@section('title', 'Laporan Mutasi & Stok Bahan Baku (Inventory Report) — Panel Admin Geprek GLO')

@section('content')
@include('admin.reports.partials.header', [
    'title' => 'Laporan Mutasi Stok & Bahan Baku',
    'subtitle' => 'Audit saldo bahan baku, ambang batas minimum stock, dan buku besar pergerakan (Stock Movements).',
    'type' => 'inventory',
    'period' => $period,
])

{{-- KPI Summary --}}
<div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--primary);">
        <div class="text-xs text-muted">Total Bahan Baku Terdaftar</div>
        <div style="font-size:1.6rem;font-weight:800;margin-top:4px;">
            {{ $data['summary']['total_materials'] }} Bahan
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Katalog raw materials aktif</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid {{ $data['summary']['low_stock_count'] > 0 ? '#e74c3c' : 'var(--ok)' }};">
        <div class="text-xs text-muted">Bahan Stok Kritis / Rendah</div>
        <div style="font-size:1.6rem;font-weight:800;color:{{ $data['summary']['low_stock_count'] > 0 ? '#e74c3c' : '#2ecc71' }};margin-top:4px;">
            {{ $data['summary']['low_stock_count'] }} Bahan
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Stok berada di bawah atau sama dengan batas minimum</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid #3498db;">
        <div class="text-xs text-muted">Total Entri Mutasi Stok</div>
        <div style="font-size:1.6rem;font-weight:800;margin-top:4px;">
            {{ $data['summary']['total_movements'] }} Mutasi
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Pergerakan tercatat pada periode ini</div>
    </div>
</div>

{{-- Movement by Type Summary Cards --}}
<div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);">
        <span class="text-xs text-muted">Stok Masuk (Stock In):</span>
        <div style="font-size:1.25rem;font-weight:700;color:#2ecc71;margin-top:4px;">
            +{{ number_format((float) ($data['by_type']['stock_in'] ?? 0), 2, ',', '.') }}
        </div>
        <small class="text-muted">Dari penerimaan barang (Goods Receiving) / manual</small>
    </div>
    <div class="card" style="padding:var(--space-md);">
        <span class="text-xs text-muted">Stok Keluar (Stock Out):</span>
        <div style="font-size:1.25rem;font-weight:700;color:#e74c3c;margin-top:4px;">
            -{{ number_format((float) ($data['by_type']['stock_out'] ?? 0), 2, ',', '.') }}
        </div>
        <small class="text-muted">Dari konsumsi otomatis pesanan preparing / waste</small>
    </div>
    <div class="card" style="padding:var(--space-md);">
        <span class="text-xs text-muted">Penyesuaian (Adjustment):</span>
        <div style="font-size:1.25rem;font-weight:700;color:var(--warning);margin-top:4px;">
            {{ number_format((float) ($data['by_type']['adjustment'] ?? 0), 2, ',', '.') }}
        </div>
        <small class="text-muted">Koreksi audit stock opname fisik</small>
    </div>
</div>

{{-- Current Stock Status Table --}}
<section class="card" style="padding:var(--space-md);margin-bottom:var(--space-lg);overflow-x:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h3>Katalog Stok Bahan Saat Ini</h3>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary btn-sm">Kelola di Inventory <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
    <table class="table" style="width:100%;font-size:0.9rem;">
        <thead>
            <tr>
                <th>Nama Bahan</th>
                <th>Kategori</th>
                <th>Satuan Pakai</th>
                <th style="text-align:right;">Stok Saat Ini</th>
                <th style="text-align:right;">Batas Minimum</th>
                <th>Status Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['materials'] as $material)
                @php
                    $isLow = (float) $material->current_stock <= (float) $material->minimum_stock;
                    $isZero = (float) $material->current_stock <= 0;
                @endphp
                <tr>
                    <td><strong>{{ $material->name }}</strong></td>
                    <td><span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));">{{ $material->category }}</span></td>
                    <td>{{ $material->usage_unit }}</td>
                    <td style="text-align:right;">
                        <strong style="color:{{ $isZero ? '#e74c3c' : ($isLow ? 'var(--warning)' : 'inherit') }};">
                            {{ number_format((float)$material->current_stock, 2, ',', '.') }}
                        </strong>
                    </td>
                    <td style="text-align:right;">
                        {{ number_format((float)$material->minimum_stock, 2, ',', '.') }}
                    </td>
                    <td>
                        @if($isZero)
                            <span class="badge badge-closed" style="background:rgba(231,76,60,0.15);color:#e74c3c;">Habis</span>
                        @elseif($isLow)
                            <span class="badge badge-promo">Menipis</span>
                        @else
                            <span class="badge badge-open">Aman</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>

{{-- Stock Movements Ledger --}}
<section class="card" style="padding:var(--space-md);overflow-x:auto;">
    <h3 style="margin-bottom:12px;">Buku Besar Mutasi Stok (Ledger)</h3>
    <table class="table" style="width:100%;font-size:0.9rem;">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Bahan Baku</th>
                <th>Tipe</th>
                <th style="text-align:right;">Perubahan</th>
                <th style="text-align:right;">Saldo Akhir</th>
                <th>Alasan / Referensi</th>
                <th>Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['movements'] as $movement)
                @php
                    $delta = (float) $movement->quantity_delta;
                    $isPositive = $delta > 0;
                @endphp
                <tr>
                    <td>
                        <div>{{ $movement->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $movement->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        <strong>{{ $movement->rawMaterial?->name ?: '-' }}</strong>
                    </td>
                    <td>
                        <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));font-size:0.75rem;">
                            {{ strtoupper($movement->movement_type) }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <strong style="color:{{ $isPositive ? '#2ecc71' : '#e74c3c' }};">
                            {{ $isPositive ? '+' : '' }}{{ number_format($delta, 2, ',', '.') }} {{ $movement->unit }}
                        </strong>
                    </td>
                    <td style="text-align:right;">
                        {{ number_format((float) $movement->balance_after, 2, ',', '.') }} {{ $movement->unit }}
                    </td>
                    <td>
                        {{ $movement->reason ?: ($movement->reference_type ? class_basename($movement->reference_type) . ' #' . $movement->reference_id : '-') }}
                    </td>
                    <td>
                        <small class="text-muted">{{ $movement->user?->name ?: 'Sistem' }}</small>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:var(--space-lg);color:var(--text-muted);">
                        Belum ada mutasi stok pada periode tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:var(--space-md);">
        {{ $data['movements']->links() }}
    </div>
</section>
@endsection
