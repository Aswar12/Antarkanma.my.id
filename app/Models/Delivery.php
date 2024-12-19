<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    /** @use HasFactory<\Database\Factories\DeliveryFactory> */
    use HasFactory;
    
    protected $fillable = [
        'transaction_id',
        'courier_id',
        'tracking_number',
        'delivery_status',
        'estimated_delivery_time',
        'actual_delivery_time'
    ];

    protected $casts = [
        'estimated_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }
}
