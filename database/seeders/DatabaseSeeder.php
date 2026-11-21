<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChickenCut;
use App\Models\DrinkOption;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promo;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin & Demo Customer User
        User::updateOrCreate(
            ['email' => 'admin@geprekglo.com'],
            [
                'name' => 'Admin Geprek GLO',
                'phone' => '081268177729',
                'password' => 'admin123',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'pelanggan@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'password' => 'password123',
                'role' => 'customer',
            ]
        );

        // Demo cashier account
        User::updateOrCreate(
            ['email' => 'kasir@geprekglo.com'],
            [
                'name' => 'Kasir Demo',
                'phone' => '081200000001',
                'password' => 'kasir123',
                'role' => 'cashier',
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

        foreach ([
            ['code' => 'cash_pickup', 'name' => 'Cash on Pickup', 'description' => 'Bayar tunai saat mengambil pesanan.', 'sort_order' => 1],
            ['code' => 'qris', 'name' => 'QRIS', 'description' => 'Bayar melalui QRIS merchant resmi dan unggah bukti.', 'sort_order' => 2],
            ['code' => 'cod', 'name' => 'Cash on Delivery', 'description' => 'Bayar tunai kepada petugas saat pesanan diterima.', 'sort_order' => 3],
        ] as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method + ['is_active' => true]
            );
        }

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

        // 6. Menu Items (Menggunakan foto resmi & harga riil dari materi Geprek GLO)
        $menuItems = [
            // Paket Hemat & Combo
            [
                'category_id' => $catHemat->id,
                'name' => 'Paket Super Komplit',
                'description' => 'Paket terlaris super puas: Nasi hangat, Ayam Geprek sambal mantap, Telur Goreng, Sop Kaldu Ayam segar, dan Es Teh Jumbo.',
                'price' => 25000,
                'image' => 'menu/paket_super_komplit.jpg',
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $catHemat->id,
                'name' => 'Paket Geprek Komplit',
                'description' => 'Paket lengkap mantap: Nasi putih pulen, Ayam Geprek pilihan, Telur Goreng gurih, dan Es Teh Jumbo segar.',
                'price' => 20000,
                'image' => 'menu/paket_geprek_komplit.jpg',
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $catHemat->id,
                'name' => 'Paket Geprek Telur',
                'description' => 'Kombinasi juara ayam geprek pedas gurih dipadukan dengan telur dadar/ceplok goreng hangat dan nasi pulen.',
                'price' => 17000,
                'image' => 'menu/paket_geprek_telur.jpg',
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $catHemat->id,
                'name' => 'PAGEPIN (Paket Geprek Paling Murah)',
                'description' => 'Solusi hemat mahasiswa dan pelajar: Nasi putih hangat, Ayam Geprek gurih pedas, plus Es Teh cup kecil.',
                'price' => 15000,
                'image' => 'menu/pagepin.jpg',
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => true,
                'sort_order' => 4,
            ],
            [
                'category_id' => $catHemat->id,
                'name' => 'Kombo Hebat (Hemat Banget)',
                'description' => 'Nasi putih, Ayam Crispy/Geprek renyah bumbu meresap, dan Es Teh Jumbo segar dingin.',
                'price' => 15000,
                'image' => 'menu/combo_hebat.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => true,
                'sort_order' => 5,
            ],
            [
                'category_id' => $catHemat->id,
                'name' => 'Kombo Oren',
                'description' => 'Paket segar komplit: Nasi hangat, Ayam Crispy/Geprek pilihan, dipadukan dengan Es Jeruk segar atau Teh Kampul.',
                'price' => 16000,
                'image' => 'menu/kombo_oren.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => true,
                'sort_order' => 6,
            ],
            [
                'category_id' => $catHemat->id,
                'name' => 'Combo Choco',
                'description' => 'Paket spesial kekinian: Nasi, Ayam Crispy/Geprek pedas gurih, dan minuman Es Coklat Glug creamy nikmat.',
                'price' => 20000,
                'image' => 'menu/combo_choco.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => true,
                'sort_order' => 7,
            ],

            // Aneka Ayam
            [
                'category_id' => $catAyam->id,
                'name' => 'Ayam Crispy Original',
                'description' => 'Ayam goreng tepung bumbu rempah rahasia Ibu Catur, krispi kriuk di luar dan juicy lembut di dalam.',
                'price' => 8000,
                'image' => 'menu/ayam_crispy.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 8,
            ],
            [
                'category_id' => $catAyam->id,
                'name' => 'Ayam Geprek Sambal Bawang',
                'description' => 'Ayam crispy digeprek dengan ulekan cabai rawit pedas dan bawang putih segar beraroma gurih nampol.',
                'price' => 10000,
                'image' => 'menu/ayam_geprek.jpg',
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => false,
                'sort_order' => 9,
            ],
            [
                'category_id' => $catAyam->id,
                'name' => 'Ayam Crispy Bakar',
                'description' => 'Ayam crispy yang dibakar dengan olesan bumbu bakar khas manis gurih legit berkaramel.',
                'price' => 12000,
                'image' => 'menu/ayam_crispy_bakar.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 10,
            ],
            [
                'category_id' => $catAyam->id,
                'name' => 'Ayam Geprek Bakar',
                'description' => 'Perpaduan sempurna kepedasan ayam geprek dengan wangi aroma bakar yang meresap istimewa.',
                'price' => 13000,
                'image' => 'menu/ayam_geprek_bakar.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 11,
            ],

            // Sup
            [
                'category_id' => $catSup->id,
                'name' => 'Sup Kaldu Ayam Segar',
                'description' => 'Kuah kaldu ayam gurih bening beraroma seledri, rempah penghangat tubuh, wortel, buncis, dan suwiran ayam.',
                'price' => 8000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 12,
            ],
            [
                'category_id' => $catSup->id,
                'name' => 'Sup Ceker Ayam Rempah',
                'description' => 'Sup ceker ayam lembut kenyal yang dimasak empuk dengan bumbu rempah kaldu kaya rasa.',
                'price' => 7000,
                'image' => null,
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 13,
            ],

            // Minuman Glug & Segar
            [
                'category_id' => $catMinuman->id,
                'name' => 'Es Teh Jumbo',
                'description' => 'Teh manis wangi melati racikan kedai disajikan dingin segar dalam cup porsi jumbo.',
                'price' => 3500,
                'image' => 'menu/minuman_glug.jpg',
                'is_available' => true,
                'is_best_seller' => true,
                'is_package' => false,
                'sort_order' => 14,
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Es Teh Kampul Segar',
                'description' => 'Sensasi teh manis solo dipadukan dengan irisan jeruk segar yang diperas langsung.',
                'price' => 5500,
                'image' => 'menu/minuman_glug.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 15,
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Es Jeruk Peras Asli',
                'description' => 'Perasan jeruk asli manis asam segar alami penghilang rasa pedas.',
                'price' => 5500,
                'image' => 'menu/kombo_oren.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 16,
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Es Coklat Glug',
                'description' => 'Minuman coklat kental nikmat khas Glug dengan rasa creamy manis pas penyegar dahaga.',
                'price' => 8000,
                'image' => 'menu/combo_choco.jpg',
                'is_available' => true,
                'is_best_seller' => false,
                'is_package' => false,
                'sort_order' => 17,
            ],
        ];

        foreach ($menuItems as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        // 7. Initial customization data (example values; adjust from admin before launch)
        $customizationGroups = [
            'data-awal-bagian-ayam' => [
                'name' => '[Data awal] Bagian ayam',
                'required' => true,
                'options' => ['Dada', 'Paha atas', 'Paha bawah', 'Sayap'],
            ],
            'data-awal-minuman' => [
                'name' => '[Data awal] Minuman paket',
                'required' => true,
                'options' => ['Air putih', 'Es teh', 'Teh hangat'],
            ],
            'data-awal-sambal' => [
                'name' => '[Data awal] Sambal',
                'required' => true,
                'options' => ['Sambal original', 'Sambal bawang', 'Sambal ijo'],
            ],
            'data-awal-topping' => [
                'name' => '[Data awal] Topping',
                'required' => false,
                'options' => ['Tambah nasi', 'Tambah tahu', 'Tambah tempe', 'Tambah telur'],
            ],
        ];

        $seededGroups = [];
        foreach ($customizationGroups as $slug => $definition) {
            $group = ModifierGroup::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'is_required' => $definition['required'],
                    'min_selections' => $definition['required'] ? 1 : 0,
                    'max_selections' => 1,
                    'is_active' => true,
                    'sort_order' => count($seededGroups) + 1,
                ]
            );
            $seededGroups[$slug] = $group;

            foreach ($definition['options'] as $index => $name) {
                ModifierOption::updateOrCreate(
                    ['modifier_group_id' => $group->id, 'name' => $name],
                    ['price_delta' => 0, 'is_available' => true, 'sort_order' => $index + 1]
                );
            }
        }

        MenuItem::whereIn('name', ['Paket Super Komplit', 'Paket Geprek Komplit', 'Paket Geprek Telur', 'PAGEPIN (Paket Geprek Paling Murah)'])
            ->get()
            ->each(fn (MenuItem $item) => $item->modifierGroups()->sync([
                $seededGroups['data-awal-bagian-ayam']->id => ['is_required' => true, 'min_selections' => 1, 'max_selections' => 1, 'sort_order' => 1],
                $seededGroups['data-awal-minuman']->id => ['is_required' => true, 'min_selections' => 1, 'max_selections' => 1, 'sort_order' => 2],
                $seededGroups['data-awal-sambal']->id => ['is_required' => true, 'min_selections' => 1, 'max_selections' => 1, 'sort_order' => 3],
                $seededGroups['data-awal-topping']->id => ['is_required' => false, 'min_selections' => 0, 'max_selections' => 4, 'sort_order' => 4],
            ]));

        MenuItem::whereIn('name', ['Ayam Crispy Original', 'Ayam Geprek Sambal Bawang', 'Ayam Crispy Bakar', 'Ayam Geprek Bakar'])
            ->get()
            ->each(fn (MenuItem $item) => $item->modifierGroups()->sync([
                $seededGroups['data-awal-bagian-ayam']->id => ['is_required' => true, 'min_selections' => 1, 'max_selections' => 1, 'sort_order' => 1],
                $seededGroups['data-awal-sambal']->id => ['is_required' => true, 'min_selections' => 1, 'max_selections' => 1, 'sort_order' => 2],
                $seededGroups['data-awal-topping']->id => ['is_required' => false, 'min_selections' => 0, 'max_selections' => 4, 'sort_order' => 3],
            ]));

        // 8. Promos dengan banner resmi
        Promo::updateOrCreate(
            ['title' => 'Spesial Paket Super Komplit Hemat'],
            [
                'subtitle' => 'Nasi + Ayam Geprek + Telur Goreng + Sop Kaldu Ayam + Es Teh Jumbo hanya Rp25.000! Super hemat dan puas.',
                'image' => 'promos/super_komplit.jpg',
                'is_active' => true,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
            ]
        );

        Promo::updateOrCreate(
            ['title' => 'Promo Mantap Paket Geprek Telur'],
            [
                'subtitle' => 'Kombinasi ayam geprek cabai segar plus telur dadar gurih cuma Rp17.000. Wajib dicoba saat santap siang!',
                'image' => 'promos/geprek_telur.jpg',
                'is_active' => true,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(60),
            ]
        );

        Promo::updateOrCreate(
            ['title' => 'Combo Hebat Kenyang Maksimal'],
            [
                'subtitle' => 'Ayam crispy gurih kriuk + nasi putih hangat + es teh jumbo cuma Rp15.000! Ramah di kantong mahasiswa.',
                'image' => 'promos/combo_hebat.jpg',
                'is_active' => true,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(60),
            ]
        );

        // 9. Sample Active & Kitchen Orders (Information Architecture demo)
        $customerUser = User::where('email', 'pelanggan@gmail.com')->first();
        $sampleAyam = MenuItem::where('name', 'Paket Super Komplit')->first();
        $sampleGeprek = MenuItem::where('name', 'Paket Geprek Telur')->first();
        $sampleEsTeh = MenuItem::where('name', 'Es Teh Jumbo')->first();

        if ($customerUser && $sampleAyam) {
            $order1 = Order::updateOrCreate(
                ['order_number' => 'GLO-261118-0001'],
                [
                    'user_id' => $customerUser->id,
                    'customer_name' => 'Budi Santoso',
                    'customer_phone' => '081234567890',
                    'service_method' => 'dine_in',
                    'table_number' => 'Meja 3',
                    'order_notes' => 'Priority Service, Add Cutlery',
                    'subtotal' => 25000,
                    'packaging_fee' => 0,
                    'total' => 25000,
                    'status' => 'confirmed',
                    'kitchen_status' => 'prepared',
                    'source' => 'online',
                ]
            );
            $order1->items()->delete();
            $order1->items()->create([
                'menu_item_id' => $sampleAyam->id,
                'item_name' => $sampleAyam->name,
                'quantity' => 1,
                'unit_price' => 25000,
                'line_total' => 25000,
                'chicken_cut' => 'Dada',
                'drink_option' => 'Es Teh Jumbo',
                'note' => 'Cabai 5 pedas mantap',
            ]);

            $order2 = Order::updateOrCreate(
                ['order_number' => 'GLO-261118-0002'],
                [
                    'user_id' => $customerUser->id,
                    'customer_name' => 'Siti Rahma',
                    'customer_phone' => '085712345678',
                    'service_method' => 'dine_in',
                    'table_number' => 'Meja 5',
                    'order_notes' => 'Add Cutlery',
                    'subtotal' => 17000,
                    'packaging_fee' => 0,
                    'total' => 17000,
                    'status' => 'pending',
                    'kitchen_status' => 'queued',
                    'source' => 'online',
                ]
            );
            $order2->items()->delete();
            $order2->items()->create([
                'menu_item_id' => $sampleGeprek->id,
                'item_name' => $sampleGeprek->name,
                'quantity' => 1,
                'unit_price' => 17000,
                'line_total' => 17000,
                'chicken_cut' => 'Paha Atas',
                'note' => 'Telur ceplok matang',
            ]);

            $order3 = Order::updateOrCreate(
                ['order_number' => 'GLO-261118-0003'],
                [
                    'user_id' => null,
                    'customer_name' => 'Dimas Arya',
                    'customer_phone' => '081399887766',
                    'service_method' => 'takeaway',
                    'table_number' => null,
                    'order_notes' => 'Priority Service',
                    'subtotal' => 23500,
                    'packaging_fee' => 1000,
                    'total' => 24500,
                    'status' => 'ready',
                    'kitchen_status' => 'ready',
                    'source' => 'online',
                ]
            );
            $order3->items()->delete();
            $order3->items()->createMany([
                [
                    'menu_item_id' => $sampleGeprek->id,
                    'item_name' => $sampleGeprek->name,
                    'quantity' => 1,
                    'unit_price' => 17000,
                    'line_total' => 17000,
                    'chicken_cut' => 'Sayap',
                    'note' => 'Bungkus rapi',
                ],
                [
                    'menu_item_id' => $sampleEsTeh->id,
                    'item_name' => $sampleEsTeh->name,
                    'quantity' => 1,
                    'unit_price' => 3500,
                    'line_total' => 3500,
                ],
            ]);
        }
    }
}
