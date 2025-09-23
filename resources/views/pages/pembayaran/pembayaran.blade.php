@extends('layouts.app')

@section('title', 'Pembayaran Tagihan')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">PEMBAYARAN TAGIHAN SISWA</h3>

        {{-- Alert success / danger --}}
        @if(session('success'))
            <div class="alert alert-success rounded-3 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('danger'))
            <div class="alert alert-danger rounded-3 shadow-sm">
                {{ session('danger') }}
            </div>
        @endif

        <div class="card shadow-sm rounded-3 border-0">
            <div class="card-body">
                <form action="" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="siswa_id" class="form-label">Siswa</label>
                        <select name="siswa_id" id="siswa_id" class="form-control rounded-pill">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswaList as $siswa)
                                <option value="{{ $siswa->id }}">
                                    {{ $siswa->nisn }} - {{ $siswa->user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('siswa_id')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tagihan_id" class="form-label">Tagihan</label>
                        <select name="tagihan_id" id="tagihan_id" class="form-control rounded-pill">
                            <option value="">-- Pilih Tagihan --</option>
                            @foreach($tagihanList as $tagihan)
                                <option value="{{ $tagihan->id }}">
                                    {{ $tagihan->nama_tagihan ?? 'Tagihan ' . $tagihan->id }}
                                    (Rp {{ number_format($tagihan->items->sum('nominal') * ($tagihan->periode ?? 1), 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('tagihan_id')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_bayar" class="form-label">Jumlah Bayar</label>
                        <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control rounded-pill" value="{{ old('jumlah_bayar') }}" min="1" required>
                        @error('jumlah_bayar')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill shadow-sm">Bayar</button>
                </form>
            </div>
        </div>
    </div>
@endsection
