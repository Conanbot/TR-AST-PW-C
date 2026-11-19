<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('phone', 30);
                $table->string('normalized_phone', 30)->unique();
                $table->string('email', 120)->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamps();

                $table->index('name');
                $table->index('status');
                $table->index('created_at');
            });
        }

        if (!Schema::hasColumn('orders', 'customer_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
                $table->index('customer_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'customer_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }

        Schema::dropIfExists('customers');
    }
};
