# Dokumen Perencanaan Proyek Antarkanma

## 1. Tujuan Proyek
- Mengembangkan aplikasi layanan pesan antar makanan dan barang multi-merchant
- Memfasilitasi koneksi antara pelanggan, pemilik usaha, dan kurir di 3 kecamatan target (Segeri, Ma'rang, Mandalle)
- Meningkatkan perekonomian lokal dengan mempromosikan usaha kuliner setempat

## 2. Ruang Lingkup Proyek
- Pengembangan backend API untuk 4 jenis pengguna: pelanggan, pemilik usaha, kurir, dan admin
- Pengembangan aplikasi mobile (Flutter) sebagai client
- Implementasi fitur utama: pemesanan, pembayaran (COD), pelacakan pesanan, manajemen pesanan
- Cakupan geografis: Kecamatan Segeri, Ma'rang, dan Mandalle, Kabupaten Pangkep
- Rencana ekspansi ke tingkat kabupaten setelah evaluasi keberhasilan

## 3. Teknologi Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 11 (PHP 8.4) |
| Admin Panel | Filament 3.2 |
| Authentication | Laravel Sanctum + Jetstream |
| Database | MySQL 8 |
| Cache & Session | Redis (Predis) |
| Push Notification | Firebase Cloud Messaging (kreait/laravel-firebase) |
| Object Storage | AWS S3 Compatible (IDCloudHost IS3) |
| Server | Laravel Octane |
| Deployment | Docker + Nginx + Cloudflare Tunnel |
| Mobile App | Flutter (client terpisah) |

## 4. Fitur MVP (Minimum Viable Product)

### Must-Have (Sudah Diimplementasi ✅)
- ✅ Registrasi dan login (pelanggan, merchant, kurir)
- ✅ Manajemen profil pengguna
- ✅ CRUD produk untuk merchant (termasuk galeri gambar, varian)
- ✅ Pencarian produk & kategori
- ✅ Sistem pemesanan multi-merchant
- ✅ Transaksi & pembayaran (COD/manual)
- ✅ Manajemen kurir (registrasi, wallet, statistik)
- ✅ Tracking status order & delivery
- ✅ Notifikasi push via Firebase Cloud Messaging
- ✅ Rating & ulasan produk
- ✅ Multi-alamat pengguna
- ✅ Dashboard admin via Filament
- ✅ Perhitungan ongkos kirim
- ✅ Health check endpoint

### Should-Have (Belum Diimplementasi)
- ⬜ Integrasi payment gateway (Midtrans/Xendit)
- ⬜ Automated testing coverage yang memadai
- ⬜ Real-time tracking GPS kurir
- ⬜ Sistem chat antara user-merchant-kurir
- ⬜ Laporan analitik untuk merchant

### Could-Have (Rencana Masa Depan)
- ⬜ Sistem loyalitas / poin reward
- ⬜ Program referral
- ⬜ Promo & voucher
- ⬜ Integrasi media sosial
- ⬜ Multi-bahasa
- ⬜ Scheduled delivery

## 5. Timeline & Jadwal Pengembangan

Alokasi waktu: 4 jam per hari

| Fase | Durasi | Deskripsi |
|------|--------|-----------|
| Persiapan & Perencanaan | 2 minggu | Finalisasi desain, arsitektur, teknologi |
| Pengembangan Backend | 6 minggu | API, database, logika bisnis, auth |
| Pengembangan Frontend | 6 minggu | Komponen UI, halaman utama, fitur pemesanan |
| Integrasi & Testing | 4 minggu | Integrasi frontend-backend, pengujian |
| Optimisasi & Launch | 2 minggu | Performa, UX, persiapan peluncuran |
| **Total** | **20 minggu** | **5 bulan** |

## 6. Keamanan

### Yang Sudah Diimplementasi
- Laravel Sanctum (API token-based auth)
- Jetstream (session management)
- Role-based access: USER, MERCHANT, COURIER, ADMIN
- HTTPS via Cloudflare
- Password hashing (bcrypt)
- Eloquent ORM (mencegah SQL injection)
- Throttle middleware (rate limiting)

### Yang Perlu Ditambahkan
- Audit keamanan berkala
- API rate limiting per-endpoint yang lebih granular
- Input validation review (Form Request di semua endpoint)
- Logging untuk aktivitas mencurigakan

## 7. Strategi Monetisasi

1. **Biaya Layanan per Transaksi**: Rp 2.000 per transaksi
2. **Target Pendapatan**: Rp 3.000.000/bulan setelah 6 bulan (50 transaksi/hari)
3. **Rencana Masa Depan**:
   - Langganan premium untuk merchant (analitik lanjutan, prioritas pencarian)
   - Slot promosi berbayar untuk merchant
   - Pengiriman ekspres dengan biaya tambahan
   - Program loyalitas dengan poin reward

## 8. Key Performance Indicators (KPI)

| KPI | Target |
|-----|--------|
| Pengguna aktif bulanan (MAU) | +20% setiap bulan (6 bulan pertama) |
| Retensi pengguna (30 hari) | 60% |
| Transaksi per hari | 50 (dalam 3 bulan pertama) |
| Rating aplikasi (Play Store) | 4.5/5 |
| Waktu pengiriman | 90% dalam 45 menit |
| Mitra usaha | 50 dalam 3 bulan pertama |
| Tingkat konversi | 5% |
| Uptime aplikasi | 99.9% |

## 9. Risiko dan Mitigasi

| Risiko | Mitigasi |
|--------|----------|
| Adopsi lambat oleh pengguna | Pemasaran agresif, program insentif pengguna awal |
| Kurangnya partisipasi merchant | Sosialisasi intensif, periode tanpa komisi untuk early adopters |
| Masalah teknis saat launch | Pengujian menyeluruh, monitoring 24/7 saat soft launch |
| Kesulitan merekrut kurir | Kompensasi kompetitif, program referral kurir |
| Keterbatasan dana | Prioritas MVP, manfaatkan tools open-source/freemium |
| Persaingan | Fokus diferensiasi lokal, bangun loyalitas komunitas |
| Keterlambatan jadwal | Milestone mingguan, evaluasi berkala, prioritas fitur inti |

## 10. Strategi Pemasaran

1. **Media Sosial**: Akun resmi di Instagram, Facebook, WA Group
2. **Kemitraan Lokal**: Ajak merchant kuliner lokal bergabung dengan insentif
3. **Program Referral**: Reward untuk pengguna yang mengajak teman
4. **Event Launching**: Acara peluncuran di setiap kecamatan target
5. **ASO**: Optimasi deskripsi dan keyword di Play Store

## 11. Infrastruktur & Deployment

### Environment
| Environment | Platform | Konfigurasi |
|-------------|----------|-------------|
| Development | Laragon / `php artisan serve` | `docker-compose.yml` |
| Development (Docker) | Laptop | `docker-compose.laptop.yml` |
| Production | VPS | `docker-compose.vps.yml` + Nginx LB |

### Load Balancing Strategy
- Write operations (POST/PUT/DELETE) → 100% VPS
- Read operations (GET) → 75% VPS, 25% Laptop (jika aktif)
- Admin routes → 100% VPS
- Health check setiap 5 detik, fallback otomatis

### Data Layer
- MySQL Master (VPS) + Slave (Laptop) dengan GTID replication
- Redis Master (VPS) + Slave (Laptop)

## 12. Rencana Pengembangan Jangka Panjang

1. **Pembaruan Berkala**: Minor setiap 2 minggu, mayor setiap 3 bulan
2. **Payment Gateway**: Integrasi Midtrans/Xendit
3. **Real-time Tracking**: GPS tracking untuk kurir
4. **Chat System**: Komunikasi user-merchant-kurir
5. **Analytics Dashboard**: Insight penjualan untuk merchant
6. **Ekspansi**: Evaluasi ekspansi ke kabupaten lain setelah 1 tahun

## 13. Kebutuhan Sumber Daya Manusia

| Peran | Jumlah | Tanggung Jawab |
|-------|--------|----------------|
| Project Manager | 1 | Keseluruhan proyek |
| Backend Developer | 1-2 | API, database, server |
| Mobile Developer | 1-2 | Aplikasi mobile (Flutter) |
| UI/UX Designer | 1 | Desain antarmuka |
| QA Tester | 1 | Pengujian kualitas |
| Customer Support | 1-2 | Penanganan masalah pengguna |

## 14. Kepatuhan Regulasi

1. **UU ITE & UU PDP**: Kepatuhan terhadap regulasi e-commerce dan perlindungan data pribadi
2. **Kebijakan Privasi**: Dokumen kebijakan privasi dan syarat penggunaan
3. **Persetujuan Pengguna**: Checkbox consent saat pendaftaran
4. **Keamanan Data**: HTTPS, enkripsi database, hak akses minimal
5. **Izin Usaha**: Konsultasi dengan pemerintah setempat untuk persyaratan
6. **Audit Mandiri**: Self-audit kepatuhan triwulanan

---

*Catatan: Dokumen ini bersifat hidup dan akan diperbarui seiring perkembangan proyek.*
