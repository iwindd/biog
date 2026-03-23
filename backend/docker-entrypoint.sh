#!/bin/bash
set -e

# Fix runtime directory permissions on container start
mkdir -p /app/backend/runtime/export-jobs
chown -R www-data:www-data /app/backend/runtime
chmod -R 755 /app/backend/runtime

# รัน composer install ถ้ายังไม่มี vendor (แก้ปัญหา Internal Server Error จาก require autoload.php)
if [ ! -f /app/vendor/autoload.php ]; then
  echo "Running composer install..."
  cd /app && composer install --no-interaction --prefer-dist
fi
exec apache2-foreground
