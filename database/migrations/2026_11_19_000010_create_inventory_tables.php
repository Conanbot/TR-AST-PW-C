<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('raw_materials')) {
            Schema::create('raw_materials', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->nullable();
                $table->string('sku')->nullable()->unique();
                $table->string('purchase_unit', 30);
                $table->string('usage_unit', 30);
                $table->decimal('conversion_factor', 14, 4)->default(1);
                $table->decimal('current_stock', 14, 4)->default(0);
                $table->decimal('minimum_stock', 14, 4)->default(0);
                $table->unsignedInteger('cost_per_purchase_unit')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'current_stock']);
            });
        }

        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('raw_material_id')->constrained()->restrictOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('movement_type', 30);
                $table->decimal('quantity_delta', 14, 4);
                $table->decimal('balance_after', 14, 4);
                $table->string('unit', 30);
                $table->string('reason');
                $table->nullableMorphs('reference');
                $table->string('idempotency_key')->nullable()->unique();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['raw_material_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('recipes')) {
            Schema::create('recipes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('raw_material_id')->constrained()->restrictOnDelete();
                $table->decimal('quantity', 14, 4);
                $table->string('unit', 30);
                $table->timestamps();

                $table->unique(['menu_item_id', 'raw_material_id']);
            });
        }

        if (!Schema::hasTable('modifier_option_recipes')) {
            Schema::create('modifier_option_recipes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('modifier_option_id')->constrained()->cascadeOnDelete();
                $table->foreignId('raw_material_id')->constrained()->restrictOnDelete();
                $table->decimal('quantity', 14, 4);
                $table->string('unit', 30);
                $table->timestamps();

                $table->unique(['modifier_option_id', 'raw_material_id'], 'mod_opt_recipes_opt_raw_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modifier_option_recipes');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('raw_materials');
    }
};
