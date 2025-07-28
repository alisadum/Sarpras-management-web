<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Periksa apakah kolom 'photo' sudah ada
        if (!Schema::hasColumn('users', 'photo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('photo')->nullable()->after('password');
            });
        }
    }

    /**
     * Balikkan perubahan migrasi.
     */
    public function down(): void
    {
        // Periksa apakah kolom 'photo' ada sebelum mencoba menghapusnya
        if (Schema::hasColumn('users', 'photo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }
    }
};
