#!/bin/bash
set -e

echo "=== Starting AfricaERP ==="
echo "PORT: ${PORT:-8000}"
echo "APP_ENV: $APP_ENV"

# Ensure storage dirs exist and are writable
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Rebuild config cache with live env vars (DB_HOST etc. are injected at runtime)
php artisan config:clear 2>/dev/null || true
php artisan cache:clear  2>/dev/null || true
php artisan config:cache
php artisan route:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Ensure super admin account exists
php artisan erp:ensure-super-admin

# Create storage symlink
php artisan storage:link --force 2>/dev/null || true

echo "Starting server on 0.0.0.0:${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
