# Analisis Kebutuhan dan Fitur Utama Aplikasi Layanan Pesan Antar

## Pengguna Aplikasi:
1. Pelanggan
2. Pemilik Usaha/Kedai
3. Kurir

## Fitur Utama:

### Untuk Pelanggan:
- Registrasi dan login
- Pencarian kedai/usaha berdasarkan lokasi atau jenis makanan
- Melihat menu dan harga dari kedai/usaha
- Membuat pesanan
- Pembayaran (metode pembayaran perlu ditentukan)
- Pelacakan pesanan
- Penilaian dan ulasan untuk kedai dan kurir

### Untuk Pemilik Usaha/Kedai:
- Registrasi dan login
- Manajemen profil usaha
- Manajemen menu dan harga
- Menerima dan mengelola pesanan
- Melihat riwayat pesanan dan pendapatan
- Melihat ulasan pelanggan

### Untuk Kurir:
- Registrasi dan login
- Menerima dan mengelola tugas pengantaran
- Navigasi ke lokasi pengambilan dan pengantaran
- Konfirmasi pengambilan dan pengantaran pesanan
- Melihat riwayat pengantaran dan pendapatan

### Fitur Umum:
- Notifikasi real-time untuk semua pengguna
- Chat in-app antara pelanggan, pemilik usaha, dan kurir
- Sistem rating dan review
- Pembatasan layanan untuk 3 kecamatan yang ditentukan: Segeri, Ma'rang, dan Mandalle

## Pertimbangan Teknis:
- Penggunaan GPS untuk pelacakan lokasi
- Integrasi dengan layanan peta untuk navigasi
- Sistem pembayaran yang aman
- Backend yang dapat menangani transaksi real-time
- Database yang dapat menyimpan data pengguna, pesanan, dan riwayat transaksi

## Pertimbangan UX/UI:
- Desain yang menarik dan mudah digunakan untuk target pasar anak muda
- Antarmuka yang intuitif untuk semua jenis pengguna
- Responsif dan cepat untuk penggunaan di perangkat mobile

## Keamanan:
- Enkripsi data pengguna
- Verifikasi identitas untuk kurir dan pemilik usaha
- Perlindungan terhadap penipuan dan penyalahgunaan sistem

## Skalabilitas:
- Desain yang memungkinkan penambahan kecamatan di masa depan
- Arsitektur yang dapat menangani peningkatan jumlah pengguna dan transaksi
# Analisis Kebutuhan Antarkanma.my.id

## 1. Kebutuhan Fungsional

### 1.1 Pelanggan
- Registrasi dan login
- Pencarian kedai/usaha berdasarkan lokasi atau jenis makanan
- Melihat menu dan harga dari kedai/usaha
- Membuat pesanan
- Pembayaran COD
- Pelacakan pesanan real-time
- Penilaian dan ulasan untuk kedai dan kurir
- Riwayat pesanan

### 1.2 Pemilik Usaha/Kedai
- Registrasi dan login
- Manajemen profil usaha
- Manajemen menu dan harga
- Menerima dan mengelola pesanan
- Melihat riwayat pesanan dan pendapatan
- Melihat ulasan pelanggan
- Dashboard analitik sederhana

### 1.3 Kurir
- Registrasi dan login
- Menerima dan mengelola tugas pengantaran
- Navigasi ke lokasi pengambilan dan pengantaran
- Konfirmasi pengambilan dan pengantaran pesanan
- Melihat riwayat pengantaran dan pendapatan
- Manajemen deposit

### 1.4 Umum
- Notifikasi real-time untuk semua pengguna
- Chat in-app antara pelanggan, pemilik usaha, dan kurir
- Sistem rating dan review

## 2. Kebutuhan Non-Fungsional

### 2.1 Performa
- Waktu respons aplikasi < 2 detik
- Mampu menangani minimal 1000 pengguna aktif bersamaan

### 2.2 Keamanan
- Enkripsi end-to-end untuk data sensitif
- Autentikasi dua faktor untuk semua jenis pengguna
- Perlindungan terhadap serangan umum (SQL injection, XSS, CSRF)

### 2.3 Ketersediaan
- Uptime 99.9%
- Backup data harian

### 2.4 Skalabilitas
- Arsitektur yang dapat menangani peningkatan jumlah pengguna dan transaksi
- Desain yang memungkinkan penambahan kecamatan di masa depan

### 2.5 Usability
- Antarmuka pengguna yang intuitif dan responsif
- Waktu pembelajaran aplikasi < 30 menit untuk pengguna baru
- Mendukung bahasa Indonesia dan bahasa daerah setempat

### 2.6 Kompatibilitas
- Kompatibel dengan Android 7.0+ dan iOS 12.0+
- Responsif pada berbagai ukuran layar smartphone

## 3. Batasan Sistem
- Layanan terbatas pada 3 kecamatan: Segeri, Ma'rang, dan Mandalle
- Pembayaran hanya menggunakan metode COD
- Pengantaran makanan maksimal dalam radius 10 km

## 4. Asumsi dan Dependensi
- Pengguna memiliki akses internet yang stabil
- Ketersediaan GPS pada perangkat pengguna
- Kerjasama dengan pihak kedai/usaha lokal
- Kepatuhan terhadap regulasi pemerintah setempat terkait layanan pesan antar makanan

Catatan: Dokumen ini akan diperbarui secara berkala sesuai dengan perkembangan proyek dan feedback dari stakeholder.
