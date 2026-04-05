#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/pearlhub-api}"
PHP_USER="${PHP_USER:-www-data}"
PHP_GROUP="${PHP_GROUP:-www-data}"

cd "$APP_DIR"

if [ ! -f .env ]; then
  echo ".env not found in $APP_DIR. Copy docs/templates/.env.production.example and fill real secrets first."
  exit 1
fi

composer install --no-dev --optimize-autoloader

php artisan key:generate --force
php artisan migrate --force
php artisan storage:link || true

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

chown -R "$PHP_USER":"$PHP_GROUP" "$APP_DIR"
chmod -R 775 storage bootstrap/cache

systemctl restart php8.3-fpm
systemctl restart nginx
supervisorctl reread || true
supervisorctl update || true
supervisorctl restart laravel-worker:* || true

echo "Laravel deploy completed successfully."
