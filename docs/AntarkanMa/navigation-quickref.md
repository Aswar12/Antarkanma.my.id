# 📋 Navigation Quick Reference

> **Purpose:** Quick access to all interconnected documents in the AntarkanMa documentation  
> **Last Updated:** 27 Februari 2026

---

## 🎯 Entry Points

| Document | Purpose | For |
|----------|---------|-----|
| [[Welcome]] | Main entry point to documentation | Everyone |
| [[README]] | Complete index with descriptions | Everyone |
| [[documentation-map]] | Visual Excalidraw map | Visual learners |
| [[comprehensive-plan]] | Master project plan | PM, Stakeholders |

---

## 📂 By Category

### 📊 Planning & Strategy (6 files)
- [[comprehensive-plan]] - Master plan with timeline
- [[project-planning]] - Overall strategy
- [[sprint-12-13-plan]] - Current sprint details
- [[active-backlog]] - Active backlog items
- [[work-plan]] - Work breakdown
- [[project-board-setup]] - GitHub board setup

### 🏗️ Architecture (7 files)
- [[dfd-level-0]] - Context diagram
- [[dfd-level-1]] - Process diagram
- [[erd-diagram]] - Entity relationships
- [[database-schema]] - Database structure
- [[sequence-diagram]] - Sequence diagrams
- [[class-diagram]] - Class structure
- [[data-flow-design]] - Data flow design

### 🔌 API (5 files)
- [[api/api-reference]] - Main API index
- [[api/user-api]] - User endpoints
- [[api/merchant-api]] - Merchant endpoints
- [[api/courier-api]] - Courier endpoints
- [[api/transaction-flow]] - Transaction flow

### 💼 Business (3 files)
- [[business/use-cases]] - Use cases
- [[business/user-stories]] - User stories
- [[business/transaction-flow]] - Business flow

### ✨ Features (8 files)
- [[features/delivery-cost-calculation]] - Ongkir calculation
- [[features/payment-system-options]] - Payment options
- [[features/payment-and-fee-management]] - Payment management
- [[features/payment-implementation-details]] - Payment implementation
- [[features/order-verification-system]] - Order verification
- [[features/fcm-api-prompt]] - FCM setup
- [[features/operational-hours-and-targets]] - Operating hours
- [[features/payment-workflow-by-role]] - Payment by role

### 🏢 Company (4 files)
- [[company/company-profile]] - Company overview
- [[company/business-model]] - Business model
- [[company/problems-and-solutions]] - Problems & solutions
- [[company/growth-roadmap]] - Growth roadmap

### 🚀 Deployment (3 files)
- [[deployment/deployment-guide]] - Deployment guide
- [[deployment/load-balancer]] - Load balancer setup
- [[deployment-checklist]] - Pre-deployment checklist

### 📖 Guides (4 files)
- [[mysql-setup-guide]] - MySQL setup
- [[mysql-quick-reference]] - MySQL quick reference
- [[troubleshooting-guide]] - Troubleshooting
- [[e2e-test-guide]] - E2E testing

### 🤖 AI & MCP (6 files)
- [[mcp-setup-guide]] - MCP setup
- [[context7-mcp-setup]] - Context7 setup
- [[ai-documentation-guide]] - AI documentation guide
- [[ai-memory-context]] - AI memory context
- [[context7-quickref]] - Context7 quick reference
- [[github-mcp-project-guide]] - GitHub MCP guide

### 📈 Progress (3 files)
- [[progress-log]] - Main progress log
- [[progress-log-update]] - Progress updates
- [[progress-log-courier-fix]] - Courier fix log

### 📋 Reports (2 files)
- [[dfd-audit-report]] - DFD audit
- [[courier-audit-report]] - Courier audit

### ⚙️ Other (3 files)
- [[technical-specifications]] - Technical specs
- [[transaction-order-flow]] - Order flow
- [[api-testing-checklist]] - API testing checklist

---

## 🔗 Common Navigation Paths

### For New Developers
```
[[Welcome]] → [[README]] → [[technical-specifications]] → [[api/api-reference]] → [[mysql-setup-guide]]
```

### For Project Managers
```
[[Welcome]] → [[comprehensive-plan]] → [[active-backlog]] → [[sprint-12-13-plan]] → [[progress-log]]
```

### For Backend Developers
```
[[README]] → [[technical-specifications]] → [[database-schema]] → [[api/api-reference]] → [[sequence-diagram]]
```

### For Flutter Developers
```
[[README]] → [[api/api-reference]] → [[sequence-diagram]] → [[business/use-cases]] → [[features/*]]
```

### For Stakeholders
```
[[Welcome]] → [[company/company-profile]] → [[company/business-model]] → [[comprehensive-plan]]
```

### For AI Assistants
```
[[mcp-setup-guide]] → [[context7-mcp-setup]] → [[ai-documentation-guide]] → [[README]]
```

---

## 📊 Document Relationships

### Core Dependencies
```
[[comprehensive-plan]]
├── [[sprint-12-13-plan]]
├── [[active-backlog]]
├── [[work-plan]]
└── [[progress-log]]
```

### Architecture Flow
```
[[dfd-level-0]] → [[dfd-level-1]] → [[data-flow-design]]
                        ↓
[[erd-diagram]] → [[database-schema]] → [[class-diagram]] → [[sequence-diagram]]
```

### API Hierarchy
```
[[technical-specifications]]
└── [[api/api-reference]]
    ├── [[api/user-api]]
    ├── [[api/merchant-api]]
    ├── [[api/courier-api]]
    └── [[api/transaction-flow]]
```

### Feature Dependencies
```
[[business/use-cases]] → [[features/order-verification-system]]
[[database-schema]] → [[features/delivery-cost-calculation]]
[[api/transaction-flow]] → [[features/payment-*]]
```

---

## 🎯 Quick Status Check

| Area | Status | Key Document |
|------|--------|--------------|
| Backend | 95% | [[technical-specifications]] |
| Mobile Apps | 90% | [[progress-log]] |
| Documentation | 80% | [[README]] |
| Testing | 5% | [[e2e-test-guide]] |
| Deployment | 85% | [[deployment-checklist]] |

---

## 📝 How to Navigate

1. **Click Links:** All `[[document-name]]` are clickable wiki-style links
2. **Use Categories:** Browse by category above
3. **Follow Paths:** Use recommended navigation paths for your role
4. **Visual Map:** Open [[documentation-map]] in Excalidraw for visual overview

---

*This navigation reference is part of the interconnected AntarkanMa documentation system*
