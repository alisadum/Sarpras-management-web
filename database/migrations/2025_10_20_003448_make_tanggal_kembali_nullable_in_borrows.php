<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeTanggalKembaliNullableInBorrows extends Migration
{
    public function up()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dateTime('tanggal_kembali')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dateTime('tanggal_kembali')->nullable(false)->change();
        });
    }
}