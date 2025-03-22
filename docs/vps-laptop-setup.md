# VPS and Laptop Server Setup Guide

## 1. Server Architecture

### VPS (Primary Server)
```yaml
Components:
- Laravel Application
- MySQL Master Database
- Redis Master
- Cloudflare Tunnel

Configuration:
docker-compose.yml (VPS):
  services:
    app:
      # Your existing Laravel app config
    db:
      image: mysql:8.0
      command: |
        --server-id=1
        --log-bin=mysql-bin
        --binlog-do-db=antarkanma
    cloudflared:
      image: cloudflare/cloudflared
      command: tunnel run
```

### Laptop (Secondary Server)
```yaml
Components:
- Laravel Application (Read-only)
- MySQL Slave Database
- Redis Replica
- Cloudflare Tunnel

Configuration:
docker-compose.yml (Laptop):
  services:
    app:
      # Similar to VPS but with read-only config
    db:
      image: mysql:8.0
      command: |
        --server-id=2
        --relay-log=relay-bin
        --read-only=1
    cloudflared:
      image: cloudflare/cloudflared
      command: tunnel run
```

## 2. Load Balancing Setup

### Cloudflare Configuration
```yaml
Load Balancer Rules:
- Primary (VPS):
  - Weight: 70%
  - Priority: 1
  - Health Check: /api/health
  
- Secondary (Laptop):
  - Weight: 30%
  - Priority: 2
  - Health Check: /api/health

Health Checks:
- Interval: 60s
- Timeout: 5s
- Retries: 3
```

## 3. Database Replication

### Master (VPS) Configuration
```sql
-- /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
server-id = 1
log_bin = mysql-bin
binlog_do_db = antarkanma
```

### Slave (Laptop) Configuration
```sql
-- /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
server-id = 2
relay_log = relay-bin
read_only = 1
```

## 4. Security Configuration

### Firewall Rules
```bash
# VPS
- Allow 80/443 (HTTPS)
- Allow 3306 (MySQL) only from laptop IP
- Allow 6379 (Redis) only from laptop IP

# Laptop
- Allow 80/443 (HTTPS)
- Allow 3306 (MySQL) for replication
- Allow 6379 (Redis) for replication
```

### SSL/TLS
```yaml
Cloudflare SSL:
- Full (strict) mode
- Origin server certificates required
- Auto-renewal enabled
```

## 5. Implementation Steps

1. VPS Setup:
```bash
# 1. Deploy application
docker-compose up -d

# 2. Configure MySQL master
mysql> CREATE USER 'repl_user'@'%' IDENTIFIED BY 'password';
mysql> GRANT REPLICATION SLAVE ON *.* TO 'repl_user'@'%';

# 3. Get master status
mysql> SHOW MASTER STATUS;
```

2. Laptop Setup:
```bash
# 1. Deploy application
docker-compose up -d

# 2. Configure MySQL slave
mysql> CHANGE MASTER TO
  MASTER_HOST='vps_ip',
  MASTER_USER='repl_user',
  MASTER_PASSWORD='password',
  MASTER_LOG_FILE='mysql-bin.000001',
  MASTER_LOG_POS=123;

mysql> START SLAVE;
```

3. Cloudflare Setup:
```bash
# 1. Create tunnels
cloudflared tunnel create vps-tunnel
cloudflared tunnel create laptop-tunnel

# 2. Configure DNS
cloudflared tunnel route dns vps-tunnel your-domain.com
cloudflared tunnel route dns laptop-tunnel laptop.your-domain.com

# 3. Configure load balancer
- Create health checks
- Add both origins
- Set up load balancing rules
```

## 6. Monitoring

### Health Checks
```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'server' => gethostname()
    ]);
});
```

### Metrics to Monitor:
- Server response time
- Database replication lag
- Redis replication status
- Error rates
- Resource usage (CPU, Memory, Disk)
