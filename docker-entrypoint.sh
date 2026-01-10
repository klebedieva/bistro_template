#!/bin/bash
set -e

# Fix MPM: ensure only mpm_prefork is enabled
echo "Fixing Apache MPM configuration..."
a2dismod mpm_event mpm_worker 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_event.* 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_worker.* 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Ensure var directories exist and have correct permissions
echo "Setting up Symfony directories..."
mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Check if Composer dependencies are installed
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "WARNING: Composer dependencies not found! Installing..."
    cd /var/www/html
    composer install --no-dev --optimize-autoloader --no-interaction || echo "Composer install failed, but continuing..."
fi

# Check critical environment variables
if [ -z "$APP_SECRET" ]; then
    echo "WARNING: APP_SECRET environment variable is not set!"
fi

if [ -z "$DATABASE_URL" ]; then
    echo "WARNING: DATABASE_URL environment variable is not set!"
fi

# Prepare Symfony cache
echo "Preparing Symfony cache..."
# Only clear cache if DATABASE_URL is set (to avoid SSL errors)
if [ -n "$DATABASE_URL" ]; then
    echo "Clearing cache (DB configured)..."
    php bin/console cache:clear --env=prod --no-debug --no-interaction || echo "Cache clear had issues, but continuing..."
else
    echo "Skipping cache clear (DATABASE_URL not set - will be done on first request)"
fi
# Warmup cache (works without DB for basic Symfony operations)
echo "Warming up cache..."
php bin/console cache:warmup --env=prod --no-debug --no-interaction || echo "Cache warmup completed (warnings are OK)"

# Test PHP syntax of index.php
echo "Testing PHP syntax..."
php -l /var/www/html/public/index.php || echo "WARNING: Syntax error in index.php!"

# Check if vendor directory exists
if [ ! -d "/var/www/html/vendor" ]; then
    echo "ERROR: vendor directory not found!"
fi

echo "Starting Apache..."
exec apache2-foreground
