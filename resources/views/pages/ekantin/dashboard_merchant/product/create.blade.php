@extends("layouts.merchant")
@section("title", isset($merchant) ? (isset($show) && $show ? "Lihat Produk" : "Edit Produk") : "Tambah Produk")

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => isset($product)
                ? (isset($show) && $show
                    ? "Lihat Produk"
                    : "Edit Produk")
                : "Tambah Produk",
            "subTitle" => "Produk",
        ]
    )

    <div class="card">
        <div class="card-body">
            <form
                id="productForm"
                action="{{ isset($product) ? route("merchant.product.update", $product->id) : route("merchant.product.store") }}"
                method="POST"
            >
                @csrf
                @if (isset($product))
                    @method("PUT")
                @endif

                <div class="row g-3">
                    {{-- Nama Produk --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="product_name"
                            label="Nama Produk"
                            placeholder="Masukkan Nama Produk"
                            icon="bx bx-store-alt"
                            :value="old('product_name', $product->product_name ?? '')"
                        />
                    </div>

                    {{-- Kategori Produk --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="product_category"
                            label="Kategori Produk"
                            placeholder="Masukkan Kategori Produk"
                            icon="bx bx-store"
                            :value="old('product_category', $product->product_category ?? '')"
                        />
                    </div>

                    {{-- Satuan Produk --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            name="product_unit"
                            label="Satuan Produk"
                            placeholder="Masukkan Satuan Produk"
                            icon="bx bx-user"
                            :value="old('product_unit', $product->product_unit ?? '')"
                        />
                    </div>

                    {{-- Jumlah Produk --}}
                    <div class="col-md-4">
                        <x-input-field
                            type="text"
                            id="number_of_product"
                            name="number_of_product"
                            label="Jumlah Produk"
                            placeholder="Masukkan Jumlah Produk"
                            icon="bx bx-phone"
                            :value="old('number_of_product', $product?->number_of_product ?? '')"
                            onkeypress="
                                return (
                                    event.charCode >= 48 && event.charCode <= 57
                                );
                            "
                            maxLength="7"
                        />
                    </div>

                    {{-- Harga Beli --}}
                    <div class="col-md-4">
                        <x-input-field
                            class="currency-display"
                            type="text"
                            id="purchase"
                            name="purchase"
                            data-target="purchase_price"
                            label="Harga Beli"
                            placeholder="Masukkan Harga Beli Produk"
                            icon="bx bx-phone"
                            oninput="formatCurrencyInput(this)"
                            :value="old('purchase_price', isset($product) ? number_format($product->purchase_price, 0, ',', '.') : '')"
                            onkeypress="
                                return (
                                    event.charCode >= 48 && event.charCode <= 57
                                );
                            "
                            maxLength="20"
                        />
                        <input
                            type="hidden"
                            name="purchase_price"
                            id="purchase_price"
                        />
                    </div>

                    {{-- Harga Jual --}}
                    <div class="col-md-4">
                        <x-input-field
                            class="currency-display"
                            type="text"
                            data-target="selling_price"
                            id="selling"
                            name="selling"
                            label="Harga Jual"
                            placeholder="Masukkan Harga Jual Produk"
                            icon="bx bx-phone"
                            oninput="formatCurrencyInput(this)"
                            :value="old('selling_price', isset($product) ? number_format($product->selling_price, 0, ',', '.') : '')"
                            onkeypress="
                                return (
                                    event.charCode >= 48 && event.charCode <= 57
                                );
                            "
                            maxLength="20"
                        />
                        <input
                            type="hidden"
                            name="selling_price"
                            id="selling_price"
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
                                    value="active"
                                    {{ old("status", $product->status ?? "") == "active" ? "selected" : "" }}
                                >
                                    Aktif
                                </option>
                                <option
                                    value="non-active"
                                    {{ old("status", $product->status ?? "") == "non-active" ? "selected" : "" }}
                                >
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="image-dropzone" class="form-label">
                            Upload Foto Produk
                        </label>
                        <div class="dropzone" id="image-dropzone"></div>
                        <input
                            type="hidden"
                            name="image"
                            id="image-hidden"
                            value="{{ old("image", $product?->image ?? "") }}"
                        />
                        <small class="text-muted">
                            Format: JPG, PNG | Max: 1MB
                        </small>
                        <div>
                            <div
                                class="modal fade"
                                id="imageModal"
                                tabindex="-1"
                            >
                                <div
                                    class="modal-dialog modal-lg modal-dialog-centered"
                                >
                                    <div class="modal-content">
                                        <div class="modal-body text-center">
                                            <img
                                                id="previewImage"
                                                src=""
                                                class="img-fluid rounded"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end gap-2">
                    <button type="submit" class="btn btn-success">
                        {{ isset($product) ? "Update" : "Simpan" }}
                    </button>
                    <a
                        href="{{ url("merchant/product/") }}"
                        class="btn btn-secondary"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.6.0/lib/qr-code-styling.js"></script>

    <script>
        @if (isset($show) && $show)
            document.addEventListener('DOMContentLoaded', function() {
                const formElements = document.querySelectorAll(
                    '#productForm input, #productForm textarea, #productForm select, #productForm button[type="submit"]'
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
            const form = document.getElementById('productForm');

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
        Dropzone.autoDiscover = false;

        document.addEventListener('DOMContentLoaded', function () {
            let dz = new Dropzone('#image-dropzone', {
                url: '{{ route("merchant.product.upload") }}',
                paramName: 'file',
                maxFiles: 1,
                maxFilesize: 1, // MB
                acceptedFiles: '.jpg,.jpeg,.png',
                addRemoveLinks: true,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                success: function (file, response) {
                    document.getElementById('image-hidden').value = response.filepath;
                },
                removedfile: function (file) {
                    file.previewElement.remove();
                    document.getElementById('image-hidden').value = '';
                },
                init: function () {
                    @if(isset($product) && $product->image)
                        const mockFile = {
                            name: 'Current Image',
                            size: 12345,
                            accepted: true
                        };

                        this.emit('addedfile', mockFile);
                        this.emit('thumbnail', mockFile, '{{ asset($product->image) }}');
                        this.emit('complete', mockFile);
                        this.files.push(mockFile);
                    @endif
                }
            });
        });
    </script>

    <script>
        function formatCurrencyInput(input) {
            let raw = input.value.replace(/\D/g, '');

            if (!raw) {
                input.value = '';
                document.getElementById(input.dataset.target).value = '';
                return;
            }

            input.value = new Intl.NumberFormat('id-ID').format(raw);
            document.getElementById(input.dataset.target).value = raw;
        }
    </script>
@endpush
