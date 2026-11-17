<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_open')->default(true);
            $table->string('wa_number')->default('6281268177729');
            $table->text('address')->nullable();
            $table->string('opening_hours')->default('Setiap Hari: 10:00 - 21:00 WIB');
            $table->unsignedInteger('packaging_fee')->default(1000);
            $table->text('maps_url')->nullable();
            $table->text('review_url')->nullable();
            $table->decimal('store_lat', 10, 7)->nullable();
            $table->decimal('store_lng', 10, 7)->nullable();
            $table->boolean('delivery_enabled')->default(false);
            $table->unsignedInteger('delivery_radius_m')->default(800);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
