FROM php:8.2-fpm

WORKDIR /var/www/html

# Install system dependencies including PostgreSQL
RUN apt-get update && apt-get install -y \
    git unzip libonig-dev libzip-dev curl zip \
    libpng-dev libjpeg-dev libfreetype6-dev libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Laravel code
COPY . .

# Install PHP dependencies, skip scripts to avoid PGSQL constant error
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Now run package discovery after PHP extensions are loaded
RUN php artisan package:discover

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
