#!/bin/bash

# Change to the project directory
cd /home/antarkanma/Antarkanma.my.id

# Start Docker services using the VPS configuration
docker-compose -f docker-compose.vps.yml up -d
