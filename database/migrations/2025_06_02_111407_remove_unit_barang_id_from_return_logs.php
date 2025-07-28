<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnitBarangIdFromReturnLogs extends Migration
{
    public function up()
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->dropForeign(['unit_barang_id']);
            $table->dropColumn('unit_barang_id');
            // Optional: hapus detail_borrow_id kalau nggak dipake
            $table->dropForeign(['detail_borrow_id']);
            $table->dropColumn('detail_borrow_id');
        });
    }

    public function down()
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->foreignId('unit_barang_id')->constrained('unit_barangs')->onDelete('cascade');
            $table->foreignId('detail_borrow_id')->nullable()->constrained('detail_borrows')->onDelete('set null');
        });
    }
}