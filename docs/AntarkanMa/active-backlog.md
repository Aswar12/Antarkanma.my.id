# Backlog Aktif — Antarkanma

Status: `⬜ Belum` `🔄 Sedang` `✅ Selesai` `⏸️ Ditunda`

_Terakhir diperbarui: 24 Februari 2026, 04:27 WITA_

---

## 🔴 Sprint Saat Ini: Order Status Flow & Dokumentasi SDLC

### Milestone: Order Flow Zero Miss-Communication (Target: Maret 2026)

| # | Task | Status | Catatan |
|---|---|---|---|
| 1 | Fix bug `CourierController@approveTransaction` reset status | ✅ | Bug fix: tidak lagi reset ke PROCESSING (24 Feb) |
| 2 | Tambah kolom `courier_status` di tabel transactions | ✅ | Migration berhasil (24 Feb) |
| 3 | Tambah endpoint `arrive-merchant` | ✅ | `POST /courier/transactions/{id}/arrive-merchant` |
| 4 | Tambah endpoint `arrive-customer` | ✅ | `POST /courier/transactions/{id}/arrive-customer` |
| 5 | Tambah endpoint `pickup` per-order | ✅ | `POST /courier/orders/{id}/pickup` |
| 6 | Tambah endpoint `complete` per-order + auto-complete Transaction | ✅ | `POST /courier/orders/{id}/complete` |
| 7 | Update `courier_provider.dart` (Courier App) | ✅ | 4 method baru |
| 8 | Update `courier_order_controller.dart` + FCM listener | ✅ | Auto-refresh tanpa pull |
| 9 | Update `order_page.dart` UI Courier App | ✅ | Tombol aksi kontekstual per courier_status |
| 10 | Update `merchant_order_controller.dart` FCM handlers | ✅ | 4 handler baru |
| 11 | Rewrite semua docs SDLC (ERD, DFD, Class, Sequence) | ✅ | Sinkron dengan implementasi aktual |
| 12 | Customer App: Tracking real-time UI (status timeline) | ⬜ | Stepper berdasarkan courier_status |
| 13 | Customer App: Auto-refresh via FCM | ⬜ | |
| 14 | Courier App: Test end-to-end happy path | ⬜ | Lihat panduan di walkthrough.md |
| 15 | Courier App: Test multi-merchant partial pickup | ⬜ | |

---

## 🟡 Backlog Backend

| # | Task | Status | Catatan |
|---|---|---|---|
| B1 | Automated testing — API auth endpoints | ⬜ | |
| B2 | Automated testing — order & transaction | ⬜ | |
| B3 | Merchant fee Rp 1.000/order implementation | ⬜ | Formula sudah di docs |
| B4 | Standardisasi error handling semua controller | ⬜ | |
| B5 | Input validation review (Form Requests) | ⬜ | |
| B6 | Courier transfer order ke kurir lain | ⬜ | Jika kurir tidak bisa lanjut |
| B7 | Auto-cancel timeout via cron job | ⏸️ | Dimatikan sementara untuk hybrid flow |
| B8 | SLA reminder: notif ulang ke merchant jika 5 menit belum approve | ⬜ | |

---

## 🟢 Backlog Flutter Apps

| # | Task | Status | Catatan |
|---|---|---|---|
| F1 | Pastikan auth flow berfungsi (register/login/logout) | ⬜ | |
| F2 | Pastikan checkout & order berfungsi end-to-end | ⬜ | |
| F3 | Customer App: Tampil langkah status di detail pesanan | ⬜ | Berbasis courier_status |
| F4 | Customer App: Live tracking kurir di peta | ⬜ | Butuh GPS kurir real-time |
| F5 | Merchant App: Orders page redesign | ⬜ | Filter lebih jelas |
| F6 | Courier App: ETA tampil ke merchant dan customer | ⬜ | |

---

## 🔵 Backlog Fitur Baru

| # | Task | Status | Catatan |
|---|---|---|---|
| N1 | Payment gateway integration (Midtrans/Xendit) | ⬜ | Q3 2026 |
| N2 | Chat in-app (customer ↔ kurir / merchant) | 🔄 | UI done, koneksi bermasalah di emulator |
| N3 | Redis caching untuk produk populer | ⬜ | |
| N4 | Upload foto bukti pengantaran | ⬜ | |
| N5 | Rating merchant & kurir setelah order selesai | ⬜ | Field `rating` sudah ada di DB |
| N6 | Slot promosi merchant (featured listing) | ⬜ | Fase 2 |

---

## ✅ Setup & Infra (Selesai)

| # | Task | Status |
|---|---|---|
| S1 | Clone 3 Flutter repos ke `mobile/` | ✅ |
| S2 | Setup database lokal + migrate | ✅ |
| S3 | Install Android Studio + SDK + licenses | ✅ |
| S4 | ADB port forwarding (HP ↔ laptop) | ✅ |
| S5 | Enable Windows Developer Mode | ✅ |
| S6 | Update Flutter config → localhost:8000 | ✅ |
| S7 | `flutter pub get` semua apps | ✅ |
| S8 | Fix Auto-Login Merchant App | ✅ |
| S9 | Redesign Dashboard Merchant App | ✅ |
| S10 | Fix Print Service (Thermal Printer) | ✅ |
| S11 | Fix Splash Screen animation Customer App | ✅ |
| S12 | Universal Search & Carousel Home Page | ✅ |

---

## 📋 Referensi Dokumen

| Dokumen | Path | Deskripsi |
|---|---|---|
| ERD | [architecture/erd-diagram.md](architecture/erd-diagram.md) | Schema database aktual |
| DFD Level 0 | [architecture/dfd-level-0.md](architecture/dfd-level-0.md) | Context diagram |
| DFD Level 1 | [architecture/dfd-level-1.md](architecture/dfd-level-1.md) | Detail proses utama |
| Class Diagram | [architecture/class-diagram.md](architecture/class-diagram.md) | Backend + Flutter classes |
| Sequence Diagram | [architecture/sequence-diagram.md](architecture/sequence-diagram.md) | Full order flow |
| Order Flow | [transaction-order-flow.md](transaction-order-flow.md) | Status state machine |
| Data Flow | [architecture/data-flow-design.md](architecture/data-flow-design.md) | Arsitektur & API summary |
| Business Model | [company/business-model.md](company/business-model.md) | Model bisnis & revenue |
