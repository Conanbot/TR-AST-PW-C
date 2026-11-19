<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns to promos only if they don't exist yet
        if (!Schema::hasColumn('promos', 'code')) {
            Schema::table('promos', function (Blueprint $table) {
                $table->string('code')->nullable()->after('title');
                $table->string('description')->nullable()->after('subtitle');
                $table->string('discount_type', 20)->default('fixed')->after('description');
                $table->unsignedInteger('discount_value')->default(0)->after('discount_type');
                $table->unsignedInteger('minimum_subtotal')->default(0)->after('discount_value');
                $table->unsignedInteger('maximum_discount')->nullable()->after('minimum_subtotal');
                $table->unsignedInteger('usage_limit')->nullable()->after('maximum_discount');
                $table->unsignedInteger('per_customer_limit')->nullable()->after('usage_limit');
                $table->boolean('stackable')->default(false)->after('per_customer_limit');
                $table->softDeletes();

                $table->unique('code');
                $table->index(['is_active', 'start_date', 'end_date']);
            });
        }

        if (!Schema::hasTable('promotion_usages')) {
            Schema::create('promotion_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('promo_id')->constrained('promos')->restrictOnDelete();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('code');
                $table->unsignedInteger('discount_amount');
                $table->foreignId('customer_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('customer_phone', 30)->nullable();
                $table->string('status', 20)->default('used');
                $table->timestamps();

                $table->unique(['promo_id', 'order_id']);
                $table->index(['promo_id', 'created_at']);
            });
        }

        // Add promo columns to orders only if they don't exist yet
        if (!Schema::hasColumn('orders', 'promo_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('promo_id')->nullable()->after('payment_method')->constrained('promos')->nullOnDelete();
                $table->string('promo_code')->nullable()->after('promo_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'promo_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['promo_id']);
                $table->dropColumn(['promo_id', 'promo_code']);
            });
        }
        Schema::dropIfExists('promotion_usages');
        if (Schema::hasColumn('promos', 'code')) {
            Schema::table('promos', function (Blueprint $table) {
                $table->dropUnique(['code']);
                $table->dropIndex(['is_active', 'start_date', 'end_date']);
                $table->dropSoftDeletes();
                $table->dropColumn([
                    'code', 'description', 'discount_type', 'discount_value',
                    'minimum_subtotal', 'maximum_discount', 'usage_limit',
                    'per_customer_limit', 'stackable',
                ]);
            });
        }
    }
};
