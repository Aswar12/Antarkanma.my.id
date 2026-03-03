# Troubleshooting Guide — Antarkanma

> **Versi:** 1.0  
> **Dibuat:** 27 Februari 2026  
> **Last Updated:** 27 Februari 2026

Panduan ini berisi solusi untuk masalah-masalah umum yang mungkin dihadapi saat development dan deployment Antarkanma.

---

## 📱 Mobile Development (Flutter)

### 1. ADB Not Recognized

**Symptoms:**
```
'adb' is not recognized as an internal or external command
```

**Solutions:**

**Option 1: Add ADB to PATH (Permanent)**
```
1. Open System Properties > Environment Variables
2. Under "System variables", find "Path"
3. Click "Edit" > "New"
4. Add: C:\Users\aswar\AppData\Local\Android\Sdk\platform-tools
5. Click "OK" on all dialogs
6. Restart VS Code / Terminal
```

**Option 2: Use Full Path (Temporary)**
```cmd
C:\Users\aswar\AppData\Local\Android\Sdk\platform-tools\adb.exe devices
```

**Option 3: Verify ADB Installation**
```cmd
where adb
adb --version
```

---

### 2. No Devices Found

**Symptoms:**
```
List of devices attached
(no devices listed)
```

**Solutions:**

**For Physical Device:**
```
1. Enable Developer Options on phone:
   - Settings > About Phone
   - Tap "Build Number" 7 times
   
2. Enable USB Debugging:
   - Settings > Developer Options
   - Enable "USB Debugging"
   
3. Set USB Mode to "File Transfer" (MTP)

4. Revoke USB debugging authorizations:
   - Developer Options > Revoke USB debugging authorizations
   
5. Reconnect USB cable

6. Accept RSA fingerprint on phone
```

**For Emulator:**
```cmd
# List available emulators
emulator -list-avds

# Start emulator
emulator -avd <avd_name>

# Or via Android Studio
Tools > Device Manager > Play button
```

**Verify Connection:**
```cmd
adb devices
adb kill-server
adb start-server
adb devices
```

---

### 3. ADB Reverse Failed

**Symptoms:**
```
error: closed
WARNING: ADB reverse failed.
```

**Solutions:**

**Check Device Connection:**
```cmd
adb devices
# Make sure device is listed as "device" not "unauthorized"
```

**Restart ADB Server:**
```cmd
adb kill-server
adb start-server
adb reverse tcp:8000 tcp:8000
```

**Alternative: Use IP Address**
```dart
// In config.dart
// Change from:
static const String baseUrl = 'http://localhost:8000/api';

// To:
static const String baseUrl = 'http://192.168.1.XXX:8000/api';
// Replace XXX with your laptop's IP address
```

**Find Your IP:**
```cmd
# Windows
ipconfig

# Look for "IPv4 Address" under your network adapter
```

---

### 4. Flutter Build Failed

**Symptoms:**
```
Error: The method '...' is undefined for the type '...'
```

**Solutions:**

**Clean and Rebuild:**
```cmd
cd mobile/merchant

# Clean build
flutter clean

# Get dependencies
flutter pub get

# Rebuild
flutter build apk --debug
```

**Check Flutter Version:**
```cmd
flutter --version
flutter upgrade
```

**Fix Dependencies:**
```cmd
flutter pub cache repair
flutter pub get
```

**Check for Null Safety Issues:**
```cmd
flutter analyze
```

---

### 5. Hot Reload Not Working

**Symptoms:**
```
Hot reload failed. Please restart the app.
```

**Solutions:**

**Full Restart:**
```
In terminal running app:
- Press 'R' (uppercase) for full restart
- Press 'r' (lowercase) for hot reload
```

**Clear Cache:**
```cmd
flutter clean
flutter pub get
flutter run
```

**Check for Compilation Errors:**
```cmd
flutter analyze
```

---

### 6. Firebase Connection Issues

**Symptoms:**
```
FirebaseException: No Firebase App
FirebaseException: SHA-1 fingerprint not found
```

**Solutions:**

**Add SHA-1 to Firebase:**
```cmd
# Get SHA-1 fingerprint
cd mobile/merchant
keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android

# Or for release keystore
keytool -list -v -keystore /path/to/keystore.jks -alias your-alias
```

**Add to Firebase Console:**
```
1. Go to Firebase Console > Project Settings
2. Add SHA-1 fingerprint
3. Download google-services.json
4. Replace file in mobile/merchant/android/app/
5. Rebuild app
```

**Verify Firebase Configuration:**
```dart
// In main.dart
await Firebase.initializeApp(
  options: DefaultFirebaseOptions.currentPlatform,
);
print('Firebase initialized!');
```

---

### 7. Dio Timeout / Connection Error

**Symptoms:**
```
DioException [connection timeout]: The connection timed out
DioException [receive timeout]: The receive timeout exceeded
```

**Solutions:**

**Increase Timeout:**
```dart
// In config.dart or dio_client.dart
static const int connectTimeout = 30000; // 30 seconds
static const int receiveTimeout = 30000; // 30 seconds
```

**Check Backend Server:**
```cmd
# Make sure Laravel server is running
php artisan serve --host=0.0.0.0 --port=8000

# Test endpoint
curl http://localhost:8000/api/health
```

**Check Network:**
```cmd
# Test connectivity
ping localhost
ping 192.168.1.XXX
```

**Update Error Handling:**
```dart
// In your provider/service
try {
  final response = await _dio.post(...);
  return response.data;
} on DioException catch (e) {
  switch (e.type) {
    case DioExceptionType.connectionTimeout:
      throw Exception('Koneksi timeout. Periksa internet Anda.');
    case DioExceptionType.receiveTimeout:
      throw Exception('Respon server timeout.');
    case DioExceptionType.connectionError:
      throw Exception('Tidak dapat terhubung ke server.');
    default:
      throw Exception('Terjadi kesalahan: ${e.message}');
  }
}
```

---

## 🐘 Backend (Laravel)

### 1. Laravel Migration Errors

**Symptoms:**
```
SQLSTATE[42000]: Syntax error or access violation
```

**Solutions:**

**Check Database Connection:**
```cmd
php artisan tinker
>>> DB::connection()->getPdo();
```

**Reset Migration:**
```cmd
# Rollback all migrations
php artisan migrate:rollback --step=10

# Or wipe database and start fresh
php artisan migrate:fresh

# Seed if needed
php artisan db:seed
```

**Fix Specific Migration:**
```cmd
# Check which migrations ran
php artisan migrate:status

# Find problematic migration
# Edit the migration file
# Then retry
php artisan migrate
```

---

### 2. Composer Install Failed

**Symptoms:**
```
Composer dependency resolution failed
```

**Solutions:**

**Clear Composer Cache:**
```cmd
composer clear-cache
composer install --no-cache
```

**Update Composer:**
```cmd
composer self-update
```

**Ignore Platform Requirements (Temporary):**
```cmd
composer install --ignore-platform-reqs
```

**Delete Vendor and Lock:**
```cmd
rm composer.lock
rm -rf vendor/
composer install
```

---

### 3. Redis Connection Failed

**Symptoms:**
```
Predis\ClientException: Connection refused
```

**Solutions:**

**Check Redis Service:**
```cmd
# Windows (via Docker)
docker ps | grep redis

# Linux
sudo systemctl status redis
```

**Start Redis:**
```cmd
# Docker
docker-compose up -d redis

# Linux
sudo systemctl start redis
```

**Test Redis Connection:**
```cmd
php artisan tinker
>>> Redis::ping();
```

**Update .env:**
```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

**Disable Redis (Development):**
```env
# Temporarily use file cache
CACHE_DRIVER=file
SESSION_DRIVER=file
```

---

### 4. Queue Not Working

**Symptoms:**
```
Jobs not being processed
Queue lag increasing
```

**Solutions:**

**Check Queue Table:**
```cmd
php artisan queue:monitor database
```

**Restart Queue Worker:**
```cmd
php artisan queue:restart

# Or run manually
php artisan queue:work --stop-when-empty -v
```

**Check Failed Jobs:**
```cmd
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Or delete failed jobs
php artisan queue:flush
```

**Clear Queue:**
```cmd
php artisan queue:clear
```

---

### 5. Storage Link Not Working

**Symptoms:**
```
File not found: /storage/app/public/...
```

**Solutions:**

**Create Storage Link:**
```cmd
php artisan storage:link
```

**Check Permissions:**
```cmd
# Windows
icacls storage /grant Users:F /T

# Linux
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

**Verify Link:**
```cmd
ls -la public/storage
```

---

### 6. Firebase Notification Not Sending

**Symptoms:**
```
Firebase notification failed
FCM token not found
```

**Solutions:**

**Check Firebase Credentials:**
```cmd
# Verify file exists
ls storage/app/firebase/firebase-credentials.json
```

**Test Firebase Connection:**
```cmd
php artisan tinker
>>> use Kreait\Laravel\Firebase\Facades\Firebase;
>>> Firebase::getDatabase()->getReference('test')->push(['test' => 'value']);
```

**Check FCM Tokens:**
```cmd
php artisan tinker
>>> App\Models\FcmToken::count();
>>> App\Models\FcmToken::first();
```

**Test Notification:**
```cmd
php artisan notification:test
```

---

### 7. S3 Upload Failed

**Symptoms:**
```
S3 upload failed
Access Denied
```

**Solutions:**

**Check S3 Credentials:**
```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=id-jkt-1
AWS_BUCKET=antarkanma
AWS_URL=https://is3.cloudhost.id
AWS_ENDPOINT=https://is3.cloudhost.id
```

**Test S3 Connection:**
```cmd
php artisan tinker
>>> Storage::disk('s3')->put('test.txt', 'test content');
>>> Storage::disk('s3')->get('test.txt');
>>> Storage::disk('s3')->delete('test.txt');
```

**Check Bucket Policy:**
```
1. Login to IDCloudHost Console
2. Go to Object Storage
3. Select bucket "antarkanma"
4. Check bucket policy
5. Ensure write permissions
```

---

## 🌐 Server / Deployment

### 1. 502 Bad Gateway

**Symptoms:**
```
502 Bad Gateway
Nginx error
```

**Solutions:**

**Check PHP-FPM:**
```cmd
sudo systemctl status php8.4-fpm
sudo journalctl -u php8.4-fpm -f
```

**Restart Services:**
```cmd
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

**Check Nginx Config:**
```cmd
sudo nginx -t
sudo cat /etc/nginx/sites-available/antarkanma
```

**Check Logs:**
```cmd
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.4-fpm.log
```

---

### 2. Database Connection Failed (Production)

**Symptoms:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solutions:**

**Check MySQL Service:**
```cmd
sudo systemctl status mysql
sudo systemctl restart mysql
```

**Check Database Host:**
```env
# .env file
DB_HOST=127.0.0.1  # or localhost
DB_PORT=3306
```

**Check MySQL User Permissions:**
```sql
mysql -u root -p
> SELECT User, Host FROM mysql.user;
> SHOW GRANTS FOR 'antarkanma'@'localhost';
```

**Check MySQL Bind Address:**
```cmd
sudo cat /etc/mysql/mysql.conf.d/mysqld.cnf | grep bind-address
```

---

### 3. SSL Certificate Issues

**Symptoms:**
```
SSL_ERROR_RX_RECORD_TOO_LONG
NET::ERR_CERT_AUTHORITY_INVALID
```

**Solutions:**

**Check SSL Certificate:**
```cmd
sudo certbot certificates
```

**Renew Certificate:**
```cmd
sudo certbot renew
```

**Force HTTPS:**
```nginx
# In Nginx config
server {
    listen 80;
    server_name antarkanma.id;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name antarkanma.id;
    # ... rest of config
}
```

---

### 4. High Memory Usage

**Symptoms:**
```
Memory usage > 80%
Server slow
```

**Solutions:**

**Check Memory:**
```cmd
free -h
top -bn1 | head -n 10
```

**Optimize PHP:**
```ini
# In php.ini
memory_limit = 512M
max_execution_time = 300
```

**Restart Services:**
```cmd
sudo systemctl restart php8.4-fpm
sudo systemctl restart mysql
sudo systemctl restart redis
```

**Clear Cache:**
```cmd
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

### 5. Disk Space Full

**Symptoms:**
```
No space left on device
```

**Solutions:**

**Check Disk Usage:**
```cmd
df -h
du -sh /var/www/antarkanma/*
```

**Clear Logs:**
```cmd
# Clear Laravel logs
> storage/logs/laravel.log

# Clear old logs
find storage/logs -name "*.log" -mtime +7 -delete
```

**Clear Cache:**
```cmd
php artisan cache:clear
php artisan view:clear
```

**Clear Old Backups:**
```cmd
# Keep only last 7 backups
ls -t /home/antarkanma/backups/ | tail -n +8 | xargs rm
```

---

## 🔍 Debugging Tools

### Laravel Debugging

**Tinker:**
```cmd
php artisan tinker
>>> App\Models\User::count();
>>> App\Models\Order::latest()->first();
```

**Logs:**
```cmd
tail -f storage/logs/laravel.log
```

**Debugbar:**
```cmd
composer require barryvdh/laravel-debugbar --dev
```

**Telescope (Local):**
```cmd
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

---

### Flutter Debugging

**Debug Mode:**
```dart
print('Debug: $variable');
debugPrint('Detailed: $data');
```

**Flutter DevTools:**
```cmd
flutter pub global activate devtools
flutter pub global run devtools
```

**Network Inspection:**
```
Use Flutter DevTools > Network tab
Or use Chrome DevTools for Flutter Web
```

---

## 📞 Getting Help

### Before Asking for Help

1. [ ] Check error logs
2. [ ] Google the error message
3. [ ] Check documentation
4. [ ] Try to reproduce consistently
5. [ ] Note what you've already tried

### When Asking for Help

Provide:
- Error message (exact text)
- What you were doing when error occurred
- Steps to reproduce
- What you've tried
- Environment (OS, versions)

### Resources

- **Laravel Docs:** https://laravel.com/docs
- **Flutter Docs:** https://docs.flutter.dev
- **Firebase Docs:** https://firebase.google.com/docs
- **Stack Overflow:** https://stackoverflow.com/questions/tagged/laravel+flutter

---

## 📝 Notes

- Update this guide when you solve a new issue
- Screenshot errors for future reference
- Keep a personal troubleshooting journal
- Share solutions with the team

---

**Last Reviewed:** 27 Februari 2026  
**Next Review:** Setelah setiap sprint  
**Owner:** Aswar
