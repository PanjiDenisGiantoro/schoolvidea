@extends("layouts.app")
@section("title", "Detail Pembayaran Gaji")
@push("styles")
    <link rel="stylesheet" href="{{ asset("assets/css/detail.css") }}" />
    <link rel="stylesheet" href="{{ asset("assets/css/style.css") }}" />
@endpush
@php
    function terbilang($nilai)
    {
        $nilai = abs($nilai);
        $huruf = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

        if ($nilai < 12) {
            return " " . $huruf[$nilai];
        } elseif ($nilai < 20) {
            return terbilang($nilai - 10) . " belas";
        } elseif ($nilai < 100) {
            return terbilang(intval($nilai / 10)) . " puluh" . terbilang($nilai % 10);
        } elseif ($nilai < 200) {
            return " seratus" . terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            return terbilang(intval($nilai / 100)) . " ratus" . terbilang($nilai % 100);
        } elseif ($nilai < 2000) {
            return " seribu" . terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            return terbilang(intval($nilai / 1000)) . " ribu" . terbilang($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            return terbilang(intval($nilai / 1000000)) . " juta" . terbilang($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            return terbilang(intval($nilai / 1000000000)) . " miliar" . terbilang($nilai % 1000000000);
        }
        return "nilai terlalu besar";
    }
@endphp
@section("content")
    @include(
        "partials.page-title",
        [
            "title" => "Detail Pembayaran Gaji",
            "subTitle" => "Penggajian",
        ]
    )

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Profile Section -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Guru & Staff
                    </h2>
                    <div class="flex items-center jus mb-4">
                        <img
                            src="{{ $payment->officer->image ? asset($payment->officer->image) : "https://via.placeholder.com/100" }}"
                            alt="Profile Picture"
                            class="w-16 h-16 rounded-full mr-4"
                        />
                        <div >
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ strtoupper($payment->officer->name) }}
                            </h3>
                            <p class="text-gray-600">
                                {{ $payment->officer->user->email }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="d-flex justify-between">
                            <strong>NIP:</strong>
                            <p>
                                {{ $payment->officer->nip }}
                            </p>
                        </div>
                        <div class="d-flex justify-between">
                            <strong>Unit:</strong>
                            <p>
                                {{ $payment->officer->unit->nama_unit }}
                            </p>
                        </div>
                        <div class="d-flex justify-between">
                            <strong>No Telp:</strong>
                            <p>
                                {{ $payment->officer->no_hp ?? "N/A" }}
                            </p>
                        </div>
                        <div class="d-flex justify-between">
                            <strong>Alamat:</strong>
                            <p>
                                {{ $payment->officer->alamat ?? "N/A" }}
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Payment Details Section -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Informasi Pembayaran
                    </h2>
                    <div class="space-y-4">
                        @if ($payment->status == "paid")
                            <div class="flex justify-between">
                                <span class="text-gray-600">
                                    Kode Transaksi :
                                </span>
                                <span class="font-medium">
                                    {{ $transaction->code_pembayaran ?? "TXN123456" }}
                                </span>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Peneriaman Gaji :</span>
                            <span class="font-medium text-green-600">
                                Rp
                                {{ number_format($payment->net_payment ?? 99.99, 0, ",", ".") }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">
                                Metode Pembayaran :
                            </span>
                            <span class="font-medium">
                                {{ $transaction->metode === "NONTUNAI"  ? "NON TUNAI" : "NON TUNAI" }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tanggal :</span>
                            <span class="font-medium">
                                {{ $payment->updated_at ?? "2023-10-01" }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status :</span>
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium {{ $payment->status == "completed" ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800" }}"
                            >
                                {{ ucfirst($payment->status ?? "pending") }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Catatan :</span>
                            <span class="font-medium">
                                {{ $payment->notes ?? "-" }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Nominal Catatan :</span>
                            <span class="font-medium">
                                Rp
                                {{ number_format($payment->salary_note ,0,',','.') ?? "-" }}
                            </span>
                        </div>
                    </div>
                    @if ($payment->status == "pending")
                        <button
                            id="btnSinkron"
                            onclick="onClickBayar()"
                            class="mt-6 w-full bg-success text-white py-2 px-4 rounded-lg hover:bg-green-700 transition"
                        >
                            Bayar
                        </button>
                    @endif
                </div>
            </div>
            <!-- Optional: Additional Actions or Notes -->
            <div class="mt-8 bg-white shadow-lg rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-1">
                    Detail Pembayaran
                </h2>
                <table width="100%" style="border-collapse: collapse">
                    <tr>
                        <td width="50%" valign="top" style="padding-right: 8px">
                            <table
                                width="100%"
                                class="earnings-deductions text-start"
                            >
                                <thead>
                                    <tr>
                                        <th
                                            colspan="2"
                                            style="padding-top: 10px"
                                            class="text-start"
                                        >
                                            <i>Penerimaan</i>
                                        </th>
                                        <th><i>Jumlah (IDR)</i></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {{-- Gaji pokok --}}
                                    <tr>
                                        <td colspan="2" class="text-start">
                                            Gaji Pokok (Rp
                                            {{ number_format($payment->details["salary"]) }}
                                            x
                                            {{ (int) ($payment->teaching_hour_week != 0 ? $payment->teaching_hour_week : $payment->teaching_hour_month) }})
                                        </td>
                                        <td
                                            class="amount"
                                            style="font-weight: 500"
                                        >
                                            Rp
                                            {{ number_format($payment->details["salary"] * ($payment->teaching_hour_week != 0 ? $payment->teaching_hour_week : $payment->teaching_hour_month), 0, ",", ".") }}
                                        </td>
                                    </tr>

                                    {{-- Komponen / Tunjangan --}}
                                    @foreach ($payment->details["components"] as $component)
                                        <tr>
                                            <td colspan="2" class="text-start">
                                                {{ $component["name"] }}
                                            </td>
                                            <td
                                                class="amount"
                                                style="font-weight: 500"
                                            >
                                                Rp
                                                {{ number_format($component["pivot"]["value"], 0, ",", ".") }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="2" class="text-start">
                                            Kehadiran Staff (Rp
                                            {{ number_format($payment->details["staff_allowance"] ?? 0, 0, ",", ".") }}
                                            x {{ $payment->presence }} )
                                        </td>
                                        <td
                                            class="amount"
                                            style="font-weight: 500"
                                        >
                                            Rp
                                            {{ number_format($payment->details["staff_allowance"] * $payment->presence, 0, ",", ".") }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-start">
                                            Transport (Rp
                                            {{ number_format($payment->details["transport_allowance"] ?? 0, 0, ",", ".") }}
                                            x {{ $payment->presence_count }} )
                                        </td>
                                        <td
                                            class="amount"
                                            style="font-weight: 500"
                                        >
                                            Rp
                                            {{ number_format($payment->details["transport_allowance"] * $payment->presence_count, 0, ",", ".") }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-start">
                                            Makan (Rp
                                            {{ number_format($payment->details["meal_allowance"] ?? 0, 0, ",", ".") }}
                                            x {{ $payment->presence_count }} )
                                        </td>
                                        <td
                                            class="amount"
                                            style="font-weight: 500"
                                        >
                                            Rp
                                            {{ number_format($payment->details["meal_allowance"] * $payment->presence_count, 0, ",", ".") }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-start">
                                            Lainnya (Rp
                                            {{ number_format($payment->details["other_allowance"] ?? 0, 0, ",", ".") }}
                                            x {{ $payment->presence_count }} )
                                        </td>
                                        <td
                                            class="amount"
                                            style="font-weight: 500"
                                        >
                                            Rp
                                            {{ number_format($payment->details["other_allowance"] * $payment->presence_count, 0, ",", ".") }}
                                        </td>
                                    </tr>

                                    <tr
                                        style="
                                            font-weight: bold;
                                            background-color: #e6ffed;
                                        "
                                    >
                                        <td colspan="2" class="text-start">
                                            TOTAL 
                                        </td>
                                        <td class="amount">
                                            Rp
                                            {{ number_format($payment->total_earnings, 0, ",", ".") }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>

                        {{-- Potongan --}}
                        <td width="50%" valign="top" style="padding-left: 8px">
                            <table width="100%" class="earnings-deductions">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="text-start">
                                            <i>Potongan</i>
                                        </th>
                                        <th><i>Jumlah (IDR)</i></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($payment->details["deductions"] as $deduction)
                                        <tr>
                                            <td colspan="2" class="text-start">
                                                {{ $deduction["name"] }}

                                                @if ($deduction["type"] === "persen")
                                                        ({{ $deduction["pivot"]["value"] }}%)
                                                @endif
                                            </td>

                                            <td
                                                class="amount"
                                                style="font-weight: 500"
                                            >
                                                @if ($deduction["type"] === "nominal")
                                                    Rp
                                                    {{ number_format($deduction["pivot"]["value"], 0, ",", ".") }}
                                                @else
                                                    Rp
                                                    {{ number_format(($deduction["pivot"]["value"] / 100) * $payment->total_earnings, 0, ",", ".") }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr
                                        style="
                                            font-weight: bold;
                                            background-color: #fff0f0;
                                        "
                                    >
                                        <td colspan="2" class="text-start">
                                            TOTAL POTONGAN
                                        </td>
                                        <td class="amount">
                                            Rp
                                            {{ number_format($payment->total_deductions, 0, ",", ".") }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
                <table
                width="100%"
                style="
                    border-collapse: collapse;
                    background: #e6ffed;
                    border: 1px solid #28a745;
                    padding: 10px;
                    margin-top: 6px;
                    border-radius: 4px;
                    font-size: 10pt;

                "
            >
                <tr>
                    <!-- KIRI: TOTAL GAJI BERSIH -->
                    <td
                        width="45%"
                        valign="top"
                        style="padding: 6px; color: #28a745"
                    >
                        <div>
                            <strong style="display: block; margin-bottom: 4px">
                                TOTAL PENERIMAAN GAJI
                            </strong>
                        </div>
                        <div>
                            <span
                                style="
                                    font-size: 12pt;
                                    font-weight: 700;
                                    color: #28a745;
                                "
                            >
                                Rp
                                {{ number_format($payment->net_payment ?? $net ?? 0, 0, ",", ".") }}
                            </span>
                        </div>
                    </td>

                    <!-- KANAN: TERBILANG -->
                    <td
                        width="55%"
                        valign="top"
                        style="padding: 6px; color: #28a745"
                    >
                        <div>
                            <strong style="display: block; margin-bottom: 4px">
                                TERBILANG
                            </strong>
                        </div>
                        <div>
                            <span style="font-size: 12pt; font-style: italic">
                                ({{ ucwords(terbilang($payment->net_payment ?? $net)) }}
                                Rupiah)
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
            </div>
            {{--<div class="mt-8 bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Notes</h3>
                <p class="text-gray-600">
                    {{ $note ?? "No additional notes for this payment." }}
                    - Rp.
                    {{ $salarynote ?? 0 }}
                </p>
            </div>--}}
        </div>
    </div>
@endsection

@push("script")
    @if ($payment)
        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    searching: false,
                    scrollX: true,
                    language: {
                        url: '{{ asset("assets/datatables/id.json") }}',
                    },
                });
            });
        </script>
    @endif
    @endpush
