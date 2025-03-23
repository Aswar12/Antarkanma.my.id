# Setup Load Balancer dengan Nginx

## Arsitektur
- antarkanmaa.my.id -> Load Balancer (VPS)
  * Menangani write operations (POST/PUT/DELETE/PATCH)
  * Weight: 60% untuk read operations (GET)
- dev.antarkanmaa.my.id -> Development (Laptop)
  * Hanya menangani read operations (GET)
  * Weight: 40% untuk read operations

## Persiapan

1. Di VPS:
```bash
# Copy file konfigurasi
cp docker-compose.lb.yml docker-compose.yml
cp .env.lb .env
cp nginx-lb.conf /etc/nginx/conf.d/default.conf

# Jalankan container
docker-compose up -d
```

2. Di Cloudflare:
- Buat tunnel baru untuk antarkanmaa.my.id
- Update DNS records:
  * antarkanmaa.my.id -> tunnel load balancer (VPS)
  * dev.antarkanmaa.my.id -> tetap ke tunnel laptop (existing)

## Konfigurasi yang Digunakan

1. Load Balancing:
- Write operations (POST/PUT/DELETE/PATCH) -> selalu ke VPS
- Read operations (GET):
  * 60% ke VPS
  * 40% ke laptop

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
   - Arahkan antarkanmaa.my.id kembali ke VPS langsung
2. Stop container load balancer:
   ```bash
   docker-compose down
   ```
3. Restore konfigurasi sebelumnya:
   ```bash
   cp docker-compose.yml.backup docker-compose.yml
   cp .env.backup .env
   docker-compose up -d
