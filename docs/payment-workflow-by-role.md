# Alur Kerja Sistem Pembayaran Per Role Antarkanma

## 1. Customer Flow

### A. Pemesanan
1. Buka aplikasi Antarkanma
2. Pilih merchant dan menu
3. Konfirmasi pesanan:
   - Lihat total harga makanan
   - Lihat ongkir berdasarkan jarak
   - Konfirmasi alamat pengantaran

### B. Pembayaran Makanan
1. Terima info pembayaran:
   - Nomor rekening merchant
   - Total yang harus ditransfer
   - Batas waktu pembayaran (30 menit)

2. Proses transfer:
   - Transfer ke rekening merchant
   - Simpan bukti transfer
   - Upload bukti ke aplikasi

3. Menunggu verifikasi:
   - Status: WAITING_VERIFICATION
   - Notifikasi jika ada masalah
   - Notifikasi jika terverifikasi

### C. Penerimaan Order
1. Tracking status pengiriman
2. Siapkan uang ongkir (cash)
3. Terima pesanan:
   - Cek kelengkapan
   - Bayar ongkir ke kurir
   - Konfirmasi penerimaan

## 2. Merchant Flow

### A. Penerimaan Order
1. Terima notifikasi order baru
2. Review pesanan:
   - Detail menu
   - Harga total
   - Alamat pengiriman

### B. Verifikasi Pembayaran
1. Terima notifikasi bukti transfer
2. Cek rekening bank:
   - Match nominal
   - Match waktu transfer
   - Match nomor referensi

3. Proses di aplikasi:
   - Konfirmasi pembayaran diterima
   - Update status ke PAYMENT_VERIFIED
   - Atau reject jika ada masalah

### C. Proses Order
1. Siapkan pesanan
2. Update status pesanan
3. Serahkan ke kurir
4. Konfirmasi pickup

## 3. Kurir Flow

### A. Penerimaan Order
1. Terima notifikasi order
2. Review detail:
   - Lokasi pickup
   - Lokasi delivery
   - Total ongkir
   - Fee platform (Rp 2.000)

### B. Pickup & Delivery
1. Pickup di merchant:
   - Konfirmasi pesanan lengkap
   - Update status PICKED_UP
   - Mulai perjalanan

2. Delivery ke customer:
   - Antarkan pesanan
   - Terima pembayaran ongkir
   - Update status DELIVERED
   - Upload foto serah terima

### C. Setoran Fee
1. Di akhir hari:
   - Cek total fee platform
   - Transfer ke rekening Antarkanma
   - Upload bukti transfer
   - Update status setoran

## 4. Admin Flow

### A. Monitoring Transaksi
1. Dashboard monitoring:
   - Order aktif
   - Status pembayaran
   - Status delivery
   - Issue tracking

2. Issue handling:
   - Payment verification issues
   - Delivery issues
   - Customer complaints
   - System errors

### B. Fee Collection
1. Monitoring setoran:
   - Track fee per kurir
   - Status setoran
   - Follow up keterlambatan
   - Update status collection

2. Rekonsiliasi:
   - Daily reconciliation
   - Report generation
   - Issue resolution
   - Performance tracking

## 5. Customer Service Flow

### A. Payment Support
1. Handle payment issues:
   - Transfer gagal
   - Verifikasi tertunda
   - Bukti transfer invalid
   - Refund requests

2. Koordinasi:
   - Dengan merchant
   - Dengan bank
   - Dengan customer
   - Dengan admin

### B. Delivery Support
1. Handle delivery issues:
   - Keterlambatan
   - Masalah pembayaran COD
   - Kurir tidak responsive
   - Order tracking

2. Issue Resolution:
   - Coordinate with courier
   - Update customer
   - Track resolution
   - Follow up completion

## 6. System Automation

### A. Notifications
1. Automatic alerts:
   - Payment reminders
   - Verification status
   - Delivery updates
   - Fee collection reminders

2. Status updates:
   - Order status
   - Payment status
   - Delivery status
   - Collection status

### B. Monitoring
1. System health:
   - Payment gateway
   - App performance
   - Database status
   - API status

2. Business metrics:
   - Transaction volume
   - Success rates
   - Issue frequency
   - Resolution times
