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
        'order_id',
        'total_price',
        'merchant_amount',
        'platform_amount',
        'grand_total',
        'shipping_price',
        'base_shipping_price',
        'service_fee',
        'platform_fee',
        'courier_earning',
        'payment_date',
        'merchant_paid_at',
        'platform_paid_at',
        'paid_at',
        'status',
        'payment_method',
        'payment_status',
        'courier_payout_status',
        'courier_paid_at',
        'courier_approval',
        'courier_status',
        'timeout_at',
        'rating',
        'note',
        'rejection_reason',
        'merchant_qris_url',
        'platform_qris_url',
        'merchant_payment_proof',
        'platform_payment_proof',
        'merchant_payment_verified',
        'platform_payment_verified',
        'delivery_proof_image',
        'delivery_proof_at',
        'requires_manual_review',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'merchant_amount' => 'decimal:2',
        'platform_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'base_shipping_price' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'courier_earning' => 'decimal:2',
        'payment_date' => 'datetime',
        'merchant_paid_at' => 'datetime',
        'platform_paid_at' => 'datetime',
        'paid_at' => 'datetime',
        'timeout_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'rating' => 'integer',
        'merchant_payment_verified' => 'boolean',
        'platform_payment_verified' => 'boolean',
        'delivery_proof_at' => 'datetime',
        'requires_manual_review' => 'boolean',
    ];

    protected $dates = [
        'payment_date',
        'merchant_paid_at',
        'platform_paid_at',
        'paid_at',
        'timeout_at',
        'created_at',
        'updated_at',
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
    const PAYMENT_COD = 'COD';
    const PAYMENT_QRIS_DUAL = 'QRIS_DUAL';
    const PAYMENT_QRIS_PLATFORM = 'QRIS_PLATFORM';

    // Payment Status Constants
    const PAYMENT_STATUS_PENDING = 'PENDING';
    const PAYMENT_STATUS_PARTIAL_PAID = 'PARTIAL_PAID';
    const PAYMENT_STATUS_PAID = 'PAID';
    const PAYMENT_STATUS_PENDING_VERIFICATION = 'PENDING_VERIFICATION'; // NEW
    const PAYMENT_STATUS_COMPLETED = 'COMPLETED';
    const PAYMENT_STATUS_FAILED = 'FAILED';

    // Courier Payout Status Constants
    const COURIER_PAYOUT_PENDING = 'PENDING';
    const COURIER_PAYOUT_CREDITED = 'CREDITED';
    const COURIER_PAYOUT_WITHDRAWN = 'WITHDRAWN';
    const COURIER_PAYOUT_FAILED = 'FAILED';

    // Courier Approval Constants
    const COURIER_PENDING = 'PENDING';
    const COURIER_APPROVED = 'APPROVED';
    const COURIER_REJECTED = 'REJECTED';

    // Courier Status Constants (tracking posisi kurir)
    const COURIER_STATUS_IDLE = 'IDLE';
    const COURIER_STATUS_HEADING_TO_MERCHANT = 'HEADING_TO_MERCHANT';
    const COURIER_STATUS_AT_MERCHANT = 'AT_MERCHANT';
    const COURIER_STATUS_HEADING_TO_CUSTOMER = 'HEADING_TO_CUSTOMER';
    const COURIER_STATUS_AT_CUSTOMER = 'AT_CUSTOMER';
    const COURIER_STATUS_DELIVERED = 'DELIVERED';

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
        return $this->payment_method === self::PAYMENT_COD;
    }

    public function isOnlinePayment()
    {
        return $this->payment_method === self::PAYMENT_ONLINE;
    }

    public function isQrisDual()
    {
        return $this->payment_method === self::PAYMENT_QRIS_DUAL;
    }

    public function isQrisPlatform()
    {
        return $this->payment_method === self::PAYMENT_QRIS_PLATFORM;
    }

    public function isFullyPaid()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID ||
               $this->payment_status === self::PAYMENT_STATUS_COMPLETED ||
               ($this->payment_method === self::PAYMENT_COD && $this->status === self::STATUS_COMPLETED);
    }

    public function isPartialPaid()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PARTIAL_PAID;
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
        return !$this->orders()
            ->whereNotIn('order_status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELED
            ])
            ->exists();
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

    /**
     * Get courier earning (base_shipping_price - platform_fee).
     * 
     * @return float
     */
    public function getCourierEarningAttribute(): float
    {
        return $this->base_shipping_price - ($this->base_shipping_price * 0.10);
    }

    /**
     * Get platform revenue (service_fee + platform_fee).
     * 
     * @return float
     */
    public function getPlatformRevenueAttribute(): float
    {
        return $this->service_fee + ($this->base_shipping_price * 0.10);
    }
}
