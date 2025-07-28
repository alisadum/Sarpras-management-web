<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReturnLogsAndBorrowsTable extends Migration
{
    public function up()
    {
        // Update tabel return_logs
        Schema::table('return_logs', function (Blueprint $table) {
            // Ubah tanggal_pinjam dan tanggal_kembali ke datetime
            $table->dateTime('tanggal_pinjam')->nullable()->change();
            $table->dateTime('tanggal_kembali')->change();
            // Update enum status untuk nambah rejected
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending')->change();
            // Hapus kerusakan kalau ada (karena pindah ke DetailReturn)
            if (Schema::hasColumn('return_logs', 'kerusakan')) {
                $table->dropColumn('kerusakan');
            }
        });

        // Update tabel borrows
        Schema::table('borrows', function (Blueprint $table) {
            // Ubah tanggal_pinjam dan tanggal_kembali ke datetime
            $table->dateTime('tanggal_pinjam')->change();
            $table->dateTime('tanggal_kembali')->change();
        });
    }

    public function down()
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->date('tanggal_pinjam')->nullable()->change();
            $table->date('tanggal_kembali')->change();
            $table->enum('status', ['pending', 'completed'])->default('completed')->change();
            $table->text('kerusakan')->nullable()->after('deskripsi');
        });

        Schema::table('borrows', function (Blueprint $table) {
            $table->date('tanggal_pinjam')->change();
            $table->date('tanggal_kembali')->change();
        });
    }
}