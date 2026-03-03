<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class WalletTopup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'courier_id',
        'amount',
        'unique_code',
        'transfer_amount',
        'payment_proof',
        'status',
        'bank_notification_matched',
        'admin_note',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
        'unique_code' => 'integer',
        'bank_notification_matched' => 'boolean',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Constants for status
    const STATUS_PENDING = 'PENDING';
    const STATUS_VERIFIED = 'VERIFIED';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    // Relationships
    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopePending(Builder $query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeVerified(Builder $query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeApproved(Builder $query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForCourier(Builder $query, int $courierId)
    {
        return $query->where('courier_id', $courierId);
    }

    // Methods
    public function approve(int $adminId): bool
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'verified_by' => $adminId,
            'verified_at' => now(),
            'bank_notification_matched' => true,
        ]);

        // Add balance to courier wallet
        $this->courier->topUpWallet($this->amount);

        return true;
    }

    public function reject(int $adminId, string $note): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'verified_by' => $adminId,
            'verified_at' => now(),
            'admin_note' => $note,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }
}
