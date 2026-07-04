#!/bin/sh
set -e

# Chờ MySQL sẵn sàng
echo "[entrypoint] Chờ MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
  echo "[entrypoint] MySQL chưa sẵn sàng, thử lại sau 2s..."
  sleep 2
done
echo "[entrypoint] MySQL đã sẵn sàng."

# Tạo APP_KEY nếu chưa có
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
  echo "[entrypoint] Tạo APP_KEY mới..."
  php artisan key:generate --force
fi

# Cache config/routes/views để tăng hiệu năng
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Chạy migration (an toàn: chỉ chạy migration mới)
php artisan migrate --force --no-interaction

echo "[entrypoint] Khởi động PHP-FPM..."
exec "$@"
