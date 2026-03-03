# 🧪 End-to-End Test Scenarios

> **Created:** 27 Februari 2026  
> **Status:** Ready for Testing  
> **Priority:** 🔴 Critical

---

## 📋 Daftar Isi

1. [Test Environment Setup](#test-environment-setup)
2. [E2E Scenario 1: Complete Order Flow](#e2e-scenario-1-complete-order-flow)
3. [E2E Scenario 2: Multi-Merchant Order](#e2e-scenario-2-multi-merchant-order)
4. [E2E Scenario 3: Order Rejection](#e2e-scenario-3-order-rejection)
5. [E2E Scenario 4: Courier Multi-Order](#e2e-scenario-4-courier-multi-order)
6. [Test Results Template](#test-results-template)

---

## 🛠️ Test Environment Setup

### Prerequisites

```bash
# 1. Ensure backend is running
cd /path/to/backend
php artisan serve

# 2. Ensure database is migrated
php artisan migrate:fresh --seed

# 3. Ensure FCM is configured (optional for notification tests)
# Check: .env -> FIREBASE_CREDENTIALS

# 4. Ensure OSRM is accessible (for delivery cost calculation)
# Check: .env -> OSRM_API_URL
```

### Test Accounts

```yaml
# Customer Account
email: customer@test.com
password: customer123
phone: 081234567890

# Merchant Account (Koneksi Rasa)
email: koneksirasa@gmail.com
password: koneksirasa123
phone: 081234567891

# Courier Account
email: antarkanma@courier.com
password: kurir12345
phone: 081234567892

# Admin Account
email: admin@antarkanma.com
password: admin123
```

---

## E2E Scenario 1: Complete Order Flow (Happy Path)

### Objective
Test complete order flow from customer order to delivery completion.

### Pre-conditions
- ✅ Customer account exists and is active
- ✅ Merchant account exists and is active
- ✅ Courier account exists and is active
- ✅ Products available in merchant store
- ✅ Customer has saved delivery address

### Test Steps

#### Step 1: Customer Login
```bash
POST /api/login
{
  "email": "customer@test.com",
  "password": "customer123"
}

Expected: 200 OK
Response: { success: true, data: { token, user } }
```

**✅ Pass Criteria:**
- Login successful
- Token received
- User data complete

---

#### Step 2: Browse Products
```bash
GET /api/products?merchant_id={merchant_id}
Authorization: Bearer {token}

Expected: 200 OK
Response: { success: true, data: [products] }
```

**✅ Pass Criteria:**
- Products list returned
- Product details complete (name, price, description)
- Merchant info included

---

#### Step 3: Create Order
```bash
POST /api/orders
Authorization: Bearer {token}
{
  "merchant_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "notes": "Pedas sedang"
    }
  ],
  "user_location_id": 1,
  "delivery_address": "Jl. Test No. 123",
  "notes": "Antar sebelum jam 12"
}

Expected: 201 Created
Response: { success: true, data: { order_id, transaction_id, total_amount } }
```

**✅ Pass Criteria:**
- Order created with status PENDING
- Transaction created
- Total amount calculated correctly (product price + delivery fee)
- FCM notification sent to merchant

---

#### Step 4: Merchant Login & View Orders
```bash
POST /api/login
{
  "email": "koneksirasa@gmail.com",
  "password": "koneksirasa123"
}

GET /api/merchant/orders
Authorization: Bearer {merchant_token}

Expected: 200 OK
Response: { success: true, data: [orders] }
```

**✅ Pass Criteria:**
- Merchant can see new order
- Order details complete
- Order status is WAITING_APPROVAL

---

#### Step 5: Merchant Approve Order
```bash
PUT /api/merchants/orders/{order_id}/approve
Authorization: Bearer {merchant_token}

Expected: 200 OK
Response: { success: true, message: "Order approved successfully" }
```

**✅ Pass Criteria:**
- Order status changed to PROCESSING
- FCM notification sent to customer
- Order appears in "Processing Orders" list

---

#### Step 6: Merchant Prepare Order
```bash
PUT /api/merchants/orders/{order_id}/ready
Authorization: Bearer {merchant_token}

Expected: 200 OK
Response: { success: true, message: "Order is ready for pickup" }
```

**✅ Pass Criteria:**
- Order status changed to READY_FOR_PICKUP
- FCM notification sent to couriers nearby

---

#### Step 7: Courier Login & View Available Orders
```bash
POST /api/login
{
  "email": "antarkanma@courier.com",
  "password": "kurir12345"
}

GET /api/courier/transactions/available
Authorization: Bearer {courier_token}

Expected: 200 OK
Response: { success: true, data: [transactions] }
```

**✅ Pass Criteria:**
- Available orders list includes the new order
- Merchant location visible
- Delivery fee visible

---

#### Step 8: Courier Accept Order
```bash
POST /api/courier/transactions/{transaction_id}/approve
Authorization: Bearer {courier_token}

Expected: 200 OK
Response: { success: true, message: "Order accepted successfully" }
```

**✅ Pass Criteria:**
- Transaction courier_id set to courier
- Courier status changed to HEADING_TO_MERCHANT
- FCM notification sent to merchant

---

#### Step 9: Courier Arrive at Merchant
```bash
POST /api/courier/transactions/{transaction_id}/arrive-merchant
Authorization: Bearer {courier_token}

Expected: 200 OK
Response: { success: true, message: "Arrived at merchant successfully" }
```

**✅ Pass Criteria:**
- Courier status changed to AT_MERCHANT
- Merchant notified

---

#### Step 10: Courier Pickup Order
```bash
POST /api/courier/orders/{order_id}/pickup
Authorization: Bearer {courier_token}

Expected: 200 OK
Response: { success: true, message: "Order picked up successfully" }
```

**✅ Pass Criteria:**
- Order status changed to PICKED_UP
- Courier status changed to DELIVERING
- FCM notification sent to customer

---

#### Step 11: Courier Arrive at Customer
```bash
POST /api/courier/transactions/{transaction_id}/arrive-customer
Authorization: Bearer {courier_token}

Expected: 200 OK
Response: { success: true, message: "Arrived at customer successfully" }
```

**✅ Pass Criteria:**
- Courier status changed to AT_CUSTOMER
- Customer notified

---

#### Step 12: Courier Complete Delivery
```bash
POST /api/courier/orders/{order_id}/complete
Authorization: Bearer {courier_token}

Expected: 200 OK
Response: { success: true, message: "Delivery completed successfully" }
```

**✅ Pass Criteria:**
- Order status changed to COMPLETED
- Transaction status changed to COMPLETED
- Courier earnings updated
- Customer receives notification
- Loyalty points added to customer

---

#### Step 13: Customer View Order History
```bash
GET /api/orders
Authorization: Bearer {customer_token}

Expected: 200 OK
Response: { success: true, data: [orders] }
```

**✅ Pass Criteria:**
- Completed order appears in history
- Order status shows COMPLETED
- Rating option available

---

### Post-conditions
- ✅ Order completed successfully
- ✅ All parties received notifications
- ✅ Database records updated correctly
- ✅ Courier earnings updated
- ✅ Customer loyalty points added

---

## E2E Scenario 2: Multi-Merchant Order

### Objective
Test order with products from multiple merchants (batch delivery).

### Pre-conditions
- ✅ At least 2 active merchants
- ✅ Customer with saved address
- ✅ Courier available

### Test Steps

1. **Customer creates order with items from 2 merchants**
   ```bash
   POST /api/orders
   {
     "items": [
       {"product_id": 1, "merchant_id": 1, "quantity": 2},
       {"product_id": 5, "merchant_id": 2, "quantity": 1}
     ]
   }
   ```

2. **System creates 2 transactions (one per merchant)**

3. **Each merchant approves independently**

4. **Courier accepts batch order**

5. **Courier picks up from merchant 1**

6. **Courier picks up from merchant 2**

7. **Courier delivers to customer**

**✅ Pass Criteria:**
- Multiple transactions created correctly
- Each merchant only sees their items
- Courier can pickup from multiple merchants
- Single delivery to customer
- Total delivery fee calculated correctly

---

## E2E Scenario 3: Order Rejection

### Objective
Test order rejection flow by merchant.

### Test Steps

1. **Customer creates order** (as in Scenario 1)

2. **Merchant rejects order**
   ```bash
   PUT /api/merchants/orders/{order_id}/reject
   {
     "rejection_reason": "Stok habis"
   }
   ```

3. **Verify rejection**
   - Order status changed to CANCELED
   - Customer receives notification with reason
   - Transaction canceled

**✅ Pass Criteria:**
- Order status is CANCELED
- Rejection reason saved
- Customer notified
- No courier assignment needed

---

## E2E Scenario 4: Courier Multi-Order

### Objective
Test courier handling multiple orders simultaneously.

### Test Steps

1. **Create 3 orders from different merchants** (same customer area)

2. **All orders ready for pickup**

3. **Courier accepts all 3 orders (batch)**

4. **Courier picks up from merchant 1**
   - Status: Order 1 = PICKED_UP, Order 2 = ACCEPTED, Order 3 = ACCEPTED

5. **Courier picks up from merchant 2**
   - Status: Order 1 = PICKED_UP, Order 2 = PICKED_UP, Order 3 = ACCEPTED

6. **Courier picks up from merchant 3**
   - Status: All orders = PICKED_UP

7. **Courier delivers all orders to customer**
   - Status: All orders = COMPLETED

**✅ Pass Criteria:**
- Courier can handle multiple orders
- Each order status tracked independently
- Batch pickup works correctly
- Customer receives all items

---

## 📊 Test Results Template

### Test Execution Log

```markdown
## Test Session: [Date]

**Tester:** [Name]
**Environment:** [Local/Staging/Production]
**Duration:** [X hours]

### Scenario 1: Complete Order Flow
- [ ] Step 1: Customer Login ✅ / ❌
- [ ] Step 2: Browse Products ✅ / ❌
- [ ] Step 3: Create Order ✅ / ❌
- [ ] Step 4: Merchant View Orders ✅ / ❌
- [ ] Step 5: Merchant Approve ✅ / ❌
- [ ] Step 6: Merchant Ready ✅ / ❌
- [ ] Step 7: Courier View Available ✅ / ❌
- [ ] Step 8: Courier Accept ✅ / ❌
- [ ] Step 9: Courier Arrive Merchant ✅ / ❌
- [ ] Step 10: Courier Pickup ✅ / ❌
- [ ] Step 11: Courier Arrive Customer ✅ / ❌
- [ ] Step 12: Courier Complete ✅ / ❌
- [ ] Step 13: Customer View History ✅ / ❌

**Issues Found:**
1. [Description]
2. [Description]

### Scenario 2: Multi-Merchant Order
- [ ] Pass / Fail

### Scenario 3: Order Rejection
- [ ] Pass / Fail

### Scenario 4: Courier Multi-Order
- [ ] Pass / Fail

## Summary
- **Total Scenarios:** 4
- **Passed:** X
- **Failed:** Y
- **Blocked:** Z

## Critical Issues
[List any blocking issues]

## Next Steps
[What needs to be fixed before re-test]
```

---

## 🐛 Bug Report Template

```markdown
### Bug Title
[Brief description]

### Severity
🔴 Critical / 🟡 High / 🟢 Medium

### Steps to Reproduce
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Expected Result
[What should happen]

### Actual Result
[What actually happened]

### Environment
- Backend Version: [X.X.X]
- Mobile App: [Customer/Merchant/Courier] [Version]
- Device: [Device model + OS version]

### Screenshots/Logs
[Attach if available]

### Proposed Fix
[If known]
```

---

*Last Updated: 27 Februari 2026*
