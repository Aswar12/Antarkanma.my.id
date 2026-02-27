# Model Bisnis Antarkanma

## Sumber Pendapatan

### 1. Iuran Harian Kurir (Hanya Selama Pakai WA)
| Item | Detail |
|------|--------|
| **Mekanisme** | Setiap kurir menyetor Rp 7.000/hari ke Antarkanma |
| **Status** | ✅ Aktif selama masih pakai WA |
| **Rencana** | ❌ **Dihapus setelah app live** — digantikan oleh komisi ongkir |
| **Alasan hapus** | Menjadi insentif kurir untuk pindah ke app: "pakai app = tidak perlu bayar iuran lagi" |

### 2. Komisi Ongkir 10% (Setelah App Live)
| Item | Detail |
|------|--------|
| **Mekanisme** | Antarkanma mengambil 10% dari ongkir setiap order |
| **Rata-rata ongkir** | ~Rp 7.500 per order (estimasi campuran jarak dekat & jauh) |
| **Fee per order** | Rp 7.500 × 10% = **Rp 750** |

> **Contoh perhitungan:**
> - Order jarak 2 km → ongkir Rp 5.000 → fee Antarkanma Rp 500, kurir terima Rp 4.500
> - Order jarak 5 km → ongkir Rp 12.500 → fee Antarkanma Rp 1.250, kurir terima Rp 11.250
> - Order jarak 4 km, 2 merchant → ongkir Rp 12.000 → fee Antarkanma Rp 1.200, kurir terima Rp 10.800

### 3. Komisi Ojek (Layanan Baru)
| Item | Detail |
|------|--------|
| **Mekanisme** | Antarkanma mengambil 10% dari tarif perjalanan ojek |
| **Tarif Dasar** | Rp 8.000 (0-3 km) |
| **Tarif per KM** | Rp 2.000/km (setelah 3 km) |
| **Potensi Pendapatan** | Rata-rata fee Rp 1.000 - 2.000 per trip |

### 4. Fee Aplikasi Merchant — Rp 1.000/order (Setelah App Live)
| Item | Detail |
|------|--------|
| **Mekanisme** | Setiap order yang masuk ke merchant via app dikenakan fee Rp 1.000 |
| **Nilai jual ke merchant** | "Dapat customer baru tanpa effort, cuma Rp 1.000 per pesanan" |
| **Perbandingan** | GoFood/GrabFood ambil 20-30% dari harga makanan — Rp 1.000 flat jauh lebih murah |

### 5. Komisi Jasa Titip (Manual Order)
| Item | Detail |
|------|--------|
| **Mekanisme** | Service charge untuk pembelian di merchant non-partner (manual) |
| **Besaran** | Rp 2.000 - Rp 5.000 per transaksi (di luar ongkir) |
| **Peluang** | Solusi untuk merchant yang tidak terdaftar atau slow response |

### 6. Potensi Pendapatan Tambahan (Masa Depan)
| Sumber | Mekanisme | Estimasi |
|--------|-----------|----------|
| **Slot promosi** | Merchant bayar untuk posisi featured/atas di app | Rp 50-100k/bulan per merchant |
| **Iklan lokal** | Banner iklan UMKM lokal di app | Rp 200-500k/bulan per slot |
| **Pengiriman ekspres** | Customer bayar ekstra untuk prioritas | Rp 3.000-5.000 per order |

---

## Proyeksi Pendapatan

### Perhitungan Per Order (Setelah App Live)
```
Pendapatan Antarkanma per order:
  Komisi ongkir 10%  :  Rp   750
  Fee merchant       :  Rp 1.000
  ─────────────────────────────
  Total per order    :  Rp 1.750
```

---

### Fase Transisi: Masih WA + App Mulai Jalan (Q1-Q2 2026)
*80 order/hari, 6 kurir, sebagian order masih via WA*

| Sumber | Perhitungan | Per Bulan |
|--------|-------------|-----------|
| Iuran kurir (masih aktif) | 6 × Rp 7.000 × 30 | Rp 1.260.000 |
| Komisi ongkir 10% (order via app) | ~40 × Rp 750 × 30 | Rp 900.000 |
| Fee merchant (order via app) | ~40 × Rp 1.000 × 30 | Rp 1.200.000 |
| **Total** | | **Rp 3.360.000** |

### Fase 1: App Fully Live (Q3-Q4 2026)
*200 order/hari, 10 kurir, iuran dihapus*

| Sumber | Perhitungan | Per Bulan |
|--------|-------------|-----------|
| ~~Iuran kurir~~ | ~~Dihapus~~ | Rp 0 |
| Komisi ongkir 10% | 200 × Rp 750 × 30 | Rp 4.500.000 |
| Fee merchant | 200 × Rp 1.000 × 30 | Rp 6.000.000 |
| **Total** | | **Rp 10.500.000** |

### Fase 2: Ekspansi (2027)
*400 order/hari, 20 kurir*

| Sumber | Perhitungan | Per Bulan |
|--------|-------------|-----------|
| Komisi ongkir 10% | 400 × Rp 750 × 30 | Rp 9.000.000 |
| Fee merchant | 400 × Rp 1.000 × 30 | Rp 12.000.000 |
| Slot promosi & iklan | ~10 merchant premium | Rp 1.000.000 |
| **Total** | | **Rp 22.000.000** |

---

## Perbandingan Revenue per Fase

| | Transisi | Fase 1 | Fase 2 |
|---|---------|--------|--------|
| **Order/hari** | 80 (50% app) | 200 | 400 |
| **Kurir** | 6 | 10 | 20 |
| **Iuran kurir** | ✅ Masih aktif | ❌ Dihapus | ❌ Dihapus |
| **Revenue/bulan** | Rp 3,36 juta | **Rp 10,5 juta** | **Rp 22 juta** |
| **Revenue/order** | Rp 1.750 | Rp 1.750 | Rp 1.750 + iklan |

---

## Struktur Biaya

### Biaya Tetap
| Item | Per Bulan |
|------|-----------|
| VPS Server | Rp 100.000 - 300.000 |
| Domain | ~Rp 15.000 (Rp 180.000/tahun) |
| S3 Storage (IDCloudHost) | Rp 50.000 - 100.000 |
| Internet & Pulsa | Rp 200.000 |
| **Total** | **~Rp 400.000 - 600.000** |

### Biaya Variabel
| Item | Detail |
|------|--------|
| Waktu development | Aswar (founder) — belum digaji |
| Operasional lapangan | Ihcal (co-founder) — belum digaji |

### Margin per Fase
| | Revenue | Biaya | **Profit** |
|---|---------|-------|-----------|
| **Transisi** | Rp 3.360.000 | ~Rp 600.000 | **Rp 2.760.000** |
| **Fase 1** | Rp 10.500.000 | ~Rp 1.000.000 | **Rp 9.500.000** |
| **Fase 2** | Rp 22.000.000 | ~Rp 2.000.000 | **Rp 20.000.000** |

---

## Tarif Ongkir (Pembagian Kurir vs Antarkanma)

| Jarak | Tarif Total | Kurir (90%) | Antarkanma (10%) |
|-------|-------------|-------------|-----------------|
| 0 - 3 km | Rp 5.000 | Rp 4.500 | Rp 500 |
| 4 km | Rp 10.000 | Rp 9.000 | Rp 1.000 |
| 5 km | Rp 12.500 | Rp 11.250 | Rp 1.250 |
| 6 km | Rp 15.000 | Rp 13.500 | Rp 1.500 |
| Multi-merchant (+) | + Rp 2.000 | + Rp 1.800 | + Rp 200 |

### Tarif Ojek (Penumpang)

| Jarak | Tarif Total | Driver (90%) | Antarkanma (10%) |
|-------|-------------|--------------|------------------|
| 0 - 3 km (Base) | Rp 8.000 | Rp 7.200 | Rp 800 |
| > 3 km | + Rp 2.000/km | + Rp 1.800/km | + Rp 200/km |
| Contoh 5 km | Rp 12.000 | Rp 10.800 | Rp 1.200 |

---

## Metode Pembayaran

| Metode | Status | Prioritas |
|--------|--------|-----------|
| **COD (Cash on Delivery)** | ✅ Aktif | Utama |
| **Transfer Bank** | ✅ Aktif | Sekunder |
| **Payment Gateway (Midtrans/Xendit)** | ⬜ Rencana | Fase 1 |
| **E-Wallet (DANA, OVO, GoPay)** | ⬜ Rencana | Fase 2 |

---

## Catatan Strategis

1. **Menghapus iuran saat app live = insentif kuat untuk kurir adopsi app**
   - Kurir yang pakai app tidak perlu bayar Rp 7.000/hari lagi
   - Motivasi kurir untuk transisi dari WA ke app

2. **Fee merchant Rp 1.000 sangat kompetitif**
   - GoFood/GrabFood ambil 20-30% dari harga makanan
   - Pesanan Rp 50.000 di GoFood = merchant bayar Rp 10.000-15.000
   - Di Antarkanma = merchant bayar **hanya Rp 1.000** → 10-15x lebih murah!

3. **Komisi ongkir 10% adil dan transparan**
   - Kurir masih terima 90% ongkir
   - Semakin jauh antar, semakin banyak juga yang diterima kurir

4. **Model ini scalable**
   - Revenue naik linear dengan jumlah order
   - Biaya operasional relatif tetap (server, domain)
   - Margin makin besar seiring pertumbuhan

---

*Dokumen ini terakhir diperbarui: 17 Februari 2026*
