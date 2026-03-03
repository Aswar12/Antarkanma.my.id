# 📋 Rencana Komprehensif Proyek Antarkanma

> **Dibuat:** 27 Februari 2026  
> **Status:** Master Plan  
> **Versi:** 1.0

Dokumen ini berisi analisis lengkap semua yang sudah dan belum dikerjakan dalam proyek Antarkanma, beserta rencana terstruktur untuk menyelesaikannya.

---

## 🎯 Executive Summary

### Status Proyek: **85% Complete (MVP Ready)**

Proyek Antarkanma telah mencapai tahap **Minimum Viable Product (MVP)** dengan semua fitur inti berfungsi. Namun, terdapat beberapa **critical gaps** yang harus ditutup sebelum **Soft Launch** yang ditargetkan pada **pertengahan Mei 2026**.

### Timeline ke Soft Launch

```
Sekarang (27 Feb) ────────────────────────────────────────► Mid Mei 2026
     │                    │                    │                    │
     ▼                    ▼                    ▼                    ▼
┌─────────┐         ┌─────────┐         ┌─────────┐         ┌─────────┐
│Critical │         │ Testing │         │  Core   │         │  Pre-   │
│  Fixes  │        │Foundation│        │ Features│         │ Launch  │
│ 2 minggu│         │ 2 minggu│         │ 2 minggu│         │ 2 minggu│
└─────────┘         └─────────┘         └─────────┘         └─────────┘
     │                    │                    │                    │
     │                    │                    │                    ▼
     │                    │                    │            ┌─────────────┐
     │                    │                    │            │ SOFT LAUNCH │
     │                    │                    │            │  Segeri DC  │
     │                    │                    │            └─────────────┘
```

---

## 📊 Metrik Proyek Saat Ini

| Metric | Current | Target | Status | Gap |
|--------|---------|--------|--------|-----|
| **API Endpoints** | 80+ | 100 | 🟢 80% | +20 endpoints |
| **Database Tables** | 17 | 17 | 🟢 100% | ✅ Complete |
| **Mobile Apps** | 3 | 3 | 🟢 100% | ✅ Complete |
| **Test Coverage** | ~5% | 80% | 🔴 6% | +75% needed |
| **Documentation** | 20+ pages | 25 | 🟢 80% | +5 pages |
| **Critical Bugs** | 0 | 0 | 🟢 Fixed | ✅ None |
| **Production Ready** | ~85% | 100% | 🟡 85% | +15% needed |

---

## ✅ BAGIAN 1: Yang Sudah Dikerjakan

### 1.1 Backend (Laravel) — ✅ 95% Complete

#### Models (17/17)
```
✅ User              ✅ Order            ✅ Delivery
✅ Merchant          ✅ OrderItem        ✅ DeliveryItem
✅ Product           ✅ Transaction      ✅ CourierBatch
✅ ProductCategory   ✅ LoyaltyPoint     ✅ FcmToken
✅ ProductGallery    ✅ Courier          ✅ UserLocation
✅ ProductVariant    ✅ ProductReview
```

#### API Controllers (17/17)
```
✅ UserController            ✅ OrderController         ✅ ShippingController
✅ MerchantController        ✅ OrderStatusController   ✅ FcmController
✅ ProductController         ✅ TransactionController   ✅ NotificationController
✅ ProductCategoryController ✅ DeliveryController      ✅ ProductReviewController
✅ ProductGalleryController  ✅ CourierController       ✅ NotificationTestController
✅ OrderItemController
```

#### Database Migrations (40 files)
```
✅ 0001_01_01_000000_create_users_table.php
✅ 0001_01_01_000001_create_cache_table.php
✅ 0001_01_01_000002_create_jobs_table.php
✅ 2024_02_14_000000_create_fcm_tokens_table.php
✅ 2024_10_20_094403_add_two_factor_columns_to_users_table.php
✅ 2024_10_20_094426_create_personal_access_tokens_table.php
✅ 2024_10_20_101535_add_fields_to_users_table.php
✅ 2024_10_20_101729_create_merchants_table.php
✅ 2024_10_20_101730_add_operating_hours_to_merchants_table.php
✅ 2024_10_20_102058_create_products_table.php
✅ 2024_10_20_102548_create_product_categories_table.php
✅ 2024_10_20_102848_create_product_galleries_table.php
✅ 2024_10_20_103008_create_orders_table.php
✅ 2024_10_20_103009_add_ready_for_pickup_status_to_orders.php
✅ 2024_10_20_111447_create_order_items_table.php
✅ 2024_10_20_111655_create_loyalty_points_table.php
✅ 2024_10_20_112939_create_couriers_table.php
✅ 2024_10_20_113547_create_transactions_table.php
✅ 2024_10_20_114851_create_deliveries_table.php
✅ 2024_10_20_121538_create_product_variants_table.php
✅ 2024_10_20_122931_create_user_locations_table.php
✅ 2024_10_21_103723_create_product_reviews_table.php
✅ 2024_10_21_124857_create_delivery_items_table.php
✅ 2024_10_21_124910_create_courier_batches_table.php
✅ 2024_10_31_083932_update_user_locations_table.php
✅ 2024_10_31_085012_update_address_type_enum_to_indonesian.php
✅ 2024_11_01_000000_add_is_active_to_users_table.php
✅ 2025_01_17_220327_update_order_status_enum_add_waiting_approval_and_picked_up.php
✅ 2025_01_21_180653_add_admin_role_to_users_table.php
✅ 2025_01_22_052601_update_roles_column_to_enum.php
✅ 2025_01_23_122024_add_logo_url_to_merchants_table.php
✅ 2025_01_23_150506_add_courier_approval_and_timeout_to_transactions.php
✅ 2025_01_23_161355_fix_transactions_orders_relationship.php
✅ 2025_02_03_130142_add_coordinates_to_merchants_table.php
✅ 2025_02_18_163947_add_courier_id_to_transactions.php
✅ 2025_02_22_151323_add_base_merchant_id_to_transactions_table.php
✅ 2025_02_28_014115_add_rejection_reason_and_customer_note_to_orders_table.php
✅ 2025_02_28_115148_move_customer_note_to_order_items_table.php
✅ 2025_03_08_080527_add_wallet_fields_to_couriers_table.php
✅ 2025_03_18_152020_add_is_active_to_merchants_table.php
```

#### Seeders (32 files)
```
✅ UserSeeder                    ✅ OrderItemSeeder
✅ MerchantSeeder                ✅ TransactionItemSeeder
✅ ProductSeeder                 ✅ DeliverySeeder
✅ ProductCategorySeeder         ✅ DeliveryItemSeeder
✅ ProductGallerySeeder          ✅ CourierBatchSeeder
✅ ProductVariantSeeder          ✅ LoyaltyPointSeeder
✅ ProductReviewSeeder           ✅ CourierSeeder
✅ OrderSeeder                   ✅ UserLocationSeeder
✅ TransactionSeeder             ✅ TestMerchantSeeder
✅ Additional50ProductsSeeder    ✅ KoneksiRasaSeeder
✅ ElectronicsMerchantSeeder     ✅ TestMultiMerchantTransactionSeeder
✅ NewUserSeeder                 ✅ CreateProductReviewSeeder
✅ NewProductWithGallery...      ✅ ClearProductAndMerchant...
✅ MerchantLocationComplete...   ✅ ClearTransactionDataSeeder
✅ TestAccountsSeeder            ✅ AntarKanMaCourierSeeder
✅ ProductCategorySeeder         ✅ DatabaseSeeder
```

#### Filament Admin Resources (14/14)
```
✅ UserResource           ✅ OrderResource          ✅ CourierResource
✅ MerchantResource       ✅ OrderItemResource      ✅ DeliveryResource
✅ ProductResource        ✅ TransactionResource    ✅ TransactionItemResource
✅ ProductCategoryResource ✅ ProductReviewResource  ✅ UserLocationResource
✅ ProductGalleryResource ✅ LoyaltyPointResource
```

#### Services
```
✅ FirebaseService.php   ✅ OsrmService.php
```

---

### 1.2 Mobile Apps (Flutter) — ✅ 90% Complete

#### Apps Structure
```
mobile/
├── customer/     ✅ Customer App (Public users)
├── merchant/     ✅ Merchant App (Business owners)
└── courier/      ✅ Courier App (Delivery partners)
```

#### Customer App Features
```
✅ Auto-login & Splash screen
✅ Registration & Login
✅ Home page dengan Universal Search
✅ Merchant listing & detail
✅ Product browsing & search
✅ Cart & Checkout
✅ Order tracking
✅ User profile management
✅ User locations (multi-address)
✅ Product reviews
✅ Chat system (Firestore real-time)
✅ FCM push notifications
```

#### Merchant App Features
```
✅ Auto-login & Splash screen
✅ Dashboard dengan statistik
✅ Order management (queue)
✅ Product CRUD
✅ Product gallery management
✅ Product variants
✅ Merchant profile & operating hours
✅ Order status updates (approve, reject, ready)
✅ Print service (Bluetooth thermal)
✅ FCM push notifications
```

#### Courier App Features
```
✅ Auto-login & Splash screen
✅ Available transactions listing
✅ Transaction acceptance/rejection
✅ Order pickup & completion
✅ Courier status tracking
✅ Wallet balance & top-up
✅ Daily statistics
✅ FCM push notifications
```

---

### 1.3 Dokumentasi — ✅ 85% Complete

#### Business Documentation
```
✅ business-model.md         — Model bisnis & revenue
✅ company-profile.md        — Profil perusahaan
✅ growth-roadmap.md         — Roadmap pertumbuhan
✅ problems-and-solutions.md — Masalah & solusi
```

#### Technical Documentation
```
✅ technical-specifications.md — Spesifikasi teknis
✅ project-planning.md         — Perencanaan proyek
✅ work-plan.md                — Rencana kerja
✅ active-backlog.md           — Backlog aktif
✅ progress-log.md             — Log progress (11 sesi)
```

#### API Documentation
```
✅ api/api-reference.md        — Referensi API lengkap
✅ api/user-api.md             — User/Customer API
✅ api/merchant-api.md         — Merchant API
✅ api/courier-api.md          — Courier API
✅ api/transaction-flow.md     — Flow transaksi
```

#### Architecture Documentation
```
✅ architecture/database-schema.md — Schema database
✅ architecture/erd-diagram.md     — ERD Diagram
✅ architecture/class-diagram.md   — Class Diagram
✅ architecture/dfd-level-0.md     — DFD Level 0
✅ architecture/dfd-level-1.md     — DFD Level 1
✅ architecture/sequence-diagram.md — Sequence Diagram
✅ architecture/data-flow-design.md — Data Flow Design
```

#### Feature Documentation
```
✅ features/delivery-cost-calculation.md
✅ features/fcm-api-prompt.md
✅ features/operational-hours-and-targets.md
✅ features/order-verification-system.md
✅ features/payment-and-fee-management.md
✅ features/payment-implementation-details.md
✅ features/payment-system-options.md
✅ features/payment-workflow-by-role.md
```

#### Deployment Documentation
```
✅ deployment/deployment-guide.md   — Panduan deployment
✅ deployment/load-balancer.md      — Setup load balancer
```

#### Images & Diagrams
```
✅ images/activity-diagram.png
✅ images/class-diagram.png
✅ images/sequence-diagram.png
```

---

### 1.4 Infrastructure — ✅ 90% Complete

#### Docker Configuration
```
✅ docker-compose.yml           — Development (Laragon)
✅ docker-compose.laptop.yml    — Laptop Docker
✅ docker-compose.vps.yml       — VPS Production
✅ Dockerfile                   — PHP-FPM container
✅ .dockerignore                — Docker ignore rules
```

#### Load Balancer & Network
```
✅ Nginx load balancer config   — Read/write splitting
✅ Cloudflare Tunnel setup      — Public exposure
✅ MySQL Master-Slave design    — GTID replication
✅ Redis Master-Slave design    — Caching layer
```

#### Batch Scripts
```
✅ run-all.bat                  — Run full stack
✅ run-app.bat                  — Run Flutter app only
✅ run-mobile.bat               — Setup mobile backend
```

---

## 🔴 BAGIAN 2: Critical Gaps (Harus Segera)

### C1: Missing Controllers — 🔴 CRITICAL

**Issue:** Dua controller direferensikan di `routes/api.php` tetapi file tidak ada.

#### C1.1: ManualOrderController
```php
// routes/api.php line ~147
Route::post('/manual-order', [App\Http\Controllers\API\ManualOrderController::class, 'store']);
```

**Status:** ❌ FILE NOT FOUND  
**Priority:** 🔴 Critical  
**Effort:** 2-3 jam  
**Action:** Create controller untuk fitur Jastip (Jasa Titip)

#### C1.2: ChatController
```php
// routes/api.php line ~150-152
Route::post('/chat/initiate', [App\Http\Controllers\API\ChatController::class, 'initiate']);
Route::get('/chat/{chatId}/messages', [App\Http\Controllers\API\ChatController::class, 'getMessages']);
Route::post('/chat/{chatId}/send', [App\Http\Controllers\API\ChatController::class, 'sendMessage']);
```

**Status:** ❌ FILE NOT FOUND  
**Priority:** 🔴 Critical  
**Effort:** 3-4 jam  
**Action:** Create controller untuk chat API (MySQL backend)

---

### C2: Automated Testing — 🔴 HIGH

**Current Coverage:** ~5% (hanya Feature tests dasar dari Jetstream)

#### Tests yang Ada (15 files)
```
✅ AuthenticationTest.php
✅ RegistrationTest.php
✅ PasswordResetTest.php
✅ EmailVerificationTest.php
✅ TwoFactorAuthenticationSettingsTest.php
✅ UpdatePasswordTest.php
✅ PasswordConfirmationTest.php
✅ ProfileInformationTest.php
✅ BrowserSessionsTest.php
✅ DeleteAccountTest.php
✅ CreateApiTokenTest.php
✅ ApiTokenPermissionsTest.php
✅ DeleteApiTokenTest.php
✅ ExampleTest.php
✅ TestCase.php
```

#### Tests yang Dibutuhkan

| Test Suite | Priority | Effort | Coverage |
|------------|----------|--------|----------|
| **Auth API Tests** | 🔴 High | 4 jam | Register, login, logout, refresh |
| **User API Tests** | 🟡 Medium | 3 jam | Profile CRUD, photo upload |
| **Merchant API Tests** | 🔴 High | 4 jam | Merchant CRUD, logo upload |
| **Product API Tests** | 🔴 High | 4 jam | Product CRUD, search, variants |
| **Order API Tests** | 🔴 High | 6 jam | Create, list, status updates |
| **Transaction API Tests** | 🔴 High | 6 jam | Create, approve, cancel |
| **Courier API Tests** | 🟡 Medium | 4 jam | Transaction flow, wallet |
| **Delivery API Tests** | 🟡 Medium | 3 jam | Assignment, status updates |

**Total Effort:** 34 jam (~1 minggu kerja)  
**Target Coverage:** 40% minimum untuk soft launch

---

### C3: Form Request Validation — 🟡 MEDIUM

**Issue:** Validasi input masih tersebar di controller, tidak menggunakan Form Request classes.

#### Controllers yang Perlu Audit

| Controller | Current Validation | Priority |
|------------|-------------------|----------|
| UserController | Inline validation | 🟡 Medium |
| MerchantController | Inline validation | 🟡 Medium |
| ProductController | Inline validation | 🟡 Medium |
| OrderController | Inline validation | 🔴 High |
| TransactionController | Inline validation | 🔴 High |
| CourierController | Inline validation | 🟡 Medium |

**Action Items:**
1. Buat Form Request classes untuk setiap action
2. Extract validation logic dari controller
3. Tambahkan custom error messages
4. Update documentation

**Total Effort:** 16 jam (~2 hari kerja)

---

### C4: Error Response Standardization — 🟡 MEDIUM

**Issue:** Format error response tidak konsisten antar controller.

#### Current State
```php
// Controller A
return response()->json(['error' => 'Message'], 400);

// Controller B
return response()->json(['message' => 'Error'], 422);

// Controller C
throw new HttpResponseException(response()->json([...], 500));
```

#### Target Standard
```php
// All controllers
return response()->json([
    'success' => false,
    'error' => [
        'code' => 'VALIDATION_ERROR',
        'message' => 'Human readable message',
        'details' => [...] // optional
    ]
], 400);
```

**Action Items:**
1. Buat helper trait/method untuk error responses
2. Audit semua controller (17 files)
3. Standardisasi format
4. Update API documentation

**Total Effort:** 8 jam (~1 hari kerja)

---

### C5: Environment Configuration — 🟢 LOW

**Issue:** `.env.example` ada tapi beberapa nilai kosong atau tidak jelas.

#### Variables yang Perlu Dilengkapi

```env
# Database
DB_CONNECTION=mysql
DB_HOST=db                    # ✅ OK
DB_PORT=3306                  # ✅ OK
DB_DATABASE=antarkanma        # ✅ OK
DB_USERNAME=antarkanma        # ✅ OK
DB_PASSWORD=Antarkanma123     # ✅ OK (contoh)
DB_ROOT_PASSWORD=Antarkanma123 # ✅ OK (contoh)

# Redis
REDIS_HOST=redis              # ✅ OK
REDIS_PORT=6379               # ✅ OK

# S3 Storage (IDCloudHost)
AWS_ACCESS_KEY_ID=your_access_key_id_here
AWS_SECRET_ACCESS_KEY=your_secret_access_key_here
AWS_DEFAULT_REGION=id-jkt-1
AWS_BUCKET=antarkanma
AWS_URL=https://is3.cloudhost.id
AWS_ENDPOINT=https://is3.cloudhost.id
AWS_USE_PATH_STYLE_ENDPOINT=true

# Firebase
FIREBASE_PROJECT=app
FIREBASE_PROJECT_ID=antarkanma-98fde
FIREBASE_CREDENTIALS=/app/storage/app/firebase/firebase-credentials.json
FIREBASE_DATABASE_URL=https://antarkanma-98fde.firebaseio.com
FIREBASE_STORAGE_DEFAULT_BUCKET=antarkanma-98fde.appspot.com
FIREBASE_SERVER_KEY=your_server_key_here
FIREBASE_MESSAGING_SENDER_ID=your_sender_id_here
FIREBASE_API_KEY=your_api_key_here

# App Settings
APP_NAME=Antarkanma
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

**Total Effort:** 2 jam

---

## 🟡 BAGIAN 3: Fitur Inti Belum Lengkap

### F1: Payment Gateway Integration — 🔴 HIGH IMPACT

**Status:** ⬜ Not Started  
**Priority:** 🔴 High (Revenue impact)  
**Effort:** High (2 sprints)  
**Target:** Q3 2026 (setelah soft launch)

#### Requirements
- [ ] Research Midtrans vs Xendit
- [ ] Setup merchant accounts
- [ ] Implement payment service layer
- [ ] Update transaction flow
- [ ] Webhook handler untuk payment callbacks
- [ ] Testing end-to-end payment flow

#### Estimated Timeline
```
Sprint 1: Research & Setup (1 minggu)
Sprint 2: Implementation (1 minggu)
Sprint 3: Testing & QA (1 minggu)
```

---

### F2: Real-time GPS Tracking — 🟡 MEDIUM IMPACT

**Status:** ⬜ Partial (OsrmService ada, belum full implementation)  
**Priority:** 🟡 Medium  
**Effort:** High  
**Target:** Post soft launch

#### Requirements
- [ ] Background location service (Courier App)
- [ ] WebSocket/Broadcasting untuk real-time updates
- [ ] Map integration (Customer & Merchant Apps)
- [ ] ETA calculation & display
- [ ] Location history tracking

#### Technical Considerations
```
Option A: Laravel Reverb + Echo (WebSocket)
Option B: Firebase Realtime Database
Option C: Polling dengan interval (simplest)

Recommendation: Start with Option C, upgrade to A/B later
```

---

### F3: Redis Caching — 🟡 MEDIUM IMPACT

**Status:** ⬜ Not Started (Redis extension belum installed)  
**Priority:** 🟡 Medium  
**Effort:** Medium  
**Target:** Pre soft launch

#### Queries to Cache
```php
// High-frequency queries
- Popular products (cache 1 hour)
- Merchant lists (cache 30 minutes)
- Product categories (cache 24 hours)
- User profile (cache 15 minutes)
- Courier statistics (cache 5 minutes)
```

#### Implementation Steps
1. Install Redis extension di PHP
2. Configure Redis connection di `.env`
3. Implement cache decorators di service layer
4. Add cache invalidation logic
5. Monitor cache hit/miss ratio

---

### F4: API Rate Limiting — 🟢 LOW IMPACT

**Status:** ⬜ Not Started  
**Priority:** 🟢 Low  
**Effort:** Low  
**Target:** Pre soft launch

#### Configuration Example
```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // Standard endpoints: 60 requests/minute
});

Route::middleware(['throttle:10,1'])->group(function () {
    // Sensitive endpoints (login, register): 10 requests/minute
});
```

---

### F5: Notification Templates — 🟢 MEDIUM IMPACT

**Status:** ⬜ Partial (FirebaseService ada, perlu enhancement)  
**Priority:** 🟢 Medium  
**Effort:** Low  
**Target:** Pre soft launch

#### Templates Needed

| Event | Recipient | Channel | Priority |
|-------|-----------|---------|----------|
| Order created | Merchant | FCM | High |
| Order approved | Customer | FCM | High |
| Order ready | Customer + Courier | FCM | High |
| Courier assigned | Customer + Merchant | FCM | Medium |
| Order picked up | Customer | FCM | Medium |
| Order completed | Customer + Merchant | FCM | Low |
| Payment received | Customer + Merchant | FCM | High |
| Chat message | Recipient | FCM + Firestore | High |

---

### F6: Order Timeout Auto-Cancel — 🟢 LOW IMPACT

**Status:** ⏸️ Disabled (untuk hybrid flow)  
**Priority:** 🟢 Low  
**Effort:** Medium  
**Target:** Post soft launch

#### Implementation Options
```
Option A: Laravel Scheduler (cron job)
  - Check every minute for expired orders
  - Auto-cancel if merchant not respond in 5 minutes

Option B: Queue Delayed Jobs
  - Dispatch job with 5-minute delay
  - Cancel if order still pending

Option C: Database Event + Timer
  - Use MySQL event scheduler
  - Auto-update status after timeout
```

**Recommendation:** Option A (most maintainable)

---

### F7: Merchant Fee Implementation — 🟢 MEDIUM IMPACT

**Status:** ⬜ Not Started (formula ada di docs, belum di code)  
**Priority:** 🟢 Medium (Revenue impact)  
**Effort:** Low  
**Target:** Pre soft launch

#### Business Logic
```
Fee per order = Rp 1.000 (flat)
Calculation:
  - Deducted from merchant revenue
  - Recorded in transaction table
  - Reported in merchant dashboard
```

#### Implementation
1. Add `platform_fee` column to `transactions` table
2. Update `TransactionController` to calculate fee
3. Update merchant revenue calculation
4. Add fee reporting to dashboard

---

### F8: Courier Transfer Order — 🔴 LOW IMPACT

**Status:** ⬜ Backlog (B6)  
**Priority:** 🔴 Low  
**Effort:** Medium  
**Target:** Post soft launch

#### Use Case
```
Scenario: Courier tidak bisa lanjut (sakit, emergency)
Action: Transfer order ke courier lain
Flow:
  1. Courier request transfer
  2. Admin/Courier approve
  3. Update courier_id di transaction
  4. Notify new courier
```

---

## 🟢 BAGIAN 4: Mobile Apps Enhancement

### M1: Customer App E2E Testing — 🔴 HIGH

**Status:** ⬜ Not Tested  
**Priority:** 🔴 High  
**Effort:** Medium  
**Target:** Sprint 14-15

#### Test Scenarios
```
1. Register → Login → Browse → Add to Cart → Checkout ✅
2. Checkout → Payment → Order Tracking → Complete ✅
3. Order → Cancel → Refund ✅
4. Browse → Search → Filter → View Detail ✅
5. Profile → Update → Upload Photo ✅
6. Locations → Add → Set Default ✅
7. Products → Review & Rate ✅
8. Chat → Send Message → Receive Reply ✅
```

---

### M2: Customer App — Order Tracking UI — 🟡 MEDIUM

**Status:** ⬜ Backlog (F3)  
**Priority:** 🟡 Medium  
**Effort:** Medium  
**Target:** Sprint 18-19

#### Requirements
```
✅ Stepper UI untuk status tracking:
   [Menunggu Konfirmasi] → [Diproses] → [Siap Diantar] → [Diantar] → [Selesai]

✅ Real-time updates via FCM
✅ Courier location on map (future)
✅ ETA display (future)
```

---

### M3: Customer App — Live GPS Tracking — 🟡 MEDIUM

**Status:** ⬜ Backlog (F4)  
**Priority:** 🟡 Medium  
**Effort:** High  
**Target:** Post soft launch

#### Requirements
```
- Map integration (Google Maps / Mapbox)
- Real-time courier location
- Route visualization
- ETA calculation
```

---

### M4: Merchant App — Orders Page Redesign — 🟢 LOW

**Status:** ⬜ Backlog (F5)  
**Priority:** 🟢 Low  
**Effort:** Low  
**Target:** Sprint 18-19

#### Improvements
```
- Better filtering (All, Pending, Processing, Completed)
- Search functionality
- Bulk actions (approve multiple orders)
- Quick stats per day
```

---

### M5: Courier App — ETA Display — 🟢 LOW

**Status:** ⬜ Backlog (F6)  
**Priority:** 🟢 Low  
**Effort:** Medium  
**Target:** Post soft launch

#### Requirements
```
- Calculate ETA based on distance & traffic
- Display to merchant & customer
- Update in real-time
```

---

### M6: Courier App — GPS Tracking — 🟡 MEDIUM

**Status:** ⬜ Not Started  
**Priority:** 🟡 Medium  
**Effort:** High  
**Target:** Sprint 18-19

#### Requirements
```
- Background location service
- Configurable interval (30 seconds)
- Battery optimization
- Offline caching
```

---

### M7: Release APK Builds — 🔴 HIGH

**Status:** ⬜ Not Started  
**Priority:** 🔴 High  
**Effort:** Medium  
**Target:** Pre soft launch

#### Checklist
```
1. Generate signing keys (keystore)
2. Configure build.gradle
3. Build release APKs
4. Test on production devices
5. Upload to Play Store (internal testing)
```

---

## 🔵 BAGIAN 5: Documentation & SDLC

### D1: Update Sequence Diagram — 🟢 LOW

**Status:** ⬜ Outdated  
**Priority:** 🟢 Low  
**Effort:** Low  
**Target:** Sprint 12-13

#### Updates Needed
```
- Add courier status flow (IDLE → HEADING_TO_MERCHANT → AT_MERCHANT → ...)
- Add new endpoints (arrive-merchant, arrive-customer, pickup, complete)
- Sync with actual implementation (Session 11)
```

---

### D2: Update API Reference — 🟢 LOW

**Status:** ⬜ Outdated  
**Priority:** 🟢 Low  
**Effort:** Low  
**Target:** Sprint 12-13

#### New Endpoints to Document
```
POST   /courier/transactions/{id}/arrive-merchant
POST   /courier/transactions/{id}/arrive-customer
POST   /courier/orders/{id}/pickup
POST   /courier/orders/{id}/complete
```

---

### D3: Update E2E Testing Guide — 🟢 LOW

**Status:** ⬜ Needs update  
**Priority:** 🟢 Low  
**Effort:** Low  
**Target:** Sprint 12-13

#### Updates
```
- Add courier flow testing
- Add multi-merchant testing
- Add FCM notification testing
- Add performance testing section
```

---

### D4: Create Deployment Checklist — 🔴 HIGH

**Status:** ⬜ Not Created  
**Priority:** 🔴 High  
**Effort:** Low  
**Target:** Sprint 12-13

#### Checklist Structure
```markdown
## Pre-Deployment
- [ ] All tests passing
- [ ] No critical bugs
- [ ] Environment variables configured
- [ ] Database migrations ready
- [ ] SSL certificate installed

## Deployment
- [ ] Backup database
- [ ] Pull latest code
- [ ] Run migrations
- [ ] Clear cache
- [ ] Restart services

## Post-Deployment
- [ ] Health check passing
- [ ] Smoke tests passing
- [ ] Monitoring active
- [ ] Logs clean
- [ ] Backup verified
```

---

### D5: Create Troubleshooting Guide — 🟢 MEDIUM

**Status:** ⬜ Not Created  
**Priority:** 🟢 Medium  
**Effort:** Medium  
**Target:** Sprint 14-15

#### Common Issues to Document
```
1. ADB not recognized
2. Flutter build failed
3. Laravel migration errors
4. Firebase connection issues
5. S3 upload failures
6. Redis connection errors
7. Database connection timeouts
8. FCM not working
```

---

## 🟣 BAGIAN 6: Infrastructure & DevOps

### I1: CI/CD Pipeline — 🟡 MEDIUM

**Status:** ⬜ Not Started  
**Priority:** 🟡 Medium  
**Effort:** High  
**Target:** Post soft launch

#### GitHub Actions Workflows
```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - Checkout code
      - Setup PHP
      - Install dependencies
      - Run tests
      - Upload coverage

# .github/workflows/deploy.yml
name: Deploy to VPS
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - Checkout code
      - Deploy via SSH
      - Run migrations
      - Restart services
```

---

### I2: Database Backup Strategy — 🔴 HIGH

**Status:** ⬜ Not Started  
**Priority:** 🔴 High  
**Effort:** Low  
**Target:** Sprint 14-15

#### Backup Plan
```bash
# Daily backup script
#!/bin/bash
mysqldump -u root -p antarkanma > backup_$(date +%Y%m%d_%H%M%S).sql

# Store in multiple locations
- Local storage
- S3 bucket
- Google Drive (via rclone)

# Retention policy
- Daily: 7 days
- Weekly: 4 weeks
- Monthly: 6 months
```

---

### I3: Monitoring & Alerting — 🔴 HIGH

**Status:** ⬜ Not Started  
**Priority:** 🔴 High  
**Effort:** Low  
**Target:** Sprint 16-17

#### Monitoring Stack
```
Option A: Free Tier
- UptimeRobot (50 checks, 5 min interval)
- Laravel Telescope (local monitoring)
- Log files monitoring

Option B: Paid (~$10-20/month)
- BetterStack / LogRocket
- Sentry (error tracking)
- New Relic (APM)
```

#### Alerts to Setup
```
- Website down (HTTP check)
- API response time > 2s
- Database connection failed
- Queue jobs failing
- Disk space < 20%
- Memory usage > 80%
```

---

### I4: Log Management — 🟢 LOW

**Status:** ⬜ Not Started  
**Priority:** 🟢 Low  
**Effort:** High  
**Target:** Post soft launch (Phase 2)

#### Future Implementation
```
- ELK Stack (Elasticsearch, Logstash, Kibana)
- Centralized logging
- Log aggregation & search
- Real-time dashboards
```

---

### I5: SSL Certificate — 🔴 HIGH

**Status:** ⬜ Not Started  
**Priority:** 🔴 High  
**Effort:** Low  
**Target:** Pre soft launch

#### Options
```
Option A: Cloudflare (Recommended)
- Free SSL
- Auto-renewal
- DDoS protection
- Easy setup

Option B: Let's Encrypt
- Free SSL
- Auto-renewal via certbot
- Manual setup required
```

---

## 📅 BAGIAN 7: Sprint Plan

### Sprint 12-13 (1-14 Maret 2026) — Critical Fixes

**Goal:** Fix all critical gaps before testing phase

| Task | Priority | Effort | Owner | Status |
|------|----------|--------|-------|--------|
| C1.1: Create ManualOrderController | 🔴 Critical | 2h | | ⬜ |
| C1.2: Create ChatController | 🔴 Critical | 3h | | ⬜ |
| C3: Error Response Standardization | 🟡 Medium | 8h | | ⬜ |
| C5: Update .env.example | 🟢 Low | 2h | | ⬜ |
| D1: Update Sequence Diagram | 🟢 Low | 2h | | ⬜ |
| D2: Update API Reference | 🟢 Low | 2h | | ⬜ |
| D4: Create Deployment Checklist | 🔴 High | 4h | | ⬜ |

**Total:** 23 jam (~3 hari kerja)

---

### Sprint 14-15 (15-28 Maret 2026) — Testing Foundation

**Goal:** Establish testing culture & E2E validation

| Task | Priority | Effort | Owner | Status |
|------|----------|--------|-------|--------|
| C2.1: Auth API Tests | 🔴 High | 4h | | ⬜ |
| C2.2: Merchant API Tests | 🔴 High | 4h | | ⬜ |
| C2.3: Product API Tests | 🔴 High | 4h | | ⬜ |
| C2.4: Order API Tests | 🔴 High | 6h | | ⬜ |
| C2.5: Transaction API Tests | 🔴 High | 6h | | ⬜ |
| C2.6: Courier API Tests | 🟡 Medium | 4h | | ⬜ |
| M1: Customer App E2E Testing | 🔴 High | 8h | | ⬜ |
| D3: Update E2E Testing Guide | 🟢 Low | 2h | | ⬜ |
| D5: Create Troubleshooting Guide | 🟢 Medium | 4h | | ⬜ |
| I2: Database Backup Strategy | 🔴 High | 4h | | ⬜ |

**Total:** 46 jam (~6 hari kerja)

---

### Sprint 16-17 (29 Maret - 11 April 2026) — Core Features

**Goal:** Implement essential features for soft launch

| Task | Priority | Effort | Owner | Status |
|------|----------|--------|-------|--------|
| C4: Form Request Validation | 🟡 Medium | 16h | | ⬜ |
| F3: Redis Caching | 🟡 Medium | 8h | | ⬜ |
| F4: API Rate Limiting | 🟢 Low | 4h | | ⬜ |
| F5: Notification Templates | 🟢 Medium | 6h | | ⬜ |
| F7: Merchant Fee Implementation | 🟢 Medium | 4h | | ⬜ |
| I3: Monitoring & Alerting | 🔴 High | 8h | | ⬜ |
| I5: SSL Certificate | 🔴 High | 4h | | ⬜ |

**Total:** 50 jam (~6 hari kerja)

---

### Sprint 18-19 (12-25 April 2026) — Mobile Enhancement

**Goal:** Polish mobile apps for production

| Task | Priority | Effort | Owner | Status |
|------|----------|--------|-------|--------|
| M2: Order Tracking UI | 🟡 Medium | 8h | | ⬜ |
| M4: Merchant Orders Redesign | 🟢 Low | 4h | | ⬜ |
| M6: Courier GPS Tracking MVP | 🟡 Medium | 12h | | ⬜ |
| M7: Release APK Builds | 🔴 High | 8h | | ⬜ |

**Total:** 32 jam (~4 hari kerja)

---

### Sprint 20-21 (26 April - 9 Mei 2026) — Pre-Launch

**Goal:** Final preparation for soft launch

| Task | Priority | Effort | Owner | Status |
|------|----------|--------|-------|--------|
| F1: Payment Gateway Research | 🔴 High | 8h | | ⬜ |
| D4: Execute Deployment Checklist | 🔴 High | 4h | | ⬜ |
| I1: CI/CD Pipeline (optional) | 🟡 Medium | 12h | | ⬜ |
| Load Testing | 🔴 High | 8h | | ⬜ |
| Security Audit | 🔴 High | 8h | | ⬜ |
| Merchant Onboarding | 🔴 High | 8h | | ⬜ |
| Courier Training | 🔴 High | 8h | | ⬜ |

**Total:** 56 jam (~7 hari kerja)

---

### Sprint 22+ (10 Mei 2026+) — Launch & Beyond

**Goal:** Soft launch & iterate based on feedback

| Task | Priority | Effort | Owner | Status |
|------|----------|--------|-------|--------|
| **SOFT LAUNCH** | 🔴 Critical | - | | 🎯 |
| F2: Real-time GPS Tracking | 🟡 Medium | 16h | | ⬜ |
| F1: Payment Gateway Integration | 🔴 High | 24h | | ⬜ |
| User Feedback Collection | 🔴 High | Ongoing | | ⬜ |
| Bug Fixes & Improvements | 🔴 High | Ongoing | | ⬜ |

---

## 🎯 BAGIAN 8: Kriteria Soft Launch Ready

### Technical Readiness

```
✅ Critical Bugs
   - Zero critical bugs
   - Zero data loss scenarios
   - Zero security vulnerabilities

✅ Testing
   - Test coverage >= 40%
   - All E2E tests passing
   - Load test: 100 concurrent users

✅ Infrastructure
   - Production environment ready
   - SSL certificate installed
   - Monitoring & alerting active
   - Backup strategy implemented
   - Disaster recovery plan

✅ Mobile Apps
   - Release APK signed & tested
   - Auto-login working
   - FCM notifications working
   - Offline handling implemented
```

### Business Readiness

```
✅ Merchants
   - Minimum 10 merchants onboarded
   - All merchants trained
   - Operating hours configured

✅ Couriers
   - Minimum 5 couriers active
   - All couriers trained
   - Wallet system tested

✅ Support
   - Customer support channel ready
   - Troubleshooting guide available
   - Escalation process defined
```

---

## 📈 BAGIAN 9: Success Metrics

### Pre-Launch Metrics (Target: Mid Mei 2026)

| Metric | Current | Target | Status |
|--------|---------|--------|--------|
| Test Coverage | 5% | 40% | 🟡 On Track |
| Critical Bugs | 0 | 0 | ✅ Done |
| Documentation | 85% | 95% | 🟡 On Track |
| Merchant Onboarded | 2 | 10 | 🔴 Behind |
| Courier Active | 1 | 5 | 🔴 Behind |

### Post-Launch Metrics (3 bulan setelah launch)

| Metric | Target | Timeline |
|--------|--------|----------|
| Daily Active Users | 100 | 3 months |
| Daily Transactions | 50 | 3 months |
| Order Completion Rate | 95% | 3 months |
| Average Delivery Time | < 45 min | 3 months |
| App Rating (Play Store) | 4.5+ | 3 months |
| Merchant Retention | 80% | 3 months |
| Courier Retention | 70% | 3 months |

---

## 📝 BAGIAN 10: Action Items Immediate

### Minggu Ini (27 Feb - 6 Mar 2026)

**Priority: Critical Gaps**

```
Day 1-2: Create Missing Controllers
  - ManualOrderController.php (2h)
  - ChatController.php (3h)
  - Test both controllers (2h)

Day 3: Error Response Standardization
  - Create helper trait (2h)
  - Audit & update controllers (6h)

Day 4: Environment & Deployment
  - Update .env.example (2h)
  - Create deployment checklist (4h)

Day 5: Documentation Updates
  - Update sequence diagram (2h)
  - Update API reference (2h)
  - Create troubleshooting guide (4h)
```

**Total:** 27 jam (~3.5 hari kerja)

---

## 📊 BAGIAN 11: Risk Assessment

### Technical Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Payment gateway delay | Medium | High | Launch with COD first |
| GPS tracking battery drain | High | Medium | Optimize interval, offer toggle |
| Server downtime during launch | Low | High | Load balancer + auto-scaling |
| Data loss | Low | Critical | Multiple backups, tested restore |
| Security breach | Low | Critical | Security audit, HTTPS, input validation |

### Business Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Low merchant adoption | Medium | High | Free trial period, incentives |
| Courier resistance to app | Medium | High | Training, support, no iuran policy |
| Customer churn | Medium | Medium | Loyalty program, excellent support |
| Competition | Low | Medium | Focus on local differentiation |

---

## 🔄 BAGIAN 12: Continuous Improvement

### Weekly Rituals

```
Monday:
  - Sprint planning (1h)
  - Review backlog priorities

Friday:
  - Progress review (1h)
  - Update progress-log.md
  - Demo new features

Daily (Optional):
  - Standup via chat
  - Blocker removal
```

### Monthly Rituals

```
End of Month:
  - Retrospective (2h)
  - Metrics review
  - Next month planning
  - Documentation audit
```

### Quarterly Rituals

```
End of Quarter:
  - Strategic review
  - Roadmap adjustment
  - Business model validation
  - Major feature planning
```

---

## 📚 BAGIAN 13: Reference Documents

### Internal Documentation
```
📄 comprehensive-plan.md (this file)
📄 active-backlog.md
📄 progress-log.md
📄 project-planning.md
📄 work-plan.md
📄 technical-specifications.md
```

### Business Documentation
```
📄 company/business-model.md
📄 company/company-profile.md
📄 company/growth-roadmap.md
📄 company/problems-and-solutions.md
```

### Technical Documentation
```
📄 api/api-reference.md
📄 api/user-api.md
📄 api/merchant-api.md
📄 api/courier-api.md
📄 architecture/erd-diagram.md
📄 architecture/class-diagram.md
📄 architecture/sequence-diagram.md
📄 architecture/dfd-level-0.md
📄 architecture/dfd-level-1.md
```

### External Resources
```
🔗 Laravel Documentation: https://laravel.com/docs
🔗 Flutter Documentation: https://docs.flutter.dev
🔗 Firebase Documentation: https://firebase.google.com/docs
🔗 Filament Documentation: https://filamentphp.com/docs
```

---

## ✨ Notes

- Dokumen ini **hidup** dan akan diperbarui setiap sprint
- Update status di `active-backlog.md` setiap selesai task
- Log semua pekerjaan di `progress-log.md`
- Prioritas dapat berubah berdasarkan feedback & business needs

---

**Last Updated:** 27 Februari 2026  
**Next Review:** 6 Maret 2026 (end of Sprint 12-13)  
**Owner:** Aswar (Project Lead)
