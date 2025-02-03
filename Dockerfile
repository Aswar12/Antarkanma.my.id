FROM dunglas/frankenphp:latest

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set composer environment
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /app

# Copy the entire application first
COPY . .

# Set Firebase env variables for build
ENV GOOGLE_CLOUD_PROJECT=antarkanma-98fde
ENV FIREBASE_PROJECT_ID=antarkanma-98fde
ENV FIREBASE_CREDENTIALS=/app/storage/app/firebase/firebase-credentials.json

# Install composer dependencies
RUN composer install --no-scripts --no-autoloader --ignore-platform-reqs

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Expose port
EXPOSE 8000

# Start PHP-FPM and Laravel Queue Worker
CMD php artisan octane:frankenphp --workers=10 --host=0.0.0.0 --port=8000 --admin-port=2019
