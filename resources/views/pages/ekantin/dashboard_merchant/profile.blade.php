@extends("layouts.merchant")
@section("title", "Profile Merchant")

@section("content")
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="card-title">Profile Merchant</h4>
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#imageModal"
                    >
                        Ubah Foto
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div
                                    class="d-flex flex-column align-items-center text-center"
                                >
                                    <img
                                        src="{{ $merchant->image ? asset("storage/" . $merchant->image) : asset("images/default.png") }}"
                                        alt="Foto Merchant"
                                        class="img-fluid rounded-circle mb-3"
                                        style="
                                            width: 150px;
                                            height: 150px;
                                            object-fit: cover;
                                        "
                                    />

                                    <h5 class="mb-1">
                                        {{ $merchant->pemilik ?? "User" }}
                                    </h5>

                                    <p class="mb-0">
                                        {{ $merchant->kode_merchant }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form
                id="merchantForm"
                action="{{ route("merchant.update.profile") }}"
                method="POST"
            >
                @csrf
                @if (isset($merchant))
                    @method("PUT")
                @endif

                <div class="row g-3">
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
                        />
                    </div>

                    {{-- Nama Merchant --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="nama_merchant"
                            label="Nama Merchant"
                            placeholder="Masukkan Nama Merchant"
                            icon="bx bx-store-alt"
                            :value="old('nama_merchant', $merchant->nama_merchant ?? '')"
                        />
                    </div>

                    {{-- jenis --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="jenis"
                            label="Jenis Usaha"
                            placeholder="Masukkan Jenis Usaha"
                            icon="bx bx-store"
                            :value="old('jenis', $merchant->jenis ?? '')"
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
                        />
                    </div>

                    {{-- bank --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            id="bank_name"
                            name="bank_name"
                            label="Nama Bank"
                            placeholder="Masukkan nama bank"
                            icon="bx bx-bank"
                            :value="old('bank_name', $merchant?->bank_name ?? '')"
                        />
                    </div>

                    {{-- rekening --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            id="account_name"
                            name="account_name"
                            label="Nama Rekening"
                            placeholder="Masukkan nama rekening"
                            icon="bx bx-credit-card-alt"
                            :value="old('account_name', $merchant?->account_name ?? '')"
                        />
                    </div>

                    {{-- no rekening --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            id="account_number"
                            name="account_number"
                            label="No. Rekening"
                            placeholder="Masukkan nomor rekening"
                            icon="bx bx-credit-card"
                            :value="old('account_number', $merchant?->account_number ?? '')"
                            onkeypress="
                                return (
                                    event.charCode >= 48 && event.charCode <= 57
                                );
                            "
                            maxLength="14"
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
                        QR akan berubah otomatis sesuai kode merchant
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

    <!-- Modal lihat & ubah foto -->
    <div
        class="modal fade"
        id="imageModal"
        tabindex="-1"
        aria-labelledby="imageModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form
                    action="{{ route("merchant.update.photo") }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @method("PATCH")
                    <div class="modal-header">
                        <h5 class="modal-title">Foto Profile Merchant</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body text-center">
                        <!-- Tampilkan foto saat ini -->
                        <img
                            id="previewImage"
                            src="{{ $merchant->image ? asset("storage/" . $merchant->image) : asset("images/default.png") }}"
                            alt="Foto Merchant"
                            class="img-fluid rounded mb-3"
                            style="max-width: 250px"
                        />

                        <!-- Input untuk ganti foto -->
                        <div class="mb-3">
                            <label for="image" class="form-label">
                                Pilih Foto Baru
                            </label>
                            <input
                                type="file"
                                name="image"
                                id="image"
                                class="form-control"
                                accept="image/*"
                            />
                        </div>
                        <small class="text-muted">
                            Format: JPG/PNG, maksimal 2MB.
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
                        <button type="submit" class="btn btn-primary">
                            Simpan Foto
                        </button>
                    </div>
                </form>
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
            const downloadBtn = document.getElementById('downloadQrBtn');
            const qrModal = document.getElementById('qrModal');

            let qrCode = null;

            // LOGO UNIT dari database merchant (AMAN)
            const logo = @json(
                $merchant->unit && $merchant->unit->image
                    ? asset("" . $merchant->unit->image)
                    : asset("images/default-logo.png")
            );

            console.log('QR LOGO:', logo);

            qrModal.addEventListener('shown.bs.modal', function () {
                qrContainer.innerHTML = '';

                qrCode = new QRCodeStyling({
                    width: 200,
                    height: 200,
                    data: vaInput.value || '-',
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
                downloadBtn.style.display = 'inline-block';
            });

            // Download QR
            downloadBtn.addEventListener('click', function () {
                if (!qrCode) return;

                qrCode.download({
                    name: 'qr-' + (vaInput.value || 'merchant'),
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

    <script>
        // Preview image sebelum upload
        const profileInput = document.getElementById('image');
        const previewImage = document.getElementById('previewImage');

        profileInput.addEventListener('change', function (e) {
            const [file] = e.target.files;
            if (file) {
                previewImage.src = URL.createObjectURL(file);
            }
        });
    </script>
@endpush
