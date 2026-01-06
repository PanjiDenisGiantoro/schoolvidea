@extends("layouts.merchant")
@section("title", "Informasi Saldo")
@section("content")
    <div
        class="welcome-section d-flex justify-content-between align-items-center mb-4"
    >
        <h3>Informasi Saldo</h3>
        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#withdrawModal"
        >
            Tarik Saldo
        </button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-sm-12">
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
                                Saldo Aktif
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp
                                {{ number_format(auth("merchant")->user()->saldo_aktif ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-wallet-alt text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-up-arrow-alt text-success"></i>
                        Total saldo saat ini
                    </small>
                                        <small class="text-muted d-block mt-2">
                        <i class="bx bx-up-arrow-alt text-success"></i>
                        Saldo dapat ditarik kapan saja
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
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
                                Total Penarikan
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp
                                {{ number_format($totalWithdraw ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="fa-duotone fa-solid fa-rupiah-sign text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-down-arrow-alt text-success"></i>
                        Total saldo yang sudah ditarik
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6 col-sm-12">
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
                                Sedang Diproses
                            </p>
                            <h3 class="fw-bold text-warning mb-0 text-absolute">
                                Rp
                                {{ number_format($pendingToday ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-hourglass-2-fill text-warning"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-warning"></i>
                        <span>Total sedang diproses hari ini</span>
                    </small>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-warning"></i>
                        <span>
                            {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
                        </span>
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
                                DIBATALKAN
                            </p>
                            <h3 class="fw-bold text-danger mb-0 text-absolute">
                                Rp
                                {{ number_format($rejectedToday ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-danger bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-x-circle text-danger"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-down-arrow-alt text-danger"></i>
                        Total yang dibatalkan
                    </small>
                                        <small class="text-muted d-block mt-2">
                        <i class="bx bx-down-arrow-alt text-danger"></i>
                        <span>
                            {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
                        </span>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
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
                                Berhasil
                            </p>
                            <h3 class="fw-bold text-info mb-0 text-absolute">
                                Rp
                                {{ number_format($successToday ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-info bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="ri-checkbox-circle-line text-info"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-info"></i>
                        <span>Total berhasil hari ini</span>
                    </small>
                    <small class="text-muted d-block mt-2">
                        <i class="ri-arrow-right-double-fill text-info"></i>
                        <span>
                            {{ \Carbon\Carbon::now()->translatedFormat("d F Y") }}
                        </span>
                    </small>
                </div>
            </div>
        </div>


        {{-- Tabel --}}
        <div class="card rounded-3 border-0 shadow-sm">
            <div class="card-body">
                <div
                    class="d-flex justify-content-between mb-3 flex-wrap gap-2"
                >
                    <h5 class="fw-bold text-dark">Daftar Penarikan</h5>
                </div>
                <div class="table-responsive">
                    <table
                        id="merchantTable"
                        class="table table-striped table-hover table-bordered align-middle table-sm text-nowrap"
                    >
                        <thead class="table-primary text-center align-middle">
                            <tr>
                                <th class="text-center" style="width: 4%">#</th>
                                <th style="width: 8%">Kode Penarikan</th>
                                <th style="width: 8%">JML Penarikan</th>
                                <th style="width: 14%">Metode Penarikan</th>
                                <th style="width: 8%">Status Penarikan</th>
                                <th class="text-center" style="width: 10%">
                                    Waktu Permintaan
                                </th>
                                <th class="text-center" style="width: 6%">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Withdrawal -->

    <div
        class="modal fade"
        id="withdrawModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="withdrawModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between">
                    <h5 class="modal-title" id="withdrawModalLabel">
                        Tarik Saldo
                    </h5>
                </div>

                <form
                    action="{{ route("merchant.balance.reqWithdraw") }}"
                    method="POST"
                >
                    @csrf

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Jumlah Penarikan (Rp)</label>

                            <input
                                type="text"
                                class="form-control"
                                id="amount_display"
                                oninput="formatCurrencyInput(this)"
                                inputmode="numeric"
                                data-target="amount"
                                placeholder="10.000"
                                required
                            />

                            <input type="hidden" id="amount" name="amount" />

                            <small class="form-text text-muted">
                                Minimal penarikan Rp 10.000. Saldo tersedia: Rp
                                {{ number_format(auth("merchant")->user()->saldo_aktif ?? 0, 0, ",", ".") }}
                            </small>
                        </div>

                        {{-- <div class="form-group">
                            <label for="bank_account">Rekening Bank</label>

                            <select
                                class="form-control"
                                id="bank_account"
                                name="bank_account"
                                required
                            >
                                <option value="">Pilih Rekening</option>

                                @foreach ($bankAccounts ?? [] as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->bank_name }} -
                                        {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Batal
                        </button>

                        <button type="submit" class="btn btn-primary" onclick="return document.getElementById('amount').value !== ''">
                            Tarik Saldo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let merchantTable;

        document.addEventListener('DOMContentLoaded', function () {
            merchantTable = $('#merchantTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100],
                ],
                pageLength: 25,
                language: {
                    url: '{{ asset("assets/datatables/id.json") }}',
                },
                ajax: {
                    url: '{{ route("merchant.balance.datatable") }}',
                    type: 'GET',
                    data: function (d) {
                        d.unit_id = $('#unit_id').val();
                        d.status = $('#status').val();
                        d.tanggal = $('#tanggal').val();
                    },
                },
columns: [
    { data: 'no', className: 'text-center' },
    { data: 'kode_withdrawal' },
    { data: 'jml' },
    { data: 'metode' },
    {
        data: 'status',
        render: function (data) {
            return data; // render HTML badge
        },
        className: 'text-center',
    },
    { data: 'waktu_penarikan', className: 'text-center' },
    {
        data: 'action',
        className: 'text-center',
        orderable: false,
        searchable: false,
        render: function (data) {
            return data; // render button HTML
        }
    }
],

                order: [[1, 'desc']],
            });
        });
    </script>
    <script>
        $('#status, #tanggal, #unit_id').on('change', function () {
            merchantTable.ajax.reload();
        });
    </script>
    <script>
        function formatCurrencyInput(input) {
            let raw = input.value.replace(/\D/g, '');
            const targetId = input.dataset.target;
            const targetInput = document.getElementById(targetId);

            if(!targetInput) {
                console.error('Target input tidak ditemukan', targetId);
                return;
            }

            if (!raw) {
                input.value = '';
                targetInput.value = '';
                return;
            }

            input.value = new Intl.NumberFormat('id-ID').format(raw);
            targetInput.value = raw;
        }
    </script>

    @if (@session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                confirmButtonText: 'Oke'
            });
        </script>
    @endif
    @if (@session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ session('error') }}',
                confirmButtonText: 'Oke'
            });
        </script>
    @endif
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                html: `{!!
            implode('<br>', $errors->all())
        !!}`,
                confirmButtonText: 'Oke'
            });
        </script>
    @endif
@endpush
