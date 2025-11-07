@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Transaksi</h3>
                <p class="text-subtitle text-muted">Kelola transaksi keuangan</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transaksi</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Tambah Transaksi</h4>
            </div>
            <div class="card-body">
                <form id="transactionForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date">Tanggal</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="transaction_type">Jenis Transaksi</label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="">Pilih Jenis</option>
                                    @foreach($transactionTypes as $key => $type)
                                        <option value="{{ $key }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="payment_method">Metode Pembayaran</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Pilih Metode</option>
                                    @foreach($paymentMethods as $key => $method)
                                        <option value="{{ $key }}" {{ $key == 'Tunai' ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="account">Akun</label>
                                <select class="form-select" id="account" name="account" required>
                                    <option value="">Pilih Akun</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->kode_akun }} - {{ $account->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount">Jumlah (Rp)</label>
                                <input type="number" class="form-control text-end" id="amount" name="amount" placeholder="0" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="description">Keterangan</label>
                                <input type="text" class="form-control" id="description" name="description" placeholder="Masukkan keterangan" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
<section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Tambah Transaksi</h4>
            </div>
            <div class="card-body">
                <form id="transactionForm" action="{{ route('transaksi.store') }}" method="POST">
                    <div class="row">
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Daftar Transaksi</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Unit</th>
                                <th>Nomor Induk</th>
                                <th>Nama Lengkap</th>
                                <th>Total Transaksi</th>
                                <th>Jenis Transaksi</th>
                                <th>Metode Pembayaran</th>
                                <th>Waktu Transaksi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="transactionTableBody">
                            @forelse($transactions as $index => $transaction)
                            @php
                                $siswa = $transaction->siswa ?? null;
                                $unit = $siswa->kelas->unit ?? null;
                                $nomorInduk = $siswa->nis ?? $siswa->nisn ?? '-';
                                $total = $transaction->debit > 0 ? $transaction->debit : $transaction->kredit;
                                $jenisTransaksi = $transaction->jenis_transaksi ?? 'Pembayaran';
                                $metodePembayaran = $transaction->metode_pembayaran ?? 'Tunai';
                                $status = $transaction->status ?? 'Lunas';
                            @endphp
                            <tr id="transaction-{{ $transaction->id }}"> <td>{{ $transactions->firstItem() + $index }}</td>
                                <td>{{ $unit->nama_unit ?? '-' }}</td>
                                <td>{{ $nomorInduk }}</td>
                                <td>{{ $siswa->nama_lengkap ?? 'Non-Siswa' }}</td>
                                <td class="text-end">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                <td>{{ $jenisTransaksi }}</td>
                                <td>{{ $metodePembayaran }}</td>
                                <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ $status == 'Lunas' ? 'success' : 'warning' }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-transaction" data-id="{{ $transaction->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data ditemukan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper mt-3 d-flex justify-content-between align-items-center">
                    <div class="pagination-info">
                        Menampilkan {{ $transactions->firstItem() ?? 0 }} sampai {{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} data
                    </div>
                    <div>
                        {{ $transactions->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle form submission
        $('#transactionForm').on('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = {
                _token: '{{ csrf_token() }}',
                tanggal: $('#date').val(),
                akun_id: $('#account').val(),
                jenis_transaksi: $('#transaction_type').val(),
                metode_pembayaran: $('#payment_method').val(),
                keterangan: $('#description').val(),
                jumlah: $('#amount').val()
            };

            // Send AJAX request
            $.ajax({
                url: '{{ route("transaksi.store") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    // Reload the page to show the new transaction
                    window.location.reload();
                },
                error: function(xhr) {
                    // Show error message
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                    console.error(xhr.responseText);
                }
            });
        });

        // Handle delete transaction
        $('.delete-transaction').on('click', function() {
            if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
                const transactionId = $(this).data('id');

                $.ajax({
                    url: `/transaksi/${transactionId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Reload the page to update the transaction list
                        window.location.reload();
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan saat menghapus transaksi.');
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
</script>
@endpush

@endsection
