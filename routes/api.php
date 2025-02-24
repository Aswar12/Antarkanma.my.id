<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\FirebaseService;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MerchantController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserLocationController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\CourierController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\DeliveryController;
use App\Http\Controllers\API\ProductReviewController;
use App\Http\Controllers\API\FcmController;
use App\Http\Controllers\API\OrderStatusController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ProductGalleryController;
use App\Http\Controllers\API\ShippingController;
use App\Http\Controllers\S3TestController;
use App\Http\Controllers\API\NotificationTestController;

// Public Product Review Routes
Route::get('products/{productId}/reviews', [ProductReviewController::class, 'getByProduct']);

// Grup rute untuk pengguna dengan middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // FCM Routes
    Route::post('/fcm/token', [FcmController::class, 'storeOrUpdateToken']);
    Route::delete('/fcm/token', [FcmController::class, 'removeToken']);
    Route::post('/fcm/topic/subscribe', [FcmController::class, 'subscribeTopic']);
    Route::post('/notifications/test/merchant', [NotificationController::class, 'testMerchantNotification']);

    // Product Review Routes
    Route::post('reviews', [ProductReviewController::class, 'store']);
    Route::put('reviews/{id}', [ProductReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ProductReviewController::class, 'destroy']);
    Route::get('user/reviews', [ProductReviewController::class, 'getUserReviews']);

    // Auth routes
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/refresh', [UserController::class, 'refresh']);

    // Rute untuk memperbarui profil pengguna
    Route::put('/user/profile', [UserController::class, 'profileUpdate']);

    // Rute untuk mengambil data profil pengguna
    Route::get('/user/profile', [UserController::class, 'fetch']);

    // Rute untuk memperbarui foto profil pengguna
    Route::post('/user/profile/photo', [UserController::class, 'updatePhoto']);
    Route::post('/merchant', [MerchantController::class, 'store']);

    Route::put('/merchant/{id}', [MerchantController::class, 'update']);
    Route::delete('/merchant/{id}', [MerchantController::class, 'delete']);
    Route::get('/merchant/list', [MerchantController::class, 'list']);
    Route::get('/merchants/owner/{id}', [MerchantController::class, 'getByOwnerId']);
    Route::post('/product', [ProductController::class, 'create']);

    Route::post('products', [ProductController::class, 'create']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    // Product Gallery routes
    Route::post('products/{id}/gallery', [ProductGalleryController::class, 'addGallery']);
    Route::put('products/{productId}/gallery/{galleryId}', [ProductGalleryController::class, 'editGallery']);
    Route::delete('products/{productId}/gallery/{galleryId}', [ProductGalleryController::class, 'deleteGallery']);

    // Product Variant routes
    Route::post('products/{productId}/variants', [ProductController::class, 'addVariant']);
    Route::put('variants/{variantId}', [ProductController::class, 'updateVariant']);
    Route::delete('variants/{variantId}', [ProductController::class, 'deleteVariant']);
    Route::get('products/{productId}/variants', [ProductController::class, 'getProductVariants']);
    Route::get('variants/{variantId}', [ProductController::class, 'getVariant']);

    // Additional Product routes
    Route::get('merchants/{merchantId}/products', [ProductController::class, 'getProductByMerchant']);
    Route::get('products/search', [ProductController::class, 'search']);

    Route::post('/product-category', [ProductCategoryController::class, 'create']);
    Route::get('/product-category/{id}', [ProductCategoryController::class, 'get']);
    Route::put('/product-category/{id}', [ProductCategoryController::class, 'update']);
    Route::delete('/product-category/{id}', [ProductCategoryController::class, 'delete']);
    Route::get('/product-categories', [ProductCategoryController::class, 'list']);

    // Delivery routes
    Route::post('/deliveries/assign-courier', [DeliveryController::class, 'assignCourier']);
    Route::put('/deliveries/{deliveryId}/status', [DeliveryController::class, 'updateDeliveryStatus']);
    Route::put('/delivery-items/{deliveryItemId}/pickup-status', [DeliveryController::class, 'updatePickupStatus']);
    Route::get('/couriers/{courierId}/deliveries', [DeliveryController::class, 'getCourierDeliveries']);

    // Order routes
    Route::post('orders', [OrderController::class, 'create']);
    Route::get('orders', [OrderController::class, 'list']);
    Route::get('orders/{id}', [OrderController::class, 'get']);

    // Order Status routes
    Route::post('orders/{id}/process', [OrderStatusController::class, 'processOrder']);
    Route::post('orders/{id}/ready-for-pickup', [OrderStatusController::class, 'readyForPickup']);
    Route::post('orders/{id}/complete', [OrderStatusController::class, 'complete']);
    Route::post('orders/{id}/cancel', [OrderStatusController::class, 'cancel']);
    Route::get('merchant/{merchantId}/orders', [OrderController::class, 'getByMerchant'])->name('merchant.orders');
    Route::get('merchant/orders/summary', [OrderController::class, 'getMerchantOrdersSummary']);
    Route::get('orders/statistics', [OrderController::class, 'getOrderStatistics']);
    Route::get('merchants/{merchantId}/orders', [OrderController::class, 'getByMerchant']);
    Route::put('merchants/orders/{orderId}/approve', [OrderController::class, 'approveOrder']);
    Route::put('merchants/orders/{orderId}/reject', [OrderController::class, 'rejectOrder']);
    Route::put('merchants/orders/{orderId}/ready', [OrderController::class, 'markAsReady']);

    // Courier Transaction Routes
    Route::prefix('courier')->group(function () {
        // Route::get('transactions', [CourierController::class, 'getTransactions']); // Removed unused route
        Route::get('new-transactions', [CourierController::class, 'getNewTransactions']);
        Route::post('transactions/{id}/status', [CourierController::class, 'updateTransactionStatus']);
        Route::post('transactions/{id}/approve', [CourierController::class, 'approveTransaction']);
        Route::post('transactions/{id}/reject', [CourierController::class, 'rejectTransaction']);
    });

    Route::post('/couriers', [CourierController::class, 'store']);
    Route::get('/couriers/{id}', [CourierController::class, 'show']);
    Route::put('/couriers/{id}', [CourierController::class, 'update']);
    Route::delete('/couriers/{id}', [CourierController::class, 'destroy']);

    Route::post('/transactions', [TransactionController::class, 'create']);
    Route::get('/transactions/{id}', [TransactionController::class, 'get']);
    Route::get('/transactions', [TransactionController::class, 'list']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::put('/transactions/{id}/cancel', [TransactionController::class, 'cancel']);
    Route::get('/merchants/{merchantId}/transaction-summary', [TransactionController::class, 'getTransactionSummaryByMerchant']);
    Route::get('/merchants/{merchantId}/transactions', [TransactionController::class, 'getByMerchant']);

    Route::get('/user-locations', [UserLocationController::class, 'index']);
    Route::post('/user-locations', [UserLocationController::class, 'store']);
    Route::get('/user-locations/{id}', [UserLocationController::class, 'show']);
    Route::put('/user-locations/{id}', [UserLocationController::class, 'update']);
    Route::delete('/user-locations/{id}', [UserLocationController::class, 'destroy']);
    Route::post('/user-locations/{id}/set-default', [UserLocationController::class, 'setDefault']);
});

// Public routes
Route::post('register', [UserController::class, 'register']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/category/{categoryId}', [ProductController::class, 'getByCategory']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/categories', [ProductCategoryController::class, 'list']);
Route::get('/categories/{id}', [ProductCategoryController::class, 'get']);
// Shipping routes
Route::post('/shipping/calculate', [ShippingController::class, 'previewCosts'])->middleware('auth:sanctum'); // For backward compatibility
Route::post('/shipping/preview', [ShippingController::class, 'previewCosts'])->middleware('auth:sanctum'); // New endpoint

// Public Merchant routes
Route::get('merchants', [MerchantController::class, 'index']); // List merchant (with optional distance)
Route::get('merchants/{id}', [MerchantController::class, 'show']); // Detail merchant with products

// Public Product routes
Route::get('products/popular', [ProductController::class, 'getPopularProducts']);
Route::get('products/top-by-category', [ProductController::class, 'getTopProductsByCategory']);
Route::get('products/{id}/with-reviews', [ProductController::class, 'getProductWithReviews']);
Route::get('merchants/{merchantId}/products', [ProductController::class, 'getProductByMerchant']);

// S3 Storage Test Route
Route::post('/test/upload-image', [S3TestController::class, 'uploadImage']);
Route::post('/notifications/test', [NotificationTestController::class, 'sendTestNotification']);
