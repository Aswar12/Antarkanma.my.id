# Penanganan Kasus Khusus Sistem Pembayaran Antarkanma

## 1. Kasus Pembayaran Makanan

### A. Transfer Kurang/Lebih
1. Transfer Kurang:
   - Merchant menolak verifikasi
   - Sistem kirim notifikasi ke customer
   - Customer harus transfer ulang penuh
   - Order diberi status PAYMENT_REJECTED

2. Transfer Lebih:
   - Merchant verifikasi pembayaran
   - Kelebihan dicatat di sistem
   - Customer dapat refund atau store credit
   - Merchant transfer balik kelebihan

### B. Bukti Transfer Invalid
1. Foto Tidak Jelas:
   - Merchant reject verifikasi
   - Customer upload ulang
   - Timer pembayaran tetap berjalan
   - 3x gagal = order cancelled

2. Nomor Referensi Tidak Match:
   - Merchant minta klarifikasi
   - Customer kirim bukti tambahan
   - Admin dapat bantu verifikasi
   - Log semua komunikasi

## 2. Kasus Ongkir COD

### A. Customer Tidak Ada Uang Cash
1. Solusi yang Ditawarkan:
   - Transfer ke rekening kurir
   - Bayar pakai e-wallet ke kurir
   - Reschedule delivery
   - Cancel order (last resort)

2. Prosedur Kurir:
   - Tahan makanan
   - Hubungi customer service
   - Tunggu konfirmasi pembayaran
   - Maximum wait time: 10 menit

### B. Uang Pas/Kembalian
1. Kurir Wajib Siapkan:
   - Uang kembalian minimal Rp 100.000
   - Pecahan kecil untuk kembalian
   - E-wallet untuk alternatif

2. Jika Tidak Ada Kembalian:
   - Kurir cari tempat tukar uang
   - Customer boleh bayar via transfer
   - Catat di sistem sebagai incident

## 3. Kasus Fee Platform

### A. Kurir Belum Setor Fee
1. Tahap Reminder:
   - H+0: Notifikasi otomatis
   - H+1: SMS & telepon
   - H+2: Warning suspension
   - H+3: Akun disuspend

2. Proses Collection:
   - Tim collection follow up
   - Payment plan jika diperlukan
   - Blacklist jika tidak kooperatif
   - Legal action untuk kasus besar

### B. Dispute Fee
1. Kurir Klaim Sudah Setor:
   - Cek bukti transfer
   - Cross check rekening
   - Review riwayat setoran
   - Validasi timestamp

2. Penyelesaian:
   - Mediasi dengan admin
   - Review bukti kedua pihak
   - Keputusan dalam 1x24 jam
   - Update status di sistem

## 4. Kasus Pembatalan

### A. Cancel Sebelum Pembayaran
1. Prosedur:
   - Order langsung dibatalkan
   - Tidak ada penalty
   - Reset kuota order
   - Notifikasi ke merchant

2. System Update:
   - Status: CANCELLED
   - Reason tracking
   - Free up resources
   - Update statistik

### B. Cancel Setelah Pembayaran
1. Sebelum Pickup:
   - Merchant refund full
   - Tidak ada biaya admin
   - Maximum refund: 1x24 jam
   - Track refund status

2. Setelah Pickup:
   - Merchant refund partial
   - Potong biaya operasional
   - Ongkir tetap dibayar
   - Fee platform tetap

## 5. Kasus Sistem Error

### A. Payment Gateway Down
1. Contingency Plan:
   - Switch ke manual transfer
   - Extend payment timer
   - Manual verification
   - Backup payment channel

2. Communication:
   - Notify all parties
   - Update status berkala
   - Provide alternative
   - ETA resolution

### B. App Error
1. Verification Issues:
   - Screenshot bukti
   - Manual logging
   - Backup verification
   - Reconcile later

2. Status Update Issues:
   - Manual tracking
   - WhatsApp backup
   - Batch update
   - Post-resolution check

## 6. Prosedur Rekonsiliasi

### A. Daily Reconciliation
1. Fee Platform:
   - Match order vs setoran
   - Flag discrepancies
   - Follow up missing fee
   - Update collection status

2. Payment Verification:
   - Cross check all transfers
   - Match dengan order
   - Verify amounts
   - Clear pending status

### B. Weekly Review
1. Performance Metrics:
   - Collection rate
   - Verification speed
   - Issue resolution
   - Customer satisfaction

2. Issue Analysis:
   - Pattern identification
   - Root cause analysis
   - Solution implementation
   - System improvement
