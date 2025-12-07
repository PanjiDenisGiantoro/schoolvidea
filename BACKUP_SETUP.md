# Setup Backup Database System

## 📋 Daftar Isi
1. [Instalasi](#instalasi)
2. [Konfigurasi](#konfigurasi)
3. [Cara Penggunaan](#cara-penggunaan)
4. [Schedule Backup Otomatis](#schedule-backup-otomatis)
5. [Troubleshooting](#troubleshooting)

---

## 🚀 Instalasi

### 1. Jalankan Migration
```bash
php artisan migrate
```

Ini akan membuat tabel:
- `backup_schedules` - Menyimpan konfigurasi backup otomatis
- `backup_logs` - Menyimpan history backup

### 2. Pastikan Storage Directory Ada
```bash
mkdir -p storage/app/backups
chmod 755 storage/app/backups
```

### 3. Setup Permissions (Opsional)
Tambahkan permission baru di database atau seeder:
- `view_backup` - Melihat halaman backup
- `create_backup` - Membuat backup manual
- `edit_backup` - Edit konfigurasi schedule
- `delete_backup` - Hapus backup
- `restore_backup` - Restore database dari backup

---

## ⚙️ Konfigurasi

### Environment Variables
Pastikan kredensial database sudah benar di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Email Configuration (Opsional)
Jika ingin notifikasi email, setup mail configuration:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 📖 Cara Penggunaan

### Akses Halaman Backup
Buka browser dan akses: `http://your-domain.com/backup`

### 1. **Manual Backup**
Tersedia 3 jenis backup manual:
- **Backup Sekarang** - Backup full database saat ini
- **Backup Mingguan** - Backup untuk periode minggu ini
- **Backup Bulanan** - Backup untuk periode bulan ini

Klik tombol sesuai kebutuhan, backup akan dibuat dan tersimpan di `storage/app/backups/`

### 2. **Konfigurasi Backup Otomatis**
Isi form konfigurasi dengan:
- **Aktifkan Backup Otomatis**: Toggle ON/OFF
- **Frekuensi Backup**:
  - Daily - Setiap hari
  - Weekly - Setiap Minggu (Minggu)
  - Monthly - Setiap Bulan (tanggal 1)
- **Waktu Backup**: Jam berapa backup akan dijalankan (format 24 jam)
- **Simpan Backup Selama**: Berapa hari backup disimpan sebelum dihapus otomatis
- **Notifikasi Email**: Aktifkan untuk menerima email setelah backup
- **Email Penerima**: Alamat email untuk notifikasi

Klik **Simpan Konfigurasi**

### 3. **Download Backup**
- Klik tombol **Download** (ikon hijau) pada backup yang diinginkan
- File akan terdownload dalam format `.zip`

### 4. **Restore Database**
⚠️ **PERINGATAN**: Restore akan menimpa semua data yang ada!

- Klik tombol **Restore** (ikon biru)
- Ketik `RESTORE` untuk konfirmasi
- Database akan di-restore dari backup

### 5. **Hapus Backup**
- Klik tombol **Hapus** (ikon merah) pada backup yang ingin dihapus
- Konfirmasi penghapusan

### 6. **Bersihkan Backup Lama**
- Klik tombol **Bersihkan Backup Lama** di header tabel
- Semua backup yang lebih tua dari periode yang ditentukan akan dihapus otomatis

---

## ⏰ Schedule Backup Otomatis

### Setup Cron Job (Linux/Mac)

1. Edit crontab:
```bash
crontab -e
```

2. Tambahkan baris ini:
```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/path-to-your-project` dengan path absolut ke project Laravel Anda.

### Setup Task Scheduler (Windows)

1. Buka **Task Scheduler**
2. Klik **Create Basic Task**
3. Nama: "Laravel Schedule"
4. Trigger: **Daily**
5. Start time: **00:00**
6. Action: **Start a program**
7. Program: `C:\xampp\php\php.exe` (sesuaikan path PHP Anda)
8. Arguments: `artisan schedule:run`
9. Start in: `C:\xampp\htdocs\your-project` (path project)

### Test Schedule
Jalankan command ini untuk test:
```bash
php artisan schedule:run
```

Jika sudah waktunya backup dan auto_backup aktif, backup akan dibuat.

### Manual Run Backup Command
```bash
php artisan backup:database
```

Atau dengan tipe tertentu:
```bash
php artisan backup:database manual
php artisan backup:database weekly
php artisan backup:database monthly
```

---

## 🔧 Troubleshooting

### Error: "mysqldump command not found"
**Solusi Windows:**
1. Cari lokasi `mysqldump.exe` (biasanya di `C:\xampp\mysql\bin\`)
2. Tambahkan ke PATH environment variable
3. Atau edit controller untuk menggunakan path lengkap:
```php
$command = sprintf(
    '"C:\xampp\mysql\bin\mysqldump.exe" --user=%s --password=%s --host=%s %s > %s',
    // ...
);
```

**Solusi Linux:**
```bash
sudo apt-get install mysql-client
```

### Error: "Permission denied"
```bash
chmod -R 755 storage/app/backups
chown -R www-data:www-data storage/app/backups
```

### Backup Tidak Jalan Otomatis
1. Pastikan cron job sudah setup
2. Cek log cron: `tail -f /var/log/cron.log`
3. Test manual: `php artisan schedule:run`
4. Cek config di database table `backup_schedules`
5. Pastikan `auto_backup` = 1

### File Backup Terlalu Besar
Jika database besar, pertimbangkan:
1. Kurangi `keep_backups` di konfigurasi
2. Buat backup hanya tabel penting
3. Compress dengan level lebih tinggi
4. Simpan backup di cloud storage

### Email Notifikasi Tidak Terkirim
1. Cek konfigurasi MAIL di `.env`
2. Test email dengan: `php artisan tinker` lalu:
```php
Mail::raw('Test', function($m) {
    $m->to('your-email@gmail.com')->subject('Test');
});
```

---

## 📊 Struktur File

```
project/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── BackupDatabase.php       # Command untuk backup
│   └── Http/
│       └── Controllers/
│           └── BackupController.php     # Controller utama
├── database/
│   └── migrations/
│       └── xxxx_create_backup_schedules_table.php
├── resources/
│   └── views/
│       └── pages/
│           └── backup/
│               └── index.blade.php      # Halaman UI backup
├── routes/
│   ├── console.php                      # Schedule definition
│   └── web.php                          # Routes backup
└── storage/
    └── app/
        └── backups/                     # Folder penyimpanan backup
            ├── backup_full_2024-01-15_120000.zip
            ├── backup_weekly_2024-01-14_020000.zip
            └── backup_monthly_2024-01-01_020000.zip
```

---

## 🔐 Security Notes

1. **Jangan commit file backup** ke Git - Tambahkan ke `.gitignore`:
```gitignore
storage/app/backups/*
```

2. **Protect backup route** dengan middleware authentication dan permission

3. **Encrypt backup** jika berisi data sensitif

4. **Simpan backup** di lokasi terpisah (cloud storage, external drive)

5. **Limit access** ke folder backups di web server (jangan bisa diakses public)

---

## 📞 Support

Jika ada masalah atau pertanyaan, hubungi tim development.

---

**Happy Backup! 🚀**
