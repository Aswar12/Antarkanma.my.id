<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    /**
     * Status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_REJECTED = 'REJECTED';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'courier_id',
        'amount',
        'fee',
        'total_amount',
        'status',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'admin_note',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the courier that owns this withdrawal.
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * Get the admin who approved this withdrawal.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending withdrawals.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved withdrawals.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include completed withdrawals.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if withdrawal is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if withdrawal is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if withdrawal is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Approve the withdrawal.
     */
    public function approve(int $adminId): bool
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Mark withdrawal as processing.
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Mark withdrawal as completed.
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);
    }

    /**
     * Reject the withdrawal.
     */
    public function reject(int $adminId, string $note = null): bool
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_note' => $note,
        ]);

        // Refund to courier wallet
        $this->courier->wallet_balance += ($this->amount + $this->fee);
        $this->courier->save();

        return true;
    }
}
