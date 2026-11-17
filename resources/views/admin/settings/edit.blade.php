@extends('layouts.admin')

@section('title', 'Pengaturan Kedai — Panel Admin Geprek GLO')

@section('content')
<div style="margin-bottom: var(--space-lg);">
    <h2 style="font-size: 1.4rem; margin-bottom: 4px;">⚙️ Pengaturan Toko &amp; Kontak</h2>
    <p class="text-sm text-muted">
        Konfigurasikan informasi resmi kedai, nomor WhatsApp pemesanan, jam buka, biaya kemasan, dan layanan pesan antar.
    </p>
</div>

<div class="card" style="padding: var(--space-xl); max-width: 780px;">
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Status Kedai & WhatsApp -->
        <h3 style="font-size: 1.15rem; margin-bottom: 14px; color: #FFFFFF; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            1. Status Operasional &amp; Jalur Pemesanan
        </h3>

        <div class="grid grid-cols-1 grid-cols-2 gap-md" style="margin-bottom: var(--space-md);">
            <div class="form-group">
                <label class="form-label">Status Kedai (Buka / Tutup)</label>
                <label class="form-check" style="background: var(--surface); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                    <input type="checkbox" name="is_open" value="1" {{ old('is_open', $settings->is_open) ? 'checked' : '' }}>
                    <span class="font-semibold {{ $settings->is_open ? 'text-ok' : 'text-off' }}">
                        {{ $settings->is_open ? 'Kedai BUKA (Menerima Pesanan)' : 'Kedai TUTUP' }}
                    </span>
                </label>
                <span class="text-xs text-dim">Status ini diatur secara manual oleh pengelola, bukan otomatis.</span>
            </div>

            <div class="form-group">
                <label for="wa_number" class="form-label">Nomor WhatsApp Kedai <span class="text-primary">*</span></label>
                <input type="text" id="wa_number" name="wa_number" class="form-control" value="{{ old('wa_number', $settings->wa_number) }}" placeholder="Contoh: 6281268177729" required>
                <span class="text-xs text-muted">Gunakan format angka tanpa spasi (diawali 62).</span>
            </div>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 gap-md" style="margin-bottom: var(--space-lg);">
            <div class="form-group">
                <label for="opening_hours" class="form-label">Teks Jam Operasional <span class="text-primary">*</span></label>
                <input type="text" id="opening_hours" name="opening_hours" class="form-control" value="{{ old('opening_hours', $settings->opening_hours) }}" required>
            </div>

            <div class="form-group">
                <label for="packaging_fee" class="form-label">Biaya Kemasan Takeaway (Rupiah) <span class="text-primary">*</span></label>
                <input type="number" id="packaging_fee" name="packaging_fee" class="form-control" value="{{ old('packaging_fee', $settings->packaging_fee) }}" min="0" required>
                <span class="text-xs text-dim">Sesuai aturan bisnis: default Rp1.000 per pesanan bungkus.</span>
            </div>
        </div>

        <!-- Alamat & Lokasi Maps -->
        <h3 style="font-size: 1.15rem; margin-bottom: 14px; color: #FFFFFF; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            2. Lokasi, Alamat &amp; Ulasan Google
        </h3>

        <div class="form-group">
            <label for="address" class="form-label">Alamat Lengkap Kedai <span class="text-primary">*</span></label>
            <textarea id="address" name="address" class="form-control" required>{{ old('address', $settings->address) }}</textarea>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 gap-md" style="margin-bottom: var(--space-md);">
            <div class="form-group">
                <label for="maps_url" class="form-label">URL Tautan Google Maps</label>
                <input type="url" id="maps_url" name="maps_url" class="form-control" value="{{ old('maps_url', $settings->maps_url) }}" placeholder="https://maps.google.com/...">
            </div>

            <div class="form-group">
                <label for="review_url" class="form-label">URL Ulasan Google Review</label>
                <input type="url" id="review_url" name="review_url" class="form-control" value="{{ old('review_url', $settings->review_url) }}" placeholder="https://g.page/r/.../review">
            </div>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 gap-md" style="margin-bottom: var(--space-lg);">
            <div class="form-group">
                <label for="store_lat" class="form-label">Koordinat Latitude Kedai</label>
                <input type="number" id="store_lat" name="store_lat" step="0.0000001" class="form-control" value="{{ old('store_lat', $settings->store_lat) }}">
            </div>

            <div class="form-group">
                <label for="store_lng" class="form-label">Koordinat Longitude Kedai</label>
                <input type="number" id="store_lng" name="store_lng" step="0.0000001" class="form-control" value="{{ old('store_lng', $settings->store_lng) }}">
            </div>
        </div>

        <!-- Layanan Delivery (Pesan Antar) -->
        <h3 style="font-size: 1.15rem; margin-bottom: 14px; color: #FFFFFF; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            3. Pengaturan Layanan Pesan Antar (Delivery)
        </h3>

        <div class="form-group">
            <label class="form-check" style="background: var(--surface); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <input type="checkbox" name="delivery_enabled" value="1" {{ old('delivery_enabled', $settings->delivery_enabled) ? 'checked' : '' }}>
                <div>
                    <span class="font-semibold block">Aktifkan Pilihan Layanan Delivery pada Website</span>
                    <span class="text-xs text-muted">Sesuai BR-03: default nonaktif. Hanya aktif jika pemilik siap mengantar pesanan.</span>
                </div>
            </label>
        </div>

        <div class="form-group" style="max-width: 300px;">
            <label for="delivery_radius_m" class="form-label">Batas Radius Maksimal Antar (Meter) <span class="text-primary">*</span></label>
            <input type="number" id="delivery_radius_m" name="delivery_radius_m" class="form-control" value="{{ old('delivery_radius_m', $settings->delivery_radius_m ?? 800) }}" min="100" max="10000" required>
            <span class="text-xs text-dim">Standar PRD: 800 meter dari kedai Blotongan.</span>
        </div>

        <div class="flex gap-md justify-end" style="margin-top: var(--space-xl); border-top: 1px solid var(--border); padding-top: var(--space-md);">
            <button type="submit" class="btn btn-primary btn-lg">
                Simpan Seluruh Pengaturan Kedai
            </button>
        </div>
    </form>
</div>
@endsection
