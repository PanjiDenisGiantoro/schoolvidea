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

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Slip Gaji - {{ $payment->officer->name }}</title>
        <style>
            /* Gaya Dasar dan Font - Dibuat lebih kompak untuk A5 Landscape */
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 9pt; /* Ukuran font umum dikecilkan */
                color: #333;
                margin: 0;
                padding: 0px;
            }

            /* Container Utama - Memanfaatkan lebar penuh Landscape */
            .payslip-container {
                width: 98%;
                max-width: 700px;
                margin: 0;
                border: 1px solid #eee;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Shadow lebih tipis */
                padding: 15px; /* Padding dikurangi */
                background-color: #fff;
            }

            /* Header Perusahaan/Slip */
            /*.header {
                text-align: center;
                padding-bottom: 10px;
                margin-bottom: 20px;
                border-bottom: 2px solid #28a745;
            }
            .header h3 {
                margin: 0;
                color: #28a745;
                font-size: 15pt;
                font-weight: 600;
            }
            .header p {
                margin: 4px 0 0;
                color: #666;
                font-size: 8.5pt;
            } */

            .header {
                margin-bottom: 0px;
                padding-bottom: 5px;
                border-bottom: 2px solid #28a745;
            }

            .header-table {
                width: 100%;
                border-collapse: collapse;
            }

            .logo-col {
                width: 70px;
                vertical-align: middle;
            }

            .unit-col {
                vertical-align: middle;
                text-align: left;
                width: 20%;
            }

            .right-col {
                vertical-align: middle;
                text-align: right;
                width: 50%;
            }

            .unit-col h3,
            .right-col h3 {
                margin: 0;
                color: #28a745;
                font-size: 16pt;
                font-weight: 600;
            }

            .unit-col p,
            .right-col p {
                margin: 4px 0 0;
                color: #666;
                font-size: 8.5pt;
                max-width: 50px;
                word-wrap: break-word;
                white-space: normal;
                display: block;
            }

            /* Detail Karyawan */
            .employee-details {
                width: 100%;
                margin-bottom: 0px;
                font-size: 7pt;
            }
            .employee-details td {
                padding: 2px 0; /* Padding vertikal sangat dikurangi */
                vertical-align: top;
            }
            .employee-details td:first-child {
                width: 20%;
                font-weight: bold;
            }
            .employee-details td:nth-child(2) {
                width: 3%;
            }

            /* Tabel Gaji dan Potongan */
            .earnings-deductions {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0 0px; /* Jarak antar baris dikurangi */
                margin-bottom: 0px;
            }
            .earnings-deductions th,
            .earnings-deductions td {
                padding: 5px 10px;
                text-align: left;
                border-bottom: 1px solid #f0f0f0;
                font-size: 7pt; /* Ukuran font di rincian dikecilkan */
            }
            .earnings-deductions th {
                background-color: #f8f9fa;
                font-weight: 600;
                color: #495057;
                text-transform: uppercase;
                font-size: 7pt;
            }
            .earnings-deductions .amount {
                text-align: right;
                font-weight: bold;
                width: 25%;
                white-space: nowrap;
            }

            /* Bagian Total Akhir */
            .net-payment-box {
                background-color: #e6ffed;
                border: 1px solid #28a745;
                padding: 10px; /* Padding dikurangi */
                margin-top: 2px;
                text-align: left;
                border-radius: 4px;
                margin-bottom: 0px;
            }
            .net-payment-box strong {
                display: block;
                font-size: 8pt;
                color: #28a745;
                margin-bottom: 5px;
            }
            .net-payment-box h2 {
                margin: 2;
                font-size: 8pt;
                color: #28a745;
                font-weight: 700;
            }

            .signatures {
                width: 100%;
                margin-top: 0px;
            }
            .signatures td {
                width: 33.33%;
                text-align: center;
                padding-top: 10px;
                font-size: 7pt;
            }
            .signatures .name-line {
                border-bottom: 1px solid #333;
                display: inline-block;
                width: 90%;
                margin-top: 0px;
                margin-bottom: 2px;
            }
        </style>
    </head>

    <body>
        <div class="">
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td class="logo-col">
                            @if ($unit_image_path)
                                <img
                                    src="file://{{ $unit_image_path }}"
                                    alt="Logo Unit"
                                    style="
                                        width: 70px;
                                        height: 70px;
                                        border-radius: 50%;
                                        object-fit: cover;
                                    "
                                />
                            @else
                                <img
                                    src="{{ public_path("assets/images/videa.png") }}"
                                    alt="Default Logo"
                                    style="
                                        width: 70px;
                                        height: 70px;
                                        border-radius: 50%;
                                        object-fit: cover;
                                    "
                                />
                            @endif
                        </td>

                        <td class="unit-col">
                            <h3>{{ $payment->officer->unit->nama_unit }}</h3>
                            <p>{{ $payment->officer->unit->alamat }}</p>
                        </td>

                        <td class="right-col">
                            <h3>SLIP GAJI BULANAN</h3>
                            <p>
                                Periode:
                                <strong>
                                    {{
                                        \Carbon\Carbon::createFromDate($payment->payment_year, $payment->payment_month, 1)
                                            ->locale("id")
                                            ->translatedFormat("F Y")
                                    }}
                                </strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            {{-- Identitas Karyawan --}}
            <table class="employee-details">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><strong>{{ $payment->officer->name }}</strong></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>
                        {{ $payment->officer->position->positions_name ?? "-" }}
                    </td>
                </tr>
                <tr>
                    <td>Tanggal Pembayaran</td>
                    <td>:</td>
                    <td>
                        {{ $payment->updated_at->locale("id")->translatedFormat("d F Y") }}
                    </td>
                </tr>
            </table>

            {{-- Tabel Penerimaan & Potongan --}}

            <table width="100%" style="border-collapse: collapse">
                <tr>
                    <!-- ===================== -->
                    <!--      KIRI (PENERIMAAN) -->
                    <!-- ===================== -->
                    <td width="50%" valign="top" style="padding-right: 8px">
                        <table width="100%" class="earnings-deductions">
                            <thead>
                                <tr>
                                    <th colspan="2" style="padding-top: 10px">
                                        <i>Penerimaan</i>
                                    </th>
                                    <th><i>Jumlah (IDR)</i></th>
                                </tr>
                            </thead>

                            <tbody>
                                {{-- Gaji pokok --}}
                                <tr>
                                    <td colspan="2">
                                        Gaji Pokok (Rp
                                        {{ number_format($payment->details["salary"]) }}
                                        x
                                        {{ (int) ($payment->teaching_hour_week != 0 ? $payment->teaching_hour_week : $payment->teaching_hour_month) }})
                                    </td>
                                    <td class="amount" style="font-weight: 500">
                                        Rp
                                        {{ number_format($payment->details["salary"] * ($payment->teaching_hour_week != 0 ? $payment->teaching_hour_week : $payment->teaching_hour_month), 0, ",", ".") }}
                                    </td>
                                </tr>

                                {{-- Komponen / Tunjangan --}}
                                @foreach ($payment->details["components"] as $component)
                                    <tr>
                                        <td colspan="2">
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
                                    <td colspan="2">
                                        Kehadiran Staff (Rp
                                        {{ number_format($payment->details["staff_allowance"] ?? 0, 0, ",", ".") }}
                                        x {{ $payment->presence }} )
                                    </td>
                                    <td class="amount" style="font-weight: 500">
                                        Rp
                                        {{ number_format($payment->details["staff_allowance"] * $payment->presence, 0, ",", ".") }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        Transport (Rp
                                        {{ number_format($payment->details["transport_allowance"] ?? 0, 0, ",", ".") }}
                                        x {{ $payment->presence_count }} )
                                    </td>
                                    <td class="amount" style="font-weight: 500">
                                        Rp
                                        {{ number_format($payment->details["transport_allowance"] * $payment->presence_count, 0, ",", ".") }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        Makan (Rp
                                        {{ number_format($payment->details["meal_allowance"] ?? 0, 0, ",", ".") }}
                                        x {{ $payment->presence_count }} )
                                    </td>
                                    <td class="amount" style="font-weight: 500">
                                        Rp
                                        {{ number_format($payment->details["meal_allowance"] * $payment->presence_count, 0, ",", ".") }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        Lainnya (Rp
                                        {{ number_format($payment->details["other_allowance"] ?? 0, 0, ",", ".") }}
                                        x {{ $payment->presence_count }} )
                                    </td>
                                    <td class="amount" style="font-weight: 500">
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
                                    <td colspan="2">TOTAL PENERIMAAN</td>
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
                                    <th colspan="2" style="padding-top: 10px">
                                        <i>Potongan</i>
                                    </th>
                                    <th><i>Jumlah(IDR)</i></th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($payment->details["deductions"] as $deduction)
                                    <tr>
                                        <td colspan="2">
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
                                    <td colspan="2">TOTAL POTONGAN</td>
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

            {{-- Catatan --}}
            <div class="notes-section">
                <table
                    width="100%"
                    style="
                        border-collapse: collapse;
                        background: #fffbe6;
                        border-left: 4px solid #ffc107;
                        font-size: 8pt;
                        margin-top: 0;
                        margin-bottom: 0;
                    "
                >
                    <tr>
                        <!-- KIRI: CATATAN -->
                        <td width="70%" valign="top" style="padding: 4px">
                            <strong>Catatan:</strong>
                            <p style="margin: 0; padding: 0">
                                {{ $payment->notes ?? "-" }}
                            </p>
                        </td>

                        <!-- KANAN: NOMINAL -->
                        <td width="30%" valign="top" style="padding: 4px">
                            <strong>Nominal:</strong>
                            <p style="margin: 0; padding: 0">
                                Rp
                                {{ number_format($payment->salary_note, 0, ",", ".") }}
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Gaji Bersih --}}

            <table
                width="100%"
                style="
                    border-collapse: collapse;
                    background: #e6ffed;
                    border: 1px solid #28a745;
                    padding: 10px;
                    margin-top: 2px;
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
                                TOTAL GAJI BERSIH DIBAYARKAN
                            </strong>
                        </div>
                        <div>
                            <span
                                style="
                                    font-size: 8pt;
                                    font-weight: 700;
                                    color: #28a745;
                                "
                            >
                                Rp
                                {{ number_format($payment->net_payment, 0, ",", ".") }}
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
                            <span style="font-size: 8pt; font-style: italic">
                                ({{ ucwords(terbilang($payment->net_payment)) }}
                                Rupiah)
                            </span>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Kolom tanda tangan --}}
            <table class="signatures">
                <tr>
                    <td>
                        Disetujui Oleh,
                        <br />
                        <br />
                        <br />
                        <br />
                        <span class="name-line">
                            ({{ $kepalaSekolah->name ?? "........................................" }})
                        </span>
                        <br />
                        Kepala Sekolah
                    </td>

                    <td>
                        Dibuat Oleh,
                        <br />
                        <br />
                        <br />
                        <br />
                        <span class="name-line">
                            ( {{ $bendahara->name ?? "........................................" }} )
                        </span>
                        <br />
                        Bendahara
                    </td>

                    <td>
                        Penerima,
                        <br />
                        <br />
                        <br />
                        <br />
                        <span class="name-line">
                            ({{ $payment->officer->name }})
                        </span>
                        <br />
                        {{ $payment->officer->position }}
                    </td>
                </tr>
            </table>

            <div
                style="
                    text-align: center;
                    margin-top: 15px;
                    font-size: 7.5pt;
                    color: #aaa;
                "
            >
                Slip ini dicetak secara otomatis dan berlaku sah tanpa tanda
                tangan basah.
            </div>
        </div>
    </body>
</html>
