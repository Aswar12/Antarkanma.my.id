# 📚 AI DOCUMENTATION GUIDE - ANTARKANMA

Panduan ini menjelaskan cara menggunakan dokumentasi project untuk AI assistant.

---

## 🗂️ FILE STRUCTURE (UPDATED)

**Root Documentation:** `docs/AntarkanMa/`

```
docs/AntarkanMa/
├── ai-memory-context.md          # 🧠 CONTEXT (Baca SETIAP sesi!)
├── progress-log.md               # 📝 Chronological progress
├── active-backlog.md             # 📋 Current tasks
├── e2e-test-guide.md             # 🧪 Testing guide
├── technical-specifications.md   # 🔧 Technical specs
├── project-planning.md           # 📅 Project plan
├── work-plan.md                  # 📋 Work plan
├── Welcome.md                    # 📖 Welcome page
├── api/                          # API documentation
│   ├── api-reference.md
│   ├── courier-api.md
│   ├── merchant-api.md
│   └── transaction-flow.md
├── architecture/                 # Architecture docs
│   ├── dfd-level-0.md
│   ├── dfd-level-1.md
│   ├── class-diagram.md
│   ├── sequence-diagram.md
│   ├── erd-diagram.md
│   └── database-schema.md
├── business/                     # Business logic
│   ├── use-cases.md
│   └── user-stories.md
├── company/                      # Company info
│   ├── business-model.md
│   ├── company-profile.md
│   └── growth-roadmap.md
├── features/                     # Feature specs
├── deployment/                   # Deployment guide
├── design/                       # Design docs
└── images/                       # Assets
```

**Workflow File:** `.agent/workflows/mulai-kerja.md` (di root project)

---

## 🎯 READING ORDER (SETIAP SESI)

### 1️⃣ MUST READ (Wajib):
```
1. docs/AntarkanMa/ai-memory-context.md    ← Context utama
2. docs/AntarkanMa/progress-log.md         ← Progress terbaru
3. docs/AntarkanMa/active-backlog.md       ← Task prioritas
```

### 2️⃣ REFERENCE (Jika diperlukan):
```
4. docs/AntarkanMa/e2e-test-guide.md       ← Saat testing
5. docs/AntarkanMa/api/api-reference.md    ← Saat coding API
6. docs/AntarkanMa/architecture/dfd-level-1.md ← Saat implement flow
```

### 3️⃣ DEEP DIVE (Jika stuck):
```
7. docs/AntarkanMa/architecture/sequence-diagram.md
8. docs/AntarkanMa/technical-specifications.md
9. docs/AntarkanMa/business/use-cases.md
```

---

## 📖 FILE DESCRIPTIONS

### 🧠 Context Files

#### `ai-memory-context.md`
**Purpose:** Main context untuk AI  
**Read:** SETIAP sesi (WAJIB!)  
**Contains:**
- Project overview
- Current session context
- Test credentials
- App status matrix
- DFD implementation status
- UI/UX standards
- Known issues
- Next priorities

#### `progress-log.md`
**Purpose:** Chronological log  
**Read:** Setiap sesi  
**Contains:**
- Session-by-session progress
- Bugs found & fixed
- Files modified
- Decisions made

#### `active-backlog.md`
**Purpose:** Task tracking  
**Read:** Setiap sesi  
**Contains:**
- Current sprint tasks
- Backlog items
- Status tracking (⬜ 🔄 ✅)

---

### 🧪 Testing Files

#### `e2e-test-guide.md`
**Purpose:** Testing guide  
**Read:** Saat testing  
**Contains:**
- Test scenarios
- Test credentials
- Step-by-step guides
- Expected results

---

### 🔧 Technical Files

#### `technical-specifications.md`
**Purpose:** Technical specs  
**Read:** Saat coding  
**Contains:**
- API structure
- Database schema
- Third-party integrations
- Security measures

#### `api/api-reference.md`
**Purpose:** API documentation  
**Read:** Saat coding API  
**Contains:**
- All endpoints
- Request/response formats
- Authentication

#### `architecture/dfd-level-1.md`
**Purpose:** Business flow  
**Read:** Saat implement feature  
**Contains:**
- Process diagrams
- Data flow
- Actor interactions

---

### 📋 Workflow Files

#### `.agent/workflows/mulai-kerja.md`
**Purpose:** Session startup  
**Read:** Setiap sesi  
**Contains:**
- Step-by-step startup guide
- ADB setup
- Server setup
- Reporting format

---

## 🎯 WHEN TO READ WHAT

### Starting Session:
```
1. ai-memory-context.md  (5 min)
2. progress-log.md       (2 min)
3. active-backlog.md     (2 min)
→ Total: 9 minutes
```

### Coding Feature:
```
1. ai-memory-context.md  (Review context)
2. architecture/dfd-level-1.md  (Understand flow)
3. api/api-reference.md  (Check endpoints)
→ Total: As needed
```

### Testing:
```
1. e2e-test-guide.md     (Follow steps)
2. ai-memory-context.md  (Check credentials)
→ Total: As needed
```

### Stuck/Debugging:
```
1. ai-memory-context.md  (Check known issues)
2. progress-log.md       (Check if happened before)
3. architecture/sequence-diagram.md  (Understand flow)
→ Total: 15 minutes
```

---

## ✨ BEST PRACTICES

### DO:
- ✅ Read `ai-memory-context.md` EVERY session
- ✅ Update `progress-log.md` AFTER each session
- ✅ Update `ai-memory-context.md` with NEW info
- ✅ Check `active-backlog.md` for priorities
- ✅ Use test credentials from memory context

### DON'T:
- ❌ Skip reading memory context
- ❌ Work without checking backlog
- ❌ Forget to update progress log
- ❌ Hardcode credentials (use from context)
- ❌ Ignore known issues section

---

## 🔄 UPDATE CYCLE

### Start of Session:
1. Read `ai-memory-context.md`
2. Read `progress-log.md`
3. Read `active-backlog.md`
4. Report status to user

### During Session:
1. Reference docs as needed
2. Take notes of changes
3. Log bugs found

### End of Session:
1. Update `ai-memory-context.md` with:
   - Files modified
   - New credentials
   - New known issues
   - Next priorities
2. Update `progress-log.md` with:
   - Session number
   - What was done
   - Bugs fixed
3. Update `active-backlog.md` with:
   - Completed tasks (✅)
   - New tasks (⬜)

---

## 📝 QUICK REFERENCE

### Project Status:
```
Backend: ✅ 100%
Merchant App: ✅ 100%
Courier App: ✅ 100%
Customer App: ⏳ Ready for testing
```

### Test Credentials:
```
Merchant: koneksirasa@gmail.com / koneksirasa123
Courier: antarkanma@courier.com / kurir12345
```

### Key Commands:
```bash
# ADB
adb reverse tcp:8000 tcp:8000

# Backend
php artisan serve --host=0.0.0.0 --port=8000
php artisan cache:clear

# Flutter
flutter clean && flutter pub get
flutter run --release
```

---

**Version:** 1.0  
**Last Updated:** 24 Februari 2026  
**Maintained By:** AI Assistant
