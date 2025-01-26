FROM dunglas/frankenphp

# Install required dependencies
RUN install-php-extensions \
    pcntl \
    opcache \
    intl \
    pdo_mysql \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Configure Laravel Octane
RUN php artisan octane:install --server=frankenphp

# Expose ports
EXPOSE 80 443

# Start FrankenPHP with Octane
CMD php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=80
