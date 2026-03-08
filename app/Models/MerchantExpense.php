<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'category',
        'amount',
        'description',
        'expense_date',
        'receipt_image',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('expense_date', '>=', $from);
        }
        if ($to) {
            $query->where('expense_date', '<=', $to);
        }
        return $query;
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get formatted category name
     */
    public function getCategoryDisplayAttribute(): string
    {
        return match ($this->category) {
            'BAHAN_BAKU' => 'Bahan Baku',
            'OPERASIONAL' => 'Operasional',
            'GAJI' => 'Gaji',
            'SEWA' => 'Sewa',
            'UTILITAS' => 'Utilitas',
            'LAINNYA' => 'Lainnya',
            default => $this->category,
        };
    }
}
