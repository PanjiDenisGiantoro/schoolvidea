@extends("layouts.app")

@section("title", "PIN Pembatalan")

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => "PIN Pembatalan",
            "subTitle" => "Pengaturan PIN Pembatalan Pembayaran",
        ]
    )

    <div class="card">
        <div class="card-body">
            {{-- ALERT --}}
            @if (session("success"))
                <div class="alert alert-success">{{ session("success") }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- TABLE --}}
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Unit</th>
                        <th>Status PIN</th>
                        <th>Terakhir Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $i => $unit)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $unit->nama_unit }}</td>
                            <td>
                                @if ($unit->pinPembatalan)
                                    <span class="badge bg-success">
                                        Sudah Diset
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Belum Diset
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ optional($unit->pinPembatalan)->updated_at?->format("d M Y H:i") ?? "-" }}
                            </td>
                            <td>
                                <button
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#pinModal"
                                    data-unit-id="{{ $unit->id }}"
                                    data-has-pin="{{ $unit->pinPembatalan ? 1 : 0 }}"
                                >
                                    Set / Ubah
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="pinModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route("pin_pembatalan.store") }}">
                @csrf

                <input type="hidden" name="unit_id" id="unit_id" />

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Set PIN Pembatalan</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>
                    </div>

                    <div class="modal-body">
                        {{-- PIN LAMA --}}
                        <div class="mb-3 d-none" id="pin-lama-wrapper">
                            <label class="form-label">PIN Lama</label>
                            <input
                                type="password"
                                name="pin_lama"
                                class="form-control"
                                inputmode="numeric"
                                maxlength="6"
                            />
                        </div>

                        {{-- PIN BARU --}}
                        <div class="mb-3">
                            <label class="form-label">PIN Baru (6 Digit)</label>
                            <input
                                type="password"
                                name="pin"
                                class="form-control"
                                inputmode="numeric"
                                maxlength="6"
                                required
                            />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Konfirmasi PIN Baru
                            </label>
                            <input
                                type="password"
                                name="pin_confirmation"
                                class="form-control"
                                inputmode="numeric"
                                maxlength="6"
                                required
                            />
                        </div>

                        <hr />

                        <div class="mb-3">
                            <label class="form-label">Password Admin</label>
                            <input
                                type="password"
                                name="password_admin"
                                class="form-control"
                                required
                            />
                        </div>

                        <div class="alert alert-warning">
                            PIN digunakan untuk pembatalan pembayaran. Jaga
                            kerahasiaan.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">Simpan</button>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        document
            .getElementById('pinModal')
            .addEventListener('show.bs.modal', function (e) {
                const button = e.relatedTarget;
                const unitId = button.getAttribute('data-unit-id');
                const hasPin = button.getAttribute('data-has-pin') === '1';

                document.getElementById('unit_id').value = unitId;

                const pinLama = document.getElementById('pin-lama-wrapper');
                hasPin
                    ? pinLama.classList.remove('d-none')
                    : pinLama.classList.add('d-none');
            });
    </script>
@endsection
