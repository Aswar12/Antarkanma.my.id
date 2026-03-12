# 📋 AntarkanMa — MASTERPLAN

> **⚠️ RULE:** Setiap AI agent WAJIB update file ini setelah menyelesaikan task. Lihat [`.agents/workflows/update-masterplan.md`](.agents/workflows/update-masterplan.md)
>
> **📚 Dokumentasi Lengkap:** [`docs/AntarkanMa/README.md`](docs/AntarkanMa/README.md)
> **🧪 Test Data:** [`docs/TEST_DATA.md`](docs/TEST_DATA.md)
> **📦 Archive:** [`docs/ARCHIVE.md`](docs/ARCHIVE.md)
> **🚀 AI Startup:** [`docs/QUICKSTART.md`](docs/QUICKSTART.md)

---

## 🎯 Status Project

**Last Updated:** 12 Maret 2026 (Order ID Copy-to-Clipboard ✅ + Cart Sync Verified ✅ + Stock Management Removed)
**Status:** 99% MVP Complete ✅
**Target Soft Launch:** Mei 2026

| Component | Status | Notes |
|-----------|--------|-------|
| Backend (Laravel) | ✅ 100% | All endpoints complete + Dual QRIS + Cart Sync |
| Merchant App | ⚠️ **85%** | **Audit complete** - 2 critical gaps (stock removed) |
| Courier App | ✅ **98%** | **Audit complete** - External Navigation + Order ID Copy! |
| Customer App | ✅ **96%** | **Cart Sync Verified!** - Cross-device sync ready + Order ID Copy |
| Admin Panel | ✅ **100%** | Cart Analytics page added |
| Documentation | ✅ 100% | Dual QRIS docs added |
| Testing | ⚠️ 5% | Needs work |

> **📊 Customer App Audit:** See `docs/CUSTOMER-APP-AUDIT.md`
> **📊 Merchant App Audit:** See `docs/MERCHANT-APP-AUDIT.md`
> **📊 Courier App Audit:** See `docs/COURIER-APP-AUDIT.md`
> **📊 Consolidated Summary:** See `docs/AUDIT-SUMMARY-ALL-APPS.md`

---

## 🔥 PRIORITAS MINGGU INI

*Fokus: 8-14 Maret 2026*

### **🆕 SERVICE FEE CONFIGURATION & TOP MERCHANTS (NEW!)**

| ID | Task | App | Priority | Status |
|----|------|-----|----------|--------|
| **SF-11** | Migration: Create service_fee_settings table | Backend | 🔴 High | ✅ DONE |
| **SF-12** | Model: ServiceFeeSetting with helper methods | Backend | 🔴 High | ✅ DONE |
| **SF-13** | Admin Panel: ServiceFeeSetting Filament Resource | Backend | 🔴 High | ✅ DONE |
| **SF-14** | Seeder: Initial service fee setting | Backend | 🔴 High | ✅ DONE |
| **TM-14** | API: Top merchants endpoint with statistics | Backend | 🟡 Medium | ✅ DONE |
| **TM-15** | Route: /api/merchants/top endpoint | Backend | 🟡 Medium | ✅ DONE |

> **📚 Features Implemented:**
> 
> **1. Configurable Service Fee:**
> - Admin dapat ubah service fee kapan saja via Admin Panel
> - Default: Rp 500 per transaksi
> - Audit trail: Siapa yang ubah, kapan, dan notes
> - Table: `service_fee_settings`
> - Model: `ServiceFeeSetting` with `getCurrentServiceFee()` helper
> - Admin Panel: `/admin/service-fee-settings`
>
> **2. Top Merchants Statistics:**
> - Endpoint: `GET /api/merchants/top?period=week|month|all_time&limit=10`
> - Ranking berdasarkan jumlah order (PAID only)
> - Stats: total_orders, total_revenue, average_rating
> - Badge: 🥇 #1, 🥈 #2, 🥉 #3, Top N
> - Cache: 5 menit untuk performance
>
> **💡 Usage Example:**
> ```bash
> # Get top merchants this week
> GET /api/merchants/top?period=week&limit=10
> 
> # Get top merchants this month
> GET /api/merchants/top?period=month&limit=5
> 
> # Get all-time top merchants
> GET /api/merchants/top?period=all_time&limit=20
> ```
>
> **📊 Response Example:**
> ```json
> {
>   "code": 200,
>   "message": "Top merchants by orders (week) retrieved successfully",
>   "data": [
>     {
>       "id": 1,
>       "name": "Koneksi Rasa",
>       "rank": 1,
>       "badge": "🥇 #1 Top Merchant",
>       "total_orders": 45,
>       "total_revenue": 2250000,
>       "average_rating": 4.8,
>       "total_products": 12,
>       "logo": "https://...",
>       "address": "...",
>       "latitude": -5.123,
>       "longitude": 119.456
>     }
>   ]
> }
> ```

### **🆕 SERVICE FEE IMPLEMENTATION**

| ID | Task | App | Priority | Status |
|----|------|-----|----------|--------|
| **SF-01** | Migration: Add service_fee fields to orders | Backend | 🔴 High | ✅ **DONE** |
| **SF-02** | Create withdrawals table | Backend | 🔴 High | ✅ **DONE** |
| **SF-03** | Create wallet_transactions table | Backend | 🔴 High | ✅ **DONE** |
| **SF-04** | Update OsrmService calculation | Backend | 🔴 High | ✅ **DONE** |
| **SF-05** | Update TransactionController | Backend | 🔴 High | ✅ **DONE** |
| **SF-06** | Create WithdrawalController | Backend | 🔴 High | ✅ **DONE** |
| **SF-07** | Auto fee deduction on completeOrder | Backend | 🔴 High | ✅ **DONE** |
| **SF-08** | Customer App: Checkout UI update | Customer | 🔴 High | ✅ **DONE** |
| **SF-09** | Courier App: Order detail UI update | Courier | 🔴 High | ✅ **DONE** |
| **SF-10** | Admin Panel: WithdrawalResource | Backend | 🔴 High | ✅ **DONE** |
| **SF-11** | Courier App: Withdrawal page | Courier | 🔴 High | ✅ **DONE** |

> **📚 Documentation:** [`docs/AntarkanMa/business/service-fee-model.md`](docs/AntarkanMa/business/service-fee-model.md)
>
> **💰 Revenue Model (Updated 12 Mar 2026):**
>
> | Source | Amount | Who Pays | Notes |
> |--------|--------|----------|-------|
> | **Service Fee** | Rp 500/**transaksi** | Customer | **NEW: Sekali per transaksi, bukan per order!** |
> | **Platform Fee** | 10% dari base ongkir | Courier | Auto-deducted per order |
> | **Merchant Commission** | 0% | Merchant | **GRATIS** - No commission |
> | **Withdrawal Fee** | Rp 1.000 | Courier | Per withdrawal request |
>
> **💡 Multi-Merchant Savings:**
> - Single merchant: Service Fee Rp 500
> - 3 merchants in 1 transaction: Service Fee Rp 500 saja! (HEMAT Rp 1.000!)
> - Customer hanya bayar SEKALI, tidak peduli berapa merchant
>
> **💸 Withdrawal Rules:**
> - Minimum Withdraw: **Rp 50.000**
> - Processing: Manual via Admin (24h SLA)
> - Payment Method: Bank transfer
> - Status Flow: PENDING → APPROVED → PROCESSING → COMPLETED
>
> **📊 Example Transaction:**
>
> **Single Merchant:**
> ```
> Customer Pays: Rp 57.500
> ├─ Makanan: Rp 50.000 → Merchant (100%)
> ├─ Base Ongkir: Rp 7.000
> │   ├─ Courier Net: Rp 6.300 (after 10% platform fee)
> │   └─ Platform: Rp 700 (10% fee)
> └─ Service Fee: Rp 500 → Platform
>
> Platform Revenue: Rp 1.200/transaction (Rp 700 + Rp 500)
> ```
>
> **Multi-Merchant (3 merchants in 1 transaction):**
> ```
> Customer Pays: Rp 84.500
> ├─ Merchant 1: Rp 30.000 → Merchant 1 (100%)
> ├─ Merchant 2: Rp 25.000 → Merchant 2 (100%)
> ├─ Merchant 3: Rp 20.000 → Merchant 3 (100%)
> ├─ Base Ongkir: Rp 9.000 (optimized route)
> │   ├─ Courier Net: Rp 8.100 (after 10% platform fee)
> │   └─ Platform: Rp 900 (10% fee)
> └─ Service Fee: Rp 500 → Platform (ONLY ONCE!)
>
> Platform Revenue: Rp 1.400/transaction (Rp 900 + Rp 500)
> 💡 Customer HEMAT Rp 1.000 vs 3 separate transactions!
> ```

> **✅ Service Fee Implementation Complete (11 Maret 2026, Updated 12 Maret 2026):**
>
> **Backend:**
> - ✅ WithdrawalResource with admin approval workflow (approve, reject, mark processing, mark completed)
> - ✅ WithdrawalController with API endpoints (index, store, show)
> - ✅ Auto fee deduction on order complete (COD & Online payments)
> - ✅ Wallet transaction logging for all movements
> - ✅ Configurable service fee via ServiceFeeSetting model
>
> **Courier App:**
> - ✅ Order detail page with fee breakdown (Base Ongkir, Platform Fee, Courier Earning)
> - ✅ Withdrawal page with bank account form
> - ✅ Updated withdrawal API to use new endpoints
>
> **Customer App:**
> - ✅ Checkout page with service fee breakdown
> - ✅ Service fee info card explaining the Rp 500 fee
>
> **API Endpoints:**
> ```bash
> GET  /api/courier/wallet/withdrawals       # Get withdrawal history
> POST /api/courier/wallet/withdrawals       # Submit withdrawal request
> GET  /api/courier/wallet/withdrawals/{id}  # Get withdrawal detail
> ```
>
> **Admin Panel:**
> - Access: `/admin/withdrawals`
> - Features: View, Approve, Reject, Mark Processing, Mark Completed
> - Navigation badge shows pending withdrawal count

### **🆕 DUAL QRIS PAYMENT SYSTEM (NEW!)**

| ID | Task | App | Priority | Status |
|----|------|-----|----------|--------|
| **DQ-01** | Migration: Add dual QRIS fields | Backend | 🔴 High | ✅ DONE |
| **DQ-02** | Update Transaction model | Backend | 🔴 High | ✅ DONE |
| **DQ-03** | Update TransactionController | Backend | 🔴 High | ✅ DONE |
| **DQ-04** | Create PaymentController | Backend | 🔴 High | ✅ DONE |
| **DQ-05** | Update CourierController | Backend | 🔴 High | ✅ DONE |
| **DQ-06** | Add API routes | Backend | 🔴 High | ✅ DONE |
| **DQ-07** | Update feature checklist | Docs | 🔴 High | ✅ DONE |
| **DQ-08** | Create documentation | Docs | 🔴 High | ✅ DONE |
| **DQ-09** | Customer App: Dual QRIS UI | Customer | ⏳ Pending |
| **DQ-10** | Customer App: Upload payment proof | Customer | ⏳ Pending |

> **📚 Documentation:** [`docs/AntarkanMa/business/dual-qris-payment-system.md`](docs/AntarkanMa/business/dual-qris-payment-system.md)
>
> **💡 Konsep:** Customer bayar terpisah via 2 QRIS:
> - **QRIS Merchant:** Untuk makanan (langsung ke merchant)
> - **QRIS Platform:** Untuk ongkir + service fee (langsung ke platform)
>
> **✅ Keuntungan:**
> - NO payment gateway cost (save Rp 900k/bulan!)
> - NO settlement ribet (merchant dapat langsung)
> - Platform TIDAK pegang uang merchant
> - Customer bayar 2x TAPI digital & tracked
>
> **📊 Payment Flow:**
> ```
> Customer → Merchant QRIS: Rp 50.000 (direct)
> Customer → Platform QRIS: Rp 7.500 (direct)
> Platform → Courier wallet: +Rp 6.300 (auto-credit)
> Platform keep: Rp 700 (fee revenue)
> ```

### **Existing Priorities**

| ID | Task | App | Priority | Status |
|----|------|-----|----------|--------|
| **CA-02** | Cart sync API | Backend + Customer | 🔴 High | ✅ **VERIFIED** - Complete |
| **MA-01** | QRIS upload implementation | Merchant | 🔴 High | ✅ **VERIFIED** - Complete |
| ~~MA-02~~ | ~~Stock management fix~~ | Backend + Merchant | 🔴 High | ❌ **REMOVED** - Not implementing |
| **CoA-01** | Delivery proof capture | Courier | 🔴 High | ⏳ Pending |
| **CoA-02** | External navigation | Courier | 🟡 Medium | ✅ **DONE** |
| **CA-03** | Error boundary handling | Mobile | 🟡 Medium | ⏳ Pending |
| **F-07** | Final E2E testing | All | 🟡 Medium | ⏳ Pending |
| **UX-01** | Order ID copy-to-clipboard | All Apps | 🟢 Low | ✅ **DONE** |

> **Note:**
> - CA = Customer App Audit (docs/CUSTOMER-APP-AUDIT.md)
> - MA = Merchant App Audit (docs/MERCHANT-APP-AUDIT.md)
> - CoA = Courier App Audit (docs/COURIER-APP-AUDIT.md)

> **✅ CA-02 Cart Sync Complete:**
> - Backend: CartSyncController with 6 endpoints (get, sync, update, remove, clear, checkout)
> - Model: CartSync with user/product/merchant relationships
> - Migration: cart_syncs table with proper indexes
> - Customer App: CartController integrated with CartSyncService
> - Features: Cross-device sync, auto-sync on add/remove, server-side persistence
> - API: `/api/cart/*` routes complete
> - Admin: CartAnalytics page for abandoned cart tracking

> **❌ MA-02 Stock Management - Removed:**
> - Decision: Tidak akan implement stock management untuk merchant
> - Reason: Fokus pada simplicity, merchant akan manage stock secara manual
> - Updated: 12 Maret 2026

> **✅ CoA-02 External Navigation Complete:**
> - Added Google Maps navigation button to Courier App
> - Navigate to customer location from order card
> - Navigate to merchant location from pick-up section
> - Uses `url_launcher` package (already in pubspec.yaml)
> - Opens Google Maps app with turn-by-turn directions
> - File: `mobile/courier/lib/app/modules/courier/views/order_page.dart`

> **✅ UX-01 Order ID Copy-to-Clipboard Complete:**
> - **Merchant App:** Order detail page - tap order ID to copy
> - **Courier App:** Order card header - tap order ID to copy
> - **Customer App:** Order card header - tap order ID to copy
> - Features:
>   - Visual feedback with snackbar/toast notification
>   - Copy icon displayed next to order ID
>   - Consistent UX across all 3 apps
>   - Files modified:
>     - `mobile/merchant/.../order_detail_page.dart`
>     - `mobile/courier/.../order_page.dart`
>     - `mobile/customer/.../order_card.dart`

---

## 🆕 **TABLE MANAGEMENT & SELF CHECKOUT** (NEW!)

> **📚 Context:** Hybrid system untuk handle 2 tipe merchant:
> - **PAY_FIRST**: Bayar di awal (Fast Food, Cafe) → Auto-release table
> - **PAY_LAST**: Bayar di akhir (Restaurant) → Manual release table
> 
> **⏱️ Duration Function:** Buffer time estimasi customer selesai makan (bukan limit/usir customer). 
> Default 60 menit = rata-rata makan 45 min + buffer 15 min.
> Prevents: (1) Release terlalu cepat → konflik 2 customer, (2) Release terlalu lama → loss revenue

### **📊 Feature Overview**

| Feature | Description | Priority | Status |
|---------|-------------|----------|--------|
| **Merchant Config** | Settings: PAY_FIRST vs PAY_LAST | 🔴 High | ⏳ Pending |
| **Table Status** | AVAILABLE → OCCUPIED → RELEASED | 🔴 High | ⏳ Pending |
| **Auto-Release Timer** | PAY_FIRST: Auto after duration | 🟡 Medium | ⏳ Pending |
| **Manual Release** | PAY_LAST: Staff release button | 🔴 High | ⏳ Pending |
| **Extend Duration** | +15, +30 min untuk PAY_LAST | 🟡 Medium | ⏳ Pending |
| **Self Checkout** | QR code → customer bayar sendiri | 🟡 Medium | ⏳ Pending |
| **Duration Settings** | Config per merchant (30-120 min) | 🟡 Medium | ⏳ Pending |

### **Implementation Tasks**

| ID | Task | App | Priority | Status | Est. Time |
|----|------|-----|----------|--------|-----------|
| **TM-01** | Migration: Add merchant config fields | Backend | 🔴 High | ⏳ Pending | 2 jam |
| **TM-02** | Migration: Add table tracking fields | Backend | 🔴 High | ⏳ Pending | 2 jam |
| **TM-03** | Create TableManagementService | Backend | 🔴 High | ⏳ Pending | 4 jam |
| **TM-04** | Update PosTransactionController | Backend | 🔴 High | ⏳ Pending | 3 jam |
| **TM-05** | Add scheduler for auto-release | Backend | 🟡 Medium | ⏳ Pending | 2 jam |
| **TM-06** | Merchant App: Table settings UI | Merchant | 🔴 High | ⏳ Pending | 4 jam |
| **TM-07** | Merchant App: Table management UI | Merchant | 🔴 High | ⏳ Pending | 6 jam |
| **TM-08** | Merchant App: Release/Extend buttons | Merchant | 🔴 High | ⏳ Pending | 3 jam |
| **TM-09** | Customer App: Self checkout UI | Customer | 🟡 Medium | ⏳ Pending | 6 jam |
| **TM-10** | Generate QR code per table | Backend | 🟡 Medium | ⏳ Pending | 3 jam |
| **TM-11** | Activity log for table release | Backend | 🟡 Medium | ⏳ Pending | 2 jam |
| **TM-12** | Notification: Table ready to release | Merchant | 🟡 Medium | ⏳ Pending | 3 jam |

**Total Estimated Time:** ~40 jam (~1 week)

---

## 📋 BACKLOG (Prioritas Menurun)

### PRIORITAS 2 — Penting

| ID | Fitur | App | Status |
|----|-------|-----|--------|
| ~~T-03~~ | ~~Chat message bug fixes~~ | All | ✅ **SELESAI** |
| ~~C-10~~ | ~~Image upload compression~~ | Backend | ✅ **SELESAI** |
| ~~Ku-05~~ | ~~Chat dari delivery page~~ | Courier | ✅ **SELESAI** |
| ~~T-02~~ | ~~Chat pagination~~ | All | ✅ **SELESAI** |
| ~~B-06~~ | ~~Share location GPS~~ | All | ✅ **SELESAI** |
| ~~MA-05~~ | ~~Login/Register error handling~~ | Merchant | ✅ **SELESAI** |
| ~~CA-06~~ | ~~Login/Register error handling~~ | Customer | ✅ **SELESAI** |
| ~~CoA-06~~ | ~~Login/Register error handling~~ | Courier | ✅ **SELESAI** |
| ~~CA-07~~ | ~~Address selection back button UI~~ | Customer | ✅ **SELESAI** |
| ~~CA-08~~ | ~~Order page UI redesign~~ | Customer | ✅ **SELESAI** |
| ~~CA-09~~ | ~~Courier chat button logic~~ | Customer | ✅ **SELESAI** |
| ~~CA-10~~ | ~~Order buttons spacing & layout~~ | Customer | ✅ **SELESAI** |
| ~~CA-11~~ | ~~Merchant & Product image aspect ratio~~ | Customer | ✅ **SELESAI** |
| ~~CA-12~~ | ~~Merchant carousel image fit cover~~ | Customer | ✅ **SELESAI** |
| ~~CA-13~~ | ~~Add "Lihat Semua" for products & merchants~~ | Customer | ✅ **SELESAI** |
| C-11 | Error boundary handling | Mobile | ⏳ Pending |
| F-08 | Offline mode support | Mobile | ⏳ Pending |

### PRIORITAS 3 — Sebelum Launch

- [ ] **Testing Infrastructure** — PHPUnit setup (40 jam)
- [ ] **Security Hardening** — Rate limiting, 2FA admin (20 jam)
- [ ] **Redis Cache** — Product caching (10 jam)
- [ ] **DB Indexing** — Performance optimization (2 jam)

### PRIORITAS 4 — Post-Launch

- [ ] Unit test Flutter apps
- [ ] Deep linking notifications
- [ ] Multi-language support (i18n)
- [ ] AI delivery optimization
- [ ] Promo code / referral system

---

## 🧪 Quick Test Info

**Password semua akun:** `antarkanma123`

| Role | Email | App |
|------|-------|-----|
| Customer | aswarthedoctor@gmail.com | Customer App |
| Merchant | koneksi@rasa.com | Merchant App |
| Courier | kurir@antarkanma.com | Courier App |
| Admin | antarkanma@gmail.com | Web Admin |

📚 **Lengkap:** [`docs/TEST_DATA.md`](docs/TEST_DATA.md)

---

## 🤖 AI Agent Workflow

### Sebelum Coding:
1. ✅ Baca file ini (prioritas minggu ini)
2. ✅ Cek `docs/AntarkanMa/ai-memory-context.md`
3. ✅ Pilih task dari tabel di atas

### Setelah Coding:
1. ✅ Update status task di tabel
2. ✅ Pindahkan yang selesai → `docs/ARCHIVE.md`
3. ✅ Update "Last Updated"
4. ✅ Commit dengan message jelas

### Commit Message Format:
```bash
✅ T-02: Chat pagination complete
🐛 C-08: Fix chat init bug
📝 Update MASTERPLAN.md
```

---

## 📊 Timeline

```
Maret 2026
├── Week 1 (1-7):   Bug fixes & stabilization
├── Week 2 (8-14):  Testing foundation
├── Week 3 (15-21): Security
└── Week 4 (22-31): Pre-launch prep

Mei 2026 → 🚀 Soft Launch
```

---

## 🔗 Quick Links

| Document | Description |
|----------|-------------|
| [📚 Documentation Hub](docs/AntarkanMa/README.md) | Complete documentation |
| [📋 Feature Checklist](docs/AntarkanMa/feature-checklist.md) | **Complete feature tracking** - 17 groups, 200+ features |
| [🧪 Test Data](docs/TEST_DATA.md) | Test accounts & scenarios |
| [📦 Archive](docs/ARCHIVE.md) | Completed tasks history |
| [🤖 AI Memory Context](docs/AntarkanMa/ai-memory-context.md) | AI session context |
| [📋 Active Backlog](docs/AntarkanMa/active-backlog.md) | Detailed backlog |
| [📈 Progress Log](docs/AntarkanMa/progress-log.md) | Development log |
| [🚀 QUICKSTART](docs/QUICKSTART.md) | AI session startup guide |
| [🐙 GitHub Project](https://github.com/users/Aswar12/projects/2) | **Project Board** - Track issues |
| [📝 Create Issues](docs/GITHUB-ISSUES-CREATION.md) | Guide to create GitHub issues |

---

## 🐙 GitHub Project Board

**Project:** [AntarkanMa](https://github.com/users/Aswar12/projects/2)

### Current Status

| Column | Count | Issues |
|--------|-------|--------|
| 🔴 **In Progress** | 1 | T-03 |
| 🟡 **Todo** | 7 | C-10, F-07, C-11, F-08, +3 infra |
| ✅ **Done** | 3 | T-02, Ku-05, B-06 |

### Quick Actions
- [View Project Board](https://github.com/users/Aswar12/projects/2)
- [Create New Issue](https://github.com/Aswar12/Antarkanma.my.id/issues/new)
- [Issue Creation Guide](docs/GITHUB-ISSUES-CREATION.md)

---

**💡 Tip:** Untuk detail lengkap, lihat [`docs/AntarkanMa/`](docs/AntarkanMa/)
