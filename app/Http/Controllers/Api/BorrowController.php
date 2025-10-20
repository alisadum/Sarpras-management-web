<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\Barang;
use App\Models\User;
use App\Models\Admin;
use App\Models\UnitBarang;
use App\Models\DetailBorrow;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class BorrowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function sendNotification(int $userId, string $title, string $message, string $type, ?int $borrowId = null): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'borrow_id' => $borrowId,
                'tanggal_notif' => now(),
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal kirim notifikasi ke user ID {$userId}: {$e->getMessage()}");
        }
    }

    private function notifyAllAdmins(string $title, string $message, string $type, ?int $borrowId = null): void
    {
        $admins = Admin::pluck('id')->toArray();
        foreach ($admins as $adminId) {
            $this->sendNotification($adminId, $title, $message, $type, $borrowId);
        }
    }

    private function isUnitValid(UnitBarang $unit, Borrow $borrow, int $unitId): bool
    {
        if ($unit->barang_id !== $borrow->barang_id || trim(strtolower($unit->status)) !== 'tersedia') {
            Log::error("Unit ID {$unitId} tidak sesuai atau tidak tersedia (Barang ID: {$unit->barang_id}, Status: {$unit->status})");
            return false;
        }

        if (DetailBorrow::where('unit_barang_id', $unitId)->where('status', 'active')->exists()) {
            Log::error("Unit ID {$unitId} sudah digunakan di peminjaman lain");
            return false;
        }

        return true;
    }

    public function store(Request $request): JsonResponse
    {
        $barang = Barang::find($request->input('barang_id'));
        if (!$barang) {
            Log::warning("Peminjaman gagal: Barang ID {$request->input('barang_id')} tidak ditemukan");
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan.',
            ], 404);
        }

        $isConsumable = strtolower($barang->tipe) === 'consumable';

        $validationRules = [
            'barang_id' => ['required', 'exists:barangs,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_pinjam' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];

        if (!$isConsumable) {
            $validationRules['tanggal_kembali'] = ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:tanggal_pinjam'];
            $validationRules['lokasi'] = ['required', 'string', 'max:255'];
        }

        $data = $request->validate($validationRules);

        $user = auth('api')->user();
        if (!$user) {
            Log::warning("Peminjaman gagal: Pengguna tidak terautentikasi");
            return response()->json([
                'status' => 'error',
                'message' => 'Pengguna tidak terautentikasi.',
            ], 401);
        }

        if (!$isConsumable) {
            $pinjam = Carbon::parse($data['tanggal_pinjam']);
            $kembali = Carbon::parse($data['tanggal_kembali']);
            if ($pinjam->isSameDay($kembali) && ($kembali->hour > 18 || ($kembali->hour == 18 && $kembali->minute > 0))) {
                Log::warning("Peminjaman gagal: Pengembalian di hari yang sama tidak boleh setelah pukul 18:00");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengembalian di hari yang sama harus sebelum atau pada pukul 18:00.',
                ], 400);
            }

            if ($kembali->diffInDays($pinjam) > 7) {
                Log::warning("Peminjaman gagal: Durasi peminjaman melebihi 7 hari");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Durasi peminjaman tidak boleh lebih dari 7 hari.',
                ], 400);
            }
        }

        if ($barang->stok < $data['jumlah']) {
            Log::warning("Peminjaman gagal: Stok barang ID {$data['barang_id']} tidak mencukupi untuk {$data['jumlah']} unit");
            return response()->json([
                'status' => 'error',
                'message' => 'Stok barang tidak cukup untuk jumlah yang diminta!',
            ], 400);
        }

        if (Borrow::where('user_id', $user->id)
            ->where('barang_id', $data['barang_id'])
            ->whereIn('status', ['pending', 'assigned'])
            ->exists()) {
            Log::warning("Peminjaman gagal: User ID {$user->id} sudah memiliki peminjaman aktif untuk barang ID {$data['barang_id']}");
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah memiliki peminjaman aktif untuk barang ini!',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $borrowData = [
                'user_id' => $user->id,
                'barang_id' => $data['barang_id'],
                'jumlah_unit' => $data['jumlah'],
                'tanggal_pinjam' => $data['tanggal_pinjam'],
                'tanggal_kembali' => $isConsumable ? null : $data['tanggal_kembali'],
                'lokasi' => $isConsumable ? null : $data['lokasi'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'status' => 'pending',
            ];
            $borrow = Borrow::create($borrowData);

            $this->sendNotification(
                $user->id,
                'Peminjaman Baru',
                "Peminjaman {$barang->nama} telah dibuat, menunggu persetujuan admin.",
                'info',
                $borrow->id
            );
            $this->notifyAllAdmins(
                'Peminjaman Baru',
                "User {$user->name} mengajukan peminjaman {$barang->nama}, menunggu persetujuan.",
                'info',
                $borrow->id
            );

            DB::commit();
            Log::info("Peminjaman berhasil dibuat: User ID {$user->id}, Barang ID {$data['barang_id']}, Jumlah diminta: {$data['jumlah']}");
            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman berhasil dibuat!',
                'data' => $borrow,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal membuat peminjaman: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat peminjaman, coba lagi nanti.',
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $borrow = Borrow::with(['user', 'barang'])->find($id);
        if (!$borrow || !$borrow->barang || !$borrow->user) {
            Log::error("Borrow ID {$id} tidak ditemukan atau relasi user/barang invalid");
            return response()->json([
                'status' => 'error',
                'message' => 'Data peminjaman, user, atau barang tidak valid',
            ], 404);
        }

        if ($borrow->status !== 'pending') {
            Log::warning("Update peminjaman gagal: Peminjaman ID {$id} tidak dalam status pending");
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya peminjaman berstatus pending yang bisa diedit',
            ], 400);
        }

        $user = auth('api')->user();
        if (!$user || $borrow->user_id !== $user->id) {
            Log::warning("Update peminjaman gagal: User ID {$user->id} tidak memiliki akses untuk Borrow ID {$id}");
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk mengedit peminjaman ini.',
            ], 403);
        }

        $isConsumable = strtolower($borrow->barang->tipe) === 'consumable';

        $validationRules = [
            'barang_id' => ['required', 'exists:barangs,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_pinjam' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];

        if (!$isConsumable) {
            $validationRules['tanggal_kembali'] = ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:tanggal_pinjam'];
            $validationRules['lokasi'] = ['required', 'string', 'max:255'];
        }

        $data = $request->validate($validationRules);

        if (!$isConsumable) {
            $pinjam = Carbon::parse($data['tanggal_pinjam']);
            $kembali = Carbon::parse($data['tanggal_kembali']);
            if ($pinjam->isSameDay($kembali) && ($kembali->hour > 18 || ($kembali->hour == 18 && $kembali->minute > 0))) {
                Log::warning("Update peminjaman gagal: Pengembalian di hari yang sama tidak boleh setelah pukul 18:00");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengembalian di hari yang sama harus sebelum atau pada pukul 18:00.',
                ], 400);
            }

            if ($kembali->diffInDays($pinjam) > 7) {
                Log::warning("Update peminjaman gagal: Durasi peminjaman melebihi 7 hari");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Durasi peminjaman tidak boleh lebih dari 7 hari.',
                ], 400);
            }
        }

        $barang = Barang::find($data['barang_id']);
        if (!$barang || $barang->stok < $data['jumlah']) {
            Log::warning("Update peminjaman gagal: Stok barang ID {$data['barang_id']} tidak mencukupi untuk {$data['jumlah']} unit");
            return response()->json([
                'status' => 'error',
                'message' => 'Stok barang tidak cukup untuk jumlah yang diminta!',
            ], 400);
        }

        if (Borrow::where('user_id', $user->id)
            ->where('barang_id', $data['barang_id'])
            ->whereIn('status', ['pending', 'assigned'])
            ->where('id', '!=', $id)
            ->exists()) {
            Log::warning("Update peminjaman gagal: User ID {$user->id} sudah memiliki peminjaman aktif untuk barang ID {$data['barang_id']}");
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah memiliki peminjaman aktif untuk barang ini!',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $borrowData = [
                'barang_id' => $data['barang_id'],
                'jumlah_unit' => $data['jumlah'],
                'tanggal_pinjam' => $data['tanggal_pinjam'],
                'tanggal_kembali' => $isConsumable ? null : $data['tanggal_kembali'],
                'lokasi' => $isConsumable ? null : $data['lokasi'],
                'deskripsi' => $data['deskripsi'] ?? null,
            ];
            $borrow->update($borrowData);

            $this->sendNotification(
                $user->id,
                'Peminjaman Diperbarui',
                "Peminjaman {$barang->nama} telah diperbarui, menunggu persetujuan admin.",
                'info',
                $borrow->id
            );
            $this->notifyAllAdmins(
                'Peminjaman Diperbarui',
                "User {$user->name} memperbarui peminjaman {$barang->nama}, menunggu persetujuan.",
                'info',
                $borrow->id
            );

            DB::commit();
            Log::info("Peminjaman berhasil diperbarui: Peminjaman ID {$id}, Jumlah diminta: {$data['jumlah']}");
            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman berhasil diperbarui!',
                'data' => $borrow->fresh(['user', 'barang']),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal memperbarui peminjaman ID {$id}: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui peminjaman, coba lagi nanti.',
            ], 500);
        }
    }

    public function assignUnit(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'unit_ids' => ['required', 'array', 'min:1'],
            'unit_ids.*' => ['exists:unit_barangs,id'],
        ]);

        $borrow = Borrow::with(['user', 'barang'])->find($id);
        if (!$borrow || !$borrow->barang || !$borrow->user) {
            Log::error("Borrow ID {$id} tidak ditemukan atau relasi user/barang invalid");
            return response()->json([
                'status' => 'error',
                'message' => 'Data peminjaman, user, atau barang tidak valid',
            ], 404);
        }

        if ($borrow->status !== 'pending') {
            Log::warning("Assign unit gagal: Peminjaman ID {$id} tidak dalam status pending");
            return response()->json([
                'status' => 'error',
                'message' => 'Peminjaman harus berstatus pending',
            ], 400);
        }

        if (count($data['unit_ids']) !== $borrow->jumlah_unit) {
            Log::warning("Assign unit gagal: Jumlah unit dipilih (" . count($data['unit_ids']) . ") tidak sesuai jumlah_unit ({$borrow->jumlah_unit})");
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah unit yang dipilih harus sesuai dengan jumlah yang diminta',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $isConsumable = strtolower($borrow->barang->tipe) === 'consumable';

            if ($isConsumable) {
                // Untuk barang consumable, kurangi stok langsung
                $barang = Barang::find($borrow->barang_id);
                $newStok = $barang->stok - $borrow->jumlah_unit;
                if ($newStok < 0) {
                    throw new \Exception('Stok tidak cukup untuk barang sekali pakai.');
                }
                $barang->update(['stok' => $newStok]);
            } else {
                // Panggil stored procedure untuk aksi 'assign' untuk barang returnable
                DB::statement('CALL manage_barang_stock(?, ?)', [$borrow->id, 'assign']);
            }

            foreach ($data['unit_ids'] as $unitId) {
                $unit = UnitBarang::lockForUpdate()->find($unitId);
                if (!$this->isUnitValid($unit, $borrow, $unitId)) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Unit ID {$unitId} tidak sesuai atau sudah digunakan",
                    ], 400);
                }

                DetailBorrow::create([
                    'borrow_id' => $borrow->id,
                    'unit_barang_id' => $unitId,
                    'barang_id' => $borrow->barang_id,
                    'jumlah' => 1,
                    'status' => 'active',
                ]);

                $unit->update(['status' => $isConsumable ? 'used' : 'dipinjam']);
            }

            $borrow->update(['status' => 'assigned', 'tanggal_approve' => now()]);

            $this->sendNotification(
                $borrow->user_id,
                'Peminjaman Disetujui',
                "Peminjaman {$borrow->barang->nama} telah disetujui oleh admin.",
                'info',
                $borrow->id
            );
            $this->notifyAllAdmins(
                'Peminjaman Disetujui',
                "Peminjaman {$borrow->barang->nama} untuk user {$borrow->user->name} telah disetujui.",
                'info',
                $borrow->id
            );

            DB::commit();
            Log::info("Unit berhasil dialokasikan untuk peminjaman ID {$id}, Jumlah unit: " . count($data['unit_ids']));
            return response()->json([
                'status' => 'success',
                'message' => 'Unit berhasil di-assign!',
                'data' => $borrow->fresh(['user', 'barang', 'details.unitBarang']),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal mengalokasikan unit untuk peminjaman ID {$id}: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal meng-assign unit: ' . ($e->getCode() == '45000' ? $e->getMessage() : 'Terjadi kesalahan server'),
            ], $e->getCode() == '45000' ? 400 : 500);
        }
    }

    // Metode lain (index, assign, destroy, reject, cancelExpired, showNotifications) tetap sama seperti kode asli
    public function index(Request $request): JsonResponse
    {
        $query = Borrow::with(['user', 'barang', 'details.unitBarang'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('barang', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
            Log::info("Pencarian peminjaman dengan kata kunci: {$search}");
        }

        $borrows = $query->paginate(10);
        Log::info("Mengambil daftar peminjaman, Total: {$borrows->total()}");
        return response()->json([
            'status' => 'success',
            'data' => $borrows,
        ], 200);
    }

    public function assign(int $id): JsonResponse
    {
        $borrow = Borrow::with('barang')->find($id);
        if (!$borrow || !$borrow->barang) {
            Log::error("Borrow ID {$id} tidak ditemukan atau barang invalid");
            return response()->json([
                'status' => 'error',
                'message' => 'Data peminjaman atau barang tidak valid',
            ], 404);
        }

        if ($borrow->status !== 'pending') {
            Log::warning("Assign unit gagal: Peminjaman ID {$id} tidak dalam status pending");
            return response()->json([
                'status' => 'error',
                'message' => 'Peminjaman harus berstatus pending',
            ], 400);
        }

        $units = UnitBarang::where('barang_id', $borrow->barang_id)
            ->whereRaw('TRIM(LOWER(status)) = ?', ['tersedia'])
            ->get();

        Log::info("Unit tersedia untuk Borrow ID {$id}, Barang ID {$borrow->barang_id}: " . $units->pluck('kode_barang')->toJson());
        if ($units->isEmpty()) {
            Log::warning("Tidak ada unit tersedia untuk Barang ID {$borrow->barang_id} pada Borrow ID {$id}");
            return response()->json([
                'status' => 'error',
                'message' => "Tidak ada unit tersedia untuk barang ini (Barang ID: {$borrow->barang_id}).",
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'borrow' => $borrow,
                'units' => $units,
            ],
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $borrow = Borrow::with(['user', 'barang'])->find($id);
        if (!$borrow || !$borrow->barang || !$borrow->user) {
            Log::error("Borrow ID {$id} tidak ditemukan atau relasi user/barang invalid");
            return response()->json([
                'status' => 'error',
                'message' => 'Data peminjaman, user, atau barang tidak valid',
            ], 404);
        }

        if ($borrow->status !== 'pending') {
            Log::warning("Hapus peminjaman gagal: Peminjaman ID {$id} tidak dalam status pending");
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya peminjaman berstatus pending yang bisa dihapus',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $this->sendNotification(
                $borrow->user_id,
                'Peminjaman Dibatalkan',
                "Peminjaman {$borrow->barang->nama} telah dibatalkan oleh admin.",
                'error',
                $borrow->id
            );
            $this->notifyAllAdmins(
                'Peminjaman Dibatalkan',
                "Peminjaman {$borrow->barang->nama} untuk user {$borrow->user->name} telah dibatalkan.",
                'info',
                $borrow->id
            );

            $borrow->delete();
            DB::commit();
            Log::info("Peminjaman berhasil dihapus: Peminjaman ID {$id}");
            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman berhasil dihapus!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menghapus peminjaman ID {$id}: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus peminjaman, coba lagi nanti.',
            ], 500);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $borrow = Borrow::with(['user', 'barang'])->find($id);
        if (!$borrow || !$borrow->barang || !$borrow->user) {
            Log::error("Borrow ID {$id} tidak ditemukan atau relasi user/barang invalid");
            return response()->json([
                'status' => 'error',
                'message' => 'Data peminjaman, user, atau barang tidak valid',
            ], 404);
        }

        if ($borrow->status !== 'pending') {
            Log::warning("Reject peminjaman gagal: Peminjaman ID {$id} tidak dalam status pending");
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya peminjaman berstatus pending yang bisa ditolak!',
            ], 400);
        }

        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            $borrow->update([
                'status' => 'rejected',
                'alasan_reject' => $data['alasan'],
                'tanggal_approve' => now(),
            ]);

            $this->sendNotification(
                $borrow->user_id,
                'Peminjaman Ditolak',
                "Peminjaman {$borrow->barang->nama} ditolak. Alasan: {$data['alasan']}",
                'error',
                $borrow->id
            );
            $this->notifyAllAdmins(
                'Peminjaman Ditolak',
                "Peminjaman {$borrow->barang->nama} untuk user {$borrow->user->name} ditolak. Alasan: {$data['alasan']}",
                'info',
                $borrow->id
            );

            DB::commit();
            Log::info("Peminjaman berhasil ditolak: Peminjaman ID {$id}, Alasan: {$data['alasan']}");
            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman berhasil ditolak!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menolak peminjaman ID {$id}: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menolak peminjaman, coba lagi nanti.',
            ], 500);
        }
    }

    public function cancelExpired(Request $request, int $id): JsonResponse
    {
        $borrow = Borrow::with(['user', 'barang'])->find($id);
        if (!$borrow || !$borrow->barang || !$borrow->user) {
            Log::error("Borrow ID {$id} tidak ditemukan atau relasi user/barang invalid");
            return response()->json([
                'status' => 'error',
                'message' => 'Data peminjaman, user, atau barang tidak valid',
            ], 404);
        }

        if (!in_array($borrow->status, ['pending', 'assigned']) || Carbon::parse($borrow->tanggal_kembali)->greaterThanOrEqualTo(now())) {
            Log::warning("Cancel expired peminjaman gagal: Peminjaman ID {$id} tidak dalam status expired atau bukan pending/assigned");
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya peminjaman berstatus pending atau assigned yang sudah expired yang bisa dibatalkan!',
            ], 400);
        }

        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            if ($borrow->status === 'assigned') {
                $details = DetailBorrow::where('borrow_id', $borrow->id)->where('status', 'active')->get();
                foreach ($details as $detail) {
                    UnitBarang::where('id', $detail->unit_barang_id)->update(['status' => 'Tersedia']);
                    $detail->update(['status' => 'cancelled']);
                }
                Barang::where('id', $borrow->barang_id)->increment('stok', $borrow->jumlah_unit);
            }

            $borrow->update([
                'status' => 'cancelled',
                'alasan_reject' => $data['alasan'],
                'tanggal_approve' => now(),
            ]);

            $this->sendNotification(
                $borrow->user_id,
                'Peminjaman Expired Dibatalkan',
                "Peminjaman {$borrow->barang->nama} telah dibatalkan karena sudah expired. Alasan: {$data['alasan']}",
                'error',
                $borrow->id
            );
            $this->notifyAllAdmins(
                'Peminjaman Expired Dibatalkan',
                "Peminjaman {$borrow->barang->nama} untuk user {$borrow->user->name} telah dibatalkan karena expired. Alasan: {$data['alasan']}",
                'info',
                $borrow->id
            );

            DB::commit();
            Log::info("Peminjaman expired berhasil dibatalkan: Peminjaman ID {$id}, Alasan: {$data['alasan']}");
            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman expired berhasil dibatalkan!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal membatalkan peminjaman expired ID {$id}: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan peminjaman expired, coba lagi nanti.',
            ], 500);
        }
    }

    public function showNotifications(): JsonResponse
    {
        $notifications = Notification::with(['user', 'borrow.barang'])
            ->orderBy('tanggal_notif', 'desc')
            ->get();
        Log::info("Mengambil daftar notifikasi, Total: {$notifications->count()}");
        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ], 200);
    }
}