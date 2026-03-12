# Alur Kerja Fitur POS — Dine-In, Takeaway & Antrian

> **Dokumen ini menjelaskan bagaimana *flow* fitur POS Merchant AntarkanMa bekerja secara end-to-end, dari pemesanan hingga penyelesaian.**

---

## 1. Overview Tipe Pesanan POS

Merchant menggunakan Aplikasi Merchant sebagai **Mesin Kasir (POS)** untuk 3 tipe pesanan:

| Tipe | Keterangan | Membutuhkan Meja? |
|------|-----------|:-----------------:|
| `DINE_IN` | Pelanggan makan di tempat | ✅ Ya |
| `TAKEAWAY` | Pelanggan pesan bawa pulang | ❌ Tidak |
| `DELIVERY` | Pesanan diantar via kurir AntarkanMa | ❌ Tidak |

---

## 2. Flow Transaksi — DINE_IN (Makan di Tempat)

```
┌─────────────────────────────────────────────────────┐
│                PELANGGAN DATANG                     │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│  1. Kasir buka Tab "Meja" → Pilih meja KOSONG      │
│     (Grid visual: Hijau=Kosong, Merah=Terisi)       │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│  2. Kasir buka Tab "Kasir" → Pilih produk           │
│     → Pilih tipe "Dine-In" + Nomor Meja             │
│     → Atur jumlah & catatan per item                │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│  3. Kasir tekan "Bayar" → Pilih metode pembayaran   │
│     (Tunai / QRIS / Transfer)                       │
│     → Input nominal bayar → Hitung kembalian        │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│  4. SISTEM OTOMATIS:                                │
│     a. Buat PosTransaction (status: PENDING)        │
│     b. Set meja → status OCCUPIED                   │
│     c. Nomor antrian otomatis (urut hari ini)       │
│     d. Kirim notifikasi ke dapur (Fase lanjut)      │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│  5. Tab "Antrian" → Dapur/Kasir lihat pesanan aktif │
│     → Gesek atau tekan "Proses" → status PROCESSING │
│     → Setelah masak, tekan "Siap" → COMPLETED       │
└──────────────────────┬──────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────┐
│  6. SISTEM OTOMATIS:                                │
│     Saat COMPLETED → Meja dikembalikan ke AVAILABLE │
└─────────────────────────────────────────────────────┘
```

---

## 3. Flow Transaksi — TAKEAWAY (Bawa Pulang)

```
Pelanggan pesan → Kasir pilih produk → Tipe "Takeaway"
  → Bayar → Nomor antrian diberikan
  → Dapur masak → Siap → Pelanggan ambil → COMPLETED
```

*(Sama seperti Dine-In, tapi tanpa alokasi meja)*

---

## 4. Manajemen Meja (Tab "Meja")

### 4.1 Setup Awal
Merchant mengatur daftar meja melalui pengaturan toko:
- Nomor meja (misal: 1, 2, 3, ... atau A1, A2, B1 dst)
- Kapasitas orang per meja
- Status default: `AVAILABLE`

### 4.2 Status Meja

| Status | Warna | Keterangan |
|--------|:-----:|-----------|
| `AVAILABLE` | 🟢 Hijau | Meja kosong, siap digunakan |
| `OCCUPIED` | 🔴 Merah | Meja sedang digunakan (ada transaksi aktif) |
| `RESERVED` | 🟡 Kuning | Meja dipesan (opsional, fase lanjut) |

### 4.3 Transisi Status Otomatis

```
AVAILABLE ──(transaksi DINE_IN dibuat)──→ OCCUPIED
OCCUPIED  ──(transaksi COMPLETED/VOIDED)──→ AVAILABLE
```

---

## 5. Sistem Antrian (Tab "Antrian")

### 5.1 Cara Kerja Nomor Antrian
- Nomor antrian di-reset setiap hari (mulai dari 1).
- Berlaku untuk SEMUA tipe pesanan POS (Dine-In, Takeaway, Delivery).
- Format: `#01`, `#02`, `#03`, dst.

### 5.2 Tampilan Antrian
Layar antrian menampilkan kartu pesanan aktif:
- **Nomor antrian besar** (mudah dibaca dari jauh)
- **Tipe pesanan** (Dine-In + Nomor Meja, Takeaway, Delivery)
- **Daftar item** yang dipesan
- **Waktu tunggu** (berubah jadi merah jika > 15 menit)
- **Tombol aksi**: Proses → Siap → Selesai

### 5.3 Lifecycle Pesanan dalam Antrian

```
PENDING ──(Kasir/Dapur terima)──→ PROCESSING ──(Masakan siap)──→ COMPLETED
                                                                    │
                                               (Batal) ← VOIDED ←──┘
```

---

## 6. Cetak Struk / Nota

Setelah transaksi berhasil, kasir dapat memilih:
- **Cetak struk pelanggan** (rincian total + kembalian)
- **Cetak nota dapur** (hanya daftar item + catatan)
- **Share via WhatsApp** (kirim struk digital ke nomor pelanggan)

---

## 7. Laporan Keuangan (Tab "Keuangan")

Rekapitulasi otomatis per hari:
- Total transaksi hari ini
- Total pendapatan (Tunai vs QRIS vs Transfer)
- Breakdown per tipe (Dine-In / Takeaway / Delivery)
- Jumlah transaksi dibatalkan (VOIDED)

---

*Dokumen ini terakhir diperbarui: 11 Maret 2026*
