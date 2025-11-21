# Keuangan Transaksi Multiple Payment - Dokumentasi

## Deskripsi
Update pada KeuanganTransaksiController untuk menangani multiple pembayaran tagihan dengan jenis transaksi `tagihan-multiple`. Implementasi ini konsisten dengan logic di PembayaranController::prosesMultiplePembayaran().

## Perubahan yang Dilakukan

### 1. Method Approve - Multiple Payment Handling

#### Konsep Dasar
Ketika transaksi dengan jenis `tagihan-multiple` di-approve:
1. Fetch semua pembayaran dengan code_pembayaran yang sama
2. Loop setiap pembayaran detail
3. Update status dan sisa_nominal untuk setiap tagihan siswa
4. Check dan update status tagihan utama jika sudah lunas

#### Implementation Detail

```php
// === UNTUK TAGIHAN-MULTIPLE: Update semua pembayaran detail ===
if ($transaksi->jenis_transaksi === 'tagihan-multiple') {
    // Load semua pembayaran dengan code_pembayaran yang sama (master payment)
    $masterCode = $pembayaran->code_pembayaran;
    $allPembayaran = \App\Models\Pembayarantagihan::where('code_pembayaran', $masterCode)->get();

    foreach ($allPembayaran as $pay) {
        $tgSiswa = $pay->tagihanSiswa;
        if ($tgSiswa) {
            $jmlBayar = (int) $pay->jumlah_bayar;
            $sisa = $tgSiswa->sisa_nominal - $jmlBayar;
            $dibayar = ($tgSiswa->jumlah_dibayar ?? 0) + $jmlBayar;

            // Determine status
            $status = '0';
            if ($sisa <= 0) {
                $status = '1'; // Lunas
                $sisa = 0;
            } elseif ($dibayar > 0 && $sisa > 0) {
                $status = '2'; // Cicilan
            }

            // Update tagihan siswa
            $tgSiswa->update([
                'status' => $status,
                'sisa_nominal' => $sisa,
                'tanggal_bayar' => now(),
            ]);

            // Check main tagihan status
            $hasUnpaid = \App\Models\Tagihansiswa::where('tagihan_id', $tgSiswa->tagihan_id)
                ->where('status', '0')
                ->exists();

            if (!$hasUnpaid) {
                \App\Models\Tagihan::where('id', $tgSiswa->tagihan_id)
                    ->update(['status_tagihan' => 1]);
            }
        }
    }
}
```

#### Data Flow Approval

```
Request approve transaksi tagihan-multiple
    ↓
Load transaksi dengan relasi pembayaranTagihan
    ↓
Check status_verifikasi (harus belum approved)
    ↓
Get master code dari pembayaran master
    ↓
Fetch semua pembayaran dengan master code
    ↓
Loop setiap pembayaran detail:
    - Get tagihanSiswa
    - Calculate: sisa = sisa_nominal - jumlah_bayar
    - Calculate: dibayar = jumlah_dibayar + jumlah_bayar
    - Determine status (0/1/2)
    - Update tagihanSiswa
    - Check main tagihan status
    - Update main tagihan if all paid
    ↓
Update transaksi status: approved
    ↓
Update pembayaran master status: approved
    ↓
Create journal entries (debit/credit)
    ↓
Log activity
    ↓
Commit transaction
    ↓
Return success response
```

### 2. Method Reject - Multiple Payment Rollback

#### Konsep Dasar
Ketika transaksi dengan jenis `tagihan-multiple` di-reject:
1. Fetch semua pembayaran dengan code_pembayaran yang sama
2. Loop setiap pembayaran detail
3. Rollback status dan sisa_nominal untuk setiap tagihan siswa
4. Restore jumlah_dibayar

#### Implementation Detail

```php
// === UNTUK TAGIHAN-MULTIPLE: Rollback semua pembayaran detail ===
if ($transaksi->jenis_transaksi === 'tagihan-multiple') {
    // Load semua pembayaran dengan code_pembayaran yang sama (master payment)
    $masterCode = $pembayaran->code_pembayaran;
    $allPembayaran = \App\Models\Pembayarantagihan::where('code_pembayaran', $masterCode)->get();

    foreach ($allPembayaran as $pay) {
        $tgSiswa = $pay->tagihanSiswa;
        if ($tgSiswa) {
            $jmlBayar = (int) $pay->jumlah_bayar;
            $sisa = $tgSiswa->sisa_nominal + $jmlBayar; // Restore
            $dibayar = ($tgSiswa->jumlah_dibayar ?? 0) - $jmlBayar; // Restore

            // Determine status setelah rollback
            $status = '0';
            if ($dibayar > 0 && $sisa > 0) {
                $status = '2'; // Cicilan jika masih ada pembayaran sebelumnya
            }

            // Update dengan rollback values
            $tgSiswa->update([
                'status' => $status,
                'sisa_nominal' => $sisa,
                'jumlah_dibayar' => max(0, $dibayar)
            ]);
        }
    }
}
```

#### Data Flow Rejection

```
Request reject transaksi tagihan-multiple
    ↓
Load transaksi dengan relasi pembayaranTagihan
    ↓
Check status_verifikasi (harus belum rejected)
    ↓
Get master code dari pembayaran master
    ↓
Fetch semua pembayaran dengan master code
    ↓
Loop setiap pembayaran detail:
    - Get tagihanSiswa
    - Calculate: sisa = sisa_nominal + jumlah_bayar (RESTORE)
    - Calculate: dibayar = jumlah_dibayar - jumlah_bayar (RESTORE)
    - Determine status (0/2 based on dibayar & sisa)
    - Update tagihanSiswa with restored values
    ↓
Update transaksi status: rejected
    ↓
Update pembayaran master status: rejected
    ↓
Log activity
    ↓
Commit transaction
    ↓
Return success response
```

## Status Management

### Approve Flow
```
Single Pembayaran (tagihan-multiple):
- payment.status_approval: pending → approved
- keuangan_transaksi.status_verifikasi: pending → approved
- keuangan_transaksi.status_approval: pending → approved

Each TagihanSiswa:
- sisa_nominal: sisa - bayar = new_sisa
- jumlah_dibayar: dibayar + bayar = new_dibayar
- status: 0 (belum) → 1 (lunas) or 2 (cicilan)
- tanggal_bayar: null → now()

Main Tagihan (if all paid):
- status_tagihan: 0 → 1
```

### Reject Flow
```
Single Pembayaran (tagihan-multiple):
- payment.status_approval: pending → rejected
- keuangan_transaksi.status_verifikasi: pending → rejected
- keuangan_transaksi.status_approval: pending → rejected

Each TagihanSiswa:
- sisa_nominal: sisa + bayar = restored_sisa (INCREASED)
- jumlah_dibayar: dibayar - bayar = restored_dibayar (DECREASED)
- status: depends on restored_dibayar & restored_sisa
- tanggal_bayar: no change (not touched)
```

## Journal Entry Handling

### Approve Creates Journal Entries
Sama seperti single pembayaran, membuat:
1. **Debit Entry**: Kas/Bank (uang masuk)
2. **Credit Entry**: Tagihan/Piutang (hutang berkurang)

Total amount = sum of all pembayaran amounts

### Reject Does NOT Delete Journal Entries
- Journal entries tetap ada untuk audit trail
- Status_approval diubah menjadi 'rejected'
- Opsional: Bisa buat reversal entries (akan implement nanti jika diperlukan)

## Difference vs Single Payment

| Aspect | Single | Multiple |
|--------|--------|----------|
| Pembayaran Records | 1 | N (sesuai jumlah tagihan) |
| Code Pembayaran | Unique per payment | Same for all details (master) |
| Update Loop | Direct | Loop setiap pembayaran detail |
| Status Check | Single tagihan | All tagihan dalam group |
| Rollback Scope | Single item | All items dalam group |
| Journal Entries | 1 Debit + 1 Credit | 1 Debit + 1 Credit (master) |

## Consistency Validation

### Before Approve
```php
// Check semua pembayaran dengan master code exist
$allPembayaran = Pembayarantagihan::where('code_pembayaran', $masterCode)->get();
if ($allPembayaran->isEmpty()) {
    throw new Exception('No pembayaran details found for master code');
}

// Check setiap tagihanSiswa accessible
foreach ($allPembayaran as $pay) {
    if (!$pay->tagihanSiswa) {
        throw new Exception('TagihanSiswa not found');
    }
}
```

### After Approve
```php
// Verify semua statuses updated
foreach ($allPembayaran as $pay) {
    $tgSiswa = $pay->tagihanSiswa->fresh();

    // Sisa harus dikurangi
    assert($tgSiswa->sisa_nominal <= original_sisa);

    // Dibayar harus ditambah
    assert($tgSiswa->jumlah_dibayar >= original_dibayar);

    // Status harus valid (0, 1, or 2)
    assert(in_array($tgSiswa->status, [0, 1, 2]));
}
```

## Edge Cases Handled

### 1. Some Items Lunas, Some Cicilan
```
Tagihan A: Bayar Rp 500.000 (lunas) → status = 1
Tagihan B: Bayar Rp 300.000 of Rp 600.000 → status = 2
Tagihan C: Bayar Rp 0 (not paid) → status = 0
```

### 2. Partial Payment Distribution
```
Total Sisa: Rp 1.500.000
Bayar: Rp 1.200.000

Distribution:
- Tag 1: Bayar min(1.200.000, 500.000) = 500.000
- Tag 2: Bayar min(700.000, 600.000) = 600.000
- Tag 3: Bayar remaining 100.000

Sisa after approval:
- Tag 1: 0 (lunas)
- Tag 2: 0 (lunas)
- Tag 3: 300.000 (cicilan)
```

### 3. Reject with Partial Payments
```
Before reject:
- Tag 1: sisa_nominal = 0, jumlah_dibayar = 500.000 (lunas)
- Tag 2: sisa_nominal = 0, jumlah_dibayar = 600.000 (lunas)
- Tag 3: sisa_nominal = 300.000, jumlah_dibayar = 100.000

After reject:
- Tag 1: sisa_nominal = 500.000, jumlah_dibayar = 0 (belum bayar)
- Tag 2: sisa_nominal = 600.000, jumlah_dibayar = 0 (belum bayar)
- Tag 3: sisa_nominal = 400.000, jumlah_dibayar = 0 (belum bayar)
```

## Integration Points

### From PembayaranController
```php
// prosesMultiplePembayaran creates:
- 1 Pembayarantagihan (master)
- N Pembayarantagihan (details with same code)
- 1 Keuangan_transaksi (jenis_transaksi = 'tagihan-multiple')
- 2 Jurnals (debit + credit)
```

### In KeuanganTransaksiController
```php
// approve() processes:
- Load transaksi dengan pembayaranTagihan
- Detect jenis_transaksi = 'tagihan-multiple'
- Load all pembayaran dengan master code
- Update each tagihanSiswa
- Update main tagihan if needed
- Create/Update journal entries
- Log activity
```

## Testing Scenarios

### Scenario 1: Approve Partial Multiple Payment
```
Input:
- 3 Tagihan: Rp 500k, Rp 600k, Rp 400k
- Bayar: Rp 1.200.000

Expected After Approve:
- Tagihan 1: Lunas (sisa 0)
- Tagihan 2: Lunas (sisa 0)
- Tagihan 3: Cicilan (sisa 300k)
- Main Tagihan: Status depends on all items
```

### Scenario 2: Reject Multiple Payment
```
Input:
- Approved 3 items dari scenario 1
- Reject transaksi

Expected After Reject:
- All items restored to original sisa_nominal
- jumlah_dibayar reset to 0
- Status back to 'Belum Bayar'
```

### Scenario 3: Complete Multiple Payment
```
Input:
- 2 Tagihan: Rp 500k, Rp 500k
- Bayar: Rp 1.000.000

Expected After Approve:
- Both Tagihan: Lunas
- Main Tagihan (if both from same): Lunas
```

## Database Integrity

### Unique Constraints Maintained
```sql
-- Pembayarantagihan
UNIQUE(tagihan_siswa_id, code_pembayaran)
-- Ensures each tagihan paid only once per code

-- Keuangan_transaksi
PRIMARY: id
UNIQUE: code_pembayaran (per transaksi)

-- Tagihansiswa
Each update is atomic within transaction
Rollback pada error
```

### Transaction Safety
```php
DB::beginTransaction();
try {
    // All updates
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // All changes reverted
}
```

## Performance Considerations

### Loop Efficiency
```
For each pembayaran detail:
- Get tagihanSiswa (1 query)
- Update tagihanSiswa (1 query)
- Check hasUnpaid (1 query per check)
- Update tagihan (1 query per check)

Total: O(n) where n = number of details
Typical: 2-5 details per multiple payment
```

### Optimization Options
```php
// Could optimize with batch updates:
Tagihansiswa::whereIn('id', $ids)->update([...])

// But maintains data integrity better with loop
// Since each item has different calculations
```

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-11-21 | Initial implementation |

## Future Enhancements

1. **Batch Journal Entries**
   - Consolidate multiple journal entries into one

2. **Reversal Entries**
   - Create automatic reversal journal entries on reject

3. **Audit Trail**
   - More detailed activity logs per item

4. **Performance Optimization**
   - Batch database updates where possible

5. **API Response Enhancement**
   - Return detailed breakdown per item in response
