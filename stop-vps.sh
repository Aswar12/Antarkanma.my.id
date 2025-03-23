#!/bin/bash

echo "Stopping VPS services..."

# 1. Stop load balancer
echo "Stopping load balancer..."
cd loadbalancer
docker-compose down
cd ..

# 2. Stop main application
echo "Stopping main application..."
docker-compose down

echo "All services stopped!"
echo "To start services again:"
echo "./setup-vps.sh"
