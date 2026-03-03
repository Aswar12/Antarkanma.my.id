# AntarkanMa — MASTERPLAN

> **⚠️ RULE: Setiap AI agent (Opus, Qwen, Claude, dll) yang mengerjakan kode di project ini WAJIB mengupdate file ini setelah menyelesaikan pekerjaan apapun. Lihat `.agents/workflows/update-masterplan.md` untuk instruksi lengkap.**

**Last Updated:** 4 Maret 2026
**Project Status:** 88% MVP ⬆️ (+3% from 85%)
**Target Soft Launch:** Mei 2026

---

## ✅ SELESAI

### Backend
- [x] Hapus debug file `db-query.php` dari root
- [x] Bersihkan 3 duplicate routes di `api.php`
- [x] Rate limiting `throttle:60,1` pada chat routes
- [x] Hapus unused `S3TestController` import
- [x] Hapus unused `order_model.dart` import di merchant_home_page
- [x] OrderItem routes ditambahkan (`orders/{id}/items`, `order-items/{id}`)
- [x] **OrderItemController enhanced** - Added order status validation, stock checking, auto recalculate total
- [x] **Order model** - Added `recalculateTotal()` and `canBeModified()` methods
- [x] **Product stock validation** - Prevent overselling on order item create/update
- [x] **Variant pricing** - Correct price handling for product variants in order items
- [x] **Chat system** - Complete ChatController with 6 methods, FCM integration
- [x] **Manual Order (Jastip)** - ManualOrderController for non-registered merchants
- [x] **Wallet Topup** - WalletTopupController + admin approval workflow
- [x] **QRIS Payment** - QrisController for QRIS code management
- [x] **Analytics Dashboard** - 7 widgets (Sales, Revenue, Top X, Peak Hours)
- [x] **App Settings** - AppSetting model + Filament page for QRIS/bank info
- [x] **Test coverage** - ChatTest (9 tests), ManualOrderTest (6 tests)
- [x] **Route fix** - Added `/merchants/{id}/orders` alias untuk Flutter compatibility
- [x] **Courier notifications** - Added FCM broadcast to all active couriers when new order created

### Courier App (Flutter)
- [x] **Route fix** - Added `/chat` route for bottom navigation (fixed Home→Profile→Chat bug)
- [x] **Routes updated** - Added ChatListPage route in app_pages.dart
- [x] **Bottom navigation** - Fixed navigation between Home, Orders, Chat, Profile tabs

### Customer App
- [x] 5 repository: merchant, product, order, user, courier
- [x] `loading_indicator.dart` widget
- [x] Tombol "Beri Ulasan" pada order COMPLETED/DELIVERED → ReviewPage

### Merchant App
- [x] 5 repository: merchant, product, order, user, courier
- [x] Fix semua lint errors (type mismatch)
- [x] Hapus dev artifacts (feature_plan.md, commit_message.txt)

### Courier App
- [x] Toggle Online/Offline → API `PUT /couriers/{id}`
- [x] Tombol "Tarik Dana" → bottom sheet withdraw
- [x] Riwayat Pengantaran page + route + navigation

- [x] **Courier app wallet balance fix** - Updated UserModel to handle `wallet_balance` and enabled periodic profile refresh in MainController.
- [x] **Cleanup** - Hapus .md redundan: ACTION_ITEMS, AUDIT_SUMMARY, DEEP_CODE_ANALYSIS, REPOSITORY_LAYER_AUDIT, UPDATE_AUDIT_REPORT

---

## 🔴 PRIORITAS 1 — Harus Sekarang

### [B-04] Pisahkan MerchantReview & CourierReview
Flutter mengirim ke `POST /transactions/{id}/review` tapi route belum ada.
- [ ] Migration: `merchant_reviews` (user_id, merchant_id, order_id, transaction_id, rating, comment)
- [ ] Migration: `courier_reviews` (user_id, courier_id, transaction_id, rating, note)
- [ ] Model: `MerchantReview.php`, `CourierReview.php`
- [ ] Relation: `reviews()` di `Merchant.php` dan `Courier.php`
- [ ] Controller: `TransactionReviewController.php` (submitReview, getReviewStatus, getMerchantReviews, getCourierReviews)
- [ ] Routes: 4 route baru di `api.php`

### [S-05] Hapus File Debug dari Root
- [ ] Hapus 11 file: `check_chat*.php`, `check_courier_fcm.php`, `check_customer_fcm.php`, `check_db.php`, `check_transaction5.php`, `delete_bad_chat.php`, `test_chat_api.php`, `test_chat_list.php`, `test_message_order.php`

### [Ku-03] Ganti Peta Statis di Courier Home
- [ ] Integrasi `flutter_map` + OpenStreetMap (atau Google Maps)
- [ ] Tampilkan rute merchant → customer

---

## 🟠 PRIORITAS 2 — Fitur Penting

| ID | Fitur | App | Status |
|----|-------|-----|--------|
| C-08 | Chat Merchant dari merchant detail page | Customer | Belum |
| M-05 | Inbox Notifikasi | Merchant | Belum |
| B-05 | Image upload di chat (multipart) | Backend | Belum |
| Ku-05 | Chat dari halaman delivery aktif | Courier | Belum |
| T-01 | Chat polling otomatis / WebSocket | Semua | Belum |
| T-02 | Pagination chat messages | Semua | Belum |

---

## 🟡 PRIORITAS 3 — Sebelum Launch

| Fitur | Estimasi |
|-------|----------|
| Testing infrastructure (PHPUnit) | 40 jam |
| Security (policies, rate limiting, 2FA admin) | 20 jam |
| Payment gateway (Midtrans/Xendit) | 25 jam |
| Cache (Redis) | 10 jam |
| Database indexing | 2 jam |
| Error handling standardization | 15 jam |

---

## 🟢 PRIORITAS 4 — Post-Launch

- Unit test Flutter apps
- Deep linking notifikasi
- i18n / multi-language
- AI delivery optimization
- Promo code / referral
- Live tracking GPS

---

## 📂 Arsitektur
```
Antarkanma/
├── app/                    # Laravel Backend
│   ├── Models/             # 21 models
│   ├── Http/Controllers/   # 21 controllers
│   └── Filament/           # Admin panel
├── mobile/
│   ├── customer/           # Flutter Customer App
│   ├── merchant/           # Flutter Merchant App
│   └── courier/            # Flutter Courier App
├── routes/api.php          # 130+ API endpoints
├── database/migrations/    # 40 migration files
└── MASTERPLAN.md           # ← FILE INI (satu-satunya source of truth)
```
