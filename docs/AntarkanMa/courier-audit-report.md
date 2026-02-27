# 📋 Audit Report — Courier App

**Tanggal:** 21 Februari 2026  
**Status:** 🔄 In Progress

---

## 🎯 Ringkasan

Courier app memiliki struktur yang lebih sederhana dibanding merchant app, tetapi terdapat beberapa bugs kritis yang perlu diperbaiki sebelum bisa digunakan.

---

## ✅ Yang Sudah Baik

| Aspek           | Status | Catatan                                                         |
| --------------- | ------ | --------------------------------------------------------------- |
| Splash Screen   | ✅     | Sudah menggunakan `withValues()`, tidak ada error deprecated    |
| Firebase Setup  | ✅     | Firebase messaging dan local notifications sudah terkonfigurasi |
| Role Validation | ✅     | Sudah ada check `isCourier` di multiple tempat                  |
| Error Handling  | 🟡     | Cukup baik tapi bisa ditingkatkan                               |

---

## 🔴 Bugs Kritis (Wajib Diperbaiki)

### 1. **Config Base URL Salah** 🔴

**File:** `lib/config.dart`  
**Masalah:** Base URL masih menggunakan `10.0.2.2` (khusus emulator Android), sehingga tidak bisa diakses dari device fisik via ADB reverse.

```dart
// ❌ SALAH
static const String baseUrl = 'http://10.0.2.2:8000/api';

// ✅ BENAR
static const String baseUrl = 'http://localhost:8000/api';
```

**Impact:** App tidak bisa terhubung ke backend saat dijalankan di device fisik.

---

### 2. **Inconsistent Auto-Login Flow** 🔴

**File:** `lib/app/services/auth_service.dart` dan `lib/app/modules/auth/controllers/auth_controller.dart`

**Masalah:**

- `AuthController.tryAutoLogin()` memanggil `_authService.getCredentials()`
- Tapi `AuthService` juga punya method `tryAutoLogin()` yang berbeda implementasi
- `AuthService.login()` tidak menyimpan `rememberMe` status
- `AuthService.saveCredentials()` menggunakan key berbeda dengan `getSavedCredentials()`

**Impact:** Auto-login tidak akan berfungsi karena:

- Credentials tidak tersimpan dengan benar
- Remember me status tidak diset
- Flow antara controller dan service tidak konsisten

---

### 3. **Double Navigation Risk** 🟡

**File:** `lib/app/modules/splash/controllers/splash_controller.dart`

**Masalah:** Sama seperti merchant app sebelumnya, ada risk double navigation:

1. `AuthService` di-init dan bisa langsung navigate di `_initializeService()`
2. `SplashController._initSplash()` juga navigate setelah `tryAutoLogin()`

**Code bermasalah:**

```dart
// Di AuthService._initializeService():
if (token != null && userData != null) {
  // ... navigate ke main ...
  Get.offAllNamed(Routes.main); // ❌ Navigation di service
}

// Di SplashController._initSplash():
if (autoLoginSuccess) {
  Get.offAllNamed(Routes.main); // ❌ Navigation lagi di controller
}
```

---

### 4. **Missing Remember Me Setup** 🟡

**File:** `lib/app/services/auth_service.dart`

**Masalah:** Saat login sukses, tidak ada pemanggilan `saveRememberMe(true)`.

```dart
// Di method login(), setelah save credentials:
await _storageService!.saveCredentials(identifier, password);
// ❌ Tidak ada: await _storageService!.saveRememberMe(true);
```

**Impact:** `canAutoLogin()` akan return false karena rememberMe tidak tersimpan.

---

## 🛠️ Daftar Perbaikan

### Priority 1 (Kritis)

1. ✅ Fix base URL di `config.dart`
2. ✅ Fix auto-login flow consistency
3. ✅ Fix double navigation di splash controller
4. ✅ Add missing `saveRememberMe(true)` di login

### Priority 2 (Medium)

5. 🟡 Add extensive logging untuk debugging (sama seperti merchant app)
6. 🟡 Fix potential race condition di auth initialization
7. 🟡 Standardisasi error handling

### Priority 3 (Low)

8. 🟡 Code cleanup dan refactoring untuk consistency dengan merchant app

---

## 📊 Statistik Courier App

| Komponen    | Jumlah | Status             |
| ----------- | ------ | ------------------ |
| Controllers | 3      | 🟡 Perlu perbaikan |
| Services    | 3      | 🟡 Perlu perbaikan |
| Providers   | 4      | ✅ OK              |
| Models      | 15+    | ✅ OK              |
| Views       | 5      | ✅ OK              |
| Widgets     | 5+     | ✅ OK              |

---

## 🎯 Rekomendasi

1. **Segera perbaiki Priority 1** agar courier app bisa di-test di device
2. **Gunakan pola yang sama** dengan merchant app untuk consistency
3. **Tambahkan logging** yang ekstensif untuk memudahkan debugging
4. **Test auth flow** setelah perbaikan: login → close app → reopen → auto-login

---

## 📝 Next Steps

- [ ] Fix config.dart base URL
- [ ] Fix auth_service.dart auto-login flow
- [ ] Fix splash_controller.dart double navigation
- [ ] Add logging ke semua auth methods
- [ ] Build & test di device
- [ ] Verifikasi auto-login berfungsi
