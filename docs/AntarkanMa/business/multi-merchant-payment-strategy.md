# 🛒 Multi-Merchant Payment Strategy

> **Implementation Date:** 11 Maret 2026
> **Status:** ✅ MVP Ready (Separate Transactions)
> **Future:** Platform Single QRIS

---

## 🚨 **THE PROBLEM**

### **Scenario: Customer Orders from 3 Merchants**

```
Shopping Cart:
├─ Merchant A: Nasi Goreng - Rp 25.000
├─ Merchant B: Es Teh - Rp 5.000
└─ Merchant C: Kerupuk - Rp 3.000

Total Food: Rp 33.000
Delivery: Rp 10.000
Service Fee: Rp 500 (per transaksi, bukan per order!)
─────────────────────────
GRAND TOTAL: Rp 43.500
```

### **❌ Problem with Dual QRIS:**

```
With DUAL_QRIS payment method:
├─ Scan QRIS Merchant A: Rp 25.000
├─ Scan QRIS Merchant B: Rp 5.000
├─ Scan QRIS Merchant C: Rp 3.000
└─ Scan QRIS Platform: Rp 10.500

TOTAL: 4 QRIS SCANS! ❌

User Experience:
❌ Too complicated
❌ Time consuming
❌ High abandonment rate
❌ Customer confusion
```

---

## ✅ **SOLUTIONS**

### **OPSI 1: Platform Single QRIS (Best UX)**

```
┌─────────────────────────────────────────────────────────┐
│  SINGLE QRIS PAYMENT                                    │
└─────────────────────────────────────────────────────────┘

Customer Action:
└─ Scan 1 QRIS Platform → Pay Rp 43.500 ✅

Platform Distribution:
├─ Merchant A: Rp 25.000 (T+1 settlement)
├─ Merchant B: Rp 5.000 (T+1 settlement)
├─ Merchant C: Rp 3.000 (T+1 settlement)
├─ Courier: Rp 9.000 (wallet credit)
└─ Platform: Rp 1.000 (fee)

┌─────────────────────────────────────────────────────────┐
│  PRO:                                                   │
│  ✅ Best UX (1 scan for everything)                     │
│  ✅ Simple & clean                                      │
│  ✅ No confusion                                        │
│                                                         │
│  CONS:                                                  │
│  ⚠️ Platform holds merchant money (float)               │
│  ⚠️ Requires T+1 settlement                             │
│  ⚠️ Needs payment gateway for scale                     │
└─────────────────────────────────────────────────────────┘
```

---

### **OPSI 2: Smart Payment Routing (Hybrid)**

```
┌─────────────────────────────────────────────────────────┐
│  SMART DETECTION                                        │
└─────────────────────────────────────────────────────────┘

IF Single Merchant:
└─ Use DUAL_QRIS (2 scans acceptable)
   ├─ QRIS Merchant: 1x
   └─ QRIS Platform: 1x

IF Multi-Merchant (2+):
└─ Use PLATFORM_QRIS (1 scan only!)
   └─ Platform settlement T+1

┌─────────────────────────────────────────────────────────┐
│  Implementation Logic:                                  │
├─────────────────────────────────────────────────────────┤
│  $merchantCount = $transaction->orders                 │
│                    ->unique('merchant_id')->count();    │
│                                                         │
│  if ($merchantCount === 1) {                            │
│      return 'DUAL_QRIS';  // 2 scans okay               │
│  } else {                                               │
│      return 'PLATFORM_QRIS';  // 1 scan only!           │
│  }                                                      │
└─────────────────────────────────────────────────────────┘
```

---

### **OPSI 3: Separate Transactions (MVP Current)**

```
┌─────────────────────────────────────────────────────────┐
│  SEPARATE TRANSACTIONS PER MERCHANT                     │
└─────────────────────────────────────────────────────────┘

System Detection:
└─ "Items from 3 different merchants detected"

Auto-Split:
├─ Transaction A: Merchant A + Delivery A
├─ Transaction B: Merchant B + Delivery B
└─ Transaction C: Merchant C + Delivery C

Customer Pays Each:
├─ Pay Order A: 2 QRIS scans (Merchant A + Platform)
├─ Pay Order B: 2 QRIS scans (Merchant B + Platform)
└─ Pay Order C: 2 QRIS scans (Merchant C + Platform)

Total: 6 scans (but split across 3 orders)

┌─────────────────────────────────────────────────────────┐
│  PRO:                                                   │
│  ✅ No settlement complexity                            │
│  ✅ Merchants get paid directly                         │
│  ✅ Platform doesn't hold money                         │
│  ✅ Simple implementation                               │
│                                                         │
│  CONS:                                                  │
│  ❌ Customer checks out 3 times                         │
│  ❌ 3x delivery fees (more expensive!)                  │
│  ❌ 3 different couriers (inefficient)                  │
└─────────────────────────────────────────────────────────┘
```

---

## 🎯 **RECOMMENDED ROADMAP**

### **PHASE 1: MVP (0-100 orders/day) - CURRENT**

```
Strategy: Separate Transactions with Warning

Implementation:
✅ Detect multi-merchant orders
✅ Show warning to customer
✅ Recommend separate orders
✅ Let customer choose

User Flow:
1. Customer adds items from multiple merchants
2. System warns: "Items from 3 different merchants"
3. Recommendation: "Create separate orders for easier payment"
4. Customer can:
   ├─ Proceed with single order (2 scans per merchant)
   └─ Split into separate orders (recommended)

┌─────────────────────────────────────────────────────────┐
│  Why This for MVP:                                      │
├─────────────────────────────────────────────────────────┤
│  ✅ Zero settlement complexity                          │
│  ✅ No payment gateway needed                           │
│  ✅ Platform doesn't hold money                         │
│  ✅ Easy to implement                                   │
│                                                         │
│  ⚠️ Trade-off: More scans, but acceptable for MVP       │
└─────────────────────────────────────────────────────────┘
```

---

### **PHASE 2: Growth (100-500 orders/day)**

```
Strategy: Smart Payment Routing

Implementation:
⏳ Auto-detect single vs multi-merchant
⏳ Route to appropriate payment method
⏳ Single merchant → DUAL_QRIS (2 scans)
⏳ Multi-merchant → PLATFORM_QRIS (1 scan)
⏳ Batch settlement T+1

User Flow:
1. Single merchant order → 2 QRIS scans
2. Multi-merchant order → 1 QRIS scan (platform)
3. Platform settles to merchants next day

┌─────────────────────────────────────────────────────────┐
│  Why Upgrade:                                           │
├─────────────────────────────────────────────────────────┤
│  ✅ Better UX for multi-merchant                        │
│  ✅ Still simple for single merchant                    │
│  ✅ Manageable settlement (batch 1x/day)                │
│                                                         │
│  ⚠️ Requires: Payment gateway integration               │
└─────────────────────────────────────────────────────────┘
```

---

### **PHASE 3: Scale (500+ orders/day)**

```
Strategy: Platform Single QRIS (Full Integration)

Implementation:
⏳ Payment gateway (Midtrans/Xendit)
⏳ Dynamic QRIS generation
⏳ Auto-webhook verification
⏳ Instant courier wallet credit
⏳ T+1 merchant settlement

User Flow:
1. Any order type → 1 QRIS scan
2. Pay grand total
3. Platform auto-distributes

┌─────────────────────────────────────────────────────────┐
│  Why Scale:                                             │
├─────────────────────────────────────────────────────────┤
│  ✅ Best possible UX (1 scan always)                    │
│  ✅ Professional & scalable                             │
│  ✅ No fraud risk (payment gateway verified)            │
│                                                         │
│  ⚠️ Cost: MDR 2-3%, settlement fees                     │
└─────────────────────────────────────────────────────────┘
```

---

## 💻 **CURRENT IMPLEMENTATION (MVP)**

### **Backend Detection:**

```php
// TransactionController.php
$merchantCount = $itemsByMerchant->keys()->count();

if ($merchantCount > 1) {
    // Multi-merchant order detected
    $multiMerchantWarning = 'Pesanan Anda terdiri dari ' . $merchantCount . 
                            ' merchant berbeda. Untuk saat ini, silahkan buat ' .
                            'pesanan terpisah untuk setiap merchant agar ' .
                            'pembayaran lebih mudah (2x scan per merchant). ' .
                            'Fitur pembayaran tunggal untuk multi-merchant ' .
                            'akan segera hadir!';
    
    $responseData['multi_merchant_warning'] = $multiMerchantWarning;
    $responseData['recommendation'] = 'Buat pesanan terpisah untuk setiap merchant.';
}
```

---

### **API Response Example:**

```json
{
    "success": true,
    "data": {
        "id": 123,
        "payment_method": "QRIS_DUAL",
        "merchant_amount": 33000,
        "platform_amount": 10500,
        "grand_total": 43500,
        "multi_merchant_warning": "Pesanan Anda terdiri dari 3 merchant berbeda. Untuk saat ini, silahkan buat pesanan terpisah untuk setiap merchant agar pembayaran lebih mudah (2x scan per merchant). Fitur pembayaran tunggal untuk multi-merchant akan segera hadir!",
        "recommendation": "Buat pesanan terpisah untuk setiap merchant untuk pembayaran yang lebih mudah.",
        "payment_info": {
            "payment_type": "DUAL_QRIS",
            "payments": [
                {
                    "type": "MERCHANT_QRIS",
                    "amount": 33000,
                    "qris_url": "https://..."
                },
                {
                    "type": "PLATFORM_QRIS",
                    "amount": 10500,
                    "qris_url": "https://..."
                }
            ]
        }
    }
}
```

---

## 📱 **CUSTOMER APP UI**

### **Multi-Merchant Warning:**

```dart
if (transaction.multiMerchantWarning != null) {
  return AlertDialog(
    title: Text('⚠️ Pesanan Multi-Merchant'),
    content: Text(transaction.multiMerchantWarning),
    actions: [
      TextButton(
        onPressed: () => _splitOrders(),
        child: Text('Buat Pesanan Terpisah (Recommended)'),
      ),
      TextButton(
        onPressed: () => _proceedAnyway(),
        child: Text('Lanjut (2x Scan per Merchant)'),
      ),
    ],
  );
}
```

---

## 📊 **COMPARISON TABLE**

| Aspect | Separate (MVP) | Smart Routing | Single QRIS |
|--------|---------------|---------------|-------------|
| **QRIS Scans** | 2 per merchant | 2 (single) / 1 (multi) | 1 always |
| **UX** | ⚠️ Okay | ✅ Good | ✅✅ Excellent |
| **Complexity** | ✅ Simple | ⚠️ Medium | ⚠️⚠️ High |
| **Settlement** | ✅ None | ⚠️ T+1 batch | ⚠️⚠️ T+1 auto |
| **Platform Risk** | ✅ None | ⚠️ Low | ⚠️⚠️ Medium |
| **Cost** | ✅ Rp 0 | ⚠️ Rp 0-500k | ⚠️⚠️ MDR 2-3% |
| **Best For** | 0-100/day | 100-500/day | 500+/day |

---

## 🎯 **DECISION**

**FOR NOW (MVP):**

✅ Stay with **Separate Transactions** strategy
✅ Show warning for multi-merchant orders
✅ Let customer choose: split or proceed
✅ Collect feedback on UX pain points

**NEXT (100 orders/day):**

⏳ Upgrade to **Smart Payment Routing**
⏳ Integrate payment gateway
⏳ Implement T+1 batch settlement

**FUTURE (500+ orders/day):**

⏳ Full **Platform Single QRIS**
⏳ Auto-distribution via payment gateway
⏳ Best possible UX

---

**Last Updated:** 11 Maret 2026
**Current Phase:** MVP (Phase 1)
**Next Review:** At 100 orders/day
