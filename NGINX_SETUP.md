# Setup Nginx untuk sps.videaclass.com

Dokumentasi ini menjelaskan cara mengkonfigurasi Nginx di luar Docker untuk domain **sps.videaclass.com**.

## Arsitektur

```
Internet → Nginx (Host, Port 80/443) → FrankenPHP (Docker, Port 8000) → Laravel App
                                              ↓
                                         PostgreSQL Master/Slave
                                              ↓
                                            Redis
```

## Prerequisites

1. Docker dan Docker Compose terinstall
2. Nginx terinstall di host/server (bukan di Docker)
3. SSL Certificate untuk sps.videaclass.com
4. Domain sps.videaclass.com sudah pointing ke IP server

## Langkah Instalasi

### 1. Setup Docker Container

Docker compose sudah dikonfigurasi untuk expose port 8000 ke host:

```yaml
frankenphp:
  ports:
    - "8000:8000"
```

Jalankan container:

```bash
docker-compose up -d
```

Verifikasi FrankenPHP berjalan:

```bash
curl http://localhost:8000
```

### 2. Install Nginx (Jika Belum)

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install nginx
```

**CentOS/RHEL:**
```bash
sudo yum install nginx
```

**Windows (sudah ada di Laragon):**
Nginx sudah terinstall di Laragon, cek di: `C:\laragon\bin\nginx\`

### 3. Copy Konfigurasi Nginx

**Linux:**

```bash
# Copy file konfigurasi
sudo cp nginx/sps.videaclass.com.conf /etc/nginx/sites-available/

# Buat symbolic link
sudo ln -s /etc/nginx/sites-available/sps.videaclass.com.conf /etc/nginx/sites-enabled/

# Atau untuk CentOS/RHEL
sudo cp nginx/sps.videaclass.com.conf /etc/nginx/conf.d/
```

**Windows (Laragon):**

```bash
# Copy ke folder Laragon nginx config
copy nginx\sps.videaclass.com.conf C:\laragon\bin\nginx\nginx-1.x.x\conf\sites-enabled\
```

### 4. Setup SSL Certificate

#### Option A: Menggunakan Let's Encrypt (Gratis)

**Linux:**
```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx  # Ubuntu/Debian
# atau
sudo yum install certbot python3-certbot-nginx  # CentOS/RHEL

# Generate certificate
sudo certbot --nginx -d sps.videaclass.com
```

Certbot akan otomatis update konfigurasi Nginx dengan path SSL certificate yang benar.

#### Option B: Menggunakan SSL Certificate Manual

Edit file `nginx/sps.videaclass.com.conf`:

```nginx
ssl_certificate /path/to/your/certificate.crt;
ssl_certificate_key /path/to/your/private.key;
```

Ganti `/path/to/your/` dengan path SSL certificate Anda.

**Contoh:**
- `/etc/ssl/certs/sps.videaclass.com.crt`
- `/etc/ssl/private/sps.videaclass.com.key`

### 5. Test Konfigurasi Nginx

**Linux:**
```bash
sudo nginx -t
```

**Windows (Laragon):**
```bash
C:\laragon\bin\nginx\nginx-1.x.x\nginx.exe -t
```

Jika ada error, perbaiki konfigurasi sesuai pesan error.

### 6. Restart Nginx

**Linux:**
```bash
sudo systemctl restart nginx
# atau
sudo service nginx restart
```

**Windows (Laragon):**
- Klik "Menu" → "Nginx" → "Reload Nginx"
- Atau restart dari Laragon control panel

### 7. Update Laravel Environment

Edit file `.env`:

```env
APP_URL=https://sps.videaclass.com
```

Jika menggunakan Docker, restart container:

```bash
docker-compose restart frankenphp
```

### 8. Configure Firewall (Linux Only)

```bash
# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

## Verifikasi

1. **Test HTTP redirect ke HTTPS:**
   ```bash
   curl -I http://sps.videaclass.com
   ```
   Harus return `301 Moved Permanently`

2. **Test HTTPS:**
   ```bash
   curl -I https://sps.videaclass.com
   ```
   Harus return `200 OK`

3. **Test di Browser:**
   Buka `https://sps.videaclass.com` dan pastikan:
   - SSL certificate valid (gembok hijau)
   - Aplikasi Laravel berjalan normal
   - Tidak ada error di console

## Troubleshooting

### 1. Error 502 Bad Gateway

**Penyebab:** Nginx tidak bisa connect ke FrankenPHP

**Solusi:**
```bash
# Cek FrankenPHP container berjalan
docker ps | grep frankenphp

# Cek log FrankenPHP
docker logs frankenphp_app

# Test port 8000
curl http://localhost:8000
```

### 2. Error 504 Gateway Timeout

**Penyebab:** Request timeout

**Solusi:** Sudah dikonfigurasi timeout 600 detik di config. Jika masih timeout, tingkatkan:

```nginx
proxy_connect_timeout 900;
proxy_send_timeout 900;
proxy_read_timeout 900;
```

### 3. SSL Certificate Error

**Penyebab:** Path SSL certificate salah atau expired

**Solusi:**
```bash
# Cek path certificate
sudo ls -la /path/to/ssl/

# Cek expiry date
openssl x509 -in /path/to/certificate.crt -noout -dates

# Test SSL
openssl s_client -connect sps.videaclass.com:443
```

### 4. Permission Denied

**Penyebab:** Nginx tidak punya akses ke file

**Solusi:**
```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/log/nginx/
sudo chmod 755 /var/log/nginx/

# SELinux (CentOS/RHEL only)
sudo setsebool -P httpd_can_network_connect 1
```

### 5. Check Nginx Logs

**Linux:**
```bash
# Access log
sudo tail -f /var/log/nginx/sps.videaclass.com-access.log

# Error log
sudo tail -f /var/log/nginx/sps.videaclass.com-error.log
```

**Windows (Laragon):**
```bash
# Logs di: C:\laragon\bin\nginx\nginx-1.x.x\logs\
```

## Maintenance

### Renew SSL Certificate (Let's Encrypt)

```bash
# Dry run
sudo certbot renew --dry-run

# Actual renewal
sudo certbot renew
```

Certbot biasanya membuat cron job otomatis untuk renewal.

### Update Konfigurasi

Setelah edit konfigurasi:

```bash
# Test config
sudo nginx -t

# Reload (tanpa downtime)
sudo nginx -s reload
```

### Monitoring

Tambahkan monitoring untuk:
- Nginx uptime
- SSL certificate expiry
- Response time
- Error rate

Tools yang bisa digunakan:
- Uptime Kuma
- Prometheus + Grafana
- New Relic
- Datadog

## Performance Tuning (Optional)

### 1. Enable HTTP/2 Push

```nginx
location / {
    http2_push /css/app.css;
    http2_push /js/app.js;
    # ...
}
```

### 2. Add Caching

```nginx
# Cache static files
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
    proxy_pass http://frankenphp_backend;
    proxy_cache_valid 200 1d;
    expires 1d;
    add_header Cache-Control "public, immutable";
}
```

### 3. Rate Limiting

```nginx
# Di dalam http block
limit_req_zone $binary_remote_addr zone=one:10m rate=10r/s;

# Di dalam server block
location / {
    limit_req zone=one burst=20 nodelay;
    # ...
}
```

## Security Checklist

- [ ] SSL/TLS enabled dengan certificate valid
- [ ] HTTP redirect ke HTTPS
- [ ] Security headers sudah dikonfigurasi
- [ ] Firewall configured (port 80, 443 only)
- [ ] Rate limiting enabled (optional)
- [ ] Regular backup database dan files
- [ ] SSL certificate auto-renewal configured
- [ ] Monitoring dan alerting setup
- [ ] Access logs configured
- [ ] Hide Nginx version: `server_tokens off;`

## Support

Jika ada masalah:
1. Check error logs (Nginx dan Docker)
2. Verify semua service berjalan
3. Test connectivity antar komponen
4. Check DNS dan firewall settings

## References

- [Nginx Documentation](https://nginx.org/en/docs/)
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [FrankenPHP Documentation](https://frankenphp.dev/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
