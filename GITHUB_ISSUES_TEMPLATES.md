# GitHub Issues Templates for AntarkanMa

Repository: **Aswar12/Antarkanma.my.id**
Project: **AntarkanMa** (PVT_kwHOBBILe84BQTKS)

---

## Issue 1: T-03 Chat Message Bug Fixes

**Title:** `🔴 T-03: Chat message bug fixes - All Apps`

**Labels:** `bug`, `high-priority`, `chat`, `mobile`

**Body:**
```markdown
## Description
Fix chat message bugs in Courier, Customer, and Merchant apps.

## Tasks
- [ ] Identify and fix chat message bugs in Courier app
- [ ] Identify and fix chat message bugs in Customer app
- [ ] Identify and fix chat message bugs in Merchant app
- [ ] Test chat functionality across all apps
- [ ] Verify message delivery and display

## Apps Affected
- Courier App
- Customer App
- Merchant App

## Priority
High Priority - Critical for user experience
```

**Project Status:** In Progress

---

## Issue 2: C-10 Image Upload Compression

**Title:** `🟡 C-10: Image upload compression - Backend`

**Labels:** `enhancement`, `medium-priority`, `backend`, `optimization`

**Body:**
```markdown
## Description
Implement image compression before upload to reduce bandwidth usage and improve upload performance.

## Tasks
- [ ] Research image compression libraries for Laravel/PHP
- [ ] Implement server-side image compression
- [ ] Add compression configuration options
- [ ] Test compression quality vs file size
- [ ] Update API documentation
- [ ] Monitor bandwidth savings

## Technical Requirements
- Support common image formats (JPEG, PNG, WebP)
- Configurable compression quality
- Maintain aspect ratio
- Handle large images efficiently

## Priority
Medium Priority - Optimization improvement
```

**Project Status:** Todo

---

## Issue 3: F-07 Final E2E Testing

**Title:** `🟡 F-07: Final E2E testing - All Apps`

**Labels:** `testing`, `medium-priority`, `e2e`

**Body:**
```markdown
## Description
Complete end-to-end testing for all critical user flows across the platform.

## Tasks
- [ ] Define critical user flows to test
- [ ] Set up E2E testing infrastructure
- [ ] Write E2E tests for Customer app flows
- [ ] Write E2E tests for Merchant app flows
- [ ] Write E2E tests for Courier app flows
- [ ] Write E2E tests for Admin panel flows
- [ ] Execute test suite
- [ ] Fix identified issues
- [ ] Document test results

## Critical Flows
- User registration and authentication
- Order creation and tracking
- Payment processing
- Chat functionality
- Notifications

## Priority
Medium Priority - Quality assurance
```

**Project Status:** Todo

---

## Issue 4: C-11 Error Boundary Handling

**Title:** `⚪ C-11: Error boundary handling - Mobile`

**Labels:** `enhancement`, `low-priority`, `mobile`, `error-handling`

**Body:**
```markdown
## Description
Add error boundaries and fallback UI for mobile apps to improve user experience during errors.

## Tasks
- [ ] Implement error boundary components (React Native/Flutter)
- [ ] Add graceful error handling for network failures
- [ ] Create fallback UI screens
- [ ] Add error logging and reporting
- [ ] Implement retry mechanisms
- [ ] Test error scenarios
- [ ] Update error messages for users

## Apps Affected
- Courier App
- Customer App
- Merchant App

## Benefits
- Better user experience during failures
- Reduced app crashes
- Improved error visibility
- Easier debugging

## Priority
Low Priority - UX improvement
```

**Project Status:** Todo

---

## Issue 5: F-08 Offline Mode Support

**Title:** `⚪ F-08: Offline mode support - Mobile`

**Labels:** `enhancement`, `low-priority`, `mobile`, `offline`

**Body:**
```markdown
## Description
Implement offline mode with local storage and synchronization for mobile apps.

## Tasks
- [ ] Design offline mode architecture
- [ ] Implement local storage (SQLite/Realm)
- [ ] Add data synchronization logic
- [ ] Handle conflict resolution
- [ ] Create offline UI indicators
- [ ] Implement queue for offline actions
- [ ] Test offline scenarios
- [ ] Document offline capabilities

## Features
- View cached data when offline
- Queue actions for later sync
- Automatic sync when online
- Conflict resolution strategy
- Offline status indicator

## Apps Affected
- Courier App
- Customer App
- Merchant App

## Priority
Low Priority - Feature enhancement
```

**Project Status:** Todo

---

## Issue 6: Testing Infrastructure - PHPUnit Setup

**Title:** `📋 Testing Infrastructure - PHPUnit Setup`

**Labels:** `testing`, `infrastructure`, `phpunit`

**Body:**
```markdown
## Description
Complete PHPUnit testing infrastructure for the backend Laravel application.

## Tasks
- [ ] Set up PHPUnit configuration
- [ ] Configure test database
- [ ] Create base test classes
- [ ] Set up test factories and seeders
- [ ] Implement CI/CD integration for tests
- [ ] Write unit tests for core services
- [ ] Write integration tests for APIs
- [ ] Set up code coverage reporting
- [ ] Document testing guidelines

## Estimated Effort
40 hours

## Deliverables
- Working PHPUnit setup
- Test suite for critical paths
- CI/CD integration
- Code coverage reports
- Testing documentation

## Priority
Priority 3 - Infrastructure
```

**Project Status:** Todo

---

## Issue 7: Security Hardening - Rate Limiting & 2FA

**Title:** `📋 Security Hardening - Rate Limiting & 2FA`

**Labels:** `security`, `backend`, `rate-limiting`, `2fa`

**Body:**
```markdown
## Description
Implement rate limiting and two-factor authentication (2FA) for admin panel to enhance security.

## Tasks
- [ ] Research rate limiting strategies
- [ ] Implement API rate limiting
- [ ] Add rate limit headers
- [ ] Set up 2FA for admin users
- [ ] Implement TOTP (Time-based One-Time Password)
- [ ] Add QR code generation for 2FA setup
- [ ] Create 2FA backup codes
- [ ] Add security audit logging
- [ ] Test security features
- [ ] Document security configuration

## Estimated Effort
20 hours

## Security Features
- Configurable rate limits per endpoint
- 2FA for admin panel access
- Backup codes for account recovery
- Security event logging

## Priority
Priority 3 - Security
```

**Project Status:** Todo

---

## Issue 8: Payment Gateway Integration

**Title:** `📋 Payment Gateway Integration - Midtrans/Xendit`

**Labels:** `payment`, `backend`, `integration`

**Body:**
```markdown
## Description
Integrate payment gateway (Midtrans or Xendit) for processing customer payments.

## Tasks
- [ ] Evaluate Midtrans vs Xendit
- [ ] Select payment gateway provider
- [ ] Set up merchant account
- [ ] Implement payment gateway SDK
- [ ] Create payment flow
- [ ] Handle payment callbacks/webhooks
- [ ] Implement refund functionality
- [ ] Add payment status tracking
- [ ] Create payment history
- [ ] Test payment scenarios
- [ ] Document payment integration

## Estimated Effort
25 hours

## Payment Methods
- Credit/Debit Cards
- Bank Transfer
- E-Wallets
- QRIS

## Features
- Secure payment processing
- Webhook handling
- Payment status updates
- Refund support
- Transaction history

## Priority
Priority 3 - Feature
```

**Project Status:** Todo

---

## Setup Instructions

### Option 1: Manual Creation
1. Go to https://github.com/Aswar12/Antarkanma.my.id/issues
2. Click "New issue"
3. Copy and paste the title, labels, and body from each template above
4. Add each issue to the AntarkanMa project
5. Set the appropriate status (Todo/In Progress)

### Option 2: Using GitHub CLI
```bash
# Install GitHub CLI first: https://cli.github.com/

# Authenticate
gh auth login

# Create issues (example for first issue)
gh issue create --title "🔴 T-03: Chat message bug fixes - All Apps" --body-file issue-1-body.md --label "bug,high-priority,chat,mobile" --repo Aswar12/Antarkanma.my.id

# Add to project
gh project item-add PVT_kwHOBBILe84BQTKS --url https://github.com/Aswar12/Antarkanma.my.id/issues/1
```

### Option 3: Using GitHub API
```bash
# Set your token
export GITHUB_TOKEN=your_personal_access_token

# Create issue via API
curl -X POST https://api.github.com/repos/Aswar12/Antarkanma.my.id/issues \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  -d '{
    "title": "🔴 T-03: Chat message bug fixes - All Apps",
    "body": "...",
    "labels": ["bug", "high-priority", "chat", "mobile"]
  }'
```

---

## Summary Table

| # | Issue Title | Priority | Labels | Status |
|---|-------------|----------|--------|--------|
| 1 | 🔴 T-03: Chat message bug fixes - All Apps | High | bug, high-priority, chat, mobile | In Progress |
| 2 | 🟡 C-10: Image upload compression - Backend | Medium | enhancement, medium-priority, backend, optimization | Todo |
| 3 | 🟡 F-07: Final E2E testing - All Apps | Medium | testing, medium-priority, e2e | Todo |
| 4 | ⚪ C-11: Error boundary handling - Mobile | Low | enhancement, low-priority, mobile, error-handling | Todo |
| 5 | ⚪ F-08: Offline mode support - Mobile | Low | enhancement, low-priority, mobile, offline | Todo |
| 6 | 📋 Testing Infrastructure - PHPUnit Setup | Priority 3 | testing, infrastructure, phpunit | Todo |
| 7 | 📋 Security Hardening - Rate Limiting & 2FA | Priority 3 | security, backend, rate-limiting, 2fa | Todo |
| 8 | 📋 Payment Gateway Integration - Midtrans/Xendit | Priority 3 | payment, backend, integration | Todo |

---

**Total Estimated Effort:** 85 hours (for issues 6-8)
**Project:** AntarkanMa (PVT_kwHOBBILe84BQTKS)
**Repository:** https://github.com/Aswar12/Antarkanma.my.id
