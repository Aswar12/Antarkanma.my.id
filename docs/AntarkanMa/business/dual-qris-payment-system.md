# 💳 Dual QRIS Payment System

> **Implementation Date:** 11 Maret 2026
> **Status:** ✅ Implemented
> **Payment Method:** `QRIS_DUAL`
> **Last Updated:** 12 Maret 2026 — Service Fee **Rp 500 per transaksi**

---

## 📋 Executive Summary

Sistem pembayaran inovatif untuk early-stage startup delivery yang **TANPA PAYMENT GATEWAY** dan **TANPA SETTLEMENT RIBET**.

**Konsep:** Customer bayar terpisah via 2 QRIS:
1. **QRIS Merchant** → Untuk makanan (langsung ke merchant)
2. **QRIS Platform** → Untuk ongkir + service fee (langsung ke platform)

---

## 🎯 Problem Solved

### **❌ Masalah Startup Delivery Baru:**

```
❌ Payment gateway mahal (MDR 2-3%, setup cost Rp 500k-1jt)
❌ Settlement ribet (T+1, T+7 ke merchant)
❌ Platform pegang uang merchant (cashflow risk)
❌ Admin work tinggi (manual transfer ke merchant)
❌ Customer harus bayar 2x (ribet!)
```

### **✅ Solusi Dual QRIS:**

```
✅ NO payment gateway (save cost!)
✅ NO settlement (merchant dapat langsung)
✅ Platform TIDAK pegang uang merchant
✅ NO admin work (otomatis via QRIS)
✅ Customer bayar 2x TAPI digital & tracked
```

---

## 💡 How It Works

### **Flow Diagram:**

```
┌─────────────────────────────────────────────────────────┐
│  1. CUSTOMER CHECKOUT                                   │
└─────────────────────────────────────────────────────────┘
   │
   ├─ Pilih payment method: QRIS_DUAL
   ├─ Sistem calculate:
   │   ├─ Makanan: Rp 50.000
   │   ├─ Ongkir: Rp 7.000
   │   └─ Service fee: Rp 500 (per transaksi, bukan per order!)
   │
   └─ Sistem generate 2 QRIS URLs:
       ├─ QRIS Merchant: Rp 50.000
       └─ QRIS Platform: Rp 7.500

         │
         ▼

┌─────────────────────────────────────────────────────────┐
│  2. CUSTOMER PAYMENT                                    │
└─────────────────────────────────────────────────────────┘
   │
   ├─ Step 1: Scan QRIS Merchant
   │   └─ Bayar: Rp 50.000
   │       └─ Merchant dapat LANGSUNG ✅
   │
   ├─ Step 2: Scan QRIS Platform
   │   └─ Bayar: Rp 7.500
   │       └─ Platform dapat LANGSUNG ✅
   │
   └─ Upload bukti pembayaran kedua QRIS

         │
         ▼

┌─────────────────────────────────────────────────────────┐
│  3. SYSTEM VERIFICATION                                 │
└─────────────────────────────────────────────────────────┘
   │
   ├─ Admin verify proofs (manual for MVP)
   ├─ Update transaction status:
   │   ├─ Merchant paid → PARTIAL_PAID
   │   ├─ Platform paid → PARTIAL_PAID
   │   └─ Both paid → PAID ✅
   │
   └─ Auto-credit courier wallet: +Rp 6.300

         │
         ▼

┌─────────────────────────────────────────────────────────┐
│  4. FULFILLMENT                                         │
└─────────────────────────────────────────────────────────┘
   │
   ├─ Courier accept order
   ├─ Pickup → Deliver → Complete
   └─ Same as COD flow
```

---

## 📊 Payment Breakdown

### **Example Transaction:**

```
┌─────────────────────────────────────────────────────────┐
│  CUSTOMER PAYMENT                                       │
├─────────────────────────────────────────────────────────┤
│  Merchant QRIS:    Rp 50.000  → Merchant (direct)       │
│  Platform QRIS:    Rp  7.500  → Platform (direct)       │
│  ─────────────────────────────                          │
│  TOTAL:            Rp 57.500                            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  PLATFORM QRIS BREAKDOWN                                │
├─────────────────────────────────────────────────────────┤
│  Base Ongkir:      Rp  7.000                            │
│  Service Fee:      Rp    500  (per transaksi!)          │
│  ─────────────────────────────                          │
│  Total:            Rp  7.500                            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  DISTRIBUTION (Platform QRIS only)                      │
├─────────────────────────────────────────────────────────┤
│  Platform Fee (10%):  Rp    700  → Platform revenue     │
│  Courier Earning:     Rp  6.300  → Courier wallet       │
│  ─────────────────────────────                          │
│  Total:               Rp  7.500  ✅ Balanced            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  CASHFLOW SUMMARY                                       │
├─────────────────────────────────────────────────────────┤
│  Merchant:  +Rp 50.000  (direct from customer) ✅       │
│  Platform:  +Rp   700  (service fee + platform fee) ✅  │
│  Courier:   +Rp 6.300  (wallet credit) ✅               │
│                                                         │
│  NO MONEY HELD BY PLATFORM!                             │
│  NO SETTLEMENT NEEDED!                                  │
│                                                         │
│  💡 Service Fee charged ONCE per transaction!           │
└─────────────────────────────────────────────────────────┘
```

---

## 🗄️ Database Schema

### **Transactions Table Updates:**

```php
// New columns added via migration
Schema::table('transactions', function (Blueprint $table) {
    // Payment method enum
    $table->enum('payment_method', [
        'COD',
        'QRIS_DUAL',      // NEW
        'QRIS_PLATFORM',  // NEW (future)
        'MANUAL',
        'ONLINE',
    ])->default('COD')->change();
    
    // Payment status enum
    $table->enum('payment_status', [
        'PENDING',
        'PARTIAL_PAID',   // NEW
        'PAID',
        'COMPLETED',
        'FAILED',
    ])->default('PENDING')->change();
    
    // Amount split
    $table->decimal('merchant_amount', 10, 2);   // NEW
    $table->decimal('platform_amount', 10, 2);   // NEW
    $table->decimal('grand_total', 10, 2);       // NEW
    
    // QRIS URLs
    $table->string('merchant_qris_url');         // NEW
    $table->string('platform_qris_url');         // NEW
    
    // Payment timestamps
    $table->timestamp('merchant_paid_at');       // NEW
    $table->timestamp('platform_paid_at');       // NEW
    
    // Payment proofs
    $table->string('merchant_payment_proof');    // NEW
    $table->string('platform_payment_proof');    // NEW
    
    // Courier payout tracking
    $table->enum('courier_payout_status', [
        'PENDING',
        'CREDITED',      // NEW
        'WITHDRAWN',
        'FAILED',
    ])->default('PENDING');
    
    $table->timestamp('courier_paid_at');        // NEW
});
```

---

## 🔧 API Endpoints

### **1. Create Transaction (with Dual QRIS)**

**Endpoint:** `POST /api/transactions`

**Request:**
```json
{
    "user_location_id": 1,
    "payment_method": "QRIS_DUAL",
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "variant_id": null,
            "customer_note": "Jangan pedas"
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Transaction created successfully",
    "data": {
        "id": 123,
        "payment_method": "QRIS_DUAL",
        "payment_status": "PENDING",
        "merchant_amount": 50000,
        "platform_amount": 7500,
        "grand_total": 57500,
        "payment_info": {
            "payment_type": "DUAL_QRIS",
            "payments": [
                {
                    "type": "MERCHANT_QRIS",
                    "amount": 50000,
                    "description": "Pembayaran makanan ke merchant",
                    "qris_url": "https://merchant-qris.com/abc123",
                    "merchant_name": "Warung Madura"
                },
                {
                    "type": "PLATFORM_QRIS",
                    "amount": 7500,
                    "description": "Ongkir + Service Fee ke platform",
                    "qris_url": "https://platform-qris.com/xyz789",
                    "breakdown": {
                        "base_ongkir": 7000,
                        "service_fee": 500
                    }
                }
            ],
            "instructions": [
                "1. Scan QRIS Merchant untuk bayar makanan sebesar Rp 50.000",
                "2. Scan QRIS Platform untuk bayar ongkir sebesar Rp 7.500",
                "3. Upload bukti pembayaran kedua QRIS di halaman detail transaksi",
                "4. Pesanan akan diproses setelah kedua pembayaran terkonfirmasi"
            ],
            "grand_total": 57500
        }
    }
}
```

---

### **2. Verify QRIS Payment**

**Endpoint:** `POST /api/payments/verify-qris`

**Request (multipart/form-data):**
```
transaction_id: 123
payment_type: merchant
payment_proof: [file image]
```

**Response:**
```json
{
    "success": true,
    "message": "Merchant payment verified successfully",
    "data": {
        "transaction_id": 123,
        "payment_type": "merchant",
        "payment_status": "PARTIAL_PAID",
        "proof_url": "https://storage.antarkanma.com/payment_proofs/qris/abc123.jpg",
        "paid_at": "2026-03-11T10:30:00Z",
        "is_fully_paid": false
    }
}
```

---

### **3. Get Payment Status**

**Endpoint:** `GET /api/transactions/{id}/payment-status`

**Response:**
```json
{
    "success": true,
    "message": "Payment status retrieved",
    "data": {
        "payment_method": "QRIS_DUAL",
        "payment_status": "PARTIAL_PAID",
        "merchant_amount": 50000,
        "platform_amount": 7500,
        "grand_total": 57500,
        "merchant_paid_at": "2026-03-11T10:30:00Z",
        "platform_paid_at": null,
        "is_fully_paid": false,
        "qris_urls": {
            "merchant": {
                "url": "https://merchant-qris.com/abc123",
                "amount": 50000,
                "merchant_name": "Warung Madura",
                "is_paid": true
            },
            "platform": {
                "url": "https://platform-qris.com/xyz789",
                "amount": 7500,
                "is_paid": false
            }
        }
    }
}
```

---

## 💻 Implementation Files

### **Created/Updated Files:**

```
✅ database/migrations/
   └─ 2026_03_11_000001_add_dual_qris_payment_to_transactions.php

✅ app/Models/
   └─ Transaction.php (updated with new fields & methods)

✅ app/Http/Controllers/API/
   ├─ TransactionController.php (updated create() method)
   ├─ PaymentController.php (NEW - verify payments)
   └─ CourierController.php (updated completeOrder())

✅ routes/
   └─ api.php (added payment routes)

✅ docs/AntarkanMa/
   └─ feature-checklist.md (updated)
```

---

## 📱 Customer App Integration

### **Checkout Flow:**

```dart
// 1. Select payment method
if (paymentMethod == 'QRIS_DUAL') {
    // 2. Show dual QRIS payment UI
    return DualQrisPaymentScreen(
        transaction: transaction,
        merchantQris: transaction.paymentInfo.payments[0].qrisUrl,
        platformQris: transaction.paymentInfo.payments[1].qrisUrl,
    );
}

// 3. Upload payment proof
Future<void> uploadPaymentProof(String paymentType, File proof) async {
    final response = await http.post(
        Uri.parse('${baseUrl}/payments/verify-qris'),
        headers: {
            'Authorization': 'Bearer $token',
        },
        fields: {
            'transaction_id': transaction.id,
            'payment_type': paymentType, // 'merchant' or 'platform'
        },
        files: {
            'payment_proof': proof,
        },
    );
    
    if (response.success) {
        // Update UI
        setState(() {
            if (paymentType == 'merchant') {
                merchantPaid = true;
            } else {
                platformPaid = true;
            }
        });
    }
}
```

---

## 🎯 Business Impact

### **Cost Comparison:**

| Aspect | Payment Gateway | Dual QRIS |
|--------|----------------|-----------|
| **Setup Cost** | Rp 500k-1jt | Rp 0 |
| **MDR Fee** | 2-3% per tx | Rp 0 |
| **Settlement Fee** | Rp 1.500/tx | Rp 0 |
| **Monthly Cost** | Rp 500k+ | Rp 0 |
| **Admin Work** | Low | Medium (manual verify) |
| **Best For** | Scale (>500 orders/day) | MVP (<100 orders/day) |

### **Savings:**

```
Monthly Cost (Payment Gateway):
├─ MDR: 2.5% × Rp 10jt = Rp 250.000
├─ Settlement: 100 × Rp 1.500 = Rp 150.000
├─ Monthly fee: Rp 500.000
└─ Total: Rp 900.000/bulan

Dual QRIS:
└─ Total: Rp 0/bulan ✅

SAVE: Rp 900.000/bulan = Rp 10.8jt/tahun! 💰
```

---

## ⚠️ Important Notes

### **DO:**

1. ✅ Always verify both payments before crediting courier
2. ✅ Store payment proofs for audit trail
3. ✅ Send notifications on each payment step
4. ✅ Track payment status meticulously
5. ✅ Admin verify proofs within 15 minutes (SLA)

### **DON'T:**

1. ❌ Credit courier before BOTH payments verified
2. ❌ Allow order processing with PARTIAL_PAID status
3. ❌ Delete payment proofs (keep for 30 days minimum)
4. ❌ Mix merchant money with platform money
5. ❌ Forget to update courier payout status

---

## 🚀 Migration Path

### **When to Upgrade to Payment Gateway:**

```
TRIGGER POINTS:
✅ >200 orders/day consistently
✅ >Rp 50jt monthly revenue
✅ Have 3+ ops staff
✅ Ready for scale

UPGRADE STEPS:
1. Integrate Midtrans/Xendit
2. Keep QRIS_DUAL as fallback option
3. Auto-settlement to merchant (T+1)
4. Auto-payout to courier
```

---

## 📊 Metrics to Track

```sql
-- Daily QRIS payment stats
SELECT 
    DATE(created_at) as date,
    payment_method,
    payment_status,
    COUNT(*) as total_transactions,
    SUM(merchant_amount) as merchant_revenue,
    SUM(platform_amount) as platform_revenue,
    SUM(courier_earning) as courier_earnings
FROM transactions
WHERE payment_method = 'QRIS_DUAL'
GROUP BY DATE(created_at), payment_method, payment_status
ORDER BY date DESC;

-- Payment verification time
SELECT 
    AVG(TIMESTAMPDIFF(MINUTE, merchant_paid_at, platform_paid_at)) as avg_verification_time_minutes
FROM transactions
WHERE payment_method = 'QRIS_DUAL'
AND merchant_paid_at IS NOT NULL
AND platform_paid_at IS NOT NULL;
```

---

**Last Updated:** 11 Maret 2026
**Status:** ✅ Production Ready
**Next Review:** After 100 transactions
