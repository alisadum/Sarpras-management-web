<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KategoriBarang;
use App\Models\UnitBarang;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barangs';

    // Pastikan semua field yang bisa diisi ada di sini
   protected $fillable = [
    'nama',
    'kategori_id',
    'tipe',
    'stok',
    'foto',
];


public function kategori()
{
    return $this->belongsTo(KategoriBarang::class, 'kategori_id', 'id');
}

    public function unitBarangs()
    {
        return $this->hasMany(UnitBarang::class, 'barang_id');
    }
}
