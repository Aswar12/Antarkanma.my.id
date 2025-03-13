<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    /** @use HasFactory<\Database\Factories\CourierFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'vehicle_type',
        'license_plate',
        'wallet_balance',
        'fee_per_order',
        'is_wallet_active',
        'minimum_balance'
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'fee_per_order' => 'decimal:2',
        'is_wallet_active' => 'boolean',
        'minimum_balance' => 'decimal:2'
    ];

    protected $hidden = ['user'];

    protected $appends = ['name', 'full_details'];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user->name ?? 'Unknown Courier';
    }

    public function getFullDetailsAttribute(): string
    {
        return "{$this->name} ({$this->vehicle_type} - {$this->license_plate})";
    }

    /**
     * Deduct fee from courier's wallet balance
     */
    public function deductFee(): bool
    {
        if (!$this->is_wallet_active || $this->wallet_balance < $this->fee_per_order) {
            return false;
        }

        $this->wallet_balance -= $this->fee_per_order;
        return $this->save();
    }

    /**
     * Check if courier has sufficient balance
     */
    public function hasSufficientBalance(): bool
    {
        return $this->is_wallet_active && $this->wallet_balance >= $this->fee_per_order;
    }

    /**
     * Top up courier's wallet balance
     */
    public function topUpWallet(float $amount): bool
    {
        $this->wallet_balance += $amount;
        return $this->save();
    }
}
