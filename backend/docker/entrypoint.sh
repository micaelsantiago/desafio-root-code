#!/bin/sh
set -e

# Install Composer dependencies if vendor/ is missing (e.g., when using a bind mount)
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

# Run pending database migrations
php artisan migrate --force

# Fix storage permissions for Laravel (bind mount may be owned by host user)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Start PHP-FPM as a daemon in the background
php-fpm -D

# Start Nginx in the foreground (keeps the container alive)
exec nginx -g 'daemon off;'
