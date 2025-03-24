# Setup Load Balancer dengan Nginx

## Arsitektur
```
Client (User)
      │
      ▼
Cloudflare DNS 
      │
      ▼
dev.antarkanmaa.my.id ➔ NGINX (di VPS)
                         │
         ┌───────────────┴────────────────┐
         ▼                                ▼
   Container App (di VPS)     loc.antarkanmaa.my.id (Cloudflare Tunnel ke Laptop)
```

## Arsitektur Data Layer

### MySQL Master (VPS)
- Primary database untuk write operations
- Konfigurasi di mysql-master.cnf:
  * GTID-based replication
  * Binary logging enabled
  * Optimized for write performance
  * 1GB InnoDB buffer pool
  * Strict durability (sync_binlog = 1)

### MySQL Slave (Laptop)
- Read-only replica untuk load distribution
- Konfigurasi di mysql-slave.cnf:
  * Strict read-only mode
  * Optimized for read operations
  * 512MB InnoDB buffer pool
  * Relaxed durability for better read performance

## Pembagian Trafik

### Application Layer
- Server utama (VPS Container App) - Weight: 75%
  * Menangani semua write operations (POST/PUT/DELETE/PATCH)
  * Menangani semua admin routes (/admin, /filament)
  * Prioritas untuk read operations (GET)
  * Fallback utama jika laptop tidak aktif

- Server pendukung (Laptop) - Weight: 25%
  * Hanya menangani read operations (GET)
  * Berperan sebagai server booster saat aktif
  * Terhubung melalui Cloudflare Tunnel

### Redis Master (VPS)
- Primary cache untuk session dan data cepat
- Konfigurasi di redis-master.conf:
  * Protected mode dengan password
  * AOF persistence untuk durability
  * 256MB memory limit
  * LRU eviction policy

### Redis Slave (Laptop)
- Read-only replica untuk distribusi cache
- Konfigurasi di redis-slave.conf:
  * Replica dari VPS Redis master
  * Read-only mode
  * 256MB memory limit
  * Identical persistence settings

### Data Layer Strategy
- Database Operations:
  * Write: Selalu ke MySQL Master di VPS
  * Read: Distributed antara Master dan Slave
  * Automatic replication untuk consistency

- Cache Operations:
  * Write: Selalu ke Redis Master di VPS
  * Read: Load balanced antara Master dan Slave
  * Real-time replication untuk fast access

## Setup DNS di Cloudflare

1. DNS Records:
   * dev.antarkanmaa.my.id -> IP VPS (A Record)
   * loc.antarkanmaa.my.id -> Cloudflare Tunnel ke Laptop (CNAME)

2. Cloudflare Tunnel:
   * Tunnel VPS: Untuk akses ke container app
   * Tunnel Laptop: Untuk akses ke development server

## Deployment di VPS

1. Setup Services:
```bash
# Copy konfigurasi
cp .env.vps .env
cp docker-compose.vps.yml docker-compose.yml

# Jalankan services
./setup-vps.sh
```

2. Verifikasi:
```bash
# Cek status services
./status-vps.sh

# Test load balancer
curl -I http://dev.antarkanmaa.my.id
```

## Konfigurasi yang Digunakan

1. Load Balancing Rules:
- Write Operations:
  * Semua POST/PUT/DELETE/PATCH -> VPS
  * Konsistensi data terjamin

- Read Operations (GET):
  * 75% trafik ke VPS
  * 25% trafik ke Laptop (jika aktif)
  * Automatic fallback ke VPS jika laptop down

- Admin Routes:
  * /admin dan /filament selalu ke VPS
  * Keamanan dan konsistensi terjamin

2. Health Checks:
- Endpoint: /api/health
- Interval: 5 detik
- Fails: 3x sebelum dianggap down
- Passes: 2x sebelum dianggap up kembali

3. Proxy Settings:
- Timeouts: 60 detik
- Gzip compression untuk static files
- Forwarded headers untuk real IP dan protokol

## Monitoring

1. Health Check Status:
```bash
# Di VPS
curl http://localhost/health
```

2. Nginx Status:
```bash
# Check error logs
docker logs antarkanma-nginx-lb

# Check config
docker exec antarkanma-nginx-lb nginx -t
```

## Troubleshooting

1. Jika load balancer tidak bisa mengakses dev.antarkanmaa.my.id:
```bash
# Check DNS resolution
docker exec antarkanma-nginx-lb nslookup dev.antarkanmaa.my.id

# Check connection
docker exec antarkanma-nginx-lb curl -v dev.antarkanmaa.my.id
```

2. Jika write operations tidak ter-route ke VPS:
```bash
# Check nginx config
docker exec antarkanma-nginx-lb nginx -T | grep "if.*request_method"
```

3. Jika health check gagal:
```bash
# Check health endpoint di kedua server
curl http://localhost:8000/api/health
curl https://dev.antarkanmaa.my.id/api/health
```

## Rollback Plan

Jika terjadi masalah dengan load balancer:
1. Update DNS di Cloudflare:
   - Pastikan dev.antarkanmaa.my.id mengarah ke IP VPS
2. Stop container load balancer:
   ```bash
   cd loadbalancer && docker-compose down
   ```
3. Restore konfigurasi sebelumnya:
   ```bash
   cd ..
   cp docker-compose.vps.yml docker-compose.yml
   cp .env.vps .env
   docker-compose up -d
   ```

## Keuntungan Setup Ini

1. High Availability:
   - VPS sebagai server utama menjamin stabilitas
   - Laptop sebagai server booster saat aktif
   - Fallback otomatis ke VPS jika laptop tidak tersedia

2. Optimasi Performa:
   - Write operations terpusat di VPS untuk konsistensi data
   - Admin routes terjamin di server utama
   - Read operations terdistribusi untuk performa optimal

3. Keamanan:
   - Semua traffic melalui NGINX di VPS
   - Admin routes terisolasi di server utama
   - Health checks untuk monitoring status server
