# Data Flow & System Design — Antarkanma

> **Versi**: v2.1 — 24 Februari 2026  
> **SDLC Phase**: Implementation (Sprint 3)  
> Dokumen ini merangkum arsitektur, business logic, dan data flow aktual sistem Antarkanma.

---

## 1. Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────────┐
│                         MOBILE APPS (Flutter)                        │
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────────┐  │
│  │ Customer App │  │ Merchant App │  │     Courier App          │  │
│  │              │  │              │  │                          │  │
│  │ GetX, FCM    │  │ GetX, FCM    │  │ GetX, FCM, OSRM          │  │
│  └──────┬───────┘  └──────┬───────┘  └─────────────┬────────────┘  │
└─────────┼─────────────────┼───────────────────────-─┼──────────────┘
          │                 │ REST API (HTTPS)          │
          ▼                 ▼                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    BACKEND (Laravel 11)                               │
│                                                                      │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │                    Routes (api.php)                          │    │
│  └──────────────────────────┬──────────────────────────────────┘    │
│                             │                                        │
│  ┌──────────────┐  ┌────────┴───────┐  ┌────────────────────────┐  │
│  │Transaction   │  │  CourierCtrl   │  │  MerchantOrderCtrl     │  │
│  │Controller    │  │                │  │                        │  │
│  └──────┬───────┘  └───────┬────────┘  └───────────┬────────────┘  │
│         │                  │                        │               │
│  ┌──────▼──────────────────▼────────────────────────▼────────────┐ │
│  │                      Services Layer                            │ │
│  │   FirebaseService │ OsrmService │ ShippingCalculator           │ │
│  └──────┬────────────────────────────────────┬────────────────────┘ │
│         │                                    │                      │
│  ┌──────▼──────────────────────────┐  ┌──────▼──────────────────┐  │
│  │         Database (MySQL)         │  │  External Services       │  │
│  │  transactions, orders,           │  │  Firebase FCM / OSRM    │  │
│  │  order_items, users,             │  └─────────────────────────┘  │
│  │  merchants, couriers, etc.       │                               │
│  └─────────────────────────────────┘                               │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 2. Entity Relationship (Ringkas)

```
User (customer) ─┐
                  ├── Transaction ──┬── Order ── OrderItem ── Product ── Merchant
User (courier) ──┘                 │
                                   └── Orders (1 per merchant dalam transaksi)
```

Lihat detail: [ERD Diagram](./erd-diagram.md)

---

## 3. Status State Machine

### Order Status

```
PENDING → WAITING_APPROVAL → PROCESSING → READY_FOR_PICKUP → PICKED_UP → COMPLETED
                    ↓                             ↑
                CANCELED ─────────────────────────┘ (bisa cancel dari status manapun)
```

### Transaction courier_status *(Ditambah Feb 2026)*

```
IDLE → HEADING_TO_MERCHANT → AT_MERCHANT → HEADING_TO_CUSTOMER → AT_CUSTOMER → DELIVERED
```

Lihat detail lengkap: [Transaction & Order Flow](../transaction-order-flow.md)

---

## 4. Data Model Aktual

### 4.1 Transaction

```
Transaction {
    id, user_id, user_location_id, courier_id, base_merchant_id
    total_price, shipping_price, payment_date
    status: PENDING | COMPLETED | CANCELED
    payment_method: MANUAL | ONLINE
    payment_status: PENDING | COMPLETED | FAILED
    courier_approval: PENDING | APPROVED | REJECTED
    courier_status: IDLE | HEADING_TO_MERCHANT | AT_MERCHANT |
                    HEADING_TO_CUSTOMER | AT_CUSTOMER | DELIVERED  ← Baru Feb 2026
    timeout_at, rating, note
}
```

### 4.2 Order

```
Order {
    id, transaction_id, user_id, merchant_id
    total_amount
    order_status: PENDING | WAITING_APPROVAL | PROCESSING |
                  READY_FOR_PICKUP | PICKED_UP | COMPLETED | CANCELED
    merchant_approval: PENDING | APPROVED | REJECTED
    rejection_reason, customer_note
}
```

### 4.3 Tabel Pendukung
- `order_items`: produk dalam setiap order
- `users`: semua aktor (roles: USER/MERCHANT/COURIER/ADMIN)
- `merchants`: toko dengan koordinat GPS
- `couriers`: data kurir + wallet
- `user_locations`: alamat pengiriman customer
- `fcm_tokens`: token notifikasi per device
- `products`, `product_categories`, `product_galleries`

---

## 5. Alur Data Lengkap

### 5.1 Customer Checkout
```
Customer → POST /api/transactions
         ← System hitung ongkir (OSRM)
         ← CREATE Transaction (PENDING)
         ← CREATE Orders per merchant (WAITING_APPROVAL)
         ← FCM push ke merchant(s)
```

### 5.2 Merchant Flow
```
Merchant → Terima FCM notifikasi
         → PUT /api/merchants/orders/{id}/approve → PROCESSING
         → PUT /api/merchants/orders/{id}/ready   → READY_FOR_PICKUP
           [Pesanan muncul di list kurir]
```

### 5.3 Courier Flow (Updated Feb 2026)
```
Courier → GET /api/courier/new-transactions (filter: READY_FOR_PICKUP)
        → POST .../approve           → courier_status: HEADING_TO_MERCHANT
                                       [Order status TIDAK berubah = FIX BUG]
        → POST .../arrive-merchant   → courier_status: AT_MERCHANT
        → POST .../orders/{id}/pickup  → order_status: PICKED_UP (per order)
                                        [jika semua: courier_status: HEADING_TO_CUSTOMER]
        → POST .../arrive-customer   → courier_status: AT_CUSTOMER
        → POST .../orders/{id}/complete → order_status: COMPLETED (per order)
                                          [jika semua: Transaction COMPLETED + DELIVERED]
```

---

## 6. Formula Ongkir

```
Single merchant (≤3 km) : Rp 5.000
Single merchant (>3 km) : 5.000 + (jarak-3) × 2.500

Multi-merchant surcharge:
  2 merchant: +Rp 2.000
  3 merchant: +Rp 1.000/merchant tambahan

Revenue split:
  Kurir     : 90% dari ongkir
  Platform  : 10% dari ongkir + Rp 1.000/order (fee merchant)
```

---

## 7. Business Logic Rules

| # | Rule | Status |
|---|---|---|
| 1 | 1 Transaction = 1 Kurir (tidak split) | ✅ Aktif |
| 2 | Kurir hanya lihat order READY_FOR_PICKUP | ✅ Aktif |
| 3 | Order tidak bisa balik ke status sebelumnya | ✅ Aktif |
| 4 | Auto-complete Transaction saat semua order selesai | ✅ Aktif |
| 5 | Pickup bisa per-order (multi-merchant support) | ✅ Baru Feb 2026 |
| 6 | courier_status tracking real-time | ✅ Baru Feb 2026 |
| 7 | Auto-cancel setelah timeout | ⏸️ Dimatikan sementara |
| 8 | Partial fulfillment (1 merchant OK lainnya cancel) | ⬜ Belum ada |

---

## 8. API Endpoints (Ringkas)

Lihat detail: [Technical Specifications](../technical-specifications.md)

### Endpoint Baru (Feb 2026)
| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/api/courier/transactions/{id}/arrive-merchant` | Kurir lapor di merchant |
| POST | `/api/courier/transactions/{id}/arrive-customer` | Kurir lapor di customer |
| POST | `/api/courier/orders/{id}/pickup` | Pickup per-order |
| POST | `/api/courier/orders/{id}/complete` | Selesaikan per-order |

---

## 9. Notifikasi (FCM)

Payload selalu mengandung:
- `type` — jenis event
- `transaction_id` — untuk deep-link ke halaman detail
- `order_id` — (opsional) untuk order spesifik

Lihat Notification Matrix lengkap: [Transaction & Order Flow](../transaction-order-flow.md#6-notifikasi-matrix-fcm)

---

## 10. Known Issues & Roadmap

### ✅ Sudah Diperbaiki (Feb 2026)
- **BUG FIX**: `CourierController@approveTransaction` tidak lagi me-reset `order_status` ke `PROCESSING`

### ⬜ Next Sprint
- Customer App: Tracking real-time UI (stepper/timeline berdasarkan `courier_status`)
- Customer App: Refresh otomatis via FCM
- Courier App: Tampilkan ETA ke merchant / ke customer

---

*Terakhir diperbarui: 24 Februari 2026*
