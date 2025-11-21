# Videaclass API Integration - Attendance Synchronization

## Overview
Integrasi API Videaclass untuk sinkronisasi data presensi guru/staff ke dalam sistem payroll. Data yang disinkronisasi mencakup:
- `presence_count` - Jumlah hadir
- `absence_count` - Jumlah absen
- `is_active` - Status aktif

## Components Created

### 1. VideaclassApiHelper (`app/Helpers/VideaclassApiHelper.php`)
Helper class untuk komunikasi dengan API Videaclass.

**Fitur:**
- Menyimpan base URL dan Bearer token
- Method `getAttendanceSessionReport()` - Mengambil laporan attendance session
- Method `syncAttendanceData()` - Wrapper untuk sinkronisasi

**API Details:**
```
Base URL: https://videaclass.com/api/v1
Bearer Key: d4ZKJcFcACb8oHQKb3VV9MeTfu0VRboDDkypL3GH81lcSMFYqdH8ElTzVuVNdgcusoGP4l9wDwVXWWnltJu3QLHRhFPqzezJHIEbbSopIv3uGeCSsCOdo2r5VRhTGccp
Endpoint: /{unit_id}/attendance/session/report
Query Parameters:
  - page: halaman data (default: 1)
  - limit: jumlah data per halaman (default: 50)
  - search: parameter pencarian (opsional)
  - is_count: parameter untuk counting (default: true)
```

### 2. AttendanceSync Model (`app/Models/AttendanceSync.php`)
Model untuk menyimpan data presensi yang sudah disinkronisasi.

**Table Schema:**
```sql
- id: Primary Key
- unit_id: Foreign Key ke units table
- officer_id: Foreign Key ke officers table
- videaclass_id: ID dari Videaclass
- registered_number: Nomor registrasi dari Videaclass
- fullname: Nama lengkap dari Videaclass
- presence_count: Jumlah hadir (default: 0)
- absence_count: Jumlah absen (default: 0)
- is_active: Status aktif (default: true)
- synced_at: Waktu terakhir sinkronisasi
- timestamps: created_at, updated_at
```

**Scopes yang Tersedia:**
- `active()` - Filter data aktif
- `byUnit($unitId)` - Filter berdasarkan unit
- `byOfficer($officerId)` - Filter berdasarkan officer

### 3. Database Migrations

#### 2025_11_21_create_attendance_syncs_table.php
Membuat table `attendance_syncs` untuk menyimpan data presensi yang disinkronisasi.

#### 2025_11_21_add_registered_number_to_officers_table.php
Menambahkan field `registered_number` ke table `officers` untuk mapping dengan Videaclass.

### 4. PayrollPaymentController Methods

#### syncAttendance(Request $request)
**Endpoint:** `POST /payroll-payment/sync-attendance`

**Request Parameters:**
```json
{
  "unit_id": "1",
  "officer_id": "1", // optional
  "search": "han"    // optional
}
```

**Process:**
1. Fetch data dari Videaclass API
2. Iterate setiap record dari response
3. Cari officer berdasarkan `registered_number` di database
4. Create/Update record di table `attendance_syncs`
5. Return synced records dengan metadata

**Response Success:**
```json
{
  "success": true,
  "message": "Data presensi berhasil disinkronisasi. Synced: 1, Error: 0",
  "synced_count": 1,
  "error_count": 0,
  "data": [
    {
      "id": 1,
      "officer_id": 1,
      "officer_name": "Han Guru",
      "registered_number": "han",
      "fullname": "HAN GURU",
      "presence_count": 3,
      "absence_count": 28,
      "is_active": true
    }
  ]
}
```

#### getAttendanceData(Request $request)
**Endpoint:** `GET /payroll-payment/getAttendanceData`

**Request Parameters:**
```json
{
  "officer_id": "1",
  "unit_id": "1"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "presence_count": 3,
    "absence_count": 28,
    "is_active": true,
    "synced_at": "2025-11-21T10:30:00Z"
  }
}
```

## Frontend Integration

### Button "Sinkronkan Presensi"
Located di: `resources/views/pages/penggajian/payroll_payment/payroll_payment.blade.php`

**Behavior:**
1. User klik button "Sinkronkan Presensi"
2. Validasi: Unit harus sudah dipilih
3. Show loading state dengan spinner
4. Send POST request ke `/payroll-payment/sync-attendance`
5. Display response kepada user dengan alert
6. Log response ke console untuk debugging

**Request Payload:**
```javascript
{
  unit_id: unitSelect.value,
  officer_id: officerSelect.value || null,
  search: officerId ? null : 'han'
}
```

## Usage Guide

### 1. Persiapan Data
Pastikan field `registered_number` di table officers sudah terisi sesuai dengan nomor registrasi di Videaclass.

### 2. Melakukan Sinkronisasi
```
1. Buka halaman Payroll Payment (http://127.0.0.1:8000/payroll-payment)
2. Pilih Unit
3. (Optional) Pilih Guru/Staff
4. Klik button "Sinkronkan Presensi"
5. Tunggu proses selesai
6. Lihat hasil di alert atau console
```

### 3. Mengambil Data Presensi
Gunakan endpoint `GET /payroll-payment/getAttendanceData` dengan parameter:
- `officer_id`: ID officer
- `unit_id`: ID unit

### 4. Programmatic Usage

```php
use App\Helpers\VideaclassApiHelper;

$videaclassApi = new VideaclassApiHelper();
$data = $videaclassApi->syncAttendanceData($unitId, $search);

// $data akan berisi:
// {
//   "page_count": 1,
//   "rows": [
//     {
//       "id": 11785,
//       "registered_number": "han",
//       "fullname": "HAN GURU",
//       "presence_count": 3,
//       "absence_count": 28,
//       "is_active": true,
//       ...
//     }
//   ]
// }
```

## Error Handling

### API Level
- Jika API gagal, response akan berisi HTTP status dan error body
- Error di-log ke file log untuk debugging

### Database Level
- Jika officer tidak ditemukan, record akan di-skip (error_count++)
- Exception pada setiap record di-catch dan di-log
- Return total synced_count dan error_count

### Frontend Level
- Validasi unit harus dipilih sebelum sinkronisasi
- Show spinner saat loading
- Display alert dengan result atau error message
- Log response ke console

## Security Considerations

1. **Bearer Token** disimpan di helper class
   - Untuk production, gunakan environment variable
   - Update: `app/Helpers/VideaclassApiHelper.php`
   ```php
   private $bearerKey = env('VIDEACLASS_BEARER_KEY');
   ```
   - Tambahkan ke `.env`:
   ```
   VIDEACLASS_BEARER_KEY=d4ZKJcFcACb8oHQKb3VV9MeTfu0VRboDDkypL3GH81lcSMFYqdH8ElTzVuVNdgcusoGP4l9wDwVXWWnltJu3QLHRhFPqzezJHIEbbSopIv3uGeCSsCOdo2r5VRhTGccp
   ```

2. **CSRF Protection**
   - Token di-include di fetch request
   - Route POST dilindungi dengan middleware auth

## Testing

### Unit Test Example
```php
public function test_sync_attendance_data()
{
    $unit = Unit::factory()->create();
    $officer = Officer::factory()->create([
        'unit_id' => $unit->id,
        'registered_number' => 'han'
    ]);

    $response = $this->postJson('/payroll-payment/sync-attendance', [
        'unit_id' => $unit->id,
        'officer_id' => $officer->id
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'synced_count',
            'error_count',
            'data'
        ]);
}
```

### Manual Testing
1. Pastikan unit dan officer sudah ada di database
2. Pastikan officer memiliki `registered_number` yang sesuai dengan Videaclass
3. Klik button "Sinkronkan Presensi"
4. Check database table `attendance_syncs` untuk verify data tersimpan
5. Check logs/storage/logs untuk error handling

## Troubleshooting

### Data tidak tersinkronisasi
- Check: Apakah registered_number di officers sesuai dengan Videaclass?
- Check: Apakah API key masih valid?
- Check: Log file di storage/logs/laravel.log

### "Officer not found" error
- Pastikan registered_number di officers table sudah tepat
- Update officer dengan registered_number yang benar

### API timeout
- Check koneksi internet
- Check URL API Videaclass masih aktif
- Coba increase timeout di Http request

## Future Enhancements

1. **Batch Processing** - Proses multiple units sekaligus
2. **Scheduled Task** - Auto sync pada waktu tertentu
3. **Webhook** - Real-time sync dari Videaclass
4. **Data Validation** - Validasi presence_count & absence_count
5. **History Tracking** - Track perubahan data presensi
6. **Integration dengan Payroll** - Auto update payroll berdasarkan presensi

## Files Modified/Created

### Created:
- `app/Helpers/VideaclassApiHelper.php`
- `app/Models/AttendanceSync.php`
- `database/migrations/2025_11_21_create_attendance_syncs_table.php`
- `database/migrations/2025_11_21_add_registered_number_to_officers_table.php`

### Modified:
- `app/Http/Controllers/PayrollPaymentController.php`
- `app/Models/Officer.php`
- `routes/web.php`
- `resources/views/pages/penggajian/payroll_payment/payroll_payment.blade.php`

## Next Steps

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Update officers dengan registered_number dari Videaclass

3. Test sinkronisasi di halaman payroll-payment

4. Monitor logs untuk error handling

5. Integrate attendance data dengan payroll calculation (future)
