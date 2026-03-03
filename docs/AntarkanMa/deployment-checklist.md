# Deployment Checklist — Antarkanma

> **Versi:** 1.0  
> **Dibuat:** 27 Februari 2026  
> **Last Updated:** 27 Februari 2026

Dokumen ini berisi checklist lengkap untuk deployment Antarkanma ke production.

---

## 📋 Pre-Deployment Checklist

### Code Quality

- [ ] Semua tests passing (`php artisan test`)
- [ ] Tidak ada critical bugs di backlog
- [ ] Code sudah di-review dan approved
- [ ] Tidak ada debugging code (dd, dump, console.log)
- [ ] Error logging sudah dikonfigurasi
- [ ] Test coverage minimal 40%

**Verification Commands:**
```bash
# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Check for debugging code
grep -r "dd(" app/
grep -r "dump(" app/
grep -r "console.log" mobile/
```

---

### Database

- [ ] Semua migrations berhasil dijalankan
- [ ] Database backup sudah dibuat
- [ ] Seeders sudah ditest (jika diperlukan)
- [ ] Database indexes sudah optimal
- [ ] Database connection pool configured

**Verification Commands:**
```bash
# Check migration status
php artisan migrate:status

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Show database size
php artisan tinker
>>> DB::select('SELECT table_schema AS "Database", ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS "Size (MB)" FROM information_schema.tables WHERE table_schema = "antarkanma" GROUP BY table_schema;');
```

---

### Environment

- [ ] File `.env` sudah dikonfigurasi dengan benar
- [ ] Semua environment variables yang diperlukan sudah diset
- [ ] API keys sudah dirotasi (jika diperlukan)
- [ ] Debug mode disabled di production (`APP_DEBUG=false`)
- [ ] APP_URL sudah sesuai domain production

**Critical Environment Variables:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://antarkanma.id

DB_HOST=production-db-host
DB_DATABASE=antarkanma_production
DB_USERNAME=production_user
DB_PASSWORD=<strong-password>

REDIS_HOST=production-redis-host

AWS_ACCESS_KEY_ID=<production-key>
AWS_SECRET_ACCESS_KEY=<production-secret>

FIREBASE_CREDENTIALS=/app/storage/app/firebase/firebase-credentials.json
```

---

### Security

- [ ] SSL certificate sudah terinstall
- [ ] HTTPS enforced (redirect HTTP to HTTPS)
- [ ] CORS sudah dikonfigurasi dengan benar
- [ ] Rate limiting enabled
- [ ] SQL injection protection verified
- [ ] XSS protection verified
- [ ] CSRF protection enabled
- [ ] Security headers configured

**Verification:**
```bash
# Test SSL
curl -I https://antarkanma.id

# Test HTTP to HTTPS redirect
curl -I http://antarkanma.id

# Check security headers
curl -I https://antarkanma.id/api/health
```

**Expected Security Headers:**
```
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

---

### Infrastructure

- [ ] Server resources adequate (CPU, RAM, Disk)
- [ ] Load balancer sudah dikonfigurasi
- [ ] Database replication berjalan
- [ ] Redis connection working
- [ ] Queue worker running
- [ ] Monitoring tools configured

**Server Requirements:**
```
Minimum:
- CPU: 2 cores
- RAM: 4 GB
- Disk: 40 GB SSD

Recommended:
- CPU: 4 cores
- RAM: 8 GB
- Disk: 80 GB SSD
```

**Verification Commands:**
```bash
# Check server resources
free -h
df -h
top -bn1 | head -n 5

# Check Redis connection
php artisan tinker
>>> Redis::ping();

# Check queue workers
ps aux | grep queue:listen

# Check Octane (if used)
ps aux | grep octane
```

---

### Mobile Apps

- [ ] Release APKs sudah di-build
- [ ] APKs sudah ditest di production devices
- [ ] API endpoints sudah dikonfigurasi (production URL)
- [ ] Firebase sudah dikonfigurasi untuk production
- [ ] App version sudah di-increment
- [ ] SHA-1 fingerprints sudah didaftarkan di Firebase

**Build Commands:**
```bash
# Customer App
cd mobile/customer
flutter build apk --release --split-per-abi
flutter build appbundle --release

# Merchant App
cd mobile/merchant
flutter build apk --release --split-per-abi
flutter build appbundle --release

# Courier App
cd mobile/courier
flutter build apk --release --split-per-abi
flutter build appbundle --release
```

---

## 🚀 Deployment Steps

### 1. Pre-Deployment

**Backup Database:**
```bash
#!/bin/bash

# Create backup directory
mkdir -p /home/antarkanma/backups

# Create database backup
mysqldump -u root -p antarkanma > /home/antarkanma/backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Compress backup
cd /home/antarkanma/backups
gzip backup_*.sql

# Upload to S3 (if configured)
aws s3 cp backup_*.sql.gz s3://antarkanma-backups/

# Keep only last 7 backups
ls -t backup_*.sql.gz | tail -n +8 | xargs -I {} rm {}

echo "Backup completed successfully!"
```

**Pull Latest Code:**
```bash
cd /var/www/antarkanma

# Pull latest code
git pull origin main

# Check current branch
git branch

# Check last commit
git log -1 --oneline
```

**Install Dependencies:**
```bash
cd /var/www/antarkanma

# Install PHP dependencies (production)
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies
npm install

# Build assets
npm run build
```

---

### 2. Migration

**Run Migrations:**
```bash
cd /var/www/antarkanma

# Run migrations (force for production)
php artisan migrate --force

# Check migration status
php artisan migrate:status
```

**Clear & Cache:**
```bash
cd /var/www/antarkanma

# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize (cache everything)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

**Storage Link:**
```bash
# Create storage link (if not exists)
php artisan storage:link

# Set permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

---

### 3. Services Restart

**Restart PHP-FPM:**
```bash
# For systemd
sudo systemctl restart php8.4-fpm

# Check status
sudo systemctl status php8.4-fpm

# View logs
sudo journalctl -u php8.4-fpm -f
```

**Restart Queue Workers:**
```bash
cd /var/www/antarkanma

# Restart all queue workers
php artisan queue:restart

# Or restart specific worker
sudo systemctl restart antarkanma-queue

# Check queue status
php artisan queue:monitor database
```

**Restart Octane (if used):**
```bash
cd /var/www/antarkanma

# Reload Octane
php artisan octane:reload

# Or restart
php artisan octane:stop
php artisan octane:start

# Check status
ps aux | grep octane
```

**Restart Nginx:**
```bash
# Test configuration first
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx

# Check status
sudo systemctl status nginx
```

---

## ✅ Post-Deployment Checklist

### Health Checks

**API Health Check:**
```bash
# Check health endpoint
curl https://antarkanma.id/api/health

# Expected response:
{
    "status": "healthy",
    "database": "connected",
    "redis": "connected",
    "server": "production-server",
    "is_replica": false
}
```

**Database Connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::select('SELECT 1');
```

**Redis Connection:**
```bash
php artisan tinker
>>> Redis::ping();
>>> Redis::set('test_key', 'test_value');
>>> Redis::get('test_key');
```

**S3 Storage:**
```bash
php artisan tinker
>>> Storage::disk('s3')->exists('test.txt');
>>> Storage::disk('s3')->put('test.txt', 'test content');
>>> Storage::disk('s3')->get('test.txt');
```

**Firebase Connection:**
```bash
php artisan tinker
>>> use Kreait\Laravel\Firebase\Facades\Firebase;
>>> Firebase::getDatabase()->getReference('test')->push(['test' => 'value']);
```

---

### Smoke Tests

**User Flow:**
- [ ] User bisa register
- [ ] User bisa login
- [ ] User bisa browse products
- [ ] User bisa view product detail
- [ ] User bisa add to cart
- [ ] User bisa create order
- [ ] User bisa view order history

**Merchant Flow:**
- [ ] Merchant bisa login
- [ ] Merchant bisa view dashboard
- [ ] Merchant bisa receive order notification
- [ ] Merchant bisa approve/reject order
- [ ] Merchant bisa mark order as ready

**Courier Flow:**
- [ ] Courier bisa login
- [ ] Courier bisa view available transactions
- [ ] Courier bisa accept transaction
- [ ] Courier bisa update status
- [ ] Courier bisa complete delivery

**API Tests:**
```bash
# Test authentication
curl -X POST https://antarkanma.id/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Test products endpoint
curl https://antarkanma.id/api/products

# Test health endpoint
curl https://antarkanma.id/api/health
```

---

### Monitoring

**Application Monitoring:**
- [ ] Laravel Telescope active (development/staging only)
- [ ] Error tracking configured (Sentry/Bugsnag)
- [ ] Log aggregation working
- [ ] Alerts configured
- [ ] Dashboard showing green

**Server Monitoring:**
- [ ] CPU usage monitoring
- [ ] Memory usage monitoring
- [ ] Disk usage monitoring
- [ ] Network monitoring

**Database Monitoring:**
- [ ] Connection pool monitoring
- [ ] Slow query log enabled
- [ ] Replication lag monitoring

**Recommended Tools:**
```
Free Tier:
- UptimeRobot (uptime monitoring)
- Laravel Telescope (local debugging)
- Google Analytics (user tracking)

Paid (~$10-50/month):
- Sentry (error tracking)
- BetterStack (log management)
- New Relic (APM)
- Datadog (full observability)
```

---

### Documentation

- [ ] Deployment log sudah diupdate
- [ ] Version tag sudah dibuat
- [ ] Changelog sudah diupdate
- [ ] Team sudah dinotifikasi

**Deployment Log Template:**
```markdown
## Deployment - YYYY-MM-DD

**Deployed by:** [Name]
**Version:** v1.x.x
**Duration:** XX minutes

### Changes
- Feature 1
- Feature 2
- Bug fix 1

### Issues
- (none / describe issues)

### Rollback Plan
- (if needed, describe steps)
```

**Git Tag:**
```bash
# Create new tag
git tag -a v1.0.0 -m "Release v1.0.0 - Soft Launch"

# Push tag
git push origin v1.0.0

# List tags
git tag -l
```

---

## 🔄 Rollback Plan

### If Deployment Fails

**Step 1: Assess the Situation**
```bash
# Check application logs
tail -f storage/logs/laravel.log

# Check Nginx logs
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.4-fpm.log

# Check database status
php artisan tinker
>>> DB::connection()->getPdo();
```

**Step 2: Stop All Services**
```bash
# Stop Octane
php artisan octane:stop

# Stop queue workers
php artisan queue:clear

# Stop Nginx
sudo systemctl stop nginx

# Stop PHP-FPM
sudo systemctl stop php8.4-fpm
```

**Step 3: Restore Database**
```bash
# Find latest backup
ls -lt /home/antarkanma/backups/ | head -5

# Download from S3 if needed
aws s3 cp s3://antarkanma-backups/backup_YYYYMMDD_HHMMSS.sql.gz .
gunzip backup_YYYYMMDD_HHMMSS.sql.gz

# Restore database
mysql -u root -p antarkanma < backup_YYYYMMDD_HHMMSS.sql

# Verify restoration
php artisan tinker
>>> DB::table('users')->count();
```

**Step 4: Checkout Previous Version**
```bash
cd /var/www/antarkanma

# Find previous tag
git tag -l | tail -5

# Checkout previous version
git checkout v0.9.0

# Install old dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

**Step 5: Rollback Migrations**
```bash
# Rollback last batch
php artisan migrate:rollback --step=1

# Or rollback to specific migration
php artisan migrate:rollback --pretend

# Check migration status
php artisan migrate:status
```

**Step 6: Restart Services**
```bash
# Start PHP-FPM
sudo systemctl start php8.4-fpm

# Start Nginx
sudo systemctl start nginx

# Start queue workers
php artisan queue:work --daemon &

# Start Octane (if used)
php artisan octane:start

# Verify health
curl https://antarkanma.id/api/health
```

---

## 📞 Emergency Contacts

| Role | Name | Phone | Email |
|------|------|-------|-------|
| DevOps / Backend | Aswar | +62-XXX-XXXX-XXXX | aswar@antarkanma.id |
| Mobile Developer | [Name] | +62-XXX-XXXX-XXXX | [email] |
| QA / Testing | [Name] | +62-XXX-XXXX-XXXX | [email] |
| Customer Support | [Name] | +62-XXX-XXXX-XXXX | [email] |

**Emergency Escalation:**
```
Level 1: Try to fix within 30 minutes
Level 2: Call team member if not resolved in 30 minutes
Level 3: Rollback if not resolved in 1 hour
```

---

## 🔧 Troubleshooting

### Common Issues

**Issue: Migration Failed**
```bash
# Check specific error
php artisan migrate --force -v

# Rollback failed migration
php artisan migrate:rollback

# Fix migration file and retry
php artisan migrate --force
```

**Issue: Queue Not Working**
```bash
# Check queue table
php artisan queue:monitor database

# Restart queue worker
php artisan queue:restart

# Run queue manually to see errors
php artisan queue:work --stop-when-empty -v
```

**Issue: Cache Not Clearing**
```bash
# Clear all cache manually
rm -rf bootstrap/cache/*
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Issue: Permission Denied**
```bash
# Fix permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# For shared hosting
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
```

**Issue: 502 Bad Gateway**
```bash
# Check PHP-FPM status
sudo systemctl status php8.4-fpm

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log

# Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

---

## 📊 Deployment Metrics

**Track these metrics after each deployment:**

| Metric | Before | After | Target |
|--------|--------|-------|--------|
| Response Time (avg) | | | < 500ms |
| Error Rate | | | < 0.1% |
| Uptime | | | > 99.9% |
| Queue Lag | | | < 10 |
| Memory Usage | | | < 80% |

---

## ✨ Notes

- Selalu lakukan deployment di waktu traffic rendah (dini hari)
- Jangan deploy di hari Jumat/Sabtu (kecuali emergency)
- Selalu punya rollback plan sebelum deploy
- Dokumentasikan semua issues yang muncul saat deployment
- Lakukan post-mortem jika ada masalah serius

---

**Last Reviewed:** 27 Februari 2026  
**Next Review:** Setelah deployment berikutnya  
**Owner:** Aswar
