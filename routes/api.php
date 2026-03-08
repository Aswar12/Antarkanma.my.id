<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MerchantController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\ProductGalleryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\OrderItemController;
use App\Http\Controllers\API\OrderStatusController;
use App\Http\Controllers\API\CourierController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserLocationController;
use App\Http\Controllers\API\DeliveryController;
use App\Http\Controllers\API\ShippingController;
use App\Http\Controllers\API\FcmController;
use App\Http\Controllers\API\NotificationController;
// S3TestController removed — test routes commented out
use App\Http\Controllers\API\NotificationTestController;
use App\Http\Controllers\API\ProductReviewController;
use App\Http\Controllers\TransactionReviewController;
use App\Http\Controllers\API\WalletTopupController;
use App\Http\Controllers\API\QrisController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\MerchantAnalyticsController;
use App\Http\Controllers\API\CourierAnalyticsController;
use App\Http\Controllers\API\ExportController;
use App\Http\Controllers\API\WishlistController;

Route::get('/health', function () {
    try {
        // Check Database
        DB::connection()->getPdo();

        // Check Redis (DISABLED - Redis extension not installed)
        // Redis::ping();

        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'redis' => 'disabled',
            'server' => gethostname(),
            'is_replica' => env('IS_REPLICA', false),
            'replica_weight' => env('REPLICA_WEIGHT', 0)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ], 503);
    }
});
// Grup rute untuk pengguna dengan middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // FCM Routes
    Route::post('/fcm/token', [FcmController::class, 'storeOrUpdateToken']);
    Route::delete('/fcm/token', [FcmController::class, 'removeToken']);
    Route::post('/fcm/topic/subscribe', [FcmController::class, 'subscribeTopic']);
    
    // Notification Inbox Routes
    Route::get('/notifications', [NotificationController::class, 'getInbox']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification']);
    Route::post('/notifications/test/merchant', [NotificationController::class, 'testMerchantNotification']);

    // Wishlist Routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
    Route::post('/wishlist/check', [WishlistController::class, 'check']);

    // Product Review Routes
    Route::get('products/{id}/reviews', [ProductReviewController::class, 'getByProduct']);
    Route::post('reviews', [ProductReviewController::class, 'store']);
    Route::put('reviews/{id}', [ProductReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ProductReviewController::class, 'destroy']);
    Route::get('user/reviews', [ProductReviewController::class, 'getUserReviews']);

    // Auth routes
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/refresh', [UserController::class, 'refresh']);

    // Mobile App Compatibility Aliases (Frontend uses /auth/* prefix)
    Route::post('/auth/logout', [UserController::class, 'logout']);
    Route::post('/auth/refresh', [UserController::class, 'refresh']);
    Route::get('/auth/user', [UserController::class, 'fetch']);
    Route::put('/auth/change-password', [UserController::class, 'changePassword']);
    Route::delete('/auth/delete-account', [UserController::class, 'deleteAccount']);

    // Rute untuk memperbarui profil pengguna
    Route::put('/user/profile', [UserController::class, 'profileUpdate']);

    // Rute untuk mengambil data profil pengguna
    Route::get('/user/profile', [UserController::class, 'fetch']);

    // Rute untuk memperbarui foto profil pengguna
    Route::post('/user/profile/photo', [UserController::class, 'updatePhoto']);

    // Rute untuk toggle status aktif
    Route::post('/user/toggle-active', [UserController::class, 'toggleActive']);

    Route::post('/merchant', [MerchantController::class, 'store']);

    Route::put('/merchant/{id}', [MerchantController::class, 'update']);
    Route::put('/merchant/{id}/status', [MerchantController::class, 'updateStatus']);
    Route::put('/merchant/{id}/extend', [MerchantController::class, 'extendOperatingHours']);
    Route::put('/merchant/{id}/products/availability', [MerchantController::class, 'updateProductAvailability']);
    Route::post('/merchant/{id}/logo', [MerchantController::class, 'updateLogo']);
    Route::delete('/merchant/{id}', [MerchantController::class, 'delete']);
    Route::get('/merchant/list', [MerchantController::class, 'list']);
    Route::get('/merchants/owner/{id}', [MerchantController::class, 'getByOwnerId']);

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

    // OrderItem routes (nested under orders)
    Route::prefix('orders')->group(function () {
        Route::get('/{id}/items', [OrderItemController::class, 'list']);
        Route::post('/{id}/items', [OrderItemController::class, 'create']);
    });
    Route::prefix('order-items')->group(function () {
        Route::get('/{id}', [OrderItemController::class, 'get']);
        Route::put('/{id}', [OrderItemController::class, 'update']);
        Route::delete('/{id}', [OrderItemController::class, 'delete']);
    });

    // New route for order summary
    Route::get('merchants/{merchantId}/order-summary', [OrderController::class, 'getMerchantOrdersSummary']);

    // Order Status routes
    Route::post('orders/{id}/process', [OrderStatusController::class, 'processOrder']);
    Route::post('orders/{id}/ready-for-pickup', [OrderStatusController::class, 'readyForPickup']);
    Route::post('orders/{id}/complete', [OrderStatusController::class, 'complete']);
    Route::post('orders/{id}/cancel', [OrderStatusController::class, 'cancel']);
    Route::get('merchant/{merchantId}/orders', [OrderController::class, 'getByMerchant'])->name('merchant.orders');
    Route::get('merchants/{merchantId}/orders', [OrderController::class, 'getByMerchant']); // Alias plural untuk Flutter
    Route::get('merchant/orders/summary', [OrderController::class, 'getMerchantOrdersSummary']);
    Route::get('orders/statistics', [OrderController::class, 'getOrderStatistics']);
    Route::put('merchants/orders/{orderId}/approve', [OrderController::class, 'approveOrder']);
    Route::put('merchants/orders/{orderId}/reject', [OrderController::class, 'rejectOrder']);
    Route::put('merchants/orders/{orderId}/ready', [OrderController::class, 'markAsReady']);
    
    // Kitchen Ticket Print route (for merchant online orders)
    Route::get('orders/{orderId}/print-kitchen-ticket', [OrderController::class, 'printKitchenTicket']);

    // Courier Transaction Routes
    Route::prefix('courier')->group(function () {
        Route::get('profile', [CourierController::class, 'getProfile']);
        Route::get('new-transactions', [CourierController::class, 'getNewTransactions']);
        Route::get('my-transactions', [CourierController::class, 'getCourierTransactions']);
        Route::post('transactions/{id}/approve', [CourierController::class, 'approveTransaction']);
        Route::post('transactions/{id}/reject', [CourierController::class, 'rejectTransaction']);

        // Kurir Tracking Routes (posisi real-time)
        Route::post('transactions/{id}/arrive-merchant', [CourierController::class, 'arriveAtMerchant']);
        Route::post('transactions/{id}/arrive-customer', [CourierController::class, 'arriveAtCustomer']);

        // Per-Order Actions
        Route::post('orders/{id}/pickup', [CourierController::class, 'pickupOrder']);
        Route::post('orders/{id}/complete', [CourierController::class, 'completeOrder']);

        // Wallet Routes (New Topup System)
        Route::prefix('wallet')->group(function () {
            Route::get('/balance', [CourierController::class, 'getWalletBalance']);
            Route::post('/withdraw', [CourierController::class, 'withdraw']);
            
            // Topup routes
            Route::post('/topups', [WalletTopupController::class, 'submitTopup']);
            Route::get('/topups', [WalletTopupController::class, 'getTopupHistory']);
            Route::get('/topups/{id}', [WalletTopupController::class, 'getTopupDetail']);
            
            // QRIS routes (Public - no auth required)
            Route::get('/qris', [QrisController::class, 'getQrisCode']);
            Route::get('/qris/download', [QrisController::class, 'downloadQrisCode']);
        });

        // Statistics Routes
        Route::get('statistics/daily', [CourierController::class, 'getDailyStatistics']);
        Route::get('transactions/status-counts', [CourierController::class, 'getStatusCounts']);

        // Courier Analytics Routes
        Route::prefix('analytics')->group(function () {
            Route::get('/earnings', [CourierAnalyticsController::class, 'earnings']);
            Route::get('/performance', [CourierAnalyticsController::class, 'performance']);
        });
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

    // Transaction Review Routes
    Route::post('/transactions/{id}/review', [TransactionReviewController::class, 'submitReview']);
    Route::get('/transactions/{id}/review-status', [TransactionReviewController::class, 'getReviewStatus']);
    Route::get('/merchants/{id}/reviews', [TransactionReviewController::class, 'getMerchantReviews']);
    Route::get('/couriers/{id}/reviews', [TransactionReviewController::class, 'getCourierReviews']);

    Route::get('/user-locations', [UserLocationController::class, 'index']);
    Route::post('/user-locations', [UserLocationController::class, 'store']);
    Route::get('/user-locations/{id}', [UserLocationController::class, 'show']);
    Route::put('/user-locations/{id}', [UserLocationController::class, 'update']);
    Route::delete('/user-locations/{id}', [UserLocationController::class, 'destroy']);
    Route::post('/user-locations/{id}/set-default', [UserLocationController::class, 'setDefault']);

    // Manual Order (Jastip)
    Route::post('/manual-order', [App\Http\Controllers\API\ManualOrderController::class, 'store']);

    // Chat Routes — rate limited to prevent spam
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/chats', [App\Http\Controllers\API\ChatController::class, 'getChatList']);
        Route::post('/chat/initiate', [App\Http\Controllers\API\ChatController::class, 'initiate']);
        Route::get('/chat/{chatId}', [App\Http\Controllers\API\ChatController::class, 'getChatDetail']);
        Route::get('/chat/{chatId}/messages', [App\Http\Controllers\API\ChatController::class, 'getMessages']);
        Route::post('/chat/{chatId}/send', [App\Http\Controllers\API\ChatController::class, 'sendMessage']);
        Route::post('/chat/{chatId}/share-location', [App\Http\Controllers\API\ChatController::class, 'shareLocation']);
        Route::put('/chat/{chatId}/read', [App\Http\Controllers\API\ChatController::class, 'markAsRead']);
        Route::post('/chat/{chatId}/close', [App\Http\Controllers\API\ChatController::class, 'closeChat']);
        Route::delete('/chat/{chatId}', [App\Http\Controllers\API\ChatController::class, 'deleteChat']);
        Route::delete('/chat/{chatId}/messages/{messageId}', [App\Http\Controllers\API\ChatController::class, 'deleteMessage']);
    });

    // Admin Analytics Routes
    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [AnalyticsController::class, 'overview']);
        Route::get('/sales', [AnalyticsController::class, 'sales']);
        Route::get('/top-products', [AnalyticsController::class, 'topProducts']);
        Route::get('/top-merchants', [AnalyticsController::class, 'topMerchants']);
        Route::get('/top-couriers', [AnalyticsController::class, 'topCouriers']);
        Route::get('/peak-hours', [AnalyticsController::class, 'peakHours']);
        Route::get('/revenue', [AnalyticsController::class, 'revenueBreakdown']);
        Route::get('/customers', [AnalyticsController::class, 'customerBehavior']);
    });

    // Export Routes
    Route::prefix('export')->group(function () {
        Route::get('/sales/csv', [ExportController::class, 'salesCsv']);
        Route::get('/sales/pdf', [ExportController::class, 'salesPdf']);
        Route::get('/products/csv', [ExportController::class, 'productsCsv']);
        Route::get('/merchants/csv', [ExportController::class, 'merchantsCsv']);
        Route::get('/couriers/csv', [ExportController::class, 'couriersCsv']);
    });

    // Merchant Analytics Routes
    Route::prefix('merchant/analytics')->group(function () {
        Route::get('/overview', [MerchantAnalyticsController::class, 'overview']);
        Route::get('/sales', [MerchantAnalyticsController::class, 'sales']);
        Route::get('/top-products', [MerchantAnalyticsController::class, 'topProducts']);
        Route::get('/peak-hours', [MerchantAnalyticsController::class, 'peakHours']);
    });

    // POS Routes
    Route::prefix('merchant/pos')->group(function () {
        Route::get('/products', [App\Http\Controllers\API\PosController::class, 'getProducts']);
        Route::post('/transactions', [App\Http\Controllers\API\PosController::class, 'createTransaction']);
        Route::get('/transactions', [App\Http\Controllers\API\PosController::class, 'getTransactions']);
        Route::get('/transactions/{id}', [App\Http\Controllers\API\PosController::class, 'getTransaction']);
        Route::post('/transactions/{id}/void', [App\Http\Controllers\API\PosController::class, 'voidTransaction']);
        Route::get('/daily-summary', [App\Http\Controllers\API\PosController::class, 'getDailySummary']);
    });

    // Merchant Finance Routes
    Route::prefix('merchant/finance')->group(function () {
        Route::get('/overview', [App\Http\Controllers\API\MerchantFinanceController::class, 'getOverview']);
        Route::get('/income', [App\Http\Controllers\API\MerchantFinanceController::class, 'getIncomeBreakdown']);
        Route::get('/expenses', [App\Http\Controllers\API\MerchantFinanceController::class, 'getExpenses']);
        Route::post('/expenses', [App\Http\Controllers\API\MerchantFinanceController::class, 'createExpense']);
        Route::put('/expenses/{id}', [App\Http\Controllers\API\MerchantFinanceController::class, 'updateExpense']);
        Route::delete('/expenses/{id}', [App\Http\Controllers\API\MerchantFinanceController::class, 'deleteExpense']);
    });
});

// Public routes
Route::post('register', [UserController::class, 'register']);
Route::post('register/merchant', [MerchantController::class, 'register']);
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
// Route::post('/test/upload-image', [S3TestController::class, 'uploadImage']);
// Route::post('/notifications/test', [NotificationTestController::class, 'sendTestNotification']);
