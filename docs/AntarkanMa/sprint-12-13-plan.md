# Sprint 12-13 Plan — Critical Fixes

> **Sprint:** 12-13  
> **Periode:** 27 Februari - 14 Maret 2026 (2 minggu)  
> **Goal:** Fix all critical gaps before testing phase  
> **Total Effort:** 23 jam (~3 hari kerja)

---

## 📋 Sprint Backlog

### C1: Missing Controllers (5 jam)

#### C1.1: Create ManualOrderController (2 jam)

**File:** `app/Http/Controllers/API/ManualOrderController.php`

**Status:** ⬜ Not Started  
**Priority:** 🔴 Critical  
**Effort:** 2 jam

**Requirements:**
```php
// Endpoint: POST /api/manual-order
// Purpose: Fitur Jastip (Jasa Titip) untuk merchant non-partner

Request Body:
{
    "merchant_name": "Nama Toko",
    "merchant_address": "Alamat toko",
    "items": [
        {
            "name": "Nama Barang",
            "quantity": 2,
            "price": 50000,
            "notes": "Catatan untuk item"
        }
    ],
    "user_location_id": 1,
    "delivery_address": "Alamat lengkap",
    "delivery_latitude": -5.123456,
    "delivery_longitude": 119.123456,
    "phone_number": "081234567890",
    "notes": "Catatan order"
}

Response:
{
    "success": true,
    "data": {
        "order_id": 123,
        "transaction_id": 456,
        "message": "Order manual berhasil dibuat"
    }
}
```

**Implementation Steps:**
1. Create controller file
2. Add validation rules
3. Create order & transaction records
4. Calculate shipping cost
5. Send FCM notification to relevant parties
6. Write unit test

---

#### C1.2: Create ChatController (3 jam)

**File:** `app/Http/Controllers/API/ChatController.php`

**Status:** ⬜ Not Started  
**Priority:** 🔴 Critical  
**Effort:** 3 jam

**Requirements:**
```php
// Endpoints:
// POST /api/chat/initiate
// GET  /api/chat/{chatId}/messages
// POST /api/chat/{chatId}/send

// Request: Initiate Chat
POST /api/chat/initiate
{
    "recipient_id": 123,
    "recipient_type": "MERCHANT|COURIER|USER",
    "order_id": 456, // optional, related order
    "message": "Halo, saya ingin tanya..."
}

// Response: Initiate
{
    "success": true,
    "data": {
        "chat_id": "uuid-here",
        "recipient": {...},
        "created_at": "2026-02-27T10:00:00Z"
    }
}

// Request: Get Messages
GET /api/chat/{chatId}/messages

// Response: Get Messages
{
    "success": true,
    "data": {
        "messages": [
            {
                "id": 1,
                "sender_id": 123,
                "message": "Halo",
                "created_at": "2026-02-27T10:00:00Z"
            }
        ],
        "pagination": {...}
    }
}

// Request: Send Message
POST /api/chat/{chatId}/send
{
    "message": "Pesan baru",
    "attachment": null // optional file
}

// Response: Send Message
{
    "success": true,
    "data": {
        "message_id": 789,
        "created_at": "2026-02-27T10:05:00Z"
    }
}
```

**Implementation Steps:**
1. Create controller file
2. Add validation rules
3. Implement initiate method (create chat room)
4. Implement getMessages method (with pagination)
5. Implement sendMessage method
6. Add FCM notification trigger
7. Write unit tests

**Database Tables Needed:**
```sql
-- Check if tables exist
- chats (id, user_id, recipient_id, recipient_type, order_id, status, created_at, updated_at)
- chat_messages (id, chat_id, sender_id, message, attachment_url, read_at, created_at)

-- If not exist, create migration
```

---

### C3: Error Response Standardization (8 jam)

**Status:** ⬜ Not Started  
**Priority:** 🟡 Medium  
**Effort:** 8 jam

**Deliverables:**
1. Helper trait for error responses
2. Audit & update all 17 controllers
3. Update API documentation

**Implementation Steps:**

#### Step 1: Create Helper Trait (2 jam)

**File:** `app/Traits/ApiResponseTrait.php`

```php
<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation successful',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Error response
     */
    protected function errorResponse(
        string $message,
        string $errorCode = 'GENERAL_ERROR',
        int $statusCode = 400,
        array $details = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message,
                'details' => $details
            ]
        ], $statusCode);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => $message,
                'details' => $errors
            ]
        ], 422);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(
        string $resource = 'Resource'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => "{$resource} not found"
            ]
        ], 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => $message
            ]
        ], 401);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(
        string $message = 'Forbidden'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => $message
            ]
        ], 403);
    }
}
```

#### Step 2: Audit & Update Controllers (6 jam)

**Controllers to Update (17 files):**

| # | Controller | Status | Time |
|---|------------|--------|------|
| 1 | UserController.php | ⬜ | 30 min |
| 2 | MerchantController.php | ⬜ | 30 min |
| 3 | ProductController.php | ⬜ | 30 min |
| 4 | ProductCategoryController.php | ⬜ | 20 min |
| 5 | ProductGalleryController.php | ⬜ | 20 min |
| 6 | OrderController.php | ⬜ | 40 min |
| 7 | OrderStatusController.php | ⬜ | 30 min |
| 8 | OrderItemController.php | ⬜ | 20 min |
| 9 | TransactionController.php | ⬜ | 40 min |
| 10 | CourierController.php | ⬜ | 30 min |
| 11 | DeliveryController.php | ⬜ | 30 min |
| 12 | ShippingController.php | ⬜ | 20 min |
| 13 | UserLocationController.php | ⬜ | 20 min |
| 14 | FcmController.php | ⬜ | 20 min |
| 15 | NotificationController.php | ⬜ | 15 min |
| 16 | ProductReviewController.php | ⬜ | 20 min |
| 17 | ManualOrderController.php | ⬜ | 20 min |
| 18 | ChatController.php | ⬜ | 20 min |

**Checklist per Controller:**
- [ ] Import `ApiResponseTrait`
- [ ] Replace `response()->json([...])` with `successResponse()` / `errorResponse()`
- [ ] Ensure consistent error codes
- [ ] Test all endpoints

---

### C5: Environment Configuration (2 jam)

**File:** `.env.example`

**Status:** ⬜ Not Started  
**Priority:** 🟢 Low  
**Effort:** 2 jam

**Action Items:**

1. Add comments to all variables
2. Provide example values for all empty variables
3. Group related variables
4. Add warnings for critical variables

**Template:**
```env
# =============================================================================
# APPLICATION SETTINGS
# =============================================================================
APP_NAME=Antarkanma
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# =============================================================================
# DATABASE CONFIGURATION
# =============================================================================
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=antarkanma
DB_USERNAME=antarkanma
DB_PASSWORD=Antarkanma123

# MySQL root password (for Docker)
DB_ROOT_PASSWORD=Antarkanma123

# =============================================================================
# REDIS CONFIGURATION
# =============================================================================
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

# =============================================================================
# AWS S3 COMPATIBLE STORAGE (IDCloudHost IS3)
# =============================================================================
# Get your credentials from IDCloudHost console:
# https://console.idcloudhost.com/object-storage
AWS_ACCESS_KEY_ID=your_access_key_id_here
AWS_SECRET_ACCESS_KEY=your_secret_access_key_here
AWS_DEFAULT_REGION=id-jkt-1
AWS_BUCKET=antarkanma
AWS_URL=https://is3.cloudhost.id
AWS_ENDPOINT=https://is3.cloudhost.id
AWS_USE_PATH_STYLE_ENDPOINT=true

# =============================================================================
# FIREBASE CONFIGURATION
# =============================================================================
# Firebase project settings
FIREBASE_PROJECT=app
FIREBASE_PROJECT_ID=antarkanma-98fde

# Path to service account credentials JSON file
# Download from: Firebase Console > Project Settings > Service Accounts
FIREBASE_CREDENTIALS=/app/storage/app/firebase/firebase-credentials.json

# Firebase Realtime Database URL
FIREBASE_DATABASE_URL=https://antarkanma-98fde.firebaseio.com

# Default storage bucket
FIREBASE_STORAGE_DEFAULT_BUCKET=antarkanma-98fde.appspot.com

# FCM Server Key (for legacy HTTP API)
# Get from: Firebase Console > Project Settings > Cloud Messaging
FIREBASE_SERVER_KEY=your_server_key_here

# Messaging Sender ID
FIREBASE_MESSAGING_SENDER_ID=your_sender_id_here

# API Key (for client-side apps)
FIREBASE_API_KEY=your_api_key_here

# =============================================================================
# MAIL CONFIGURATION
# =============================================================================
MAIL_MAILER=log
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@antarkanma.id
MAIL_FROM_NAME="${APP_NAME}"

# =============================================================================
# QUEUE & CACHE CONFIGURATION
# =============================================================================
QUEUE_CONNECTION=sync
CACHE_DRIVER=file
SESSION_DRIVER=database

# =============================================================================
# BROADCASTING
# =============================================================================
BROADCAST_DRIVER=log

# =============================================================================
# FILESYSTEM
# =============================================================================
FILESYSTEM_DISK=local

# =============================================================================
# LOGGING
# =============================================================================
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

---

### D1: Update Sequence Diagram (2 jam)

**File:** `docs/AntarkanMa/architecture/sequence-diagram.md`

**Status:** ⬜ Not Started  
**Priority:** 🟢 Low  
**Effort:** 2 jam

**Updates Needed:**

1. Add courier status flow:
   - IDLE
   - HEADING_TO_MERCHANT
   - AT_MERCHANT
   - HEADING_TO_CUSTOMER
   - AT_CUSTOMER
   - DELIVERED

2. Add new endpoints:
   - `POST /courier/transactions/{id}/arrive-merchant`
   - `POST /courier/transactions/{id}/arrive-customer`
   - `POST /courier/orders/{id}/pickup`
   - `POST /courier/orders/{id}/complete`

3. Sync with Session 11 implementation

---

### D2: Update API Reference (2 jam)

**File:** `docs/AntarkanMa/api/api-reference.md`

**Status:** ⬜ Not Started  
**Priority:** 🟢 Low  
**Effort:** 2 jam

**New Endpoints to Document:**

```markdown
## Courier Tracking Endpoints

### POST /courier/transactions/{id}/arrive-merchant
**Description:** Courier arrives at merchant location  
**Request:**
```json
{
    "latitude": -5.123456,
    "longitude": 119.123456
}
```
**Response:**
```json
{
    "success": true,
    "message": "Courier arrived at merchant",
    "data": {
        "transaction_id": 123,
        "courier_status": "AT_MERCHANT"
    }
}
```

### POST /courier/transactions/{id}/arrive-customer
**Description:** Courier arrives at customer location  
**Request:**
```json
{
    "latitude": -5.654321,
    "longitude": 119.654321
}
```
**Response:**
```json
{
    "success": true,
    "message": "Courier arrived at customer",
    "data": {
        "transaction_id": 123,
        "courier_status": "AT_CUSTOMER"
    }
}
```

### POST /courier/orders/{id}/pickup
**Description:** Courier picks up order from merchant  
**Request:**
```json
{
    "latitude": -5.123456,
    "longitude": 119.123456
}
```
**Response:**
```json
{
    "success": true,
    "message": "Order picked up successfully",
    "data": {
        "order_id": 456,
        "order_status": "PICKED_UP"
    }
}
```

### POST /courier/orders/{id}/complete
**Description:** Courier completes delivery  
**Request:**
```json
{
    "latitude": -5.654321,
    "longitude": 119.654321,
    "proof": "base64_image_or_null"
}
```
**Response:**
```json
{
    "success": true,
    "message": "Delivery completed successfully",
    "data": {
        "order_id": 456,
        "order_status": "COMPLETED",
        "transaction_status": "COMPLETED"
    }
}
```
```

---

### D4: Create Deployment Checklist (4 jam)

**File:** `docs/AntarkanMa/deployment-checklist.md`

**Status:** ⬜ Not Started  
**Priority:** 🔴 High  
**Effort:** 4 jam

**Structure:**

```markdown
# Deployment Checklist — Antarkanma

## Pre-Deployment Checklist

### Code Quality
- [ ] All tests passing (`php artisan test`)
- [ ] No critical bugs in backlog
- [ ] Code reviewed and approved
- [ ] No debugging code (dd, dump, console.log)
- [ ] Error logging configured

### Database
- [ ] All migrations run successfully
- [ ] Database backup created
- [ ] Seeders tested (if needed)
- [ ] Database indexes optimized

### Environment
- [ ] `.env` file configured correctly
- [ ] All required environment variables set
- [ ] API keys rotated (if needed)
- [ ] Debug mode disabled in production

### Security
- [ ] SSL certificate installed
- [ ] HTTPS enforced
- [ ] CORS configured correctly
- [ ] Rate limiting enabled
- [ ] SQL injection protection verified
- [ ] XSS protection verified

### Infrastructure
- [ ] Server resources adequate
- [ ] Load balancer configured
- [ ] Database replication working
- [ ] Redis connection working
- [ ] Queue worker running
- [ ] Monitoring tools configured

### Mobile Apps
- [ ] Release APKs built
- [ ] APKs tested on production devices
- [ ] API endpoints configured (production URL)
- [ ] Firebase configured for production
- [ ] App version incremented

## Deployment Steps

### 1. Pre-Deployment
```bash
# Create database backup
mysqldump -u root -p antarkanma > backup_$(date +%Y%m%d_%H%M%S).sql

# Upload backup to S3
aws s3 cp backup_*.sql s3://antarkanma-backups/

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

### 2. Migration
```bash
# Run migrations
php artisan migrate --force

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Services Restart
```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Restart queue workers
php artisan queue:restart

# Restart Octane (if used)
php artisan octane:reload
```

## Post-Deployment Checklist

### Health Checks
- [ ] Health endpoint returns healthy (`GET /api/health`)
- [ ] Database connection working
- [ ] Redis connection working
- [ ] S3 storage accessible
- [ ] Firebase connection working

### Smoke Tests
- [ ] User can login
- [ ] User can browse products
- [ ] User can create order
- [ ] Merchant can receive order notification
- [ ] Courier can see available transactions
- [ ] FCM notifications working

### Monitoring
- [ ] Application monitoring active
- [ ] Error tracking configured
- [ ] Log aggregation working
- [ ] Alerts configured
- [ ] Dashboard showing green

### Documentation
- [ ] Deployment log updated
- [ ] Version tag created
- [ ] Changelog updated
- [ ] Team notified

## Rollback Plan

### If Deployment Fails
1. Stop all services
2. Restore database from backup
3. Checkout previous version
4. Run migrations rollback
5. Restart services
6. Verify health checks

### Rollback Commands
```bash
# Rollback last migration
php artisan migrate:rollback --step=1

# Or rollback to specific migration
php artisan migrate:rollback --pretend

# Checkout previous version
git checkout <previous-tag>

# Restart services
sudo systemctl restart php8.4-fpm
php artisan octane:reload
```

## Emergency Contacts

| Role | Name | Contact |
|------|------|---------|
| DevOps | Aswar | +62-XXX-XXXX-XXXX |
| Backend | [Name] | +62-XXX-XXXX-XXXX |
| Mobile | [Name] | +62-XXX-XXXX-XXXX |
```

---

## 📊 Sprint Board

### To Do
```
[ ] C1.1: Create ManualOrderController
[ ] C1.2: Create ChatController
[ ] C3: Error Response Standardization
[ ] C5: Update .env.example
[ ] D1: Update Sequence Diagram
[ ] D2: Update API Reference
[ ] D4: Create Deployment Checklist
```

### In Progress
```
[ ] (Add tasks as you start working on them)
```

### Done
```
[ ] (Add tasks as you complete them)
```

---

## 📈 Progress Tracking

### Daily Standup Template

```
Date: ___________

Yesterday:
- 

Today:
- 

Blockers:
- 
```

### Sprint Burndown

| Day | Planned Hours | Completed Hours | Remaining Hours |
|-----|---------------|-----------------|-----------------|
| Day 1 | 3 | | 20 |
| Day 2 | 3 | | 17 |
| Day 3 | 3 | | 14 |
| Day 4 | 3 | | 11 |
| Day 5 | 3 | | 8 |
| Day 6 | 3 | | 5 |
| Day 7 | 3 | | 2 |
| Day 8 | 2 | | 0 |

---

## 🎯 Definition of Done

A task is considered done when:
- [ ] Code is implemented and tested
- [ ] Code is committed to git
- [ ] Documentation is updated
- [ ] No related bugs created

---

## 📝 Notes

- Update `active-backlog.md` after each task completion
- Log work in `progress-log.md` daily
- Ask for help if blocked for more than 2 hours

---

**Sprint Start:** 27 Februari 2026  
**Sprint End:** 14 Maret 2026  
**Sprint Owner:** Aswar
