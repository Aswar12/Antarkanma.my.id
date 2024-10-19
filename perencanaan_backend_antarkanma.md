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
