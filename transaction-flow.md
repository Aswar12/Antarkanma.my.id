# Flow Transaksi dan Order

## Struktur Data

### Transaction (1)
- courier_id (1 kurir untuk 1 transaksi)
- courier_approval (PENDING/APPROVED/REJECTED)
- timeout_at (5 menit dari pembuatan)
- status (PENDING/COMPLETED/CANCELED)
- payment_method (MANUAL/ONLINE)
- payment_status (PENDING/COMPLETED/FAILED)

### Orders (many, per merchant)
- merchant_id
- merchant_approval (PENDING/APPROVED/REJECTED)
- order_status (PENDING/WAITING_APPROVAL/PROCESSING/READY/PICKED_UP/COMPLETED/CANCELED)
- OrderItems (many, untuk merchant tersebut)

## Flow Status

### A. Transaction Flow
1. PENDING (baru dibuat)
   - Mencari Kurir (timeout 5 menit)
     * Kurir Approve -> Lanjut ke Merchant Approval
     * Timeout/No Kurir -> CANCELED
   - Merchant Approval
     * Semua Merchant Reject -> CANCELED
     * Min 1 Merchant Approve -> COMPLETED (setelah delivery)
   - Payment (parallel dengan approval)
     * ONLINE: Harus COMPLETED sebelum proses
     * MANUAL: COMPLETED setelah delivery

### B. Order Flow (per merchant)
1. PENDING (baru dibuat)
   - Order belum muncul di merchant dashboard
   - Sistem mencari kurir (timeout 5 menit)

2. WAITING_APPROVAL (setelah kurir approve)
   - Order muncul di merchant dashboard
   - Merchant bisa approve/reject
   - Merchant reject jika:
     * Stok produk habis
     * Toko sedang tutup/sibuk
     * Ada masalah dengan pesanan

3. PROCESSING (merchant approve)
   - Merchant mulai siapkan pesanan
   - Update ke READY saat pesanan siap

4. READY (siap diambil kurir)
   - Menunggu kurir pickup

5. PICKED_UP (kurir ambil pesanan)
   - Kurir menuju customer

6. COMPLETED (sudah diantar)
   - Order selesai

## API Merchant

### 1. Melihat Order
```
GET /api/merchant/{merchantId}/orders
- Hanya menampilkan order dengan status WAITING_APPROVAL ke atas
- Tidak menampilkan order PENDING (belum ada kurir)
- Bisa filter berdasarkan status
```

### 2. Approve Order
```
PUT /merchants/orders/{orderId}/approve
Request: merchant_id
Response:
- merchant_approval: APPROVED
- order_status: PROCESSING
```

### 3. Reject Order
```
PUT /merchants/orders/{orderId}/reject
Request: merchant_id
Response:
- merchant_approval: REJECTED
- order_status: CANCELED
- Jika semua order canceled -> transaction juga canceled
```

### 4. Ready for Pickup
```
PUT /merchants/orders/{orderId}/ready
Request: merchant_id
Response:
- order_status: READY
```

## Notifikasi

1. Ke User:
- Order approved: "Your order has been approved and is being processed"
- Order rejected: "Your order has been rejected by the merchant"
- Order ready: "Your order is ready for pickup"

2. Ke Merchant:
- New order (setelah kurir approve)
- Order picked up by courier

## Catatan Penting

1. Efisiensi Proses:
- Order hanya muncul di merchant setelah ada kurir (WAITING_APPROVAL)
- Mengurangi "noise" dari order yang mungkin dibatalkan
- Merchant bisa langsung fokus pada order yang siap diproses

2. Validasi Status:
- Merchant hanya bisa approve/reject order dengan status WAITING_APPROVAL
- Merchant hanya bisa update ke READY dari status PROCESSING
- Setiap perubahan status menggunakan database transaction

3. Keamanan:
- Validasi ownership setiap order
- Validasi status sebelum setiap perubahan
- Atomic updates dengan database transaction
