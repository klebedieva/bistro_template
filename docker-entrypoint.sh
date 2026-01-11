#!/bin/bash
set -e

# Ensure var directories exist and have correct permissions
mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    /var/www/html/var/cache/prod/pools \
    /var/www/html/var/cache/prod/asset_mapper
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Prepare Symfony cache (clear first to ensure fresh config)
php bin/console cache:clear --env=prod --no-debug --no-interaction || true

# Fix permissions again after cache:clear (it may create new directories)
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Run database migrations if DATABASE_URL is set
if [ -n "$DATABASE_URL" ]; then
    echo "Running database migrations..."
    echo "DATABASE_URL is set, checking connection..."
    php bin/console doctrine:migrations:status --no-interaction || true
    echo "Executing migrations..."
    if php bin/console doctrine:migrations:migrate --no-interaction 2>&1; then
        echo "✓ Migrations completed successfully"
    else
        MIGRATION_EXIT_CODE=$?
        echo "✗ ERROR: Database migrations failed with exit code: $MIGRATION_EXIT_CODE"
        echo "This may prevent the application from working correctly."
        echo "Continuing startup, but please check the error above."
    fi
fi

# Fix permissions again after migrations (they may create cache entries)
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Load fixtures if LOAD_FIXTURES environment variable is set
if [ -n "$LOAD_FIXTURES" ]; then
    echo "Loading fixtures..."
    # Load users first (admin and moderator)
    php bin/console doctrine:fixtures:load --group=users --append --no-interaction || true
    # Load allergens (required by menu fixtures)
    php bin/console doctrine:fixtures:load --group=allergens --append --no-interaction || true
    # Load menu fixtures (depends on allergens, badges, tags)
    php bin/console doctrine:fixtures:load --group=menu --append --no-interaction || true
    echo "✓ Fixtures loaded"
fi

# Warm up cache after migrations
php bin/console cache:warmup --env=prod --no-debug --no-interaction || true

# Ensure asset_mapper directory exists (may be created during warmup)
mkdir -p /var/www/html/var/cache/prod/asset_mapper

# Final permissions fix before starting Apache (ensure everything is writable)
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var
find /var/www/html/var -type d -exec chmod 775 {} \;
find /var/www/html/var -type f -exec chmod 664 {} \;

exec apache2-foreground
