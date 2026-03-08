<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_transaction_id',
        'product_id',
        'product_variant_id',
        'name',
        'quantity',
        'price',
        'discount',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function posTransaction()
    {
        return $this->belongsTo(PosTransaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // ─── Boot ──────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Auto-calculate subtotal
            $item->subtotal = ($item->price * $item->quantity) - $item->discount;
        });
    }
}
