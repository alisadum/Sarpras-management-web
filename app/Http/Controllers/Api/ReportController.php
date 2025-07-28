<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\ReturnLog;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function borrowReport(Request $request)
    {
        $query = Borrow::with(['user', 'barang', 'details.unitBarang']);

        // Filter berdasarkan tanggal
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('tanggal_pinjam', [$request->start_date, $request->end_date]);
        }

        // Filter berdasarkan status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $borrows = $query->paginate(10);

        return response()->json([
            'data' => $borrows->items(),
            'pagination' => [
                'current_page' => $borrows->currentPage(),
                'total_pages' => $borrows->lastPage(),
                'total' => $borrows->total(),
            ]
        ]);
    }

    public function returnReport(Request $request)
    {
        $query = ReturnLog::with(['user', 'barang', 'unitBarang']);

        // Filter berdasarkan tanggal
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('tanggal_kembali', [$request->start_date, $request->end_date]);
        }

        // Filter berdasarkan status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan keterlambatan
        if ($request->has('terlambat') && $request->terlambat !== '') {
            $query->where('terlambat', '>', 0);
        }

        $returnLogs = $query->paginate(10);

        return response()->json([
            'data' => $returnLogs->items(),
            'pagination' => [
                'current_page' => $returnLogs->currentPage(),
                'total_pages' => $returnLogs->lastPage(),
                'total' => $returnLogs->total(),
            ]
        ]);
    }
}