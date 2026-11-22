@extends('layouts.app')

@section('title', 'Profil Usaha & Sejarah — Geprek GLO Salatiga')

@section('content')
<section style="background: radial-gradient(circle at 50% 10%, rgba(215, 38, 61, 0.15), transparent 75%); padding: var(--space-2xl) 0 var(--space-xl) 0; border-bottom: 1px solid var(--border);">
    <div class="container text-center">
        <span class="badge badge-promo" style="margin-bottom: 10px;">Tentang Geprek GLO</span>
        <h1 style="margin-bottom: 12px;">Profil Usaha &amp; Komitmen Rasa</h1>
        <p class="text-muted text-lg" style="max-width: 680px; margin: 0 auto; line-height: 1.6;">
            Mengenal lebih dekat dedikasi kuliner Ibu Catur Legawaningsih dalam menyajikan ayam goreng renyah dan olahan geprek rempah asli di Blotongan, Salatiga.
        </p>
    </div>
</section>

<section style="padding: var(--space-2xl) 0;">
    <div class="container">
        <div class="grid grid-cols-1 grid-cols-2 gap-xl items-center" style="margin-bottom: var(--space-2xl);">
            <div>
                <span class="badge badge-best-seller" style="margin-bottom: 8px;">Sosok di Balik Rasa</span>
                <h2 style="margin-bottom: 16px;">Ibu Catur Legawaningsih</h2>
                <p class="text-muted" style="line-height: 1.7; margin-bottom: 14px;">
                    Usaha kuliner ini lahir dari ketekunan dan kecintaan Ibu Catur Legawaningsih dalam meracik hidangan rumahan yang lezat, higienis, dan ramah di kantong masyarakat luas. Sejak awal mula berdiri, seluruh proses pemilihan bahan ayam segar, marinasi bumbu, hingga penggorengan dikerjakan secara langsung dengan standar kebersihan yang tinggi.
                </p>
                <p class="text-muted" style="line-height: 1.7; margin-bottom: 20px;">
                    Bagi Ibu Catur, kepuasan pelanggan menikmati ayam yang renyah di luar namun tetap lembut dan berbumbu gurih di dalam adalah prioritas utama. Karena dikelola secara mandiri, setiap porsi disiapkan dengan ketelitian dan rasa kekeluargaan yang khas.
                </p>
                <div class="flex gap-md flex-wrap">
                    <div style="background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 18px;">
                        <span class="text-xs text-muted block">Status Usaha</span>
                        <strong style="color: #FFFFFF; font-size: 1.05rem;">UMKM Mandiri</strong>
                    </div>
                    <div style="background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 18px;">
                        <span class="text-xs text-muted block">Lokasi Utama</span>
                        <strong style="color: var(--primary); font-size: 1.05rem;">Blotongan, Salatiga</strong>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 12px; background: var(--surface-card);">
                <div class="product-image-box" style="aspect-ratio: 4/3; border-radius: var(--radius-md);">
                    <div class="placeholder-content">
                        <svg class="placeholder-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="placeholder-text" style="font-weight: 500;">Dokumentasi Kedai &amp; Dapur Geprek GLO</span>
                        <span class="text-xs text-dim">Foto aset resmi dalam tahap persiapan</span>
                    </div>
                </div>
                <div class="text-xs text-dim text-center" style="margin-top: 8px;">
                    Kedai berlokasi di wilayah Blotongan, Sidorejo, Kota Salatiga
                </div>
            </div>
        </div>

        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: var(--space-xl); margin-bottom: var(--space-2xl);">
            <div class="text-center" style="max-width: 650px; margin: 0 auto var(--space-xl) auto;">
                <span class="badge badge-promo" style="margin-bottom: 8px;">Perjalanan Waktu</span>
                <h2>Kronologi Usaha</h2>
                <p class="text-muted text-sm" style="margin-top: 6px;">
                    Jejak langkah ketekunan Ibu Catur Legawaningsih dalam merintis dan mengembangkan usaha kuliner ayam hingga kini dikenal sebagai Geprek GLO.
                </p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px; max-width: 780px; margin: 0 auto; position: relative;">
                <div class="card" style="padding: var(--space-md); background: var(--surface-card);">
                    <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                        <h4 style="color: var(--primary); font-size: 1.15rem;">Awal 2022 — Titik Awal di Blotongan</h4>
                        <span class="badge" style="background: rgba(255,255,255,0.06);">Tahun Rintisan</span>
                    </div>
                    <p class="text-sm text-muted" style="line-height: 1.6;">
                        Usaha kuliner ayam goreng mulai dirintis secara mandiri oleh Ibu Catur Legawaningsih di kawasan Blotongan, Salatiga. Fokus awal adalah melayani warga sekitar dan mahasiswa yang mencari makanan lezat dengan harga bersahabat.
                    </p>
                </div>

                <div class="card" style="padding: var(--space-md); background: var(--surface-card);">
                    <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                        <h4 style="color: var(--warning); font-size: 1.15rem;">Periode Ngawen — Menjajaki Pasar Baru</h4>
                        <span class="badge" style="background: rgba(255,255,255,0.06);">Pengembangan</span>
                    </div>
                    <p class="text-sm text-muted" style="line-height: 1.6;">
                        Dalam perjalanan usahanya, kegiatan operasional kedai sempat berpindah ke wilayah Ngawen untuk memperluas jangkauan pembeli dan mengasah variasi menu racikan bumbu ayam.
                    </p>
                </div>

                <div class="card" style="padding: var(--space-md); background: var(--surface-card);">
                    <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                        <h4 style="color: var(--info); font-size: 1.15rem;">Tahun 2024 — Kembali Beroperasi di Blotongan</h4>
                        <span class="badge" style="background: rgba(255,255,255,0.06);">Kembali ke Akar</span>
                    </div>
                    <p class="text-sm text-muted" style="line-height: 1.6;">
                        Kedai kembali beroperasi menetap di kawasan Blotongan, Salatiga. Lokasi yang strategis dan basis pelanggan setia yang sudah mengenal cita rasa masakan Ibu Catur menyambut hangat kehadiran kembali kedai ini.
                    </p>
                </div>

                <div class="card" style="padding: var(--space-md); background: var(--surface-card);">
                    <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                        <h4 style="color: var(--ok); font-size: 1.15rem;">Tahun 2025 — Kelahiran Brand &amp; Produk Geprek GLO</h4>
                        <span class="badge badge-open">Transformasi Modern</span>
                    </div>
                    <p class="text-sm text-muted" style="line-height: 1.6;">
                        Produk dan identitas yang secara khusus dikenal sebagai <strong>Geprek GLO</strong> mulai resmi dikembangkan. Diperkuat dengan digitalisasi katalog web terpadu untuk memudahkan pelanggan melihat menu dan memesan via WhatsApp.
                    </p>
                </div>
            </div>
        </div>

        <div style="margin-bottom: var(--space-2xl);">
            <div class="text-center" style="max-width: 650px; margin: 0 auto var(--space-xl) auto;">
                <span class="badge badge-promo" style="margin-bottom: 8px;">Ciri Khas Kuliner</span>
                <h2>Keunikan Menu Geprek GLO</h2>
                <p class="text-muted text-sm" style="margin-top: 6px;">
                    Setiap menu memiliki karakter olahan yang berbeda untuk memenuhi selera penikmat ayam goreng.
                </p>
            </div>

            <div class="grid grid-cols-1 grid-cols-2 grid-cols-4 gap-md">
                <div class="card" style="padding: var(--space-md);">
                    <div class="home-category-icon" style="margin-bottom: 8px;" aria-hidden="true"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3c4 0 7 3 7 7 0 5-3 8-7 8a7 7 0 01-7-7c0-4 3-8 7-8zm-3 13l-3 3m11-3l3 3"/></svg></div>
                    <h4 style="margin-bottom: 6px; font-size: 1rem;">Ayam Crispy Original</h4>
                    <p class="text-xs text-muted" style="line-height: 1.5;">
                        Tepung berbumbu rempah dengan teknik penggorengan suhu presisi menghasilkan lapisan krispi keriting yang tahan renyah dan gurih alami.
                    </p>
                </div>

                <div class="card" style="padding: var(--space-md);">
                    <div class="home-category-icon" style="margin-bottom: 8px;" aria-hidden="true"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 4c3 4 1 8-3 11-3 2-6 1-7-1 4 0 6-2 7-5 1-2 2-4 3-5zm-1 1c2-1 4-1 5 0"/></svg></div>
                    <h4 style="margin-bottom: 6px; font-size: 1rem;">Ayam Geprek Cabai Segar</h4>
                    <p class="text-xs text-muted" style="line-height: 1.5;">
                        Ayam crispy panas diulek langsung bersama cabai rawit merah segar dan bawang putih tanpa pengawet. Pedas segar nendang.
                    </p>
                </div>

                <div class="card" style="padding: var(--space-md);">
                    <div class="home-category-icon" style="margin-bottom: 8px;" aria-hidden="true"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 3c2 4-1 6 2 9 2-2 2-4 2-4 3 4 2 9-2 11H8c-4-2-5-7-2-10 1 3 3 3 4 2-1-4 1-6 3-8z"/></svg></div>
                    <h4 style="margin-bottom: 6px; font-size: 1rem;">Ayam Crispy Bakar</h4>
                    <p class="text-xs text-muted" style="line-height: 1.5;">
                        Ayam renyah yang dibalut olesan saus bakar kecap gurih aromatik dan dipanggang sebentar untuk menghasilkan aroma manis berasap.
                    </p>
                </div>

                <div class="card" style="padding: var(--space-md);">
                    <div style="font-size: 1.8rem; margin-bottom: 8px;"><i class="fa-solid fa-bowl-food" aria-hidden="true"></i></div>
                    <h4 style="margin-bottom: 6px; font-size: 1rem;">Ayam Geprek Bakar</h4>
                    <p class="text-xs text-muted" style="line-height: 1.5;">
                        Kombinasi istimewa antara rasa pedas ulekan cabai geprek dengan karamelisasi bumbu bakar panggangan yang menggoyang lidah.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <h3 style="margin-bottom: 12px;">Ingin Mencicipi Kelezatannya Hari Ini?</h3>
            <p class="text-muted text-sm" style="margin-bottom: 20px;">
                Lihat daftar harga dan paket hemat kami sekarang juga.
            </p>
            <a href="{{ route('menu') }}" class="btn btn-primary btn-lg">
                Buka Katalog Menu Lengkap &rarr;
            </a>
        </div>
    </div>
</section>
@endsection
