@extends('layouts.app')

@section('title', 'Geprek GLO Salatiga — Cita Rasa Ayam Crispy & Geprek Pedas Nikmat')

@section('content')
<section class="home-hero">
    <div class="container home-hero-grid">
        <div class="home-hero-copy">
            <span class="home-kicker">Ayam crispy &amp; geprek Blotongan</span>
            <h1>Ayam geprek, <span>hari jadi seru.</span></h1>
            <p>Ayam krispi panas, sambal cabai segar, dan pilihan paket hemat untuk makan bersama di kampus maupun di rumah.</p>
            <div class="home-hero-actions">
                <a href="{{ route('menu') }}" class="btn btn-primary btn-lg">
                    <svg aria-hidden="true" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Pesan menu sekarang
                </a>
                <a href="{{ route('kontak') }}" class="btn btn-secondary btn-lg">
                    <svg aria-hidden="true" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Lihat lokasi kedai
                </a>
            </div>
            <div class="home-store-status">
                @if($settings->is_open ?? true)
                    <span class="pulse-dot"></span>
                    <span>Kedai <strong class="text-ok">buka</strong> dan siap menerima pesanan</span>
                @else
                    <span class="pulse-dot closed"></span>
                    <span>Kedai <strong class="text-off">tutup</strong> untuk sementara</span>
                @endif
            </div>
        </div>
        <div class="home-hero-art" aria-label="Ilustrasi menu Geprek GLO">
            <div class="hero-orbit"></div>
            <div class="hero-plate"><span>GLO</span></div>
            <div class="hero-price-tag">Menu mulai<br><strong>Rp3.000</strong></div>
        </div>
    </div>
</section>

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

<section class="home-section" style="background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div class="home-section-heading">
            <div>
                <span class="home-kicker">Pilih sesuai selera</span>
                <h2>Kategori favorit</h2>
            </div>
            <a href="{{ route('menu') }}" class="text-sm text-primary font-semibold">Lihat semua menu &rarr;</a>
        </div>
        <div class="home-category-grid">
            @foreach($categories as $cat)
            <a href="{{ route('menu', ['category' => $cat->slug]) }}" class="home-category-card">
                <span class="home-category-icon" aria-hidden="true">
                    @if($cat->slug === 'paket-hemat')
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16v12H4zM8 7V5h8v2M4 11h16"/></svg>
                    @elseif($cat->slug === 'aneka-ayam')
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3c4 0 7 3 7 7 0 5-3 8-7 8a7 7 0 01-7-7c0-4 3-8 7-8zm-3 13l-3 3m11-3l3 3"/></svg>
                    @elseif($cat->slug === 'sup')
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 11h14c0 5-3 8-7 8s-7-3-7-8zm2 2h10M8 8c0-1 1-2 1-3m4 3c0-1 1-2 1-3"/></svg>
                    @else
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h10l-1 18H8L7 3zm1 5h8M10 3l1-2m4 2l-1-2"/></svg>
                    @endif
                </span>
                <h3>{{ $cat->name }}</h3>
                <span>{{ $cat->menu_items_count }} menu tersedia</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

<section style="padding: var(--space-2xl) 0;">
    <div class="container">
        <div class="grid grid-cols-1 grid-cols-2 gap-xl items-center">
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

            <div class="grid grid-cols-1 gap-md">
                <div class="home-service-card">
                    <svg aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3v8m-3-8v5a3 3 0 006 0V3m-3 8v10M17 3v18m0-18c3 2 3 7 0 9"/></svg>
                    <div>
                        <h3>Makan di Tempat</h3>
                    <p class="text-sm text-muted" style="line-height: 1.5;">
                        Nikmati ayam panas baru selesai digoreng bersama sambal geprek hangat langsung di kedai. Tanpa biaya tambahan apa pun.
                    </p>
                    </div>
                </div>
                <div class="home-service-card">
                    <svg aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14l-1 13H6L5 8zm3 0a4 4 0 018 0"/></svg>
                    <div>
                        <h3>Bawa Pulang</h3>
                    <p class="text-sm text-muted" style="line-height: 1.5;">
                        Dikemas higienis dengan kertas kotak kokoh dan kantong bersih. Dikenakan biaya kemasan tetap sebesar Rp{{ number_format($settings->packaging_fee ?? 1000, 0, ',', '.') }} per pesanan.
                    </p>
                    </div>
                </div>
                <div class="home-service-card">
                    <svg aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l9-3 9 3-9 3-9-3zm0 0v12l9 3 9-3V6M12 9v12"/></svg>
                    <div>
                        <h3>Pesanan Rombongan</h3>
                    <p class="text-sm text-muted" style="line-height: 1.5;">
                        Menerima pesanan nasi kotak untuk acara keluarga, arisan, rapat kampus, atau Jumat Berkah (minimal 20 porsi). Hubungi kami melalui WhatsApp untuk konfirmasi waktu dan harga spesial.
                    </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
