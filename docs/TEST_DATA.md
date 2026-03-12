# 🧪 Test Data — AntarkanMa

> **Quick reference untuk test accounts dan data testing**
> 
> 🔗 Related: [[../MASTERPLAN|MASTERPLAN]], [[AntarkanMa/ai-memory-context|AI Memory Context]]

---

## 🔑 Test Accounts

**⚠️ PENTING:** Password untuk SEMUA akun adalah `antarkanma123`

### Admin & Staff

| Role | Email | Password | Notes |
|------|-------|----------|-------|
| Admin | antarkanma@gmail.com | antarkanma123 | Administrator utama |

### User Accounts (Customer)

| ID | Name | Email | Password | Notes |
|----|------|-------|----------|-------|
| 1 | aswar | aswarthedoctor@gmail.com | antarkanma123 | Main test account |
| 2 | Mathias Brekke | aylin49@example.net | antarkanma123 | Generated user |
| 3 | Hunter Parisian | georgette78@example.org | antarkanma123 | Generated user |

**Total:** 56 user accounts tersedia (lihat `database/seeders/`)

### Merchant Accounts

| ID | Merchant Name | Email | Password | Products |
|----|---------------|-------|----------|----------|
| 1 | Koneksi Rasa | koneksi@rasa.com | antarkanma123 | 8 produk elektronik |
| 2 | Toko Elektronik Segeri | tokoelektroniksegeri3707@example.com | antarkanma123 | Test merchant |

**Merchant Details:**
- **Koneksi Rasa:** Jl. Teknologi No. 123, Jakarta
- **Logo:** `public/merchants/logos/koneksi-rasa.png`
- **Products:** Smart TV, Kulkas, Mesin Cuci, AC, Rice Cooker, Microwave, Blender, Laptop

### Courier Accounts

| ID | Name | Email | Password | Status |
|----|------|-------|----------|--------|
| 1 | Kurir AntarkanMa | kurir@antarkanma.com | antarkanma123 | Active |

---

## 📦 Test Products (Koneksi Rasa)

| ID | Product Name | Price | Category |
|----|--------------|-------|----------|
| 2 | Smart TV LED 43" | Rp 3.500.000 | Electronics |
| 3 | Kulkas 2 Pintu | Rp 2.800.000 | Appliances |
| 4 | Mesin Cuci Front Loading | Rp 4.200.000 | Appliances |
| 5 | AC Split 1 PK | Rp 3.100.000 | Electronics |
| 6 | Rice Digital 1.8L | Rp 850.000 | Appliances |
| 7 | Microwave Oven | Rp 1.200.000 | Appliances |
| 8 | Blender Multifungsi | Rp 450.000 | Appliances |
| 9 | Laptop Gaming 15" | Rp 12.500.000 | Electronics |

---

## 🚀 Quick Test Scenarios

### 1. Customer Order Flow
```
1. Login: aswarthedoctor@gmail.com / antarkanma123
2. Browse merchant: Koneksi Rasa
3. Add to cart: Smart TV + Kulkas
4. Checkout → Pilih delivery
5. Payment: COD
6. Track order
```

### 2. Merchant Order Management
```
1. Login: koneksi@rasa.com / antarkanma123
2. Buka Merchant App
3. Accept new order
4. Update order status: READY_FOR_PICKUP
5. Print kitchen ticket
```

### 3. Courier Delivery Flow
```
1. Login: kurir@antarkanma.com / antarkanma123
2. Go online
3. Accept delivery request
4. Pickup order at merchant
5. Deliver to customer
6. Complete delivery
```

---

## 🔗 API Testing

### Base URL
- **Development:** `http://localhost:8000/api`
- **Production:** `https://antarkanma.com/api`

### Authentication
```bash
# Login
POST /api/login
{
  "email": "aswarthedoctor@gmail.com",
  "password": "antarkanma123"
}

# Response: { "token": "xxx", "user": {...} }
```

### Common Endpoints

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/user` | GET | ✅ | Get current user |
| `/api/merchants` | GET | ❌ | List all merchants |
| `/api/merchants/{id}` | GET | ❌ | Merchant detail |
| `/api/products?merchant_id={id}` | GET | ❌ | Products by merchant |
| `/api/orders` | POST | ✅ | Create order |
| `/api/orders/{id}` | GET | ✅ | Order detail |
| `/api/chat/initiate` | POST | ✅ | Start chat |
| `/api/notifications` | GET | ✅ | Get notifications |

---

## 📊 Database Reset

Jika perlu reset data testing:

```bash
# Refresh database + seed
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 🎨 Test Images

### Product Images
Location: `storage/app/public/products/`

### Merchant Logos
Location: `storage/app/public/merchants/logos/`
- `koneksi-rasa.png` — Test merchant logo

### Profile Photos
Location: `storage/app/public/profile-photos/`

---

## 📱 Mobile App Testing

### ADB Setup (Android)
```bash
# Forward port
adb reverse tcp:8000 tcp:8000

# Check connection
adb devices

# Install APK
flutter build apk --release
flutter install
```

### iOS Testing
```bash
# Run on simulator
flutter run

# Build for release
flutter build ios --release
```

---

## 🔗 Related Documents

- **[MASTERPLAN](../MASTERPLAN.md)** — Current priorities
- **[AI Memory Context](AntarkanMa/ai-memory-context.md)** — AI session context
- **[E2E Test Guide](AntarkanMa/e2e-test-guide.md)** — Complete test scenarios
- **[API Reference](AntarkanMa/api/api-reference.md)** — API documentation

---

**Last Updated:** 8 Maret 2026  
**Maintained By:** AntarkanMa Team
