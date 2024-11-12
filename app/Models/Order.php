<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'order_status'
    ];

    protected $casts = [
        'order_status' => 'string',
        'total_amount' => 'decimal:2'
    ];

    // Relasi untuk mendukung multi-merchant
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    // Metode untuk mendapatkan merchant unik dalam order
    public function getUniqueMerchants()
    {
        return $this->orderItems->pluck('merchant_id')->unique();
    }

    // Metode untuk menghitung total amount
    public function calculateTotalAmount()
    {
        return $this->orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    // Metode untuk mengelompokkan item berdasarkan merchant
    public function getItemsByMerchant()
    {
        return $this->orderItems->groupBy('merchant_id');
    }

    protected static function booted()
    {
        static::saving(function ($order) {
            $order->total_amount = $order->calculateTotalAmount();
        });
    }
}
