FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    linux-headers

# Install PHP extensions for SQLite, BCMath, Zip
RUN docker-php-ext-install pdo pdo_sqlite bcmath zip

# Copy Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Set default .env if missing
RUN cp .env.example .env 2>/dev/null || true

# Install Composer dependencies (production only)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set writable permissions for storage, cache, and database
RUN chmod -R 777 storage bootstrap/cache database

# Bind port provided by hosting platforms (Render, Railway, Fly, etc.)
ENV PORT=8000
EXPOSE 8000

# Start server
CMD php artisan serve --host=0.0.0.0 --port=$PORT

