@extends("layouts.app")
@section("title", "Detail Penarikan Dana")

@push("styles")
    <link rel="stylesheet" href="{{ asset("assets/css/detail.css") }}" />
    <link rel="stylesheet" href="{{ asset("assets/css/style.css") }}" />
@endpush

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => "Detail Penarikan Dana",
            "subTitle" => "Penarikan Dana Merchant",
        ]
    )

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- PROFILE MERCHANT --}}
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Merchant
                    </h2>

                    <div class="flex items-center jus mb-4 gap-3">
                        <img
                            src="{{ $withdrawal->merchant->image ? asset("storage/" . $withdrawal->merchant->image) : asset("images/default.png") }}"
                            alt="Foto Merchant"
                            class="img-fluid rounded-circle mb-3"
                            style="
                                width: 100px;
                                height: 100px;
                                object-fit: cover;
                            "
                        />
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ strtoupper($withdrawal->merchant->nama_merchant) }}
                            </h3>
                            <p class="text-gray-600">
                                {{ $withdrawal->merchant->kode_merchant }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="d-flex justify-between">
                            <strong>Pemilik:</strong>
                            <p>{{ $withdrawal->merchant->pemilik ?? "-" }}</p>
                        </div>
                        <div class="d-flex justify-between">
                            <strong>No Telp:</strong>
                            <p>{{ $withdrawal->merchant->no_hp ?? "-" }}</p>
                        </div>

                        <div class="d-flex justify-between">
                            <strong>Unit:</strong>
                            <p>
                                {{ $withdrawal->merchant->unit->nama_unit ?? "-" }}
                            </p>
                        </div>
                        <div class="d-flex justify-between">
                            <strong>Jenis:</strong>
                            <p>{{ $withdrawal->merchant->jenis ?? "-" }}</p>
                        </div>
                        <div class="d-flex justify-between align-items-center">
                            <strong>Status:</strong>

                            @if (($withdrawal->merchant->status ?? 0) == 1)
                                <span
                                    class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    Aktif
                                </span>
                            @else
                                <span
                                    class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    Tidak Aktif
                                </span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-3">
                            @if ($withdrawal->status === "pending")
                                {{-- Approve --}}
                                <form
                                    action="{{ route("merchant_withdrawal.approve") }}"
                                    method="POST"
                                    class="form-approve"
                                >
                                    @csrf
                                    @method("PUT")
                                    <input
                                        type="hidden"
                                        name="withdrawal_id"
                                        value="{{ $withdrawal->id }}"
                                    />
                                    <button
                                        type="submit"
                                        class="btn btn-success rounded-pill"
                                    >
                                        Approve
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <form
                                    action="{{ route("merchant_withdrawal.reject", $withdrawal->id) }}"
                                    method="POST"
                                    class="form-reject"
                                >
                                    @csrf
                                    @method("PUT")
                                    <button
                                        type="submit"
                                        class="btn btn-danger rounded-pill"
                                    >
                                        Reject
                                    </button>
                                </form>
                            @endif

                            {{-- Kembali --}}
                            <a
                                href="{{ route("merchant_withdrawal.index") }}"
                                class="btn btn-secondary rounded-pill"
                            >
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>

                {{-- DETAIL PENARIKAN --}}
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Informasi Penarikan
                    </h2>

                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Reference ID :</span>
                            <span class="font-medium">
                                {{ $withdrawal->reference_id }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">
                                Jumlah Penarikan :
                            </span>
                            <span class="font-medium text-green-600">
                                Rp
                                {{ number_format($withdrawal->amount, 0, ",", ".") }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Bank :</span>
                            <span class="font-medium">
                                {{ $withdrawal->bank_name }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">No Rekening :</span>
                            <span class="font-medium">
                                {{ $withdrawal->account_number }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Atas Nama :</span>
                            <span class="font-medium">
                                {{ $withdrawal->account_name }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">
                                Tanggal Pengajuan :
                            </span>
                            <span class="font-medium">
                                {{ $withdrawal->requested_at->format("d-m-Y H:i") ?? "-" }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">
                                Tanggal Diproses :
                            </span>
                            <span class="font-medium">
                                {{ $withdrawal->processed_at?->format("d-m-Y H:i") ?? "-" }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Diproses Oleh :</span>
                            <span class="font-medium">
                                {{ $withdrawal->processedBy->name ?? "-" }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status :</span>
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium {{
                                    $withdrawal->status == "approved"
                                        ? "bg-green-100 text-green-800"
                                        : ($withdrawal->status == "rejected"
                                            ? "bg-red-100 text-red-800"
                                            : "bg-yellow-100 text-yellow-800")
                                }}"
                            >
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </div>
                    </div>

                    @if ($withdrawal->status == "approved")
                        <a
                            href="{{ route("merchant_withdrawal.print", $withdrawal->id) }}"
                            target="_blank"
                            class="mt-6 w-full bg-success text-white py-2 px-4 rounded-lg d-block text-center"
                        >
                            Print Struk (PDF)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // APPROVE
            document.querySelectorAll('.form-approve').forEach((form) => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Approve Penarikan?',
                        text: 'Dana akan diproses ke merchant',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Approve',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#16a34a',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // REJECT
            document.querySelectorAll('.form-reject').forEach((form) => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Reject Penarikan?',
                        text: 'Penarikan akan dibatalkan',
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Reject',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc2626',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    @if (session("success"))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session("success") }}',
                confirmButtonText: 'Oke',
                confirmButtonColor: '#1bb394',
            });
        </script>
    @endif

    @if (session("error"))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ session("error") }}',
                confirmButtonText: 'Oke',
                confirmButtonColor: '#5d7186',
            });
        </script>
    @endif
@endpush
