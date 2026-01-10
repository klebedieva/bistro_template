#!/bin/bash
set -e

# Fix MPM: ensure only mpm_prefork is enabled
echo "Fixing Apache MPM configuration..."
a2dismod mpm_event mpm_worker 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_event.* 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_worker.* 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

echo "Starting Apache..."
exec apache2-foreground
