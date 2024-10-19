# Spesifikasi Teknis Antarkanma.my.id

## 1. Struktur API

### 1.1 Autentikasi
- POST /api/auth/register
- POST /api/auth/login
- POST /api/auth/logout
- GET /api/auth/user

### 1.2 Pengguna
- GET /api/users/:id
- PUT /api/users/:id
- DELETE /api/users/:id
- GET /api/users/:id/profile
- PUT /api/users/:id/profile

### 1.3 Toko
- GET /api/stores
- POST /api/stores
- GET /api/stores/:id
- PUT /api/stores/:id
- DELETE /api/stores/:id
- GET /api/stores/:id/dashboard

### 1.4 Menu
- GET /api/stores/:id/menu
- POST /api/stores/:id/menu
- PUT /api/stores/:id/menu/:itemId
- DELETE /api/stores/:id/menu/:itemId

### 1.5 Pesanan
- POST /api/orders
- GET /api/orders/:id
- PUT /api/orders/:id/status
- GET /api/users/:id/orders
- GET /api/stores/:id/orders

### 1.6 Penilaian dan Ulasan
- POST /api/stores/:id/reviews
- POST /api/couriers/:id/reviews
- GET /api/stores/:id/reviews
- GET /api/couriers/:id/reviews

### 1.7 Kurir
- GET /api/couriers/:id/dashboard
- GET /api/couriers/:id/deposits
- POST /api/couriers/:id/deposits

### 1.8 Pencarian
- GET /api/search/stores
- GET /api/search/menu-items

### 1.9 Notifikasi
- POST /api/notifications/send
- GET /api/users/:id/notifications

## 2. Skema Database

### 2.1 Tabel Pengguna
- id (PK)
- nama
- email
- password_hash
- peran (enum: pelanggan, pemilik_toko, kurir)
- nomor_telepon
- alamat
- created_at
- updated_at

### 2.2 Tabel Toko
- id (PK)
- pemilik_id (FK ke Pengguna)
- nama
- deskripsi
- alamat
- jam_operasional
- created_at
- updated_at

### 2.3 Tabel Item_Menu
- id (PK)
- toko_id (FK ke Toko)
- nama
- deskripsi
- harga
- tersedia
- created_at
- updated_at

### 2.4 Tabel Pesanan
- id (PK)
- pelanggan_id (FK ke Pengguna)
- toko_id (FK ke Toko)
- kurir_id (FK ke Pengguna)
- status (enum: menunggu, diterima, dalam_proses, terkirim, dibatalkan)
- total_harga
- biaya_pengiriman
- metode_pembayaran (enum: cod, deposit)
- created_at
- updated_at

### 2.5 Tabel Item_Pesanan
- id (PK)
- pesanan_id (FK ke Pesanan)
- item_menu_id (FK ke Item_Menu)
- jumlah
- harga

### 2.6 Tabel Ulasan
- id (PK)
- pengulas_id (FK ke Pengguna)
- diulas_id (FK ke Pengguna atau Toko)
- tipe_diulas (enum: toko, kurir)
- rating
- komentar
- created_at

### 2.7 Tabel Deposit_Kurir
- id (PK)
- kurir_id (FK ke Pengguna)
- jumlah
- jenis_transaksi (enum: deposit, penarikan, pembayaran_pesanan)
- created_at

## 3. Integrasi Layanan Pihak Ketiga

### 3.1 Firebase Authentication
- Detail implementasi untuk registrasi dan login pengguna
- Alur autentikasi berbasis token

### 3.2 Firebase Cloud Messaging
- Pengaturan untuk mengirim notifikasi push
- Jenis notifikasi dan struktur payload

### 3.3 Google Maps API
- Integrasi untuk menampilkan lokasi toko
- Optimisasi rute kurir

### 3.4 Midtrans/Xendit (Implementasi Masa Depan)
- Rencana integrasi gateway pembayaran
- Alur transaksi dan penanganan callback

## 4. Langkah-langkah Keamanan

### 4.1 Enkripsi Data
- Penggunaan AES-256 untuk mengenkripsi data sensitif di database
- Implementasi HTTPS untuk semua komunikasi API

### 4.2 Autentikasi dan Otorisasi
- Autentikasi berbasis JWT
- Kontrol akses berbasis peran (RBAC) untuk berbagai jenis pengguna

### 4.3 Validasi dan Sanitasi Input
- Validasi input sisi server untuk semua endpoint API
- Penggunaan prepared statements untuk query database untuk mencegah SQL injection

### 4.4 Pembatasan Rate
- Implementasi pembatasan rate pada endpoint API untuk mencegah penyalahgunaan
- Batas yang dapat dikonfigurasi berdasarkan endpoint dan peran pengguna

### 4.5 Penanganan Error dan Logging
- Penanganan error yang aman untuk mencegah kebocoran informasi
- Logging komprehensif untuk audit keamanan dan pemecahan masalah

## 5. Pertimbangan Skalabilitas

### 5.1 Optimisasi Database
- Strategi pengindeksan untuk field yang sering diakses
- Pertimbangan untuk sharding di masa depan berdasarkan lokasi geografis

### 5.2 Strategi Caching
- Penggunaan Redis untuk caching data yang sering diakses
- Strategi invalidasi cache untuk menjaga konsistensi data

### 5.3 Load Balancing
- Rencana implementasi untuk scaling horizontal server aplikasi
- Penggunaan load balancer untuk mendistribusikan traffic

### 5.4 Pemrosesan Asynchronous
- Penggunaan message queue untuk menangani tugas yang memakan waktu
- Pemrosesan job latar belakang untuk operasi non-real-time

## 6. Integrasi Pihak Ketiga

### 6.1 Firebase Authentication
- Implementasi alur registrasi dan login pengguna
- Autentikasi berbasis token untuk permintaan API

### 6.2 Firebase Cloud Messaging
- Pengaturan untuk mengirim notifikasi push ke perangkat mobile
- Implementasi topik notifikasi untuk peran pengguna yang berbeda

### 6.3 Google Maps API
- Integrasi untuk menampilkan lokasi toko dan rute pengiriman
- Geocoding untuk validasi alamat dan perhitungan jarak

### 6.4 Gateway Pembayaran (Implementasi Masa Depan)
- Rencana integrasi untuk Midtrans atau Xendit
- Penanganan callback pembayaran dan pembaruan status transaksi

## 7. Strategi Pengujian

### 7.1 Pengujian Unit
- Backend: Jest untuk komponen Node.js
- Frontend: Jest dan React Testing Library untuk komponen React Native

### 7.2 Pengujian Integrasi
- Pengujian API menggunakan tools seperti Postman atau Supertest
- Pengujian integrasi database

### 7.3 Pengujian End-to-End
- Cypress atau Detox untuk pengujian aplikasi mobile
- Pengujian berbasis skenario yang mencakup alur utama pengguna

### 7.4 Pengujian Performa
- Load testing menggunakan tools seperti Apache JMeter
- Optimisasi waktu respons API dan query database

### 7.5 Pengujian Keamanan
- Penetration testing untuk mengidentifikasi kerentanan
- Audit keamanan dan code review secara berkala

Spesifikasi teknis ini memberikan dasar untuk pengembangan Antarkanma.my.id. Spesifikasi ini harus ditinjau dan diperbarui secara berkala seiring dengan kemajuan proyek dan evolusi kebutuhan.
