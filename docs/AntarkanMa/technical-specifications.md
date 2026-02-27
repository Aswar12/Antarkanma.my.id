# Spesifikasi Teknis Antarkanma

## 1. Struktur API

Base URL: `/api`
Authentication: Laravel Sanctum (Bearer Token)

### 1.1 Autentikasi (Public)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/register` | Registrasi user baru |
| POST | `/register/merchant` | Registrasi merchant baru |
| POST | `/login` | Login user |

### 1.2 Autentikasi (Authenticated)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/logout` | Logout user |
| POST | `/refresh` | Refresh token |

### 1.3 User Profile
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/user/profile` | Ambil data profil |
| PUT | `/user/profile` | Update profil |
| POST | `/user/profile/photo` | Update foto profil |
| POST | `/user/toggle-active` | Toggle status aktif |

### 1.4 Merchant
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/merchants` | Daftar merchant (public) |
| GET | `/merchants/{id}` | Detail merchant (public) |
| POST | `/merchant` | Buat merchant baru |
| PUT | `/merchant/{id}` | Update merchant |
| POST | `/merchant/{id}/logo` | Update logo |
| DELETE | `/merchant/{id}` | Hapus merchant |
| GET | `/merchant/list` | Daftar merchant (owner) |
| GET | `/merchants/owner/{id}` | Merchant by owner |

### 1.5 Produk
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/products` | Daftar produk (public) |
| GET | `/products/popular` | Produk populer (public) |
| GET | `/products/top-by-category` | Top produk per kategori (public) |
| GET | `/products/{id}/with-reviews` | Produk detail + review (public) |
| POST | `/products` | Buat produk baru |
| PUT | `/products/{id}` | Update produk |
| DELETE | `/products/{id}` | Hapus produk |
| GET | `/products/search` | Cari produk |
| GET | `/merchants/{merchantId}/products` | Produk per merchant |

### 1.6 Product Gallery
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/products/{id}/gallery` | Tambah gambar |
| PUT | `/products/{productId}/gallery/{galleryId}` | Edit gambar |
| DELETE | `/products/{productId}/gallery/{galleryId}` | Hapus gambar |

### 1.7 Product Variant
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/products/{productId}/variants` | Tambah varian |
| PUT | `/variants/{variantId}` | Update varian |
| DELETE | `/variants/{variantId}` | Hapus varian |
| GET | `/products/{productId}/variants` | List varian per produk |
| GET | `/variants/{variantId}` | Detail varian |

### 1.8 Kategori Produk
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/categories` | Daftar kategori (public) |
| GET | `/categories/{id}` | Detail kategori (public) |
| POST | `/product-category` | Buat kategori |
| PUT | `/product-category/{id}` | Update kategori |
| DELETE | `/product-category/{id}` | Hapus kategori |

### 1.9 Order
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/orders` | Buat order baru |
| GET | `/orders` | Daftar order user |
| GET | `/orders/{id}` | Detail order |
| GET | `/orders/statistics` | Statistik order |
| GET | `/merchant/{merchantId}/orders` | Order per merchant |
| GET | `/merchants/{merchantId}/order-summary` | Ringkasan order merchant |
| PUT | `/merchants/orders/{orderId}/approve` | Approve order |
| PUT | `/merchants/orders/{orderId}/reject` | Reject order |
| PUT | `/merchants/orders/{orderId}/ready` | Tandai order siap |

### 1.10 Order Status
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/orders/{id}/process` | Proses order |
| POST | `/orders/{id}/ready-for-pickup` | Siap pickup |
| POST | `/orders/{id}/complete` | Selesaikan order |
| POST | `/orders/{id}/cancel` | Batalkan order |

### 1.11 Transaksi
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/transactions` | Buat transaksi |
| GET | `/transactions/{id}` | Detail transaksi |
| GET | `/transactions` | Daftar transaksi |
| PUT | `/transactions/{id}` | Update transaksi |
| PUT | `/transactions/{id}/cancel` | Batalkan transaksi |
| GET | `/merchants/{merchantId}/transactions` | Transaksi per merchant |
| GET | `/merchants/{merchantId}/transaction-summary` | Ringkasan transaksi |

### 1.12 Kurir
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/couriers` | Daftar menjadi kurir |
| GET | `/couriers/{id}` | Detail data kurir |
| PUT | `/couriers/{id}` | Update data kurir |
| DELETE | `/couriers/{id}` | Hapus data kurir |
| GET | `/courier/new-transactions` | Transaksi baru (available) |
| GET | `/courier/my-transactions` | Transaksi kurir sendiri |
| POST | `/courier/transactions/{id}/status` | Update status transaksi |
| POST | `/courier/transactions/{id}/approve` | Terima transaksi |
| POST | `/courier/transactions/{id}/reject` | Tolak transaksi |
| POST | `/courier/transactions/{id}/pickup` | Update status pickup |
| POST | `/courier/wallet/topup` | Top-up wallet kurir |
| GET | `/courier/wallet/balance` | Cek saldo wallet |
| GET | `/courier/statistics/daily` | Statistik harian |
| GET | `/courier/transactions/status-counts` | Jumlah per status |

### 1.13 Delivery
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/deliveries/assign-courier` | Assign kurir ke delivery |
| PUT | `/deliveries/{deliveryId}/status` | Update status delivery |
| PUT | `/delivery-items/{deliveryItemId}/pickup-status` | Update status pickup item |
| GET | `/couriers/{courierId}/deliveries` | Daftar delivery kurir |

### 1.14 Shipping
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/shipping/calculate` | Hitung ongkos kirim |
| POST | `/shipping/preview` | Preview ongkos kirim |

### 1.15 User Location
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/user-locations` | Daftar alamat |
| POST | `/user-locations` | Tambah alamat |
| GET | `/user-locations/{id}` | Detail alamat |
| PUT | `/user-locations/{id}` | Update alamat |
| DELETE | `/user-locations/{id}` | Hapus alamat |
| POST | `/user-locations/{id}/set-default` | Set default alamat |

### 1.16 Notifikasi (FCM)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/fcm/token` | Simpan/update FCM token |
| DELETE | `/fcm/token` | Hapus FCM token |
| POST | `/fcm/topic/subscribe` | Subscribe topik |
| POST | `/notifications/test/merchant` | Test notifikasi merchant |

### 1.17 Product Review
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/reviews` | Buat review |
| PUT | `/reviews/{id}` | Update review |
| DELETE | `/reviews/{id}` | Hapus review |
| GET | `/user/reviews` | Daftar review user |

### 1.18 Health Check (Public)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/health` | Status server, DB, Redis |

---

## 2. Database

### Database Engine
- **MySQL 8** dengan InnoDB engine
- **Redis** untuk caching dan session

### Tabel Utama (17 Models)
| Tabel | Fungsi |
|-------|--------|
| `users` | Semua pengguna (USER, MERCHANT, COURIER, ADMIN) |
| `merchants` | Data toko/merchant |
| `products` | Produk yang dijual |
| `product_categories` | Kategori produk |
| `product_galleries` | Galeri gambar produk |
| `product_variants` | Varian produk |
| `product_reviews` | Review & rating produk |
| `orders` | Pesanan |
| `order_items` | Item dalam pesanan |
| `transactions` | Transaksi pembayaran |
| `couriers` | Data kurir |
| `courier_batches` | Batch pengiriman kurir |
| `deliveries` | Data pengiriman |
| `delivery_items` | Item dalam pengiriman |
| `user_locations` | Alamat pengguna |
| `loyalty_points` | Poin loyalitas |
| `fcm_tokens` | Firebase Cloud Messaging tokens |

Untuk detail lengkap skema database, lihat [Database Schema](architecture/database-schema.md) dan [ERD Diagram](architecture/erd-diagram.md).

---

## 3. Integrasi Layanan Pihak Ketiga

### 3.1 Firebase Cloud Messaging (kreait/laravel-firebase)
- Push notification ke perangkat mobile
- Topic-based subscription untuk role-based notifications
- Test endpoint untuk validasi integrasi

### 3.2 AWS S3 Compatible Storage (IDCloudHost IS3)
- Upload dan penyimpanan gambar produk, galeri, logo merchant, foto profil
- Endpoint: `https://is3.cloudhost.id`
- Bucket: `antarkanma`

### 3.3 Payment Gateway (Rencana Masa Depan)
- Integrasi Midtrans atau Xendit
- Saat ini menggunakan pembayaran manual (COD)

---

## 4. Keamanan

### 4.1 Authentication & Authorization
- **Laravel Sanctum** untuk API token-based authentication
- **Jetstream** untuk session management
- Role-based access control: USER, MERCHANT, COURIER, ADMIN

### 4.2 Enkripsi & Transport
- HTTPS untuk semua komunikasi API
- Password hashing via Laravel `bcrypt`

### 4.3 Validasi Input
- Form Request validation di setiap endpoint
- Prepared statements via Eloquent ORM (mencegah SQL injection)

### 4.4 Rate Limiting
- Laravel built-in throttle middleware
- Konfigurasi per-endpoint jika diperlukan

---

## 5. Infrastructure

### 5.1 Docker
- Multi-environment: `docker-compose.yml`, `docker-compose.laptop.yml`, `docker-compose.vps.yml`
- Containerized: PHP-FPM, MySQL, Redis, Nginx

### 5.2 Load Balancer (Nginx)
- Write operations (POST/PUT/DELETE) → 100% VPS
- Read operations (GET) → 75% VPS, 25% Laptop (jika aktif)
- Admin routes → 100% VPS
- Health check setiap 5 detik

### 5.3 Cloudflare Tunnel
- Menghubungkan laptop development ke domain publik
- Fallback otomatis ke VPS jika laptop down

### 5.4 Caching (Redis)
- Session storage
- Data caching untuk query yang sering diakses
- Master-Slave replication antara VPS dan Laptop

---

## 6. Testing

### 6.1 Unit & Feature Tests
- Framework: **PHPUnit 11**
- Lokasi: `tests/`
- Command: `php artisan test`

### 6.2 API Testing
- Gunakan Postman atau tools sejenis
- Health check endpoint: `GET /api/health`

### 6.3 Performance Testing
- Load testing untuk endpoint kritis
- Redis caching untuk optimasi response time

---

Spesifikasi teknis ini akan ditinjau dan diperbarui seiring perkembangan proyek.
