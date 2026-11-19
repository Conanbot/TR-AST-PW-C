@extends('layouts.admin')

@section('title', $purchaseOrder->po_number.' — Purchase Order')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:end;gap:16px;margin-bottom:var(--space-lg);"><div><h2>{{ $purchaseOrder->po_number }}</h2><p class="text-sm text-muted">{{ $purchaseOrder->supplier->name }} · {{ $purchaseOrder->purchase_date->format('d/m/Y') }}</p></div><span class="badge">{{ $purchaseOrder->status }}</span></div>
<div class="flex gap-xs" style="margin-bottom:var(--space-lg)">
@if($purchaseOrder->status==='draft')<form method="POST" action="{{ route('admin.purchasing.ordered',$purchaseOrder) }}">@csrf @method('PATCH')<button class="btn btn-primary">Tandai Ordered</button></form><a class="btn btn-secondary" href="{{ route('admin.purchasing.edit',$purchaseOrder) }}">Edit draft</a>@endif
@if(!in_array($purchaseOrder->status,['received','cancelled'],true))<form method="POST" action="{{ route('admin.purchasing.cancel',$purchaseOrder) }}" onsubmit="return confirm('Batalkan purchase order ini?')">@csrf @method('PATCH')<button class="btn btn-secondary">Batalkan PO</button></form>@endif
</div>
<section class="card" style="padding:var(--space-md);overflow:auto;"><h3>Item purchase order</h3><table class="table" style="margin-top:12px"><thead><tr><th>Bahan</th><th>Pesan</th><th>Diterima</th><th>Ditolak</th><th>Sisa</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody>@foreach($purchaseOrder->items as $item)<tr><td><strong>{{ $item->material_name }}</strong><div class="text-xs text-muted">{{ $item->purchase_unit }} · 1 {{ $item->purchase_unit }} = {{ $item->conversion_factor }} {{ $item->usage_unit }}</div></td><td>{{ $item->ordered_quantity }} {{ $item->purchase_unit }}</td><td>{{ $item->received_quantity }}</td><td>{{ $item->rejected_quantity }}</td><td>{{ $item->remaining_quantity }} {{ $item->purchase_unit }}</td><td>Rp{{ number_format($item->unit_price,0,',','.') }}</td><td>Rp{{ number_format($item->subtotal,0,',','.') }}</td></tr>@endforeach</tbody></table><div style="text-align:right;margin-top:12px"><strong>Total: Rp{{ number_format($purchaseOrder->total,0,',','.') }}</strong></div></section>

@if(in_array($purchaseOrder->status,['ordered','partially_received'],true))
<section class="card" style="padding:var(--space-md);margin-top:var(--space-lg);"><h3>Catat goods receiving</h3><form method="POST" action="{{ route('admin.purchasing.receive',$purchaseOrder) }}" style="display:grid;gap:10px;margin-top:12px;">@csrf
<input type="hidden" id="receiving-idempotency-key" name="idempotency_key" value="">
<input class="form-input" type="date" name="received_at" value="{{ now()->toDateString() }}"><textarea class="form-input" name="notes" placeholder="Catatan penerimaan"></textarea>
@foreach($purchaseOrder->items as $item)<div style="display:grid;grid-template-columns:minmax(180px,2fr) 1fr 1fr 1fr;gap:8px;"><input type="hidden" name="items[][purchase_order_item_id]" value="{{ $item->id }}"><div><strong>{{ $item->material_name }}</strong><div class="text-xs text-muted">Sisa {{ $item->remaining_quantity }} {{ $item->purchase_unit }}</div></div><input class="form-input" name="items[][accepted_quantity]" type="number" step="0.0001" min="0" value="0" placeholder="Diterima"><input class="form-input" name="items[][rejected_quantity]" type="number" step="0.0001" min="0" value="0" placeholder="Ditolak"><input class="form-input" name="items[][actual_unit_price]" type="number" min="0" value="{{ $item->unit_price }}" placeholder="Harga aktual"></div>@endforeach
<button class="btn btn-primary" type="submit">Simpan penerimaan</button></form></section>
@endif

<section class="card" style="padding:var(--space-md);margin-top:var(--space-lg);overflow:auto;"><h3>Riwayat receiving</h3><table class="table" style="margin-top:12px"><thead><tr><th>Dokumen</th><th>Tanggal</th><th>Diterima oleh</th><th>Item diterima/ditolak</th></tr></thead><tbody>@forelse($purchaseOrder->receivings()->latest()->get() as $receiving)<tr><td>{{ $receiving->receiving_number }}</td><td>{{ $receiving->received_at->format('d/m/Y') }}</td><td>{{ $receiving->receiver->name ?? 'Sistem' }}</td><td>@foreach($receiving->items as $detail)<div>{{ $detail->purchaseOrderItem->material_name }}: {{ $detail->accepted_quantity }} diterima, {{ $detail->rejected_quantity }} ditolak</div>@endforeach</td></tr>@empty<tr><td colspan="4">Belum ada penerimaan.</td></tr>@endforelse</tbody></table></section>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const keyInput = document.getElementById('receiving-idempotency-key');
    if (keyInput && !keyInput.value) {
        keyInput.value = 'grn-manual:po:{{ $purchaseOrder->id }}:' + crypto.randomUUID();
    }
});
</script>
@endpush
