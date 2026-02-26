# Antarkanma

Antarkanma adalah aplikasi layanan pesan antar makanan dan barang multi-merchant. Sistem ini menghubungkan pelanggan, pemilik usaha (merchant), dan kurir di Kecamatan Segeri, Ma'rang, dan Mandalle, Kabupaten Pangkep, Sulawesi Selatan.

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| **Framework** | Laravel 11 (PHP 8.4) |
| **Admin Panel** | Filament 3.2 |
| **Authentication** | Laravel Sanctum + Jetstream |
| **Push Notification** | Firebase Cloud Messaging (kreait/laravel-firebase) |
| **Object Storage** | AWS S3 Compatible (IDCloudHost IS3) |
| **Cache & Queue** | Redis (Predis) |
| **Server** | Laravel Octane |
| **Database** | MySQL 8 |
| **Deployment** | Docker + Nginx Load Balancer + Cloudflare Tunnel |

## Fitur Utama

- **Multi-Merchant**: Satu pesanan bisa mencakup produk dari berbagai merchant
- **3 Role Pengguna**: Pelanggan (USER), Pemilik Usaha (MERCHANT), Kurir (COURIER), Admin (ADMIN)
- **Manajemen Produk**: CRUD produk, galeri gambar, varian produk, kategori
- **Sistem Pemesanan**: Order multi-item, multi-merchant dengan status tracking
- **Transaksi & Pembayaran**: Pembayaran manual (COD) dengan tracking status
- **Pengiriman**: Manajemen kurir, batch delivery, tracking pickup per item
- **Notifikasi Push**: Real-time notification via Firebase Cloud Messaging
- **Sistem Review**: Rating & ulasan produk oleh pelanggan
- **Lokasi Pengguna**: Multi-alamat dengan default location
- **Admin Panel**: Dashboard admin lengkap via Filament
- **Kurir Wallet**: Sistem deposit/topup dan statistik harian kurir

## Prerequisites

- PHP >= 8.4
- Composer
- MySQL 8
- Redis
- Node.js (untuk Vite asset building)

## Instalasi

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

# Konfigurasi .env (sesuaikan DB, Redis, Firebase, S3)

# Jalankan migrasi & seeder
php artisan migrate --seed

# Storage link
php artisan storage:link
```

## Menjalankan Development Server

```bash
# Jalankan semua services sekaligus (server, queue, logs, vite)
composer dev

# Atau jalankan manual
php artisan serve
php artisan queue:listen
npm run dev
```

## Konfigurasi Environment

Lihat `.env.example` untuk semua konfigurasi yang diperlukan:

- **Database**: MySQL connection (`DB_*`)
- **Redis**: Cache & session (`REDIS_*`)
- **S3 Storage**: Upload gambar (`AWS_*`)
- **Firebase**: Push notifications (`FIREBASE_*`)

## Struktur Project

```
├── app/
│   ├── Http/Controllers/API/   # 17 API Controllers
│   ├── Models/                  # 17 Eloquent Models
│   ├── Filament/                # Admin Panel (Filament)
│   ├── Services/                # Business Logic Services
│   └── Observers/               # Model Observers
├── database/
│   ├── migrations/              # 40 Migration Files
│   ├── seeders/                 # 32 Seeder Files
│   └── factories/               # Model Factories
├── routes/
│   └── api.php                  # Semua API Routes
├── docs/                        # Dokumentasi Lengkap
│   ├── api/                     # API Documentation
│   ├── architecture/            # Diagram & Database Schema
│   ├── business/                # Use Cases & User Stories
│   ├── deployment/              # Panduan Deployment
│   ├── features/                # Dokumentasi Fitur
│   └── images/                  # Gambar & Diagram
├── docker/                      # Docker Configuration
└── config/                      # Laravel Configuration
```

## Dokumentasi

| Dokumen | Deskripsi |
|---------|-----------|
| [API Reference](docs/api/api-reference.md) | Daftar lengkap API endpoints |
| [User API](docs/api/user-api.md) | Dokumentasi API untuk user/pelanggan |
| [Merchant API](docs/api/merchant-api.md) | Dokumentasi API untuk merchant |
| [Courier API](docs/api/courier-api.md) | Dokumentasi API untuk kurir |
| [Database Schema](docs/architecture/database-schema.md) | Skema database & relasi |
| [ERD Diagram](docs/architecture/erd-diagram.md) | Entity Relationship Diagram |
| [Class Diagram](docs/architecture/class-diagram.md) | Class Diagram sistem |
| [Use Cases](docs/business/use-cases.md) | Use case aplikasi |
| [Deployment Guide](docs/deployment/deployment-guide.md) | Panduan deployment |
| [Load Balancer](docs/deployment/load-balancer.md) | Setup load balancer |
| [Project Planning](docs/project-planning.md) | Perencanaan proyek |
| [Technical Specs](docs/technical-specifications.md) | Spesifikasi teknis |

## Deployment

Project ini mendukung deployment via Docker dengan konfigurasi multi-environment:

- **Development**: Laragon / `php artisan serve`
- **Laptop (Docker)**: `docker-compose.laptop.yml`
- **VPS (Production)**: `docker-compose.vps.yml` + Nginx Load Balancer

Lihat [Deployment Guide](docs/deployment/deployment-guide.md) untuk detail lengkap.

## Contributing

Lihat [CONTRIBUTING.md](CONTRIBUTING.md) untuk panduan kontribusi.

## License

Project ini dilisensikan di bawah [MIT License](LICENSE).