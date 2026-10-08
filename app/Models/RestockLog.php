<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockLog extends Model
{
    protected $fillable = ['slot_id', 'product_id', 'quantity_added', 'restocked_at'];
}
