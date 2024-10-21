<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'address',
        'total_price',
        'shipping_price',
        'status',
        'payment',
        'payment_status',
        'user_location_id',
        'courier_id',
        'rating',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userLocation()
    {
        return $this->belongsTo(UserLocation::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }
}
