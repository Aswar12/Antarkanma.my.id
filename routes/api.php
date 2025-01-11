<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\FirebaseService;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MerchantController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserLocationController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\DeliveryController;
use App\Http\Controllers\API\CourierController;
use App\Http\Controllers\API\ProductReviewController;
use App\Http\Controllers\API\FcmController;

// Public Product Review Routes
Route::get('products/{productId}/reviews', [ProductReviewController::class, 'getByProduct']);

// Grup rute untuk pengguna dengan middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // FCM Routes
    Route::post('/fcm/token', [FcmController::class, 'updateToken']);
    Route::delete('/fcm/token', [FcmController::class, 'removeToken']);
    Route::post('/fcm/topic/subscribe', [FcmController::class, 'subscribeTopic']);

    // Product Review Routes
    Route::post('reviews', [ProductReviewController::class, 'store']);
    Route::put('reviews/{id}', [ProductReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ProductReviewController::class, 'destroy']);
    Route::get('user/reviews', [ProductReviewController::class, 'getUserReviews']);

    // Rute untuk logout pengguna
    Route::post('/logout', [UserController::class, 'logout']);

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
    Route::post('products/{id}/gallery', [ProductController::class, 'addGallery']);
    Route::put('products/{productId}/gallery/{galleryId}', [ProductController::class, 'editGallery']);
    Route::delete('products/{productId}/gallery/{galleryId}', [ProductController::class, 'deleteGallery']);
    Route::delete('galleries/{id}', [ProductController::class, 'deleteGallery']);

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
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('merchant/orders', [OrderController::class, 'getMerchantOrders']);
    Route::get('merchant/orders/summary', [OrderController::class, 'getMerchantOrdersSummary']);
    Route::get('orders/statistics', [OrderController::class, 'getOrderStatistics']);

    Route::get('/couriers', [CourierController::class, 'index']);
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

// Test Firebase notification route
Route::post('/test-notification', function (Request $request) {
    $firebaseService = new FirebaseService();
    
    try {
        $result = $firebaseService->sendToUser(
            $request->token,
            ['action' => 'test'],
            'Test Notification',
            'This is a test notification from Antarkanma'
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully',
            'result' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send notification: ' . $e->getMessage()
        ], 500);
    }
});


// Public routes
Route::post('register', [UserController::class, 'register']);
Route::get('products', [ProductController::class, 'all']);
Route::get('products/category/{categoryId}', [ProductController::class, 'getByCategory']);
Route::post('/login', [UserController::class, 'login']);

// Public Product routes
Route::get('products/popular', [ProductController::class, 'getPopularProducts']);
Route::get('products/top-by-category', [ProductController::class, 'getTopProductsByCategory']);
Route::get('products/{id}/with-reviews', [ProductController::class, 'getProductWithReviews']);
