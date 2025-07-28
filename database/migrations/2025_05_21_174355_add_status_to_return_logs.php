<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToReturnLogs extends Migration
{
    public function up(): void
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed'])->default('completed')->after('terlambat');
        });
    }

    public function down(): void
    {
        Schema::table('return_logs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}