<?php
namespace App\Exports;

use App\Models\ReturnLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReturnReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $returnLogs;

    public function __construct($returnLogs)
    {
        $this->returnLogs = $returnLogs;
    }

    public function collection()
    {
        return $this->returnLogs;
    }

    public function headings(): array
    {
        return [
            'Nama User',
            'Nama Barang',
            'Kode Barang',
            'Tgl Pinjam',
            'Tgl Kembali',
            'Terlambat',
            'Status',
        ];
    }

    public function map($log): array
    {
        return [
            $log->user->name ?? '-',
            $log->nama_barang ?? '-',
            $log->unitBarang->kode_barang ?? '-',
            \Carbon\Carbon::parse($log->tanggal_pinjam)->format('d-m-Y'),
            \Carbon\Carbon::parse($log->tanggal_kembali)->format('d-m-Y'),
            $log->terlambat > 0 ? 'Terlambat ' . $log->terlambat . ' hari' : 'Tidak Terlambat',
            ucfirst($log->status),
        ];
    }
}