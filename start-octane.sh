#!/bin/bash
set -e  # Exit on error

echo "Installing Composer dependencies..."
composer install

echo "Installing NPM packages..."
npm install

echo "Building assets..."
npm run build

echo "Stopping any running Octane process..."
php artisan octane:stop

echo "Clearing and optimizing Laravel cache..."
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo "Rebuilding Laravel cache..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Octane with FrankenPHP..."
sudo -E php artisan octane:start \
    --server=frankenphp \
    --host=127.0.0.1 \
    --port=8000 \
    --workers=1 \
    --task-workers=1 \
    --max-requests=250 \
    --watch 

echo "Updating Caddy configuration..."
if [ -f "Caddyfile" ]; then
    sudo cp Caddyfile /etc/caddy/Caddyfile || { echo "Failed to copy Caddyfile"; exit 1; }
    echo "Restarting Caddy service..."
    sudo systemctl restart caddy || { echo "Failed to restart Caddy"; exit 1; }
    echo "Checking Caddy status..."
    sudo systemctl status caddy || { echo "Failed to get Caddy status"; exit 1; }
else
    echo "Caddyfile not found, skipping Caddy configuration"
fi

echo "Setup complete!"
