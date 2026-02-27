# DFD Level 0 — Antarkanma

> **Versi**: v2.0 — 24 Februari 2026  
> DFD Level 0 = **Context Diagram** — menunjukkan sistem secara keseluruhan dan semua entitas eksternal yang berinteraksi dengannya.

---

## Context Diagram

```mermaid
flowchart LR
    CUSTOMER(["👤 Customer"])
    MERCHANT(["🏪 Merchant"])
    COURIER(["🛵 Courier"])
    SYSTEM[["⚙️ ANTARKANMA\nSYSTEM"]]
    FIREBASE(["🔔 Firebase\nFCM"])
    OSRM(["🗺️ OSRM\nRoute Engine"])
    PAYMENT(["💳 Payment\nGateway\n(Rencana)"])

    %% Customer flows
    CUSTOMER -->|"Browse, Checkout, Bayar"| SYSTEM
    SYSTEM -->|"Status pesanan, History, Notifikasi"| CUSTOMER

    %% Merchant flows
    MERCHANT -->|"Approve/Reject order, Update status masak"| SYSTEM
    SYSTEM -->|"Pesanan masuk, Statistik, Notifikasi"| MERCHANT

    %% Courier flows
    COURIER -->|"Ambil order, Update posisi, Selesaikan"| SYSTEM
    SYSTEM -->|"Daftar pesanan, Lokasi merchant, Notifikasi"| COURIER

    %% External services
    SYSTEM -->|"Kirim push notification"| FIREBASE
    FIREBASE -->|"Deliver ke device"| CUSTOMER
    FIREBASE -->|"Deliver ke device"| MERCHANT
    FIREBASE -->|"Deliver ke device"| COURIER

    SYSTEM -->|"Request kalkulasi rute/jarak"| OSRM
    OSRM -->|"Jarak, durasi, rute optimal"| SYSTEM

    SYSTEM <-->|"Proses pembayaran (Q3 2026)"| PAYMENT
```

---

## Penjelasan Entitas Eksternal

| Entitas | Tipe | Interaksi dengan Sistem |
|---|---|---|
| **Customer** | User | Membuat pesanan, membayar, melacak status |
| **Merchant** | User | Menerima & memproses pesanan |
| **Courier** | User | Mengambil & mengantarkan pesanan |
| **Firebase FCM** | External Service | Push notification real-time ke semua aktor |
| **OSRM** | External Service | Kalkulasi jarak & rute untuk ongkir |
| **Payment Gateway** | External Service | Proses pembayaran online (Midtrans/Xendit, Q3 2026) |

---

## Data Flows Utama

### Input ke Sistem
| Dari | Data | Keterangan |
|---|---|---|
| Customer | Pesanan, lokasi pengiriman, pembayaran | Via REST API |
| Merchant | Keputusan approve/reject, update status masak | Via REST API |
| Courier | Keputusan ambil pesanan, update posisi/status | Via REST API |
| OSRM | Jarak & durasi antar titik | Untuk kalkulasi ongkir |

### Output dari Sistem
| Ke | Data | Keterangan |
|---|---|---|
| Customer | Konfirmasi pesanan, status real-time, history | REST API + FCM |
| Merchant | Pesanan masuk, statistik harian | REST API + FCM |
| Courier | Daftar pesanan tersedia, pendapatan | REST API + FCM |
| Firebase | Payload notifikasi | FCM push |

---

*Terakhir diperbarui: 24 Februari 2026*