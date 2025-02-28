# Dokumentasi Sistem Perhitungan Biaya Pengiriman Antarkanma

## Struktur Biaya Pengiriman

### 1. Range Jarak dan Biaya

| Range Jarak | Total Biaya | Fee Platform | Biaya Kurir | Biaya Operasional* | Pendapatan Bersih Kurir |
|-------------|-------------|--------------|-------------|-------------------|------------------------|
| 0-3 km      | Rp 7.000    | Rp 2.000     | Rp 5.000    | Rp 1.101         | Rp 3.899              |
| 3-6 km      | Rp 10.000   | Rp 2.000     | Rp 8.000    | Rp 2.202         | Rp 5.798              |
| 6-10 km     | Rp 15.000   | Rp 2.000     | Rp 13.000   | Rp 3.303         | Rp 9.697              |
| 11-13 km    | Rp 22.000   | Rp 2.000     | Rp 18.000   | Rp 4.404         | Rp 13.596             |
| >14 km      | Rp 25.000   | Rp 2.000     | Rp 23.000   | Rp 5.505         | Rp 17.495             |

*Biaya Operasional mencakup BBM (Rp 222/km) + Oli (Rp 25/km) + Ban (Rp 20/km) + Biaya Tak Terduga (Rp 100/km) = Rp 367/km

### 2. Komponen Biaya

1. **Fee Platform (Antarkanma)**
   - Biaya tetap: Rp 2.000 per transaksi
   - Tidak bergantung pada jarak
   - Digunakan untuk:
     * Maintenance server
     * Pengembangan sistem
     * Biaya operasional

2. **Biaya Kurir**
   - Bervariasi berdasarkan jarak
   - Range: Rp 5.000 - Rp 23.000
   - Faktor pertimbangan:
     * Jarak tempuh
     * Bahan bakar (Rp 10.000/liter, konsumsi 45km/liter)
     * Waktu pengantaran
   - Pendapatan bersih setelah dikurangi biaya BBM: Rp 4.333 - Rp 19.667

## Sistem Perhitungan

### 1. Penentuan Jarak
- Menggunakan koordinat GPS
- Perhitungan jarak langsung (straight line)
- Dibulatkan ke atas dalam kilometer

### 2. Penentuan Biaya
- Sistem otomatis menentukan range jarak
- Aplikasikan biaya sesuai range
- Breakdown otomatis fee platform dan biaya kurir

### 3. Contoh Perhitungan

**Contoh 1: Jarak 2.5 km**
- Masuk range 0-3 km
- Total biaya: Rp 7.000
- Breakdown:
  * Platform: Rp 2.000
  * Kurir: Rp 5.000
  * Biaya Operasional: Rp 918 (2.5 km × Rp 367/km)
    - BBM: Rp 555
    - Oli: Rp 63
    - Ban: Rp 50
    - Biaya Tak Terduga: Rp 250
  * Pendapatan bersih kurir: Rp 4.082

**Contoh 2: Jarak 4.2 km**
- Masuk range 3-6 km
- Total biaya: Rp 10.000
- Breakdown:
  * Platform: Rp 2.000
  * Kurir: Rp 8.000
  * Biaya Operasional: Rp 1.541 (4.2 km × Rp 367/km)
    - BBM: Rp 932
    - Oli: Rp 105
    - Ban: Rp 84
    - Biaya Tak Terduga: Rp 420
  * Pendapatan bersih kurir: Rp 6.459

## Implementasi Teknis

### 1. Input yang Diperlukan
- Koordinat merchant (latitude, longitude)
- Koordinat customer (latitude, longitude)

### 2. Proses Kalkulasi
1. Hitung jarak antara koordinat
2. Tentukan range yang sesuai
3. Aplikasikan struktur biaya
4. Generate breakdown biaya

### 3. Output yang Dihasilkan
```json
{
    "distance": "2.5 km",
    "total_cost": 7000,
    "breakdown": {
        "platform_fee": 2000,
        "courier_fee": 5000,
        "operational_costs": {
            "fuel_cost": 555,
            "oil_cost": 63,
            "tire_cost": 50,
            "misc_cost": 250,
            "total": 918
        },
        "courier_net_income": 4082
    },
    "distance_range": "0-3 km"
}
```

## Perhitungan Biaya Operasional

### 1. Biaya BBM

#### Parameter BBM
- Konsumsi BBM: 45 km/liter
- Harga BBM: Rp 10.000/liter
- Formula: (Jarak × Harga BBM) ÷ Konsumsi per liter

#### Contoh Perhitungan BBM
- Untuk jarak 6 km:
  * Konsumsi BBM = 6 km ÷ 45 km/liter = 0,133 liter
  * Biaya BBM = 0,133 liter × Rp 10.000 = Rp 1.333

#### Efisiensi Biaya BBM
- Semakin jauh jarak tempuh, semakin tinggi margin pendapatan bersih kurir
- Persentase biaya BBM terhadap pendapatan kurir:
  * 0-3 km: 13,34% dari biaya kurir
  * 3-6 km: 16,66% dari biaya kurir
  * 6-9 km: 15,38% dari biaya kurir
  * 9-12 km: 14,82% dari biaya kurir
  * >12 km: 14,49% dari biaya kurir

### 2. Biaya Perawatan Kendaraan

#### Penggantian Oli
- Interval: 2.000 - 5.000 km
- Frekuensi: 2-3 bulan sekali (tergantung pemakaian)
- Biaya rata-rata: Rp 50.000 per penggantian
- Faktor yang mempengaruhi:
  * Jenis oli (mineral atau sintetis)
  * Kondisi berkendara (stop-and-go vs. perjalanan panjang)
- Estimasi biaya per km: Rp 10-25 (Rp 50.000 ÷ 2.000-5.000 km)

#### Penggantian Ban
- Interval: 15.000 - 30.000 km
- Biaya rata-rata: Rp 300.000 per ban
- Faktor yang mempengaruhi:
  * Jenis ban
  * Tekanan ban
  * Gaya berkendara
  * Kondisi jalan
- Estimasi biaya per km: Rp 10-20 (Rp 300.000 ÷ 15.000-30.000 km)

### 3. Biaya Tak Terduga
- Cadangan biaya service: 10% dari pendapatan per hari
- Komponen yang termasuk:
  * Perbaikan mendadak
  * Penggantian spare part
  * Biaya perawatan rutin lainnya
- Estimasi per km: Rp 50-100

### 4. Total Biaya Operasional per Kilometer
| Komponen Biaya    | Biaya per KM    |
|-------------------|-----------------|
| BBM              | Rp 222          |
| Oli              | Rp 10-25        |
| Ban              | Rp 10-20        |
| Biaya Tak Terduga| Rp 50-100       |
| Total            | Rp 292-367      |

## Catatan Penting

1. **Pembulatan Jarak**
   - Jarak dibulatkan ke atas dalam kilometer
   - Contoh: 2.1 km tetap masuk range 0-3 km

2. **Batas Maksimum**
   - Jarak maksimum yang dilayani: tidak dibatasi
   - Semua jarak >10 km menggunakan rate yang sama

3. **Transparansi**
   - Customer dapat melihat breakdown biaya
   - Kurir dapat melihat bagian yang diterima
   - Merchant dapat memantau biaya pengiriman
