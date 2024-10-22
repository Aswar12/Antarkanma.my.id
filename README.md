# Antarkanma

Antarkanma adalah aplikasi e-commerce yang mendukung multi-merchant, memungkinkan pengguna untuk memesan makanan dan barang dari berbagai merchant dengan mudah. Sistem ini dirancang untuk memberikan pengalaman pengguna yang optimal melalui fitur-fitur yang lengkap dan intuitif.

## Rangkuman Database

### Entitas dan Atribut

#### Users
- `id` (Primary Key)
- `name`
- `email` (unique)
- `password`
- `roles` (USER, MERCHANT, COURIER)
- `username`
- `phone_number`
- `created_at`
- `updated_at`

#### Merchants
- `id` (Primary Key)
- `name`
- `owner_id` (Foreign Key ke Users)
- `address`
- `phone_number`
- `created_at`
- `updated_at`

#### Products
- `id` (Primary Key)
- `merchant_id` (Foreign Key ke Merchants)
- `category_id` (Foreign Key ke Product_Categories)
- `name`
- `description`
- `price`
- `created_at`
- `updated_at`

#### Product_Categories
- `id` (Primary Key)
- `name`
- `softDeletes`
- `created_at`
- `updated_at`

#### Product_Galleries
- `id` (Primary Key)
- `products_id` (Foreign Key ke Products)
- `url`
- `softDeletes`
- `created_at`
- `updated_at`

#### Orders
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `total_amount`
- `order_status` (PENDING, PROCESSING, COMPLETED, CANCELED)
- `created_at`
- `updated_at`

#### Order_Items
- `id` (Primary Key)
- `order_id` (Foreign Key ke Orders)
- `product_id` (Foreign Key ke Products)
- `merchant_id` (Foreign Key ke Merchants)
- `quantity`
- `price`
- `created_at`
- `updated_at`

#### Loyalty_Points
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `points`
- `created_at`

#### Couriers
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `vehicle_type`
- `license_plate`
- `created_at`
- `updated_at`

#### Transactions
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

#### User_Locations
- `id` (Primary Key)
- `customer_name`
- `user_id` (Foreign Key ke Users)
- `address`
- `longitude`
- `latitude`
- `address_type`
- `phone_number`
- `created_at`
- `updated_at`

#### Delivery
- `id` (Primary Key)
- `transaction_id` (Foreign Key ke Transactions)
- `courier_id` (Foreign Key ke Couriers)
- `delivery_status` (PENDING, IN_PROGRESS, DELIVERED, CANCELED)
- `estimated_delivery_time`
- `actual_delivery_time`
- `created_at`
- `updated_at`

#### Product_Reviews
- `id` (Primary Key)
- `user_id` (Foreign Key ke Users)
- `product_id` (Foreign Key ke Products)
- `rating`
- `comment`
- `created_at`
- `updated_at`

#### Delivery_Items
- `id` (Primary Key)
- `delivery_id` (Foreign Key ke Deliveries)
- `order_item_id` (Foreign Key ke Order_Items)
- `pickup_status` (ENUM: 'PENDING', 'PICKED_UP')
- `pickup_time` (DATETIME, nullable)
- `created_at`
- `updated_at`

#### Courier_Batches
- `id` (Primary Key)
- `courier_id` (Foreign Key ke Couriers)
- `status` (ENUM: 'PREPARING', 'IN_PROGRESS', 'COMPLETED')
- `start_time` (DATETIME)
- `end_time` (DATETIME)
- `created_at`
- `updated_at`

### Relasi Antar Tabel
- **Users** memiliki banyak **Merchants**, **Orders**, **Loyalty_Points**, ****User _Locations**, **Product_Reviews**, dan **Transactions**.
- **Merchants** terkait dengan satu **User ** dan memiliki banyak **Products** serta **Order_Items**.
- **Products** terkait dengan satu **Merchant** dan satu **Product_Category**, serta memiliki banyak **Order_Items**, **Product_Galleries**, dan **Product_Reviews**.
- **Product_Categories** memiliki banyak **Products**.
- **Orders** terkait dengan satu **User ** dan memiliki banyak **Order_Items** serta satu **Transaction**.
- **Order_Items** terkait dengan satu **Order**, satu **Product**, dan satu **Merchant**, serta memiliki satu **Delivery_Item**.
- **Loyalty_Points** terkait dengan satu **User **.
- **Couriers** terkait dengan satu **User ** dan memiliki banyak **Deliveries** serta **Courier_Batches**.
- **Transactions** terkait dengan satu **Order**, satu **User **, dan satu **User _Location**, serta memiliki satu **Delivery**.
- **User _Locations** terkait dengan satu **User **.
- **Deliveries** terkait dengan satu **Transaction**, satu **Courier**, dan memiliki banyak **Delivery_Items**.
- **Product_Reviews** terkait dengan satu **User ** dan satu **Product**.
- **Delivery_Items** terkait dengan satu **Delivery** dan satu **Order_Item**.
- **Courier_Batches** terkait dengan satu **Courier** dan memiliki banyak **Deliveries**.

### Kesimpulan dan Fitur Utama
- **Multi-Merchant Support**: Sistem mendukung multi-merchant, memungkinkan satu pesanan mencakup produk dari berbagai merchant.
- **Manajemen Produk**: Produk terkait dengan kategori dan merchant, serta galeri produk memungkinkan penambahan gambar multiple untuk setiap produk.
- **Sistem Pemesanan**: Orders menyimpan informasi pesanan keseluruhan, sedangkan Order_Items menyimpan detail item dalam pesanan.
- **Transaksi dan Pembayaran**: Transactions menyimpan informasi pembayaran dan status, mendukung berbagai metode pembayaran (manual dan online).
- **Sistem Pengiriman**: Deliveries melacak status pengiriman untuk setiap transaksi, dengan Courier_Batches memungkinkan pengelompokan pengiriman untuk efisiensi.
- **Manajemen Pengguna**: Users dapat memiliki peran berbeda (USER, MERCHANT, COURIER), dengan User_Locations mendukung penyimpanan beberapa alamat untuk setiap pengguna.
- **Sistem Loyalitas**: Loyalty_Points memungkinkan implementasi sistem loyalitas pelanggan.
- **Ulasan Produk**: Product_Reviews memungkinkan pengguna memberikan ulasan dan rating untuk produk.
- **Manajemen Kurir**: Couriers terkait dengan Users, memungkinkan pengelolaan informasi kurir.
- **Fleksibilitas Lokasi**: User_Locations memungkinkan pengguna menyimpan beberapa alamat.
- **Pelacakan Pengiriman Detail**: Delivery_Items memungkinkan pelacakan status pickup untuk setiap item dalam pengiriman.

Struktur database ini memberikan fondasi yang kuat untuk aplikasi Antarkanma, mendukung berbagai fitur e-commerce dan manajemen pengiriman. Sistem ini memungkinkan skalabilitas dan fleksibilitas untuk pengembangan fitur lebih lanjut di masa depan.

## Spesifikasi Teknis
Untuk informasi lebih lanjut tentang spesifikasi teknis, API, dan integrasi pihak ketiga, silakan lihat bagian berikutnya dalam dokumentasi ini.
