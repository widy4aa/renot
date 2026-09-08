#!/bin/sh
set -e

echo "==> [ReNot] Starting entrypoint..."

# Pastikan storage & cache directory ada dan writable
mkdir -p /var/www/html/storage/app/public/avatars
mkdir -p /var/www/html/storage/app/public/documents
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Tunggu database ready
echo "==> [ReNot] Waiting for database..."
until php -r "
    try {
        \$pdo = new PDO('pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
        echo 'DB ready' . PHP_EOL;
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    echo "  ...database not ready yet, retrying in 2s"
    sleep 2
done

# Laravel setup
echo "==> [ReNot] Running migrations..."
php artisan migrate --force --no-interaction

echo "==> [ReNot] Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "==> [ReNot] Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [ReNot] Starting supervisord (PHP-FPM + Nginx)..."
exec supervisord -c /etc/supervisord.conf
