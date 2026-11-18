-- Dump Database Geprek GLO Salatiga
-- Digenerate untuk phpMyAdmin / MySQL Laragon
-- Waktu: 2026-11-18 07:31:38

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'Admin Geprek GLO',
  `username` varchar(255) NOT NULL UNIQUE,
  `email` varchar(255) DEFAULT NULL UNIQUE,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `store_settings`;
CREATE TABLE `store_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `wa_number` varchar(255) NOT NULL DEFAULT '6281268177729',
  `address` text DEFAULT NULL,
  `opening_hours` varchar(255) NOT NULL DEFAULT 'Setiap Hari: 10:00 - 21:00 WIB',
  `packaging_fee` int unsigned NOT NULL DEFAULT '1000',
  `maps_url` text DEFAULT NULL,
  `review_url` text DEFAULT NULL,
  `store_lat` decimal(10,7) DEFAULT NULL,
  `store_lng` decimal(10,7) DEFAULT NULL,
  `delivery_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `delivery_radius_m` int unsigned NOT NULL DEFAULT '800',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int unsigned NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `is_best_seller` tinyint(1) NOT NULL DEFAULT '0',
  `is_package` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_category_id_foreign` (`category_id`),
  CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) DEFAULT NULL UNIQUE,
  `user_id` bigint unsigned DEFAULT NULL,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `service_method` varchar(20) NOT NULL,
  `table_number` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `order_notes` varchar(255) DEFAULT NULL,
  `staff_notes` varchar(255) DEFAULT NULL,
  `subtotal` int unsigned NOT NULL,
  `packaging_fee` int unsigned NOT NULL DEFAULT '0',
  `total` int unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `kitchen_status` varchar(20) NOT NULL DEFAULT 'queued',
  `source` varchar(20) NOT NULL DEFAULT 'online',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_status_created_at_index` (`status`, `created_at`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_cashier_id_foreign` (`cashier_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `menu_item_id` bigint unsigned DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` smallint unsigned NOT NULL,
  `unit_price` int unsigned NOT NULL,
  `line_total` int unsigned NOT NULL,
  `chicken_cut` varchar(255) DEFAULT NULL,
  `drink_option` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_menu_item_id_foreign` (`menu_item_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `chicken_cuts`;
CREATE TABLE `chicken_cuts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `drink_options`;
CREATE TABLE `drink_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `promos`;
CREATE TABLE `promos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `phone`) VALUES (1, 'Admin Geprek GLO', 'admin', 'admin@geprekglo.com', '$2y$12$4H5CirIjqVOTpISRcYuzHOT7L8QcSGYid.mamt8./Fwyk.dV4pwq6', NULL, '2026-11-18 07:31:35', '2026-11-18 07:31:35', 'admin', 081268177729);
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `phone`) VALUES (2, 'Budi Santoso', 'pelanggan', 'pelanggan@gmail.com', '$2y$12$M3TLA4jy1AH9nvcqi7S2Cu5ZN5bP4qWqcVk8CP9bDAEhDAj4LE4bq', NULL, '2026-11-18 07:31:35', '2026-11-18 07:31:35', 'customer', 081234567890);

-- Dumping data for table `store_settings`
INSERT INTO `store_settings` (`id`, `is_open`, `wa_number`, `address`, `opening_hours`, `packaging_fee`, `maps_url`, `review_url`, `store_lat`, `store_lng`, `delivery_enabled`, `delivery_radius_m`, `created_at`, `updated_at`) VALUES (1, 1, 6281268177729, 'Jl. Fatmawati, Blotongan, Kec. Sidorejo, Kota Salatiga, Jawa Tengah 50715', 'Senin - Minggu: 10:00 - 21:00 WIB', 1000, 'https://maps.google.com/?q=Blotongan+Salatiga', 'https://maps.google.com/?q=Blotongan+Salatiga', -7.3117469, 110.4851234, 0, 800, '2026-11-18 07:31:35', '2026-11-18 07:31:35');

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Paket Hemat', 'paket-hemat', 1, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Aneka Ayam', 'aneka-ayam', 2, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Sup', 'sup', 3, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 'Minuman', 'minuman', 4, '2026-11-18 07:31:35', '2026-11-18 07:31:35');

-- Dumping data for table `menu_items`
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 1, 'Paket Super Komplit', 'Paket terlaris super puas: Nasi hangat, Ayam Geprek sambal mantap, Telur Goreng, Sop Kaldu Ayam segar, dan Es Teh Jumbo.', 25000, 'menu/paket_super_komplit.jpg', 1, 1, 1, 1, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 1, 'Paket Geprek Komplit', 'Paket lengkap mantap: Nasi putih pulen, Ayam Geprek pilihan, Telur Goreng gurih, dan Es Teh Jumbo segar.', 20000, 'menu/paket_geprek_komplit.jpg', 1, 1, 1, 2, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 1, 'Paket Geprek Telur', 'Kombinasi juara ayam geprek pedas gurih dipadukan dengan telur dadar/ceplok goreng hangat dan nasi pulen.', 17000, 'menu/paket_geprek_telur.jpg', 1, 1, 1, 3, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 1, 'PAGEPIN (Paket Geprek Paling Murah)', 'Solusi hemat mahasiswa dan pelajar: Nasi putih hangat, Ayam Geprek gurih pedas, plus Es Teh cup kecil.', 15000, 'menu/pagepin.jpg', 1, 1, 1, 4, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (5, 1, 'Kombo Hebat (Hemat Banget)', 'Nasi putih, Ayam Crispy/Geprek renyah bumbu meresap, dan Es Teh Jumbo segar dingin.', 15000, 'menu/combo_hebat.jpg', 1, 0, 1, 5, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (6, 1, 'Kombo Oren', 'Paket segar komplit: Nasi hangat, Ayam Crispy/Geprek pilihan, dipadukan dengan Es Jeruk segar atau Teh Kampul.', 16000, 'menu/kombo_oren.jpg', 1, 0, 1, 6, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (7, 1, 'Combo Choco', 'Paket spesial kekinian: Nasi, Ayam Crispy/Geprek pedas gurih, dan minuman Es Coklat Glug creamy nikmat.', 20000, 'menu/combo_choco.jpg', 1, 0, 1, 7, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (8, 2, 'Ayam Crispy Original', 'Ayam goreng tepung bumbu rempah rahasia Ibu Catur, krispi kriuk di luar dan juicy lembut di dalam.', 8000, 'menu/ayam_crispy.jpg', 1, 0, 0, 8, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (9, 2, 'Ayam Geprek Sambal Bawang', 'Ayam crispy digeprek dengan ulekan cabai rawit pedas dan bawang putih segar beraroma gurih nampol.', 10000, 'menu/ayam_geprek.jpg', 1, 1, 0, 9, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (10, 2, 'Ayam Crispy Bakar', 'Ayam crispy yang dibakar dengan olesan bumbu bakar khas manis gurih legit berkaramel.', 12000, 'menu/ayam_crispy_bakar.jpg', 1, 0, 0, 10, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (11, 2, 'Ayam Geprek Bakar', 'Perpaduan sempurna kepedasan ayam geprek dengan wangi aroma bakar yang meresap istimewa.', 13000, 'menu/ayam_geprek_bakar.jpg', 1, 0, 0, 11, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (12, 3, 'Sup Kaldu Ayam Segar', 'Kuah kaldu ayam gurih bening beraroma seledri, rempah penghangat tubuh, wortel, buncis, dan suwiran ayam.', 8000, NULL, 1, 0, 0, 12, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (13, 3, 'Sup Ceker Ayam Rempah', 'Sup ceker ayam lembut kenyal yang dimasak empuk dengan bumbu rempah kaldu kaya rasa.', 7000, NULL, 1, 0, 0, 13, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (14, 4, 'Es Teh Jumbo', 'Teh manis wangi melati racikan kedai disajikan dingin segar dalam cup porsi jumbo.', 3500, 'menu/minuman_glug.jpg', 1, 1, 0, 14, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (15, 4, 'Es Teh Kampul Segar', 'Sensasi teh manis solo dipadukan dengan irisan jeruk segar yang diperas langsung.', 5500, 'menu/minuman_glug.jpg', 1, 0, 0, 15, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (16, 4, 'Es Jeruk Peras Asli', 'Perasan jeruk asli manis asam segar alami penghilang rasa pedas.', 5500, 'menu/kombo_oren.jpg', 1, 0, 0, 16, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (17, 4, 'Es Coklat Glug', 'Minuman coklat kental nikmat khas Glug dengan rasa creamy manis pas penyegar dahaga.', 8000, 'menu/combo_choco.jpg', 1, 0, 0, 17, '2026-11-18 07:31:35', '2026-11-18 07:31:35');

-- Dumping data for table `chicken_cuts`
INSERT INTO `chicken_cuts` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Dada', 1, 1, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `chicken_cuts` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Paha Atas', 1, 2, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `chicken_cuts` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Paha Bawah', 1, 3, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `chicken_cuts` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 'Sayap', 1, 4, '2026-11-18 07:31:35', '2026-11-18 07:31:35');

-- Dumping data for table `drink_options`
INSERT INTO `drink_options` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Es Teh', 1, 1, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `drink_options` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Teh Hangat', 1, 2, '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `drink_options` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Air Mineral', 1, 3, '2026-11-18 07:31:35', '2026-11-18 07:31:35');

-- Dumping data for table `promos`
INSERT INTO `promos` (`id`, `title`, `subtitle`, `image`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES (1, 'Spesial Paket Super Komplit Hemat', 'Nasi + Ayam Geprek + Telur Goreng + Sop Kaldu Ayam + Es Teh Jumbo hanya Rp25.000! Super hemat dan puas.', 'promos/super_komplit.jpg', 1, '2026-11-13 07:31:35', '2026-12-18 07:31:35', '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `promos` (`id`, `title`, `subtitle`, `image`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES (2, 'Promo Mantap Paket Geprek Telur', 'Kombinasi ayam geprek cabai segar plus telur dadar gurih cuma Rp17.000. Wajib dicoba saat santap siang!', 'promos/geprek_telur.jpg', 1, '2026-11-13 07:31:35', '2027-01-17 07:31:35', '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `promos` (`id`, `title`, `subtitle`, `image`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES (3, 'Combo Hebat Kenyang Maksimal', 'Ayam crispy gurih kriuk + nasi putih hangat + es teh jumbo cuma Rp15.000! Ramah di kantong mahasiswa.', 'promos/combo_hebat.jpg', 1, '2026-11-13 07:31:35', '2027-01-17 07:31:35', '2026-11-18 07:31:35', '2026-11-18 07:31:35');

-- Dumping data for table `orders`
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `cashier_id`, `customer_name`, `customer_phone`, `service_method`, `table_number`, `customer_address`, `subtotal`, `packaging_fee`, `total`, `status`, `source`, `created_at`, `updated_at`, `kitchen_status`, `order_notes`, `staff_notes`) VALUES (1, 'GLO-261118-0001', 2, NULL, 'Budi Santoso', 081234567890, 'dine_in', 'Meja 3', NULL, 25000, 0, 25000, 'confirmed', 'online', '2026-11-18 07:31:35', '2026-11-18 07:31:35', 'prepared', 'Priority Service, Add Cutlery', NULL);
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `cashier_id`, `customer_name`, `customer_phone`, `service_method`, `table_number`, `customer_address`, `subtotal`, `packaging_fee`, `total`, `status`, `source`, `created_at`, `updated_at`, `kitchen_status`, `order_notes`, `staff_notes`) VALUES (2, 'GLO-261118-0002', 2, NULL, 'Siti Rahma', 085712345678, 'dine_in', 'Meja 5', NULL, 17000, 0, 17000, 'pending', 'online', '2026-11-18 07:31:35', '2026-11-18 07:31:35', 'queued', 'Add Cutlery', NULL);
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `cashier_id`, `customer_name`, `customer_phone`, `service_method`, `table_number`, `customer_address`, `subtotal`, `packaging_fee`, `total`, `status`, `source`, `created_at`, `updated_at`, `kitchen_status`, `order_notes`, `staff_notes`) VALUES (3, 'GLO-261118-0003', NULL, NULL, 'Dimas Arya', 081399887766, 'takeaway', NULL, NULL, 23500, 1000, 24500, 'ready', 'online', '2026-11-18 07:31:35', '2026-11-18 07:31:35', 'ready', 'Priority Service', NULL);

-- Dumping data for table `order_items`
INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `item_name`, `quantity`, `unit_price`, `line_total`, `chicken_cut`, `drink_option`, `note`, `created_at`, `updated_at`) VALUES (1, 1, 1, 'Paket Super Komplit', 1, 25000, 25000, 'Dada', 'Es Teh Jumbo', 'Cabai 5 pedas mantap', '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `item_name`, `quantity`, `unit_price`, `line_total`, `chicken_cut`, `drink_option`, `note`, `created_at`, `updated_at`) VALUES (2, 2, 3, 'Paket Geprek Telur', 1, 17000, 17000, 'Paha Atas', NULL, 'Telur ceplok matang', '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `item_name`, `quantity`, `unit_price`, `line_total`, `chicken_cut`, `drink_option`, `note`, `created_at`, `updated_at`) VALUES (3, 3, 3, 'Paket Geprek Telur', 1, 17000, 17000, 'Sayap', NULL, 'Bungkus rapi', '2026-11-18 07:31:35', '2026-11-18 07:31:35');
INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `item_name`, `quantity`, `unit_price`, `line_total`, `chicken_cut`, `drink_option`, `note`, `created_at`, `updated_at`) VALUES (4, 3, 14, 'Es Teh Jumbo', 1, 3500, 3500, NULL, NULL, NULL, '2026-11-18 07:31:35', '2026-11-18 07:31:35');

SET FOREIGN_KEY_CHECKS=1;
