<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'merchant_id',
        'total_amount',
        'order_status',
        'merchant_approval'
    ];

    protected $casts = [
        'order_status' => 'string',
        'merchant_approval' => 'string',
        'total_amount' => 'decimal:2'
    ];

    // Status Constants
    const STATUS_PENDING = 'PENDING';
    const STATUS_WAITING_APPROVAL = 'WAITING_APPROVAL';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_READY = 'READY_FOR_PICKUP';
    const STATUS_PICKED_UP = 'PICKED_UP';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELED = 'CANCELED';

    // Merchant Approval Constants
    const MERCHANT_PENDING = 'PENDING';
    const MERCHANT_APPROVED = 'APPROVED';
    const MERCHANT_REJECTED = 'REJECTED';

    protected $with = ['orderItems.product.merchant'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class)->withDefault();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            if (!$order->order_status) {
                $order->order_status = self::STATUS_PENDING;
            }
            if (!$order->merchant_approval) {
                $order->merchant_approval = self::MERCHANT_PENDING;
            }
        });

        static::saving(function ($order) {
            if ($order->orderItems()->exists()) {
                $order->total_amount = $order->calculateTotalAmount();
            }
        });
    }

    public function calculateTotalAmount()
    {
        return $this->orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    // Helper methods for status checks
    public function isPending()
    {
        return $this->order_status === self::STATUS_PENDING;
    }

    public function isWaitingApproval()
    {
        return $this->order_status === self::STATUS_WAITING_APPROVAL;
    }

    public function isProcessing()
    {
        return $this->order_status === self::STATUS_PROCESSING;
    }

    public function isReady()
    {
        return $this->order_status === self::STATUS_READY;
    }

    public function isPickedUp()
    {
        return $this->order_status === self::STATUS_PICKED_UP;
    }

    public function isCompleted()
    {
        return $this->order_status === self::STATUS_COMPLETED;
    }

    public function isCanceled()
    {
        return $this->order_status === self::STATUS_CANCELED;
    }

    public function needsMerchantApproval()
    {
        return $this->merchant_approval === self::MERCHANT_PENDING &&
               $this->order_status === self::STATUS_WAITING_APPROVAL;
    }
}
