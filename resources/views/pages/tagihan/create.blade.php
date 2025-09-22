@extends('layouts.app')

@section('title', 'Tambah Tagihan')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-3">
            <h3>TAMBAH TAGIHAN</h3>
            <a href="{{ route('tagihan.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('tagihan.store') }}" method="POST">
                    @csrf
                    {{-- Unit Pendidikan --}}
                    <div class="mb-3">
                        <label class="form-label">Unit Pendidikan</label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Daftar Kelas --}}
                    <div class="mb-3">
                        <label class="form-label">Daftar Kelas</label><br>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pilihKelas" onchange="toggleKelas()">
                            <label class="form-check-label" for="pilihKelas">Memilih</label>
                        </div>
                        <select name="kelas_id" id="kelasSelect" class="form-control mt-2 d-none">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jumlah Periode --}}
                    <div class="mb-3">
                        <label class="form-label">Jumlah Periode Tagihan <span class="text-danger">*</span></label>
                        <input type="number" name="periode" class="form-control" min="1" required>
                    </div>

                    {{-- Bulan & Tahun Mulai --}}
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <label class="form-label">Bulan Mulai</label>
                            <select name="bulan_mulai" class="form-control" required>
                                <option value="">-- Pilih Bulan --</option>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bulan)
                                    <option value="{{ $i+1 }}">{{ $bulan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Mulai</label>
                            <select name="tahun_mulai" class="form-control" required>
                                @for($y = date('Y'); $y <= date('Y')+5; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Item Tagihan --}}
                    <div id="itemTagihan">
                        <div class="mb-3">
                            <label class="form-label">Item Tagihan 1 <span class="text-danger">*</span></label>
                            <input type="text" name="items[0][nama]" class="form-control mb-2" placeholder="Nama Item">
                            <input type="number" name="items[0][jumlah]" class="form-control" placeholder="Nominal">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="tambahItem()">+ Tambah Item</button>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('tagihan.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let itemCount = 1;

        function tambahItem() {
            let container = document.getElementById('itemTagihan');
            let html = `
        <div class="mb-3">
            <label class="form-label">Item Tagihan ${itemCount+1}</label>
            <input type="text" name="items[${itemCount}][nama]" class="form-control mb-2" placeholder="Nama Item">
            <input type="number" name="items[${itemCount}][jumlah]" class="form-control" placeholder="Nominal">
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            itemCount++;
        }

        function toggleKelas() {
            document.getElementById('kelasSelect').classList.toggle('d-none');
        }
    </script>
@endsection
