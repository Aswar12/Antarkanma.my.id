# Business Model Canvas (BMC) - AntarkanMa

Berikut adalah kerangka *Business Model Canvas* untuk **AntarkanMa** berdasarkan *Masterplan* dan *Blueprint* terbaru (Maret 2026). BMC ini mendeskripsikan 9 pilar utama logika perusahaan untuk menciptakan dan mendistribusikan nilai kepada pengguna.

---

## 1. Customer Segments (Segmen Pelanggan)
Siapa audiens yang kita layani?
- **Customer (End-User):** Warga lokal (Kabupaten Pangkep—Segeri, Ma'rang, Mandalle) yang membutuhkan efisiensi waktu untuk membeli makanan, kebutuhan pokok, atau jasa pengantaran barang/penumpang.
- **Merchant / UMKM Lokal:** Pemilik warung makan, toko kelontong, dan penjual lokal skala kecil-menengah yang ingin go-digital tanpa modal *marketing* besar.
- **Kurir & Driver:** Pemuda lokal / warga sekitar yang membutuhkan penghasilan fleksibel dengan memanfaatkan kendaraan roda duanya.

## 2. Value Propositions (Proposisi Nilai)
Apa masalah yang kita selesaikan dan nilai yang kita tawarkan?
- **Untuk Customer:**
  - One-stop app untuk Delivery Makanan, Antar Barang, Ojek, & Jasa Titip.
  - Ongkos kirim paling terjangkau (didukung sistem hitung OSRM lokal yang efisien).
  - Transparansi harga tanpa biaya *mark-up* tersembunyi.
- **Untuk Merchant:** 
  - **Komisi 0%**, merchant tidak rugi/dipusingkan oleh potongan platform (beda dengan kompetitor besar yang potong 20-30%).
  - Aplikasi POS terintegrasi gratis untuk mengelola *dine-in* & antrean *offline*.
- **Untuk Kurir:** 
  - Pekerjaan berbasis *demand* lokal, dengan area jelajah yang dikenalnya.
  - Pendapatan tetap adil (Platform hanya meminta 10% *subsidy contribution* dari uang ongkir, lebih kecil dari kompetitor).

## 3. Channels (Saluran Distribusi)
Bagaimana cara kita menjangkau pengguna?
- **Mobile Apps (Android/iOS):** Customer App, Merchant App, Courier App.
- **Marketing Komunitas:** *Word of Mouth* (mulut ke mulut), promosi komunitas/grup WhatsApp warga lokal.
- **Merchant Touchpoints:** Pasang stiker / spanduk QR AntarkanMa di warung-warung lokal.
- **Sosial Media:** Instagram, TikTok lokal (target geolokasi).

## 4. Customer Relationships (Hubungan Pelanggan)
Bagaimana kita berinteraksi dan mengikat pelanggan?
- **Hyperlocal & Personal:** Karena berbasis kota kecil, kedekatan "kenal kurirnya, kenal penjualnya" menumbuhkan rasa *trust* organik.
- **Customer Support Responsif:** Sistem bantuan berbasis chat.
- **Reward Program (Fase 2):** Diskon ongkir untuk pengguna rutin, sistem *loyalty point*.

## 5. Revenue Streams (Arus Pendapatan)
Dari mana saja kas uang masuk?
- **Service Fee:** Biaya pemeliharaan platform (Rp 500/transaksi dibebankan langsung secara transparan ke Customer).
- **Platform Fee Kurir:** Subsidi operasional dari kurir sebesar 10% dari tarif *Base Ongkir* (otomatis terpotong dari wallet saat order selesai).
- **Withdrawal Fee:** Biaya administrasi tetap (Rp 1.000) saat uang tunai dicairkan untuk kurir melalui proses manual admin.
- **Premium Placement / Iklan (Fase Ekspansi):** Biaya promosi/iklan *(sponsored merchant)* untuk slot daftar teratas di App.

## 6. Key Resources (Sumber Daya Utama)
Apa aset terpenting yang agar bisnis bisa jalan?
- **Teknologi:** Server VPS, sistem Laravel Backend, Firebase (FCM), 3 Mobile Apps Flutter, dan Filament Panel Admin.
- **Talenta Manusia (HR):**
  - **Founder (Aswar):** Penemu, Manajer Produk IT, CEO, dan merangkap kurir *hands-on*.
  - **Co-Founder (Ihcal):** Pengawas operasional lapangan & manajemen mitra kurir/branding.
  - **Mitra Kurir Lokal.**
- **Brand AntarkanMa:** Merek yang sudah sukses mengantongi tingkat kepercayaan awal di wilayah 3 kecamatan perdana.

## 7. Key Activities (Aktivitas Kunci)
Apa kegiatan inti harian tim kita?
- **Pengembangan Platform (Development):** Menerjemahkan kebutuhan lokal ke kode aplikasi, menambal bug *Flutter*, menjaga *stability routing OSRM*.
- **Akuisisi Merchant & Kurir:** Edukasi *door-to-door* mendigitalkan penjual lokal agar menggunakan POS AntarkanMa.
- **Operasional Pembukuan & Pencairan:** Pemrosesan pengiriman uang *withdrawal* manual melalui internet-banking Admin, menjaga stabilitas kas harian.
- **Customer Support:** Menyelesaikan komplain, retur, keterlambatan, atau pesanan bermasalah.

## 8. Key Partnerships (Kemitraan Utama)
Siapa pihak ketiga/mitra eksternal tempat kita bersandar?
- **Pemerintah / Instansi Lokal:** Penguatan relasi legalitas operasional dan izin usaha skala BUMDes (Bila ada).
- **Vendor Hosting Cloud:** IDCloudHost / VPS provider (backbone infrastruktur).
- **Google Maps & OSRM Engine:** Untuk akurasi *live-tracking* pergerakan GPS kurir dan perhitungan radius harga tiket logistik.
- **Provider Finansial Manual:** Bank-bank lokal di Pangkep untuk kemudahan transfer dana Admin-ke-Kurir tanpa payment gateway.

## 9. Cost Structure (Struktur Biaya)
Biaya apa saja yang wajib kita keluarkan untuk *survive*?
- **Biaya Fixed (Tetap):** Biaya sewa Server (VPS), Hosting Database Firebase (Bila melebihi tier gratis), dan sewa Domain tahunan.
- **Biaya Operasional (Opex):** 
  - Subsidi ongkos operasional harian tim inti di lapangan (bensin, makan).
  - Materi Branding Offline (cetak stiker, x-banner UMKM, jaket kurir).
- **Tech Resource Cost:** Penukar Waktu (*Sweat Equity*) Developer — yang bekerja tidak dibayar / belum mengambil margin keuntungan sebelum perusahaan BEP (*Break Even Point*).

---
**Dokumen Terkait:**
- `company-profile.md`: Konteks histori dan Visi-Misi.
- `service-fee-model.md`: Detail Arus Kas.
- `growth-roadmap.md`: Peta transisi dan ekspansi kuartal.

*Dokumen BMC ini terakhir diperbarui: 10 Maret 2026*
