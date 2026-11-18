<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_required')->default(false);
            $table->unsignedTinyInteger('min_selections')->default(0);
            $table->unsignedTinyInteger('max_selections')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('modifier_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('price_delta')->default(0);
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['modifier_group_id', 'is_available']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->unsignedInteger('price');
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['menu_item_id', 'is_available']);
        });

        Schema::create('menu_item_modifier_group', function (Blueprint $table) {
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('modifier_group_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->nullable();
            $table->unsignedTinyInteger('min_selections')->nullable();
            $table->unsignedTinyInteger('max_selections')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['menu_item_id', 'modifier_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_modifier_group');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('modifier_options');
        Schema::dropIfExists('modifier_groups');
    }
};
