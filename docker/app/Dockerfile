FROM php:8.4-fpm-alpine

WORKDIR /var/www

# System dependencies
RUN apk add --no-cache \
    bash curl git unzip libpng-dev libjpeg-turbo-dev libwebp-dev \
    freetype-dev libzip-dev icu-dev oniguruma-dev postgresql-dev \
    supervisor nodejs npm

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install \
    pdo pdo_pgsql pgsql \
    gd zip intl bcmath mbstring opcache pcntl

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# PHP config
COPY docker/app/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/app/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction \
 && npm ci && npm run build

# Permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
 && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
