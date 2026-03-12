<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CartSync extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'merchant_id',
        'product_variant_id',
        'quantity',
        'is_selected',
        'last_added_at',
        'last_checkout_attempt_at',
        'has_checked_out',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'is_selected' => 'boolean',
        'has_checked_out' => 'boolean',
        'last_added_at' => 'datetime',
        'last_checkout_attempt_at' => 'datetime',
    ];

    /**
     * Get the user that owns the cart item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product in the cart.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the merchant for the cart item.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the variant of the product.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Scope to get cart items for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get only selected items.
     */
    public function scopeSelected(Builder $query): Builder
    {
        return $query->where('is_selected', true);
    }

    /**
     * Scope to get items that were never checked out (abandoned carts).
     */
    public function scopeAbandoned(Builder $query): Builder
    {
        return $query->where('has_checked_out', false)
            ->whereNotNull('last_checkout_attempt_at');
    }

    /**
     * Scope to group items by merchant.
     */
    public function scopeGroupedByMerchant(Builder $query): Builder
    {
        return $query->select('merchant_id')
            ->selectRaw('COUNT(*) as items_count')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('merchant_id');
    }

    /**
     * Get the total price for this cart item.
     */
    public function getTotalPriceAttribute(): float
    {
        $price = $this->product->price ?? 0;
        return $price * $this->quantity;
    }

    /**
     * Mark cart as checked out.
     */
    public function markAsCheckedOut(): void
    {
        $this->update([
            'has_checked_out' => true,
            'last_checkout_attempt_at' => now(),
        ]);
    }

    /**
     * Reset checkout status (for when user adds again after checkout).
     */
    public function resetCheckoutStatus(): void
    {
        $this->update([
            'has_checked_out' => false,
            'last_checkout_attempt_at' => null,
        ]);
    }
}
