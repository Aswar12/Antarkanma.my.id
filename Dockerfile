FROM dunglas/frankenphp

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    netcat-traditional

# Install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    intl && \
    pecl install redis && \
    docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy configuration files
COPY docker/php/conf.d/custom.ini $PHP_INI_DIR/conf.d/
COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Copy application files
COPY . .

# Create storage directory for Firebase credentials if needed
RUN mkdir -p /app/storage/app/firebase && \
    chown -R www-data:www-data /app/storage/app/firebase && \
    chmod -R 775 /app/storage/app/firebase

# Set environment variables for build
ENV FIREBASE_PROJECT=app \
    FIREBASE_PROJECT_ID=antarkanma-98fde \
    FIREBASE_CREDENTIALS=/app/storage/app/firebase/firebase-credentials.json \
    FIREBASE_DATABASE_URL=https://antarkanma-98fde.firebaseio.com \
    FIREBASE_STORAGE_DEFAULT_BUCKET=antarkanma-98fde.appspot.com

# Create directory for Firebase credentials
RUN mkdir -p /app/storage/app/firebase && \
    touch /app/storage/app/firebase/firebase-credentials.json && \
    chown -R www-data:www-data /app/storage/app/firebase && \
    chmod -R 775 /app/storage/app/firebase


# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache \
    && chmod -R 755 /app/storage/app/firebase

# Create log directory for Caddy
RUN mkdir -p /var/log/caddy && chown -R www-data:www-data /var/log/caddy

# FrankenPHP worker mode configuration
ENV FRANKENPHP_CONFIG="worker"

# Expose ports
EXPOSE 80 443 8080

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint"]
