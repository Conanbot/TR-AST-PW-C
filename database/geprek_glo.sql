-- Dump Database Geprek GLO Salatiga
-- Digenerate untuk phpMyAdmin / MySQL Laragon
-- Waktu: 2026-11-17 10:26:40

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'Admin Geprek GLO',
  `username` varchar(255) NOT NULL UNIQUE,
  `email` varchar(255) DEFAULT NULL UNIQUE,
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
  `subtotal` int unsigned NOT NULL,
  `packaging_fee` int unsigned NOT NULL DEFAULT '0',
  `total` int unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
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
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES (1, 'Admin Geprek GLO', 'admin', 'admin@geprekglo.com', '$2y$12$hPrQIGnJGiXJXKc2PCksYu7RQ3S/vzhGkg2afC/xemY1z4s6uoP.2', 'admin', NULL, '2026-11-17 10:24:22', '2026-11-17 10:24:22');

-- Dumping data for table `store_settings`
INSERT INTO `store_settings` (`id`, `is_open`, `wa_number`, `address`, `opening_hours`, `packaging_fee`, `maps_url`, `review_url`, `store_lat`, `store_lng`, `delivery_enabled`, `delivery_radius_m`, `created_at`, `updated_at`) VALUES (1, 1, 6281268177729, 'Jl. Fatmawati, Blotongan, Kec. Sidorejo, Kota Salatiga, Jawa Tengah 50715', 'Senin - Minggu: 10:00 - 21:00 WIB', 1000, 'https://maps.google.com/?q=Blotongan+Salatiga', 'https://maps.google.com/?q=Blotongan+Salatiga', -7.3117469, 110.4851234, 0, 800, '2026-11-17 10:24:22', '2026-11-17 10:24:22');

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Paket Hemat', 'paket-hemat', 1, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Aneka Ayam', 'aneka-ayam', 2, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Sup', 'sup', 3, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 'Minuman', 'minuman', 4, '2026-11-17 10:24:22', '2026-11-17 10:24:22');

-- Dumping data for table `menu_items`
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 1, 'Paket Hemat Ayam Crispy', 'Satu porsi nasi hangat, ayam crispy renyah gurih, sambal pilihan, dan minuman paket segar.', 11000, NULL, 1, 1, 1, 1, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 1, 'Paket Hemat Ayam Geprek', 'Nasi putih pulen, ayam goreng renyah digeprek dengan cabai rawit segar khas GLO, plus minuman paket.', 13000, NULL, 1, 1, 1, 2, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 1, 'Paket Hemat Ayam Crispy Bakar', 'Ayam crispy berbalut racikan kecap rempah bakar aroma manis gurih istimewa, nasi, dan minuman paket.', 14000, NULL, 1, 0, 1, 3, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 2, 'Ayam Crispy Original (Ala Carte)', 'Potongan ayam pilihan digoreng tepung keemasan dengan tekstur kriuk tahan lama dan bumbu meresap.', 8000, NULL, 1, 0, 0, 4, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (5, 2, 'Ayam Geprek Cabai Segar', 'Ayam crispy digeprek langsung dengan cabai rawit merah pedas nendang (level pedas bisa request di catatan).', 10000, NULL, 1, 1, 0, 5, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (6, 2, 'Ayam Crispy Bakar Spesial', 'Kombinasi tekstur krispi di luar dengan karamelisasi bumbu bakar manis gurih yang meresap.', 11000, NULL, 1, 0, 0, 6, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (7, 2, 'Ayam Geprek Bakar', 'Paduan unik ayam geprek pedas dengan sentuhan panggangan beraroma asap yang menggugah selera.', 12000, NULL, 1, 0, 0, 7, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (8, 3, 'Sup Sayur Hangat', 'Semangkuk sup berkuah kaldu ayam bening gurih dengan irisan wortel, buncis, kembang kol, dan seledri.', 5000, NULL, 1, 0, 0, 8, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (9, 3, 'Sup Ceker Ayam Gurih', 'Sup ceker ayam lembut yang dimasak empuk dengan rempah kuah kaldu segar penghangat tubuh.', 7000, NULL, 1, 0, 0, 9, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (10, 4, 'Es Teh Manis', 'Seduhan teh melati wangi dengan es kristal dingin yang menyegarkan.', 3000, NULL, 1, 0, 0, 10, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (11, 4, 'Teh Hangat Manis', 'Teh hangat beraroma wangi melati alami untuk menemani santap santai.', 3000, NULL, 1, 0, 0, 11, '2026-11-17 10:24:23', '2026-11-17 10:24:23');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (12, 4, 'Air Mineral Botol', 'Air mineral kemasan botol higienis dan menyegarkan.', 4000, NULL, 1, 0, 0, 12, '2026-11-17 10:24:23', '2026-11-17 10:24:23');
INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_best_seller`, `is_package`, `sort_order`, `created_at`, `updated_at`) VALUES (13, 4, 'Es Jeruk Peras', 'Jeruk asli diperas segar dengan rasa manis asam alami penyegar dahaga.', 4000, NULL, 1, 0, 0, 13, '2026-11-17 10:24:23', '2026-11-17 10:24:23');

-- Dumping data for table `chicken_cuts`
INSERT INTO `chicken_cuts` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Dada', 1, 1, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `chicken_cuts` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Paha Atas', 1, 2, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `chicken_cuts` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Paha Bawah', 1, 3, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `chicken_cuts` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (4, 'Sayap', 1, 4, '2026-11-17 10:24:22', '2026-11-17 10:24:22');

-- Dumping data for table `drink_options`
INSERT INTO `drink_options` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (1, 'Es Teh', 1, 1, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `drink_options` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (2, 'Teh Hangat', 1, 2, '2026-11-17 10:24:22', '2026-11-17 10:24:22');
INSERT INTO `drink_options` (`id`, `name`, `is_available`, `sort_order`, `created_at`, `updated_at`) VALUES (3, 'Air Mineral', 1, 3, '2026-11-17 10:24:22', '2026-11-17 10:24:22');

-- Dumping data for table `promos`
INSERT INTO `promos` (`id`, `title`, `subtitle`, `image`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES (1, 'Spesial Menu Paket Hemat Blotongan', 'Nikmati Paket Hemat Ayam Crispy komplit nasi dan minuman mulai Rp11.000! Lezat, puas, dan ramah di kantong.', NULL, 1, '2026-11-12 10:24:23', '2026-12-17 10:24:23', '2026-11-17 10:24:23', '2026-11-17 10:24:23');
INSERT INTO `promos` (`id`, `title`, `subtitle`, `image`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES (2, 'Pemesanan Rombongan & Jumat Berkah', 'Menerima pesanan nasi kotak / rombongan (min. 20 porsi). Hubungi kami via WhatsApp untuk harga & koordinasi pengiriman.', NULL, 1, '2026-11-12 10:24:23', '2027-01-16 10:24:23', '2026-11-17 10:24:23', '2026-11-17 10:24:23');

SET FOREIGN_KEY_CHECKS=1;
