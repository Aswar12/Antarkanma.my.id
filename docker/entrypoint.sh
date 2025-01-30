#!/bin/bash
set -e

# Create fresh .env file
cp .env.example .env

# Set required environment variables
cat > .env <<EOL
APP_NAME=${APP_NAME:-Laravel}
APP_ENV=${APP_ENV:-local}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-true}
APP_URL=${APP_URL:-http://localhost}

DB_CONNECTION=mysql
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-laravel}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD}

FIREBASE_PROJECT=app
FIREBASE_PROJECT_ID=antarkanma-98fde
FIREBASE_CREDENTIALS=/app/storage/app/firebase/firebase-credentials.json
FIREBASE_DATABASE_URL=https://antarkanma-98fde.firebaseio.com
FIREBASE_STORAGE_DEFAULT_BUCKET=antarkanma-98fde.appspot.com
EOL

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Wait for database to be ready
until nc -z -v -w30 db 3306
do
  echo "Waiting for database connection..."
  sleep 5
done

# Clear config cache
php artisan config:clear

# Run migrations
php artisan migrate --force

# Install and configure Octane with FrankenPHP
php artisan octane:install --server=frankenphp

# Start FrankenPHP with workers
exec php artisan octane:start --server=frankenphp --workers=4 --max-requests=500
exec php artisan octane:frankenphp --workers