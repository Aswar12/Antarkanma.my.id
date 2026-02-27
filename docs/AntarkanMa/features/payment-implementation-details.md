# Detail Implementasi Sistem Pembayaran Antarkanma

## Alur Transaksi Detail

### 1. Proses Order
1. Customer membuat pesanan:
   - Pilih merchant
   - Pilih menu
   - Input alamat pengantaran
   - Sistem hitung total:
     * Harga makanan
     * Ongkir (berdasarkan jarak)

2. Sistem generate:
   - Order ID
   - Payment instructions
   - Nomor rekening merchant
   - Total yang harus ditransfer
   - Batas waktu pembayaran (30 menit)

### 2. Proses Pembayaran Makanan

#### A. Transfer ke Merchant
1. Customer dapat info rekening merchant
2. Customer transfer total harga makanan
3. Customer upload bukti transfer:
   - Foto/screenshot bukti transfer
   - Nomor referensi transfer
   - Waktu transfer
   - Bank pengirim

#### B. Verifikasi Pembayaran
1. Merchant cek rekening
2. Merchant konfirmasi di aplikasi:
   - Konfirmasi jumlah sesuai
   - Input nomor referensi
   - Update status pembayaran
   - Timestamp verifikasi

### 3. Proses Pengantaran & COD Ongkir

#### A. Pengambilan Order
1. Merchant siapkan pesanan
2. Kurir terima order detail:
   - Rincian pesanan
   - Total ongkir
   - Alamat pickup & delivery
   - Fee platform (Rp 2.000)

#### B. Pembayaran Ongkir
1. Kurir antar pesanan
2. Customer bayar ongkir cash
3. Kurir input di aplikasi:
   - Konfirmasi terima ongkir
   - Upload foto serah terima
   - Update status delivery

## Sistem Pencatatan

### 1. Database Records

#### A. Tabel Transactions
```sql
- id (PK)
- user_id (FK) -> users.id
- user_location_id (FK) -> user_locations.id
- total_price (decimal 10,2)
- shipping_price (decimal 10,2)
- payment_date (datetime, nullable)
- status (enum):
  * PENDING
  * COMPLETED
  * CANCELED
- payment_method (enum):
  * MANUAL (COD)
  * ONLINE
- payment_status (enum):
  * PENDING
  * COMPLETED
  * FAILED
- courier_approval (enum):
  * PENDING
  * APPROVED
  * REJECTED
- timeout_at (timestamp, nullable)
- rating (integer, nullable)
- note (text, nullable)
- created_at
- updated_at
```

#### B. Tabel Orders
```sql
- id (PK)
- transaction_id (FK) -> transactions.id
- user_id (FK) -> users.id
- merchant_id (FK) -> merchants.id
- total_amount (decimal 10,2)
- order_status (enum):
  * PENDING
  * WAITING_APPROVAL
  * PROCESSING
  * READY_FOR_PICKUP
  * PICKED_UP
  * COMPLETED
  * CANCELED
- merchant_approval (enum):
  * PENDING
  * APPROVED
  * REJECTED
- created_at
- updated_at
```

#### C. Tabel OrderItems
```sql
- id (PK)
- order_id (FK) -> orders.id
- product_id (FK) -> products.id
- quantity
- price
- created_at
- updated_at
```

### 2. Status Tracking

#### A. Transaction Status
- PENDING: Transaksi baru dibuat
- COMPLETED: Transaksi selesai
- CANCELED: Transaksi dibatalkan

#### B. Payment Status
- PENDING: Menunggu pembayaran
- COMPLETED: Pembayaran selesai
- FAILED: Pembayaran gagal

#### C. Order Status
- PENDING: Order baru dibuat
- WAITING_APPROVAL: Menunggu persetujuan merchant
- PROCESSING: Sedang diproses merchant
- READY_FOR_PICKUP: Siap diambil kurir
- PICKED_UP: Sudah diambil kurir
- COMPLETED: Order selesai
- CANCELED: Order dibatalkan

#### D. Approval Status
1. Merchant Approval:
   - PENDING: Menunggu persetujuan
   - APPROVED: Disetujui
   - REJECTED: Ditolak

2. Courier Approval:
   - PENDING: Menunggu persetujuan
   - APPROVED: Disetujui
   - REJECTED: Ditolak

## Notifikasi System

### 1. Customer Notifications
- Payment instructions
- Payment verification status
- Order status updates
- Delivery updates

### 2. Merchant Notifications
- New order alerts
- Payment upload notifications
- Courier assignment
- Order completion

### 3. Courier Notifications
- New order assignments
- Order approval requests
- Order status updates
- Pickup reminders
- Delivery confirmations
- Rating notifications

## Reporting System

### 1. Financial Reports
- Daily transactions
- Fee collections
- Settlement status
- Outstanding payments

### 2. Operational Reports
- Order statistics
- Delivery performance
- Payment verification times
- Issue tracking

### 3. Reconciliation
- Daily fee reconciliation
- Weekly settlement review
- Monthly performance analysis
- Issue resolution tracking

## Security Measures

### 1. Payment Verification
- Double verification system
- Reference number matching
- Amount verification
- Timestamp validation

### 2. Delivery Verification
- GPS tracking
- Photo evidence
- Customer confirmation
- Digital receipt

### 3. Fee Collection
- Daily reconciliation
- Automated tracking
- Reminder system
- Penalty system
