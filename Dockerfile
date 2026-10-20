FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite \
    && docker-php-ext-install pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd intl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

RUN composer install --no-interaction --prefer-dist --no-progress --no-dev \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
