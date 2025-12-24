@extends("layouts.merchant")

@section("title", "Produk")

@section("content")
    <div
        class="welcome-section d-flex justify-content-between align-items-center mb-4"
    >
        <h1>Produk</h1>
        <button type="" class="btn btn-primary">Tambah Produk</button>
    </div>
    {{-- Tabel --}}
    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="fw-bold text-dark">Daftar Merchant</h5>
            </div>
            <div class="table-responsive">
                <table
                    id="merchantTable"
                    class="table table-striped table-hover table-bordered align-middle table-sm text-nowrap"
                >
                    <thead class="table-primary text-center align-middle">
                        <tr>
                            <th class="text-center" style="width: 4%">#</th>
                            <th style="width: 8%">Nama Produk</th>
                            <th style="width: 8%">Kategori Produk</th>
                            <th style="width: 14%">Satuan Produk</th>
                            <th style="width: 8%">Jumlah Produk</th>
                            <th style="width: 14%">Harga Jual</th>
                            <th class="text-center" style="width: 10%">
                                Saldo Aktif
                            </th>
                            <th class="text-center" style="width: 10%">
                                Waktu Registrasi
                            </th>
                            <th class="text-center" style="width: 6%">
                                Status
                            </th>
                            <th class="text-center" style="width: 6%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">Ini halaman Produkl</div>
@endsection
