# 🗺️ Documentation Map — AntarkanMa

> **Panduan navigasi semua file dokumentasi**
>
> 🔗 Related: [[README|Documentation Hub]], [[../MASTERPLAN|MASTERPLAN]], [[QUICKSTART]]

---

## 📍 File Locations

### Root Level (Project Root)

| File | Purpose | Size | When to Read |
|------|---------|------|--------------|
| `MASTERPLAN.md` | Prioritas aktif & status | <150 baris | Setiap sesi |
| `CONTRIBUTING.md` | Contribution guidelines | Reference | Contribute |
| `README.md` | Project overview | Reference | First time |

### docs/ Folder

| File | Purpose | Size | When to Read |
|------|---------|------|--------------|
| `docs/QUICKSTART.md` | ⭐ **AI session startup** | 5 min read | **EVERY session** |
| `docs/TEST_DATA.md` | Test accounts & data | Reference | Testing |
| `docs/ARCHIVE.md` | Completed tasks history | History | Reference |
| `docs/AntarkanMa/` | Complete documentation | 40+ files | As needed |

---

## 🧭 Navigation Flow

### AI Agent Session Flow

```
START
  │
  ├─→ 1. docs/QUICKSTART.md (5 min)
  │      └─→ Check priorities: MASTERPLAN.md
  │      └─→ Read context: docs/AntarkanMa/ai-memory-context.md
  │
  ├─→ 2. Choose task from:
  │      └─→ MASTERPLAN.md (prioritas minggu ini)
  │      └─→ docs/AntarkanMa/active-backlog.md (detailed backlog)
  │
  ├─→ 3. Read reference docs:
  │      └─→ API: docs/AntarkanMa/api/
  │      └─→ Architecture: docs/AntarkanMa/architecture/
  │      └─→ Features: docs/AntarkanMa/features/
  │
  ├─→ 4. Code & Test
  │
  └─→ 5. Update documentation:
         └─→ MASTERPLAN.md (mark complete)
         └─→ docs/ARCHIVE.md (move completed)
         └─→ docs/AntarkanMa/progress-log.md (log session)
```

---

## 📚 Documentation Categories

### 🚀 Quick Access (Must Read)

| File | Path | Purpose |
|------|------|---------|
| **QUICKSTART** | `docs/QUICKSTART.md` | Session startup guide |
| **MASTERPLAN** | `MASTERPLAN.md` | Current priorities |
| **AI Context** | `docs/AntarkanMa/ai-memory-context.md` | Session context |
| **Test Data** | `docs/TEST_DATA.md` | Test accounts |

### 📋 Planning & Strategy

| File | Path | Purpose |
|------|------|---------|
| **Active Backlog** | `docs/AntarkanMa/active-backlog.md` | Current tasks |
| **Progress Log** | `docs/AntarkanMa/progress-log.md` | Session logs |
| **Comprehensive Plan** | `docs/AntarkanMa/comprehensive-plan.md` | Master plan (old) |
| **Archive** | `docs/ARCHIVE.md` | Completed tasks |

### 🏗️ Architecture

| File | Path | Purpose |
|------|------|---------|
| **DFD Level 0** | `docs/AntarkanMa/architecture/dfd-level-0.md` | Context diagram |
| **DFD Level 1** | `docs/AntarkanMa/architecture/dfd-level-1.md` | Process diagram |
| **ERD** | `docs/AntarkanMa/architecture/erd-diagram.md` | Database ERD |
| **Database Schema** | `docs/AntarkanMa/architecture/database-schema.md` | DB structure |
| **Sequence Diagram** | `docs/AntarkanMa/architecture/sequence-diagram.md` | Workflows |
| **Class Diagram** | `docs/AntarkanMa/architecture/class-diagram.md` | Class structure |

### 🔌 API Documentation

| File | Path | Purpose |
|------|------|---------|
| **API Reference** | `docs/AntarkanMa/api/api-reference.md` | Complete API |
| **User API** | `docs/AntarkanMa/api/user-api.md` | User endpoints |
| **Merchant API** | `docs/AntarkanMa/api/merchant-api.md` | Merchant endpoints |
| **Courier API** | `docs/AntarkanMa/api/courier-api.md` | Courier endpoints |
| **Transaction Flow** | `docs/AntarkanMa/api/transaction-flow.md` | Transaction API |

### 💼 Business Layer

| File | Path | Purpose |
|------|------|---------|
| **Use Cases** | `docs/AntarkanMa/business/use-cases.md` | Use cases |
| **User Stories** | `docs/AntarkanMa/business/user-stories.md` | User stories |
| **Transaction Flow** | `docs/AntarkanMa/business/transaction-flow.md` | Business flow |

### ✨ Features

| File | Path | Purpose |
|------|------|---------|
| **Delivery Cost** | `docs/AntarkanMa/features/delivery-cost-calculation.md` | Ongkir |
| **Payment System** | `docs/AntarkanMa/features/payment-system-options.md` | Payment |
| **Payment & Fee** | `docs/AntarkanMa/features/payment-and-fee-management.md` | Fees |
| **Order Verification** | `docs/AntarkanMa/features/order-verification-system.md` | Verification |
| **FCM API** | `docs/AntarkanMa/features/fcm-api-prompt.md` | Notifications |
| **Operational Hours** | `docs/AntarkanMa/features/operational-hours-and-targets.md` | Merchant hours |

### 🏢 Company

| File | Path | Purpose |
|------|------|---------|
| **Company Profile** | `docs/AntarkanMa/company/company-profile.md` | Company info |
| **Business Model** | `docs/AntarkanMa/company/business-model.md` | Business model |
| **Problems & Solutions** | `docs/AntarkanMa/company/problems-and-solutions.md` | Solutions |
| **Growth Roadmap** | `docs/AntarkanMa/company/growth-roadmap.md` | Roadmap |

### 🚀 Deployment

| File | Path | Purpose |
|------|------|---------|
| **Deployment Guide** | `docs/AntarkanMa/deployment/deployment-guide.md` | How to deploy |
| **Load Balancer** | `docs/AntarkanMa/deployment/load-balancer.md` | LB config |
| **Deployment Checklist** | `docs/AntarkanMa/deployment-checklist.md` | Pre-launch |

### 📖 Guides & References

| File | Path | Purpose |
|------|------|---------|
| **MySQL Setup** | `docs/AntarkanMa/mysql-setup-guide.md` | DB setup |
| **MySQL Quick Reference** | `docs/AntarkanMa/mysql-quick-reference.md` | SQL queries |
| **Troubleshooting** | `docs/AntarkanMa/troubleshooting-guide.md` | Common issues |
| **E2E Test Guide** | `docs/AntarkanMa/e2e-test-guide.md` | Testing guide |

### 🤖 AI & MCP

| File | Path | Purpose |
|------|------|---------|
| **MCP Setup** | `docs/AntarkanMa/mcp-setup-guide.md` | MCP setup |
| **Context7 MCP** | `docs/AntarkanMa/context7-mcp-setup.md` | Context7 |
| **AI Documentation Guide** | `docs/AntarkanMa/ai-documentation-guide.md` | AI guide |
| **AI Memory Context** | `docs/AntarkanMa/ai-memory-context.md` | AI context |
| **GitHub MCP** | `docs/AntarkanMa/github-mcp-project-guide.md` | GitHub MCP |

---

## 🔗 Cross-References

### By Workflow

#### Starting Session
```
QUICKSTART.md
  → MASTERPLAN.md
  → ai-memory-context.md
  → active-backlog.md
```

#### Coding Feature
```
ai-memory-context.md
  → architecture/dfd-level-1.md (understand flow)
  → api/api-reference.md (check endpoints)
  → features/ (business logic)
```

#### Testing
```
e2e-test-guide.md
  → TEST_DATA.md (credentials)
  → api-testing-checklist.md (test cases)
```

#### After Coding
```
MASTERPLAN.md (update status)
  → ARCHIVE.md (move completed)
  → progress-log.md (log session)
```

### By User Role

#### New Developer
```
Welcome.md
  → technical-specifications.md
  → mysql-setup-guide.md
  → QUICKSTART.md
```

#### Project Manager
```
MASTERPLAN.md
  → comprehensive-plan.md
  → active-backlog.md
  → progress-log.md
```

#### AI Assistant
```
QUICKSTART.md ⭐
  → ai-memory-context.md
  → MASTERPLAN.md
  → mcp-setup-guide.md
```

#### Stakeholder
```
company/company-profile.md
  → company/business-model.md
  → company/growth-roadmap.md
  → MASTERPLAN.md (progress)
```

---

## 📊 File Statistics

### Total Files

| Category | Count |
|----------|-------|
| Quick Access | 4 |
| Planning | 5 |
| Architecture | 7 |
| API | 5 |
| Business | 3 |
| Features | 8 |
| Company | 4 |
| Deployment | 3 |
| Guides | 4 |
| AI/MCP | 5 |
| **Total** | **48+** |

### File Sizes

| Size | Count | Purpose |
|------|-------|---------|
| <100 baris | 10 | Quick reference |
| 100-300 baris | 25 | Standard docs |
| 300-500 baris | 10 | Comprehensive guides |
| >500 baris | 3 | Master plans |

---

## 🎯 Best Practices

### DO ✅
- ✅ Read QUICKSTART.md every session
- ✅ Keep MASTERPLAN.md <150 baris
- ✅ Move completed to ARCHIVE.md
- ✅ Update progress-log.md
- ✅ Use wiki-style links `[[file]]`
- ✅ Keep documentation DRY

### DON'T ❌
- ❌ Duplicate content across files
- ❌ Let MASTERPLAN.md grow >200 baris
- ❌ Forget to update after coding
- ❌ Mix active tasks with history
- ❌ Break wiki links

---

## 🔄 Update Workflow

### Every Session
1. Read QUICKSTART.md
2. Check MASTERPLAN.md
3. Read ai-memory-context.md
4. Code
5. Update:
   - MASTERPLAN.md (status)
   - ARCHIVE.md (move completed)
   - progress-log.md (session log)

### Weekly
1. Review ARCHIVE.md
2. Clean up MASTERPLAN.md
3. Update priorities
4. Archive old tasks

---

## 📞 Quick Navigation

### Most Used Files
- [QUICKSTART](QUICKSTART.md) — Session startup
- [MASTERPLAN](../MASTERPLAN.md) — Priorities
- [TEST_DATA](TEST_DATA.md) — Test accounts
- [AI Context](ai-memory-context.md) — Session context
- [Backlog](active-backlog.md) — Task list

### Reference Files
- [API Reference](api/api-reference.md) — All endpoints
- [Architecture](architecture/dfd-level-1.md) — System flow
- [Features](features/) — Feature specs
- [Business](business/use-cases.md) — Use cases

---

**Last Updated:** 8 Maret 2026  
**Maintained By:** AntarkanMa Team
