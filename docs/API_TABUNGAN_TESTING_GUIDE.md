# API Tabungan Siswa - Testing Guide

## Overview
Dokumentasi lengkap untuk testing API transaksi tabungan siswa (setor dan tarik) menggunakan Postman.

## Daftar Isi
1. [Setup & Configuration](#setup--configuration)
2. [API Endpoints](#api-endpoints)
3. [Testing Scenarios](#testing-scenarios)
4. [Business Rules](#business-rules)
5. [Database Schema](#database-schema)

---

## Setup & Configuration

### Import Postman Collection
File sudah include di: `/postman/Tagihan_Siswa_API_Collection.json`

Section: **Tabungan (Savings)**

### Environment Variables
- `base_url`: `http://localhost:8000/api/v1`
- `access_token`: Bearer token dari login

---

## API Endpoints

### 1. Dashboard Tabungan
**Endpoint:** `GET /tabungan/dashboard`

**Query Parameters:**
- `siswa_id` (required): Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "saldo_saat_ini": 500000,
    "total_setoran": 1000000,
    "total_penarikan": 500000,
    "jumlah_setoran": 10,
    "jumlah_penarikan": 5,
    "status_rekening": "aktif"
  }
}
```

---

### 2. Setor Tabungan (Deposit)
**Endpoint:** `POST /tabungan/setor`

**Request Body:**
```json
{
  "siswa_id": 1,
  "jumlah": 100000,
  "metode": "CASH",
  "keterangan": "Setoran rutin bulanan"
}
```

**Parameters:**
- `siswa_id` (required): Student ID
- `jumlah` (required): Amount to deposit (minimum: 1000)
- `metode` (optional): Payment method
  - `CASH` (default)
  - `TRANSFER`
  - `QRIS`
  - `VIRTUAL_ACCOUNT`
  - `MOBILE_BANKING`
- `keterangan` (optional): Transaction note

**Success Response (201):**
```json
{
  "success": true,
  "message": "Setoran tabungan berhasil",
  "data": {
    "transaksi": {
      "id": 1,
      "code_pembayaran": "SET20251104123456789",
      "penerima_id": 1,
      "penerima_tipe": "App\\Models\\Siswa",
      "jenis_transaksi": "setoran_tabungan",
      "jumlah": 100000,
      "metode": "CASH",
      "keterangan": "Setoran rutin bulanan",
      "tanggal_transaksi": "2025-11-04 12:34:56",
      "created_by": 1,
      "createdBy": {
        "id": 1,
        "name": "Admin User"
      }
    },
    "saldo_sebelum": 400000,
    "saldo_sesudah": 500000,
    "siswa": {
      "id": 1,
      "nama": "John Doe",
      "nisn": "12345678"
    }
  }
}
```

**Error Response - Validation (422):**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "siswa_id": ["The siswa id field is required."],
    "jumlah": ["The jumlah must be at least 1000."]
  }
}
```

**Error Response - Student Not Found (404):**
```json
{
  "success": false,
  "message": "Siswa tidak ditemukan"
}
```

**Error Response - No User Account (400):**
```json
{
  "success": false,
  "message": "Siswa belum memiliki akun user"
}
```

---

### 3. Tarik Tabungan (Withdrawal)
**Endpoint:** `POST /tabungan/tarik`

**Request Body:**
```json
{
  "siswa_id": 1,
  "jumlah": 50000,
  "metode": "CASH",
  "keterangan": "Penarikan untuk keperluan sekolah"
}
```

**Parameters:**
- `siswa_id` (required): Student ID
- `jumlah` (required): Amount to withdraw (minimum: 1000)
- `metode` (optional): Payment method (default: CASH)
- `keterangan` (optional): Transaction note

**Success Response (201):**
```json
{
  "success": true,
  "message": "Penarikan tabungan berhasil",
  "data": {
    "transaksi": {
      "id": 2,
      "code_pembayaran": "TRK20251104123456789",
      "penerima_id": 1,
      "penerima_tipe": "App\\Models\\Siswa",
      "jenis_transaksi": "penarikan_tabungan",
      "jumlah": 50000,
      "metode": "CASH",
      "keterangan": "Penarikan untuk keperluan sekolah",
      "tanggal_transaksi": "2025-11-04 12:34:56",
      "created_by": 1
    },
    "saldo_sebelum": 500000,
    "saldo_sesudah": 450000,
    "siswa": {
      "id": 1,
      "nama": "John Doe",
      "nisn": "12345678"
    }
  }
}
```

**Error Response - Insufficient Balance (400):**
```json
{
  "success": false,
  "message": "Saldo tidak mencukupi",
  "data": {
    "saldo_tersedia": 30000,
    "jumlah_penarikan": 50000,
    "kekurangan": 20000
  }
}
```

**Error Response - No Savings Account (404):**
```json
{
  "success": false,
  "message": "Rekening tabungan belum ada atau tidak aktif"
}
```

---

### 4. Get Transaksi Tabungan
**Endpoint:** `GET /tabungan/transaksi`

**Query Parameters:**
- `siswa_id` (required): Student ID
- `jenis_transaksi` (optional): `setoran_tabungan` or `penarikan_tabungan`
- `start_date` (optional): Start date (Y-m-d)
- `end_date` (optional): End date (Y-m-d)
- `per_page` (optional): Items per page (default: 15)

**Example:**
```
GET /tabungan/transaksi?siswa_id=1&jenis_transaksi=setoran_tabungan&per_page=10
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "code_pembayaran": "SET20251104123456789",
        "jenis_transaksi": "setoran_tabungan",
        "jumlah": 100000,
        "keterangan": "Setoran rutin bulanan",
        "tanggal_transaksi": "2025-11-04 12:34:56",
        "created_by": {
          "id": 1,
          "name": "Admin User"
        },
        "created_at": "2025-11-04 12:34:56"
      }
    ],
    "per_page": 15,
    "total": 1
  }
}
```

---

### 5. Get Detail Transaksi
**Endpoint:** `GET /tabungan/transaksi/{id}`

Returns detailed information about a specific transaction.

---

### 6. Get Mutasi Rekening
**Endpoint:** `GET /tabungan/mutasi`

**Query Parameters:**
- `siswa_id` (required): Student ID
- `start_date` (optional): Filter start date
- `end_date` (optional): Filter end date

**Response:**
```json
{
  "success": true,
  "data": {
    "saldo_akhir": 450000,
    "periode": {
      "start_date": "semua",
      "end_date": "semua"
    },
    "mutasi": [
      {
        "id": 1,
        "tanggal": "2025-11-04 10:00:00",
        "code_pembayaran": "SET20251104100000123",
        "jenis_transaksi": "setoran_tabungan",
        "tipe": "kredit",
        "jumlah": 100000,
        "keterangan": "Setoran rutin",
        "saldo": 100000
      },
      {
        "id": 2,
        "tanggal": "2025-11-04 14:00:00",
        "code_pembayaran": "TRK20251104140000456",
        "jenis_transaksi": "penarikan_tabungan",
        "tipe": "debit",
        "jumlah": 50000,
        "keterangan": "Penarikan keperluan sekolah",
        "saldo": 50000
      }
    ]
  }
}
```

---

## Testing Scenarios

### Scenario 1: First Time Deposit (New Account)
```
1. POST /tabungan/setor
   {
     "siswa_id": 1,
     "jumlah": 100000,
     "metode": "CASH"
   }
   → Creates saldo_keuangan record automatically
   → Saldo: 0 → 100,000

2. GET /tabungan/dashboard?siswa_id=1
   → Verify account created and balance updated
```

### Scenario 2: Multiple Deposits
```
1. POST /tabungan/setor
   { "siswa_id": 1, "jumlah": 100000 }
   → Saldo: 0 → 100,000

2. POST /tabungan/setor
   { "siswa_id": 1, "jumlah": 50000 }
   → Saldo: 100,000 → 150,000

3. POST /tabungan/setor
   { "siswa_id": 1, "jumlah": 75000, "metode": "TRANSFER" }
   → Saldo: 150,000 → 225,000

4. GET /tabungan/dashboard?siswa_id=1
   → total_setoran: 225,000
   → jumlah_setoran: 3
```

### Scenario 3: Deposit and Withdrawal
```
1. POST /tabungan/setor
   { "siswa_id": 1, "jumlah": 500000 }
   → Saldo: 0 → 500,000

2. POST /tabungan/tarik
   { "siswa_id": 1, "jumlah": 200000 }
   → Saldo: 500,000 → 300,000

3. GET /tabungan/mutasi?siswa_id=1
   → View complete transaction history
```

### Scenario 4: Insufficient Balance Error
```
1. Current Balance: 100,000

2. POST /tabungan/tarik
   {
     "siswa_id": 1,
     "jumlah": 150000
   }

Expected Response: 400 Bad Request
{
  "success": false,
  "message": "Saldo tidak mencukupi",
  "data": {
    "saldo_tersedia": 100000,
    "jumlah_penarikan": 150000,
    "kekurangan": 50000
  }
}
```

### Scenario 5: Minimum Amount Validation
```
POST /tabungan/setor
{
  "siswa_id": 1,
  "jumlah": 500
}

Expected Response: 422 Validation Error
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "jumlah": ["The jumlah must be at least 1000."]
  }
}
```

### Scenario 6: Withdrawal Without Account
```
POST /tabungan/tarik
{
  "siswa_id": 99,  // Student who never deposited
  "jumlah": 50000
}

Expected Response: 404 Not Found
{
  "success": false,
  "message": "Rekening tabungan belum ada atau tidak aktif"
}
```

### Scenario 7: Complete Flow with Transaction History
```
1. POST /tabungan/setor
   { "siswa_id": 1, "jumlah": 100000, "keterangan": "Setoran awal" }

2. POST /tabungan/setor
   { "siswa_id": 1, "jumlah": 50000, "keterangan": "Setoran tambahan" }

3. POST /tabungan/tarik
   { "siswa_id": 1, "jumlah": 30000, "keterangan": "Beli buku" }

4. GET /tabungan/transaksi?siswa_id=1&per_page=20
   → View all transactions

5. GET /tabungan/mutasi?siswa_id=1
   → View running balance:
     - Setoran 100,000 (saldo: 100,000)
     - Setoran 50,000 (saldo: 150,000)
     - Penarikan 30,000 (saldo: 120,000)

6. GET /tabungan/dashboard?siswa_id=1
   → saldo_saat_ini: 120,000
   → total_setoran: 150,000
   → total_penarikan: 30,000
```

### Scenario 8: Filter by Transaction Type
```
GET /tabungan/transaksi?siswa_id=1&jenis_transaksi=setoran_tabungan
→ Returns only deposits

GET /tabungan/transaksi?siswa_id=1&jenis_transaksi=penarikan_tabungan
→ Returns only withdrawals
```

### Scenario 9: Date Range Filter
```
GET /tabungan/transaksi?siswa_id=1&start_date=2025-01-01&end_date=2025-12-31
→ Returns transactions within date range

GET /tabungan/mutasi?siswa_id=1&start_date=2025-11-01&end_date=2025-11-30
→ Returns mutation history for November
```

---

## Business Rules

### Setor (Deposit)
✅ **Allowed:**
- Minimum deposit: Rp 1,000
- Creates account automatically on first deposit
- Multiple deposits allowed
- All payment methods accepted

❌ **Not Allowed:**
- Deposit less than Rp 1,000
- Deposit for non-existent student

### Tarik (Withdrawal)
✅ **Allowed:**
- Minimum withdrawal: Rp 1,000
- Withdraw up to available balance

❌ **Not Allowed:**
- Withdraw more than available balance
- Withdraw from inactive account
- Withdraw from non-existent account
- Withdrawal less than Rp 1,000

### Account Management
- Account created automatically on first deposit
- Account status: active (1) or inactive (0)
- Only active accounts can perform transactions
- Balance never goes negative

### Transaction Recording
- All transactions recorded in `keuangan_transaksis`
- Unique transaction code generated:
  - Deposit: `SET{timestamp}{random}`
  - Withdrawal: `TRK{timestamp}{random}`
- Balance updated in `saldo_keuangan`
- Journal entries created automatically

---

## Database Schema

### saldo_keuangan
```sql
- id
- user_id (FK to users)
- akun_id (FK to akuns, nullable)
- saldo_akhir (current balance)
- status (1=active, 0=inactive)
- last_updated
- created_at
- updated_at
```

### keuangan_transaksis
```sql
- id
- code_pembayaran (unique)
- penerima_id (siswa_id)
- penerima_tipe (polymorphic - Siswa class)
- jenis_transaksi (setoran_tabungan, penarikan_tabungan)
- jumlah (amount)
- metode (CASH, TRANSFER, etc)
- referensi_tagihan_id (nullable)
- keterangan (description)
- tanggal_transaksi
- created_by (FK to users)
- created_at
- updated_at
```

### jurnals
```sql
- id
- transaksi_id (FK)
- akun_id (FK)
- debit
- kredit
- keterangan
- created_at
- updated_at
```

---

## Journal Entries

### Setor (Deposit)
```
Debit:  Kas/Bank (kategori: tabungan-masuk, debit=1)
Kredit: Tabungan Siswa (kategori: tabungan-masuk, kredit=1)
```

### Tarik (Withdrawal)
```
Debit:  Tabungan Siswa (kategori: tabungan-keluar, debit=1)
Kredit: Kas/Bank (kategori: tabungan-keluar, kredit=1)
```

---

## Quick Reference

### Payment Methods
- `CASH`: Cash payment
- `TRANSFER`: Bank transfer
- `QRIS`: QRIS payment
- `VIRTUAL_ACCOUNT`: Virtual account
- `MOBILE_BANKING`: Mobile banking

### Transaction Types
- `setoran_tabungan`: Deposit
- `penarikan_tabungan`: Withdrawal

### Status Codes
- `200`: Success (GET)
- `201`: Created (POST)
- `400`: Bad Request
- `404`: Not Found
- `422`: Validation Error
- `500`: Server Error

---

## Notes

1. **Auto Account Creation**: Account automatically created on first deposit
2. **Minimum Amount**: All transactions minimum Rp 1,000
3. **Balance Validation**: System prevents negative balance
4. **Audit Trail**: Complete transaction history maintained
5. **Journal Integration**: Automatic journal entries for accounting
6. **Security**: Bearer token required for all endpoints
7. **Transaction Safety**: Uses database transactions for data integrity

---

## Error Handling Examples

### 1. Student Without User Account
```
Request: POST /tabungan/setor { "siswa_id": 1 }
Response: 400 Bad Request
{
  "success": false,
  "message": "Siswa belum memiliki akun user"
}
```

### 2. Insufficient Funds
```
Request: POST /tabungan/tarik { "siswa_id": 1, "jumlah": 1000000 }
Response: 400 Bad Request
{
  "success": false,
  "message": "Saldo tidak mencukupi",
  "data": {
    "saldo_tersedia": 50000,
    "jumlah_penarikan": 1000000,
    "kekurangan": 950000
  }
}
```

### 3. Invalid Amount
```
Request: POST /tabungan/setor { "siswa_id": 1, "jumlah": 500 }
Response: 422 Validation Error
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "jumlah": ["The jumlah must be at least 1000."]
  }
}
```

---

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Verify database: Check `saldo_keuangan` and `keuangan_transaksis` tables
- Test with Postman collection: Import and test endpoints
