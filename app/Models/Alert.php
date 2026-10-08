<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['machine_id', 'type', 'message', 'status', 'occurred_at'];
}
