#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "Starting API health check loop..."
echo "Press Ctrl+C to stop"
echo "------------------------"

while true; do
    # Get current timestamp
    timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    # Test VPS Backend
    echo -e "\n${timestamp}"
    echo -e "${BLUE}Testing VPS Backend:${NC}"
    response=$(curl -s "https://dev.antarkanmaa.my.id/api/health?server=vps")
    http_code=$?
    if [ $http_code -eq 0 ]; then
        echo -e "${GREEN}✓ VPS Backend is responding${NC}"
        echo -e "${YELLOW}Response:${NC} $response"
    else
        echo -e "${RED}✗ VPS Backend is not responding${NC}"
        echo -e "Error: $response"
    fi
    
    # Test Laptop Backend
    echo -e "\n${BLUE}Testing Laptop Backend:${NC}"
    response=$(curl -s "https://dev.antarkanmaa.my.id/api/health?server=laptop")
    http_code=$?
    if [ $http_code -eq 0 ]; then
        echo -e "${GREEN}✓ Laptop Backend is responding${NC}"
        echo -e "${YELLOW}Response:${NC} $response"
    else
        echo -e "${RED}✗ Laptop Backend is not responding${NC}"
        echo -e "Error: $response"
    fi
    
    # Test Load Balanced Backend
    echo -e "\n${BLUE}Testing Load Balanced Backend:${NC}"
    response=$(curl -s "https://dev.antarkanmaa.my.id/api/health")
    http_code=$?
    if [ $http_code -eq 0 ]; then
        echo -e "${GREEN}✓ Load Balanced Backend is responding${NC}"
        echo -e "${YELLOW}Response:${NC} $response"
    else
        echo -e "${RED}✗ Load Balanced Backend is not responding${NC}"
        echo -e "Error: $response"
    fi
    
    echo "------------------------"
    # Wait 1 second before next check
    sleep 1
done
