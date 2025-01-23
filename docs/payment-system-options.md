# Opsi Sistem Pembayaran Antarkanma

## Opsi 1: Pembayaran via ShopeePay Antarkanma

### Alur Pembayaran:
1. Customer transfer ke ShopeePay Antarkanma
2. Admin Antarkanma transfer ke merchant
3. Kurir collect ongkir secara COD

#### Kelebihan:
- Jaminan pembayaran ke merchant
- Kontrol penuh atas transaksi
- Mudah tracking pembayaran
- Bisa implementasi sistem refund

#### Kekurangan:
- Admin perlu transfer manual ke merchant
- Delay settlement ke merchant
- Perlu modal awal untuk float
- Beban operasional lebih tinggi

### Biaya Operasional:
- Fee ShopeePay: 0.7%
- Biaya transfer ke merchant
- Biaya admin untuk handling

## Opsi 2: Pembayaran Langsung ke Merchant + COD Ongkir

### Alur Pembayaran:
1. Customer transfer langsung ke rekening merchant
2. Merchant konfirmasi penerimaan pembayaran
3. Kurir collect ongkir (Rp 2.000 fee platform) secara COD

#### Kelebihan:
- Merchant terima pembayaran langsung
- Tidak perlu modal float
- Operasional lebih ringan
- Proses lebih cepat

#### Kekurangan:
- Sulit kontrol pembayaran
- Risiko dispute pembayaran
- Tracking pembayaran lebih sulit
- Sistem refund lebih kompleks

### Biaya Operasional:
- Tidak ada fee payment gateway
- Hanya biaya sistem

## Rekomendasi Implementasi

### Tahap Awal:
Gunakan Opsi 2 (Transfer ke Merchant + COD Ongkir) karena:
1. Modal awal minimal
2. Operasional lebih sederhana
3. Merchant lebih puas (terima uang langsung)
4. Fokus pada pengumpulan fee platform

### Proses Pembayaran:
1. Customer order makanan
2. Sistem hitung total:
   - Harga makanan (transfer ke merchant)
   - Ongkir (bayar COD ke kurir)

3. Flow Pembayaran Makanan:
   - Customer transfer ke rekening merchant
   - Customer upload bukti transfer
   - Merchant verifikasi pembayaran
   - Order diproses setelah verifikasi

4. Flow Ongkir (COD):
   - Kurir collect ongkir saat delivery
   - Kurir setor fee platform (Rp 2.000)
   - Sistem catat setoran kurir

### Sistem Verifikasi:
1. Verifikasi Pembayaran Merchant:
   - Upload bukti transfer
   - Konfirmasi merchant
   - Timestamp verifikasi
   - Status pembayaran

2. Verifikasi Ongkir:
   - Kurir konfirmasi terima ongkir
   - System track fee platform
   - Reminder setoran kurir
   - Status setoran fee

### Monitoring:
1. Dashboard Admin:
   - Status pembayaran merchant
   - Status ongkir COD
   - Track fee platform
   - Laporan harian/mingguan

2. Dashboard Merchant:
   - Riwayat pembayaran
   - Status order
   - Total pendapatan
   - Rekap transaksi

3. Dashboard Kurir:
   - Ongkir terkumpul
   - Fee platform terhutang
   - Status setoran
   - Riwayat transaksi
