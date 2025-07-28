<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\NotificationTrait;
use App\Models\ReturnLog;
use App\Models\DetailReturn;
use App\Models\DetailBorrow;
use App\Models\UnitBarang;
use App\Models\Borrow;
use App\Models\Barang;
use App\Models\Admin;
use App\Models\User;
use App\Enums\ReturnStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ReturnLogResource;

class ReturnController extends Controller
{
    use NotificationTrait;

    public function returnBarang(Request $request)
    {
        Log::info('Request received for returnBarang', [
            'detail_id' => $request->input('detail_id'),
            'kondisi' => $request->input('kondisi'),
            'kerusakan' => $request->hasFile('foto_kerusakan') ? array_keys($request->file('foto_kerusakan')) : 'No files',
            'user' => auth('sanctum')->user() ? auth('sanctum')->user()->toArray() : 'No user',
        ]);

        // Validasi waktu pengembalian sebelum pukul 18:00 WIB
        $wibZone = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTime('now', $wibZone);
        if ($now->format('H') >= 18) {
            Log::warning("Pengembalian ditolak: Waktu pengembalian melewati pukul 18:00 WIB, waktu saat ini: " . $now->format('Y-m-d H:i:s'));
            return response()->json([
                'status' => false,
                'error' => 'Pengembalian hanya dapat dilakukan sebelum pukul 18:00 WIB'
            ], 400);
        }

        $request->validate([
            'detail_id' => 'required|array|min:1',
            'detail_id.*' => 'exists:detail_borrows,id',
            'kondisi' => 'required|array|min:1',
            'kondisi.*' => 'in:Baik,Rusak,Hilang',
            'kerusakan' => 'nullable|array',
            'kerusakan.*' => 'nullable|string|max:1000',
            'foto_kerusakan' => 'nullable|array',
            'foto_kerusakan.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth('sanctum')->user();
        if (!$user || $user->role === 'admin') {
            Log::warning("Gagal autentikasi/otorisasi untuk pengguna: " . ($user ? 'Role admin' : 'User tidak ditemukan'));
            return response()->json(['status' => false, 'error' => 'Hanya pengguna biasa yang dapat mengembalikan barang'], $user ? 403 : 401);
        }

        $detailIds = $request->input('detail_id', []);
        $kondisi = $request->input('kondisi', []);
        $kerusakan = $request->input('kerusakan', []);

        if (count($detailIds) !== count($kondisi) || count($detailIds) !== count($kerusakan)) {
            Log::warning("Jumlah input tidak sesuai", [
                'detail_id_count' => count($detailIds),
                'kondisi_count' => count($kondisi),
                'kerusakan_count' => count($kerusakan),
            ]);
            return response()->json(['status' => false, 'error' => 'Jumlah detail_id, kondisi, dan kerusakan harus sama'], 400);
        }

        foreach ($kondisi as $index => $kondisiValue) {
            if ($kondisiValue === 'Rusak') {
                if (empty($kerusakan[$index]) || !$request->hasFile("foto_kerusakan.$index")) {
                    Log::warning("Validasi gagal untuk kondisi Rusak pada index $index", [
                        'kerusakan' => $kerusakan[$index] ?? 'kosong',
                        'foto' => $request->hasFile("foto_kerusakan.$index") ? 'ada' : 'kosong',
                    ]);
                    return response()->json(['status' => false, 'error' => 'Kerusakan dan foto wajib diisi untuk kondisi Rusak'], 400);
                }
            } elseif (!empty($kerusakan[$index]) || $request->hasFile("foto_kerusakan.$index")) {
                Log::warning("Validasi gagal untuk kondisi $kondisiValue pada index $index");
                return response()->json(['status' => false, 'error' => "Kerusakan atau foto tidak boleh diisi untuk kondisi $kondisiValue"], 400);
            }
        }

        DB::beginTransaction();
        try {
            $firstDetail = DetailBorrow::with(['borrow.barang', 'unitBarang'])->find($detailIds[0]);
            if (!$firstDetail) {
                Log::error("Detail ID {$detailIds[0]} tidak ditemukan");
                return response()->json(['status' => false, 'error' => 'Detail peminjaman tidak ditemukan'], 400);
            }
            $borrow = $firstDetail->borrow;
            Log::info('Borrow details', [
                'borrow_id' => $borrow->id,
                'status' => $borrow->status,
                'tanggal_kembali' => $borrow->tanggal_kembali,
            ]);

            if (!in_array($borrow->status, ['assigned', 'expired'])) {
                Log::warning("Validasi status gagal untuk borrow ID {$borrow->id}: status={$borrow->status}");
                DB::rollBack();
                return response()->json(['status' => false, 'error' => 'Hanya peminjaman berstatus assigned atau expired yang dapat dikembalikan'], 400);
            }

            // Periksa duplikat detail_id
            $uniqueDetailIds = array_unique($detailIds);
            if (count($uniqueDetailIds) !== count($detailIds)) {
                Log::warning("Terdeteksi duplikat detail_id", ['detail_ids' => $detailIds]);
                DB::rollBack();
                return response()->json(['status' => false, 'error' => 'Terdeteksi duplikat unit dalam pengembalian'], 400);
            }

            foreach ($detailIds as $detailId) {
                $detail = DetailBorrow::with(['unitBarang'])->find($detailId);
                if (!$detail) {
                    Log::error("Detail ID {$detailId} tidak ditemukan");
                    DB::rollBack();
                    return response()->json(['status' => false, 'error' => 'Detail peminjaman tidak ditemukan'], 400);
                }
                if ($detail->borrow_id !== $borrow->id || $detail->borrow->user_id !== $user->id) {
                    Log::warning("Akses tidak sah untuk detail ID {$detailId}", [
                        'borrow_id' => $detail->borrow_id,
                        'user_id' => $user->id,
                    ]);
                    DB::rollBack();
                    return response()->json(['status' => false, 'error' => 'Akses tidak sah untuk detail peminjaman'], 403);
                }
                if (!$detail->unit_barang_id) {
                    Log::warning("Unit barang tidak valid untuk detail ID {$detailId}");
                    DB::rollBack();
                    return response()->json(['status' => false, 'error' => 'Unit barang tidak valid'], 400);
                }
                if ($detail->status === 'returned') {
                    Log::warning("Detail ID {$detailId} sudah dikembalikan");
                    DB::rollBack();
                    return response()->json(['status' => false, 'error' => 'Unit ini sudah dikembalikan sebelumnya'], 400);
                }
            }

            // Hitung terlambat
            $terlambat = $borrow->tanggal_kembali ? \Carbon\Carbon::parse($now)->diffInDays(\Carbon\Carbon::parse($borrow->tanggal_kembali)) : 0;
            if ($terlambat < 0) $terlambat = 0;

            // Buat return log dengan status pending
            $returnLog = ReturnLog::create([
                'user_id' => $user->id,
                'tanggal_pinjam' => $borrow->tanggal_pinjam,
                'tanggal_kembali' => $now,
                'terlambat' => $terlambat,
                'status' => ReturnStatus::PENDING->value,
                'borrow_id' => $borrow->id,
                'barang_id' => $borrow->barang_id,
            ]);

            foreach ($detailIds as $index => $detailId) {
                $detail = DetailBorrow::with(['unitBarang'])->findOrFail($detailId);
                $unit = $detail->unitBarang;

                if ($unit->status !== 'dipinjam') {
                    Log::warning("Unit ID {$unit->id} tidak dalam status dipinjam, status={$unit->status}");
                    DB::rollBack();
                    return response()->json(['status' => false, 'error' => "Unit {$unit->kode_barang} tidak valid untuk pengembalian"], 400);
                }

                $detailReturnData = [
                    'return_log_id' => $returnLog->id,
                    'detail_borrow_id' => $detail->id,
                    'unit_barang_id' => $unit->id,
                    'kerusakan' => $kondisi[$index] === 'Hilang' ? 'Barang dilaporkan hilang' : ($kondisi[$index] === 'Rusak' ? $kerusakan[$index] : null),
                ];

                if ($kondisi[$index] === 'Rusak' && $request->hasFile("foto_kerusakan.$index")) {
                    try {
                        $path = $request->file("foto_kerusakan.$index")->store('kerusakan', 'public');
                        $detailReturnData['foto_kerusakan'] = $path;
                    } catch (\Exception $e) {
                        Log::error("Gagal menyimpan foto_kerusakan untuk index $index: {$e->getMessage()}");
                        DB::rollBack();
                        return response()->json(['status' => false, 'error' => 'Gagal menyimpan foto kerusakan'], 500);
                    }
                }

                DetailReturn::create($detailReturnData);
            }

            $returnLog->refresh();
            $totalTerlambat = $returnLog->terlambat;

            $notificationMessage = "Pengembalian barang telah diajukan" . ($totalTerlambat > 0 ? " dengan keterlambatan $totalTerlambat hari" : "") . ".";
            $this->sendNotification(
                $user->id,
                'Pengembalian Diajukan',
                $notificationMessage,
                'info',
                $borrow->id
            );

            $adminNotificationMessage = "User {$user->name} telah mengajukan pengembalian barang: ";
            foreach ($detailIds as $index => $detailId) {
                $detail = DetailBorrow::with(['unitBarang'])->findOrFail($detailId);
                $adminNotificationMessage .= "{$detail->unitBarang->kode_barang} ({$kondisi[$index]}" . ($kondisi[$index] === 'Rusak' ? ": {$kerusakan[$index]})" : ")");
                if ($index < count($detailIds) - 1) {
                    $adminNotificationMessage .= ", ";
                }
            }
            $adminNotificationMessage .= ($totalTerlambat > 0 ? " dengan keterlambatan $totalTerlambat hari" : "") . ".";

            $admins = Admin::pluck('id');
            foreach ($admins as $adminId) {
                $this->sendNotification(
                    $adminId,
                    'Pengembalian Baru',
                    $adminNotificationMessage,
                    'info',
                    $borrow->id
                );
            }

            DB::commit();
            Log::info("Berhasil mengajukan pengembalian untuk borrow ID {$borrow->id}, terlambat: $totalTerlambat hari");
            return response()->json([
                'status' => true,
                'data' => new ReturnLogResource($returnLog->load(['user', 'detailReturns.unitBarang', 'detailReturns.detailBorrow.borrow.barang'])),
                'terlambat' => $totalTerlambat,
                'message' => 'Pengembalian berhasil diajukan, menunggu persetujuan admin',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal memproses pengembalian", [
                'borrow_id' => isset($borrow) ? $borrow->id : 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => false,
                'error' => 'Gagal memproses pengembalian: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function approveReturn(Request $request, $returnLogId)
    {
        Log::info("Request received for approveReturn, return_log_id: $returnLogId", [
            'user' => auth('sanctum')->user() ? auth('sanctum')->user()->toArray() : 'No user',
        ]);

        $user = auth('sanctum')->user();
        if (!$user || $user->role !== 'admin') {
            Log::warning("Gagal autentikasi/otorisasi untuk approve return: " . ($user ? 'Bukan admin' : 'User tidak ditemukan'));
            return response()->json(['status' => false, 'error' => 'Hanya admin yang dapat menyetujui pengembalian'], $user ? 403 : 401);
        }

        $returnLog = ReturnLog::with(['detailReturns.detailBorrow.unitBarang', 'borrow.barang'])->find($returnLogId);
        if (!$returnLog) {
            Log::error("ReturnLog ID {$returnLogId} tidak ditemukan");
            return response()->json(['status' => false, 'error' => 'Log pengembalian tidak ditemukan'], 404);
        }

        if ($returnLog->status !== ReturnStatus::PENDING->value) {
            Log::warning("ReturnLog ID {$returnLogId} tidak dalam status pending, status: {$returnLog->status}");
            return response()->json(['status' => false, 'error' => 'Hanya pengembalian berstatus pending yang dapat disetujui'], 400);
        }

        DB::beginTransaction();
        try {
            $validReturnsCount = 0;
            foreach ($returnLog->detailReturns as $detailReturn) {
                $detailBorrow = $detailReturn->detailBorrow;
                $unit = $detailReturn->unitBarang;
                $kondisi = $detailReturn->kerusakan === 'Barang dilaporkan hilang' ? 'Hilang' : ($detailReturn->kerusakan ? 'Rusak' : 'Baik');

                $newStatus = $kondisi === 'Hilang' ? 'hilang' : ($kondisi === 'Rusak' ? 'rusak' : 'Tersedia');
                $unit->update(['status' => $newStatus]);
                $detailBorrow->update(['status' => 'returned']);

                if ($kondisi === 'Baik') {
                    $validReturnsCount++;
                }
            }

            // Update stok barang
            $barang = Barang::find($returnLog->borrow->barang_id);
            if ($barang) {
                $currentStok = $barang->stok;
                $newStok = $currentStok + $validReturnsCount;
                $barang->update(['stok' => $newStok]);
                Log::info("Stok barang ID {$barang->id} diperbarui dari $currentStok menjadi $newStok");
            } else {
                Log::warning("Barang ID {$returnLog->borrow->barang_id} tidak ditemukan untuk update stok");
            }

            // Cek apakah semua detail borrow sudah dikembalikan
            $remainingDetails = DetailBorrow::where('borrow_id', $returnLog->borrow_id)
                ->where('status', 'active')
                ->count();
            if ($remainingDetails == 0) {
                $returnLog->borrow->update(['status' => 'returned']);
                Log::info("Borrow ID {$returnLog->borrow_id} updated to status 'returned'");
            }

            $returnLog->update(['status' => ReturnStatus::COMPLETED->value]);

            $notificationMessage = "Pengembalian barang telah disetujui" . ($returnLog->terlambat > 0 ? " dengan keterlambatan {$returnLog->terlambat} hari" : "") . ".";
            $this->sendNotification(
                $returnLog->user_id,
                'Pengembalian Disetujui',
                $notificationMessage,
                'success',
                $returnLog->borrow_id
            );

            $adminNotificationMessage = "Pengembalian barang oleh user {$returnLog->user->name} telah disetujui.";
            $admins = Admin::pluck('id');
            foreach ($admins as $adminId) {
                $this->sendNotification(
                    $adminId,
                    'Pengembalian Disetujui',
                    $adminNotificationMessage,
                    'success',
                    $returnLog->borrow_id
                );
            }

            DB::commit();
            Log::info("Pengembalian ID {$returnLogId} berhasil disetujui");
            return response()->json([
                'status' => true,
                'data' => new ReturnLogResource($returnLog->load(['user', 'detailReturns.unitBarang', 'detailReturns.detailBorrow.borrow.barang'])),
                'message' => 'Pengembalian berhasil disetujui',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyetujui pengembalian ID {$returnLogId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => false,
                'error' => 'Gagal menyetujui pengembalian: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function rejectReturn(Request $request, $returnLogId)
    {
        Log::info("Request received for rejectReturn, return_log_id: $returnLogId", [
            'user' => auth('sanctum')->user() ? auth('sanctum')->user()->toArray() : 'No user',
            'alasan_reject' => $request->input('alasan_reject'),
        ]);

        $request->validate([
            'alasan_reject' => 'required|string|max:1000',
        ]);

        $user = auth('sanctum')->user();
        if (!$user || $user->role !== 'admin') {
            Log::warning("Gagal autentikasi/otorisasi untuk reject return: " . ($user ? 'Bukan admin' : 'User tidak ditemukan'));
            return response()->json(['status' => false, 'error' => 'Hanya admin yang dapat menolak pengembalian'], $user ? 403 : 401);
        }

        $returnLog = ReturnLog::with(['detailReturns.detailBorrow.unitBarang', 'borrow.barang'])->find($returnLogId);
        if (!$returnLog) {
            Log::error("ReturnLog ID {$returnLogId} tidak ditemukan");
            return response()->json(['status' => false, 'error' => 'Log pengembalian tidak ditemukan'], 404);
        }

        if ($returnLog->status !== ReturnStatus::PENDING->value) {
            Log::warning("ReturnLog ID {$returnLogId} tidak dalam status pending, status: {$returnLog->status}");
            return response()->json(['status' => false, 'error' => 'Hanya pengembalian berstatus pending yang dapat ditolak'], 400);
        }

        DB::beginTransaction();
        try {
            $returnLog->update([
                'status' => ReturnStatus::REJECTED->value,
                'alasan_reject' => $request->input('alasan_reject'),
            ]);

            $notificationMessage = "Pengembalian barang ditolak: {$request->input('alasan_reject')}";
            $this->sendNotification(
                $returnLog->user_id,
                'Pengembalian Ditolak',
                $notificationMessage,
                'error',
                $returnLog->borrow_id
            );

            $adminNotificationMessage = "Pengembalian barang oleh user {$returnLog->user->name} ditolak: {$request->input('alasan_reject')}";
            $admins = Admin::pluck('id');
            foreach ($admins as $adminId) {
                $this->sendNotification(
                    $adminId,
                    'Pengembalian Ditolak',
                    $adminNotificationMessage,
                    'error',
                    $returnLog->borrow_id
                );
            }

            DB::commit();
            Log::info("Pengembalian ID {$returnLogId} berhasil ditolak");
            return response()->json([
                'status' => true,
                'data' => new ReturnLogResource($returnLog->load(['user', 'detailReturns.unitBarang', 'detailReturns.detailBorrow.borrow.barang'])),
                'message' => 'Pengembalian berhasil ditolak',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menolak pengembalian ID {$returnLogId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => false,
                'error' => 'Gagal menolak pengembalian: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            Log::warning("Akses tidak sah untuk riwayat pengembalian");
            return response()->json(['status' => false, 'error' => 'Unauthenticated'], 401);
        }

        $query = ReturnLog::where('user_id', $user->id)
            ->with(['barang', 'detailReturns.unitBarang'])
            ->select(['id', 'user_id', 'barang_id', 'tanggal_pinjam', 'tanggal_kembali', 'status', 'terlambat', 'alasan_reject']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('tanggal_kembali', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('tanggal_kembali', '<=', $request->end_date);
        }
        if ($request->has('terlambat') && $request->terlambat == '1') {
            $query->where('terlambat', '>', 0);
        }

        $returns = $query->get()->map(function ($return) {
            return [
                'id' => $return->id,
                'barang_id' => $return->barang_id,
                'nama_barang' => $return->barang->nama ?? '-',
                'tanggal_pinjam' => \Carbon\Carbon::parse($return->tanggal_pinjam)->format('d-m-Y H:i'),
                'tanggal_kembali' => \Carbon\Carbon::parse($return->tanggal_kembali)->format('d-m-Y H:i'),
                'status' => $return->status,
                'terlambat' => $return->terlambat,
                'alasan_reject' => $return->alasan_reject,
                'details' => $return->detailReturns->map(function ($detail) {
                    return [
                        'unit_barang_id' => $detail->unit_barang_id,
                        'kode_barang' => $detail->unitBarang->kode_barang ?? '-',
                        'kerusakan' => $detail->kerusakan,
                        'foto_kerusakan' => $detail->foto_kerusakan ? asset('storage/' . $detail->foto_kerusakan) : null,
                    ];
                }),
            ];
        });

        Log::info("Mengambil riwayat pengembalian untuk user ID {$user->id}: " . $returns->count() . " item");
        return response()->json(['status' => true, 'data' => $returns]);
    }
}