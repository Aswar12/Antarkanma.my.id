<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceFeeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_fee',
        'is_active',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'service_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the current active service fee
     */
    public static function getCurrentServiceFee(): float
    {
        $setting = static::where('is_active', true)
            ->latest('id')
            ->first();

        return $setting ? (float) $setting->service_fee : 500.00;
    }

    /**
     * Check if service fee is active
     */
    public static function isServiceFeeActive(): bool
    {
        return static::where('is_active', true)->exists();
    }

    /**
     * Update service fee with audit trail
     */
    public static function updateServiceFee(float $amount, ?string $updatedBy = null, ?string $notes = null): self
    {
        // Deactivate previous settings
        static::where('is_active', true)->update(['is_active' => false]);

        // Create new setting
        return static::create([
            'service_fee' => $amount,
            'is_active' => true,
            'updated_by' => $updatedBy,
            'notes' => $notes,
        ]);
    }
}
