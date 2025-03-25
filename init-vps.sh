#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored messages
print_message() {
    echo -e "${GREEN}[+] $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}[!] $1${NC}"
}

print_error() {
    echo -e "${RED}[ERROR] $1${NC}"
}

# Function to check command status
check_status() {
    if [ $? -eq 0 ]; then
        print_message "$1 successful"
    else
        print_error "$1 failed"
        exit 1
    fi
}

# Check if script is run as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root"
    exit 1
fi

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Start installation
print_message "Starting VPS initialization..."

# Update system packages
print_message "Updating system packages..."
apt update && apt upgrade -y
check_status "System update"

# Install required packages
print_message "Installing required packages..."
apt install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    software-properties-common \
    gnupg \
    lsb-release
check_status "Package installation"

# Install Docker if not already installed
if ! command_exists docker; then
    print_message "Installing Docker..."
    
    # Add Docker's official GPG key
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
    check_status "Docker GPG key installation"

    # Set up Docker repository
    echo \
        "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
        $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Update package list and install Docker
    apt update
    apt install -y docker-ce docker-ce-cli containerd.io
    check_status "Docker installation"

    # Enable and start Docker service
    systemctl enable docker
    systemctl start docker
    check_status "Docker service initialization"
else
    print_warning "Docker is already installed"
fi

# Install Docker Compose if not already installed
if ! command_exists docker-compose; then
    print_message "Installing Docker Compose..."
    
    # Install latest version of Docker Compose
    curl -L "https://github.com/docker/compose/releases/download/v2.5.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    check_status "Docker Compose installation"
else
    print_warning "Docker Compose is already installed"
fi

# Create Docker network if it doesn't exist
if ! docker network ls | grep -q "antarkanma-network"; then
    print_message "Creating Docker network..."
    docker network create antarkanma-network
    check_status "Docker network creation"
else
    print_warning "Docker network 'antarkanma-network' already exists"
fi

# Install Cloudflared if not already installed
if ! command_exists cloudflared; then
    print_message "Installing Cloudflared..."
    
    # Add Cloudflared GPG key
    curl -fsSL https://pkg.cloudflare.com/cloudflare-main.gpg | tee /usr/share/keyrings/cloudflare-main.gpg >/dev/null
    check_status "Cloudflared GPG key installation"

    # Add Cloudflared repository
    echo 'deb [signed-by=/usr/share/keyrings/cloudflare-main.gpg] https://pkg.cloudflare.com/cloudflared jammy main' | tee /etc/apt/sources.list.d/cloudflared.list
    
    # Update package list and install Cloudflared
    apt update && apt install -y cloudflared
    check_status "Cloudflared installation"

    # Create directory for Cloudflared config
    mkdir -p /etc/cloudflared

    # Copy config files if they exist
    if [ -f "cloudflared/config.yml" ] && [ -f "cloudflared/credentials.json" ]; then
        cp cloudflared/config.yml /etc/cloudflared/config.yml
        cp cloudflared/credentials.json /etc/cloudflared/credentials.json
        check_status "Cloudflared configuration files copy"
    else
        print_warning "Cloudflared config files not found in ./cloudflared directory"
        print_warning "Please manually configure Cloudflared after installation"
    fi

    # Install and start Cloudflared service
    cloudflared service install
    systemctl start cloudflared
    systemctl enable cloudflared
    check_status "Cloudflared service initialization"
else
    print_warning "Cloudflared is already installed"
fi

# Print installation summary
print_message "\nInstallation Summary:"
echo -e "${GREEN}----------------------------------------${NC}"
echo -e "Docker Version: $(docker --version)"
echo -e "Docker Compose Version: $(docker-compose --version)"
echo -e "Cloudflared Version: $(cloudflared --version)"
echo -e "${GREEN}----------------------------------------${NC}"

print_message "VPS initialization completed successfully!"
print_message "You can now run setup-vps.sh to configure your application"
