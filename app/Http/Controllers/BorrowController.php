<?php

namespace App\Http\Controllers;

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
use Carbon\Carbon;

class BorrowController extends Controller
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
        $query = Borrow::with(['user', 'barang', 'details.unitBarang'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('barang', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
            Log::info("Pencarian peminjaman dengan kata kunci: {$search}");
        }

        $borrows = $query->paginate(10);
        Log::info("Mengambil daftar peminjaman, Total: " . $borrows->total());
        return view('borrows.index', compact('borrows'));
    }

    public function create()
    {
        $users = User::where('status', 'active')->get();
        $barangs = Barang::where('stok', '>', 0)->get();
        return view('borrows.create', compact('users', 'barangs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barang_id' => 'required|exists:barangs,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal_pinjam' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'tanggal_kembali' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:tanggal_pinjam'],
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
        ]);

        // Validasi waktu pengembalian (maksimal 18:00 jika di hari yang sama)
        $pinjam = Carbon::parse($data['tanggal_pinjam']);
        $kembali = Carbon::parse($data['tanggal_kembali']);
        if ($pinjam->isSameDay($kembali) && ($kembali->hour > 18 || ($kembali->hour == 18 && $kembali->minute > 0))) {
            Log::warning("Peminjaman gagal: Pengembalian di hari yang sama tidak boleh setelah pukul 18:00");
            return redirect()->route('admin.borrows.index')->with('error', 'Pengembalian di hari yang sama harus sebelum atau pada pukul 18:00.');
        }

        // Validasi batas maksimal 7 hari
        if ($kembali->diffInDays($pinjam) > 7) {
            Log::warning("Peminjaman gagal: Durasi peminjaman melebihi 7 hari");
            return redirect()->route('admin.borrows.index')->with('error', 'Durasi peminjaman tidak boleh lebih dari 7 hari.');
        }

        $user = User::findOrFail($data['user_id']);
        if ($user->status === 'blocked') {
            Log::warning("Peminjaman gagal: User ID {$data['user_id']} kena block");
            return redirect()->route('admin.borrows.index')->with('error', 'User ini diblokir, pilih user lain.');
        }

        $barang = Barang::findOrFail($data['barang_id']);
        if ($barang->stok < $data['jumlah']) {
            Log::warning("Peminjaman gagal: Stok barang ID {$data['barang_id']} tidak mencukupi untuk {$data['jumlah']} unit");
            return redirect()->route('admin.borrows.index')->with('error', 'Stok barang tidak cukup untuk jumlah yang diminta!');
        }

        if (Borrow::where('user_id', $data['user_id'])
            ->where('barang_id', $data['barang_id'])
            ->whereIn('status', ['pending', 'assigned'])
            ->exists()) {
            Log::warning("Peminjaman gagal: User ID {$data['user_id']} sudah memiliki peminjaman aktif untuk barang ID {$data['barang_id']}");
            return redirect()->route('admin.borrows.index')->with('error', 'User sudah memiliki peminjaman aktif untuk barang ini!');
        }

        DB::beginTransaction();
        try {
            $data['status'] = 'pending';
            $data['jumlah_unit'] = $data['jumlah'];
            unset($data['jumlah']);
            $borrow = Borrow::create($data);

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
            Log::info("Peminjaman berhasil dibuat: User ID {$data['user_id']}, Barang ID {$data['barang_id']}, Jumlah diminta: {$data['jumlah_unit']}");
            return redirect()->route('admin.borrows.index')->with('success', 'Peminjaman berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal membuat peminjaman: {$e->getMessage()}");
            return redirect()->route('admin.borrows.index')->with('error', 'Gagal membuat peminjaman, coba lagi nanti.');
        }
    }

    public function assign($id)
    {
        $borrow = Borrow::with('barang')->findOrFail($id);
        if (!$borrow->barang) {
            Log::error("Barang tidak ditemukan untuk Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', 'Data barang tidak valid');
        }
        if ($borrow->status !== 'pending') {
            Log::warning("Assign unit gagal: Peminjaman ID {$id} tidak dalam status pending");
            return redirect()->route('admin.borrows.index')->with('error', 'Peminjaman harus berstatus pending');
        }

        $units = UnitBarang::where('barang_id', $borrow->barang_id)
            ->whereRaw('TRIM(LOWER(status)) = ?', ['tersedia'])
            ->get();

        Log::info("Unit tersedia untuk Borrow ID {$id}, Barang ID {$borrow->barang_id}: " . $units->pluck('kode_barang')->toJson());
        if ($units->isEmpty()) {
            Log::warning("Tidak ada unit tersedia untuk Barang ID {$borrow->barang_id} pada Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', "Tidak ada unit tersedia untuk barang ini (Barang ID: {$borrow->barang_id}). Periksa status unit di tabel unit_barangs.");
        }

        return view('borrows.assign', compact('borrow', 'units'));
    }

    public function assignUnit(Request $request, $id)
    {
        $data = $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'exists:unit_barangs,id',
        ]);

        $borrow = Borrow::with(['user', 'barang'])->findOrFail($id);
        if (!$borrow->barang || !$borrow->user) {
            Log::error("Relasi user atau barang tidak ditemukan untuk Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', 'Data user atau barang tidak valid');
        }
        if ($borrow->status !== 'pending') {
            Log::warning("Assign unit gagal: Peminjaman ID {$id} tidak dalam status pending");
            return redirect()->route('admin.borrows.index')->with('error', 'Peminjaman harus berstatus pending');
        }

        Log::info("Unit IDs yang dipilih untuk Borrow ID {$id}: " . json_encode($data['unit_ids']));

        if (count($data['unit_ids']) != $borrow->jumlah_unit) {
            Log::warning("Assign unit gagal: Jumlah unit dipilih (" . count($data['unit_ids']) . ") tidak sesuai jumlah_unit ({$borrow->jumlah_unit}), Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', 'Jumlah unit yang dipilih harus sesuai dengan jumlah yang diminta');
        }

        $user = User::findOrFail($borrow->user_id);
        if ($user->status === 'blocked') {
            Log::warning("Assign unit gagal: User ID {$borrow->user_id} kena block");
            return redirect()->route('admin.borrows.index')->with('error', 'User ini diblokir, gagal assign unit.');
        }

        $barang = Barang::findOrFail($borrow->barang_id);
        if ($barang->stok < count($data['unit_ids'])) {
            Log::warning("Assign unit gagal: Stok barang ID {$borrow->barang_id} ({$barang->stok}) tidak mencukupi untuk " . count($data['unit_ids']) . " unit");
            return redirect()->route('admin.borrows.index')->with('error', 'Stok barang tidak cukup untuk jumlah unit yang diminta');
        }

        DB::beginTransaction();
        try {
            foreach ($data['unit_ids'] as $unitId) {
                $unit = UnitBarang::lockForUpdate()->findOrFail($unitId);

                if ($unit->barang_id !== $borrow->barang_id || trim(strtolower($unit->status)) !== 'tersedia') {
                    DB::rollBack();
                    Log::error("Assign unit gagal: Unit ID {$unitId} tidak sesuai atau tidak tersedia (Barang ID: {$unit->barang_id}, Status: {$unit->status})");
                    return redirect()->route('admin.borrows.index')->with('error', "Unit {$unit->kode_barang} tidak sesuai atau tidak tersedia");
                }

                if (DetailBorrow::where('unit_barang_id', $unitId)->where('status', 'active')->exists()) {
                    DB::rollBack();
                    Log::error("Assign unit gagal: Unit ID {$unitId} sudah digunakan di peminjaman lain");
                    return redirect()->route('admin.borrows.index')->with('error', "Unit {$unit->kode_barang} sudah digunakan");
                }

                DetailBorrow::create([
                    'borrow_id' => $borrow->id,
                    'unit_barang_id' => $unitId,
                    'barang_id' => $borrow->barang_id,
                    'jumlah' => 1,
                    'status' => 'active',
                ]);

                $unit->update(['status' => 'dipinjam']);
            }

            $barang->decrement('stok', count($data['unit_ids']));
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
            return redirect()->route('admin.borrows.index')->with('success', 'Unit berhasil di-assign!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal mengalokasikan unit untuk peminjaman ID {$id}: {$e->getMessage()}");
            return redirect()->route('admin.borrows.index')->with('error', 'Gagal meng-assign unit: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $borrow = Borrow::with(['user', 'barang'])->findOrFail($id);
        if (!$borrow->barang || !$borrow->user) {
            Log::error("Relasi user atau barang tidak ditemukan untuk Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', 'Data user atau barang tidak valid');
        }
        if ($borrow->status !== 'pending') {
            Log::warning("Edit peminjaman gagal: Peminjaman ID {$id} tidak dalam status pending");
            return redirect()->route('admin.borrows.index')->with('error', 'Hanya peminjaman berstatus pending yang bisa diedit');
        }

        $users = User::where('status', 'active')->get();
        $barangs = Barang::where('stok', '>', 0)->get();
        return view('borrows.edit', compact('borrow', 'users', 'barangs'));
    }

    public function update(Request $request, $id)
    {
        $borrow = Borrow::with(['user', 'barang'])->findOrFail($id);
        if (!$borrow->barang || !$borrow->user) {
            Log::error("Relasi user atau barang tidak ditemukan untuk Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', 'Data user atau barang tidak valid');
        }
        if ($borrow->status !== 'pending') {
            Log::warning("Update peminjaman gagal: Peminjaman ID {$id} tidak dalam status pending");
            return redirect()->route('admin.borrows.index')->with('error', 'Hanya peminjaman berstatus pending yang bisa diedit');
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'barang_id' => 'required|exists:barangs,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal_pinjam' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'tanggal_kembali' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:tanggal_pinjam'],
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
        ]);

        // Validasi waktu pengembalian (maksimal 18:00 jika di hari yang sama)
        $pinjam = Carbon::parse($data['tanggal_pinjam']);
        $kembali = Carbon::parse($data['tanggal_kembali']);
        if ($pinjam->isSameDay($kembali) && ($kembali->hour > 18 || ($kembali->hour == 18 && $kembali->minute > 0))) {
            Log::warning("Update peminjaman gagal: Pengembalian di hari yang sama tidak boleh setelah pukul 18:00");
            return redirect()->route('admin.borrows.index')->with('error', 'Pengembalian di hari yang sama harus sebelum atau pada pukul 18:00.');
        }

        // Validasi batas maksimal 7 hari
        if ($kembali->diffInDays($pinjam) > 7) {
            Log::warning("Update peminjaman gagal: Durasi peminjaman melebihi 7 hari");
            return redirect()->route('admin.borrows.index')->with('error', 'Durasi peminjaman tidak boleh lebih dari 7 hari.');
        }

        $user = User::findOrFail($data['user_id']);
        if ($user->status === 'blocked') {
            Log::warning("Update peminjaman gagal: User ID {$data['user_id']} kena block");
            return redirect()->route('admin.borrows.index')->with('error', 'User ini diblokir, pilih user lain.');
        }

        $barang = Barang::findOrFail($data['barang_id']);
        if ($barang->stok < $data['jumlah']) {
            Log::warning("Update peminjaman gagal: Stok barang ID {$data['barang_id']} tidak mencukupi untuk {$data['jumlah']} unit");
            return redirect()->route('admin.borrows.index')->with('error', 'Stok barang tidak cukup untuk jumlah yang diminta!');
        }

        if (Borrow::where('user_id', $data['user_id'])
            ->where('barang_id', $data['barang_id'])
            ->whereIn('status', ['pending', 'assigned'])
            ->where('id', '!=', $id)
            ->exists()) {
            Log::warning("Update peminjaman gagal: User ID {$data['user_id']} sudah memiliki peminjaman aktif untuk barang ID {$data['barang_id']}");
            return redirect()->route('admin.borrows.index')->with('error', 'User sudah memiliki peminjaman aktif untuk barang ini!');
        }

        DB::beginTransaction();
        try {
            $data['jumlah_unit'] = $data['jumlah'];
            unset($data['jumlah']);
            $borrow->update($data);

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
            Log::info("Peminjaman berhasil diperbarui: Peminjaman ID {$id}, Jumlah diminta: {$data['jumlah_unit']}");
            return redirect()->route('admin.borrows.index')->with('success', 'Peminjaman berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal memperbarui peminjaman ID {$id}: {$e->getMessage()}");
            return redirect()->route('admin.borrows.index')->with('error', 'Gagal memperbarui peminjaman, coba lagi nanti.');
        }
    }

    public function destroy($id)
    {
        $borrow = Borrow::with(['user', 'barang'])->findOrFail($id);
        if (!$borrow->barang || !$borrow->user) {
            Log::error("Relasi user atau barang tidak ditemukan untuk Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', 'Data user atau barang tidak valid');
        }
        if ($borrow->status !== 'pending') {
            Log::warning("Hapus peminjaman gagal: Peminjaman ID {$id} tidak dalam status pending");
            return redirect()->route('admin.borrows.index')->with('error', 'Hanya peminjaman berstatus pending yang bisa dihapus');
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
            return redirect()->route('admin.borrows.index')->with('success', 'Peminjaman berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menghapus peminjaman ID {$id}: {$e->getMessage()}");
            return redirect()->route('admin.borrows.index')->with('error', 'Gagal menghapus peminjaman, coba lagi nanti.');
        }
    }

    public function reject(Request $request, $id)
    {
        $borrow = Borrow::with(['user', 'barang'])->findOrFail($id);
        if (!$borrow->barang || !$borrow->user) {
            Log::error("Relasi user atau barang tidak ditemukan untuk Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', 'Data user atau barang tidak valid');
        }
        if ($borrow->status !== 'pending') {
            Log::warning("Reject peminjaman gagal: Peminjaman ID {$id} tidak dalam status pending");
            return redirect()->route('admin.borrows.index')->with('error', 'Hanya peminjaman berstatus pending yang bisa ditolak!');
        }

        $data = $request->validate([
            'alasan' => 'required|string|max:1000',
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
            return redirect()->route('admin.borrows.index')->with('success', 'Peminjaman berhasil ditolak!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menolak peminjaman ID {$id}: {$e->getMessage()}");
            return redirect()->route('admin.borrows.index')->with('error', 'Gagal menolak peminjaman, coba lagi nanti.');
        }
    }

    public function showBlockedUsers()
    {
        $blockedUsers = DB::table('user_block_logs')->get();
        Log::info("Mengambil daftar user blocked, Total: " . $blockedUsers->count());
        return view('borrows.blocked_users', compact('blockedUsers'));
    }

    public function showExpiredBorrows()
    {
        $expiredBorrows = Borrow::whereIn('status', ['pending', 'assigned'])
            ->where('tanggal_kembali', '<', now())
            ->with(['user', 'barang'])
            ->get();
        Log::info("Mengambil daftar peminjaman expired, Total: " . $expiredBorrows->count());
        return view('borrows.expired_borrows', compact('expiredBorrows'));
    }

    public function showNotifications()
    {
        $notifications = Notification::with(['user', 'borrow.barang'])
            ->orderBy('tanggal_notif', 'desc')
            ->get();
        Log::info("Mengambil daftar notifikasi, Total: " . $notifications->count());
        return view('borrows.notifications', compact('notifications'));
    }

    public function unblockUser($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->status !== 'blocked') {
            Log::warning("Unblock gagal: User ID {$userId} tidak dalam status blocked");
            return redirect()->route('admin.borrows.blocked_users')->with('error', 'User ini tidak diblokir!');
        }

        DB::beginTransaction();
        try {
            $user->update(['status' => 'active']);
            DB::commit();
            Log::info("User ID {$userId} berhasil di-unblock");
            return redirect()->route('admin.borrows.blocked_users')->with('success', 'User berhasil di-unblock!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal unblock user ID {$userId}: {$e->getMessage()}");
            return redirect()->route('admin.borrows.blocked_users')->with('error', 'Gagal unblock user, coba lagi nanti.');
        }
    }

    public function cancelExpired(Request $request, $id)
    {
        $borrow = Borrow::with(['user', 'barang'])->findOrFail($id);
        if (!$borrow->barang || !$borrow->user) {
            Log::error("Relasi user atau barang tidak ditemukan untuk Borrow ID {$id}");
            return redirect()->route('admin.borrows.index')->with('error', 'Data user atau barang tidak valid');
        }
        if (!in_array($borrow->status, ['pending', 'assigned']) || Carbon::parse($borrow->tanggal_kembali)->greaterThanOrEqualTo(now())) {
            Log::warning("Cancel expired peminjaman gagal: Peminjaman ID {$id} tidak dalam status expired atau bukan pending/assigned");
            return redirect()->route('admin.borrows.index')->with('error', 'Hanya peminjaman berstatus pending atau assigned yang sudah expired yang bisa dibatalkan!');
        }

        $data = $request->validate([
            'alasan' => 'required|string|max:1000',
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
            return redirect()->route('admin.borrows.index')->with('success', 'Peminjaman expired berhasil dibatalkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal membatalkan peminjaman expired ID {$id}: {$e->getMessage()}");
            return redirect()->route('admin.borrows.index')->with('error', 'Gagal membatalkan peminjaman expired, coba lagi nanti.');
        }
    }
}
