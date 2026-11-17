@extends('layouts.app')

@section('title', 'Geprek GLO Salatiga — Cita Rasa Ayam Crispy & Geprek Pedas Nikmat')

@section('content')
<!-- Hero Section -->
<section style="background: radial-gradient(circle at 50% 20%, rgba(215, 38, 61, 0.12), transparent 70%); padding: var(--space-2xl) 0 var(--space-xl) 0; border-bottom: 1px solid var(--border);">
    <div class="container text-center">
        <!-- Brand Tagline Badge -->
        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(215, 38, 61, 0.15); border: 1px solid rgba(215, 38, 61, 0.35); padding: 6px 16px; border-radius: var(--radius-pill); margin-bottom: var(--space-md);">
            <span style="color: var(--primary); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
                🔥 KULINER AYAM CRISPY & GEPREK BLOTONGAN
            </span>
        </div>

        <h1 style="max-width: 840px; margin: 0 auto; letter-spacing: -0.5px;">
            Sensasi Renyah Gurih &amp; <span class="text-primary">Pedas Cabai Segar</span> yang Menggugah Selera
        </h1>

        <p class="text-muted text-lg" style="max-width: 650px; margin: var(--space-md) auto var(--space-xl) auto; line-height: 1.6;">
            Ayam goreng tepung keemasan dengan tekstur krispi renyah, dipadukan ulekan cabai rawit segar khas kedai <strong>Geprek GLO Salatiga</strong>. Nikmati santap hemat, puas, dan higienis setiap hari.
        </p>

        <!-- CTA Action Buttons -->
        <div class="flex items-center justify-center gap-md flex-wrap">
            <a href="{{ route('menu') }}" class="btn btn-primary btn-lg">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Pesan Menu Sekarang
            </a>
            <a href="{{ route('kontak') }}" class="btn btn-secondary btn-lg">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Lihat Lokasi Kedai
            </a>
        </div>

        <!-- Store Status Notice in Hero -->
        <div style="margin-top: var(--space-lg); display: flex; justify-content: center;">
            <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; color: var(--text-muted); background: var(--surface); padding: 8px 16px; border-radius: var(--radius-pill); border: 1px solid var(--border);">
                @if($settings->is_open ?? true)
                    <span class="pulse-dot"></span>
                    <span>Kedai saat ini <strong class="text-ok">BUKA</strong> — Siap menerima pesanan langsung &amp; takeaway</span>
                @else
                    <span class="pulse-dot closed"></span>
                    <span>Kedai saat ini <strong class="text-off">TUTUP</strong> — Pemesanan online dapat dijadwalkan via WhatsApp</span>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Active Promos Banner Section (FR-01, FR-23) -->
@if(isset($promos) && $promos->count() > 0)
<section style="padding: var(--space-xl) 0; background: var(--bg-elevated); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-md);">
            <div class="flex items-center gap-sm">
                <span class="badge badge-promo">Promo Aktif</span>
                <h3 style="font-size: 1.25rem;">Penawaran Spesial Hari Ini</h3>
            </div>
            <a href="{{ route('menu') }}" class="text-sm text-primary font-semibold">Lihat Menu Lengkap &rarr;</a>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 gap-md">
            @foreach($promos as $promo)
            <div class="card" style="padding: var(--space-md); display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid var(--primary);">
                <div>
                    <h4 style="color: #FFFFFF; font-size: 1.1rem; margin-bottom: 6px;">{{ $promo->title }}</h4>
                    <p class="text-sm text-muted" style="line-height: 1.5;">{{ $promo->subtitle }}</p>
                </div>
                <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="text-xs text-dim">Berlaku untuk Dine-In &amp; Takeaway</span>
                    <a href="{{ route('menu') }}" class="btn btn-outline-primary btn-sm">Pesan Promo</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Menu Unggulan (Best Seller) Section (FR-01) -->
<section style="padding: var(--space-2xl) 0;">
    <div class="container">
        <div class="text-center" style="max-width: 600px; margin: 0 auto var(--space-xl) auto;">
            <span class="badge badge-best-seller" style="margin-bottom: 8px;">Pilihan Favorit Pelanggan</span>
            <h2>Menu Unggulan Geprek GLO</h2>
            <p class="text-muted text-sm" style="margin-top: 6px;">
                Menu yang paling digemari mahasiswa, warga sekitar Blotongan, dan penikmat kuliner pedas gurih.
            </p>
        </div>

        <div class="grid grid-cols-1 grid-cols-2 grid-cols-3 gap-lg">
            @forelse($featuredItems as $item)
            <div class="card flex flex-col justify-between">
                <div>
                    <!-- Product Image Placeholder Component (Section 5) -->
                    <x-product-image :src="$item->image" :alt="$item->name" />
                    
                    <div style="padding: var(--space-md);">
                        <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                            <span class="text-xs text-muted">{{ $item->category->name ?? 'Aneka Ayam' }}</span>
                            <x-badge-status :available="$item->is_available" :bestSeller="$item->is_best_seller" :package="$item->is_package" />
                        </div>
                        <h4 style="font-size: 1.1rem; margin-bottom: 6px; color: var(--text);">{{ $item->name }}</h4>
                        <p class="text-sm text-muted" style="line-height: 1.4; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $item->description ?? 'Olahan ayam gurih khas dengan bumbu racikan istimewa.' }}
                        </p>
                    </div>
                </div>

                <div style="padding: 0 var(--space-md) var(--space-md) var(--space-md); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 12px;">
                    <div>
                        <span class="text-xs text-muted">Mulai dari</span>
                        <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">
                            {{ $item->formatted_price }}
                        </div>
                    </div>
                    <a href="{{ route('menu') }}" class="btn btn-primary btn-sm">
                        Pilih Menu
                    </a>
                </div>
            </div>
            @empty
            <div style="grid-column: span 3; text-align: center; padding: 40px; color: var(--text-muted);">
                Belum ada menu unggulan yang ditandai. Silakan kunjungi katalog menu lengkap kami.
            </div>
            @endforelse
        </div>

        <div class="text-center" style="margin-top: var(--space-xl);">
            <a href="{{ route('menu') }}" class="btn btn-secondary">
                Lihat Seluruh Menu &amp; Paket Lengkap &rarr;
            </a>
        </div>
    </div>
</section>

<!-- Kategori Menu Ringkasan -->
<section style="padding: var(--space-xl) 0; background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <h3 class="text-center" style="margin-bottom: var(--space-lg); font-size: 1.35rem;">Kategori Pilihan Kami</h3>
        
        <div class="grid grid-cols-2 grid-cols-4 gap-md">
            @foreach($categories as $cat)
            <a href="{{ route('menu', ['category' => $cat->slug]) }}" class="card text-center" style="padding: var(--space-lg); text-decoration: none;">
                <div style="font-size: 2rem; margin-bottom: 8px;">
                    @if($cat->slug === 'paket-hemat') 🍱
                    @elseif($cat->slug === 'aneka-ayam') 🍗
                    @elseif($cat->slug === 'sup') 🍲
                    @else 🥤
                    @endif
                </div>
                <h4 style="font-size: 1rem; color: var(--text);">{{ $cat->name }}</h4>
                <span class="text-xs text-muted" style="margin-top: 4px; display: block;">{{ $cat->menu_items_count }} Menu Pilihan</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Informasi Layanan & Profil Singkat -->
<section style="padding: var(--space-2xl) 0;">
    <div class="container">
        <div class="grid grid-cols-1 grid-cols-2 gap-xl items-center">
            <!-- Cerita Singkat Usaha -->
            <div>
                <span class="badge badge-promo" style="margin-bottom: 10px;">Tentang Kami</span>
                <h2 style="margin-bottom: 14px;">Ayam Goreng Tepung Renyah Buatan Ibu Catur</h2>
                <p class="text-muted" style="line-height: 1.6; margin-bottom: 12px;">
                    Dirintis sejak awal tahun 2022 di Blotongan, Salatiga oleh Ibu Catur Legawaningsih. Mengusung dedikasi pada kebersihan bahan, potongan ayam segar, serta racikan bumbu rempah yang meresap hingga ke serat daging.
                </p>
                <p class="text-muted" style="line-height: 1.6; margin-bottom: 20px;">
                    Mulai tahun 2025, brand <strong>Geprek GLO</strong> hadir semakin dekat dengan pelanggan lewat kemudahan melihat katalog online dan pemesanan WhatsApp tanpa harus mengetik manual.
                </p>
                <a href="{{ route('profil') }}" class="btn btn-secondary btn-sm">
                    Baca Sejarah Lengkap Usaha &rarr;
                </a>
            </div>

            <!-- Kartu Layanan Dine-in & Takeaway (FR-11, BR-01) -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div class="card" style="padding: var(--space-md); border-left: 4px solid var(--ok);">
                    <div class="flex items-center gap-sm" style="margin-bottom: 6px;">
                        <span style="font-size: 1.4rem;">🍽️</span>
                        <h4 style="font-size: 1.05rem;">Makan di Tempat (Dine-In)</h4>
                    </div>
                    <p class="text-sm text-muted" style="line-height: 1.5;">
                        Nikmati ayam panas baru selesai digoreng bersama sambal geprek hangat langsung di kedai. Tanpa biaya tambahan apa pun.
                    </p>
                </div>

                <div class="card" style="padding: var(--space-md); border-left: 4px solid var(--primary);">
                    <div class="flex items-center gap-sm" style="margin-bottom: 6px;">
                        <span style="font-size: 1.4rem;">🛍️</span>
                        <h4 style="font-size: 1.05rem;">Bawa Pulang (Takeaway)</h4>
                    </div>
                    <p class="text-sm text-muted" style="line-height: 1.5;">
                        Dikemas higienis dengan kertas kotak kokoh dan kantong bersih. Dikenakan biaya kemasan tetap sebesar Rp{{ number_format($settings->packaging_fee ?? 1000, 0, ',', '.') }} per pesanan.
                    </p>
                </div>

                <div class="card" style="padding: var(--space-md); border-left: 4px solid var(--wa-green);">
                    <div class="flex items-center gap-sm" style="margin-bottom: 6px;">
                        <span style="font-size: 1.4rem;">📦</span>
                        <h4 style="font-size: 1.05rem;">Pesanan Kotak &amp; Rombongan</h4>
                    </div>
                    <p class="text-sm text-muted" style="line-height: 1.5;">
                        Menerima pesanan nasi kotak untuk acara keluarga, arisan, rapat kampus, atau Jumat Berkah (minimal 20 porsi). Hubungi kami melalui WhatsApp untuk konfirmasi waktu dan harga spesial.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
