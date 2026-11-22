<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

class ExportSqlDump extends Command

{

    protected $signature = 'db:export-sql {--path=database/geprek_glo.sql} {--connection=}';

    protected $description = 'Export database schema and data as MySQL compatible SQL file';

    public function handle()

    {

        $path = base_path($this->option('path'));

        $conn = $this->option('connection') ?: config('database.default');

        $tables = [

            'users',

            'store_settings',

            'categories',

            'menu_items',

            'chicken_cuts',

            'drink_options',

            'promos',

            'orders',

            'order_items',

        ];

        $sql = "-- Dump Database Geprek GLO Salatiga\n";

        $sql .= "-- Digenerate untuk phpMyAdmin / MySQL Laragon\n";

        $sql .= "-- Waktu: " . date('Y-m-d H:i:s') . "\n\n";

        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $sql .= "DROP TABLE IF EXISTS `order_items`;\n";

        $sql .= "DROP TABLE IF EXISTS `orders`;\n";

        // Users schema

        $sql .= "DROP TABLE IF EXISTS `users`;\n";

        $sql .= "CREATE TABLE `users` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `name` varchar(255) NOT NULL DEFAULT 'Admin Geprek GLO',\n"

              . "  `username` varchar(255) NOT NULL UNIQUE,\n"

              . "  `email` varchar(255) DEFAULT NULL UNIQUE,\n"

              . "  `phone` varchar(30) DEFAULT NULL,\n"

              . "  `password` varchar(255) NOT NULL,\n"

              . "  `role` varchar(255) NOT NULL DEFAULT 'admin',\n"

              . "  `remember_token` varchar(100) DEFAULT NULL,\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`)\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Store Settings schema

        $sql .= "DROP TABLE IF EXISTS `store_settings`;\n";

        $sql .= "CREATE TABLE `store_settings` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `is_open` tinyint(1) NOT NULL DEFAULT '1',\n"

              . "  `wa_number` varchar(255) NOT NULL DEFAULT '6281268177729',\n"

              . "  `address` text DEFAULT NULL,\n"

              . "  `opening_hours` varchar(255) NOT NULL DEFAULT 'Setiap Hari: 10:00 - 21:00 WIB',\n"

              . "  `packaging_fee` int unsigned NOT NULL DEFAULT '1000',\n"

              . "  `maps_url` text DEFAULT NULL,\n"

              . "  `review_url` text DEFAULT NULL,\n"

              . "  `store_lat` decimal(10,7) DEFAULT NULL,\n"

              . "  `store_lng` decimal(10,7) DEFAULT NULL,\n"

              . "  `delivery_enabled` tinyint(1) NOT NULL DEFAULT '0',\n"

              . "  `delivery_radius_m` int unsigned NOT NULL DEFAULT '800',\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`)\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Categories schema

        $sql .= "DROP TABLE IF EXISTS `categories`;\n";

        $sql .= "CREATE TABLE `categories` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `name` varchar(255) NOT NULL,\n"

              . "  `slug` varchar(255) NOT NULL UNIQUE,\n"

              . "  `sort_order` int unsigned NOT NULL DEFAULT '0',\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`)\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Menu items schema

        $sql .= "DROP TABLE IF EXISTS `menu_items`;\n";

        $sql .= "CREATE TABLE `menu_items` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `category_id` bigint unsigned NOT NULL,\n"

              . "  `name` varchar(255) NOT NULL,\n"

              . "  `description` text DEFAULT NULL,\n"

              . "  `price` int unsigned NOT NULL,\n"

              . "  `image` varchar(255) DEFAULT NULL,\n"

              . "  `is_available` tinyint(1) NOT NULL DEFAULT '1',\n"

              . "  `is_best_seller` tinyint(1) NOT NULL DEFAULT '0',\n"

              . "  `is_package` tinyint(1) NOT NULL DEFAULT '0',\n"

              . "  `sort_order` int unsigned NOT NULL DEFAULT '0',\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`),\n"

              . "  KEY `menu_items_category_id_foreign` (`category_id`),\n"

              . "  CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Orders and immutable menu snapshots

        $sql .= "CREATE TABLE `orders` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `order_number` varchar(255) DEFAULT NULL UNIQUE,\n"

              . "  `user_id` bigint unsigned DEFAULT NULL,\n"

              . "  `cashier_id` bigint unsigned DEFAULT NULL,\n"

              . "  `customer_name` varchar(255) NOT NULL,\n"

              . "  `customer_phone` varchar(30) DEFAULT NULL,\n"

              . "  `service_method` varchar(20) NOT NULL,\n"

              . "  `table_number` varchar(20) DEFAULT NULL,\n"

              . "  `customer_address` text DEFAULT NULL,\n"

              . "  `order_notes` varchar(255) DEFAULT NULL,\n"

              . "  `staff_notes` varchar(255) DEFAULT NULL,\n"

              . "  `subtotal` int unsigned NOT NULL,\n"

              . "  `packaging_fee` int unsigned NOT NULL DEFAULT '0',\n"

              . "  `total` int unsigned NOT NULL,\n"

              . "  `status` varchar(20) NOT NULL DEFAULT 'pending',\n"

              . "  `kitchen_status` varchar(20) NOT NULL DEFAULT 'queued',\n"

              . "  `source` varchar(20) NOT NULL DEFAULT 'online',\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`),\n"

              . "  KEY `orders_status_created_at_index` (`status`, `created_at`),\n"

              . "  KEY `orders_user_id_foreign` (`user_id`),\n"

              . "  KEY `orders_cashier_id_foreign` (`cashier_id`),\n"

              . "  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,\n"

              . "  CONSTRAINT `orders_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        $sql .= "CREATE TABLE `order_items` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `order_id` bigint unsigned NOT NULL,\n"

              . "  `menu_item_id` bigint unsigned DEFAULT NULL,\n"

              . "  `item_name` varchar(255) NOT NULL,\n"

              . "  `quantity` smallint unsigned NOT NULL,\n"

              . "  `unit_price` int unsigned NOT NULL,\n"

              . "  `line_total` int unsigned NOT NULL,\n"

              . "  `chicken_cut` varchar(255) DEFAULT NULL,\n"

              . "  `drink_option` varchar(255) DEFAULT NULL,\n"

              . "  `note` varchar(255) DEFAULT NULL,\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`),\n"

              . "  KEY `order_items_order_id_foreign` (`order_id`),\n"

              . "  KEY `order_items_menu_item_id_foreign` (`menu_item_id`),\n"

              . "  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,\n"

              . "  CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Chicken cuts schema

        $sql .= "DROP TABLE IF EXISTS `chicken_cuts`;\n";

        $sql .= "CREATE TABLE `chicken_cuts` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `name` varchar(255) NOT NULL,\n"

              . "  `is_available` tinyint(1) NOT NULL DEFAULT '1',\n"

              . "  `sort_order` int unsigned NOT NULL DEFAULT '0',\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`)\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Drink options schema

        $sql .= "DROP TABLE IF EXISTS `drink_options`;\n";

        $sql .= "CREATE TABLE `drink_options` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `name` varchar(255) NOT NULL,\n"

              . "  `is_available` tinyint(1) NOT NULL DEFAULT '1',\n"

              . "  `sort_order` int unsigned NOT NULL DEFAULT '0',\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`)\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Promos schema

        $sql .= "DROP TABLE IF EXISTS `promos`;\n";

        $sql .= "CREATE TABLE `promos` (\n"

              . "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n"

              . "  `title` varchar(255) NOT NULL,\n"

              . "  `subtitle` text DEFAULT NULL,\n"

              . "  `image` varchar(255) DEFAULT NULL,\n"

              . "  `is_active` tinyint(1) NOT NULL DEFAULT '1',\n"

              . "  `start_date` date DEFAULT NULL,\n"

              . "  `end_date` date DEFAULT NULL,\n"

              . "  `created_at` timestamp NULL DEFAULT NULL,\n"

              . "  `updated_at` timestamp NULL DEFAULT NULL,\n"

              . "  PRIMARY KEY (`id`)\n"

              . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Insert table data

        foreach ($tables as $table) {

            $rows = DB::connection($conn)->table($table)->get();

            if ($rows->count() > 0) {

                $sql .= "-- Dumping data for table `$table`\n";

                foreach ($rows as $row) {

                    $rowArray = (array) $row;

                    $cols = array_keys($rowArray);

                    $quotedCols = array_map(fn($c) => "`$c`", $cols);

                    $escapedVals = array_map(function ($val) {

                        if (is_null($val)) return 'NULL';

                        if (is_bool($val)) return $val ? '1' : '0';

                        if (is_numeric($val)) return (string) $val;

                        return "'" . addslashes((string) $val) . "'";

                    }, array_values($rowArray));

                    $sql .= "INSERT INTO `$table` (" . implode(', ', $quotedCols) . ") VALUES (" . implode(', ', $escapedVals) . ");\n";

                }

                $sql .= "\n";

            }

        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($path, $sql);

        $this->info("Database exported to: $path");

        return 0;

    }

}

