# Dokumentasi Sistem Pembayaran dan Pengelolaan Fee Antarkanma

## Sistem Pembayaran

### 1. Metode Pembayaran yang Tersedia

#### A. Cash on Delivery (COD)
- Customer membayar langsung ke kurir
- Kurir menerima total biaya (makanan + ongkir)
- Kurir wajib setor fee platform (Rp 2.000/order)

#### B. QRIS (Tahap Selanjutnya)
- Pembayaran langsung ke rekening Antarkanma
- Fee QRIS 0.7% dari total transaksi
- Settlement otomatis H+1 ke merchant

### 2. Pengelolaan Fee Platform (COD)

#### A. Sistem Setoran Fee
- Fee tetap: Rp 2.000 per transaksi
- Periode setoran: Harian (setelah jam operasional)
- Metode setoran:
  * Transfer bank
  * E-wallet
  * Setor tunai (mitra collection point)

#### B. Monitoring Setoran
- Dashboard kurir menampilkan:
  * Total fee harian yang harus disetor
  * Riwayat setoran
  * Status setoran

## Alur Transaksi COD

### 1. Proses Order
1. Customer membuat pesanan
2. Sistem menghitung total:
   - Harga makanan
   - Ongkos kirim (berdasarkan jarak)
3. Kurir menerima order
4. Kurir mengambil makanan
5. Kurir mengantar ke customer
6. Customer membayar total biaya ke kurir

### 2. Proses Setoran Fee
1. Sistem mencatat fee per transaksi (Rp 2.000)
2. Di akhir hari:
   - Sistem menghitung total fee harian
   - Mengirim notifikasi jumlah yang harus disetor
3. Kurir melakukan setoran
4. Admin memverifikasi setoran
5. Status setoran diupdate di sistem

## Pencatatan dan Pelaporan

### 1. Laporan Harian
- Total transaksi
- Total fee yang harus diterima
- Status setoran per kurir
- Fee yang sudah diterima

### 2. Laporan Mingguan
- Rekap transaksi per kurir
- Total fee yang diterima
- Analisa performa setoran
- Identifikasi keterlambatan setoran

### 3. Laporan Bulanan
- Total pendapatan fee platform
- Perbandingan dengan biaya operasional
- Analisa trend transaksi
- Rekomendasi penyesuaian sistem

## Penanganan Masalah

### 1. Keterlambatan Setoran
- Notifikasi pengingat otomatis
- Batas waktu setoran hingga H+1
- Suspend akun jika belum setor H+1

### 2. Orderan Fiktif
- Verifikasi setiap transaksi
- Monitoring pola transaksi mencurigakan
- Sistem penalti untuk pelanggaran

### 3. Dispute Resolution
- Pencatatan riwayat setoran
- Bukti transfer/setoran
- Proses mediasi jika ada selisih

## Target dan Monitoring

### 1. Target Minimal
- 3 transaksi per hari untuk BEP
- Total fee: Rp 6.000/hari
- Total fee: Rp 180.000/bulan

### 2. Target Optimal
- 5 transaksi per hari
- Total fee: Rp 10.000/hari
- Total fee: Rp 300.000/bulan

### 3. Monitoring Performa
- Tracking jumlah transaksi harian
- Monitoring ketepatan setoran
- Analisa trend pendapatan
- Evaluasi sistem setiap bulan
