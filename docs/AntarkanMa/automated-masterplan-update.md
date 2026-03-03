# 🔄 AUTOMATED MASTERPLAN.MD UPDATES

**Last Updated:** 4 Maret 2026  
**Status:** ✅ **ACTIVE**

---

## 🎯 GOAL

Memastikan **MASTERPLAN.md selalu terupdate** setiap kali ada code changes, tanpa perlu diingat manual.

---

## ✅ SOLUTIONS IMPLEMENTED

### **1. Git Pre-Commit Hook** ⭐ (ALWAYS ACTIVE)

**Location:** `.git/hooks/pre-commit`

**How it works:**
- Every time you run `git commit`, this hook checks if MASTERPLAN.md was updated
- If code files changed but MASTERPLAN.md didn't → **Commit BLOCKED** ⚠️
- If both changed → **Commit ALLOWED** ✅

**Example:**
```bash
# ❌ This will FAIL
git add app/Http/Controllers/OrderController.php
git commit -m "Fix order bug"

# Error: MASTERPLAN.md not updated!

# ✅ This will WORK
git add app/Http/Controllers/OrderController.php
git add MASTERPLAN.md
git commit -m "Fix order bug + update MASTERPLAN"
```

---

### **2. Node.js Check Script** 🤖

**Location:** `scripts/check-masterplan.js`

**Run manually:**
```bash
npm run check-masterplan
```

**Auto-run before commit:**
```bash
npm run commit-with-masterplan
```

**What it does:**
- Checks staged files
- Detects if code files changed
- Verifies MASTERPLAN.md is also staged
- Shows helpful error messages if not updated

---

### **3. AI Memory** 🧠

**Saved in project memory:**
> "Setiap menyelesaikan task di project AntarkanMa, saya WAJIB otomatis update MASTERPLAN.md tanpa perlu diminta"

**Effect:** AI assistants (Qwen, Claude, etc.) will automatically:
1. Update MASTERPLAN.md after completing any task
2. Follow the workflow in `.agents/workflows/update-masterplan.md`
3. Commit documentation changes with code

---

## 📋 WORKFLOW FOR DEVELOPERS

### **Before Commit:**

```bash
# 1. Make your code changes
git add app/...

# 2. Update MASTERPLAN.md (manual or AI does it)
# Edit MASTERPLAN.md, add tasks to "## ✅ SELESAI" section

git add MASTERPLAN.md

# 3. Commit (pre-commit hook will verify)
git commit -m "Feature: Added order notifications"

# If MASTERPLAN.md not updated → Commit REJECTED ❌
# If MASTERPLAN.md updated → Commit SUCCESS ✅
```

### **After Task Completion (AI):**

AI automatically:
1. ✅ Completes code changes
2. ✅ Updates MASTERPLAN.md
3. ✅ Shows summary of changes
4. ✅ Ready to commit

---

## 🔧 CONFIGURATION FILES

| File | Purpose |
|------|---------|
| `.git/hooks/pre-commit` | Git hook that enforces MASTERPLAN.md updates |
| `scripts/check-masterplan.js` | Node.js script to check documentation |
| `package.json` | NPM scripts for automation |
| `.vscode/tasks.json` | VS Code task for quick commit |
| `.agents/workflows/update-masterplan.md` | AI workflow documentation |

---

## 🚨 BYPASS OPTIONS (Not Recommended)

### **Temporary Bypass:**
```bash
git commit --no-verify -m "Quick fix"
```

⚠️ **Warning:** Only use for emergencies. Documentation is important!

### **Disable Hook:**
```bash
# Remove pre-commit hook
rm .git/hooks/pre-commit  # Linux/Mac
del .git\hooks\pre-commit  # Windows
```

⚠️ **Warning:** Not recommended. Hook ensures documentation quality.

---

## 📊 BENEFITS

| Benefit | Description |
|---------|-------------|
| **Always Updated** | MASTERPLAN.md is always current |
| **No Manual Reminder** | No need to remember to update |
| **Automated Check** | Pre-commit hook enforces rule |
| **AI Compliance** | AI assistants follow workflow automatically |
| **Team Alignment** | Everyone follows same process |
| **Audit Trail** | Clear history of what was done when |

---

## 🎯 BEST PRACTICES

### **DO:**
- ✅ Update MASTERPLAN.md immediately after task completion
- ✅ Be specific in task descriptions
- ✅ Update project status percentage when significant progress made
- ✅ Include date in "Last Updated"
- ✅ Commit MASTERPLAN.md with code changes

### **DON'T:**
- ❌ Commit code without updating MASTERPLAN.md
- ❌ Use bypass unless absolutely necessary
- ❌ Remove pre-commit hook
- ❌ Update MASTERPLAN.md without actual code changes
- ❌ Forget to update "Last Updated" date

---

## 🧪 TESTING

### **Test Pre-Commit Hook:**

```bash
# 1. Make a code change
echo "// Test" >> app/Http/Controllers/TestController.php
git add app/Http/Controllers/TestController.php

# 2. Try to commit without MASTERPLAN.md
git commit -m "Test commit"

# Expected: ❌ Commit REJECTED

# 3. Add MASTERPLAN.md update
git add MASTERPLAN.md
git commit -m "Test commit + MASTERPLAN update"

# Expected: ✅ Commit SUCCESS
```

---

## 📝 EXAMPLE WORKFLOW

### **Scenario: Fix Bottom Navigation Bug**

**1. Complete Code Changes:**
```bash
# Edit files
- mobile/courier/lib/app/routes/app_routes.dart
- mobile/courier/lib/app/routes/app_pages.dart

git add mobile/courier/lib/app/routes/
```

**2. Update MASTERPLAN.md:**
```markdown
## ✅ SELESAI

### Courier App (Flutter)
- [x] **Route fix** - Added `/chat` route for bottom navigation
- [x] **Routes updated** - Added ChatListPage route in app_pages.dart
```

**3. Commit:**
```bash
git add MASTERPLAN.md
git commit -m "Fix: Bottom navigation bug + update MASTERPLAN"
```

**4. Pre-commit Hook Checks:**
```
✅ Code files detected: app_routes.dart, app_pages.dart
✅ MASTERPLAN.md detected
✅ Ready to commit!
```

---

## 🎓 TRAINING FOR NEW TEAM MEMBERS

### **Onboarding Steps:**

1. **Read** `.agents/workflows/update-masterplan.md`
2. **Understand** why MASTERPLAN.md is important
3. **Practice** updating MASTERPLAN.md
4. **Test** pre-commit hook
5. **Commit** first task with documentation

### **Quick Reference:**

```
Code Change → Update MASTERPLAN.md → Commit → Push
     ↓                ↓                 ↓        ↓
  Edit files    Add to "✅ SELESAI"  git add  git push
```

---

## 📞 SUPPORT

**Issue:** Pre-commit hook not working?

**Solution:**
```bash
# Make sure hook is executable (Linux/Mac)
chmod +x .git/hooks/pre-commit

# Check hook exists
ls -la .git/hooks/pre-commit

# Test hook manually
.git/hooks/pre-commit
```

**Issue:** Node.js script not working?

**Solution:**
```bash
# Install dependencies
npm install

# Run script manually
node scripts/check-masterplan.js
```

---

## ✨ SUMMARY

**Before:**
- ❌ Manual reminder needed
- ❌ Often forgotten
- ❌ Inconsistent documentation

**After:**
- ✅ Automatic enforcement
- ✅ Always updated
- ✅ Consistent documentation
- ✅ AI compliance
- ✅ Team alignment

---

**Created:** 4 Maret 2026  
**By:** AI Assistant  
**Status:** ✅ **ACTIVE & ENFORCED**
