# Use PHP 8.2 with FPM
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libonig-dev \
    libzip-dev \
    curl \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Laravel application
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 8000 (Laravel's dev server)
EXPOSE 8000

# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=8000
