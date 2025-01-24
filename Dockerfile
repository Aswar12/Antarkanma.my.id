FROM dunglas/frankenphp

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache \
    intl \
    zip

# Set working directory
WORKDIR /app

# Copy composer files and Firebase credentials
COPY composer.json composer.lock antarkanma-98fde-firebase-adminsdk-2tqx3-25b58dd15e.json ./

# Set Firebase environment variables
ENV FIREBASE_CREDENTIALS=/app/antarkanma-98fde-firebase-adminsdk-2tqx3-25b58dd15e.json
ENV FIREBASE_PROJECT_ID=antarkanma-98fde

# Install composer dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage

ENV FRANKENPHP_CONFIG="worker ./public/frankenphp-worker.php" 
