FROM php:8.2-fpm

RUN apt-get update && apt-get install -y unzip git libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN composer install --no-interaction

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]