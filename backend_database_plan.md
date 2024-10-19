# Perencanaan Pengembangan Backend dan Database

## Struktur Backend

### 1. API Endpoints
- /auth: Untuk autentikasi dan manajemen pengguna
- /customers: Endpoint untuk pelanggan
- /merchants: Endpoint untuk pemilik usaha/kedai
- /couriers: Endpoint untuk kurir
- /orders: Manajemen pesanan
- /payments: Integrasi pembayaran
- /notifications: Manajemen notifikasi

### 2. Middleware
- Authentication: Verifikasi token JWT
- Error Handling: Penanganan kesalahan global
- Logging: Pencatatan aktivitas dan error
- Rate Limiting: Pembatasan jumlah request

### 3. Services
- UserService: Manajemen pengguna dan autentikasi
- OrderService: Pemrosesan dan manajemen pesanan
- PaymentService: Integrasi dengan gateway pembayaran
- NotificationService: Pengiriman notifikasi
- GeolocationService: Layanan lokasi dan pemetaan

## Struktur Database

### 1. Tabel Users
- id (PK)
- username
- email
- password_hash
- role (customer, merchant, courier)
- created_at
- updated_at

### 2. Tabel Customers
- id (PK)
- user_id (FK to Users)
- full_name
- phone_number
- address
- created_at
- updated_at

### 3. Tabel Merchants
- id (PK)
- user_id (FK to Users)
- business_name
- business_address
- business_phone
- business_description
- is_open
- created_at
- updated_at

### 4. Tabel Couriers
- id (PK)
- user_id (FK to Users)
- full_name
- phone_number
- vehicle_type
- license_plate
- created_at
- updated_at

### 5. Tabel Products
- id (PK)
- merchant_id (FK to Merchants)
- name
- description
- price
- image_url
- is_available
- created_at
- updated_at

### 6. Tabel Orders
- id (PK)
- customer_id (FK to Customers)
- merchant_id (FK to Merchants)
- courier_id (FK to Couriers)
- status (pending, confirmed, preparing, on_delivery, delivered, cancelled)
- total_amount
- created_at
- updated_at

### 7. Tabel OrderItems
- id (PK)
- order_id (FK to Orders)
- product_id (FK to Products)
- quantity
- price
- created_at
- updated_at

### 8. Tabel Payments
- id (PK)
- order_id (FK to Orders)
- amount
- payment_method
- status (pending, completed, failed)
- transaction_id
- created_at
- updated_at

### 9. Tabel Reviews
- id (PK)
- order_id (FK to Orders)
- reviewer_id (FK to Users)
- reviewee_id (FK to Users)
- rating
- comment
- created_at
- updated_at

## Indeks Database
- Users: email, username
- Products: merchant_id, name
- Orders: customer_id, merchant_id, courier_id, status
- Payments: order_id, status

## Caching Strategy
- Menggunakan Redis untuk menyimpan:
  - Informasi sesi pengguna
  - Data produk yang sering diakses
  - Status pesanan terkini

## Keamanan Database
- Enkripsi data sensitif (misalnya, informasi pembayaran)
- Penggunaan prepared statements untuk mencegah SQL injection
- Regular backups dan replikasi data

## Skalabilitas
- Implementasi database sharding berdasarkan lokasi geografis jika diperlukan
- Penggunaan read replicas untuk mengurangi beban pada database utama

## Migrasi dan Versioning
- Menggunakan tools seperti Knex.js atau Sequelize untuk manajemen skema dan migrasi database

Perencanaan ini memberikan struktur dasar untuk backend dan database aplikasi layanan pesan antar. Implementasi detail akan disesuaikan selama proses pengembangan berdasarkan kebutuhan spesifik dan feedback pengguna.
