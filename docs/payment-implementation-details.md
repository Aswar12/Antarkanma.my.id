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

#### A. Tabel Orders
```sql
- order_id (PK)
- customer_id (FK)
- merchant_id (FK)
- total_food_price
- delivery_fee
- platform_fee
- payment_status
- delivery_status
- created_at
- updated_at
```

#### B. Tabel Payments
```sql
- payment_id (PK)
- order_id (FK)
- payment_proof
- bank_from
- bank_reference
- verified_at
- verified_by
- status
- notes
```

#### C. Tabel Delivery_Fees
```sql
- delivery_fee_id (PK)
- order_id (FK)
- courier_id (FK)
- amount
- platform_fee
- collected_at
- remitted_at
- status
```

### 2. Status Tracking

#### A. Payment Status
- WAITING_PAYMENT
- PAYMENT_UPLOADED
- PAYMENT_VERIFIED
- PAYMENT_REJECTED
- EXPIRED

#### B. Delivery Status
- WAITING_PICKUP
- PICKED_UP
- DELIVERING
- DELIVERED
- COMPLETED
- CANCELLED

#### C. Fee Collection Status
- PENDING
- COLLECTED
- REMITTED
- OVERDUE

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
- Pickup reminders
- Fee collection reminders
- Settlement reminders

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
