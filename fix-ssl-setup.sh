#!/bin/bash

# Script untuk fix SSL certificate issue
# Membuat dummy certificate dulu, baru request yang real

DOMAIN="sps.videaclass.com"
EMAIL="panjidenisgiantoroo@gmail.com"

echo "=== Fixing SSL Setup untuk $DOMAIN ==="

# STEP 1: Stop containers
echo "### Step 1: Stopping containers..."
docker compose down

# STEP 2: Buat direktori untuk certificates
echo "### Step 2: Creating directories..."
mkdir -p ./certbot/conf/live/$DOMAIN
mkdir -p ./certbot/www

# STEP 3: Buat dummy/self-signed certificate
echo "### Step 3: Creating dummy self-signed certificate..."
docker compose run --rm --entrypoint "\
    openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
    -keyout '/etc/letsencrypt/live/$DOMAIN/privkey.pem' \
    -out '/etc/letsencrypt/live/$DOMAIN/fullchain.pem' \
    -subj '/CN=localhost'" certbot

echo "### Dummy certificate created!"

# STEP 4: Start Nginx dengan dummy certificate
echo "### Step 4: Starting Nginx with dummy certificate..."
docker compose up -d nginx

# Wait for Nginx to start
echo "### Waiting for Nginx to be ready..."
sleep 5

# Check if Nginx is running
if docker compose ps nginx | grep -q "Up"; then
    echo "### Nginx is running!"
else
    echo "### ERROR: Nginx failed to start. Check logs:"
    docker compose logs nginx
    exit 1
fi

# STEP 5: Hapus dummy certificate
echo "### Step 5: Removing dummy certificate..."
docker compose run --rm --entrypoint "\
    rm -rf /etc/letsencrypt/live/$DOMAIN && \
    rm -rf /etc/letsencrypt/archive/$DOMAIN && \
    rm -rf /etc/letsencrypt/renewal/$DOMAIN.conf" certbot

# STEP 6: Request REAL certificate dari Let's Encrypt
echo "### Step 6: Requesting REAL certificate from Let's Encrypt..."
echo "### This may take 1-2 minutes..."

docker compose run --rm certbot certonly \
    --webroot \
    -w /var/www/certbot \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    --force-renewal \
    -d $DOMAIN

# Check if certificate was created
if [ -f "./certbot/conf/live/$DOMAIN/fullchain.pem" ]; then
    echo "### SUCCESS! Certificate obtained!"

    # STEP 7: Reload Nginx
    echo "### Step 7: Reloading Nginx with real certificate..."
    docker compose exec nginx nginx -s reload

    echo ""
    echo "==================================================================="
    echo "✓ SSL Setup Complete!"
    echo "==================================================================="
    echo ""
    echo "Test your HTTPS site:"
    echo "  https://$DOMAIN"
    echo ""
    echo "Check certificate:"
    echo "  openssl s_client -connect $DOMAIN:443 -servername $DOMAIN"
    echo ""
else
    echo ""
    echo "### ERROR: Failed to obtain certificate!"
    echo ""
    echo "Possible issues:"
    echo "1. DNS not pointing to this server yet"
    echo "2. Port 80 not accessible from internet"
    echo "3. Firewall blocking connections"
    echo ""
    echo "Run troubleshooting:"
    echo "  ./troubleshoot-ssl.sh"
    echo ""
    exit 1
fi
