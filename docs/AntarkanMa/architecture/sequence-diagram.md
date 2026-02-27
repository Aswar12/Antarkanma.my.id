# Sequence Diagram — Antarkanma

> **Versi**: v2.0 — 24 Februari 2026  
> Sequence diagram untuk alur utama: Order flow lengkap dari Customer checkout hingga Delivered.

---

## Sequence Diagram: Full Order Flow (Happy Path)

```mermaid
sequenceDiagram
    autonumber
    actor Customer
    actor Merchant
    actor Courier
    participant CustomerApp as Customer App
    participant MerchantApp as Merchant App
    participant CourierApp as Courier App
    participant API as Laravel API
    participant DB as Database
    participant FCM as Firebase FCM

    %% ── PHASE 1: Customer Checkout ─────────────────────────────────────────
    rect rgb(230, 245, 255)
        Note over Customer, FCM: PHASE 1 — Customer Checkout
        Customer->>CustomerApp: Pilih produk & checkout
        CustomerApp->>API: POST /api/transactions
        API->>DB: CREATE Transaction (PENDING)
        API->>DB: CREATE Orders (WAITING_APPROVAL) per merchant
        API->>FCM: Push "Pesanan Baru" ke Merchant(s)
        FCM-->>MerchantApp: 🔔 "Pesanan Baru #X"
        API-->>CustomerApp: 200 { transaction_id }
        CustomerApp-->>Customer: Tampil "Pesanan dibuat, menunggu konfirmasi"
    end

    %% ── PHASE 2: Merchant Approve & Siapkan ────────────────────────────────
    rect rgb(230, 255, 230)
        Note over Customer, FCM: PHASE 2 — Merchant Approve & Siapkan
        Merchant->>MerchantApp: Buka notifikasi, lihat order
        Merchant->>MerchantApp: Tap "Approve"
        MerchantApp->>API: PUT /api/merchants/orders/{id}/approve
        API->>DB: UPDATE order_status = PROCESSING
        API->>FCM: Push "Pesanan disetujui" ke Customer
        FCM-->>CustomerApp: 🔔 "Pesananmu sedang disiapkan"
        API-->>MerchantApp: 200 OK

        Note over Merchant: [Mempersiapkan makanan...]

        Merchant->>MerchantApp: Tap "Tandai Siap Diambil"
        MerchantApp->>API: PUT /api/merchants/orders/{id}/ready
        API->>DB: UPDATE order_status = READY_FOR_PICKUP
        API->>FCM: Push "Pesanan siap" ke semua Courier terdekat
        FCM-->>CourierApp: 🔔 "Ada pesanan baru dekat kamu"
        API-->>MerchantApp: 200 OK
    end

    %% ── PHASE 3: Courier Ambil ──────────────────────────────────────────────
    rect rgb(255, 245, 220)
        Note over Customer, FCM: PHASE 3 — Courier Ambil Pesanan
        Courier->>CourierApp: Lihat daftar pesanan tersedia
        CourierApp->>API: GET /api/courier/new-transactions
        API->>DB: Query Transaction dgn READY_FOR_PICKUP, courier_id=null
        API-->>CourierApp: List transaksi + jarak
        CourierApp-->>Courier: Tampil daftar pesanan

        Courier->>CourierApp: Tap "Terima Pesanan"
        CourierApp->>API: POST /api/courier/transactions/{id}/approve
        API->>DB: courier_id = X
        API->>DB: courier_status = HEADING_TO_MERCHANT
        Note over API,DB: ⚠️ Order status TIDAK berubah (tetap READY_FOR_PICKUP)
        API->>FCM: Push ke Merchant + Customer
        FCM-->>MerchantApp: 🔔 "Kurir sedang menuju toko"
        FCM-->>CustomerApp: 🔔 "Kurir ditemukan"
        API-->>CourierApp: 200 OK
    end

    %% ── PHASE 4: Kurir ke Merchant ──────────────────────────────────────────
    rect rgb(255, 235, 220)
        Note over Customer, FCM: PHASE 4 — Kurir Tiba di Merchant
        Note over Courier: [Berkendara menuju merchant...]

        Courier->>CourierApp: Tap "Saya Sudah di Merchant"
        CourierApp->>API: POST /api/courier/transactions/{id}/arrive-merchant
        API->>DB: courier_status = AT_MERCHANT
        API->>FCM: Push ke Merchant + Customer
        FCM-->>MerchantApp: 🔔 "Kurir sudah tiba! Serahkan pesanan."
        FCM-->>CustomerApp: 🔔 "Kurir sedang ambil pesanan"
        API-->>CourierApp: 200 OK

        loop Per Order (bisa multi-merchant)
            Courier->>CourierApp: Tap "Ambil" pada Order tertentu
            CourierApp->>API: POST /api/courier/orders/{orderId}/pickup
            API->>DB: order_status = PICKED_UP
            API->>DB: [jika semua PICKED_UP] courier_status = HEADING_TO_CUSTOMER
            API->>FCM: Push ke Merchant + Customer
            FCM-->>MerchantApp: 🔔 "Pesanan diambil ✅"
            FCM-->>CustomerApp: 🔔 "Kurir dalam perjalanan 🚀"
            API-->>CourierApp: all_picked_up=true/false
        end
    end

    %% ── PHASE 5: Kurir ke Customer & Selesai ────────────────────────────────
    rect rgb(220, 255, 220)
        Note over Customer, FCM: PHASE 5 — Kurir ke Customer & Selesai
        Note over Courier: [Berkendara menuju alamat customer...]

        Courier->>CourierApp: Tap "Saya Sudah di Lokasi Customer"
        CourierApp->>API: POST /api/courier/transactions/{id}/arrive-customer
        API->>DB: courier_status = AT_CUSTOMER
        API->>FCM: Push ke Customer
        FCM-->>CustomerApp: 🔔 "Kurir sudah tiba! 🎉"
        API-->>CourierApp: 200 OK

        loop Per Order
            Courier->>CourierApp: Tap "Selesai" pada Order
            CourierApp->>API: POST /api/courier/orders/{orderId}/complete
            API->>DB: order_status = COMPLETED
            API->>DB: [jika SEMUA selesai] Transaction.status = COMPLETED
            API->>DB: [jika SEMUA selesai] courier_status = DELIVERED
            API->>FCM: Push ke Customer + Merchant
            FCM-->>CustomerApp: 🔔 "Pesanan selesai 🎉"
            FCM-->>MerchantApp: 🔔 "Pesanan berhasil diantarkan"
            API-->>CourierApp: transaction_completed=true/false
        end

        CourierApp-->>Courier: "Semua pesanan selesai! Kerja bagus!"
    end
```

---

## Sequence Diagram: Merchant Reject Flow

```mermaid
sequenceDiagram
    autonumber
    actor Customer
    actor Merchant
    participant CustomerApp as Customer App
    participant MerchantApp as Merchant App
    participant API as Laravel API
    participant DB as Database
    participant FCM as Firebase FCM

    Customer->>CustomerApp: Checkout
    CustomerApp->>API: POST /api/transactions
    API->>DB: CREATE Transaction + Orders (WAITING_APPROVAL)
    API->>FCM: Push ke Merchant
    FCM-->>MerchantApp: 🔔 "Pesanan Baru"

    Merchant->>MerchantApp: Tap "Tolak"
    MerchantApp->>API: PUT /api/merchants/orders/{id}/reject
    API->>DB: order_status = CANCELED
    API->>DB: Transaction.status = CANCELED (jika semua order cancel)
    API->>FCM: Push ke Customer
    FCM-->>CustomerApp: 🔔 "Pesananmu ditolak oleh merchant"
    API-->>MerchantApp: 200 OK
    CustomerApp-->>Customer: "Pesanan dibatalkan. Silakan coba merchant lain."
```

---

*Terakhir diperbarui: 24 Februari 2026*