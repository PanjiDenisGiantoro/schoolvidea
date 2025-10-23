@extends('layouts.app')

@section('title', 'Edit Potongan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Edit Potongan',
        'subTitle' => 'Potongan'
    ])

    <div class="card">
        <div class="card-body">
            <form id="potonganForm" method="POST" action="{{ route('potongan.update', $potongan->id) }}">
                @csrf
                @method('PUT')

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="unit_id" class="form-label">Unit</label>
                <select name="unit_id" id="unit_id" class="form-select">
                    <option value="">-- Pilih Unit --</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ $unit->id == $potongan->unit_id ? 'selected' : '' }}>
                            {{ $unit->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($kelas as $kelas)
                        <option value="{{ $kelas->id }}" {{ $kelas->id == $potongan->kelas_id ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
