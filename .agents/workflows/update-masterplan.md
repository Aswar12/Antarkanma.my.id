# 🔄 WORKFLOW: Update MASTERPLAN.md

**Status:** ✅ **MANDATORY**  
**Applies To:** ALL AI Agents (Opus, Qwen, Claude, Gemini, etc.)  
**Priority:** 🔴 **CRITICAL**  
**Enforcement:** REQUIRED after EVERY task completion

---

## 📋 RULE

> **⚠️ SETELAH MENYELESAIKAN PEKERJAAN APAPUN (coding, fixing, adding features, debugging, dll), AI AGENT WAJIB:**
> 
> 1. Update file `MASTERPLAN.md` dengan perubahan yang dibuat
> 2. Commit perubahan tersebut bersama dengan code changes
> 3. TIDAK BOLEH menyelesaikan task tanpa update MASTERPLAN.md

---

## 🎯 TUJUAN

1. **Single Source of Truth** - MASTERPLAN.md adalah satu-satunya referensi status project
2. **Konsistensi** - Semua agent update dokumentasi yang sama
3. **Track Progress** - Memudahkan tracking apa yang sudah/belum dikerjakan
4. **Handover** - Agent berikutnya langsung tahu status terkini

---

## 📝 CARA UPDATE

### Step 1: Identifikasi Perubahan

Setelah menyelesaikan task, tanyakan pada diri sendiri:

```
✅ Apa yang saya kerjakan?
✅ File apa yang saya ubah?
✅ Fitur apa yang saya tambahkan?
✅ Bug apa yang saya fix?
✅ Apakah ini menyelesaikan prioritas yang ada?
```

---

### Step 2: Update Section yang Sesuai

#### A. Jika Menyelesaikan Task Baru

**Tambahkan ke section `## ✅ SELESAI`:**

```markdown
### Backend
- [x] **Nama Fitur/Task** - Deskripsi singkat apa yang dikerjakan
- [x] **OrderItemController enhanced** - Added order status validation, stock checking, auto recalculate total
- [x] **Order model** - Added `recalculateTotal()` and `canBeModified()` methods
```

**Format:**
```markdown
- [x] **Bold Title** - Deskripsi singkat (max 2 baris)
```

---

#### B. Jika Menyelesaikan Prioritas

**Pindahkan dari section prioritas ke `## ✅ SELESAI`:**

**Sebelum:**
```markdown
## 🔴 PRIORITAS 1
| ID | Fitur | Status |
|----|-------|--------|
| B-04 | Pisahkan MerchantReview | Belum |
```

**Setelah:**
```markdown
## ✅ SELESAI
### Backend
- [x] **B-04: Pisahkan MerchantReview** - Created migrations, models, controllers, routes
```

---

#### C. Jika Menambah File Baru

**Update section `## 📂 Arsitektur`:**

```markdown
├── app/
│   ├── Models/             # 21 models → 22 models (added MerchantReview, CourierReview)
│   ├── Http/Controllers/   # 21 controllers → 23 controllers
```

---

### Step 3: Update Metadata

**Update bagian atas file:**

```markdown
**Last Updated:** [Tanggal Hari Ini]
**Project Status:** [XX]% MVP
**Target Soft Launch:** [Bulan Tahun]
```

---

### Step 4: Commit Changes

**Git commit message format:**

```bash
git add MASTERPLAN.md
git commit -m "📝 Update MASTERPLAN.md - [Task Name]"
```

**Example:**
```bash
git add MASTERPLAN.md
git commit -m "📝 Update MASTERPLAN.md - OrderItem enhancements"
```

---

## 🚨 CHECKLIST SEBELUM SELESAI

Setelah menyelesaikan task, **WAJIB** checklist ini:

```markdown
### Pre-Completion Checklist
- [ ] Code changes completed & tested
- [ ] MASTERPLAN.md updated with changes
- [ ] Git commit includes MASTERPLAN.md changes
- [ ] Commit message follows format
- [ ] Ready to push
```

---

## 📌 CONTOH UPDATE

### Contoh 1: Menambahkan Fitur Baru

**Task:** Membuat ChatController

**Update MASTERPLAN.md:**

```markdown
## ✅ SELESAI

### Backend
- [x] **Chat System** - Complete ChatController with 6 methods (initiate, getMessages, sendMessage, markAsRead, getChatList, closeChat)
- [x] **Chat migrations** - Created chats & chat_messages tables with soft deletes
- [x] **Chat models** - Created Chat & ChatMessage models with relationships
- [x] **Chat routes** - Added 8 chat endpoints with rate limiting (60 req/min)
- [x] **Chat tests** - Created ChatTest with 9 test cases (ALL PASSING)
```

---

### Contoh 2: Fix Bug

**Task:** Fix N+1 query di OrderController

**Update MASTERPLAN.md:**

```markdown
## ✅ SELESAI

### Backend
- [x] **Performance fix** - Added eager loading to OrderController::list() to prevent N+1 queries
```

---

### Contoh 3: Refactoring

**Task:** Extract validation to Form Request

**Update MASTERPLAN.md:**

```markdown
## ✅ SELESAI

### Backend
- [x] **Code quality** - Refactored ChatController validation to InitiateChatRequest & SendMessageRequest Form Requests
```

---

## ⚠️ SANKSI

Jika agent TIDAK update MASTERPLAN.md:

1. **Warning** - Agent berikutnya akan menambahkan reminder
2. **Auto-fix** - Agent berikutnya akan update dan commit atas nama agent yang lupa
3. **Escalation** - Ditambahkan ke `.agents/agent-performance.md` (jika ada file ini)

---

## 🎯 BEST PRACTICES

### ✅ DO

- Update segera setelah task selesai
- Gunakan format yang konsisten
- Include file names yang diubah
- Mention priority ID jika ada (B-04, C-08, dll)
- Keep descriptions concise but informative

### ❌ DON'T

- Jangan update sebelum task benar-benar selesai
- Jangan gunakan format yang berbeda
- Jangan lupa commit MASTERPLAN.md
- Jangan update section yang tidak relevan
- Jangan terlalu detail (max 2 baris per item)

---

## 🔄 WORKFLOW INTEGRATION

### Untuk AI Agents

File ini **OTOMATIS TERBACA** sebagai bagian dari system prompt. Setiap agent WAJIB:

1. **Baca file ini** sebelum mulai bekerja
2. **Follow workflow** setelah selesai bekerja
3. **Verify** update sebelum commit

### Untuk Human Developers

1. **Review** update di MASTERPLAN.md
2. **Verify** perubahan sesuai dengan code changes
3. **Enforce** rule ini ke semua AI agents

---

## 📊 TRACKING

### Update History

| Date | Agent | Task | Status |
|------|-------|------|--------|
| 4 Mar 2026 | Qwen | OrderItem enhancements | ✅ Updated |
| 3 Mar 2026 | Claude | Chat system implementation | ✅ Updated |
| 2 Mar 2026 | Opus | Wallet topup feature | ✅ Updated |

---

## 🎓 TRAINING

### New Agent Onboarding

Ketika agent baru mulai bekerja di project ini:

1. **Read** file ini FIRST
2. **Understand** the importance of documentation
3. **Commit** to follow this workflow
4. **Start** working

---

## ❓ FAQ

**Q: Apa jika task sangat kecil (fix typo, dll)?**  
A: Tetap update jika perubahan affect functionality. Untuk typo cosmetic, optional.

**Q: Apa jika mengerjakan multiple tasks sekaligus?**  
A: Update sekali saja setelah semua task selesai, list semua changes.

**Q: Apa jika lupa update?**  
A: Agent berikutnya akan remind dan update untukmu.

**Q: Apakah perlu update untuk setiap commit?**  
A: Tidak. Update setelah task/batch selesai, bukan setiap commit.

---

## 🔗 RELATED FILES

- `MASTERPLAN.md` - Main project plan (WAJIB diupdate)
- `.agents/agent-guidelines.md` - General AI agent guidelines
- `.github/PULL_REQUEST_TEMPLATE.md` - PR template (jika ada)

---

**Version:** 1.0  
**Created:** 4 Maret 2026  
**Maintained By:** All AI Agents  
**Enforcement:** MANDATORY ✅

---

## ✨ ACKNOWLEDGMENT

> **Dengan bekerja di project ini, saya MENYETUJAI untuk:**
> 
> 1. ✅ Update MASTERPLAN.md setelah setiap task
> 2. ✅ Follow format yang ditentukan
> 3. ✅ Commit perubahan dokumentasi
> 4. ✅ Maintain konsistensi dokumentasi

**Last Acknowledged By:** Qwen (4 Maret 2026)
