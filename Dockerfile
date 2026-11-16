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

# Ensure all storage directories exist and are writable
RUN mkdir -p \
    storage/framework/views \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/logs \
    bootstrap/cache \
    database

# Set writable permissions for storage, cache, and database
RUN chmod -R 777 storage bootstrap/cache database

# Default production environments for standalone demo
ENV APP_NAME="EdOrbit"
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_KEY="base64:5ntNhQ6iHGcYs0JMPFRbYubE0cck/2QlUuq1gRcfwS0="
ENV DB_CONNECTION=sqlite
ENV SESSION_DRIVER=file
ENV CACHE_STORE=file
ENV PORT=8000
EXPOSE 8000

# Start server (ensuring directories exist at runtime)
CMD sh -c "mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs bootstrap/cache && chmod -R 777 storage bootstrap/cache database && php artisan serve --host=0.0.0.0 --port=$PORT"


