# Deployment Guide

## Arsitektur

1. Laptop (Development):
- Domain: antarkanmaa.my.id
- Container: antarkanma-app (existing)
- Tunnel: menggunakan token yang ada

2. VPS (Production + Load Balancer):
- Domain: dev.antarkanmaa.my.id
- Containers:
  * antarkanma-app-vps: Aplikasi utama
  * antarkanma-db-vps: Database master
  * antarkanma-redis-vps: Redis master
  * antarkanma-nginx-lb: Load balancer
  * antarkanma-cloudflared: Tunnel (menggunakan token yang sama)

## Load Balancing Strategy
- GET requests:
  * 60% ke VPS (localhost:8000)
  * 40% ke Laptop (antarkanmaa.my.id)
- POST/PUT/DELETE requests:
  * 100% ke VPS

## Setup di VPS

1. Clone repository:
```bash
git clone <repository_url>
cd antarkanma
```

2. Copy file credentials:
```bash
# Copy Firebase credentials
mkdir -p storage/app/firebase
cp /path/to/firebase-credentials.json storage/app/firebase/

# Copy SSL certificates (jika ada)
mkdir -p storage/certs
cp /path/to/ssl/* storage/certs/
```

3. Jalankan setup script:
```bash
chmod +x setup-vps.sh stop-vps.sh status-vps.sh
./setup-vps.sh
```

Script akan:
- Copy konfigurasi yang diperlukan
- Setup direktori load balancer
- Jalankan aplikasi utama
- Jalankan load balancer

## Monitoring

1. Cek status service:
```bash
./status-vps.sh
```

2. Cek logs:
```bash
# Log aplikasi
docker logs -f antarkanma-app-vps

# Log load balancer
docker logs -f antarkanma-nginx-lb

# Log tunnel
docker logs -f antarkanma-cloudflared
```

3. Health check:
```bash
# Aplikasi utama
curl http://localhost:8000/api/health

# Load balancer
curl http://localhost/health
```

## Maintenance

1. Stop semua service:
```bash
./stop-vps.sh
```

2. Update aplikasi:
```bash
git pull
./setup-vps.sh
```

3. Backup database:
```bash
./backup-database.sh
```

## Troubleshooting

1. Jika load balancer tidak bisa mengakses laptop:
```bash
# Di VPS
ping antarkanmaa.my.id
curl -v https://antarkanmaa.my.id/api/health
```

2. Jika tunnel bermasalah:
```bash
# Restart tunnel
docker restart antarkanma-cloudflared
```

3. Jika load balancer tidak bekerja:
```bash
# Cek nginx config
docker exec antarkanma-nginx-lb nginx -t

# Reload nginx
docker exec antarkanma-nginx-lb nginx -s reload
```

## Rollback

Jika terjadi masalah:

1. Stop semua service:
```bash
./stop-vps.sh
```

2. Restore dari backup:
```bash
# Restore database
./restore-database.sh <backup_file>

# Restore code
git checkout <last_working_commit>
```

3. Start ulang:
```bash
./setup-vps.sh
