Tentu, saya akan membuatkan Use Case Diagram berdasarkan database yang telah kita bahas untuk aplikasi Antarkanma. Berikut adalah Use Case Diagram yang menggambarkan interaksi antara aktor dan sistem:


Verify

Open In Editor
Edit
Copy code
+---------------------------------------------------+
|                    Antarkanma                     |
|                                                   |
|  +------------+        +-----------------------+  |
|  | Pelanggan  |        |    Pemilik Usaha      |  |
|  +------------+        +-----------------------+  |
|    |                    |                         |
|    | - Registrasi       | - Registrasi            |
|    | - Login            | - Login                 |
|    | - Cari Produk      | - Kelola Profil Usaha   |
|    | - Pesan Makanan    | - Tambah Produk         |
|    | - Bayar Pesanan    | - Edit Produk           |
|    | - Lacak Pesanan    | - Hapus Produk          |
|    | - Beri Rating      | - Lihat Pesanan Masuk   |
|    | - Kelola Profil    | - Konfirmasi Pesanan    |
|    | - Lihat Riwayat    |                         |
|    |   Pesanan          |                         |
|    |                    |                         |
|    |                    |                         |
|  +------------+        +-----------------------+  |
|  |   Kurir    |        |        Admin          |  |
|  +------------+        +-----------------------+  |
|    |                    |                         |
|    | - Registrasi       | - Login                 |
|    | - Login            | - Kelola Pengguna       |
|    | - Terima Pesanan   | - Kelola Merchant       |
|    | - Update Status    | - Kelola Produk         |
|    |   Pengiriman       | - Kelola Pesanan        |
|    | - Selesaikan       | - Kelola Pembayaran     |
|    |   Pengiriman       | - Lihat Laporan         |
|    |                    | - Kelola Sistem         |
|    |                    |                         |
+---------------------------------------------------+
Penjelasan Use Case Diagram:

Pelanggan:

Dapat melakukan registrasi dan login
Mencari produk
Memesan makanan
Melakukan pembayaran
Melacak pesanan
Memberikan rating
Mengelola profil
Melihat riwayat pesanan
Pemilik Usaha (Merchant):

Dapat melakukan registrasi dan login
Mengelola profil usaha
Menambah, mengedit, dan menghapus produk
Melihat pesanan yang masuk
Mengonfirmasi pesanan
Kurir:

Dapat melakukan registrasi dan login
Menerima pesanan untuk diantar
Mengupdate status pengiriman
Menyelesaikan pengiriman
Admin:

Dapat login ke sistem admin
Mengelola pengguna (pelanggan, pemilik usaha, kurir)
Mengelola merchant
Mengelola produk
Mengelola pesanan
Mengelola pembayaran
Melihat laporan
Mengelola sistem secara keseluruhan
Use Case Diagram ini memberikan gambaran umum tentang fungsi-fungsi utama yang tersedia dalam aplikasi Antarkanma untuk setiap jenis pengguna. Diagram ini dapat membantu dalam memahami interaksi antara pengguna dan sistem, serta fitur-fitur yang perlu dikembangkan dalam aplikasi.
