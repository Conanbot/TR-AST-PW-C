@extends('layouts.admin')

@section('title', 'Tambah Banner Promo — Panel Admin Geprek GLO')

@section('content')
<div style="margin-bottom: var(--space-lg);">
    <a href="{{ route('admin.promos.index') }}" class="text-sm text-muted hover-white">&larr; Kembali ke Daftar Promo</a>
    <h2 style="font-size: 1.4rem; margin-top: 6px;">+ Tambah Banner Promo Baru</h2>
</div>

<div class="card" style="padding: var(--space-xl); max-width: 680px;">
    <form action="{{ route('admin.promos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="title" class="form-label">Judul Promo <span class="text-primary">*</span></label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" placeholder="Contoh: Spesial Jumat Berkah" required>
        </div>

        <div class="form-group">
            <label for="subtitle" class="form-label">Keterangan / Deskripsi Promo</label>
            <textarea id="subtitle" name="subtitle" class="form-control" placeholder="Contoh: Dapatkan diskon spesial untuk pemesanan rombongan minimal 20 kotak...">{{ old('subtitle') }}</textarea>
        </div>
        <div class="grid grid-cols-1 grid-cols-2 gap-md">
            <div class="form-group"><label for="code" class="form-label">Kode Promo (Opsional)</label><input type="text" id="code" name="code" class="form-control" value="{{ old('code') }}" placeholder="Contoh: GEprek10"></div>
            <div class="form-group"><label for="discount_type" class="form-label">Tipe Diskon</label><select id="discount_type" name="discount_type" class="form-control"><option value="fixed" @selected(old('discount_type','fixed')==='fixed')>Nominal tetap</option><option value="percentage" @selected(old('discount_type')==='percentage')>Persentase</option><option value="free_delivery" @selected(old('discount_type')==='free_delivery')>Gratis ongkir delivery</option></select></div>
        </div>
        <div class="grid grid-cols-1 grid-cols-2 gap-md">
            <div class="form-group"><label for="discount_value" class="form-label">Nilai Diskon</label><input type="number" id="discount_value" name="discount_value" class="form-control" value="{{ old('discount_value',0) }}" min="0" required></div>
            <div class="form-group"><label for="minimum_subtotal" class="form-label">Minimum Pembelian</label><input type="number" id="minimum_subtotal" name="minimum_subtotal" class="form-control" value="{{ old('minimum_subtotal',0) }}" min="0"></div>
        </div>
        <div class="grid grid-cols-1 grid-cols-2 gap-md">
            <div class="form-group"><label for="maximum_discount" class="form-label">Maksimum Diskon Percentage</label><input type="number" id="maximum_discount" name="maximum_discount" class="form-control" value="{{ old('maximum_discount') }}" min="0"></div>
            <div class="form-group"><label for="usage_limit" class="form-label">Batas Pemakaian Total</label><input type="number" id="usage_limit" name="usage_limit" class="form-control" value="{{ old('usage_limit') }}" min="0"></div>
        </div>
        <div class="form-group"><label for="per_customer_limit" class="form-label">Batas Pemakaian Per Pelanggan</label><input type="number" id="per_customer_limit" name="per_customer_limit" class="form-control" value="{{ old('per_customer_limit') }}" min="0"></div>

        <div class="grid grid-cols-1 grid-cols-2 gap-md">
            <div class="form-group">
                <label for="start_date" class="form-label">Tanggal Mulai (Opsional)</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date') }}">
            </div>

            <div class="form-group">
                <label for="end_date" class="form-label">Tanggal Selesai (Opsional)</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date') }}">
            </div>
        </div>

        <div class="form-group" style="padding: var(--space-md); background: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius-md);">
            <label for="image" class="form-label">Gambar Banner Promo (Opsional)</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/jpg" style="padding: 6px;" onchange="previewPromoImage(this)">
            <div id="promo-preview-wrapper" style="display: none; margin-top: 12px; align-items: center; gap: 12px;">
                <img id="promo-preview" src="#" alt="Preview Banner Promo" style="width: 140px; aspect-ratio: 16/9; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <span class="text-xs text-ok">Pratinjau banner promo</span>
            </div>
            <p class="text-xs text-muted" style="margin-top: 6px; line-height: 1.4;">
                Format: JPG, JPEG, PNG. Ukuran maksimal 2 MB. Direkomendasikan rasio memanjang (16:9).
            </p>
        </div>

        <div class="form-group">
            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Aktifkan Banner Promo</span>
            </label>
        </div>
        <div class="form-group">
            <label class="form-check"><input type="checkbox" name="stackable" value="1" {{ old('stackable')?'checked':'' }}><span>Dapat dikombinasikan dengan promo lain</span></label>
        </div>

        <div class="flex gap-md justify-end" style="margin-top: var(--space-xl); border-top: 1px solid var(--border); padding-top: var(--space-md);">
            <a href="{{ route('admin.promos.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Banner Promo</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function previewPromoImage(input) {
    const wrapper = document.getElementById('promo-preview-wrapper');
    const preview = document.getElementById('promo-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            wrapper.style.display = 'flex';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        wrapper.style.display = 'none';
        preview.src = '#';
    }
}
</script>
@endpush
