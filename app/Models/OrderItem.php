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
        'merchant_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2'
    ];

    // Relasi untuk mendukung multi-merchant
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    // Accessor untuk total harga item
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->price;
    }

    // Metode untuk validasi stok
    public function checkStock()
    {
        $product = $this->product;
        return $this->quantity <= $product->stock;
    }
}

    // Accessor untuk formatted price
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2, ',', '.');
    }

    // Accessor untuk formatted total price
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2, ',', '.');
    }

    // Method untuk mengurangi stok produk
    public function reduceProductStock()
    {
        $product = $this->product;
        $product->stock -= $this->quantity;
        $product->save();
    }

    // Scope untuk item dengan quantity lebih dari X
    public function scopeQuantityMoreThan($query, $quantity)
    {
        return $query->where('quantity', '>', $quantity);
    }

    // Scope untuk item dari merchant tertentu
    public function scopeFromMerchant($query, $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }
}
