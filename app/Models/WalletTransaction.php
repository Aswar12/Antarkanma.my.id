<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    /**
     * Type constants
     */
    const TYPE_TOPUP = 'TOPUP';
    const TYPE_WITHDRAWAL = 'WITHDRAWAL';
    const TYPE_FEE = 'FEE';
    const TYPE_EARNING = 'EARNING';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'courier_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_id',
        'reference_type',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Get the courier that owns this transaction.
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * Get the parent reference model (withdrawal, order, topup, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include income transactions (positive amount).
     */
    public function scopeIncome($query)
    {
        return $query->whereIn('type', [self::TYPE_TOPUP, self::TYPE_EARNING]);
    }

    /**
     * Scope a query to only include deduction transactions (negative amount).
     */
    public function scopeDeduction($query)
    {
        return $query->whereIn('type', [self::TYPE_WITHDRAWAL, self::TYPE_FEE]);
    }

    /**
     * Create a new wallet transaction and update courier balance.
     * 
     * @param int $courierId
     * @param string $type
     * @param float $amount
     * @param string $description
     * @param mixed $reference
     * @return WalletTransaction
     */
    public static function createTransaction(
        int $courierId,
        string $type,
        float $amount,
        string $description,
        $reference = null
    ): WalletTransaction {
        $courier = Courier::findOrFail($courierId);
        $balanceBefore = $courier->wallet_balance;
        $balanceAfter = $balanceBefore + $amount;

        $transaction = new self();
        $transaction->courier_id = $courierId;
        $transaction->type = $type;
        $transaction->amount = $amount;
        $transaction->balance_before = $balanceBefore;
        $transaction->balance_after = $balanceAfter;
        $transaction->description = $description;

        if ($reference) {
            $transaction->reference_id = $reference->id;
            $transaction->reference_type = get_class($reference);
        }

        $transaction->save();

        // Update courier balance
        $courier->wallet_balance = $balanceAfter;
        $courier->save();

        return $transaction;
    }

    /**
     * Check if this is an income transaction.
     */
    public function isIncome(): bool
    {
        return in_array($this->type, [self::TYPE_TOPUP, self::TYPE_EARNING]);
    }

    /**
     * Check if this is a deduction transaction.
     */
    public function isDeduction(): bool
    {
        return in_array($this->type, [self::TYPE_WITHDRAWAL, self::TYPE_FEE]);
    }
}
