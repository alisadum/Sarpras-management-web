<?php
namespace App\Exports;

use App\Models\Borrow;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BorrowReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $borrows;

    public function __construct($borrows)
    {
        $this->borrows = $borrows;
    }

    public function collection()
    {
        return $this->borrows;
    }

    public function headings(): array
    {
        return [
            'Nama User',
            'Nama Barang',
            'Kode Barang',
            'Tgl Pinjam',
            'Tgl Kembali',
            'Status',
        ];
    }

    public function map($borrow): array
    {
        $kodeBarang = $borrow->details->map(fn($detail) => $detail->unitBarang->kode_barang ?? '-')->implode(', ');
        return [
            $borrow->user->name ?? '-',
            $borrow->barang->nama ?? '-',
            $kodeBarang,
            \Carbon\Carbon::parse($borrow->tanggal_pinjam)->format('d-m-Y'),
            \Carbon\Carbon::parse($borrow->tanggal_kembali)->format('d-m-Y'),
            ucfirst($borrow->status),
        ];
    }
}