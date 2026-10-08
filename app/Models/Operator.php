<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Operator extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'username', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
