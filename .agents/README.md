# 🤖 AI Agents Workspace

**Purpose:** Panduan dan workflow untuk AI agents yang bekerja di project AntarkanMa

**Supported Agents:** Opus, Qwen, Claude, Gemini, dan AI assistants lainnya

---

## 📁 STRUKTUR

```
.agents/
├── README.md                      # ← File ini
├── agent-guidelines.md            # General guidelines (TODO)
├── agent-performance.md           # Performance tracking (TODO)
└── workflows/
    ├── update-masterplan.md       # ✅ MANDATORY: Update MASTERPLAN.md
    ├── code-review.md             # Code review workflow (TODO)
    └── testing.md                 # Testing workflow (TODO)
```

---

## 🔴 MANDATORY WORKFLOWS

### 1. Update MASTERPLAN.md

**File:** `workflows/update-masterplan.md`

**Status:** ✅ **REQUIRED**

**Rule:** SETELAH MENYELESAIKAN PEKERJAAN APAPUN, AI AGENT WAJIB:
1. Update `MASTERPLAN.md` dengan perubahan yang dibuat
2. Commit perubahan tersebut bersama code changes
3. TIDAK BOLEH menyelesaikan task tanpa update dokumentasi

**Why:** Maintain single source of truth untuk project status

---

## 📋 QUICK START UNTUK AGENT BARU

### Step 1: Baca File Ini

Pahami struktur project dan expectations.

### Step 2: Baca Workflows

Minimal baca `workflows/update-masterplan.md`.

### Step 3: Mulai Bekerja

Follow workflows yang ada.

### Step 4: Update Dokumentasi

Setelah selesai, update MASTERPLAN.md.

---

## 🎯 EXPECTATIONS

### AI Agents WAJIB:

- ✅ Follow coding standards
- ✅ Write tests untuk new features
- ✅ Update dokumentasi
- ✅ Comment complex logic
- ✅ Use meaningful names

### AI Agents TIDAK BOLEH:

- ❌ Leave debug code
- ❌ Break existing tests
- ❌ Skip documentation
- ❌ Commit without testing
- ❌ Ignore security

---

## 📊 AGENT RESPONSIBILITIES

| Responsibility | Priority | Frequency |
|----------------|----------|-----------|
| Code Quality | 🔴 HIGH | Every task |
| Testing | 🔴 HIGH | Every feature |
| Documentation | 🔴 HIGH | After every task |
| Security Review | 🟡 MEDIUM | Every PR |
| Performance | 🟡 MEDIUM | Every feature |

---

## 🔄 WORKFLOW OVERVIEW

```
┌─────────────────┐
│  Start Task     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Read           │
│  Guidelines     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Work on Task   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Test Changes   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Update          │
│ MASTERPLAN.md   │ ← MANDATORY!
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Commit & Push  │
└─────────────────┘
```

---

## 📝 BEST PRACTICES

### Communication

- Leave clear commit messages
- Comment complex code
- Update documentation
- Ask for clarification if unsure

### Code Quality

- Follow PSR-12 (PHP)
- Follow Dart Style Guide (Flutter)
- Write self-documenting code
- Add tests for new features

### Documentation

- Update MASTERPLAN.md after EVERY task
- Keep comments up-to-date
- Document API changes
- Maintain changelog

---

## 🚨 ENFORCEMENT

### Violations

Jika agent melanggar workflow:

1. **First Time:** Warning + auto-fix
2. **Second Time:** Warning + performance log
3. **Third Time:** Escalation to human developer

### Quality Checks

- Code review by other agents
- Automated testing
- Documentation audit
- Performance monitoring

---

## 📈 PERFORMANCE TRACKING

### Metrics

| Metric | Target | Current |
|--------|--------|---------|
| Test Coverage | 80% | 17% |
| Documentation | 100% | 95% |
| Code Quality | A | A |
| Security | 0 critical | 0 critical |

---

## 🎓 TRAINING RESOURCES

### For New Agents

1. Read this README
2. Read `workflows/update-masterplan.md`
3. Review recent commits
4. Understand project architecture
5. Start with small tasks

### For Experienced Agents

1. Mentor new agents
2. Improve workflows
3. Optimize processes
4. Maintain quality standards

---

## 🔗 RELATED FILES

- `MASTERPLAN.md` - Main project plan
- `CONTRIBUTING.md` - Contribution guidelines
- `README.md` - Project overview
- `docs/` - Full documentation

---

## ✨ ACKNOWLEDGMENT

> **By working in this project, I acknowledge that I have:**
> 
> 1. ✅ Read and understood this guide
> 2. ✅ Committed to follow workflows
> 3. ✅ Agreed to maintain documentation
> 4. ✅ Accepted quality standards

**Last Updated:** 4 Maret 2026  
**Maintained By:** All AI Agents  
**Version:** 1.0

---

## 📞 SUPPORT

For questions or clarifications:
- Check existing documentation
- Ask in commit messages
- Leave comments for next agent
- Escalate to human developer if needed

---

**Welcome to AntarkanMa AI Agents Workspace! 🚀**
