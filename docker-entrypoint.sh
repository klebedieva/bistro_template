#!/bin/bash
set -e

# Ensure var directories exist and have correct permissions
mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Prepare Symfony cache
php bin/console cache:clear --env=prod --no-debug --no-interaction || true
php bin/console cache:warmup --env=prod --no-debug --no-interaction || true

exec apache2-foreground
