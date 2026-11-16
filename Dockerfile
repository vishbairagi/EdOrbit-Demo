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
    git

# Install PHP extensions for SQLite and Laravel
RUN docker-php-ext-install pdo pdo_sqlite bcmath

WORKDIR /var/www/html

# Copy application files
COPY . .

# Set writable permissions for storage and database
RUN chmod -R 777 storage bootstrap/cache database

# Bind port provided by hosting platforms (Render, Railway, Fly, etc.)
ENV PORT=8000
EXPOSE 8000

# Start server
CMD php artisan serve --host=0.0.0.0 --port=$PORT

