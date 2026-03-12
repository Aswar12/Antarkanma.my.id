<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'table_number',
        'capacity',
        'status',
        'current_pos_transaction_id',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    // ─── Constants ────────────────────────────────────────

    const STATUS_AVAILABLE = 'AVAILABLE';
    const STATUS_OCCUPIED  = 'OCCUPIED';
    const STATUS_RESERVED  = 'RESERVED';

    // ─── Relationships ────────────────────────────────────

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function currentTransaction()
    {
        return $this->belongsTo(PosTransaction::class, 'current_pos_transaction_id');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', self::STATUS_OCCUPIED);
    }

    // ─── Methods ──────────────────────────────────────────

    /**
     * Mark table as occupied by a POS transaction
     */
    public function occupy(int $posTransactionId): void
    {
        $this->update([
            'status' => self::STATUS_OCCUPIED,
            'current_pos_transaction_id' => $posTransactionId,
        ]);
    }

    /**
     * Release table back to available
     */
    public function release(): void
    {
        $this->update([
            'status' => self::STATUS_AVAILABLE,
            'current_pos_transaction_id' => null,
        ]);
    }

    /**
     * Check if table is available
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Get status display label (Bahasa Indonesia)
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'Tersedia',
            self::STATUS_OCCUPIED  => 'Terisi',
            self::STATUS_RESERVED  => 'Dipesan',
            default => $this->status,
        };
    }
}
