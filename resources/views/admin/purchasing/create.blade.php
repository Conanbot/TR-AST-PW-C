@extends('layouts.admin')

@section('title', 'Buat Purchase Order — Panel Admin Geprek GLO')

@section('content')
<h2 style="margin-bottom:var(--space-lg)">Buat purchase order</h2>
<form method="POST" action="{{ route('admin.purchasing.store') }}" class="card" style="padding:var(--space-md);">@csrf
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
<select class="form-input" name="supplier_id" required><option value="">Pilih supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select>
<input class="form-input" type="date" name="purchase_date" value="{{ now()->toDateString() }}" required>
</div>
<textarea class="form-input" name="notes" placeholder="Catatan PO" style="margin-top:10px"></textarea>
<h3 style="margin-top:16px">Item bahan</h3>
<div id="po-items" style="display:grid;gap:8px;margin-top:10px;"></div>
<button type="button" class="btn btn-secondary" onclick="addPoItem()" style="margin-top:10px">+ Tambah bahan</button>
<button class="btn btn-primary" style="margin-left:8px" type="submit">Simpan draft</button>
</form>
<template id="po-item-template"><div class="po-item" style="display:grid;grid-template-columns:minmax(180px,2fr) 1fr 1fr 1fr;gap:8px;"><select class="form-input" name="items[][raw_material_id]" required><option value="">Pilih bahan</option>@foreach($materials as $material)<option value="{{ $material->id }}" data-unit="{{ $material->purchase_unit }}">{{ $material->name }} ({{ $material->purchase_unit }})</option>@endforeach</select><input class="form-input" name="items[][ordered_quantity]" type="number" step="0.0001" min="0.0001" placeholder="Jumlah" required><input class="form-input" name="items[][unit_price]" type="number" min="0" placeholder="Harga satuan" required><input class="form-input" name="items[][notes]" placeholder="Catatan"></div></template>
@endsection
@push('scripts')
<script>
function addPoItem(){const template=document.getElementById('po-item-template');document.getElementById('po-items').appendChild(template.content.cloneNode(true));}
addPoItem();
</script>
@endpush
