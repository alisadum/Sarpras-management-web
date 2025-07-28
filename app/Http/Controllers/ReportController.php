<?php
namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\ReturnLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BorrowReportExport;
use App\Exports\ReturnReportExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function borrowReport(Request $request)
    {
        $query = Borrow::with(['user', 'barang', 'details.unitBarang'])
            ->select(['id', 'user_id', 'barang_id', 'tanggal_pinjam', 'tanggal_kembali', 'status']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('tanggal_pinjam', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('tanggal_pinjam', '<=', $request->end_date);
        }

        $borrows = $query->paginate(10);
        Log::info("Mengambil laporan peminjaman, Total: {$borrows->total()}");

        if ($request->has('export') && $request->export === 'excel') {
            return Excel::download(new BorrowReportExport($query->get()), 'laporan_peminjaman.xlsx');
        }
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.borrow_pdf', ['borrows' => $query->get()]);
            return $pdf->download('laporan_peminjaman.pdf');
        }

        return view('reports.borrows', compact('borrows'));
    }

    public function returnReport(Request $request)
    {
        $query = ReturnLog::with(['user', 'barang', 'detailReturns.unitBarang'])
            ->select(['id', 'user_id', 'nama_barang', 'tanggal_pinjam', 'tanggal_kembali', 'status', 'terlambat']);

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

        $returnLogs = $query->paginate(10);
        Log::info("Mengambil laporan pengembalian, Total: {$returnLogs->total()}");

        if ($request->has('export') && $request->export === 'excel') {
            return Excel::download(new ReturnReportExport($query->get()), 'laporan_pengembalian.xlsx');
        }
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.return_pdf', ['returnLogs' => $query->get()]);
            return $pdf->download('laporan_pengembalian.pdf');
        }

        return view('reports.returns', compact('returnLogs'));
    }
}
