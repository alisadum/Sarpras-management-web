<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barang;
use App\Models\Borrow;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function dashboard()
    {
        $usersCount = User::count();
        $barangCount = Barang::count();
        $activeBorrowsCount = Borrow::where('status', 'active')->count();
        $returnedItemsCount = Borrow::where('status', 'returned')->count();

        // Data chart peminjaman per bulan
        $borrowChartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $borrowChartData[] = Borrow::whereMonth('created_at', $i)
                ->whereYear('created_at', date('Y'))
                ->count();
        }
        // Jika array kosong/ada error, isi 12 nol
        if (empty($borrowChartData) || count($borrowChartData) < 12) {
            $borrowChartData = array_fill(0, 12, 0);
        }

        // Ambil notifikasi terbaru
        $recentNotifications = Notification::orderBy('tanggal_notif', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'usersCount',
            'barangCount',
            'activeBorrowsCount',
            'returnedItemsCount',
            'borrowChartData',
            'recentNotifications'
        ));
    }
}
