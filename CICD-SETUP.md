# CI/CD Setup Guide - GitHub Actions

## Konsep Deployment

### Branching Strategy
- **develop** branch → Auto deploy ke `/var/www/html/schoolvideadev` (Development)
- **master/main** branch → Auto deploy ke `/var/www/html/schoolvidea` (Production)

### Workflow
1. Developer push ke branch `develop` → GitHub Actions auto deploy ke server development
2. Setelah testing OK di dev, merge ke `master` → GitHub Actions auto deploy ke production
3. Production deployment bisa juga di-trigger manual via GitHub Actions UI

---

## Setup Instructions

### 1. Setup GitHub Secrets

Buka repository di GitHub → Settings → Secrets and variables → Actions → New repository secret

Tambahkan secrets berikut:

#### Development Secrets
- **DEV_SERVER_HOST**: `103.186.0.60`
- **DEV_SERVER_USERNAME**: `videa_sps`
- **DEV_SERVER_PASSWORD**: `!!IDNoperasional12@`

#### Production Secrets
- **PROD_SERVER_HOST**: `103.186.0.60`
- **PROD_SERVER_USERNAME**: `videa_sps`
- **PROD_SERVER_PASSWORD**: `!!IDNoperasional12@`

---

### 2. Setup Git di Server

SSH ke server dan setup git repository:

```bash
ssh videa_sps@103.186.0.60

# Setup Development
cd /var/www/html/schoolvideadev
git init
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
git fetch origin
git checkout develop
git branch --set-upstream-to=origin/develop develop

# Setup Production
cd /var/www/html/schoolvidea
git init
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
git fetch origin
git checkout master
git branch --set-upstream-to=origin/master master
```

---

### 3. Setup GitHub Personal Access Token (untuk git pull)

Jika repository private, Anda perlu setup Personal Access Token:

1. GitHub → Settings → Developer settings → Personal access tokens → Generate new token
2. Berikan akses: `repo` (Full control of private repositories)
3. Copy token yang di-generate
4. Di server, setup git credential:

```bash
# Development
cd /var/www/html/schoolvideadev
git config credential.helper store
git pull origin develop
# Masukkan username GitHub dan token sebagai password

# Production
cd /var/www/html/schoolvidea
git config credential.helper store
git pull origin master
# Masukkan username GitHub dan token sebagai password
```

---

### 4. Install Composer Dependencies (First Time)

```bash
# Development
cd /var/www/html/schoolvideadev
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Production
cd /var/www/html/schoolvidea
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
```

---

### 5. Setup File Permissions

```bash
# Development
cd /var/www/html/schoolvideadev
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Production
cd /var/www/html/schoolvidea
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

### 6. Test Deployment

1. Commit dan push ke branch `develop`:
```bash
git add .
git commit -m "test: CI/CD setup"
git push origin develop
```

2. Cek di GitHub → Actions → Lihat progress deployment
3. Jika berhasil, coba push ke `master` untuk test production deployment

---

## Troubleshooting

### Error: Permission denied
**Solusi**: Pastikan user `videa_sps` punya akses write ke folder:
```bash
sudo chown -R videa_sps:www-data /var/www/html/schoolvideadev
sudo chown -R videa_sps:www-data /var/www/html/schoolvidea
```

### Error: Composer not found
**Solusi**: Install composer di server atau ubah path di workflow file

### Error: Git authentication failed
**Solusi**: Setup Personal Access Token seperti di langkah 3

### Error: Migration failed
**Solusi**: Pastikan database credentials di `.env` sudah benar

---

## Advanced: Menggunakan SSH Key (Recommended untuk Production)

Untuk keamanan lebih baik, gunakan SSH key instead of password:

### 1. Generate SSH Key di GitHub Actions
```bash
ssh-keygen -t ed25519 -C "github-actions" -f github_actions_key
```

### 2. Copy public key ke server
```bash
ssh-copy-id -i github_actions_key.pub videa_sps@103.186.0.60
```

### 3. Update GitHub Secrets
- Ganti `DEV_SERVER_PASSWORD` dengan `DEV_SERVER_KEY` (isi dengan private key)
- Ganti `PROD_SERVER_PASSWORD` dengan `PROD_SERVER_KEY` (isi dengan private key)

### 4. Update workflow files
Ganti `password:` dengan `key:` di file `.github/workflows/*.yml`

---

## Monitoring

Untuk monitoring deployment:
- GitHub → Actions tab → Lihat semua deployment history
- Klik pada workflow run untuk lihat detail logs
- Jika gagal, akan ada notifikasi email dari GitHub

---

## Best Practices

1. **Selalu test di development dulu** sebelum deploy ke production
2. **Backup database** sebelum migration (sudah include di production workflow)
3. **Gunakan SSH key** instead of password untuk keamanan
4. **Setup branch protection** di GitHub untuk mencegah direct push ke master
5. **Review code** via Pull Request sebelum merge ke master

---

## File Structure

```
.github/
└── workflows/
    ├── deploy-dev.yml     # Auto deploy when push to develop
    └── deploy-prod.yml    # Auto deploy when push to master
```

---

## Deployment Flow

```
Developer
  │
  ├─ Push to develop branch
  │     │
  │     └─ GitHub Actions
  │           └─ Deploy to /var/www/html/schoolvideadev
  │
  └─ Merge to master branch
        │
        └─ GitHub Actions
              └─ Deploy to /var/www/html/schoolvidea
```

---

## Support

Jika ada masalah, cek:
1. GitHub Actions logs
2. Server logs: `/var/log/nginx/error.log` atau `/var/log/apache2/error.log`
3. Laravel logs: `storage/logs/laravel.log`
