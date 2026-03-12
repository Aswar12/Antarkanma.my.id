# 🏆 Top Merchants & ⚙️ Service Fee Configuration

**Implementation Date:** 11 Maret 2026
**Status:** ✅ Complete

---

## 📋 Overview

Implementasi 2 fitur utama untuk AntarkanMa:

1. **Top Merchants Statistics** - API endpoint untuk menampilkan merchant dengan order terbanyak
2. **Configurable Service Fee** - Admin dapat mengubah service fee kapan saja via Admin Panel
   - Default: Rp 500 per transaksi (bukan per order!)
   - Customer hanya bayar sekali per transaksi, tidak peduli berapa merchant

---

## 🎯 1. TOP MERCHANTS STATISTICS

### **API Endpoint**

```http
GET /api/merchants/top
```

### **Query Parameters**

| Parameter | Type | Default | Options | Description |
|-----------|------|---------|---------|-------------|
| `period` | string | `week` | `week`, `month`, `all_time` | Periode statistik |
| `limit` | integer | `10` | `1-100` | Jumlah merchant yang ditampilkan |

### **Request Example**

```bash
# Top merchants minggu ini (default)
curl -X GET "http://localhost:8000/api/merchants/top"

# Top 5 merchants bulan ini
curl -X GET "http://localhost:8000/api/merchants/top?period=month&limit=5"

# All-time top 20 merchants
curl -X GET "http://localhost:8000/api/merchants/top?period=all_time&limit=20"
```

### **Response Example**

```json
{
  "code": 200,
  "message": "Top merchants by orders (week) retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Koneksi Rasa",
      "address": "Jl. Raya Segeri No. 123, Pangkep",
      "phone_number": "+6281234567890",
      "description": "Restoran makanan tradisional...",
      "logo": "https://antarkanma.s3.amazonaws.com/merchants/koneksi-rasa.jpg",
      "latitude": -5.123456,
      "longitude": 119.456789,
      "opening_time": "08:00",
      "closing_time": "22:00",
      "operating_days": "monday,tuesday,wednesday,thursday,friday,saturday,sunday",
      "rank": 1,
      "badge": "🥇 #1 Top Merchant",
      "total_orders": 45,
      "total_revenue": 2250000.00,
      "average_rating": 4.8,
      "total_products": 12
    },
    {
      "id": 2,
      "name": "Cafe Ma'rang",
      "rank": 2,
      "badge": "🥈 #2 Top Merchant",
      "total_orders": 38,
      "total_revenue": 1900000.00,
      "average_rating": 4.6,
      "total_products": 8
    }
  ]
}
```

### **Response Fields**

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Merchant ID |
| `name` | string | Nama merchant |
| `address` | string | Alamat lengkap |
| `phone_number` | string | Nomor telepon |
| `description` | string | Deskripsi merchant |
| `logo` | string | URL logo merchant |
| `latitude` | float | Koordinat latitude |
| `longitude` | float | Koordinat longitude |
| `opening_time` | string | Jam buka (HH:mm) |
| `closing_time` | string | Jam tutup (HH:mm) |
| `operating_days` | string | Hari operasional (comma-separated) |
| `rank` | integer | Peringkat merchant (1, 2, 3, ...) |
| `badge` | string | Badge untuk display (🥇, 🥈, 🥉, Top N) |
| `total_orders` | integer | Total order dalam periode |
| `total_revenue` | float | Total revenue dalam periode (Rp) |
| `average_rating` | float | Rata-rata rating (0.0 - 5.0) |
| `total_products` | integer | Jumlah produk aktif |

### **Business Logic**

1. **Order Counting:**
   - Hanya menghitung order dengan status `PAID`
   - Filter berdasarkan periode (week, month, all_time)
   - Week: Mulai dari Monday minggu ini
   - Month: Mulai dari tanggal 1 bulan ini
   - All-time: Semua order tanpa filter waktu

2. **Ranking:**
   - Diurutkan berdasarkan `total_orders` (descending)
   - Badge otomatis:
     - Rank 1: 🥇 #1 Top Merchant
     - Rank 2: 🥈 #2 Top Merchant
     - Rank 3: 🥉 #3 Top Merchant
     - Rank 4+: Top N Merchant

3. **Caching:**
   - Cache duration: 5 menit (300 seconds)
   - Cache key format: `top_merchants:{period}:{limit}`
   - Auto-clear saat ada order baru

### **Implementation Files**

- **Controller:** `app/Http/Controllers/API/MerchantController.php`
  - Method: `topMerchants(Request $request)`
- **Route:** `routes/api.php`
  - `Route::get('/merchants/top', [MerchantController::class, 'topMerchants']);`

---

## ⚙️ 2. CONFIGURABLE SERVICE FEE

### **Overview**

Admin dapat mengubah service fee kapan saja via Admin Panel dengan audit trail lengkap.

### **Database Schema**

**Table:** `service_fee_settings`

```sql
CREATE TABLE service_fee_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_fee DECIMAL(10,2) DEFAULT 500.00,
    is_active BOOLEAN DEFAULT TRUE,
    updated_by VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (is_active)
);
```

### **Model**

**File:** `app/Models/ServiceFeeSetting.php`

**Helper Methods:**

```php
// Get current active service fee
$fee = ServiceFeeSetting::getCurrentServiceFee();
// Returns: 500.00 (default) or custom value

// Check if service fee is active
$isActive = ServiceFeeSetting::isServiceFeeActive();
// Returns: true | false

// Update service fee with audit trail
ServiceFeeSetting::updateServiceFee(
    amount: 1000.00,
    updatedBy: 'Admin John',
    notes: 'Increased fee for peak season'
);
```

### **Admin Panel**

**URL:** `/admin/service-fee-settings`

**Navigation:**
- Group: Settings
- Icon: Currency Dollar (💲)
- Sort: 1 (first in group)

**Features:**

1. **List Page:**
   - Table dengan kolom: Service Fee, Active, Updated By, Notes, Created At
   - Filter by Active Status (All, Active Only, Inactive Only)
   - Default sort: Created At (newest first)
   - Actions: Edit, Delete

2. **Create/Edit Form:**
   - Service Fee Input (Rp) dengan prefix "Rp"
   - Toggle Active/Inactive
   - Notes textarea (optional)
   - Auto-fill `updated_by` dengan nama admin yang login

3. **Audit Trail:**
   - Setiap create/update auto-fill `updated_by`
   - Setting lama auto-deactivated saat buat baru
   - History lengkap di table

### **Usage in Code**

**Example: Apply service fee to order**

```php
use App\Models\ServiceFeeSetting;

// In TransactionController or checkout logic
$serviceFee = ServiceFeeSetting::getCurrentServiceFee();

$order = Order::create([
    'user_id' => $userId,
    'merchant_id' => $merchantId,
    'subtotal' => $subtotal,
    'service_fee' => $serviceFee,
    'total_amount' => $subtotal + $serviceFee,
]);
```

**Example: Display in API response**

```php
return ResponseFormatter::success([
    'checkout_details' => [
        'subtotal' => 50000,
        'ongkir' => 7000,
        'service_fee' => ServiceFeeSetting::getCurrentServiceFee(),
        'total' => 57500,
    ]
], 'Checkout details');
```

### **Implementation Files**

1. **Migration:** `database/migrations/2026_03_11_140442_create_service_fee_settings_table.php`
2. **Model:** `app/Models/ServiceFeeSetting.php`
3. **Filament Resource:** `app/Filament/Resources/ServiceFeeSettingResource.php`
4. **Pages:**
   - `app/Filament/Resources/ServiceFeeSettingResource/Pages/ListServiceFeeSettings.php`
   - `app/Filament/Resources/ServiceFeeSettingResource/Pages/CreateServiceFeeSetting.php`
   - `app/Filament/Resources/ServiceFeeSettingResource/Pages/EditServiceFeeSetting.php`
5. **Seeder:** `database/seeders/ServiceFeeSettingSeeder.php`

---

## 🧪 Testing

### **1. Test Top Merchants API**

```bash
# Using curl
curl -X GET "http://localhost:8000/api/merchants/top" | json_pp

# Using HTTPie
http GET localhost:8000/api/merchants/top

# With parameters
http GET localhost:8000/api/merchants/top period==month limit==5
```

### **2. Test Service Fee Configuration**

**Via Admin Panel:**

1. Login ke `/admin` dengan akun admin
2. Navigate ke **Settings → Service Fee Settings**
3. Click **New Service Fee Setting**
4. Set fee (misal: Rp 1000)
5. Add notes (optional)
6. Save

**Via Code:**

```bash
php artisan tinker

>>> use App\Models\ServiceFeeSetting;
>>> ServiceFeeSetting::getCurrentServiceFee();
=> 500.0

>>> ServiceFeeSetting::updateServiceFee(1000.00, 'Test Admin', 'Testing fee increase');
>>> ServiceFeeSetting::getCurrentServiceFee();
=> 1000.0
```

---

## 📊 Integration Points

### **Where to Use Service Fee**

1. **Checkout Process** (`TransactionController@store`)
   ```php
   $serviceFee = ServiceFeeSetting::getCurrentServiceFee();
   ```

2. **Order API Response**
   ```php
   return [
       'service_fee' => ServiceFeeSetting::getCurrentServiceFee(),
   ];
   ```

3. **Customer App Checkout Screen**
   - Display breakdown: Subtotal + Ongkir + Service Fee

### **Where to Use Top Merchants**

1. **Customer App Homepage**
   - Section: "Merchant Populer Minggu Ini"
   - Horizontal scroll list dengan badge

2. **Customer App Merchant List**
   - Tab: "Top Merchants"
   - Filter by period

3. **Marketing & Analytics**
   - Featured merchants section
   - Performance tracking

---

## 🔐 Security Considerations

1. **Service Fee:**
   - Only admin dapat akses Admin Panel
   - Audit trail untuk compliance
   - Validation: numeric, min 0

2. **Top Merchants:**
   - Public endpoint (requires auth:sanctum)
   - Cache untuk prevent abuse
   - Only count PAID orders (prevent fraud)

---

## 🚀 Performance

### **Caching Strategy**

| Feature | Cache Driver | Duration | Key Pattern |
|---------|-------------|----------|-------------|
| Top Merchants | File/Redis | 5 min | `top_merchants:{period}:{limit}` |
| Service Fee | Config | Until change | N/A (singleton) |

### **Optimization Tips**

1. **For High Traffic:**
   - Increase cache duration to 15-30 min
   - Use Redis instead of file cache
   - Add database indexing on `orders.created_at`

2. **For Large Datasets:**
   - Add pagination to top merchants
   - Use cursor-based pagination
   - Consider materialized views

---

## 📝 Migration Guide

### **From Hardcoded to Configurable Service Fee**

**Before:**
```php
// Hardcoded
$order->service_fee = 500;
```

**After:**
```php
// Configurable
use App\Models\ServiceFeeSetting;
$order->service_fee = ServiceFeeSetting::getCurrentServiceFee();
```

### **Search & Replace**

```bash
# Find all hardcoded service fees
grep -r "500" --include="*.php" app/ | grep -i service

# Replace in TransactionController
# Old: 'service_fee' => 500
# New: 'service_fee' => ServiceFeeSetting::getCurrentServiceFee()
```

---

## 🐛 Troubleshooting

### **Issue: Top Merchants returns empty**

**Solution:**
- Pastikan ada order dengan status `PAID`
- Check periode (week/month/all_time)
- Clear cache: `php artisan cache:clear`

### **Issue: Service Fee not updating**

**Solution:**
- Check admin permissions
- Verify `is_active = true`
- Clear config cache: `php artisan config:clear`

### **Issue: Cache not working**

**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 📚 Related Documentation

- [Service Fee Model](docs/AntarkanMa/business/service-fee-model.md)
- [API Reference](docs/AntarkanMa/api/api-reference.md)
- [Admin Panel Guide](docs/AntarkanMa/features/admin-panel.md)
- [Feature Checklist](docs/AntarkanMa/feature-checklist.md)

---

## ✅ Checklist

- [x] Migration created
- [x] Model with helper methods
- [x] Filament resource
- [x] API endpoint
- [x] Route registered
- [x] Seeder created
- [x] Documentation complete
- [x] Cache implemented
- [x] Audit trail implemented
- [ ] Integration with checkout (TODO)
- [ ] Customer App UI (TODO)

---

**Last Updated:** 11 Maret 2026
**Author:** AI Agent
**Status:** ✅ Complete
