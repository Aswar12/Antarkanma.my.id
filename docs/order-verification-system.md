# Dokumentasi Sistem Verifikasi dan Pencegahan Order Fiktif Antarkanma

## Sistem Verifikasi Multi-Layer

### 1. Verifikasi Pengguna

#### A. Registrasi Customer
- Verifikasi nomor HP via OTP
- Email verification (opsional)
- Maksimal 2 akun per nomor HP
- Sistem deteksi multiple account

#### B. Registrasi Kurir
- Verifikasi KTP
- Verifikasi nomor HP
- Foto selfie dengan KTP
- Verifikasi alamat domisili

#### C. Registrasi Merchant
- Verifikasi lokasi fisik
- Foto tempat usaha
- Dokumen legalitas usaha
- Verifikasi rekening bank

### 2. Verifikasi Transaksi

#### A. Saat Pemesanan
- Validasi jarak merchant-customer
- Cek riwayat transaksi customer
- Batasan nilai minimum order
- Deteksi pola pemesanan mencurigakan

#### B. Saat Pengambilan Order
- Kurir wajib di lokasi merchant (GPS)
- Foto makanan/pesanan
- Konfirmasi merchant
- Timestamp pengambilan

#### C. Saat Pengantaran
- Tracking GPS kurir
- Foto serah terima
- Konfirmasi penerimaan customer
- Timestamp pengantaran

## Sistem Deteksi Order Fiktif

### 1. Parameter Deteksi
- Frekuensi order tidak wajar
- Pola waktu mencurigakan
- Lokasi pickup/delivery tidak sesuai
- Nilai transaksi tidak wajar

### 2. Monitoring Otomatis
- Flag transaksi mencurigakan
- Alert untuk admin
- Tracking GPS realtime
- Review transaksi berkala

### 3. Tindakan Preventif
- Batasan order per akun per hari
- Verifikasi tambahan untuk order mencurigakan
- Suspend akun yang terindikasi fraud
- Blacklist permanen untuk pelanggar berat

## Sistem Penalti

### 1. Pelanggaran Ringan
- Warning pertama
- Suspend 1-3 hari
- Verifikasi ulang akun

### 2. Pelanggaran Sedang
- Suspend 1 minggu
- Denda administratif
- Verifikasi ulang lengkap

### 3. Pelanggaran Berat
- Blacklist permanen
- Pelaporan ke pihak berwajib
- Blacklist nomor HP/device

## Monitoring dan Evaluasi

### 1. Monitoring Harian
- Review transaksi mencurigakan
- Verifikasi setoran kurir
- Analisa pola transaksi
- Tindak lanjut laporan

### 2. Evaluasi Mingguan
- Analisa trend pelanggaran
- Review efektivitas sistem
- Penyesuaian parameter deteksi
- Update blacklist

### 3. Laporan Bulanan
- Statistik pelanggaran
- Efektivitas pencegahan
- Rekomendasi improvement
- Cost analysis

## Prosedur Penanganan Order Fiktif

### 1. Deteksi Awal
1. Sistem mendeteksi anomali
2. Alert ke admin
3. Review cepat transaksi
4. Keputusan tindak lanjut

### 2. Investigasi
1. Pengumpulan bukti
2. Review riwayat transaksi
3. Analisa pola
4. Konfirmasi ke pihak terkait

### 3. Tindak Lanjut
1. Penentuan level pelanggaran
2. Eksekusi penalti
3. Dokumentasi kasus
4. Update sistem pencegahan

## Improvement Berkelanjutan

### 1. Review Sistem
- Evaluasi parameter deteksi
- Update metode verifikasi
- Peningkatan keamanan
- Optimasi proses

### 2. Training Tim
- Update prosedur verifikasi
- Pengenalan pola baru
- Handling case study
- Best practices

### 3. Teknologi
- Update sistem deteksi
- Improve tracking GPS
- Enhance security features
- Optimize monitoring tools
