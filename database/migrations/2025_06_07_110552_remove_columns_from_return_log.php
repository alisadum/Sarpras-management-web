<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'nama_barang', 'lokasi']);
        });
    }

    public function down(): void
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->text('deskripsi')->nullable();
            $table->varchar('nama_barang', 255);
            $table->varchar('lokasi', 255)->nullable();
        });
    }
};