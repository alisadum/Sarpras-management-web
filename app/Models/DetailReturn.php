<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailReturn extends Model
{
    protected $fillable = [
        'return_log_id',
        'detail_borrow_id',
        'unit_barang_id',
        'kerusakan',
        'foto_kerusakan',
    ];

    public function returnLog()
    {
        return $this->belongsTo(ReturnLog::class);
    }

    public function detailBorrow()
    {
        return $this->belongsTo(DetailBorrow::class);
    }

    public function unitBarang()
    {
        return $this->belongsTo(UnitBarang::class);
    }
}