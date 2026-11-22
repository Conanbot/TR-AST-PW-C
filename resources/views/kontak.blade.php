@extends('layouts.app')

@section('title', 'Lokasi & Kontak Kedai — Geprek GLO Salatiga')

@section('content')
<!-- Header Page Section -->
<section style="background: radial-gradient(circle at 50% 10%, rgba(215, 38, 61, 0.12), transparent 75%); padding: var(--space-xl) 0 var(--space-md) 0; border-bottom: 1px solid var(--border);">
    <div class="container text-center">
        <span class="badge badge-promo" style="margin-bottom: 8px;">Informasi Kedai</span>
        <h1 style="font-size: 2.2rem; margin-bottom: 8px;">Lokasi &amp; Kontak Resmi</h1>
        <p class="text-muted text-sm" style="max-width: 600px; margin: 0 auto; line-height: 1.5;">
            Temukan lokasi kedai Geprek GLO di Blotongan, Salatiga, atau hubungi kami langsung melalui WhatsApp untuk pemesanan dan pertanyaan seputar menu.
        </p>
    </div>
</section>

<!-- Content Section -->
<section style="padding: var(--space-2xl) 0;">
    <div class="container">
        <div class="grid grid-cols-1 grid-cols-2 gap-xl items-start">
            <!-- Kolom Informasi Kontak & Jam Buka -->
            <div style="display: flex; flex-direction: column; gap: var(--space-md);">
                <!-- Status Buka Kedai -->
                @php
                    $isOpen = $settings->is_open ?? true;
                @endphp
                <div class="card" style="padding: var(--space-md);'var(--ok)' : 'var(--off)' }};">
                    <div class="flex items-center gap-sm">
                        <span class="pulse-dot {{ $isOpen ? '' : 'closed' }}"></span>
                        <h4 style="font-size: 1.1rem; color: #FFFFFF;">
                            Status Saat Ini: <span class="{{ $isOpen ? 'text-ok' : 'text-off' }}">{{ $isOpen ? 'BUKA' : 'TUTUP' }}</span>
                        </h4>
                    </div>
                    <p class="text-xs text-muted" style="margin-top: 6px; line-height: 1.4;">
                        @if($isOpen)
                            Kedai sedang beroperasi melayani makan di tempat (Dine-In) dan pesanan bawa pulang (Takeaway).
                        @else
                            Kedai saat ini sedang tutup. Pemesanan terjadwal tetap dapat dikomunikasikan melalui WhatsApp.
                        @endif
                    </p>
                </div>

                <!-- Alamat Lengkap & Petunjuk Arah -->
                <div class="card" style="padding: var(--space-lg);">
                    <div class="flex items-center gap-sm" style="margin-bottom: 8px;">
                        <i class="fa-solid fa-location-dot" aria-hidden="true" style="font-size: 1.4rem;"></i>
                        <h3 style="font-size: 1.2rem;">Alamat Kedai</h3>
                    </div>
                    <p class="text-muted" style="line-height: 1.6; margin-bottom: 14px;">
                        {{ $settings->address ?? 'Jl. Fatmawati, Blotongan, Kec. Sidorejo, Kota Salatiga, Jawa Tengah 50715' }}
                    </p>
                    <div class="flex gap-sm flex-wrap">
                        <a href="{{ $settings->maps_url ?? 'https://maps.google.com/?q=Blotongan+Salatiga' }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                            Buka Petunjuk Arah Google Maps
                        </a>
                    </div>
                </div>

                <!-- Jam Operasional -->
                <div class="card" style="padding: var(--space-lg);">
                    <div class="flex items-center gap-sm" style="margin-bottom: 8px;">
                        <i class="fa-solid fa-clock" aria-hidden="true" style="font-size: 1.4rem;"></i>
                        <h3 style="font-size: 1.2rem;">Jam Operasional</h3>
                    </div>
                    <p class="text-muted" style="line-height: 1.6; margin-bottom: 6px;">
                        {{ $settings->opening_hours ?? 'Senin - Minggu: 10:00 - 21:00 WIB' }}
                    </p>
                    <span class="text-xs text-dim">
                        *Jadwal buka dapat berubah sewaktu-waktu tergantung ketersediaan stok bahan segar dan kondisi pengelola.
                    </span>
                </div>

                <!-- WhatsApp & Media Sosial (FR-12, FR-13) -->
                <div class="card" style="padding: var(--space-lg);">
                    <div class="flex items-center gap-sm" style="margin-bottom: 8px;">
                        <i class="fa-solid fa-comment-dots" aria-hidden="true" style="font-size: 1.4rem;"></i>
                        <h3 style="font-size: 1.2rem;">Hubungi Kami Langsung</h3>
                    </div>
                    <p class="text-sm text-muted" style="line-height: 1.5; margin-bottom: 14px;">
                        Untuk pemesanan jumlah banyak, konfirmasi pesanan, atau pertanyaan seputar lokasi:
                    </p>
                    <div class="flex flex-col gap-sm">
                        <a href="https://wa.me/{{ $settings->wa_number ?? '6281268177729' }}" target="_blank" rel="noopener noreferrer" class="btn btn-wa" title="Hubungi via WhatsApp">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                            Chat WhatsApp
                        </a>

                        <a href="{{ $settings->review_url ?? 'https://maps.google.com/?q=Blotongan+Salatiga' }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                            <i class="fa-solid fa-star" aria-hidden="true"></i> Beri Ulasan di Google Review
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kolom Google Maps Embed (FR-12) -->
            <div class="card" style="padding: var(--space-md); overflow: hidden;">
                <div class="flex items-center justify-between" style="margin-bottom: 12px;">
                    <div class="flex items-center gap-sm">
                        <span class="badge badge-promo">Peta Lokasi</span>
                        <h4 style="font-size: 1.1rem; color: #FFFFFF;">Peta Tersemat Blotongan</h4>
                    </div>
                    <span class="text-xs text-muted">Salatiga, Jawa Tengah</span>
                </div>

                <!-- Google Maps Embed Iframe -->
                <div style="width: 100%; height: 420px; border-radius: var(--radius-md); overflow: hidden; background: #1C1C1F; border: 1px solid var(--border);">
                    <iframe 
                        title="Google Maps Lokasi Geprek GLO Salatiga"
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        loading="lazy" 
                        allowfullscreen 
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://maps.google.com/maps?q=-7.3117469,110.4851234&hl=id&z=15&output=embed">
                    </iframe>
                </div>
                <div class="text-xs text-dim text-center" style="margin-top: 10px;">
                    Koordinat GPS: {{ $settings->store_lat ?? '-7.3117469' }}, {{ $settings->store_lng ?? '110.4851234' }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
