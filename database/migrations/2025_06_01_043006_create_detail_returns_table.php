<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailReturnsTable extends Migration
{
    public function up()
    {
        Schema::create('detail_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_log_id')->constrained('return_logs')->onDelete('cascade');
            $table->foreignId('detail_borrow_id')->constrained('detail_borrows')->onDelete('cascade');
            $table->foreignId('unit_barang_id')->constrained('unit_barangs')->onDelete('cascade');
            $table->text('kerusakan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_returns');
    }
}