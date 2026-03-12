# GitHub Issues Creation Script for AntarkanMa
# Run this script with your GitHub token to create all issues automatically

param(
    [Parameter(Mandatory=$true)]
    [string]$GitHubToken,
    
    [string]$Repo = "Aswar12/Antarkanma.my.id",
    [string]$ProjectId = "PVT_kwHOBBILe84BQTKS"
)

$Headers = @{
    "Authorization" = "token $GitHubToken"
    "Accept" = "application/vnd.github.v3+json"
    "User-Agent" = "AntarkanMa-Issue-Creator"
}

$BaseUrl = "https://api.github.com/repos/$Repo"

# Function to create GitHub issue
function Create-Issue {
    param(
        [string]$Title,
        [string]$Body,
        [string[]]$Labels
    )
    
    $IssueData = @{
        title = $Title
        body = $Body
        labels = $Labels
    } | ConvertTo-Json -Depth 10
    
    Write-Host "Creating issue: $Title" -ForegroundColor Cyan
    
    $Response = Invoke-RestMethod -Uri "$BaseUrl/issues" -Method Post -Headers $Headers -Body $IssueData -ContentType "application/json"
    
    Write-Host "✓ Created: $($Response.html_url) (#$($Response.number))" -ForegroundColor Green
    
    return @{
        Number = $Response.number
        Url = $Response.html_url
        Title = $Response.title
    }
}

# Function to add issue to project
function Add-IssueToProject {
    param(
        [int]$IssueNumber,
        [string]$Status = "Todo"
    )
    
    # Note: Adding to project requires additional API calls
    # This is a simplified version - you may need to adjust based on project type
    Write-Host "  -> Add issue #$IssueNumber to project $ProjectId with status: $Status" -ForegroundColor Yellow
}

Write-Host "========================================" -ForegroundColor Magenta
Write-Host "GitHub Issues Creator for AntarkanMa" -ForegroundColor Magenta
Write-Host "========================================" -ForegroundColor Magenta
Write-Host ""

$CreatedIssues = @()

# Issue 1: T-03 Chat Message Bug Fixes
$Issue1 = Create-Issue -Title "🔴 T-03: Chat message bug fixes - All Apps" -Body @"
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
"@ -Labels @("bug", "high-priority", "chat", "mobile")
$CreatedIssues += $Issue1
Add-IssueToProject -IssueNumber $Issue1.Number -Status "In Progress"

# Issue 2: C-10 Image Upload Compression
$Issue2 = Create-Issue -Title "🟡 C-10: Image upload compression - Backend" -Body @"
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
"@ -Labels @("enhancement", "medium-priority", "backend", "optimization")
$CreatedIssues += $Issue2
Add-IssueToProject -IssueNumber $Issue2.Number -Status "Todo"

# Issue 3: F-07 Final E2E Testing
$Issue3 = Create-Issue -Title "🟡 F-07: Final E2E testing - All Apps" -Body @"
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
"@ -Labels @("testing", "medium-priority", "e2e")
$CreatedIssues += $Issue3
Add-IssueToProject -IssueNumber $Issue3.Number -Status "Todo"

# Issue 4: C-11 Error Boundary Handling
$Issue4 = Create-Issue -Title "⚪ C-11: Error boundary handling - Mobile" -Body @"
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
"@ -Labels @("enhancement", "low-priority", "mobile", "error-handling")
$CreatedIssues += $Issue4
Add-IssueToProject -IssueNumber $Issue4.Number -Status "Todo"

# Issue 5: F-08 Offline Mode Support
$Issue5 = Create-Issue -Title "⚪ F-08: Offline mode support - Mobile" -Body @"
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
"@ -Labels @("enhancement", "low-priority", "mobile", "offline")
$CreatedIssues += $Issue5
Add-IssueToProject -IssueNumber $Issue5.Number -Status "Todo"

# Issue 6: Testing Infrastructure - PHPUnit Setup
$Issue6 = Create-Issue -Title "📋 Testing Infrastructure - PHPUnit Setup" -Body @"
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
"@ -Labels @("testing", "infrastructure", "phpunit")
$CreatedIssues += $Issue6
Add-IssueToProject -IssueNumber $Issue6.Number -Status "Todo"

# Issue 7: Security Hardening - Rate Limiting & 2FA
$Issue7 = Create-Issue -Title "📋 Security Hardening - Rate Limiting & 2FA" -Body @"
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
"@ -Labels @("security", "backend", "rate-limiting", "2fa")
$CreatedIssues += $Issue7
Add-IssueToProject -IssueNumber $Issue7.Number -Status "Todo"

# Issue 8: Payment Gateway Integration
$Issue8 = Create-Issue -Title "📋 Payment Gateway Integration - Midtrans/Xendit" -Body @"
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
"@ -Labels @("payment", "backend", "integration")
$CreatedIssues += $Issue8
Add-IssueToProject -IssueNumber $Issue8.Number -Status "Todo"

# Summary Report
Write-Host ""
Write-Host "========================================" -ForegroundColor Magenta
Write-Host "SUMMARY REPORT" -ForegroundColor Magenta
Write-Host "========================================" -ForegroundColor Magenta
Write-Host ""

foreach ($issue in $CreatedIssues) {
    Write-Host "✓ #$($issue.Number): $($issue.Title)" -ForegroundColor Green
    Write-Host "  URL: $($issue.Url)" -ForegroundColor Gray
    Write-Host ""
}

Write-Host "Total issues created: $($CreatedIssues.Count)" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Add all issues to AntarkanMa project (PVT_kwHOBBILe84BQTKS)" -ForegroundColor Gray
Write-Host "2. Set appropriate Status (Todo/In Progress/Done)" -ForegroundColor Gray
Write-Host "3. Assign issues to team members" -ForegroundColor Gray
Write-Host ""
