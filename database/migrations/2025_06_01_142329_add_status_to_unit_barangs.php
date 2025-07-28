<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToUnitBarangs extends Migration
{
    public function up()
    {
        Schema::table('unit_barangs', function (Blueprint $table) {
            // Ganti kolom status dengan enum baru
            $table->enum('status', ['Tersedia', 'dipinjam', 'rusak', 'hilang'])
                  ->default('Tersedia')
                  ->change();
        });
    }

    public function down()
    {
        Schema::table('unit_barangs', function (Blueprint $table) {
            // Kembalikan ke status awal kalau rollback
            $table->enum('status', ['Tersedia', 'dipinjam'])
                  ->default('Tersedia')
                  ->change();
        });
    }
}