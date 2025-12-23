@extends("layouts.app")
@section("title", isset($merchant) ? (isset($show) && $show ? "Lihat Merchant" : "Edit Merchant") : "Tambah Merchant")

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => isset($merchant)
                ? (isset($show) && $show
                    ? "Lihat Data"
                    : "Edit Data")
                : "Tambah Data",
            "subTitle" => "Merchant",
        ]
    )

    <div class="card">
        <div class="card-body">
            <form
                id="merchantForm"
                action="{{ isset($merchant) ? route("merchant.update", $merchant->id) : route("merchant.store") }}"
                method="POST"
            >
                @csrf
                @if (isset($merchant))
                    @method("PUT")
                @endif

                <div class="row g-3">
                    {{-- Unit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select
                                name="unit_id"
                                id="unit_id"
                                class="form-select"
                                required
                            >
                                <option>-- Pilih Unit --</option>
                                @foreach ($units as $u)
                                    <option
                                        value="{{ $u->id }}"
                                        data-logo="{{ $u->image ? asset($u->image) : asset("images/default-logo.png") }}"
                                        {{ old("unit_id", $merchant->unit_id ?? "") == $u->id ? "selected" : "" }}
                                    >
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Kode Merchant --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="kode_merchant"
                            label="Kode Merchant"
                            placeholder="Masukkan Kode Merchant"
                            icon="bx bx-barcode"
                            value="$kodeMerchant"
                            :value="old('kode_merchant', $merchant->kode_merchant ?? $kodeMerchant ?? '')"
                            required
                            readonly
                        />
                    </div>

                    {{-- Nama Merchant --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="nama_merchant"
                            label="Nama Merchant"
                            placeholder="Masukkan Nama Merchant"
                            icon="bx bx-book"
                            :value="old('nama_merchant', $merchant->nama_merchant ?? '')"
                            required
                        />
                    </div>

                    {{-- jenis --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="jenis"
                            label="Jenis Usaha"
                            placeholder="Masukkan Jenis Usaha"
                            icon="bxr bx-core"
                            :value="old('jenis', $merchant->jenis ?? '')"
                            required
                        />
                    </div>

                    {{-- Nama Pemilik --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="pemilik"
                            label="Nama Pemilik"
                            placeholder="Masikkan Nama Pemilik"
                            icon="bx bx-user"
                            :value="old('pemilik', $merchant->pemilik ?? '')"
                            required
                        />
                    </div>

                    {{-- Password --}}

                    <div class="col-md-4">
                        <x-input-field
                            type="password"
                            id="password"
                            name="password"
                            label="Password"
                            :placeholder="isset($merchant)
                                ? 'Kosongkan jika tidak ingin mengubah password'
                            : 'Masukkan Password'"
                            icon="bx bx-lock"
                            :disabled="isset($show) && $show"
                            required
                        />
                    </div>

                    {{-- no hp --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            id="no_hp"
                            name="no_hp"
                            label="No. Telepon"
                            placeholder="Masukkan nomor telepon"
                            icon="bx bx-phone"
                            :value="old('no_hp', $merchant?->no_hp ?? '')"
                            onkeypress="
                                return (
                                    event.charCode >= 48 && event.charCode <= 57
                                );
                            "
                            maxLength="14"
                            required
                        />
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                Status
                            </label>
                            <select
                                name="status"
                                id="status"
                                class="form-select"
                            >
                                <option
                                    value="1"
                                    {{ old("status", $merchant->status ?? "") == "1" ? "selected" : "" }}
                                >
                                    Aktif
                                </option>
                                <option
                                    value="0"
                                    {{ old("status", $merchant->status ?? "") == "0" ? "selected" : "" }}
                                >
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end gap-2">
                    <button
                        type="button"
                        class="btn btn-info"
                        data-bs-toggle="modal"
                        data-bs-target="#qrModal"
                    >
                        <i class="bx bx-qr"></i>
                        Lihat QR
                    </button>
                    <button type="submit" class="btn btn-success">
                        {{ isset($merchant) ? "Update" : "Simpan" }}
                    </button>
                    <a href="{{ url("merchant/") }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal --}}
    <div
        class="modal fade"
        id="qrModal"
        tabindex="-1"
        aria-labelledby="qrModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">
                        <i class="bx bx-qr"></i>
                        QR Code Merchant
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body text-center">
                    <!-- QR Code -->
                    <div
                        id="qrcode"
                        class="mb-3 p-3 d-flex justify-content-center"
                        style="border: 1px solid #000"
                    ></div>

                    <!-- hidden input untuk simpan data QR -->
                    <input type="hidden" id="qrcode-text" name="qrcode_text" />

                    <small class="text-muted">
                        QR akan berubah otomatis sesuai kode merchant & unit
                    </small>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Tutup
                    </button>
                    <button
                        type="button"
                        id="downloadQrBtn"
                        class="btn btn-primary"
                        style="display: none"
                    >
                        <i class="bx bx-download"></i>
                        Download QR
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.6.0/lib/qr-code-styling.js"></script>

    <script>
        @if (isset($show) && $show)
            document.addEventListener('DOMContentLoaded', function() {
                const formElements = document.querySelectorAll(
                    '#merchantForm input, #merchantForm textarea, #merchantForm select, #merchantForm button[type="submit"]'
                    );
                formElements.forEach(el => {
                    el.disabled = true;
                    if (el.type === 'submit') {
                        el.style.display = 'none';
                    }
                });
            });
        @endif
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('merchantForm');

            form.addEventListener('submit', function (e) {
                e.preventDefault(); // cegah submit langsung

                Swal.fire({
                    title: 'Apakah data sudah benar?',
                    text: 'Pastikan semua data sudah diisi dengan benar sebelum menyimpan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // tampilkan loading sebelum submit
                        Swal.fire({
                            title: 'Menyimpan...',
                            text: 'Harap tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        // submit form setelah konfirmasi
                        form.submit();
                    }
                });
            });
        });
    </script>

    {{-- QR --}}
    <script>
        console.log('QR SCRIPT LOADED');

        document.addEventListener('DOMContentLoaded', function () {
            const vaInput = document.querySelector(
                'input[name="kode_merchant"]',
            );
            const qrContainer = document.getElementById('qrcode');
            const unitSelect = document.getElementById('unit_id');
            const downloadBtn = document.getElementById('downloadQrBtn');
            const qrModal = document.getElementById('qrModal');
            qrModal.addEventListener('shown.bs.modal', function () {
                console.log('MODAL SHOWN');
            });

            let defaultLogo = '{{ asset("images/default-logo.png") }}';
            let qrCode = null;

            qrModal.addEventListener('shown.bs.modal', function () {
                qrContainer.innerHTML = '';

                const selectedOption =
                    unitSelect.options[unitSelect.selectedIndex];
                const logo =
                    selectedOption?.getAttribute('data-logo') || defaultLogo;

                qrCode = new QRCodeStyling({
                    width: 200,
                    height: 200,
                    data: vaInput.value || ' ',
                    image: logo,
                    dotsOptions: {
                        color: '#000',
                        type: 'rounded',
                    },
                    backgroundOptions: {
                        color: '#fff',
                    },
                    imageOptions: {
                        crossOrigin: 'anonymous',
                        margin: 4,
                        imageSize: 0.4,
                        hideBackgroundDots: true,
                        imageCornerRadius: 100,
                    },
                });

                qrCode.append(qrContainer);

                downloadBtn.style.display = selectedOption?.value
                    ? 'inline-block'
                    : 'none';
            });

            unitSelect.addEventListener('change', function () {
                if (!qrCode) return;

                const selectedOption =
                    unitSelect.options[unitSelect.selectedIndex];
                const logo =
                    selectedOption?.getAttribute('data-logo') || defaultLogo;

                qrCode.update({ image: logo });

                downloadBtn.style.display = selectedOption?.value
                    ? 'inline-block'
                    : 'none';
            });

            // tombol download
            downloadBtn.addEventListener('click', function () {
                if (!qrCode) return;

                const fileName = 'qr-' + (vaInput.value || 'merchant') + '.png';
                qrCode.download({
                    name: fileName,
                    extension: 'png',
                });
            });
        });
    </script>

    {{-- Alert Sukses & Error --}}
    @if (session("success"))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session("success") }}',
                timer: 2000,
                showConfirmButton: false,
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: `
                    <ul style="text-align:left;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                            </ul>
            `,
                            confirmButtonColor: '#d33',
                        });
        </script>
    @endif
@endpush
