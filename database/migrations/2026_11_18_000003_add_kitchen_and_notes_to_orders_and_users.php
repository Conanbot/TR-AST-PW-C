<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'kitchen_status')) {
                $table->string('kitchen_status', 20)->default('queued')->after('status');
            }
            if (!Schema::hasColumn('orders', 'order_notes')) {
                $table->string('order_notes', 255)->nullable()->after('customer_address');
            }
            if (!Schema::hasColumn('orders', 'staff_notes')) {
                $table->string('staff_notes', 255)->nullable()->after('order_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'staff_notes')) {
                $table->dropColumn('staff_notes');
            }
            if (Schema::hasColumn('orders', 'order_notes')) {
                $table->dropColumn('order_notes');
            }
            if (Schema::hasColumn('orders', 'kitchen_status')) {
                $table->dropColumn('kitchen_status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
