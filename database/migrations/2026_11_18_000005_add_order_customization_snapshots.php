<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('discount_amount')->default(0)->after('packaging_fee');
            $table->unsignedInteger('delivery_fee')->default(0)->after('discount_amount');
            $table->string('payment_status', 20)->default('unpaid')->after('status');
            $table->string('payment_method', 30)->nullable()->after('payment_status');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('menu_item_id')->constrained('product_variants')->nullOnDelete();
            $table->string('variant_name')->nullable()->after('item_name');
            $table->unsignedInteger('base_price')->default(0)->after('unit_price');
            $table->unsignedInteger('modifier_total')->default(0)->after('base_price');
        });

        Schema::create('order_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modifier_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('modifier_option_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group_name');
            $table->string('option_name');
            $table->unsignedInteger('price_delta')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_modifiers');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn(['variant_id', 'variant_name', 'base_price', 'modifier_total']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'delivery_fee', 'payment_status', 'payment_method']);
        });
    }
};
