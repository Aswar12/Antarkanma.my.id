# 🚀 AntarkanMa

> **Multi-merchant delivery platform** — Connecting customers, merchants, and couriers in Pangkep, Sulawesi Selatan

**Status:** 99% MVP Complete ✅ | **Target Soft Launch:** Mei 2026

---

## 🎯 Quick Start

### For Developers
```bash
# Clone & setup
git clone https://github.com/Aswar12/Antarkanma.my.id.git
cd Antarkanma
composer install
cp .env.example .env
php artisan migrate --seed
composer dev
```

### 🤖 For AI Agents (NEW!)
**Starting a new session?**
1. 📚 Read [`docs/QUICKSTART.md`](docs/QUICKSTART.md) — Session startup guide
2. 📋 Check [`MASTERPLAN.md`](MASTERPLAN.md) — Current priorities
3. 🧪 Load [`docs/AntarkanMa/ai-memory-context.md`](docs/AntarkanMa/ai-memory-context.md) — Session context
4. 🎯 Pick task from priorities & start coding!

**Detailed guide:** [Starting a New AI Session](#-starting-a-new-ai-session) (scroll down)

### Documentation
📖 **Complete docs:** [`docs/AntarkanMa/README.md`](docs/AntarkanMa/README.md)

---

## 📊 Project Status

| Component | Status | Notes |
|-----------|--------|-------|
| Backend (Laravel 11) | ✅ 100% | All endpoints complete |
| Merchant App (Flutter) | ✅ 100% | Production ready |
| Courier App (Flutter) | ✅ 100% | Production ready |
| Customer App (Flutter) | ✅ 100% | Production ready |
| Documentation | ✅ 95% | 40+ docs files |
| Testing | ⚠️ 5% | Needs work |

---

## 🏗️ Tech Stack

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Framework** | Laravel 11 (PHP 8.4) | Backend API |
| **Admin Panel** | Filament 3.2 | Admin dashboard |
| **Auth** | Laravel Sanctum + Jetstream | Authentication |
| **Push Notification** | Firebase Cloud Messaging | Real-time notifications |
| **Storage** | AWS S3 Compatible (IDCloudHost) | File uploads |
| **Cache & Queue** | Redis (Predis) | Caching & job queues |
| **Server** | Laravel Octane | High-performance server |
| **Database** | MySQL 8 | Primary database |
| **Mobile** | Flutter 3 | Customer, Merchant, Courier apps |
| **Deployment** | Docker + Nginx + Cloudflare | Production deployment |

---

## 📱 Mobile Apps

### 3 Flutter Apps (Multi-platform)

| App | Users | Features |
|-----|-------|----------|
| **Customer App** | End users | Browse, order, track, chat, review |
| **Merchant App** | Store owners | Order management, product CRUD, analytics |
| **Courier App** | Delivery drivers | Order pickup, delivery, wallet, earnings |

**Location:** `mobile/` folder
- `mobile/customer/` — Customer app
- `mobile/merchant/` — Merchant app
- `mobile/courier/` — Courier app

---

## ✨ Fitur Utama

### Core Features
- ✅ **Multi-Merchant**: Satu pesanan dari berbagai merchant
- ✅ **3 Role Pengguna**: Customer, Merchant, Courier + Admin
- ✅ **Product Management**: CRUD, variants, galleries, categories
- ✅ **Order System**: Multi-item, multi-merchant dengan status tracking
- ✅ **Payment**: COD (Cash on Delivery) dengan tracking
- ✅ **Delivery Management**: Batch delivery, courier assignment, tracking
- ✅ **Real-time Chat**: Customer ↔ Merchant ↔ Courier
- ✅ **Media Sharing**: Image upload, GPS location sharing
- ✅ **Push Notifications**: Firebase Cloud Messaging
- ✅ **Review System**: Rating & reviews untuk product, merchant, courier
- ✅ **Wallet System**: Courier wallet, topup, withdrawal
- ✅ **Analytics**: Dashboard untuk merchant & courier
- ✅ **Admin Panel**: Complete Filament admin dashboard

### Recent Features (Maret 2026)
- ✅ Chat pagination (infinite scroll)
- ✅ Share location with GPS accuracy
- ✅ Chat image upload (multipart)
- ✅ Kitchen ticket print
- ✅ Merchant review system
- ✅ Courier review system
- ✅ Wallet topup with admin approval
- ✅ QRIS payment integration
- ✅ Analytics dashboard (7 widgets)

---

## 📂 Project Structure

```
Antarkanma/
├── 📱 Mobile Apps
│   ├── customer/          # Customer Flutter app
│   ├── merchant/          # Merchant Flutter app
│   └── courier/           # Courier Flutter app
│
├── 🔧 Backend (Laravel)
│   ├── app/
│   │   ├── Http/Controllers/API/   # 21 API controllers
│   │   ├── Models/                 # 21 Eloquent models
│   │   ├── Filament/               # Admin panel resources
│   │   ├── Services/               # Business logic
│   │   └── Observers/              # Model observers
│   ├── database/
│   │   ├── migrations/             # 40+ migrations
│   │   ├── seeders/                # 32+ seeders
│   │   └── factories/              # Model factories
│   ├── routes/
│   │   └── api.php                 # 130+ API endpoints
│   └── config/                     # Laravel config
│
├── 📚 Documentation
│   ├── docs/
│   │   ├── QUICKSTART.md          # ⭐ AI session startup
│   │   ├── TEST_DATA.md           # Test accounts & scenarios
│   │   ├── ARCHIVE.md             # Completed tasks history
│   │   ├── DOCUMENTATION-MAP.md   # Navigation guide
│   │   └── AntarkanMa/            # Complete docs (40+ files)
│   │       ├── README.md          # Documentation hub
│   │       ├── api/               # API documentation
│   │       ├── architecture/      # System diagrams
│   │       ├── business/          # Use cases & stories
│   │       ├── company/           # Company info
│   │       ├── deployment/        # Deployment guides
│   │       └── features/          # Feature specs
│   └── MASTERPLAN.md              # Current priorities
│
└── 🐳 Deployment
    ├── docker/                    # Docker configs
    ├── docker-compose.yml         # Local development
    ├── docker-compose.laptop.yml  # Laptop deployment
    └── docker-compose.vps.yml     # VPS production
```

---

## 🚀 Installation

### Prerequisites
- PHP >= 8.4
- Composer
- MySQL 8
- Redis
- Node.js 18+
- Flutter 3+ (for mobile apps)

### Quick Setup
```bash
# Clone repository
git clone https://github.com/Aswar12/Antarkanma.my.id.git
cd Antarkanma

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations & seeders
php artisan migrate --seed

# Storage link
php artisan storage:link

# Start development server
composer dev
```

### Mobile Apps Setup
```bash
# Customer app
cd mobile/customer
flutter pub get
flutter run

# Merchant app
cd mobile/merchant
flutter pub get
flutter run

# Courier app
cd mobile/courier
flutter pub get
flutter run
```

---

## 🧪 Testing

### Test Accounts
| Role | Email | Password |
|------|-------|----------|
| Customer | aswarthedoctor@gmail.com | antarkanma123 |
| Merchant | koneksi@rasa.com | antarkanma123 |
| Courier | kurir@antarkanma.com | antarkanma123 |
| Admin | antarkanma@gmail.com | antarkanma123 |

📚 **Complete test data:** [`docs/TEST_DATA.md`](docs/TEST_DATA.md)

### Run Tests
```bash
# Backend tests
php artisan test

# Specific test
php artisan test --filter=ChatTest

# Flutter tests
cd mobile/customer && flutter test
```

---

## 📖 Documentation

### Quick Navigation

| Category | Files | Description |
|----------|-------|-------------|
| **🚀 Startup** | [`docs/QUICKSTART.md`](docs/QUICKSTART.md) | AI session startup guide |
| **📊 Priorities** | [`MASTERPLAN.md`](MASTERPLAN.md) | Current priorities & status |
| **🧪 Testing** | [`docs/TEST_DATA.md`](docs/TEST_DATA.md) | Test accounts & scenarios |
| **📚 Complete Docs** | [`docs/AntarkanMa/README.md`](docs/AntarkanMa/README.md) | Documentation hub (40+ files) |
| **📦 History** | [`docs/ARCHIVE.md`](docs/ARCHIVE.md) | Completed tasks history |

### Documentation Categories

| Category | Path | Files |
|----------|------|-------|
| API Documentation | `docs/AntarkanMa/api/` | 5 files |
| Architecture | `docs/AntarkanMa/architecture/` | 7 files |
| Business Layer | `docs/AntarkanMa/business/` | 3 files |
| Company Info | `docs/AntarkanMa/company/` | 4 files |
| Deployment | `docs/AntarkanMa/deployment/` | 3 files |
| Features | `docs/AntarkanMa/features/` | 8 files |
| Guides & References | `docs/AntarkanMa/` | 4 files |
| AI & MCP | `docs/AntarkanMa/` | 5 files |

**Total:** 40+ documentation files

---

## 🐳 Deployment

### Environments

| Environment | Method | Config File |
|-------------|--------|-------------|
| **Development** | Laragon / `php artisan serve` | `.env` |
| **Laptop (Docker)** | Docker Compose | `docker-compose.laptop.yml` |
| **VPS (Production)** | Docker + Nginx + Cloudflare | `docker-compose.vps.yml` |

### Deployment Commands
```bash
# Development
composer dev

# Docker (Laptop)
docker-compose -f docker-compose.laptop.yml up -d

# Docker (VPS Production)
docker-compose -f docker-compose.vps.yml up -d
```

📚 **Complete guide:** [`docs/deployment/deployment-guide.md`](docs/deployment/deployment-guide.md)

---

## 🤝 Contributing

Terima kasih untuk kontribusi Anda! Silakan baca [`CONTRIBUTING.md`](CONTRIBUTING.md) untuk panduan lengkap.

### Quick Start for Contributors
1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

---

## 🤖 Starting a New AI Session

**Untuk AI Agent Baru:** Ikuti langkah ini untuk memulai sesi kerja.

### Step 1: Read Documentation
```bash
# 1. Baca panduan utama
cat docs/QUICKSTART.md

# 2. Cek prioritas
cat MASTERPLAN.md

# 3. Load context
cat docs/AntarkanMa/ai-memory-context.md

# 4. Test accounts
cat docs/TEST_DATA.md
```

### Step 2: Check Current Priorities

**Prioritas Minggu Ini (8-14 Maret 2026):**

| ID | Task | Priority | Status |
|----|------|----------|--------|
| **T-03** | Chat bug fixes | 🔴 High | 🔄 In Progress |
| **C-10** | Image compression | 🟡 Medium | ⏳ Pending |
| **F-07** | E2E testing | 🟡 Medium | ⏳ Pending |

📚 **Lengkap:** [`MASTERPLAN.md`](MASTERPLAN.md)

### Step 3: Choose Task & Code

Pilih task dari prioritas, lalu mulai coding.

### Step 4: Update Documentation

Setelah selesai coding:
```bash
# 1. Update status di MASTERPLAN.md
# 2. Pindahkan completed task ke docs/ARCHIVE.md
# 3. Update progress log di docs/AntarkanMa/progress-log.md
# 4. Update context di docs/AntarkanMa/ai-memory-context.md
```

### Step 5: Commit

```bash
git add .
git commit -m "✅ T-03: Chat bug fixes complete
📝 Update MASTERPLAN.md"
git push
```

---

### Quick Commands

```bash
# Environment setup
composer dev
adb reverse tcp:8000 tcp:8000

# Run tests
php artisan test
flutter test

# Check status
git status
git log -n 3
```

---

### Need Help?

| Resource | Link |
|----------|------|
| **Session Startup** | [`docs/QUICKSTART.md`](docs/QUICKSTART.md) |
| **Priorities** | [`MASTERPLAN.md`](MASTERPLAN.md) |
| **Test Data** | [`docs/TEST_DATA.md`](docs/TEST_DATA.md) |
| **Documentation** | [`docs/AntarkanMa/README.md`](docs/AntarkanMa/README.md) |
| **Workflow** | [`.agents/workflows/update-masterplan.md`](.agents/workflows/update-masterplan.md) |

---

## 📄 License

Project ini dilisensikan di bawah [MIT License](LICENSE).

---

## 📞 Contact & Support

**Project:** AntarkanMa  
**Location:** Kecamatan Segeri, Ma'rang, Mandalle — Kabupaten Pangkep, Sulawesi Selatan  
**Status:** MVP Ready (99% Complete)  
**Target Launch:** Mei 2026

**Links:**
- 📚 Documentation: [`docs/AntarkanMa/README.md`](docs/AntarkanMa/README.md)
- 📊 Priorities: [`MASTERPLAN.md`](MASTERPLAN.md)
- 🧪 Test Data: [`docs/TEST_DATA.md`](docs/TEST_DATA.md)
- 🐛 Issues: GitHub Issues
- 📦 Archive: [`docs/ARCHIVE.md`](docs/ARCHIVE.md)

---

**Made with ❤️ for AntarkanMa Team**