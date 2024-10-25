User                    Sistem Antarkanma           Merchant                Kurir
 |                              |                       |                     |
 |   1. Cari Produk             |                       |                     |
 |----------------------------->|                       |                     |
 |   2. Tampilkan Hasil         |                       |                     |
 |<-----------------------------|                       |                     |
 |   3. Pilih Produk            |                       |                     |
 |----------------------------->|                       |                     |
 |   4. Tambah ke Keranjang     |                       |                     |
 |----------------------------->|                       |                     |
 |   5. Tampilkan Keranjang     |                       |                     |
 |<-----------------------------|                       |                     |
 |   6. Checkout                |                       |                     |
 |----------------------------->|                       |                     |
 |   7. Minta Data Pengiriman   |                       |                     |
 |<-----------------------------|                       |                     |
 |   8. Input Data Pengiriman   |                       |                     |
 |----------------------------->|                       |                     |
 |   9. Tampilkan Ringkasan     |                       |                     |
 |<-----------------------------|                       |                     |
 |   10. Konfirmasi Pesanan     |                       |                     |
 |----------------------------->|                       |                     |
 |                              |   11. Kirim Pesanan   |                     |
 |                              |---------------------->|                     |
 |                              |   12. Konfirmasi      |                     |
 |                              |<----------------------|                     |
 |   13. Tampilkan Status       |                       |                     |
 |<-----------------------------|                       |                     |
 |                              |                       |   14. Proses Pesanan|
 |                              |                       |-------------------->|
 |                              |                       |   15. Siap Kirim    |
 |                              |                       |<--------------------|
 |                              |   16. Update Status   |                     |
 |                              |<----------------------|                     |
 |                              |                       |   17. Assign Kurir  |
 |                              |-------------------------------------->|     |
 |                              |                       |   18. Terima Tugas  |
 |                              |<--------------------------------------|     |
 |   19. Update Status Pengiriman                       |                     |
 |<-----------------------------|                       |                     |
 |                              |                       |                     |
 |   20. Pesanan Diterima       |                       |                     |
 |----------------------------->|                       |                     |
 |                              |   21. Update Status   |                     |
 |                              |---------------------->|                     |
 |                              |                       |   22. Selesai       |
 |                              |-------------------------------------->|     |
 |   23. Minta Review           |                       |                     |
 |<-----------------------------|                       |                     |
 |   24. Kirim Review           |                       |                     |
 |----------------------------->|                       |                     |
 |                              |                       |                     |

 Penjelasan langkah-langkah:

1-5: User mencari dan memilih produk, lalu menambahkannya ke keranjang. 
6-10: User melakukan checkout, memasukkan data pengiriman, dan mengonfirmasi pesanan. 11-13: Sistem mengirim pesanan ke Merchant dan mengonfirmasi ke User.
14-16: Merchant memproses pesanan dan memperbarui statusnya di sistem. 
17-18: Sistem menugaskan Kurir untuk pengiriman, dan Kurir menerima tugas tersebut. 19: Sistem memperbarui status pengiriman kepada User. 
20: User menerima pesanan dan mengonfirmasi penerimaan di sistem. 
21-22: Sistem memperbarui status pesanan ke Merchant dan menandai tugas Kurir sebagai selesai. 
23-24: Sistem meminta User untuk memberikan review, dan User mengirimkan reviewnya.
Penjelasan tambahan:

Interaksi Pengguna:

Diagram ini menunjukkan bagaimana User berinteraksi dengan sistem mulai dari pencarian produk hingga pemberian review.
Setiap langkah melibatkan pertukaran informasi antara User dan sistem.
Proses di Belakang Layar:

Setelah User mengonfirmasi pesanan, ada serangkaian proses yang terjadi di belakang layar melibatkan Merchant dan Kurir.
Sistem bertindak sebagai perantara, mengoordinasikan komunikasi antara semua pihak.
Status Updates:

Sepanjang proses, sistem terus memperbarui status pesanan dan menginformasikannya kepada User.
Ini mencakup konfirmasi pesanan, status pemrosesan oleh Merchant, dan status pengiriman oleh Kurir.
Peran Merchant:

Merchant menerima pesanan, memprosesnya, dan memperbarui statusnya di sistem.
Ini menunjukkan integrasi yang erat antara sistem Antarkanma dan operasi Merchant.
Penugasan dan Peran Kurir:

Sistem menugaskan Kurir secara otomatis setelah Merchant memproses pesanan.
Kurir berinteraksi dengan sistem untuk menerima tugas dan memperbarui status pengiriman.
Konfirmasi dan Review:

Setelah pesanan diterima, User diminta untuk mengonfirmasi penerimaan.
Sistem kemudian meminta User untuk memberikan review, yang penting untuk umpan balik dan peningkatan layanan.
Alur Informasi:

Diagram ini menunjukkan bagaimana informasi mengalir antara berbagai aktor dalam sistem.
Sistem Antarkanma bertindak sebagai hub pusat, mengelola dan mendistribusikan informasi ke semua pihak yang terlibat.
Otomatisasi:

Banyak langkah dalam proses ini bisa diotomatisasi, seperti penugasan kurir dan permintaan review.
Ini membantu meningkatkan efisiensi dan konsistensi layanan.
Transparansi:

Sequence diagram ini menunjukkan tingkat transparansi yang tinggi dalam proses, di mana User dapat melacak status pesanan mereka di setiap tahap.
Skalabilitas:

Meskipun diagram ini menunjukkan proses dasar, struktur ini memungkinkan untuk penambahan langkah-langkah tambahan atau fitur baru di masa depan.