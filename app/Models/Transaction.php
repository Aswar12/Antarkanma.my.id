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

    // Relasi untuk mendukung multi-merchant
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userLocation()

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }
    {
        return $this->belongsTo(UserLocation::class);
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

    // Accessor untuk formatted total price
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2, ',', '.');
    }

    // Accessor untuk formatted shipping price
    public function getFormattedShippingPriceAttribute()
    {
        return number_format($this->shipping_price, 2, ',', '.');
    }

    // Scope untuk transaksi dengan status tertentu
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk transaksi dengan payment status tertentu
    public function scopeWithPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    // Scope untuk transaksi dalam rentang tanggal tertentu
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Menyelesaikan method processMultiMerchantOrder
    public function processMultiMerchantOrder()
    {
        $order = $this->order;
        $merchantGroups = $order->getItemsByMerchant();

        foreach ($merchantGroups as $merchantId => $items) {
            $merchant = Merchant::find($merchantId);
            $subTotal = 0;

            foreach ($items as $item) {
                $subTotal += $item->total_price;
                $item->reduceProductStock();
            }

            // Buat sub-transaksi untuk setiap merchant
            $subTransaction = new Transaction([
                'order_id' => $order->id,
                'user_id' => $this->user_id,
                'user_location_id' => $this->user_location_id,
                'total_price' => $subTotal,
                'shipping_price' => $merchant->shipping_price,
                'status' => 'processing',
                'payment_method' => $this->payment_method,
                'payment_status' => $this->payment_status,
            ]);

            $subTransaction->save();
        }

        // Update status transaksi utama
        $this->status = 'processed';
        $this->save();
    }
}
