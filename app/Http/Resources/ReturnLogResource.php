<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReturnLogResource extends JsonResource
{

        public function __construct($resource)
    {
        parent::__construct($resource);
    }
    public function toArray($request)
    {
        return [
            'return_log_id' => $this->id,
            'borrow_id' => $this->borrow_id,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user ? $this->user->name : '-',
            ],
            'barang' => [
                'id' => $this->barang_id,
                'nama' => $this->nama_barang,
            ],
            'detail_returns' => $this->detailReturns->map(function ($detail) {
                return [
                    'detail_borrow_id' => $detail->detail_borrow_id,
                    'unit_barang_id' => $detail->unit_barang_id,
                    'kode_barang' => $detail->unitBarang ? $detail->unitBarang->kode : '-',
                    'kerusakan' => $detail->kerusakan ?? '-',
                ];
            }),
            'tanggal_pinjam' => $this->tanggal_pinjam ? \Carbon\Carbon::parse($this->tanggal_pinjam)->toIso8601String() : null,
            'tanggal_kembali' => $this->tanggal_kembali ? \Carbon\Carbon::parse($this->tanggal_kembali)->toIso8601String() : null,
            'terlambat' => $this->terlambat > 0 ? $this->terlambat . ' jam' : 'Tidak',
            'status' => $this->status->value, // Ambil string dari enum
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
