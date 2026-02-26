# Panduan Kontribusi untuk Antarkanma

Terima kasih atas minat Anda untuk berkontribusi pada proyek Antarkanma! Berikut adalah panduan untuk membantu Anda mulai.

## Proses Kontribusi

1. Fork repository ini
2. Buat branch baru: `feature/nama-fitur` atau `fix/nama-perbaikan`
3. Lakukan perubahan di branch tersebut
4. Pastikan kode mengikuti standar dan gaya penulisan yang ada
5. Jalankan tes dan pastikan semua lulus
6. Commit dengan pesan yang jelas dan deskriptif
7. Push branch Anda ke fork di GitHub
8. Buat pull request ke branch `main` di repository utama

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.4)
- **Admin Panel**: Filament 3.2
- **Database**: MySQL 8
- **Cache**: Redis
- **Auth**: Laravel Sanctum

## Setup Development Environment

```bash
# Clone & install
git clone <your-fork-url>
cd Antarkanma
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Jalankan migrasi
php artisan migrate --seed

# Jalankan server
composer dev
```

## Standar Kode

- Ikuti standar **PSR-12** untuk PHP
- Gunakan indentasi **4 spasi** untuk semua file PHP
- Ikuti konvensi penamaan Laravel:
  - Controller: `PascalCase` + suffix `Controller` (contoh: `OrderController`)
  - Model: `PascalCase` singular (contoh: `Product`, `OrderItem`)
  - Migration: `snake_case` dengan prefix timestamp
  - Route: `kebab-case` untuk URL, `camelCase` untuk nama route
- Gunakan **type hints** dan **return types** pada method
- Tambahkan komentar untuk logika bisnis yang kompleks
- Gunakan **Form Request** untuk validasi input

## Menjalankan Tests

```bash
# Jalankan semua tests
php artisan test

# Jalankan test spesifik
php artisan test --filter=NamaTest

# Dengan coverage
php artisan test --coverage
```

## Pelaporan Bug

Buat issue baru dengan label "bug" dan sertakan:
- Deskripsi singkat tentang bug
- Langkah-langkah untuk mereproduksi
- Perilaku yang diharapkan vs aktual
- Tangkapan layar (jika relevan)
- Informasi environment (versi PHP, OS, dll.)

## Saran Fitur Baru

Buat issue dengan label "enhancement" dan jelaskan:
- Deskripsi fitur yang diusulkan
- Alasan mengapa fitur ini bermanfaat
- Mockup atau diagram (jika memungkinkan)

## Proses Review

- Setiap pull request di-review oleh minimal satu anggota tim inti
- Kami mungkin meminta perubahan atau klarifikasi sebelum merge
- Pastikan untuk merespons komentar review dengan cepat

## Lisensi

Dengan berkontribusi, Anda setuju bahwa kontribusi Anda akan dilisensikan di bawah [MIT License](LICENSE).
