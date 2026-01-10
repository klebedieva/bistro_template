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

# Clear cache if needed (optional - uncomment if cache issues occur)
# php bin/console cache:clear --env=prod --no-debug || true

echo "Starting Apache..."
exec apache2-foreground
