@extends('layouts.admin')

@section('title', 'Pengaturan Kedai — Panel Admin Geprek GLO')

@section('content')
<div style="margin-bottom: var(--space-lg);">
    <h2 style="font-size: 1.4rem; margin-bottom: 4px;">Pengaturan Toko &amp; Kontak</h2>
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
                <input type="hidden" name="is_open" id="settings-is-open-input" value="{{ old('is_open', $settings->is_open) ? '1' : '0' }}">
                <button type="button"
                        id="settings-store-toggle-btn"
                        class="store-state {{ $settings->is_open ? 'is-open' : 'is-closed' }}"
                        onclick="toggleStoreFromSettings()"
                        style="width: 100%; justify-content: center; min-height: 48px; font-size: 0.92rem; cursor: pointer;">
                    <span class="status-dot"></span>
                    <span id="settings-store-toggle-text">{{ $settings->is_open ? 'Kedai BUKA (Menerima Pesanan)' : 'Kedai TUTUP' }}</span>
                </button>
                <span class="text-xs text-dim" style="display: block; margin-top: 6px;">Klik tombol di atas untuk langsung mengubah status kedai (Buka / Tutup).</span>
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
            <label class="form-label">Status Layanan Delivery (Pesan Antar)</label>
            <input type="hidden" name="delivery_enabled" id="settings-delivery-input" value="{{ old('delivery_enabled', $settings->delivery_enabled) ? '1' : '0' }}">
            <button type="button"
                    id="settings-delivery-toggle-btn"
                    class="store-state {{ $settings->delivery_enabled ? 'is-open' : 'is-closed' }}"
                    onclick="toggleDeliveryFromSettings()"
                    style="width: 100%; max-width: 360px; justify-content: center; min-height: 48px; font-size: 0.92rem; cursor: pointer;">
                <span class="status-dot"></span>
                <span id="settings-delivery-toggle-text">{{ $settings->delivery_enabled ? 'Delivery AKTIF (Pesan Antar Tersedia)' : 'Delivery NONAKTIF' }}</span>
            </button>
            <span class="text-xs text-dim" style="display: block; margin-top: 6px;">Klik tombol di atas untuk langsung mengaktifkan atau menonaktifkan layanan pesan antar di website.</span>
        </div>

        <div class="form-group" style="max-width: 300px;">
            <label for="delivery_radius_m" class="form-label">Batas Radius Maksimal Antar (Meter) <span class="text-primary">*</span></label>
            <input type="number" id="delivery_radius_m" name="delivery_radius_m" class="form-control" value="{{ old('delivery_radius_m', $settings->delivery_radius_m ?? 800) }}" min="100" max="800" required>
            <span class="text-xs text-dim">Standar PRD: maksimal 800 meter dari kedai Blotongan.</span>
        </div>

        <div class="flex gap-md justify-end" style="margin-top: var(--space-xl); border-top: 1px solid var(--border); padding-top: var(--space-md);">
            <button type="submit" class="btn btn-primary btn-lg">
                Simpan Seluruh Pengaturan Kedai
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
async function toggleStoreFromSettings() {
    const btn = document.getElementById('settings-store-toggle-btn');
    const txt = document.getElementById('settings-store-toggle-text');
    const input = document.getElementById('settings-is-open-input');
    const headerBtn = document.getElementById('header-store-toggle-btn');
    const headerTxt = document.getElementById('header-store-text');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    try {
        btn.disabled = true;
        const resp = await fetch('{{ route("admin.store.toggle") }}', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        if (!resp.ok) throw new Error('Gagal memperbarui status.');
        const data = await resp.json();

        btn.classList.toggle('is-open', data.is_open);
        btn.classList.toggle('is-closed', !data.is_open);
        txt.textContent = data.is_open ? 'Kedai BUKA (Menerima Pesanan)' : 'Kedai TUTUP';
        if (input) input.value = data.is_open ? '1' : '0';

        if (headerBtn && headerTxt) {
            headerBtn.classList.toggle('is-open', data.is_open);
            headerBtn.classList.toggle('is-closed', !data.is_open);
            headerTxt.textContent = `Kedai: ${data.is_open ? 'BUKA' : 'TUTUP'}`;
        }
        if (window.showToast) window.showToast(data.message, 'success');
    } catch (e) {
        if (window.showToast) window.showToast('Status kedai gagal diperbarui.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function toggleDeliveryFromSettings() {
    const btn = document.getElementById('settings-delivery-toggle-btn');
    const txt = document.getElementById('settings-delivery-toggle-text');
    const input = document.getElementById('settings-delivery-input');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    try {
        btn.disabled = true;
        const resp = await fetch('{{ route("admin.settings.toggle-delivery") }}', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        if (!resp.ok) throw new Error('Gagal memperbarui status delivery.');
        const data = await resp.json();

        btn.classList.toggle('is-open', data.delivery_enabled);
        btn.classList.toggle('is-closed', !data.delivery_enabled);
        txt.textContent = data.delivery_enabled ? 'Delivery AKTIF (Pesan Antar Tersedia)' : 'Delivery NONAKTIF';
        if (input) input.value = data.delivery_enabled ? '1' : '0';

        if (window.showToast) window.showToast(data.message, 'success');
    } catch (e) {
        if (window.showToast) window.showToast('Status layanan delivery gagal diperbarui.', 'error');
    } finally {
        btn.disabled = false;
    }
}
</script>
@endpush
@endsection
