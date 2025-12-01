#!/bin/bash
set -e

APP_ENV=${APP_ENV:-development}
echo "Starting in $APP_ENV mode..."

# Generate APP_SECRET
APP_SECRET=$(openssl rand -hex 16)
sed -i "s/^APP_SECRET=.*/APP_SECRET=$APP_SECRET/" .env
echo "Generated APP_SECRET: $APP_SECRET"

if [ "$APP_ENV" != "production" ]; then
    # Install dependencies if missing
    if [ ! -d vendor/symfony/messenger ]; then
        echo "Installing dependencies..."
        composer install --no-interaction
    fi

    # Run migrations (sync migration versions if table exists)
    echo "Running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>/dev/null || \
        php bin/console doctrine:migrations:sync-metadata-storage && \
        php bin/console doctrine:migrations:version --add --all --no-interaction 2>/dev/null || true

    # Setup test database
    echo "Setting up test database..."
    php bin/console doctrine:database:create --env=test --if-not-exists 2>/dev/null || true
    php bin/console doctrine:migrations:migrate --env=test --no-interaction --allow-no-migration 2>/dev/null || true

    # Clear cache
    php bin/console cache:clear
fi

exec "$@"
