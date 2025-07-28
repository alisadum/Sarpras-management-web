<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLokasiAndJumlahUnitToBorrowsTable extends Migration
{
    public function up()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->string('lokasi')->nullable()->after('deskripsi');
            $table->integer('jumlah_unit')->default(1)->after('lokasi');
        });
    }

    public function down()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn(['lokasi', 'jumlah_unit']);
        });
    }
}
