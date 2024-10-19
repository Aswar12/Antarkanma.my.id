# Catatan Perencanaan Implementasi Backend Antarkanma

## Fokus Implementasi
1. Manajemen pengguna (pelanggan, pemilik usaha, kurir)
2. Manajemen kedai/usaha dan menu

## Rencana Implementasi

### 1. Manajemen Pengguna
- Model User:
  - Atribut: id, name, email, password, role (enum: pelanggan, pemilik_usaha, kurir), phone_number, address, created_at, updated_at
- Resource Filament untuk User
- CRUD operations untuk User
- Filter berdasarkan role

### 2. Manajemen Kedai/Usaha dan Menu
- Model Store:
  - Atribut: id, name, owner_id (foreign key ke User), address, description, operating_hours, created_at, updated_at
- Model Menu:
  - Atribut: id, store_id (foreign key ke Store), name, description, price, is_available, created_at, updated_at
- Resource Filament untuk Store dan Menu
- CRUD operations untuk Store dan Menu
- Relasi antara Store dan Menu di Filament

## Langkah-langkah Implementasi
1. Siapkan proyek Laravel baru
2. Instal Filament
3. Konfigurasi database
4. Buat migrasi untuk tabel users, stores, dan menus
5. Buat model User, Store, dan Menu
6. Buat Filament resources untuk User, Store, dan Menu
7. Implementasi logika bisnis dan validasi
8. Kustomisasi tampilan Filament sesuai kebutuhan

Catatan: Implementasi akan dilakukan setelah persetujuan dan mungkin memerlukan penyesuaian berdasarkan kebutuhan spesifik proyek.

## Sistem Pembayaran dan Keuangan
- Implementasi sistem deposit untuk kurir
- Pembayaran menggunakan metode COD (Cash on Delivery)
- Kurir menggunakan saldo deposit untuk membayar pesanan terlebih dahulu
- Pelanggan membayar harga produk + ongkos kirim kepada kurir
- Platform mengambil keuntungan 2000 rupiah per transaksi

### Fitur Sistem Deposit
- Manajemen saldo deposit untuk setiap kurir
- Sistem top-up saldo deposit
- Pencatatan penggunaan saldo untuk setiap transaksi
- Pelaporan dan ringkasan keuangan untuk kurir dan admin

## Sistem Pelacakan Pesanan
1. Status Dasar:
   - Pesanan Diterima
   - Dalam Perjalanan
   - Terkirim

2. Pelacakan Real-time (untuk implementasi di masa depan):
   - Menggunakan GPS pada perangkat kurir
   - Pengiriman data lokasi secara berkala ke server
   - Penyimpanan dan pembaruan lokasi terkini di database
   - Penggunaan WebSockets untuk pembaruan real-time ke aplikasi pelanggan

Catatan: Implementasi awal akan fokus pada status dasar, dengan rencana untuk menambahkan pelacakan real-time di fase pengembangan selanjutnya.

## Rangkuman dan Poin Penting:
1. Target awal: 3 kecamatan (Segeri, Ma'rang, dan Mandalle)
2. Sistem pembayaran: COD dengan sistem deposit untuk kurir
3. Keuntungan platform: 2000 rupiah per transaksi
4. Sistem pelacakan: Implementasi awal dengan status dasar, rencana pengembangan real-time di masa depan
5. Fitur berbagi menu ke WhatsApp akan dipertimbangkan

Catatan: Prioritas fitur chat in-app masih perlu ditentukan dalam diskusi selanjutnya.

Langkah selanjutnya:
1. Finalisasi desain database dan struktur API
2. Mulai pengembangan MVP (Minimum Viable Product)
3. Uji coba di skala kecil di salah satu kecamatan target
4. Evaluasi dan iterasi berdasarkan umpan balik pengguna

## Fitur Penilaian dan Ulasan
1. Untuk Kedai:
   - Rating bintang (misalnya 1-5)
   - Ulasan tertulis
   - Kategori penilaian: kualitas makanan, harga, pelayanan

2. Untuk Kurir:
   - Rating bintang (misalnya 1-5)
   - Ulasan tertulis
   - Kategori penilaian: kecepatan pengiriman, keramahan, ketepatan

Catatan: Implementasi awal akan mencakup rating bintang dan ulasan tertulis. Kategori penilaian dapat ditambahkan dalam pengembangan selanjutnya.

## Fitur Promosi dan Diskon
1. Promosi Awal:
   - Gratis ongkir untuk 15 kali pesanan pertama per pengguna
   
2. Implementasi:
   - Sistem penghitungan jumlah pesanan per pengguna
   - Penerapan ongkir gratis secara otomatis untuk 15 pesanan pertama
   - Notifikasi kepada pengguna tentang sisa jumlah pesanan gratis ongkir

Catatan: Sistem ini dapat dikembangkan lebih lanjut di masa depan untuk mencakup jenis promosi lain seperti kupon atau diskon persentase.

## Fitur Notifikasi
1. Notifikasi Status Pesanan:
   - Pesanan diterima
   - Pesanan sedang diproses
   - Kurir dalam perjalanan
   - Pesanan telah tiba
   
2. Notifikasi Promosi:
   - Pemberitahuan ketika pengguna mendapatkan 1 kali pesanan gratis ongkir setelah melakukan 15 kali pesanan

Implementasi:
- Sistem push notification untuk aplikasi mobile
- Integrasi dengan sistem manajemen pesanan dan sistem promosi

Catatan: Sistem notifikasi ini dapat dikembangkan lebih lanjut di masa depan untuk mencakup jenis notifikasi lain seperti promosi khusus atau pengingat.

## Manajemen Menu
1. Fitur Kategori Menu:
   - Pemilik usaha dapat membuat dan mengelola kategori menu (misalnya: Makanan Utama, Minuman, Cemilan, dll.)
   - Setiap item menu dapat dikaitkan dengan satu atau lebih kategori

2. Implementasi:
   - CRUD (Create, Read, Update, Delete) operasi untuk kategori menu
   - Asosiasi item menu dengan kategori
   - Fitur pencarian dan filter berdasarkan kategori

Catatan: Fitur ini dapat dikembangkan lebih lanjut di masa depan untuk mencakup variasi produk atau opsi tambahan (add-ons).

## Layanan Aplikasi Antarkanma
Mari kita bahas lebih detail tentang layanan yang akan disediakan:

1. Jenis Layanan Utama:
   a. Pengantaran Makanan:
      - Menghubungkan pelanggan dengan restoran/kedai lokal
      - Pemesanan dan pengantaran makanan
   
   b. Pengantaran Barang:
      - Layanan kurir untuk pengantaran barang antar lokasi dalam 3 kecamatan

2. Cakupan Wilayah:
   - Terbatas pada 3 kecamatan: Segeri, Ma'rang, dan Mandalle

3. Fitur Khusus:
   - Sistem pemesanan yang terintegrasi untuk makanan dan barang
   - Pelacakan pesanan real-time (untuk pengembangan masa depan)
   - Penilaian dan ulasan untuk kedai dan kurir

Catatan: Pengembangan selanjutnya dapat mempertimbangkan perluasan wilayah layanan atau penambahan jenis layanan baru.

4. Batasan dan Aturan Pengantaran Barang:
   a. Kendaraan:
      - Utama: Motor
      - Rencana pengembangan: Bentor (becak motor)
   
   b. Batasan:
      - Ukuran: Barang harus dapat dimuat di motor atau bentor
      - Berat: Sesuai dengan kapasitas motor atau bentor (perlu ditentukan batas spesifik)
      - Jenis barang: Tidak diizinkan untuk barang berbahaya, ilegal, atau mudah rusak

   c. Fleksibilitas:
      - Sistem harus dapat mengakomodasi penambahan jenis kendaraan di masa depan
      - Opsi untuk memilih jenis kendaraan saat pemesanan (setelah bentor ditambahkan)

Catatan: Perlu dibuat panduan detail untuk kurir mengenai jenis barang yang dapat diantarkan dan prosedur penanganan barang.

5. Sistem Pembayaran:
   a. Metode Pembayaran Saat Ini:
      - COD (Cash on Delivery)
      - Sistem deposit untuk kurir
   
   b. Rencana Integrasi Pembayaran Digital:
      - E-wallet
      - Transfer bank

   c. Implementasi:
      - Integrasi API dengan penyedia layanan e-wallet
      - Integrasi dengan sistem perbankan untuk verifikasi transfer
      - Sistem manajemen saldo untuk e-wallet dalam aplikasi
      - Fitur pemilihan metode pembayaran saat checkout

   d. Keamanan:
      - Enkripsi data pembayaran
      - Verifikasi dua faktor untuk transaksi digital
      - Sistem deteksi fraud

Catatan: Perlu dilakukan penelitian lebih lanjut tentang regulasi fintech dan keamanan data untuk implementasi pembayaran digital.

6. Keamanan dan Privasi Data:
   a. Enkripsi Data:
      - Enkripsi end-to-end untuk komunikasi antara aplikasi dan server
      - Enkripsi data sensitif pengguna (seperti informasi pembayaran) saat disimpan di database
   
   b. Autentikasi Dua Faktor:
      - Menggunakan nomor WhatsApp pengguna untuk verifikasi
      - Implementasi sistem OTP (One-Time Password) melalui WhatsApp

   c. Kebijakan Privasi:
      - Penyusunan kebijakan privasi yang komprehensif
      - Fitur persetujuan pengguna untuk pengumpulan dan penggunaan data

   d. Keamanan Akun:
      - Sistem deteksi dan pencegahan akses tidak sah
      - Fitur log aktivitas akun yang dapat diakses oleh pengguna

   e. Kepatuhan Regulasi:
      - Memastikan kepatuhan terhadap regulasi perlindungan data yang berlaku di Indonesia

Catatan: Perlu dilakukan konsultasi dengan ahli keamanan siber untuk implementasi yang tepat dan aman.
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
