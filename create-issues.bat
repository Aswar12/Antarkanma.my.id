@echo off
echo ========================================
echo GitHub Issues Creator for AntarkanMa
echo ========================================
echo.
echo This script will create 8 GitHub issues for the AntarkanMa project.
echo.
echo Prerequisites:
echo 1. GitHub CLI (gh) must be installed
echo 2. You must be authenticated with GitHub
echo.
echo To install GitHub CLI: https://cli.github.com/
echo To authenticate: gh auth login
echo.
echo ========================================
echo.

where gh >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] GitHub CLI (gh) is not installed!
    echo.
    echo Please install GitHub CLI from: https://cli.github.com/
    echo Or use the PowerShell script: scripts\create-github-issues.ps1
    echo.
    pause
    exit /b 1
)

echo Checking GitHub authentication...
gh auth status >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Not authenticated with GitHub!
    echo.
    echo Please run: gh auth login
    echo.
    pause
    exit /b 1
)

echo GitHub CLI is ready!
echo.
echo Creating issues...
echo.

REM Issue 1
echo [1/8] Creating T-03: Chat message bug fixes...
gh issue create --title "🔴 T-03: Chat message bug fixes - All Apps" --body "## Description
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
High Priority - Critical for user experience" --label "bug,high-priority,chat,mobile" --repo Aswar12/Antarkanma.my.id

REM Issue 2
echo [2/8] Creating C-10: Image upload compression...
gh issue create --title "🟡 C-10: Image upload compression - Backend" --body "## Description
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
Medium Priority - Optimization improvement" --label "enhancement,medium-priority,backend,optimization" --repo Aswar12/Antarkanma.my.id

REM Issue 3
echo [3/8] Creating F-07: Final E2E testing...
gh issue create --title "🟡 F-07: Final E2E testing - All Apps" --body "## Description
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
Medium Priority - Quality assurance" --label "testing,medium-priority,e2e" --repo Aswar12/Antarkanma.my.id

REM Issue 4
echo [4/8] Creating C-11: Error boundary handling...
gh issue create --title "⚪ C-11: Error boundary handling - Mobile" --body "## Description
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
Low Priority - UX improvement" --label "enhancement,low-priority,mobile,error-handling" --repo Aswar12/Antarkanma.my.id

REM Issue 5
echo [5/8] Creating F-08: Offline mode support...
gh issue create --title "⚪ F-08: Offline mode support - Mobile" --body "## Description
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
Low Priority - Feature enhancement" --label "enhancement,low-priority,mobile,offline" --repo Aswar12/Antarkanma.my.id

REM Issue 6
echo [6/8] Creating Testing Infrastructure - PHPUnit Setup...
gh issue create --title "📋 Testing Infrastructure - PHPUnit Setup" --body "## Description
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
Priority 3 - Infrastructure" --label "testing,infrastructure,phpunit" --repo Aswar12/Antarkanma.my.id

REM Issue 7
echo [7/8] Creating Security Hardening - Rate Limiting ^& 2FA...
gh issue create --title "📋 Security Hardening - Rate Limiting & 2FA" --body "## Description
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
Priority 3 - Security" --label "security,backend,rate-limiting,2fa" --repo Aswar12/Antarkanma.my.id

REM Issue 8
echo [8/8] Creating Payment Gateway Integration...
gh issue create --title "📋 Payment Gateway Integration - Midtrans/Xendit" --body "## Description
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
Priority 3 - Feature" --label "payment,backend,integration" --repo Aswar12/Antarkanma.my.id

echo.
echo ========================================
echo All issues created successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Add all issues to AntarkanMa project (PVT_kwHOBBILe84BQTKS)
echo 2. Set appropriate Status (Todo/In Progress/Done)
echo 3. Assign issues to team members
echo.
echo View issues at: https://github.com/Aswar12/Antarkanma.my.id/issues
echo.
pause
