# 📋 GitHub Issues Creation Guide — AntarkanMa

> **Quick guide untuk membuat GitHub Issues dari MASTERPLAN.md**
>
> 📅 **Created:** 8 Maret 2026  
> 🎯 **Project:** https://github.com/users/Aswar12/projects/2

---

## 🎯 GitHub Project Info

| Property | Value |
|----------|-------|
| **Project Name** | AntarkanMa |
| **Project URL** | https://github.com/users/Aswar12/projects/2 |
| **Project ID** | `PVT_kwHOBBILe84BQTKS` |
| **Repository** | Aswar12/Antarkanma.my.id |
| **Status Fields** | Todo, In Progress, Done |

---

## 📝 Issues to Create

### Priority 1 — High (Minggu Ini)

#### 1. T-03: Chat message bug fixes
```markdown
---
title: "🔴 T-03: Chat message bug fixes - All Apps"
labels: [bug, high-priority, chat, mobile]
assignees: [Aswar12]
project: AntarkanMa/2
status: In Progress
---

## Description
Fix chat message bugs in Courier, Customer, and Merchant apps related to pagination and message loading.

## Tasks
- [ ] Fix pagination logic in ChatRepository
- [ ] Test infinite scroll on all 3 apps
- [ ] Fix loading indicator display
- [ ] Verify date separators with pagination

## Affected Files
- `mobile/courier/lib/app/modules/chat/`
- `mobile/customer/lib/app/modules/chat/`
- `mobile/merchant/lib/app/modules/chat/`

## Testing
- [ ] Test with 100+ messages
- [ ] Test scroll performance
- [ ] Test on slow network

## References
- MASTERPLAN.md: T-03
- Related: T-02 (Chat pagination - completed)
```

---

### Priority 2 — Medium

#### 2. C-10: Image upload compression
```markdown
---
title: "🟡 C-10: Image upload compression - Backend"
labels: [enhancement, medium-priority, backend, optimization]
assignees: [Aswar12]
project: AntarkanMa/2
status: Todo
---

## Description
Implement image compression before upload to reduce bandwidth and storage usage.

## Tasks
- [ ] Add image compression library (Intervention Image)
- [ ] Compress images before S3 upload
- [ ] Add config for compression quality
- [ ] Maintain aspect ratio

## Acceptance Criteria
- Images compressed to max 500KB
- Quality maintained at 80%
- Configurable via .env

## Estimated Time
4 hours
```

#### 3. F-07: Final E2E testing
```markdown
---
title: "🟡 F-07: Final E2E testing - All Apps"
labels: [testing, medium-priority, e2e]
assignees: [Aswar12]
project: AntarkanMa/2
status: Todo
---

## Description
Complete end-to-end testing for all critical user flows.

## Test Scenarios
- [ ] User registration & login
- [ ] Browse merchants & products
- [ ] Add to cart & checkout
- [ ] Order tracking
- [ ] Chat with media sharing
- [ ] Review submission
- [ ] Payment (COD)

## Apps to Test
- [ ] Customer App
- [ ] Merchant App
- [ ] Courier App

## Estimated Time
8 hours
```

---

### Priority 2 — Low

#### 4. C-11: Error boundary handling
```markdown
---
title: "⚪ C-11: Error boundary handling - Mobile"
labels: [enhancement, low-priority, mobile, error-handling]
assignees: [Aswar12]
project: AntarkanMa/2
status: Todo
---

## Description
Add error boundaries and fallback UI for mobile apps to handle crashes gracefully.

## Tasks
- [ ] Implement error boundary widget
- [ ] Add fallback UI for each screen
- [ ] Log errors to analytics
- [ ] Show user-friendly error messages

## Apps
- [ ] Customer App
- [ ] Merchant App
- [ ] Courier App

## Estimated Time
6 hours
```

#### 5. F-08: Offline mode support
```markdown
---
title: "⚪ F-08: Offline mode support - Mobile"
labels: [enhancement, low-priority, mobile, offline]
assignees: [Aswar12]
project: AntarkanMa/2
status: Todo
---

## Description
Implement offline mode with local storage and background sync.

## Features
- [ ] Cache product & merchant data
- [ ] Queue actions when offline
- [ ] Sync when back online
- [ ] Show offline indicator

## Estimated Time
12 hours
```

---

### Priority 3 — Infrastructure

#### 6. Testing Infrastructure
```markdown
---
title: "📋 Testing Infrastructure - PHPUnit Setup"
labels: [testing, infrastructure, phpunit]
assignees: [Aswar12]
project: AntarkanMa/2
status: Todo
---

## Description
Complete PHPUnit testing infrastructure for backend.

## Tasks
- [ ] Setup PHPUnit with Laravel
- [ ] Create test database configuration
- [ ] Add base test classes
- [ ] Write tests for critical endpoints

## Coverage Target
- API endpoints: 80%
- Models: 80%
- Services: 60%

## Estimated Time
40 hours
```

#### 7. Security Hardening
```markdown
---
title: "📋 Security Hardening - Rate Limiting & 2FA"
labels: [security, backend, rate-limiting, 2fa]
assignees: [Aswar12]
project: AntarkanMa/2
status: Todo
---

## Description
Implement rate limiting and 2FA for admin panel.

## Tasks
- [ ] Configure rate limiting (60 req/min)
- [ ] Add 2FA for admin accounts
- [ ] Implement IP blocking
- [ ] Add security headers
- [ ] Audit sensitive endpoints

## Estimated Time
20 hours
```

#### 8. Payment Gateway
```markdown
---
title: "📋 Payment Gateway Integration - Midtrans/Xendit"
labels: [payment, backend, integration]
assignees: [Aswar12]
project: AntarkanMa/2
status: Todo
---

## Description
Integrate payment gateway (Midtrans or Xendit) for automated payments.

## Tasks
- [ ] Choose payment provider
- [ ] Setup merchant account
- [ ] Implement payment callbacks
- [ ] Add payment status tracking
- [ ] Test with sandbox environment

## Payment Methods
- [ ] Credit/Debit Card
- [ ] Bank Transfer
- [ ] E-Wallet (GoPay, OVO, DANA)
- [ ] QRIS

## Estimated Time
25 hours
```

---

## 🚀 How to Create Issues

### Option 1: GitHub CLI (Recommended)

```bash
# Install GitHub CLI: https://cli.github.com/
gh auth login

# Create issue
gh issue create \
  --title "🔴 T-03: Chat message bug fixes - All Apps" \
  --body-file "GITHUB_ISSUES_TEMPLATES.md" \
  --label "bug,high-priority,chat,mobile" \
  --project "AntarkanMa/2"
```

### Option 2: GitHub Web Interface

1. Go to: https://github.com/Aswar12/Antarkanma.my.id/issues
2. Click "New issue"
3. Copy-paste from templates above
4. Add labels and assign to project

### Option 3: PowerShell Script

```powershell
# Run from project root
.\scripts\create-github-issues.ps1 -GitHubToken "ghp_your_token"
```

---

## 📊 Issue Tracking

After creating all issues, track them in the project board:

**Project Board:** https://github.com/users/Aswar12/projects/2

| Column | Issues |
|--------|--------|
| 🔴 **In Progress** | T-03 |
| 🟡 **Todo** | C-10, F-07, C-11, F-08, Testing, Security, Payment |
| ✅ **Done** | T-02, Ku-05, B-06 |

---

## 🔗 Quick Links

- **Project Board:** https://github.com/users/Aswar12/projects/2
- **Repository:** https://github.com/Aswar12/Antarkanma.my.id
- **Issues:** https://github.com/Aswar12/Antarkanma.my.id/issues
- **MASTERPLAN:** MASTERPLAN.md

---

**Last Updated:** 8 Maret 2026  
**Maintained By:** AntarkanMa Team
