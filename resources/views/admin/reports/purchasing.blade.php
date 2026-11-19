@extends('layouts.admin')

@section('title', 'Laporan Pengadaan & Pembelian (Purchasing Report) — Panel Admin Geprek GLO')

@section('content')
@include('admin.reports.partials.header', [
    'title' => 'Laporan Pengadaan & Penerimaan Barang',
    'subtitle' => 'Status Purchase Order (PO), audit penerimaan aktual (Goods Receiving), dan total belanja supplier.',
    'type' => 'purchasing',
    'period' => $period,
])

{{-- KPI Summary --}}
<div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--ok);">
        <div class="text-xs text-muted">Belanja Riil Barang Diterima</div>
        <div style="font-size:1.5rem;font-weight:800;color:#2ecc71;margin-top:4px;">
            Rp{{ number_format($data['summary']['total_received_spend'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Dari surat penerimaan (Goods Receiving)</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--primary);">
        <div class="text-xs text-muted">Komitmen Nilai PO</div>
        <div style="font-size:1.5rem;font-weight:800;margin-top:4px;">
            Rp{{ number_format($data['summary']['total_po_value'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Total order status ordered/received</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid #3498db;">
        <div class="text-xs text-muted">Surat Penerimaan (Receivings)</div>
        <div style="font-size:1.5rem;font-weight:800;margin-top:4px;">
            {{ $data['summary']['total_receivings'] }} Penerimaan
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Aktivitas bongkar muat bahan baku</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--warning);">
        <div class="text-xs text-muted">Total Purchase Order (PO)</div>
        <div style="font-size:1.5rem;font-weight:800;margin-top:4px;">
            {{ $data['summary']['total_po'] }} PO
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">{{ $data['summary']['received_po_count'] }} PO tuntas diterima</div>
    </div>
</div>

{{-- PO Status Breakdown --}}
<div class="grid grid-cols-2 grid-cols-5 gap-md" style="margin-bottom:var(--space-lg);">
    @foreach(['draft' => 'Draft', 'ordered' => 'Ordered (Dipesan)', 'partially_received' => 'Parsial', 'received' => 'Received (Tuntas)', 'cancelled' => 'Cancelled'] as $statusKey => $statusLabel)
        @php $row = $data['po_status']->get($statusKey); @endphp
        <div class="card" style="padding:var(--space-sm) var(--space-md);">
            <span class="text-xs text-muted">{{ $statusLabel }}:</span>
            <div style="font-size:1.15rem;font-weight:700;margin-top:2px;">
                {{ $row ? $row->count : 0 }} PO
            </div>
            <small class="text-muted">Rp{{ number_format($row ? $row->total_val : 0, 0, ',', '.') }}</small>
        </div>
    @endforeach
</div>

{{-- Goods Receiving History --}}
<section class="card" style="padding:var(--space-md);overflow-x:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h3>Riwayat Penerimaan Barang Riil (Goods Receiving)</h3>
        <a href="{{ route('admin.purchasing.index') }}" class="btn btn-secondary btn-sm">Buka Modul Purchasing →</a>
    </div>

    <table class="table" style="width:100%;font-size:0.9rem;">
        <thead>
            <tr>
                <th>No. Penerimaan</th>
                <th>No. PO</th>
                <th>Supplier</th>
                <th>Tgl Diterima</th>
                <th>Petugas Penerima</th>
                <th>Item &amp; Kuantitas Diterima</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['receivings'] as $gr)
                <tr>
                    <td>
                        <strong style="font-family:monospace;">{{ $gr->receiving_number }}</strong>
                    </td>
                    <td>
                        @if($gr->purchaseOrder)
                            <a href="{{ route('admin.purchasing.show', $gr->purchaseOrder) }}" style="text-decoration:none;font-weight:600;color:var(--text);">
                                {{ $gr->purchaseOrder->po_number }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        {{ $gr->purchaseOrder?->supplier?->name ?: '-' }}
                    </td>
                    <td>
                        {{ $gr->received_at->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));">{{ $gr->receiver?->name ?: 'Admin' }}</span>
                    </td>
                    <td>
                        @foreach($gr->items as $item)
                            <div class="text-xs">
                                • <strong>{{ $item->purchaseOrderItem?->rawMaterial?->name ?: 'Item' }}</strong>:
                                <span style="color:#2ecc71;">{{ number_format((float)$item->accepted_quantity, 2, ',', '.') }} {{ $item->purchase_unit }}</span>
                                @if((float)$item->rejected_quantity > 0)
                                    <span style="color:#e74c3c;">({{ number_format((float)$item->rejected_quantity, 2, ',', '.') }} ditolak)</span>
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td>
                        <small class="text-muted">{{ $gr->notes ?: '-' }}</small>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:var(--space-lg);color:var(--text-muted);">
                        Belum ada data penerimaan barang pada rentang tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:var(--space-md);">
        {{ $data['receivings']->links() }}
    </div>
</section>
@endsection
