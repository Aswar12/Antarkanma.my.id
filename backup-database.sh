#!/bin/bash

# Set directories and files
PROJECT_DIR="/home/blacky12/antarkanma"
BACKUP_DIR="/home/blacky12/database_backups"
BACKUP_FILE="$BACKUP_DIR/antarkanma_backup.sql"
LOG_FILE="$BACKUP_DIR/backup.log"

# Navigate to project directory
cd $PROJECT_DIR

# Load environment variables
source .env

# Create backup
docker compose exec -T db mysqldump -u root -p${DB_ROOT_PASSWORD} --databases antarkanma > $BACKUP_FILE

# Add timestamp to backup file
echo "# Backup created on $(date)" >> $BACKUP_FILE

# Log the backup
echo "[$(date)] Database backup completed successfully" >> $LOG_FILE

# Set permissions
chmod 644 $BACKUP_FILE

# Print status
echo "Database backup completed. Backup stored in: $BACKUP_FILE"
echo "You can check the backup file with: cat $BACKUP_FILE"
