<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Jalankan pengingat peminjaman setiap hari pukul 08:00 WIB
        $schedule->command('borrows:check-due')->dailyAt('08:00')->timezone('Asia/Jakarta');

        // Jalankan pemeriksaan expired setiap hari pukul 18:30 WIB
        $schedule->command('borrows:check-expired')->dailyAt('18:30')->timezone('Asia/Jakarta');

        // Jalankan pemeriksaan integritas data setiap minggu (misalnya, setiap Senin pukul 09:00 WIB)
        $schedule->command('borrows:check-integrity')->weeklyOn(1, '09:00')->timezone('Asia/Jakarta');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        // Pastikan command terdaftar
        $this->commands = [
            \App\Console\Commands\CheckDueBorrows::class,
            \App\Console\Commands\CheckExpiredBorrows::class,
            \App\Console\Commands\CheckDataIntegrity::class,
        ];

        require base_path('routes/console.php');
    }
}
