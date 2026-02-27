# DFD Level 1 — Antarkanma

> **Versi**: v2.0 — 24 Februari 2026  
> DFD Level 1 = Pemecahan proses utama ke sub-proses.  
> Tiga proses utama: (1) Order Management, (2) Courier Flow, (3) Notification System.

---

## Proses 1: Order Management

```mermaid
flowchart TD
    CUSTOMER(["👤 Customer"])
    MERCHANT(["🏪 Merchant"])
    DB_TRX[("🗄️ Transactions DB")]
    DB_ORDER[("🗄️ Orders DB")]
    OSRM(["🗺️ OSRM"])
    NOTIF["🔔 Notification\nProcess"]

    P1_1["1.1\nBrowse &\nCheckout"]
    P1_2["1.2\nHitung\nOngkir"]
    P1_3["1.3\nBuat\nTransaction\n& Orders"]
    P1_4["1.4\nMerchant\nApprove"]
    P1_5["1.5\nMerchant\nSiapkan &\nReady"]

    CUSTOMER -->|"produk dipilih, alamat"| P1_1
    P1_1 -->|"data order + koordinat"| P1_2
    P1_2 -->|"minta jarak rute"| OSRM
    OSRM -->|"jarak + durasi"| P1_2
    P1_2 -->|"total ongkir"| P1_3
    P1_3 -->|"simpan transaction"| DB_TRX
    P1_3 -->|"simpan orders per merchant"| DB_ORDER
    P1_3 -->|"kirim notif pesanan baru"| NOTIF
    NOTIF -.->|"push ke merchant"| MERCHANT

    MERCHANT -->|"approve/reject order"| P1_4
    P1_4 -->|"update order_status"| DB_ORDER
    P1_4 -->|"kirim notif"| NOTIF

    MERCHANT -->|"tandai siap diambil"| P1_5
    P1_5 -->|"status → READY_FOR_PICKUP"| DB_ORDER
    P1_5 -->|"kirim notif ke kurir"| NOTIF
```

---

## Proses 2: Courier Flow

```mermaid
flowchart TD
    COURIER(["🛵 Courier"])
    CUSTOMER2(["👤 Customer"])
    MERCHANT2(["🏪 Merchant"])
    DB_TRX2[("🗄️ Transactions DB")]
    DB_ORDER2[("🗄️ Orders DB")]
    NOTIF2["🔔 Notification\nProcess"]

    P2_1["2.1\nLihat Pesanan\nTersedia"]
    P2_2["2.2\nTerima Pesanan\n(approve)"]
    P2_3["2.3\nSampai di\nMerchant"]
    P2_4["2.4\nPickup\nPer-Order"]
    P2_5["2.5\nSampai di\nCustomer"]
    P2_6["2.6\nSelesaikan\nPer-Order"]

    COURIER -->|"minta list pesanan READY"| P2_1
    P2_1 -->|"query READY_FOR_PICKUP"| DB_ORDER2
    DB_ORDER2 -->|"list pesanan + jarak"| P2_1
    P2_1 -->|"tampil ke kurir"| COURIER

    COURIER -->|"tap Terima"| P2_2
    P2_2 -->|"set courier_id + courier_status=HEADING_TO_MERCHANT"| DB_TRX2
    P2_2 -->|"⚠️ Order status TIDAK berubah (tetap READY)"| DB_ORDER2
    P2_2 -->|"notif ke merchant & customer"| NOTIF2

    COURIER -->|"tap Sampai di Merchant"| P2_3
    P2_3 -->|"courier_status=AT_MERCHANT"| DB_TRX2
    P2_3 -->|"notif ke merchant & customer"| NOTIF2

    COURIER -->|"tap Ambil (per order)"| P2_4
    P2_4 -->|"order_status=PICKED_UP"| DB_ORDER2
    P2_4 -->|"jika semua PICKED_UP: courier_status=HEADING_TO_CUSTOMER"| DB_TRX2
    P2_4 -->|"notif ke merchant & customer"| NOTIF2

    COURIER -->|"tap Sampai di Customer"| P2_5
    P2_5 -->|"courier_status=AT_CUSTOMER"| DB_TRX2
    P2_5 -->|"notif ke customer"| NOTIF2

    COURIER -->|"tap Selesai (per order)"| P2_6
    P2_6 -->|"order_status=COMPLETED"| DB_ORDER2
    P2_6 -->|"jika semua done: Transaction=COMPLETED, DELIVERED"| DB_TRX2
    P2_6 -->|"notif ke merchant & customer"| NOTIF2

    NOTIF2 -.->|"push"| CUSTOMER2
    NOTIF2 -.->|"push"| MERCHANT2
```

---

## Proses 3: Notification System

```mermaid
flowchart LR
    EVENT["⚡ Event\n(status berubah)"]
    P3_1["3.1\nDetermine\nRecipient"]
    P3_2["3.2\nBuild\nPayload"]
    P3_3["3.3\nFetch\nFCM Token"]
    DB_FCM[("🗄️ FCM Tokens DB")]
    FIREBASE(["🔔 Firebase\nFCM Server"])
    DEVICE(["📱 User Device"])

    EVENT -->|"type + transaction_id"| P3_1
    P3_1 -->|"user_id target"| P3_3
    P3_3 -->|"query active tokens"| DB_FCM
    DB_FCM -->|"tokens list"| P3_3
    P3_3 -->|"tokens"| P3_2
    P3_2 -->|"payload FCM"| FIREBASE
    FIREBASE -->|"push notification"| DEVICE
```

### Tipe Notifikasi & Penerima

```mermaid
graph LR
    subgraph "Event & Penerima"
        E1["new_order"] -->|"→"| M["🏪 Merchant"]
        E2["order_approved\norder_rejected"] -->|"→"| C["👤 Customer"]
        E3["order_ready\n(READY_FOR_PICKUP)"] -->|"broadcast →"| K["🛵 Courier"]
        E4["courier_heading_to_merchant"] -->|"→"| M
        E4 -->|"→"| C
        E5["courier_arrived_at_merchant"] -->|"→"| M
        E5 -->|"→"| C
        E6["order_picked_up"] -->|"→"| M
        E6 -->|"→"| C
        E7["courier_arrived_at_customer"] -->|"→"| C
        E8["order_completed"] -->|"→"| C
        E8 -->|"→"| M
    end
```

---

## Data Stores (Tabel Database Utama)

| Store | Tabel | Fungsi |
|---|---|---|
| D1 | `transactions` | Status transaksi + courier tracking |
| D2 | `orders` | Status per order per merchant |
| D3 | `order_items` | Detail produk dalam pesanan |
| D4 | `fcm_tokens` | Token FCM per device per user |
| D5 | `users` | Data semua aktor |
| D6 | `merchants` | Profil + koordinat merchant |
| D7 | `couriers` | Profil + kondisi kurir |
| D8 | `user_locations` | Alamat pengiriman customer |

---

*Terakhir diperbarui: 24 Februari 2026*