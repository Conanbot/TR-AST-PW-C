@extends('layouts.admin')

@section('title', 'Inventory — Panel Admin Geprek GLO')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:end;gap:16px;margin-bottom:var(--space-lg);">
    <div><h2>Inventory bahan baku</h2><p class="text-sm text-muted">Semua perubahan stok dicatat sebagai ledger dan tidak mengubah angka secara diam-diam.</p></div>
    <a class="btn btn-secondary" href="{{ route('admin.inventory.recipes') }}">Kelola resep/BOM</a>
</div>

<div class="grid grid-cols-1 grid-cols-2 gap-md">
    <section class="card" style="padding:var(--space-md);">
        <h3>Tambah bahan baku</h3>
        <form method="POST" action="{{ route('admin.inventory.materials.store') }}" style="display:grid;gap:10px;margin-top:12px;">
            @csrf
            <input class="form-input" name="name" placeholder="Nama bahan" required>
            <input class="form-input" name="category" placeholder="Kategori (opsional)">
            <input class="form-input" name="sku" placeholder="SKU (opsional)">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;"><input class="form-input" name="purchase_unit" placeholder="Satuan beli" required><input class="form-input" name="usage_unit" placeholder="Satuan pakai" required></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;"><input class="form-input" name="conversion_factor" type="number" step="0.0001" min="0.0001" value="1" placeholder="Konversi" required><input class="form-input" name="minimum_stock" type="number" step="0.0001" min="0" value="0" placeholder="Stok minimum" required></div>
            <input class="form-input" name="cost_per_purchase_unit" type="number" min="0" value="0" placeholder="Biaya per satuan beli" required>
            <button class="btn btn-primary" type="submit">Simpan bahan</button>
        </form>
    </section>
    <section class="card" style="padding:var(--space-md);">
        <h3>Catat pergerakan stok</h3>
        <form method="POST" action="{{ route('admin.inventory.movements.store') }}" style="display:grid;gap:10px;margin-top:12px;">
            @csrf
            <select class="form-input" name="raw_material_id" required><option value="">Pilih bahan</option>@foreach($materials as $material)<option value="{{ $material->id }}">{{ $material->name }} ({{ $material->current_stock }} {{ $material->usage_unit }})</option>@endforeach</select>
            <select class="form-input" name="movement_type" required><option value="stock_in">Stock in</option><option value="stock_out">Stock out</option><option value="adjustment">Adjustment (+)</option></select>
            <input class="form-input" name="quantity" type="number" step="0.0001" min="0.0001" placeholder="Jumlah" required>
            <input class="form-input" name="reason" placeholder="Alasan transaksi" required>
            <button class="btn btn-primary" type="submit">Catat transaksi</button>
        </form>
    </section>
</div>

<section id="low-stock" class="card" style="padding:var(--space-md);margin-top:var(--space-lg);">
    <h3>Stok saat ini</h3>
    <div style="overflow:auto;margin-top:12px;"><table class="table"><thead><tr><th>Bahan</th><th>SKU</th><th>Stok</th><th>Minimum</th><th>Biaya/satuan beli</th><th>Status</th></tr></thead><tbody>
    @forelse($materials as $material)<tr><td><strong>{{ $material->name }}</strong><div class="text-xs text-muted">{{ $material->category ?: 'Tanpa kategori' }}</div></td><td>{{ $material->sku ?: '-' }}</td><td>{{ $material->current_stock }} {{ $material->usage_unit }}</td><td>{{ $material->minimum_stock }}</td><td>Rp{{ number_format($material->cost_per_purchase_unit, 0, ',', '.') }}</td><td><span class="badge {{ $material->is_low_stock ? 'badge-closed' : 'badge-open' }}">{{ $material->is_low_stock ? 'Stok rendah' : 'Aman' }}</span></td></tr>@empty<tr><td colspan="6">Belum ada bahan baku.</td></tr>@endforelse
    </tbody></table></div>{{ $materials->links() }}
</section>

<section id="movement" class="card" style="padding:var(--space-md);margin-top:var(--space-lg);">
    <h3>Riwayat pergerakan terbaru</h3>
    <div style="overflow:auto;margin-top:12px;"><table class="table"><thead><tr><th>Waktu</th><th>Bahan</th><th>Jenis</th><th>Perubahan</th><th>Saldo</th><th>Alasan</th><th>Oleh</th></tr></thead><tbody>
    @forelse($movements as $movement)<tr><td>{{ $movement->created_at->format('d/m/Y H:i') }}</td><td>{{ $movement->rawMaterial->name }}</td><td>{{ $movement->movement_type }}</td><td>{{ $movement->quantity_delta }} {{ $movement->unit }}</td><td>{{ $movement->balance_after }}</td><td>{{ $movement->reason }}</td><td>{{ $movement->user->name ?? 'Sistem' }}</td></tr>@empty<tr><td colspan="7">Belum ada pergerakan stok.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
