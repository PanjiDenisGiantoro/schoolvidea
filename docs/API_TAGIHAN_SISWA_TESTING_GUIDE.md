# API Tagihan Siswa & Mutasi - Testing Guide

## Overview
Dokumentasi lengkap untuk testing API transaksi tagihan siswa dan mutasi tagihan menggunakan Postman.

## Daftar Isi
1. [Setup & Configuration](#setup--configuration)
2. [Authentication](#authentication)
3. [API Endpoints](#api-endpoints)
4. [Testing Scenarios](#testing-scenarios)
5. [Database Schema](#database-schema)

---

## Setup & Configuration

### 1. Import Postman Collection
```bash
File Location: /postman/Tagihan_Siswa_API_Collection.json
```

### 2. Run Database Migration
```bash
php artisan migrate
```

Migration yang akan dijalankan:
- `2025_09_23_073627_create_tagihan_siswa_table.php`
- `2025_09_24_074324_create_pembayaran_tagihan_table.php`
- `2025_11_04_100000_create_tagihan_siswa_mutasi_table.php` (NEW)

### 3. Configure Environment Variables
Di Postman, set collection variables:
- `base_url`: `http://localhost:8000/api/v1`
- `access_token`: (akan otomatis ter-set setelah login)

---

## Authentication

### Login
**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
  "code_unit": "UNIT001",
  "email": "admin@school.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

> Token akan otomatis tersimpan di collection variable `access_token`

---

## API Endpoints

### A. Tagihan Siswa (Student Bills)

#### 1. Get All Student Bills
**Endpoint:** `GET /tagihan-siswa`

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)
- `siswa_id` (optional): Filter by student ID
- `tagihan_id` (optional): Filter by bill ID
- `status` (optional): 0=Belum Bayar, 1=Lunas, 2=Cicilan
- `kelas_id` (optional): Filter by class ID

**Example:**
```
GET /tagihan-siswa?status=0&kelas_id=1
```

#### 2. Get Student Bill Detail
**Endpoint:** `GET /tagihan-siswa/{id}`

**Response includes:**
- Student information
- Bill details
- Payment summary
- Remaining balance

#### 3. Get Bills by Student
**Endpoint:** `GET /tagihan-siswa/siswa/{siswaId}`

Returns all bills for a specific student with summary.

#### 4. Get Unpaid Bills
**Endpoint:** `GET /tagihan-siswa/unpaid/{siswaId}`

Returns only unpaid or partially paid bills.

---

### B. Pembayaran (Payments)

#### 1. Create Payment
**Endpoint:** `POST /pembayaran`

**Request Body:**
```json
{
  "tagihan_siswa_id": 1,
  "jumlah_bayar": 500000,
  "metode": "CASH",
  "keterangan": "Pembayaran SPP Januari 2025"
}
```

**Payment Methods:**
- `CASH`: Tunai
- `TRANSFER`: Transfer Bank
- `QRIS`: QRIS
- `VIRTUAL_ACCOUNT`: Virtual Account
- `MOBILE_BANKING`: Mobile Banking

**Business Rules:**
- Cannot pay more than remaining balance
- Cannot pay if bill is already fully paid (status=1)
- Status updates automatically:
  - `0` (Belum Bayar) → `2` (Cicilan) → `1` (Lunas)
- Creates financial transaction record
- Creates journal entries (debit/credit)

#### 2. Get Payment History
**Endpoint:** `GET /pembayaran/siswa/{siswaId}`

Returns complete payment history for a student.

#### 3. Get Payment Receipt
**Endpoint:** `GET /pembayaran/receipt/{id}`

Returns formatted receipt data for printing.

---

### C. Tagihan Siswa Mutasi (NEW)

#### 1. Create Mutation
**Endpoint:** `POST /tagihan-siswa-mutasi`

**Request Body:**
```json
{
  "tagihan_siswa_id": 1,
  "jenis_mutasi": "diskon",
  "nominal_perubahan": -100000,
  "keterangan": "Diskon prestasi akademik",
  "alasan": "Siswa mendapat juara 1 olimpiade",
  "auto_approve": true
}
```

**Mutation Types (jenis_mutasi):**
- `koreksi`: Correction of billing amount
- `diskon`: Discount/reduction
- `denda`: Penalty/fine
- `pembatalan`: Bill cancellation
- `lainnya`: Other adjustments

**Parameters:**
- `nominal_perubahan`:
  - **Negative** for reductions (discounts, corrections)
  - **Positive** for additions (penalties, fines)
- `auto_approve`:
  - `true`: Immediately applied to billing
  - `false`: Requires approval first

**Business Rules:**
- Cannot mutate fully paid bills (status=1)
- Resulting nominal cannot be negative
- Updates bill's sisa_nominal accordingly
- Appends mutation log to bill's catatan field
- Auto-generates mutation code: `MTS{timestamp}{random}`

#### 2. Get All Mutations
**Endpoint:** `GET /tagihan-siswa-mutasi`

**Query Parameters:**
- `per_page`: Items per page
- `tagihan_siswa_id`: Filter by bill ID
- `jenis_mutasi`: Filter by type
- `status_mutasi`: pending, approved, rejected

#### 3. Get Mutations by Student
**Endpoint:** `GET /tagihan-siswa-mutasi/siswa/{siswaId}`

Returns all mutations for a specific student.

#### 4. Approve Mutation
**Endpoint:** `POST /tagihan-siswa-mutasi/{id}/approve`

Approves a pending mutation and applies changes to billing.

#### 5. Reject Mutation
**Endpoint:** `POST /tagihan-siswa-mutasi/{id}/reject`

**Request Body:**
```json
{
  "alasan_penolakan": "Tidak memenuhi kriteria"
}
```

Rejects a pending mutation without applying changes.

---

## Testing Scenarios

### Scenario 1: Complete Payment Flow
```
1. GET /tagihan-siswa/unpaid/{siswaId}
   → Get list of unpaid bills

2. POST /pembayaran
   {
     "tagihan_siswa_id": 1,
     "jumlah_bayar": 500000,
     "metode": "CASH"
   }
   → Process payment

3. GET /pembayaran/receipt/{id}
   → Get receipt for printing

4. GET /tagihan-siswa/{id}
   → Verify updated balance and status
```

### Scenario 2: Partial Payment (Installment)
```
1. GET /tagihan-siswa/{id}
   → Nominal: 1,000,000
   → Sisa: 1,000,000
   → Status: 0 (Belum Bayar)

2. POST /pembayaran
   {
     "tagihan_siswa_id": 1,
     "jumlah_bayar": 300000
   }
   → Sisa: 700,000
   → Status: 2 (Cicilan)

3. POST /pembayaran
   {
     "tagihan_siswa_id": 1,
     "jumlah_bayar": 700000
   }
   → Sisa: 0
   → Status: 1 (Lunas)
```

### Scenario 3: Apply Discount Before Payment
```
1. GET /tagihan-siswa/{id}
   → Nominal: 1,000,000
   → Sisa: 1,000,000

2. POST /tagihan-siswa-mutasi
   {
     "tagihan_siswa_id": 1,
     "jenis_mutasi": "diskon",
     "nominal_perubahan": -200000,
     "keterangan": "Diskon prestasi"
   }
   → Nominal: 800,000
   → Sisa: 800,000

3. POST /pembayaran
   {
     "tagihan_siswa_id": 1,
     "jumlah_bayar": 800000
   }
   → Lunas with discounted amount
```

### Scenario 4: Apply Penalty for Late Payment
```
1. POST /tagihan-siswa-mutasi
   {
     "tagihan_siswa_id": 1,
     "jenis_mutasi": "denda",
     "nominal_perubahan": 50000,
     "keterangan": "Denda keterlambatan"
   }
   → Nominal increases by 50,000

2. Verify updated bill amount
```

### Scenario 5: Pending Approval Flow
```
1. POST /tagihan-siswa-mutasi
   {
     "tagihan_siswa_id": 1,
     "jenis_mutasi": "diskon",
     "nominal_perubahan": -500000,
     "auto_approve": false
   }
   → Status: pending

2. GET /tagihan-siswa-mutasi?status_mutasi=pending
   → View pending mutations

3. POST /tagihan-siswa-mutasi/{id}/approve
   → Apply mutation

   OR

   POST /tagihan-siswa-mutasi/{id}/reject
   {
     "alasan_penolakan": "Tidak memenuhi kriteria"
   }
   → Reject mutation
```

### Scenario 6: Error Handling Tests

#### Test: Payment exceeds remaining balance
```
POST /pembayaran
{
  "tagihan_siswa_id": 1,
  "jumlah_bayar": 2000000
}

Expected Response: 400 Bad Request
{
  "success": false,
  "message": "Jumlah bayar tidak boleh lebih besar dari sisa tagihan"
}
```

#### Test: Mutate fully paid bill
```
POST /tagihan-siswa-mutasi
{
  "tagihan_siswa_id": 1,  // status = 1 (Lunas)
  "jenis_mutasi": "diskon",
  "nominal_perubahan": -100000
}

Expected Response: 400 Bad Request
{
  "success": false,
  "message": "Tidak dapat melakukan mutasi pada tagihan yang sudah lunas"
}
```

#### Test: Negative resulting nominal
```
POST /tagihan-siswa-mutasi
{
  "tagihan_siswa_id": 1,  // nominal = 100,000
  "jenis_mutasi": "diskon",
  "nominal_perubahan": -200000
}

Expected Response: 400 Bad Request
{
  "success": false,
  "message": "Nominal tagihan setelah mutasi tidak boleh negatif"
}
```

---

## Database Schema

### tagihan_siswa
```sql
- id
- tagihan_id (FK)
- siswa_id (FK)
- nominal
- sisa_nominal
- status (0=Belum Bayar, 1=Lunas, 2=Cicilan)
- catatan
- created_at
- updated_at
```

### pembayaran_tagihan
```sql
- id
- code_pembayaran (unique)
- tagihan_siswa_id (FK)
- jumlah_bayar
- tanggal_bayar
- metode_bayar
- keterangan
- create_by (FK user)
- created_at
- updated_at
```

### tagihan_siswa_mutasi (NEW)
```sql
- id
- code_mutasi (unique)
- tagihan_siswa_id (FK)
- jenis_mutasi (enum)
- nominal_sebelum
- nominal_perubahan
- nominal_sesudah
- keterangan
- alasan
- created_by (FK user)
- approved_by (FK user)
- approved_at
- status_mutasi (pending, approved, rejected)
- created_at
- updated_at
```

---

## API Response Format

### Success Response
```json
{
  "success": true,
  "data": {
    // response data
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // validation errors (if applicable)
  }
}
```

---

## Quick Reference

### Status Codes
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `404`: Not Found
- `422`: Validation Error
- `500`: Server Error

### Bill Status
- `0`: Belum Bayar (Unpaid)
- `1`: Lunas (Paid in full)
- `2`: Cicilan (Partial payment)

### Mutation Status
- `pending`: Waiting for approval
- `approved`: Approved and applied
- `rejected`: Rejected

---

## Notes

1. All monetary values are in IDR (Indonesian Rupiah)
2. All dates use ISO 8601 format (Y-m-d)
3. Bearer token required for all protected endpoints
4. Transactions use database transactions for data integrity
5. Financial records create journal entries automatically
6. Mutations maintain complete audit trail

---

## Support

For issues or questions:
- Check API logs: `storage/logs/laravel.log`
- Review migration status: `php artisan migrate:status`
- Clear cache if needed: `php artisan cache:clear`
