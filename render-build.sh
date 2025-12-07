#!/usr/bin/env bash
# exit on error
set -o errexit

echo "🔧 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔑 Generating application key..."
php artisan key:generate --force

echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "🧹 Clearing and caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔗 Creating storage link..."
php artisan storage:link || true

echo "✅ Build completed successfully!"
