#!/bin/bash

if [ -z "$1" ]; then
    echo "Usage: ./restore-vps.sh <backup_file.tar.gz>"
    exit 1
fi

BACKUP_FILE=$1
TEMP_DIR="temp_restore"

echo "Starting VPS restore process..."

# 1. Stop all services
echo "Stopping all services..."
./stop-vps.sh

# 2. Extract backup
echo "Extracting backup..."
mkdir -p $TEMP_DIR
tar -xzf $BACKUP_FILE -C $TEMP_DIR

# 3. Restore configuration files
echo "Restoring configuration files..."
cp $TEMP_DIR/config_*/docker-compose.yml ./
cp $TEMP_DIR/config_*/docker-compose.vps.yml ./
cp $TEMP_DIR/config_*/docker-compose.lb.yml ./
cp $TEMP_DIR/config_*/nginx-lb.conf ./
cp $TEMP_DIR/config_*/mysql-master.cnf ./
cp $TEMP_DIR/config_*/redis-master.conf ./
cp $TEMP_DIR/config_*/.env ./
cp $TEMP_DIR/config_*/.env.vps ./

# 4. Start services
echo "Starting services..."
./setup-vps.sh

# 5. Restore database
echo "Restoring MySQL database..."
cat $TEMP_DIR/db_backup_*.sql | docker exec -i antarkanma-db-vps mysql -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE}

# 6. Restore Redis data
echo "Restoring Redis data..."
docker cp $TEMP_DIR/redis_backup_*.rdb antarkanma-redis-vps:/data/dump.rdb
docker restart antarkanma-redis-vps

# 7. Cleanup
echo "Cleaning up temporary files..."
rm -rf $TEMP_DIR

echo "Restore complete!"
echo "Running status check..."
./status-vps.sh

echo ""
echo "Please verify that all services are running correctly."
echo "If you encounter any issues:"
echo "1. Check the logs with: docker logs antarkanma-app-vps"
echo "2. Check database connection"
echo "3. Verify Redis data"
