<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('payment_reference')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method_code', 30);
            $table->unsignedInteger('amount');
            $table->string('status', 30)->default('unpaid');
            $table->string('proof_image')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'method_code']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('payment_verification_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->unsignedInteger('claimed_amount')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_lat', 10, 7)->nullable()->after('customer_address');
            $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat');
        });

        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('qris_image')->nullable();
            $table->string('qris_merchant_name')->nullable();
            $table->text('qris_instructions')->nullable();
            $table->boolean('qris_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['qris_image', 'qris_merchant_name', 'qris_instructions', 'qris_enabled']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_lat', 'delivery_lng']);
        });
        Schema::dropIfExists('payment_verification_histories');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
    }
};
