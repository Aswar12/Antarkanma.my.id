<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_location_id',
        'courier_id',
        'base_merchant_id',
        'total_price',
        'shipping_price',
        'payment_date',
        'status',
        'payment_method',
        'payment_status',
        'courier_approval',
        'timeout_at',
        'rating',
        'note'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'payment_date' => 'datetime',
        'timeout_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'rating' => 'integer'
    ];

    protected $dates = [
        'payment_date',
        'timeout_at',
        'created_at',
        'updated_at'
    ];

    // Remove default eager loading to prevent conflicts
    protected $with = [];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function userLocation()
    {
        return $this->belongsTo(UserLocation::class)->withDefault();
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id')->withDefault();
    }

    public function baseMerchant()
    {
        return $this->belongsTo(Merchant::class, 'base_merchant_id')->withDefault();
    }

    // Add a method to get the courier's details
    public function getCourierDetails()
    {
        return $this->courier()->first();
    }

    public function orderItems()
    {
        return $this->hasManyThrough(
            OrderItem::class,
            Order::class
        );
    }

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (!$transaction->timeout_at) {
                $transaction->timeout_at = now()->addMinutes(5);
            }
            if (!$transaction->courier_approval) {
                $transaction->courier_approval = 'PENDING';
            }
        });
    }

    public function getItemsByMerchant()
    {
        return collect($this->orders)->flatMap(function ($order) {
            return [$order->merchant_id => $order->orderItems];
        });
    }

    public function getMerchantTotals()
    {
        return collect($this->orders)->mapWithKeys(function ($order) {
            return [$order->merchant_id => $order->total_amount];
        });
    }

    // Status Constants
    const STATUS_PENDING = 'PENDING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELED = 'CANCELED';

    // Payment Method Constants
    const PAYMENT_MANUAL = 'MANUAL';
    const PAYMENT_ONLINE = 'ONLINE';

    // Payment Status Constants
    const PAYMENT_STATUS_PENDING = 'PENDING';
    const PAYMENT_STATUS_COMPLETED = 'COMPLETED';
    const PAYMENT_STATUS_FAILED = 'FAILED';

    // Courier Approval Constants
    const COURIER_PENDING = 'PENDING';
    const COURIER_APPROVED = 'APPROVED';
    const COURIER_REJECTED = 'REJECTED';

    // Helper methods for status checks
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCanceled()
    {
        return $this->status === self::STATUS_CANCELED;
    }

    public function isCOD()
    {
        return $this->payment_method === self::PAYMENT_MANUAL;
    }

    public function isOnlinePayment()
    {
        return $this->payment_method === self::PAYMENT_ONLINE;
    }

    public function isTimedOut()
    {
        return $this->timeout_at && now()->gt($this->timeout_at);
    }

    public function needsCourierApproval()
    {
        return $this->courier_approval === self::COURIER_PENDING &&
               !$this->isTimedOut() &&
               $this->status !== self::STATUS_CANCELED;
    }

    public function canBeProcessed()
    {
        return $this->courier_approval === self::COURIER_APPROVED &&
               $this->status !== self::STATUS_CANCELED &&
               (!$this->isOnlinePayment() || $this->payment_status === self::PAYMENT_STATUS_COMPLETED);
    }

    public function approveCourier(): void
    {
        DB::transaction(function () {
            // Update courier approval status
            $this->update(['courier_approval' => self::COURIER_APPROVED]);

            // Update all associated orders to waiting approval status
            $this->orders()->update([
                'order_status' => Order::STATUS_WAITING_APPROVAL
            ]);
        });
    }

    public function rejectCourier(): void
    {
        $this->update(['courier_approval' => self::COURIER_REJECTED]);
    }

    public function hasApprovedOrders()
    {
        return $this->orders()
            ->where('merchant_approval', Order::MERCHANT_APPROVED)
            ->exists();
    }

    public function allOrdersCompleted()
    {
        $allCompleted = !$this->orders()
            ->whereNotIn('order_status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELED
            ])
            ->exists();

        // If all orders are completed, deduct fee from courier's wallet
        if ($allCompleted && $this->courier) {
            $this->courier->deductFee();
        }

        return $allCompleted;
    }

    /**
     * Check if courier has sufficient balance before accepting order
     */
    public function canCourierAccept(): bool
    {
        return $this->courier && $this->courier->hasSufficientBalance();
    }

    public function allOrdersCanceled()
    {
        return !$this->orders()
            ->where('order_status', '!=', Order::STATUS_CANCELED)
            ->exists();
    }

    public function clearShippingCache(): void
    {
        $cacheKey = 'shipping_calculation_' . $this->user_id;
        DB::table('cache')->where('key', $cacheKey)->delete();
        Log::info('Cleared shipping calculation from cache', [
            'transaction_id' => $this->id,
            'user_id' => $this->user_id
        ]);
    }


    public function calculateShippingPrice()
    {
        return collect($this->orders)->sum(function ($order) {
            return $order->merchant->shipping_price ?? 0;
        });
    }
}
