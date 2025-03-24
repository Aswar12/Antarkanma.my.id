#!/bin/bash

echo "Stopping all VPS services..."

# Stop all services
docker-compose down

echo "All services stopped!"
echo "Services stopped:"
echo "- Load balancer (port 80)"
echo "- Application (port 8000)"
echo "- Database"
echo "- Redis"
echo "- Cloudflared"
echo "To start services again:"
echo "./setup-vps.sh"
