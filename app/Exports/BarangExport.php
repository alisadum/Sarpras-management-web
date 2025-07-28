<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BarangExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Barang::with('kategori')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Tipe',
            'Kategori',
            'Stok',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $item->nama,
            $item->tipe,
            $item->kategori->nama_kategori,
            $item->stok,
        ];
    }
}