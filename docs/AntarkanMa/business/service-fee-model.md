# 💰 Service Fee Model — AntarkanMa

> **Decision Date:** 10 Maret 2026
> **Status:** ✅ Approved
> **Implementation Priority:** 🔴 High
> **Last Updated:** 12 Maret 2026 — Changed to **Rp 500 per transaksi** (bukan per order)

---

## 📋 Executive Summary

Meninggalkan model "membakar uang" a-la startup konvensional, AntarkanMa fokus pada profitabilitas per transaksi melalui **Sustainable Cashflow Model**.

**Keputusan (Revisi Blueprint 10 Mar 2026, Updated 12 Mar 2026):**
- ✅ **Service Fee: Rp 500/transaksi** (ditampilkan transparan ke customer)
  - **IMPORTANT:** Dikenakan **sekali per transaksi**, bukan per order
  - Satu transaksi bisa berisi multiple orders dari berbagai merchant
  - Customer hanya bayar Rp 500 sekali, tidak peduli berapa merchant
- ✅ **Platform Fee / Potongan Kurir: 10% dari base ongkir** (untuk cashflow operational)
- ✅ **Merchant Commission: 0%** (GRATIS untuk merchant)
- ✅ **Withdrawal Fee: Rp 1.000** (per penarikan manual)
- ✅ **Minimum Withdraw: Rp 50.000**

---

## 🎯 Business Model

### **Revenue Streams**

| Source | Amount | Who Pays | Frequency |
|--------|--------|----------|-----------|
| **Service Fee** | Rp 500 | Customer | **Per transaksi** (bukan per order) |
| **Platform Fee** | 10% dari base ongkir | Dipotong dari penghasilan Kurir | Per order |
| **Withdrawal Fee** | Rp 1.000 | Courier | Per withdrawal manual |

**📝 Note:** 
- **Per Transaksi** = Customer hanya bayar Rp 500 sekali, tidak peduli berapa merchant dalam satu transaksi
- **Per Order** = Jika customer buat 3 transaksi terpisah, bayar 3 × Rp 500 = Rp 1.500

### **Fee Structure (Contoh)**

#### **Single Merchant Transaction:**

```
┌─────────────────────────────────────────────────────────┐
│  CUSTOMER PAYMENT (Via Tunai - COD ke Kurir)            │
├─────────────────────────────────────────────────────────┤
│  Harga Makanan:    Rp 50.000                            │
│  Ongkir Total:     Rp  7.500 (Base Rp 7k + Serv Rp 500) │
│  ─────────────────────────────────────────              │
│  TOTAL BAYAR:      Rp 57.500                            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  DISTRIBUTION & CASHFLOW                                │
├─────────────────────────────────────────────────────────┤
│  Porsi Merchant:     Rp 50.000 (100% dari harga makan)  │
│  Porsi Kurir (Net):  Rp  6.300 (Base 7k - 10% Platform) │
│  Porsi Perusahaan:   Rp  1.200 (Service 500 + Plat 700) │
│  ─────────────────────────────────────────              │
│  TOTAL:              Rp 57.500 ✅ Balanced              │
└─────────────────────────────────────────────────────────┘
```

#### **Multi-Merchant Transaction (3 Merchant):**

```
┌─────────────────────────────────────────────────────────┐
│  CUSTOMER PAYMENT (Via Tunai - COD ke Kurir)            │
├─────────────────────────────────────────────────────────┤
│  Merchant 1:       Rp 30.000                            │
│  Merchant 2:       Rp 25.000                            │
│  Merchant 3:       Rp 20.000                            │
│  Ongkir Total:     Rp  9.000 (optimized route)          │
│  Service Fee:      Rp    500 (ONLY ONCE!)               │
│  ─────────────────────────────────────────              │
│  TOTAL BAYAR:      Rp 84.500                            │
│                                                         │
│  💡 Customer HEMAT! Jika 3 transaksi terpisah:          │
│     - Service Fee: 3 × Rp 500 = Rp 1.500               │
│     - Sekarang: Rp 500 saja!                           │
│     - Hemat: Rp 1.000!                                 │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  DISTRIBUTION & CASHFLOW                                │
├─────────────────────────────────────────────────────────┤
│  Merchant 1:       Rp 30.000 (100%)                     │
│  Merchant 2:       Rp 25.000 (100%)                     │
│  Merchant 3:       Rp 20.000 (100%)                     │
│  Porsi Kurir (Net):  Rp  8.100 (Base 9k - 10% Plat)     │
│  Porsi Perusahaan:   Rp  1.400 (Service 500 + Plat 900) │
│  ─────────────────────────────────────────              │
│  TOTAL:              Rp 84.500 ✅ Balanced              │
└─────────────────────────────────────────────────────────┘
```

---

## 💸 Withdrawal System (Manual Transfer)

Karena integrasi *Payment Gateway* memakan biaya operasional tinggi, sistem pencairan (Withdrawal) dilakukan secara **manual** melalui Request yang diproses Human Admin.

### **Rules**

| Parameter | Value |
|-----------|-------|
| **Minimum Withdraw** | Rp 50.000 |
| **Withdrawal Fee** | Rp 1.000 (Pendapatan admin) |
| **Processing Time** | 1 hari kerja (SLA 24 Jam) |
| **Method** | Bank transfer Manual via Admin |
| **Approval** | Admin / Exec di Filament Panel |

### **Withdrawal Flow**

```
┌─────────────────────────────────────────────────────────┐
│  1. KURIER REQUEST                                      │
│                                                         │
│  Amount: Rp 100.000                                     │
│  Fee: Rp 1.000                                          │
│  ─────────────────────────                              │
│  Total Deducted: Rp 101.000                             │
│  Status: PENDING                                        │
└─────────────────────────────────────────────────────────┘
         │
         │ Admin approve
         ▼
┌─────────────────────────────────────────────────────────┐
│  2. ADMIN TRANSFER                                      │
│                                                         │
│  Bank: BCA                                              │
│  Account: 1234567890                                    │
│  Amount: Rp 99.000 (100.000 - 1.000)                    │
│  Status: PROCESSING                                     │
└─────────────────────────────────────────────────────────┘
         │
         │ Transfer completed
         ▼
┌─────────────────────────────────────────────────────────┐
│  3. COMPLETED                                           │
│                                                         │
│  Status: COMPLETED                                      │
│  Paid At: 2026-03-10 14:30:00                          │
│  Verified By: Admin                                     │
└─────────────────────────────────────────────────────────┘
```

### **Database Schema**

```php
Schema::create('withdrawals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('courier_id')->constrained();
    $table->decimal('amount', 10, 2); // Amount requested
    $table->decimal('fee', 10, 2)->default(1000); // Fixed Rp 1.000
    $table->decimal('total_amount', 10, 2); // amount - fee
    
    $table->enum('status', [
        'PENDING',      // Menunggu approval admin
        'APPROVED',     // Approved, siap transfer
        'PROCESSING',   // Sedang ditransfer
        'COMPLETED',    // Selesai
        'REJECTED'      // Ditolak
    ])->default('PENDING');
    
    $table->string('bank_account_name');
    $table->string('bank_account_number');
    $table->string('bank_name');
    $table->text('admin_note')->nullable();
    
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('paid_at')->nullable();
    
    $table->timestamps();
    
    $table->index(['courier_id', 'status']);
    $table->index('created_at');
});
```

---

## 🏪 Merchant Financial Flow

### **Payment Settlement**

```
Merchant Model: 0% Commission (GRATIS)

Customer Bayar:
├─ Harga Makanan: Rp 50.000
└─ Merchant Diterima: Rp 50.000 (100%)

Platform TIDAK potong commission dari merchant
```

### **Why 0% Commission?**

✅ **Merchant Acquisition** - Mudah onboard merchant baru  
✅ **Competitive Advantage** - GoFood/GrabFood charge 20-25%  
✅ **Revenue dari Source Lain** - Service fee + platform fee sudah cukup  
✅ **Long-term Partnership** - Build trust dengan merchant  

---

## 📊 Revenue Projection

### **Per Transaction Breakdown**

#### **Single Merchant Transaction:**

```
Asumsi: Base ongkir Rp 7.000

Customer Pays:
├─ Makanan: Rp 50.000
├─ Ongkir: Rp 7.000
├─ Service Fee: Rp 500
└─ Total: Rp 57.500

Platform Revenue:
├─ Service Fee: Rp 500
├─ Platform Fee (10%): Rp 700
└─ Total: Rp 1.200/transaction

Courier Earning:
├─ Base Ongkir: Rp 7.000
├─ Platform Fee (10%): Rp 700
└─ Diterima: Rp 6.300
```

#### **Multi-Merchant Transaction (3 merchants):**

```
Asumsi: Base ongkir Rp 9.000 (optimized route)

Customer Pays:
├─ Makanan (3 merchant): Rp 75.000
├─ Ongkir: Rp 9.000
├─ Service Fee: Rp 500 (ONLY ONCE!)
└─ Total: Rp 84.500

Platform Revenue:
├─ Service Fee: Rp 500
├─ Platform Fee (10%): Rp 900
└─ Total: Rp 1.400/transaction

Courier Earning:
├─ Base Ongkir: Rp 9.000
├─ Platform Fee (10%): Rp 900
└─ Diterima: Rp 8.100
```

### **Monthly Projection**

**Scenario: 5 Kurir Aktif, 5 Transaction/Hari/Kurir**

```
Daily:
├─ Total Transactions: 5 kurir × 5 tx = 25 tx
├─ Service Fee Revenue: 25 × Rp 500 = Rp 12.500
├─ Platform Fee Revenue: 25 × Rp 750 (avg) = Rp 18.750
└─ Daily Total: Rp 31.250

Monthly (25 hari kerja):
├─ Service Fee: Rp 12.500 × 25 = Rp 312.500
├─ Platform Fee: Rp 18.750 × 25 = Rp 468.750
├─ Withdrawal Fees: 5 kurir × 4x × Rp 1.000 = Rp 20.000
└─ Monthly Total: Rp 801.250

💡 Multi-Merchant Bonus:
   Jika 40% transaksi adalah multi-merchant (avg 2.5 merchant/tx):
   - Customer lebih hemat (tidak perlu bayar service fee berulang)
   - Platform tetap dapat revenue dari platform fee per order
   - Customer loyalty meningkat!
```

**Scenario: 10 Kurir Aktif, 7 Transaction/Hari/Kurir**

```
Daily:
├─ Total Transactions: 10 kurir × 7 tx = 70 tx
├─ Service Fee Revenue: 70 × Rp 500 = Rp 35.000
├─ Platform Fee Revenue: 70 × Rp 750 (avg) = Rp 52.500
└─ Daily Total: Rp 87.500

Monthly (25 hari kerja):
├─ Service Fee: Rp 35.000 × 25 = Rp 875.000
├─ Platform Fee: Rp 52.500 × 25 = Rp 1.312.500
├─ Withdrawal Fees: 10 kurir × 4x × Rp 1.000 = Rp 40.000
└─ Monthly Total: Rp 2.227.500

💡 Multi-Merchant Bonus:
   Jika 50% transaksi adalah multi-merchant (avg 3 merchant/tx):
   - Average platform fee per tx: Rp 1.100 (higher base ongkir)
   - Platform Fee Revenue: 70 × Rp 1.100 = Rp 77.000/hari
   - Total Monthly: Rp 2.547.500 (increase 14%!)
```

---

## 🎯 Why Transparent Model?

### **Comparison: Stealth vs Transparent**

| Factor | Stealth Fee | Transparent Fee | Winner |
|--------|-------------|-----------------|--------|
| **Customer Trust** | ❌ Low | ✅ High | Transparent |
| **Legal Risk** | ❌ High | ✅ Low | Transparent |
| **Brand Reputation** | ❌ Risky | ✅ Positive | Transparent |
| **Short-term Conversion** | ✅ Higher | ⚠️ Slightly lower | Stealth |
| **Long-term Loyalty** | ❌ Low | ✅ High | Transparent |
| **Ethical** | ❌ Questionable | ✅ Right | Transparent |
| **Investor Appeal** | ❌ Risky | ✅ Sustainable | Transparent |

**Score: Transparent 7-1 Stealth** 🏆

### **Why We Chose Transparent**

1. ✅ **Build Trust** - Customer appreciate honesty
2. ✅ **Legal Compliant** - Follow UU No. 8 Tahun 1999 (Consumer Protection)
3. ✅ **Brand Value** - Known as honest platform
4. ✅ **Long-term Loyalty** - Trust > Short-term revenue
5. ✅ **Peace of Mind** - No ethical concerns
6. ✅ **Investor Friendly** - Sustainable business model

---

## 📱 UI/UX Guidelines

### **Checkout Page (Customer App)**

```dart
Column(
  crossAxisAlignment: CrossAxisAlignment.start,
  children: [
    Text('Rincian Pembayaran', style: headingStyle),
    SizedBox(height: 12),
    
    _buildRow('Subtotal', formatCurrency(subtotal)),
    _buildRow('Ongkir', formatCurrency(shippingFee)),
    _buildRow('Service Fee', formatCurrency(serviceFee)),
    
    Divider(height: 24),
    
    _buildRow('Total', formatCurrency(grandTotal), isBold: true),
    
    SizedBox(height: 16),
    
    // Info box
    Container(
      padding: EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: primaryColor.withOpacity(0.05),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Icon(Icons.info_outline, color: primaryColor, size: 20),
          SizedBox(width: 8),
          Expanded(
            child: Text(
              'Service Fee Rp 500/transaksi (bukan per order!). Lebih hemat untuk multi-merchant!',
              style: TextStyle(fontSize: 11, color: secondaryTextColor),
            ),
          ),
        ],
      ),
    ),
  ],
)
```

### **Order Detail (Courier App)**

```dart
Column(
  children: [
    _buildRow('Jarak', '$distance km'),
    _buildRow('Base Ongkir', formatCurrency(baseShipping)),
    _buildRow('Platform Fee (10%)', '- ' + formatCurrency(platformFee)),
    Divider(),
    _buildRow('Penghasilan Anda', formatCurrency(courierEarning), 
              isBold: true, color: primaryColor),
  ],
)
```

---

## 🔧 Implementation Checklist

### **Backend**

- [ ] **Migration: Add service_fee fields**
  - [ ] `service_fee` to transactions table
  - [ ] `base_shipping_price` to transactions table
  - [ ] Update existing migrations

- [ ] **Create withdrawals table**
  - [ ] Migration for withdrawals
  - [ ] Withdrawal model
  - [ ] Withdrawal controller
  - [ ] Admin approval UI (Filament)

- [ ] **Create wallet_transactions table**
  - [ ] Migration for wallet transactions
  - [ ] WalletTransaction model
  - [ ] Auto-log all wallet movements

- [ ] **Update OsrmService**
  - [ ] Add SERVICE_FEE constant
  - [ ] Update calculateShipping() method
  - [ ] Return base_shipping + service_fee

- [ ] **Update TransactionController**
  - [ ] Include service_fee in calculation
  - [ ] Save base_shipping and service_fee separately
  - [ ] Update response format

- [ ] **Update CourierController**
  - [ ] Auto-deduct platform fee on completeOrder
  - [ ] Log wallet transactions
  - [ ] Update withdraw() method with new rules

### **Mobile Apps**

- [ ] **Customer App**
  - [ ] Update checkout page UI
  - [ ] Show service_fee breakdown
  - [ ] Add info tooltip
  - [ ] Update order confirmation

- [ ] **Courier App**
  - [ ] Update order detail page
  - [ ] Show earning breakdown
  - [ ] Update wallet page
  - [ ] Add withdrawal request UI

### **Documentation**

- [ ] Update API documentation
- [ ] Update user guide
- [ ] Update FAQ
- [ ] Create merchant onboarding guide

---

## 📚 API Endpoints

### **Transaction Creation**

**Endpoint:** `POST /api/transactions`

**Request:**
```json
{
  "order_items": [...],
  "delivery_location": {...},
  "payment_method": "ONLINE"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "ANT-123",
    "total_price": 50000,
    "base_shipping_price": 7000,
    "service_fee": 500,
    "shipping_price": 7500,
    "platform_fee": 700,
    "grand_total": 57500,
    "courier_earning": 6300
  }
}
```

### **Courier Withdrawal**

**Endpoint:** `POST /api/courier/wallet/withdraw`

**Request:**
```json
{
  "amount": 100000,
  "bank_account_name": "John Doe",
  "bank_account_number": "1234567890",
  "bank_name": "BCA"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "amount": 100000,
    "fee": 1000,
    "total_amount": 99000,
    "status": "PENDING",
    "bank_account_name": "John Doe",
    "bank_account_number": "1234567890",
    "bank_name": "BCA"
  },
  "message": "Withdrawal berhasil diajukan"
}
```

---

## ⚠️ Important Notes

### **DO NOT:**

1. ❌ Calculate platform fee from total ongkir (must be from BASE)
2. ❌ Hide service fee from customer
3. ❌ Charge courier fee per order (it's FREE)
4. ❌ Charge merchant commission (it's 0%)
5. ❌ Allow withdrawal < Rp 50.000

### **MUST DO:**

1. ✅ Always display service fee transparently
2. ✅ Calculate platform fee from base_shipping only
3. ✅ Log all wallet transactions
4. ✅ Require admin approval for withdrawals
5. ✅ Explain value of service fee to customer
6. ✅ **Apply service fee ONCE per transaction (NOT per order!)**
7. ✅ **Highlight multi-merchant savings to customers**

---

## 🔗 Related Documents

- [Transaction Flow](transaction-flow.md)
- [Delivery Cost Calculation](delivery-cost-calculation.md)
- [Wallet System Design](wallet-system-design.md)

---

**Last Updated:** 12 Maret 2026 — **Changed to Rp 500 per transaksi (bukan per order)**
**Status:** ✅ Approved for Implementation
**Next Review:** After implementation complete
