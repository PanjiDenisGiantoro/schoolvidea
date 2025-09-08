@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row g-5">

                <div class="col-lg-12">
                    <h5 class="card-title mb-4">
                        Officer
                    </h5>
                </div>
                <div class="col-lg-12">
                    <button class="btn btn-primary">Tambah Data</button>
                </div>
                <div class="col-lg-12">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIK</th>
                                <th>Tempat Lahir</th>
                                <th>Tanggal Lahir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('partials.vendor-scripts')
    <!-- Gridjs Plugin js -->
    <script src="{{ asset('assets/vendor/gridjs/gridjs.umd.js') }}"></script>

    <!-- Gridjs Demo js -->
    <script src="{{ asset('assets/js/components/table-gridjs.js') }}"></script>
@endsection
