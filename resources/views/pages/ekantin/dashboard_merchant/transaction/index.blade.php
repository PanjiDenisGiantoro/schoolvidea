@extends("layouts.merchant")
@section("title", "Transaksi")
@section("content")
    <div
        class="welcome-section d-flex justify-content-between align-items-center mb-4"
    >
        <h3>Informasi Transaksi Merchant</h3>
    </div>

    <div class="row g-3 mb-4">
        {{-- Card --}}
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-green shadow-sm h-100 transition-all"
            >
                <div class="card-body position-relative">
                    <div
                        class="d-flex justify-content-between align-items-start"
                    >
                        <div>
                            <p
                                class="text-muted fw-500 mb-1 text-uppercase"
                                style="font-size: 12px; letter-spacing: 0.5px"
                            >
                                Total Transaksi
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                {{ $trxCount ?? 0 }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-funds-line text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-success"></i>
                        <span>(Keseluruhan)</span>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-purple shadow-sm h-100 transition-all"
            >
                <div class="card-body position-relative">
                    <div
                        class="d-flex justify-content-between align-items-start"
                    >
                        <div>
                            <p
                                class="text-muted fw-500 mb-1 text-uppercase"
                                style="font-size: 12px; letter-spacing: 0.5px"
                            >
                                Transaksi Kredit
                            </p>
                            <h3 class="fw-bold text-info mb-0 text-absolute">
                                {{ $trxCredit ?? 0 }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-info bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-money-dollar-circle-fill text-info"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-info"></i>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-yellow shadow-sm h-100 transition-all"
            >
                <div class="card-body position-relative">
                    <div
                        class="d-flex justify-content-between align-items-start"
                    >
                        <div>
                            <p
                                class="text-muted fw-500 mb-1 text-uppercase"
                                style="font-size: 12px; letter-spacing: 0.5px"
                            >
                                Transaksi Debit
                            </p>
                            <h3 class="fw-bold text-warning mb-0 text-absolute">
                                {{ $trxDebit ?? 0 }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-money-dollar-circle-line text-warning"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-warning"></i>
                        <span></span>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-green shadow-sm h-100 transition-all"
            >
                <div class="card-body position-relative">
                    <div
                        class="d-flex justify-content-between align-items-start"
                    >
                        <div>
                            <p
                                class="text-muted fw-500 mb-1 text-uppercase"
                                style="font-size: 12px; letter-spacing: 0.5px"
                            >
                                Total Hari Ini
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp.
                                {{ number_format($totalToday ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-timer-flash-line text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-success"></i>
                        {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
                    </small>
                </div>
            </div>
        </div>
        {{-- Filter Card --}}
        @if (auth("merchant")->check() || ! auth("merchant")->check())
            <div class="card rounded-3 border-0 shadow-sm mb-2">
                <div class="card-body">
                    <div
                        class="d-flex justify-content-between align-items-center gap-3 mb-3"
                        data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse"
                        style="cursor: pointer"
                    >
                        <h5 class="fw-bold text-primary">
                            <i class="bx bx-filter"></i>
                            Filter Transaksi
                        </h5>
                        <i
                            class="bx bx-chevron-down text-primary"
                            style="font-size: 26px"
                        ></i>
                    </div>
                    <form
                        action="{{ route("merchant.transaction.index") }}"
                        method="GET"
                    >
                        <div class="row g-3 collapse" id="filterCollapse">
                            {{-- Filter Search --}}
                            <div class="col-md-3">
                                <label for="search" class="form-label">
                                    Cari Transaksi
                                </label>
                                <input
                                    type="text"
                                    name="search"
                                    id="search"
                                    class="form-control p-3 shadow-sm"
                                    style="font-size: 14px"
                                    placeholder="Kode, Nama, Status..."
                                    value="{{ request("search") }}"
                                />
                            </div>

                            <div class="col-md-3">
                                <label for="tanggal" class="form-label">
                                    Tanggal
                                </label>
                                <input
                                    type="date"
                                    name="tanggal"
                                    id="tanggal"
                                    class="form-control p-3 shadow-sm"
                                    style="font-size: 14px"
                                    value="{{ request("tanggal") }}"
                                />
                            </div>

                            {{-- Tombol Filter --}}
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i>
                                    Filter
                                </button>
                                <a
                                    href="{{ route("merchant.transaction.index") }}"
                                    class="btn btn-secondary"
                                >
                                    <i class="bx bx-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Tabel --}}
        <div class="card rounded-3 border-0 shadow-sm">
            <div class="card-body">
                <div
                    class="d-flex justify-content-between mb-3 flex-wrap gap-2"
                >
                    <h5 class="fw-bold text-dark">Daftar Transaksi</h5>
                </div>
                <div class="table-responsive">
                    <table
                        id="merchantTable"
                        class="table table-striped table-hover table-bordered align-middle table-sm text-nowrap"
                    >
                        <thead class="table-primary text-center align-middle">
                            <tr>
                                <th class="text-center" style="width: 4%">#</th>
                                <th style="width: 8%">Kode Merchant</th>
                                <th style="width: 14%">Nama Merchant</th>
                                <th style="width: 8%">JML Transaksi</th>
                                <th style="width: 8%">Jenis Transaksi</th>
                                <th style="width: 14%">JML Saldo Akhir</th>
                                <th class="text-center" style="width: 10%">
                                    Waktu Registrasi
                                </th>
                                <th class="text-center" style="width: 6%">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("styles")
    <style>
        /* Horizontal scroll untuk tabel */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Firefox scrollbar styling */
        .table-responsive {
            scrollbar-color: #888 #f1f1f1;
            scrollbar-width: thin;
        }
    </style>
@endpush

@push("scripts")
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let merchantTable;

        document.addEventListener('DOMContentLoaded', function () {
            merchantTable = $('#merchantTable').DataTable({
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
                    url: '{{ route("merchant.transaction.datatable") }}',
                    type: 'GET',
                    data: function (d) {
                        d.tanggal = $('#tanggal').val();
                    },
                },
                columns: [
                    { data: 'no', className: 'text-center' },
                    { data: 'kode_merchant' },
                    { data: 'nama_merchant' },
                    { data: 'amount' },
                    { data: 'type' },
                    { data: 'balance_after' },
                    { data: 'waktu_registrasi', className: 'text-center' },
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
    </script>
    <script>
        $('#tanggal').on('change', function () {
            merchantTable.ajax.reload();
        });
    </script>
@endpush
