#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting Laravel application..."

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear and cache config
echo "🧹 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Set correct permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# Start PHP-FPM in background
echo "▶️ Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "▶️ Starting Nginx..."
nginx -g 'daemon off;'
