# 📚 AntarkanMa Documentation Hub

> **Last Updated:** 27 Februari 2026  
> **Project Status:** 85% Complete (MVP Ready)  
> **Target Soft Launch:** Mid Mei 2026

Welcome to the complete documentation for **AntarkanMa** — a delivery platform connecting customers, merchants, and couriers.

---

## 🎯 Quick Navigation

### 🚀 Start Here
| Document | Description |
|----------|-------------|
| [[Welcome]] | Entry point to the documentation |
| [[comprehensive-plan]] | **Master Plan** — Complete project overview, timeline, and status |
| [[technical-specifications]] | Full API reference and technical specs |
| [[documentation-map]] | Visual map (Excalidraw) of all documentation |

---

## 📂 Documentation Structure

```
AntarkanMa/
├── 📊 Planning & Strategy (6 files)
├── 🏗️ Architecture (7 files)
├── 🔌 API Documentation (5 files)
├── 💼 Business Layer (3 files)
├── ✨ Features (8 files)
├── 🏢 Company (4 files)
├── 🚀 Deployment & DevOps (3 files)
├── 📖 Guides & References (4 files)
├── 🤖 AI & MCP Tools (4 files)
├── 📈 Progress Logs (3 files)
└── 📋 Audit Reports (2 files)
```

---

## 📊 Planning & Strategy

Documents related to project planning, sprints, and backlog management.

| File | Description | Related |
|------|-------------|---------|
| [[comprehensive-plan]] | **Master Plan** — Complete project analysis, timeline to soft launch, metrics | [[sprint-12-13-plan]], [[active-backlog]] |
| [[project-planning]] | Overall project planning and strategy | [[comprehensive-plan]] |
| [[sprint-12-13-plan]] | Sprint 12-13 specific planning and tasks | [[comprehensive-plan]], [[active-backlog]] |
| [[active-backlog]] | Current product backlog with priorities | [[sprint-12-13-plan]], [[work-plan]] |
| [[work-plan]] | Detailed work breakdown and assignments | [[active-backlog]], [[project-board-setup]] |
| [[project-board-setup]] | GitHub project board configuration | [[work-plan]], [[github-mcp-project-guide]] |

---

## 🏗️ Architecture

System architecture, diagrams, and data flow documentation.

| File | Description | Related |
|------|-------------|---------|
| [[dfd-level-0]] | **Context Diagram** — System overview with external entities | [[dfd-level-1]], [[architecture/dfd-audit-report]] |
| [[dfd-level-1]] | **Process Diagram** — Detailed internal processes | [[dfd-level-0]], [[data-flow-design]] |
| [[erd-diagram]] | Entity Relationship Diagram for database | [[database-schema]], [[class-diagram]] |
| [[database-schema]] | Complete database structure and relationships | [[erd-diagram]], [[mysql-quick-reference]] |
| [[sequence-diagram]] | Sequence diagrams for key workflows | [[transaction-order-flow]], [[business/transaction-flow]] |
| [[class-diagram]] | Class structure and relationships | [[technical-specifications]], [[erd-diagram]] |
| [[data-flow-design]] | Detailed data flow design and processes | [[dfd-level-1]], [[sequence-diagram]] |

---

## 🔌 API Documentation

Complete API reference for all endpoints.

| File | Description | Related |
|------|-------------|---------|
| [[api/api-reference]] | **Main API Index** — Complete endpoint list | [[technical-specifications]], [[api/transaction-flow]] |
| [[api/user-api]] | User management API (auth, profile) | [[api/api-reference]], [[database-schema]] |
| [[api/merchant-api]] | Merchant management API | [[api/api-reference]], [[features/operational-hours-and-targets]] |
| [[api/courier-api]] | Courier management API | [[api/api-reference]], [[courier-audit-report]] |
| [[api/transaction-flow]] | Transaction API flow and processes | [[api/api-reference]], [[business/transaction-flow]], [[sequence-diagram]] |

---

## 💼 Business Layer

Business requirements, use cases, and user stories.

| File | Description | Related |
|------|-------------|---------|
| [[business/use-cases]] | Complete use case documentation | [[business/user-stories]], [[dfd-level-0]] |
| [[business/user-stories]] | User stories by role (customer, merchant, courier) | [[business/use-cases]], [[active-backlog]] |
| [[business/transaction-flow]] | Business transaction flow | [[api/transaction-flow]], [[sequence-diagram]] |

---

## ✨ Features

Detailed feature specifications and implementations.

| File | Description | Related |
|------|-------------|---------|
| [[features/delivery-cost-calculation]] | Ongkir calculation logic and algorithms | [[dfd-level-0]], [[database-schema]] |
| [[features/payment-system-options]] | Payment system analysis and options | [[features/payment-and-fee-management]], [[features/payment-implementation-details]] |
| [[features/payment-and-fee-management]] | Payment and fee management design | [[features/payment-system-options]], [[database-schema]] |
| [[features/payment-implementation-details]] | Technical payment implementation | [[features/payment-system-options]], [[api/transaction-flow]] |
| [[features/order-verification-system]] | Order verification workflow | [[business/use-cases]], [[sequence-diagram]] |
| [[features/fcm-api-prompt]] | Firebase Cloud Messaging setup | [[mcp-setup-guide]], [[database-schema]] |
| [[features/operational-hours-and-targets]] | Merchant operating hours and targets | [[api/merchant-api]], [[database-schema]] |
| [[features/payment-workflow-by-role]] | Payment workflows by user role | [[features/payment-and-fee-management]], [[business/user-stories]] |

---

## 🏢 Company

Company profile, business model, and roadmap.

| File | Description | Related |
|------|-------------|---------|
| [[company/company-profile]] | Company overview and mission | [[company/business-model]] |
| [[company/business-model]] | Business model canvas and strategy | [[company/company-profile]], [[company/problems-and-solutions]] |
| [[company/problems-and-solutions]] | Problems solved and solutions offered | [[company/business-model]], [[company/growth-roadmap]] |
| [[company/growth-roadmap]] | Growth strategy and roadmap | [[comprehensive-plan]], [[company/problems-and-solutions]] |

---

## 🚀 Deployment & DevOps

Deployment guides, infrastructure, and checklists.

| File | Description | Related |
|------|-------------|---------|
| [[deployment/deployment-guide]] | Complete deployment guide | [[deployment-checklist]], [[deployment/load-balancer]] |
| [[deployment/load-balancer]] | Load balancer configuration | [[deployment/deployment-guide]], [[technical-specifications]] |
| [[deployment-checklist]] | Pre-deployment checklist | [[deployment/deployment-guide]], [[comprehensive-plan]] |

---

## 📖 Guides & References

How-to guides and quick references.

| File | Description | Related |
|------|-------------|---------|
| [[mysql-setup-guide]] | MySQL database setup guide | [[mysql-quick-reference]], [[database-schema]] |
| [[mysql-quick-reference]] | MySQL quick reference and common queries | [[mysql-setup-guide]], [[database-schema]] |
| [[troubleshooting-guide]] | Common issues and solutions | [[deployment/deployment-guide]], [[mysql-setup-guide]] |
| [[e2e-test-guide]] | End-to-end testing guide | [[api-testing-checklist]], [[comprehensive-plan]] |

---

## 🤖 AI & MCP Tools

AI assistant setup and MCP (Model Context Protocol) configuration.

| File | Description | Related |
|------|-------------|---------|
| [[mcp-setup-guide]] | MCP (Model Context Protocol) setup | [[context7-mcp-setup]], [[ai-documentation-guide]] |
| [[context7-mcp-setup]] | Context7 MCP specific configuration | [[mcp-setup-guide]], [[context7-quickref]] |
| [[ai-documentation-guide]] | Guide for AI-assisted documentation | [[mcp-setup-guide]], [[ai-memory-context]] |
| [[ai-memory-context]] | AI memory and context management | [[ai-documentation-guide]], [[mcp-setup-guide]] |
| [[context7-quickref]] | Context7 quick reference | [[context7-mcp-setup]] |
| [[github-mcp-project-guide]] | GitHub MCP project integration | [[project-board-setup]], [[mcp-setup-guide]] |

---

## 📈 Progress Logs

Development progress and fix logs.

| File | Description | Related |
|------|-------------|---------|
| [[progress-log]] | Main development progress log | [[progress-log-update]], [[comprehensive-plan]] |
| [[progress-log-update]] | Update progress log | [[progress-log]], [[sprint-12-13-plan]] |
| [[progress-log-courier-fix]] | Courier-specific fix log | [[progress-log]], [[courier-audit-report]] |

---

## 📋 Audit Reports

System audit reports and analysis.

| File | Description | Related |
|------|-------------|---------|
| [[dfd-audit-report]] | DFD audit and recommendations | [[dfd-level-0]], [[dfd-level-1]] |
| [[courier-audit-report]] | Courier module audit | [[api/courier-api]], [[progress-log-courier-fix]] |

---

## 🔗 Cross-Reference Maps

### By User Role

#### Customer Flow
```
[[business/user-stories]] → [[api/user-api]] → [[business/transaction-flow]] → [[features/order-verification-system]] → [[sequence-diagram]]
```

#### Merchant Flow
```
[[api/merchant-api]] → [[features/operational-hours-and-targets]] → [[business/use-cases]] → [[dfd-level-1]]
```

#### Courier Flow
```
[[api/courier-api]] → [[features/delivery-cost-calculation]] → [[courier-audit-report]] → [[progress-log-courier-fix]]
```

### By Development Phase

#### Planning Phase
```
[[comprehensive-plan]] → [[project-planning]] → [[sprint-12-13-plan]] → [[active-backlog]] → [[work-plan]]
```

#### Architecture Phase
```
[[dfd-level-0]] → [[dfd-level-1]] → [[erd-diagram]] → [[database-schema]] → [[sequence-diagram]] → [[class-diagram]]
```

#### Implementation Phase
```
[[technical-specifications]] → [[api/api-reference]] → [[features/*]] → [[deployment/deployment-guide]]
```

#### Testing Phase
```
[[e2e-test-guide]] → [[api-testing-checklist]] → [[deployment-checklist]]
```

---

## 📊 Project Metrics

| Metric | Status | Target | Gap |
|--------|--------|--------|-----|
| API Endpoints | 80+ (80%) | 100 | +20 endpoints |
| Database Tables | 17 (100%) | 17 | ✅ Complete |
| Mobile Apps | 3 (100%) | 3 | ✅ Complete |
| Test Coverage | ~5% | 80% | +75% needed |
| Documentation | 20+ pages (80%) | 25 | +5 pages |
| Production Ready | ~85% | 100% | +15% needed |

---

## 🎯 Timeline to Soft Launch

```
Sekarang (27 Feb) ────────────────────────────────────────► Mid Mei 2026
     │                    │                    │                    │
     ▼                    ▼                    ▼                    ▼
┌─────────┐         ┌─────────┐         ┌─────────┐         ┌─────────┐
│Critical │         │ Testing │         │  Core   │         │  Pre-   │
│  Fixes  │        │Foundation│        │ Features│         │ Launch  │
│ 2 minggu│         │ 2 minggu│         │ 2 minggu│         │ 2 minggu│
└─────────┘         └─────────┘         └─────────┘         └─────────┘
```

---

## 📝 How to Use This Documentation

### For Developers
1. Start with [[technical-specifications]] for API details
2. Reference [[database-schema]] for data structures
3. Check [[sequence-diagram]] for workflow understanding
4. Use [[troubleshooting-guide]] for common issues

### For Project Managers
1. Review [[comprehensive-plan]] for overall status
2. Track progress in [[progress-log]]
3. Manage backlog in [[active-backlog]]
4. Plan sprints with [[sprint-12-13-plan]]

### For Stakeholders
1. Read [[company/company-profile]] for company overview
2. Review [[company/business-model]] for business strategy
3. Check [[company/growth-roadmap]] for future plans
4. Monitor progress in [[comprehensive-plan]]

### For AI Assistants
1. Setup with [[mcp-setup-guide]]
2. Configure context with [[context7-mcp-setup]]
3. Follow [[ai-documentation-guide]] for documentation standards
4. Reference [[ai-memory-context]] for context management

---

## 📞 Quick Links

- **Visual Map:** [[documentation-map]] (Excalidraw)
- **API Testing:** [[api-testing-checklist]]
- **Deployment:** [[deployment-checklist]]
- **MySQL:** [[mysql-quick-reference]]
- **MCP Setup:** [[mcp-setup-guide]]

---

*This documentation hub is interconnected using wiki-style links. Click any [[link]] to navigate between related documents.*
