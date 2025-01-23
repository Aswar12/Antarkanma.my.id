# Dokumentasi Sistem Perhitungan Biaya Pengiriman Antarkanma

## Struktur Biaya Pengiriman

### 1. Range Jarak dan Biaya

| Range Jarak | Total Biaya | Fee Platform | Biaya Kurir |
|-------------|-------------|--------------|-------------|
| 0-3 km      | Rp 7.000    | Rp 2.000     | Rp 5.000    |
| 3-5 km      | Rp 10.000   | Rp 2.000     | Rp 8.000    |
| 5-7 km      | Rp 15.000   | Rp 2.000     | Rp 13.000   |
| 7-10 km     | Rp 20.000   | Rp 2.000     | Rp 18.000   |
| >10 km      | Rp 25.000   | Rp 2.000     | Rp 23.000   |

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
     * Bahan bakar
     * Waktu pengantaran

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

**Contoh 2: Jarak 4.2 km**
- Masuk range 3-5 km
- Total biaya: Rp 10.000
- Breakdown:
  * Platform: Rp 2.000
  * Kurir: Rp 8.000

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
        "courier_fee": 5000
    },
    "distance_range": "0-3 km"
}
```

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
