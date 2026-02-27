# API Reference - Antarkanma

Base URL: `/api`
Authentication: Bearer Token (Laravel Sanctum)

---

## Public Endpoints (Tanpa Auth)

### Authentication
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/register` | `UserController@register` | Registrasi user baru |
| POST | `/register/merchant` | `MerchantController@register` | Registrasi merchant |
| POST | `/login` | `UserController@login` | Login user |

### Products
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/products` | `ProductController@index` | Daftar semua produk |
| GET | `/products/popular` | `ProductController@getPopularProducts` | Produk populer |
| GET | `/products/top-by-category` | `ProductController@getTopProductsByCategory` | Top produk per kategori |
| GET | `/products/category/{categoryId}` | `ProductController@getByCategory` | Produk per kategori |
| GET | `/products/{id}/with-reviews` | `ProductController@getProductWithReviews` | Produk + review |
| GET | `/merchants/{merchantId}/products` | `ProductController@getProductByMerchant` | Produk per merchant |

### Categories
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/categories` | `ProductCategoryController@list` | Daftar kategori |
| GET | `/categories/{id}` | `ProductCategoryController@get` | Detail kategori |

### Merchants
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/merchants` | `MerchantController@index` | Daftar merchant |
| GET | `/merchants/{id}` | `MerchantController@show` | Detail merchant + produk |

### Health
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/health` | Status server, database, Redis |

---

## Authenticated Endpoints (Require Bearer Token)

### User Profile
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/user/profile` | `UserController@fetch` | Ambil profil |
| PUT | `/user/profile` | `UserController@profileUpdate` | Update profil |
| POST | `/user/profile/photo` | `UserController@updatePhoto` | Update foto profil |
| POST | `/user/toggle-active` | `UserController@toggleActive` | Toggle status aktif |
| POST | `/logout` | `UserController@logout` | Logout |
| POST | `/refresh` | `UserController@refresh` | Refresh token |

### Merchant Management
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/merchant` | `MerchantController@store` | Buat merchant |
| PUT | `/merchant/{id}` | `MerchantController@update` | Update merchant |
| POST | `/merchant/{id}/logo` | `MerchantController@updateLogo` | Update logo |
| DELETE | `/merchant/{id}` | `MerchantController@delete` | Hapus merchant |
| GET | `/merchant/list` | `MerchantController@list` | List merchant (owner) |
| GET | `/merchants/owner/{id}` | `MerchantController@getByOwnerId` | Merchant by owner |

### Product CRUD
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/products` | `ProductController@create` | Buat produk |
| PUT | `/products/{id}` | `ProductController@update` | Update produk |
| DELETE | `/products/{id}` | `ProductController@destroy` | Hapus produk |
| GET | `/products/search` | `ProductController@search` | Cari produk |
| GET | `/merchants/{merchantId}/products` | `ProductController@getProductByMerchant` | Produk per merchant |

### Product Gallery
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/products/{id}/gallery` | `ProductGalleryController@addGallery` | Tambah gambar |
| PUT | `/products/{productId}/gallery/{galleryId}` | `ProductGalleryController@editGallery` | Edit gambar |
| DELETE | `/products/{productId}/gallery/{galleryId}` | `ProductGalleryController@deleteGallery` | Hapus gambar |

### Product Variants
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/products/{productId}/variants` | `ProductController@addVariant` | Tambah varian |
| PUT | `/variants/{variantId}` | `ProductController@updateVariant` | Update varian |
| DELETE | `/variants/{variantId}` | `ProductController@deleteVariant` | Hapus varian |
| GET | `/products/{productId}/variants` | `ProductController@getProductVariants` | List varian |
| GET | `/variants/{variantId}` | `ProductController@getVariant` | Detail varian |

### Product Categories
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/product-category` | `ProductCategoryController@create` | Buat kategori |
| GET | `/product-category/{id}` | `ProductCategoryController@get` | Detail kategori |
| PUT | `/product-category/{id}` | `ProductCategoryController@update` | Update kategori |
| DELETE | `/product-category/{id}` | `ProductCategoryController@delete` | Hapus kategori |
| GET | `/product-categories` | `ProductCategoryController@list` | List kategori |

### Product Reviews
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/reviews` | `ProductReviewController@store` | Buat review |
| PUT | `/reviews/{id}` | `ProductReviewController@update` | Update review |
| DELETE | `/reviews/{id}` | `ProductReviewController@destroy` | Hapus review |
| GET | `/user/reviews` | `ProductReviewController@getUserReviews` | Review user |

### Orders
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/orders` | `OrderController@create` | Buat order |
| GET | `/orders` | `OrderController@list` | List order user |
| GET | `/orders/{id}` | `OrderController@get` | Detail order |
| GET | `/orders/statistics` | `OrderController@getOrderStatistics` | Statistik order |

### Order Status
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/orders/{id}/process` | `OrderStatusController@processOrder` | Proses order |
| POST | `/orders/{id}/ready-for-pickup` | `OrderStatusController@readyForPickup` | Siap pickup |
| POST | `/orders/{id}/complete` | `OrderStatusController@complete` | Selesai |
| POST | `/orders/{id}/cancel` | `OrderStatusController@cancel` | Batalkan |

### Merchant Orders
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/merchant/{merchantId}/orders` | `OrderController@getByMerchant` | Orders merchant |
| GET | `/merchants/{merchantId}/orders` | `OrderController@getByMerchant` | Orders merchant |
| GET | `/merchants/{merchantId}/order-summary` | `OrderController@getMerchantOrdersSummary` | Ringkasan |
| GET | `/merchant/orders/summary` | `OrderController@getMerchantOrdersSummary` | Ringkasan |
| PUT | `/merchants/orders/{orderId}/approve` | `OrderController@approveOrder` | Approve |
| PUT | `/merchants/orders/{orderId}/reject` | `OrderController@rejectOrder` | Reject |
| PUT | `/merchants/orders/{orderId}/ready` | `OrderController@markAsReady` | Tandai siap |

### Transactions
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/transactions` | `TransactionController@create` | Buat transaksi |
| GET | `/transactions/{id}` | `TransactionController@get` | Detail transaksi |
| GET | `/transactions` | `TransactionController@list` | List transaksi |
| PUT | `/transactions/{id}` | `TransactionController@update` | Update transaksi |
| PUT | `/transactions/{id}/cancel` | `TransactionController@cancel` | Batalkan |
| GET | `/merchants/{merchantId}/transactions` | `TransactionController@getByMerchant` | Transaksi merchant |
| GET | `/merchants/{merchantId}/transaction-summary` | `TransactionController@getTransactionSummaryByMerchant` | Ringkasan |

### Courier
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/couriers` | `CourierController@store` | Daftar jadi kurir |
| GET | `/couriers/{id}` | `CourierController@show` | Detail kurir |
| PUT | `/couriers/{id}` | `CourierController@update` | Update kurir |
| DELETE | `/couriers/{id}` | `CourierController@destroy` | Hapus kurir |

### Courier Transactions (prefix: `/courier`)
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/courier/new-transactions` | `CourierController@getnewTransactions` | Transaksi baru |
| GET | `/courier/my-transactions` | `CourierController@getCourierTransactions` | Transaksi saya |
| POST | `/courier/transactions/{id}/status` | `CourierController@updateTransactionStatus` | Update status |
| POST | `/courier/transactions/{id}/approve` | `CourierController@approveTransaction` | Terima |
| POST | `/courier/transactions/{id}/reject` | `CourierController@rejectTransaction` | Tolak |
| POST | `/courier/transactions/{id}/pickup` | `CourierController@updateOrderStatus` | Pickup |

### Courier Wallet
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/courier/wallet/topup` | `CourierController@topUpWallet` | Top-up wallet |
| GET | `/courier/wallet/balance` | `CourierController@getWalletBalance` | Cek saldo |

### Courier Statistics
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/courier/statistics/daily` | `CourierController@getDailyStatistics` | Statistik harian |
| GET | `/courier/transactions/status-counts` | `CourierController@getStatusCounts` | Jumlah per status |

### Delivery
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/deliveries/assign-courier` | `DeliveryController@assignCourier` | Assign kurir |
| PUT | `/deliveries/{deliveryId}/status` | `DeliveryController@updateDeliveryStatus` | Update status |
| PUT | `/delivery-items/{deliveryItemId}/pickup-status` | `DeliveryController@updatePickupStatus` | Update pickup |
| GET | `/couriers/{courierId}/deliveries` | `DeliveryController@getCourierDeliveries` | Delivery kurir |

### Shipping
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/shipping/calculate` | `ShippingController@previewCosts` | Hitung ongkir |
| POST | `/shipping/preview` | `ShippingController@previewCosts` | Preview ongkir |

### User Locations
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| GET | `/user-locations` | `UserLocationController@index` | List alamat |
| POST | `/user-locations` | `UserLocationController@store` | Tambah alamat |
| GET | `/user-locations/{id}` | `UserLocationController@show` | Detail alamat |
| PUT | `/user-locations/{id}` | `UserLocationController@update` | Update alamat |
| DELETE | `/user-locations/{id}` | `UserLocationController@destroy` | Hapus alamat |
| POST | `/user-locations/{id}/set-default` | `UserLocationController@setDefault` | Set default |

### Firebase Cloud Messaging (FCM)
| Method | Endpoint | Controller | Deskripsi |
|--------|----------|------------|-----------|
| POST | `/fcm/token` | `FcmController@storeOrUpdateToken` | Simpan FCM token |
| DELETE | `/fcm/token` | `FcmController@removeToken` | Hapus FCM token |
| POST | `/fcm/topic/subscribe` | `FcmController@subscribeTopic` | Subscribe topik |
| POST | `/notifications/test/merchant` | `NotificationController@testMerchantNotification` | Test notif merchant |

---

*Dokumen ini di-generate dari `routes/api.php`. Update dokumen ini jika ada perubahan pada routing.*
