# ERD Diagram — Antarkanma

> **Versi**: v2.0 — Diperbarui 24 Februari 2026  
> **Sumber kebenaran**: Migrations aktual di `database/migrations/`  
> Diagram ini mencerminkan schema **aktual** yang sedang berjalan, bukan desain lama.

---

## Entity Relationship Diagram (Mermaid)

```mermaid
erDiagram
    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum roles "USER|MERCHANT|COURIER|ADMIN"
        string username
        string phone_number
        string profile_photo_path
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    MERCHANTS {
        bigint id PK
        bigint owner_id FK
        string name
        string address
        decimal latitude
        decimal longitude
        string phone_number
        json operating_hours
        enum status "ACTIVE|INACTIVE|SUSPENDED"
        time open_time
        time close_time
        timestamp created_at
        timestamp updated_at
    }

    PRODUCTS {
        bigint id PK
        bigint merchant_id FK
        bigint category_id FK
        string name
        text description
        decimal price
        enum status "ACTIVE|INACTIVE"
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_CATEGORIES {
        bigint id PK
        string name
        timestamp deleted_at
    }

    PRODUCT_GALLERIES {
        bigint id PK
        bigint products_id FK
        string url
        timestamp deleted_at
    }

    COURIERS {
        bigint id PK
        bigint user_id FK
        string vehicle_type
        string license_plate
        boolean is_available
        decimal current_latitude
        decimal current_longitude
        decimal wallet_balance
        timestamp created_at
        timestamp updated_at
    }

    USER_LOCATIONS {
        bigint id PK
        bigint user_id FK
        string customer_name
        string address
        decimal longitude
        decimal latitude
        string address_type
        string phone_number
        boolean is_default
        timestamp created_at
        timestamp updated_at
    }

    FCM_TOKENS {
        bigint id PK
        bigint user_id FK
        string token
        boolean is_active
        string device_type
        timestamp created_at
        timestamp updated_at
    }

    TRANSACTIONS {
        bigint id PK
        bigint user_id FK
        bigint user_location_id FK
        bigint courier_id FK
        bigint base_merchant_id FK
        decimal total_price
        decimal shipping_price
        date payment_date
        enum status "PENDING|COMPLETED|CANCELED"
        enum payment_method "MANUAL|ONLINE"
        enum payment_status "PENDING|COMPLETED|FAILED"
        enum courier_approval "PENDING|APPROVED|REJECTED"
        enum courier_status "IDLE|HEADING_TO_MERCHANT|AT_MERCHANT|HEADING_TO_CUSTOMER|AT_CUSTOMER|DELIVERED"
        timestamp timeout_at
        decimal rating
        text note
        timestamp created_at
        timestamp updated_at
    }

    ORDERS {
        bigint id PK
        bigint transaction_id FK
        bigint user_id FK
        bigint merchant_id FK
        decimal total_amount
        enum order_status "PENDING|WAITING_APPROVAL|PROCESSING|READY_FOR_PICKUP|PICKED_UP|COMPLETED|CANCELED"
        enum merchant_approval "PENDING|APPROVED|REJECTED"
        string rejection_reason
        text customer_note
        timestamp created_at
        timestamp updated_at
    }

    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        bigint merchant_id FK
        int quantity
        decimal price
        text customer_note
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_REVIEWS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        int rating
        text comment
        timestamp created_at
        timestamp updated_at
    }

    USERS ||--o{ MERCHANTS : "memiliki (owner)"
    USERS ||--o{ USER_LOCATIONS : "memiliki"
    USERS ||--o{ FCM_TOKENS : "memiliki"
    USERS ||--o{ TRANSACTIONS : "membuat"
    USERS ||--o{ PRODUCT_REVIEWS : "memberi"
    USERS ||--o| COURIERS : "adalah"

    MERCHANTS ||--o{ PRODUCTS : "memiliki"
    MERCHANTS ||--o{ ORDERS : "menerima"

    PRODUCTS ||--o{ PRODUCT_GALLERIES : "memiliki foto"
    PRODUCTS }o--|| PRODUCT_CATEGORIES : "termasuk"
    PRODUCTS ||--o{ ORDER_ITEMS : "ada di"
    PRODUCTS ||--o{ PRODUCT_REVIEWS : "menerima"

    COURIERS ||--o{ TRANSACTIONS : "mengambil"

    USER_LOCATIONS ||--o{ TRANSACTIONS : "digunakan di"

    TRANSACTIONS ||--o{ ORDERS : "berisi"

    ORDERS ||--o{ ORDER_ITEMS : "berisi"
```

---

## Penjelasan Entitas Utama

### 🔑 Hierarki Pesanan

```
TRANSACTIONS  (1 per pembayaran)
    └── ORDERS  (1 per merchant)
            └── ORDER_ITEMS  (1 per produk)
```

**Satu Transaction bisa memiliki beberapa Order** (kalau customer pesan dari beberapa merchant sekaligus). Tapi **satu Order hanya untuk satu merchant** dan hanya belongTo satu Transaction.

---

### TRANSACTIONS — Field Penting

| Field | Tipe | Deskripsi |
|---|---|---|
| `status` | ENUM | Status transaksi keseluruhan: PENDING / COMPLETED / CANCELED |
| `courier_approval` | ENUM | Apakah kurir sudah menerima: PENDING / APPROVED / REJECTED |
| `courier_status` | ENUM | **BARU:** Posisi kurir secara real-time (tracking) |
| `courier_id` | FK | Null = belum ada kurir; sudah isi = kurir sudah ambil |
| `base_merchant_id` | FK | Merchant utama (terjauh / pertama) untuk hitung ongkir |
| `timeout_at` | timestamp | Deadline order — saat ini tidak auto-cancel |

### courier_status — State Machine

```
IDLE → HEADING_TO_MERCHANT → AT_MERCHANT → HEADING_TO_CUSTOMER → AT_CUSTOMER → DELIVERED
```

| Value | Kondisi |
|---|---|
| `IDLE` | Belum ada kurir, atau default |
| `HEADING_TO_MERCHANT` | Kurir sudah terima, sedang menuju merchant |
| `AT_MERCHANT` | Kurir sudah tiba di merchant |
| `HEADING_TO_CUSTOMER` | Semua order sudah diambil, menuju customer |
| `AT_CUSTOMER` | Kurir sudah tiba di lokasi customer |
| `DELIVERED` | Semua order selesai diantarkan |

---

### ORDERS — order_status State Machine

```
PENDING → WAITING_APPROVAL → PROCESSING → READY_FOR_PICKUP → PICKED_UP → COMPLETED
                    ↓                                                   
                CANCELED  (bisa dari mana saja selain COMPLETED)
```

| Status | Siapa yang mengubah | Kondisi |
|---|---|---|
| `PENDING` | System | Saat customer checkout |
| `WAITING_APPROVAL` | System | Langsung setelah PENDING (auto) |
| `PROCESSING` | Merchant | Setelah merchant approve |
| `READY_FOR_PICKUP` | Merchant | Setelah makanan selesai disiapkan |
| `PICKED_UP` | Courier | Setelah kurir ambil dari merchant |
| `COMPLETED` | Courier | Setelah berhasil diantarkan ke customer |
| `CANCELED` | Merchant / System | Jika ditolak atau gagal |

---

## Tabel Migrasi Aktual

| Tabel | File Migration |
|---|---|
| `users` | `0001_01_01_000000_create_users_table.php` |
| `merchants` | `2024_10_20_101729_create_merchants_table.php` |
| `products` | `2024_10_20_102058_create_products_table.php` |
| `orders` (lama, bukan parent) | `2024_10_20_103008_create_orders_table.php` |
| `transactions` | `2024_10_20_113547_create_transactions_table.php` |
| `couriers` | `2024_10_20_122829_create_couriers_table.php` |
| `user_locations` | `2024_10_20_132008_create_user_locations_table.php` |
| `fcm_tokens` | `2024_02_14_000000_create_fcm_tokens_table.php` |
| `courier_approval` + `timeout_at` | `2025_01_23_150506_add_courier_approval_...php` |
| **`courier_status`** | **`2026_02_24_add_courier_status_to_transactions.php`** |

---

*Terakhir diperbarui: 24 Februari 2026 — Sinkron dengan implementasi aktual*