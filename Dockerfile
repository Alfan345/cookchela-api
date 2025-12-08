# Gunakan image PHP + composer + extensions yang sesuai dengan Laravel
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip exif pcntl gd

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app code
WORKDIR /var/www
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www/storage

# Copy the default Laravel config for production
COPY .env.example .env

# Expose port
EXPOSE 8080

# Start Laravel using PHP's built-in server for simple deployment
CMD php artisan serve --host=0.0.0.0 --port=8080