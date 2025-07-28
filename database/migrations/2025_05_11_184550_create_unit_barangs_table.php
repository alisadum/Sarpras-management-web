<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
        public function up()
    {
        Schema::create('unit_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained()->onDelete('cascade');
            $table->string('kode_barang');
            $table->string('kondisi');
            $table->string('status');
            $table->string('lokasi');
            $table->integer('stok')->default(1); // Default 1 karena tiap unit biasanya 1 stok
            $table->timestamps();
        });
    }




    public function down(): void
    {
        Schema::dropIfExists('unit_barangs');
    }
};
