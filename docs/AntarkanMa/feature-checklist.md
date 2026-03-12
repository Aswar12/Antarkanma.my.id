# 📋 AntarkanMa — Feature Checklist

> Tandai status setiap fitur:
> - `[ ]` Belum dikerjakan / tidak ada
> - `[?]` Ada tapi perlu dicek / belum pasti
> - `[!]` Ada tapi bermasalah / perlu diperbaiki
> - `[x]` Sudah aman / berfungsi baik

---

## 🔐 1. Authentication & User Management

### Backend (Laravel)
- [ ] Register User (Customer)
- [ ] Register Merchant
- [ ] Login (Sanctum Token)
- [ ] Logout
- [ ] Refresh Token
- [ ] Fetch Profile (`GET /user/profile`)
- [ ] Update Profile (`PUT /user/profile`)
- [ ] Update Profile Photo (`POST /user/profile/photo`)
- [ ] Change Password
- [ ] Delete Account
- [ ] Toggle Active Status

### Customer App (Flutter)
- [] Login Screen
- [ ] Register Screen
- [ ] Profile Screen
- [ ] Edit Profile
- [ ] Change Password

### Merchant App (Flutter)
- [ ] Login Screen
- [ ] Register Screen
- [ ] Profile Screen
- [ ] Edit Profile

### Courier App (Flutter)
- [ ] Login Screen
- [ ] Profile Screen

---

## 🏪 2. Merchant Management

### Backend
- [ ] Create Merchant (`POST /merchant`)
- [ ] Update Merchant (`PUT /merchant/{id}`)
- [ ] Update Status (Buka/Tutup) (`PUT /merchant/{id}/status`)
- [ ] Extend Operating Hours (`PUT /merchant/{id}/extend`)
- [ ] Update Product Availability (`PUT /merchant/{id}/products/availability`)
- [ ] Upload Logo (`POST /merchant/{id}/logo`)
- [ ] Upload QRIS (`POST /merchant/{id}/qris`)
- [ ] Delete Merchant
- [ ] List Merchants (Public) — with distance calc
- [ ] Merchant Detail (Public)
- [ ] Get By Owner ID
- [ ] Merchant List (Auth)

### Customer App
- [ ] Browse Merchants
- [ ] Merchant Detail Page
- [ ] Merchant Products Grid

### Merchant App
- [ ] Store Profile Page
- [ ] Edit Store Info
- [ ] Operating Hours Setting
- [ ] Logo Upload
- [ ] QRIS Upload

### Admin Panel (Filament)
- [ ] Merchant Resource (CRUD)
- [ ] Merchant Locations Map Widget

---

## 📦 3. Product Management

### Backend
- [ ] Create Product (`POST /products`)
- [ ] Update Product (`PUT /products/{id}`)
- [ ] Delete Product (`DELETE /products/{id}`)
- [ ] List Products (Public)
- [ ] Products by Category
- [ ] Products by Merchant
- [ ] Search Products
- [ ] Popular Products
- [ ] Top Products by Category
- [ ] Product with Reviews

### Product Gallery
- [ ] Add Gallery Image (`POST /products/{id}/gallery`)
- [ ] Edit Gallery Image
- [ ] Delete Gallery Image

### Product Variants
- [ ] Add Variant (`POST /products/{id}/variants`)
- [ ] Update Variant
- [ ] Delete Variant
- [ ] List Variants by Product
- [ ] Get Single Variant

### Customer App
- [ ] Product Listing
- [ ] Product Detail
- [ ] Product Search
- [ ] Product Reviews Display

### Merchant App
- [ ] Product Management (CRUD)
- [ ] Product Gallery Management
- [ ] Product Variant Management
- [ ] Stock/Availability Toggle

### Admin Panel (Filament)
- [ ] Product Resource (CRUD)
- [ ] Product Category Resource
- [ ] Product Gallery Resource
- [ ] Product Review Resource

---

## 🛒 4. Order & Transaction System

### Backend — Orders
- [ ] Create Order (`POST /orders`)
- [ ] List Orders (`GET /orders`)
- [ ] Get Order Detail (`GET /orders/{id}`)
- [ ] Merchant Orders (`GET /merchants/{id}/orders`)
- [ ] Order Summary (`GET /merchants/{id}/order-summary`)
- [ ] Order Statistics

### Backend — Order Status Flow
- [ ] Process Order (Merchant approve)
- [ ] Ready for Pickup
- [ ] Complete Order
- [ ] Cancel Order
- [ ] Approve Order (Merchant)
- [ ] Reject Order (Merchant)
- [ ] Mark as Ready

### Backend — Order Items
- [ ] List Order Items
- [ ] Create Order Item
- [ ] Get Order Item
- [ ] Update Order Item
- [ ] Delete Order Item

### Backend — Transactions
- [ ] Create Transaction (`POST /transactions`)
- [ ] Get Transaction (`GET /transactions/{id}`)
- [ ] List Transactions
- [ ] Update Transaction
- [ ] Cancel Transaction
- [ ] Transaction Summary by Merchant
- [ ] Transactions by Merchant
- [ ] Auto-cancel Timed-out Transactions (Scheduler)

### Kitchen Ticket
- [ ] Print Kitchen Ticket (`GET /orders/{id}/print-kitchen-ticket`)

### Customer App
- [ ] Cart / Checkout Flow
- [ ] Order History
- [ ] Order Detail
- [ ] Order Status Tracking

### Merchant App
- [ ] Incoming Orders Queue
- [ ] Approve/Reject Order
- [ ] Order Management

### Admin Panel (Filament)
- [ ] Order Resource
- [ ] Order Item Resource
- [ ] Transaction Resource
- [ ] Transaction Item Resource

---

## 🔖 5. POS (Point of Sale) — Merchant

### Backend
- [ ] Get POS Products
- [ ] Create POS Transaction
- [ ] List POS Transactions (with filters)
- [ ] Get POS Transaction Detail
- [ ] Void POS Transaction
- [ ] Update Transaction Status
- [ ] Daily Summary

### Table Management (Basic)
- [ ] Get Tables (`GET /pos/tables`)
- [ ] Create Table (`POST /pos/tables`)
- [ ] Update Table (`PUT /pos/tables/{id}`)
- [ ] Delete Table (`DELETE /pos/tables/{id}`)

### Table Management (Advanced)
- [ ] Mark Food Completed (`POST /pos/transactions/{id}/food-completed`)
- [ ] Release Table Manual (`POST /pos/tables/{id}/release`)
- [ ] Extend Duration (`POST /pos/transactions/{id}/extend`)
- [ ] Tables Ready to Release (`GET /pos/tables/ready-to-release`)
- [ ] Get Merchant Config (`GET /pos/merchant-config`)
- [ ] Update Merchant Config (`PUT /pos/merchant-config`)
- [ ] Auto-Release Scheduler Command (`tables:auto-release`)
- [ ] PAY_FIRST / PAY_LAST Configuration

### Queue Management
- [ ] Get Active Queue (`GET /pos/queue`)

### Merchant App (Flutter)
- [ ] POS Cashier Page (Kasir)
- [ ] POS Product Grid (Produk)
- [ ] POS Table Page (Meja) — grid visual
- [ ] POS Queue Page (Antrian) — nomor antrian
- [ ] POS History Page (Riwayat)
- [ ] POS Finance Page (Keuangan)
- [ ] Table Timer Countdown (Advanced)
- [ ] Table Release Button (Advanced)
- [ ] Table Extend Duration Button (Advanced)
- [ ] Table Settings Page (PAY_FIRST/PAY_LAST config)

---

## 🚚 6. Delivery & Courier System

### Backend — Courier
- [ ] Courier Profile (`GET /courier/profile`)
- [ ] New Transactions for Courier
- [ ] My Transactions (Courier)
- [ ] Approve Transaction (Courier)
- [ ] Reject Transaction (Courier)
- [ ] Arrive at Merchant
- [ ] Arrive at Customer
- [ ] Pickup Order (per-order)
- [ ] Complete Order (per-order)
- [ ] Daily Statistics
- [ ] Status Counts

### Backend — Delivery
- [ ] Assign Courier (`POST /deliveries/assign-courier`)
- [ ] Update Delivery Status
- [ ] Update Pickup Status
- [ ] Get Courier Deliveries

### Backend — Shipping
- [ ] Calculate Shipping Cost (`POST /shipping/calculate`)
- [ ] Preview Shipping Cost (`POST /shipping/preview`)
- [ ] OSRM Service Integration (Route calculation)

### Courier App (Flutter)
- [ ] Dashboard / Home
- [ ] New Orders List
- [ ] Active Delivery View
- [ ] Delivery Tracking
- [ ] Earnings Module
- [ ] Wallet Module
- [ ] Chat Module

### Admin Panel (Filament)
- [ ] Courier Resource
- [ ] Courier Review Resource
- [ ] Delivery Resource

---

## 💰 7. Finance & Wallet

### Backend — Merchant Finance
- [ ] Finance Overview (`GET /merchant/finance/overview`)
- [ ] Income Breakdown (`GET /merchant/finance/income`)
- [ ] Expenses List (`GET /merchant/finance/expenses`)
- [ ] Create Expense
- [ ] Update Expense
- [ ] Delete Expense

### Backend — Courier Wallet
- [ ] Get Wallet Balance
- [ ] Withdraw

### Backend — Wallet Topup
- [ ] Submit Topup (`POST /courier/wallet/topups`)
- [ ] Topup History
- [ ] Topup Detail

### Backend — QRIS
- [ ] Get QRIS Code
- [ ] Download QRIS Code

### Merchant App
- [ ] POS Finance Page

### Courier App
- [ ] Wallet Balance View
- [ ] Topup Flow
- [ ] Withdraw Flow
- [ ] Earnings Overview

### Admin Panel (Filament)
- [ ] Wallet Topup Resource (Approve/Reject)

---

## ⭐ 8. Reviews & Ratings

### Backend
- [ ] Product Reviews (`GET /products/{id}/reviews`)
- [ ] Submit Product Review
- [ ] Update Product Review
- [ ] Delete Product Review
- [ ] User Reviews List
- [ ] Transaction Review (Combined — Merchant + Courier)
- [ ] Review Status Check
- [ ] Merchant Reviews List
- [ ] Courier Reviews List

### Customer App
- [ ] Submit Review (after order)
- [ ] View Product Reviews

### Admin Panel (Filament)
- [ ] Product Review Resource
- [ ] Merchant Review Resource
- [ ] Courier Review Resource

---

## 💬 9. Chat System

### Backend
- [ ] Get Chat List (`GET /chats`)
- [ ] Initiate Chat (`POST /chat/initiate`)
- [ ] Get Chat Detail
- [ ] Get Messages
- [ ] Send Message (Text)
- [ ] Share Location
- [ ] Mark as Read
- [ ] Close Chat
- [ ] Delete Chat
- [ ] Delete Message
- [ ] Rate Limiting (60/min)

### Customer App
- [ ] Chat List Screen
- [ ] Chat Detail Screen
- [ ] Send Text Message
- [ ] Share Location
- [ ] Image Sharing

### Merchant App
- [ ] Chat List Screen
- [ ] Chat Detail Screen
- [ ] Send Text Message

### Courier App
- [ ] Chat Module

---

## 🔔 10. Notifications (FCM)

### Backend
- [ ] Store/Update FCM Token
- [ ] Remove FCM Token
- [ ] Subscribe Topic
- [ ] Notification Inbox (`GET /notifications`)
- [ ] Unread Count
- [ ] Mark as Read
- [ ] Mark All as Read
- [ ] Delete Notification
- [ ] Test Merchant Notification
- [ ] Firebase Service Integration

### Customer App
- [ ] Push Notification Handling
- [ ] Notification Inbox

### Merchant App
- [ ] Push Notification Handling
- [ ] Order Notification

### Courier App
- [ ] Push Notification Handling
- [ ] New Order Notification

---

## 📍 11. Location Management

### Backend
- [ ] List User Locations
- [ ] Create Location
- [ ] Get Location
- [ ] Update Location
- [ ] Delete Location
- [ ] Set Default Location

### Customer App
- [ ] Address Management
- [ ] Add/Edit Address
- [ ] Set Default Address

### Admin Panel (Filament)
- [ ] User Location Resource

---

## ❤️ 12. Wishlist

### Backend
- [ ] Get Wishlist (`GET /wishlist`)
- [ ] Toggle Wishlist (`POST /wishlist/toggle`)
- [ ] Check Wishlist Status

### Customer App
- [ ] Wishlist Page
- [ ] Add/Remove from Wishlist

---

## 📊 13. Analytics & Reporting

### Backend — Admin Analytics
- [ ] Overview (`GET /analytics/overview`)
- [ ] Sales
- [ ] Top Products
- [ ] Top Merchants
- [ ] Top Couriers
- [ ] Peak Hours
- [ ] Revenue Breakdown
- [ ] Customer Behavior

### Backend — Merchant Analytics
- [ ] Overview (`GET /merchant/analytics/overview`)
- [ ] Sales
- [ ] Top Products
- [ ] Peak Hours

### Backend — Courier Analytics
- [ ] Earnings (`GET /courier/analytics/earnings`)
- [ ] Performance

### Backend — Export
- [ ] Sales CSV
- [ ] Sales PDF
- [ ] Products CSV
- [ ] Merchants CSV
- [ ] Couriers CSV

### Admin Panel (Filament) — Widgets
- [ ] Stats Overview
- [ ] Sales Chart Widget
- [ ] Sales Stats Widget
- [ ] Orders Chart
- [ ] Order Status Pie Chart
- [ ] Latest Orders
- [ ] Popular Products
- [ ] Top Products Widget
- [ ] Top Merchants Widget
- [ ] Top Couriers Widget
- [ ] Peak Hours Widget
- [ ] Revenue Breakdown Widget

---

## 📝 14. Manual Order (Jastip)

### Backend
- [ ] Create Manual Order (`POST /manual-order`)

### Customer App
- [ ] Manual Order Form

---

## 🛡️ 15. Admin Panel (Filament)

### Resources (CRUD)
- [ ] User Resource
- [ ] Merchant Resource (with relation pages)
- [ ] Product Resource (with relation pages)
- [ ] Product Category Resource
- [ ] Product Gallery Resource
- [ ] Courier Resource
- [ ] Order Resource
- [ ] Order Item Resource
- [ ] Transaction Resource (with relation pages)
- [ ] Transaction Item Resource
- [ ] Delivery Resource
- [ ] User Location Resource
- [ ] Product Review Resource
- [ ] Merchant Review Resource
- [ ] Courier Review Resource
- [ ] Loyalty Point Resource
- [ ] Wallet Topup Resource

---

## 🔧 16. Infrastructure & DevOps

### Backend
- [ ] Health Check Endpoint (`GET /api/health`)
- [ ] Docker Setup (Dockerfile, docker-compose)
- [ ] S3 Object Storage Integration
- [ ] OSRM Routing Service
- [ ] Firebase Cloud Messaging
- [ ] Scheduled Tasks (Kernel.php)
  - [ ] `transactions:cancel-timed-out` (every minute)
  - [ ] `tables:auto-release` (every 5 minutes)
- [ ] Rate Limiting on Auth & Chat routes
- [ ] Database Migrations (67 migration files)
- [ ] Seeders

### Mobile Apps
- [ ] Customer App builds successfully
- [ ] Merchant App builds successfully
- [ ] Courier App builds successfully

---

## 📄 17. Dokumentasi

### Docs Folder
- [ ] Company Profile
- [ ] Growth Roadmap
- [ ] POS Dine-In Flow
- [ ] Business Model Canvas
- [ ] MASTERPLAN.md

---

_Last updated: 2026-03-11_
_Total feature groups: 17_
_Total individual features: ~200+_
