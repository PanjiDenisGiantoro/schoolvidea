@extends("layouts.app")
@section("title", "Transaksi Keuangan")
@push("styles")
    <link rel="stylesheet" href="{{ asset("assets/css/style.css") }}" />
    <link rel="stylesheet" href="{{ asset("assets/css/tabungan.css") }}" />
@endpush

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => "Transaksi Keuangan",
            "subTitle" => "Kelola semua transaksi keuangan",
        ]
    )

    {{-- Summary Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-4 col-md-6 col-sm-12">
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
                                Pemasukan Bulan ini
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp
                                {{ number_format($summary["total_pemasukan"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-trending-up text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-up-arrow-alt text-success"></i>
                        Periode {{ Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
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
                                Pengeluaran Bulan Ini
                            </p>
                            <h3 class="fw-bold text-danger mb-0 text-absolute">
                                Rp
                                {{ number_format($summary["total_pengeluaran"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-danger bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-trending-down text-danger"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-down-arrow-alt text-danger"></i>
                        Periode {{ Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-blue shadow-sm h-100 transition-all"
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
                                Data Transaksi
                            </p>
                            <h3 class="fw-bold text-primary mb-0 text-absolute">
                                {{ number_format($summary["total_data_transaksi"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-info bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-transfer text-primary"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-transfer text-primary"></i>
                        Data Transaksi Keseluruhan
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-0 mb-3">
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-blue shadow-sm h-100 transition-all"
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
                                Transaksi Keseluruhan
                            </p>
                            <h3 class="fw-bold text-primary mb-0 text-absolute">
                                Rp
                                {{ number_format($summary["total_transaksi"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-info bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-bar-chart text-primary"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-bar-chart text-primary"></i>
                        Total Keseluruhan Transaksi
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
                                Hari Ini
                            </p>
                            <h3 class="fw-bold text-info mb-0 text-absolute">
                                Rp
                                {{ number_format($summary["total_harian"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-info bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-calendar text-info"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-calendar text-primary"></i>
                        {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
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
                                Transaksi Non-Tunai
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp
                                {{ number_format($summary["total_non_tunai"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-credit-card text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-credit-card text-success"></i>
                        Transfer, E Wallet
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
                                Transaksi Tunai
                            </p>
                            <h3 class="fw-bold text-warning mb-0 text-absolute">
                                Rp
                                {{ number_format($summary["total_tunai"] ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-money text-warning"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-money text-warning"></i>
                        Kas Langsung
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card rounded-3 mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div
                class="justify-content-between align-items-center d-flex gap-3"
                data-bs-toggle="collapse"
                data-bs-target="#filterCollapse"
                style="cursor: pointer"
            >
                <h5 class="fw-bold text-primary mt-3">
                    <span>
                        <i class="bx bx-filter"></i>
                        Filter Transaksi
                    </span>
                </h5>
                <h5 class="fw-bold text-primary" style="font-size: 26px">
                    <i class="bx bx-chevron-down"></i>
                </h5>
            </div>

            <form
                action="{{ route("keuangan_transaksi.index") }}"
                method="GET"
            >
                <div class="row g-3 collapse" id="filterCollapse">
                    {{-- Filter Unit --}}
                    <div class="col-md-3">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id" class="form-select">
                            <option value="">Pilih Unit</option>
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

                    {{-- Filter Jenis Transaksi --}}
                    <div class="col-md-3">
                        <label for="jenis_transaksi" class="form-label">
                            Jenis Transaksi
                        </label>
                        <select
                            name="jenis_transaksi"
                            id="jenis_transaksi"
                            class="form-select"
                        >
                            <option value="">Semua Jenis</option>
                            <option
                                value="setoran_tabungan"
                                {{ request("jenis_transaksi") == "setoran_tabungan" ? "selected" : "" }}
                            >
                                Setoran Tabungan
                            </option>
                            <option
                                value="penarikan_tabungan"
                                {{ request("jenis_transaksi") == "penarikan_tabungan" ? "selected" : "" }}
                            >
                                Penarikan Tabungan
                            </option>
                            <option
                                value="tagihan"
                                {{ request("jenis_transaksi") == "tagihan" ? "selected" : "" }}
                            >
                                Pembayaran Tagihan
                            </option>
                            <option
                                value="tagihan-keluar"
                                {{ request("jenis_transaksi") == "tagihan-keluar" ? "selected" : "" }}
                            >
                                Pembayaran Gaji
                            </option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-md-3">
                        <label for="status_verifikasi" class="form-label">
                            Status Verifikasi
                        </label>
                        <select
                            name="status_verifikasi"
                            id="status_verifikasi"
                            class="form-select"
                        >
                            <option value="">Semua Status</option>
                            <option
                                value="pending"
                                {{ request("status_verifikasi") == "pending" ? "selected" : "" }}
                            >
                                Pending
                            </option>
                            <option
                                value="approved"
                                {{ request("status_verifikasi") == "approved" ? "selected" : "" }}
                            >
                                Approved
                            </option>
                            <option
                                value="rejected"
                                {{ request("status_verifikasi") == "rejected" ? "selected" : "" }}
                            >
                                Rejected
                            </option>
                        </select>
                    </div>

                    {{-- Filter Kode Pembayaran --}}
                    <div class="col-md-3">
                        <label for="kode_pembayaran" class="form-label">
                            Kode Pembayaran
                        </label>
                        <input
                            type="text"
                            name="kode_pembayaran"
                            id="kode_pembayaran"
                            class="form-control p-3"
                            placeholder="Cari kode pembayaran"
                            value="{{ request("kode_pembayaran") }}"
                        />
                    </div>

                    {{-- Filter Nama Siswa --}}
                    <div class="col-md-3">
                        <label for="nama_siswa" class="form-label">
                            Nama/NISN Siswa
                        </label>
                        <input
                            type="text"
                            name="nama_siswa"
                            id="nama_siswa"
                            class="form-control p-3"
                            placeholder="Cari nama atau NISN"
                            value="{{ request("nama_siswa") }}"
                        />
                    </div>

                    {{-- Filter Tanggal Dari --}}
                    <div class="col-md-3">
                        <label for="dari_tanggal" class="form-label">
                            Dari Tanggal
                        </label>
                        <input
                            type="text"
                            name="dari_tanggal"
                            id="dari_tanggal"
                            class="form-control datepicker p-3"
                            placeholder="DD/MM/YYYY"
                            value="{{ request("dari_tanggal") }}"
                        />
                    </div>

                    {{-- Filter Tanggal Sampai --}}
                    <div class="col-md-3">
                        <label for="sampai_tanggal" class="form-label">
                            Sampai Tanggal
                        </label>
                        <input
                            type="text"
                            name="sampai_tanggal"
                            id="sampai_tanggal"
                            class="form-control datepicker p-3"
                            placeholder="DD/MM/YYYY"
                            value="{{ request("sampai_tanggal") }}"
                        />
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search"></i>
                            Filter
                        </button>
                        <a
                            href="{{ route("keuangan_transaksi.index") }}"
                            class="btn btn-secondary"
                        >
                            <i class="bx bx-refresh"></i>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Utama --}}
    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            {{-- Action Buttons & Pagination Control --}}
            <div class="d-flex justify-content-end mb-3 flex-wrap gap-2">
                <div class="d-flex justify-content-between gap-3">
                    @php
                        $pendingTabungan = \App\Models\Keuangan_transaksi::where("status_approval", "pending")
                            ->whereIn("jenis_transaksi", ["setoran_tabungan", "penarikan_tabungan"])
                            ->when(Auth::user()->unit_id, function ($q) {
                                $q->whereHas("penerima", function ($sq) {
                                    $sq->where("unit_id", Auth::user()->unit_id);
                                });
                            })
                            ->count();
                        $pendingTagihan = \App\Models\Keuangan_transaksi::where("status_approval", "pending")
                            ->where("jenis_transaksi", "tagihan")
                            ->when(Auth::user()->unit_id, function ($q) {
                                $q->whereHas("penerima", function ($sq) {
                                    $sq->where("unit_id", Auth::user()->unit_id);
                                });
                            })
                            ->count();
                    @endphp

                    @if ($pendingTabungan > 0)
                        <button
                            type="button"
                            class="btn btn-warning rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm position-relative"
                            onclick="showPendingTransactions('tabungan')"
                        >
                            <i class="bx bx-time-five"></i>
                            Pending Tabungan
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            >
                                {{ $pendingTabungan }}
                            </span>
                        </button>
                    @endif

                    @if ($pendingTagihan > 0)
                        <button
                            type="button"
                            class="btn btn-warning rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm position-relative"
                            onclick="showPendingTransactions('tagihan')"
                        >
                            <i class="bx bx-receipt"></i>
                            Pending Tagihan
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            >
                                {{ $pendingTagihan }}
                            </span>
                        </button>
                    @endif

                    {{-- <a href="{{ route('keuangan_transaksi.print_laporan') }}" target="_blank" --}}
                    {{-- class="btn btn-outline-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm"> --}}
                    {{-- <i class="bx bx-printer"></i> Cetak Laporan --}}
                    {{-- </a> --}}
                </div>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table
                    id="datatable"
                    class="table-bordered table-hover table overflow-hidden text-nowrap text-center align-middle"
                >
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>No Transaksi</th>
                            <th>Nama & NISN</th>
                            <th>Jenis Transaksi</th>
                            <th>Tot. Transaksi</th>
                            <th>Metode</th>
                            <th>Wkt. Transaksi</th>
                            <th>Status</th>
                            <th>Petugas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksis as $transaksi)
                            @php
                                // Determine which code to display based on payment type
                                $displayCode = $transaksi->code_pembayaran;
                                $isMulitple = false;

                                if (in_array($transaksi->jenis_transaksi, ["tagihan", "pembayaran"]) && $transaksi->pembayaranTagihan) {
                                    if ($transaksi->pembayaranTagihan->is_master === true && $transaksi->pembayaranTagihan->head_tagihan) {
                                        $displayCode = $transaksi->pembayaranTagihan->head_tagihan;
                                        $isMulitple = true;
                                    }
                                }
                            @endphp

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span
                                        class="badge @if($isMulitple) bg-info @else bg-secondary @endif"
                                    >
                                        @if ($isMulitple)
                                            <i class="bx bx-link-alt me-1"></i>
                                            {{ $displayCode }}
                                        @else
                                            {{ $displayCode }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if ($transaksi->penerima)
                                        @if ($transaksi->penerima_tipe === "App\Models\Siswa")
                                            {{ $transaksi->penerima->user->name ?? "-" }}
                                            <br />
                                            <small class="text-muted">
                                                NISN:
                                                {{ $transaksi->penerima->nisn ?? "-" }}
                                            </small>
                                        @else
                                            {{ $transaksi->penerima->name ?? "-" }}
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match ($transaksi->jenis_transaksi) {
                                            "setoran_tabungan" => "success",
                                            "penarikan_tabungan" => "danger",
                                            "pembayaran" => "info",
                                            "tagihan" => "info",
                                            default => "secondary",
                                        };
                                        $jenisText = match ($transaksi->jenis_transaksi) {
                                            "setoran_tabungan" => "Setoran Tabungan",
                                            "penarikan_tabungan" => "Penarikan Tabungan",
                                            "pembayaran" => "Pembayaran",
                                            "tagihan" => "Pembayaran Tagihan",
                                            default => ucwords(str_replace("_", " ", $transaksi->jenis_transaksi)),
                                        };
                                    @endphp

                                    <span
                                        class="badge rounded-pill bg-{{ $badgeColor }}"
                                    >
                                        {{ $jenisText }}
                                    </span>
                                    @if (in_array($transaksi->jenis_transaksi, ["tagihan", "pembayaran"]) && $transaksi->pembayaranTagihan)
                                        <br />
                                        <small class="text-muted">
                                            @if ($transaksi->pembayaranTagihan->tagihanSiswa && $transaksi->pembayaranTagihan->tagihanSiswa->tagihan)
                                                {{ $transaksi->pembayaranTagihan->tagihanSiswa->tagihan->nama_tagihan ?? "-" }}
                                            @endif
                                        </small>
                                        @if (

                                            $transaksi->pembayaranTagihan->tagihanSiswa &&
                                            $transaksi->pembayaranTagihan->tagihanSiswa->tagihan &&
                                            $transaksi->pembayaranTagihan->tagihanSiswa->tagihan->items->count() > 0                                        )
                                            <br />
                                            <small class="text-muted">
                                                @foreach ($transaksi->pembayaranTagihan->tagihanSiswa->tagihan->items as $item)
                                                    <span
                                                        class="badge badge-sm bg-light text-dark"
                                                    >
                                                        {{ $item->kategori->nama_kategori ?? "-" }}
                                                    </span>
                                                @endforeach
                                            </small>
                                        @endif
                                    @endif
                                </td>

                                <td>
                                    @if (in_array($transaksi->jenis_transaksi, ["setoran_tabungan", "pembayaran", "tagihan"]))
                                        <span class="text-success fw-bold">
                                            + Rp
                                            {{ number_format($transaksi->jumlah, 0, ",", ".") }}
                                        </span>
                                    @else
                                        <span class="text-danger fw-bold">
                                            - Rp
                                            {{ number_format($transaksi->jumlah, 0, ",", ".") }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $metodeBadge = match ($transaksi->metode) {
                                            "TUNAI" => "primary",
                                            "CASH" => "primary",
                                            "TRANSFER" => "info",
                                            "NONTUNAI" => "info",
                                            "SALDO_TABUNGAN" => "warning",
                                            default => "secondary",
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $metodeBadge }}">
                                        {{ $transaksi->metode }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format("d/m/Y") }}
                                </td>
                                <td>
                                    @if ($transaksi->status_verifikasi == "approved")
                                        <span
                                            class="badge bg-success rounded-pill"
                                        >
                                            <i
                                                class="bx bx-check-circle me-1"
                                            ></i>
                                            Approved
                                        </span>
                                    @elseif ($transaksi->status_verifikasi == "rejected")
                                        <span
                                            class="badge bg-danger rounded-pill"
                                        >
                                            <i class="bx bx-x-circle me-1"></i>
                                            Rejected
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-warning rounded-pill"
                                        >
                                            <i class="bx bx-time-five me-1"></i>
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $transaksi->approvedBy->name ?? ($transaksi->verifier->name ?? ($transaksi->creator->name ?? "-")) }}
                                </td>
                                <td>
                                    <div
                                        class="d-flex justify-content-center gap-1"
                                    >
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success rounded-pill btn-detail-trx"
                                            data-id="{{ $transaksi->id }}"
                                            title="Lihat Detail"
                                        >
                                            <i class="bx bx-show"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning rounded-pill btn-cetak-trx"
                                            data-id="{{ $transaksi->id }}"
                                            title="Cetak Struk"
                                        >
                                            <i class="bx bx-printer"></i>
                                        </button>
                                        {{-- @if ($transaksi->status_verifikasi == 'pending') --}}
                                        {{-- <button type="button" class="btn btn-sm btn-success rounded-pill btn-approve-trx" --}}
                                        {{-- data-id="{{ $transaksi->id }}" --}}
                                        {{-- title="Approve"> --}}
                                        {{-- <i class="bx bx-check"></i> --}}
                                        {{-- </button> --}}
                                        {{-- <button type="button" class="btn btn-sm btn-danger rounded-pill btn-reject-trx" --}}
                                        {{-- data-id="{{ $transaksi->id }}" --}}
                                        {{-- title="Reject"> --}}
                                        {{-- <i class="bx bx-x"></i> --}}
                                        {{-- </button> --}}
                                        {{-- @endif --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    Belum ada data transaksi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push("styles")
    <style>
        .animate-btn {
            transition: all 0.3s ease-in-out;
        }

        .animate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
        }

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
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
    />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        function changePerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }

        function showPendingTransactions(type = 'tabungan') {
            // Tentukan judul dan URL berdasarkan tipe
            const titles = {
                tabungan: 'Transaksi Pending Tabungan',
                tagihan: 'Transaksi Pending Tagihan/Pembayaran',
            };
            const icons = {
                tabungan: 'bx-time-five',
                tagihan: 'bx-receipt',
            };

            // Template modal
            const modalHtml = `
            <div class="modal fade" id="pendingModal" tabindex="-1" aria-labelledby="pendingModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title fw-bold" id="pendingModalLabel">
                                <i class="bx ${icons[type]} me-2"></i>${titles[type]}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                                <div class="spinner-border text-warning" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-3">Memuat data transaksi pending...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

            const existingModal = document.getElementById('pendingModal');
            if (existingModal) existingModal.remove();

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            const modal = new bootstrap.Modal(
                document.getElementById('pendingModal'),
            );
            modal.show();

            // URL berbeda berdasarkan tipe
            const url =
                type === 'tagihan'
                    ? '/keuangan-transaksi/pending-tagihan'
                    : '/keuangan-transaksi/pending-tabungan';

            fetch(url, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    const modalBody = document.querySelector(
                        '#pendingModal .modal-body',
                    );

                    if (data.success && data.data?.length > 0) {
                        let tableRows = '';

                        data.data.forEach((trx, index) => {
                            // Determine badge based on transaction type
                            let jenisClass, jenisIcon, jenisText;

                            if (type === 'tagihan') {
                                jenisClass = 'info';
                                jenisIcon = 'receipt';
                                jenisText = 'Pembayaran Tagihan';
                            } else {
                                jenisClass =
                                    trx.jenis_transaksi === 'setoran_tabungan'
                                        ? 'success'
                                        : 'danger';
                                jenisIcon =
                                    trx.jenis_transaksi === 'setoran_tabungan'
                                        ? 'plus-circle'
                                        : 'minus-circle';
                                jenisText =
                                    trx.jenis_transaksi === 'setoran_tabungan'
                                        ? 'Setoran'
                                        : 'Penarikan';
                            }

                            const tanggal = new Date(
                                trx.tanggal_transaksi,
                            ).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                            });

                            let statusBadge = '';
                            if (type === 'tagihan') {
                                statusBadge =
                                    '<span class="badge bg-warning text-dark"><i class="bx bx-time-five me-1"></i>Menunggu Verifikasi</span>';
                            } else if (
                                trx.jenis_transaksi === 'penarikan_tabungan'
                            ) {
                                statusBadge =
                                    trx.status_approval === 'pending'
                                        ? '<span class="badge bg-warning text-dark"><i class="bx bx-time-five me-1"></i>Belum Verify Token</span>'
                                        : '';
                            } else {
                                statusBadge =
                                    '<span class="badge bg-info"><i class="bx bx-info-circle me-1"></i>Menunggu Verifikasi</span>';
                            }

                            // Additional info for tagihan
                            const additionalInfo =
                                type === 'tagihan' && trx.nama_tagihan
                                    ? `<br><small class="text-muted">${trx.nama_tagihan}</small>`
                                    : '';

                            tableRows += `
                        <tr class="align-middle">
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">
                                <span class="badge bg-${jenisClass} px-3 py-2">
                                    <i class="bx bx-${jenisIcon} me-1"></i>${jenisText}
                                </span>
                                ${additionalInfo}
                            </td>
                            <td><strong>${trx.nomor_transaksi || trx.code_pembayaran}</strong></td>
                            <td>${trx.siswa_nama || '-'}</td>
                            <td class="text-end"><strong>Rp ${new Intl.NumberFormat('id-ID').format(trx.jumlah)}</strong></td>
                            <td class="text-center"><small>${tanggal}</small></td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary rounded-pill" onclick="viewDetailPending(${trx.transaksi_id})">
                                    <i class="bx bx-show me-1"></i>Detail
                                </button>
                            </td>
                        </tr>
                    `;
                        });

                        modalBody.innerHTML = `
                    <div class="alert alert-warning border-0 shadow-sm mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-info-circle fs-4 me-3"></i>
                            <div>
                                <strong>Perhatian:</strong> Terdapat <strong>${data.data.length}</strong> transaksi yang menunggu approval.
                                <br><small>Untuk penarikan tabungan, verifikasi token terlebih dahulu sebelum approve.</small>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-warning text-white">
                                <tr>
                                    <th class="text-center" style="width:50px;">#</th>
                                    <th class="text-center" style="width:120px;">Jenis</th>
                                    <th style="width:150px;">Nomor Transaksi</th>
                                    <th style="width:200px;">Nama Siswa</th>
                                    <th class="text-end" style="width:130px;">Jumlah</th>
                                    <th class="text-center" style="width:150px;">Tanggal</th>
                                    <th class="text-center" style="width:150px;">Status</th>
                                    <th class="text-center" style="width:100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>${tableRows}</tbody>
                        </table>
                    </div>
                `;
                    } else {
                        modalBody.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bx bx-check-circle text-success" style="font-size:80px;"></i>
                        <h4 class="mt-3">Tidak Ada Transaksi Pending</h4>
                        <p class="text-muted">Semua transaksi sudah diproses</p>
                    </div>
                `;
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    const modalBody = document.querySelector(
                        '#pendingModal .modal-body',
                    );
                    modalBody.innerHTML = `
                <div class="text-center py-5">
                    <i class="bx bx-error-circle text-danger" style="font-size:80px;"></i>
                    <h4 class="mt-3">Gagal Memuat Data</h4>
                    <p class="text-muted">Terjadi kesalahan saat memuat transaksi pending</p>
                    <button class="btn btn-primary" onclick="showPendingTransactions()">
                        <i class="bx bx-refresh me-1"></i>Coba Lagi
                    </button>
                </div>
            `;
                });
        }

        function viewDetailPending(transaksiId) {
            const pendingModal = bootstrap.Modal.getInstance(
                document.getElementById('pendingModal'),
            );
            if (pendingModal) pendingModal.hide();
            window.location.href = `/keuangan-transaksi/show/${transaksiId}`;
        }

        function showDetailTransaksi(transaksiId) {
            Swal.fire({
                title: 'Memuat...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            fetch(`/api/v1/tabungan/${transaksiId}/detail`, {
                method: 'GET',
                headers: {
                    Authorization:
                        'Bearer {{ auth()->user()->api_token ?? "" }}',
                    Accept: 'application/json',
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        const trx = data.data;
                        const statusBadge = getStatusBadge(
                            trx.status_pembayaran,
                        );
                        const buktiHtml = trx.bukti_transfer
                            ? `<div class="mb-3">
                        <strong>Bukti Transfer:</strong><br>
                        <a href="${trx.bukti_transfer}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="bx bx-image me-1"></i>Lihat Bukti Transfer
                        </a>
                    </div>`
                            : '<div class="alert alert-warning"><i class="bx bx-info-circle me-1"></i>Bukti transfer belum diupload</div>';

                        const catatanHtml = trx.catatan_verifikasi
                            ? `<div class="alert alert-info">
                        <strong>Catatan Verifikasi:</strong><br>
                        ${trx.catatan_verifikasi}<br>
                        <small class="text-muted">Oleh: ${trx.verified_by || '-'} pada ${trx.verified_at || '-'}</small>
                    </div>`
                            : '';

                        const actionButtons =
                            trx.status_pembayaran === 'pending'
                                ? `<div class="mt-4 d-flex gap-2 justify-content-center">
                        <button class="btn btn-success" onclick="approveTransaksi(${transaksiId})">
                            <i class="bx bx-check-circle me-1"></i>Approve
                        </button>
                        <button class="btn btn-danger" onclick="rejectTransaksi(${transaksiId})">
                            <i class="bx bx-x-circle me-1"></i>Reject
                        </button>
                    </div>`
                                : '';

                        Swal.fire({
                            title: 'Detail Transaksi',
                            html: `
                        <div class="text-start">
                            <div class="mb-3">
                                <strong>Nomor Transaksi:</strong><br>
                                <span class="badge bg-secondary">${trx.nomor_transaksi}</span>
                            </div>
                            <div class="mb-3"><strong>Jenis Transaksi:</strong><br>${trx.jenis_transaksi}</div>
                            <div class="mb-3"><strong>Jumlah:</strong><br>
                                <h4 class="text-primary">Rp ${new Intl.NumberFormat('id-ID').format(trx.jumlah)}</h4>
                            </div>
                            <div class="mb-3"><strong>Tanggal Transaksi:</strong><br>${trx.tanggal_transaksi}</div>
                            <div class="mb-3"><strong>Deskripsi:</strong><br>${trx.deskripsi || '-'}</div>
                            <div class="mb-3"><strong>Status:</strong><br>${statusBadge}</div>
                            ${buktiHtml}
                            ${catatanHtml}
                            ${actionButtons}
                        </div>
                    `,
                            width: '600px',
                            showCloseButton: true,
                            showConfirmButton: false,
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal memuat detail transaksi',
                        confirmButtonColor: '#f56565',
                    });
                });
        }

        // Handle detail button click
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-detail-trx').forEach((button) => {
                button.addEventListener('click', function () {
                    const transaksiId = this.dataset.id;
                    window.location.href = `{{ url("keuangan-transaksi/show") }}/${transaksiId}`;
                });
            });

            // Handle cetak struk button click
            document.querySelectorAll('.btn-cetak-trx').forEach((button) => {
                button.addEventListener('click', function () {
                    const transaksiId = this.dataset.id;
                    window.open(
                        `{{ url("keuangan-transaksi/cetak-struk") }}/${transaksiId}`,
                        '_blank',
                    );
                });
            });

            // Handle approve button click
            document.querySelectorAll('.btn-approve-trx').forEach((button) => {
                button.addEventListener('click', function () {
                    const transaksiId = this.dataset.id;
                    approveTransaksi(transaksiId);
                });
            });

            // Handle reject button click
            document.querySelectorAll('.btn-reject-trx').forEach((button) => {
                button.addEventListener('click', function () {
                    const transaksiId = this.dataset.id;
                    rejectTransaksi(transaksiId);
                });
            });
        });

        function approveTransaksi(transaksiId) {
            Swal.fire({
                title: 'Approve Transaksi',
                html: `
                    <div class="text-start">
                        <label for="catatan-approve" class="form-label">Catatan (Opsional)</label>
                        <textarea id="catatan-approve" class="form-control" rows="3" placeholder="Masukkan catatan verifikasi..."></textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#48bb78',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bx bx-check me-1"></i> Approve',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    const catatan =
                        document.getElementById('catatan-approve').value;
                    processApproval(transaksiId, catatan);
                }
            });
        }

        function rejectTransaksi(transaksiId) {
            Swal.fire({
                title: 'Reject Transaksi',
                html: `
                    <div class="text-start">
                        <label for="catatan-reject" class="form-label">Alasan Reject <span class="text-danger">*</span></label>
                        <textarea id="catatan-reject" class="form-control" rows="3" placeholder="Masukkan alasan reject..." required></textarea>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f56565',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bx bx-x me-1"></i> Reject',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const catatan =
                        document.getElementById('catatan-reject').value;
                    if (!catatan) {
                        Swal.showValidationMessage('Alasan reject harus diisi');
                        return false;
                    }
                    return catatan;
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    processRejection(transaksiId, result.value);
                }
            });
        }

        function processApproval(transaksiId, catatan) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            fetch(`{{ url("keuangan-transaksi/approve") }}/${transaksiId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    catatan_verifikasi: catatan,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#48bb78',
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message,
                            confirmButtonColor: '#f56565',
                        });
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat approve transaksi',
                        confirmButtonColor: '#f56565',
                    });
                });
        }

        function processRejection(transaksiId, catatan) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            fetch(`{{ url("keuangan-transaksi/reject") }}/${transaksiId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    catatan_verifikasi: catatan,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#48bb78',
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message,
                            confirmButtonColor: '#f56565',
                        });
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat reject transaksi',
                        confirmButtonColor: '#f56565',
                    });
                });
        }

        // Initialize datepicker dengan format DD/MM/YYYY
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('.datepicker', {
                dateFormat: 'd/m/Y',
                allowInput: true,
                onChange: function (selectedDates, dateStr, instance) {
                    // Convert DD/MM/YYYY to YYYY-MM-DD for backend
                    if (selectedDates.length > 0) {
                        const date = selectedDates[0];
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(
                            2,
                            '0',
                        );
                        const day = String(date.getDate()).padStart(2, '0');
                        const formattedDate = `${year}-${month}-${day}`;

                        // Set the actual input value to backend format
                        if (instance.element.id === 'dari_tanggal') {
                            instance.element.setAttribute(
                                'data-value',
                                formattedDate,
                            );
                        } else if (instance.element.id === 'sampai_tanggal') {
                            instance.element.setAttribute(
                                'data-value',
                                formattedDate,
                            );
                        }
                    }
                },
            });

            // Convert dates before form submission
            document
                .querySelector('form')
                .addEventListener('submit', function (e) {
                    const dariTanggal = document.getElementById('dari_tanggal');
                    const sampaiTanggal =
                        document.getElementById('sampai_tanggal');

                    // Convert dari_tanggal
                    if (dariTanggal.value) {
                        const dataValue =
                            dariTanggal.getAttribute('data-value');
                        if (dataValue) {
                            dariTanggal.value = dataValue;
                        } else {
                            const parts = dariTanggal.value.split('/');
                            if (parts.length === 3) {
                                dariTanggal.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
                            }
                        }
                    }

                    // Convert sampai_tanggal
                    if (sampaiTanggal.value) {
                        const dataValue =
                            sampaiTanggal.getAttribute('data-value');
                        if (dataValue) {
                            sampaiTanggal.value = dataValue;
                        } else {
                            const parts = sampaiTanggal.value.split('/');
                            if (parts.length === 3) {
                                sampaiTanggal.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
                            }
                        }
                    }
                });
        });
    </script>
    @if ($transaksis->isNotEmpty())
        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    searching: false,
                    scrollX: true,
                    language: {
                        url: '{{ asset("assets/datatables/id.json") }}',
                    },
                });

                // ✅ Konfirmasi hapus data
                $('.btn-delete').on('click', function (e) {
                    e.preventDefault();
                    const form = $(this).closest('form');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Data penggajian ini akan dihapus permanen!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endif
@endpush
