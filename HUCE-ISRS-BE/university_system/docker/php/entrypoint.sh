#!/bin/sh
set -e

# Tạo SQLite DB nếu chưa tồn tại (dùng cho api_clients)
if [ ! -f "database/database.sqlite" ]; then
    echo "[entrypoint] Tạo SQLite database..."
    touch database/database.sqlite
fi

# Cache config/routes
php artisan config:cache
php artisan route:cache

# Chạy migration SQLite (api_clients table)
php artisan migrate --force --no-interaction

echo "[entrypoint] Khởi động PHP-FPM..."
exec "$@"
