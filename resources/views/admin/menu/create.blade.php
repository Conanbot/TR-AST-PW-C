@extends('layouts.admin')

@section('title', 'Tambah Menu Baru — Panel Admin Geprek GLO')

@section('content')
<div style="margin-bottom: var(--space-lg);">
    <a href="{{ route('admin.items.index') }}" class="text-sm text-muted hover-white">&larr; Kembali ke Daftar Menu</a>
    <h2 style="font-size: 1.4rem; margin-top: 6px;">+ Tambah Menu Baru</h2>
</div>

<div class="card" style="padding: var(--space-xl); max-width: 720px;">
    <form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">Nama Menu <span class="text-primary">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Paket Hemat Ayam Crispy" required>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 gap-md">
            <div class="form-group">
                <label for="category_id" class="form-label">Kategori <span class="text-primary">*</span></label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="price" class="form-label">Harga (Rupiah) <span class="text-primary">*</span></label>
                <input type="number" id="price" name="price" class="form-control" value="{{ old('price', 10000) }}" min="0" step="500" placeholder="Contoh: 11000" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi Menu (Opsional)</label>
            <textarea id="description" name="description" class="form-control" placeholder="Tuliskan komposisi atau keistimewaan menu...">{{ old('description') }}</textarea>
        </div>

        <!-- Foto Produk Upload (Section 5) -->
        <div class="form-group" style="padding: var(--space-md); background: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius-md);">
            <label for="image" class="form-label">Foto Produk (Opsional)</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/jpg" style="padding: 6px;" onchange="previewMenuImage(this)">
            <div id="image-preview-wrapper" style="display: none; margin-top: 12px; align-items: center; gap: 12px;">
                <img id="image-preview" src="#" alt="Preview Foto Menu" style="width: 100px; aspect-ratio: 4/3; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <span class="text-xs text-ok">Pratinjau gambar baru</span>
            </div>
            <p class="text-xs text-muted" style="margin-top: 6px; line-height: 1.4;">
                *Jika foto belum tersedia, biarkan kosong. Sistem akan otomatis menampilkan <strong>placeholder netral 4:3</strong> yang rapi. Format: JPG, JPEG, PNG. Ukuran maksimal 2 MB.
            </p>
        </div>

        <!-- Checkbox Options -->
        <div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-top: var(--space-md); margin-bottom: var(--space-lg);">
            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_available" value="1" {{ old('is_available', 1) ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Tersedia (Stok Ada)</span>
            </label>

            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller') ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Tandai Best Seller</span>
            </label>

            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_package" value="1" {{ old('is_package') ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Menu Paket (Wajib Minuman)</span>
            </label>
        </div>

        <div class="form-group" style="max-width: 200px;">
            <label for="sort_order" class="form-label">Urutan Tampil</label>
            <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
        </div>

        <div class="flex gap-md justify-end" style="margin-top: var(--space-xl); border-top: 1px solid var(--border); padding-top: var(--space-md);">
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Menu Baru</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function previewMenuImage(input) {
    const wrapper = document.getElementById('image-preview-wrapper');
    const preview = document.getElementById('image-preview');
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
