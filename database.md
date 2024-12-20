# Rangkuman Database Antarkanma

## Entitas dan Atribut

### Users
- `id` (Primary Key)
- `name`
- `email` (unique)
- `password`
- `roles` (USER, MERCHANT, COURIER)
- `username`
- `phone_number`
- `created_at`
- `updated_at`

### Merchants
- `id` (Primary Key)
- `name`
- `owner_id` (Foreign Key ke Users)
- `address`
- `phone_number`
- `created_at`
- `updated_at`

### Products
- `id` (Primary Key)
- `merchant_id` (Foreign Key ke Merchants)
- `category_id` (Foreign Key ke Product_Categories)
- `name`
- `description`
- `price`
- `created_at`
- `updated_at`

### Product_Categories
- `id` (Primary Key)
- `name`
- `softDeletes`
- `created_at`
- `updated_at`

### Product_Galleries
- `id` (Primary Key)
- `products_id` (Foreign Key ke Products)
- `url`
- `softDeletes`
- `created_at`
- `updated_at`

### Orders
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `total_amount`
- `order_status` (PENDING, PROCESSING, COMPLETED, CANCELED)
- `created_at`
- `updated_at`

### Order_Items
- `id` (Primary Key)
- `order_id` (Foreign Key ke Orders)
- `product_id` (Foreign Key ke Products)
- `merchant_id` (Foreign Key ke Merchants)
- `quantity`
- `price`
- `created_at`
- `updated_at`

### Loyalty_Points
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `points`
- `created_at`

### Couriers
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `vehicle_type`
- `license_plate`
- `created_at`
- `updated_at`

### Transactions
- `id` (Primary Key)
- `order_id` (Foreign Key ke Orders)
- `user_id` (Foreign Key ke Users)
- `user_location_id` (Foreign Key ke User_Locations)
- `total_price`
- `shipping_price`
- `payment_date`
- `status` (PENDING, COMPLETED, CANCELED)
- `payment_method` (MANUAL, ONLINE)
- `payment_status` (PENDING, COMPLETED, FAILED)
- `rating`
- `note`
- `created_at`
- `updated_at`

### User_Locations
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `customer_name` (nullable)
- `address` (text)
- `city`
- `district` (nullable)
- `postal_code`
- `latitude` (decimal, nullable)
- `longitude` (decimal, nullable)
- `address_type` (ENUM: 'RUMAH', 'KANTOR', 'TOKO', 'LAINNYA')
- `phone_number`
- `is_default` (boolean)
- `notes` (text, nullable)
- `is_active` (boolean)
- `deleted_at` (timestamp, nullable - untuk soft delete)
- `created_at`
- `updated_at`

### Delivery
- `id` (Primary Key)
- `transaction_id` (Foreign Key ke Transactions)
- `courier_id` (Foreign Key ke Couriers)
- `delivery_status` (PENDING, IN_PROGRESS, DELIVERED, CANCELED)
- `estimated_delivery_time`
- `actual_delivery_time`
- `created_at`
- `updated_at`

### Product_Reviews
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `product_id` (Foreign Key ke Products)
- `rating`
- `comment`
- `created_at`
- `updated_at`

### Delivery_Items
- `id` (Primary Key)
- `delivery_id` (Foreign Key ke Deliveries)
- `order_item_id` (Foreign Key ke Order_Items)
- `pickup_status` (ENUM: 'PENDING', 'PICKED_UP')
- `pickup_time` (DATETIME, nullable)
- `created_at`
- `updated_at`

### Courier_Batches
- `id` (Primary Key)
- `courier_id` (Foreign Key ke Couriers)
- `status` (ENUM: 'PREPARING', 'IN_PROGRESS', 'COMPLETED')
- `start_time` (DATETIME)
- `end_time` (DATETIME)
- `created_at`
- `updated_at`

### Product_Variants
- `id` (Primary Key)
- `product_id` (Foreign Key to Products)
- `name`
- `value`
- `price_adjustment` (decimal, default 0)
- `status` (ENUM: 'ACTIVE', 'INACTIVE', 'OUT_OF_STOCK', default 'ACTIVE')
- `created_at`
- `updated_at`

## Relasi Antar Tabel

### Users
- Memiliki banyak Merchants (1-to-many)
- Memiliki banyak Orders (1-to-many)
- Memiliki banyak Loyalty_Points (1-to-many)
- Memiliki satu Courier (1-to-1)
- Memiliki banyak User_Locations (1-to-many)
- Memiliki banyak Product_Reviews (1-to-many)
- Memiliki banyak Transactions (1-to-many)

### Products
- Terkait dengan satu Merchant (many-to-1)
- Terkait dengan satu Product_Category (many-to-1)
- Memiliki banyak Order_Items (1-to-many)
- Memiliki banyak Product_Galleries (1-to-many)
- Memiliki banyak Product_Reviews (1-to-many)
- Memiliki banyak Product_Variants (1-to-many)

### Product_Categories
- Memiliki banyak Products (1-to-many)

### Orders
- Terkait dengan satu User (many-to-1)
- Memiliki banyak Order_Items (1-to-many)
- Memiliki satu Transaction (1-to-1)

### Order_Items
- Terkait dengan satu Order (many-to-1)
- Terkait dengan satu Product (many-to-1)
- Terkait dengan satu Merchant (many-to-1)
- Memiliki satu Delivery_Item (1-to-1)

### Loyalty_Points
- Terkait dengan satu User (many-to-1)

### Couriers
- Terkait dengan satu User (1-to-1)
- Memiliki banyak Deliveries (1-to-many)
- Memiliki banyak Courier_Batches (1-to-many)

### Transactions
- Terkait dengan satu Order (1-to-1)
- Terkait dengan satu User (many-to-1)
- Terkait dengan satu User_Location (many-to-1)
- Memiliki satu Delivery (1-to-1)

### User_Locations
- Terkait dengan satu User (many-to-1)

### Deliveries
- Terkait dengan satu Transaction (1-to-1)
- Terkait dengan satu Courier (many-to-1)
- Memiliki banyak Delivery_Items (1-to-many)
- Terkait dengan satu Courier_Batch (many-to-1, optional)

### Product_Reviews
- Terkait dengan satu User (many-to-1)
- Terkait dengan satu Product (many-to-1)

### Delivery_Items
- Terkait dengan satu Delivery (many-to-1)
- Terkait dengan satu Order_Item (1-to-1)

### Courier_Batches
- Terkait dengan satu Courier (many-to-1)
- Memiliki banyak Deliveries (1-to-many)

## Alur Data dan Use Cases

### Alur Pemesanan
1. User mencari dan memilih produk
2. User menambahkan produk ke keranjang
3. User melakukan checkout dan memilih alamat pengiriman
4. Sistem membuat Order dan Order_Items
5. User melakukan pembayaran
6. Sistem membuat Transaction
7. Merchant memproses pesanan
8. Sistem membuat Delivery dan menugaskan Courier
9. Courier mengupdate status pengiriman
10. User menerima pesanan dan memberikan review

### Use Cases per Role

#### User
- Mendaftar dan login
- Mengelola profil dan alamat
- Melihat katalog dan mencari produk
- Mengelola keranjang belanja
- Melakukan pemesanan dan pembayaran
- Melacak pesanan
- Memberikan review
- Mengelola poin loyalitas

#### Merchant
- Mengelola profil toko
- Mengelola produk dan kategori
- Memproses pesanan
- Melihat laporan penjualan
- Mengelola promosi

#### Courier
- Menerima tugas pengiriman
- Mengupdate status pengiriman
- Mengelola batch pengiriman
- Melihat riwayat pengiriman

## Kesimpulan dan Fitur Utama

### Multi-Merchant Support
- Sistem mendukung multi-merchant
- Satu pesanan dapat mencakup produk dari berbagai merchant

### Manajemen Produk
- Produk terkait dengan kategori dan merchant
- Galeri produk untuk multiple gambar
- Sistem variant produk

### Sistem Pemesanan
- Orders menyimpan informasi pesanan keseluruhan
- Order_Items menyimpan detail item dalam pesanan

### Transaksi dan Pembayaran
- Transactions menyimpan informasi pembayaran dan status
- Mendukung berbagai metode pembayaran

### Sistem Pengiriman
- Deliveries melacak status pengiriman
- Courier_Batches untuk efisiensi pengiriman
- Delivery_Items untuk tracking detail

### Manajemen Pengguna
- Users dengan multiple roles
- User_Locations untuk multiple alamat
- Sistem loyalitas pelanggan

### Sistem Review
- Product_Reviews untuk feedback produk
- Rating untuk merchant dan kurir

Struktur database ini memberikan fondasi yang kuat untuk aplikasi Antarkanma, mendukung berbagai fitur e-commerce dan manajemen pengiriman. Sistem ini memungkinkan skalabilitas dan fleksibilitas untuk pengembangan fitur lebih lanjut di masa depan.
