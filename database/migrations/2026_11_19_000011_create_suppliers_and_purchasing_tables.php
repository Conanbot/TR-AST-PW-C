<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('contact_person')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->date('purchase_date');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['supplier_id', 'status', 'purchase_date']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_name');
            $table->string('purchase_unit', 30);
            $table->string('usage_unit', 30);
            $table->decimal('conversion_factor', 14, 4)->default(1);
            $table->decimal('ordered_quantity', 14, 4);
            $table->decimal('received_quantity', 14, 4)->default(0);
            $table->decimal('rejected_quantity', 14, 4)->default(0);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id', 'raw_material_id']);
        });

        Schema::create('goods_receivings', function (Blueprint $table) {
            $table->id();
            $table->string('receiving_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('received_at');
            $table->text('notes')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();

            $table->index(['purchase_order_id', 'received_at']);
        });

        Schema::create('goods_receiving_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receiving_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained()->restrictOnDelete();
            $table->decimal('ordered_quantity', 14, 4);
            $table->decimal('previously_received_quantity', 14, 4);
            $table->decimal('accepted_quantity', 14, 4);
            $table->decimal('rejected_quantity', 14, 4)->default(0);
            $table->string('purchase_unit', 30);
            $table->decimal('conversion_factor', 14, 4)->default(1);
            $table->unsignedBigInteger('actual_unit_price')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['goods_receiving_id', 'purchase_order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receiving_items');
        Schema::dropIfExists('goods_receivings');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
