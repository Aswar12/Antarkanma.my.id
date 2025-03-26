#!/bin/bash

echo "Setting up laptop as replica server..."

# Function to check if VPS_HOST is set
check_vps_host() {
    if [ -z "${VPS_HOST}" ]; then
        echo "Enter VPS IP address:"
        read VPS_HOST
        export VPS_HOST
    fi

    # Validate IP address
    if [[ ! $VPS_HOST =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        error_exit "Invalid IP address format"
    fi
}

# Function to display error message and exit
error_exit() {
    echo "Error: $1"
    exit 1
}

# Function to display error message and exit
error_exit() {
    echo "Error: $1"
    exit 1
}

# Check prerequisites
if [ ! -f .env.laptop ]; then
    error_exit ".env.laptop file not found"
fi

if ! command -v docker &> /dev/null; then
    error_exit "Docker is not installed"
fi

if ! command -v docker compose &> /dev/null; then
    error_exit "Docker Compose is not installed"
fi

# Check VPS host
check_vps_host

# Stop existing containers if any
echo "Stopping existing containers..."
docker compose -f docker-compose.laptop.yml down

# Remove existing volumes
echo "Removing existing volumes..."
docker volume prune -f

# Copy laptop environment and replace VPS_HOST
echo "Setting up environment..."
cp .env.laptop .env
sed -i "s/\${VPS_HOST}/$VPS_HOST/g" .env

# Update Redis slave configuration
echo "Configuring Redis replication..."
sed -i "s/# replicaof will be configured by setup script/replicaof $VPS_HOST 6379/" redis-slave.conf

# Start the containers with laptop configuration
echo "Starting containers..."
docker compose -f docker-compose.laptop.yml up -d

# Wait for services to be ready
echo "Waiting for services to be ready..."
sleep 30

# Configure MySQL replication
echo "Configuring MySQL replication..."
docker compose -f docker-compose.laptop.yml exec db mysql -u root -p"${DB_ROOT_PASSWORD}" -e "
STOP SLAVE;
RESET SLAVE;
CHANGE MASTER TO
    MASTER_HOST='$VPS_HOST',
    MASTER_PORT=3306,
    MASTER_USER='${MYSQL_REPLICATION_USER}',
    MASTER_PASSWORD='${MYSQL_REPLICATION_PASSWORD}',
    MASTER_AUTO_POSITION=1;
START SLAVE;
"

# Install dependencies and build assets
echo "Installing dependencies..."
docker compose -f docker-compose.laptop.yml exec app composer install
docker compose -f docker-compose.laptop.yml exec app npm install
docker compose -f docker-compose.laptop.yml exec app npm run build

echo "Checking replication status..."

# Check MySQL replication
echo "MySQL Replication Status:"
docker compose -f docker-compose.laptop.yml exec db mysql -u root -p"${DB_ROOT_PASSWORD}" -e "SHOW SLAVE STATUS\G"

# Check Redis replication
echo "Redis Replication Status:"
docker compose -f docker-compose.laptop.yml exec redis redis-cli -a "${REDIS_PASSWORD}" INFO replication

echo "Setup complete! Your laptop is now configured as a replica server."
echo "To verify everything is working:"
echo "1. MySQL replication should show 'Slave_IO_Running: Yes' and 'Slave_SQL_Running: Yes'"
echo "2. Redis replication should show 'role:slave' and 'master_link_status:up'"
echo "3. Access the application at: ${APP_URL}"
