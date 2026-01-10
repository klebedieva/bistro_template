# Script to check PostgreSQL extension in PHP
# Run this after installing pdo_pgsql extension

Write-Host "Checking PHP PostgreSQL extensions..." -ForegroundColor Cyan
php -m | Select-String -Pattern "pdo_pgsql|pgsql"

if ($LASTEXITCODE -ne 0) {
    Write-Host "`nExtensions not found. Please install pdo_pgsql extension first." -ForegroundColor Yellow
    Write-Host "See INSTALL_POSTGRESQL_PHP.md for instructions." -ForegroundColor Yellow
}
