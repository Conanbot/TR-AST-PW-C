<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChickenCut;
use App\Models\DrinkOption;
use App\Models\MenuItem;
use App\Models\Promo;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Geprek GLO',
                'email' => 'admin@geprekglo.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // 2. Store Settings
        StoreSetting::updateOrCreate(
            ['id' => 1],
            [
                'is_open' => true,
                'wa_number' => '6281268177729',
                'address' => 'Jl. Fatmawati, Blotongan, Kec. Sidorejo, Kota Salatiga, Jawa Tengah 50715',
                'opening_hours' => 'Senin - Minggu: 10:00 - 21:00 WIB',
                'packaging_fee' => 1000,
                'maps_url' => 'https://maps.google.com/?q=Blotongan+Salatiga',
                'review_url' => 'https://maps.google.com/?q=Blotongan+Salatiga',
                'store_lat' => -7.3117469,
                'store_lng' => 110.4851234,
                'delivery_enabled' => false,
                'delivery_radius_m' => 800,
            ]
        );

        // 3. Categories
        $catHemat = Category::updateOrCreate(
            ['slug' => 'paket-hemat'],
            ['name' => 'Paket Hemat', 'sort_order' => 1]
        );

        $catAyam = Category::updateOrCreate(
            ['slug' => 'aneka-ayam'],
            ['name' => 'Aneka Ayam', 'sort_order' => 2]
        );

        $catSup = Category::updateOrCreate(
            ['slug' => 'sup'],
            ['name' => 'Sup', 'sort_order' => 3]
        );

        $catMinuman = Category::updateOrCreate(
            ['slug' => 'minuman'],
            ['name' => 'Minuman', 'sort_order' => 4]
        );

        // 4. Chicken Cuts
        $cuts = [
            ['name' => 'Dada', 'is_available' => true, 'sort_order' => 1],
            ['name' => 'Paha Atas', 'is_available' => true, 'sort_order' => 2],
            ['name' => 'Paha Bawah', 'is_available' => true, 'sort_order' => 3],
            ['name' => 'Sayap', 'is_available' => true, 'sort_order' => 4],
        ];
        foreach ($cuts as $cut) {
            ChickenCut::updateOrCreate(['name' => $cut['name']], $cut);
        }

        // 5. Drink Options
        $drinks = [
            ['name' => 'Es Teh', 'is_available' => true, 'sort_order' => 1],
            ['name' => 'Teh Hangat', 'is_available' => true, 'sort_order' => 2],
            ['name' => 'Air Mineral', 'is_available' => true, 'sort_order' => 3],
        ];
        foreach ($drinks as $drink) {
            DrinkOption::updateOrCreate(['name' => $drink['name']], $drink);
        }

        // 6. Menu Items (Data ilustrasi bertanda placeholder sesuai PRD)
        $menuItems = [
            // Paket Hemat
            [
                'category_id' => $catHemat->id,
                'name' => 'Paket Hemat Ayam Crispy',
                'description' => 'Satu porsi nasi hangat, ayam crispy renyah gurih, sambal pilihan, dan minuman paket segar.',
                'price' => 11000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $catHemat->id,
                'name' => 'Paket Hemat Ayam Geprek',
                'description' => 'Nasi putih pulen, ayam goreng renyah digeprek dengan cabai rawit segar khas GLO, plus minuman paket.',
                'price' => 13000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $catHemat->id,
                'name' => 'Paket Hemat Ayam Crispy Bakar',
                'description' => 'Ayam crispy berbalut racikan kecap rempah bakar aroma manis gurih istimewa, nasi, dan minuman paket.',
                'price' => 14000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => true,
                'sort_order' => 3,
            ],

            // Aneka Ayam
            [
                'category_id' => $catAyam->id,
                'name' => 'Ayam Crispy Original (Ala Carte)',
                'description' => 'Potongan ayam pilihan digoreng tepung keemasan dengan tekstur kriuk tahan lama dan bumbu meresap.',
                'price' => 8000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 4,
            ],
            [
                'category_id' => $catAyam->id,
                'name' => 'Ayam Geprek Cabai Segar',
                'description' => 'Ayam crispy digeprek langsung dengan cabai rawit merah pedas nendang (level pedas bisa request di catatan).',
                'price' => 10000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => false,
                'sort_order' => 5,
            ],
            [
                'category_id' => $catAyam->id,
                'name' => 'Ayam Crispy Bakar Spesial',
                'description' => 'Kombinasi tekstur krispi di luar dengan karamelisasi bumbu bakar manis gurih yang meresap.',
                'price' => 11000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 6,
            ],
            [
                'category_id' => $catAyam->id,
                'name' => 'Ayam Geprek Bakar',
                'description' => 'Paduan unik ayam geprek pedas dengan sentuhan panggangan beraroma asap yang menggugah selera.',
                'price' => 12000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 7,
            ],

            // Sup
            [
                'category_id' => $catSup->id,
                'name' => 'Sup Sayur Hangat',
                'description' => 'Semangkuk sup berkuah kaldu ayam bening gurih dengan irisan wortel, buncis, kembang kol, dan seledri.',
                'price' => 5000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 8,
            ],
            [
                'category_id' => $catSup->id,
                'name' => 'Sup Ceker Ayam Gurih',
                'description' => 'Sup ceker ayam lembut yang dimasak empuk dengan rempah kuah kaldu segar penghangat tubuh.',
                'price' => 7000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 9,
            ],

            // Minuman
            [
                'category_id' => $catMinuman->id,
                'name' => 'Es Teh Manis',
                'description' => 'Seduhan teh melati wangi dengan es kristal dingin yang menyegarkan.',
                'price' => 3000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 10,
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Teh Hangat Manis',
                'description' => 'Teh hangat beraroma wangi melati alami untuk menemani santap santai.',
                'price' => 3000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 11,
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Air Mineral Botol',
                'description' => 'Air mineral kemasan botol higienis dan menyegarkan.',
                'price' => 4000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 12,
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Es Jeruk Peras',
                'description' => 'Jeruk asli diperas segar dengan rasa manis asam alami penyegar dahaga.',
                'price' => 4000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 13,
            ],
        ];

        foreach ($menuItems as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        // 7. Promos
        Promo::updateOrCreate(
            ['title' => 'Spesial Menu Paket Hemat Blotongan'],
            [
                'subtitle' => 'Nikmati Paket Hemat Ayam Crispy komplit nasi dan minuman mulai Rp11.000! Lezat, puas, dan ramah di kantong.',
                'image' => null,
                'is_active' => true,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
            ]
        );

        Promo::updateOrCreate(
            ['title' => 'Pemesanan Rombongan & Jumat Berkah'],
            [
                'subtitle' => 'Menerima pesanan nasi kotak / rombongan (min. 20 porsi). Hubungi kami via WhatsApp untuk harga & koordinasi pengiriman.',
                'image' => null,
                'is_active' => true,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(60),
            ]
        );
    }
}
