<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropItemNameFromBorrows extends Migration
{
    public function up()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn('item_name');
        });
    }

    public function down()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->string('item_name')->nullable();
        });
    }
}