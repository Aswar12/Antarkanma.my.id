<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MerchantController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserLocationController;
use App\Http\Controllers\API\OrderController;

// Grup rute untuk pengguna dengan middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk logout pengguna
    Route::post('/logout', [UserController::class, 'logout']);

    // Rute untuk memperbarui profil pengguna
    Route::put('/user/profile', [UserController::class, 'profileUpdate']);

    // Rute untuk mengambil data profil pengguna
    Route::get('/user/profile', [UserController::class, 'fetch']);

    // Rute untuk memperbarui foto profil pengguna
    Route::post('/user/profile/photo', [UserController::class, 'updatePhoto']);
    Route::post('/merchant', [MerchantController::class, 'create']);
    Route::get('/merchant/{id}', [MerchantController::class, 'get']);
    Route::put('/merchant/{id}', [MerchantController::class, 'update']);
    Route::delete('/merchant/{id}', [MerchantController::class, 'delete']);
    Route::get('/merchant/list', [MerchantController::class, 'list']);


    Route::post('/product', [ProductController::class, 'create']);

    Route::post('products', [ProductController::class, 'create']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    // Product Gallery routes
    Route::post('products/{id}/gallery', [ProductController::class, 'addGallery']);
    Route::delete('galleries/{id}', [ProductController::class, 'deleteGallery']);

    // Product Variant routes
    Route::post('products/{productId}/variants', [ProductController::class, 'addVariant']);
    Route::put('variants/{variantId}', [ProductController::class, 'updateVariant']);
    Route::delete('variants/{variantId}', [ProductController::class, 'deleteVariant']);
    Route::get('products/{productId}/variants', [ProductController::class, 'getProductVariants']);
    Route::get('variants/{variantId}', [ProductController::class, 'getVariant']);

    // Additional Product routes
    Route::get('merchants/{merchantId}/products', [ProductController::class, 'getByMerchant']);
    Route::get('categories/{categoryId}/products', [ProductController::class, 'getByCategory']);
    Route::get('products/search', [ProductController::class, 'search']);


    Route::post('/product-category', [ProductCategoryController::class, 'create']);
    Route::get('/product-category/{id}', [ProductCategoryController::class, 'get']);
    Route::put('/product-category/{id}', [ProductCategoryController::class, 'update']);
    Route::delete('/product-category/{id}', [ProductCategoryController::class, 'delete']);
    Route::get('/product-categories', [ProductCategoryController::class, 'list']);

    Route::get('product-reviews', [ProductReviewController::class, 'index']);
    Route::post('product-reviews', [ProductReviewController::class, 'store']);
    Route::put('product-reviews/{id}', [ProductReviewController::class, 'update']);
    Route::delete('product-reviews/{id}', [ProductReviewController::class, 'destroy']);

    Route::post('/orders', [OrderController::class, 'createOrder']);

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
    Route::get('orders/statistics', [OrderController::class, 'getOrderStatistics']);
    Route::get('/couriers', [CourierController::class, 'index']);
    Route::post('/couriers', [CourierController::class, 'store']);
    Route::get('/couriers/{id}', [CourierController::class, 'show']);
    Route::put('/couriers/{id}', [CourierController::class, 'update']);
    Route::delete('/couriers/{id}', [CourierController::class, 'destroy']);

    Route::post('/transactions', [TransactionController::class, 'create']);

    // Rute untuk mengambil transaksi berdasarkan ID
    Route::get('/transactions/{id}', [TransactionController::class, 'get']);

    // Rute untuk mengambil daftar transaksi
    Route::get('/transactions', [TransactionController::class, 'list']);

    // Rute untuk memperbarui transaksi
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);

    // Rute untuk membatalkan transaksi
    Route::post('/transactions/{id}/cancel', [TransactionController::class, 'cancel']);

    // Rute untuk mengambil transaksi berdasarkan merchant
    Route::get('/merchants/{merchantId}/transactions', [TransactionController::class, 'getByMerchant']);


    // Tambahkan rute-rute berikut untuk UserLocation
    Route::get('/user-locations', [UserLocationController::class, 'index']);
    Route::post('/user-locations', [UserLocationController::class, 'store']);
    Route::get('/user-locations/{id}', [UserLocationController::class, 'show']);
    Route::put('/user-locations/{id}', [UserLocationController::class, 'update']);
    Route::delete('/user-locations/{id}', [UserLocationController::class, 'destroy']);
    Route::post('/user-locations/{id}/set-default', [UserLocationController::class, 'setDefault']);
});

// Rute untuk registrasi pengguna
Route::post('register', [UserController::class, 'register']);
Route::get('products', [ProductController::class, 'all']);
// Rute untuk login pengguna
Route::post('/login', [UserController::class, 'login']);
