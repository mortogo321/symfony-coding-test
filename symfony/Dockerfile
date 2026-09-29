# base — pinned minor, bookworm variant for reproducible builds
FROM php:8.4-cli-bookworm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip git libzip-dev librabbitmq-dev curl \
    && docker-php-ext-install pdo pdo_mysql zip opcache \
    && pecl install amqp \
    && docker-php-ext-enable amqp opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Run as non-root
RUN useradd -m -u 10001 appuser

# dev — source mounted via compose volume, dependencies installed at runtime
FROM base AS dev

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS http://localhost:8000/api/health || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

# production — self-contained, no dev deps, warmed cache
FROM base AS prod

ENV APP_ENV=prod

# Copy source code (build context is repo root)
COPY symfony/ .
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Use prod env as base; APP_SECRET MUST be overridden at runtime
RUN cp .env.prod .env \
    && composer install --no-interaction --no-dev --optimize-autoloader --no-scripts \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R appuser:appuser /var/www/html \
    && php bin/console cache:warmup --env=prod

USER appuser

EXPOSE 8000

HEALTHCHECK --interval=15s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://localhost:8000/api/health || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
