<?php

namespace App\Http\Controllers;

use App\Models\ReturnLog;
use App\Enums\ReturnStatus;
use App\Models\UnitBarang;
use App\Models\Borrow;
use App\Models\DetailBorrow;
use App\Models\Notification;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    private function sendNotification($userId, $title, $message, $type, $borrowId = null)
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

    private function notifyAllAdmins($title, $message, $type, $borrowId = null)
    {
        $admins = Admin::pluck('id');
        foreach ($admins as $adminId) {
            $this->sendNotification($adminId, $title, $message, $type, $borrowId);
        }
    }

    public function index(Request $request)
    {
        $query = ReturnLog::with(['user', 'borrow.barang', 'detailReturns.unitBarang'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('borrow.barang', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
            Log::info("Pencarian pengembalian dengan kata kunci: {$search}");
        }

        $returnLogs = $query->paginate(10);
        Log::info("Mengambil daftar pengembalian, Total: " . $returnLogs->total());
        return view('return.index', compact('returnLogs'));
    }

    public function approveOrReject(Request $request, $returnLogId)
    {
        $request->validate([
            'status' => 'required|in:completed,rejected',
            'alasan' => 'required_if:status,rejected|string|max:1000',
        ], [
            'alasan.required_if' => 'Alasan penolakan wajib diisi.',
        ]);

        $returnLog = ReturnLog::with(['detailReturns.unitBarang', 'borrow.barang', 'user'])->findOrFail($returnLogId);
        if (!$returnLog->borrow || !$returnLog->borrow->barang || !$returnLog->user) {
            Log::error("ReturnLog ID {$returnLogId} punya relasi tidak valid");
            return redirect()->route('admin.return.index')->with('error', 'Data peminjaman atau user tidak valid');
        }
        if ($returnLog->status !== ReturnStatus::PENDING) {
            Log::warning("ReturnLog ID {$returnLogId} bukan pending, status: {$returnLog->status}");
            return redirect()->route('admin.return.index')->with('error', 'Hanya pengembalian pending yang bisa diubah');
        }

        DB::beginTransaction();
        try {
            if ($request->status === 'completed') {
                $returnLog->update([
                    'status' => ReturnStatus::COMPLETED,
                    'tanggal_approve' => now(),
                ]);

                foreach ($returnLog->detailReturns as $detailReturn) {
                    $detailBorrow = DetailBorrow::findOrFail($detailReturn->detail_borrow_id);
                    $unit = UnitBarang::findOrFail($detailReturn->unit_barang_id);

                    $detailBorrow->update(['status' => 'returned']);
                    if ($detailReturn->kerusakan !== 'Barang dilaporkan hilang' && !$detailReturn->kerusakan) {
                        $unit->update(['status' => 'Tersedia']);
                    }
                }

                $remainingActiveDetails = DetailBorrow::where('borrow_id', $returnLog->borrow_id)
                    ->where('status', 'active')
                    ->count();
                if ($remainingActiveDetails === 0) {
                    $returnLog->borrow->update(['status' => 'returned']);
                }

                $this->sendNotification(
                    $returnLog->user_id,
                    'Pengembalian Disetujui',
                    "Pengembalian barang disetujui. Terlambat: " . ($returnLog->terlambat > 0 ? ($returnLog->terlambat >= 1 ? floor($returnLog->terlambat) . ' hari' : floor($returnLog->terlambat * 24) . ' jam') : 'Tidak terlambat'),
                    'success',
                    $returnLog->borrow_id
                );
                $this->notifyAllAdmins(
                    'Pengembalian Disetujui',
                    "Pengembalian barang untuk {$returnLog->user->name} disetujui.",
                    'info',
                    $returnLog->borrow_id
                );
            } else {
                $returnLog->update([
                    'status' => ReturnStatus::REJECTED,
                    'alasan_reject' => $request->alasan,
                    'tanggal_approve' => now(),
                ]);

                foreach ($returnLog->detailReturns as $detailReturn) {
                    $detailBorrow = DetailBorrow::findOrFail($detailReturn->detail_borrow_id);
                    $unit = UnitBarang::findOrFail($detailReturn->unit_barang_id);

                    $unit->update(['status' => 'dipinjam']);
                    $detailBorrow->update(['status' => 'active']);
                    $detailBorrow->borrow->barang->decrement('stok');
                }

                $this->sendNotification(
                    $returnLog->user_id,
                    'Pengembalian Ditolak',
                    "Pengembalian barang ditolak. Alasan: {$request->alasan}",
                    'error',
                    $returnLog->borrow_id
                );
                $this->notifyAllAdmins(
                    'Pengembalian Ditolak',
                    "Pengembalian barang untuk {$returnLog->user->name} ditolak.",
                    'info',
                    $returnLog->borrow_id
                );
            }

            DB::commit();
            Log::info("Status pengembalian ID {$returnLogId} diubah menjadi {$request->status}");
            return redirect()->route('admin.return.index')->with('success', "Pengembalian diubah ke status {$request->status}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal ubah status pengembalian ID {$returnLogId}: {$e->getMessage()}");
            return redirect()->route('admin.return.index')->with('error', 'Gagal ubah status pengembalian, coba lagi!');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'borrow_id' => 'required|exists:borrows,id',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
        ]);

        DB::beginTransaction();
        try {
            $terlambat = \Carbon\Carbon::parse($request->tanggal_kembali)->diffInDays(\Carbon\Carbon::parse($request->tanggal_pinjam));

            $returnLog = ReturnLog::create([
                'user_id' => $request->user_id,
                'borrow_id' => $request->borrow_id,
                'barang_id' => Borrow::findOrFail($request->borrow_id)->barang_id,
                'tanggal_pinjam' => $request->tanggal_pinjam,
                'tanggal_kembali' => $request->tanggal_kembali,
                'terlambat' => $terlambat,
                'status' => ReturnStatus::PENDING,
            ]);

            $this->sendNotification(
                $returnLog->user_id,
                'Pengembalian Diajukan',
                "Pengembalian barang telah diajukan. Terlambat: " . ($terlambat > 0 ? ($terlambat >= 1 ? floor($terlambat) . ' hari' : floor($terlambat * 24) . ' jam') : 'Tidak terlambat'),
                'info',
                $returnLog->borrow_id
            );
            $this->notifyAllAdmins(
                'Pengembalian Baru',
                "Pengembalian barang oleh {$returnLog->user->name} diajukan.",
                'info',
                $returnLog->borrow_id
            );

            DB::commit();
            Log::info("Pengembalian ID {$returnLog->id} berhasil dibuat");
            return redirect()->route('admin.return.index')->with('success', 'Pengembalian berhasil diajukan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal membuat pengembalian: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Gagal membuat pengembalian, coba lagi!');
        }
    }
}