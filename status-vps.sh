#!/bin/bash

echo "=== Main Application Status ==="
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep antarkanma-app-vps
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep antarkanma-db-vps
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep antarkanma-redis-vps

echo -e "\n=== Load Balancer Status ==="
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep antarkanma-nginx-lb
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep antarkanma-cloudflared

echo -e "\n=== Health Checks ==="
echo "Main app health:"
curl -s http://localhost:8000/api/health || echo "Not responding"

echo -e "\nLoad balancer health:"
curl -s http://localhost/health || echo "Not responding"

echo -e "\n=== DNS Resolution ==="
echo "dev.antarkanmaa.my.id -> $(dig +short dev.antarkanmaa.my.id)"
echo "antarkanmaa.my.id -> $(dig +short antarkanmaa.my.id)"

echo -e "\n=== Load Balancer Config ==="
echo "Testing nginx config..."
docker exec antarkanma-nginx-lb nginx -t 2>/dev/null || echo "Nginx config test failed"

echo -e "\n=== Database Status ==="
echo "MySQL Master status:"
docker exec antarkanma-db-vps mysql -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" -e "SHOW MASTER STATUS\G"

echo -e "\n=== Redis Status ==="
echo "Redis Master info:"
docker exec antarkanma-redis-vps redis-cli -a "${REDIS_PASSWORD}" info replication

echo -e "\n=== Recent Logs ==="
echo "Main app logs (last 5 lines):"
docker logs --tail 5 antarkanma-app-vps 2>&1

echo -e "\nLoad balancer logs (last 5 lines):"
docker logs --tail 5 antarkanma-nginx-lb 2>&1

echo -e "\nTunnel logs (last 5 lines):"
docker logs --tail 5 antarkanma-cloudflared 2>&1
