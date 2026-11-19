@extends('layouts.admin')

@section('title', 'Detail Promo — Panel Admin Geprek GLO')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:end;gap:16px;margin-bottom:var(--space-lg);">
    <div><h2>{{ $promo->title }}</h2><p class="text-sm text-muted">Kode: {{ $promo->code ?: '-' }} · {{ $promo->discount_type }}</p></div>
    <a href="{{ route('admin.promos.edit',$promo->id) }}" class="btn btn-secondary">Edit promo</a>
</div>
<section class="card" style="padding:var(--space-md);margin-bottom:var(--space-lg);">
    <div class="grid grid-cols-1 grid-cols-2 gap-md">
        <div><span class="text-muted text-sm">Nilai diskon</span><strong style="display:block">{{ $promo->discount_type==='percentage'?$promo->discount_value.'%':'Rp'.number_format($promo->discount_value,0,',','.') }}</strong></div>
        <div><span class="text-muted text-sm">Minimum pembelian</span><strong style="display:block">Rp{{ number_format($promo->minimum_subtotal,0,',','.') }}</strong></div>
        <div><span class="text-muted text-sm">Kuota total</span><strong style="display:block">{{ $promo->usage_limit ?: 'Tanpa batas' }}</strong></div>
        <div><span class="text-muted text-sm">Pemakaian</span><strong style="display:block">{{ $promo->usages->count() }}</strong></div>
    </div>
</section>
<section class="card" style="padding:var(--space-md);overflow:auto;">
<h3>Histori penggunaan</h3>
<table class="table" style="margin-top:12px"><thead><tr><th>Waktu</th><th>Order</th><th>Pelanggan</th><th>Diskon</th><th>Status</th></tr></thead><tbody>
@forelse($promo->usages as $usage)<tr><td>{{ $usage->created_at->format('d/m/Y H:i') }}</td><td>{{ $usage->order->order_number ?? '-' }}</td><td>{{ $usage->customer->name ?? $usage->customer_phone ?: '-' }}</td><td>Rp{{ number_format($usage->discount_amount,0,',','.') }}</td><td>{{ $usage->status }}</td></tr>@empty<tr><td colspan="5">Belum ada penggunaan promo.</td></tr>@endforelse
</tbody></table>
</section>
@endsection
