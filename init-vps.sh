#!/bin/bash

echo "Initializing VPS environment..."

# 1. Make scripts executable
echo "Making scripts executable..."
chmod +x setup-vps.sh stop-vps.sh status-vps.sh backup-vps.sh restore-vps.sh

# 2. Create required directories
echo "Creating required directories..."
mkdir -p storage/app/firebase
mkdir -p storage/certs
mkdir -p backups
mkdir -p loadbalancer

# 3. Check required files
echo "Checking required files..."
REQUIRED_FILES=(
    ".env.vps"
    "docker-compose.vps.yml"
    "docker-compose.lb.yml"
    "nginx-lb.conf"
    "mysql-master.cnf"
    "redis-master.conf"
    "storage/app/firebase/firebase-credentials.json"
)

MISSING_FILES=0
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo "Missing required file: $file"
        MISSING_FILES=1
    fi
done

if [ $MISSING_FILES -eq 1 ]; then
    echo "Please provide all required files before proceeding."
    exit 1
fi

# 4. Check Docker installation
echo "Checking Docker installation..."
if ! command -v docker &> /dev/null; then
    echo "Docker is not installed. Installing Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
    echo "Please log out and log back in to use Docker without sudo."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "Docker Compose is not installed. Installing Docker Compose..."
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
fi

# 5. Install required packages
echo "Installing required packages..."
sudo apt-get update
sudo apt-get install -y dnsutils curl

# 6. Create first backup directory
echo "Creating backup directory structure..."
mkdir -p backups/$(date +"%Y%m")

echo "Initialization complete!"
echo ""
echo "Next steps:"
echo "1. Review and update .env.vps with your settings"
echo "2. Run ./setup-vps.sh to start all services"
echo "3. Run ./status-vps.sh to verify everything is running"
echo ""
echo "Available commands:"
echo "- ./setup-vps.sh: Start all services"
echo "- ./stop-vps.sh: Stop all services"
echo "- ./status-vps.sh: Check service status"
echo "- ./backup-vps.sh: Create backup"
echo "- ./restore-vps.sh: Restore from backup"
