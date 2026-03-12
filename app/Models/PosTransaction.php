<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PosTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'transaction_code',
        'order_type',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'payment_method',
        'subtotal',
        'discount',
        'tax',
        'total',
        'amount_paid',
        'change_amount',
        'notes',
        'status',
        'table_number',
        'delivery_id',
        'created_by',
        'food_completed_at',
        'auto_release_at',
        'table_released_at',
        'released_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'food_completed_at' => 'datetime',
        'auto_release_at' => 'datetime',
        'table_released_at' => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function items()
    {
        return $this->hasMany(PosTransactionItem::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function releasedByUser()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function table()
    {
        return $this->hasOne(MerchantTable::class, 'current_pos_transaction_id');
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                     ->whereYear('created_at', Carbon::now()->year);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('order_type', $type);
    }

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    // ─── Methods ───────────────────────────────────────────────

    /**
     * Generate a unique transaction code: POS-YYYYMMDD-XXXX
     */
    public static function generateTransactionCode(int $merchantId): string
    {
        $date = Carbon::now()->format('Ymd');
        $prefix = "POS-{$date}-";

        $lastTransaction = self::where('merchant_id', $merchantId)
            ->where('transaction_code', 'like', $prefix . '%')
            ->orderBy('transaction_code', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if this transaction can be voided
     */
    public function canBeVoided(): bool
    {
        return $this->status !== 'VOIDED' && $this->delivery_id === null;
    }

    /**
     * Recalculate totals based on items
     */
    public function recalculateTotal(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $this->subtotal = $subtotal;
        $this->total = $subtotal - $this->discount + $this->tax;
        $this->save();
    }

    /**
     * Get formatted order type
     */
    public function getOrderTypeDisplayAttribute(): string
    {
        return match ($this->order_type) {
            'DINE_IN' => 'Makan di Tempat',
            'TAKEAWAY' => 'Bawa Pulang',
            'DELIVERY' => 'Delivery',
            default => $this->order_type,
        };
    }

    /**
     * Get formatted payment method
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match ($this->payment_method) {
            'CASH' => 'Tunai',
            'QRIS' => 'QRIS',
            'TRANSFER' => 'Transfer',
            default => $this->payment_method,
        };
    }

    // ─── Table Management Methods ──────────────────────────

    /**
     * Mark food as completed and schedule auto-release if applicable
     */
    public function markFoodCompleted(): void
    {
        $this->update(['food_completed_at' => Carbon::now()]);

        $merchant = $this->merchant;
        if ($merchant && $merchant->shouldAutoReleaseTable()) {
            $this->scheduleAutoRelease($merchant->default_dine_duration);
        }
    }

    /**
     * Schedule auto-release at food_completed_at + duration minutes
     */
    public function scheduleAutoRelease(int $durationMinutes): void
    {
        $baseTime = $this->food_completed_at ?? Carbon::now();
        $this->update([
            'auto_release_at' => Carbon::parse($baseTime)->addMinutes($durationMinutes),
        ]);
    }

    /**
     * Release the table (manual or auto)
     */
    public function releaseTable(?int $userId = null): void
    {
        $this->update([
            'table_released_at' => Carbon::now(),
            'released_by' => $userId,
        ]);

        // Release the physical table
        $table = MerchantTable::where('current_pos_transaction_id', $this->id)->first();
        if ($table) {
            $table->release();
        }
    }

    /**
     * Check if table is ready for auto-release
     */
    public function isReadyForAutoRelease(): bool
    {
        return $this->auto_release_at
            && Carbon::now()->gte($this->auto_release_at)
            && !$this->table_released_at;
    }

    /**
     * Extend the auto-release time
     */
    public function extendDuration(int $additionalMinutes): void
    {
        if ($this->auto_release_at) {
            $this->update([
                'auto_release_at' => Carbon::parse($this->auto_release_at)
                    ->addMinutes($additionalMinutes),
            ]);
        }
    }
}
