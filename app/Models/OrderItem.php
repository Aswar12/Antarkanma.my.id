<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'merchant_id',
        'quantity',
        'price',
        'customer_note'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    // Relasi untuk mendukung multi-merchant
    public function order()
    {
        return $this->belongsTo(Order::class)->withDefault();
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class)->withDefault();
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withDefault();
    }

    // Accessor untuk total harga item
    public function getTotalPriceAttribute()
    {
        return ($this->quantity ?? 0) * ($this->price ?? 0);
    }

    protected static function booted()
    {
        static::saving(function ($orderItem) {
            if (!$orderItem->merchant_id && $orderItem->product) {
                $orderItem->merchant_id = $orderItem->product->merchant_id;
            }
        });
    }
}
