@extends('layouts.admin')

@section('title', 'Supplier — Panel Admin Geprek GLO')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:end;gap:16px;margin-bottom:var(--space-lg);">
    <div><h2>Manajemen supplier</h2><p class="text-sm text-muted">Supplier transaksi tetap dapat ditemukan meskipun dinonaktifkan.</p></div>
</div>

<div class="grid grid-cols-1 grid-cols-2 gap-md">
<section class="card" style="padding:var(--space-md);">
<h3>Tambah supplier</h3>
<form method="POST" action="{{ route('admin.suppliers.store') }}" style="display:grid;gap:10px;margin-top:12px;">@csrf
<input class="form-input" name="name" placeholder="Nama supplier" required>
<input class="form-input" name="phone" placeholder="Nomor telepon">
<input class="form-input" name="contact_person" placeholder="Kontak person">
<textarea class="form-input" name="address" placeholder="Alamat"></textarea>
<textarea class="form-input" name="notes" placeholder="Catatan"></textarea>
<button class="btn btn-primary">Simpan supplier</button>
</form>
</section>
<section class="card" style="padding:var(--space-md);">
<h3>Cari supplier</h3>
<form method="GET" action="{{ route('admin.suppliers.index') }}" style="display:grid;gap:10px;margin-top:12px;">
<input class="form-input" name="search" value="{{ request('search') }}" placeholder="Nama, telepon, atau kontak">
<select class="form-input" name="status"><option value="">Semua status</option><option value="active" @selected(request('status')==='active')>Aktif</option><option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option></select>
<button class="btn btn-secondary">Filter</button>
</form>
</section>
</div>

<section class="card" style="padding:var(--space-md);margin-top:var(--space-lg);overflow:auto;">
<table class="table"><thead><tr><th>Supplier</th><th>Kontak</th><th>Telepon</th><th>Status</th><th>Transaksi</th><th>Aksi</th></tr></thead><tbody>
@forelse($suppliers as $supplier)<tr>
<td><a href="{{ route('admin.suppliers.show',$supplier) }}"><strong>{{ $supplier->name }}</strong></a><div class="text-xs text-muted">{{ $supplier->address ?: '-' }}</div></td>
<td>{{ $supplier->contact_person ?: '-' }}</td><td>{{ $supplier->phone ?: '-' }}</td>
<td><span class="badge {{ $supplier->is_active ? 'badge-open':'badge-closed' }}">{{ $supplier->is_active?'Aktif':'Nonaktif' }}</span></td>
<td>{{ $supplier->purchase_orders_count }}</td>
<td><div class="flex gap-xs"><form method="POST" action="{{ route('admin.suppliers.toggle',$supplier) }}">@csrf @method('PATCH')<button class="btn btn-secondary btn-sm">{{ $supplier->is_active?'Nonaktifkan':'Aktifkan' }}</button></form><form method="POST" action="{{ route('admin.suppliers.destroy',$supplier) }}" onsubmit="return confirm('Hapus atau nonaktifkan supplier ini?')">@csrf @method('DELETE')<button class="btn btn-secondary btn-sm">Hapus</button></form></div></td>
</tr>@empty<tr><td colspan="6">Belum ada supplier.</td></tr>@endforelse
</tbody></table>{{ $suppliers->links() }}
</section>
@endsection
