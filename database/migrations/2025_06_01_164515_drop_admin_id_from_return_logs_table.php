<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::table('return_logs', function (Blueprint $table) {
        $table->dropForeign(['admin_id']); // kalau ada foreign key
        $table->dropColumn('admin_id');
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('return_logs', function (Blueprint $table) {
        $table->unsignedBigInteger('admin_id')->nullable()->after('unit_barang_id');

        // Optional: foreign key kalo sebelumnya ada
        $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
    });
    }
};
