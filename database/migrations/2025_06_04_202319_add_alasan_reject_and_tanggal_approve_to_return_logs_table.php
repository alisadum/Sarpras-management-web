<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlasanRejectAndTanggalApproveToReturnLogsTable extends Migration
{
    public function up()
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->text('alasan_reject')->nullable()->after('status');
            $table->timestamp('tanggal_approve')->nullable()->after('alasan_reject');
        });
    }

    public function down()
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->dropColumn(['alasan_reject', 'tanggal_approve']);
        });
    }
}