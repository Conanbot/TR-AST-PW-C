@extends('layouts.admin')

@section('title', 'Edit Draft PO — Panel Admin Geprek GLO')

@section('content')
<h2 style="margin-bottom:var(--space-lg)">Edit {{ $purchaseOrder->po_number }}</h2>
<form method="POST" action="{{ route('admin.purchasing.update',$purchaseOrder) }}" class="card" style="padding:var(--space-md);">@csrf @method('PUT')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><select class="form-input" name="supplier_id" required>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected($purchaseOrder->supplier_id==$supplier->id)>{{ $supplier->name }}</option>@endforeach</select><input class="form-input" type="date" name="purchase_date" value="{{ $purchaseOrder->purchase_date->toDateString() }}" required></div>
<textarea class="form-input" name="notes" style="margin-top:10px">{{ $purchaseOrder->notes }}</textarea><h3 style="margin-top:16px">Item bahan</h3><div id="po-items" style="display:grid;gap:8px;margin-top:10px;">
@foreach($purchaseOrder->items as $poItem)<div class="po-item" style="display:grid;grid-template-columns:minmax(180px,2fr) 1fr 1fr 1fr;gap:8px;"><select class="form-input" name="items[][raw_material_id]" required>@foreach($materials as $material)<option value="{{ $material->id }}" @selected($poItem->raw_material_id==$material->id)>{{ $material->name }} ({{ $material->purchase_unit }})</option>@endforeach</select><input class="form-input" name="items[][ordered_quantity]" type="number" step="0.0001" min="0.0001" value="{{ $poItem->ordered_quantity }}" required><input class="form-input" name="items[][unit_price]" type="number" min="0" value="{{ $poItem->unit_price }}" required><input class="form-input" name="items[][notes]" value="{{ $poItem->notes }}"></div>@endforeach
</div><button class="btn btn-primary" type="submit" style="margin-top:12px">Simpan perubahan</button>
</form>
@endsection
