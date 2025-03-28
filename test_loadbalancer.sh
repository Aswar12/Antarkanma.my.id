#!/bin/bash

echo "Testing load balancer distribution to dev.antarkanmaa.my.id..."

# Test VPS server
echo -e "\nTesting VPS server (default):"
wget -qS --header="Host: dev.antarkanmaa.my.id" \
    --header="Accept: application/json" \
    -O - https://dev.antarkanmaa.my.id/api/health 2>&1 \
    | grep -E "HTTP/|^  Server:|^  X-Server:|^  X-Powered-By:|^$|{.*}"
echo "----------------------------------------"

# Test Laptop server
echo -e "\nTesting Laptop server:"
wget -qS --header="Host: dev.antarkanmaa.my.id" \
    --header="Accept: application/json" \
    -O - "https://dev.antarkanmaa.my.id/api/health?server=laptop" 2>&1 \
    | grep -E "HTTP/|^  Server:|^  X-Server:|^  X-Powered-By:|^$|{.*}"
echo "----------------------------------------"

echo "Test completed."
