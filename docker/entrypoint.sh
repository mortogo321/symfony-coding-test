#!/bin/bash
set -eu

APP_ENV=${APP_ENV:-dev}
echo "Starting in $APP_ENV mode..."

# Copy environment-specific .env file if no .env exists yet
if [ ! -f ".env" ] && [ -f ".env.${APP_ENV}" ]; then
    echo "Copying .env.${APP_ENV} to .env..."
    cp ".env.${APP_ENV}" .env
fi

# Fail fast in prod when APP_SECRET was never set
if [ "${APP_ENV}" = "prod" ] || [ "${APP_ENV}" = "production" ]; then
    if grep -qE '^APP_SECRET=(changeme_in_production|dev_secret_change_me|test_secret_123|)?$' .env 2>/dev/null; then
        echo "ERROR: APP_SECRET must be set to a random value in production (pass -e APP_SECRET=\$(openssl rand -hex 32))." >&2
        exit 1
    fi
# In dev/test only generate a secret when the placeholder is still present
elif [ -f ".env" ] && grep -qE '^APP_SECRET=(dev_secret_change_me|test_secret_123|changeme_in_production|)?$' .env 2>/dev/null; then
    if command -v openssl >/dev/null 2>&1; then
        GENERATED_SECRET=$(openssl rand -hex 32)
        # Portable in-place replace (macOS + GNU sed)
        if sed --version >/dev/null 2>&1; then
            sed -i "s/^APP_SECRET=.*/APP_SECRET=${GENERATED_SECRET}/" .env
        else
            sed -i '' "s/^APP_SECRET=.*/APP_SECRET=${GENERATED_SECRET}/" .env
        fi
        echo "Generated dev APP_SECRET."
    fi
fi

if [ "$APP_ENV" != "prod" ] && [ "$APP_ENV" != "production" ]; then
    # Install dependencies if missing
    if [ ! -d vendor/symfony/messenger ]; then
        echo "Installing dependencies..."
        composer install --no-interaction
    fi

    # Run migrations (sync migration versions if table exists)
    echo "Running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>/dev/null || \
        { php bin/console doctrine:migrations:sync-metadata-storage 2>/dev/null || true; \
          php bin/console doctrine:migrations:version --add --all --no-interaction 2>/dev/null || true; }

    # Setup test database
    echo "Setting up test database..."
    php bin/console doctrine:database:create --env=test --if-not-exists 2>/dev/null || true
    php bin/console doctrine:migrations:migrate --env=test --no-interaction --allow-no-migration 2>/dev/null || true

    # Clear cache
    php bin/console cache:clear
fi

exec "$@"
