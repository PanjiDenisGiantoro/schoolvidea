@extends('layouts.app')

@section('title', 'Detail Potongan')

@section('content')
    <div class="container-fluid">
        <!-- Header with Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Detail Potongan</h3>
            <a href="{{ route('potongan.index') }}" class="btn btn-secondary rounded-2">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Potongan Detail Section -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card rounded-3 border-0 shadow-sm h-100">
                    <div class="card-header bg-light border-0 rounded-top-3">
                        <h5 class="mb-0">Informasi Potongan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Unit</label>
                            <p class="mb-0 fw-bold">{{ $potongan->unit->nama_unit }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Kelas</label>
                            <p class="mb-0 fw-bold">{{ $potongan->kelas->nama_kelas }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Kategori Tagihan</label>
                            <p class="mb-0 fw-bold">{{ $potongan->kategoriTagihan->nama_kategori }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Tipe Potongan</label>
                            <p class="mb-0">
                                <span class="badge bg-light text-dark">
                                    {{ ucfirst($potongan->tipe_potongan) }}
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Nilai Potongan</label>
                            <p class="mb-0 fw-bold fs-5">
                                @if (strtolower($potongan->tipe_potongan) == 'persentase')
                                    <span class="text-success">{{ $potongan->nilai }}%</span>
                                @else
                                    <span class="text-success">Rp {{ number_format($potongan->nilai, 0, ',', '.') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small">Keterangan</label>
                            <p class="mb-0">{{ $potongan->keterangan ?? '-' }}</p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-md-8 mb-4">
                <!-- Potongan Siswa Section -->
                <div class="card rounded-3 border-0 shadow-sm">
                    <div class="card-header bg-light border-0 rounded-top-3">
                        <h5 class="mb-0">Daftar Potongan Tagihan ({{ $potongan->potonganSiswa->count() }} siswa)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="12%">Bulan</th>
                                        <th>Kategori</th>
                                        <th width="18%">Biaya Tagihan</th>
                                        <th width="18%">Nominal Potongan</th>
                                        <th width="18%">Sisa Tagihan</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($potongan->potonganSiswa as $potonganSiswa)
                                        @php
                                            // Ambil bulan dari tagihan_siswa
                                            $tagihanSiswa = $potonganSiswa->tagihanSiswa;
                                            $bulanKe = $tagihanSiswa->bulan_ke;
                                            $bulanMulai = (int) $potonganSiswa->tagihan->bulan_mulai;
                                            $tahunMulai = (int) $potonganSiswa->tagihan->tahun_mulai;

                                            // Hitung bulan dan tahun berdasarkan bulan_ke
                                            $date = \Carbon\Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($bulanKe - 1);
                                            $bulanTahun = $date->translatedFormat('F Y'); // e.g., "November 2025"

                                            // Ambil nominal tagihan dari item kategori yang sesuai
                                            $nominalTagihan = $potonganSiswa->tagihan->items()
                                                ->where('kategori_id', $potongan->kategori_tagihan_id)
                                                ->sum('nominal');

                                            // Hitung sisa tagihan setelah potongan
                                            $sisaTagihan = $nominalTagihan - $potonganSiswa->nominal;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $bulanTahun }}
                                                </span>
                                            </td>
                                            <td>{{ $potongan->kategoriTagihan->nama_kategori }}</td>
                                            <td><strong>Rp {{ number_format($nominalTagihan, 0, ',', '.') }}</strong></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    Rp {{ number_format($potonganSiswa->nominal, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>
                                                    @if ($sisaTagihan > 0)
                                                        <span class="text-success">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-muted">Rp 0</span>
                                                    @endif
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-info rounded-2"
                                                        title="Lihat Detail" onclick="alert('Detail untuk siswa ID: {{ $potonganSiswa->tagihan_siswa_id }}')">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger rounded-2"
                                                        title="Hapus" onclick="deletePotonganSiswa({{ $potonganSiswa->id }})">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <p class="text-muted mb-0">Belum ada potongan untuk siswa</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deletePotonganSiswa(id) {
            if (confirm('Apakah Anda yakin ingin menghapus potongan ini?')) {
                // Implement delete logic
                alert('Delete potongan siswa dengan ID: ' + id);
            }
        }
    </script>
@endsection
