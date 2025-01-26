Berikut adalah deskripsi tekstual dari DFD Level 0 untuk Antarkanma:


Verify

Open In Editor
Edit
Copy code
[User] <---> (Registrasi, Login, Data Profil)
       <---> (Pencarian Produk, Katalog)
       <---> (Pesanan, Pembayaran)
       <---> (Ulasan, Poin Loyalitas)
       <---> (Alamat Pengiriman)
             |
             |
             v
+----------------------------+
|                            |
|                            |
|        Sistem              |
|        Antarkanma          |
|                            |
|                            |
+----------------------------+
             ^
             |
             |
[Merchant] <---> (Registrasi, Login, Data Profil)
           <---> (Manajemen Produk)
           <---> (Pesanan Masuk, Proses Pesanan)
           <---> (Laporan Penjualan, Analitik)
             |
             |
             v
[Kurir] <---> (Login, Data Profil)
        <---> (Tugas Pengiriman)
        <---> (Update Status Pengiriman)
        <---> (Laporan Pengiriman)
             |
             |
             v
[Admin] <---> (Login, Manajemen Pengguna)
        <---> (Manajemen Sistem)
        <---> (Laporan dan Analitik)
        <---> (Konfigurasi Aplikasi)
Penjelasan:

User:

Mengirim data registrasi dan login
Menerima informasi profil
Mengirim permintaan pencarian dan menerima katalog produk
Mengirim pesanan dan data pembayaran
Menerima konfirmasi pesanan dan status pengiriman
Mengirim ulasan dan menerima informasi poin loyalitas
Mengirim dan menerima data alamat pengiriman
Merchant:

Mengirim data registrasi dan login
Menerima informasi profil
Mengirim dan menerima data produk
Menerima pesanan masuk dan mengirim status proses pesanan
Menerima laporan penjualan dan data analitik
Kurir:

Mengirim data login
Menerima informasi profil
Menerima tugas pengiriman
Mengirim update status pengiriman
Mengirim laporan pengiriman
Admin:

Mengirim data login
Mengelola data pengguna (User, Merchant, Kurir)
Mengelola konfigurasi sistem
Menerima laporan dan data analitik platform
Mengirim konfigurasi aplikasi
Sistem Antarkanma:

Menerima dan memproses semua input dari entitas eksternal
Menyimpan dan mengelola data
Menghasilkan output yang sesuai untuk setiap entitas