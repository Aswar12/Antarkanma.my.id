# Transaction Flow Analysis & Optimization Proposal

## Kondisi Saat Ini (Current Flow)
Sistem Antarkanma menggunakan pendekatan **Courier-First Trigger**:
1.  **Order Masuk** -> Masuk Database (`PENDING`).
2.  **Broadcast ke Kurir** -> Kurir mendapat notifikasi via FCM.
3.  **Merchant Menunggu** -> Merchant **BELUM** mendapat notifikasi resmi untuk memproses pesanan sampai ada Kurir yang menerima (`Approve`).

### Kenapa Flow ini dipakai?
*   **Mencegah Pemborosan**: Merchant tidak akan memasak/menyiapkan makanan jika tidak ada kurir yang menjemput.
*   **Kejelasan Tanggung Jawab**: Memastikan rantai pengiriman utuh sebelum produksi dimulai.

---

## Masalah 1: "Bagaimana jika tidak ada Kurir sama sekali?"
Risiko: Customer pesan, menunggu 10 menit, lalu batal otomatis.
*Result*: Customer kecewa.

### Solusi Rekomendasi: Validasi di Awal
Mencegah pesanan dibuat jika tidak ada kurir yang aktif.
*   **Mekanisme**: Saat User klik "Checkout", sistem cek tabel `couriers` (status active/online). Jika 0, tolak pesanan.
*   **Benefit**: Fail-fast. Customer tidak menunggu sia-sia.

---

## Masalah 2: "Bagaimana jika Orderan Padat & Semua Kurir Sibuk?"
Risiko: Kurir ada, tapi sedang mengantar order lain.

### Solusi A: Batching / Double Order (Recommended)
Izinkan kurir mengambil lebih dari 1 order sekaligus, terutama jika rute searah.
*   **Status Backend**: Saat ini backend `CourierController` **TIDAK** membatasi jumlah transaksi aktif per kurir. Jadi secara teknis, **Double Order sudah support**.
*   **Action**: Pastikan UI Aplikasi Kurir mengizinkan mereka menerima notifikasi/job baru mesti sedang status `ON_DELIVERY`.

### Solusi B: Antrean & Dynamic Timeout
Jika sistem deteksi high load (banyak order pending), timeout order diperpanjang (misal 30 menit) & user diberitahu "High Demand".

---

## Masalah 3: Aturan Batching (New Request)
Agar kurir tidak mengambil order yang arahnya berlawanan (tidak efisien).

### Solusi: "5km Rule"
Logic cerdas di `OsrmService` / `CourierController`:
1.  **Jika Jarak < 5 KM**: Kurir BEBAS ambil order tambahan (jarak pendek dianggap efisien).
2.  **Jika Jarak > 5 KM**: Sistem wajib cek "Searah".
    *   *Teknis*: Hitung sudut (bearing) antara [Posisi Kurir -> Tujuan Lama] dengan [Posisi Kurir -> Tujuan Baru]. Jika beda sudut > 45 derajat, sembunyikan order tersebut dari kurir ini.

---

## Kesimpulan & Rencana Aksi

Untuk fase ini, prioritas kita adalah:

1.  [ ] **Fix Critical Bug**: Implementasi `CourierController@updateTransactionStatus` agar kurir bisa selesaikan order.
2.  [ ] **Implementasi Validasi Awal**: Cek ketersediaan kurir sebelum checkout.
3.  [ ] **Implementasi 5km Rule**: Filter order yang muncul di aplikasi kurir berdasarkan jarak & arah.

Bagaimana? Apakah kita sepakat dengan pendekatan ini?
