<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_logs', function (Blueprint $table) {
            // Drop foreign key lama
            $table->dropForeign(['detail_borrow_id']);
            // Tambahkan foreign key baru dengan restrict
            $table->foreign('detail_borrow_id')
                ->references('id')
                ->on('detail_borrows')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('return_logs', function (Blueprint $table) {
            // Kembalikan ke cascade
            $table->dropForeign(['detail_borrow_id']);
            $table->foreign('detail_borrow_id')
                ->references('id')
                ->on('detail_borrows')
                ->onDelete('cascade');
        });
    }
};
