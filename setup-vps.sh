#!/bin/bash

echo "Setting up VPS environment..."

# 1. Copy configuration files
echo "Copying configuration files..."
cp .env.vps .env
cp docker-compose.vps.yml docker-compose.yml

# 2. Create directory for load balancer
echo "Creating load balancer directory..."
mkdir -p loadbalancer
cd loadbalancer

# 3. Copy load balancer files
echo "Setting up load balancer..."
cp ../docker-compose.lb.yml docker-compose.yml
cp ../nginx-lb.conf nginx.conf

# 4. Start main application
echo "Starting main application..."
cd ..
docker-compose up -d

# 5. Start load balancer
echo "Starting load balancer..."
cd loadbalancer
docker-compose up -d

echo "Setup complete!"
echo "Main application running on port 8000"
echo "Load balancer running on port 80"
echo ""
echo "To check status:"
echo "./status-vps.sh"
echo ""
echo "To stop all services:"
echo "./stop-vps.sh"
