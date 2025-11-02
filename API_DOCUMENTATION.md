# School Management API Documentation

## Overview
API ini menyediakan endpoint untuk mengelola sistem manajemen sekolah dengan autentikasi JWT (JSON Web Token).

## Base URL
```
http://your-domain.com/api/v1
```

## Authentication
API ini menggunakan JWT (JSON Web Token) untuk autentikasi. Setelah login, Anda akan menerima token yang harus disertakan dalam header setiap request.

### Header Format
```
Authorization: Bearer {your-token-here}
```

## API Documentation UI
Akses dokumentasi interaktif Swagger UI di:
```
http://your-domain.com/api/documentation
```

## Available Endpoints

### Authentication

#### 1. Login
**Endpoint:** `POST /api/v1/auth/login`

**Request Body:**
```json
{
  "username": "admin",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "username": "admin"
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

#### 2. Register
**Endpoint:** `POST /api/v1/auth/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "username": "johndoe",
  "password": "password123",
  "password_confirmation": "password123",
  "unit_id": 1
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user": {
    "id": 2,
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

#### 3. Get Current User
**Endpoint:** `GET /api/v1/auth/me`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "username": "admin",
    "roles": [],
    "permissions": []
  }
}
```

#### 4. Logout
**Endpoint:** `POST /api/v1/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

#### 5. Refresh Token
**Endpoint:** `POST /api/v1/auth/refresh`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {...}
}
```

---

### Siswa (Students)

#### 1. Get All Siswa
**Endpoint:** `GET /api/v1/siswa`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)
- `search` (optional): Search by name, NIS, or NISN
- `kelas_id` (optional): Filter by class ID
- `unit_id` (optional): Filter by unit ID

**Example:**
```
GET /api/v1/siswa?per_page=20&search=Ahmad&unit_id=1
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "nama": "Ahmad Fauzi",
        "nis": "2023001",
        "nisn": "0012345678",
        "jenis_kelamin": "L",
        "kelas": {
          "id": 1,
          "nama_kelas": "X IPA 1"
        },
        "unit": {
          "id": 1,
          "nama_unit": "SMA"
        }
      }
    ],
    "per_page": 15,
    "total": 100
  }
}
```

#### 2. Get Siswa by ID
**Endpoint:** `GET /api/v1/siswa/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama": "Ahmad Fauzi",
    "nis": "2023001",
    "nisn": "0012345678",
    "jenis_kelamin": "L",
    "tempat_lahir": "Jakarta",
    "tanggal_lahir": "2008-05-15",
    "alamat": "Jl. Merdeka No. 123",
    "kelas": {...},
    "unit": {...},
    "jurusan": {...}
  }
}
```

#### 3. Create Siswa
**Endpoint:** `POST /api/v1/siswa`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "nama": "Ahmad Fauzi",
  "nis": "2023001",
  "nisn": "0012345678",
  "jenis_kelamin": "L",
  "tempat_lahir": "Jakarta",
  "tanggal_lahir": "2008-05-15",
  "alamat": "Jl. Merdeka No. 123",
  "kelas_id": 1,
  "unit_id": 1,
  "jurusan_id": 1,
  "tahun_masuk": "2023",
  "status": "aktif"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Siswa created successfully",
  "data": {...}
}
```

#### 4. Update Siswa
**Endpoint:** `PUT /api/v1/siswa/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:** (Same as Create, all fields optional)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Siswa updated successfully",
  "data": {...}
}
```

#### 5. Delete Siswa
**Endpoint:** `DELETE /api/v1/siswa/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Siswa deleted successfully"
}
```

#### 6. Get Siswa by Kelas
**Endpoint:** `GET /api/v1/siswa/kelas/{kelasId}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": [...]
}
```

---

### Kelas (Classes)

#### 1. Get All Kelas
**Endpoint:** `GET /api/v1/kelas`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `per_page` (optional): Items per page
- `search` (optional): Search by class name
- `unit_id` (optional): Filter by unit ID

#### 2. Get Kelas by ID
**Endpoint:** `GET /api/v1/kelas/{id}`

#### 3. Create Kelas
**Endpoint:** `POST /api/v1/kelas`

**Request Body:**
```json
{
  "nama_kelas": "X IPA 1",
  "tingkat": 10,
  "unit_id": 1,
  "jurusan_id": 1,
  "kapasitas": 36
}
```

#### 4. Update Kelas
**Endpoint:** `PUT /api/v1/kelas/{id}`

#### 5. Delete Kelas
**Endpoint:** `DELETE /api/v1/kelas/{id}`

#### 6. Get Siswa in Kelas
**Endpoint:** `GET /api/v1/kelas/{id}/siswa`

---

### Officer (Teachers/Staff)

#### 1. Get All Officers
**Endpoint:** `GET /api/v1/officer`

**Query Parameters:**
- `per_page` (optional)
- `search` (optional): Search by name, NIP, or email
- `unit_id` (optional)

#### 2. Get Officer by ID
**Endpoint:** `GET /api/v1/officer/{id}`

#### 3. Create Officer
**Endpoint:** `POST /api/v1/officer`

**Request Body:**
```json
{
  "nama": "Budi Santoso",
  "nip": "198505152010011001",
  "email": "budi@school.com",
  "jenis_kelamin": "L",
  "tempat_lahir": "Bandung",
  "tanggal_lahir": "1985-05-15",
  "alamat": "Jl. Pendidikan No. 45",
  "unit_id": 1,
  "position_id": 1,
  "status": "aktif"
}
```

#### 4. Update Officer
**Endpoint:** `PUT /api/v1/officer/{id}`

#### 5. Delete Officer
**Endpoint:** `DELETE /api/v1/officer/{id}`

---

### Tagihan (Bills)

#### 1. Get All Tagihan
**Endpoint:** `GET /api/v1/tagihan`

**Query Parameters:**
- `per_page` (optional)
- `search` (optional)
- `unit_id` (optional)
- `kelas_id` (optional)

#### 2. Get Tagihan by ID
**Endpoint:** `GET /api/v1/tagihan/{id}`

#### 3. Create Tagihan
**Endpoint:** `POST /api/v1/tagihan`

**Request Body:**
```json
{
  "nama_tagihan": "SPP Bulan Januari",
  "kategori_id": 1,
  "unit_id": 1,
  "kelas_id": 1,
  "tahun_ajaran_id": 1,
  "jumlah": 500000,
  "jenis": "bulanan",
  "tanggal_jatuh_tempo": "2024-01-10"
}
```

#### 4. Update Tagihan
**Endpoint:** `PUT /api/v1/tagihan/{id}`

#### 5. Delete Tagihan
**Endpoint:** `DELETE /api/v1/tagihan/{id}`

#### 6. Get Tagihan by Siswa
**Endpoint:** `GET /api/v1/tagihan/siswa/{siswaId}`

---

---

### Pembayaran (Payments)

#### 1. Get All Pembayaran
**Endpoint:** `GET /api/v1/pembayaran`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `per_page` (optional): Items per page
- `siswa_id` (optional): Filter by student ID
- `kelas_id` (optional): Filter by class ID
- `start_date` (optional): Start date (Y-m-d)
- `end_date` (optional): End date (Y-m-d)

**Example:**
```
GET /api/v1/pembayaran?siswa_id=1&start_date=2024-01-01&end_date=2024-01-31
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "code_pembayaran": "PAY202401011234567890",
        "tagihan_siswa_id": 1,
        "jumlah_bayar": 500000,
        "tanggal_bayar": "2024-01-15 10:30:00",
        "metode_bayar": "CASH",
        "keterangan": "Pembayaran SPP Januari",
        "tagihanSiswa": {
          "siswa": {...},
          "tagihan": {...}
        }
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

#### 2. Process Payment (Bayar Tagihan)
**Endpoint:** `POST /api/v1/pembayaran`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "tagihan_siswa_id": 1,
  "jumlah_bayar": 500000,
  "metode": "CASH",
  "keterangan": "Pembayaran SPP Januari 2024"
}
```

**Metode Pembayaran:**
- `CASH` - Tunai
- `TRANSFER` - Transfer Bank
- `QRIS` - QRIS
- `VIRTUAL_ACCOUNT` - Virtual Account
- `MOBILE_BANKING` - Mobile Banking

**Success Response (201):**
```json
{
  "success": true,
  "message": "Pembayaran lunas berhasil",
  "data": {
    "pembayaran": {
      "id": 1,
      "code_pembayaran": "PAY202401011234567890",
      "tagihan_siswa_id": 1,
      "jumlah_bayar": 500000,
      "tanggal_bayar": "2024-01-15 10:30:00",
      "metode_bayar": "CASH",
      "keterangan": "Pembayaran SPP Januari 2024"
    },
    "tagihan_siswa": {
      "id": 1,
      "siswa_id": 1,
      "tagihan_id": 1,
      "nominal": 500000,
      "sisa_nominal": 0,
      "status": 1
    },
    "transaksi": {
      "id": 1,
      "code_pembayaran": "PAY202401011234567890",
      "jumlah": 500000,
      "jenis_transaksi": "tagihan"
    }
  }
}
```

**Error Response (400) - Already Paid:**
```json
{
  "success": false,
  "message": "Tagihan ini sudah lunas"
}
```

**Error Response (400) - Amount Exceeds:**
```json
{
  "success": false,
  "message": "Jumlah bayar tidak boleh lebih besar dari sisa tagihan",
  "data": {
    "jumlah_bayar": 600000,
    "sisa_nominal": 500000
  }
}
```

#### 3. Get Payment Detail
**Endpoint:** `GET /api/v1/pembayaran/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code_pembayaran": "PAY202401011234567890",
    "jumlah_bayar": 500000,
    "tanggal_bayar": "2024-01-15 10:30:00",
    "metode_bayar": "CASH",
    "tagihanSiswa": {
      "siswa": {
        "nama": "Ahmad Fauzi",
        "nis": "2023001",
        "kelas": {...}
      },
      "tagihan": {
        "nama_tagihan": "SPP Januari 2024",
        "kategori": {...}
      }
    }
  }
}
```

#### 4. Get Payment History by Student
**Endpoint:** `GET /api/v1/pembayaran/siswa/{siswaId}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "siswa": {
      "id": 1,
      "nama": "Ahmad Fauzi",
      "nis": "2023001",
      "kelas": {...}
    },
    "pembayaran": [
      {
        "id": 1,
        "code_pembayaran": "PAY202401011234567890",
        "jumlah_bayar": 500000,
        "tanggal_bayar": "2024-01-15 10:30:00"
      }
    ],
    "summary": {
      "total_bayar": 5000000,
      "jumlah_transaksi": 10
    }
  }
}
```

#### 5. Get Payments by Class
**Endpoint:** `GET /api/v1/pembayaran/kelas/{kelasId}`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `start_date` (optional): Y-m-d
- `end_date` (optional): Y-m-d

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "pembayaran": [...],
    "summary": {
      "total_bayar": 15000000,
      "jumlah_transaksi": 30,
      "periode": {
        "start_date": "2024-01-01",
        "end_date": "2024-01-31"
      }
    }
  }
}
```

#### 6. Get Payments for Specific Tagihan Siswa
**Endpoint:** `GET /api/v1/pembayaran/tagihan-siswa/{tagihanSiswaId}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tagihan_siswa": {
      "id": 1,
      "nominal": 500000,
      "sisa_nominal": 0,
      "status": 1,
      "siswa": {...},
      "tagihan": {...}
    },
    "pembayaran": [
      {
        "id": 1,
        "jumlah_bayar": 300000,
        "tanggal_bayar": "2024-01-10"
      },
      {
        "id": 2,
        "jumlah_bayar": 200000,
        "tanggal_bayar": "2024-01-15"
      }
    ],
    "summary": {
      "nominal_tagihan": 500000,
      "total_bayar": 500000,
      "sisa_nominal": 0,
      "status": "Lunas"
    }
  }
}
```

#### 7. Get Payment Receipt
**Endpoint:** `GET /api/v1/pembayaran/receipt/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "code_pembayaran": "PAY202401011234567890",
    "tanggal_bayar": "2024-01-15 10:30:00",
    "siswa": {
      "nama": "Ahmad Fauzi",
      "nis": "2023001",
      "kelas": "X IPA 1",
      "unit": "SMA"
    },
    "tagihan": {
      "nama": "SPP Januari 2024",
      "kategori": "SPP",
      "nominal_tagihan": 500000,
      "jumlah_bayar": 500000,
      "sisa": 0,
      "status": "LUNAS"
    },
    "metode_bayar": "CASH",
    "keterangan": "Pembayaran SPP Januari 2024",
    "petugas": "Admin User"
  }
}
```

---

### Tagihan Siswa (Student Bills)

#### 1. Get All Tagihan Siswa
**Endpoint:** `GET /api/v1/tagihan-siswa`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `per_page` (optional): Items per page
- `siswa_id` (optional): Filter by student ID
- `tagihan_id` (optional): Filter by tagihan ID
- `status` (optional): 0=Belum Bayar, 1=Lunas, 2=Cicilan
- `kelas_id` (optional): Filter by class ID

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "siswa_id": 1,
        "tagihan_id": 1,
        "nominal": 500000,
        "sisa_nominal": 200000,
        "status": 2,
        "siswa": {...},
        "tagihan": {...},
        "pembayaranTagihan": [...]
      }
    ]
  }
}
```

#### 2. Get Tagihan Siswa Detail
**Endpoint:** `GET /api/v1/tagihan-siswa/{id}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tagihan_siswa": {
      "id": 1,
      "nominal": 500000,
      "sisa_nominal": 200000,
      "status": 2,
      "pembayaranTagihan": [...]
    },
    "summary": {
      "nominal_tagihan": 500000,
      "total_bayar": 300000,
      "sisa_nominal": 200000,
      "status": "Cicilan",
      "jumlah_pembayaran": 2
    }
  }
}
```

#### 3. Get All Bills for a Student
**Endpoint:** `GET /api/v1/tagihan-siswa/siswa/{siswaId}`

**Query Parameters:**
- `status` (optional): Filter by status

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tagihan_siswa": [...],
    "summary": {
      "total_tagihan": 5000000,
      "total_bayar": 3000000,
      "total_sisa": 2000000,
      "jumlah_tagihan": 10,
      "lunas": 5,
      "cicilan": 3,
      "belum_bayar": 2
    }
  }
}
```

#### 4. Get Bills by Class
**Endpoint:** `GET /api/v1/tagihan-siswa/kelas/{kelasId}`

**Query Parameters:**
- `tagihan_id` (optional): Filter by specific tagihan
- `status` (optional): Filter by status

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tagihan_siswa": [...],
    "summary": {
      "total_tagihan": 15000000,
      "total_bayar": 10000000,
      "total_sisa": 5000000,
      "jumlah_siswa": 30,
      "jumlah_tagihan": 30,
      "lunas": 20,
      "cicilan": 5,
      "belum_bayar": 5
    }
  }
}
```

#### 5. Get Unpaid Bills for Student
**Endpoint:** `GET /api/v1/tagihan-siswa/unpaid/{siswaId}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "tagihan_siswa": [
      {
        "id": 1,
        "nominal": 500000,
        "sisa_nominal": 200000,
        "status": 2,
        "tagihan": {...}
      }
    ],
    "summary": {
      "total_sisa": 700000,
      "jumlah_tagihan": 3
    }
  }
}
```

---

### Other Resources

Semua resource berikut mengikuti pola CRUD yang sama:

- **Unit**: `/api/v1/unit`
- **Tahun Ajaran**: `/api/v1/tahun-ajaran`
- **Jurusan**: `/api/v1/jurusan`
- **Kategori Tagihan**: `/api/v1/kategori-tagihan`
- **Potongan**: `/api/v1/potongan`
- **Roles**: `/api/v1/roles`

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "field_name": [
      "Error message here"
    ]
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## Testing with Postman

### Setup
1. Import koleksi API ke Postman
2. Buat environment variable:
   - `base_url`: `http://your-domain.com/api/v1`
   - `token`: (akan di-set otomatis setelah login)

### Login Flow
1. Request login ke `/auth/login`
2. Copy `access_token` dari response
3. Set sebagai environment variable `token`
4. Gunakan `{{token}}` di Authorization header untuk request berikutnya

---

## Testing with cURL

### Login
```bash
curl -X POST http://your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password123"
  }'
```

### Get Siswa dengan Token
```bash
curl -X GET http://your-domain.com/api/v1/siswa \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### Create Siswa
```bash
curl -X POST http://your-domain.com/api/v1/siswa \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "Ahmad Fauzi",
    "nis": "2023001",
    "jenis_kelamin": "L",
    "kelas_id": 1,
    "unit_id": 1
  }'
```

---

## Rate Limiting
API ini menggunakan rate limiting standar Laravel:
- 60 requests per menit untuk authenticated routes
- 10 requests per menit untuk login endpoint

---

## Versioning
API saat ini menggunakan versi v1. URL format: `/api/v1/{resource}`

Versi baru akan ditambahkan sebagai `/api/v2/{resource}` di masa depan tanpa menghilangkan v1.

---

## Support
Untuk bantuan atau pertanyaan, hubungi tim development atau buka issue di repository.

## Changelog

### Version 1.0.0 (2024-01-01)
- Initial API release
- JWT Authentication
- CRUD operations for all main resources
- Swagger documentation
- Pagination and search support
