<!DOCTYPE html>
<html lang="id" data-type="place" data-radius="soft" data-buttons="solid" data-motion="hover">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="orders-url" content="{{ route('orders.store') }}">
    <meta name="description" content="Website Resmi UMKM Geprek GLO Salatiga. Profil kedai, katalog aneka ayam crispy & geprek pedas, dan pemesanan cepat via WhatsApp.">
    <meta name="site-kind" content="other">
    <meta name="page-kind" content="place">
    <meta name="page-type" content="place">
    <meta name="theme-color" content="#FFF9F1">
    <title>@yield('title', 'Geprek GLO Salatiga — Cita Rasa Ayam Crispy & Geprek Pedas Nikmat')</title>

    <script>try { document.documentElement.dataset.theme = localStorage.getItem('glo-theme') || 'light'; } catch (error) { document.documentElement.dataset.theme = 'light'; }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glo.css') }}">
    @stack('styles')
</head>
<body>
    <header class="site-header">
        <div class="container flex items-center justify-between">
            <div class="flex items-center gap-md">
                <a href="{{ route('home') }}" class="flex items-center gap-sm" style="text-decoration: none;">
                    <img src="{{ asset('assets/img/brand/logo.jpg') }}" alt="Geprek GLO" style="height: 44px; width: auto; object-fit: contain;">
                </a>

                @php
                    $isOpen = $settings->is_open ?? true;
                @endphp
                <div class="badge {{ $isOpen ? 'badge-open' : 'badge-closed' }}" title="{{ $isOpen ? 'Kedai sedang beroperasi menerima pesanan' : 'Kedai sedang tutup' }}">
                    <span class="pulse-dot {{ $isOpen ? '' : 'closed' }}"></span>
                    <span>{{ $isOpen ? 'Buka' : 'Tutup' }}</span>
                </div>
            </div>

            <nav class="flex items-center gap-sm" style="display: none;" id="desktop-nav">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('profil') }}" class="nav-link {{ request()->routeIs('profil') ? 'active' : '' }}">Profil</a>
                <a href="{{ route('menu') }}" class="nav-link {{ request()->routeIs('menu') ? 'active' : '' }}">Katalog Menu</a>
                <a href="{{ route('kontak') }}" class="nav-link {{ request()->routeIs('kontak') ? 'active' : '' }}">Lokasi & Kontak</a>
                @if(auth()->check() && auth()->user()->role === 'customer')
                    <a href="{{ route('account.dashboard') }}" class="nav-link">Akun saya</a>
                @endif
            </nav>

            <style>
                @media (min-width: 860px) {
                    #desktop-nav { display: flex !important; }
                    #nav-toggle-btn { display: none !important; }
                }
            </style>

            <div class="flex items-center gap-sm">
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema" title="Ganti tema"><span aria-hidden="true">◐</span><span class="theme-toggle-label">Tema</span></button>
                @if(auth()->check() && auth()->user()->role === 'customer')
                    <a href="{{ route('account.dashboard') }}" class="btn btn-secondary btn-sm account-shortcut" title="Lihat Akun &amp; Riwayat Pesanan">
                        {{ Str::limit(auth()->user()->name, 12) }}
                    </a>
                    <form action="{{ route('account.logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Keluar dari akun">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('account.login') }}" class="btn btn-secondary btn-sm account-shortcut">
                        Masuk
                    </a>
                    <a href="{{ route('account.register') }}" class="btn btn-primary btn-sm register-shortcut">
                        Daftar
                    </a>
                @endif
                <button type="button" class="btn btn-secondary btn-sm" onclick="openCartModal()" aria-label="Buka Keranjang Pesanan" style="position: relative;">
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span style="display: none; @media (min-width: 640px) { display: inline; }">Keranjang</span>
                    <span class="cart-count-badge badge badge-best-seller" style="display: none; padding: 2px 6px; font-size: 0.7rem;">0</span>
                </button>

                <button type="button" id="nav-toggle-btn" class="btn btn-secondary btn-sm" aria-label="Menu Navigasi">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <div id="mobile-nav" class="mobile-nav">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <img src="{{ asset('assets/img/brand/logo.jpg') }}" alt="Geprek GLO" style="height: 44px; width: auto; object-fit: contain;">
            <button type="button" id="mobile-nav-close" class="close-btn" aria-label="Tutup Menu">&times;</button>
        </div>
        <div class="flex flex-col gap-md" style="font-size: 1.2rem;">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
            <a href="{{ route('profil') }}" class="nav-link {{ request()->routeIs('profil') ? 'active' : '' }}">Profil Usaha</a>
            <a href="{{ route('menu') }}" class="nav-link {{ request()->routeIs('menu') ? 'active' : '' }}">Katalog Menu</a>
            <a href="{{ route('kontak') }}" class="nav-link {{ request()->routeIs('kontak') ? 'active' : '' }}">Lokasi & Kontak</a>
            <a href="{{ route('account.login') }}" class="nav-link">Masuk pelanggan</a>
            <a href="{{ route('cashier.login') }}" class="nav-link">Portal kasir</a>
            <a href="{{ route('admin.login') }}" class="nav-link">Portal admin</a>
        </div>
        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border);">
            <div class="text-sm text-muted" style="margin-bottom: 8px;">Status Kedai Hari Ini:</div>
            <div class="badge {{ $isOpen ? 'badge-open' : 'badge-closed' }}">
                <span class="pulse-dot {{ $isOpen ? '' : 'closed' }}"></span>
                <span>{{ $isOpen ? 'Kedai Buka — Siap Melayani' : 'Kedai Sedang Tutup' }}</span>
            </div>
        </div>
    </div>

    <main style="flex: 1 0 auto;">
        @yield('content')
    </main>

    <div id="cart-modal" class="modal-overlay" onclick="handleModalOverlayClick(event)">
        <div class="modal-content" role="dialog" aria-labelledby="cart-modal-title">
            <div class="modal-header">
                <div class="flex items-center gap-sm">
                    <svg style="width: 22px; height: 22px; color: var(--primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h3 id="cart-modal-title">Keranjang Pesanan</h3>
                </div>
                <button type="button" class="close-btn" onclick="closeCartModal()" aria-label="Tutup Keranjang">&times;</button>
            </div>

            <div class="modal-body">
                <div id="checkout-closed-warning" style="display: none; background: rgba(122,122,128,0.15); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px 14px; font-size: 0.85rem; margin-bottom: 14px;"></div>
                
                <div id="cart-drawer-items">
                </div>

                <div id="checkout-order-warning" style="display: none; background: rgba(243,156,18,0.15); border: 1px solid rgba(243,156,18,0.4); border-radius: var(--radius-md); padding: 10px 14px; font-size: 0.85rem; margin-top: 14px;"></div>

                <div id="checkout-result" style="display: none; margin-top: 14px; padding: 14px; background: rgba(46, 125, 50, 0.12); border: 1px solid rgba(46, 125, 50, 0.35); border-radius: var(--radius-md);">
                    <strong>Pesanan berhasil dicatat.</strong>
                    <p id="checkout-result-number" class="text-sm" style="margin: 6px 0;"></p>
                    <p class="text-xs text-muted" style="margin: 0 0 10px;">WhatsApp belum berarti pesan sudah terkirim. Gunakan tombol di bawah untuk membuka kembali pesan order yang sama.</p>
                    <div class="flex items-center gap-sm" style="flex-wrap: wrap;">
                        <a id="checkout-result-tracking" class="btn btn-secondary btn-sm" target="_blank" rel="noopener noreferrer">Lihat tracking</a>
                        <button type="button" id="checkout-result-whatsapp" class="btn btn-wa btn-sm" onclick="Cart.retryWhatsApp()">Buka WhatsApp</button>
                    </div>
                </div>

                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label for="customer-name" class="form-label">Nama Pemesan <span class="text-primary">*</span></label>
                            <input type="text" id="customer-name" class="form-control" value="{{ auth()->check() ? auth()->user()->name : '' }}" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div class="form-group">
                            <label for="customer-phone" class="form-label">Nomor WhatsApp</label>
                            <input type="text" id="customer-phone" class="form-control" value="{{ auth()->check() ? auth()->user()->phone : '' }}" placeholder="0812xxx">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Metode Layanan <span class="text-primary">*</span></label>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <label class="form-check" style="background: var(--surface); padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                                <input type="radio" name="service_method" value="dine_in" checked onchange="Cart.renderCartDrawer()">
                                <div>
                                    <div style="font-weight: 600; font-size: 0.95rem;">Dine-In (Makan di Tempat)</div>
                                    <div class="text-xs text-muted">Tanpa biaya tambahan kemasan</div>
                                </div>
                            </label>

                            <label class="form-check" style="background: var(--surface); padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                                <input type="radio" name="service_method" value="takeaway" onchange="Cart.renderCartDrawer()">
                                <div>
                                    <div style="font-weight: 600; font-size: 0.95rem;">Takeaway (Bawa Pulang)</div>
                                    <div class="text-xs text-muted">Biaya kemasan higienis +Rp{{ number_format($settings->packaging_fee ?? 1000, 0, ',', '.') }} per pesanan</div>
                                </div>
                            </label>

                            @if(($activePaymentMethods['cod'] ?? null)?->is_active && ($settings->delivery_enabled ?? false))
                            <label class="form-check" style="background: var(--surface); padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                                <input type="radio" name="service_method" value="delivery" onchange="Cart.renderCartDrawer()">
                                <div>
                                    <div style="font-weight: 600; font-size: 0.95rem;">Delivery (Pesan Antar Kedai)</div>
                                    <div class="text-xs text-muted">Maksimal radius {{ $settings->delivery_radius_m ?? 800 }} meter dari kedai Blotongan</div>
                                </div>
                            </label>
                            @endif
                        </div>
                    </div>

                    <!-- Table Selection for Dine-In (User Flow: Select / Assign Table) -->
                    <div id="table-number-container" class="form-group" style="margin-top: 10px;">
                        <label for="table-number" class="form-label" style="display: flex; justify-content: space-between;">
                            <span>Pilih / Tetapkan Nomor Meja (Dine-In)</span>
                            <span class="text-xs text-muted">Makan di tempat</span>
                        </label>
                        <select id="table-number" class="form-control" style="background: var(--surface); color: var(--text);">
                            <option value="">-- Pilih Meja Anda --</option>
                            <option value="Meja 1">Meja 1 (Depan)</option>
                            <option value="Meja 2">Meja 2 (Depan)</option>
                            <option value="Meja 3">Meja 3 (Tengah)</option>
                            <option value="Meja 4">Meja 4 (Tengah)</option>
                            <option value="Meja 5">Meja 5 (Dalam)</option>
                            <option value="Meja 6">Meja 6 (Dalam)</option>
                            <option value="Meja 7">Meja 7 (Lesehan)</option>
                            <option value="Meja 8">Meja 8 (Lesehan)</option>
                            <option value="Meja Luar / Teras">Meja Luar / Teras</option>
                        </select>
                    </div>

                    <div id="delivery-address-container" class="form-group" style="display: none;">
                        <label for="customer-address" class="form-label">Alamat Pengantaran & Catatan Lokasi <span class="text-primary">*</span></label>
                        <textarea id="customer-address" class="form-control" placeholder="Tuliskan nama jalan, nomor rumah / kos, patokan, dll."></textarea>
                        <button type="button" class="btn btn-secondary btn-sm" style="margin-top:8px;" onclick="Cart.captureDeliveryLocation()">Gunakan lokasi perangkat</button>
                        <input type="hidden" id="delivery-lat">
                        <input type="hidden" id="delivery-lng">
                    </div>

                    <div class="form-group" style="margin-top:14px;">
                        <label class="form-label">Metode Pembayaran <span class="text-primary">*</span></label>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            @if(($activePaymentMethods['cash_pickup'] ?? null)?->is_active)
                            <label class="form-check" style="background:var(--surface);padding:10px 14px;border:1px solid var(--border);border-radius:var(--radius-md);">
                                <input type="radio" name="payment_method" value="cash_pickup" checked>
                                <div><strong>Cash on Pickup</strong><div class="text-xs text-muted">Bayar tunai saat pesanan diambil.</div></div>
                            </label>
                            @endif
                            @if(($activePaymentMethods['qris'] ?? null)?->is_active && ($settings->qris_enabled ?? false))
                            <label class="form-check" style="background:var(--surface);padding:10px 14px;border:1px solid var(--border);border-radius:var(--radius-md);">
                                <input type="radio" name="payment_method" value="qris">
                                <div><strong>QRIS</strong><div class="text-xs text-muted">Upload bukti manual untuk diverifikasi admin.</div></div>
                            </label>
                            @endif
                            @if($settings->delivery_enabled ?? false)
                            <label class="form-check" style="background:var(--surface);padding:10px 14px;border:1px solid var(--border);border-radius:var(--radius-md);">
                                <input type="radio" name="payment_method" value="cod">
                                <div><strong>Cash on Delivery</strong><div class="text-xs text-muted">Bayar tunai saat pesanan diterima.</div></div>
                            </label>
                            @endif
                        </div>
                    </div>

                    <!-- Information Architecture: Order Notes (Priority Service, Add Cutlery, Birthday Message, Table Change) -->
                    <div class="form-group" style="margin-top: 14px;">
                        <label class="form-label" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px;">Catatan Khusus Pesanan (Pilihan Cepat):</label>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px;">
                            <label class="form-check" style="background: var(--surface); padding: 6px 8px; border-radius: var(--radius-sm); border: 1px solid var(--border); font-size: 0.8rem;">
                                <input type="checkbox" name="order_note_tag" value="Priority Service">
                                <span>Layanan Cepat / Prioritas</span>
                            </label>
                            <label class="form-check" style="background: var(--surface); padding: 6px 8px; border-radius: var(--radius-sm); border: 1px solid var(--border); font-size: 0.8rem;">
                                <input type="checkbox" name="order_note_tag" value="Add Cutlery">
                                <span>Tambah Sendok &amp; Garpu</span>
                            </label>
                            <label class="form-check" style="background: var(--surface); padding: 6px 8px; border-radius: var(--radius-sm); border: 1px solid var(--border); font-size: 0.8rem;">
                                <input type="checkbox" name="order_note_tag" value="Birthday Message">
                                <span>Ucapan Ulang Tahun</span>
                            </label>
                            <label class="form-check" style="background: var(--surface); padding: 6px 8px; border-radius: var(--radius-sm); border: 1px solid var(--border); font-size: 0.8rem;">
                                <input type="checkbox" name="order_note_tag" value="Table Change">
                                <span>Permintaan Pindah Meja</span>
                            </label>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('change', function(e) {
                            if (e.target.name === 'service_method') {
                                const deliveryBox = document.getElementById('delivery-address-container');
                                const tableBox = document.getElementById('table-number-container');
                                if (deliveryBox) {
                                    deliveryBox.style.display = e.target.value === 'delivery' ? 'block' : 'none';
                                }
                                if (tableBox) {
                                    tableBox.style.display = e.target.value === 'dine_in' ? 'block' : 'none';
                                }
                            }
                        });
                    </script>
                </div>

                    <div class="form-group" style="margin-top:16px;">
                        <label for="promo-code" class="form-label">Kode Promo</label>
                        <div style="display:flex;gap:8px;">
                            <input type="text" id="promo-code" class="form-control" placeholder="Masukkan kode promo" maxlength="50" style="text-transform:uppercase;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="Cart.applyPromotion()">Terapkan</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="Cart.removePromotion()">Hapus</button>
                        </div>
                        <div id="promo-status" class="text-sm" style="margin-top:6px;min-height:18px;"></div>
                    </div>

                <div style="margin-top: 16px; padding: 14px; background: var(--surface); border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span class="text-muted">Subtotal Menu:</span>
                        <span id="checkout-subtotal" style="font-weight: 600;">Rp0</span>
                    </div>
                    <div id="checkout-fee-row" style="display: none; justify-content: space-between; font-size: 0.9rem;">
                        <span class="text-muted">Biaya Kemasan:</span>
                        <span id="checkout-fee" style="font-weight: 600;">Rp0</span>
                    </div>
                    <div id="checkout-discount-row" style="display: none; justify-content: space-between; font-size: 0.9rem;">
                        <span class="text-muted">Diskon Promo:</span>
                        <span id="checkout-discount" style="font-weight: 600;">-Rp0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.05rem; font-weight: 700; border-top: 1px solid var(--border); padding-top: 8px; margin-top: 4px;">
                        <span>Total Pesanan:</span>
                        <span id="checkout-total" style="color: var(--primary);">Rp0</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeCartModal()">Lanjut Pilih Menu</button>
                <button type="button" id="btn-checkout-wa" class="btn btn-wa" onclick="Cart.checkoutWhatsApp()">
                    <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.07-1.745-.39-1.284-.533-2.115-1.841-2.18-1.926-.065-.086-.519-.691-.519-1.319 0-.628.327-.936.443-1.063.116-.127.253-.159.338-.159s.169.004.243.008c.079.004.184-.03.288.22.109.262.373.91.406.977.034.068.056.147.011.235-.044.088-.067.143-.133.22-.066.077-.14.172-.199.231-.067.066-.137.138-.059.271.078.133.346.571.743.924.512.456.943.597 1.077.663.134.067.213.056.292-.034.08-.09.342-.397.433-.533.091-.136.183-.113.305-.068.122.045.775.366.909.433.134.067.223.1.256.155.033.057.033.328-.111.733z"/>
                    </svg>
                    Pesan via WhatsApp
                </button>
            </div>
        </div>
    </div>

    <div id="cart-floating-bar" class="cart-floating-bar" style="display: none;" onclick="openCartModal()">
        <div class="flex items-center gap-sm">
            <span class="badge badge-best-seller" style="font-size: 0.8rem; padding: 4px 8px;">
                <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.4 11.2a2 2 0 002 1.6h7.8a2 2 0 001.9-1.4L21 7H6M10 20h.01M18 20h.01"/></svg>
            </span>
            <div>
                <div class="floating-cart-count" style="font-weight: 700; font-size: 0.95rem; color: #FFFFFF;">0 menu</div>
                <div class="text-xs text-muted">Tekan untuk checkout pesanan</div>
            </div>
        </div>
        <div class="flex items-center gap-sm">
            <span class="floating-cart-total" style="font-weight: 800; color: var(--primary); font-size: 1.05rem;">Rp0</span>
            <span class="btn btn-primary btn-sm" style="border-radius: var(--radius-pill); padding: 4px 12px;">Lihat</span>
        </div>
    </div>

    <footer class="site-footer">
        <div class="container">
            <div class="grid grid-cols-1 grid-cols-2 grid-cols-4 gap-lg" style="margin-bottom: var(--space-xl);">
                <div class="footer-brand">
                    <img src="{{ asset('assets/img/brand/logo.jpg') }}" alt="Geprek GLO" style="height: 52px; width: auto; object-fit: contain; margin-bottom: 10px;">
                    <p class="text-muted text-sm" style="margin-top: 10px; max-width: 440px; line-height: 1.6;">
                        Kuliner ayam crispy renyah dan ayam geprek sambal cabai segar khas Blotongan, Salatiga. Dikelola langsung dengan sepenuh hati oleh Ibu Catur Legawaningsih untuk menyajikan kelezatan higienis, pedas mantap, dan terjangkau bagi semua.
                    </p>
                    <div style="margin-top: 14px;">
                        <span class="badge {{ $isOpen ? 'badge-open' : 'badge-closed' }}">
                            <span class="pulse-dot {{ $isOpen ? '' : 'closed' }}"></span>
                            <span>{{ $isOpen ? 'Kedai Buka Hari Ini' : 'Kedai Sedang Tutup' }}</span>
                        </span>
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 12px; color: #FFFFFF;">Navigasi</h4>
                    <div class="flex flex-col gap-sm text-sm text-muted">
                        <a href="{{ route('home') }}" class="hover-white">Beranda</a>
                        <a href="{{ route('profil') }}" class="hover-white">Profil Usaha</a>
                        <a href="{{ route('menu') }}" class="hover-white">Katalog Menu & Paket</a>
                        <a href="{{ route('kontak') }}" class="hover-white">Lokasi & Jam Buka</a>
                        <a href="{{ route('admin.login') }}" class="text-dim" style="margin-top: 6px;">Portal Admin</a>
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 12px; color: #FFFFFF;">Jam Operasional & Alamat</h4>
                    <div class="flex flex-col gap-sm text-sm text-muted">
                        <div>
                            <strong style="color: var(--text);">Jam Buka:</strong><br>
                            {{ $settings->opening_hours ?? 'Senin - Minggu: 10:00 - 21:00 WIB' }}
                        </div>
                        <div style="margin-top: 4px;">
                            <strong style="color: var(--text);">Alamat:</strong><br>
                            {{ $settings->address ?? 'Blotongan, Sidorejo, Salatiga' }}
                        </div>
                        <div style="margin-top: 8px;">
                            <a href="https://wa.me/{{ $settings->wa_number ?? '6281268177729' }}" target="_blank" class="btn btn-wa btn-sm" style="display: inline-flex;">
                                WhatsApp: +{{ $settings->wa_number ?? '6281268177729' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border); padding-top: 18px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; font-size: 0.825rem; color: var(--text-dim);">
                <div>
                    &copy; {{ date('Y') }} Geprek GLO Salatiga. Hak cipta dilindungi.
                </div>
                <div>
                    Tugas Rancang Akhir Pemrograman Web &middot; FTI UKSW Salatiga
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal Wajib Login / Daftar untuk Tamu (Gating Pemesanan & Menu) -->
    <div id="auth-required-modal" class="modal-overlay" onclick="handleAuthModalClick(event)">
        <div class="modal-content" role="dialog" aria-labelledby="auth-modal-title" style="max-width: 440px; text-align: center; padding: 28px 24px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(215, 38, 61, 0.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; color: var(--primary);">
                <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h3 id="auth-modal-title" style="margin-bottom: 8px;">Silakan Masuk atau Daftar Terlebih Dahulu</h3>
            <p class="text-sm text-muted" style="margin-bottom: 24px; line-height: 1.5;">
                Untuk memilih menu makanan/minuman dan memesan di Geprek GLO, Anda harus masuk dengan akun pelanggan. Jika belum punya akun, silakan daftar akun baru terlebih dahulu.
            </p>
            <div class="flex flex-col gap-sm">
                <a href="{{ route('account.login') }}?redirect={{ urlencode(request()->getRequestUri()) }}" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Masuk ke Akun (Login)
                </a>
                <a href="{{ route('account.register') }}" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                    Daftar Akun Baru (Register)
                </a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAuthRequiredModal()" style="margin-top: 6px; opacity: 0.7;">
                    Tutup &amp; Lihat Menu Dulu
                </button>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script>
        window.isUserLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
        window.currentUser = @json(auth()->user());

        Cart.init({
            isOpen: {{ ($settings->is_open ?? true) ? 'true' : 'false' }},
            waNumber: '{{ $settings->wa_number ?? "6281268177729" }}',
            packagingFee: {{ (int) ($settings->packaging_fee ?? 1000) }},
            deliveryEnabled: {{ ($settings->delivery_enabled ?? false) ? 'true' : 'false' }},
            deliveryRadiusM: {{ (int) ($settings->delivery_radius_m ?? 800) }},
            storeLat: {{ (float) ($settings->store_lat ?? -7.3117469) }},
            storeLng: {{ (float) ($settings->store_lng ?? 110.4851234) }}
        });

        function openAuthRequiredModal() {
            const modal = document.getElementById('auth-required-modal');
            if (modal) {
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeAuthRequiredModal() {
            const modal = document.getElementById('auth-required-modal');
            if (modal) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        function handleAuthModalClick(e) {
            if (e.target.id === 'auth-required-modal') {
                closeAuthRequiredModal();
            }
        }

        function openCartModal() {
            const modal = document.getElementById('cart-modal');
            if (modal) {
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
                Cart.renderCartDrawer();
            }
        }

        function closeCartModal() {
            const modal = document.getElementById('cart-modal');
            if (modal) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        function handleModalOverlayClick(e) {
            if (e.target.id === 'cart-modal') {
                closeCartModal();
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
