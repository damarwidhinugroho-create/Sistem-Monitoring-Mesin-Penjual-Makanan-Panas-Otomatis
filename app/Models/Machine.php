<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $fillable = ['machine_code', 'name', 'location', 'status', 'temperature'];
}
