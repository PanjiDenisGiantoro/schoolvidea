#!/bin/bash

# Script untuk inisialisasi Let's Encrypt SSL Certificate
# untuk sps.videaclass.com

DOMAIN="sps.videaclass.com"
EMAIL="panjidenisgiantoroo@gmail.com"  # Ganti dengan email Anda
STAGING=0  # Set ke 1 untuk testing, 0 untuk production

echo "=== Inisialisasi Let's Encrypt untuk $DOMAIN ==="

# Buat direktori untuk certbot jika belum ada
mkdir -p ./certbot/conf
mkdir -p ./certbot/www



# Download recommended TLS parameters
if [ ! -f "./certbot/conf/options-ssl-nginx.conf" ]; then
    echo "### Downloading recommended TLS parameters..."
    curl -s https://raw.githubusercontent.com/certbot/certbot/master/certbot-nginx/certbot_nginx/_internal/tls_configs/options-ssl-nginx.conf > "./certbot/conf/options-ssl-nginx.conf"
fi

if [ ! -f "./certbot/conf/ssl-dhparams.pem" ]; then
    echo "### Downloading recommended SSL DH parameters..."
    curl -s https://raw.githubusercontent.com/certbot/certbot/master/certbot/certbot/ssl-dhparams.pem > "./certbot/conf/ssl-dhparams.pem"
fi

# Buat dummy certificate untuk inisialisasi Nginx
echo "### Creating dummy certificate for $DOMAIN..."
mkdir -p "./certbot/conf/live/$DOMAIN"

if [ ! -f "./certbot/conf/live/$DOMAIN/fullchain.pem" ]; then
    docker compose run --rm --entrypoint "\
        openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
        -keyout '/etc/letsencrypt/live/$DOMAIN/privkey.pem' \
        -out '/etc/letsencrypt/live/$DOMAIN/fullchain.pem' \
        -subj '/CN=localhost'" certbot
fi

# Start Nginx
echo "### Starting nginx..."
docker compose up -d nginx

# Hapus dummy certificate
echo "### Deleting dummy certificate for $DOMAIN..."
docker compose run --rm --entrypoint "\
    rm -rf /etc/letsencrypt/live/$DOMAIN && \
    rm -rf /etc/letsencrypt/archive/$DOMAIN && \
    rm -rf /etc/letsencrypt/renewal/$DOMAIN.conf" certbot

# Request certificate dari Let's Encrypt
echo "### Requesting Let's Encrypt certificate for $DOMAIN..."

if [ $STAGING != "0" ]; then
    echo "### Using STAGING mode (for testing)..."
    STAGING_ARG="--staging"
else
    echo "### Using PRODUCTION mode..."
    STAGING_ARG=""
fi

docker compose run --rm --entrypoint "\
    certbot certonly --webroot -w /var/www/certbot \
    $STAGING_ARG \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    -d $DOMAIN" certbot

# Reload Nginx
echo "### Reloading nginx..."
docker compose exec nginx nginx -s reload

echo "=== Setup selesai! ==="
echo "Cek status SSL: https://$DOMAIN"
