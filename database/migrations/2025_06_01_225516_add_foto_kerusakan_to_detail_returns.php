<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFotoKerusakanToDetailReturns extends Migration
{
    public function up()
    {
        Schema::table('detail_returns', function (Blueprint $table) {
            $table->string('foto_kerusakan')->nullable()->after('kerusakan');
        });
    }

    public function down()
    {
        Schema::table('detail_returns', function (Blueprint $table) {
            $table->dropColumn('foto_kerusakan');
        });
    }
}