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

cd $APP_DIR

# 1. Start containers
echo "[1/6] Starting containers..."
docker-compose up -d

# Wait for container to be ready
echo "Waiting for container to be ready..."
sleep 10

# 2. Install/update composer dependencies
echo "[2/6] Installing composer dependencies..."
docker exec $CONTAINER_NAME composer install --no-dev --optimize-autoloader --no-interaction

# 3. Run database migrations
echo "[3/6] Running database migrations..."
docker exec $CONTAINER_NAME php artisan migrate --force

# 4. Clear and rebuild cache
echo "[4/6] Clearing cache..."
docker exec $CONTAINER_NAME php artisan optimize:clear

echo "[5/6] Rebuilding cache..."
docker exec $CONTAINER_NAME php artisan config:cache
docker exec $CONTAINER_NAME php artisan view:cache

# 6. Restart queue workers (if using)
echo "[6/6] Restarting queue workers..."
docker exec $CONTAINER_NAME php artisan queue:restart || true

echo "=========================================="
echo "Deployment completed successfully!"
echo "=========================================="
