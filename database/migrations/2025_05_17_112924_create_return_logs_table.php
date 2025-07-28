<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detail_borrow_id');
            $table->unsignedBigInteger('unit_barang_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->date('tanggal_kembali');
            $table->integer('terlambat')->nullable();
            $table->timestamps();

            $table->foreign('detail_borrow_id')->references('id')->on('detail_borrows')->onDelete('cascade');
            $table->foreign('unit_barang_id')->references('id')->on('unit_barangs')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_logs');
    }
};
