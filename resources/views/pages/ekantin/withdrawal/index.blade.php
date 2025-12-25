@extends("layouts.app")
@section("title", "Data Penarikan Dana")
@push("styles")
    <link rel="stylesheet" href="{{ asset("assets/css/style.css") }}" />
    <link rel="stylesheet" href="{{ asset("assets/css/tabungan.css") }}" />
@endpush

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => "Penarikan Dana Merchant",
            "subTitle" => "Penarikan Dana Merchant",
        ]
    )

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
                                Total Nominal
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp.
                                {{ number_format($summary["total_nominal"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-money-dollar-circle-line text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-success"></i>
                        <span>Total nominal hari ini</span>
                    </small>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-success"></i>
                        <span>
                            {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
                        </span>
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
                                Sedang Diproses
                            </p>
                            <h3 class="fw-bold text-warning mb-0 text-absolute">
                                Rp.
                                {{ $summary['total_pending'] ?? 0, 0, ',', '.' }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-hourglass-2-fill text-warning"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-warning"></i>
                        <span>Total sedang diproses hari ini</span>
                    </small>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-warning"></i>
                        <span>
                            {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
                        </span>
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
                                Berhasil
                            </p>
                            <h3 class="fw-bold text-info mb-0 text-absolute">
                                Rp.
                                {{ $summary['total_approved'] ?? 0, 0, ',', '.' }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-info bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-checkbox-circle-line text-info"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-info"></i>
                        <span>Total berhasil hari ini</span>
                    </small>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-info"></i>
                        <span>
                            {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
                        </span>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-red shadow-sm h-100 transition-all"
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
                                Dibatalkan
                            </p>
                            <h3 class="fw-bold text-danger mb-0 text-absolute">
                                Rp.
                                {{ number_format($summary["total_rejected"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-danger bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-close-circle-line text-danger"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-danger"></i>
                        <span>Total dibatalkan hari ini</span>
                    </small>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-danger"></i>
                        <span>
                            {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
                        </span>
                    </small>
                </div>
            </div>
        </div>
        {{-- Filter Card --}}
        @if ((auth()->user()->yayasan_id && ! auth()->user()->unit_id) || (! auth()->user()->yayasan_id && ! auth()->user()->unit_id))
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
                            Filter Penarikan
                        </h5>
                        <i
                            class="bx bx-chevron-down text-primary"
                            style="font-size: 26px"
                        ></i>
                    </div>
                    <form
                        action="{{ route("merchant_withdrawal.index") }}"
                        method="GET"
                    >
                        <div class="row g-3 collapse" id="filterCollapse">
                            {{-- Filter Unit --}}
                            <div class="col-md-3">
                                <label for="unit_id" class="form-label">
                                    Unit
                                </label>
                                <select
                                    name="unit_id"
                                    id="unit_id"
                                    class="form-select shadow-sm"
                                >
                                    <option value="">Semua Unit</option>
                                    @foreach ($units as $unit)
                                        <option
                                            value="{{ $unit->id }}"
                                            {{ request("unit_id") == $unit->id ? "selected" : "" }}
                                        >
                                            {{ $unit->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Search --}}
                            <div class="col-md-3">
                                <label for="search" class="form-label">
                                    Cari Penarikan
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
                                <label
                                    for="status"
                                    class="form-label fw-semibold"
                                >
                                    Filter Status
                                </label>
                                <select
                                    id="status"
                                    name="status"
                                    class="form-select shadow-sm"
                                >
                                    <option value="">-- Pilih Status --</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Berhasil</option>
                                    <option value="rejected">Dibatalkan</option>
                                </select>
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
                                    href="{{ route("merchant_withdrawal.index") }}"
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
                    <h5 class="fw-bold text-dark">Daftar Penarikan</h5>
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
                                <th style="width: 8%">Nomor Telepon</th>
                                <th style="width: 8%">JML Penarikan</th>
                                <th style="width: 14%">Metode Penarikan</th>
                                <th style="width: 8%">Status Penarikan</th>
                                <th class="text-center" style="width: 10%">
                                    Waktu Permintaan
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

@push("script")
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

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
                    url: '{{ route("merchant_withdrawal.datatable") }}',
                    type: 'GET',
                    data: function (d) {
                        d.unit_id = $('#unit_id').val();
                        d.status_penarikan = $('#status').val();
                        d.tanggal = $('#tanggal').val();
                    },
                },
                columns: [
                    { data: 'no', className: 'text-center' },
                    { data: 'kode_merchant' },
                    { data: 'nama_merchant' },
                    { data: 'no_telp' },
                    { data: 'jml' },
                    { data: 'metode' },
                    { data: 'status' },
                    { data: 'waktu_penarikan', className: 'text-center' },
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
        $('#status, #tanggal, #unit_id').on('change', function () {
            merchantTable.ajax.reload();
        });
    </script>
@endpush
