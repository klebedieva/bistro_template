# PowerShell script to run migrations on Render PostgreSQL
# Usage: .\migrate-render.ps1

$env:DATABASE_URL = "postgresql://bistro_wqfb_user:OeiuamyoeIGUq5BAbsJSpdxzHSOwCQRd@dpg-d5h4kq94tr6s739bo5v0-a.virginia-postgres.render.com:5432/bistro_wqfb"
$env:APP_ENV = "prod"
$env:APP_DEBUG = "0"

Write-Host "Connecting to Render PostgreSQL database..." -ForegroundColor Cyan
Write-Host "Database: bistro_wqfb" -ForegroundColor Gray
Write-Host "Host: dpg-d5h4kq94tr6s739bo5v0-a.virginia-postgres.render.com`n" -ForegroundColor Gray

Write-Host "Checking migration status..." -ForegroundColor Cyan
php bin/console doctrine:migrations:status

Write-Host "`nRunning migrations..." -ForegroundColor Cyan
php bin/console doctrine:migrations:migrate --no-interaction

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✓ Migrations completed successfully!" -ForegroundColor Green
} else {
    Write-Host "`n✗ Migrations failed. Check errors above." -ForegroundColor Red
}
