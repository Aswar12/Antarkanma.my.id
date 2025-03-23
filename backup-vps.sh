#!/bin/bash

# Set timestamp for backup files
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="backups"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

echo "Starting VPS backup process..."

# 1. Backup MySQL database
echo "Backing up MySQL database..."
docker exec antarkanma-db-vps mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql"

# 2. Backup Redis data
echo "Backing up Redis data..."
docker exec antarkanma-redis-vps redis-cli SAVE
docker cp antarkanma-redis-vps:/data/dump.rdb "$BACKUP_DIR/redis_backup_$TIMESTAMP.rdb"

# 3. Backup configuration files
echo "Backing up configuration files..."
mkdir -p "$BACKUP_DIR/config_$TIMESTAMP"
cp .env "$BACKUP_DIR/config_$TIMESTAMP/"
cp .env.vps "$BACKUP_DIR/config_$TIMESTAMP/"
cp docker-compose.yml "$BACKUP_DIR/config_$TIMESTAMP/"
cp docker-compose.vps.yml "$BACKUP_DIR/config_$TIMESTAMP/"
cp docker-compose.lb.yml "$BACKUP_DIR/config_$TIMESTAMP/"
cp nginx-lb.conf "$BACKUP_DIR/config_$TIMESTAMP/"
cp mysql-master.cnf "$BACKUP_DIR/config_$TIMESTAMP/"
cp redis-master.conf "$BACKUP_DIR/config_$TIMESTAMP/"

# 4. Create archive
echo "Creating backup archive..."
cd $BACKUP_DIR
tar -czf "full_backup_$TIMESTAMP.tar.gz" \
    "db_backup_$TIMESTAMP.sql" \
    "redis_backup_$TIMESTAMP.rdb" \
    "config_$TIMESTAMP"

# 5. Cleanup temporary files
echo "Cleaning up temporary files..."
rm "db_backup_$TIMESTAMP.sql"
rm "redis_backup_$TIMESTAMP.rdb"
rm -rf "config_$TIMESTAMP"
cd ..

echo "Backup complete!"
echo "Backup files are located in: $BACKUP_DIR/full_backup_$TIMESTAMP.tar.gz"
echo ""
echo "To restore from this backup:"
echo "./restore-vps.sh $BACKUP_DIR/full_backup_$TIMESTAMP.tar.gz"
