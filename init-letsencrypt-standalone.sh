#!/bin/bash

# Script alternative menggunakan standalone mode
# Mode ini lebih reliable untuk first-time setup

DOMAIN="sps.videaclass.com"
EMAIL="panjidenisgiantoroo@gmail.com"
STAGING=0  # Set ke 1 untuk testing

echo "=== Setup SSL Certificate (Standalone Mode) ==="

# Buat direktori
mkdir -p ./certbot/conf
mkdir -p ./certbot/www

# STEP 1: Stop Nginx sementara (standalone mode butuh port 80)
echo "### Stopping Nginx..."
docker compose stop nginx

# STEP 2: Request certificate dengan standalone mode
echo "### Requesting certificate from Let's Encrypt..."

if [ $STAGING != "0" ]; then
    echo "### Using STAGING mode (for testing)..."
    STAGING_ARG="--staging"
else
    echo "### Using PRODUCTION mode..."
    STAGING_ARG=""
fi

# Gunakan standalone mode dengan timeout
docker compose run --rm certbot certonly \
    --standalone \
    --preferred-challenges http \
    $STAGING_ARG \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    --non-interactive \
    --http-01-port 80 \
    -d $DOMAIN

# STEP 3: Start Nginx kembali
echo "### Starting Nginx with SSL..."
docker compose up -d nginx

# STEP 4: Check status
echo ""
echo "=== Setup Complete ==="
echo "Certificate location: ./certbot/conf/live/$DOMAIN/"
echo ""
echo "Test SSL dengan command:"
echo "  curl -I https://$DOMAIN"
echo "  openssl s_client -connect $DOMAIN:443 -servername $DOMAIN"
