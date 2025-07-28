<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;


class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $guard = 'admin';

    protected $fillable =
    ['name', 'email', 'password', 'photo'];

    protected $hidden = ['password'];
}
