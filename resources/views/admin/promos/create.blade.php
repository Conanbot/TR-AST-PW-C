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
            <div class="form-group">
                <label for="start_date" class="form-label">Tanggal Mulai (Opsional)</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date') }}">
            </div>

            <div class="form-group">
                <label for="end_date" class="form-label">Tanggal Selesai (Opsional)</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-check" style="background: var(--surface); padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                <span class="text-sm font-semibold">Aktifkan Banner Promo</span>
            </label>
        </div>

        <div class="flex gap-md justify-end" style="margin-top: var(--space-xl); border-top: 1px solid var(--border); padding-top: var(--space-md);">
            <a href="{{ route('admin.promos.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Banner Promo</button>
        </div>
    </form>
</div>
@endsection
