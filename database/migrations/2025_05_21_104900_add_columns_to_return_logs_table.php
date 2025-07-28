<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_logs', function (Blueprint $table) {
            // Tambah kolom kalau belum ada
            if (!Schema::hasColumn('return_logs', 'borrow_id')) {
                $table->unsignedBigInteger('borrow_id')->after('id');
                $table->foreign('borrow_id')->references('id')->on('borrows')->onDelete('cascade');
            }
            if (!Schema::hasColumn('return_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('borrow_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('return_logs', 'barang_id')) {
                $table->unsignedBigInteger('barang_id')->after('user_id');
                $table->foreign('barang_id')->references('id')->on('barangs')->onDelete('cascade');
            }
            if (!Schema::hasColumn('return_logs', 'nama_barang')) {
                $table->string('nama_barang')->after('barang_id');
            }
            if (!Schema::hasColumn('return_logs', 'tanggal_pinjam')) {
                $table->date('tanggal_pinjam')->nullable()->after('nama_barang');
            }
            if (!Schema::hasColumn('return_logs', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('tanggal_pinjam');
            }
            if (!Schema::hasColumn('return_logs', 'lokasi')) {
                $table->string('lokasi')->nullable()->after('deskripsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->dropForeign(['borrow_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['barang_id']);
            $table->dropColumn(['borrow_id', 'user_id', 'barang_id', 'nama_barang', 'tanggal_pinjam', 'deskripsi', 'lokasi']);
        });
    }
};
