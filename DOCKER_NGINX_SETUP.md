# Setup Nginx di Docker untuk sps.videaclass.com

Dokumentasi ini menjelaskan cara setup Nginx sebagai reverse proxy di dalam Docker untuk domain **sps.videaclass.com**.

## Arsitektur

```
Internet → Nginx Container (Port 80/443) → FrankenPHP Container (Port 8000) → Laravel App
                                                      ↓
                                              PostgreSQL Master/Slave
                                                      ↓
                                                    Redis
```

**Keuntungan menggunakan Nginx di Docker:**
- Tidak perlu install Nginx di host
- Easy deployment dan portability
- Isolasi konfigurasi
- Mudah di-scale
- Tidak ada masalah dpkg/apt di host

## Prerequisites

1. Docker dan Docker Compose terinstall
2. SSL Certificate untuk sps.videaclass.com
3. Domain sps.videaclass.com sudah pointing ke IP server
4. Port 80 dan 443 tidak digunakan di host

## Struktur File

```
schoolvidea/
├── docker-compose.yml
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf                    # Main nginx config
│   │   ├── conf.d/
│   │   │   ├── default.conf              # Default HTTP (untuk dev/IP access)
│   │   │   └── sps.videaclass.com.conf   # HTTPS config untuk domain
│   │   ├── ssl/
│   │   │   ├── README.md
│   │   │   ├── .gitignore
│   │   │   ├── certificate.crt           # (add your SSL cert)
│   │   │   └── private.key               # (add your private key)
│   │   └── logs/                         # Nginx logs (auto-created)
│   └── frankenphp/
│       ├── Dockerfile
│       └── Caddyfile
└── ...
```

## Langkah Setup

### 1. Setup SSL Certificate

#### Option A: Menggunakan SSL Certificate dari Provider

Jika sudah punya SSL certificate (dari provider seperti Cloudflare, Let's Encrypt, dll):

```bash
# Copy certificate files ke directory ssl
cp /path/to/your/certificate.crt docker/nginx/ssl/certificate.crt
cp /path/to/your/private.key docker/nginx/ssl/private.key

# Set permission (Linux only)
chmod 600 docker/nginx/ssl/private.key
```

#### Option B: Generate Self-Signed Certificate (Development Only)

Untuk testing/development saja:

```bash
cd docker/nginx/ssl

# Generate self-signed certificate (valid 1 tahun)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout private.key \
  -out certificate.crt \
  -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Videa/OU=IT/CN=sps.videaclass.com"
```

**Note:** Self-signed certificate akan menampilkan warning di browser. Gunakan untuk development saja.

#### Option C: Menggunakan Let's Encrypt dengan Certbot

Untuk production dengan free SSL:

```bash
# Install certbot di host
sudo apt install certbot -y  # Ubuntu/Debian

# Generate certificate (pastikan port 80 tidak digunakan)
sudo certbot certonly --standalone -d sps.videaclass.com

# Copy certificate ke docker directory
sudo cp /etc/letsencrypt/live/sps.videaclass.com/fullchain.pem docker/nginx/ssl/certificate.crt
sudo cp /etc/letsencrypt/live/sps.videaclass.com/privkey.pem docker/nginx/ssl/private.key

# Set permission
sudo chmod 644 docker/nginx/ssl/certificate.crt
sudo chmod 600 docker/nginx/ssl/private.key
```

### 2. Update Environment Variables

Edit file `.env`:

```env
APP_URL=https://sps.videaclass.com
APP_ENV=production
APP_DEBUG=false
```

### 3. Build dan Start Containers

```bash
# Build images (pertama kali atau jika ada perubahan Dockerfile)
docker-compose build

# Start semua services
docker-compose up -d

# Cek status containers
docker-compose ps
```

Expected output:
```
NAME              IMAGE                          STATUS
frankenphp_app    schoolvidea/frankenphp:latest  Up
nginx_proxy       nginx:alpine                   Up
postgres_master   postgres:16-alpine             Up
postgres_slave    postgres:16-alpine             Up
redis_cache       redis:7-alpine                 Up
```

### 4. Verifikasi Setup

```bash
# Cek nginx logs
docker-compose logs nginx

# Cek frankenphp logs
docker-compose logs frankenphp

# Test HTTP endpoint (should redirect to HTTPS)
curl -I http://localhost

# Test HTTPS endpoint (dari dalam server)
curl -k -I https://localhost

# Test dari browser
# https://sps.videaclass.com
```

### 5. Setup Firewall (Linux Only)

```bash
# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

## Management Commands

### Start/Stop Services

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart specific service
docker-compose restart nginx
docker-compose restart frankenphp

# Stop without removing containers
docker-compose stop

# Start existing containers
docker-compose start
```

### View Logs

```bash
# View all logs
docker-compose logs

# View specific service logs
docker-compose logs nginx
docker-compose logs frankenphp

# Follow logs (real-time)
docker-compose logs -f nginx

# View last 100 lines
docker-compose logs --tail=100 nginx
```

### Access Container Shell

```bash
# Access nginx container
docker exec -it nginx_proxy sh

# Access frankenphp container
docker exec -it frankenphp_app sh

# Test nginx config inside container
docker exec nginx_proxy nginx -t

# Reload nginx config without restart
docker exec nginx_proxy nginx -s reload
```

### Update Nginx Configuration

```bash
# Edit konfigurasi
nano docker/nginx/conf.d/sps.videaclass.com.conf

# Test konfigurasi
docker exec nginx_proxy nginx -t

# Reload nginx (no downtime)
docker exec nginx_proxy nginx -s reload

# Atau restart container
docker-compose restart nginx
```

## Troubleshooting

### 1. Port Already in Use

**Error:**
```
Error starting userland proxy: listen tcp4 0.0.0.0:80: bind: address already in use
```

**Penyebab:** Port 80 atau 443 sudah digunakan oleh service lain di host

**Solusi:**

```bash
# Cek service yang menggunakan port 80
sudo lsof -i :80
sudo netstat -tulpn | grep :80

# Jika ada Apache/Nginx di host, stop dulu
sudo systemctl stop apache2
sudo systemctl stop nginx

# Atau disable permanent
sudo systemctl disable apache2
sudo systemctl disable nginx

# Kemudian start docker containers
docker-compose up -d
```

### 2. SSL Certificate Error

**Error:** `nginx: [emerg] cannot load certificate`

**Penyebab:** File certificate tidak ada atau path salah

**Solusi:**

```bash
# Cek file certificate ada
ls -la docker/nginx/ssl/

# Harus ada:
# certificate.crt
# private.key

# Test certificate valid
openssl x509 -in docker/nginx/ssl/certificate.crt -noout -text

# Test private key valid
openssl rsa -in docker/nginx/ssl/private.key -check

# Test certificate dan key match
openssl x509 -noout -modulus -in docker/nginx/ssl/certificate.crt | openssl md5
openssl rsa -noout -modulus -in docker/nginx/ssl/private.key | openssl md5
# Kedua output harus sama
```

### 3. Cannot Connect to FrankenPHP

**Error:** `502 Bad Gateway`

**Penyebab:** Nginx tidak bisa connect ke FrankenPHP container

**Solusi:**

```bash
# Cek FrankenPHP running
docker ps | grep frankenphp

# Cek FrankenPHP logs
docker logs frankenphp_app

# Test koneksi dari nginx container ke frankenphp
docker exec nginx_proxy wget -O- http://frankenphp_app:8000

# Cek network
docker network inspect schoolvidea_laravel_network

# Restart services
docker-compose restart frankenphp
docker-compose restart nginx
```

### 4. Permission Denied on Logs

**Error:** `nginx: [emerg] open() "/var/log/nginx/..." failed (13: Permission denied)`

**Solusi:**

```bash
# Create logs directory dengan permission yang tepat
mkdir -p docker/nginx/logs
chmod 755 docker/nginx/logs

# Restart nginx
docker-compose restart nginx
```

### 5. Configuration Test Failed

```bash
# Test nginx config di dalam container
docker exec nginx_proxy nginx -t

# Jika error, cek syntax di file config
nano docker/nginx/conf.d/sps.videaclass.com.conf

# Cek logs untuk detail error
docker logs nginx_proxy
```

### 6. Domain Not Resolving

**Penyebab:** DNS belum pointing atau propagasi belum selesai

**Solusi:**

```bash
# Test DNS resolution
nslookup sps.videaclass.com
dig sps.videaclass.com

# Test local
echo "YOUR_SERVER_IP sps.videaclass.com" | sudo tee -a /etc/hosts

# Atau gunakan curl dengan Host header
curl -H "Host: sps.videaclass.com" http://YOUR_SERVER_IP
```

### 7. Check All Services Health

```bash
# Comprehensive check script
cat > check-services.sh << 'EOFCHECK'
#!/bin/bash

echo "=== Docker Containers Status ==="
docker-compose ps

echo -e "\n=== Network Connectivity ==="
docker exec nginx_proxy wget -q -O- http://frankenphp_app:8000 >/dev/null && echo "✓ Nginx → FrankenPHP: OK" || echo "✗ Nginx → FrankenPHP: FAILED"

echo -e "\n=== Nginx Config Test ==="
docker exec nginx_proxy nginx -t

echo -e "\n=== SSL Certificate ==="
openssl x509 -in docker/nginx/ssl/certificate.crt -noout -dates 2>/dev/null && echo "✓ Certificate: OK" || echo "✗ Certificate: NOT FOUND"

echo -e "\n=== Port Listening ==="
netstat -tulpn | grep -E ':(80|443) ' || ss -tulpn | grep -E ':(80|443) '

echo -e "\n=== Recent Nginx Errors ==="
docker logs --tail=10 nginx_proxy 2>&1 | grep -i error || echo "No errors"
EOFCHECK

chmod +x check-services.sh
./check-services.sh
```

## Maintenance

### Renew SSL Certificate (Let's Encrypt)

```bash
# Renew certificate di host
sudo certbot renew

# Copy ke docker directory
sudo cp /etc/letsencrypt/live/sps.videaclass.com/fullchain.pem docker/nginx/ssl/certificate.crt
sudo cp /etc/letsencrypt/live/sps.videaclass.com/privkey.pem docker/nginx/ssl/private.key

# Reload nginx
docker exec nginx_proxy nginx -s reload
```

**Setup Auto-Renewal dengan Cron:**

```bash
# Buat script renewal
cat > /usr/local/bin/renew-docker-ssl.sh << 'EOFRENEWAL'
#!/bin/bash
certbot renew --quiet
cp /etc/letsencrypt/live/sps.videaclass.com/fullchain.pem /path/to/schoolvidea/docker/nginx/ssl/certificate.crt
cp /etc/letsencrypt/live/sps.videaclass.com/privkey.pem /path/to/schoolvidea/docker/nginx/ssl/private.key
docker exec nginx_proxy nginx -s reload
EOFRENEWAL

chmod +x /usr/local/bin/renew-docker-ssl.sh

# Add to crontab (cek setiap hari jam 3 pagi)
sudo crontab -e
# Add line:
0 3 * * * /usr/local/bin/renew-docker-ssl.sh
```

### View Resource Usage

```bash
# View container stats
docker stats

# View specific container
docker stats nginx_proxy frankenphp_app
```

### Backup Configuration

```bash
# Backup semua config
tar -czf nginx-backup-$(date +%Y%m%d).tar.gz docker/nginx/

# Restore
tar -xzf nginx-backup-YYYYMMDD.tar.gz
```

### Update Nginx Image

```bash
# Pull latest nginx:alpine
docker pull nginx:alpine

# Recreate nginx container dengan image baru
docker-compose up -d --force-recreate nginx
```

## Performance Tuning

### 1. Adjust Worker Processes

Edit `docker/nginx/nginx.conf`:

```nginx
# Auto = jumlah CPU cores
worker_processes auto;

# Atau set manual (untuk server dengan 4 cores)
worker_processes 4;
```

### 2. Enable Caching

Tambahkan di `docker/nginx/conf.d/sps.videaclass.com.conf`:

```nginx
# Tambahkan di dalam server block
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
    proxy_pass http://frankenphp_backend;
    expires 7d;
    add_header Cache-Control "public, immutable";
}
```

### 3. Increase Worker Connections

Edit `docker/nginx/nginx.conf`:

```nginx
events {
    worker_connections 2048;  # increase from 1024
    use epoll;
    multi_accept on;
}
```

### 4. Rate Limiting

Edit `docker/nginx/conf.d/sps.videaclass.com.conf`:

```nginx
# Tambahkan sebelum server block
limit_req_zone $binary_remote_addr zone=one:10m rate=10r/s;

# Tambahkan di dalam location /
location / {
    limit_req zone=one burst=20 nodelay;
    # ... proxy settings
}
```

## Monitoring

### View Access Logs

```bash
# Real-time access logs
docker exec nginx_proxy tail -f /var/log/nginx/sps.videaclass.com-access.log

# Or from host (jika mount logs directory)
tail -f docker/nginx/logs/sps.videaclass.com-access.log
```

### View Error Logs

```bash
# Real-time error logs
docker exec nginx_proxy tail -f /var/log/nginx/sps.videaclass.com-error.log

# Or from host
tail -f docker/nginx/logs/sps.videaclass.com-error.log
```

### Setup Monitoring (Optional)

Tools yang bisa digunakan:
- **Uptime Kuma** - Self-hosted monitoring
- **Prometheus + Grafana** - Metrics dan dashboards
- **nginx-prometheus-exporter** - Nginx metrics
- **Cloudflare Analytics** - Jika menggunakan Cloudflare

## Security Checklist

- [ ] SSL/TLS enabled dengan certificate valid
- [ ] HTTP redirect ke HTTPS
- [ ] Security headers configured
- [ ] Firewall configured (port 80, 443 only)
- [ ] SSL private key permissions: 600
- [ ] Regular SSL certificate renewal
- [ ] Monitoring dan alerting setup
- [ ] Regular backup
- [ ] Docker containers auto-restart enabled
- [ ] Nginx version hidden (server_tokens off)
- [ ] Rate limiting enabled (optional)
- [ ] Keep Docker images updated

## Production Deployment Checklist

Before going to production:

1. **SSL Certificate**
   - [ ] Valid SSL certificate installed (not self-signed)
   - [ ] Certificate expiry date > 30 days
   - [ ] Auto-renewal configured

2. **Environment**
   - [ ] APP_ENV=production
   - [ ] APP_DEBUG=false
   - [ ] Strong APP_KEY generated

3. **Security**
   - [ ] Firewall enabled and configured
   - [ ] Only necessary ports open (80, 443)
   - [ ] SSH key-based authentication
   - [ ] Regular security updates enabled

4. **Performance**
   - [ ] Laravel caches generated (config, routes, views)
   - [ ] Composer autoload optimized
   - [ ] Redis configured for caching
   - [ ] Database indexed properly

5. **Monitoring**
   - [ ] Error logging configured
   - [ ] Uptime monitoring enabled
   - [ ] SSL expiry monitoring
   - [ ] Disk space monitoring

6. **Backup**
   - [ ] Database backup automated
   - [ ] File backup configured
   - [ ] Backup tested and restorable

## References

- [Nginx Docker Official Image](https://hub.docker.com/_/nginx)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [FrankenPHP Documentation](https://frankenphp.dev/)
- [Laravel Deployment](https://laravel.com/docs/deployment)

## Support

Jika ada masalah:
1. Check logs: `docker-compose logs nginx frankenphp`
2. Verify services running: `docker-compose ps`
3. Test connectivity: Run health check script
4. Check firewall and DNS settings
5. Review configuration files

---

**Last Updated:** 2025-11-25
