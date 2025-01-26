<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierBatch extends Model
{
    /** @use HasFactory<\Database\Factories\CourierBatchFactory> */
    use HasFactory;
    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
