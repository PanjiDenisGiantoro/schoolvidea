#!/bin/bash

# Quick SSL Setup - Metode paling simple dan reliable
# Step-by-step dengan penjelasan

DOMAIN="sps.videaclass.com"
EMAIL="panjidenisgiantoroo@gmail.com"

echo "=========================================="
echo "Quick SSL Setup untuk $DOMAIN"
echo "=========================================="
echo ""

# Step 1: Stop containers
echo "[1/6] Stopping all containers..."
docker compose down
echo "✓ Done"
echo ""

# Step 2: Rename config files
echo "[2/6] Switching to HTTP-only config..."
if [ -f "./docker/nginx/conf.d/default.conf" ]; then
    mv ./docker/nginx/conf.d/default.conf ./docker/nginx/conf.d/default.conf.ssl-backup
    echo "✓ Backed up SSL config"
fi

if [ -f "./docker/nginx/conf.d/default-http-only.conf" ]; then
    cp ./docker/nginx/conf.d/default-http-only.conf ./docker/nginx/conf.d/default.conf
    echo "✓ Using HTTP-only config"
fi
echo ""

# Step 3: Create directories
echo "[3/6] Creating certificate directories..."
mkdir -p ./certbot/conf
mkdir -p ./certbot/www
echo "✓ Directories created"
echo ""

# Step 4: Start Nginx (HTTP only, no SSL yet)
echo "[4/6] Starting Nginx (HTTP only)..."
docker compose up -d nginx
sleep 3

# Check Nginx status
if docker compose ps nginx | grep -q "Up"; then
    echo "✓ Nginx is running on port 80"
else
    echo "✗ ERROR: Nginx failed to start!"
    docker compose logs nginx
    exit 1
fi
echo ""

# Step 5: Test HTTP access
echo "[5/6] Testing HTTP access..."
echo "Checking if http://$DOMAIN is accessible..."
if curl -s -o /dev/null -w "%{http_code}" http://$DOMAIN | grep -q "200\|301\|302"; then
    echo "✓ HTTP is accessible!"
else
    echo "⚠ WARNING: Cannot reach http://$DOMAIN"
    echo "  Make sure:"
    echo "  - DNS points to this server"
    echo "  - Port 80 is open in firewall"
    echo ""
    echo "Continue anyway? (y/n)"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        echo "Setup cancelled"
        exit 1
    fi
fi
echo ""

# Step 6: Request SSL certificate
echo "[6/6] Requesting SSL certificate from Let's Encrypt..."
echo "This may take 1-2 minutes..."
echo ""

docker compose run --rm certbot certonly \
    --webroot \
    -w /var/www/certbot \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    -d $DOMAIN \
    -v

# Check result
if [ -f "./certbot/conf/live/$DOMAIN/fullchain.pem" ]; then
    echo ""
    echo "=========================================="
    echo "✓ SUCCESS! SSL Certificate obtained!"
    echo "=========================================="
    echo ""

    # Restore SSL config
    echo "Switching back to HTTPS config..."
    if [ -f "./docker/nginx/conf.d/default.conf.ssl-backup" ]; then
        mv ./docker/nginx/conf.d/default.conf.ssl-backup ./docker/nginx/conf.d/default.conf
    fi

    # Restart Nginx with SSL
    echo "Restarting Nginx with HTTPS..."
    docker compose restart nginx

    echo ""
    echo "✓ Setup Complete!"
    echo ""
    echo "Your site is now available at:"
    echo "  https://$DOMAIN"
    echo ""
    echo "Certificate will auto-renew via certbot container"
    echo ""
else
    echo ""
    echo "=========================================="
    echo "✗ FAILED to obtain certificate"
    echo "=========================================="
    echo ""
    echo "Please check:"
    echo "1. DNS: dig $DOMAIN (should point to this server)"
    echo "2. Port 80: telnet $DOMAIN 80"
    echo "3. Logs: docker compose logs certbot"
    echo ""
fi
