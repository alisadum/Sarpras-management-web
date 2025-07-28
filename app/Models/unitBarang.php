<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitBarang extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'kode_barang',
        'kondisi',
        'status',
        'lokasi',
        'stok',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function detailBorrows()
    {
        return $this->hasMany(DetailBorrow::class, 'unit_barang_id');
    }
}
