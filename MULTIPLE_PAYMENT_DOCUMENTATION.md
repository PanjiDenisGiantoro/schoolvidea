# Multiple Payment Processing - Dokumentasi

## Deskripsi
Function `prosesMultiplePembayaran` di PembayaranController memungkinkan pengguna untuk menggabungkan beberapa pembayaran tagihan menjadi 1 pembayaran utama (master payment). Ini sangat berguna ketika siswa membayar multiple tagihan sekaligus.

## Endpoint
```
POST /pembayaran/proses-multiple
```

## Request Parameters

```json
{
  "tagihan_siswa_ids": [1, 2, 3],
  "jumlah_bayar": 1500000,
  "metode": "tunai"
}
```

### Parameter Validation:
- **tagihan_siswa_ids**:
  - Type: array
  - Required: yes
  - Min items: 2
  - Items must exist in `tagihan_siswa` table

- **jumlah_bayar**:
  - Type: integer
  - Required: yes
  - Min value: 1

- **metode**:
  - Type: string
  - Required: no
  - Default: 'tunai'
  - Examples: 'tunai', 'transfer', 'cek', etc.

## Business Logic

### 1. Data Validation
```php
// Semua tagihan harus milik siswa yang sama
$firstSiswaId = $tagihanSiswaList->first()->siswa_id;
$allSameSiswa = $tagihanSiswaList->every(fn($ts) => $ts->siswa_id === $firstSiswaId);

if (!$allSameSiswa) {
    return error: 'Semua tagihan harus milik siswa yang sama'
}
```

### 2. Payment Distribution
Pembayaran didistribusikan ke masing-masing tagihan secara proporsional:

```
Contoh:
- Tagihan 1: Rp 500.000 (sisa)
- Tagihan 2: Rp 600.000 (sisa)
- Tagihan 3: Rp 400.000 (sisa)
- Total Sisa: Rp 1.500.000
- Jumlah Bayar: Rp 1.200.000

Distribusi:
- Tagihan 1: Bayar min(1.200.000, 500.000) = Rp 500.000 → Lunas
- Tagihan 2: Bayar min(700.000, 600.000) = Rp 600.000 → Lunas
- Tagihan 3: Bayar sisa 100.000 → Status Cicilan, Sisa Rp 300.000
```

### 3. Master Payment Record Creation
Membuat 1 pembayaran master dengan kode unik:
```
Code: PS + YmdHis + random(1000-9999)
Contoh: PS20251121143045823
```

### 4. Payment Details Recording
Untuk setiap tagihan, catat:
- Nama tagihan
- Sisa nominal sebelum pembayaran
- Jumlah yang dibayar
- Sisa nominal sesudah pembayaran
- Status baru (0: Belum Bayar, 1: Lunas, 2: Cicilan)

### 5. Accounting Journal Entry
Membuat 2 jurnal (double entry):
- **Debit**: Kas/Bank (uang masuk)
- **Kredit**: Tagihan/Piutang (hutang berkurang)

## Response

### Success Response
```json
{
  "status": true,
  "message": "Pembayaran gabungan berhasil diproses",
  "data": {
    "pembayaran_master": {
      "id": 1,
      "code_pembayaran": "PS20251121143045823",
      "tagihan_siswa_id": 1,
      "jumlah_bayar": 1200000,
      "tanggal_bayar": "2025-11-21T14:30:45Z",
      "metode_bayar": "tunai",
      "keterangan": "Pembayaran gabungan 3 tagihan sebesar Rp 1.200.000",
      "status_approval": "approved"
    },
    "pembayaran_details": [
      {
        "tagihan_siswa_id": 1,
        "tagihan_nama": "Uang Pangkal",
        "sisa_nominal_sebelum": 500000,
        "jumlah_bayar": 500000,
        "sisa_nominal_sesudah": 0,
        "status": "1"
      },
      {
        "tagihan_siswa_id": 2,
        "tagihan_nama": "SPP",
        "sisa_nominal_sebelum": 600000,
        "jumlah_bayar": 600000,
        "sisa_nominal_sesudah": 0,
        "status": "1"
      },
      {
        "tagihan_siswa_id": 3,
        "tagihan_nama": "Seragam",
        "sisa_nominal_sebelum": 400000,
        "jumlah_bayar": 100000,
        "sisa_nominal_sesudah": 300000,
        "status": "2"
      }
    ],
    "siswa": {
      "id": 5,
      "nama": "John Doe"
    },
    "summary": {
      "jumlah_tagihan": 3,
      "total_sisa_nominal_sebelum": 1500000,
      "jumlah_bayar": 1200000,
      "total_sisa_nominal_sesudah": 300000
    }
  }
}
```

### Error Response

#### Validation Error
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "tagihan_siswa_ids": ["The tagihan siswa ids field is required."],
    "jumlah_bayar": ["The jumlah bayar field is required."]
  }
}
```

#### Different Student Error
```json
{
  "status": false,
  "message": "Semua tagihan harus milik siswa yang sama"
}
```

#### Payment Amount Exceeds Total
```json
{
  "status": false,
  "message": "Jumlah bayar tidak boleh lebih besar dari total sisa tagihan (Rp 1.500.000)"
}
```

#### Database Error
```json
{
  "status": false,
  "message": "Terjadi kesalahan: [error message]"
}
```

## Database Updates

### Tables Modified:
1. **pembayaran_tagihan** - Tambah record pembayaran master
2. **tagihan_siswa** - Update status dan sisa_nominal untuk setiap tagihan
3. **tagihans** - Update status_tagihan jika semua items lunas
4. **keuangan_transaksis** - Catat transaksi master
5. **jurnals** - Catat double entry journal

## Usage Example

### JavaScript Fetch
```javascript
const tagihanIds = [1, 2, 3];
const jumlahBayar = 1200000;
const metode = 'tunai';

fetch('/pembayaran/proses-multiple', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    tagihan_siswa_ids: tagihanIds,
    jumlah_bayar: jumlahBayar,
    metode: metode
  })
})
.then(response => response.json())
.then(data => {
  if (data.status) {
    console.log('Payment success:', data.data);
    alert(`Pembayaran berhasil! Kode: ${data.data.pembayaran_master.code_pembayaran}`);
  } else {
    alert('Error: ' + data.message);
  }
})
.catch(error => console.error('Error:', error));
```

### PHP/Curl Example
```bash
curl -X POST http://127.0.0.1:8000/pembayaran/proses-multiple \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -d '{
    "tagihan_siswa_ids": [1, 2, 3],
    "jumlah_bayar": 1200000,
    "metode": "tunai"
  }'
```

## Key Features

### 1. Transaction Safety
- Menggunakan `DB::beginTransaction()` dan `DB::commit()`
- Automatic rollback jika terjadi error

### 2. Data Integrity
- Validasi semua input
- Cek semua tagihan milik siswa yang sama
- Cek payment amount valid
- Proporsional distribution

### 3. Complete Accounting
- Master payment record
- Payment details untuk tracking
- Double entry journal
- Complete audit trail

### 4. Status Management
- Auto-update tagihan status (Belum Bayar → Cicilan → Lunas)
- Auto-update tagihan utama status jika semua items lunas
- Timestamp tracking

## Constraints & Validations

1. **Minimum 2 tagihan**: Tidak bisa membayar 1 tagihan (gunakan endpoint `/pembayaran/store`)
2. **Same Student**: Semua tagihan harus milik siswa yang sama
3. **Valid Amount**: Jumlah bayar harus ≤ total sisa nominal
4. **Existing Records**: Semua tagihan_siswa_id harus exist di database

## Error Handling

Function ini memiliki comprehensive error handling:
- Validation errors (400)
- Business logic errors (400)
- Database errors (500)
- Exception handling dengan rollback

## Performance Considerations

- Query optimization dengan eager loading (`with()`)
- Single database transaction untuk atomicity
- Efficient array operations untuk distribution

## Future Enhancements

1. **Selective Distribution** - Allow custom distribution per tagihan
2. **Payment Plans** - Auto-create cicilan schedule
3. **SMS Notification** - Send receipt via SMS
4. **Email Receipt** - Send receipt to registered email
5. **Batch Processing** - Process multiple students at once
6. **Automated Journaling** - Configurable journal accounts

## Testing Checklist

- [ ] Verify master payment created correctly
- [ ] Verify all tagihan updated with correct status
- [ ] Verify journal entries created correctly (debit/credit)
- [ ] Verify proporsional distribution is accurate
- [ ] Verify error cases handled properly
- [ ] Verify different student validation works
- [ ] Verify amount validation works
- [ ] Test with various payment amounts
- [ ] Test with 2 tagihan, 3 tagihan, more than 3 tagihan
- [ ] Verify response data structure
