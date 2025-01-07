<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'user_location_id',
        'total_price',
        'shipping_price',
        'payment_date',
        'status',
        'payment_method',
        'payment_status',
        'rating',
        'note'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'payment_date' => 'datetime',
        'rating' => 'integer'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userLocation()
    {
        return $this->belongsTo(UserLocation::class);
    }

    public function orderItems()
    {
        return $this->hasOneThrough(
            OrderItem::class,
            Order::class,
            'id', // Foreign key on orders table
            'order_id', // Foreign key on order_items table
            'order_id', // Local key on transactions table
            'id' // Local key on orders table
        );
    }

    public function getOrderItemsAttribute()
    {
        return $this->order?->orderItems ?? collect();
    }

    public function getItemsByMerchant()
    {
        return $this->order?->getItemsByMerchant() ?? collect();
    }

    public function getMerchantTotals()
    {
        return $this->order?->orderItems
            ->groupBy('merchant_id')
            ->map(function ($items) {
                return $items->sum(function ($item) {
                    return $item->price * $item->quantity;
                });
            }) ?? collect();
    }

    // Metode untuk menghitung biaya pengiriman multi-merchant
    public function calculateShippingPrice()
    {
        $order = $this->order;
        $merchantGroups = $order->getItemsByMerchant();

        $totalShippingPrice = 0;
        foreach ($merchantGroups as $merchantId => $items) {
            $merchant = Merchant::find($merchantId);
            // Misalkan setiap merchant memiliki biaya kirim berbeda
            $totalShippingPrice += $merchant->shipping_price;
        }

        return $totalShippingPrice;
    }

    // Metode untuk memproses transaksi multi-merchant
    public function processMultiMerchantOrder()
    {
        $order = $this->order;
        $merchantGroups = $order->getItemsByMerchant();

        foreach ($merchantGroups as $merchantId => $items) {
            // Proses setiap kelompok item per merchant
            $this->processmerchantItems($merchantId, $items);
        }
    }

    protected function processmerchantItems($merchantId, $items)
    {
        // Logika untuk memproses item per merchant
        // Misalnya: 
        // - Kurangi stok produk
        // - Buat catatan penjualan per merchant
        foreach ($items as $item) {
            $product = $item->product;
            $product->stock -= $item->quantity;
            $product->save();
        }
    }
}
