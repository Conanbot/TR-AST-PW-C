# Website Profil & Katalog Pemesanan UMKM Geprek GLO Salatiga
> **Tugas Rancang Akhir Pemrograman Web · Teknik Informatika, FTI UKSW Salatiga**  
> **Turunan Terpadu dari Product Requirements Document (PRD) v1.1**

---

## 1. Gambaran Proyek

**Geprek GLO** merupakan usaha kuliner ayam crispy dan ayam geprek milik **Ibu Catur Legawaningsih** yang berlokasi di Blotongan, Sidorejo, Kota Salatiga. Usaha ini dirintis sejak awal 2022 dan hampir seluruh kegiatan operasional ditangani langsung oleh pemilik.

Aplikasi web ini dikembangkan sebagai:
1. **Pusat Informasi Resmi:** Menyatukan informasi jam operasional, lokasi, profil usaha, dan media sosial dalam satu tautan resmi.
2. **Katalog Digital Interaktif:** Menampilkan aneka varian menu ayam crispy, ayam geprek, paket hemat, sup, dan minuman dengan pengalaman pemesanan bergaya aplikasi modern.
3. **Pemesanan WhatsApp Cepat:** Menyusun rincian pesanan (varian potongan ayam, minuman paket, jumlah, catatan, metode layanan) menjadi format pesan otomatis WhatsApp (`wa.me`) tanpa perlu pengetikan manual.
4. **Panel Admin Pengelola Mobile-First:** Memberikan kemudahan bagi Ibu Catur untuk mengubah status kedai (Buka/Tutup dalam 1 ketukan), memperbarui stok habis, mengganti harga, dan mengunggah foto melalui ponsel.

---

## 2. Tim Pengembang & Pembagian Peran

| Anggota Tim | NIM | Peran Utama | Wilayah Kerja |
|---|---|---|---|
| **Nicholas Karsono** | 672025037 | Tech Lead & Back-end | Setup arsitektur, migrasi database, model Eloquent, controller, API JSON, autentikasi sesi, middleware, validasi upload foto, keamanan sistem. |
| **Dava Rafael Nugraha** | 672025038 | Front-end Publik | Desain token CSS responsif, layout Blade, antarmuka beranda, profil, katalog menu, modal varian produk, keranjang belanja (localStorage), kalkulator kemasan & jarak Haversine. |
| **Jonathan Firnand Firdaus** | 672025031 | Panel Admin & QA | Antarmuka panel admin mobile-first, dashboard status, CRUD menu & promo, toggle stok cepat, pengujian fungsional dan eksekusi test case (T-01 s.d. T-17). |
| **Elia Hans Hayudyo Purwanto** | 672025047 | Dokumentasi & Konten | Penyusunan laporan Bab 2–7, akurasi kronologi sejarah usaha, perancangan wireframe low-fidelity, dokumentasi README, dan materi demo sistem. |

---

## 3. Teknologi yang Digunakan

* **Backend Framework:** Laravel 10 (PHP 8.1 / 8.5)
* **Frontend Engine:** Blade Template, HTML5, Modern CSS3 (CSS Variables, Flexbox, CSS Grid), Vanilla JavaScript ES6
* **Basis Data:** MySQL 8.0 (Laragon) / phpMyAdmin (disertai dump `database/geprek_glo.sql`)
* **Lingkungan Pengembangan:** Laragon (Apache, MySQL, PHP 8.x)
* **Penyimpanan Berkas:** Laravel Storage (Simbolik link `storage:link` untuk upload foto menu & promo)
* **Integrasi WhatsApp:** WhatsApp Click to Chat (`wa.me`) via `encodeURIComponent`
* **Peta & Lokasi:** Google Maps Iframe Embed & Tombol Petunjuk Arah
* **Testing:** PHPUnit Automated Test Suite

---

## 4. Panduan Instalasi & Menjalankan di Laragon

### A. Persyaratan Sistem
* Laragon (dengan Apache/Nginx, MySQL, dan PHP >= 8.1)
* Composer 2.x
* Git

### B. Langkah Instalasi Langkah demi Langkah

1. **Letakkan Proyek pada Folder `www` Laragon:**
   ```bash
   # Pastikan repositori berada di dalam c:\laragon\www\...
   cd "C:\laragon\www\Belajar PHP Ast PW C\TR AST PW C\TR-AST-PW-C"
   ```

2. **Pasang Dependensi Composer (jika belum terpasang):**
   ```bash
   composer install
   ```

3. **Konfigurasi Lingkungan (`.env`):**
   Salin berkas `.env.example` menjadi `.env`:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. **Buat Database di Laragon / phpMyAdmin:**
   * Buka browser dan akses `http://localhost/phpmyadmin`
   * Buat database baru bernama: `geprek_glo` (Collation: `utf8mb4_unicode_ci`)
   * Konfigurasi pada `.env` sudah disesuaikan dengan standar Laragon:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=geprek_glo
     DB_USERNAME=root
     DB_PASSWORD=
     ```

5. **Jalankan Migrasi & Seeder Database:**
   ```bash
   php artisan migrate --seed
   ```
   *Alternatif via phpMyAdmin:* Anda juga dapat langsung mengimpor berkas dump SQL yang telah disiapkan di `database/geprek_glo.sql`.

6. **Hubungkan Storage Simbolik (Penyimpanan Foto Upload):**
   ```bash
   php artisan storage:link
   ```

7. **Jalankan Aplikasi:**
   * Jika menggunakan fitur Auto-VirtualHost Laragon: akses `http://tr-ast-pw-c.test`
   * Atau jalankan server bawaan Laravel:
     ```bash
     php artisan serve
     ```
     Buka browser di `http://127.0.0.1:8000`

---

## 5. Kredensial Akun Admin Pengujian

Untuk mengakses panel admin:
* **URL Login:** `http://127.0.0.1:8000/admin/login`
* **Username:** `admin`
* **Password:** `admin123`

### Area Login Berdasarkan Peran

* **Admin:** `/admin/login` — mengelola menu, pengaturan kedai, dan akun kasir.
* **Kasir:** `/kasir/login` — akun kasir dibuat oleh admin dari panel pengelolaan kasir; kasir menggunakan dashboard POS di `/kasir`.
* **Pelanggan:** `/akun/login` — pelanggan dapat membuat akun di `/akun/daftar` dan melihat riwayat pesanan di `/akun`.

Pesanan pelanggan maupun kasir disimpan pada tabel `orders` dan `order_items`. Jika menyiapkan database melalui phpMyAdmin, impor `database/geprek_glo.sql`; dump tersebut menyertakan skema peran dan pesanan, tanpa data pesanan contoh. Jika memakai migrasi, jalankan `php artisan migrate --seed`.

---

## 6. Struktur Folder Proyek

```
TR-AST-PW-C/
├── app/
│   ├── Console/Commands/        # Perintah ekspor SQL dump (ExportSqlDump)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php       # Beranda publik (hero, promo, menu unggulan)
│   │   │   ├── ProfileController.php    # Profil usaha, kronologi 2022-2025, keunikan produk
│   │   │   ├── MenuController.php       # Katalog lengkap, filter kategori, detail varian
│   │   │   ├── ContactController.php    # Peta Google Maps, petunjuk arah, kontak WA
│   │   │   ├── ApiController.php        # JSON /api/menu dan /api/settings (PRD Section 4)
│   │   │   └── Admin/
│   │   │       ├── AuthController.php        # Login & logout sesi admin terproteksi
│   │   │       ├── DashboardController.php   # Dashboard statistik & 1-tap store toggle
│   │   │       ├── StockController.php       # Kelola stok cepat menu/potongan/minuman
│   │   │       ├── MenuItemController.php    # CRUD menu & upload foto
│   │   │       ├── PromoController.php       # CRUD banner promo
│   │   │       └── SettingController.php     # Pengaturan kedai, WA, biaya kemasan
│   │   ├── Requests/            # Validasi form request ketat (MenuItem, Setting, Promo)
│   │   └── Middleware/          # Proteksi autentikasi (Authenticate) & throttle
│   └── Models/                  # Eloquent models (User, StoreSetting, Category, MenuItem, dll)
├── database/
│   ├── migrations/              # Skema tabel database Laravel
│   ├── seeders/                 # Seeder data awal (admin, kategori, menu, promo, setting)
│   └── geprek_glo.sql           # Dump SQL siap impor phpMyAdmin
├── public/
│   ├── assets/
│   │   ├── css/tokens.css       # Token desain UI kuliner gelap, tipografi, komponen
│   │   └── js/
│   │       ├── app.js           # Utilitas toast notifikasi & navbar responsif
│   │       └── cart.js          # Mesin keranjang belanja, checkout WA & Haversine
├── resources/views/
│   ├── components/              # Komponen reusable (product-image 4:3 placeholder, badge)
│   ├── layouts/
│   │   ├── app.blade.php        # Layout utama publik dengan live store status & floating cart
│   │   └── admin.blade.php      # Layout dashboard admin mobile-first
│   ├── home.blade.php           # Halaman beranda
│   ├── profil.blade.php         # Halaman profil usaha
│   ├── menu.blade.php           # Halaman katalog menu & modal varian
│   ├── kontak.blade.php         # Halaman kontak, jam buka & Google Maps
│   └── admin/                   # Tampilan panel admin (login, dashboard, stock, menu, promo, setting)
├── routes/
│   └── web.php                  # Rute publik, API kontrak, dan rute grup admin terproteksi
├── storage/app/public/          # Folder upload gambar menu dan banner promo
└── tests/                       # Automated Feature Tests (14 test cases, 73 assertions)
```

---

## 7. Rangkuman Aturan Bisnis & Fitur Utama

1. **Alur Pelanggan & Gating Pemesanan (User Flow):**
   * Pelanggan baru **wajib mendaftar (Register)** terlebih dahulu &rarr; data akun dan nomor telepon tersimpan di database MySQL &rarr; diarahkan ke halaman **Login** &rarr; masuk menggunakan kredensial baru &rarr; barulah pelanggan dapat memilih menu, menentukan varian, dan melakukan pemesanan.
   * Pelanggan yang belum login (tamu/guest) atau ter-logout akan dicegat oleh modal interaktif (*Auth Required Modal*) saat mencoba memilih varian menu, menambahkan ke keranjang, atau membuka keranjang.
   * Alur navigasi mengikuti User Flow: `Login -> Home -> Select / Assign Table | Open Existing Order | Modify Table -> Order Type -> Menu Browser`.
2. **Penetapan Meja Dine-In (Table Assignment):**
   * Pelanggan yang login dapat menetapkan nomor meja (Meja 1 s.d. Meja 8, Meja Teras/Lesehan) langsung dari bar sesi pelanggan. Nomor meja otomatis tersinkronisasi ke keranjang belanja dan struk pesanan WhatsApp.
3. **Information Architecture (Dashboard Operasional 4 Pilar):**
   * **Orders:** Menampilkan Active Orders, ringkasan Sales Overview (*Daily Revenue*, *Peak Hours: 11:00-14:00 & 17:00-20:00*, *Best Sellers*, *Order Volume*), dan aksi cepat pembaruan status.
   * **Menu Categories:** Mengelompokkan Menu Items, Food Items (Paket, Ayam, Sup), serta katalog **Add-Ons** resmi (*Nasi Daun Jeruk +2K, Telur Goreng +5K, Sambal Bawang, Sambal Ijo, Sambal Bajak, Sambal Matah*).
   * **Kitchen (Dapur):** Kanban Cooking Queue 3 tahap interaktif (*Queued &rarr; Prepared &rarr; Ready*), rincian Ready Orders per nomor meja, serta daftar pesanan selesai (*Completed Orders*).
   * **Support & Notes:** Pelacak *Order Notes* pelanggan (*Priority Service, Add Cutlery, Birthday Message, Table Change*), *Customer Requests*, dan *Staff Messages* (*Kitchen Delay & Item Unavailable*).
4. **Implementasi Foto Resmi Menu & Promo:**
   * Seluruh foto produk asli hasil kurasi kedai (Ayam Crispy, Ayam Geprek, Paket Super Komplit, Paket Geprek Telur, PAGEPIN, Kombo Oren, Combo Choco, Minuman Glug, dll.) dan banner promo telah diintegrasikan langsung pada direktori publik (`public/assets/img/menu/`, `public/assets/img/promos/`, `public/assets/img/brand/logo.jpg`), penyimpanan storage, dan basis data `database/geprek_glo.sql`.
5. **Keranjang Bersih & Bebas Slop Emoji:**
   * Seluruh ornamen emoji pada kartu keranjang, badge varian potongan ayam, dan minuman paket telah dibersihkan menjadi badge teks terstruktur yang rapi, profesional, dan elegan.
6. **Biaya Kemasan Takeaway (BR-01):** Dikenakan Rp1.000 flat per pesanan bungkus; Dine-in dikenakan Rp0 (tanpa biaya kemasan).
7. **Pilihan Potongan Ayam & Minuman Paket (BR-08, FR-07, FR-08):**
   * Menu ayam wajib memilih potongan (Dada, Paha Atas, Paha Bawah, Sayap). Potongan yang habis tidak dapat dipilih.
   * Menu Paket Hemat wajib memilih varian minuman (Es Teh, Teh Hangat, Air Mineral).
8. **Pemesanan Rombongan (BR-04):** Pesanan $\ge 20$ porsi otomatis menampilkan pemberitahuan khusus untuk negosiasi jadwal masak langsung melalui WhatsApp.
9. **Status Kedai Manual (BR-05):** Pengelola dapat mengubah status kedai menjadi Buka atau Tutup dalam hitungan detik lewat sakelar admin.
10. **Layanan Antar (Delivery) (BR-03, FR-14):** Layanan pesan antar berstatus *default nonaktif* (hanya muncul saat diaktifkan admin) dengan validasi radius maksimal 800 meter menggunakan rumus Haversine.

---

## 8. Verifikasi Pengujian Otomatis

Proyek dilengkapi automated feature testing yang memverifikasi seluruh alur pengguna, keamanan, dan integrasi arsitektur informasi:
```bash
php artisan test
# atau
vendor/bin/phpunit
```
**Hasil Verifikasi:** `OK (25 tests, 132 assertions)` lulus 100%.
* `T-01`: Semua navigasi halaman publik berhasil dibuka (200 OK).
* `T-02`: Alur Registrasi Pelanggan &rarr; Simpan DB &rarr; Redirect Login &rarr; Login Pelanggan &rarr; Akses Menu.
* `T-03`: Sakelar status kedai admin berhasil mengubah status publik.
* `T-04`: Pengubahan stok ketersediaan menu berfungsi.
* `T-05`: Alur Kitchen Queue (*queued* &rarr; *prepared* &rarr; *ready* &rarr; *completed*) berfungsi akurat.
* `T-06`: Penyimpanan nomor meja dan catatan khusus (*Order Notes: Priority Service, Add Cutlery*) pada pesanan.
* `T-07 & T-08`: Kalkulasi biaya kemasan Takeaway (+Rp1.000) dan Dine-In (+Rp0) akurat.
* `T-11`: Akses halaman admin tanpa sesi login dialihkan ke form login admin.
* `T-12`: Pemisahan login berdasarkan peran (Admin, Kasir, Pelanggan) terproteksi ketat.
* `T-13 & T-14`: Validasi upload foto menolak file non-gambar/PHP berbahaya dan menolak ukuran > 2 MB.
* `T-15`: Proteksi SQL Injection pada form login melalui prepared statements Eloquent.
* Kontrak Endpoint API JSON `/api/menu` dan `/api/settings` sesuai spesifikasi kontrak PRD.
