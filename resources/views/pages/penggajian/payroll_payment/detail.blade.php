@extends('layouts.app')
@section('title', 'Detail Pembayaran Gaji')

@section('content')
    @include('partials.page-title', [
        'title' => 'Detail Pembayaran Gaji',
        'subTitle' => 'Penggajian'
    ])

    <div class="row g-4">
        {{-- Detail Siswa --}}
        <div class="col-md-4">
            <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                <div class="card-header bg-gradient-info text-white text-center py-3">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-user-circle me-1 text-white"></i>Informasi Guru
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $payment->officer->image ? asset($payment->officer->image) : asset('assets/images/videa.png') }}"
                                 alt="Foto Siswa"
                                 class="img-fluid rounded-circle shadow border border-3 border-info"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 bg-info rounded-circle p-2 border border-2 border-white">
                                <i class="bx bx-wallet text-white"></i>
                            </span>
                        </div>
                    </div>

                    <div class="student-info">
                        <div class="info-item mb-2 p-2 bg-light rounded">
                            <small class="text-muted d-block mb-1">Nama Lengkap</small>
                            <strong class="text-dark">{{ $payment->officer->name }}</strong>
                        </div>
                        <div class="info-item mb-2 p-2">
                            <small class="text-muted d-block mb-1">Nomor Induk (NIP)</small>
                            <strong class="text-dark">{{ $payment->officer->nip }}</strong>
                        </div>
                        <div class="info-item mb-3 p-2 bg-light rounded">
                            <small class="text-muted d-block mb-1">Unit</small>
                            <strong class="text-dark">{{ $payment->unit->nama_unit }}</strong>
                        </div>
                    </div>

                    {{-- <div class="saldo-info">

                        <div class="alert alert-success border-0 shadow-sm mb-0">
                            <small class="text-muted d-block mb-1">Saldo Saat Ini</small>
                            <div class="fs-5 fw-bold">Rp {{ number_format($saldo_akhir, 0, ',', '.') }}</div>
                        </div>
                    </div> --}}
                    {{-- <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ url('tabungan/') }}" class="btn btn-secondary"><i class='bx  bx-chevron-left'></i> </a>
                        <a href="{{ route('tabungan.create', ['siswa_id' => $payment->id, 'unit_id' => $siswa->unit_id, 'kelas_id' => $siswa->kelas_id]) }}" class="btn btn-success"><i class="bx bx-plus-circle me-1"></i> Setor</a>
                        <a href="{{ route('tabungan.tarik', ['siswa_id' => $siswa->id, 'unit_id' => $siswa->unit_id, 'kelas_id' => $siswa->kelas_id]) }}" class="btn btn-danger"><i class="bx bx-minus-circle me-1"></i>Tarik</a>
                        <a href="{{ url('keuangan-transaksi?siswa_id=' . $siswa->nisn) }}" class="btn btn-info" title="Lihat detail keuangan siswa"><i class="bx bx-qr" style="font-size: 20px"></i></a>
                        <a href="{{ route('tabungan.print_mutasi', $siswa->id) }}" class="btn btn-warning" target="_blank"><i class="bx bx-printer" style="font-size: 20px"></i></a>
                    </div> --}}
                </div>
            </div>
        </div>

        {{-- p Transaksi --}}
        <div class="col-md-8">
            <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-list-ul me-2 text-white"></i>Riwayat Transaksi Penggajian
                    </h5>
                </div>
                <div class="card-body p-4">
                    {{-- Search & Filter Form --}}
                    <form method="GET" action="{{ route('tabungan.show', $payment) }}" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="jenis_transaksi" class="form-select form-select-sm">
                                    <option value="">Semua Jenis</option>
                                    <option value="setoran_tabungan" {{ request('jenis_transaksi') == 'setoran_tabungan' ? 'selected' : '' }}>
                                        <i class="bx bx-plus-circle"></i>Setoran
                                    </option>
                                    <option value="penarikan_tabungan" {{ request('jenis_transaksi') == 'penarikan_tabungan' ? 'selected' : '' }}>
                                        <i class="bx bx-minus-circle"></i>Penarikan
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="dari_tanggal" class="form-control "
                                       placeholder="Dari Tanggal" value="{{ request('dari_tanggal') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="sampai_tanggal" class="form-control"
                                       placeholder="Sampai Tanggal" value="{{ request('sampai_tanggal') }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="bx bx-search me-1"></i>Cari
                                </button>
                                <a href="{{ route('tabungan.show', $payment->id) }}" class="btn btn-sm btn-secondary w-100 mt-1">
                                    <i class="bx bx-x me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover align-middle mb-0">
                            <thead>
                            <tr class="border-bottom border-2 justify-middle text-center text-nowrap">
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-calendar me-1"></i>Waktu
                                </th>
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-transfer me-1"></i>Jenis
                                </th>
                            <th style="width: 200px; text-align: center">
                                Presensi
                                <div class="custom-presensi-header">
                                    <span class="text-muted">JM</span>
                                    <span class="text-muted">JB</span>
                                    <span class="text-muted">Hadir</span>
                                    <span class="text-muted">T.Hadir</span>
                                    <span class="text-muted">H.Staff</span>
                                </div>
                            </th>
{{--                                <th class="text-muted fw-semibold text-nowrap">--}}
{{--                                    <i class="bx bx-transfer me-1"></i>Debit/Kredit--}}
{{--                                </th>--}}
                                <th class="text-muted fw-semibold text-nowrap">
                                    <i class="bx bx-note me-1"></i>Keterangan
                                </th>
                                <th class="text-muted fw-semibold text-center text-nowrap">
                                    <i class="bx bx-check-shield me-1"></i>Penerimaan
                                </th>
                                <th class="text-muted fw-semibold text-center text-nowrap">
                                    <i class="bx bx-check-shield me-1"></i>Total Potongan
                                </th>
                                <th class="text-muted fw-semibold text-center text-nowrap">
                                    <i class="bx bx-check-shield me-1"></i>Penerimaan Bersih
                                </th>
                                <th class="text-muted fw-semibold text-center">
                                    <i class="bx bx-cog me-1"></i>Aksi
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-gradient-info {
            background: linear-gradient(135deg, #1bb394  0%, #1bb394 100%);
        }
        .student-info .info-item {
            transition: all 0.2s ease;
        }
        .student-info .info-item:hover {
            background-color: #e6f7ff !important;
            transform: translateX(5px);
        }
        .transaction-row {
            transition: all 0.2s ease;
        }
        .transaction-row:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
        }
        .saldo-info .alert {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .table thead th {
            border-bottom: 2px solid #1bb394  !important;
            padding: 1rem;
            background-color: #f8fafc;
        }
        .badge {
            font-weight: 600;
            font-size: 0.85rem;
        }
        .btn-verify {
            transition: all 0.2s ease;
        }
        .btn-verify:hover {
            transform: scale(1.05);
        }
    </style>
@endpush

@push('script')
    @if ($payment)
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
            });
        </script>
    @endif
@endpush