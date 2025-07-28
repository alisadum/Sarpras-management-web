<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Borrow;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckDueBorrows extends Command
{
    use NotificationTrait;

    protected $signature = 'borrows:check-due';
    protected $description = 'Check borrows due soon and send notifications to users';

    public function handle()
    {
        try {
            $now = Carbon::now();
            $dueSoon = $now->copy()->addDays(2)->endOfDay(); // Notifikasi 1-2 hari sebelum tenggat
            $dueTimeToday = $now->copy()->setTime(18, 0); // Batas pukul 18:00 hari ini

            $borrows = Borrow::where('status', 'assigned')
                ->where('tanggal_kembali', '>=', $now)
                ->where('tanggal_kembali', '<=', $dueSoon)
                ->with(['barang', 'user'])
                ->get();

            if ($borrows->isEmpty()) {
                $this->info('Tidak ada peminjaman yang mendekati tenggat.');
                Log::info('CheckDueBorrows: Tidak ada peminjaman yang mendekati tenggat.');
                return;
            }

            foreach ($borrows as $borrow) {
                if (!$borrow->barang || !$borrow->user) {
                    Log::warning("Data tidak lengkap untuk peminjaman ID {$borrow->id}: barang atau user tidak ditemukan.");
                    continue;
                }

                $isToday = $borrow->tanggal_kembali->isSameDay($now) && $borrow->tanggal_kembali->lte($dueTimeToday);
                $message = $isToday
                    ? "Peminjaman {$borrow->barang->nama} akan jatuh tempo hari ini pada {$borrow->tanggal_kembali->format('d-m-Y H:i')}. Harap segera kembalikan barang."
                    : "Peminjaman {$borrow->barang->nama} akan jatuh tempo pada {$borrow->tanggal_kembali->format('d-m-Y H:i')}. Harap persiapkan pengembalian.";

                try {
                    $this->sendNotification(
                        $borrow->user_id,
                        'Pengingat Pengembalian',
                        $message,
                        'warning',
                        $borrow->id
                    );
                    Log::info("Notifikasi pengingat dikirim untuk peminjaman ID {$borrow->id} kepada user ID {$borrow->user_id}.");
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim notifikasi ke user ID {$borrow->user_id} untuk peminjaman ID {$borrow->id}: {$e->getMessage()}");
                }
            }

            $this->info("Berhasil memeriksa {$borrows->count()} peminjaman yang mendekati tenggat.");
        } catch (\Exception $e) {
            Log::error("Gagal menjalankan CheckDueBorrows: {$e->getMessage()}");
            $this->error('Terjadi kesalahan saat memproses pengingat peminjaman.');
        }
    }
}
