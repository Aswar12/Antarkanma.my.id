# Masalah & Solusi — Antarkanma

Dokumen ini memetakan masalah operasional saat ini (berbasis WhatsApp) dan bagaimana aplikasi Antarkanma menyelesaikannya.

---

## 1. Kapasitas Perangkat Terbatas

| | Detail |
|---|---|
| **Masalah** | WhatsApp hanya mendukung max 5 perangkat terhubung. Dengan 6+ kurir, tidak semua bisa akses chat customer secara bersamaan. |
| **Dampak** | Kurir kehilangan informasi, order terlewat, koordinasi lambat. |
| **Solusi App** | Setiap kurir punya akun sendiri di aplikasi. Tidak ada batasan jumlah kurir. Notifikasi push otomatis ke kurir yang tersedia. |

## 2. Kurir Harus Balas Chat Sambil di Jalan

| | Detail |
|---|---|
| **Masalah** | Kurir sedang mengantar tapi harus membaca dan membalas WA customer baru. Berbahaya dan tidak efisien. |
| **Dampak** | Respon lambat, potensi kecelakaan, customer menunggu lama. |
| **Solusi App** | Customer pesan sendiri lewat app (pilih menu, checkout). Kurir hanya terima notifikasi "ada order baru" — tinggal accept/decline. Kurir fokus di jalan. |

## 3. Salah Pesanan

| | Detail |
|---|---|
| **Masalah** | Customer mengirim pesanan lewat chat tidak terstruktur. Kurir kadang salah baca atau salah interpretasi. |
| **Dampak** | Customer kecewa, makanan terbuang, kurir harus bolak-balik. |
| **Solusi App** | Menu terstruktur dengan nama, foto, harga, dan varian. Customer pilih langsung dari katalog — tidak ada ambiguitas. Order tercatat digital dengan detail lengkap. |

## 4. Bingung Ongkir Multi-Merchant

| | Detail |
|---|---|
| **Masalah** | Ketika customer pesan dari 2-3 merchant berbeda, menghitung ongkir jadi rumit dan membingungkan kurir. |
| **Dampak** | Ongkir kadang salah hitung, customer berdebat soal ongkir, kurir rugi waktu. |
| **Solusi App** | App menghitung ongkir otomatis: jarak × Rp 2.500 (atau flat Rp 5.000 untuk ≤3km) + Rp 2.000 per merchant tambahan. Customer lihat total sebelum checkout. Tidak ada perdebatan. |

## 5. Koordinasi 6+ Kurir

| | Detail |
|---|---|
| **Masalah** | Aswar/Ihcal harus manual menentukan kurir mana yang ambil order mana. Lewat WA ini sangat lambat. |
| **Dampak** | Ada kurir menganggur sementara kurir lain kewalahan. Order menumpuk di jam sibuk. |
| **Solusi App** | Sistem assignment kurir: kurir terdekat/tersedia mendapat notifikasi pertama. Dashboard untuk melihat status semua kurir sekaligus. |

## 6. Customer Tidak Tahu Mau Pesan Apa

| | Detail |
|---|---|
| **Masalah** | Customer WA tanya "ada apa aja?" — Aswar/Ihcal harus jelaskan menu dari beberapa merchant satu per satu lewat chat. |
| **Dampak** | Waktu terbuang 5-10 menit per customer hanya untuk browsing menu. Kalikan 80 order/hari. |
| **Solusi App** | Katalog lengkap dengan foto, deskripsi, harga, rating. Customer browse sendiri kapan saja. Filter berdasarkan kategori, jarak, popularitas. |

## 7. Tidak Ada Riwayat & Tracking

| | Detail |
|---|---|
| **Masalah** | Tidak ada catatan riwayat pesanan. Customer tanya "kemarin saya pesan apa?" harus scroll chat WA. Tidak ada tracking lokasi kurir. |
| **Dampak** | Customer tidak tahu status pesanan, sering bertanya "sudah sampai mana?". Tidak bisa repeat order dengan mudah. |
| **Solusi App** | Riwayat lengkap semua pesanan. Status tracking real-time (dipesan → diproses → di jalan → sampai). Fitur "pesan lagi" untuk repeat order. |

## 8. Tidak Ada Data untuk Keputusan Bisnis

| | Detail |
|---|---|
| **Masalah** | Tidak pernah hitung total revenue per bulan. Tidak tahu merchant mana yang paling laris. Tidak tahu jam sibuk mana yang perlu tambah kurir. |
| **Dampak** | Keputusan bisnis berdasarkan feeling, bukan data. Sulit meyakinkan investor atau partner. |
| **Solusi App** | Dashboard analytics: total order, revenue, top merchant, peak hours, performa kurir. Semua terukur dan bisa jadi bahan keputusan. |

---

## Ringkasan Dampak

| Metrik | Sebelum (WA) | Sesudah (App) |
|--------|-------------|---------------|
| **Waktu per order** | 5-10 menit (chat + koordinasi) | < 1 menit (customer self-service) |
| **Kapasitas kurir** | Max 5 terhubung | Tidak terbatas |
| **Akurasi pesanan** | Sering salah | Terstruktur digital |
| **Ongkir multi-merchant** | Dihitung manual, sering salah | Otomatis |
| **Data bisnis** | Tidak ada | Dashboard lengkap |
| **Skalabilitas** | Terbatas | Siap ekspansi |

---

*Dokumen ini terakhir diperbarui: 16 Februari 2026*
