#!/bin/bash

npm install

npm run build  


# Stop any running Octane process
php artisan octane:stop

# Clear Laravel cache
php artisan optimize:clear



# Start Octane with FrankenPHP
sudo php artisan octane:start --server=frankenphp --host=127.0.0.1 --port=8000 --workers=10 

sudo cp Caddyfile /etc/caddy/Caddyfile && sudo systemctl restart php8.4-fpm && sudo systemctl restart caddy && sudo systemctl status caddy

