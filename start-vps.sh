#!/bin/bash

echo "Starting main application..."
cp .env.vps .env
cp docker-compose.vps.yml docker-compose.yml
docker-compose up -d

echo "Starting load balancer..."
mkdir -p loadbalancer
cd loadbalancer
cp ../.env.lb .env
cp ../docker-compose.lb.yml docker-compose.yml
cp ../nginx-lb.conf nginx.conf
docker-compose up -d

echo "All services started!"
echo "Main app running on dev.antarkanmaa.my.id"
echo "Load balancer running on antarkanmaa.my.id"
