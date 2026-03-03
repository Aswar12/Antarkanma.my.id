# 🔑 RESET PASSWORD KURIR - SELESAI

**Date:** 4 Maret 2026  
**Status:** ✅ **COMPLETED**

---

## ✅ HASIL

**Password semua kurir berhasil direset!**

- **Total Kurir:** 22 akun (21 seeder + 1 main account)
- **Password Baru:** `courier123`
- **Status:** ✅ Siap digunakan

---

## 🎯 AKUN UTAMA - KURIR ANTARKANMA

**Gunakan akun ini untuk testing:**

| Field | Value |
|-------|-------|
| **Name** | Kurir Antarkanma |
| **Email** | antarkanma@courier.com |
| **Password** | courier123 |
| **Vehicle** | motorcycle |
| **Plate** | ANT-1234-MA |
| **Wallet** | Rp 20.000 |
| **Courier ID** | 21 |
| **User ID** | 249 |

---

## 📋 SEMUA AKUN KURIR (22 Total)

### Main Account (RECOMMENDED):
| Email | Password | Name | Vehicle | Plate | Wallet |
|-------|----------|------|---------|-------|--------|
| antarkanma@courier.com | courier123 | Kurir Antarkanma | motorcycle | ANT-1234-MA | Rp 20.000 |

### Seeder Accounts (21):
| Email | Password | Name |
|-------|----------|------|
| sarmstrong@example.org | courier123 | Minerva Raynor |
| deshaun.white@example.net | courier123 | Mr. Marcellus Wunsch I |
| nleuschke@example.net | courier123 | Freida Walsh |
| monahan.terence@example.com | courier123 | Verna Swaniawski |
| stefanie.koepp@example.com | courier123 | Kirk McDermott |
| mweimann@example.org | courier123 | Ms. Rossie Rodriguez |
| alex.feeney@example.net | courier123 | Kristofer Gusikowski III |
| zetta.swaniawski@example.org | courier123 | Lavon Vandervort DVM |
| obie.graham@example.net | courier123 | Miss Esmeralda Stiedemann III |
| judge24@example.com | courier123 | Quinn Schumm DDS |

**... dan 11 kurir seeder lainnya**

---

## 🎯 CARA LOGIN

### Customer/Merchant App:
```
Email: sarmstrong@example.org
Password: courier123
```

### Courier App:
```
Email: sarmstrong@example.org
Password: courier123
```

**Note:** Pastikan memilih role **COURIER** saat login (jika ada pilihan).

---

## 🔧 CARA RESET MANUAL (Jika Perlu Lagi)

### Option 1: Via SQL Query

```sql
-- Reset semua password kurir
UPDATE users u
JOIN couriers c ON u.id = c.user_id
SET u.password = '$2y$12$jjKGQlZJAWZiCrvOLt3k7euzvJWFbd5P8PsLkPBlaj/4dk5vO20wy'
WHERE u.roles = 'COURIER';
```

**Password:** `courier123`

---

### Option 2: Via Laravel Tinker

```bash
php artisan tinker
```

```php
// Reset password user tertentu
$user = User::where('email', 'sarmstrong@example.org')->first();
$user->password = Hash::make('courier123');
$user->save();

// Reset semua kurir
User::where('roles', 'COURIER')->each(function($user) {
    $user->password = Hash::make('courier123');
    $user->save();
});
```

---

### Option 3: Via Admin Dashboard

1. Login ke admin dashboard
2. Buka **Users** → **Couriers**
3. Edit courier yang diinginkan
4. Update password di form
5. Save

---

## 📝 GENERATE PASSWORD BARU

### Generate Hash Password:

```bash
php artisan tinker
```

```php
// Generate hash untuk password baru
echo bcrypt('password_baru_anda');
```

**Output:**
```
$2y$12$abc123...  ← Copy hash ini
```

### Update Password dengan Hash Baru:

```sql
UPDATE users
SET password = '$2y$12$abc123...'  -- ← Paste hash di sini
WHERE email = 'courier@example.com';
```

---

## 🔒 REKOMENDASI KEAMANAN

### Setelah Reset:

1. ✅ **Login dengan password default**
2. ✅ **Ganti password di settings**
3. ✅ **Gunakan password yang kuat**
   - Minimal 8 karakter
   - Kombinasi huruf, angka, simbol
   - Tidak menggunakan informasi pribadi

### Password Recommendations:

```
✅ Good: Courier@2026!
✅ Good: AntarkanMa#123
✅ Good: KurirHebat99!
❌ Bad: 123456
❌ Bad: password
❌ Bad: courier123 (default, terlalu sederhana)
```

---

## 🧪 TESTING

### Test Login:

```bash
# Test via API
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "sarmstrong@example.org",
    "password": "courier123"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 224,
      "name": "Minerva Raynor",
      "email": "sarmstrong@example.org",
      "roles": "COURIER"
    }
  }
}
```

---

## ⚠️ TROUBLESHOOTING

### Problem: Login gagal setelah reset

**Check:**
```sql
-- Verify password sudah diupdate
SELECT id, email, roles, password 
FROM users 
WHERE email = 'sarmstrong@example.org';
```

**Expected:** Password harus mulai dengan `$2y$12$`

---

### Problem: User tidak ada di tabel couriers

**Check:**
```sql
-- Cek apakah user punya record di couriers
SELECT u.id, u.email, u.roles, c.id as courier_id
FROM users u
LEFT JOIN couriers c ON u.id = c.user_id
WHERE u.email = 'sarmstrong@example.org';
```

**Fix (jika courier_id NULL):**
```sql
-- Buat record courier
INSERT INTO couriers (user_id, vehicle_type, license_plate, created_at, updated_at)
VALUES (224, 'Motorcycle', 'B 1234 CD', NOW(), NOW());
```

---

### Problem: Role bukan COURIER

**Check:**
```sql
SELECT id, email, roles FROM users WHERE email = 'sarmstrong@example.org';
```

**Fix:**
```sql
UPDATE users
SET roles = 'COURIER'
WHERE email = 'sarmstrong@example.org';
```

---

## 📊 SUMMARY

| Item | Status |
|------|--------|
| Password Reset | ✅ Done |
| Total Kurir | 21 akun |
| Default Password | courier123 |
| Ready to Use | ✅ Yes |

---

## 🚀 NEXT STEPS

1. ✅ **Login dengan salah satu akun kurir di atas**
2. ✅ **Test semua fitur kurir**
3. ✅ **Ganti password setelah login pertama kali**
4. ✅ **Update dokumentasi dengan akun yang digunakan**

---

**Created:** 4 Maret 2026  
**By:** AI Assistant  
**Status:** ✅ Production Ready
