@extends('layouts.admin')

@section('title', 'Purchase Order — Panel Admin Geprek GLO')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:end;gap:16px;margin-bottom:var(--space-lg);"><div><h2>Purchase order</h2><p class="text-sm text-muted">Pembuatan PO tidak menambah stok sampai goods receiving disetujui.</p></div><a class="btn btn-primary" href="{{ route('admin.purchasing.create') }}">+ Buat PO</a></div>
<form method="GET" action="{{ route('admin.purchasing.index') }}" class="card" style="padding:var(--space-md);display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:var(--space-lg);">
<select class="form-input" name="supplier_id"><option value="">Semua supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(request('supplier_id')==$supplier->id)>{{ $supplier->name }}</option>@endforeach</select>
<select class="form-input" name="status"><option value="">Semua status</option>@foreach(['draft','ordered','partially_received','received','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')==$status)>{{ $status }}</option>@endforeach</select>
<input class="form-input" type="date" name="from" value="{{ request('from') }}"><input class="form-input" type="date" name="to" value="{{ request('to') }}"><button class="btn btn-secondary" style="grid-column:1/-1">Filter</button>
</form>
<section class="card" style="padding:var(--space-md);overflow:auto;"><table class="table"><thead><tr><th>Nomor PO</th><th>Supplier</th><th>Tanggal</th><th>Status</th><th>Total</th><th>Aksi</th></tr></thead><tbody>@forelse($purchaseOrders as $po)<tr><td><a href="{{ route('admin.purchasing.show',$po) }}"><strong>{{ $po->po_number }}</strong></a></td><td>{{ $po->supplier->name }}</td><td>{{ $po->purchase_date->format('d/m/Y') }}</td><td><span class="badge">{{ $po->status }}</span></td><td>Rp{{ number_format($po->total,0,',','.') }}</td><td>@if($po->status==='draft')<a class="btn btn-secondary btn-sm" href="{{ route('admin.purchasing.edit',$po) }}">Edit draft</a>@else<a class="btn btn-secondary btn-sm" href="{{ route('admin.purchasing.show',$po) }}">Detail</a>@endif</td></tr>@empty<tr><td colspan="6">Belum ada purchase order.</td></tr>@endforelse</tbody></table>{{ $purchaseOrders->links() }}</section>
@endsection
