#!/bin/bash

echo "Setting up VPS environment..."

# 1. Copy configuration files
echo "Copying configuration files..."
cp .env.vps .env
cp docker-compose.vps.yml docker-compose.yml

# 2. Start all services
echo "Starting all services..."
docker-compose up -d

echo "Setup complete!"
echo "Load balancer running on port 80"
echo "Application running on port 8000"
echo ""
echo "To check status:"
echo "./status-vps.sh"
echo ""
echo "To stop all services:"
echo "./stop-vps.sh"
