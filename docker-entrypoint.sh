#!/bin/sh
set -e

echo "=== Fixing Apache MPM (force prefork only) ==="
a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

echo "=== Apache loaded modules (mpm) ==="
apachectl -M | grep -i mpm || true

echo "=== Enabled MPM symlinks ==="
ls -la /etc/apache2/mods-enabled | grep mpm || true

echo "=== Available MPM modules ==="
ls -la /etc/apache2/mods-available | grep mpm || true

exec apache2-foreground
