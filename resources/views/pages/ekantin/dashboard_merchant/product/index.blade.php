@extends("layouts.merchant")

@section("title", "Produk")

@section("content")
    <div
        class="welcome-section d-flex justify-content-between align-items-center mb-4"
    >
        <h1>Produk</h1>
        <a href="{{ url("merchant/product/create") }}" class="btn btn-primary">
            <i class="bi bi-download me-1"></i>
            Tambah Data
        </a>
    </div>
    {{-- Tabel --}}
    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="fw-bold text-dark">Daftar Produk</h5>
            </div>
            <div class="table-responsive">
                <table
                    id="productTable"
                    class="table table-striped table-hover table-bordered align-middle table-sm text-nowrap"
                >
                    <thead class="table-primary text-center align-middle">
                        <tr>
                            <th class="text-center" style="width: 4%">#</th>
                            <th style="width: 8%">Nama Produk</th>
                            <th style="width: 8%">Kategori Produk</th>
                            <th style="width: 8%">Jumlah Produk</th>
                            <th style="width: 8%">Harga Jual</th>
                            <th class="text-center" style="width: 6%">
                                Status
                            </th>
                            <th class="text-center" style="width: 6%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let productTable;

        document.addEventListener('DOMContentLoaded', function () {
            productTable = $('#productTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100],
                ],
                pageLength: 25,
                language: {
                    url: '{{ asset("assets/datatables/id.json") }}',
                },
                ajax: {
                    url: '{{ route("merchant.product.datatable") }}',
                    type: 'GET',
                    data: function (d) {},
                },
                columns: [
                    { data: 'no', className: 'text-center' },
                    { data: 'product_name' },
                    { data: 'product_category' },
                    { data: 'number_of_product' },
                    { data: 'selling_price' },
                    {
                        data: 'status',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                    },
                ],
                order: [[1, 'desc']],
            });
        });

        $(document).on('click', '.btn-delete', function () {
            let url = $(this).data('url');

            Swal.fire({
                title: 'Yakin hapus product?',
                text: 'Data product akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE',
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message ?? 'Product berhasil dihapus',
                                timer: 1500,
                                showConfirmButton: false,
                            });

                            // reload datatable tanpa reset halaman
                            productTable.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire(
                                'Gagal!',
                                xhr.responseJSON?.message ??
                                    'Product gagal dihapus.',
                                'error',
                            );
                        },
                    });
                }
            });
        });
    </script>
@endpush
