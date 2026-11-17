@extends('layouts.admin')

@section('title', 'Ubah Menu — Panel Admin Geprek GLO')

@section('content')
<div style="margin-bottom: var(--space-lg);">
    <a href="{{ route('admin.items.index') }}" class="text-sm text-muted hover-white">&larr; Kembali ke Daftar Menu</a>
    <h2 style="font-size: 1.4rem; margin-top: 6px;">Ubah Menu: {{ $item->name }}</h2>
</div>

<div class="card" style="padding: var(--space-xl); max-width: 720px;">
    <form action="{{ route('admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">Nama Menu <span class="text-primary">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 gap-md">
            <div class="form-group">
                <label for="category_id" class="form-label">Kategori <span class="text-primary">*</span></label>
                <select id="category_id" name="category_id" class="form-control" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="price" class="form-label">Harga (Rupiah) <span class="text-primary">*</span></label>
                <input type="number" id="price" name="price" class="form-control" value="{{ old('price', $item->price) }}" min="0" step="500" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi Menu (Opsional)</label>
            <textarea id="description" name="description" class="form-control">{{ old('description', $item->description) }}</textarea>
        </div>

        <!-- Foto Produk & Penggantian (Section 5) -->
        <div class="form-group" style="padding: var(--space-md); background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md);">
            <label class="form-label">Foto Produk Saat Ini</label>
            
            <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 12px;">
                <div style="width: 100px; aspect-ratio: 4/3; border-radius: var(--radius-sm); overflow: hidden; background: #171719;">
                    @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <div class="placeholder-content" style="padding: 4px;">
                            <span class="text-xs text-dim">Placeholder 4:3</span>
                        </div>
                    @endif
                </div>

                <div>
                    @if($item->image)
                        <span class="text-sm text-ok">✓ Foto kustom terpasang</span>
                        <div style="margin-top: 6px;">
                            <label class="form-check" style="font-size: 0.85rem; color: #FF7B89;">
                                <input type="checkbox" name="remove_image" value="1">
                                <span>Hapus foto (kembali ke placeholder)</span>
                            </label>
                        </div>
                    @else
                        <span class="text-sm text-muted">Saat ini menggunakan placeholder netral.</span>
                    @endif
                </div>
            </div>

            <label for="image" class="form-label text-sm">Unggah / Ganti Foto Baru</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/jpg" style="padding: 6px;" onchange="previewEditImage(this)">
            <div id="image-edit-preview-wrapper" style="display: none; margin-top: 10px; align-items: center; gap: 12px;">
                <img id="image-edit-preview" src="#" alt="Preview Foto Baru" style="width: 100px; aspect-ratio: 4/3; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <span class="text-xs text-ok">Pratinjau calon foto pengganti</span>
            </div>
            <p class="text-xs text-muted" style="margin-top: 6px;">
                Format: JPG, JPEG, PNG. Maksimal 2 MB.
            </p>
        </div>

        <!-- Checkbox Options -->
        <div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-top: var(--space-md); margin-bottom: var(--space-lg);">
            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_available" value="1" {{ old('is_available', $item->is_available) ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Tersedia (Stok Ada)</span>
            </label>

            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller', $item->is_best_seller) ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Tandai Best Seller</span>
            </label>

            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_package" value="1" {{ old('is_package', $item->is_package) ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Menu Paket (Wajib Minuman)</span>
            </label>
        </div>

        <div class="form-group" style="max-width: 200px;">
            <label for="sort_order" class="form-label">Urutan Tampil</label>
            <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $item->sort_order) }}" min="0">
        </div>

        <div class="flex gap-md justify-end" style="margin-top: var(--space-xl); border-top: 1px solid var(--border); padding-top: var(--space-md);">
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan Menu</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function previewEditImage(input) {
    const wrapper = document.getElementById('image-edit-preview-wrapper');
    const preview = document.getElementById('image-edit-preview');
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
