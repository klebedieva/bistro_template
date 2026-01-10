#!/bin/bash
set -e

# Ensure var directories exist and have correct permissions
mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Prepare Symfony cache (clear first to ensure fresh config)
php bin/console cache:clear --env=prod --no-debug --no-interaction || true

# Run database migrations if DATABASE_URL is set
if [ -n "$DATABASE_URL" ]; then
    echo "Running database migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction || {
        echo "Warning: Database migrations failed, but continuing startup..."
    }
fi

# Warm up cache after migrations
php bin/console cache:warmup --env=prod --no-debug --no-interaction || true

exec apache2-foreground
