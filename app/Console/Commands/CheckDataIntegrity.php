<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Borrow;
use App\Models\UnitBarang;
use App\Models\Barang;
use Illuminate\Support\Facades\Log;

class CheckDataIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borrows:check-integrity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check data integrity for borrows and units';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $borrows = Borrow::whereIn('status', ['assigned', 'expired'])
                ->with(['barang', 'details'])
                ->get();

            $issues = 0;

            foreach ($borrows as $borrow) {
                if (!$borrow->barang) {
                    Log::warning("Inconsistensi data: Peminjaman ID {$borrow->id} tidak memiliki barang terkait.");
                    $issues++;
                    continue;
                }

                // Periksa jumlah unit peminjaman vs detail
                $unitCount = $borrow->details->where('status', 'active')->count();
                if ($unitCount != $borrow->jumlah_unit) {
                    Log::warning("Inconsistensi data: Peminjaman ID {$borrow->id} memiliki jumlah_unit {$borrow->jumlah_unit} tetapi detail aktif {$unitCount}.");
                    $issues++;
                }

                // Periksa konsistensi stok barang
                $barang = $borrow->barang;
                $activeUnits = UnitBarang::where('barang_id', $barang->id)
                    ->where('status', 'dipinjam')
                    ->count();
                $expectedUnits = Borrow::where('barang_id', $barang->id)
                    ->whereIn('status', ['assigned', 'expired'])
                    ->sum('jumlah_unit');

                if ($activeUnits != $expectedUnits) {
                    Log::warning("Inconsistensi stok: Barang ID {$barang->id} memiliki {$activeUnits} unit dipinjam, tetapi jumlah unit peminjaman aktif adalah {$expectedUnits}.");
                    $issues++;
                }
            }

            if ($issues === 0) {
                $this->info('Pemeriksaan integritas data selesai. Tidak ada masalah ditemukan.');
                Log::info('CheckDataIntegrity: Tidak ada masalah ditemukan.');
            } else {
                $this->info("Pemeriksaan integritas data selesai. Ditemukan {$issues} masalah.");
                Log::warning("CheckDataIntegrity: Ditemukan {$issues} masalah.");
            }
        } catch (\Exception $e) {
            Log::error("Gagal menjalankan CheckDataIntegrity: {$e->getMessage()}");
            $this->error('Terjadi kesalahan saat memeriksa integritas data.');
        }
    }
}
