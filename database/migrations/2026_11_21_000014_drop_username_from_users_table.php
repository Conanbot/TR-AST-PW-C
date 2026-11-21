<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'username')) {
            return;
        }

        // Backfill empty/null emails before removing username so every account can log in via email
        $usersWithoutEmail = DB::table('users')
            ->where(function ($q) {
                $q->whereNull('email')->orWhere('email', '');
            })
            ->get();

        foreach ($usersWithoutEmail as $user) {
            $base = Str::slug($user->username ?: $user->name ?: ('user' . $user->id));
            $email = $base . '@geprekglo.com';
            $suffix = 1;
            while (DB::table('users')->where('email', $email)->where('id', '!=', $user->id)->exists()) {
                $email = $base . $suffix++ . '@geprekglo.com';
            }
            DB::table('users')->where('id', $user->id)->update(['email' => $email]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });
    }
};
