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

    # Generate migrations if schema changed
    echo "Checking for schema changes..."
    php bin/console doctrine:migrations:diff --no-interaction --allow-empty-diff 2>/dev/null || true

    # Run migrations
    echo "Running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

    # Setup test database
    echo "Setting up test database..."
    php bin/console doctrine:database:create --env=test --if-not-exists
    php bin/console doctrine:migrations:migrate --env=test --no-interaction --allow-no-migration

    # Clear cache
    php bin/console cache:clear
fi

exec "$@"
