<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBorrowsStatusEnum extends Migration
{
    public function up(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            DB::statement("ALTER TABLE borrows MODIFY COLUMN status ENUM('pending', 'assigned', 'returned', 'rejected') NOT NULL DEFAULT 'pending'");
        });
    }

    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            DB::statement("ALTER TABLE borrows MODIFY COLUMN status ENUM('pending', 'assigned', 'returned') NOT NULL DEFAULT 'pending'");
        });
    }
}