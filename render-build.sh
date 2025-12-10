#!/usr/bin/env bash
# exit on error
set -o errexit

composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Create backup directory
mkdir -p storage/app/backups
chmod -R 775 storage/app/backups
