<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBorrow extends Model
{
 protected $table = 'detail_borrows';
 protected $fillable = ['borrow_id','barang_id','jumlah', 'unit_barang_id', 'status'];

 public function borrow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
 {
 return $this->belongsTo(Borrow::class);
 }

 public function unitBarang(): \Illuminate\Database\Eloquent\Relations\BelongsTo
 {
 return $this->belongsTo(UnitBarang::class, 'unit_barang_id');
 }

 public function returnLog(): \Illuminate\Database\Eloquent\Relations\HasOne
 {
 return $this->hasOne(ReturnLog::class, 'detail_borrow_id');
 }
}
