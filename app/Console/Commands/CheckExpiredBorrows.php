<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Borrow;
use App\Models\Admin;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckExpiredBorrows extends Command
{
    use NotificationTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borrows:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expired borrows and update status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $borrows = Borrow::where('status', 'assigned')
                ->where('tanggal_kembali', '<', Carbon::now())
                ->with(['barang', 'user'])
                ->get();

            if ($borrows->isEmpty()) {
                $this->info('Tidak ada peminjaman yang expired.');
                Log::info('CheckExpiredBorrows: Tidak ada peminjaman yang expired.');
                return;
            }

            foreach ($borrows as $borrow) {
                if (!$borrow->barang || !$borrow->user) {
                    Log::warning("Data tidak lengkap untuk peminjaman ID {$borrow->id}: barang atau user tidak ditemukan.");
                    continue;
                }

                DB::beginTransaction();
                try {
                    $borrow->update(['status' => 'expired']);

                    DB::table('expired_logs')->insert([
                        'borrow_id' => $borrow->id,
                        'user_id' => $borrow->user_id,
                        'barang_id' => $borrow->barang_id,
                        'tanggal_kembali' => $borrow->tanggal_kembali,
                        'tanggal_expired' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->sendNotification(
                        $borrow->user_id,
                        'Peminjaman Expired',
                        "Peminjaman barang {$borrow->barang->nama} telah expired! Segera kembalikan!",
                        'error',
                        $borrow->id
                    );

                    // Kirim notifikasi ke semua admin
                    $admins = Admin::pluck('id');
                    foreach ($admins as $adminId) {
                        $this->sendNotification(
                            $adminId,
                            'Peminjaman Expired',
                            "Peminjaman barang {$borrow->barang->nama} oleh {$borrow->user->name} telah expired.",
                            'warning',
                            $borrow->id
                        );
                    }

                    DB::commit();
                    Log::info("Peminjaman ID {$borrow->id} diubah ke status expired dan log disimpan.");
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Gagal memproses peminjaman ID {$borrow->id}: {$e->getMessage()}");
                }
            }

            $this->info("Berhasil memeriksa {$borrows->count()} peminjaman expired.");
        } catch (\Exception $e) {
            Log::error("Gagal menjalankan CheckExpiredBorrows: {$e->getMessage()}");
            $this->error('Terjadi kesalahan saat memproses peminjaman expired.');
        }
    }
}
