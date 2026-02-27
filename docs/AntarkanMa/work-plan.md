# Rencana Kerja Proyek Antarkanma

## Status Saat Ini

Proyek Antarkanma sudah memiliki backend yang berfungsi dengan fitur utama (MVP) yang sudah diimplementasikan. Dokumen ini berisi rencana kerja terstruktur untuk perbaikan dan pengembangan lebih lanjut.

---

## Backlog (Diprioritaskan)

### 🔴 Prioritas Tinggi (Harus Segera)

| # | Item | Estimasi | Status |
|---|------|----------|--------|
| 1 | **Automated Testing** — Buat test untuk API endpoints kritis (auth, order, transaction) | 2 sprint | ⬜ |
| 2 | **Standardisasi Error Handling** — Review semua controller, pastikan error response konsisten | 1 sprint | ⬜ |
| 3 | **Input Validation Review** — Pastikan semua endpoint pakai Form Request validation | 1 sprint | ⬜ |
| 4 | **Payment Gateway Integration** — Integrasi Midtrans atau Xendit untuk pembayaran online | 2 sprint | ⬜ |

### 🟡 Prioritas Menengah (Setelah Fondasi Kuat)

| # | Item | Estimasi | Status |
|---|------|----------|--------|
| 5 | **Performance Optimization** — Implementasi Redis caching untuk query sering digunakan | 1 sprint | ⬜ |
| 6 | **Database Query Optimization** — Review N+1 query, tambah eager loading, optimize index | 1 sprint | ⬜ |
| 7 | **API Rate Limiting** — Konfigurasi rate limiting per-endpoint | 0.5 sprint | ⬜ |
| 8 | **Real-time GPS Tracking** — Tracking lokasi kurir real-time (broadcasting) | 2 sprint | ⬜ |
| 9 | **Chat System** — Fitur chat antar user-merchant-kurir | 2 sprint | ⬜ |
| 10 | **Analytics Dashboard Merchant** — Laporan penjualan & insight via API | 1 sprint | ⬜ |

### 🟢 Prioritas Rendah (Nice-to-Have)

| # | Item | Estimasi | Status |
|---|------|----------|--------|
| 11 | **Promo & Voucher System** — Fitur diskon dan voucher | 1 sprint | ⬜ |
| 12 | **Loyalty Points Enhancement** — Perluas fitur poin loyalitas | 1 sprint | ⬜ |
| 13 | **Scheduled Delivery** — Opsi pengiriman terjadwal | 1 sprint | ⬜ |
| 14 | **Multi-language Support** — Internasionalisasi API responses | 1 sprint | ⬜ |
| 15 | **Referral Program** — Sistem ajak teman dengan reward | 1 sprint | ⬜ |

---

## Sprint Plan

**Durasi Sprint**: 2 minggu
**Alokasi**: 4 jam per hari

### Sprint 1: Foundation & Quality (Minggu 1-2)

**Tujuan**: Memperkuat fondasi kode yang ada

| Task | Detail | Poin |
|------|--------|------|
| Setup PHPUnit test suite | Konfigurasi test database, factories, traits | 3 |
| Test: Authentication API | Register, login, logout, refresh token | 5 |
| Test: Product API | CRUD produk, search, filter | 5 |
| Review error handling | Audit semua controller untuk konsistensi response | 5 |
| **Total** | | **18** |

### Sprint 2: Testing & Validation (Minggu 3-4)

**Tujuan**: Menambah test coverage dan validasi input

| Task | Detail | Poin |
|------|--------|------|
| Test: Order & Transaction API | Create, list, status update, cancel | 8 |
| Test: Courier API | Transaksi kurir, wallet, statistik | 5 |
| Form Request validation | Buat/review Form Request di semua controller | 8 |
| **Total** | | **21** |

### Sprint 3: Payment Gateway (Minggu 5-6)

**Tujuan**: Integrasi pembayaran online

| Task | Detail | Poin |
|------|--------|------|
| Research & pilih payment gateway | Evaluasi Midtrans vs Xendit | 2 |
| Implementasi payment service | Service layer untuk payment processing | 8 |
| Update Transaction flow | Integrate payment ke flow transaksi | 5 |
| Webhook untuk payment callback | Handler untuk notifikasi pembayaran | 5 |
| Test payment flow | End-to-end testing payment | 3 |
| **Total** | | **23** |

### Sprint 4: Performance & Caching (Minggu 7-8)

**Tujuan**: Optimasi performa backend

| Task | Detail | Poin |
|------|--------|------|
| Redis caching untuk produk populer | Cache query produk yang sering diakses | 5 |
| Eager loading review | Fix N+1 query issues | 5 |
| Database indexing | Review dan tambah index yang dibutuhkan | 3 |
| API response caching | Cache response untuk public endpoints | 3 |
| Load testing | Setup basic load test, ukur baseline | 3 |
| **Total** | | **19** |

### Sprint 5-6: Real-time Features (Minggu 9-12)

**Tujuan**: Fitur real-time

| Task | Detail | Poin |
|------|--------|------|
| GPS tracking kurir | Broadcasting lokasi real-time | 8 |
| Chat system | Komunikasi user-merchant-kurir | 13 |
| Enhanced push notifications | Notifikasi lebih granular per event | 5 |
| **Total** | | **26** |

---

## Definition of Done (DoD)

Sebuah task dianggap "done" ketika:

1. ✅ Kode sudah di-push ke branch dan di-merge ke `main`
2. ✅ Semua existing test lulus (`php artisan test`)
3. ✅ Fitur baru memiliki test (minimal happy path + error case)
4. ✅ Input validation sudah ada di Form Request
5. ✅ Error response menggunakan format yang konsisten
6. ✅ Dokumentasi API diperbarui (jika ada endpoint baru)
7. ✅ Tidak ada error/warning di log saat operasi normal

---

## Area yang Sudah Diimplementasi (Referensi)

Fitur-fitur berikut sudah berfungsi dan tidak perlu dikerjakan ulang (kecuali ada perbaikan):

- ✅ User authentication (register, login, logout, refresh)
- ✅ User profile management (update, foto, toggle active)
- ✅ Merchant CRUD + logo
- ✅ Product CRUD + gallery + variants
- ✅ Product categories
- ✅ Product search & filtering
- ✅ Product reviews & ratings
- ✅ Order creation (multi-merchant)
- ✅ Order status management (process, ready, complete, cancel)
- ✅ Transaction management
- ✅ Courier management (register, wallet, statistics)
- ✅ Delivery assignment & tracking
- ✅ Shipping cost calculation
- ✅ User locations (multi-address, set default)
- ✅ FCM push notifications
- ✅ Health check endpoint
- ✅ Filament admin panel
- ✅ Docker deployment (multi-environment)
- ✅ Nginx load balancer
- ✅ Cloudflare Tunnel

---

*Rencana ini bersifat hidup dan akan diperbarui setiap akhir sprint berdasarkan progress dan feedback.*
