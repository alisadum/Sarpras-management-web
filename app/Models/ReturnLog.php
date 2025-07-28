<?php

namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturnLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_id',
        'user_id',
        'barang_id',
        'nama_barang',
        'tanggal_pinjam',
        'tanggal_kembali',
        'terlambat',
        'status',
        'alasan_reject',
        'tanggal_approve'
    ];

    protected $casts = [
        'tanggal_pinjam' => 'datetime',
        'tanggal_kembali' => 'datetime',
        'status' => ReturnStatus::class,
    ];

    /**
     * Relationship with Borrow model.
     */
    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }

    /**
     * Relationship with User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Barang model.
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Relationship with DetailReturn model.
     */
    public function detailReturns()
    {
        return $this->hasMany(DetailReturn::class, 'return_log_id');
    }

    /**
     * Relationship with UnitBarang through DetailReturn.
     */
    public function unitBarang()
    {
        return $this->hasOneThrough(
            UnitBarang::class,
            DetailReturn::class,
            'return_log_id', // Foreign key on DetailReturn
            'id', // Foreign key on UnitBarang
            'id', // Local key on ReturnLog
            'unit_barang_id' // Local key on DetailReturn
        );
    }
}