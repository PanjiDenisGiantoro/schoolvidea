# Multiple Payment Processing - Frontend Implementation Example

## HTML Structure Example

```html
<!-- Form untuk Multiple Payment -->
<div class="card rounded-4 border-0 p-4 shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Pembayaran Multiple Tagihan</h5>
    </div>
    <div class="card-body">
        <!-- Pilih Siswa -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="siswaSelect" class="form-label">Pilih Siswa</label>
                <select id="siswaSelect" class="form-select rounded-pill">
                    <option value="">-- Pilih Siswa --</option>
                </select>
            </div>
        </div>

        <!-- List Tagihan yang belum dibayar -->
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">Pilih Tagihan (minimal 2)</label>
                <div id="tagihanList" class="d-flex flex-column gap-2">
                    <!-- Tagihan items akan di-generate dengan JavaScript -->
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="row mb-3 bg-light p-3 rounded">
            <div class="col-md-3">
                <small class="text-muted">Jumlah Tagihan Dipilih</small>
                <p id="jumlahTagihanDipilih" class="fw-bold">0</p>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Total Sisa Nominal</small>
                <p id="totalSisaNominal" class="fw-bold">Rp 0</p>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Diskon/Potongan</small>
                <input type="number" id="diskonInput" class="form-control form-control-sm" value="0">
            </div>
            <div class="col-md-3">
                <small class="text-muted">Jumlah Bayar</small>
                <p id="jumlahBayarDisplay" class="fw-bold text-success">Rp 0</p>
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="metodeSelect" class="form-label">Metode Pembayaran</label>
                <select id="metodeSelect" class="form-select rounded-pill">
                    <option value="tunai">Tunai</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="cek">Cek</option>
                    <option value="e-wallet">E-Wallet</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="catatanInput" class="form-label">Catatan (Optional)</label>
                <input type="text" id="catatanInput" class="form-control rounded-pill" placeholder="Catatan pembayaran">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <button id="prosesBtn" class="btn btn-success btn-lg rounded-pill w-100" disabled>
                    <i class="ri-check-line"></i> Proses Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk detail pembayaran -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button id="printBtn" class="btn btn-primary">
                    <i class="ri-printer-line"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>
```

## JavaScript Implementation

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const siswaSelect = document.getElementById('siswaSelect');
    const tagihanList = document.getElementById('tagihanList');
    const jumlahTagihanDipilih = document.getElementById('jumlahTagihanDipilih');
    const totalSisaNominal = document.getElementById('totalSisaNominal');
    const diskonInput = document.getElementById('diskonInput');
    const jumlahBayarDisplay = document.getElementById('jumlahBayarDisplay');
    const metodeSelect = document.getElementById('metodeSelect');
    const prosesBtn = document.getElementById('prosesBtn');
    const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));

    let selectedTagihan = [];
    let siswaList = [];

    // Load data siswa
    async function loadSiswa() {
        try {
            const response = await fetch('/api/siswa'); // Adjust endpoint as needed
            siswaList = await response.json();

            siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>' +
                siswaList.map(s => `<option value="${s.id}">${s.user.name}</option>`).join('');
        } catch (error) {
            console.error('Error loading siswa:', error);
            alert('Gagal memuat data siswa');
        }
    }

    // Load tagihan ketika siswa dipilih
    siswaSelect.addEventListener('change', async function() {
        const siswaId = this.value;
        selectedTagihan = [];

        if (!siswaId) {
            tagihanList.innerHTML = '<p class="text-muted">Pilih siswa terlebih dahulu</p>';
            updateSummary();
            return;
        }

        try {
            const response = await fetch(`/api/tagihan/belum-lunas/${siswaId}`);
            const data = await response.json();

            if (data.length === 0) {
                tagihanList.innerHTML = '<p class="text-success">Tidak ada tagihan yang belum dibayar</p>';
                updateSummary();
                return;
            }

            // Render tagihan sebagai checkbox
            tagihanList.innerHTML = data.map(t => `
                <div class="form-check p-2 border rounded">
                    <input
                        class="form-check-input tagihan-checkbox"
                        type="checkbox"
                        data-id="${t.id}"
                        data-sisa="${t.sisa_nominal}"
                        data-nama="${t.tagihan.jenis_tagihan}"
                        id="tagihan_${t.id}">
                    <label class="form-check-label w-100" for="tagihan_${t.id}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${t.tagihan.jenis_tagihan}</strong>
                                <br>
                                <small class="text-muted">${t.bulan_ke ? 'Bulan ke-' + t.bulan_ke : 'Tagihan'}</small>
                            </div>
                            <div class="text-end">
                                <div>Sisa: <strong>${formatRupiah(t.sisa_nominal)}</strong></div>
                                <small class="text-${t.status === 0 ? 'danger' : 'warning'}">
                                    ${t.status === 0 ? 'Belum Bayar' : 'Cicilan'}
                                </small>
                            </div>
                        </div>
                    </label>
                </div>
            `).join('');

            // Add event listener ke checkboxes
            document.querySelectorAll('.tagihan-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', handleTagihanChange);
            });

        } catch (error) {
            console.error('Error loading tagihan:', error);
            alert('Gagal memuat data tagihan');
        }
    });

    // Handle tagihan selection
    function handleTagihanChange(event) {
        const checkbox = event.target;

        if (checkbox.checked) {
            selectedTagihan.push({
                id: parseInt(checkbox.dataset.id),
                sisa_nominal: parseInt(checkbox.dataset.sisa),
                nama: checkbox.dataset.nama
            });
        } else {
            selectedTagihan = selectedTagihan.filter(t => t.id !== parseInt(checkbox.dataset.id));
        }

        updateSummary();
    }

    // Update summary
    function updateSummary() {
        const jumlahDipilih = selectedTagihan.length;
        const totalSisa = selectedTagihan.reduce((sum, t) => sum + t.sisa_nominal, 0);
        const diskon = parseInt(diskonInput.value) || 0;
        const jumlahBayar = totalSisa - diskon;

        jumlahTagihanDipilih.textContent = jumlahDipilih;
        totalSisaNominal.textContent = formatRupiah(totalSisa);
        jumlahBayarDisplay.textContent = formatRupiah(jumlahBayar);

        // Enable button jika minimal 2 tagihan dipilih
        prosesBtn.disabled = jumlahDipilih < 2;
    }

    // Handle diskon change
    diskonInput.addEventListener('change', updateSummary);

    // Process pembayaran
    prosesBtn.addEventListener('click', async function() {
        const siswaId = siswaSelect.value;
        const tagihanIds = selectedTagihan.map(t => t.id);
        const totalSisa = selectedTagihan.reduce((sum, t) => sum + t.sisa_nominal, 0);
        const diskon = parseInt(diskonInput.value) || 0;
        const jumlahBayar = totalSisa - diskon;
        const metode = metodeSelect.value;

        if (!siswaId) {
            alert('Pilih siswa terlebih dahulu');
            return;
        }

        if (tagihanIds.length < 2) {
            alert('Pilih minimal 2 tagihan');
            return;
        }

        if (jumlahBayar <= 0) {
            alert('Jumlah bayar harus lebih dari 0');
            return;
        }

        // Show loading
        prosesBtn.disabled = true;
        prosesBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Sedang Proses...';

        try {
            const response = await fetch('/pembayaran/proses-multiple', {
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
            });

            const data = await response.json();

            if (data.status) {
                // Show success modal dengan detail
                showDetailModal(data.data);

                // Reset form
                selectedTagihan = [];
                siswaSelect.value = '';
                tagihanList.innerHTML = '';
                diskonInput.value = '0';
                updateSummary();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        } finally {
            prosesBtn.disabled = false;
            prosesBtn.innerHTML = '<i class="ri-check-line"></i> Proses Pembayaran';
        }
    });

    // Show detail modal
    function showDetailModal(data) {
        const siswa = data.siswa;
        const master = data.pembayaran_master;
        const details = data.pembayaran_details;
        const summary = data.summary;

        let html = `
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-success">
                        <strong>✓ Pembayaran Berhasil Diproses</strong>
                        <br>Kode: <strong>${master.code_pembayaran}</strong>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nama Siswa:</strong> ${siswa.nama}</p>
                    <p><strong>Metode:</strong> ${master.metode_bayar}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tanggal:</strong> ${new Date(master.tanggal_bayar).toLocaleDateString('id-ID')}</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Approved</span></p>
                </div>
            </div>

            <h6 class="mb-2">Detail Pembayaran:</h6>
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Tagihan</th>
                        <th class="text-end">Sisa Sebelum</th>
                        <th class="text-end">Dibayar</th>
                        <th class="text-end">Sisa Sesudah</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
        `;

        details.forEach(detail => {
            const statusBadge = detail.status === '1'
                ? '<span class="badge bg-success">Lunas</span>'
                : '<span class="badge bg-warning">Cicilan</span>';

            html += `
                <tr>
                    <td>${detail.tagihan_nama}</td>
                    <td class="text-end">${formatRupiah(detail.sisa_nominal_sebelum)}</td>
                    <td class="text-end"><strong>${formatRupiah(detail.jumlah_bayar)}</strong></td>
                    <td class="text-end">${formatRupiah(detail.sisa_nominal_sesudah)}</td>
                    <td class="text-center">${statusBadge}</td>
                </tr>
            `;
        });

        html += `
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2">TOTAL</th>
                        <th class="text-end"><strong>${formatRupiah(summary.jumlah_bayar)}</strong></th>
                        <th class="text-end"><strong>${formatRupiah(summary.total_sisa_nominal_sesudah)}</strong></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>

            <div class="row">
                <div class="col-md-6">
                    <p><small class="text-muted">Jumlah Tagihan: ${summary.jumlah_tagihan}</small></p>
                </div>
                <div class="col-md-6 text-end">
                    <p><small class="text-muted">Sisa Tagihan Total: ${formatRupiah(summary.total_sisa_nominal_sesudah)}</small></p>
                </div>
            </div>
        `;

        document.getElementById('detailContent').innerHTML = html;
        detailModal.show();
    }

    // Format rupiah
    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    // Load siswa on page load
    loadSiswa();
});
```

## CSS Styling (Optional)

```css
/* Multiple Payment Form Styling */
.tagihan-checkbox {
    cursor: pointer;
}

.form-check:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.form-check.selected {
    background-color: #e7f3ff;
    border-left: 4px solid #0d6efd !important;
}

#tagihanList .form-check-label {
    cursor: pointer;
    margin-bottom: 0;
}

#detailContent .table {
    margin-bottom: 0;
}

.payment-summary {
    border-radius: 0.5rem;
    padding: 1rem;
    background-color: #f8f9fa;
}

.payment-summary .summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.payment-summary .summary-item:last-child {
    border-bottom: none;
}
```

## Integration Tips

1. **API Endpoint for Unpaid Tagihan**:
   Buat endpoint baru untuk get tagihan yang belum dibayar:
   ```php
   Route::get('/api/tagihan/belum-lunas/{siswaId}', [TagihanController::class, 'getBelumLunas']);
   ```

2. **Existing Controller Method**:
   Gunakan method `bayar()` untuk pembayaran single tagihan.
   Gunakan method `prosesMultiplePembayaran()` untuk pembayaran multiple.

3. **Form Validation**:
   Frontend validation sudah implement di contoh di atas.
   Backend validation juga sudah implement di controller.

4. **Print Receipt**:
   Setelah pembayaran success, user bisa print struk:
   ```javascript
   const printBtn = document.getElementById('printBtn');
   printBtn.addEventListener('click', function() {
       window.open(`/pembayaran/${data.data.pembayaran_master.id}/print-struk`, '_blank');
   });
   ```

## Testing Checklist

- [ ] Load siswa dari API
- [ ] Load tagihan untuk siswa yang dipilih
- [ ] Enable button ketika 2+ tagihan dipilih
- [ ] Calculate total dengan diskon
- [ ] Submit pembayaran
- [ ] Display success modal
- [ ] Display detail pembayaran
- [ ] Print receipt functionality
- [ ] Reset form setelah success
- [ ] Test error cases
