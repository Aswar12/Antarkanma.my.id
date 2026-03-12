# 🚀 QUICKSTART — AntarkanMa

> **Panduan cepat untuk AI agent memulai sesi kerja**
>
> ⏱️ **Waktu baca:** 5 menit  
> 📍 **Posisi:** Baca SETIAP memulai sesi

---

## 1️⃣ START HERE (Wajib Setiap Sesi)

### A. Context Setup (2 menit)

```bash
# 1. Baca file ini
cat docs/QUICKSTART.md

# 2. Baca context AI
cat docs/AntarkanMa/ai-memory-context.md

# 3. Cek prioritas
cat MASTERPLAN.md
```

### B. Status Check (1 menit)

```markdown
## Session Startup Checklist:

- [ ] Baca ai-memory-context.md
- [ ] Cek MASTERPLAN.md (prioritas minggu ini)
- [ ] Lihat progress-log.md (last session)
- [ ] Siap mulai coding
```

### C. Environment Setup (2 menit)

```bash
# Backend
cd C:\laragon\www\Antarkanma
php artisan serve --host=0.0.0.0 --port=8000
php artisan cache:clear

# ADB (Android)
adb reverse tcp:8000 tcp:8000
adb devices

# Flutter Apps
cd mobile/customer && flutter run
cd mobile/merchant && flutter run
cd mobile/courier && flutter run
```

---

## 2️⃣ CHOOSE TASK

### Current Priorities (Minggu Ini)

| ID | Task | App | Priority |
|----|------|-----|----------|
| **T-03** | Chat bug fixes | All | 🔴 High |
| **C-10** | Image compression | Backend | 🟡 Medium |
| **F-07** | E2E testing | All | 🟡 Medium |

📚 **Lengkap:** [MASTERPLAN.md](../MASTERPLAN.md#-prioritas-minggu-ini)

### Backlog Options

Jika prioritas selesai, pilih dari backlog:

- **PRIORITAS 2:** Error boundary, Offline mode
- **PRIORITAS 3:** Testing, Security, Payment
- **PRIORITAS 4:** Post-launch features

📋 **Detail:** [docs/AntarkanMa/active-backlog.md](AntarkanMa/active-backlog.md)

---

## 3️⃣ WORKFLOW

### Sebelum Coding

```markdown
1. ✅ Pilih 1 task dari prioritas
2. ✅ Baca dokumentasi terkait:
   - API: docs/AntarkanMa/api/api-reference.md
   - Architecture: docs/AntarkanMa/architecture/
   - Features: docs/AntarkanMa/features/
3. ✅ Buat branch baru (jika perlu)
4. ✅ Mulai coding
```

### Saat Coding

```markdown
1. ✅ Ikuti coding standards project
2. ✅ Tulis clean, readable code
3. ✅ Tambahkan comments jika kompleks
4. ✅ Test locally
```

### Setelah Coding

```markdown
1. ✅ Test functionality
2. ✅ Update MASTERPLAN.md:
   - Mark task as ✅ SELESAI
   - Update "Last Updated"
3. ✅ Pindahkan ke docs/ARCHIVE.md
4. ✅ Update ai-memory-context.md
5. ✅ Commit dengan format:
   ✅ TASK-ID: Description
   📝 Update MASTERPLAN.md
```

---

## 4️⃣ DOCUMENTATION MAP

### Quick Reference

| File | Purpose | When to Read |
|------|---------|--------------|
| [MASTERPLAN.md](../MASTERPLAN.md) | Prioritas & status | Setiap sesi |
| [ai-memory-context.md](AntarkanMa/ai-memory-context.md) | AI context | Setiap sesi |
| [TEST_DATA.md](TEST_DATA.md) | Test accounts | Saat testing |
| [ARCHIVE.md](ARCHIVE.md) | History | Referensi |
| [active-backlog.md](AntarkanMa/active-backlog.md) | Detailed backlog | Pilih task |
| [progress-log.md](AntarkanMa/progress-log.md) | Session log | Update setelah coding |

### Deep Reference

| Category | Files | When to Read |
|----------|-------|--------------|
| **API** | `api/` folder | Coding API endpoints |
| **Architecture** | `architecture/` folder | Implement features |
| **Features** | `features/` folder | Understand business logic |
| **Business** | `business/` folder | Use case implementation |
| **Testing** | `e2e-test-guide.md` | Testing session |
| **Deployment** | `deployment/` folder | Pre-launch prep |

---

## 5️⃣ TEST ACCOUNTS

### Quick Access

```
Password semua akun: antarkanma123

Customer:  aswarthedoctor@gmail.com
Merchant:  koneksi@rasa.com
Courier:   kurir@antarkanma.com
Admin:     antarkanma@gmail.com
```

📚 **Lengkap:** [docs/TEST_DATA.md](TEST_DATA.md)

---

## 6️⃣ COMMON COMMANDS

### Backend

```bash
# Server
php artisan serve --host=0.0.0.0 --port=8000

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Database
php artisan migrate:fresh --seed
php artisan tinker

# Testing
php artisan test
php artisan test --filter=ChatTest
```

### Flutter

```bash
# Setup
flutter clean
flutter pub get

# Run
flutter run
flutter run --release

# Build
flutter build apk --release
flutter build ios --release

# Testing
flutter test
flutter analyze
```

### ADB (Android)

```bash
# Port forward
adb reverse tcp:8000 tcp:8000

# Devices
adb devices

# Install
adb install app.apk

# Logs
adb logcat | grep -i flutter
```

---

## 7️⃣ COMMIT MESSAGE FORMAT

### Standard Format

```bash
✅ TASK-ID: Description of completed task
🐛 TASK-ID: Bug fix description
✨ TASK-ID: New feature description
📝 Update MASTERPLAN.md
```

### Examples

```bash
✅ T-02: Chat pagination complete
🐛 C-08: Fix chat init bug in courier app
✨ B-05: Add image upload support
📝 Update MASTERPLAN.md - mark T-02 complete
```

---

## 8️⃣ REPORTING FORMAT

### End of Session Report

```markdown
## Session Report — [Date]

### ✅ Completed
- [Task ID] Description
- [Task ID] Description

### 🐛 Bugs Fixed
- Description

### 📝 Files Modified
- path/to/file.dart
- path/to/file.php

### 📊 Status Update
- MASTERPLAN.md: ✅ Updated
- ARCHIVE.md: ✅ Added completed tasks
- ai-memory-context.md: ✅ Updated

### 🔄 Next Session
- [Task ID] Next priority
- [Task ID] Next priority
```

---

## 9️⃣ TROUBLESHOOTING

### Common Issues

**Issue:** ADB not detecting device  
**Solution:** `adb kill-server && adb start-server`

**Issue:** Flutter build failed  
**Solution:** `flutter clean && flutter pub get`

**Issue:** API 404 error  
**Solution:** Check route: `php artisan route:list --path=chat`

**Issue:** Cache error  
**Solution:** `php artisan cache:clear && php artisan config:clear`

📚 **Lengkap:** [docs/AntarkanMa/troubleshooting-guide.md](AntarkanMa/troubleshooting-guide.md)

---

## 🔟 AI AGENT RULES

### MUST DO ✅

- ✅ Read ai-memory-context.md EVERY session
- ✅ Check MASTERPLAN.md for priorities
- ✅ Update MASTERPLAN.md after coding
- ✅ Move completed tasks to ARCHIVE.md
- ✅ Use test accounts from TEST_DATA.md
- ✅ Follow commit message format
- ✅ Write session report

### MUST NOT ❌

- ❌ Skip reading context
- ❌ Work without checking priorities
- ❌ Forget to update documentation
- ❌ Hardcode credentials
- ❌ Ignore known issues
- ❌ Commit without testing

---

## 📞 QUICK HELP

### Need Help?

1. **Understand feature?** → Read `docs/AntarkanMa/features/`
2. **API endpoint?** → Read `docs/AntarkanMa/api/`
3. **Architecture?** → Read `docs/AntarkanMa/architecture/`
4. **Testing?** → Read `docs/AntarkanMa/e2e-test-guide.md`
5. **Deployment?** → Read `docs/AntarkanMa/deployment/`

### Documentation Hub

📚 **Start Here:** [docs/AntarkanMa/README.md](AntarkanMa/README.md)

---

**💡 Tip:** Bookmark file ini untuk quick access setiap sesi!

**Last Updated:** 8 Maret 2026  
**Maintained By:** AntarkanMa Team
