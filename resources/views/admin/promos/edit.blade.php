@extends('layouts.admin')

@section('title', 'Ubah Banner Promo — Panel Admin Geprek GLO')

@section('content')
<div style="margin-bottom: var(--space-lg);">
    <a href="{{ route('admin.promos.index') }}" class="text-sm text-muted hover-white">&larr; Kembali ke Daftar Promo</a>
    <h2 style="font-size: 1.4rem; margin-top: 6px;">Ubah Banner Promo</h2>
</div>

<div class="card" style="padding: var(--space-xl); max-width: 680px;">
    <form action="{{ route('admin.promos.update', $promo->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="title" class="form-label">Judul Promo <span class="text-primary">*</span></label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $promo->title) }}" required>
        </div>

        <div class="form-group">
            <label for="subtitle" class="form-label">Keterangan / Deskripsi Promo</label>
            <textarea id="subtitle" name="subtitle" class="form-control">{{ old('subtitle', $promo->subtitle) }}</textarea>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 gap-md">
            <div class="form-group">
                <label for="start_date" class="form-label">Tanggal Mulai (Opsional)</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', $promo->start_date ? $promo->start_date->format('Y-m-d') : '') }}">
            </div>

            <div class="form-group">
                <label for="end_date" class="form-label">Tanggal Selesai (Opsional)</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date', $promo->end_date ? $promo->end_date->format('Y-m-d') : '') }}">
            </div>
        </div>

        <!-- Banner Image & Replacement -->
        <div class="form-group" style="padding: var(--space-md); background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md);">
            <label class="form-label">Gambar Banner Saat Ini</label>
            <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 12px;">
                <div style="width: 140px; aspect-ratio: 16/9; border-radius: var(--radius-sm); overflow: hidden; background: #171719; display: flex; align-items: center; justify-content: center;">
                    @if($promo->image_url)
                        <img src="{{ $promo->image_url }}" alt="{{ $promo->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span class="text-xs text-dim">Belum ada gambar</span>
                    @endif
                </div>
                <div>
                    @if($promo->image)
                        <span class="text-sm text-ok">✓ Banner kustom terpasang</span>
                        <div style="margin-top: 6px;">
                            <label class="form-check" style="font-size: 0.85rem; color: #FF7B89;">
                                <input type="checkbox" name="remove_image" value="1">
                                <span>Hapus gambar banner</span>
                            </label>
                        </div>
                    @else
                        <span class="text-sm text-muted">Promo ini belum memiliki gambar banner.</span>
                    @endif
                </div>
            </div>

            <label for="image" class="form-label text-sm">Unggah / Ganti Banner Baru</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/jpg" style="padding: 6px;" onchange="previewEditPromoImage(this)">
            <div id="promo-edit-preview-wrapper" style="display: none; margin-top: 10px; align-items: center; gap: 12px;">
                <img id="promo-edit-preview" src="#" alt="Preview Banner Baru" style="width: 140px; aspect-ratio: 16/9; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <span class="text-xs text-ok">Pratinjau calon banner baru</span>
            </div>
            <p class="text-xs text-muted" style="margin-top: 6px;">
                Format: JPG, JPEG, PNG. Maksimal 2 MB.
            </p>
        </div>

        <div class="form-group">
            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promo->is_active) ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Aktifkan Banner Promo</span>
            </label>
        </div>

        <div class="flex gap-md justify-end" style="margin-top: var(--space-xl); border-top: 1px solid var(--border); padding-top: var(--space-md);">
            <a href="{{ route('admin.promos.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan Promo</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function previewEditPromoImage(input) {
    const wrapper = document.getElementById('promo-edit-preview-wrapper');
    const preview = document.getElementById('promo-edit-preview');
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
