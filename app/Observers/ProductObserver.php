<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Clear all product-related caches
     */
    private function clearProductCaches(Product $product): void
    {
        // Clear specific product caches
        Cache::forget("product_with_reviews_{$product->id}");
        
        // Clear category-related caches
        if ($product->category_id) {
            Cache::tags(['products', "category_{$product->category_id}"])->flush();
        }
        
        // Clear merchant-related caches
        if ($product->merchant_id) {
            Cache::tags(['products', "merchant_{$product->merchant_id}"])->flush();
        }

        // Clear general product listing caches
        Cache::tags(['products'])->flush();

        // Clear popular products cache
        Cache::forget('products_popular_*');

        // Clear top products by category cache
        Cache::forget('products_top_by_category_*');
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        // Clear caches
        $this->clearProductCaches($product);

        try {
            // Notify subscribers about new product
            $this->firebase->sendProductUpdate(
                'all_products',
                [
                    'action' => 'created',
                    'product_id' => $product->id,
                    'product' => $product->load(['merchant', 'category', 'galleries'])->toArray()
                ],
                'Produk Baru',
                'Produk baru telah ditambahkan: ' . $product->name
            );
        } catch (\Exception $e) {
            // Log the error but don't let it affect the response
            Log::error('Firebase notification error: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Clear caches
        $this->clearProductCaches($product);

        // Get the changes
        $changes = $product->getDirty();
        
        // Only send notification if relevant fields are changed
        $relevantFields = ['name', 'price', 'stock', 'description', 'is_available'];
        $hasRelevantChanges = !empty(array_intersect(array_keys($changes), $relevantFields));

        if ($hasRelevantChanges) {
            try {
                // Notify subscribers about product update
                $this->firebase->sendProductUpdate(
                    'all_products',
                    [
                        'action' => 'updated',
                        'product_id' => $product->id,
                        'product' => $product->fresh(['merchant', 'category', 'galleries'])->toArray(),
                        'changes' => $changes
                    ],
                    'Produk Diperbarui',
                    'Produk telah diperbarui: ' . $product->name
                );

                // Send to specific category subscribers
                if ($product->category) {
                    $this->firebase->sendProductUpdate(
                        'product_category_' . $product->category_id,
                        [
                            'action' => 'updated',
                            'product_id' => $product->id,
                            'product' => $product->fresh(['merchant', 'category', 'galleries'])->toArray(),
                            'changes' => $changes
                        ],
                        'Update Produk Kategori',
                        'Produk dalam kategori ini telah diperbarui: ' . $product->name
                    );
                }
            } catch (\Exception $e) {
                // Log the error but don't let it affect the response
                Log::error('Firebase notification error: ' . $e->getMessage(), [
                    'product_id' => $product->id,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        // Clear caches
        $this->clearProductCaches($product);

        try {
            // Notify subscribers about product deletion
            $this->firebase->sendProductUpdate(
                'all_products',
                [
                    'action' => 'deleted',
                    'product_id' => $product->id
                ],
                'Produk Dihapus',
                'Produk telah dihapus: ' . $product->name
            );
        } catch (\Exception $e) {
            // Log the error but don't let it affect the response
            Log::error('Firebase notification error: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        // Clear caches
        $this->clearProductCaches($product);

        try {
            // Notify subscribers about product restoration
            $this->firebase->sendProductUpdate(
                'all_products',
                [
                    'action' => 'restored',
                    'product_id' => $product->id,
                    'product' => $product->fresh(['merchant', 'category', 'galleries'])->toArray()
                ],
                'Produk Dipulihkan',
                'Produk telah dipulihkan: ' . $product->name
            );
        } catch (\Exception $e) {
            // Log the error but don't let it affect the response
            Log::error('Firebase notification error: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
