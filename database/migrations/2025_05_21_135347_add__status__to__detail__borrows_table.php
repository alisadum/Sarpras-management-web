<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToDetailBorrowsTable extends Migration
{
    public function up()
    {
        Schema::table('detail_borrows', function (Blueprint $table) {
            $table->enum('status', ['active', 'returned'])->default('active')->after('unit_barang_id');
        });
    }

    public function down()
    {
        Schema::table('detail_borrows', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
