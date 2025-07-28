<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'notifications';
    protected $fillable = ['user_id', 'borrow_id', 'message', 'tanggal_notif', 'is_read'];
    protected $dates = ['tanggal_notif', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function borrow()
    {
        return $this->belongsTo(Borrow::class, 'borrow_id');
    }
}
