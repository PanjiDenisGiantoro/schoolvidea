# 📋 DOKUMENTASI SISTEM PEMBAYARAN MULTIPLE DENGAN HEAD_TAGIHAN

## 🚀 OVERVIEW

Sistem baru untuk mengelola pembayaran tagihan gabungan (multiple payment) dengan tracking detail per tagihan menggunakan nomor `head_tagihan` unik.

---

## 📁 FILES YANG DIBUAT/DIUPDATE

### 1. Database Migrations
✅ `database/migrations/2025_12_02_000001_add_head_tagihan_to_pembayaran_tagihan_table.php`
- Tambah 4 kolom ke tabel `pembayaran_tagihan`
- Kolom: `head_tagihan`, `is_master`, `parent_pembayaran_id`, `jumlah_tagihan_detail`

✅ `database/migrations/2025_12_02_000002_create_pembayaran_tagihan_detail_table.php`
- Tabel baru untuk tracking detail pembayaran
- Menyimpan breakdown pembayaran per tagihan

### 2. Models
✅ `app/Models/PembayaranTagihanDetail.php` (NEW)
- Model untuk tabel `pembayaran_tagihan_detail`
- Relasi dengan `Pembayarantagihan` dan `Tagihansiswa`

✅ `app/Models/Pembayarantagihan.php` (UPDATED)
- Tambah relasi: `pembayaranDetail()`, `parentPembayaran()`, `childPembayaran()`
- Tambah scope: `byHeadTagihan()`, `master()`, `detail()`

### 3. Controller
✅ `app/Http/Controllers/PembayaranController.php` (UPDATED)
- Tambah import `PembayaranTagihanDetail`
- Tambah method: `generateHeadTagihan()` (private)
- Tambah method: `createPembayaranDetail()` (private)
- Tambah method: `prosesPembayaranMultipleWithDetail()` (public)
- Tambah method: `getPembayaranDetailByHeadTagihan()` (public)
- Update method: `bayar()` - support head_tagihan untuk single payment

### 4. Routes
✅ `routes/web.php` (UPDATED)
- Route POST: `/pembayaran/proses-multiple-with-detail`
- Route GET: `/pembayaran/detail/{headTagihan}`

### 5. Views
✅ `resources/views/pages/pembayaran/pembayaran.blade.php` (UPDATED)
- Update function: `prosesMultiplePembayaran()`
- Sekarang call endpoint `/pembayaran/proses-multiple-with-detail`
- Tambah input form untuk nominal pembayaran
- Tampil head_tagihan saat berhasil

---

## 🔧 ENDPOINT & USAGE

### 1. Proses Pembayaran Multiple dengan Detail
```
POST /pembayaran/proses-multiple-with-detail
Content-Type: application/json

Request Body:
{
  "tagihan_siswa_ids": [1, 2, 3],      // Array ID tagihan (required)
  "jumlah_bayar": 1500000,              // Nominal pembayaran total (required)
  "metode": "TUNAI"                     // Metode pembayaran (optional, default: TUNAI)
}

Response (Success):
{
  "status": true,
  "message": "Pembayaran gabungan berhasil diproses",
  "data": {
    "head_tagihan": "HT202412021530001234",
    "pembayaran_master": {
      "id": 123,
      "code_pembayaran": "PS20241202153000...",
      "head_tagihan": "HT202412021530001234",
      "is_master": true,
      "jumlah_bayar": 1500000,
      "jumlah_tagihan_detail": 3,
      ...
    },
    "pembayaran_details": [
      {
        "tagihan_siswa_id": 1,
        "tagihan_nama": "SPP",
        "sisa_nominal_sebelum": 500000,
        "jumlah_bayar": 500000,
        "sisa_nominal_sesudah": 0,
        "status": 1  // 1=Lunas, 2=Cicilan
      },
      {
        "tagihan_siswa_id": 2,
        "tagihan_nama": "Buku Tulis",
        "sisa_nominal_sebelum": 600000,
        "jumlah_bayar": 600000,
        "sisa_nominal_sesudah": 0,
        "status": 1
      },
      {
        "tagihan_siswa_id": 3,
        "tagihan_nama": "Seragam",
        "sisa_nominal_sebelum": 400000,
        "jumlah_bayar": 400000,
        "sisa_nominal_sesudah": 0,
        "status": 1
      }
    ],
    "siswa": {
      "id": 5,
      "nama": "John Doe"
    },
    "summary": {
      "jumlah_tagihan": 3,
      "total_sisa_nominal_sebelum": 1500000,
      "jumlah_bayar": 1500000,
      "total_sisa_nominal_sesudah": 0
    }
  }
}

Response (Error):
{
  "status": false,
  "message": "Error message here"
}
```

### 2. Get Detail Pembayaran by Head Tagihan
```
GET /pembayaran/detail/{headTagihan}

Response (Success):
{
  "status": true,
  "data": {
    "head_tagihan": "HT202412021530001234",
    "pembayaran_master": {...},
    "details": [
      {
        "urutan": 1,
        "siswa_nama": "John Doe",
        "tagihan_nama": "SPP",
        "periode": "Oktober",
        "tahun": 2024,
        "jumlah_bayar": 500000,
        "jumlah_bayar_formatted": "500.000"
      },
      {...}
    ],
    "total_bayar": 1500000
  }
}
```

### 3. Single Payment Pembayaran (Tetap menggunakan endpoint lama)
```
POST /pembayaran/store
Content-Type: application/json

Request Body:
{
  "tagihan_siswa_id": 1,
  "bulan": "Oktober",
  "tahun": 2024,
  "nominal": 500000,
  "jumlah_bayar": 500000,
  "metode": "TUNAI"
}

Note: Method ini sudah di-update untuk support head_tagihan otomatis
```

---

## 💾 DATABASE SCHEMA

### Table: pembayaran_tagihan (UPDATED)
```sql
Kolom Baru:
- head_tagihan VARCHAR(50) UNIQUE NULLABLE - Nomor head pembayaran
- is_master BOOLEAN DEFAULT TRUE - Flag pembayaran master/detail
- parent_pembayaran_id BIGINT UNSIGNED NULLABLE - FK ke pembayaran master
- jumlah_tagihan_detail INT DEFAULT 1 - Jumlah tagihan dalam group
```

### Table: pembayaran_tagihan_detail (NEW)
```sql
CREATE TABLE pembayaran_tagihan_detail (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  head_tagihan VARCHAR(50) NOT NULL,
  pembayaran_id BIGINT UNSIGNED NOT NULL,
  tagihan_siswa_id BIGINT UNSIGNED NOT NULL,
  jumlah_bayar_detail DECIMAL(15,2) NOT NULL,
  urutan INT DEFAULT 1,
  periode VARCHAR(50) NULLABLE,
  tahun INT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,

  FOREIGN KEY (pembayaran_id) REFERENCES pembayaran_tagihan(id) ON DELETE CASCADE,
  FOREIGN KEY (tagihan_siswa_id) REFERENCES tagihan_siswa(id) ON DELETE CASCADE,
  UNIQUE KEY (head_tagihan, tagihan_siswa_id),
  INDEX (head_tagihan),
  INDEX (pembayaran_id),
  INDEX (tagihan_siswa_id)
);
```

---

## 📊 FLOW DIAGRAM

### Single Payment
```
User Klik "Bayar" Button
    ↓
Input Nominal (Full/Sebagian)
    ↓
Generate head_tagihan (HT + YmdHis + 4digit)
    ↓
Create pembayaran_tagihan (is_master=true)
    ↓
Create pembayaran_tagihan_detail (1 record)
    ↓
Update tagihan_siswa status
    ↓
Create transaksi keuangan & jurnal
    ↓
Response dengan head_tagihan
```

### Multiple Payment
```
User Pilih 2+ Checkbox Tagihan
    ↓
Klik Button "Proses Pembayaran"
    ↓
Input Nominal Pembayaran Total
    ↓
Generate head_tagihan (HT + YmdHis + 4digit)
    ↓
Create pembayaran_tagihan MASTER (is_master=true)
    ↓
Loop setiap tagihan:
  ├─ Distribusi nominal proporsional/sesuai input
  ├─ Create pembayaran_tagihan_detail record
  ├─ Update tagihan_siswa status & sisa_nominal
  └─ Cek apakah tagihan_id lainnya masih belum lunas
    ↓
Create transaksi keuangan (master)
    ↓
Create jurnal debit & kredit (per master)
    ↓
Response dengan head_tagihan & breakdown detail
    ↓
Reload tabel tagihan
```

---

## 🔍 QUERY EXAMPLES

### Get pembayaran dengan head_tagihan
```php
$pembayaran = Pembayarantagihan::byHeadTagihan('HT202412021530001234')->first();
```

### Get semua detail pembayaran multiple
```php
$details = PembayaranTagihanDetail::byHeadTagihan('HT202412021530001234')
    ->with('tagihanSiswa.siswa.user', 'tagihanSiswa.tagihan', 'pembayaran')
    ->orderBy('urutan')
    ->get();
```

### Get total bayar per head_tagihan
```php
$totalBayar = PembayaranTagihanDetail::byHeadTagihan($headTagihan)
    ->sum('jumlah_bayar_detail');
```

### Get pembayaran master saja
```php
$masters = Pembayarantagihan::master()->get();
```

### Get pembayaran dengan relasi detail
```php
$pembayaran = Pembayarantagihan::with('pembayaranDetail.tagihanSiswa.siswa')
    ->find($id);
```

---

## ✅ VALIDASI & BUSINESS RULES

1. **Semua tagihan dari siswa yang sama**
   - Error jika tagihan dari siswa berbeda

2. **Maksimal 10 tagihan per pembayaran**
   - Error jika > 10 tagihan

3. **Nominal pembayaran <= total sisa nominal**
   - Error jika melebihi total

4. **Distribusi proporsional otomatis**
   - Tagihan ke-n: `min(sisa_bayar, sisa_nominal_tagihan)`
   - Tagihan terakhir: `sisa_bayar` yang tersisa

5. **Auto-generate head_tagihan**
   - Format: `HT` + `YmdHis` + 4 digit random
   - Unik dan tidak bisa duplicate

6. **Database Transaction**
   - Rollback otomatis jika ada error
   - Konsistensi data terjamin

---

## 🎯 FITUR YANG DIDUKUNG

✅ **Single Payment**
- Bayar 1 tagihan
- Auto generate head_tagihan
- Track via head_tagihan

✅ **Multiple Payment**
- Bayar 2-10 tagihan sekaligus
- Distribusi proporsional
- Generate head_tagihan sekali untuk semua
- Detail tracking per tagihan
- Breakdown pembayaran terlihat jelas

✅ **Tracking & Reporting**
- Query by head_tagihan
- Lihat breakdown detail pembayaran
- Export data pembayaran multiple
- Audit trail via transaksi keuangan

✅ **Financial Integration**
- Auto create transaksi keuangan
- Auto create jurnal akun
- Debit: Kas masuk
- Kredit: Hutang berkurang

---

## 🐛 ERROR HANDLING

Semua error ditangani dengan response JSON:

```json
{
  "status": false,
  "message": "Error description"
}
```

Common errors:
- Semua tagihan harus milik siswa yang sama
- Maksimal 10 tagihan per pembayaran
- Jumlah bayar tidak boleh lebih besar dari total
- Tagihan sudah lunas
- Data tidak ditemukan

---

## 🚀 RUNNING MIGRATIONS

```bash
# Run semua migration
php artisan migrate

# Rollback jika ada masalah
php artisan migrate:rollback
```

---

## 📝 NOTES

1. Kolom `is_master` digunakan untuk membedakan pembayaran master (true) vs detail (false)
2. `parent_pembayaran_id` digunakan untuk relasi antara master & detail
3. `jumlah_tagihan_detail` menyimpan jumlah tagihan dalam satu group
4. Setiap pembayaran (single atau multiple) generate unique `head_tagihan`
5. Pembayaran detail records tersimpan di `pembayaran_tagihan_detail`
6. Frontend otomatis call endpoint `/pembayaran/proses-multiple-with-detail` via button "Proses Pembayaran"

---

## 📞 SUPPORT

Jika ada pertanyaan atau issue, silakan review:
1. Controller method: `prosesPembayaranMultipleWithDetail()`
2. Model relasi di: `Pembayarantagihan` dan `PembayaranTagihanDetail`
3. Route di: `routes/web.php`
4. Frontend logic di: `pembayaran.blade.php` - function `prosesMultiplePembayaran()`
