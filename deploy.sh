#!/bin/bash

# ==========================================
# Deployment Script for SchoolVidea
# ==========================================

set -e

echo "=========================================="
echo "Starting deployment..."
echo "=========================================="

# Variables
APP_DIR="/home/videa_payment/schoolvidea"
CONTAINER_NAME="frankenphp_app"

# Fix git safe directory
git config --global --add safe.directory $APP_DIR

cd $APP_DIR

# 1. Enable maintenance mode
echo "[1/8] Enabling maintenance mode..."
docker exec $CONTAINER_NAME php artisan down --retry=60 || true

# 2. Pull latest code (already done by CI/CD, but just in case)
echo "[2/8] Pulling latest code..."
git pull origin master

# 3. Install/update composer dependencies
echo "[3/8] Installing composer dependencies..."
docker exec $CONTAINER_NAME composer install --no-dev --optimize-autoloader --no-interaction

# 4. Run database migrations
echo "[4/8] Running database migrations..."
docker exec $CONTAINER_NAME php artisan migrate --force

# 5. Clear and rebuild cache
echo "[5/8] Clearing cache..."
docker exec $CONTAINER_NAME php artisan optimize:clear

echo "[6/8] Rebuilding cache..."
docker exec $CONTAINER_NAME php artisan config:cache
docker exec $CONTAINER_NAME php artisan view:cache
# docker exec $CONTAINER_NAME php artisan route:cache  # Skip jika ada duplicate route

# 7. Restart queue workers (if using)
echo "[7/8] Restarting queue workers..."
docker exec $CONTAINER_NAME php artisan queue:restart || true

# 8. Disable maintenance mode
echo "[8/8] Disabling maintenance mode..."
docker exec $CONTAINER_NAME php artisan up

echo "=========================================="
echo "Deployment completed successfully!"
echo "=========================================="
