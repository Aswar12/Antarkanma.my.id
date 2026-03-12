# Backlog Aktif — Antarkanma

Status: `⬜ Belum` `🔄 Sedang` `✅ Selesai` `⏸️ Ditunda`

_Terakhir diperbarui: 10 Maret 2026, 15:30 WITA_

---

## 🔴 Sprint 12-13: Critical Fixes (27 Feb - 14 Mar 2026)

### C1: Missing Controllers

| # | Task | Status | Catatan |
|---|---|---|---|
| C1.1 | Create ManualOrderController | ✅ | Implemented (27 Feb) |
| C1.2 | Create ChatController | ✅ | Implemented (27 Feb) - 6 jam |

### C3: Error Response Standardization

| # | Task | Status | Catatan |
|---|---|---|---|
| C3.1 | Create ApiResponseTrait | ⬜ | 2 jam |
| C3.2 | Update all controllers | ⬜ | 6 jam |

### C5: Environment Configuration

| # | Task | Status | Catatan |
|---|---|---|---|
| C5.1 | Update .env.example | ⬜ | 2 jam |

### D: Documentation

| # | Task | Status | Catatan |
|---|---|---|---|
| D1 | Update Sequence Diagram | ⬜ | 2 jam |
| D2 | Update API Reference | ⬜ | 2 jam |
| D4 | Create Deployment Checklist | ✅ | Created (27 Feb) |
| D5 | Create Troubleshooting Guide | ✅ | Created (27 Feb) |
| D6 | Create API Testing Checklist | ✅ | Created (27 Feb) |
| D7 | Create Missing Controllers Guide | ✅ | Created (27 Feb) |
| D8 | Create Comprehensive Plan | ✅ | Created (27 Feb) |
| D9 | Create Sprint 12-13 Plan | ✅ | Created (27 Feb) |
| D10 | Update Progress Log (Session 13) | ✅ | Created (27 Feb) |

---

## 🟡 Backlog Backend

| # | Task | Status | Catatan |
|---|---|---|---|
| B1 | Automated testing — API auth endpoints | ⬜ | |
| B2 | Automated testing — order & transaction | ⬜ | |
| ~~B3~~ | ~~Merchant fee Rp 1.000/order implementation~~ | ✅ **REPLACED** | ~~Formula sudah di docs~~ → **See SF-04 to SF-10 in MASTERPLAN.md** |
| B4 | Standardisasi error handling semua controller | ⬜ | |
| B5 | Input validation review (Form Requests) | ⬜ | |
| B6 | Courier transfer order ke kurir lain | ⬜ | Jika kurir tidak bisa lanjut |
| B7 | Auto-cancel timeout via cron job | ⏸️ | Dimatikan sementara untuk hybrid flow |
| B8 | SLA reminder: notif ulang ke merchant jika 5 menit belum approve | ⬜ | |

> **⚠️ Update 10 Mar 2026:** B3 diganti dengan **Service Fee Model** baru. Lihat [`docs/AntarkanMa/business/service-fee-model.md`](service-fee-model.md)
> 
> **Revenue Model Baru:**
> - Service Fee: Rp 500/transaksi (Customer)
> - Platform Fee: 10% dari base ongkir (Courier)
> - Merchant Commission: 0% (GRATIS)
> - Withdrawal Fee: Rp 1.000
> 
> **Implementation tasks:** SF-04, SF-05, SF-06, SF-07, SF-10 (see MASTERPLAN.md)

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
