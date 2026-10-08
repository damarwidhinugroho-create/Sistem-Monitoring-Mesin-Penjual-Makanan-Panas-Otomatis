<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemperatureLog extends Model
{
    protected $fillable = ['machine_id', 'temperature', 'recorded_at'];
}
