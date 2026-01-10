#!/bin/sh
set -e

echo "=== Apache loaded modules (mpm) ==="
apachectl -M | grep -i mpm || true
echo "=== Enabled MPM symlinks ==="
ls -la /etc/apache2/mods-enabled | grep mpm || true
echo "=== Available MPM modules ==="
ls -la /etc/apache2/mods-available | grep mpm || true

exec apache2-foreground