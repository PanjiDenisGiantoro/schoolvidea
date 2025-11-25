#!/bin/bash

# Script untuk troubleshoot masalah SSL setup

DOMAIN="sps.videaclass.com"

echo "=== Troubleshooting SSL Setup untuk $DOMAIN ==="
echo ""

# 1. Check DNS
echo "### 1. Checking DNS Resolution..."
echo "Domain: $DOMAIN"
dig +short $DOMAIN
echo ""

# 2. Check if ports are open
echo "### 2. Checking if port 80 is accessible..."
timeout 5 curl -I http://$DOMAIN 2>&1 | head -5
echo ""

# 3. Check Docker containers
echo "### 3. Checking Docker containers..."
docker compose ps
echo ""

# 4. Check Nginx logs
echo "### 4. Last Nginx error logs (if any)..."
docker compose logs nginx --tail=20 2>&1 | grep -i error || echo "No errors found"
echo ""

# 5. Check certbot logs
echo "### 5. Last Certbot logs..."
docker compose logs certbot --tail=30 2>&1 || echo "No certbot logs yet"
echo ""

# 6. Check firewall
echo "### 6. Checking if firewall is blocking..."
sudo ufw status 2>/dev/null || echo "UFW not active or need sudo"
echo ""

# 7. Test Let's Encrypt connectivity
echo "### 7. Testing Let's Encrypt connectivity..."
curl -I https://acme-v02.api.letsencrypt.org/directory 2>&1 | head -3
echo ""

# 8. Check existing certificates
echo "### 8. Checking existing certificates..."
if [ -d "./certbot/conf/live/$DOMAIN" ]; then
    ls -la ./certbot/conf/live/$DOMAIN/
else
    echo "No certificates found yet"
fi
echo ""

echo "=== Troubleshooting Complete ==="
echo ""
echo "Common fixes:"
echo "1. Jika DNS tidak resolve: Tunggu propagasi DNS (bisa 1-24 jam)"
echo "2. Jika port 80 tidak bisa diakses: Buka di firewall/security group"
echo "3. Jika timeout: Gunakan standalone mode (init-letsencrypt-standalone.sh)"
