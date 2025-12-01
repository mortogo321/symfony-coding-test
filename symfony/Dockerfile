# base
FROM php:8.4-cli AS base

RUN apt-get update && apt-get install -y \
    unzip git libzip-dev librabbitmq-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# dev
FROM base AS dev

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

# production
FROM base AS prod

ENV APP_ENV=prod

# Copy source code
COPY symfony/ .
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Copy prod env file
RUN cp .env.prod .env

# Install production dependencies only
RUN composer install --no-interaction --no-dev --optimize-autoloader \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && php bin/console cache:warmup --env=prod

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
