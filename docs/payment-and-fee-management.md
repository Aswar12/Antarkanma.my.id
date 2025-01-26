# Dokumentasi Sistem Pembayaran dan Pengelolaan Fee Antarkanma

## Sistem Pembayaran

### 1. Metode Pembayaran yang Tersedia

#### A. Transfer ke Rekening Kurir
- Customer transfer total biaya (makanan + ongkir) ke rekening kurir
- Kurir membayar ke merchant
- Fee platform dipotong dari saldo kurir

#### B. Cash on Delivery (COD)
- Customer membayar total biaya ke kurir
- Kurir membayar ke merchant
- Fee platform dipotong dari saldo kurir

#### C. QRIS (Tahap Selanjutnya)
- Pembayaran langsung ke rekening Antarkanma
- Fee QRIS 0.7% dari total transaksi
- Settlement otomatis H+1 ke merchant

### 2. Sistem Saldo Kurir

#### A. Mekanisme Topup
- Minimal topup: Rp 20.000
- Metode topup:
  * Transfer bank ke rekening Antarkanma
  * Upload bukti transfer
  * Verifikasi admin
  * Penambahan saldo otomatis

#### B. Pengelolaan Saldo
- Saldo minimal: Rp 20.000
- Pemotongan otomatis Rp 2.000/order
- Sistem blokir order jika saldo < minimal
- History pemotongan saldo tersedia

## Alur Transaksi

### 1. Proses Order (Transfer)
1. Customer membuat pesanan
2. Sistem menghitung total:
   - Harga makanan
   - Ongkos kirim (berdasarkan jarak)
3. Customer transfer total ke rekening kurir
4. Kurir verifikasi pembayaran masuk
5. Kurir mengambil dan membayar makanan
6. Kurir mengantar ke customer
7. Sistem otomatis potong fee dari saldo

### 2. Proses Order (COD)
1. Customer membuat pesanan
2. Kurir mengambil dan membayar makanan
3. Kurir mengantar ke customer
4. Customer membayar total biaya
5. Sistem otomatis potong fee dari saldo

## Pencatatan dan Pelaporan

### 1. Dashboard Kurir
- Saldo tersedia
- Riwayat transaksi
- History topup
- History pemotongan fee

### 2. Laporan Harian
- Total transaksi
- Total fee terkumpul
- Saldo per kurir
- Status topup

### 3. Laporan Mingguan
- Rekap transaksi per kurir
- Total fee terkumpul
- Analisa performa kurir
- Trend topup saldo

### 4. Laporan Bulanan
- Total pendapatan fee platform
- Perbandingan dengan target
- Analisa trend transaksi
- Rekomendasi penyesuaian sistem

## Penanganan Masalah

### 1. Saldo Tidak Mencukupi
- Notifikasi saldo menipis (< Rp 30.000)
- Reminder untuk topup
- Blokir order jika di bawah minimal
- Panduan cara topup

### 2. Verifikasi Topup
- Sistem verifikasi otomatis
- Backup verifikasi manual
- SLA verifikasi maksimal 1 jam
- Notifikasi status topup

### 3. Dispute Resolution
- Riwayat transaksi detail
- Bukti transfer/topup
- History pemotongan
- Proses mediasi jika ada selisih

## Target dan Monitoring

### 1. Target Minimal
- 3 transaksi per hari untuk BEP
- Total fee: Rp 6.000/hari
- Total fee: Rp 180.000/bulan
- Minimal saldo: Rp 20.000

### 2. Target Optimal
- 5 transaksi per hari
- Total fee: Rp 10.000/hari
- Total fee: Rp 300.000/bulan
- Saldo optimal: Rp 50.000

### 3. Monitoring Performa
- Tracking jumlah transaksi
- Monitoring saldo kurir
- Analisa trend topup
- Evaluasi sistem setiap bulan

### 4. Insentif Program
- Bonus untuk topup rutin
- Reward transaksi tinggi
- Program loyalitas kurir
- Cashback untuk topup nominal besar
