<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $fillable = ['code', 'product_id', 'stock', 'capacity', 'last_filled_at'];
}
