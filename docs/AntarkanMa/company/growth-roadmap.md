# Growth Roadmap — Antarkanma 2026-2027

---

## Fase 1: Transisi WA → App (Q1-Q2 2026)

**Tujuan**: Aplikasi berfungsi dan mulai dipakai customer, tanpa menghentikan operasi WA.

### Milestone

| # | Milestone | Target | KPI |
|---|-----------|--------|-----|
| 1.1 | Backend API stabil & tested | Maret 2026 | Semua endpoint kritis punya test |
| 1.2 | App Flutter customer siap (fix bugs) | April 2026 | Customer bisa browse, pesan, bayar |
| 1.3 | Soft launch — 10-20 customer pakai app | Mei 2026 | 10% order via app |
| 1.4 | Iterasi berdasarkan feedback | Juni 2026 | Rating app > 4.0 |

### Strategi Transisi
1. **Jalankan WA dan App bersamaan** — jangan matikan WA langsung
2. **Mulai dari customer loyal** — ajak mereka coba app dengan insentif (gratis ongkir 3x pertama)
3. **Kurir install app bertahap** — mulai 2-3 kurir dulu, pastikan lancar
4. **Merchant upload menu** — bantu merchant isi katalog (foto, harga)

### Teknis yang Harus Diselesaikan
- [ ] Fix bugs di Flutter app
- [ ] Automated testing untuk API endpoints kritis
- [ ] Perhitungan ongkir otomatis (sesuai formula saat ini)
- [ ] Push notification ke kurir saat ada order baru
- [ ] Dashboard admin untuk monitoring

---

## Fase 2: App Stabil & Growth (Q3-Q4 2026)

**Tujuan**: 100% order via app, 200 order/hari, 10 kurir aktif.

### Milestone

| # | Milestone | Target | KPI |
|---|-----------|--------|-----|
| 2.1 | 50% order via app | Agustus 2026 | 40-50 order/hari via app |
| 2.2 | Tambah 4 kurir baru | September 2026 | 10 kurir aktif |
| 2.3 | 100% order via app, WA dihentikan | Oktober 2026 | 0 order via WA |
| 2.4 | 200 order/hari tercapai | Desember 2026 | 200 order/hari konsisten |

### Strategi Pertumbuhan
1. **Marketing agresif** — promo di WA group, sosial media, banner di merchant
2. **Insentif customer** — diskon ongkir untuk 5 order pertama via app
3. **Rekrut kurir** — buka pendaftaran kurir baru, target 10 orang
4. **Tambah merchant** — target 50+ merchant aktif di app
5. **Fitur baru** — promo/voucher, repeat order, favorit merchant

### Teknis yang Harus Diselesaikan
- [ ] Payment gateway (Midtrans/Xendit) — customer bayar langsung di app
- [ ] Real-time tracking kurir (GPS)
- [ ] Sistem promo & voucher
- [ ] Analytics dashboard untuk merchant
- [ ] Performance optimization (caching, query tuning)

---

## Fase 3: Ekspansi (2027)

**Tujuan**: Ekspansi ke kecamatan lain di Kabupaten Pangkep, 400+ order/hari.

### Milestone

| # | Milestone | Target | KPI |
|---|-----------|--------|-----|
| 3.1 | Ekspansi ke 2 kecamatan baru | Q1 2027 | 5 area operasi |
| 3.2 | 20 kurir aktif | Q2 2027 | 20 kurir |
| 3.3 | 400 order/hari | Q3 2027 | 400 order/hari |
| 3.4 | Revenue Rp 30+ juta/bulan | Q4 2027 | Sustainable business |

### Strategi Ekspansi
1. **Model "franchise" kurir** — rekrut kepala kurir per kecamatan
2. **Kerjasama dengan pemerintah daerah** — dukung UMKM lokal
3. **Hire customer support** — 1 orang untuk handle masalah customer
4. **Tambah developer** — percepat pengembangan fitur baru
5. **Fitur baru**: chat in-app, scheduled delivery, loyalty points

---

## Ringkasan Timeline

```
2026 Q1-Q2  ──────────────  2026 Q3-Q4  ──────────────  2027
│                           │                           │
│  TRANSISI WA → APP        │  GROWTH & STABILISASI     │  EKSPANSI
│                           │                           │
│  • Fix Flutter bugs       │  • 200 order/hari         │  • 5 kecamatan
│  • Test API               │  • 10 kurir               │  • 20 kurir
│  • Soft launch            │  • Payment gateway        │  • 400 order/hari
│  • 10% via app            │  • 100% via app           │  • Rp 30+ juta/bulan
│                           │                           │
│  Revenue: ~Rp 3.6 juta    │  Revenue: ~Rp 16 juta     │  Revenue: ~Rp 36 juta
```

---

## Risiko & Mitigasi

| Risiko | Probabilitas | Dampak | Mitigasi |
|--------|-------------|--------|----------|
| Customer enggan pindah dari WA | Tinggi | Tinggi | Insentif, edukasi, jalan dua-duanya dulu |
| App banyak bug saat launch | Sedang | Tinggi | Testing menyeluruh, soft launch bertahap |
| Kurir tidak mau pakai app | Sedang | Tinggi | Training, UI sederhana, tunjukan manfaat |
| Merchant malas upload menu | Tinggi | Sedang | Bantu upload pertama kali, tunjukan benefit |
| Kompetitor lokal ikut bikin app | Rendah | Sedang | First mover advantage, brand yang sudah kuat |
| Masalah teknis server | Rendah | Tinggi | Monitoring, backup, Docker deployment |

---

*Dokumen ini terakhir diperbarui: 16 Februari 2026*
