#!/bin/bash

# Stop any running Octane process
php artisan octane:stop

# Clear Laravel cache
php artisan optimize:clear

# Start Octane with FrankenPHP
php artisan octane:start --server=frankenphp --host=127.0.0.1 --port=8000 --workers=20 --task-workers=1
