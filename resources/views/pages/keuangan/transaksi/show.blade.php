@extends("layouts.app")
@section("title", "Detail Transaksi")

@push('styles')
    <link rel="stylesheet" href="{{ asset("assets/css/style.css") }}" />
@endpush

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => "Detail Transaksi",
            "subTitle" => "Keuangan / Transaksi",
        ]
    )

    <div class="row g-4">
        {{-- Detail Transaksi --}}
        <div class="col-md-5">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bx bx-receipt"></i>
                    Informasi Transaksi
                </h5>
                <hr />

                <div class="mb-3">
                    <span class="badge bg-secondary fs-6">
                        {{ $transaksi->code_pembayaran }}
                    </span>
                </div>

                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>Jenis Transaksi:</strong>
                        <br />
                        @php
                            $badgeColor = match ($transaksi->jenis_transaksi) {
                                "setoran_tabungan" => "success",
                                "penarikan_tabungan" => "warning",
                                "pembayaran" => "info",
                                default => "secondary",
                            };
                            $jenisText = match ($transaksi->jenis_transaksi) {
                                "setoran_tabungan" => "Setoran Tabungan",
                                "penarikan_tabungan" => "Penarikan Tabungan",
                                "pembayaran" => "Pembayaran",
                                "pembayaran-multiple" => "Pembayaran Multiple",
                                default => ucwords(str_replace("_", " ", $transaksi->jenis_transaksi)),
                            };
                        @endphp

                        <span class="badge rounded-pill bg-{{ $badgeColor }}">
                            {{ $jenisText }}
                        </span>
                    </li>

                    <li class="mb-2">
                        <strong>Jumlah:</strong>
                        <br />

                        @if (in_array($transaksi->jenis_transaksi, ["setoran_tabungan", "pembayaran", "tagihan"]))
                            <span class="text-success fw-bold fs-5">
                                Rp
                                {{ number_format($transaksi->jumlah, 0, ",", ".") }}
                            </span>
                        @else
                            <span class="text-danger fw-bold fs-5">
                                Rp
                                {{ number_format($transaksi->jumlah, 0, ",", ".") }}
                            </span>
                        @endif
                    </li>

                    <li class="mb-2">
                        <strong>Metode:</strong>
                        <br />
                        @php
                            $metodeBadge = match ($transaksi->metode) {
                                "CASH" => "primary",
                                "TRANSFER" => "info",
                                "SALDO_TABUNGAN" => "warning",
                                default => "secondary",
                            };
                        @endphp

                        <span class="badge bg-{{ $metodeBadge }}">
                            {{ $transaksi->metode }}
                        </span>
                    </li>

                    <li class="mb-2">
                        <strong>Tanggal Transaksi:</strong>
                        <br />
                        {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->translatedFormat("d F Y") }}
                    </li>

                    @if ($transaksi->referensi_tagihan_id)
                        <li class="mb-2">
                            <strong>Referensi Tagihan:</strong>
                            <br />
                            {{ $transaksi->referensi_tagihan_id }}
                        </li>
                    @endif

                    @if ($transaksi->keterangan)
                        <li class="mb-2">
                            <strong>Keterangan:</strong>
                            <br />
                            {{ $transaksi->keterangan }}
                        </li>
                    @endif
                </ul>

                <hr />

                {{-- Detail Tagihan untuk Single Payment atau Multiple Payment --}}
                @if (in_array($transaksi->jenis_transaksi, ["tagihan", "pembayaran", "pembayaran-multiple"]) && $transaksi->pembayaranTagihan)
                    {{-- Jika ini Multi-Tagihan (pembayaran-multiple), tampilkan list tagihan --}}
                    @if ($transaksi->jenis_transaksi == "pembayaran-multiple" && $pembayaranDetail && $pembayaranDetail->count() > 1)
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bx bx-list-check"></i>
                            Daftar Tagihan ({{ $pembayaranDetail->count() }}
                            tagihan)
                        </h6>

                        @if ($headTagihan)
                            <div class="alert alert-info mb-3 small">
                                <strong>
                                    <i class="bx bx-tag me-1"></i>
                                    Head Tagihan:
                                </strong>
                                <span
                                    class="font-monospace bg-white px-2 py-1 rounded"
                                >
                                    {{ $headTagihan }}
                                </span>
                            </div>
                        @endif

                        <div class="table-responsive mb-3">
                            <table
                                class="table table-sm table-hover align-middle"
                            >
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 20%">Nama Tagihan</th>
                                        <th style="width: 15%">Periode</th>
                                        <th style="width: 15%">Nominal</th>
                                        <th style="width: 15%">Potongan</th>
                                        <th style="width: 15%">Dibayar</th>
                                        <th style="width: 10%">Siswa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pembayaranDetail as $detail)
                                    {{-- @php
    dd(
        $detail->getRelations(),
        method_exists($detail, 'tagihan'),
        optional($detail->tagihan)->periode
    );
@endphp --}}

                                        @php
                                            $bulanIndo = [
                                                "January" => "Januari",
                                                "February" => "Februari",
                                                "March" => "Maret",
                                                "April" => "April",
                                                "May" => "Mei",
                                                "June" => "Juni",
                                                "July" => "Juli",
                                                "August" => "Agustus",
                                                "September" => "September",
                                                "October" => "Oktober",
                                                "November" => "November",
                                                "December" => "Desember",
                                                "Oktober" => "Oktober",
                                                "Januari" => "Januari",
                                                "Februari" => "Februari",
                                                "Maret" => "Maret",
                                                "Mei" => "Mei",
                                                "Juni" => "Juni",
                                                "Juli" => "Juli",
                                                "Agustus" => "Agustus",
                                                "November" => "November",
                                                "Desember" => "Desember",
                                            ];

                                            // Mapping angka ke nama bulan Indonesia
                                            $namaBulan = [
                                                1 => "Januari",
                                                2 => "Februari",
                                                3 => "Maret",
                                                4 => "April",
                                                5 => "Mei",
                                                6 => "Juni",
                                                7 => "Juli",
                                                8 => "Agustus",
                                                9 => "September",
                                                10 => "Oktober",
                                                11 => "November",
                                                12 => "Desember",
                                            ];

                                            $periode = $detail->tagihanSiswa->tagihan->periode;


                                            $tahun = $detail->tahun ?? date("Y");

                                            // Convert periode: jika angka gunakan mapping angka, jika bahasa Inggris gunakan bulanIndo
                                            if (is_numeric($periode) && isset($namaBulan[(int) $periode])) {
                                                $periodeIndo = $namaBulan[(int) $periode];
                                            } else {
                                                $periodeIndo = $bulanIndo[$periode] ?? $periode;
                                            }
                                        @endphp

                                        <tr>
                                            <td class="fw-bold text-center">
                                                <span
                                                    class="badge bg-primary rounded-circle"
                                                >
                                                    {{ $detail->urutan }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-dark">
                                                    {{ $detail->tagihanSiswa->tagihanItem->kategori->nama_kategori ?? "Tagihan" }}
                                                </strong>
                                            </td>

                                            <td>
                                                <span
                                                    class="badge bg-light text-dark"
                                                >
                                                    {{ $periodeIndo }}
                                                    {{ $tahun }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    Rp
                                                    {{ number_format($detail->tagihanSiswa->tagihanItem->nominal ?? 0, 0, ",", ".") }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $totalPotongan = $detail->tagihanSiswa->potonganSiswa->sum("nominal");
                                                @endphp

                                                @if ($totalPotongan > 0)
                                                    <span
                                                        class="text-warning fw-bold"
                                                    >
                                                        <small
                                                            class="badge bg-warning text-dark"
                                                        >
                                                            -Rp
                                                            {{ number_format($totalPotongan, 0, ",", ".") }}
                                                        </small>
                                                    </span>
                                                @elseif ($totalPotongan === 0)
                                                    <span class="text-muted">
                                                        -
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="text-success fw-bold"
                                                >
                                                    Rp
                                                    {{ number_format($detail->jumlah_bayar_detail, 0, ",", ".") }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ substr($detail->tagihanSiswa->siswa->user->name ?? "-", 0, 15) }}
                                                </small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td
                                                colspan="7"
                                                class="text-center text-muted"
                                            >
                                                Tidak ada detail tagihan
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">
                                            Total Pembayaran:
                                        </th>
                                        <th>
                                            @php
                                                $totalAllPotongan = $pembayaranDetail->sum(function ($item) {
                                                    return $item->tagihanSiswa->potonganSiswa->sum("nominal");
                                                });
                                            @endphp

                                            @if ($totalAllPotongan > 0)
                                                <span
                                                    class="text-warning fw-bold"
                                                >
                                                    -Rp
                                                    {{ number_format($totalAllPotongan, 0, ",", ".") }}
                                                </span>
                                            @else
                                                    -
                                            @endif
                                        </th>
                                        <th
                                            colspan="2"
                                            class="text-success fw-bold"
                                        >
                                            Rp
                                            {{ number_format($pembayaranDetail->sum("jumlah_bayar_detail"), 0, ",", ".") }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <hr />

                        {{-- Single Payment Detail --}}
                    @else
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bx bx-receipt"></i>
                            Detail Tagihan
                        </h6>
                        <ul class="list-unstyled small">
                            @if ($transaksi->pembayaranTagihan->tagihanSiswa)
                                @php
                            
                                    $tagihanSiswa = $transaksi->pembayaranTagihan->tagihanSiswa;

                                    $tagihan = $tagihanSiswa->tagihan;

                                    // Konversi periode (angka bulan) menjadi nama bulan
                                    $namaBulan = [
                                        1 => "Januari",
                                        2 => "Februari",
                                        3 => "Maret",
                                        4 => "April",
                                        5 => "Mei",
                                        6 => "Juni",
                                        7 => "Juli",
                                        8 => "Agustus",
                                        9 => "September",
                                        10 => "Oktober",
                                        11 => "November",
                                        12 => "Desember",
                                    ];
                                    $periodeBulan =
                                        isset($tagihanSiswa->bulan_ke) && isset($namaBulan[$tagihanSiswa->bulan_ke])
                                            ? $namaBulan[$tagihanSiswa->bulan_ke]
                                            : $tagihanSiswa->bulan_ke;
                                @endphp

                                <li>
                                    <strong>Periode:</strong>
                                    {{ $pembayaranDetail->first()->periode }}
                                    {{ $tagihan->tahun_mulai ?? "" }}
                                </li>
                                <li>
                                    <strong>Dibayar:</strong>
                                    Rp
                                    {{ number_format($transaksi->pembayaranTagihan->jumlah_bayar ?? 0, 0, ",", ".") }}
                                </li>
                                @if ($tagihan && $tagihan->items && $tagihan->items->count() > 0)
                                    <li class="mt-2">
                                        <strong>Jenis Tagihan:</strong>
                                    </li>
                                    <ul class="mt-1">
                                        @foreach ($tagihan->items as $item)
                                            <li>
                                                {{ $item->kategori->nama_kategori ?? "-" }}
                                                - Rp
                                                {{ number_format($item->nominal ?? 0, 0, ",", ".") }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($tagihanSiswa->potonganSiswa && $tagihanSiswa->potonganSiswa->count() > 0)
                                    <li class="mt-2">
                                        <strong>
                                            Potongan (Diskon/Beasiswa):
                                        </strong>
                                    </li>
                                    <ul class="mt-1">
                                        @foreach ($tagihanSiswa->potonganSiswa as $potongan)
                                            <li>
                                                <span
                                                    class="badge bg-warning text-dark"
                                                >
                                                    {{ $potongan->potongan->tipe_potongan === "persentase" ? $potongan->potongan->nilai . "%" : "Nominal" }}
                                                </span>
                                                {{ $potongan->potongan->keterangan ?? "-" }}
                                                - Rp
                                                {{ number_format($potongan->nominal ?? 0, 0, ",", ".") }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                <li class="text-muted">
                                    Detail tagihan tidak tersedia
                                </li>
                            @endif
                        </ul>

                        <hr />
                    @endif
                @endif

                <h6 class="fw-bold text-primary mb-2">
                    <i class="bx bx-user"></i>
                    Siswa
                </h6>
                @if ($transaksi->penerima)
                    @if ($transaksi->penerima_tipe === "App\Models\Siswa")
                        <ul class="list-unstyled small">
                            <li class="info-row">
                                <strong class="info-label">Nama</strong>
                                <strong class="info-sep">:</strong>
                                <span class="info-value">
                                    {{ $transaksi->penerima->user->name ?? "-" }}
                                </span>
                            </li>
                            <li class="info-row">
                                <strong class="info-label">NISN</strong>
                                <strong class="info-sep">:</strong>
                                <span class="info-value">{{ $transaksi->penerima->nisn ?? "-" }}</span>
                                
                            </li>
                            <li class="info-row">
                                <strong class="info-label">Kelas</strong>
                                <strong class="info-sep">:</strong>
                                <span class="info-value">{{ $transaksi->penerima->kelas->nama_kelas ?? "-" }}</span>
                            </li>
                            <li class="info-row">
                                <strong class="info-label">Unit</strong>
                                <strong class="info-sep">:</strong>
                                <span class="info-value">{{ $transaksi->penerima->unit->nama_unit ?? "-" }}</span>      
                            </li>
                        </ul>
                    @else
                        <p class="small">
                            {{ $transaksi->penerima->name ?? "-" }}
                        </p>
                    @endif
                @else
                    <p class="text-muted small">-</p>
                @endif

                <hr />

                {{-- Status Verifikasi --}}
                <h6 class="fw-bold text-primary mb-2">
                    <i class="bx bx-check-shield"></i>
                    Status Verifikasi
                </h6>
                <div class="mb-3">
                    @if ($transaksi->status_verifikasi == "approved")
                        <span class="badge bg-success rounded-pill px-3 py-2">
                            <i class="bx bx-check-circle me-1"></i>
                            Approved
                        </span>
                    @elseif ($transaksi->status_verifikasi == "rejected")
                        <span class="badge bg-danger rounded-pill px-3 py-2">
                            <i class="bx bx-x-circle me-1"></i>
                            Rejected
                        </span>
                    @else
                        <span class="badge bg-warning rounded-pill px-3 py-2">
                            <i class="bx bx-time-five me-1"></i>
                            Pending
                        </span>
                    @endif

                    @if ($transaksi->verified_by && $transaksi->verified_at)
                        <div class="mt-2 small text-muted">
                            <i class="bx bx-user me-1"></i>
                            Diverifikasi oleh:
                            {{ $transaksi->verifier->name ?? "-" }}
                            <br />
                            <i class="bx bx-calendar me-1"></i>
                            Pada:
                            {{ \Carbon\Carbon::parse($transaksi->verified_at)->format("d/m/Y H:i:s") }}
                        </div>
                    @endif

                    @if ($transaksi->catatan_verifikasi)
                        <div class="alert alert-info mt-2 small">
                            <strong>Catatan Verifikasi:</strong>
                            <br />
                            {{ $transaksi->catatan_verifikasi }}
                        </div>
                    @endif

                    {{-- token --}}
                    @if ($transaksi->token)
                        <div class="alert alert-info mt-2 small">
                            <strong>Token:</strong>
                            <br />
                            {{ $transaksi->token }}
                        </div>
                    @endif
                </div>

                {{-- Bukti Transfer --}}
                @php
                    $buktiBayar = null;
                    $buktiBayarUrl = null;
                    // Cek bukti transfer dari transaksi
                    if ($transaksi->bukti_transfer) {
                        $buktiBayar = $transaksi->bukti_transfer;
                        $buktiBayarUrl = asset($buktiBayar);
                    }
                    // Cek bukti dari pembayaran tagihan
                    elseif (in_array($transaksi->jenis_transaksi, ["pembayaran", "tagihan"]) && $transaksi->pembayaranTagihan && $transaksi->pembayaranTagihan->file_bukti) {
                        $buktiBayar = $transaksi->pembayaranTagihan->file_bukti;
                        // Gunakan Storage::disk('public')->url() untuk file pembayaran tagihan
                        $buktiBayarUrl = \Illuminate\Support\Facades\Storage::disk("public")->url($buktiBayar);
                    }
                @endphp
                @php
                    $hasBukti = !empty($buktiBayar) && !empty($buktiBayarUrl);
                    $isPending = $transaksi->status_verifikasi === 'pending';
                    $allowedJenis = ['setoran_tabungan', 'pembayaran', 'tagihan'];
                    $isAllowedJenis = in_array($transaksi->jenis_transaksi, $allowedJenis);
                    $canVerify = $hasBukti && $isPending && $isAllowedJenis;
               @endphp


                @if ($hasBukti)
                    <hr />
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="bx bx-image"></i>
                        Bukti Pembayaran
                    </h6>
                    <div class="mb-3">
                        <a
                            href="{{ $buktiBayarUrl }}"
                            target="_blank"
                            class="btn btn-outline-primary btn-sm"
                        >
                            <i class="bx bx-download me-1"></i>
                            Lihat Bukti Pembayaran
                        </a>
                        <div class="mt-2">
                            @if (Str::endsWith($buktiBayar, [".pdf", ".PDF"]))
                                <embed
                                    src="{{ $buktiBayarUrl }}"
                                    type="application/pdf"
                                    width="100%"
                                    height="400px"
                                    class="rounded shadow-sm"
                                />
                            @else
                                <img
                                    src="{{ $buktiBayarUrl }}"
                                    alt="Bukti Pembayaran"
                                    class="img-fluid rounded shadow-sm"
                                    style="max-height: 300px; cursor: pointer"
                                    onclick="
                                        window.open(
                                            '{{ $buktiBayarUrl }}',
                                            '_blank',
                                        )
                                    "
                                />
                            @endif
                        </div>
                    </div>
                @endif

                @if (!$hasBukti && $isPending && $isAllowedJenis)
                <div class="alert alert-warning d-flex align-items-center shadow-sm">
                    <i class="bx bx-error-circle me-2 fs-5"></i>
                    <div>
                        Bukti pembayaran <strong>belum diupload</strong>. <br>
                        Silahkan menunggu upload bukti terlebih dahulu sebelum melakukan verifikasi.
                    </div>
                </div>      
                @endif
                {{-- Keterangan dari siswa (untuk pembayaran tagihan) --}}
                @if (in_array($transaksi->jenis_transaksi, ["pembayaran", "tagihan"]) && $transaksi->pembayaranTagihan && $transaksi->pembayaranTagihan->keterangan_siswa)
                    <div class="mb-3">
                        <div class="alert alert-secondary small">
                            <strong>Keterangan dari Siswa:</strong>
                            <br />
                            {{ $transaksi->pembayaranTagihan->keterangan_siswa }}
                        </div>
                    </div>
                @endif

                <hr />

                <ul class="list-unstyled small text-muted">
                    <li>
                        <strong>Dibuat Oleh:</strong>
                        {{ $transaksi->creator->name ?? "-" }}
                    </li>
                    <li>
                        <strong>Dibuat Pada:</strong>
                        {{ \Carbon\Carbon::parse($transaksi->created_at)->format("d/m/Y H:i:s") }}
                    </li>
                    @if ($transaksi->updated_at != $transaksi->created_at)
                        <li>
                            <strong>Terakhir Update:</strong>
                            {{ \Carbon\Carbon::parse($transaksi->updated_at)->format("d/m/Y H:i:s") }}
                        </li>
                    @endif
                </ul>

                {{-- Token Verification Button (untuk penarikan tabungan yang pending) --}}

                {{-- Approve/Reject Buttons --}}
                @php
                    // Detect apakah ini pembayaran multiple atau single
                    // Jika ada head_tagihan maka multiple, jika tidak ada maka single
                    $headTagihan = $transaksi->pembayaranTagihan->head_tagihan ?? null;
                    $isMultiple = ! empty($headTagihan);
                    $pembayaranId = $transaksi->referensi_tagihan_id;
                @endphp

                @if ($transaksi->status_verifikasi == "pending")
                    @if ($transaksi->jenis_transaksi == "penarikan_tabungan" && $transaksi->status_approval == "pending")
                        <div class="mt-3">
                            <button
                                type="button"
                                class="btn btn-sm btn-success rounded-pill text-nowrap btn-verify w-100 shadow-sm mb-2"
                                data-id="{{ $transaksi->id }}"
                                data-token="{{ $transaksi->token }}"
                                data-jumlah="{{ $transaksi->jumlah }}"
                                title="{{  'Verifikasi dengan Token' }}"
                            >
                                <i class="bx bx-key"></i>
                                Verifikasi Token
                            </button>
                            <button
                                type="button"
                                class="btn btn-danger w-100 rounded-pill shadow-sm mb-2 btn-reject-detail"
                                data-id="{{ $transaksi->id }}"
                                data-is-multiple="false"
                            >
                                <i class="bx bx-x-circle me-1"></i>
                                Reject Transaksi
                            </button>
                        </div>
                    @else
                        <div class="mt-3">
                            <button
                                type="button"
                                class="btn btn-success w-100 rounded-pill shadow-sm mb-2 btn-approve-detail"
                                data-id="{{ $transaksi->id }}"
                                data-is-multiple="{{ $isMultiple ? "true" : "false" }}"
                                @if($isMultiple) data-head-tagihan="{{ $headTagihan }}" @endif
                                {{ !$canVerify ? 'disabled' : '' }}
                            >
                                <i class="bx bx-check-circle me-1"></i>
                                {{ $isMultiple ? "Approve Pembayaran Multiple" : "Approve Transaksi" }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-danger w-100 rounded-pill shadow-sm mb-2 btn-reject-detail"
                                data-id="{{ $transaksi->id }}"
                                data-is-multiple="{{ $isMultiple ? "true" : "false" }}"
                                @if($isMultiple) data-head-tagihan="{{ $headTagihan }}" @endif
                            >
                                <i class="bx bx-x-circle me-1"></i>
                                {{ $isMultiple ? "Reject Pembayaran Multiple" : "Reject Transaksi" }}
                            </button>
                        </div>
                    @endif
                @endif

                <div class="mt-4 d-flex justify-content-between">
                    <a
                        href="{{ route("keuangan_transaksi.index") }}"
                        class="btn btn-secondary rounded-2 shadow-sm mb-2"
                    >
                        <i class="bx bx-arrow-back"></i>
                        Kembali
                    </a>
                    <a
                        href="{{ route("keuangan_transaksi.print_detail", $transaksi->id) }}"
                        target="_blank"
                        class="btn btn-primary rounded-2 shadow-sm mb-2"
                    >
                        <i class="bx bx-printer"></i>
                        Cetak Detail
                    </a>
                    <a
                        href="{{ route("keuangan_transaksi.index") }}"
                        id="btnBatalkan"
                        class="btn btn-danger rounded-2 shadow-sm mb-2"
                    >
                        <i class="bx bx-x"></i>
                        Batalkan Transaksi
                    </a>
                </div>
            </div>
        </div>

        {{-- History Jurnal --}}
        <div class="col-md-7">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bx bx-book"></i>
                    History Jurnal
                </h5>
                <hr />

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Akun</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaksi->jurnals as $jurnal)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>
                                            {{ $jurnal->akun->kode_akun ?? "-" }}
                                        </strong>
                                        <br />
                                        <small class="text-muted">
                                            {{ $jurnal->akun->nama_akun ?? "-" }}
                                        </small>
                                    </td>
                                    <td>
                                        @if ($jurnal->debit > 0)
                                            <span class="text-success fw-bold">
                                                Rp
                                                {{ number_format($jurnal->debit, 0, ",", ".") }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($jurnal->kredit > 0)
                                            <span class="text-danger fw-bold">
                                                Rp
                                                {{ number_format($jurnal->kredit, 0, ",", ".") }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $jurnal->keterangan ?? "-" }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="text-center text-muted"
                                    >
                                        Belum ada jurnal
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Total:</th>
                                <th class="text-success">
                                    Rp
                                    {{ number_format($transaksi->jurnals->sum("debit"), 0, ",", ".") }}
                                </th>
                                <th class="text-danger">
                                    Rp
                                    {{ number_format($transaksi->jurnals->sum("kredit"), 0, ",", ".") }}
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Activity Logs --}}
            @if (isset($logs) && $logs->count() > 0)
                <div class="card p-4 shadow-sm rounded-4 border-0 mt-4">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bx bx-history"></i>
                        Activity Log
                    </h5>
                    <hr />

                    <div class="timeline">
                        @foreach ($logs as $log)
                            <div class="timeline-item mb-3">
                                <div class="d-flex gap-3">
                                    <div>
                                        @php
                                            $iconColor = match ($log->aksi) {
                                                "create" => "success",
                                                "update" => "warning",
                                                "delete" => "danger",
                                                default => "secondary",
                                            };
                                            $iconClass = match ($log->aksi) {
                                                "create" => "bx-plus-circle",
                                                "update" => "bx-edit",
                                                "delete" => "bx-trash",
                                                default => "bx-info-circle",
                                            };
                                        @endphp

                                        <span
                                            class="badge bg-{{ $iconColor }} rounded-circle p-2"
                                        >
                                            <i class="bx {{ $iconClass }}"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong class="text-capitalize">
                                            {{ $log->aksi }}
                                        </strong>
                                        oleh {{ $log->pelaku->name ?? "-" }}
                                        <br />
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($log->dilakukan_pada)->format("d/m/Y H:i:s") }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push("styles")
    <style>
        .timeline {
            position: relative;
        }
        .timeline-item {
            position: relative;
            padding-left: 10px;
        }
    </style>
@endpush

@push("scripts")
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle verify token button click (untuk penarikan tabungan)
            const verifyBtn = document.querySelector('.btn-verify');
            if (verifyBtn) {
                verifyBtn.addEventListener('click', function () {
                    const transaksiId = this.dataset.id;
                    const expectedToken = this.dataset.token;
                    const jumlah = this.dataset.jumlah;
                    showVerifyModal(transaksiId, expectedToken, jumlah);
                });
            }

            // Handle approve button click
            const approveBtn = document.querySelector('.btn-approve-detail');
            if (approveBtn) {
                approveBtn.addEventListener('click', function () {
                    const transaksiId = this.dataset.id;
                    const isMultiple = this.dataset.isMultiple === 'true';
                    const headTagihan = this.dataset.headTagihan;
                    approveTransaksi(transaksiId, isMultiple, headTagihan);
                });
            }

            // Handle reject button click
            const rejectBtn = document.querySelector('.btn-reject-detail');
            if (rejectBtn) {
                rejectBtn.addEventListener('click', function () {
                    const transaksiId = this.dataset.id;
                    const isMultiple = this.dataset.isMultiple === 'true';
                    const headTagihan = this.dataset.headTagihan;
                    rejectTransaksi(transaksiId, isMultiple, headTagihan);
                });
            }
        });

        function approveTransaksi(transaksiId, isMultiple, headTagihan) {
            const title = isMultiple
                ? 'Approve Pembayaran Multiple'
                : 'Approve Transaksi';

            Swal.fire({
                title: title,
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
                    processApproval(
                        transaksiId,
                        catatan,
                        isMultiple,
                        headTagihan,
                    );
                }
            });
        }

        function rejectTransaksi(transaksiId, isMultiple, headTagihan) {
            const title = isMultiple
                ? 'Reject Pembayaran Multiple'
                : 'Reject Transaksi';

            Swal.fire({
                title: title,
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
                    processRejection(
                        transaksiId,
                        result.value,
                        isMultiple,
                        headTagihan,
                    );
                }
            });
        }

        function processApproval(
            transaksiId,
            catatan,
            isMultiple,
            headTagihan,
        ) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            // Tentukan endpoint berdasarkan tipe (single vs multiple)
            let url, body;

            if (isMultiple) {
                // Multiple payment endpoint - gunakan head_tagihan untuk find pembayaran
                url = `{{ url("keuangan-transaksi/approve-multiple") }}`;
                body = {
                    head_tagihan: headTagihan, // UBAH: gunakan head_tagihan bukan pembayaran_id
                    catatan_verifikasi: catatan,
                };
            } else {
                // Single payment endpoint
                url = `{{ url("keuangan-transaksi/approve") }}/${transaksiId}`;
                body = {
                    catatan_verifikasi: catatan,
                };
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(body),
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

        function processRejection(
            transaksiId,
            catatan,
            isMultiple,
            headTagihan,
        ) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            // Tentukan endpoint berdasarkan tipe (single vs multiple)
            let url, body;

            if (isMultiple) {
                // Multiple payment endpoint - gunakan head_tagihan untuk find pembayaran
                url = `{{ url("keuangan-transaksi/reject-multiple") }}`;
                body = {
                    head_tagihan: headTagihan, // UBAH: gunakan head_tagihan bukan pembayaran_id
                    catatan_verifikasi: catatan,
                };
            } else {
                // Single payment endpoint
                url = `{{ url("keuangan-transaksi/reject") }}/${transaksiId}`;
                body = {
                    catatan_verifikasi: catatan,
                };
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(body),
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
    </script>
    <script>
        document
            .getElementById('btnBatalkan')
            .addEventListener('click', function (e) {
                e.preventDefault(); // Mencegah navigasi link

                // Ambil data transaksi yang dibutuhkan untuk ditampilkan
                const codePembayaran = '{{ $transaksi->code_pembayaran }}';
                const jumlahFormat =
                    'Rp {{ number_format($transaksi->jumlah, 0, ",", ".") }}';

                Swal.fire({
                    title: 'Verifikasi Pembatalan Transaksi',
                    html: `
                    <div class="text-start">
                        <div class="alert alert-info mb-3">
                            <strong><i class="bx bx-info-circle me-1"></i>Informasi:</strong><br>
                            <small class="d-block">Pembatalan Transaksi Ini !!! Harap Minta Token Kepada Atasan Anda</small>
                            <small class="d-block mt-1">No Transaksi : <strong>${codePembayaran}</strong></small>
                        </div>
                        <label for="token-input-batal" class="form-label">Token (6 Digit) <span class="text-danger">*</span></label>
                        <input type="text" id="token-input-batal" class="form-control form-control-lg text-center"
                               placeholder="000000" maxlength="6"
                               style="letter-spacing: 8px; font-family: monospace; font-size: 1.5rem;">
                        <small class="text-muted mt-2 d-block">Minta token pembatalan dari Atasan/Admin.</small>
                    </div>
                `,
                    icon: 'warning', // Gunakan 'warning' untuk aksi kritis
                    showCancelButton: true,
                    confirmButtonColor: '#f8ac59', // Merah untuk Batalkan
                    cancelButtonColor: '#6c757d',
                    confirmButtonText:
                        '<i class="bx bx-lock me-1"></i> Verifikasi Token',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    focusConfirm: false, // Fokus pada input
                    preConfirm: () => {
                        const token =
                            document.getElementById('token-input-batal').value;
                        if (!token || token.length !== 6) {
                            Swal.showValidationMessage('Token harus 6 digit');
                            return false;
                        }
                        // TIDAK ADA LOGIKA AJAX/FORM SUBMIT DI SINI
                        // Hanya mengembalikan nilai untuk tujuan tampilan (atau Anda dapat menghapusnya jika benar-benar hanya tampilan)
                        return token;
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika Anda ingin menampilkan pesan sukses setelah verifikasi token (tanpa aksi backend)
                        Swal.fire({
                            icon: 'info',
                            title: 'Token Diterima',
                            text: `Token: ${result.value} diterima. Harap hubungi Atasan untuk memproses pembatalan.`,
                            confirmButtonColor: '#0d6efd',
                        });
                    }
                });

                // Auto focus pada input token
                setTimeout(() => {
                    document.getElementById('token-input-batal').focus();
                }, 500);
            });

        // Show Verify Modal untuk Token Verification
        function showVerifyModal(transaksiId, expectedToken, jumlah) {
            Swal.fire({
                title: 'Verifikasi Token Penarikan',
                html: `
                    <div class="text-start">
                        <div class="alert alert-info mb-3">
                            <strong><i class="bx bx-info-circle me-1"></i>Informasi:</strong><br>
                            <small class="d-block">Masukkan 6 digit token untuk memverifikasi penarikan sebesar : </small>
                            <small class="d-block"><strong>Rp ${new Intl.NumberFormat('id-ID').format(jumlah)}</strong></small>
                        </div>
                        <label for="token-input" class="form-label">Token (6 Digit) <span class="text-danger">*</span></label>
                        <input type="text" id="token-input" class="form-control form-control-lg text-center"
                               placeholder="000000" maxlength="6"
                               style="letter-spacing: 8px; font-family: monospace; font-size: 1.5rem;">
                        <small class="text-muted mt-2 d-block">Token diberikan saat transaksi penarikan dibuat</small>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonColor: '#48bb78',
                denyButtonColor: '#f56565',
                cancelButtonColor: '#6c757d',
                confirmButtonText:
                    '<i class="bx bx-check-circle me-1"></i> Approve',
                denyButtonText: '<i class="bx bx-x-circle me-1"></i> Reject',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                preConfirm: () => {
                    const token = document.getElementById('token-input').value;
                    if (!token || token.length !== 6) {
                        Swal.showValidationMessage('Token harus 6 digit');
                        return false;
                    }
                    return { token: token, action: 'approve' };
                },
                preDeny: () => {
                    const token = document.getElementById('token-input').value;
                    if (!token || token.length !== 6) {
                        Swal.showValidationMessage('Token harus 6 digit');
                        return false;
                    }
                    return { token: token, action: 'reject' };
                },
            }).then((result) => {
                if (result.isConfirmed || result.isDenied) {
                    processTokenVerification(
                        transaksiId,
                        result.value.token,
                        result.value.action,
                    );
                }
            });

            // Auto focus on token input
            setTimeout(() => {
                document.getElementById('token-input').focus();
            }, 500);
        }

        // Process Token Verification
        function processTokenVerification(transaksiId, token, action) {
            Swal.fire({
                title: 'Memverifikasi...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            fetch('/tabungan/verify-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    transaksi_id: transaksiId,
                    token: token,
                    action: action,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: `
                            <div class="text-start">
                                <p>${data.message}</p>
                                <div class="alert alert-info mt-3">
                                    <strong>Detail:</strong><br>
                                    <small>Transaksi ID: ${data.data.transaksi_id}</small><br>
                                    <small>Code: ${data.data.code_pembayaran}</small><br>
                                    <small>Status: <span class="badge bg-${action === 'approve' ? 'success' : 'danger'}">${data.data.status_approval}</span></small><br>
                                    <small>Saldo Akhir: Rp ${new Intl.NumberFormat('id-ID').format(data.data.saldo_akhir)}</small>
                                </div>
                            </div>
                        `,
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat verifikasi token',
                        confirmButtonColor: '#f56565',
                    });
                    console.error('Error:', error);
                });
        }
    </script>
@endpush

